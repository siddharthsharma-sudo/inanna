<?php
// address_save.php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$uid = (int)$_SESSION['user_id'];

$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || empty($_SESSION['addr_csrf']) || !hash_equals($_SESSION['addr_csrf'], $csrf)) {
    die('Invalid CSRF');
}

$id = (int)($_POST['id'] ?? 0);
$label = trim($_POST['label'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$line1 = trim($_POST['address_line1'] ?? '');
$line2 = trim($_POST['address_line2'] ?? '');
$city = trim($_POST['city'] ?? '');
$state = trim($_POST['state'] ?? '');
$postal = trim($_POST['postal_code'] ?? '');
$country = trim($_POST['country'] ?? 'India');
$is_default = !empty($_POST['is_default']) ? 1 : 0;

if ($full_name === '' || $line1 === '' || $city === '') {
    header('Location: address_edit.php?id=' . $id . '&err=missing');
    exit;
}

// if setting default, clear other defaults
if ($is_default) {
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :uid")->execute(['uid'=>$uid]);
}

if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE addresses SET label=:label, full_name=:fullname, phone=:phone, address_line1=:l1, address_line2=:l2, city=:city, state=:state, postal_code=:postal, country=:country, is_default=:def WHERE id = :id AND user_id = :uid");
    $stmt->execute([
        'label'=>$label,'fullname'=>$full_name,'phone'=>$phone,'l1'=>$line1,'l2'=>$line2,
        'city'=>$city,'state'=>$state,'postal'=>$postal,'country'=>$country,'def'=>$is_default,
        'id'=>$id,'uid'=>$uid
    ]);
} else {
    $stmt = $pdo->prepare("INSERT INTO addresses (user_id,label,full_name,phone,address_line1,address_line2,city,state,postal_code,country,is_default) VALUES (:uid,:label,:fullname,:phone,:l1,:l2,:city,:state,:postal,:country,:def)");
    $stmt->execute([
        'uid'=>$uid,'label'=>$label,'fullname'=>$full_name,'phone'=>$phone,'l1'=>$line1,'l2'=>$line2,
        'city'=>$city,'state'=>$state,'postal'=>$postal,'country'=>$country,'def'=>$is_default
    ]);
}
header('Location: address_book.php?msg=saved');
exit;
