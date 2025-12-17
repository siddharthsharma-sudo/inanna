<?php
// admin/login.php
if (session_status() === PHP_SESSION_NONE) session_start();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php'; // provides $pdo

$error = null;

// CSRF token (simple)
if (empty($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['login_csrf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf'] ?? '';

    if (!$email) {
        $error = 'Please enter a valid email.';
    } elseif (empty($password)) {
        $error = 'Please enter your password.';
    } elseif (!hash_equals($csrf, $token)) {
        $error = 'Invalid request (CSRF).';
    } else {
        // Fetch admin user
        $stmt = $pdo->prepare("SELECT id, password_hash, role, name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['role'] === 'admin' && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
            // Login success
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'] ?? '';

            // Redirect either to preserved URL or dashboard
            $dest = $_SESSION['after_login'] ?? 'dashboard.php';
            unset($_SESSION['after_login']);
            header('Location: ' . $dest);
            exit;
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login - Inanna</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container">
    <div style="max-width:420px;margin:80px auto;">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-3">Admin Login</h4>

          <?php if ($error): ?>
            <div class="alert alert-danger small"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="d-grid">
              <button class="btn btn-primary" type="submit">Sign in</button>
            </div>
          </form>

          <hr>
          <div class="small text-muted">Use the admin credentials you created in the database.</div>
        </div>
      </div>

      <div class="text-center mt-3 small text-muted">
        <a href="./">Back to site</a>
      </div>
    </div>
  </div>
</body>
</html>
