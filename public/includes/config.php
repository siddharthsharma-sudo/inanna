<?php
// public/includes/config.php
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
        'smtp_user' => 'worldofinanna@gmail.com',     // your Gmail
        'smtp_pass' => 'tiuwqsunclbmutff',        // Gmail App Password (not normal password)

        // The email sender info shown to users
        'from_email' => 'no-reply@yourdomain.com',         // or same as your Gmail
        'from_name'  => 'Inanna Shop',
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
