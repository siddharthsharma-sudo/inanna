<?php
// admin/product_save.php
// Robust, defensive product save handler with delete support.
// Drop into admin/ — expects same $pdo used by product_edit.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/auth.php';
require_admin();

// start session
if (session_status() === PHP_SESSION_NONE) session_start();

// Simple flash helper (use session to pass messages)
function flash($k,$v){ $_SESSION['flash'][$k]=$v; }
function get_flash($k){ if (isset($_SESSION['flash'][$k])) { $v=$_SESSION['flash'][$k]; unset($_SESSION['flash'][$k]); return $v;} return null; }

// safe escape for redirects
function escq($s){ return rawurlencode((string)$s); }

// get column max length helper
function get_column_max_length(PDO $pdo, string $table, string $column) {
    try {
        $sql = "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1";
        $q = $pdo->prepare($sql);
        $q->execute(['t'=>$table,'c'=>$column]);
        $r = $q->fetch(PDO::FETCH_ASSOC);
        if (!$r) return null;
        return $r['CHARACTER_MAXIMUM_LENGTH'] !== null ? (int)$r['CHARACTER_MAXIMUM_LENGTH'] : null;
    } catch (Exception $e) {
        return null;
    }
}
function fit_column_length(PDO $pdo, string $table, string $column, string $str, bool $appendHash=false){
    $max = get_column_max_length($pdo,$table,$column);
    if ($max === null) return (string)$str;
    $s = (string)$str;
    if (mb_strlen($s) <= $max) return $s;
    if ($appendHash && $max > 8) {
        $hash = substr(bin2hex(random_bytes(3)),0,6);
        $avail = $max - (1 + strlen($hash));
        if ($avail <= 0) return mb_substr($s,0,$max);
        return mb_substr($s,0,$avail) . '-' . $hash;
    }
    return mb_substr($s,0,$max);
}

// helper: check column existence
function table_has_column(PDO $pdo, $table, $col) {
    try {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1");
        $q->execute(['t'=>$table,'c'=>$col]);
        return (bool)$q->fetchColumn();
    } catch (Exception $e) { return false; }
}

// upload helper - stores under uploads/products/{product_id}/ and returns relative path
function handle_upload_file(array $file, $productId) {
    if (empty($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return '';
    $dir = __DIR__ . "/../uploads/products/{$productId}";
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = preg_replace('/[^a-zA-Z0-9\-_\.]/','-',pathinfo($file['name'], PATHINFO_FILENAME));
    $fname = time() . '-' . bin2hex(random_bytes(4)) . ($ext ? '.' . $ext : '');
    $target = $dir . '/' . $fname;
    if (!move_uploaded_file($file['tmp_name'], $target)) return '';
    return 'uploads/products/' . $productId . '/' . $fname;
}

// normalize gallery multiple upload (files array from <input name="product_images[]">)
function handle_multi_uploads(array $filesArr, $productId) {
    $out = [];
    if (!isset($filesArr['name'])) return $out;
    $count = count($filesArr['name']);
    for ($i=0;$i<$count;$i++){
        if (empty($filesArr['tmp_name'][$i])) continue;
        $single = [
            'name'=>$filesArr['name'][$i],
            'type'=>$filesArr['type'][$i] ?? '',
            'tmp_name'=>$filesArr['tmp_name'][$i],
            'error'=>$filesArr['error'][$i],
            'size'=>$filesArr['size'][$i] ?? 0,
        ];
        $path = handle_upload_file($single, $productId);
        if ($path) $out[] = $path;
    }
    return $out;
}

// Read incoming
$post = $_POST;
$id = isset($post['id']) ? (int)$post['id'] : 0;

// ----------------- DELETE BLOCK -----------------
$action = $_POST['action'] ?? '';
if ($action === 'delete') {
    // optional CSRF validation (we use the session token created in admin/products.php)
    if (!empty($_SESSION['crud_csrf']) && !empty($_POST['csrf']) && $_POST['csrf'] !== $_SESSION['crud_csrf']) {
        flash('error','Invalid request (CSRF).');
        header('Location: products.php?err=invalid');
        exit;
    }

    if ($id <= 0) {
        flash('error','Invalid product id.');
        header('Location: products.php?err=invalid');
        exit;
    }

    try {
        // fetch product for cleanup info
        $pstmt = $pdo->prepare("SELECT image FROM products WHERE id = :id LIMIT 1");
        $pstmt->execute(['id'=>$id]);
        $prod = $pstmt->fetch(PDO::FETCH_ASSOC);

        $pdo->beginTransaction();

        // delete product_images rows if table exists
        try {
            $pdo->prepare("DELETE FROM product_images WHERE product_id = :pid")->execute(['pid'=>$id]);
        } catch (Exception $ex) {
            error_log("product_save.php delete: product_images deletion failed: " . $ex->getMessage());
        }

        // collect variant images and delete variants
        $variantImages = [];
        try {
            $vsel = $pdo->prepare("SELECT image, images FROM product_variants WHERE product_id = :pid");
            $vsel->execute(['pid'=>$id]);
            while ($r = $vsel->fetch(PDO::FETCH_ASSOC)) {
                if (!empty($r['image'])) $variantImages[] = $r['image'];
                if (!empty($r['images'])) {
                    $dec = json_decode($r['images'], true);
                    if (is_array($dec)) $variantImages = array_merge($variantImages, $dec);
                    else $variantImages = array_merge($variantImages, array_filter(array_map('trim', explode(',', $r['images']))));
                }
            }
            $pdo->prepare("DELETE FROM product_variants WHERE product_id = :pid")->execute(['pid'=>$id]);
        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log("product_save.php delete: variants deletion error: " . $ex->getMessage());
            header('Location: products.php?err=db');
            exit;
        }

        // delete the main product row
        $pdo->prepare("DELETE FROM products WHERE id = :id")->execute(['id'=>$id]);

        $pdo->commit();

        // filesystem cleanup
        $filesToTry = [];
        if (!empty($prod['image'])) $filesToTry[] = $prod['image'];
        foreach ($variantImages as $vi) $filesToTry[] = $vi;

        $uploadDir = dirname(__DIR__) . "/uploads/products/{$id}";
        if (is_dir($uploadDir)) {
            foreach (glob($uploadDir . '/*') as $f) {
                if (is_file($f)) $filesToTry[] = $f;
            }
        }

        foreach ($filesToTry as $fp) {
            if (empty($fp)) continue;
            $fp = trim($fp);
            if ((strpos($fp, '/') === 0 || preg_match('#^[A-Za-z]:\\\\#', $fp)) && is_file($fp)) {
                @unlink($fp);
                continue;
            }
            $candidate = dirname(__DIR__) . '/' . ltrim($fp, '/');
            if (is_file($candidate)) { @unlink($candidate); continue; }
            $bn = basename($fp);
            $candidate2 = dirname(__DIR__) . "/uploads/products/{$id}/" . $bn;
            if (is_file($candidate2)) { @unlink($candidate2); continue; }
        }

        if (is_dir($uploadDir)) {
            $remaining = glob($uploadDir . '/*');
            if (empty($remaining)) @rmdir($uploadDir);
            $gallery = $uploadDir . '/gallery';
            if (is_dir($gallery)) {
                $rem = glob($gallery . '/*');
                if (empty($rem)) @rmdir($gallery);
            }
        }

        flash('success','Product deleted.');
        header('Location: products.php?msg=deleted');
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("product_save.php delete: exception: " . $e->getMessage());
        flash('error','Delete failed.');
        header('Location: products.php?err=db');
        exit;
    }
}
// ----------------- END DELETE BLOCK -----------------

// (the rest of your original create/update logic stays the same)
// csrf check (optional - if you used csrf in edit)
// if (empty($post['csrf']) || $post['csrf'] !== ($_SESSION['crud_csrf'] ?? '')) {
//     flash('error','CSRF mismatch'); header('Location: products.php'); exit;
// }

try {
    $pdo->beginTransaction();

    // columns present on products table (we'll update only those)
    $productCols = [];
    $colsRes = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colsRes as $c) $productCols[$c['Field']] = $c;

    // Build product data map from allowed inputs
    $allowedInputs = [
        'sku' => $post['sku'] ?? null,
        'name' => $post['name'] ?? null,
        'description' => $post['long_description'] ?? ($post['description'] ?? null),
        'short_description' => $post['short_description'] ?? null,
        'long_description' => $post['long_description'] ?? null,
        'details' => $post['details'] ?? null,
        'info_block' => $post['info_block'] ?? null,
        'price' => isset($post['price']) ? $post['price'] : null,
        'stock' => isset($post['stock']) ? (int)$post['stock'] : null,
        'categories' => isset($post['categories']) ? (is_array($post['categories']) ? json_encode(array_values($post['categories'])) : $post['categories']) : null,
        'gender' => $post['gender'] ?? null,
        'custom_size_allowed' => isset($post['custom_size_allowed']) ? 1 : 0,
        'custom_size_text' => $post['custom_size_text'] ?? null,
        'gallery' => null, // we'll set separately if needed
    ];

    // prepare data to insert/update only for columns that exist in table
    $payload = [];
    foreach ($allowedInputs as $k=>$v) {
        if (array_key_exists($k, $productCols) && $v !== null) {
            // enforce length limits for VARCHAR columns
            if (stripos($productCols[$k]['Type'],'varchar') !== false) {
                $max = get_column_max_length($pdo,'products',$k);
                if ($max !== null && mb_strlen((string)$v) > $max) {
                    $v = mb_substr((string)$v,0,$max);
                }
            }
            $payload[$k] = $v;
        }
    }

    // handle main image upload (works even for new product: we need product id)
    if ($id <= 0) {
        if (!array_key_exists('name',$productCols)) throw new Exception('Products table missing required "name" column');
        $insertCols = [];
        $insertVals = [];
        $params = [];
        $insertCols[] = 'name';
        $insertVals[] = ':name';
        $params['name'] = $payload['name'] ?? 'Untitled';
        if (array_key_exists('price',$productCols) && isset($payload['price'])) { $insertCols[]='price'; $insertVals[]=':price'; $params['price'] = $payload['price']; }
        if (array_key_exists('stock',$productCols) && isset($payload['stock'])) { $insertCols[]='stock'; $insertVals[]=':stock'; $params['stock'] = $payload['stock']; }
        $sql = "INSERT INTO products (" . implode(',',$insertCols) . ") VALUES (" . implode(',', $insertVals) . ")";
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $id = (int)$pdo->lastInsertId();
    }

    if (!empty($_FILES['main_image']) && is_uploaded_file($_FILES['main_image']['tmp_name'])) {
        $p = handle_upload_file($_FILES['main_image'], $id);
        if ($p) {
            if (array_key_exists('image',$productCols)) $payload['image'] = $p;
            else {
                if (array_key_exists('gallery',$productCols)) {
                    $payload['gallery'] = $p;
                }
            }
        }
    }

    $newGalleryPaths = [];
    if (!empty($_FILES['product_images'])) {
        $newGalleryPaths = handle_multi_uploads($_FILES['product_images'], $id);
    }

    $hasProductImagesTable = false;
try {
    $hasProductImagesTable = (bool)$pdo->query("SHOW TABLES LIKE 'product_images'")->fetchColumn();
} catch (Exception $e) { $hasProductImagesTable = false; }

if ($hasProductImagesTable && !empty($newGalleryPaths)) {
    $ins = $pdo->prepare("INSERT INTO product_images (product_id, path) VALUES (:pid, :path)");
    foreach ($newGalleryPaths as $p) $ins->execute(['pid'=>$id, 'path'=>$p]);
} else {
        // ---- GALLERY SAVE (ALWAYS REWRITE) ----
// ===== FINAL GALLERY SAVE (SINGLE SOURCE OF TRUTH) =====
$keepGallery = $_POST['existing_gallery_keep'] ?? [];
$newGalleryPaths = $newGalleryPaths ?? [];

$finalGallery = array_values(array_filter(array_merge(
    is_array($keepGallery) ? $keepGallery : [],
    is_array($newGalleryPaths) ? $newGalleryPaths : []
)));

if (array_key_exists('gallery', $productCols)) {
    $payload['gallery'] = json_encode($finalGallery);
}
// --------------------------------------------------
// DELETE REMOVED GALLERY FILES FROM DISK
// --------------------------------------------------

$oldGallery = [];

// get previous gallery from DB
$prev = $pdo->prepare("SELECT gallery FROM products WHERE id = :id");
$prev->execute(['id' => $id]);
$prevRow = $prev->fetch(PDO::FETCH_ASSOC);

if (!empty($prevRow['gallery'])) {
    $decoded = json_decode($prevRow['gallery'], true);
    if (is_array($decoded)) {
        $oldGallery = $decoded;
    }
}

// normalize paths
$oldGallery = array_map(function($p){
    return ltrim($p, '/');
}, $oldGallery);

$finalGalleryClean = array_map(function($p){
    return ltrim($p, '/');
}, $finalGallery);

// files to delete = old − new
$toDelete = array_diff($oldGallery, $finalGalleryClean);

// delete files
foreach ($toDelete as $file) {
    $fullPath = dirname(__DIR__) . '/' . $file;
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}


    }

    if (!empty($payload)) {
        $sets = []; $params = [];
        foreach ($payload as $k=>$v) {
            $sets[] = "`$k` = :$k";
            $params[$k] = $v;
        }
        $params['id'] = $id;
        $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id LIMIT 1";
        $st = $pdo->prepare($sql);
        $st->execute($params);
    }

    // VARIANTS handling (kept as-is)
    $incomingVariants = $_POST['variants'] ?? [];
    $existingVarIds = [];
    $rows = $pdo->prepare("SELECT id FROM product_variants WHERE product_id = :pid");
    $rows->execute(['pid'=>$id]);
    while ($r = $rows->fetch(PDO::FETCH_ASSOC)) $existingVarIds[] = (int)$r['id'];
    $incomingIds = [];

    $varCols = [];
    try {
        $vcols = $pdo->query("SHOW COLUMNS FROM product_variants")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($vcols as $c) $varCols[$c['Field']] = $c;
    } catch (Exception $e) { $varCols = []; }

    foreach ($incomingVariants as $idx => $v) {
        $vid = isset($v['id']) ? (int)$v['id'] : 0;
        $vs = [
            'variant_sku' => $v['sku'] ?? null,
            'size' => $v['size'] ?? null,
            'color' => $v['color'] ?? null,
            'price' => isset($v['price']) && $v['price'] !== '' ? $v['price'] : null,
            'stock' => isset($v['stock']) ? (int)$v['stock'] : 0,
        ];
        foreach (['variant_sku','size','color'] as $ck) {
            if (isset($vs[$ck]) && $vs[$ck] !== null && isset($varCols[$ck]) && stripos($varCols[$ck]['Type'],'varchar')!==false) {
                $max = get_column_max_length($pdo,'product_variants',$ck);
                if ($max !== null && mb_strlen((string)$vs[$ck]) > $max) $vs[$ck] = mb_substr((string)$vs[$ck],0,$max);
            }
        }

        if (!empty($_FILES['variants_files']) && isset($_FILES['variants_files']['tmp_name'][$idx]) && is_uploaded_file($_FILES['variants_files']['tmp_name'][$idx])) {
            $file = [
                'name' => $_FILES['variants_files']['name'][$idx],
                'tmp_name' => $_FILES['variants_files']['tmp_name'][$idx],
                'error' => $_FILES['variants_files']['error'][$idx],
                'size' => $_FILES['variants_files']['size'][$idx],
            ];
            $p = handle_upload_file($file, $id);
            if ($p && array_key_exists('image',$varCols)) $vs['image'] = $p;
        }

        $multiPaths = [];
        if (!empty($_FILES['variants_files_multi']) && isset($_FILES['variants_files_multi']['name'][$idx]) ) {
            $filesArr = [
                'name' => $_FILES['variants_files_multi']['name'][$idx],
                'type' => $_FILES['variants_files_multi']['type'][$idx],
                'tmp_name' => $_FILES['variants_files_multi']['tmp_name'][$idx],
                'error' => $_FILES['variants_files_multi']['error'][$idx],
                'size' => $_FILES['variants_files_multi']['size'][$idx],
            ];
            $multiPaths = handle_multi_uploads($filesArr, $id);
        }
        $keep = $_POST['variants_existing_images_list'][$idx] ?? [];
        $mergedImages = array_values(array_filter(array_merge($keep, $multiPaths)));

        if (!empty($mergedImages) && array_key_exists('images',$varCols)) {
            $vs['images'] = json_encode($mergedImages);
        }

        if ($vid > 0) {
            $incomingIds[] = $vid;
            $sets = []; $params = ['id'=>$vid];
            foreach ($vs as $k=>$val) {
                if (!array_key_exists($k,$varCols)) continue;
                $sets[] = "`$k` = :$k";
                $params[$k] = $val;
            }
            if (!empty($sets)) {
                $sql = "UPDATE product_variants SET " . implode(', ', $sets) . " WHERE id = :id LIMIT 1";
                $pdo->prepare($sql)->execute($params);
            }
        } else {
            $fields = ['product_id']; $place = [':product_id']; $params = ['product_id'=>$id];
            foreach ($vs as $k=>$val) {
                if (!array_key_exists($k,$varCols) || $val === null) continue;
                $fields[] = "`$k`";
                $place[] = ':' . $k;
                $params[$k] = $val;
            }
            if (!in_array('product_id',$fields)) { $fields[]='product_id'; $place[]=':product_id'; $params['product_id']=$id; }
            $sql = "INSERT INTO product_variants (" . implode(',',$fields) . ") VALUES (" . implode(',',$place) . ")";
            $st = $pdo->prepare($sql);
            $st->execute($params);
            $incomingIds[] = (int)$pdo->lastInsertId();
        }
    } // end foreach incoming variants

    $toDelete = array_diff($existingVarIds, $incomingIds);
    if (!empty($toDelete)) {
        $in = implode(',', array_map('intval', $toDelete));
        $pdo->exec("DELETE FROM product_variants WHERE id IN ({$in})");
    }

    if ($hasProductImagesTable) {
        $keepIds = $_POST['existing_product_images_keep'] ?? [];
        $allIds = [];
        $q = $pdo->prepare("SELECT id FROM product_images WHERE product_id = :pid");
        $q->execute(['pid'=>$id]);
        while ($r = $q->fetch(PDO::FETCH_ASSOC)) $allIds[] = (int)$r['id'];
        if (is_array($keepIds)) {
            $toRemove = array_diff($allIds, array_map('intval',$keepIds));
            if (!empty($toRemove)) {
                $in = implode(',', array_map('intval',$toRemove));
                $pdo->exec("DELETE FROM product_images WHERE id IN ($in)");
            }
        }
    }

    $pdo->commit();

    flash('success','Product saved successfully.');
    header('Location: products.php');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $msg = 'Save failed: ' . $e->getMessage();
    flash('error', $msg);
    header('Location: product_edit.php?id=' . intval($id) . '&err=' . escq($e->getMessage()));
    exit;
}
