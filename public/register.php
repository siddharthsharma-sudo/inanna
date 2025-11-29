<?php
// public/register.php
// DB connection
require_once __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// If already logged in, redirect to account
if (!empty($_SESSION['user']) && !empty($_SESSION['user']['id'])) {
    header('Location: account.php');
    exit;
}

// simple CSRF token for the form
if (empty($_SESSION['register_csrf'])) {
    $_SESSION['register_csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['register_csrf'];

$errors = [];
$old = ['name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($csrf, (string)$token)) {
        $errors[] = "Invalid request (CSRF).";
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    $old['name'] = $name;
    $old['email'] = $email;
    $old['phone'] = $phone;

    if ($name === '') $errors[] = "Please enter your name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $password2) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email'=>$email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $errors[] = "An account with that email already exists.";
        } else {
            // create user
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (name,email,password_hash,phone,role) VALUES (:name,:email,:hash,:phone,'customer')");
            $ins->execute([
                'name'=>$name,
                'email'=>$email,
                'hash'=>$hash,
                'phone'=>$phone
            ]);
            // auto-login — set normalized session user and redirect to account
            $userId = $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => (int)$userId,
                'name' => $name,
                'username' => $email,
                'is_admin' => 0
            ];
            // legacy vars kept for compatibility
            $_SESSION['user_id'] = $_SESSION['user']['id'];
            $_SESSION['user_name'] = $_SESSION['user']['name'];

            header('Location: account.php?welcome=1');
            exit;
        }
    }
}

// include header (public header)
include __DIR__ . '/includes/header.php';
?>
<div class="container my-4" style="max-width:720px;">
  <h2>Register</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card p-4 shadow-sm">
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>">

      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input name="name" class="form-control" required value="<?php echo htmlspecialchars($old['name']); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input name="email" type="email" class="form-control" required value="<?php echo htmlspecialchars($old['email']); ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Phone (optional)</label>
        <input name="phone" class="form-control" value="<?php echo htmlspecialchars($old['phone']); ?>">
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Password</label>
          <input name="password" type="password" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Confirm password</label>
          <input name="password2" type="password" class="form-control" required>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit">Create account</button>
        <a class="btn btn-outline-secondary" href="login.php">Already have an account? Sign in</a>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
