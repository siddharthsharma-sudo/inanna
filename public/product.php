<?php
// public/product.php
// Improved gallery URL resolution (produces root-relative /... URLs when file exists inside web root)
// and removed white background for custom-size block (now transparent).
// Use ?debug_images=1 to see resolved URLs and server-side file existence checks.

require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

// fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="container my-4"><div class="alert alert-warning">Product not found. <a href="products.php">Back to products</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// check product_variants columns safely
try {
    $colCheck = $pdo->query("SHOW COLUMNS FROM product_variants")->fetchAll(PDO::FETCH_COLUMN, 0);
    $haveImageCol = in_array('image', $colCheck);
    $haveImagesCol = in_array('images', $colCheck);
} catch (Exception $e) {
    $haveImageCol = false; $haveImagesCol = false;
}

$variantCols = ['id','variant_sku','size','color','price','stock'];
$selectCols = $variantCols;
if ($haveImageCol) $selectCols[] = 'image';
if ($haveImagesCol) $selectCols[] = 'images';
$sql = "SELECT " . implode(', ', $selectCols) . " FROM product_variants WHERE product_id = :pid ORDER BY size, color, id";
$vstmt = $pdo->prepare($sql);
$vstmt->execute(['pid' => $id]);
$variants = $vstmt->fetchAll();

// fetch gallery rows or fallback to products.gallery
$product_images = [];
try {
    $chk = $pdo->query("SHOW TABLES LIKE 'product_images'")->fetch();
    if ($chk) {
        $gq = $pdo->prepare("SELECT id, path FROM product_images WHERE product_id = :pid ORDER BY id ASC");
        $gq->execute(['pid' => $id]);
        while ($r = $gq->fetch()) $product_images[] = $r['path'];
    } else {
        if (!empty($product['gallery'])) {
            $dec = json_decode($product['gallery'], true);
            if (is_array($dec)) $product_images = $dec;
            else $product_images = array_filter(array_map('trim', explode(',', $product['gallery'])));
        }
    }
} catch (Exception $e) {
    $product_images = [];
}

// -------------------- smart resolver --------------------
/**
 * try_resolve_to_url
 * - If $path is full URL -> return it.
 * - If $path is server filesystem path or relative path and the file exists within DOCUMENT_ROOT,
 *   return a root-relative URL beginning with "/".
 * - If path exists relative to public/ folder (this script's dir), return root-relative URL if possible,
 *   otherwise './path' fallback.
 */
function try_resolve_to_url($path) {
    if (empty($path)) return '';
    $raw = trim($path);
    // Normalize backslashes to slashes
    $norm = str_replace('\\','/',$raw);

    // 1) full URL
    if (preg_match('#^https?://#i', $norm)) return $norm;

    // 2) leading slash (root-relative path) — return as-is
    if (strpos($norm, '/') === 0) return $norm;

    // 3) if path contains '/public/' segment (common when full FS path stored), map to what's after public/
    $lc = strtolower($norm);
    if (strpos($lc, '/public/') !== false) {
        $parts = preg_split('#/public/#i', $norm, 2);
        if (isset($parts[1]) && $parts[1] !== '') {
            // produce root-relative URL
            return '/' . ltrim($parts[1], '/');
        }
    }

    // 4) if the path looks like an absolute filesystem path inside DOCUMENT_ROOT, map to web root
    if (!empty($_SERVER['DOCUMENT_ROOT'])) {
        $docroot = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'],'/'));
        if (stripos($norm, $docroot) === 0) {
            $after = substr($norm, strlen($docroot));
            if ($after === '') return '/';
            return '/' . ltrim($after, '/');
        }
    }

    // 5) check if file exists relative to this script (public/)
    $cand = __DIR__ . '/' . ltrim($norm, '/');
    if (file_exists($cand)) {
        // Try to map to DOCUMENT_ROOT if possible
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docroot = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'],'/')) . '/';
            $fs = str_replace('\\','/',$cand);
            if (strpos($fs, $docroot) === 0) {
                $after = substr($fs, strlen($docroot));
                return '/' . ltrim($after, '/');
            }
        }
        // Otherwise return relative path that should work from public/ script
        return './' . ltrim($norm, '/');
    }

    // 6) try common folders by basename
    $bn = basename($norm);
    $candFolders = [
        __DIR__ . '/uploads/' . $bn => '/uploads/' . $bn,
        __DIR__ . '/images/' . $bn => '/images/' . $bn,
        __DIR__ . '/img/' . $bn => '/img/' . $bn,
        __DIR__ . '/' . $bn => '/' . $bn,
    ];
    foreach ($candFolders as $fs=>$url) {
        if (file_exists($fs)) return $url;
    }

    // 7) strip leading ../ sequences and try again relative
    $p2 = preg_replace('#^\.\./+#','',$norm);
    $cand2 = __DIR__ . '/' . ltrim($p2, '/');
    if (file_exists($cand2)) {
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docroot = str_replace('\\','/', rtrim($_SERVER['DOCUMENT_ROOT'],'/')) . '/';
            $fs = str_replace('\\','/',$cand2);
            if (strpos($fs, $docroot) === 0) {
                $after = substr($fs, strlen($docroot));
                return '/' . ltrim($after, '/');
            }
        }
        return './' . ltrim($p2, '/');
    }

    // final fallback: return './' + path so browser can still try to load it
    return './' . ltrim($norm, '/');
}

// normalize variant images
foreach ($variants as &$v) {
    $v['images_arr'] = [];
    if (isset($v['images']) && !empty($v['images'])) {
        $dec = json_decode($v['images'], true);
        if (is_array($dec)) $v['images_arr'] = $dec;
        else $v['images_arr'] = array_filter(array_map('trim', explode(',', $v['images'])));
    }
    if (!isset($v['image'])) $v['image'] = null;
}
unset($v);

// resolve product images into URLs (root-relative preferred)
$resolved_product_images = [];
if (!empty($product_images)) {
    foreach ($product_images as $pi) {
        $u = try_resolve_to_url($pi);
        if ($u) $resolved_product_images[] = $u;
    }
}
// fallback to product.image
if (empty($resolved_product_images) && !empty($product['image'])) {
    $resolved_product_images[] = try_resolve_to_url($product['image']);
}

// JSON for JS
$variants_json = json_encode($variants, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
$product_images_json = json_encode(array_values($resolved_product_images), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);

include __DIR__ . '/includes/header.php';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root { --header-offset: 90px; }
  body { background: #6b0000 !important; }

  .main-content { padding-top: var(--header-offset); min-height: calc(100vh - var(--header-offset)); color: #fff; }
  .card, .price-box { background: #fff; color: #212529; }

  /* Force requested headings/labels white */
  .product-name, .product-meta, .meta-label, .measurement-label, .custom-size-label, .section-title-plain {
    color: #fff !important;
  }

  .product-card { border-radius:12px; overflow:visible; }
  .product-main-img { width:100%; max-height:520px; object-fit:cover; border-radius:10px; background:#fafafa; display:block; }
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

  /* Remove white bg from custom size block - make it transparent */
  #customSizeBlock { background: transparent !important; border-color: rgba(255,255,255,0.15); }
  #customSizeBlock .form-control { background: rgba(255,255,255,0.95); color:#000; } /* keep inputs readable */

  @media (max-width: 768px) {
    .product-main-img { max-height:360px; }
    .thumb { height:60px; width:60px; }
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
          <?php
            $primary_url = $resolved_product_images[0] ?? null;
            if ($primary_url): ?>
              <img id="main-image" src="<?php echo htmlspecialchars($primary_url); ?>" class="product-main-img rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
              <div id="main-image" class="product-main-img d-flex align-items-center justify-content-center text-muted">
                <div>No image available</div>
              </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($resolved_product_images)): ?>
          <div class="mt-1 d-flex gap-2 flex-wrap" id="thumbs" role="list">
            <?php foreach ($resolved_product_images as $i => $imgUrl): ?>
              <img src="<?php echo htmlspecialchars($imgUrl); ?>" data-src="<?php echo htmlspecialchars($imgUrl); ?>" role="listitem" class="thumb<?php echo $i===0 ? ' active' : ''; ?>" alt="thumb-<?php echo $i; ?>">
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="small text-muted">No gallery images</div>
        <?php endif; ?>

        <?php if (!empty($_GET['debug_images']) && $_GET['debug_images'] == '1') : ?>
          <div class="dbg">
            <strong>DEBUG: resolved image URLs</strong>
            <ul>
              <?php foreach ($resolved_product_images as $u):
                if (preg_match('#^https?://#i', $u)) {
                  $fs = '(external URL)';
                } else {
                  $p = preg_replace('#^\./#','',$u);
                  $cand = __DIR__ . '/' . $p;
                  $fs = file_exists($cand) ? $cand . ' (exists)' : $cand . ' (MISSING)';
                }
              ?>
                <li><code><?php echo htmlspecialchars($u); ?></code> — <?php echo $fs; ?></li>
              <?php endforeach; ?>
            </ul>
            <div><strong>Raw DB paths (product_images / products.gallery / products.image):</strong>
              <pre><?php echo htmlspecialchars(json_encode($product_images ?: ($product['gallery'] ?? $product['image'] ?? []), JSON_PRETTY_PRINT)); ?></pre>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <div class="small-note mt-2 small text-muted">Click thumbnails to change main image. Selecting a variant may switch gallery to variant-specific images if available.</div>
    </div>

    <!-- RIGHT: details, price, add-to-cart -->
    <div class="col-lg-6">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <h2 class="fw-bold mb-1 product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
          <div class="text-muted product-meta">SKU: <span class="fw-semibold" style="color:#fff;"><?php echo htmlspecialchars($product['sku']); ?></span></div>
        </div>
      </div>

      <div class="price-box mb-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <div class="small text-muted">Price</div>
            <div class="price-large" id="price-display">₹<?php echo number_format($product['price'],2); ?></div>
          </div>
          <div class="text-end">
            <div class="small text-muted">Stock</div>
            <div class="stock-badge" id="stock-display"><?php echo (int)$product['stock']; ?></div>
          </div>
        </div>
        <?php if (!empty($product['short_description'])): ?>
          <p class="mt-3 mb-0"><?php echo htmlspecialchars($product['short_description']); ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($variants)): ?>
        <div class="mb-3">
          <label class="form-label meta-label">Choose option</label>
          <select id="variant-select" class="form-select">
            <option value="">-- Select variant --</option>
            <?php foreach ($variants as $v):
              $label = trim(($v['size'] ?: '') . ($v['color'] ? ' / '.$v['color'] : ''));
              if ($label === '') $label = ($v['variant_sku'] ?: 'Variant '.$v['id']);
            ?>
              <option value="<?php echo (int)$v['id']; ?>"
                      data-price="<?php echo ($v['price'] !== null && $v['price'] !== '') ? (float)$v['price'] : (float)$product['price']; ?>"
                      data-stock="<?php echo (int)$v['stock']; ?>"
                      data-sku="<?php echo htmlspecialchars($v['variant_sku']); ?>"
                      data-image="<?php echo htmlspecialchars($v['image'] ?? ''); ?>"
                      data-images='<?php echo json_encode($v['images_arr']); ?>'>
                <?php echo htmlspecialchars($label); ?> <?php if (!empty($v['variant_sku'])): ?> (<?php echo htmlspecialchars($v['variant_sku']); ?>)<?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <form id="addcart" action="add_to_cart.php" method="post" class="row gx-2 gy-2 align-items-end">
        <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
        <input type="hidden" name="variant_id" id="variant_id" value="">
        <input type="hidden" name="redirect" value="cart.php">

        <div class="col-auto">
          <label class="form-label small meta-label">Quantity</label>
          <input type="number" name="qty" id="qty" value="1" min="1" max="<?php echo max(1,(int)$product['stock']); ?>" class="form-control" style="width:120px;">
        </div>

        <div class="col-auto d-flex gap-2">
          <button type="submit" class="btn btn-success btn-lg">Add to cart</button>
          <button type="submit" formaction="place_order.php" class="btn btn-primary btn-lg">Buy Now</button>
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
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_shoulder" name="measurements[Shoulder]" placeholder="Shoulder">
                <label for="m_shoulder">Shoulder</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_bust" name="measurements[Bust]" placeholder="Bust">
                <label for="m_bust">Bust</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_waist" name="measurements[Waist]" placeholder="Waist">
                <label for="m_waist">Waist</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_hip" name="measurements[Hip]" placeholder="Hip">
                <label for="m_hip">Hip</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_length" name="measurements[Length]" placeholder="Length">
                <label for="m_length">Length</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_arm" name="measurements[Arm Round]" placeholder="Arm Round">
                <label for="m_arm">Arm Round</label>
              </div>
              <div class="form-floating measurement-field">
                <input type="text" class="form-control" id="m_thigh" name="measurements[Thigh]" placeholder="Thigh">
                <label for="m_thigh">Thigh</label>
              </div>
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
          <!-- Information column -->
          <div class="card info-card p-3 h-100">
            <div class="card-body">
              <h3 class="section-title-plain mb-2">Information</h3>
              <?php
                echo !empty($product['info_block']) ? $product['info_block'] : '<p class="text-muted">No additional information provided.</p>';
              ?>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <!-- Details column -->
          <div class="card p-3 h-100">
            <div class="card-body">
              <h3 class="section-title">Details</h3>
              <?php if (!empty($product['details'])): ?>
                <p><?php echo nl2br(htmlspecialchars($product['details'])); ?></p>
              <?php else: ?>
                <p class="text-muted">No details available.</p>
              <?php endif; ?>

              <?php if (!empty($product['long_description']) || !empty($product['description'])): ?>
                <hr>
                <h5 class="fw-bold">Description</h5>
                <div>
                  <?php
                    if (!empty($product['long_description'])) echo nl2br(htmlspecialchars($product['long_description']));
                    else echo nl2br(htmlspecialchars($product['description']));
                  ?>
                </div>
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
  const variants = <?php echo $variants_json; ?>;
  const basePrice = <?php echo json_encode((float)$product['price']); ?>;
  const baseStock = <?php echo json_encode((int)$product['stock']); ?>;
  const productImages = <?php echo $product_images_json; ?>; // resolved URLs
  const select = document.getElementById('variant-select');
  const priceDisplay = document.getElementById('price-display');
  const stockDisplay = document.getElementById('stock-display');
  const qtyInput = document.getElementById('qty');
  const variantInput = document.getElementById('variant_id');
  const mainImage = document.getElementById('main-image');
  const thumbsWrap = document.getElementById('thumbs');
  const customToggle = document.getElementById('customSizeToggle');
  const customBlock = document.getElementById('customSizeBlock');

  function isImgTag(el) { return el && el.tagName && el.tagName.toLowerCase() === 'img'; }

  // thumbnail click -> swap main image
  if (thumbsWrap) {
    thumbsWrap.addEventListener('click', function(e){
      const t = e.target;
      if (t && t.classList.contains('thumb')) {
        const src = t.dataset.src || t.getAttribute('src');
        if (src) {
          if (isImgTag(mainImage)) mainImage.src = src;
          else mainImage.innerHTML = '<img src="'+src+'" class="product-main-img rounded">';
        }
        const thumbs = thumbsWrap.querySelectorAll('.thumb');
        thumbs.forEach(x => x.classList.remove('active'));
        t.classList.add('active');
      }
    });
  }

  function setGalleryTo(paths) {
    if (!paths || !paths.length) {
      const fallback = '<?php echo addslashes(try_resolve_to_url($product['image'] ?? '')); ?>';
      if (fallback) {
        if (isImgTag(mainImage)) mainImage.src = fallback;
        else mainImage.innerHTML = '<img src="'+fallback+'" class="product-main-img rounded">';
      } else {
        if (isImgTag(mainImage)) {
          mainImage.src = '';
          mainImage.outerHTML = '<div id="main-image" class="product-main-img d-flex align-items-center justify-content-center text-muted">No image available</div>';
        } else {
          mainImage.textContent = 'No image available';
        }
      }
      if (thumbsWrap) thumbsWrap.innerHTML = '';
      return;
    }

    const first = paths[0];
    if (first) {
      if (isImgTag(mainImage)) mainImage.src = first;
      else mainImage.innerHTML = '<img src="'+first+'" class="product-main-img rounded">';
    }
    if (thumbsWrap) {
      thumbsWrap.innerHTML = '';
      paths.forEach(function(p, idx){
        const img = document.createElement('img');
        img.className = 'thumb' + (idx===0 ? ' active' : '');
        img.src = p;
        img.dataset.src = p;
        thumbsWrap.appendChild(img);
      });
    }
  }

  if (select) {
    select.addEventListener('change', function(){
      const val = select.value;
      if (!val) {
        priceDisplay.textContent = '₹' + parseFloat(basePrice).toFixed(2);
        stockDisplay.textContent = parseInt(baseStock);
        variantInput.value = '';
        if (qtyInput) qtyInput.max = parseInt(baseStock);
        setGalleryTo(productImages);
        return;
      }
      const v = variants.find(x => String(x.id) === String(val));
      if (!v) return;
      const p = (v.price === null || v.price === '') ? parseFloat(basePrice) : parseFloat(v.price);
      priceDisplay.textContent = '₹' + parseFloat(p).toFixed(2);
      stockDisplay.textContent = parseInt(v.stock);
      variantInput.value = v.id;
      if (qtyInput) qtyInput.max = parseInt(v.stock);

      // build variant image paths: prefer images_arr, then image, else fallback to productImages
      let paths = [];
      if (Array.isArray(v.images_arr) && v.images_arr.length) {
        paths = v.images_arr.map(function(x){
          if (!x) return '';
          if (x.indexOf('http')===0 || x.indexOf('/')===0) return x;
          return './' + x.replace(/^\/+/,'');
        }).filter(Boolean);
      } else if (v.image && v.image !== null && v.image !== '') {
        paths = [ (v.image.indexOf('http')===0 || v.image.indexOf('/')===0) ? v.image : ('./' + v.image.replace(/^\/+/,'')) ];
      } else {
        paths = productImages;
      }
      setGalleryTo(paths);
    });
  }

  if (customToggle && customBlock) {
    customToggle.addEventListener('change', function(){
      customBlock.style.display = customToggle.checked ? 'block' : 'none';
    });
  }

  // initialize gallery
  setGalleryTo(productImages);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
