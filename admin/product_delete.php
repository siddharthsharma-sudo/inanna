<?php
// admin/product_delete.php
// Safe admin product delete that removes dependent child rows (FKs) first.
// WARNING: this will DELETE dependent rows (e.g. order_items). Backup DB before using on production.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/auth.php';
require_admin();

if (session_status() === PHP_SESSION_NONE) session_start();

// simple helper to validate identifier names (table/column) from information_schema
function valid_ident($s) {
    return is_string($s) && preg_match('/^[A-Za-z0-9_]+$/', $s);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php?err=invalid');
    exit;
}

$csrf = $_POST['csrf'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0 || empty($csrf) || $csrf !== ($_SESSION['crud_csrf'] ?? '')) {
    header('Location: products.php?err=invalid');
    exit;
}

try {
    // begin transaction
    $pdo->beginTransaction();

    // 1) find all foreign key constraints that reference products.id in this database
    $sql = "
        SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME
        FROM information_schema.key_column_usage
        WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME = 'products'
          AND REFERENCED_COLUMN_NAME = 'id'
    ";
    $stmt = $pdo->query($sql);
    $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete dependent rows for each referencing table/column (skip self references)
    foreach ($fks as $fk) {
        $tbl = $fk['TABLE_NAME'];
        $col = $fk['COLUMN_NAME'];
        if (!valid_ident($tbl) || !valid_ident($col)) {
            // skip suspicious names
            continue;
        }

        // don't delete from the products table itself
        if (strtolower($tbl) === 'products') continue;

        // Prepare and execute deletion
        $dsql = "DELETE FROM `{$tbl}` WHERE `{$col}` = :pid";
        $del = $pdo->prepare($dsql);
        $del->execute(['pid' => $id]);
    }

    // 2) delete product_variants if exists
    try {
        $pdo->prepare("DELETE FROM product_variants WHERE product_id = :pid")->execute(['pid' => $id]);
    } catch (Exception $e) {
        // if table doesn't exist or deletion fails, rethrow to be consistent
        throw new Exception("Failed to delete product_variants: " . $e->getMessage());
    }

    // 3) delete product_images rows (if table exists)
    try {
        $pdo->prepare("DELETE FROM product_images WHERE product_id = :pid")->execute(['pid' => $id]);
    } catch (Exception $e) {
        // ignore if table missing; rethrow only if unexpected
        // (we'll ignore silently to allow schema differences)
    }

    // 4) fetch product image path for filesystem cleanup
    $pstmt = $pdo->prepare("SELECT image FROM products WHERE id = :id LIMIT 1");
    $pstmt->execute(['id' => $id]);
    $prod = $pstmt->fetch(PDO::FETCH_ASSOC);

    // 5) finally delete product row
    $pdo->prepare("DELETE FROM products WHERE id = :id LIMIT 1")->execute(['id' => $id]);

    // commit
    $pdo->commit();

    // 6) file cleanup (attempt best-effort removal)
    $filesToTry = [];
    if (!empty($prod['image'])) $filesToTry[] = $prod['image'];

    // also attempt to remove uploads/products/{id} and its gallery files
    $uploadDir = dirname(__DIR__) . "/uploads/products/{$id}";
    if (is_dir($uploadDir)) {
        foreach (glob($uploadDir . '/*') as $f) {
            if (is_file($f)) $filesToTry[] = $f;
        }
    }

    foreach ($filesToTry as $fp) {
        if (empty($fp)) continue;
        $fp = trim($fp);
        // If absolute filesystem path given, unlink directly
        if ((strpos($fp, '/') === 0 || preg_match('#^[A-Za-z]:\\\\#', $fp)) && is_file($fp)) {
            @unlink($fp);
            continue;
        }
        // otherwise try project-root relative
        $candidate = dirname(__DIR__) . '/' . ltrim($fp, '/');
        if (is_file($candidate)) { @unlink($candidate); continue; }
        // try uploads folder basename fallback
        $bn = basename($fp);
        $candidate2 = dirname(__DIR__) . "/uploads/products/{$id}/" . $bn;
        if (is_file($candidate2)) { @unlink($candidate2); continue; }
    }

    // attempt to remove directories if empty
    if (is_dir($uploadDir)) {
        $remaining = glob($uploadDir . '/*');
        if (empty($remaining)) @rmdir($uploadDir);
        $gallery = $uploadDir . '/gallery';
        if (is_dir($gallery)) {
            $rem = glob($gallery . '/*');
            if (empty($rem)) @rmdir($gallery);
        }
    }

    header('Location: products.php?msg=deleted');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    // Log the exception for server logs
    error_log("admin/product_delete.php error deleting product {$id}: " . $e->getMessage());
    // Return user-friendly message
    // If you want to debug locally you can echo $e->getMessage() temporarily
    header('Location: products.php?err=db');
    exit;
}
