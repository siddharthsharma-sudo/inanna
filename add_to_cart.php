<?php
// add_to_cart.php
require_once __DIR__ . '/includes/db.php';
session_start();

// Expect POST: product_id, qty, variant_id (optional), measurements[] (optional), custom_size_text (optional)
// Redirect target (optional): redirect (e.g. cart.php or product.php?id=...)
$product_id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$variant_id = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? (int)$_POST['variant_id'] : 0;
$custom_size_text = trim((string)($_POST['custom_size_text'] ?? ''));
$raw_measurements = $_POST['measurements'] ?? null;
$redirect = $_POST['redirect'] ?? 'cart.php';

if ($product_id <= 0) {
    header('Location: products.php');
    exit;
}

// fetch product
$stmt = $pdo->prepare("SELECT id, name, price, image, stock FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: products.php');
    exit;
}

$unitPrice = (float)($product['price'] ?? 0.0);
$img = $product['image'] ?? '';
$productStock = isset($product['stock']) ? (int)$product['stock'] : null;

// variant details (if provided)
$variant_size = '';
$variant_color = '';
$variant_sku = '';
$variant_image = '';
$variant_stock = null;

if ($variant_id) {
    $vstmt = $pdo->prepare("SELECT id, size, color, price, stock, variant_sku, image, images FROM product_variants WHERE id = :vid AND product_id = :pid LIMIT 1");
    $vstmt->execute(['vid' => $variant_id, 'pid' => $product_id]);
    $variant = $vstmt->fetch(PDO::FETCH_ASSOC);
    if ($variant) {
        if ($variant['price'] !== null && $variant['price'] !== '') {
            $unitPrice = (float)$variant['price'];
        }
        $variant_size = $variant['size'] ?? '';
        $variant_color = $variant['color'] ?? '';
        $variant_sku = $variant['variant_sku'] ?? '';
        $variant_image = $variant['image'] ?? '';
        $variant_stock = isset($variant['stock']) ? (int)$variant['stock'] : null;

        // prefer variant image if present
        if ($variant_image) $img = $variant_image;
    }
}

// sanitize measurements (expect associative array measurements[name]=value)
$measurements = null;
if (is_array($raw_measurements)) {
    $measurements = [];
    foreach ($raw_measurements as $k => $v) {
        $k2 = trim((string)$k);
        $v2 = trim((string)$v);
        if ($k2 === '') continue;
        $measurements[$k2] = $v2;
    }
}

// decide final allowed qty based on stock (prefer variant stock then product stock if present)
$maxAllowed = null;
if ($variant_stock !== null) $maxAllowed = $variant_stock;
elseif ($productStock !== null) $maxAllowed = $productStock;

if ($maxAllowed !== null) {
    // if adding to an existing cart entry later we'll re-check; for now cap per-add
    if ($qty > $maxAllowed) $qty = $maxAllowed;
}

// make sure cart exists
if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];

// build key: product-variant + hash of measurements/custom_size so different measurement choices are separate entries
$keyBase = $product_id . '-' . ($variant_id ?: 0);
$keyExtraData = json_encode([
    'custom_size_text' => $custom_size_text ?: null,
    'measurements' => $measurements ?: null
], JSON_UNESCAPED_UNICODE);
$keyHash = substr(md5($keyExtraData), 0, 8);
$key = $keyBase . '-' . $keyHash;

// if identical item already exists (same key) increment, else create
if (isset($_SESSION['cart'][$key])) {
    // attempt to increase but respect stock if available
    $existingQty = (int)$_SESSION['cart'][$key]['qty'];
    $newQty = $existingQty + $qty;
    if ($maxAllowed !== null && $newQty > $maxAllowed) {
        $newQty = $maxAllowed;
    }
    $_SESSION['cart'][$key]['qty'] = $newQty;
} else {
    // store cart item with variant metadata and measurements
    $_SESSION['cart'][$key] = [
        'product_id' => $product['id'],
        'variant_id' => $variant_id ? $variant_id : null,
        'name' => $product['name'],
        'price' => $unitPrice,
        'qty' => $qty,
        'image' => $img,
        'variant_size' => $variant_size,
        'variant_color' => $variant_color,
        'variant_sku' => $variant_sku,
        'custom_size_text' => $custom_size_text ?: null,
        'measurements' => $measurements ?: null,
        'added_at' => time(),
    ];
}

// redirect back (preserves intent)
header('Location: ' . $redirect);
exit;
