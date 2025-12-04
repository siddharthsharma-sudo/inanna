<?php
// test_razorpay.php (debug)
ini_set('display_errors',1); error_reporting(E_ALL);

$paths = [
    __DIR__ . '/includes/razorpay_config.php',
    __DIR__ . '/includes/razorpay_config.php'
];
$found = false;
foreach ($paths as $p) {
    if (file_exists($p)) { require_once $p; $found = $p; break; }
}
require_once __DIR__ . '/../vendor/autoload.php';
use Razorpay\Api\Api;

echo "Using razorpay_config from: " . ($found ?: 'NOT FOUND') . "<br>";
if (!defined('rzp_live_d1RiMPHQ8X6L7N')) { echo "RAZORPAY_KEY_ID not defined<br>"; exit; }

try {
    $api = new Api('rzp_live_d1RiMPHQ8X6L7N', 'ZtrIVdfDQ2vDLNIH9eV0Kct7');
    $acc = $api->account->fetch();
    echo "<pre>" . htmlspecialchars(json_encode($acc->toArray(), JSON_PRETTY_PRINT)) . "</pre>";
} catch (Exception $e) {
    echo "API error: " . htmlspecialchars($e->getMessage());
}
