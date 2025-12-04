<?php
// admin/dashboard.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin(); // only admin allowed

$admin = current_admin_info($pdo);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard - Inanna</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Inanna Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Welcome, <?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></h3>
  </div>

  <!-- STAT CARDS -->
  <div class="row g-3 mb-4">

    <?php
      $pcount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
      $ucount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
      $ocount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
      $pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
    ?>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3">
        <h6 class="text-muted">Total Products</h6>
        <h3><?php echo $pcount; ?></h3>
        <a href="products.php" class="text-primary small">Manage Products →</a>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3">
        <h6 class="text-muted">Total Users</h6>
        <h3><?php echo $ucount; ?></h3>
        <a href="users.php" class="text-primary small">View Users →</a>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3">
        <h6 class="text-muted">Total Orders</h6>
        <h3><?php echo $ocount; ?></h3>
        <a href="orders.php" class="text-primary small">View Orders →</a>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 p-3 bg-warning bg-opacity-10">
        <h6 class="text-muted">Pending Orders</h6>
        <h3><?php echo $pending; ?></h3>
        <a href="orders.php?status=pending" class="text-warning small">Review Pending →</a>
      </div>
    </div>

  </div>

  <div class="row g-3">

    <!-- RECENT ORDERS -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0 p-3 h-100">
        <h5>Recent Orders</h5>

        <?php
          $recentOrders = $pdo->query("
            SELECT id, customer_name, total, status, created_at 
            FROM orders ORDER BY created_at DESC LIMIT 5
          ")->fetchAll();
        ?>

        <ul class="list-group list-group-flush">
          <?php if ($recentOrders): ?>
            <?php foreach ($recentOrders as $o): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong>#<?php echo $o['id']; ?></strong> - 
                  <?php echo htmlspecialchars($o['customer_name']); ?>
                  <div class="small text-muted"><?php echo $o['created_at']; ?></div>
                </div>
                <div class="text-end">
                  ₹<?php echo number_format($o['total'],2); ?><br>
                  <small class="text-muted"><?php echo htmlspecialchars($o['status']); ?></small>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-center text-muted">No recent orders</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

    <!-- RECENT USERS -->
    <div class="col-md-6">
      <div class="card shadow-sm border-0 p-3 h-100">
        <h5>Recent Users</h5>

        <?php
          $recentUsers = $pdo->query("
            SELECT id, name, email, created_at 
            FROM users ORDER BY created_at DESC LIMIT 5
          ")->fetchAll();
        ?>

        <ul class="list-group list-group-flush">
          <?php if ($recentUsers): ?>
            <?php foreach ($recentUsers as $u): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                  <br>
                  <small><?php echo htmlspecialchars($u['email']); ?></small>
                </div>
                <div class="small text-muted"><?php echo $u['created_at']; ?></div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="list-group-item text-center text-muted">No recent users</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>

  </div>

</div>

</body>
</html>
