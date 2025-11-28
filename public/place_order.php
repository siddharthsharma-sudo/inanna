<?php
// public/place_order.php (updated) - saves shipping snapshot & payment_method
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

// fetch address snapshot
$ast = $pdo->prepare("SELECT * FROM addresses WHERE id = :id AND user_id = :uid LIMIT 1");
$ast->execute(['id' => $address_id, 'uid' => $userId]);
$address = $ast->fetch();
if (!$address) {
    $_SESSION['checkout_error'] = 'Invalid address selected.';
    header('Location: checkout.php');
    exit;
}

// fetch user email & name
$ust = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = :id LIMIT 1");
$ust->execute(['id' => $userId]);
$user = $ust->fetch();
$customerEmail = $user['email'] ?? '';
$customerName = $user['name'] ?? '';

try {
    $pdo->beginTransaction();

    $total = 0;
    // validate stock and compute total; lock rows for update
    foreach ($cart as $key => $it) {
        $qty = max(1, (int)$it['qty']);
        $product_id = (int)$it['product_id'];
        $variant_id = (int)$it['variant_id'];

        if ($variant_id) {
            $vst = $pdo->prepare("SELECT id, stock, price FROM product_variants WHERE id = :vid AND product_id = :pid FOR UPDATE");
            $vst->execute(['vid' => $variant_id, 'pid' => $product_id]);
            $vrow = $vst->fetch();
            if (!$vrow) throw new Exception("Variant not found for product {$product_id}.");
            if ((int)$vrow['stock'] < $qty) throw new Exception("Insufficient stock for selected variant.");
            $unitPrice = ($vrow['price'] !== null && $vrow['price'] !== '') ? (float)$vrow['price'] : (float)$it['price'];
        } else {
            $pst = $pdo->prepare("SELECT id, stock, price FROM products WHERE id = :pid FOR UPDATE");
            $pst->execute(['pid' => $product_id]);
            $prow = $pst->fetch();
            if (!$prow) throw new Exception("Product not found: {$product_id}");
            if ((int)$prow['stock'] < $qty) throw new Exception("Insufficient stock for product: {$product_id}");
            $unitPrice = (float)$prow['price'];
        }

        $total += $unitPrice * $qty;
    }

    // insert order with shipping snapshot and payment_method
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

    // insert items and reduce stock
    $oit = $pdo->prepare("INSERT INTO order_items (order_id, product_id, variant_id, qty, price) VALUES (:oid,:pid,:vid,:qty,:price)");
    foreach ($cart as $key => $it) {
        $qty = max(1,(int)$it['qty']);
        $product_id = (int)$it['product_id'];
        $variant_id = (int)$it['variant_id'];
        $unitPrice = (float)$it['price'];

        // decrease stock
        if ($variant_id) {
            $pdo->prepare("UPDATE product_variants SET stock = stock - :q WHERE id = :vid")->execute(['q' => $qty, 'vid' => $variant_id]);
        } else {
            $pdo->prepare("UPDATE products SET stock = stock - :q WHERE id = :pid")->execute(['q' => $qty, 'pid' => $product_id]);
        }

        $oit->execute([
            'oid' => $orderId,
            'pid' => $product_id,
            'vid' => $variant_id ? $variant_id : null,
            'qty' => $qty,
            'price' => $unitPrice
        ]);
    }

    $pdo->commit();

    // clear cart & session order
    unset($_SESSION['cart']);
    unset($_SESSION['order']);

    // send emails (customer + admin) - reuse PHPMailer like earlier
    // prepare items HTML
    $itemsStmt = $pdo->prepare("SELECT oi.*, p.name AS product_name, pv.size, pv.color, pv.variant_sku FROM order_items oi LEFT JOIN products p ON p.id = oi.product_id LEFT JOIN product_variants pv ON pv.id = oi.variant_id WHERE oi.order_id = :oid");
    $itemsStmt->execute(['oid' => $orderId]);
    $items = $itemsStmt->fetchAll();

    $html = "<h3>Order #{$orderId}</h3>";
    $html .= "<p><strong>Total:</strong> ₹" . number_format($total,2) . "</p>";
    $html .= "<p><strong>Shipping to:</strong> " . htmlspecialchars($address['full_name']) . " — " . htmlspecialchars($address['phone']) . "</p>";
    $html .= "<p>" . nl2br(htmlspecialchars($address['address_line1'] . ($address['address_line2'] ? "\n".$address['address_line2'] : '') . "\n".$address['city'].', '.$address['state'].' - '.$address['postal_code'])) . "</p>";
    $html .= "<table cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'><thead><tr><th>Product</th><th>Variant</th><th>Qty</th><th>Price</th></tr></thead><tbody>";
    foreach ($items as $it) {
        $variantLabel = trim(($it['size'] ?? '') . ($it['color'] ? ' / '.$it['color'] : '') . ($it['variant_sku'] ? ' ('.$it['variant_sku'].')' : ''));
        $html .= "<tr><td>" . htmlspecialchars($it['product_name']) . "</td><td>" . htmlspecialchars($variantLabel ?: '-') . "</td><td>" . (int)$it['qty'] . "</td><td>₹" . number_format($it['price'],2) . "</td></tr>";
    }
    $html .= "</tbody></table>";

    // Customer email
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
        $mail->addAddress($customerEmail, $customerName);
        $mail->isHTML(true);
        $mail->Subject = "Order confirmation #{$orderId}";
        $mail->Body = "<p>Thank you for your order.</p>" . $html;
        $mail->AltBody = "Thank you for your order. Order #{$orderId} Total: ₹" . number_format($total,2);
        $mail->send();
    } catch (Exception $e) {
        error_log("Order email to customer failed: " . $mail->ErrorInfo);
    }

    // Admin email
    try {
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host = $config['mail']['smtp_host'];
        $mail2->SMTPAuth = true;
        $mail2->Username = $config['mail']['smtp_user'];
        $mail2->Password = $config['mail']['smtp_pass'];
        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail2->Port = $config['mail']['smtp_port'];

        $mail2->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
        $mail2->addAddress($config['mail']['from_email']); // admin
        $mail2->isHTML(true);
        $mail2->Subject = "New order received #{$orderId}";
        $mail2->Body = $html;
        $mail2->AltBody = "New order #{$orderId} Total: ₹" . number_format($total,2);
        $mail2->send();
    } catch (Exception $e) {
        error_log("Order email to admin failed: " . $mail2->ErrorInfo);
    }

    // redirect to order view
    header('Location: order_view.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Place order failed: " . $e->getMessage());
    $_SESSION['checkout_error'] = 'Order failed: ' . $e->getMessage();
    header('Location: checkout.php');
    exit;
}
