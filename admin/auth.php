<?php
// admin/auth.php
// Include at the top of any admin page that must be protected.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in, redirect to login
function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        // preserve attempted URL if you like
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin/dashboard.php';
        header('Location: login.php');
        exit;
    }
}

// optional: get admin info
function current_admin_info(?PDO $pdo = null) {
    if (empty($_SESSION['admin_id'])) return null;
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $_SESSION['admin_id']]);
        return $stmt->fetch();
    }
    return ['id'=>$_SESSION['admin_id']];
}
