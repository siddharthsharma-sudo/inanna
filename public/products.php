<?php
// public/products.php - fetches from DB via PDO

require_once __DIR__ . '/../public/includes/db.php';
$stmt = $pdo->query("SELECT id, sku, name, price, stock, image, LEFT(description,200) AS short FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();


include __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Products</h2>
    <a href="index.php" class="btn btn-outline-secondary">Home</a>
  </div>

  <div class="row g-4">
    <?php foreach ($products as $p): ?>
      <div class="col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100 shadow-sm">
          <?php if (!empty($p['image']) && file_exists(__DIR__ . '/' . $p['image'])): ?>
            <img src="<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" style="height:180px; object-fit:cover;" loading="lazy" alt="<?php echo htmlspecialchars($p['name']); ?>">
          <?php else: ?>
            <div style="height:180px;display:flex;align-items:center;justify-content:center;background:#f5f5f5;color:#999;">
              No Image
            </div>
          <?php endif; ?>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
            <p class="card-text text-muted mb-2" style="font-size:.95rem;"><?php echo htmlspecialchars($p['short']); ?></p>

            <div class="mt-auto d-flex justify-content-between align-items-center">
              <div>
                <strong>₹<?php echo number_format($p['price'], 2); ?></strong>
                <div class="small text-muted">Stock: <?php echo (int)$p['stock']; ?></div>
              </div>
              <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">View</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
