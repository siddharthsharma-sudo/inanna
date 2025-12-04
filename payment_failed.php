<?php
// payment_failed.php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-danger bg-opacity-25">

<div class="container mt-5">
    <div class="card p-4 shadow">
        <h2 class="text-danger">❌ Payment Failed</h2>

        <p>Your payment could not be verified.</p>

        <p>Please try again or use Cash on Delivery.</p>

        <a href="checkout.php" class="btn btn-danger mt-3">Retry Payment</a>
    </div>
</div>

</body>
</html>
