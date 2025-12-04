<?php
// reset_password.php (secure reset)
require_once __DIR__ . '/includes/config.php';
$config = require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
session_start();

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$error = '';
$success = false;
$showForm = true;

if (!$token) {
    $error = 'Invalid or expired token.';
    $showForm = false;
} else {
    // fetch reset row and ensure it's still valid
    $stmt = $pdo->prepare("SELECT pr.id, pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = :token LIMIT 1");
    $stmt->execute(['token' => $token]);
    $row = $stmt->fetch();

    if (!$row) {
        $error = 'Invalid or expired token.';
        $showForm = false;
    } else {
        $expires = new DateTime($row['expires_at']);
        $now = new DateTime();
        if ($expires < $now) {
            $error = 'This reset link has expired.';
            $showForm = false;
            // cleanup expired token
            $pdo->prepare("DELETE FROM password_resets WHERE id = :id")->execute(['id' => $row['id']]);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $showForm) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // enforce password rules
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        // perform update in transaction: update password and delete tokens for this user
        try {
            $pdo->beginTransaction();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :uid")->execute(['hash' => $hash, 'uid' => $row['user_id']]);
            // delete tokens for this user (all)
            $pdo->prepare("DELETE FROM password_resets WHERE user_id = :uid")->execute(['uid' => $row['user_id']]);
            $pdo->commit();
            $success = true;
            $showForm = false;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Password reset error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:640px;">
  <h3>Reset Password</h3>

  <?php if ($success): ?>
    <div class="alert alert-success">Your password has been reset. <a href="login.php">Sign in</a></div>
  <?php elseif (!$showForm): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?> <a href="forgot_password.php">Request a new link</a></div>
  <?php else: ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="card p-4 shadow-sm">
      <form method="post" novalidate>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="mb-3">
          <label class="form-label">New password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm password</label>
          <input name="password2" type="password" class="form-control" required>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="submit">Set new password</button>
          <a href="login.php" class="btn btn-outline-secondary">Back to login</a>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
