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
