<?php
// admin/products.php
// Manage products list (Admin area)

require_once __DIR__ . '/../includes/db.php'; // your working path
require_once __DIR__ . '/auth.php';
require_admin();

// start session and prepare CSRF token used by delete form
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['crud_csrf'])) $_SESSION['crud_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['crud_csrf'];

// optional flash messages via ?msg=
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// fetch products
$stmt = $pdo->query("SELECT id, sku, name, price, stock, image, created_at FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

// fetch variant counts in one query (if product_variants table exists)
$variantCounts = [];
try {
    $vc = $pdo->query("SELECT product_id, COUNT(*) AS cnt FROM product_variants GROUP BY product_id")->fetchAll();
    foreach ($vc as $r) {
        $variantCounts[(int)$r['product_id']] = (int)$r['cnt'];
    }
} catch (Exception $e) {
    // ignore if table not present
    $variantCounts = [];
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Products - Admin</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .thumb { height:50px; width:50px; object-fit:cover; border-radius:4px; }
  </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Inanna Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Products</h3>
    <div>
      <a href="product_edit.php" class="btn btn-success">Add Product</a>
      <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
    </div>
  </div>

  <?php if ($msg === 'created'): ?>
    <div class="alert alert-success">Product created successfully.</div>
  <?php elseif ($msg === 'updated'): ?>
    <div class="alert alert-success">Product updated successfully.</div>
  <?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success">Product deleted.</div>
  <?php elseif ($err === 'invalid'): ?>
    <div class="alert alert-danger">Invalid request.</div>
  <?php elseif ($err === 'uploadfail'): ?>
    <div class="alert alert-danger">Image upload failed.</div>
  <?php elseif ($err === 'db'): ?>
    <div class="alert alert-danger">A server error occurred while deleting. Check logs.</div>
  <?php endif; ?>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-striped mb-0 align-middle">
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th style="width:80px">Image</th>
            <th>Name</th>
            <th style="width:120px">SKU</th>
            <th style="width:110px">Price</th>
            <th style="width:90px">Stock</th>
            <th style="width:100px">Variants</th>
            <th style="width:160px">Created</th>
            <th style="width:190px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><?php echo (int)$p['id']; ?></td>
                <td>
                  <?php if (!empty($p['image']) && file_exists(__DIR__ . '/../' . $p['image'])): ?>
                    <img src="<?php echo '../' . htmlspecialchars($p['image']); ?>" class="thumb" alt="">
                  <?php else: ?>
                    <div style="height:50px;width:50px;background:#f0f0f0;border-radius:4px;"></div>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['sku']); ?></td>
                <td>₹<?php echo number_format($p['price'],2); ?></td>
                <td><?php echo (int)$p['stock']; ?></td>
                <td><?php echo isset($variantCounts[(int)$p['id']]) ? (int)$variantCounts[(int)$p['id']] : 0; ?></td>
                <td><?php echo htmlspecialchars($p['created_at']); ?></td>
                <td>
                  <a href="product_edit.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">Edit</a>

                  <form method="post" action="product_delete.php" style="display:inline" onsubmit="return confirm('Delete this product?');">

                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>

                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center p-4">No products found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
