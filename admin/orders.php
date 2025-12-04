<?php
// admin/orders.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

// filters (allow order id, customer email, status, date range)
$order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$search_email = isset($_GET['email']) ? trim($_GET['email']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$date_from = isset($_GET['from']) ? trim($_GET['from']) : '';
$date_to = isset($_GET['to']) ? trim($_GET['to']) : '';

// base sql
$sql = "SELECT id, user_id, customer_name, customer_email, total, status, payment_method, created_at
        FROM orders
        WHERE 1=1";
$params = [];

// apply filters
if ($order_id !== '') {
    // allow numeric id or "#123"
    $clean = preg_replace('/[^0-9]/','',$order_id);
    if ($clean !== '') {
        $sql .= " AND id = :oid";
        $params['oid'] = (int)$clean;
    }
}
if ($search_email !== '') {
    $sql .= " AND customer_email LIKE :email";
    $params['email'] = '%' . $search_email . '%';
}
if ($status !== '') {
    $sql .= " AND status = :status";
    $params['status'] = $status;
}
if ($date_from !== '') {
    $sql .= " AND created_at >= :from";
    $params['from'] = $date_from . " 00:00:00";
}
if ($date_to !== '') {
    $sql .= " AND created_at <= :to";
    $params['to'] = $date_to . " 23:59:59";
}

$sql .= " ORDER BY created_at DESC LIMIT 500";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Orders</h3>
    <a href="dashboard.php" class="btn btn-outline-secondary">Back</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-auto" style="min-width:160px;">
      <input name="order_id" class="form-control" placeholder="Order # or ID" value="<?php echo htmlspecialchars($order_id); ?>">
    </div>
    <div class="col-auto" style="min-width:220px;">
      <input name="email" class="form-control" placeholder="Customer email" value="<?php echo htmlspecialchars($search_email); ?>">
    </div>
    <div class="col-auto">
      <select name="status" class="form-select">
        <option value="">All statuses</option>
        <option value="pending" <?php if($status==='pending') echo 'selected'; ?>>Pending</option>
        <option value="paid" <?php if($status==='paid') echo 'selected'; ?>>Paid</option>
        <option value="shipped" <?php if($status==='shipped') echo 'selected'; ?>>Shipped</option>
        <option value="delivered" <?php if($status==='delivered') echo 'selected'; ?>>Delivered</option>
        <option value="cancelled" <?php if($status==='cancelled') echo 'selected'; ?>>Cancelled</option>
      </select>
    </div>
    <div class="col-auto">
      <input type="date" name="from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From">
    </div>
    <div class="col-auto">
      <input type="date" name="to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filter</button>
      <a href="orders.php" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>

  <div class="card p-3">
    <div class="table-responsive">
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th>#</th><th>Customer</th><th>Email</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>#<?php echo (int)$o['id']; ?></td>
              <td><?php echo htmlspecialchars($o['customer_name']); ?> <div class="text-muted small">UID:<?php echo (int)$o['user_id']; ?></div></td>
              <td><?php echo htmlspecialchars($o['customer_email']); ?></td>
              <td>₹<?php echo number_format($o['total'],2); ?></td>
              <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
              <td><?php echo htmlspecialchars($o['status']); ?></td>
              <td><?php echo htmlspecialchars($o['created_at']); ?></td>
              <td>
                <a class="btn btn-sm btn-outline-primary" href="order_view.php?id=<?php echo (int)$o['id']; ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($orders)): ?>
            <tr><td colspan="8" class="text-center p-4">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
