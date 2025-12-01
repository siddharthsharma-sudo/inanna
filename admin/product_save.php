<?php
// admin/product_save.php (enhanced: gallery, categories, short/long descriptions, details, info block, custom-size, variant multi-images)
require_once __DIR__ . '/../public/includes/db.php';
require_once __DIR__ . '/auth.php';
require_admin();

if (session_status() === PHP_SESSION_NONE) session_start();
$csrf = $_POST['csrf'] ?? '';
if (empty($csrf) || empty($_SESSION['crud_csrf']) || !hash_equals($_SESSION['crud_csrf'], $csrf)) {
    die('Invalid CSRF token');
}

$action = $_POST['action'] ?? 'save';

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        header('Location: products.php?err=invalid');
        exit;
    }

    // delete variant images (including extra images stored in product_variants.images or image column)
    $vstmt = $pdo->prepare("SELECT id, image, images FROM product_variants WHERE product_id = :pid");
    $vstmt->execute(['pid'=>$id]);
    while ($vr = $vstmt->fetch()) {
        if (!empty($vr['image'])) {
            $imgPath = __DIR__ . '/../public/' . $vr['image'];
            if (file_exists($imgPath)) @unlink($imgPath);
        }
        if (!empty($vr['images'])) {
            // try JSON decode or CSV
            $arr = json_decode($vr['images'], true);
            if (!is_array($arr)) $arr = array_filter(array_map('trim', explode(',', $vr['images'])));
            foreach ($arr as $p) {
                $imgPath = __DIR__ . '/../public/' . $p;
                if (file_exists($imgPath)) @unlink($imgPath);
            }
        }
    }

    // delete product main image
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id LIMIT 1");
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image'])) {
        $imgPath = __DIR__ . '/../public/' . $row['image'];
        if (file_exists($imgPath)) @unlink($imgPath);
    }

    // delete product gallery images from product_images table if exists
    try {
        $pimgs = $pdo->prepare("SELECT path FROM product_images WHERE product_id = :pid");
        $pimgs->execute(['pid'=>$id]);
        while ($pi = $pimgs->fetch()) {
            $imgPath = __DIR__ . '/../public/' . $pi['path'];
            if (file_exists($imgPath)) @unlink($imgPath);
        }
        $pdo->prepare("DELETE FROM product_images WHERE product_id = :pid")->execute(['pid'=>$id]);
    } catch (Exception $e) {
        // table might not exist — ignore
    }

    // DELETE product and cascade variants if FK exists
    $del = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $del->execute(['id'=>$id]);
    header('Location: products.php?msg=deleted');
    exit;
}

// helper: save uploaded file, returns relative path or null
function save_upload_file($file, $uploadBaseRel = 'uploads') {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) return null;
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimetype, $allowed)) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $ext = strtolower($ext) ?: (strpos($mimetype,'jpeg')!==false ? 'jpg' : 'png');
    $nameOnDisk = time() . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destRel = rtrim($uploadBaseRel, '/') . '/' . $nameOnDisk;
    $destAbs = __DIR__ . '/../public/' . $destRel;
    if (!is_dir(dirname($destAbs))) mkdir(dirname($destAbs), 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $destAbs)) return null;
    return $destRel;
}

// ---- collect form fields ----
$id = (int)($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$sku = trim($_POST['sku'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$short_description = trim($_POST['short_description'] ?? '');
$long_description = trim($_POST['long_description'] ?? '');
$details = trim($_POST['details'] ?? '');
$info_block = trim($_POST['info_block'] ?? '');
$custom_size_allowed = !empty($_POST['custom_size_allowed']) ? 1 : 0;
$custom_size_text = trim($_POST['custom_size_text'] ?? '');
// categories sent as array of ids
$categories = $_POST['categories'] ?? [];
$categories_csv = '';
if (is_array($categories)) {
    $catsFiltered = array_map('intval', $categories);
    $categories_csv = implode(',', $catsFiltered);
} elseif ($categories !== '') {
    $categories_csv = trim($categories);
}

// simple validation
if ($name === '') {
    header('Location: product_edit.php?id=' . $id . '&err=missing_name');
    exit;
}

// handle main image upload (new form uses main_image; keep legacy 'image' too)
$mainFile = $_FILES['main_image'] ?? ($_FILES['image'] ?? null);
$mainImageRel = null;
if ($mainFile && !empty($mainFile['tmp_name'])) {
    $mainImageRel = save_upload_file($mainFile);
    if ($mainImageRel === null && $mainFile['error'] !== UPLOAD_ERR_NO_FILE) {
        header('Location: product_edit.php?id=' . $id . '&err=notsupported');
        exit;
    }
}

// handle additional gallery uploads (product_images[])
$galleryPathsNew = [];
if (!empty($_FILES['product_images'])) {
    // product_images is a multiple file input => reorganize
    $pfiles = $_FILES['product_images'];
    foreach ($pfiles['tmp_name'] as $k => $tmp) {
        if (empty($tmp)) continue;
        $file = [
            'name'=>$pfiles['name'][$k],
            'type'=>$pfiles['type'][$k],
            'tmp_name'=>$pfiles['tmp_name'][$k],
            'error'=>$pfiles['error'][$k],
            'size'=>$pfiles['size'][$k],
        ];
        $saved = save_upload_file($file);
        if ($saved) $galleryPathsNew[] = $saved;
    }
}

// begin transaction
$pdo->beginTransaction();

try {
    // if updating existing product, fetch current gallery entries (if product_images table exists) and manage deletions
    $existing_gallery = [];
    $has_product_images_table = false;
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'product_images'")->fetch();
        $has_product_images_table = !!$check;
    } catch (Exception $e) { $has_product_images_table = false; }

    if ($id > 0) {
        // update main product
        if ($mainImageRel) {
            // delete old main image if present
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = :id LIMIT 1");
            $stmt->execute(['id'=>$id]);
            $old = $stmt->fetch();
            if ($old && !empty($old['image'])) {
                $oldAbs = __DIR__ . '/../public/' . $old['image'];
                if (file_exists($oldAbs)) @unlink($oldAbs);
            }
            $stmt = $pdo->prepare("UPDATE products SET sku=:sku, name=:name, price=:price, stock=:stock, image=:image, short_description=:sdesc, long_description=:ldesc, details=:details, info_block=:info, custom_size_allowed=:csize, custom_size_text=:cstext, categories=:cats WHERE id = :id");
            $stmt->execute([
                'sku'=>$sku ?: null,
                'name'=>$name,
                'price'=>$price,
                'stock'=>$stock,
                'image'=>$mainImageRel,
                'sdesc'=>$short_description,
                'ldesc'=>$long_description,
                'details'=>$details,
                'info'=>$info_block,
                'csize'=>$custom_size_allowed,
                'cstext'=>$custom_size_text,
                'cats'=>$categories_csv,
                'id'=>$id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE products SET sku=:sku, name=:name, price=:price, stock=:stock, short_description=:sdesc, long_description=:ldesc, details=:details, info_block=:info, custom_size_allowed=:csize, custom_size_text=:cstext, categories=:cats WHERE id = :id");
            $stmt->execute([
                'sku'=>$sku ?: null,
                'name'=>$name,
                'price'=>$price,
                'stock'=>$stock,
                'sdesc'=>$short_description,
                'ldesc'=>$long_description,
                'details'=>$details,
                'info'=>$info_block,
                'csize'=>$custom_size_allowed,
                'cstext'=>$custom_size_text,
                'cats'=>$categories_csv,
                'id'=>$id
            ]);
        }

        // manage existing gallery images (if product_images table exists)
        if ($has_product_images_table) {
            $stmt = $pdo->prepare("SELECT id, path FROM product_images WHERE product_id = :pid ORDER BY id ASC");
            $stmt->execute(['pid'=>$id]);
            while ($r = $stmt->fetch()) {
                $existing_gallery[$r['id']] = $r['path'];
            }
            // posted keep list
            $keep = $_POST['existing_product_images_keep'] ?? [];
            $keep = is_array($keep) ? array_map('intval', $keep) : [];
            // delete those not kept
            foreach ($existing_gallery as $imgId => $path) {
                if (!in_array($imgId, $keep)) {
                    $abs = __DIR__ . '/../public/' . $path;
                    if (file_exists($abs)) @unlink($abs);
                    $pdo->prepare("DELETE FROM product_images WHERE id = :id")->execute(['id'=>$imgId]);
                }
            }
            // insert newly uploaded gallery images
            if (!empty($galleryPathsNew)) {
                $ins = $pdo->prepare("INSERT INTO product_images (product_id, path) VALUES (:pid, :path)");
                foreach ($galleryPathsNew as $g) {
                    $ins->execute(['pid'=>$id, 'path'=>$g]);
                }
            }
        } else {
            // fallback: update products.gallery JSON or CSV column if exists
            // fetch current
            $stmt = $pdo->prepare("SHOW COLUMNS FROM products LIKE 'gallery'");
            $stmt->execute();
            $hasGalleryColumn = !!$stmt->fetch();
            if ($hasGalleryColumn) {
                $gstmt = $pdo->prepare("SELECT gallery FROM products WHERE id = :id LIMIT 1");
                $gstmt->execute(['id'=>$id]);
                $gr = $gstmt->fetch();
                $cur = [];
                if (!empty($gr['gallery'])) {
                    $dec = json_decode($gr['gallery'], true);
                    if (is_array($dec)) $cur = $dec;
                    else $cur = array_filter(array_map('trim', explode(',', $gr['gallery'])));
                }
                // keep list posted as existing_product_images_keep holds ids, but we don't have ids here.
                // To keep it simple: if there are existing files listed in a hidden input existing_product_images_list[], we honor keep
                $existing_list = $_POST['existing_product_images_list'] ?? [];
                $keep_paths = [];
                if (is_array($existing_list)) {
                    $keep_paths = array_values(array_filter(array_map('trim', $existing_list)));
                }
                // delete files that are in $cur but not in keep_paths
                foreach ($cur as $p) {
                    if (!in_array($p, $keep_paths)) {
                        $abs = __DIR__ . '/../public/' . $p;
                        if (file_exists($abs)) @unlink($abs);
                    } else {
                        $keep_final[] = $p;
                    }
                }
                $keep_final = $keep_final ?? [];
                $newGalleryAll = array_merge($keep_final, $galleryPathsNew);
                $json = json_encode(array_values($newGalleryAll));
                $pdo->prepare("UPDATE products SET gallery = :g WHERE id = :id")->execute(['g'=>$json,'id'=>$id]);
            } else {
                // nothing to do, drop gallery uploads (or you could still move them and not store)
                // we'll leave them moved on disk but not recorded
            }
        }

    } else {
        // insert new product
        $stmt = $pdo->prepare("INSERT INTO products (sku,name,description,price,stock,image,short_description,long_description,details,info_block,custom_size_allowed,custom_size_text,categories) VALUES (:sku,:name,:description,:price,:stock,:image,:sdesc,:ldesc,:details,:info,:csize,:cstext,:cats)");
        $stmt->execute([
            'sku'=>$sku ?: null,
            'name'=>$name,
            'description'=> $long_description ?: '',
            'price'=>$price,
            'stock'=>$stock,
            'image'=>$mainImageRel,
            'sdesc'=>$short_description,
            'ldesc'=>$long_description,
            'details'=>$details,
            'info'=>$info_block,
            'csize'=>$custom_size_allowed,
            'cstext'=>$custom_size_text,
            'cats'=>$categories_csv
        ]);
        $id = (int)$pdo->lastInsertId();

        // insert gallery files if product_images table exists
        if ($has_product_images_table && !empty($galleryPathsNew)) {
            $ins = $pdo->prepare("INSERT INTO product_images (product_id, path) VALUES (:pid, :path)");
            foreach ($galleryPathsNew as $g) {
                $ins->execute(['pid'=>$id, 'path'=>$g]);
            }
        } else {
            // fallback to gallery column if present
            $stmt = $pdo->prepare("SHOW COLUMNS FROM products LIKE 'gallery'");
            $stmt->execute();
            if ($stmt->fetch() && !empty($galleryPathsNew)) {
                $pdo->prepare("UPDATE products SET gallery = :g WHERE id = :id")->execute(['g'=>json_encode(array_values($galleryPathsNew)),'id'=>$id]);
            }
        }
    }

    // ---- VARIANTS handling ----
    // We'll keep/update existing variants where id is posted, delete any existing variant that isn't posted.
    // Fetch existing variant ids
    $existingVariantIds = [];
    $vQ = $pdo->prepare("SELECT id FROM product_variants WHERE product_id = :pid");
    $vQ->execute(['pid'=>$id]);
    while ($r = $vQ->fetch()) $existingVariantIds[] = (int)$r['id'];

    $postedVariants = $_POST['variants'] ?? [];
    $postedVariantIds = [];
    // reorganize files arrays for single primary image per variant and multi-images per variant
    $vfiles_single = $_FILES['variants_files'] ?? null; // single per index
    $vfiles_multi = $_FILES['variants_files_multi'] ?? null; // multi: name[index][]
    // variants_existing_images_list and keep arrays
    $variants_existing_images_list = $_POST['variants_existing_images_list'] ?? []; // associative by index
    $variants_existing_images_keep = $_POST['variants_existing_images_keep'] ?? [];

    // Track variant ids that will remain after update
    foreach ($postedVariants as $idx => $v) {
        $v_id = isset($v['id']) ? (int)$v['id'] : 0;
        $vsku = trim($v['sku'] ?? '');
        $vsize = trim($v['size'] ?? '');
        $vcolor = trim($v['color'] ?? '');
        $vprice = ($v['price'] !== '') ? (float)$v['price'] : null;
        $vstock = (int)($v['stock'] ?? 0);

        // handle primary image file for this variant (either from $vfiles_single or legacy structure)
        $vImageRel = null;
        if ($vfiles_single) {
            // if structured as variants_files[name][idx] or variants_files[tmp_name][idx]
            if (isset($vfiles_single['tmp_name'][$idx]) && $vfiles_single['tmp_name'][$idx]) {
                $vf = [
                    'name'=>$vfiles_single['name'][$idx],
                    'type'=>$vfiles_single['type'][$idx],
                    'tmp_name'=>$vfiles_single['tmp_name'][$idx],
                    'error'=>$vfiles_single['error'][$idx],
                    'size'=>$vfiles_single['size'][$idx],
                ];
                $s = save_upload_file($vf);
                if ($s) $vImageRel = $s;
            }
        }

        // handle multi images for this variant (variants_files_multi[idx][])
        $vExtraPaths = [];
        if ($vfiles_multi && isset($vfiles_multi['tmp_name'][$idx]) && is_array($vfiles_multi['tmp_name'][$idx])) {
            foreach ($vfiles_multi['tmp_name'][$idx] as $k2 => $tmp2) {
                if (empty($tmp2)) continue;
                $vf = [
                    'name'=>$vfiles_multi['name'][$idx][$k2],
                    'type'=>$vfiles_multi['type'][$idx][$k2],
                    'tmp_name'=>$vfiles_multi['tmp_name'][$idx][$k2],
                    'error'=>$vfiles_multi['error'][$idx][$k2],
                    'size'=>$vfiles_multi['size'][$idx][$k2],
                ];
                $s = save_upload_file($vf);
                if ($s) $vExtraPaths[] = $s;
            }
        }

        // Existing extra images keep list for this variant index (paths)
        $existing_list = $variants_existing_images_list[$idx] ?? [];
        $existing_keep = $variants_existing_images_keep[$idx] ?? [];
        $existing_keep = is_array($existing_keep) ? $existing_keep : [];

        // Build final extra images array: keep those checked + newly uploaded
        $finalExtraImages = [];
        // keep existing ones that are in the keep list
        if (is_array($existing_list)) {
            foreach ($existing_list as $path) {
                $path = trim($path);
                if ($path === '') continue;
                if (in_array($path, $existing_keep)) {
                    $finalExtraImages[] = $path;
                } else {
                    // delete unchecked existing file
                    $abs = __DIR__ . '/../public/' . $path;
                    if (file_exists($abs)) @unlink($abs);
                }
            }
        }
        // append new uploaded ones
        $finalExtraImages = array_merge($finalExtraImages, $vExtraPaths);

        // now insert or update variant row
        if ($v_id > 0) {
            // update existing variant
            // if new primary image uploaded, delete old primary image
            if ($vImageRel) {
                // fetch old
                $oldq = $pdo->prepare("SELECT image, images FROM product_variants WHERE id = :id LIMIT 1");
                $oldq->execute(['id'=>$v_id]);
                $oldv = $oldq->fetch();
                if ($oldv && !empty($oldv['image'])) {
                    $oldAbs = __DIR__ . '/../public/' . $oldv['image'];
                    if (file_exists($oldAbs)) @unlink($oldAbs);
                }
                $upd = $pdo->prepare("UPDATE product_variants SET variant_sku=:vsku, size=:vsize, color=:vcolor, price=:vprice, stock=:vstock, image=:img, images=:images WHERE id = :id");
                $upd->execute([
                    'vsku'=>$vsku ?: null,
                    'vsize'=>$vsize ?: null,
                    'vcolor'=>$vcolor ?: null,
                    'vprice'=>$vprice,
                    'vstock'=>$vstock,
                    'img'=>$vImageRel,
                    'images'=>empty($finalExtraImages) ? null : json_encode(array_values($finalExtraImages)),
                    'id'=>$v_id
                ]);
            } else {
                // no new primary image, just update other fields and images
                $upd = $pdo->prepare("UPDATE product_variants SET variant_sku=:vsku, size=:vsize, color=:vcolor, price=:vprice, stock=:vstock, images=:images WHERE id = :id");
                $upd->execute([
                    'vsku'=>$vsku ?: null,
                    'vsize'=>$vsize ?: null,
                    'vcolor'=>$vcolor ?: null,
                    'vprice'=>$vprice,
                    'vstock'=>$vstock,
                    'images'=>empty($finalExtraImages) ? null : json_encode(array_values($finalExtraImages)),
                    'id'=>$v_id
                ]);
            }
            $postedVariantIds[] = $v_id;
        } else {
            // insert new variant
            $ins = $pdo->prepare("INSERT INTO product_variants (product_id, variant_sku, size, color, price, stock, image, images) VALUES (:pid, :vsku, :vsize, :vcolor, :vprice, :vstock, :img, :images)");
            $ins->execute([
                'pid'=>$id,
                'vsku'=>$vsku ?: null,
                'vsize'=>$vsize ?: null,
                'vcolor'=>$vcolor ?: null,
                'vprice'=>$vprice,
                'vstock'=>$vstock,
                'img'=>$vImageRel,
                'images'=>empty($finalExtraImages) ? null : json_encode(array_values($finalExtraImages))
            ]);
            $postedVariantIds[] = (int)$pdo->lastInsertId();
        }
    }

    // delete variants that were not posted (and remove their files)
    foreach ($existingVariantIds as $exid) {
        if (!in_array($exid, $postedVariantIds)) {
            // delete their files
            $vstmt = $pdo->prepare("SELECT image, images FROM product_variants WHERE id = :id LIMIT 1");
            $vstmt->execute(['id'=>$exid]);
            $vv = $vstmt->fetch();
            if ($vv) {
                if (!empty($vv['image'])) {
                    $abs = __DIR__ . '/../public/' . $vv['image'];
                    if (file_exists($abs)) @unlink($abs);
                }
                if (!empty($vv['images'])) {
                    $arr = json_decode($vv['images'], true);
                    if (!is_array($arr)) $arr = array_filter(array_map('trim', explode(',', $vv['images'])));
                    foreach ($arr as $p) {
                        $abs = __DIR__ . '/../public/' . $p;
                        if (file_exists($abs)) @unlink($abs);
                    }
                }
            }
            $pdo->prepare("DELETE FROM product_variants WHERE id = :id")->execute(['id'=>$exid]);
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    // consider logging $e->getMessage() to a file in production
    die("Save failed: " . htmlspecialchars($e->getMessage()));
}

header('Location: products.php?msg=ok');
exit;
