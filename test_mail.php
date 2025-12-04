<?php
// test_mail.php (project root) — quick SMTP test using config inside includes/
require_once __DIR__ . '/includes/config.php';
$config = require __DIR__ . '/includes/config.php';

require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/includes/PHPMailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $config['mail']['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['mail']['smtp_user'];
    $mail->Password = $config['mail']['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $config['mail']['smtp_port'];

    $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
    $mail->addAddress($config['mail']['smtp_user']); // send to configured Gmail address

    $mail->Subject = 'SMTP test';
    $mail->Body = 'This is a test email from Inanna using PHPMailer + SMTP.';
    $mail->send();
    echo "SMTP test sent OK. Check inbox (and spam).";
} catch (Exception $e) {
    echo "Mail failed: " . $mail->ErrorInfo;
}
