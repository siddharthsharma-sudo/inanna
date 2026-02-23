<?php
// ===============================
// PHPMailer setup (NO Composer)
// ===============================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/includes/PHPMailer/Exception.php';
require __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/includes/PHPMailer/SMTP.php';

// ===============================
// SMTP Configuration
// ===============================
$config = [
    'smtp_host'   => 'smtp.gmail.com',
    'smtp_port'   => 587,
    'smtp_user'   => 'worldofinanna@gmail.com',
    'smtp_pass'   => 'tiuwqsunclbmutff', // Gmail App Password
    'from_email'  => 'worldofinanna@gmail.com',
    'from_name'   => 'Inanna Shop',
    'admin_email' => 'worldofinanna@gmail.com'
];

// ===============================
// Form Data (safe fetch)
// ===============================
$name         = isset($_POST['name']) ? trim($_POST['name']) : '';
$wedding_date = isset($_POST['wedding_date']) ? trim($_POST['wedding_date']) : '';
$city         = isset($_POST['city']) ? trim($_POST['city']) : '';
$functions    = isset($_POST['functions']) ? trim($_POST['functions']) : '';

// ===============================
// Basic validation
// ===============================
if ($name === '' || $city === '') {
    echo "Required fields missing.";
    exit;
}

// ===============================
// Send Email
// ===============================
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['smtp_port'];

    // Email headers
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['admin_email']);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'New Wedding Lead - Inanna Shop';

    $mail->Body = "
        <h2>New Wedding Lead</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Wedding Date:</strong> {$wedding_date}</p>
        <p><strong>City:</strong> {$city}</p>
        <p><strong>Functions:</strong><br>" . nl2br($functions) . "</p>
    ";

    $mail->AltBody = "New Wedding Lead\n
    Name: {$name}
    Wedding Date: {$wedding_date}
    City: {$city}
    Functions: {$functions}";

    $mail->send();

    echo "Lead submitted successfully!";
} catch (Exception $e) {
    echo "Mail Error: " . $mail->ErrorInfo;
}