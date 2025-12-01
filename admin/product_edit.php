<?php
// admin/product_edit.php (with categories as checkboxes + gender radio + live preview)
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$variants = [];
$product_images = [];

// Try to load categories from DB; if none, we'll fall back to the fixed list below.
$categories = [];
try {
    $cstmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $cstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Fixed category fallback (your requested list)
$defaultCategories = [
    ['id' => 'coord-set', 'name' => 'Co-ord Set'],
    ['id' => 'dresses',   'name' => 'Dresses'],
    ['id' => 'shirts',    'name' => 'Shirts'],
    ['id' => 'pants',     'name' => 'Pants'],
    ['id' => 'suits',     'name' => 'Suits'],
    ['id' => 'saree',     'name' => 'Saree'],
];

if ($id > 0) {
    // load product
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
    $stmt->execute(['id'=>$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    // load variants
    try {
        $vstmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = :pid ORDER BY id ASC");
        $vstmt->execute(['pid'=>$id]);
        $variants = $vstmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $variants = [];
    }

    // load product extra images (if table exists)
    try {
        $imgstmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = :pid ORDER BY id ASC");
        $imgstmt->execute(['pid'=>$id]);
        $product_images = $imgstmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $product_images = [];
    }
} else {
    $product_images = [];
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['crud_csrf'])) $_SESSION['crud_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['crud_csrf'];

/**
 * Helper: parse stored categories into array
 * Accepts CSV string or JSON array or already-array.
 */
function parse_saved_categories($raw) {
    if (empty($raw)) return [];
    if (is_array($raw)) return $raw;
    $raw = trim($raw);
    // try JSON first
    $dec = json_decode($raw, true);
    if (is_array($dec)) return $dec;
    // fallback to comma separated
    $parts = array_map('trim', explode(',', $raw));
    return array_filter($parts, function($x){ return $x !== ''; });
}

// compute selected categories from product (support CSV or JSON)
$selectedCats = [];
if ($product && isset($product['categories']) && $product['categories'] !== null) {
    $selectedCats = parse_saved_categories($product['categories']);
}

// gender value (if saved)
$savedGender = $product['gender'] ?? '';

// decide which category source to show: DB categories if available, otherwise default list
$categoriesToShow = !empty($categories) ? array_map(function($c){ return ['id'=>$c['id'],'name'=>$c['name']]; }, $categories) : $defaultCategories;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?php echo $product ? 'Edit' : 'Add'; ?> Product</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .variant-row { border:1px dashed #ddd; padding:10px; margin-bottom:8px; border-radius:6px; background:#fff; }
    .thumb { height:70px; object-fit:cover; border-radius:4px; margin-right:8px; border:1px solid #eee; }
    .gallery-wrap { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .small-note { font-size: .85rem; color:#6c757d; }
    .cat-grid { display:flex; gap:8px; flex-wrap:wrap; }
    .cat-item { min-width:140px; }
    .preview-badge { margin-right:6px; }
  </style>
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:1100px">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><?php echo $product ? 'Edit' : 'Add'; ?> Product</h3>
    <div>
      <a href="products.php" class="btn btn-outline-secondary">Back to list</a>
    </div>
  </div>

  <div class="card p-3 shadow-sm">
    <form action="product_save.php" method="post" enctype="multipart/form-data" id="product-form">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <input type="hidden" name="id" value="<?php echo $product ? (int)$product['id'] : 0; ?>">

      <div class="row">
        <div class="col-md-8">
          <div class="mb-3">
            <label class="form-label">Product Name</label>
            <input id="product-name" name="name" class="form-control" required value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>">
            <div class="mt-2">
              <strong>Selected:</strong>
              <span id="preview-categories"></span>
              <span id="preview-gender" style="margin-left:12px;"></span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Short Description (summary)</label>
            <input name="short_description" class="form-control" maxlength="255" value="<?php echo $product ? htmlspecialchars($product['short_description'] ?? '') : ''; ?>">
            <div class="form-text small-note">Shown on product list / quick view. Keep it brief (1-2 lines).</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Long Description (detailed)</label>
            <textarea name="long_description" rows="8" class="form-control"><?php echo $product ? htmlspecialchars($product['long_description'] ?? $product['description'] ?? '') : ''; ?></textarea>
            <div class="form-text small-note">Full product description shown on the product page. (HTML allowed if you sanitize on server)</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Details</label>
            <textarea name="details" rows="4" class="form-control"><?php echo $product ? htmlspecialchars($product['details'] ?? '') : ''; ?></textarea>
            <div class="form-text small-note">Technical details or composition (e.g., 100% cotton, washing instructions).</div>
          </div>

        </div>

        <div class="col-md-4">
          <div class="mb-3">
            <label class="form-label">Base Price (INR)</label>
            <input name="price" type="number" step="0.01" class="form-control" required value="<?php echo $product ? htmlspecialchars($product['price']) : '0.00'; ?>">
            <div class="form-text small-note">Variant price (if set) will override this.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Stock (base)</label>
            <input name="stock" type="number" class="form-control" required value="<?php echo $product ? (int)$product['stock'] : 0; ?>">
            <div class="form-text small-note">Use product-level stock if you don't use variants.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Main Image (optional)</label>
            <input type="file" name="main_image" accept="image/*" class="form-control">
            <div class="form-text small-note">This will be the primary image shown on listings.</div>
            <?php if ($product && !empty($product['image']) && file_exists(__DIR__ . '/../public/' . $product['image'])): ?>
              <div class="mt-2">
                <img src="<?php echo '../' . htmlspecialchars($product['image']); ?>" style="height:90px; object-fit:cover;" alt="">
                <div class="form-text">Uploading a new main image replaces the old one.</div>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

      <hr>

      <!-- CATEGORIES (checkbox grid) -->
      <div class="mb-3">
        <label class="form-label">Categories</label>
        <div class="cat-grid mt-2" id="categories-grid">
          <?php
            // render checkboxes for categoriesToShow
            foreach ($categoriesToShow as $c):
              // ID may be numeric (DB) or string (fallback). Use value as-is.
              $cid = htmlspecialchars($c['id']);
              $cname = htmlspecialchars($c['name']);
              $checked = in_array((string)$c['id'], array_map('strval',$selectedCats), true) ? 'checked' : '';
          ?>
            <div class="form-check cat-item">
              <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" value="<?php echo $cid;?>" id="cat_<?php echo $cid;?>" <?php echo $checked;?>>
              <label class="form-check-label" for="cat_<?php echo $cid;?>"><?php echo $cname;?></label>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="form-text small-note">Select one or more categories. Categories are submitted as <code>categories[]</code>.</div>
      </div>

      <!-- GENDER -->
      <div class="mb-3">
        <label class="form-label">Gender</label>
        <div class="d-flex gap-3 align-items-center mt-2">
          <?php
            $g = $savedGender ? $savedGender : '';
            $maleChecked = ($g === 'Men' || $g === 'men' || $g === 'male') ? 'checked' : '';
            $femaleChecked = ($g === 'Women' || $g === 'women' || $g === 'female') ? 'checked' : '';
          ?>
          <div class="form-check">
            <input class="form-check-input gender-radio" type="radio" name="gender" id="gender_men" value="Men" <?php echo $maleChecked; ?>>
            <label class="form-check-label" for="gender_men">Men</label>
          </div>
          <div class="form-check">
            <input class="form-check-input gender-radio" type="radio" name="gender" id="gender_women" value="Women" <?php echo $femaleChecked; ?>>
            <label class="form-check-label" for="gender_women">Women</label>
          </div>
          <div class="form-text small-note ms-2">Choose product gender to help filtering on storefront.</div>
        </div>
      </div>

      <hr>

      <!-- Gallery upload + existing images -->
      <div class="mb-3">
        <label class="form-label">Additional Gallery Images</label>
        <input type="file" name="product_images[]" accept="image/*" class="form-control" multiple>
        <div class="form-text small-note mt-1">Upload multiple images to showcase the product (angles, closeups). Existing images listed below with keep/delete options.</div>

        <?php if (!empty($product_images)): ?>
          <div class="mt-2 gallery-wrap">
            <?php foreach ($product_images as $pi):
              $imgPath = __DIR__ . '/../public/' . ltrim($pi['path'],'/');
              $src = '../' . ltrim($pi['path'],'/');
              $imgId = (int)$pi['id'];
            ?>
              <div style="text-align:center;">
                <?php if (file_exists($imgPath)): ?>
                  <img src="<?php echo htmlspecialchars($src); ?>" class="thumb" alt="">
                <?php else: ?>
                  <div class="thumb" style="display:flex;align-items:center;justify-content:center;background:#f5f5f5">No file</div>
                <?php endif; ?>
                <div class="form-check mt-1">
                  <input class="form-check-input" type="checkbox" name="existing_product_images_keep[]" value="<?php echo $imgId; ?>" id="keep_img_<?php echo $imgId; ?>" checked>
                  <label class="form-check-label small-note" for="keep_img_<?php echo $imgId; ?>">Keep</label>
                </div>
                <div class="small-note">ID: <?php echo $imgId; ?></div>
                <input type="hidden" name="existing_product_images_ids[]" value="<?php echo $imgId; ?>">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <hr>

      <!-- Variants block (unchanged logic from your previous file) -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Variants (size & color)</h5>
        <button type="button" class="btn btn-sm btn-success" id="add-variant">Add Variant</button>
      </div>

      <div id="variants-container">
        <?php if (!empty($variants)): ?>
          <?php foreach ($variants as $i => $v):
            $variant_images = [];
            if (!empty($v['images'])) {
              $maybe = json_decode($v['images'], true);
              if (is_array($maybe)) $variant_images = $maybe;
              else $variant_images = array_filter(array_map('trim', explode(',', $v['images'])));
            }
          ?>
          <div class="variant-row" data-variant-id="<?php echo (int)$v['id']; ?>">
            <input type="hidden" name="variants[<?php echo $i; ?>][id]" value="<?php echo (int)$v['id']; ?>">
            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label small">Variant SKU</label>
                <input name="variants[<?php echo $i; ?>][sku]" class="form-control" value="<?php echo htmlspecialchars($v['variant_sku']); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label small">Size</label>
                <input name="variants[<?php echo $i; ?>][size]" class="form-control" value="<?php echo htmlspecialchars($v['size']); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label small">Color</label>
                <input name="variants[<?php echo $i; ?>][color]" class="form-control" value="<?php echo htmlspecialchars($v['color']); ?>">
              </div>
              <div class="col-md-2">
                <label class="form-label small">Price (override)</label>
                <input name="variants[<?php echo $i; ?>][price]" type="number" step="0.01" class="form-control" value="<?php echo $v['price'] !== null ? htmlspecialchars($v['price']) : ''; ?>">
              </div>
              <div class="col-md-1">
                <label class="form-label small">Stock</label>
                <input name="variants[<?php echo $i; ?>][stock]" type="number" class="form-control" value="<?php echo (int)$v['stock']; ?>">
              </div>
            </div>

            <div class="mt-2">
              <label class="form-label small">Variant primary image (optional)</label>
              <input type="file" name="variants_files[<?php echo $i; ?>]" accept="image/*" class="form-control">
              <?php if (!empty($v['image']) && file_exists(__DIR__ . '/../public/' . $v['image'])): ?>
                <div class="mt-2"><img src="<?php echo '../' . htmlspecialchars($v['image']); ?>" style="height:60px;object-fit:cover" alt=""></div>
              <?php endif; ?>
            </div>

            <div class="mt-2">
              <label class="form-label small">Variant extra images (multiple)</label>
              <input type="file" name="variants_files_multi[<?php echo $i; ?>][]" accept="image/*" class="form-control" multiple>
              <div class="form-text small-note">Upload multiple images specific to this variant (closeups, model wearing this variant, etc.)</div>

              <?php if (!empty($variant_images)): ?>
                <div class="mt-2 gallery-wrap">
                  <?php foreach ($variant_images as $viIndex => $viPath):
                    $viFull = __DIR__ . '/../public/' . ltrim($viPath,'/');
                  ?>
                    <div style="text-align:center;">
                      <?php if (file_exists($viFull)): ?>
                        <img src="<?php echo '../' . htmlspecialchars($viPath); ?>" class="thumb" alt="">
                      <?php else: ?>
                        <div class="thumb" style="display:flex;align-items:center;justify-content:center;background:#f5f5f5">No file</div>
                      <?php endif; ?>
                      <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" name="variants_existing_images_keep[<?php echo $i; ?>][]" value="<?php echo htmlspecialchars($viPath); ?>" id="vkeep_<?php echo $i . '_' . $viIndex; ?>" checked>
                        <label class="form-check-label small-note" for="vkeep_<?php echo $i . '_' . $viIndex; ?>">Keep</label>
                      </div>
                      <input type="hidden" name="variants_existing_images_list[<?php echo $i; ?>][]" value="<?php echo htmlspecialchars($viPath); ?>">
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="mt-2 text-end">
              <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- variant template -->
      <template id="variant-template">
        <div class="variant-row" data-variant-id="">
          <input type="hidden" name="__INDEX__" value="">
          <div class="row g-2">
            <div class="col-md-3">
              <label class="form-label small">Variant SKU</label>
              <input name="variants[__INDEX__][sku]" class="form-control" value="">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Size</label>
              <input name="variants[__INDEX__][size]" class="form-control" value="">
            </div>
            <div class="col-md-3">
              <label class="form-label small">Color</label>
              <input name="variants[__INDEX__][color]" class="form-control" value="">
            </div>
            <div class="col-md-2">
              <label class="form-label small">Price (override)</label>
              <input name="variants[__INDEX__][price]" type="number" step="0.01" class="form-control" value="">
            </div>
            <div class="col-md-1">
              <label class="form-label small">Stock</label>
              <input name="variants[__INDEX__][stock]" type="number" class="form-control" value="0">
            </div>
          </div>
          <div class="mt-2">
            <label class="form-label small">Variant primary image (optional)</label>
            <input type="file" name="variants_files[__INDEX__]" accept="image/*" class="form-control">
          </div>
          <div class="mt-2">
            <label class="form-label small">Variant extra images (multiple)</label>
            <input type="file" name="variants_files_multi[__INDEX__][]" accept="image/*" class="form-control" multiple>
            <div class="form-text small-note">Multiple images for this variant.</div>
          </div>
          <div class="mt-2 text-end">
            <button type="button" class="btn btn-sm btn-danger remove-variant">Remove</button>
          </div>
        </div>
      </template>

      <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary" type="submit">Save Product</button>
        <a class="btn btn-secondary" href="products.php">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const container = document.getElementById('variants-container');
  const tpl = document.getElementById('variant-template').innerHTML;

  function nextIndex(){
    const rows = container.querySelectorAll('.variant-row');
    let max = -1;
    rows.forEach(r=>{
      const input = r.querySelector('input[name^="variants"]');
      if (!input) return;
      const m = input.name.match(/^variants\[(\d+)\]/);
      if (m) max = Math.max(max, parseInt(m[1],10));
    });
    return max + 1;
  }

  document.getElementById('add-variant').addEventListener('click', function(){
    const idx = nextIndex();
    let html = tpl.replace(/__INDEX__/g, idx);
    const wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    container.appendChild(wrapper.firstElementChild);
  });

  container.addEventListener('click', function(e){
    if (e.target.matches('.remove-variant')) {
      const row = e.target.closest('.variant-row');
      if (row) row.remove();
    }
  });

  // --- CATEGORY & GENDER live preview ---
  const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
  const genderRadios = document.querySelectorAll('.gender-radio');
  const previewCats = document.getElementById('preview-categories');
  const previewGender = document.getElementById('preview-gender');

  function updatePreview() {
    const selected = [];
    categoryCheckboxes.forEach(cb=>{
      if (cb.checked) {
        const label = document.querySelector('label[for="'+cb.id+'"]');
        selected.push(label ? label.textContent.trim() : cb.value);
      }
    });
    previewCats.innerHTML = '';
    if (selected.length) {
      selected.forEach(s=>{
        const span = document.createElement('span');
        span.className = 'badge bg-primary preview-badge';
        span.textContent = s;
        previewCats.appendChild(span);
      });
    } else {
      previewCats.textContent = '(none)';
    }

    let g = '';
    genderRadios.forEach(r=>{
      if (r.checked) g = r.value;
    });
    previewGender.innerHTML = '';
    if (g) {
      const gspan = document.createElement('span');
      gspan.className = 'badge bg-success';
      gspan.textContent = g;
      previewGender.appendChild(gspan);
    } else {
      previewGender.textContent = '';
    }
  }

  categoryCheckboxes.forEach(cb=>cb.addEventListener('change', updatePreview));
  genderRadios.forEach(r=>r.addEventListener('change', updatePreview));

  // initial preview
  updatePreview();

  // ensure that if product-name changes we still keep preview visible (optional UX)
  document.getElementById('product-name').addEventListener('input', function(){ /* noop for now */ });

})();
</script>
</body>
</html>
