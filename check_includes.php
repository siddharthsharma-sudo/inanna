<?php
// check_includes.php — run this in your browser to inspect include paths
header('Content-Type: text/plain; charset=utf-8');
echo "Running include checks...\n\n";

$paths = [
    '__DIR__/includes/razorpay_config.php' => realpath(__DIR__ . '/includes/razorpay_config.php'),
    '__DIR__/includes/db.php'              => realpath(__DIR__ . '/includes/db.php'),
    '__DIR__/vendor/autoload.php'         => realpath(__DIR__ . '/vendor/autoload.php'),
    '__DIR__/includes/config.php'         => realpath(__DIR__ . '/includes/config.php'),
    '__DIR__/includes/config.php'  => realpath(__DIR__ . '/includes/config.php'),
];

foreach ($paths as $k => $v) {
    echo $k . " => " . ($v ? $v : '[NOT FOUND]') . "\n";
}
echo "\nSession cookie name: " . session_name() . "\n";
