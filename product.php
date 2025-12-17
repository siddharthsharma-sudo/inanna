<?php
// product.php
// Improved product page (images fix for project subfolder + project-root uploads mapping)

// --- bootstrap / DB
require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: products.php'); exit; }

// fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id'=>$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="container my-4"><div class="alert alert-warning">Product not found. <a href="products.php">Back to products</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// --- compute web base (handles project in a subfolder like /inanna on localhost)
$webBase = '';
if (!empty($_SERVER['SCRIPT_NAME'])) {
    $wb = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME']));
    if ($wb === '/' || $wb === '\\' || $wb === '.') $wb = '';
    $webBase = rtrim($wb, '/');
}

// helper: site-wide info (static)
function site_common_info_block_html(){
    return <<<HTML
<h4>Information</h4>
<p><strong>Shipping</strong><br>We currently offer free shipping worldwide on all orders over \$100.</p>
<p><strong>Sizing</strong><br>Fits true to size. Do you need size advice?</p>
<p><strong>Return &amp; exchange</strong><br>If you are not satisfied with your purchase you can return it to us within 14 days for an exchange or refund. More info.</p>
<p><strong>Assistance</strong><br>Contact us on (+91) 7456000222, or email us at worldofinanna@gmail.com</p>
HTML;
}

// check which optional product cols exist (categories, gender, short_description, long_description, details, info_block, gallery)
function table_has_column(PDO $pdo, $table, $col) {
    try {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1");
        $q->execute(['t'=>$table,'c'=>$col]);
        return (bool)$q->fetchColumn();
    } catch (Exception $e) { return false; }
}
$has_categories_col = table_has_column($pdo,'products','categories');
$has_gender_col     = table_has_column($pdo,'products','gender');
$has_short_desc     = table_has_column($pdo,'products','short_description');
$has_long_desc      = table_has_column($pdo,'products','long_description');
$has_details_col    = table_has_column($pdo,'products','details');
$has_info_col       = table_has_column($pdo,'products','info_block');
$has_gallery_col    = table_has_column($pdo,'products','gallery');

// --- variants: include optional image/images columns if present
try {
    $cols = $pdo->query("SHOW COLUMNS FROM product_variants")->fetchAll(PDO::FETCH_COLUMN,0);
    $haveVarImageCol = in_array('image',$cols);
    $haveVarImagesCol = in_array('images',$cols);
} catch (Exception $e) {
    $haveVarImageCol = false;
    $haveVarImagesCol = false;
}
$variantSelectCols = ['id','variant_sku','size','color','price','stock'];
if ($haveVarImageCol) $variantSelectCols[] = 'image';
if ($haveVarImagesCol) $variantSelectCols[] = 'images';

$sql = "SELECT " . implode(', ', $variantSelectCols) . " FROM product_variants WHERE product_id = :pid ORDER BY size, color, id";
$vstmt = $pdo->prepare($sql);
$vstmt->execute(['pid'=>$id]);
$variants = $vstmt->fetchAll(PDO::FETCH_ASSOC);

// --- gather gallery images from multiple places ---
// 1) product_images table
// --------------------------------------------------
// FINAL GALLERY SOURCE (DB ONLY — NO FILE SCANNING)
// --------------------------------------------------

$product_images = [];

/* main image */
if (!empty($product['image'])) {
    $product_images[] = '/' . ltrim($product['image'], '/');
}

/* additional gallery images (JSON only) */
if ($has_gallery_col && !empty($product['gallery'])) {
    $decoded = json_decode($product['gallery'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $img) {
            $product_images[] = '/' . ltrim($img, '/');
        }
    }
}

/* de-duplicate */
$product_images = array_values(array_unique($product_images));

/* resolved images = final gallery */
$resolved_product_images = $product_images;

/* variants still stay as-is */


// 5) add variant images too (primary + any images list)
foreach ($variants as $vv) {
    if (!empty($vv['image'])) $product_images[] = $vv['image'];
    if (!empty($vv['images'])) {
        // could be JSON or CSV
        $dec = json_decode($vv['images'], true);
        if (is_array($dec)) $product_images = array_merge($product_images, $dec);
        else $product_images = array_merge($product_images, array_filter(array_map('trim', explode(',', $vv['images']))));
    }
}

// normalize and resolve image path -> URL (prefer root-relative URLs with $webBase)
function try_resolve_to_url($path) {
    global $webBase;
    if (empty($path)) return '';
    $raw = trim($path);
    $norm = str_replace('\\','/',$raw);

    // full URL
    if (preg_match('#^https?://#i', $norm)) return $norm;

    // already root-relative
    if (strpos($norm, '/') === 0) {
        if ($webBase && stripos($norm, '/uploads/') === 0) return $webBase . $norm;
        return preg_replace('#/+#','/',$norm);
    }

    // if it's already 'uploads/...' or 'images/...' keep it root-relative with webBase
    if (stripos($norm, 'uploads/') === 0 || stripos($norm, 'images/') === 0) {
        return ($webBase ? $webBase : '') . '/' . ltrim($norm, '/');
    }

    // if path contains folders, return as root-relative preserving folders (and prefix webBase)
    if (strpos($norm, '/') !== false) {
        return ($webBase ? $webBase : '') . '/' . ltrim($norm, '/');
    }

    // Try to find file on disk and map to web URL (with webBase if needed)
    $candidates = [];

    // relative to this script
    $candidates[] = __DIR__ . '/' . ltrim($norm, '/');
    // project-root guesses
    $candidates[] = dirname(__DIR__) . '/uploads/' . ltrim($norm, '/');
    $candidates[] = dirname(__DIR__) . '/uploads/products/' . ltrim($norm, '/');
    $candidates[] = dirname(dirname(__DIR__)) . '/uploads/' . ltrim($norm, '/');
    $candidates[] = dirname(dirname(__DIR__)) . '/uploads/products/' . ltrim($norm, '/');
    // DOCUMENT_ROOT
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/' . ltrim($norm, '/');
        $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/products/' . ltrim($norm, '/');
    }
    // fallback common locations
    $bn = basename($norm);
    $candidates[] = __DIR__ . '/uploads/' . $bn;
    $candidates[] = __DIR__ . '/images/' . $bn;
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/' . $bn;
        $candidates[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/images/' . $bn;
    }

    foreach ($candidates as $fs) {
        if ($fs && file_exists($fs) && is_file($fs)) {
            $fsNorm = str_replace('\\','/',$fs);
            if (stripos($fsNorm, '/uploads/') !== false) {
                $pos = stripos($fsNorm, '/uploads/');
                $url = substr($fsNorm, $pos);
                return ($webBase ? $webBase : '') . $url;
            }
            if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                $doc = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'],'/')) . '/';
                if (strpos($fsNorm, $doc) === 0) {
                    return ($webBase ? $webBase : '') . '/' . ltrim(substr($fsNorm, strlen($doc)), '/');
                }
            }
            return ($webBase ? $webBase : '') . '/uploads/' . ltrim(basename($fsNorm), '/');
        }
    }

    // final fallback - assume uploads root and prefix webBase
    return ($webBase ? $webBase : '') . '/uploads/' . ltrim($norm, '/');
}

// resolve and de-duplicate while preserving order
$resolved_product_images = [];
$seen = [];
foreach ($product_images as $p) {
    $u = try_resolve_to_url($p);
    if ($u && !isset($seen[$u])) { $seen[$u] = true; $resolved_product_images[] = $u; }
}

// prepare variant images arrays too (resolved)
foreach ($variants as &$v) {
    $v['images_arr'] = [];
    if (!empty($v['images'])) {
        $dec = json_decode($v['images'], true);
        if (is_array($dec)) $v['images_arr'] = $dec;
        else $v['images_arr'] = array_filter(array_map('trim', explode(',',$v['images'])));
    }
    if (!empty($v['image'])) $v['images_arr'] = array_merge([$v['image']], $v['images_arr']);
    // resolve to URLs
    $tmp = [];
    foreach ($v['images_arr'] as $pp) {
        $uu = try_resolve_to_url($pp);
        if ($uu && !in_array($uu, $tmp, true)) $tmp[] = $uu;
    }
    $v['images_resolved'] = $tmp;
}
unset($v);

// --- categories parsing if present (may be JSON or CSV) ---
$categoryBadges = [];
if ($has_categories_col && !empty($product['categories'])) {
    $raw = $product['categories'];
    $dec = json_decode($raw, true);
    if (is_array($dec)) $categoryBadges = $dec;
    else $categoryBadges = array_filter(array_map('trim', explode(',', $raw)));
}

// gender badge (if exists)
$genderBadge = $has_gender_col ? trim((string)($product['gender'] ?? '')) : '';

// product text fields (defensive)
$short_description = $has_short_desc ? ($product['short_description'] ?? '') : '';
$long_description  = $has_long_desc ? ($product['long_description'] ?? '') : ($product['description'] ?? '');
$details_text      = $has_details_col ? ($product['details'] ?? '') : '';
// info_block: we use static site common info (user requested)
$info_block_html = site_common_info_block_html();

// JSON for JS
$variants_json = json_encode($variants, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
$resolved_images_json = json_encode(array_values($resolved_product_images), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);

// include header
include __DIR__ . '/includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root { --header-offset: 90px; }
  body { background: #6b0000 !important; }
  .main-content { padding-top: var(--header-offset); min-height: calc(100vh - var(--header-offset)); color: #fff; }
  .card, .price-box { background: #fff; color: #212529; }

  .product-name, .product-meta, .meta-label, .measurement-label, .custom-size-label, .section-title-plain {
    color: #fff !important;
  }

  .product-card { border-radius:12px; overflow:visible;width: fit-content; }
  .product-main-img { width:100%; max-height:520px; object-fit:contain; border-radius:10px; background:#fafafa; display:block; }
  .thumb { height:72px; width:72px; object-fit:cover; border-radius:8px; border:1px solid #e9ecef; cursor:pointer; transition:transform .12s ease, box-shadow .12s ease; }
  .thumb:hover { transform:translateY(-4px); box-shadow:0 6px 18px rgba(15,23,42,0.06); }
  .thumb.active { outline:3px solid #0d6efd; box-shadow:0 8px 24px rgba(13,110,253,0.12); }

  .price-box { border-radius:10px; padding:16px; box-shadow:0 6px 18px rgba(15,23,42,0.04); }
  .price-large { font-size:1.6rem; font-weight:700; color:#0d6efd; }
  .stock-badge { font-weight:600; padding:.35rem .6rem; border-radius:999px; background:#f1f3f5; color:#212529; }

  .section-title { font-size:1.25rem; font-weight:700; margin-bottom:.5rem; color:#2b2b2b; }
  .section-title-plain { font-size:1.25rem; font-weight:700; color:#fff; }

  .measurement-field .form-control { text-align:center; font-weight:600; }
  .measurement-label { font-weight:600; font-size:.9rem; color:#fff; }
  .meta-label { font-weight:700; color:#fff; }
  .lead-strong { font-size:1.03rem; color:#fff; }
  .muted-small { color:#f1eaea; font-size:.9rem; }

  /* Transparent custom size block: no white bg */
 /* --- CUSTOM SIZE BLOCK DARK THEME --- */
#customSizeBlock {
    background: transparent !important;
    border: 1px solid rgba(255,255,255,0.25) !important;
}

#customSizeBlock .form-floating {
    background: rgba(0,0,0,0.35) !important;
    border-radius: 8px;
    padding: 6px;
}

#customSizeBlock .form-control {
    background: rgba(0,0,0,0.55) !important; 
    color: #fff !important;
    border: 1px solid rgba(255,255,255,0.25) !important;
}

#customSizeBlock .form-control:focus {
    background: rgba(0,0,0,0.8) !important;
    color: #fff !important;
    border-color: #0d6efd !important;
}

#customSizeBlock label {
    color:blue !important;
}


  /* variant buttons row */
  .variants-row { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
  .variant-btn {
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:8px;
    cursor:pointer;
    border:1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.04);
    color: #fff;
    transition: transform .08s ease, box-shadow .12s ease;
  }
  .variant-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.25); }
  .variant-btn.active { background: #fff; color: #212529; border-color:#0d6efd; box-shadow:0 8px 20px rgba(13,110,253,0.12); }

  .category-badges { display:flex; gap:6px; flex-wrap:wrap; margin-top:6px; }
  .category-badges .badge { background: rgba(255,255,255,0.12); color:#fff; border-radius:999px; padding:.35rem .6rem; }

  @media (max-width:768px) {
    .product-main-img { max-height:360px; }
    .thumb { height:60px; width:60px; }
    .variant-btn { padding:8px; }
  }

  .dbg { background:#fff; color:#000; padding:12px; border-radius:8px; margin-top:12px; font-size:.9rem; }
</style>

<main class="main-content">
  <div class="container my-5">
    <div class="row g-4">
      <!-- LEFT: images -->
      <div class="col-lg-6">
        <div class="card product-card p-3">
          <div id="main-image-wrap" class="mb-3 text-center">
            <?php $primary_url = $resolved_product_images[0] ?? null; ?>
            <?php if ($primary_url): ?>
              <img id="main-image" src="<?php echo htmlspecialchars($primary_url); ?>" class="product-main-img rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
              <div id="main-image" class="product-main-img d-flex align-items-center justify-content-center text-muted">
                <div>No image available</div>
              </div>
            <?php endif; ?>
          </div>

          <!-- thumbnails -->
          <?php if (!empty($resolved_product_images)): ?>
            <div class="mt-1 d-flex gap-2 flex-wrap" id="thumbs" role="list">
              <?php foreach ($resolved_product_images as $i => $imgUrl): ?>
                <img src="<?php echo htmlspecialchars($imgUrl); ?>" data-src="<?php echo htmlspecialchars($imgUrl); ?>" role="listitem" class="thumb<?php echo $i===0 ? ' active' : ''; ?>" alt="thumb-<?php echo $i; ?>">
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="small text-muted">No gallery images</div>
          <?php endif; ?>

         
        </div>
      </div>

      <!-- RIGHT: details -->
      <div class="col-lg-6">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h2 class="fw-bold mb-1 product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
            <div class="text-muted product-meta">SKU: <span class="fw-semibold" style="color:#fff;"><?php echo htmlspecialchars($product['sku'] ?? ''); ?></span></div>

            <!-- categories & gender -->
            <div class="category-badges mt-2">
              <?php foreach ($categoryBadges as $c): ?>
                <span class="badge"><?php echo htmlspecialchars($c); ?></span>
              <?php endforeach; ?>
              <?php if (!empty($genderBadge)): ?>
                <span class="badge" style="background: rgba(13,110,253,0.12); color:#fff;"><?php echo htmlspecialchars($genderBadge); ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="price-box mb-3">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <div class="small text-muted">Price</div>
              <div class="price-large" id="price-display">₹<?php echo number_format((float)($product['price'] ?? 0),2); ?></div>
            </div>
            <div class="text-end">
              <div class="small text-muted">Stock</div>
              <div class="stock-badge" id="stock-display"><?php echo (int)($product['stock'] ?? 0); ?></div>
            </div>
          </div>
          <?php if (!empty($short_description)): ?>
            <p class="mt-3 mb-0"><?php echo htmlspecialchars($short_description); ?></p>
          <?php endif; ?>
        </div>

        <!-- VARIANT BUTTONS (horizontal) -->
        <?php if (!empty($variants)): ?>
          <div class="mb-3">
            <div class="meta-label mb-2">Choose option</div>
            <div class="variants-row" id="variants-row" role="tablist" aria-label="Product variants">
              <?php foreach ($variants as $v): 
                $label = trim((string)($v['size'] ?? '') . (($v['color'] ?? '') ? ' / '.($v['color'] ?? '') : ''));
                if ($label === '') $label = ($v['variant_sku'] ?? '') ?: 'Variant ' . $v['id'];
              ?>
                <button type="button"
                        class="variant-btn"
                        data-vid="<?php echo (int)$v['id']; ?>"
                        data-price="<?php echo isset($v['price']) && $v['price']!==null && $v['price']!=='' ? (float)$v['price'] : (float)$product['price']; ?>"
                        data-stock="<?php echo (int)($v['stock'] ?? 0); ?>"
                        data-sku="<?php echo htmlspecialchars($v['variant_sku'] ?? ''); ?>"
                        data-image="<?php echo htmlspecialchars($v['images_resolved'][0] ?? ''); ?>"
                        data-images='<?php echo json_encode($v['images_resolved'] ?? []); ?>'
                        aria-pressed="false">
                  <div style="text-align:left;">
                    <div style="font-weight:700;"><?php echo htmlspecialchars($label); ?></div>
                    <?php if (!empty($v['variant_sku'])): ?><div style="font-size:.85rem;color:rgba(255,255,255,0.8)"><?php echo htmlspecialchars($v['variant_sku']); ?></div><?php endif; ?>
                  </div>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <form id="addcart" action="add_to_cart.php" method="post" class="row gx-2 gy-2 align-items-end">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
          <input type="hidden" name="variant_id" id="variant_id" value="">
          <input type="hidden" name="redirect" value="cart.php">

          <div class="col-auto">
            <label class="form-label small meta-label">Quantity</label>
            <input type="number" name="qty" id="qty" value="1" min="1" max="<?php echo max(1,(int)($product['stock'] ?? 1)); ?>" class="form-control" style="width:120px;">
          </div>

          <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-success btn-lg">Add to cart</button>
          </div>

          <!-- custom size toggle -->
          <div class="col-12 mt-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="customSizeToggle" name="custom_size" value="1">
              <label class="form-check-label custom-size-label" for="customSizeToggle">Request custom size</label>
            </div>

            <div id="customSizeBlock" class="mt-3 p-3 border rounded" style="display:none;">
              <div class="muted-small mb-3">Please provide measurements in centimeters. We'll contact you if we need clarifications.</div>
              <div class="d-flex flex-wrap gap-2">
                <?php
                  $measures = ['Shoulder','Bust','Waist','Hip','Length','Arm Round','Thigh'];
                  foreach ($measures as $m):
                ?>
                  <div class="form-floating measurement-field" style="min-width:120px;">
                    <input type="text" class="form-control" id="<?php echo 'm_'.str_replace(' ','_',strtolower($m)); ?>" name="measurements[<?php echo $m; ?>]" placeholder="<?php echo $m; ?>">
                    <label for="<?php echo 'm_'.str_replace(' ','_',strtolower($m)); ?>"><?php echo $m; ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </form>

      </div> <!-- end right column -->
    </div>

    <!-- Information + Details two-column -->
    <div class="row mt-4">
      <div class="col-lg-8">
        <div class="row g-3">
          <div class="col-md-6">
            <!-- Information column (static site-wide) -->
            <div class="card info-card p-3 h-100">
              <div class="card-body">
         
                <?php echo $info_block_html; ?>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <!-- Details column -->
            <div class="card p-3 h-100">
              <div class="card-body">
                <h3 class="section-title">Details</h3>
                <?php if (!empty($details_text)): ?>
                  <p><?php echo nl2br(htmlspecialchars($details_text)); ?></p>
                <?php else: ?>
                  <p class="text-muted">No details available.</p>
                <?php endif; ?>

                <?php if (!empty($long_description)): ?>
                  <hr>
                  <h5 class="fw-bold">Description</h5>
                  <div><?php echo nl2br(htmlspecialchars($long_description)); ?></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4 d-flex align-items-start justify-content-end">
        <a href="products.php" class="btn btn-link" style="color:#fff;">Back to products</a>
      </div>
    </div>
  </div>
</main>

<script>
(function(){
  const variants = <?php echo $variants_json ?: '[]'; ?>;
  const productImages = <?php echo $resolved_images_json ?: '[]'; ?>;
  const basePrice = <?php echo json_encode((float)($product['price'] ?? 0)); ?>;
  const baseStock = <?php echo json_encode((int)($product['stock'] ?? 0)); ?>;

  const priceDisplay = document.getElementById('price-display');
  const stockDisplay = document.getElementById('stock-display');
  const qtyInput = document.getElementById('qty');
  const variantIdInput = document.getElementById('variant_id');
  const mainImage = document.getElementById('main-image');
  const thumbsWrap = document.getElementById('thumbs');

  // utility
  function isImgTag(el){ return el && el.tagName && el.tagName.toLowerCase()==='img'; }
  function setMainImage(src){
    if (!src) return;
    if (isImgTag(mainImage)) mainImage.src = src;
    else mainImage.innerHTML = '<img src="'+src+'" class="product-main-img rounded">';
  }
  function setThumbs(paths){
    if (!thumbsWrap) return;
    thumbsWrap.innerHTML = '';
    if (!paths || !paths.length) return;
    paths.forEach((p,idx)=>{
      const img = document.createElement('img');
      img.className = 'thumb' + (idx===0 ? ' active' : '');
      img.src = p;
      img.dataset.src = p;
      thumbsWrap.appendChild(img);
    });
  }

  // thumbnail click handler (delegation)
  if (thumbsWrap) {
    thumbsWrap.addEventListener('click', function(e){
      const t = e.target;
      if (!t || !t.classList.contains('thumb')) return;
      const src = t.dataset.src || t.getAttribute('src');
      setMainImage(src);
      thumbsWrap.querySelectorAll('.thumb').forEach(x=>x.classList.remove('active'));
      t.classList.add('active');
    });
  }

  // set gallery to given paths or fallback to productImages
  function setGallery(paths){
    if (!paths || !paths.length) {
      if (productImages && productImages.length) {
        setMainImage(productImages[0]);
        setThumbs(productImages);
      }
      return;
    }
    setMainImage(paths[0]);
    setThumbs(paths);
  }

  // handle variant button clicks
  const vrow = document.getElementById('variants-row');
  if (vrow) {
    vrow.addEventListener('click', function(e){
      const btn = e.target.closest('.variant-btn');
      if (!btn) return;
      // mark active
      vrow.querySelectorAll('.variant-btn').forEach(x=>x.classList.remove('active'));
      btn.classList.add('active');

      // read data
      const vid = btn.getAttribute('data-vid');
      const price = parseFloat(btn.getAttribute('data-price') || basePrice);
      const stock = parseInt(btn.getAttribute('data-stock') || baseStock, 10);
      const images = JSON.parse(btn.getAttribute('data-images') || '[]') || [];
      const imgFromData = btn.getAttribute('data-image') || '';

      // set form hidden variant id
      variantIdInput.value = vid;
      // price/stock
      priceDisplay.textContent = '₹' + parseFloat(price).toFixed(2);
      stockDisplay.textContent = isNaN(stock) ? 0 : stock;
      if (qtyInput) qtyInput.max = isNaN(stock) ? 1 : stock;

      // build resolved image paths: prefer images array, then single image, else product images
      let paths = [];
      if (images && images.length) paths = images;
      else if (imgFromData) paths = [imgFromData];
      else paths = productImages;

      // ensure any relative paths are used as-is
      setGallery(paths);
    });
  }

  // custom size toggle behaviour
  const customToggle = document.getElementById('customSizeToggle');
  const customBlock = document.getElementById('customSizeBlock');
  if (customToggle && customBlock) {
    customToggle.addEventListener('change', function(){
      customBlock.style.display = customToggle.checked ? 'block' : 'none';
    });
  }

  // initial setup
  if (productImages && productImages.length) {
    setGallery(productImages);
  } else {
    // nothing — main image already set server-side
  }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
