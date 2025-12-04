<?php
// products.php - robust PHP-side filtering version (drop-in replacement)
// Uses your original design, but builds categories from product rows and filters in PHP
require_once __DIR__ . '/includes/db.php';

// read selected filters
$selectedCategory = isset($_GET['category']) && $_GET['category'] !== '' ? trim($_GET['category']) : null;
$selectedGender   = isset($_GET['gender']) && $_GET['gender'] !== '' ? trim($_GET['gender']) : null;

// -------- Fetch all products (we will filter in PHP) --------
try {
    $stmt = $pdo->query("SELECT id, sku, name, price, stock, image, category_slug, categories, gender FROM products ORDER BY created_at DESC");
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allProducts = [];
}

// -------- Build canonical categories list from product rows --------
// supports: category_slug values, JSON arrays in `categories`, comma-separated `categories`, plain names
$catMap = []; // value => label
foreach ($allProducts as $row) {
    // 1) category_slug
    if (!empty($row['category_slug'])) {
        $key = trim((string)$row['category_slug']);
        if ($key !== '') {
            $label = (strpos($key, '-') !== false || strpos($key, '_') !== false) ? ucwords(str_replace(['-','_'], ' ', $key)) : ucwords(strtolower($key));
            $catMap[$key] = $label;
        }
    }

    // 2) categories column (could be JSON array or comma-separated)
    if (!empty($row['categories'])) {
        $c = trim((string)$row['categories']);
        if ($c !== '') {
            // JSON array?
            if (strpos($c, '[') === 0) {
                $decoded = json_decode($c, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $it) {
                        $it = trim((string)$it);
                        if ($it === '') continue;
                        $label = ucwords(strtolower($it));
                        $catMap[$it] = $label;
                    }
                    continue;
                }
            }
            // comma-separated
            $parts = preg_split('/\s*,\s*/', $c);
            foreach ($parts as $p) {
                $p = trim((string)$p);
                if ($p === '') continue;
                $catMap[$p] = ucwords(strtolower($p));
            }
        }
    }
}

// If catMap still empty, fall back to your provided list (order preserved)
if (empty($catMap)) {
    $fallback = ['Co-ord Set','Dresses','Shirts','Pants','Suits','Sarees','Men','Women'];
    foreach ($fallback as $f) $catMap[$f] = $f;
}

// Build genders list from fetched products (or fallback)
$genders = [];
foreach ($allProducts as $r) {
    if (!empty($r['gender'])) {
        $g = trim((string)$r['gender']);
        if ($g !== '') $genders[$g] = $g;
    }
}
if (empty($genders)) $genders = ['Men','Women','Unisex'];
else $genders = array_values($genders);

// -------- Filter products in PHP (client-visible results) --------
$products = $allProducts;

if ($selectedGender) {
    $filtered = [];
    foreach ($products as $r) {
        if (isset($r['gender']) && strcasecmp(trim((string)$r['gender']), $selectedGender) === 0) $filtered[] = $r;
    }
    $products = $filtered;
}

if ($selectedCategory) {
    $filtered = [];
    $sel = trim((string)$selectedCategory);

    // prepare lower-case variants for comparisons
    $sel_lower = mb_strtolower($sel);

    foreach ($products as $r) {
        $matched = false;

        // 1) compare against category_slug
        if (!empty($r['category_slug'])) {
            if (strcasecmp(trim((string)$r['category_slug']), $sel) === 0) { $matched = true; }
        }

        // 2) check categories column (JSON array or comma list)
        if (!$matched && !empty($r['categories'])) {
            $c = trim((string)$r['categories']);
            if ($c !== '') {
                if (strpos($c, '[') === 0) {
                    $decoded = json_decode($c, true);
                    if (is_array($decoded)) {
                        foreach ($decoded as $it) {
                            if (strcasecmp(trim((string)$it), $sel) === 0) { $matched = true; break; }
                        }
                    }
                } else {
                    $parts = preg_split('/\s*,\s*/', $c);
                    foreach ($parts as $p) {
                        if (strcasecmp(trim((string)$p), $sel) === 0) { $matched = true; break; }
                    }
                }
            }
        }

        // 3) compare against category labels we built (user might have clicked label instead of raw value)
        if (!$matched) {
            foreach ($catMap as $val => $label) {
                if (strcasecmp($label, $sel) === 0 || strcasecmp($val, $sel) === 0) {
                    // check if product contains the val
                    if (!empty($r['category_slug']) && strcasecmp(trim((string)$r['category_slug']), $val) === 0) { $matched = true; break; }

                    if (!empty($r['categories'])) {
                        $c = trim((string)$r['categories']);
                        if ($c !== '') {
                            if (strpos($c, '[') === 0) {
                                $decoded = json_decode($c, true);
                                if (is_array($decoded)) {
                                    foreach ($decoded as $it) {
                                        if (strcasecmp(trim((string)$it), $val) === 0) { $matched = true; break; }
                                    }
                                    if ($matched) break;
                                }
                            } else {
                                $parts = preg_split('/\s*,\s*/', $c);
                                foreach ($parts as $p) {
                                    if (strcasecmp(trim((string)$p), $val) === 0) { $matched = true; break; }
                                }
                                if ($matched) break;
                            }
                        }
                    }
                }
            }
        }

        if ($matched) $filtered[] = $r;
    }

    // If we found matched items, use them. Otherwise leave products empty to show "No products found."
    $products = $filtered;
}

// Now we have $products filtered reliably. Include header and render (design unchanged).
include __DIR__ . '/includes/header.php';
?>

<style>
  /* Page background */
  body {
    background-color: #8B0000; /* dark red */
  }

  .products-wrapper { padding-bottom: 60px; }

  .product-card { border: none; border-radius: .5rem; overflow: hidden; }

  .product-img { width: 100%; height: 460px; object-fit: cover; display: block; background: #f5f5f5; }

  .product-overlay { position: absolute; left: 0; right: 0; bottom: 0; padding: .75rem; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(transparent, rgba(0,0,0,0.45)); color: #fff; }

  .product-tile { position: relative; }

  .product-card-body { background: transparent; padding: .5rem .75rem; }
  .filter-bar { gap: .5rem; flex-wrap: wrap; }

  .filter-btn.active { background: #ffffff; color: #8B0000; font-weight: 600; }

  .product-card:hover { transform: translateY(-4px); transition: transform .15s ease; }

  .page-title, .back-home { color: #fff; }

  .gender-btn { border-radius: 18px; padding: 6px 10px; border: 1px solid rgba(255,255,255,0.3); color: #fff; background: transparent; }
  .gender-btn.active { background: #fff; color: #8B0000; font-weight:600; }

  @media (max-width: 576px) { .product-img { height: 400px; object-fit:cover; } }
</style>

<div class="container products-wrapper" id="productsWrapper">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0 page-title">Products</h2>
    <div>
      <a href="index.php" class="btn btn-outline-light back-home">Home</a>
    </div>
  </div>

  <div class="mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex filter-bar align-items-center">
        <!-- category filter buttons -->
        <button class="btn btn-sm btn-outline-secondary filter-btn <?php echo $selectedCategory === null ? 'active' : ''; ?>" data-cat="">All</button>
        <?php foreach ($catMap as $val => $label):
            $isActive = ($selectedCategory !== null && (strcasecmp($selectedCategory, $val) === 0 || strcasecmp($selectedCategory, $label) === 0)) ? 'active' : '';
        ?>
          <button class="btn btn-sm btn-outline-secondary filter-btn text-white <?php echo $isActive; ?>" data-cat="<?php echo htmlspecialchars($val); ?>"><?php echo htmlspecialchars($label); ?></button>
        <?php endforeach; ?>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="me-2" style="color:#fff;font-weight:600;margin-right:8px;"></div>
        <div>
          <button class="gender-btn <?php echo $selectedGender === null ? 'active' : ''; ?>" data-gender="">All</button>
          <?php foreach ($genders as $g): $gActive = ($selectedGender !== null && strcasecmp($selectedGender, $g) === 0) ? 'active' : ''; ?>
            <button class="gender-btn <?php echo $gActive; ?>" data-gender="<?php echo htmlspecialchars($g); ?>"><?php echo htmlspecialchars($g); ?></button>
          <?php endforeach; ?>
        </div>
        <div class="ms-3">
          <span class="small text-white">Showing <strong><?php echo count($products); ?></strong> products</span>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <div class="text-center py-5 text-muted">No products found.</div>
        </div>
      <?php endif; ?>

      <?php foreach ($products as $p): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card product-card h-100 shadow-sm">
            <div class="product-tile">
              <?php
                $imgPath = '';
                if (!empty($p['image'])) {
                    $possiblePath = __DIR__ . '/' . $p['image'];
                    if (file_exists($possiblePath)) $imgPath = $p['image'];
                    else $imgPath = $p['image'];
                }
              ?>
              <?php if ($imgPath !== ''): ?>
                <img src="<?php echo htmlspecialchars($imgPath); ?>" class="product-img" loading="lazy" alt="<?php echo htmlspecialchars($p['name']); ?>">
              <?php else: ?>
                <div class="product-img d-flex align-items-center justify-content-center" aria-hidden="true">
                  <span class="text-muted">No Image</span>
                </div>
              <?php endif; ?>

              <div class="product-overlay">
                <div>
                  <strong>₹<?php echo number_format($p['price'], 2); ?></strong>
                  <div class="small">Stock: <?php echo (int)$p['stock']; ?></div>
                </div>

                <div>
                  <a href="product.php?id=<?php echo (int)$p['id']; ?>" class="btn btn-sm btn-light">View</a>
                </div>
              </div>
            </div>

            <div class="product-card-body">
              <h3><?php echo htmlspecialchars($p['name']); ?></h3>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  (function() {
    var wrapper = document.getElementById('productsWrapper');
    if (!wrapper) return;
    var header = document.querySelector('header, .site-header, .navbar, .topbar');
    if (header) {
      var rect = header.getBoundingClientRect();
      var headerHeight = rect.height || 70;
      wrapper.style.paddingTop = (headerHeight + 18) + 'px';
    } else {
      wrapper.style.paddingTop = '18px';
    }

    var catButtons = document.querySelectorAll('.filter-btn');
    var genderButtons = document.querySelectorAll('.gender-btn');

    function navigateWith(cat, gender) {
      var url = new URL(window.location.href);
      if (!cat) url.searchParams.delete('category'); else url.searchParams.set('category', cat);
      if (!gender) url.searchParams.delete('gender'); else url.searchParams.set('gender', gender);
      window.location.href = url.toString();
    }

    catButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var cat = btn.getAttribute('data-cat') || '';
        var activeGender = document.querySelector('.gender-btn.active');
        var currentGender = activeGender ? activeGender.getAttribute('data-gender') || '' : '';
        navigateWith(cat, currentGender);
      });
    });

    genderButtons.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var gender = btn.getAttribute('data-gender') || '';
        var activeCat = document.querySelector('.filter-btn.active');
        var currentCat = activeCat ? activeCat.getAttribute('data-cat') || '' : '';
        navigateWith(currentCat, gender);
      });
    });
  })();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
