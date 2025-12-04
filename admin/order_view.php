<?php
// admin/order_view.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: orders.php'); exit; }

// fetch order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }

// fetch items
$itst = $pdo->prepare("SELECT oi.*, p.name AS product_name, pv.size, pv.color, pv.variant_sku FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id LEFT JOIN product_variants pv ON pv.id = oi.variant_id WHERE oi.order_id = :oid");
$itst->execute(['oid' => $id]);
$items = $itst->fetchAll();

include __DIR__ . '/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Order #<?php echo (int)$order['id']; ?></h3>
    <div>
      <a href="orders.php" class="btn btn-outline-secondary">Back</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-8">
      <div class="card p-3 mb-3">
        <h5>Items</h5>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th></tr></thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?php echo htmlspecialchars($it['product_name']); ?></td>
                  <td><?php echo htmlspecialchars(trim(($it['size'] ?? '') . ($it['color'] ? ' / '.$it['color'] : '') . ($it['variant_sku'] ? ' ('.$it['variant_sku'].')' : ''))); ?></td>
                  <td><?php echo (int)$it['qty']; ?></td>
                  <td>₹<?php echo number_format($it['price'],2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card p-3">
        <h5>Customer</h5>
        <p><strong><?php echo htmlspecialchars($order['customer_name']); ?></strong></p>
        <p><?php echo htmlspecialchars($order['customer_email']); ?> — <?php echo htmlspecialchars($order['customer_phone']); ?></p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card p-3 mb-3">
        <h5>Shipping address</h5>
        <div><?php echo nl2br(htmlspecialchars($order['shipping_address_line1'] . ($order['shipping_address_line2'] ? "\n".$order['shipping_address_line2'] : '') . "\n".$order['shipping_city'].', '.$order['shipping_state'].' - '.$order['shipping_postal_code'])); ?></div>
        <div class="small text-muted mt-2">Phone: <?php echo htmlspecialchars($order['shipping_phone']); ?></div>
      </div>

      <div class="card p-3 mb-3">
        <h5>Order summary</h5>
        <div><strong>Total:</strong> ₹<?php echo number_format($order['total'],2); ?></div>
        <div><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></div>
      </div>

      <div class="card p-3">
        <h5>Update status</h5>
        <form method="post" action="order_update.php">
          <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
          <div class="mb-2">
            <select name="status" class="form-select">
              <option value="pending" <?php if($order['status']==='pending') echo 'selected'; ?>>Pending</option>
              <option value="paid" <?php if($order['status']==='paid') echo 'selected'; ?>>Paid</option>
              <option value="shipped" <?php if($order['status']==='shipped') echo 'selected'; ?>>Shipped</option>
              <option value="delivered" <?php if($order['status']==='delivered') echo 'selected'; ?>>Delivered</option>
              <option value="cancelled" <?php if($order['status']==='cancelled') echo 'selected'; ?>>Cancelled</option>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label small">Admin note (optional)</label>
            <textarea name="admin_note" class="form-control" rows="3"></textarea>
          </div>
          <div class="d-grid">
            <button class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
