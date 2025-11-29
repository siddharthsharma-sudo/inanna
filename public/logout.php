<?php
// public/logout.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Unset normalized user and legacy variables
unset($_SESSION['user'], $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['after_login']);

// Destroy session cookie safely
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
// Redirect to login or homepage
header('Location: login.php');
exit;
