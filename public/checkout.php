<?php
// public/checkout.php
require_once __DIR__ . '/includes/db.php';
session_start();

if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// require login to checkout
if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

$uid = (int)$_SESSION['user_id'];

// fetch user's addresses for selection
$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
$stmt->execute(['uid' => $uid]);
$addresses = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // expected: address_id or new address fields, payment_method (cod/online)
    $address_id = (int)($_POST['address_id'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'cod';

    if ($address_id <= 0) {
        $_SESSION['checkout_error'] = 'Please select an address or add one in your Address Book.';
        header('Location: checkout.php');
        exit;
    }

    // save chosen address and payment in session and go to place_order
    $_SESSION['order'] = [
        'address_id' => $address_id,
        'payment_method' => $payment_method
    ];
    header('Location: place_order.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:900px;">
  <h3>Checkout</h3>

  <?php if (!empty($_SESSION['checkout_error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['checkout_error']); unset($_SESSION['checkout_error']); ?></div>
  <?php endif; ?>

  <div class="card p-3 mb-3">
    <h5>Shipping address</h5>
    <?php if (empty($addresses)): ?>
      <div class="p-3 text-muted">You have no saved addresses. <a href="address_edit.php">Add an address</a>.</div>
    <?php else: ?>
      <form method="post">
        <?php foreach ($addresses as $a): ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="address_id" id="addr_<?php echo $a['id']; ?>" value="<?php echo $a['id']; ?>" <?php echo $a['is_default'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="addr_<?php echo $a['id']; ?>">
              <strong><?php echo htmlspecialchars($a['label'] ?: 'Address'); ?></strong>
              <div class="small text-muted"><?php echo nl2br(htmlspecialchars($a['address_line1'] . ($a['address_line2'] ? ', '.$a['address_line2'] : '') . ', '.$a['city'].', '.$a['state'].' - '.$a['postal_code'])); ?></div>
            </label>
          </div>
        <?php endforeach; ?>

        <hr>
        <h5>Payment method</h5>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" id="pm_cod" value="cod" checked>
          <label class="form-check-label" for="pm_cod">Cash on Delivery</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" id="pm_online" value="online">
          <label class="form-check-label" for="pm_online">Pay Online (will integrate later)</label>
        </div>

        <div class="mt-3">
          <button class="btn btn-success">Place order</button>
          <a class="btn btn-outline-secondary" href="cart.php">Back to cart</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
