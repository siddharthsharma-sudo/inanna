<?php
// public/includes/products_grid.php
// Dynamic homepage product grid — uses your existing includes/db.php (which creates $pdo).
// Place this file in: public/includes/products_grid.php

// Require the DB connection (adjust path if your includes folder is elsewhere)
$dbFile = __DIR__ . '/db.php';
if (! file_exists($dbFile)) {
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Products unavailable (DB config missing: includes/db.php).</div></div></section>';
    return;
}
require_once $dbFile; // this should create $pdo

// If $pdo not set for some reason, bail
if (! isset($pdo) || ! ($pdo instanceof PDO)) {
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Products unavailable (DB connection not initialized).</div></div></section>';
    return;
}

// Query: show up to 8 most recently updated products that are in stock
try {
    $sql = "
      SELECT id, name, price, image
      FROM products
      WHERE stock > 0
      ORDER BY updated_at DESC, id DESC
      LIMIT 8
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Products unavailable (DB query error).</div></div></section>';
    // For debugging during development you can uncomment the next line:
    // echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    return;
}
?>

<section id="products" class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">Clothing — Featured</h3>
      <small class="text-muted">Handpicked for you</small>
    </div>

    <div class="row g-3">
      <?php if (empty($products)): ?>
        <div class="col-12">
          <div class="p-4 text-center text-muted rounded-3 border">No products found. Add items in admin panel or check stock.</div>
        </div>
      <?php else: ?>
        <?php foreach ($products as $p):
          $prodId    = (int)$p['id'];
          $prodName  = htmlspecialchars($p['name'], ENT_QUOTES | ENT_SUBSTITUTE);
          $prodPrice = is_numeric($p['price']) ? (float)$p['price'] : 0.0;
          // If image column stores filename (e.g. 'uploads/foo.jpg'), you may need to prepend path
          $prodImg   = !empty($p['image']) ? $p['image'] : 'https://via.placeholder.com/600x600?text=No+Image';
        ?>
          <div class="col-6 col-md-3">
            <div class="card product-card h-100 shadow-sm">
              <a href="product.php?id=<?php echo $prodId; ?>" class="stretched-link" aria-label="View <?php echo $prodName; ?> details">
                <div class="ratio ratio-1x1 overflow-hidden">
                  <img src="<?php echo htmlspecialchars($prodImg, ENT_QUOTES); ?>" class="card-img-top object-cover" alt="<?php echo $prodName; ?>" loading="lazy">
                </div>
              </a>
              <div class="card-body d-flex flex-column">
                <h6 class="card-title mb-1 text-truncate" title="<?php echo $prodName; ?>"><?php echo $prodName; ?></h6>
                <div class="mb-3">
                  <span class="h6 mb-0">₹<?php echo number_format($prodPrice); ?></span>
                  <span class="text-muted small ms-2 text-decoration-line-through">₹<?php echo number_format($prodPrice + 400); ?></span>
                </div>
                <div class="mt-auto">
                  <a href="product.php?id=<?php echo $prodId; ?>" class="btn btn-outline-primary btn-sm w-100" role="button" aria-label="More details for <?php echo $prodName; ?>">
                    More details
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="text-center mt-4">
      <a href="products.php" class="btn btn-lg btn-primary px-5">View more</a>
    </div>
  </div>

  <style>
    .product-card { transition: transform .24s ease, box-shadow .24s ease; border-radius: .6rem; overflow: hidden; border: 0; }
    .product-card:hover { transform: translateY(-8px); box-shadow: 0 18px 40px rgba(13, 38, 59, 0.12); }
    .product-card .object-cover { width:100%; height:100%; object-fit:cover; display:block; transition: transform .4s ease; }
    .product-card:hover .object-cover { transform: scale(1.06); }
    .card-body { padding: .75rem !important; }
    @media (max-width: 575.98px) {
      .product-card .btn { padding: .5rem .6rem; font-size: .95rem; }
    }
  </style>
</section>
