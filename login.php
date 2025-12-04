<?php
// login.php
// DB connection (expects includes/db.php to set $pdo)
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
                error_log('Login DB error: ' . $e->getMessage());
                $user = false;
            }

            if ($user && !empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id' => (int)$user['id'],
                    'name' => $user['name'] ?? '',
                    'username' => $user['email'] ?? '',
                    'is_admin' => (isset($user['role']) && $user['role'] === 'admin') ? 1 : 0,
                ];

                // Legacy compatibility
                $_SESSION['user_id'] = $_SESSION['user']['id'];
                $_SESSION['user_name'] = $_SESSION['user']['name'];

                header('Location: account.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- ====================== PAGE STYLING ====================== -->
<style>
    body {
        background-color: #8B0000 !important; /* Dark red background */
    }

    /* Push page content below fixed header */
    .page-content {
        padding-top: 120px; /* Adjust based on exact header height */
        padding-bottom: 60px;
    }
</style>

<!-- ====================== LOGIN PAGE CONTENT ====================== -->

<div class="page-content">
    <div class="container my-4" style="max-width:540px;">
        <h2 class="text-white mb-4">Sign in</h2>

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
                    <button class="btn btn-primary" type="submit">Sign in</button>
                    <a href="forgot_password.php" class="small">Forgot password?</a>
                </div>

                <a class="btn btn-outline-secondary w-100" href="register.php">Create account</a>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
