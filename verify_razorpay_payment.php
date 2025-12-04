<?php
// verify_razorpay_payment.php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/includes/razorpay_config.php';
    require_once __DIR__ . '/includes/db.php';        // must set $pdo (PDO)
    $config = require __DIR__ . '/includes/config.php';
    require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/includes/PHPMailer/SMTP.php';
    require_once __DIR__ . '/includes/PHPMailer/Exception.php';
    require_once __DIR__ . '/vendor/autoload.php';
} catch (Throwable $e) {
    http_response_code(500);
    error_log('verify include error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server include error', 'message' => $e->getMessage()]);
    exit;
}

use Razorpay\Api\Api;
use PHPMailer\PHPMailer\PHPMailer;

session_start();

function json_resp($code, $arr) {
    http_response_code($code);
    echo json_encode($arr);
    exit;
}

// helper to check for a column
function column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1");
        $q->execute(['t' => $table, 'c' => $column]);
        return (bool)$q->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

// Basic auth/post checks
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($uid <= 0) json_resp(403, ['success' => false, 'error' => 'Not logged in (session missing)']);

$rp_payment_id = trim($_POST['razorpay_payment_id'] ?? '');
$rp_order_id   = trim($_POST['razorpay_order_id'] ?? '');
$rp_signature  = trim($_POST['razorpay_signature'] ?? '');
$address_id    = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;

if ($rp_payment_id === '' || $rp_order_id === '' || $rp_signature === '') {
    json_resp(400, ['success' => false, 'error' => 'Missing payment parameters']);
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    error_log('verify: $pdo missing or invalid');
    json_resp(500, ['success' => false, 'error' => 'Server DB error']);
}

// Recompute expected amount from session cart (paise)
$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart) || empty($cart)) json_resp(400, ['success' => false, 'error' => 'Cart empty on server']);

$expectedPaise = 0;
foreach ($cart as $it) {
    $price = isset($it['price']) ? (float)$it['price'] : 0.0;
    $qty = isset($it['qty']) ? (int)$it['qty'] : 1;
    $expectedPaise += (int) round($price * 100) * $qty;
}

try {
    if (!defined('RAZORPAY_KEY_ID') || !defined('RAZORPAY_KEY_SECRET')) {
        error_log('verify: Razorpay keys not defined');
        json_resp(500, ['success' => false, 'error' => 'Payment gateway misconfigured']);
    }
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    // Verify signature
    $attributes = [
        'razorpay_order_id'   => $rp_order_id,
        'razorpay_payment_id' => $rp_payment_id,
        'razorpay_signature'  => $rp_signature
    ];
    $api->utility->verifyPaymentSignature($attributes);

    // Fetch payment
    $payment = $api->payment->fetch($rp_payment_id);
    $paymentArr = $payment->toArray();

    if (!isset($paymentArr['status']) || $paymentArr['status'] !== 'captured') {
        json_resp(400, ['success' => false, 'error' => 'Payment not captured', 'debug' => $paymentArr]);
    }

    $paidPaise = (int)($paymentArr['amount'] ?? 0);
    if ($paidPaise !== $expectedPaise) {
        error_log("verify: amount mismatch paid={$paidPaise} expected={$expectedPaise} user={$uid}");
        json_resp(400, ['success' => false, 'error' => 'Payment amount mismatch', 'paid' => $paidPaise, 'expected' => $expectedPaise]);
    }

    // --- Now replicate place_order.php logic: validate cart, lock stocks, compute totals, insert order + items ---
    $oi_has_variant = column_exists($pdo, 'order_items', 'variant_id');
    $oi_has_measurements = column_exists($pdo, 'order_items', 'measurements') || column_exists($pdo, 'order_items', 'meta');

    // fetch address snapshot (if provided)
    $address = null;
    if ($address_id > 0) {
        $ast = $pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
        $ast->execute(['id' => $address_id, 'uid' => $uid]);
        $address = $ast->fetch(PDO::FETCH_ASSOC);
    }
    // fetch user info
    $ust = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1");
    $ust->execute(['id' => $uid]);
    $user = $ust->fetch(PDO::FETCH_ASSOC);
    $customerEmail = $user['email'] ?? ($_SESSION['user_email'] ?? '');
    $customerName  = $user['name'] ?? ($_SESSION['user_name'] ?? 'Customer');

    // Begin transaction for order creation & stock updates
    $pdo->beginTransaction();

    $total = 0.0;
    $itemsForInsert = [];

    foreach ($cart as $key => $it) {
        $qty = max(1, (int)($it['qty'] ?? 1));
        $product_id = (int)($it['product_id'] ?? 0);
        $variant_id = isset($it['variant_id']) ? (int)$it['variant_id'] : 0;
        $requested_price = isset($it['price']) ? (float)$it['price'] : 0.0;
        $measurements = $it['measurements'] ?? null;

        if ($product_id <= 0) throw new Exception('Invalid product in cart.');

        if ($variant_id) {
            // variant chosen: lock variant row
            $vst = $pdo->prepare("SELECT id, stock, price, size, color, variant_sku FROM product_variants WHERE id = :vid AND product_id = :pid FOR UPDATE");
            $vst->execute(['vid' => $variant_id, 'pid' => $product_id]);
            $vrow = $vst->fetch(PDO::FETCH_ASSOC);
            if (!$vrow) throw new Exception("Variant not found for product {$product_id} (variant {$variant_id}).");
            if ((int)$vrow['stock'] < $qty) throw new Exception("Insufficient stock for variant (product {$product_id}).");

            if ($vrow['price'] !== null && $vrow['price'] !== '') $unitPrice = (float)$vrow['price'];
            else {
                $pst = $pdo->prepare("SELECT price FROM products WHERE id = :pid LIMIT 1");
                $pst->execute(['pid' => $product_id]);
                $prow = $pst->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $prow ? (float)$prow['price'] : (float)$requested_price;
            }

            $variant_label = trim(($vrow['size'] ?? '') . (($vrow['color'] ?? '') ? ' / '.$vrow['color'] : '') . (($vrow['variant_sku'] ?? '') ? ' ('.$vrow['variant_sku'].')' : ''));
            $has_measurements = false;
            $measurements = null;
        } else {
            // No variant: lock product row
            $pst = $pdo->prepare("SELECT id, stock, price, name FROM products WHERE id = :pid FOR UPDATE");
            $pst->execute(['pid' => $product_id]);
            $prow = $pst->fetch(PDO::FETCH_ASSOC);
            if (!$prow) throw new Exception("Product not found: {$product_id}");
            if ((int)$prow['stock'] < $qty) throw new Exception("Insufficient stock for product: {$product_id}");
            $unitPrice = (float)$prow['price'];
            $variant_label = '-';

            // normalize measurements
            $has_measurements = false;
            if (!empty($measurements) && is_array($measurements)) {
                $clean = [];
                foreach ($measurements as $k => $v) {
                    $vStr = is_scalar($v) ? trim((string)$v) : '';
                    if ($vStr !== '') $clean[$k] = $vStr;
                }
                if (!empty($clean)) {
                    $measurements = $clean;
                    $has_measurements = true;
                } else {
                    $measurements = null;
                    $has_measurements = false;
                }
            } else {
                $measurements = null;
                $has_measurements = false;
            }
        }

        $total += $unitPrice * $qty;

        $itemsForInsert[] = [
            'product_id' => $product_id,
            'variant_id' => $variant_id ?: null,
            'qty' => $qty,
            'price' => $unitPrice,
            'measurements' => $measurements,
            'has_measurements' => $has_measurements,
            'variant_label' => $variant_label,
            'name' => $it['name'] ?? '',
            // carry some variant metadata if present in cart
            'variant_size' => $it['variant_size'] ?? null,
            'variant_color' => $it['variant_color'] ?? null,
            'variant_sku' => $it['variant_sku'] ?? null,
        ];
    }

    // Insert orders row (shipping snapshot)
    $insOrder = $pdo->prepare("
      INSERT INTO orders (
        user_id, customer_name, customer_email, customer_phone,
        shipping_name, shipping_phone, shipping_address_line1, shipping_address_line2,
        shipping_city, shipping_state, shipping_postal_code, shipping_country,
        total, status, payment_method, razorpay_order_id, razorpay_payment_id, created_at
      ) VALUES (
        :user_id, :cust_name, :cust_email, :cust_phone,
        :ship_name, :ship_phone, :ship_line1, :ship_line2,
        :ship_city, :ship_state, :ship_postal, :ship_country,
        :total, 'paid', 'online', :rp_order_id, :rp_payment_id, NOW()
      )
    ");

    $insOrder->execute([
        'user_id' => $uid,
        'cust_name' => $customerName,
        'cust_email' => $customerEmail,
        'cust_phone' => $user['phone'] ?? '',
        'ship_name' => $address['full_name'] ?? ($user['name'] ?? ''),
        'ship_phone' => $address['phone'] ?? ($user['phone'] ?? ''),
        'ship_line1' => $address['address_line1'] ?? '',
        'ship_line2' => $address['address_line2'] ?? '',
        'ship_city' => $address['city'] ?? '',
        'ship_state' => $address['state'] ?? '',
        'ship_postal' => $address['postal_code'] ?? '',
        'ship_country' => $address['country'] ?? 'India',
        'total' => $total,
        'rp_order_id' => $rp_order_id,
        'rp_payment_id' => $rp_payment_id
    ]);

    $orderId = (int)$pdo->lastInsertId();
    if ($orderId <= 0) throw new Exception('Failed to obtain inserted order id');

    // prepare dynamic order_items insert depending on schema
    $oi_has_variant = column_exists($pdo, 'order_items', 'variant_id');
    $oi_has_measurements = column_exists($pdo, 'order_items', 'measurements') || column_exists($pdo, 'order_items', 'meta');

    if ($oi_has_variant && $oi_has_measurements) {
        $sql = "INSERT INTO order_items (order_id, product_id, variant_id, qty, price, measurements) VALUES (:oid,:pid,:vid,:qty,:price,:measurements)";
    } elseif ($oi_has_variant && !$oi_has_measurements) {
        $sql = "INSERT INTO order_items (order_id, product_id, variant_id, qty, price) VALUES (:oid,:pid,:vid,:qty,:price)";
    } elseif (!$oi_has_variant && $oi_has_measurements) {
        $sql = "INSERT INTO order_items (order_id, product_id, qty, price, measurements) VALUES (:oid,:pid,:qty,:price,:measurements)";
    } else {
        $sql = "INSERT INTO order_items (order_id, product_id, qty, price) VALUES (:oid,:pid,:qty,:price)";
    }
    $oit = $pdo->prepare($sql);

    // Insert items and decrement stock
    foreach ($itemsForInsert as $it) {
        $qty = (int)$it['qty'];
        $pid = (int)$it['product_id'];
        $vid = $it['variant_id'] ? (int)$it['variant_id'] : null;
        $price = (float)$it['price'];
        $meas = $it['measurements'] ?? null;

        // decrement stock
        if ($vid) {
            $u = $pdo->prepare("UPDATE product_variants SET stock = stock - :q WHERE id = :vid");
            $u->execute(['q' => $qty, 'vid' => $vid]);
        } else {
            $u = $pdo->prepare("UPDATE products SET stock = stock - :q WHERE id = :pid");
            $u->execute(['q' => $qty, 'pid' => $pid]);
        }

        // execute dynamic insert
        $params = [
            'oid' => $orderId,
            'pid' => $pid,
            'qty' => $qty,
            'price' => $price
        ];
        if ($oi_has_variant) $params['vid'] = $vid;
        if ($oi_has_measurements) $params['measurements'] = $meas ? json_encode($meas, JSON_UNESCAPED_UNICODE) : null;

        $oit->execute($params);
    }

    // Insert payment row if payments table exists
    try {
        $pdescStmt = $pdo->query("DESCRIBE payments");
        $pcols = $pdescStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        if (!empty($pcols)) {
            $paymentDesired = [
                'order_id' => $orderId,
                'provider' => 'razorpay',
                'provider_payment_id' => $rp_payment_id,
                'amount' => $paidPaise / 100,
                'currency' => $paymentArr['currency'] ?? 'INR',
                'raw_response' => json_encode($paymentArr)
            ];
            $pInsertCols = [];
            $pInsertParams = [];
            foreach ($pcols as $pcol) {
                if ($pcol === 'id' || $pcol === 'created_at') continue;
                if (array_key_exists($pcol, $paymentDesired)) {
                    $pInsertCols[] = $pcol;
                    $pInsertParams[$pcol] = $paymentDesired[$pcol];
                }
            }
            if (!empty($pInsertCols)) {
                $pcList = implode(',', $pInsertCols);
                $phList = ':' . implode(',:', array_keys($pInsertParams));
                $pSql = "INSERT INTO payments ($pcList) VALUES ($phList)";
                $pstmt = $pdo->prepare($pSql);
                $pstmt->execute($pInsertParams);
            }
        }
    } catch (Exception $ex) {
        // not fatal — continue
        error_log("verify: payments insert skipped/failure: " . $ex->getMessage());
    }

    $pdo->commit();

    // ------------------------------
    // Build email HTML (match place_order.php detail)
    // ------------------------------
    $html = "<h3>Order #{$orderId}</h3>";
    $html .= "<p><strong>Payment:</strong> Online (Paid) — Razorpay Order ID: <code>{$rp_order_id}</code> | Payment ID: <code>{$rp_payment_id}</code></p>";
    $html .= "<p><strong>Total:</strong> ₹" . number_format($total,2) . "</p>";
    $html .= "<h4>Shipping</h4>";
    $shipName = htmlspecialchars($address['full_name'] ?? $user['name'] ?? '');
    $shipPhone = htmlspecialchars($address['phone'] ?? $user['phone'] ?? '');
    $html .= "<p>{$shipName} — {$shipPhone}</p>";
    $addrText = '';
    if (!empty($address['address_line1'])) {
        $addrText .= $address['address_line1'] . ($address['address_line2'] ? "\n".$address['address_line2'] : '');
        $addrText .= "\n" . ($address['city'] ?? '') . ', ' . ($address['state'] ?? '') . ' - ' . ($address['postal_code'] ?? '');
        if (!empty($address['country'])) $addrText .= ', ' . $address['country'];
    }
    $html .= "<p>" . nl2br(htmlspecialchars($addrText)) . "</p>";

    $html .= "<h4>Items</h4>";
    $html .= "<table cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'><thead><tr style='text-align:left;'><th>Product</th><th>Variant / Measurements</th><th>Qty</th><th>Price</th></tr></thead><tbody>";
    foreach ($itemsForInsert as $it) {
        $prodName = htmlspecialchars($it['name'] ?: ('Product '.$it['product_id']));
        $variantLabel = trim((string)($it['variant_label'] ?? ''));
        $measText = '';

        if (!empty($it['has_measurements']) && is_array($it['measurements'])) {
            $parts = [];
            foreach ($it['measurements'] as $k=>$v) {
                $parts[] = htmlspecialchars($k) . ': ' . htmlspecialchars((string)$v);
            }
            $measText = implode(', ', $parts);
        }

        $variantOrMeas = $measText !== '' ? $measText : ($variantLabel !== '' ? $variantLabel : '-');
        $html .= "<tr><td style='border:1px solid #ddd;padding:6px;'>{$prodName}</td><td style='border:1px solid #ddd;padding:6px;'>{$variantOrMeas}</td><td style='border:1px solid #ddd;padding:6px;text-align:center;'>" . (int)$it['qty'] . "</td><td style='border:1px solid #ddd;padding:6px;text-align:right;'>₹" . number_format($it['price'],2) . "</td></tr>";
    }
    $html .= "</tbody></table>";

    $html .= "<p style='margin-top:12px;'>You can view your order here: <a href='" . (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . "://" . $_SERVER['HTTP_HOST'] . "order_view.php?id=" . rawurlencode($orderId) . "'>Order #{$orderId}</a></p>";
    $html .= "<p style='font-size:12px;color:#666;'>If you have any questions, contact us at (+91) 7456000222 or worldofinanna@gmail.com</p>";

    // ------------------------------
    // PHPMailer send (customer + admin)
    // ------------------------------
    @mkdir(__DIR__.'/logs', 0775, true);
    $adminEmail = $config['mail']['admin_email'] ?? ($config['mail']['from_email'] ?? 'worldofinanna@gmail.com');
    $fromEmail = $config['mail']['from_email'] ?? null;
    $fromName  = $config['mail']['from_name'] ?? 'Store';

    $setupMailer = function() use ($config, $fromEmail, $fromName) {
        $m = new PHPMailer(true);
        $m->isSMTP();
        $m->Host = $config['mail']['smtp_host'] ?? '';
        $m->SMTPAuth = true;
        $m->Username = $config['mail']['smtp_user'] ?? '';
        $m->Password = $config['mail']['smtp_pass'] ?? '';
        $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $m->Port = $config['mail']['smtp_port'] ?? 587;
        if ($fromEmail) $m->setFrom($fromEmail, $fromName);
        // verbose debug into logs when needed (comment/uncomment during debugging)
        // $m->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_CONNECTION;
        // $m->Debugoutput = function($str, $level) { @file_put_contents(__DIR__.'/logs/smtp_debug.log', date('c')." [debug] (level $level) $str\n", FILE_APPEND); };
        return $m;
    };

    if (!empty($customerEmail)) {
        try {
            $mail = $setupMailer();
            $mail->addAddress($customerEmail, $customerName ?: '');
            $mail->isHTML(true);
            $mail->Subject = "Order confirmation #{$orderId}";
            $mail->Body = "<p>Thank you for your order.</p>" . $html;
            $mail->AltBody = "Thank you for your order. Order #{$orderId} Total: ₹" . number_format($total,2) . "\nPayment: Online (Paid) - Razorpay Payment ID: {$rp_payment_id}";
            $mail->send();
            @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - mail to customer ({$customerEmail}) ok for order {$orderId}\n", FILE_APPEND);
        } catch (Exception $me) {
            error_log("verify: customer mail send failed for order {$orderId}: " . ($mail->ErrorInfo ?? $me->getMessage()));
            @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - mail to customer ({$customerEmail}) failed: ".($mail->ErrorInfo ?? $me->getMessage())."\n", FILE_APPEND);
        }
    } else {
        @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - no customer email for order {$orderId}\n", FILE_APPEND);
    }

    if (!empty($adminEmail)) {
        try {
            $mail2 = $setupMailer();
            $mail2->addAddress($adminEmail);
            $mail2->isHTML(true);
            $mail2->Subject = "New order received #{$orderId}";
            $mail2->Body = $html;
            $mail2->AltBody = "New order #{$orderId} Total: ₹" . number_format($total,2) . "\nPayment: Online (Paid) - Razorpay Payment ID: {$rp_payment_id}";
            $mail2->send();
            @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - mail to admin ({$adminEmail}) ok for order {$orderId}\n", FILE_APPEND);
        } catch (Exception $me2) {
            error_log("verify: admin mail send failed for order {$orderId}: " . ($mail2->ErrorInfo ?? $me2->getMessage()));
            @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - mail to admin ({$adminEmail}) failed: ".($mail2->ErrorInfo ?? $me2->getMessage())."\n", FILE_APPEND);
        }
    }

    // Clear cart and order session
    unset($_SESSION['cart']);
    unset($_SESSION['order']);

    json_resp(200, ['success' => true, 'order_id' => (string)$orderId]);

} catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('verify sig error: ' . $e->getMessage());
    json_resp(400, ['success' => false, 'error' => 'Signature verification failed', 'message' => $e->getMessage()]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('verify PDO error: ' . $e->getMessage());
    json_resp(500, ['success' => false, 'error' => 'Database error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('verify exception: ' . $e->getMessage());
    @file_put_contents(__DIR__.'/logs/payment_debug.log', date('c')." - verify exception: ".$e->getMessage()."\n", FILE_APPEND);
    json_resp(500, ['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
}
