<?php
// admin/order_update.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../public/includes/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: orders.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$admin_note = trim($_POST['admin_note'] ?? '');

if ($id <= 0 || !$newStatus) {
    header('Location: orders.php');
    exit;
}

// fetch current order
$ost = $pdo->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$ost->execute(['id' => $id]);
$order = $ost->fetch();
if (!$order) { header('Location: orders.php'); exit; }

try {
    $pdo->beginTransaction();

    // If cancelling an order that was previously paid/shipped/etc, optionally restore stock
    if ($newStatus === 'cancelled' && $order['status'] !== 'cancelled') {
        // restore stock for items
        $its = $pdo->prepare("SELECT product_id, variant_id, qty FROM order_items WHERE order_id = :oid");
        $its->execute(['oid' => $id]);
        while ($r = $its->fetch()) {
            $qty = (int)$r['qty'];
            if (!empty($r['variant_id'])) {
                $pdo->prepare("UPDATE product_variants SET stock = stock + :q WHERE id = :vid")->execute(['q'=>$qty,'vid'=>$r['variant_id']]);
            } else {
                $pdo->prepare("UPDATE products SET stock = stock + :q WHERE id = :pid")->execute(['q'=>$qty,'pid'=>$r['product_id']]);
            }
        }
    }

    // update order status and store admin note (if you have a notes column; else you can save in a new table)
    $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id")->execute(['status'=>$newStatus,'id'=>$id]);

    // optionally store admin_note in a separate table or in orders (if you added a column). For now we log it:
    if ($admin_note !== '') {
        $log = $pdo->prepare("INSERT INTO order_admin_notes (order_id, note, created_at) VALUES (:oid, :note, NOW())");
        // Create table if doesn't exist
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS order_admin_notes (
              id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              order_id INT UNSIGNED NOT NULL,
              note TEXT NOT NULL,
              created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              INDEX (order_id),
              FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $log->execute(['oid'=>$id,'note'=>$admin_note]);
    }

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("Order update failed: " . $e->getMessage());
    // fallback
}

header('Location: order_view.php?id=' . $id);
exit;
