<?php
// appointment.php - drop into project root (next to includes/)
// Uses includes/config.php and optional includes/header.php/footer.php

session_start();

// ----------------------
// Load existing config
// ----------------------
$configPath = __DIR__ . '/includes/config.php';
if (!file_exists($configPath)) {
    // friendly error so you can still see page in dev
    die('Configuration file not found: /includes/config.php');
}
$config = require $configPath;
$mailCfg = $config['mail'] ?? [];

// ----------------------
// PHPMailer detection (composer or local folder)
// ----------------------
$phpmailer_available = false;
$phpmailer_load_error = null;
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) $phpmailer_available = true;
}
if (!$phpmailer_available) {
    $try_paths = [
        __DIR__ . '/includes/PHPMailer',
        __DIR__ . '/includes/phpmailer',
        __DIR__ . '/includes/PHPMailer/src',
        __DIR__ . '/includes/phpmailer/src',
    ];
    foreach ($try_paths as $p) {
        if (file_exists($p . '/PHPMailer.php') && file_exists($p . '/SMTP.php') && file_exists($p . '/Exception.php')) {
            require_once $p . '/Exception.php';
            require_once $p . '/PHPMailer.php';
            require_once $p . '/SMTP.php';
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                $phpmailer_available = true; break;
            } else {
                $phpmailer_load_error = 'PHPMailer included but class missing in ' . $p;
            }
        }
    }
    if (!$phpmailer_available && !$phpmailer_load_error) {
        $phpmailer_load_error = 'PHPMailer files not found in expected locations.';
    }
}

// ----------------------
// Helpers + time slots
// ----------------------
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function old($k) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') return isset($_POST[$k]) ? htmlspecialchars($_POST[$k], ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') : '';
    return '';
}
function getTimeSlots(): array {
    $slots = [];
    for ($h = 9; $h <= 17; $h++) $slots[] = sprintf('%02d:00 - %02d:45', $h, $h);
    return $slots;
}
$timeSlots = getTimeSlots();

$minDaysAhead = $config['MIN_DAYS_AHEAD'] ?? 0;
$maxDaysAhead = $config['MAX_DAYS_AHEAD'] ?? 90;
$minDate = (new DateTime())->modify("+{$minDaysAhead} days")->format('Y-m-d');
$maxDate = (new DateTime())->modify("+{$maxDaysAhead} days")->format('Y-m-d');

// ----------------------
// Categories (slug => label)
// ----------------------
$categories = [
    'co-ord-set' => 'Co-ord Set',
    'dresses'    => 'Dresses',
    'shirts'     => 'Shirts',
    'pants'      => 'Pants',
    'suits'      => 'Suits',
    'saree'      => 'Saree',
];

// ----------------------
// DB connection (preferred) and fallback CSV
// ----------------------
$pdo = null;
$dbCfg = $config['db'] ?? null;
if (!empty($dbCfg) && !empty($dbCfg['host']) && !empty($dbCfg['name'])) {
    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
            $dbCfg['host'], $dbCfg['name'], $dbCfg['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $dbCfg['user'], $dbCfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        // create table if not exists (more comprehensive schema for Look Reservations)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS look_reservations (
                id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                whatsapp VARCHAR(100) NOT NULL,
                country VARCHAR(100) NOT NULL,
                city VARCHAR(100) NOT NULL,
                occasion VARCHAR(100) NOT NULL,
                outfit_name VARCHAR(255) DEFAULT NULL,
                outfit_description TEXT DEFAULT NULL,
                delivery_date DATE NOT NULL,
                size_type VARCHAR(50) NOT NULL,
                standard_size VARCHAR(50) DEFAULT NULL,
                measurements JSON DEFAULT NULL,
                address JSON DEFAULT NULL,
                notes TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
        ");
    } catch (Exception $e) {
        $pdo = null;
        $pdoErrorNote = $e->getMessage();
    }
}
$csvPath = __DIR__ . '/look_reservations_storage.csv';

// ----------------------
// Process form
// ----------------------
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    // Extract new fields
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = trim(($_POST['country_code'] ?? '') . ' ' . ($_POST['whatsapp_number'] ?? ''));
    $country = trim($_POST['country'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $occasion = trim($_POST['occasion'] ?? '');
    $outfit_name = trim($_POST['outfit_name'] ?? '');
    $outfit_description = trim($_POST['outfit_description'] ?? '');
    $delivery_date = trim($_POST['delivery_date'] ?? '');
    $size_type = trim($_POST['size_type'] ?? '');
    $standard_size = trim($_POST['standard_size'] ?? '');
    
    // Measurements
    $measurements = [];
    if (!empty($_POST['measure_bust'])) $measurements['Bust/Chest'] = $_POST['measure_bust'];
    if (!empty($_POST['measure_waist'])) $measurements['Waist'] = $_POST['measure_waist'];
    if (!empty($_POST['measure_hips'])) $measurements['Hips'] = $_POST['measure_hips'];
    if (!empty($_POST['measure_shoulder'])) $measurements['Shoulder'] = $_POST['measure_shoulder'];
    if (!empty($_POST['measure_length'])) $measurements['Length'] = $_POST['measure_length'];
    if (!empty($_POST['measure_inseam'])) $measurements['Inseam'] = $_POST['measure_inseam'];

    // Address
    $address = [
        'Flat/House' => $_POST['addr_flat'] ?? '',
        'Street' => $_POST['addr_street'] ?? '',
        'City' => $_POST['addr_city'] ?? '',
        'State' => $_POST['addr_state'] ?? '',
        'Pincode' => $_POST['addr_pincode'] ?? '',
        'Country' => $_POST['addr_country'] ?? '',
    ];

    $notes = trim($_POST['notes'] ?? '');

    // basic validation
    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (trim($_POST['whatsapp_number'] ?? '') === '') $errors[] = 'Please enter a WhatsApp number.';
    if ($delivery_date === '') $errors[] = 'Please pick a delivery date.';

    if (empty($errors)) {
        // ----------------------
        // SAVE TO DATABASE / CSV
        // ----------------------
        $savedToStorage = false;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO look_reservations 
                    (name, email, whatsapp, country, city, occasion, outfit_name, outfit_description, delivery_date, size_type, standard_size, measurements, address, notes)
                    VALUES (:name, :email, :whatsapp, :country, :city, :occasion, :outfit_name, :outfit_description, :delivery_date, :size_type, :standard_size, :measurements, :address, :notes)
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':whatsapp' => $whatsapp,
                    ':country' => $country,
                    ':city' => $city,
                    ':occasion' => $occasion,
                    ':outfit_name' => $outfit_name,
                    ':outfit_description' => $outfit_description,
                    ':delivery_date' => $delivery_date,
                    ':size_type' => $size_type,
                    ':standard_size' => $standard_size,
                    ':measurements' => json_encode($measurements),
                    ':address' => json_encode($address),
                    ':notes' => $notes,
                ]);
                $savedToStorage = true;
            } catch (Exception $e) {
                // Log or handle error if needed, but we'll try CSV fallback or continue to email
            }
        }

        if (!$savedToStorage) {
            // CSV fallback
            $lockFp = @fopen($csvPath, 'a');
            if ($lockFp) {
                if (flock($lockFp, LOCK_EX)) {
                    $csvLine = [
                        date('Y-m-d H:i:s'),
                        $name, $email, $whatsapp, $country, $city, $occasion,
                        $outfit_name, $outfit_description, $delivery_date,
                        $size_type, $standard_size, json_encode($measurements),
                        json_encode($address), $notes
                    ];
                    fputcsv($lockFp, $csvLine);
                    flock($lockFp, LOCK_UN);
                    $savedToStorage = true;
                }
                fclose($lockFp);
            }
        }

        // ----------------------
        // SEND EMAILS
        // ----------------------
        // Prepare email content
        $adminRecipient = $mailCfg['admin_email'] ?? ($mailCfg['smtp_user'] ?? ($mailCfg['from_email'] ?? 'worldofinanna@gmail.com'));

        $detailsHtml = "<h2>New Inanna Look Reservation</h2>";
        $detailsHtml .= "<p><strong>Name:</strong> " . h($name) . "</p>";
        $detailsHtml .= "<p><strong>Email:</strong> " . h($email) . "</p>";
        $detailsHtml .= "<p><strong>WhatsApp:</strong> " . h($whatsapp) . "</p>";
        $detailsHtml .= "<p><strong>Location:</strong> " . h($city) . ", " . h($country) . "</p>";
        $detailsHtml .= "<hr>";
        $detailsHtml .= "<p><strong>Occasion:</strong> " . h($occasion) . "</p>";
        $detailsHtml .= "<p><strong>Outfit:</strong> " . h($outfit_name) . "</p>";
        if ($outfit_description) {
            $detailsHtml .= "<p><strong>Description:</strong><br>" . nl2br(h($outfit_description)) . "</p>";
        }
        $detailsHtml .= "<p><strong>Required By:</strong> " . h($delivery_date) . "</p>";
        $detailsHtml .= "<hr>";
        $detailsHtml .= "<p><strong>Size Type:</strong> " . h($size_type) . "</p>";
        if ($size_type === 'standard' && $standard_size) {
            $detailsHtml .= "<p><strong>Standard Size:</strong> " . h($standard_size) . "</p>";
        } elseif ($size_type === 'custom' && !empty($measurements)) {
            $detailsHtml .= "<h3>Measurements (inches):</h3><ul>";
            foreach ($measurements as $label => $val) {
                $detailsHtml .= "<li><strong>$label:</strong> " . h($val) . "</li>";
            }
            $detailsHtml .= "</ul>";
        }
        $detailsHtml .= "<hr>";
        $detailsHtml .= "<h3>Delivery Address:</h3><p>";
        foreach ($address as $label => $val) {
            if ($val) $detailsHtml .= "<strong>$label:</strong> " . h($val) . "<br>";
        }
        $detailsHtml .= "</p>";
        if ($notes) {
            $detailsHtml .= "<p><strong>Additional Notes:</strong><br>" . nl2br(h($notes)) . "</p>";
        }
        
        // Add file info if uploaded
        if (isset($_FILES['outfit_image']) && $_FILES['outfit_image']['error'] === UPLOAD_ERR_OK) {
            $detailsHtml .= "<p><strong>Attachment:</strong> Reference image included as attachment.</p>";
        }

        $userSubject = "Your Appointment Request is Confirmed - World of Inanna";
        $userHtml = "<p>Hi " . h($name) . ",</p>";
        $userHtml .= "<p>Your appointment request has been received.</p>";
        $userHtml .= "<p>Our team will reach out to you on WhatsApp within 24 hours to confirm your slot, discuss pricing, and share next steps.</p>";
        $userHtml .= "<p>Need to reschedule? Reply to this email or reach us at worldofinanna@gmail.com — free reschedule up to 24 hours before your slot.</p>";
        $userHtml .= "<p>See you soon.</p>";
        $userHtml .= "<p>— World of Inanna<br>worldofinanna.org</p>";

        $mailSentAdmin = false;
        $mailSentUser = false;

        if ($phpmailer_available) {
            try {
                $m = new \PHPMailer\PHPMailer\PHPMailer(true);
                // $m->SMTPDebug = 2; // Debug off for production
                $m->isSMTP();
                $m->Host = $mailCfg['smtp_host'] ?? 'smtp.gmail.com';
                $m->SMTPAuth = true;
                $m->Username = $mailCfg['smtp_user'] ?? '';
                $m->Password = $mailCfg['smtp_pass'] ?? '';
                $secure = $mailCfg['smtp_secure'] ?? 'tls';
                if (!empty($secure)) $m->SMTPSecure = $secure;
                $m->Port = $mailCfg['smtp_port'] ?? 587;
                $m->CharSet = 'UTF-8';
                $m->Timeout = 30;

                $fromEmail = $mailCfg['from_email'] ?? $m->Username;
                $fromName  = $mailCfg['from_name'] ?? 'Inanna';

                // admin
                $m->setFrom($fromEmail, $fromName);
                $m->addAddress($adminRecipient);
                $m->addReplyTo($email, $name);
                $m->isHTML(true);
                $m->Subject = "New Inanna Look Reservation: {$name}";
                $m->Body = $detailsHtml;
                $m->AltBody = strip_tags($detailsHtml);
                
                // Handle file attachment if uploaded
                if (isset($_FILES['outfit_image']) && $_FILES['outfit_image']['error'] === UPLOAD_ERR_OK) {
                    $m->addAttachment($_FILES['outfit_image']['tmp_name'], $_FILES['outfit_image']['name']);
                }

                $m->send();
                $mailSentAdmin = true;

                // user
                $u = new \PHPMailer\PHPMailer\PHPMailer(true);
                $u->isSMTP();
                $u->Host = $mailCfg['smtp_host'] ?? 'smtp.gmail.com';
                $u->SMTPAuth = true;
                $u->Username = $mailCfg['smtp_user'] ?? '';
                $u->Password = $mailCfg['smtp_pass'] ?? '';
                if (!empty($secure)) $u->SMTPSecure = $secure;
                $u->Port = $mailCfg['smtp_port'] ?? 587;
                $u->CharSet = 'UTF-8';
                $u->Timeout = 30;
                $u->setFrom($fromEmail, $fromName);
                $u->addAddress($email, $name);
                $u->addReplyTo($adminRecipient);
                $u->isHTML(true);
                $u->Subject = $userSubject;
                $u->Body = $userHtml;
                $u->AltBody = strip_tags($userHtml);
                $u->send();
                $mailSentUser = true;
            } catch (\PHPMailer\PHPMailer\Exception $pex) {
                // $errors[] = 'PHPMailer error: ' . $pex->getMessage();
            } catch (\Exception $ex) {
                // $errors[] = 'Mail error: ' . $ex->getMessage();
            }
        } else {
            $from = ($mailCfg['from_name'] ?? 'Inanna') . " <" . ($mailCfg['from_email'] ?? $adminRecipient) . ">";
            $headers = "From: {$from}\r\nReply-To: {$adminRecipient}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
            if (@mail($adminRecipient, "New Inanna Look Reservation: {$name}", $detailsHtml, $headers)) $mailSentAdmin = true;
            if (@mail($email, $userSubject, $userHtml, $headers)) $mailSentUser = true;
        }

        // We consider success if either data was saved or mail was sent (best effort)
        if ($savedToStorage || $mailSentAdmin || $mailSentUser) $success = true;
    }
}

// Attempt to include header/footer if they exist (keeps design)
$header_class = 'navbar-light-bg';
$headerPath = __DIR__ . '/includes/header.php';
if (file_exists($headerPath)) include $headerPath;
?>

<!-- ---------- DESIGN/CSS from your preferred layout (keeps structure + visuals) ---------- -->
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
 
  :root {
    --cream: #F7F1E8;
    --warm-white: #FDF9F4;
    --clay: #C4825A;
    --deep: #1A1208;
    --gold: #B8956A;
    --muted: #8A7A6A;
    --border: #E0D5C5;
    --input-bg: #FEFCF9;
  }
 
  body {
    background-color: var(--cream);
    font-family: 'DM Sans', sans-serif;
    color: var(--deep);
    min-height: 100vh;
    overflow-x: hidden;
  }
 
  /* Background texture */
  body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23C4825A' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
    z-index: 0;
  }
 
  .appoint-page-layout {
    position: relative;
    z-index: 1;
    max-width: 1140px;
    margin: 0 auto;
    padding: 120px 24px 80px;
  }

  .appoint-content-grid {
    display: flex;
    gap: 48px;
    align-items: flex-start;
  }

  .appoint-form-container {
    flex: 1;
    min-width: 0;
    max-width: 680px;
  }

  /* Sidebar */
  .appoint-sidebar {
    flex: 0 0 280px;
    position: sticky;
    top: 100px;
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .appoint-sidebar-card {
    background: var(--warm-white);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 24px 22px;
  }

  .appoint-sidebar-section {
    margin-bottom: 22px;
    padding-bottom: 22px;
    border-bottom: 1px dashed var(--border);
  }

  .appoint-sidebar-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
  }

  .appoint-sidebar-label {
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--clay);
    margin-bottom: 10px;
    display: block;
  }

  .appoint-sidebar-body {
    font-size: 13px;
    color: var(--deep);
    line-height: 1.75;
  }

  .appoint-sidebar-body strong {
    font-weight: 500;
  }

  .appoint-sidebar-list {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 13px;
    color: var(--deep);
    line-height: 1.9;
  }

  .appoint-sidebar-list li::before {
    content: "✦";
    color: var(--gold);
    font-size: 9px;
    margin-right: 8px;
    vertical-align: middle;
  }

  .appoint-sidebar-hours {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .appoint-hours-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: var(--deep);
  }

  .appoint-hours-row span:first-child { color: var(--muted); }

  .appoint-sidebar-link {
    color: var(--clay);
    text-decoration: none;
    font-size: 13px;
  }

  .appoint-sidebar-link:hover { text-decoration: underline; }

  .appoint-sidebar-note {
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    font-family: "Cormorant Garamond", serif;
    font-size: 14px;
    line-height: 1.6;
    margin-top: 8px;
  }

  @media (max-width: 900px) {
    .appoint-content-grid {
      flex-direction: column;
      align-items: stretch;
    }
    .appoint-sidebar {
      position: static;
      flex: none;
      order: 1; /* Below header, before form */
      margin-bottom: 40px;
    }
    .appoint-form-container { 
      max-width: 100%; 
      order: 2;
    }
  }

  /* Header */
  .appoint-header {
    text-align: center;
    margin-top: 40px;
    margin-bottom: 56px;
    animation: fadeUp 0.8s ease both;
  }
 
  .appoint-inline-logo {
    height: 1.2em;
    width: auto;
    vertical-align: middle;
    margin-top: -0.2em;
    display: inline-block;
  }
 
  .appoint-brand-logo {
    height: 64px;
    width: auto;
    margin-bottom: 24px;
    display: block;
    margin-left: auto;
    margin-right: auto;
  }
 
  .appoint-brand-tag {
    font-family: 'DM Sans', sans-serif;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--clay);
    margin-bottom: 20px;
    display: block;
  }
 
  .appoint-header h1 {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(42px, 8vw, 64px);
    font-weight: 300;
    line-height: 1.05;
    color: var(--deep);
    margin-bottom: 8px;
  }
 
  .appoint-header h1 em {
    font-style: italic;
    color: var(--clay);
  }
 
  .appoint-header p {
    font-size: 18px;
    color: var(--muted);
    font-weight: 300;
    margin-top: 16px;
    line-height: 1.7;
  }
 
  .appoint-divider {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 32px auto;
    max-width: 200px;
  }
 
  .appoint-divider::before, .appoint-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--gold);
    opacity: 0.4;
  }
 
  .appoint-divider-icon {
    color: var(--gold);
    font-size: 16px;
  }
 
  .appoint-header-tagline {
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(18px, 3vw, 22px);
    font-weight: 400;
    font-style: italic;
    color: var(--clay);
    margin-bottom: 16px;
    line-height: 1.4;
  }
 
  .appoint-header-body {
    font-size: 14px;
    color: var(--muted);
    font-weight: 300;
    line-height: 1.8;
    margin-bottom: 16px;
  }
 
  .appoint-header-note {
    font-size: 12px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--gold);
    font-weight: 500;
  }
 
  /* Form card */
  .appoint-form-card {
    background: var(--warm-white);
    border: 1px solid var(--border);
    border-radius: 4px;
    padding: 48px 40px;
    box-shadow: 0 8px 40px rgba(26,18,8,0.06), 0 2px 8px rgba(26,18,8,0.04);
    animation: fadeUp 0.8s 0.2s ease both;
  }
 
  @media (max-width: 520px) {
    .appoint-form-card { padding: 32px 24px; }
  }
 
  /* Field groups */
  .appoint-field-group {
    margin-bottom: 32px;
  }
 
  .appoint-field-label {
    display: block;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--deep);
    margin-bottom: 6px;
  }
 
  .appoint-field-hint {
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    margin-bottom: 10px;
    display: block;
  }
 
  input[type="text"],
  input[type="email"],
  input[type="tel"],
  input[type="date"],
  select,
  textarea {
    width: 100%;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 14px 16px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--deep);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
    -webkit-appearance: none;
  }
 
  input[type="text"]:focus,
  input[type="email"]:focus,
  input[type="tel"]:focus,
  input[type="date"]:focus,
  select:focus,
  textarea:focus {
    border-color: var(--clay);
    box-shadow: 0 0 0 3px rgba(196,130,90,0.1);
  }
 
  input::placeholder, textarea::placeholder {
    color: #C5B9A8;
    font-style: italic;
  }
 
  select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238A7A6A' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 16px center;
    padding-right: 40px;
    cursor: pointer;
  }
 
  textarea {
    resize: vertical;
    min-height: 110px;
    line-height: 1.6;
  }
 
  /* Radio / Size options */
  .appoint-size-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
 
  .appoint-size-option {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 2px;
    background: var(--input-bg);
    transition: border-color 0.2s, background 0.2s;
  }
 
  .appoint-size-option:hover {
    border-color: var(--clay);
    background: #FEF8F3;
  }
 
  .appoint-size-option input[type="radio"] {
    width: 16px;
    height: 16px;
    accent-color: var(--clay);
    flex-shrink: 0;
    cursor: pointer;
  }
 
  .appoint-size-option input[type="radio"]:checked + .appoint-size-label {
    color: var(--clay);
  }
 
  .appoint-size-option:has(input:checked) {
    border-color: var(--clay);
    background: #FEF8F3;
  }
 
  .appoint-size-label {
    font-size: 14px;
    color: var(--deep);
    transition: color 0.2s;
  }
 
  /* Section separator */
  .appoint-section-sep {
    border: none;
    border-top: 1px dashed var(--border);
    margin: 36px 0;
  }
 
  /* File upload */
  .appoint-upload-area {
    border: 1.5px dashed var(--border);
    border-radius: 2px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--input-bg);
    margin-top: 10px;
  }
 
  .appoint-upload-area:hover {
    border-color: var(--clay);
    background: #FEF8F3;
  }
 
  .appoint-upload-area input { display: none; }
 
  .appoint-upload-icon { font-size: 24px; margin-bottom: 8px; display: block; }
 
  .appoint-upload-text {
    font-size: 13px;
    color: var(--muted);
    font-style: italic;
    font-family: 'Cormorant Garamond', serif;
    font-size: 15px;
  }
 
  .appoint-upload-text strong {
    color: var(--clay);
    font-weight: 400;
    font-style: normal;
    text-decoration: underline;
    text-underline-offset: 2px;
  }
 
  #appoint-file-name {
    margin-top: 8px;
    font-size: 12px;
    color: var(--clay);
    font-style: italic;
  }
 
  /* Submit button */
  .appoint-submit-wrap {
    margin-top: 40px;
    text-align: center;
  }
 
  .appoint-submit-btn {
    background: var(--deep);
    color: var(--cream);
    border: none;
    padding: 18px 56px;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    cursor: pointer;
    border-radius: 2px;
    transition: background 0.25s, transform 0.15s, box-shadow 0.25s;
    position: relative;
    overflow: hidden;
  }
 
  .appoint-submit-btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--clay);
    transform: translateX(-100%);
    transition: transform 0.35s ease;
    z-index: 0;
  }
 
  .appoint-submit-btn:hover::after { transform: translateX(0); }
 
  .appoint-submit-btn span {
    position: relative;
    z-index: 1;
  }
 
  .appoint-submit-btn:hover {
    box-shadow: 0 8px 24px rgba(196,130,90,0.3);
    transform: translateY(-1px);
  }
 
  .appoint-submit-btn:active { transform: translateY(0); }
 
  .appoint-submit-note {
    margin-top: 14px;
    font-size: 12px;
    color: var(--muted);
    font-family: 'Cormorant Garamond', serif;
    font-style: italic;
    font-size: 14px;
  }
 
  /* Success state */
  .appoint-success-msg {
    display: none;
    text-align: center;
    padding: 48px 24px;
    animation: fadeUp 0.5s ease both;
  }
 
  .appoint-success-msg.show { display: block; }
 
  .appoint-success-icon { font-size: 40px; margin-bottom: 16px; }
 
  .appoint-success-msg h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 36px;
    font-weight: 300;
    color: var(--deep);
    margin-bottom: 12px;
  }
 
  .appoint-success-msg p {
    font-size: 14px;
    color: var(--muted);
    line-height: 1.7;
  }
 
  /* Animations */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }
 
  .appoint-field-group {
    animation: fadeUp 0.6s ease both;
  }
 
  .appoint-field-group:nth-child(1) { animation-delay: 0.1s; }
  .appoint-field-group:nth-child(2) { animation-delay: 0.15s; }
  .appoint-field-group:nth-child(3) { animation-delay: 0.2s; }
  .appoint-field-group:nth-child(4) { animation-delay: 0.25s; }
  .appoint-field-group:nth-child(5) { animation-delay: 0.3s; }
  .appoint-field-group:nth-child(6) { animation-delay: 0.35s; }
  .appoint-field-group:nth-child(7) { animation-delay: 0.4s; }
  .appoint-field-group:nth-child(8) { animation-delay: 0.45s; }
 
  /* Size Chart */
  .appoint-size-chart-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--clay);
    cursor: pointer;
    border: none;
    background: none;
    padding: 0;
    font-family: 'DM Sans', sans-serif;
    text-decoration: underline;
    text-underline-offset: 3px;
  }
 
  .appoint-size-chart-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(26,18,8,0.6);
    z-index: 100;
    overflow-y: auto;
    padding: 24px 16px;
    backdrop-filter: blur(4px);
  }
 
  .appoint-size-chart-modal.open { display: flex; align-items: flex-start; justify-content: center; }
 
  .appoint-size-chart-inner {
    background: var(--warm-white);
    border: 1px solid var(--border);
    border-radius: 4px;
    max-width: 720px;
    width: 100%;
    padding: 40px 32px;
    position: relative;
    animation: fadeUp 0.3s ease both;
    margin: auto;
  }
 
  .appoint-size-chart-close {
    position: absolute;
    top: 16px;
    right: 20px;
    font-size: 22px;
    cursor: pointer;
    color: var(--muted);
    background: none;
    border: none;
    line-height: 1;
    font-family: 'DM Sans', sans-serif;
  }
 
  .appoint-size-chart-close:hover { color: var(--deep); }
 
  .appoint-size-chart-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 28px;
    font-weight: 300;
    color: var(--deep);
    margin-bottom: 4px;
  }
 
  .appoint-size-chart-subtitle {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 28px;
    letter-spacing: 0.05em;
  }
 
  .appoint-size-chart-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 28px;
    border-bottom: 1px solid var(--border);
  }
 
  .appoint-size-tab {
    padding: 10px 24px;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    border: none;
    background: none;
    color: var(--muted);
    font-family: 'DM Sans', sans-serif;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.2s, border-color 0.2s;
  }
 
  .appoint-size-tab.active {
    color: var(--clay);
    border-bottom-color: var(--clay);
  }
 
  .appoint-size-chart-body { display: none; }
  .appoint-size-chart-body.active { display: block; }
 
  .appoint-body-figure-wrap {
    display: flex;
    gap: 32px;
    align-items: flex-start;
    flex-wrap: wrap;
    margin-bottom: 28px;
  }
 
  .appoint-body-figure {
    flex: 0 0 120px;
    text-align: center;
  }
 
  .appoint-body-figure svg {
    width: 100px;
    height: auto;
    display: block;
    margin: 0 auto 8px;
  }
 
  .appoint-body-figure-label {
    font-size: 11px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--muted);
  }
 
  .appoint-measure-list {
    flex: 1;
    min-width: 200px;
  }
 
  .appoint-measure-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
  }
 
  .appoint-measure-item:last-child { border-bottom: none; }
  .appoint-measure-name { color: var(--muted); }
  .appoint-measure-where { color: var(--deep); font-weight: 500; }
 
  .appoint-size-table-wrap { overflow-x: auto; }
 
  table.appoint-size-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    margin-top: 8px;
  }
 
  .appoint-size-table th {
    background: var(--deep);
    color: var(--cream);
    padding: 10px 14px;
    text-align: center;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }
 
  .appoint-size-table td {
    padding: 10px 14px;
    text-align: center;
    border-bottom: 1px solid var(--border);
    color: var(--deep);
  }
 
  .appoint-size-table tr:nth-child(even) td { background: #FAF6F0; }
  .appoint-size-table tr:hover td { background: #FEF3EA; }
 
  .appoint-size-table td:first-child {
    font-weight: 600;
    color: var(--clay);
    letter-spacing: 0.08em;
  }
 
  .appoint-size-note {
    margin-top: 16px;
    font-size: 12px;
    color: var(--muted);
    font-style: italic;
    font-family: 'Cormorant Garamond', serif;
    font-size: 14px;
    line-height: 1.6;
  }
 
 
  /* Enhanced Size Section */
  .appoint-size-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 14px;
  }
 
  @media (max-width: 480px) {
    .appoint-size-cards { grid-template-columns: 1fr; }
  }
 
  .appoint-size-card {
    position: relative;
    cursor: pointer;
  }
 
  .appoint-size-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
  }
 
  .appoint-size-card-inner {
    border: 1.5px solid var(--border);
    border-radius: 4px;
    padding: 18px 14px;
    text-align: center;
    background: var(--input-bg);
    transition: all 0.2s ease;
    height: 100%;
  }
 
  .appoint-size-card:hover .appoint-size-card-inner {
    border-color: var(--clay);
    background: #FEF8F3;
  }
 
  .appoint-size-card input:checked ~ .appoint-size-card-inner {
    border-color: var(--clay);
    background: #FEF3EA;
    box-shadow: 0 0 0 3px rgba(196,130,90,0.12);
  }
 
  .appoint-size-card-icon {
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
  }
 
  .appoint-size-card-title {
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--deep);
    margin-bottom: 6px;
    display: block;
  }
 
  .appoint-size-card-desc {
    font-size: 11.5px;
    color: var(--muted);
    font-family: "Cormorant Garamond", serif;
    font-style: italic;
    font-size: 13px;
    line-height: 1.4;
  }
 
  .appoint-size-card input:checked ~ .appoint-size-card-inner .appoint-size-card-title {
    color: var(--clay);
  }
 
  /* Custom measurements reveal */
  .appoint-custom-measurements {
    display: none;
    margin-top: 20px;
    border: 1.5px solid var(--clay);
    border-radius: 4px;
    padding: 24px;
    background: #FEF8F3;
    animation: fadeUp 0.3s ease both;
  }
 
  .appoint-custom-measurements.visible { display: block; }
 
  .appoint-custom-measurements-title {
    font-family: "Cormorant Garamond", serif;
    font-size: 18px;
    font-weight: 400;
    color: var(--deep);
    margin-bottom: 4px;
  }
 
  .appoint-custom-measurements-hint {
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 20px;
    line-height: 1.6;
  }
 
  .appoint-measurements-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
 
  @media (max-width: 480px) {
    .appoint-measurements-grid { grid-template-columns: 1fr; }
  }
 
  .appoint-measurement-field label {
    display: block;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--deep);
    margin-bottom: 5px;
  }
 
  .appoint-measurement-field input {
    width: 100%;
    background: white;
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 10px 12px;
    font-family: "DM Sans", sans-serif;
    font-size: 13px;
    color: var(--deep);
    outline: none;
    transition: border-color 0.2s;
  }
 
  .appoint-measurement-field input:focus {
    border-color: var(--clay);
    box-shadow: 0 0 0 2px rgba(196,130,90,0.1);
  }
 
  .appoint-measurement-field input::placeholder {
    color: #C5B9A8;
    font-style: italic;
  }
 
  .appoint-gender-toggle {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
  }
 
  .appoint-gender-btn {
    flex: 1;
    padding: 8px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    border: 1.5px solid var(--border);
    border-radius: 2px;
    background: white;
    color: var(--muted);
    font-family: "DM Sans", sans-serif;
    transition: all 0.2s;
  }
 
  .appoint-gender-btn.active {
    border-color: var(--clay);
    background: var(--clay);
    color: white;
  }
 
  /* Standard size dropdown reveal */
  .appoint-standard-size-select {
    display: none;
    margin-top: 20px;
    animation: fadeUp 0.3s ease both;
  }
 
  .appoint-standard-size-select.visible { display: block; }
 
  .appoint-size-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 8px;
    margin-top: 10px;
  }
 
  @media (max-width: 480px) {
    .appoint-size-grid { grid-template-columns: repeat(3, 1fr); }
  }
 
  .appoint-size-bubble {
    text-align: center;
    cursor: pointer;
  }
 
  .appoint-size-bubble input[type="radio"] {
    display: none;
  }
 
  .appoint-size-bubble-label {
    display: block;
    padding: 10px 4px;
    border: 1.5px solid var(--border);
    border-radius: 2px;
    font-size: 12px;
    font-weight: 500;
    color: var(--deep);
    background: white;
    transition: all 0.2s;
    cursor: pointer;
  }
 
  .appoint-size-bubble input:checked + .appoint-size-bubble-label {
    border-color: var(--clay);
    background: var(--clay);
    color: white;
  }
 
  .appoint-size-bubble:hover .appoint-size-bubble-label {
    border-color: var(--clay);
    color: var(--clay);
  }
 
 
  /* Phone with country code */
  .appoint-phone-row {
    display: flex;
    gap: 10px;
  }
 
  .appoint-country-code-select {
    flex: 0 0 140px;
  }
 
  .appoint-phone-input-wrap { flex: 1; }
 
  /* Two column grid for address */
  .appoint-address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
  }
 
  @media (max-width: 480px) {
    .appoint-address-grid { grid-template-columns: 1fr; }
    .appoint-country-code-select { flex: 0 0 120px; }
    .appoint-header h1 {font-size: clamp(38px, 8vw, 64px);}
  }

  
 
  .appoint-address-grid .appoint-full-width { grid-column: 1 / -1; }
 
  /* Outfit tabs */
  .appoint-outfit-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
  }
 
  .appoint-outfit-tab-btn {
    flex: 1;
    padding: 9px 12px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    cursor: pointer;
    border: 1.5px solid var(--border);
    border-radius: 2px;
    background: var(--input-bg);
    color: var(--muted);
    font-family: "DM Sans", sans-serif;
    transition: all 0.2s;
  }
 
  .appoint-outfit-tab-btn.active {
    border-color: var(--clay);
    color: var(--clay);
    background: #FEF3EA;
  }
 
  .appoint-outfit-tab-panel { display: none; }
  .appoint-outfit-tab-panel.active { display: block; }
 
</style>
</head>
<body>
 
<div class="appoint-page-layout">
 
  <!-- Header -->
  <div class="appoint-header">
    <h1>Book Your <em><img src="https://worldofinanna.org/assets/images/logo-inanna.avif" alt="Inanna" class="appoint-inline-logo"> Look.</em></h1>
    <div class="appoint-divider"><span class="appoint-divider-icon">✦</span></div>
    <p class="appoint-header-tagline"><img src="https://worldofinanna.org/assets/images/logo-inanna.avif" alt="Inanna" class="appoint-inline-logo" style="height: 1em;"> isn't off-the-rack.</p>
    <p class="appoint-header-body">Leave your details and we'll tailor the experience to you.</p>
    <p class="appoint-header-note">We respond within 24 hours.</p>
  </div>

  <div class="appoint-content-grid">
    <!-- Sidebar -->
    <aside class="appoint-sidebar">
      <div class="appoint-sidebar-card">
        <div class="appoint-sidebar-section">
          <span class="appoint-sidebar-label">Opening Hours</span>
          <div class="appoint-sidebar-hours">
            <div class="appoint-hours-row"><span>Mon — Sat</span><span>9:00 — 18:00</span></div>
            <div class="appoint-hours-row"><span>Sunday</span><span>Closed</span></div>
          </div>
        </div>

        <div class="appoint-sidebar-section">
          <span class="appoint-sidebar-label">Location</span>
          <p class="appoint-sidebar-body">Studio Inanna<br>Jhajra, Dehradun 248007</p>
        </div>

        <div class="appoint-sidebar-section">
          <span class="appoint-sidebar-label">Quick Facts</span>
          <ul class="appoint-sidebar-list">
            <li>45-min appointments</li>
            <li>Fittings by appointment</li>
            <li>Appointments online only</li>
          </ul>
        </div>

        <div class="appoint-sidebar-section">
          <span class="appoint-sidebar-label">Prepare for Your Appointment</span>
          <ul class="appoint-sidebar-list">
            <li>Bring reference images or links</li>
            <li>Know your usual sizes (chest / waist / hips / height)</li>
            <li>If fabric is available, bring a swatch</li>
          </ul>
        </div>

        <div class="appoint-sidebar-section">
          <span class="appoint-sidebar-label">Cancellation & Reschedule</span>
          <p class="appoint-sidebar-body">Free reschedule up to 24 hours before your slot.</p>
          <p class="appoint-sidebar-note">To cancel or change, reply to your confirmation email.</p>
        </div>
      </div>
    </aside>

    <div class="appoint-form-container">
      <div class="appoint-form-card">
        <?php if (!empty($errors)): ?>
          <div class="appoint-error" style="background:#fff2f3;color:#7f1d1d;padding:10px;border-radius:8px;margin-bottom:12px"><?php echo implode('<br>', array_map('h', $errors)); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="appoint-success-msg show" id="appoint-success-msg">
          <div class="appoint-success-icon">✦</div>
          <h2>Your look is reserved.</h2>
          <p>We've received your request and will reach out<br>on WhatsApp within 24–48 hours.<br><br>Get ready — something beautiful is coming.</p>
        </div>
        <style>#appoint-booking-form { display: none; }</style>
        <?php else: ?>
          <form id="appoint-booking-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="final_submit" value="1">
 
      <!-- Full Name -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-full-name">Full Name</label>
        <span class="appoint-field-hint">So we know who we're dressing.</span>
        <input type="text" id="appoint-full-name" name="full_name" placeholder="Your name here" required>
      </div>
 
      <!-- WhatsApp -->
      <div class="appoint-field-group">
        <label class="appoint-field-label">WhatsApp Number</label>
        <span class="appoint-field-hint">Where we'll send pricing & details.</span>
        <div class="appoint-phone-row">
          <div class="appoint-country-code-select">
            <select name="country_code" required>
              <option value="" disabled selected>Country</option>
              <option value="+91">🇮🇳 +91 India</option>
              <option value="+1">🇺🇸 +1 USA</option>
              <option value="+44">🇬🇧 +44 UK</option>
              <option value="+971">🇦🇪 +971 UAE</option>
              <option value="+61">🇦🇺 +61 Australia</option>
              <option value="+65">🇸🇬 +65 Singapore</option>
              <option value="+60">🇲🇾 +60 Malaysia</option>
              <option value="+1-CA">🇨🇦 +1 Canada</option>
              <option value="+49">🇩🇪 +49 Germany</option>
              <option value="+33">🇫🇷 +33 France</option>
              <option value="+39">🇮🇹 +39 Italy</option>
              <option value="+81">🇯🇵 +81 Japan</option>
              <option value="+82">🇰🇷 +82 South Korea</option>
              <option value="+92">🇵🇰 +92 Pakistan</option>
              <option value="+880">🇧🇩 +880 Bangladesh</option>
              <option value="+94">🇱🇰 +94 Sri Lanka</option>
              <option value="+977">🇳🇵 +977 Nepal</option>
              <option value="+other">🌍 Other</option>
            </select>
          </div>
          <div class="appoint-phone-input-wrap">
            <input type="tel" id="appoint-whatsapp" name="whatsapp_number" placeholder="00000 00000" required>
          </div>
        </div>
      </div>
 
      <!-- Email -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-email">Email Address</label>
        <span class="appoint-field-hint">For your booking confirmation.</span>
        <input type="email" id="appoint-email" name="email" placeholder="you@example.com" required>
      </div>
 
      <!-- Country -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-country">Country</label>
        <span class="appoint-field-hint">For delivery timeline clarity.</span>
        <select id="appoint-country" name="country" required>
          <option value="" disabled selected>Select your country</option>
          <option>India</option>
          <option>United States</option>
          <option>United Kingdom</option>
          <option>United Arab Emirates</option>
          <option>Australia</option>
          <option>Singapore</option>
          <option>Malaysia</option>
          <option>Canada</option>
          <option>Germany</option>
          <option>France</option>
          <option>Italy</option>
          <option>Japan</option>
          <option>South Korea</option>
          <option>Pakistan</option>
          <option>Bangladesh</option>
          <option>Sri Lanka</option>
          <option>Nepal</option>
          <option>Other</option>
        </select>
      </div>
 
      <!-- City -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-city">City</label>
        <span class="appoint-field-hint">Which city are you based in?</span>
        <input type="text" id="appoint-city" name="city" placeholder="e.g. Mumbai, Delhi, Dubai" required>
      </div>
 
      <hr class="appoint-section-sep">
 
      <!-- Occasion -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-occasion">Occasion</label>
        <span class="appoint-field-hint">What are we dressing you for?</span>
        <select id="appoint-occasion" name="occasion" required>
          <option value="" disabled selected>Select your occasion</option>
          <option value="wedding-guest">Wedding Guest</option>
          <option value="bridesmaid">Bridesmaid</option>
          <option value="haldi-cocktail-reception">Haldi / Cocktail / Reception</option>
          <option value="resort-holiday">Resort / Holiday</option>
          <option value="custom-event">Custom Event</option>
          <option value="just-because">Just because I felt like it 💅</option>
        </select>
      </div>
 
      <!-- Outfit Reference -->
      <div class="appoint-field-group">
        <label class="appoint-field-label">Outfit Reference</label>
        <span class="appoint-field-hint">Tell us exactly what you have in mind.</span>
        <div class="appoint-outfit-tabs">
          <button type="button" class="appoint-outfit-tab-btn active" onclick="switchOutfitTab('name', this)">I Know the Name</button>
          <button type="button" class="appoint-outfit-tab-btn" onclick="switchOutfitTab('describe', this)">Describe It</button>
          <button type="button" class="appoint-outfit-tab-btn" onclick="switchOutfitTab('upload', this)">Upload Image</button>
        </div>
        <div class="appoint-outfit-tab-panel active" id="appoint-outfit-name-panel">
          <input type="text" id="appoint-outfit-name" name="outfit_name" placeholder="e.g. The Rani Set, Throneplay, Golden Hour Saree" required>
        </div>
        <div class="appoint-outfit-tab-panel" id="appoint-outfit-describe-panel">
          <textarea name="outfit_description" placeholder="Describe the style, silhouette, fabric, color, or any inspiration you have in mind..." style="min-height:90px;"></textarea>
        </div>
        <div class="appoint-outfit-tab-panel" id="appoint-outfit-upload-panel">
          <label class="appoint-upload-area" for="appoint-outfit-image">
            <input type="file" id="appoint-outfit-image" name="outfit_image" accept="image/*" onchange="showFileName(this)">
            <span class="appoint-upload-icon">📎</span>
            <span class="appoint-upload-text">Drag & drop or <strong>browse</strong> to upload a screenshot</span>
            <div id="appoint-file-name"></div>
          </label>
        </div>
      </div>
 
      <!-- Delivery Date -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-required-by">When Do You Need It Delivered?</label>
        <span class="appoint-field-hint">Earliest delivery is 15 days from today.</span>
        <input type="date" id="appoint-required-by" name="delivery_date" required>
      </div>
 
      <!-- Size -->
      <div class="appoint-field-group">
        <label class="appoint-field-label">Size Preference</label>
        <span class="appoint-field-hint">Choose how you'd like us to fit your outfit.</span>
 
        <div class="appoint-size-cards">
          <label class="appoint-size-card">
            <input type="radio" name="size_type" value="standard" required onchange="handleSizeType(this.value)">
            <div class="appoint-size-card-inner">
              <span class="appoint-size-card-icon">📐</span>
              <span class="appoint-size-card-title">Standard</span>
              <span class="appoint-size-card-desc">I know my size — XS to XXL</span>
            </div>
          </label>
          <label class="appoint-size-card">
            <input type="radio" name="size_type" value="custom" onchange="handleSizeType(this.value)">
            <div class="appoint-size-card-inner">
              <span class="appoint-size-card-icon">✂️</span>
              <span class="appoint-size-card-title">Custom</span>
              <span class="appoint-size-card-desc">I'll share my exact measurements</span>
            </div>
          </label>
          <label class="appoint-size-card">
            <input type="radio" name="size_type" value="assistance" onchange="handleSizeType(this.value)">
            <div class="appoint-size-card-inner">
              <span class="appoint-size-card-icon">🤝</span>
              <span class="appoint-size-card-title">Need Help</span>
              <span class="appoint-size-card-desc">Guide me through sizing</span>
            </div>
          </label>
        </div>
 
        <!-- Standard Size Selector -->
        <div class="appoint-standard-size-select" id="appoint-standardSizeSelect">
          <span class="appoint-field-hint" style="margin-bottom:8px;display:block;">Select your size below.</span>
          <div class="appoint-size-grid">
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="XS"><span class="appoint-size-bubble-label">XS</span></label>
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="S"><span class="appoint-size-bubble-label">S</span></label>
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="M"><span class="appoint-size-bubble-label">M</span></label>
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="L"><span class="appoint-size-bubble-label">L</span></label>
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="XL"><span class="appoint-size-bubble-label">XL</span></label>
            <label class="appoint-size-bubble"><input type="radio" name="standard_size" value="XXL"><span class="appoint-size-bubble-label">XXL</span></label>
          </div>
        </div>
 
        <!-- Custom Measurements -->
        <div class="appoint-custom-measurements" id="appoint-customMeasurements">
          <p class="appoint-custom-measurements-title">Your Measurements</p>
          <p class="appoint-custom-measurements-hint">Fill in what you know. Leave the rest blank and we'll guide you. All measurements in inches.</p>
          <div class="appoint-gender-toggle">
            <button type="button" class="appoint-gender-btn active" onclick="setGender('female', this)">👗 Women</button>
            <button type="button" class="appoint-gender-btn" onclick="setGender('male', this)">👔 Men</button>
          </div>
          <div class="appoint-measurements-grid" id="appoint-measurementsGrid">
            <div class="appoint-measurement-field">
              <label>Bust / Chest (inches)</label>
              <input type="text" name="measure_bust" placeholder="e.g. 36">
            </div>
            <div class="appoint-measurement-field">
              <label>Waist (inches)</label>
              <input type="text" name="measure_waist" placeholder="e.g. 30">
            </div>
            <div class="appoint-measurement-field">
              <label>Hips (inches)</label>
              <input type="text" name="measure_hips" placeholder="e.g. 38">
            </div>
            <div class="appoint-measurement-field">
              <label>Shoulder (inches)</label>
              <input type="text" name="measure_shoulder" placeholder="e.g. 14">
            </div>
            <div class="appoint-measurement-field">
              <label>Length (inches)</label>
              <input type="text" name="measure_length" placeholder="e.g. 52">
            </div>
            <div class="appoint-measurement-field" id="appoint-field-inseam">
              <label>Inseam (inches)</label>
              <input type="text" name="measure_inseam" placeholder="e.g. 30">
            </div>
          </div>
        </div>
 
        <button type="button" class="appoint-size-chart-toggle" style="margin-top:14px;" onclick="document.getElementById('appoint-sizeChartModal').classList.add('open')">
          📏 View Size Chart
        </button>
      </div>
 
      <hr class="appoint-section-sep">
 
      <!-- Address -->
      <div class="appoint-field-group">
        <label class="appoint-field-label">Delivery Address</label>
        <span class="appoint-field-hint">Where should we send your look?</span>
        <div class="appoint-address-grid">
          <div class="appoint-full-width">
            <input type="text" name="addr_flat" placeholder="Flat / House No. & Building Name" required>
          </div>
          <div class="appoint-full-width">
            <input type="text" name="addr_street" placeholder="Street / Area / Locality" required>
          </div>
          <div>
            <input type="text" name="addr_city" placeholder="City" required>
          </div>
          <div>
            <input type="text" name="addr_state" placeholder="State / Province" required>
          </div>
          <div>
            <input type="text" name="addr_pincode" placeholder="PIN / ZIP Code" required>
          </div>
          <div>
            <input type="text" name="addr_country" placeholder="Country" required>
          </div>
        </div>
      </div>
 
      <!-- Notes -->
      <div class="appoint-field-group">
        <label class="appoint-field-label" for="appoint-notes">Anything We Should Know?</label>
        <span class="appoint-field-hint">Color preference, modifications, budget range — all of it welcome.</span>
        <textarea id="appoint-notes" name="notes" placeholder="The more you tell us, the better we dress you..."></textarea>
      </div>
 
      <!-- Submit -->
      <div class="appoint-submit-wrap">
        <button type="submit" class="appoint-submit-btn">
          <span>✦ Secure My Look</span>
        </button>
        <p class="appoint-submit-note">We'll be in touch within 24–48 hours via WhatsApp.</p>
      </div>
 
    </form>
    <?php endif; ?>
 
    </div> <!-- appoint-form-card -->
    </div> <!-- appoint-form-container -->
  </div> <!-- appoint-content-grid -->
</div> <!-- appoint-page-layout -->
 
<script>
  function showFileName(input) {
    const display = document.getElementById('appoint-file-name');
    if (input.files && input.files[0]) {
      display.textContent = '✓ ' + input.files[0].name;
    }
  }
</script>
 
 
<!-- Size Chart Modal -->
<div class="appoint-size-chart-modal" id="appoint-sizeChartModal">
  <div class="appoint-size-chart-inner">
    <button class="appoint-size-chart-close" onclick="document.getElementById('appoint-sizeChartModal').classList.remove('open')">&times;</button>
    <h2 class="appoint-size-chart-title">Size Guide</h2>
    <p class="appoint-size-chart-subtitle">All measurements are in inches. When between sizes, size up.</p>
 
    <div class="appoint-size-chart-tabs">
      <button class="appoint-size-tab active" onclick="switchTab('female', this)">Women</button>
      <button class="appoint-size-tab" onclick="switchTab('male', this)">Men</button>
      <button class="appoint-size-tab" onclick="switchTab('how', this)">How to Measure</button>
    </div>
 
    <!-- WOMEN -->
    <div class="appoint-size-chart-body active" id="appoint-tab-female">
      <div class="appoint-size-table-wrap">
        <table class="appoint-size-table">
          <thead>
            <tr>
              <th>Size</th>
              <th>Bust</th>
              <th>Waist</th>
              <th>Hips</th>
              <th>Shoulder</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>XS</td><td>32</td><td>26</td><td>35</td><td>13.5</td></tr>
            <tr><td>S</td><td>34</td><td>28</td><td>37</td><td>14</td></tr>
            <tr><td>M</td><td>36</td><td>30</td><td>39</td><td>14.5</td></tr>
            <tr><td>L</td><td>38</td><td>32</td><td>41</td><td>15</td></tr>
            <tr><td>XL</td><td>40</td><td>34</td><td>43</td><td>15.5</td></tr>
            <tr><td>XXL</td><td>42</td><td>36</td><td>45</td><td>16</td></tr>
          </tbody>
        </table>
      </div>
      <p class="appoint-size-note">All measurements are in inches. For sarees and lehengas, hip and waist measurements are most important. For blouses and tops, go by bust and shoulder.</p>
    </div>
 
    <!-- MEN -->
    <div class="appoint-size-chart-body" id="appoint-tab-male">
      <div class="appoint-size-table-wrap">
        <table class="appoint-size-table">
          <thead>
            <tr>
              <th>Size</th>
              <th>Chest</th>
              <th>Waist</th>
              <th>Hips</th>
              <th>Shoulder</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>S</td><td>36</td><td>30</td><td>36</td><td>16</td></tr>
            <tr><td>M</td><td>38</td><td>32</td><td>38</td><td>17</td></tr>
            <tr><td>L</td><td>40</td><td>34</td><td>40</td><td>17.5</td></tr>
            <tr><td>XL</td><td>42</td><td>36</td><td>42</td><td>18</td></tr>
            <tr><td>XXL</td><td>44</td><td>38</td><td>44</td><td>18.5</td></tr>
            <tr><td>3XL</td><td>46</td><td>40</td><td>46</td><td>19</td></tr>
          </tbody>
        </table>
      </div>
      <p class="appoint-size-note">All measurements are in inches. For sherwanis and kurtas, chest and shoulder are the key measurements. Waist is critical for fitted bottoms.</p>
    </div>
 
    <!-- HOW TO MEASURE -->
    <div class="appoint-size-chart-body" id="appoint-tab-how">
      <div class="appoint-body-figure-wrap">
 
        <!-- Female figure SVG -->
        <div class="appoint-body-figure">
          <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle cx="50" cy="18" r="12" fill="none" stroke="#C4825A" stroke-width="1.5"/>
            <!-- Neck -->
            <line x1="50" y1="30" x2="50" y2="40" stroke="#C4825A" stroke-width="1.5"/>
            <!-- Shoulders -->
            <path d="M28 45 Q50 38 72 45" fill="none" stroke="#C4825A" stroke-width="1.5"/>
            <!-- Bust line -->
            <path d="M30 62 Q50 57 70 62" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Body torso -->
            <path d="M28 45 L24 90 Q50 98 76 90 L72 45" fill="none" stroke="#C4825A" stroke-width="1.5"/>
            <!-- Waist line -->
            <path d="M26 78 Q50 72 74 78" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Hips line -->
            <path d="M20 105 Q50 100 80 105" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Skirt/legs -->
            <path d="M24 90 Q20 115 22 150 L40 150 L50 120 L60 150 L78 150 Q80 115 76 90 Q50 98 24 90Z" fill="none" stroke="#C4825A" stroke-width="1.5"/>
            <!-- Arms -->
            <path d="M28 45 L18 85" stroke="#C4825A" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M72 45 L82 85" stroke="#C4825A" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Shoulder arrow -->
            <text x="4" y="47" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Shoulder</text>
            <!-- Bust arrow -->
            <text x="2" y="64" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Bust</text>
            <!-- Waist arrow -->
            <text x="4" y="80" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Waist</text>
            <!-- Hip arrow -->
            <text x="6" y="108" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Hips</text>
          </svg>
          <span class="appoint-body-figure-label">Women</span>
        </div>
 
        <!-- Male figure SVG -->
        <div class="appoint-body-figure">
          <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle cx="50" cy="18" r="12" fill="none" stroke="#1A1208" stroke-width="1.5"/>
            <!-- Neck -->
            <line x1="50" y1="30" x2="50" y2="40" stroke="#1A1208" stroke-width="1.5"/>
            <!-- Shoulders broad -->
            <path d="M22 48 Q50 40 78 48" fill="none" stroke="#1A1208" stroke-width="1.5"/>
            <!-- Chest line -->
            <path d="M24 62 Q50 58 76 62" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Torso -->
            <path d="M22 48 L24 95 Q50 100 76 95 L78 48" fill="none" stroke="#1A1208" stroke-width="1.5"/>
            <!-- Waist line -->
            <path d="M25 82 Q50 78 75 82" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Hip line -->
            <path d="M23 100 Q50 96 77 100" fill="none" stroke="#B8956A" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Legs -->
            <path d="M24 95 Q22 120 24 155 L42 155 L50 115 L58 155 L76 155 Q78 120 76 95 Q50 100 24 95Z" fill="none" stroke="#1A1208" stroke-width="1.5"/>
            <!-- Arms -->
            <path d="M22 48 L14 90" stroke="#1A1208" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M78 48 L86 90" stroke="#1A1208" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Labels -->
            <text x="2" y="50" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Shoulder</text>
            <text x="2" y="64" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Chest</text>
            <text x="4" y="84" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Waist</text>
            <text x="6" y="102" font-size="5.5" fill="#8A7A6A" font-family="DM Sans">Hips</text>
          </svg>
          <span class="appoint-body-figure-label">Men</span>
        </div>
 
        <!-- Measurement instructions -->
        <div class="appoint-measure-list">
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Bust / Chest</span>
            <span class="appoint-measure-where">Fullest part of your chest</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Waist</span>
            <span class="appoint-measure-where">Narrowest part of your torso</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Hips</span>
            <span class="appoint-measure-where">Fullest part of your hips</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Shoulder</span>
            <span class="appoint-measure-where">Across the back, shoulder to shoulder</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Length</span>
            <span class="appoint-measure-where">Shoulder to where you want it to end</span>
          </div>
        </div>
 
      </div>
      <p class="appoint-size-note">Use a soft measuring tape. Keep it parallel to the floor. Measure over your innerwear, not over heavy clothing. When in doubt, share your measurements with us and we'll guide you.</p>
    </div>
 
  </div>
</div>
 
<script>
  function switchTab(tab, el) {
    document.querySelectorAll('.appoint-size-chart-body').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.appoint-size-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('appoint-tab-' + tab).classList.add('active');
    el.classList.add('active');
  }
 
  // Close on outside click
  document.getElementById('appoint-sizeChartModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
</script>
 
 
<script>
  // Set minimum delivery date to 15 days from today
  (function() {
    const dateInput = document.getElementById('appoint-required-by');
    const today = new Date();
    today.setDate(today.getDate() + 15);
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    dateInput.min = yyyy + '-' + mm + '-' + dd;
  })();
 
  function switchOutfitTab(tab, el) {
    document.querySelectorAll('.appoint-outfit-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.appoint-outfit-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('appoint-outfit-' + tab + '-panel').classList.add('active');
    el.classList.add('active');
  }
 
  function handleSizeType(val) {
    document.getElementById('appoint-standardSizeSelect').classList.toggle('visible', val === 'standard');
    document.getElementById('appoint-customMeasurements').classList.toggle('visible', val === 'custom');
  }
 
  function setGender(gender, btn) {
    document.querySelectorAll('.appoint-gender-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const inseam = document.getElementById('appoint-field-inseam');
    const bustLabel = document.querySelector('[name="measure_bust"]').previousElementSibling;
    if (gender === 'male') {
      inseam.style.display = 'block';
      bustLabel.textContent = 'Chest (inches)';
    } else {
      inseam.style.display = 'none';
      bustLabel.textContent = 'Bust / Chest (inches)';
    }
  }
</script>

<?php
$footerPath = __DIR__ . '/includes/footer.php';
if (file_exists($footerPath)) include $footerPath;
?>
