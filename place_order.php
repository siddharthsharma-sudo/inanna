<?php
// place_order.php - robust version that supports missing variant_id column and includes custom-size measurements in email
// Updated: prefer variant over measurements; only show measurements when they are non-empty.

require_once __DIR__ . '/includes/config.php';
$config = require __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/includes/PHPMailer/SMTP.php';
require_once __DIR__ . '/includes/PHPMailer/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;

session_start();

// basic checks
$cart = $_SESSION['cart'] ?? [];
$orderSess = $_SESSION['order'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

if (empty($cart) || !$orderSess || !$userId) {
    header('Location: cart.php');
    exit;
}

$address_id = (int)$orderSess['address_id'];
$payment_method = $orderSess['payment_method'] ?? 'cod';

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

// fetch address snapshot
$ast = $pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
$ast->execute(['id' => $address_id, 'uid' => $userId]);
$address = $ast->fetch(PDO::FETCH_ASSOC);
if (!$address) {
    $_SESSION['checkout_error'] = 'Invalid address selected.';
    header('Location: checkout.php');
    exit;
}

// fetch user info
$ust = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1");
$ust->execute(['id' => $userId]);
$user = $ust->fetch(PDO::FETCH_ASSOC);
$customerEmail = $user['email'] ?? '';
$customerName = $user['name'] ?? 'Customer';

// determine admin recipient — prefer explicit admin_email in config
$adminEmail = $config['mail']['admin_email'] ?? null;
if (empty($adminEmail)) {
    $adminEmail = $config['mail']['from_email'] ?? null;
}

$oi_has_variant = column_exists($pdo, 'order_items', 'variant_id');
$oi_has_measurements = column_exists($pdo, 'order_items', 'measurements') || column_exists($pdo, 'order_items', 'meta');

try {
    $pdo->beginTransaction();

    // Validate cart, lock product/variants for update, compute total and collect item details for email
    $total = 0.0;
    $itemsForInsert = []; // will hold arrays with product_id, variant_id (or null), qty, price, measurements, has_measurements
    foreach ($cart as $key => $it) {
        $qty = max(1, (int)($it['qty'] ?? 1));
        $product_id = (int)($it['product_id'] ?? 0);
        $variant_id = isset($it['variant_id']) ? (int)$it['variant_id'] : 0;
        $requested_price = isset($it['price']) ? (float)$it['price'] : 0.0;
        $measurements = $it['measurements'] ?? null; // may be array or null

        if ($product_id <= 0) throw new Exception('Invalid product in cart.');

        if ($variant_id) {
            // If a variant is chosen, ignore any measurements (customer chose a standard size).
            $measurements = null;
            $has_measurements = false;

            // check variant
            $vst = $pdo->prepare("SELECT id, stock, price, size, color, variant_sku FROM product_variants WHERE id = :vid AND product_id = :pid FOR UPDATE");
            $vst->execute(['vid' => $variant_id, 'pid' => $product_id]);
            $vrow = $vst->fetch(PDO::FETCH_ASSOC);
            if (!$vrow) throw new Exception("Variant not found for product {$product_id} (variant {$variant_id}).");
            if ((int)$vrow['stock'] < $qty) throw new Exception("Insufficient stock for variant (product {$product_id}).");

            // determine unit price: variant.price if set, else product price else requested price fallback
            if ($vrow['price'] !== null && $vrow['price'] !== '') $unitPrice = (float)$vrow['price'];
            else {
                $pst = $pdo->prepare("SELECT price FROM products WHERE id = :pid LIMIT 1");
                $pst->execute(['pid' => $product_id]);
                $prow = $pst->fetch(PDO::FETCH_ASSOC);
                $unitPrice = $prow ? (float)$prow['price'] : (float)$requested_price;
            }

            $variant_label = trim(($vrow['size'] ?? '') . (($vrow['color'] ?? '') ? ' / '.$vrow['color'] : '') . (($vrow['variant_sku'] ?? '') ? ' ('.$vrow['variant_sku'].')' : ''));

        } else {
            // No variant selected: measurements may apply
            // Normalize and decide if measurements are meaningful (non-empty values)
            $has_measurements = false;
            if (!empty($measurements) && is_array($measurements)) {
                // keep only entries with non-empty values
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

            // product-level lock/check
            $pst = $pdo->prepare("SELECT id, stock, price, name FROM products WHERE id = :pid FOR UPDATE");
            $pst->execute(['pid' => $product_id]);
            $prow = $pst->fetch(PDO::FETCH_ASSOC);
            if (!$prow) throw new Exception("Product not found: {$product_id}");
            if ((int)$prow['stock'] < $qty) throw new Exception("Insufficient stock for product: {$product_id}");
            $unitPrice = (float)$prow['price'];
            $variant_label = '-';
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
        ];
    }

    // Insert orders row (shipping snapshot)
    $insOrder = $pdo->prepare("
      INSERT INTO orders (
        user_id, customer_name, customer_email, customer_phone,
        shipping_name, shipping_phone, shipping_address_line1, shipping_address_line2,
        shipping_city, shipping_state, shipping_postal_code, shipping_country,
        total, status, payment_method, created_at
      ) VALUES (
        :user_id, :cust_name, :cust_email, :cust_phone,
        :ship_name, :ship_phone, :ship_line1, :ship_line2,
        :ship_city, :ship_state, :ship_postal, :ship_country,
        :total, 'pending', :payment_method, NOW()
      )
    ");

    $insOrder->execute([
        'user_id' => $userId,
        'cust_name' => $customerName,
        'cust_email' => $customerEmail,
        'cust_phone' => $user['phone'] ?? '',
        'ship_name' => $address['full_name'],
        'ship_phone' => $address['phone'] ?? '',
        'ship_line1' => $address['address_line1'],
        'ship_line2' => $address['address_line2'],
        'ship_city' => $address['city'],
        'ship_state' => $address['state'],
        'ship_postal' => $address['postal_code'],
        'ship_country' => $address['country'] ?? 'India',
        'total' => $total,
        'payment_method' => $payment_method
    ]);

    $orderId = (int)$pdo->lastInsertId();

    // prepare dynamic order_items insert depending on schema
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

    $pdo->commit();

    // build email content from itemsForInsert and address
    $html = "<h3>Order #{$orderId}</h3>";
    $html .= "<p><strong>Total:</strong> ₹" . number_format($total,2) . "</p>";
    $html .= "<h4>Shipping</h4>";
    $html .= "<p>" . htmlspecialchars($address['full_name'] ?? '') . " — " . htmlspecialchars($address['phone'] ?? '') . "</p>";
    $html .= "<p>" . nl2br(htmlspecialchars(($address['address_line1'] ?? '') . ($address['address_line2'] ? "\n".$address['address_line2'] : '') . "\n" . ($address['city'] ?? '') . ', ' . ($address['state'] ?? '') . ' - ' . ($address['postal_code'] ?? ''))) . "</p>";

    $html .= "<h4>Items</h4>";
    // columns: Product | Variant/Measurements (only one shown) | Qty | Price
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

        // show measurements if present, otherwise show variant label
        $variantOrMeas = $measText !== '' ? $measText : ($variantLabel !== '' ? $variantLabel : '-');

        $html .= "<tr><td>{$prodName}</td><td>{$variantOrMeas}</td><td>" . (int)$it['qty'] . "</td><td>₹" . number_format($it['price'],2) . "</td></tr>";
    }
    $html .= "</tbody></table>";

    // --- Send customer email (if available) ---
    if (!empty($customerEmail)) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['mail']['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['mail']['smtp_user'];
            $mail->Password = $config['mail']['smtp_pass'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $config['mail']['smtp_port'];

            $fromEmail = $config['mail']['from_email'] ?? null;
            $fromName  = $config['mail']['from_name'] ?? null;
            if ($fromEmail) $mail->setFrom($fromEmail, $fromName ?: 'Store');
            $mail->addAddress($customerEmail, $customerName);
            $mail->isHTML(true);
            $mail->Subject = "Order confirmation #{$orderId}";
            $mail->Body = "<p>Thank you for your order.</p>" . $html;
            $mail->AltBody = "Thank you for your order. Order #{$orderId} Total: ₹" . number_format($total,2);
            $mail->send();
        } catch (Exception $e) {
            error_log("Order email to customer failed: " . ($mail->ErrorInfo ?? $e->getMessage()));
        }
    }

    // --- Send admin email once (to adminEmail if configured) ---
    if (!empty($adminEmail)) {
        try {
            $mail2 = new PHPMailer(true);
            $mail2->isSMTP();
            $mail2->Host = $config['mail']['smtp_host'];
            $mail2->SMTPAuth = true;
            $mail2->Username = $config['mail']['smtp_user'];
            $mail2->Password = $config['mail']['smtp_pass'];
            $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail2->Port = $config['mail']['smtp_port'];

            $fromEmail = $config['mail']['from_email'] ?? null;
            $fromName  = $config['mail']['from_name'] ?? null;
            if ($fromEmail) $mail2->setFrom($fromEmail, $fromName ?: 'Store');
            $mail2->addAddress($adminEmail);
            $mail2->isHTML(true);
            $mail2->Subject = "New order received #{$orderId}";
            $mail2->Body = $html;
            $mail2->AltBody = "New order #{$orderId} Total: ₹" . number_format($total,2);
            $mail2->send();
        } catch (Exception $e) {
            error_log("Order email to admin failed: " . ($mail2->ErrorInfo ?? $e->getMessage()));
        }
    }

    // clear cart & session order
    unset($_SESSION['cart']);
    unset($_SESSION['order']);

    header('Location: order_view.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Place order failed: " . $e->getMessage());
    $_SESSION['checkout_error'] = 'Order failed: ' . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
