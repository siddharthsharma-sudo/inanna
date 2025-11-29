<?php
// public/login.php
// DB connection (expects public/includes/db.php to set $pdo)
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// If already logged in, go to account
if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    header('Location: account.php');
    exit;
}

// CSRF token
if (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['login_csrf'];

$error = '';
$email = '';

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, (string)$token)) {
        $error = 'Invalid request (CSRF).';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $error = 'Please enter email and password.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, password_hash, role, email FROM users WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                // DB error
                error_log('Login DB error: ' . $e->getMessage());
                $user = false;
            }

            if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                // Success: normalize session shape for header and site-wide use
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'] ?? '',
                    'username' => $user['email'] ?? '',
                    // keep is_admin field if present (but we won't redirect on it here)
                    'is_admin' => (isset($user['role']) && $user['role'] === 'admin') ? 1 : 0,
                ];

                // Legacy compatibility
                $_SESSION['user_id'] = $_SESSION['user']['id'];
                $_SESSION['user_name'] = $_SESSION['user']['name'];

                // Redirect to account page (relative path as in your project)
                header('Location: account.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

// Include header and render form
include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:540px;">
  <h2>Sign in</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <div class="card p-4 shadow-sm">
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <button class="btn btn-primary" type="submit">Sign in</button>
        </div>
        <div>
          <!-- Forgot password link -->
          <a href="forgot_password.php" class="small">Forgot password?</a>
        </div>
      </div>

      <div>
        <a class="btn btn-outline-secondary" href="register.php">Create account</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
