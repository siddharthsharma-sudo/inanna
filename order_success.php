<?php
// order_success.php
session_start();
require_once __DIR__ . '/includes/db.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    die("Invalid order.");
}

// fetch order (optional)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Successful</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2 class="text-success">🎉 Order Placed Successfully!</h2>

        <p>Your order has been successfully placed.</p>

        <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>

        <?php if ($order): ?>
            <p><strong>Total:</strong> ₹<?= htmlspecialchars($order['total']) ?></p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        <?php endif; ?>

        <a href="order_view.php" class="btn btn-primary mt-3">View My Orders</a>
        <a href="./" class="btn btn-outline-secondary mt-3">Continue Shopping</a>
    </div>
</div>

</body>
</html>
