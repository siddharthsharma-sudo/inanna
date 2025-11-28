<?php
// public/add_to_cart.php
require_once __DIR__ . '/includes/db.php';
session_start();

// Expect POST: product_id, qty, variant_id (optional)
$product_id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int)$_POST['variant_id'] : 0;

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// fetch product and variant if provided
$stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

$unitPrice = (float)$product['price'];
$img = $product['image'] ?? '';

if ($variant_id) {
    $vstmt = $pdo->prepare("SELECT id, price, stock FROM product_variants WHERE id = :vid AND product_id = :pid LIMIT 1");
    $vstmt->execute(['vid' => $variant_id, 'pid' => $product_id]);
    $variant = $vstmt->fetch();
    if ($variant) {
        if ($variant['price'] !== null && $variant['price'] !== '') {
            $unitPrice = (float)$variant['price'];
        }
        // if variant has its own image field, prefer that (not implemented here by default)
    }
}

// initialize cart
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['cart'])) $_SESSION['cart'] = [];

// key by product-variant
$key = $product_id . '-' . ($variant_id ?: 0);

if (isset($_SESSION['cart'][$key])) {
    $_SESSION['cart'][$key]['qty'] += $qty;
} else {
    $_SESSION['cart'][$key] = [
        'product_id' => $product_id,
        'variant_id' => $variant_id,
        'name' => $product['name'],
        'price' => $unitPrice,
        'qty' => $qty,
        'image' => $img
    ];
}

// redirect back to cart or product (preserve intent)
$redirect = $_POST['redirect'] ?? 'cart.php';
header('Location: ' . $redirect);
exit;
