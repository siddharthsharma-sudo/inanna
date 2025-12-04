<?php
// address_edit.php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$address = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
    $stmt->execute(['id'=>$id,'uid'=>$uid]);
    $address = $stmt->fetch();
}
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['addr_csrf'])) $_SESSION['addr_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['addr_csrf'];

include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:720px;">
  <h3><?php echo $address ? 'Edit' : 'Add'; ?> Address</h3>
  <div class="card p-4">
    <form method="post" action="address_save.php">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <input type="hidden" name="id" value="<?php echo $address ? (int)$address['id'] : 0; ?>">
      <div class="mb-3">
        <label class="form-label">Label (Home, Office)</label>
        <input name="label" class="form-control" value="<?php echo $address ? htmlspecialchars($address['label']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input name="full_name" class="form-control" required value="<?php echo $address ? htmlspecialchars($address['full_name']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input name="phone" class="form-control" value="<?php echo $address ? htmlspecialchars($address['phone']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Address line 1</label>
        <input name="address_line1" class="form-control" required value="<?php echo $address ? htmlspecialchars($address['address_line1']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Address line 2</label>
        <input name="address_line2" class="form-control" value="<?php echo $address ? htmlspecialchars($address['address_line2']) : ''; ?>">
      </div>
      <div class="row g-2">
        <div class="col-md-4 mb-3">
          <label class="form-label">City</label>
          <input name="city" class="form-control" required value="<?php echo $address ? htmlspecialchars($address['city']) : ''; ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">State</label>
          <input name="state" class="form-control" value="<?php echo $address ? htmlspecialchars($address['state']) : ''; ?>">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Postal code</label>
          <input name="postal_code" class="form-control" value="<?php echo $address ? htmlspecialchars($address['postal_code']) : ''; ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Country</label>
        <input name="country" class="form-control" value="<?php echo $address ? htmlspecialchars($address['country']) : 'India'; ?>">
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_default" id="is_default" value="1" <?php echo ($address && $address['is_default']) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="is_default">Set as default address</label>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Save address</button>
        <a href="address_book.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
