<?php
// cart.php (improved: live subtotal/total update + robust server-side update/delete)
require_once __DIR__ . '/includes/db.php';
session_start();

$cart = &$_SESSION['cart']; // reference for convenience
if (!is_array($cart)) $cart = [];

// message to show after POST operations
$msg = '';
$err = '';

// handle update/remove actions (POST)
// Note: we do NOT immediately redirect. We process the POST and continue rendering the page
// so the user sees the updated totals immediately. If you prefer redirect-on-post, restore header() redirect.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If a Delete button (single-item) was clicked, it will be present as 'delete' with the item key value
    if (!empty($_POST['delete'])) {
        $delKey = (string)$_POST['delete'];
        if (isset($_SESSION['cart'][$delKey])) {
            unset($_SESSION['cart'][$delKey]);
            $msg = 'Item removed from cart.';
        } else {
            $err = 'Item not found in cart.';
        }
    }

    // If batch update submitted
    if (!empty($_POST['update'])) {
        if (!empty($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $key => $q) {
                $key = (string)$key;
                // sanitize qty (integers only)
                $q = filter_var($q, FILTER_VALIDATE_INT, ['options'=>['default'=>0,'min_range'=>0]]);
                if ($q === 0) {
                    if (isset($_SESSION['cart'][$key])) {
                        unset($_SESSION['cart'][$key]);
                    }
                } else {
                    if (isset($_SESSION['cart'][$key])) {
                        // ensure it's stored as integer
                        $_SESSION['cart'][$key]['qty'] = (int)$q;
                    }
                }
            }
            $msg = 'Cart updated.';
        } else {
            $err = 'No quantities provided.';
        }
    }
}

// include header after processing POST so we can display messages
include __DIR__ . '/includes/header.php';
?>
<style>
  /* page-level styling */
  :root { --header-offset: 90px; }
  body { background: #6b0000 !important; }
  /* ensure cart content sits below fixed header */
  .cart-container { padding-top: var(--header-offset); padding-bottom: 40px; min-height: calc(100vh - var(--header-offset)); }
  /* card/table styling so content is readable on dark background */
  .card, .table { background: #fff; color: #212529; }
  .cart-empty { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.06); color:#fff; }
  .product-thumb { height:60px; width:60px; object-fit:cover; border-radius:6px; margin-right:8px; }
  .small-muted-dark { color:#6c757d; }
  .meas-key { font-weight:600; margin-right:6px; }
  .meas-val { color:#444; }
</style>

<div class="container cart-container" style="max-width:980px;">
  <h3 class="text-white mb-3">Your cart</h3>

  <?php if ($err): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>
  <?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
  <?php endif; ?>

  <?php if (empty($cart)): ?>
    <div class="card p-4 text-center text-muted">Your cart is empty. <a href="products.php">Continue shopping</a></div>
  <?php else: ?>
    <form method="post" id="cartForm">
      <table class="table table-hover align-middle" id="cartTable">
        <thead>
          <tr>
            <th>Product</th>
            <th style="width:140px">Price</th>
            <th style="width:120px">Qty</th>
            <th style="width:160px">Subtotal</th>
            <th style="width:120px">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php
          $total = 0.0;

          // small helper to render measurements object/array
          function render_measurements($m) {
              if (empty($m) || !is_array($m)) return '';
              $out = '<div class="small-muted-dark">';
              foreach ($m as $k => $v) {
                  $kEsc = htmlspecialchars((string)$k);
                  $vEsc = htmlspecialchars((string)$v);
                  $out .= '<div><span class="meas-key">'.$kEsc.':</span> <span class="meas-val">'.$vEsc.'</span></div>';
              }
              $out .= '</div>';
              return $out;
          }

          // Render rows and attach data-price attributes so JS can recalc
          foreach ($cart as $key => $it):
            // defensive cast & defaults
            $keyStr = (string)$key;
            $itPrice = isset($it['price']) ? (float)$it['price'] : 0.0;
            $itQty = isset($it['qty']) ? (int)$it['qty'] : 1;
            $subtotal = $itPrice * $itQty;
            $total += $subtotal;

            // image resolution — check file relative to public dir
            $imgSrc = '';
            if (!empty($it['image'])) {
                $try = __DIR__ . '/' . ltrim($it['image'], '/\\');
                if (file_exists($try)) {
                    // use as given (it's already a path accessible from web)
                    $imgSrc = $it['image'];
                } else {
                    // try with leading slash fallback (in case images stored as /uploads/...)
                    $imgSrc = '/' . ltrim($it['image'], '/\\');
                }
            }

            // determine whether this item is a custom-size item: either measurements array or custom_size_text exists
            $hasMeasurements = !empty($it['measurements']) && is_array($it['measurements']) && count(array_filter($it['measurements'], fn($v)=>trim((string)$v)!=='')) > 0;
            $hasCustomSizeText = !empty($it['custom_size_text']);

            // variant display fields (size/color/sku)
            $variant_size = !empty($it['variant_size']) ? (string)$it['variant_size'] : '';
            $variant_color = !empty($it['variant_color']) ? (string)$it['variant_color'] : '';
            $variant_sku = !empty($it['variant_sku']) ? (string)$it['variant_sku'] : '';
        ?>
          <tr data-key="<?php echo htmlspecialchars($keyStr); ?>" data-price="<?php echo number_format($itPrice, 2, '.', ''); ?>">
            <td>
              <div class="d-flex align-items-center">
                <?php if ($imgSrc): ?>
                  <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="product-thumb" alt="">
                <?php endif; ?>
                <div>
                  <div style="font-weight:600; color:#000;"><?php echo htmlspecialchars($it['name'] ?? 'Product'); ?></div>

                  <?php
                  // Show measurements/custom-size OR the simple variant size — NOT both.
                  if ($hasMeasurements || $hasCustomSizeText):
                      // Show only the measurements / custom-size text
                      if ($hasCustomSizeText && !$hasMeasurements):
                          // only custom_size_text
                          echo '<div class="small-muted-dark">Custom: ' . htmlspecialchars($it['custom_size_text']) . '</div>';
                      else:
                          // render the measurements (array)
                          echo render_measurements($it['measurements'] ?? []);
                          // if both are present prefer measurements list and ignore custom_size_text
                      endif;
                  else:
                      // No custom size chosen — show variant size/color/sku if any
                      if ($variant_size || $variant_color || $variant_sku):
                          $parts = [];
                          if ($variant_size) $parts[] = htmlspecialchars($variant_size);
                          if ($variant_color) $parts[] = htmlspecialchars($variant_color);
                          $label = implode(' / ', $parts);
                          echo '<div class="small text-muted">' . ($label ?: '') . ($variant_sku ? ' <span style="color:#6c757d">('.htmlspecialchars($variant_sku).')</span>' : '') . '</div>';
                      else:
                          // show variant id if present, otherwise nothing
                          if (!empty($it['variant_id'])) {
                              echo '<div class="small text-muted">Variant ID: '.(int)$it['variant_id'].'</div>';
                          }
                      endif;
                  endif;
                  ?>
                </div>
              </div>
            </td>

            <td style="vertical-align:middle;">₹<span class="row-price"><?php echo number_format($itPrice, 2); ?></span></td>

            <td style="vertical-align:middle;">
              <input class="qty-input form-control" type="number" name="qty[<?php echo htmlspecialchars($keyStr); ?>]" value="<?php echo (int)$itQty; ?>" min="0" style="width:100px;">
            </td>

            <td style="vertical-align:middle;">₹<span class="row-subtotal"><?php echo number_format($subtotal, 2); ?></span></td>

            <td style="vertical-align:middle;">
              <!-- Single-item delete button: posts with name "delete" and value = key -->
              <button type="submit" name="delete" value="<?php echo htmlspecialchars($keyStr); ?>" class="btn btn-sm btn-danger">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="3" class="text-end" style="color:#fff"><strong>Total</strong></td>
            <td>₹<strong id="cart-total"><?php echo number_format($total,2); ?></strong></td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <div class="d-flex gap-2">
        <button type="submit" name="update" value="1" class="btn btn-primary">Update cart</button>
        <a href="checkout.php" class="btn btn-success">Proceed to checkout</a>
        <a href="products.php" class="btn btn-outline-light">Continue shopping</a>
      </div>
    </form>

    <script>
      // Live subtotal & total update when user changes quantities (immediate UI feedback)
      (function(){
        const cartTable = document.getElementById('cartTable');
        const totalEl = document.getElementById('cart-total');

        function parseFloatSafe(v){
          v = String(v).replace(/[^0-9.\-]/g,'');
          const n = parseFloat(v);
          return isNaN(n) ? 0 : n;
        }

        function recalcTotals(){
          let sum = 0;
          cartTable.querySelectorAll('tbody tr[data-key]').forEach(row=>{
            const price = parseFloatSafe(row.getAttribute('data-price') || row.querySelector('.row-price')?.textContent || '0');
            const qtyInput = row.querySelector('.qty-input');
            const qty = qtyInput ? Math.max(0, parseInt(qtyInput.value || '0', 10)) : 0;
            const subtotal = price * qty;
            const subEl = row.querySelector('.row-subtotal');
            if (subEl) subEl.textContent = subtotal.toFixed(2);
            sum += subtotal;
          });
          if (totalEl) totalEl.textContent = sum.toFixed(2);
        }

        // attach handlers on each qty input
        cartTable.querySelectorAll('.qty-input').forEach(input=>{
          input.addEventListener('input', function(){
            // immediate recalc (throttling not needed for small tables)
            recalcTotals();
          });
          // also recalc when user leaves the field to ensure consistent state
          input.addEventListener('change', recalcTotals);
        });

        // initial recalc to be safe
        recalcTotals();
      })();
    </script>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
