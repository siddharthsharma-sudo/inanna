<?php
// order_view.php
require_once __DIR__ . '/includes/db.php';
session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = (int)$_SESSION['user_id'];
if ($orderId <= 0) { header('Location: account.php'); exit; }

// fetch order and ensure it belongs to user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :uid LIMIT 1");
$stmt->execute(['id'=>$orderId,'uid'=>$uid]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) { header('Location: account.php'); exit; }

/**
 * helper: check whether a column exists in a table
 */
function column_exists(PDO $pdo, string $table, string $column): bool {
    try {
        $q = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c LIMIT 1");
        $q->execute(['t'=>$table, 'c'=>$column]);
        return (bool)$q->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

$oi_has_variant = column_exists($pdo, 'order_items', 'variant_id');

// If order_items has variant_id column -> join product_variants and display chosen variant
if ($oi_has_variant) {
    $sql = "
     SELECT oi.*, p.name AS product_name, p.image AS product_image,
            pv.size AS variant_size, pv.color AS variant_color, pv.variant_sku
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     LEFT JOIN product_variants pv ON pv.id = oi.variant_id
     WHERE oi.order_id = :oid
    ";
    $itemsStmt = $pdo->prepare($sql);
    $itemsStmt->execute(['oid'=>$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // order_items does not have variant_id: fetch items normally and we will attempt to show available variants for the product
    $sql = "
     SELECT oi.*, p.name AS product_name, p.image AS product_image
     FROM order_items oi
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = :oid
    ";
    $itemsStmt = $pdo->prepare($sql);
    $itemsStmt->execute(['oid'=>$orderId]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
}

// helper: get variants list for a product (size / color / sku)
function get_product_variants(PDO $pdo, int $product_id) : array {
    try {
        $q = $pdo->prepare("SELECT id, size, color, variant_sku FROM product_variants WHERE product_id = :pid ORDER BY size, color, id");
        $q->execute(['pid'=>$product_id]);
        return $q->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

include __DIR__ . '/includes/header.php';
?>
<style>
  :root { --header-offset: 90px; }
  body { background: #6b0000 !important; }
  /* ensure content starts below header */
  .order-main { padding-top: var(--header-offset); min-height: calc(100vh - var(--header-offset)); color: #fff; }
  .card { background: #fff; color: #212529; }
  .muted-on-dark { color: rgba(255,255,255,0.85); }
  .variant-available { font-size: .95rem; color: #333; background: rgba(255,255,255,0.9); padding:6px 8px; border-radius:6px; display:inline-block; margin-right:6px; margin-bottom:6px; }
</style>

<div class="container order-main my-4" style="max-width:900px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="text-white">Order #<?php echo (int)$order['id']; ?></h3>
    <div class="small text-white">Placed: <?php echo htmlspecialchars($order['created_at']); ?></div>
  </div>

  <div class="card p-3 mb-3">
    <div><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></div>
    <div><strong>Total:</strong> ₹<?php echo number_format($order['total'],2); ?></div>
    <div><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?> — <?php echo htmlspecialchars($order['customer_email']); ?></div>

    <div class="mt-2">
      <strong>Shipping address</strong><br>
      <?php
        $addr_lines = [];
        if (!empty($order['shipping_name'])) $addr_lines[] = htmlspecialchars($order['shipping_name']);
        if (!empty($order['shipping_phone'])) $addr_lines[] = htmlspecialchars($order['shipping_phone']);
        $addr_lines[] = htmlspecialchars($order['shipping_address_line1'] . ($order['shipping_address_line2'] ? ', '.$order['shipping_address_line2'] : ''));
        $addr_lines[] = htmlspecialchars($order['shipping_city'] . ($order['shipping_state'] ? ', '.$order['shipping_state'] : '') . ' - ' . $order['shipping_postal_code']);
        if (!empty($order['shipping_country'])) $addr_lines[] = htmlspecialchars($order['shipping_country']);
        echo implode('<br>', $addr_lines);
      ?>
    </div>
  </div>

  <div class="card p-3 mb-3">
    <h5>Items</h5>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead><tr><th>Product</th><th>Variant / Chosen</th><th>Qty</th><th>Price</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach ($items as $it):
            $subtotal = (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 0);
            $productId = (int)($it['product_id'] ?? 0);
          ?>
            <tr>
              <td style="vertical-align:middle;">
                <?php
                  if (!empty($it['product_image'])) {
                      $imgPath = __DIR__ . '/' . ltrim($it['product_image'], '/');
                      if (file_exists($imgPath)) {
                          echo '<img src="'.htmlspecialchars($it['product_image']).'" style="height:50px;object-fit:cover;margin-right:8px" alt="">';
                      }
                  }
                ?>
                <?php echo htmlspecialchars($it['product_name'] ?? ''); ?>
              </td>

              <td style="vertical-align:middle;">
                <?php if ($oi_has_variant): ?>
                  <?php
                    // variant info available from join
                    $vs = trim((string)($it['variant_size'] ?? ''));
                    $vc = trim((string)($it['variant_color'] ?? ''));
                    $vsku = trim((string)($it['variant_sku'] ?? ''));
                    $chosenLabel = '';
                    if ($vs !== '') $chosenLabel .= $vs;
                    if ($vc !== '') $chosenLabel .= ($chosenLabel ? ' / ' : '') . $vc;
                    if ($vsku !== '') $chosenLabel .= ($chosenLabel ? ' ' : '') . '(' . $vsku . ')';
                    echo $chosenLabel ? htmlspecialchars($chosenLabel) : '-';
                  ?>
                <?php else: ?>
                  <!-- variant was not recorded into order_items at order time -->
                  <div class="text-danger" style="font-weight:600;">Chosen variant: <span style="font-weight:700">not recorded</span></div>
                  <div style="margin-top:6px;">
                    <div class="small text-muted">Available variants for this product:</div>
                    <div style="margin-top:6px;">
                      <?php
                        $pvars = get_product_variants($pdo, $productId);
                        if (!empty($pvars)) {
                            foreach ($pvars as $pv) {
                                $lab = trim(($pv['size'] ?? '') . (($pv['color'] ?? '') ? ' / '.$pv['color'] : '') . (($pv['variant_sku'] ?? '') ? ' ('.$pv['variant_sku'].')' : ''));
                                echo '<span class="variant-available">'.htmlspecialchars($lab ?: ('#'.$pv['id'])).'</span>';
                            }
                        } else {
                            echo '<div class="small text-muted">No variants available for this product.</div>';
                        }
                      ?>
                    </div>
                  </div>
                <?php endif; ?>
              </td>

              <td style="vertical-align:middle;"><?php echo (int)($it['qty'] ?? 0); ?></td>
              <td style="vertical-align:middle;">₹<?php echo number_format((float)($it['price'] ?? 0),2); ?></td>
              <td style="vertical-align:middle;">₹<?php echo number_format($subtotal,2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    <a href="account.php" class="btn btn-outline-secondary">Back to account</a>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
