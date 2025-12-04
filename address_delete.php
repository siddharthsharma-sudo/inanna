<?php
//address_delete.php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { header('Location: address_book.php'); exit; }

// ensure address belongs to user
$stmt = $pdo->prepare("DELETE FROM addresses WHERE id = :id AND user_id = :uid");
$stmt->execute(['id'=>$id,'uid'=>$uid]);
header('Location: address_book.php?msg=deleted');
exit;
