<?php
// public/cart.php
require_once __DIR__ . '/includes/db.php';
session_start();

$cart = $_SESSION['cart'] ?? [];

// handle update/remove actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['update'])) {
        foreach ($_POST['qty'] as $key => $q) {
            $q = max(0, (int)$q);
            if ($q === 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                if (isset($_SESSION['cart'][$key])) $_SESSION['cart'][$key]['qty'] = $q;
            }
        }
    }
    // redirect to avoid form resubmission
    header('Location: cart.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:980px;">
  <h3>Your cart</h3>

  <?php if (empty($cart)): ?>
    <div class="card p-4 text-center text-muted">Your cart is empty. <a href="products.php">Continue shopping</a></div>
  <?php else: ?>
    <form method="post">
      <table class="table">
        <thead><tr><th>Product</th><th>Price</th><th style="width:120px">Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
        <?php
          $total = 0;
          foreach ($cart as $key => $it):
            $subtotal = $it['price'] * $it['qty'];
            $total += $subtotal;
        ?>
          <tr>
            <td>
              <?php if (!empty($it['image']) && file_exists(__DIR__ . '/' . $it['image'])): ?>
                <img src="<?php echo htmlspecialchars($it['image']); ?>" style="height:60px;object-fit:cover;margin-right:8px;" alt="">
              <?php endif; ?>
              <?php echo htmlspecialchars($it['name']); ?>
              <?php if ($it['variant_id']): ?><div class="small text-muted">Variant ID: <?php echo (int)$it['variant_id']; ?></div><?php endif; ?>
            </td>
            <td>₹<?php echo number_format($it['price'],2); ?></td>
            <td>
              <input type="number" name="qty[<?php echo htmlspecialchars($key); ?>]" value="<?php echo (int)$it['qty']; ?>" min="0" class="form-control" style="width:90px;">
            </td>
            <td>₹<?php echo number_format($subtotal,2); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="text-end"><strong>Total</strong></td>
            <td><strong>₹<?php echo number_format($total,2); ?></strong></td>
          </tr>
        </tfoot>
      </table>

      <div class="d-flex gap-2">
        <button name="update" class="btn btn-primary">Update cart</button>
        <a href="checkout.php" class="btn btn-success">Proceed to checkout</a>
        <a href="products.php" class="btn btn-outline-secondary">Continue shopping</a>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
