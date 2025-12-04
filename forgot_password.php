<?php
// forgot_password.php (production; uses PHPMailer + SMTP)
// Assumes: includes/config.php, includes/db.php, includes/PHPMailer/*

require_once __DIR__ . '/includes/config.php';
$config = require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } else {
        // find user
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        // Always show generic response to avoid account enumeration
        if (!$user) {
            $sent = true;
        } else {
            // create secure token & expiry (1 hour)
            $token = bin2hex(random_bytes(32));
            $expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

            $ins = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:uid, :token, :expires)");
            $ins->execute(['uid' => $user['id'], 'token' => $token, 'expires' => $expires]);

            // Build reset URL (absolute)
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $scheme . '://' . $_SERVER['HTTP_HOST'];
            $base = rtrim(dirname($_SERVER['REQUEST_URI']), '/');
            $resetUrl = $host . $base . '/reset_password.php?token=' . $token;

            // Send email with PHPMailer via SMTP
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $config['mail']['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['mail']['smtp_user'];
                $mail->Password = $config['mail']['smtp_pass'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $config['mail']['smtp_port'];

                $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
                $mail->addAddress($email, $user['name'] ?? '');
                $mail->isHTML(true);
                $mail->Subject = 'Reset your password';
                $html = "<p>Hi " . htmlspecialchars($user['name']) . ",</p>";
                $html .= "<p>We received a request to reset your password. Click the link below to reset it (valid for 1 hour):</p>";
                $html .= "<p><a href=\"" . htmlspecialchars($resetUrl) . "\">Reset password</a></p>";
                $html .= "<p>If you didn't request this, you can safely ignore this email.</p>";
                $html .= "<p>Regards,<br>" . htmlspecialchars($config['mail']['from_name']) . "</p>";

                $mail->Body = $html;
                $mail->AltBody = "Hi " . ($user['name'] ?? '') . ",\n\nUse this link to reset your password (valid 1 hour):\n\n" . $resetUrl . "\n\nIf you didn't request this, ignore this email.";

                $mail->send();
            } catch (Exception $e) {
                // Log for debugging (do not show to user)
                error_log("Forgot password email failed: " . $mail->ErrorInfo);
            }

            $sent = true;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Page styling to match other auth pages -->
<style>
    body {
        background-color: #8B0000 !important;
    }
    .page-content {
        padding-top: 120px; /* adjust to match your header height */
        padding-bottom: 60px;
    }
    h3 { color: #ffffff; }
    .card { background: #ffffff; }
</style>

<div class="page-content">
  <div class="container my-4" style="max-width:640px;">
    <h3>Forgot Password</h3>

    <?php if ($sent): ?>
      <div class="alert alert-success">If that email exists in our system we sent a password reset link. Please check your inbox (and spam folder).</div>
      <div class="mt-2"><a class="btn btn-outline-secondary" href="login.php">Back to sign in</a></div>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

      <div class="card p-4 shadow-sm">
        <form method="post" novalidate>
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input name="email" type="email" class="form-control" required>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Send reset link</button>
            <a href="login.php" class="btn btn-outline-secondary">Back to login</a>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
