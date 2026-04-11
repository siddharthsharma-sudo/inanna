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
// Form Data
// ===============================
$name         = isset($_POST['name']) ? trim($_POST['name']) : '';
$mobile       = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
$email        = isset($_POST['email']) ? trim($_POST['email']) : '';
$wedding_date = isset($_POST['wedding_date']) ? trim($_POST['wedding_date']) : '';
$city         = isset($_POST['city']) ? trim($_POST['city']) : '';
$functions    = isset($_POST['functions']) ? trim($_POST['functions']) : '';

// ===============================
// Basic Validation
// ===============================
if ($name === '' || $city === '' || $mobile === '' || $email === '') {
    echo "Required fields missing.";
    exit;
}

// ===============================
// Send Emails
// ===============================
try {

    // ===============================
    // ADMIN EMAIL (Lead Notification)
    // ===============================
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $config['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp_user'];
    $mail->Password   = $config['smtp_pass'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $config['smtp_port'];

    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($config['admin_email']);

    $mail->isHTML(true);
    $mail->Subject = 'New Wedding Lead - Inanna Shop';

    $mail->Body = "
        <h2>New Wedding Lead</h2>

        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Mobile Number:</strong> {$mobile}</p>
        <p><strong>Email ID:</strong> {$email}</p>
        <p><strong>Wedding Date:</strong> {$wedding_date}</p>
        <p><strong>City:</strong> {$city}</p>

        <p><strong>Functions:</strong><br>" . nl2br($functions) . "</p>
    ";

    $mail->AltBody = "New Wedding Lead
    Name: {$name}
    Mobile Number: {$mobile}
    Email ID: {$email}
    Wedding Date: {$wedding_date}
    City: {$city}
    Functions: {$functions}";

    $mail->send();


    // ===============================
    // CUSTOMER EMAIL (Auto Reply)
    // ===============================
    $userMail = new PHPMailer(true);

    $userMail->isSMTP();
    $userMail->Host       = $config['smtp_host'];
    $userMail->SMTPAuth   = true;
    $userMail->Username   = $config['smtp_user'];
    $userMail->Password   = $config['smtp_pass'];
    $userMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $userMail->Port       = $config['smtp_port'];

    $userMail->setFrom($config['from_email'], $config['from_name']);
    $userMail->addAddress($email, $name);

    // Attach Brochure
    $brochurePath = __DIR__ . '/assets/pdf/His&Hers_Brochure.pdf';
    if (file_exists($brochurePath)) {
        $userMail->addAttachment($brochurePath, 'His&Hers_Brochure.pdf');
    }

    $userMail->isHTML(true);
    $userMail->Subject = "Welcome to The World of INANNA";

    $userMail->Body = "
        <p>Dear {$name},</p>

        <p>Welcome to <strong> The World of INANNA.</strong></p>

        <p>You have just taken the first step towards a completely stress-free, beautifully coordinated wedding wardrobe for you and your partner.</p>

        <p>We have attached our exclusive His and Hers brochure for you to explore at your leisure. Inside, you will find everything about our Wedding Guest Collection, the functions we dress you for, our design process, and our packages.</p>

        <p>As a quick reminder of what awaits you:</p>

        <ul>
            <li>Coordinated His and Hers outfits for 3 to 4 wedding functions</li>
            <li>- Custom designed and handcrafted, exclusively for you</li>
            <li>Personal consultation with the INANNA design team</li>
            <li>- 3 Function Package starting from Rs.99,999</li>
            <li>4 Function Package up to Rs.1,49,999 (includes optional child and family dressing)</li>
        </ul>

        <p>One of our designers will be in touch with you shortly to schedule your personal wardrobe consultation.</p>

        <p>In the meantime, feel free to browse our world at <a href='http://worldofinanna.org'>worldofinanna.org</a></p>

        <p>With warmth,<br>
        The INANNA Team<br>
        <a href='http://worldofinanna.org'>worldofinanna.org</a></p>
    ";

    $userMail->send();


    // ===============================
    // Success Message
    // ===============================
    echo "Lead submitted successfully!";

} catch (Exception $e) {

    echo "Mail Error: " . $mail->ErrorInfo;

}
?>