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
        __DIR__ . '/includes/phpmailer/src',
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
        // create table if not exists
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS appointments (
                id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(100) DEFAULT NULL,
                service VARCHAR(100) NOT NULL,
                design TEXT,
                appt_date DATE NOT NULL,
                appt_time VARCHAR(64) NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(64) NOT NULL,
                note TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_slot (appt_date, appt_time)
            ) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
        ");
    } catch (Exception $e) {
        $pdo = null;
        $pdoErrorNote = $e->getMessage();
    }
}
$csvPath = __DIR__ . '/appointments_storage.csv';

// ----------------------
// Process form
// ----------------------
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $categorySlug = trim(strtolower($_POST['category'] ?? ''));
    $categoryLabel = $categories[$categorySlug] ?? null;

    $service = 'Custom Clothing';
    $custom_service = trim($_POST['custom_service'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // basic validation
    if ($categorySlug === '' || $categoryLabel === null) $errors[] = 'Please select a valid category.';
    if ($custom_service === '') $errors[] = 'Please describe your custom clothing request (fabric, style, size, color).';
    $dobj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dobj) $errors[] = 'Please pick a valid date.';
    else {
        $d0 = (clone $dobj)->setTime(0,0,0);
        $min = (new DateTime())->modify("+{$minDaysAhead} days")->setTime(0,0,0);
        $max = (new DateTime())->modify("+{$maxDaysAhead} days")->setTime(0,0,0);
        if ($d0 < $min || $d0 > $max) $errors[] = "Date must be between {$min->format('Y-m-d')} and {$max->format('Y-m-d')}.";
    }
    if ($time === '' || !in_array($time, $timeSlots, true)) $errors[] = 'Please choose a valid time slot.';
    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($phone === '') $errors[] = 'Please enter a phone number.';

    if (empty($errors)) {
        $slotTaken = false;

        // Try to insert (DB unique constraint protects double-booking)
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO appointments
                  (category, service, design, appt_date, appt_time, name, email, phone, note)
                  VALUES (:category, :service, :design, :date, :time, :name, :email, :phone, :note)
                ");
                $stmt->execute([
                    ':category' => $categoryLabel,
                    ':service' => $service,
                    ':design' => $custom_service,
                    ':date' => $date,
                    ':time' => $time,
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':note' => $note,
                ]);
                $savedId = $pdo->lastInsertId();
            } catch (PDOException $e) {
                if ($e->getCode() === '23000' || stripos($e->getMessage(), 'Duplicate') !== false) {
                    $slotTaken = true;
                } else {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        } else {
            // CSV fallback with exclusive lock
            $lockFp = @fopen($csvPath, 'c+');
            if ($lockFp === false) {
                $errors[] = 'Unable to open storage file for bookings.';
            } else {
                try {
                    if (!flock($lockFp, LOCK_EX)) {
                        $errors[] = 'Unable to lock booking storage — try again.';
                    } else {
                        fseek($lockFp, 0);
                        $found = false;
                        while (($line = fgets($lockFp)) !== false) {
                            $line = trim($line);
                            if ($line === '') continue;
                            $parts = str_getcsv($line);
                            // CSV layout index: 2=date, 3=time in this fallback
                            if (isset($parts[2]) && isset($parts[3])) {
                                if ($parts[2] === $date && $parts[3] === $time) { $found = true; break; }
                            }
                        }
                        if ($found) $slotTaken = true;
                        else {
                            $id = time() . rand(100,999);
                            $created_at = (new DateTime())->format('c');
                            $csvLine = [
                                $id,
                                $categoryLabel,
                                $date,
                                $time,
                                $name,
                                $email,
                                $phone,
                                $note,
                                $created_at,
                                $service,
                                $custom_service
                            ];
                            fseek($lockFp, 0, SEEK_END);
                            fwrite($lockFp, implode(',', array_map(function($v){
                                return '"' . str_replace('"', '""', (string)$v) . '"';
                            }, $csvLine)) . PHP_EOL);
                            fflush($lockFp);
                        }
                        flock($lockFp, LOCK_UN);
                    }
                } finally {
                    fclose($lockFp);
                }
            }
        }

        if ($slotTaken) {
            $errors[] = 'Sorry — that date and time slot has already been booked. Please choose a different slot.';
        } else {
            // send admin and user emails (adminRecipient from config)
            $adminRecipient = $mailCfg['smtp_user'] ?? ($mailCfg['from_email'] ?? 'worldofinanna@gmail.com');

            $detailsHtml = "<h2>New Appointment Booking — Custom Clothing</h2>";
            $detailsHtml .= "<p><strong>Category:</strong> " . h($categoryLabel) . "</p>";
            $detailsHtml .= "<p><strong>Design Description:</strong><br>" . nl2br(h($custom_service)) . "</p>";
            $detailsHtml .= "<p><strong>Date:</strong> " . h($date) . "</p>";
            $detailsHtml .= "<p><strong>Time:</strong> " . h($time) . "</p>";
            $detailsHtml .= "<p><strong>Name:</strong> " . h($name) . "</p>";
            $detailsHtml .= "<p><strong>Email:</strong> " . h($email) . "</p>";
            $detailsHtml .= "<p><strong>Phone:</strong> " . h($phone) . "</p>";
            $detailsHtml .= "<p><strong>Note:</strong><br/>" . nl2br(h($note)) . "</p>";

            $userSubject = "Your appointment is confirmed — " . h($date) . " " . h($time);
            $userHtml = "<h2>Your appointment is confirmed</h2>";
            $userHtml .= "<p>Thanks <strong>" . h($name) . "</strong>, your appointment for <strong>Custom Clothing</strong> has been booked.</p>";
            $userHtml .= $detailsHtml;
            $userHtml .= "<p style='color:#6b7280;font-size:13px'>If you need to change or cancel, reply to this email.</p>";

            $mailSentAdmin = false;
            $mailSentUser = false;

            if ($phpmailer_available) {
                try {
                    $m = new \PHPMailer\PHPMailer\PHPMailer(true);
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
                    $m->Subject = "New Appointment: Custom Clothing — {$date} {$time}";
                    $m->Body = $detailsHtml;
                    $m->AltBody = strip_tags($detailsHtml);
                    $m->send();
                    $mailSentAdmin = true;

                    // user (new instance)
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
                    $errors[] = 'PHPMailer error while sending emails: ' . $pex->getMessage();
                } catch (\Exception $ex) {
                    $errors[] = 'Mail sending error: ' . $ex->getMessage();
                }
            } else {
                $from = ($mailCfg['from_name'] ?? 'Inanna') . " <" . ($mailCfg['from_email'] ?? $adminRecipient) . ">";
                $headers = "From: {$from}\r\nReply-To: {$adminRecipient}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
                if (@mail($adminRecipient, "New Appointment: Custom Clothing — {$date} {$time}", $detailsHtml, $headers)) $mailSentAdmin = true;
                else $errors[] = 'Unable to send admin email via mail().';
                if (@mail($email, $userSubject, $userHtml, $headers)) $mailSentUser = true;
                else $errors[] = 'Unable to send confirmation email to the user via mail().';
            }

            if ($mailSentAdmin || $mailSentUser) $success = true;
        }
    }
}

// Attempt to include header/footer if they exist (keeps design)
$headerPath = __DIR__ . '/includes/header.php';
if (file_exists($headerPath)) include $headerPath;
?>

<!-- ---------- DESIGN/CSS from your preferred layout (keeps structure + visuals) ---------- -->
<style>
:root{
  --bg1: #fffaf0; /* warm parchment */
  --bg2: #f3f7ff;
  --accent:#c026d3;
  --accent-2:#f97316;
  --muted:#6b7280;
  --card:#ffffff;
  --shadow: 0 14px 40px rgba(16,24,40,0.08);
}
body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,Arial;color:#0f172a;background: linear-gradient(180deg, #1a1a1d, #272330, #332f45);margin:0;padding-top:var(--top-offset,0px)}
.container-appt{max-width:1040px;margin:28px auto;padding:18px}
.bespoke-card{background:var(--card);border-radius:18px;padding:28px;box-shadow:var(--shadow);position:relative;overflow:visible}
.top-bar{display:flex;gap:16px;align-items:center;margin-bottom:18px}
.logo-patch{display:flex;flex-wrap:wrap; align-items:center;gap:12px}
.logo-mark{width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:20px;box-shadow:0 6px 18px rgba(192,38,211,0.12)}
.title h1{margin:0;font-size:22px}
.title p{margin:4px 0 0;color:var(--muted)}
.main-grid{display:grid;grid-template-columns:1fr 380px;gap:28px;align-items:start}
@media(max-width:980px){.main-grid{grid-template-columns:1fr}}
.form-area{padding:6px 0}
.panel{background:linear-gradient(180deg,#fff,#fbfdff);border-radius:12px;padding:18px;border:1px solid #f1f5f9;margin-bottom:18px}
.label-hero{display:flex;justify-content:space-between;align-items:center}
.heading{font-weight:700;margin:0 0 6px}
.sub{color:var(--muted);font-size:13px;margin:0}
.field{margin-top:12px}
label{display:block;font-size:13px;color:var(--muted);margin-bottom:6px}
textarea, input[type="text"], input[type="email"], input[type="tel"], input[type="date"], select{
  width:100%;padding:12px;border-radius:10px;border:1px dashed #f1e6ff;background:#fff;font-size:14px;outline:none;transition:all .12s;
}
textarea{min-height:140px;resize:vertical;border-radius:12px}
input:focus,textarea:focus,select:focus{box-shadow:0 10px 30px rgba(192,38,211,0.06);border-color:rgba(192,38,211,0.2)}
.small{font-size:13px;color:var(--muted);margin-top:6px}
.design-box{border:1px solid #f0e7f9;padding:12px;border-radius:12px;background:linear-gradient(180deg,#fff,#fffaf8)}
.design-area{display:flex;gap:12px;align-items:flex-start}
.sketch{width:84px;height:84px;border-radius:10px;background:linear-gradient(135deg,#fff6f0,#fff0ff);display:flex;align-items:center;justify-content:center;border:1px solid #fde8d9}
.sketch svg{width:54px;height:54px;opacity:0.95}
.time-select{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.slot{padding:8px 10px;border-radius:10px;border:1px solid #f1f3f5;background:#fff;cursor:pointer;font-weight:700;font-size:13px}
.slot.selected{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;border-color:transparent;box-shadow:0 6px 18px rgba(192,38,211,0.12)}
.actions{display:flex;justify-content:space-between;gap:12px;margin-top:18px}
.btn-primary{background:linear-gradient(90deg,var(--accent),var(--accent-2));color:#fff;padding:12px 16px;border-radius:12px;border:0;font-weight:800;cursor:pointer}
.btn-ghost{background:transparent;border:1px solid #f1f3f5;padding:10px 14px;border-radius:12px;cursor:pointer;color:var(--muted)}
.info-card{background:linear-gradient(180deg,#fff,#fcfbff);border-radius:12px;padding:18px;border:1px solid #f1f3f9}
.info-card h3{margin:0 0 8px}
.info-card p{margin:0;color:var(--muted)}
.error{background:#fff2f3;color:#7f1d1d;padding:10px;border-radius:8px;margin-bottom:12px}
.success{background:#eefcf3;color:#065f46;padding:10px;border-radius:8px;margin-bottom:12px}
</style>

<div class="container-appt">
  <div class="bespoke-card">
    <div class="top-bar">
      <div class="logo-patch">
        <div class="logo-mark">IN</div>
        <div class="title">
          <h1>Inanna Bespoke — Custom Clothing</h1>
          <p>Create your garment. We'll translate your idea into a tailored appointment.</p>
        </div>
      </div>
      <div style="margin-left:auto;color:var(--muted);font-size:13px;">
        Bookings are emailed to <strong><?php echo h($mailCfg['smtp_user'] ?? 'worldofinanna@gmail.com'); ?></strong>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="error"><?php echo implode('<br>', array_map('h', $errors)); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="success">Thanks — your appointment request was sent successfully. We'll reply soon.</div>
    <?php endif; ?>

    <div class="main-grid">
      <!-- LEFT: form -->
      <div class="form-area">
        <form method="post" id="apptForm" novalidate>
          <!-- Design / Service block -->
          <div class="panel design-box">
            <div class="label-hero">
              <div>
                <div class="heading">Service: <span style="color:var(--accent);">Custom Clothing</span></div>
                <div class="sub">Only one service — describe your clothing design in detail</div>
              </div>
              <div class="small">45 min slots · 9:00–17:45</div>
            </div>

            <div class="design-area" style="margin-top:12px">
              <div class="sketch" aria-hidden="true">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M8 44c0-8 8-12 14-12s8 6 14 6 12-6 18-6" stroke="#c026d3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M18 18c0 6 6 10 14 10s14-4 14-10" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="32" cy="10" r="3" fill="#c026d3"/>
                </svg>
              </div>
              <div style="flex:1">
                <label for="category">Category</label>

                <!-- Category select (uses slugs as values). -->
                <select id="category" name="category" required class="styled-select">
                  <option value="" disabled <?php echo (old('category') === '') ? 'selected' : ''; ?>>-- choose category --</option>
                  <?php foreach ($categories as $slug => $label): ?>
                    <option value="<?php echo h($slug); ?>" <?php echo (old('category') === $slug) ? 'selected' : ''; ?>><?php echo h($label); ?></option>
                  <?php endforeach; ?>
                </select>

                <label for="custom_service" style="margin-top:12px">Describe your design (fabric, cut, measurements, color, reference links)</label>
                <textarea id="custom_service" name="custom_service" placeholder="Example: Silk kurta, A-line, ivory with hand-embroidered neckline, size M..."><?php echo old('custom_service'); ?></textarea>
                <div class="small">Tip: include measurements or upload links to reference images in the notes below.</div>
              </div>
            </div>
          </div>

          <!-- Date & time panel -->
          <div class="panel">
            <div class="heading" style="font-size:16px">Choose date & time</div>
            <div class="small" style="margin-top:6px">Pick a convenient date and one of the 45-minute slots.</div>

            <div class="field" style="margin-top:12px">
              <label for="date">Appointment date</label>
              <input type="date" id="date" name="date" min="<?php echo h($minDate); ?>" max="<?php echo h($maxDate); ?>" value="<?php echo old('date'); ?>" required>
            </div>

            <div class="field">
              <label for="time">Time slot</label>
              <select id="time" name="time" required>
                <option value="">-- choose a time --</option>
                <?php foreach ($timeSlots as $s): ?>
                  <option value="<?php echo h($s); ?>" <?php echo (old('time')===$s)?'selected':''; ?>><?php echo h($s); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Details panel -->
          <div class="panel">
            <div class="heading" style="font-size:16px">Your details</div>

            <div class="field">
              <label for="name">Full name</label>
              <input id="name" name="name" type="text" value="<?php echo old('name'); ?>" required>
            </div>

            <div class="row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
              <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?php echo old('email'); ?>" required>
              </div>
              <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="tel" value="<?php echo old('phone'); ?>" required>
              </div>
            </div>

            <div class="field">
              <label for="note">Notes or reference URLs (optional)</label>
              <textarea id="note" name="note" placeholder="Add image links, sizing notes, or other info"><?php echo old('note'); ?></textarea>
            </div>

            <div class="actions">
              <button type="button" class="btn-ghost" onclick="resetForm()">Reset</button>
              <button type="submit" name="final_submit" class="btn-primary">Confirm appointment</button>
            </div>
          </div>
        </form>
      </div>

      <!-- RIGHT: info and mini-preview -->
      <aside class="info-card" aria-labelledby="info-heading">
  <h3 id="info-heading">What to expect</h3>

  <p style="margin-top:8px;color:var(--muted)">
    You'll receive an email with the booking details and a confirmation copy. At your appointment we will inspect fabric, take measurements, confirm the design & timeline. Paste reference image links in the notes field or email them to us beforehand.
  </p>

  <div style="margin-top:14px;display:flex;gap:12px;align-items:flex-start">
    <div style="flex:1">
      <strong>Opening hours</strong>
      <div class="small" style="margin-top:6px;color:var(--muted)">
        Mon — Sat: 9:00 — 18:00<br>
        Sun: Closed
      </div>

      <div style="margin-top:12px">
        <strong>Location</strong>
        <div class="small" style="margin-top:6px;color:var(--muted)">
          Studio Inanna — Jhajra, Dehradun 248007
        </div>
      </div>
    </div>

    <div style="width:110px;text-align:center">
      <strong>Quick facts</strong>
      <div class="small" style="margin-top:6px;color:var(--muted);text-align:left">
        • 45-min appointments<br>
        • Fittings by appointment<br>
        • Appointments online only
      </div>
    </div>
  </div>

  <hr style="border:none;border-top:1px solid #f1f3f7;margin:12px 0;">

  <div>
    <strong>Prepare for your appointment</strong>
    <ul style="margin:8px 0 0 18px;color:var(--muted);font-size:13px;line-height:1.45">
      <li>Bring reference images or links (paste in notes).</li>
      <li>Know your usual sizes (chest/waist/hips/height).</li>
      <li>If fabric is available, bring a swatch—otherwise we’ll recommend options.</li>
    </ul>
  </div>

  <div style="margin-top:12px">
    <strong>Cancellation & reschedule</strong>
    <div class="small" style="margin-top:6px;color:var(--muted)">
      Free reschedule up to <strong>24 hours</strong> before your slot. To cancel or change, reply to your confirmation email or contact us at <a href="mailto:<?php echo h($mailCfg['smtp_user'] ?? 'worldofinanna@gmail.com'); ?>" style="color:inherit;text-decoration:underline;"><?php echo h($mailCfg['smtp_user'] ?? 'worldofinanna@gmail.com'); ?></a>.
    </div>
  </div>

  <hr style="border:none;border-top:1px solid #f1f3f7;margin:12px 0;">

  <div>
    <strong>Frequently asked</strong>
    <details style="margin-top:8px;color:var(--muted);font-size:13px">
      <summary style="cursor:pointer;font-weight:700">How long does a custom piece take?</summary>
      <div style="margin-top:6px">Typically 1–3 weeks depending on fabric & complexity. We’ll confirm during your appointment.</div>
    </details>

    <details style="margin-top:8px;color:var(--muted);font-size:13px">
      <summary style="cursor:pointer;font-weight:700">Can I bring my own fabric?</summary>
      <div style="margin-top:6px">Yes—bring a swatch so we can advise exact quantities and suitability.</div>
    </details>

    <details style="margin-top:8px;color:var(--muted);font-size:13px">
      <summary style="cursor:pointer;font-weight:700">What payment methods?</summary>
      <div style="margin-top:6px">We accept cash, UPI and card payments at pickup. A deposit may be requested for some projects.</div>
    </details>
  </div>

  <hr style="border:none;border-top:1px solid #f1f3f7;margin:12px 0;">

  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <div style="flex:1">
      <strong>Selected preview</strong>
      <div id="miniSummary" class="small" style="margin-top:8px;color:var(--muted)">No preview yet — fill the form to preview here.</div>
    </div>

    <div style="display:flex;gap:8px;align-items:center">
      <!-- Simple image placeholders (inline SVG) -->
      <div style="width:56px;height:56px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;border:1px solid #f1f3f5">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="20" height="14" x="2" y="5" rx="2" stroke="#d8c2e6" stroke-width="1.5"/></svg>
      </div>
      <div style="width:56px;height:56px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;border:1px solid #f1f3f5">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7h16M4 12h10M4 17h16" stroke="#f9d6c1" stroke-width="1.5" stroke-linecap="round"/></svg>
      </div>
      <div style="width:56px;height:56px;border-radius:8px;background:#fff;display:flex;align-items:center;justify-content:center;border:1px solid #f1f3f5">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="10" r="3" stroke="#e7d1ff" stroke-width="1.5"/><path d="M4 20c2-4 6-6 8-6s6 2 8 6" stroke="#e7d1ff" stroke-width="1.3" stroke-linecap="round"/></svg>
      </div>
    </div>
  </div>

  <div style="margin-top:12px;font-size:12px;color:var(--muted)">
    <strong>Where we send bookings</strong>
    <div style="margin-top:6px"><?php echo h($mailCfg['smtp_user'] ?? 'worldofinanna@gmail.com'); ?></div>
  </div>
</aside>

    </div>
  </div>
</div>

<!-- ---------- JS: preview, reset, header offset, category fallback (keeps dropdown safe) ---------- -->
<script>
/* Header overlap fix */
(function(){
  function setOffset(){
    var headerSelectors = ['header', '.site-header', '#header', '.header', '.navbar', '.topbar'];
    var hEl = null;
    for(var i=0;i<headerSelectors.length;i++){
      var el = document.querySelector(headerSelectors[i]);
      if(el && el.offsetHeight > 0){
        hEl = el; break;
      }
    }
    var offset = 0;
    if(hEl) offset = hEl.offsetHeight + 10;
    document.documentElement.style.setProperty('--top-offset', offset + 'px');
  }
  setOffset();
  window.addEventListener('resize', setOffset);
  setTimeout(setOffset,400);
})();

/* preview + reset: preview shows option text (label) instead of slug */
function esc(s){ return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;'); }
function updateMiniSummary(){
  var catEl = document.getElementById('category');
  var catLabel = '';
  if (catEl && catEl.selectedOptions && catEl.selectedOptions[0]) catLabel = catEl.selectedOptions[0].text;
  var cs = document.getElementById('custom_service').value || '';
  var date = document.getElementById('date').value || '';
  var time = document.getElementById('time').value || '';
  var name = document.getElementById('name').value || '';
  var lines = [];
  if(catLabel) lines.push(catLabel);
  if(cs) lines.push('Design: ' + cs.substring(0,60) + (cs.length>60?'…':''));
  if(date) lines.push('Date: ' + date);
  if(time) lines.push('Time: ' + time);
  if(name) lines.push('Name: ' + name);
  var el = document.getElementById('miniSummary');
  if (el) el.innerHTML = lines.length ? esc(lines.join(' • ')) : 'No preview yet — fill the form to preview here.';
}
['category','custom_service','date','time','name'].forEach(function(id){
  var el = document.getElementById(id);
  if(el) { el.addEventListener('input', updateMiniSummary); el.addEventListener('change', updateMiniSummary); }
});
updateMiniSummary();

function resetForm(){
  if(confirm('Reset the appointment form?')){
    document.getElementById('apptForm').reset();
    updateMiniSummary();
  }
}

/* Debug + client fallback: log options and populate if empty (helps diagnose your "no options" issue) */
(function(){
  var catEl = document.getElementById('category');
  if (!catEl) return;
  try {
    console.log('Category options:', [...catEl.options].map(o => ({v: o.value, t: o.text, d: o.disabled})));
  } catch(e){ console.log('Category options: (error)', e); }

  // If the select has only the disabled placeholder (or length <=1), populate from a client-side list
  if (catEl && catEl.options.length <= 1) {
    var fallback = [
      {v:'co-ord-set', t:'Co-ord Set'},
      {v:'dresses', t:'Dresses'},
      {v:'shirts', t:'Shirts'},
      {v:'pants', t:'Pants'},
      {v:'suits', t:'Suits'},
      {v:'saree', t:'Saree'}
    ];
    fallback.forEach(function(it){
      var opt = document.createElement('option');
      opt.value = it.v; opt.text = it.t;
      catEl.appendChild(opt);
    });
    // restore preselected if server set it
    try {
      var pre = <?php echo json_encode((string)old('category'), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
      if (pre) catEl.value = pre;
    } catch(e){}
    console.log('Category select seemed empty — populated fallback options client-side.');
  }
})();
</script>

<?php
// include footer if available
$footerPath = __DIR__ . '/includes/footer.php';
if (file_exists($footerPath)) include $footerPath;
?>
