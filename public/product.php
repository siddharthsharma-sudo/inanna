<?php
// public/product.php - product detail with Add to Cart (variant-aware)
// NOTE: assumes DB connection is at public/includes/db.php and header/footer are in includes/

require_once __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: products.php');
    exit;
}

// fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    include __DIR__ . '/includes/header.php';
    echo '<div class="container my-4"><div class="alert alert-warning">Product not found. <a href="products.php">Back to products</a></div></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

// fetch variants (if any)
$vstmt = $pdo->prepare("SELECT id, variant_sku, size, color, price, stock FROM product_variants WHERE product_id = :pid ORDER BY size, color, id");
$vstmt->execute(['pid' => $id]);
$variants = $vstmt->fetchAll();

$variants_json = json_encode($variants, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);

include __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
  <div class="row g-4">
    <div class="col-md-6">
      <?php if (!empty($product['image']) && file_exists(__DIR__ . '/' . $product['image'])): ?>
        <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
      <?php else: ?>
        <div style="height:360px;background:#f5f5f5;display:flex;align-items:center;justify-content:center;color:#999;">No image</div>
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <h2><?php echo htmlspecialchars($product['name']); ?></h2>
      <p class="text-muted">SKU: <?php echo htmlspecialchars($product['sku']); ?></p>

      <h4 class="text-primary">Price: <span id="price-display">₹<?php echo number_format($product['price'],2); ?></span></h4>
      <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

      <div class="small text-muted">Available stock: <span id="stock-display"><?php echo (int)$product['stock']; ?></span></div>

      <?php if (empty($variants)): ?>
        <!-- No variants: simple add-to-cart -->
        <form action="add_to_cart.php" method="post" class="row gx-2 gy-2 align-items-end mt-3">
          <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
          <input type="hidden" name="variant_id" value="">
          <input type="hidden" name="redirect" value="cart.php">
          <div class="col-auto">
            <label class="form-label small">Quantity</label>
            <input type="number" name="qty" value="1" min="1" max="<?php echo max(1,(int)$product['stock']); ?>" class="form-control" style="width:110px;">
          </div>
          <div class="col-auto">
            <label class="form-label small">&nbsp;</label>
            <button type="submit" class="btn btn-success">Add to cart</button>
          </div>
          <div class="col-auto">
            <label class="form-label small">&nbsp;</label>
            <button type="submit" formaction="place_order.php" class="btn btn-primary">Buy Now</button>
          </div>
        </form>

      <?php else: ?>
        <!-- Variants exist -->
        <div class="mt-3">
          <label class="form-label small">Choose option</label>

          <!-- If you want separate selects for size/color, uncomment and adjust as needed.
               For now we generate a single select with all variant combinations. -->
          <select id="variant-select" class="form-select mb-2">
            <option value="">-- Select variant --</option>
            <?php foreach ($variants as $v): 
              $label = trim(($v['size'] ?: '') . ($v['color'] ? ' / '.$v['color'] : ''));
              ?>
              <option value="<?php echo (int)$v['id']; ?>"
                      data-price="<?php echo ($v['price'] !== null && $v['price'] !== '') ? (float)$v['price'] : (float)$product['price']; ?>"
                      data-stock="<?php echo (int)$v['stock']; ?>"
                      data-sku="<?php echo htmlspecialchars($v['variant_sku']); ?>">
                <?php echo htmlspecialchars($label ?: ($v['variant_sku'] ?: 'Variant '.$v['id'])); ?> <?php if ($v['variant_sku']): ?> (<?php echo htmlspecialchars($v['variant_sku']); ?>)<?php endif; ?>
              </option>
            <?php endforeach; ?>
          </select>

          <form id="addcart" action="add_to_cart.php" method="post" class="row gx-2 gy-2 align-items-end">
            <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
            <input type="hidden" name="variant_id" id="variant_id" value="">
            <input type="hidden" name="redirect" value="cart.php">

            <div class="col-auto">
              <label class="form-label small">Quantity</label>
              <input type="number" name="qty" id="qty" value="1" min="1" class="form-control" style="width:110px;">
            </div>

            <div class="col-auto">
              <label class="form-label small">&nbsp;</label>
              <button type="submit" class="btn btn-success">Add to cart</button>
            </div>

            <div class="col-auto">
              <label class="form-label small">&nbsp;</label>
              <button type="submit" formaction="place_order.php" class="btn btn-primary">Buy Now</button>
            </div>
          </form>
        </div>
      <?php endif; ?>

      <div class="mt-3">
        <a href="products.php" class="btn btn-link">Back to products</a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const variants = <?php echo $variants_json; ?>;
  const select = document.getElementById('variant-select');
  const priceDisplay = document.getElementById('price-display');
  const stockDisplay = document.getElementById('stock-display');
  const qtyInput = document.getElementById('qty');
  const variantInput = document.getElementById('variant_id');

  if (!select) return;

  select.addEventListener('change', function(){
    const val = select.value;
    if (!val) {
      // reset to base product price & stock
      priceDisplay.textContent = '₹<?php echo number_format($product['price'],2); ?>';
      stockDisplay.textContent = '<?php echo (int)$product['stock']; ?>';
      variantInput.value = '';
      if (qtyInput) qtyInput.max = <?php echo (int)$product['stock']; ?>;
      return;
    }
    const idx = variants.findIndex(v => String(v.id) === String(val));
    if (idx === -1) return;
    const v = variants[idx];
    const p = (v.price === null || v.price === '') ? <?php echo (float)$product['price']; ?> : parseFloat(v.price);
    priceDisplay.textContent = '₹' + p.toFixed(2);
    stockDisplay.textContent = (v.stock !== null ? v.stock : '<?php echo (int)$product['stock']; ?>');
    variantInput.value = v.id;
    if (qtyInput) qtyInput.max = (v.stock !== null ? v.stock : <?php echo (int)$product['stock']; ?>);
  });

})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
