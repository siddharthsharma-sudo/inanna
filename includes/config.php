<?php
// includes/config.php
// Global configuration for database, email (SMTP), etc.
return [

    // ------------------------------------
    // DATABASE CONFIGURATION
    // ------------------------------------
    'db' => [
        'host'    => '127.0.0.1',
        'name'    => 'inanna_db',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    // ------------------------------------
    // SMTP EMAIL CONFIGURATION (GMAIL)
    // ------------------------------------
    'mail' => [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'worldofinanna@gmail.com',
    'smtp_pass' => 'tiuwqsunclbmutff', // app password

    'from_email' => 'worldofinanna@gmail.com', // <-- FIXED
    'from_name'  => 'Inanna Shop',

    'admin_email' => 'worldofinanna@gmail.com', // <-- Add this too
],


    // ------------------------------------
    // FUTURE FEATURE (WhatsApp/Twilio)
    // ------------------------------------
    'twilio' => [
        'enabled' => false,
        'sid'     => '',
        'token'   => '',
        'from'    => '',
    ],

];
