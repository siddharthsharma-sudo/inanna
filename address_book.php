<?php
// address_book.php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];
// handle simple flash from save/delete
$msg = $_GET['msg'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, id DESC");
$stmt->execute(['uid'=>$uid]);
$addresses = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>My Addresses</h3>
    <a href="address_edit.php" class="btn btn-success">Add Address</a>
  </div>

  <?php if ($msg === 'saved'): ?>
    <div class="alert alert-success">Address saved.</div>
  <?php elseif ($msg === 'deleted'): ?>
    <div class="alert alert-success">Address deleted.</div>
  <?php endif; ?>

  <?php if (empty($addresses)): ?>
    <div class="card p-4 text-center text-muted">No addresses saved yet.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($addresses as $a): ?>
        <div class="col-md-6">
          <div class="card p-3">
            <div class="d-flex justify-content-between">
              <div>
                <strong><?php echo htmlspecialchars($a['label'] ?: 'Address'); ?></strong>
                <?php if ($a['is_default']): ?><span class="badge bg-primary ms-2">Default</span><?php endif; ?>
                <div class="small text-muted"><?php echo htmlspecialchars($a['full_name']); ?> — <?php echo htmlspecialchars($a['phone']); ?></div>
                <div><?php echo nl2br(htmlspecialchars($a['address_line1'] . ($a['address_line2'] ? "\n".$a['address_line2'] : '') . "\n" . $a['city'] . ', ' . $a['state'] . ' ' . $a['postal_code'] . "\n" . $a['country'])); ?></div>
              </div>
              <div class="text-end">
                <a class="btn btn-sm btn-outline-primary mb-2" href="address_edit.php?id=<?php echo $a['id']; ?>">Edit</a>
                <form method="post" action="address_delete.php" style="display:inline" onsubmit="return confirm('Delete address?');">
                  <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
