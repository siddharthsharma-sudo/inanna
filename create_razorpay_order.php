<?php
// create_razorpay_order.php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Load dependencies
require_once __DIR__ . '/includes/razorpay_config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Razorpay\Api\Api;

session_start();

// 2. Check cart
$cart = $_SESSION['cart'] ?? null;

if (!is_array($cart) || empty($cart)) {
    http_response_code(400);
    echo json_encode(["error" => "Cart is empty"]);
    exit;
}

// 3. Calculate total in paise
$totalPaise = 0;

foreach ($cart as $item) {
    $price = (float)($item['price'] ?? 0); // rupees
    $qty   = (int)($item['qty'] ?? 1);
    $totalPaise += round($price * 100) * $qty;
}

// 4. Create Razorpay order
try {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $receipt = "rcpt_" . time();

    $order = $api->order->create([
        "receipt" => $receipt,
        "amount" => $totalPaise,
        "currency" => "INR",
        "payment_capture" => 1
    ]);

    echo json_encode([
        "order_id" => $order['id'],
        "amount" => $totalPaise,
        "currency" => "INR",
        "key" => RAZORPAY_KEY_ID
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Razorpay API Error",
        "message" => $e->getMessage()
    ]);
}
