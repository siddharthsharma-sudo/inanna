<?php
$page_title = 'Product Detail – Inanna';
require_once __DIR__ . '/includes/db.php';
include 'includes/header.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id <= 0) {
    header('Location: shop.php');
    exit;
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo '<div class="container my-5 pt-5"><div class="alert alert-warning">Product not found. <a href="shop.php">Back to shop</a></div></div>';
    include 'includes/footer.php';
    exit;
}

// Fetch variants
$vstmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = :pid ORDER BY size, color, id");
$vstmt->execute(['pid' => $product_id]);
$variants = $vstmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for image resolution (reusing logic from product.php)
function resolve_img($path) {
    if (empty($path)) return 'assets/images/placeholder.webp';
    if (preg_match('#^https?://#i', $path)) return $path;
    return ltrim($path, '/');
}

// Build gallery
$gallery = [];
if (!empty($product['image'])) {
    $gallery[] = resolve_img($product['image']);
}
if (!empty($product['gallery'])) {
    $decoded = json_decode($product['gallery'], true);
    if (is_array($decoded)) {
        foreach ($decoded as $img) {
            $gallery[] = resolve_img($img);
        }
    }
}
$gallery = array_values(array_unique($gallery));

// Related products (from same category/gender)
$relStmt = $pdo->prepare("SELECT id, name as title, price, image as img FROM products WHERE id != :id AND gender = :gender LIMIT 8");
$relStmt->execute(['id' => $product_id, 'gender' => $product['gender'] ?? '']);
$relatedProducts = $relStmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($relatedProducts)) {
    $relatedProducts = $pdo->query("SELECT id, name as title, price, image as img FROM products WHERE id != $product_id LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
    body {
        background-color: #fff;
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }

    .product-detail-container {
        max-width: 1400px;
        margin: 30px auto;
        padding: 140px 40px 80px;
    }

    /* Header Visibility for Detail Page */
    /* By default (not scrolled), keep header white with black text */
    .main-navbar:not(.scrolled) {
        background-color: #fff !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .main-navbar:not(.scrolled) .nav-link,
    .main-navbar:not(.scrolled) .dropdown-toggle,
    .main-navbar:not(.scrolled) .text-dark,
    .main-navbar:not(.scrolled) .mobile-hamburger .bi-list {
        color: #000 !important;
    }

    .main-navbar:not(.scrolled) .navbar-brand img,
    .main-navbar:not(.scrolled) .cart-img {
        filter: none !important;
    }

    /* When scrolled, the header turns black (via style.css). 
       We ensure the text and icons are white and visible. */
    .main-navbar.scrolled:not(:hover) .nav-link,
    .main-navbar.scrolled:not(:hover) .dropdown-toggle,
    .main-navbar.scrolled:not(:hover) .text-dark,
    .main-navbar.scrolled:not(:hover) .mobile-hamburger .bi-list {
        color: #fff !important;
    }

    .main-navbar.scrolled:not(:hover) .navbar-brand img,
    .main-navbar.scrolled:not(:hover) .cart-img {
        filter: invert(1) brightness(200%) !important;
    }

    /* On hover while scrolled, the header turns white (via style.css). 
       We ensure the text and icons turn black. */
    .main-navbar.scrolled:hover .nav-link,
    .main-navbar.scrolled:hover .dropdown-toggle,
    .main-navbar.scrolled:hover .text-dark,
    .main-navbar.scrolled:hover .mobile-hamburger .bi-list {
        color: #000 !important;
    }

    .main-navbar.scrolled:hover .navbar-brand img,
    .main-navbar.scrolled:hover .cart-img {
        filter: none !important;
    }

    /* Smart Sticky Support */
    body.navbar-hidden .product-detail-container {
        padding-top: 80px;
    }

    .product-layout {
        display: grid;
        grid-template-columns: 0.8fr 0.15fr 1fr;
        gap: 40px;
        align-items: start;
    }

    /* Left: Main Image */
    .product-main-image {
        position: relative;
        height: fit-content;
    }

    .product-main-image img {
        width: 100%;
        height: auto;
        border-radius: 8px;
        object-fit: cover;
    }

    /* Middle: Thumbnails */
    .product-thumbnails {
        display: flex;
        flex-direction: column;
        gap: 15px;
        height: fit-content;
    }

    .thumb-item {
        width: 100%;
        aspect-ratio: 3/4;
        cursor: pointer;
        overflow: hidden;
        border-radius: 4px;
        border: 2px solid transparent;
        transition: border-color 0.3s;
    }

    .thumb-item.active {
        border-color: #000;
    }

    .thumb-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Right: Product Info */
    .product-meta {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .product-category {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 2px;
        color: #999;
    }

    .product-title-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-title-row h1 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 28px;
        font-weight: 500;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .product-actions-icons {
        display: flex;
        gap: 15px;
        font-size: 18px;
        color: #333;
    }

    .product-description {
        font-size: 13px;
        line-height: 1.6;
        color: #666;
    }

    .product-price {
        font-size: 18px;
        font-weight: 500;
        color: #000;
    }

    .price-subtext {
        font-size: 11px;
        color: #999;
        margin-top: 5px;
    }

    .product-color {
        font-size: 12px;
        color: #333;
    }

    /* Size Selector */
    .size-section {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 10px;
    }

    .size-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .size-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .size-guide-link {
        font-size: 11px;
        text-decoration: underline;
        color: #666;
        cursor: pointer;
    }

    /* Size Chart Modal */
    .appoint-size-chart-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 10000;
        overflow-y: auto;
        padding: 24px 16px;
        backdrop-filter: blur(4px);
    }

    .appoint-size-chart-modal.open {
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .appoint-size-chart-inner {
        background: #fff;
        border-radius: 4px;
        max-width: 720px;
        width: 100%;
        padding: 40px 32px;
        position: relative;
        margin: auto;
    }

    .appoint-size-chart-close {
        position: absolute;
        top: 16px;
        right: 20px;
        font-size: 22px;
        cursor: pointer;
        color: #999;
        background: none;
        border: none;
        line-height: 1;
    }

    .appoint-size-chart-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 28px;
        font-weight: 300;
        color: #000;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .appoint-size-chart-subtitle {
        font-size: 12px;
        color: #666;
        margin-bottom: 28px;
        letter-spacing: 0.05em;
    }

    .appoint-size-chart-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 28px;
        border-bottom: 1px solid #eee;
    }

    .appoint-size-tab {
        padding: 10px 24px;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        cursor: pointer;
        border: none;
        background: none;
        color: #999;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        transition: color 0.2s, border-color 0.2s;
    }

    .appoint-size-tab.active {
        color: #000;
        border-bottom-color: #000;
    }

    .appoint-size-chart-body {
        display: none;
    }

    .appoint-size-chart-body.active {
        display: block;
    }

    .appoint-body-figure-wrap {
        display: flex;
        gap: 32px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 28px;
    }

    .appoint-body-figure {
        flex: 0 0 120px;
        text-align: center;
    }

    .appoint-body-figure svg {
        width: 100px;
        height: auto;
        display: block;
        margin: 0 auto 8px;
    }

    .appoint-body-figure-label {
        font-size: 11px;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #999;
    }

    .appoint-measure-list {
        flex: 1;
        min-width: 200px;
    }

    .appoint-measure-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 13px;
    }

    .appoint-measure-item:last-child {
        border-bottom: none;
    }

    .appoint-measure-name {
        color: #666;
    }

    .appoint-measure-where {
        color: #000;
        font-weight: 500;
    }

    .appoint-size-table-wrap {
        overflow-x: auto;
    }

    table.appoint-size-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin-top: 8px;
    }

    .appoint-size-table th {
        background: #000;
        color: #fff;
        padding: 10px 14px;
        text-align: center;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .appoint-size-table td {
        padding: 10px 14px;
        text-align: center;
        border-bottom: 1px solid #eee;
        color: #000;
    }

    .appoint-size-table tr:nth-child(even) td {
        background: #fafafa;
    }

    .appoint-size-note {
        margin-top: 16px;
        font-size: 12px;
        color: #666;
        font-style: italic;
        line-height: 1.6;
    }

    .size-options {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .size-pill {
        border: 1px solid #ddd;
        padding: 8px 15px;
        font-size: 11px;
        min-width: 45px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        text-transform: uppercase;
    }

    .size-pill.selected {
        background: #000;
        color: #fff;
        border-color: #000;
    }

    .size-pill.custom-size {
        min-width: 100px;
    }

    /* Quantity */
    .quantity-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 15px;
    }

    .quantity-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .quantity-controls {
        display: flex;
        align-items: center;
        border: 1px solid #ddd;
    }

    .qty-btn {
        padding: 5px 15px;
        cursor: pointer;
        background: none;
        border: none;
        font-size: 16px;
    }

    .qty-input {
        width: 40px;
        text-align: center;
        border: none;
        font-size: 14px;
        outline: none;
    }

    .made-to-order {
        font-size: 11px;
        color: #999;
        font-style: italic;
    }

    /* Custom Size Block */
    #customSizeBlock {
        background: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 8px;
        margin-top: 20px;
        padding: 20px;
    }

    .measurement-field .form-control {
        text-align: center;
        font-weight: 600;
        background: #fff;
        border: 1px solid #ddd;
    }

    .measurement-label {
        font-weight: 600;
        font-size: .9rem;
        color: #333;
    }

    .muted-small {
        color: #999;
        font-size: .9rem;
    }

    .form-floating > label {
        color: #666 !important;
    }

    /* Add to Cart */
    .add-to-cart-btn {
        background: #000;
        color: #fff;
        border: none;
        padding: 16px;
        width: 100%;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 20px;
        transition: background 0.3s;
    }

    .add-to-cart-btn:hover {
        background: #333;
    }

    /* Accordions */
    .product-accordions {
        margin-top: 40px;
        border-top: 1px solid #eee;
    }

    .accordion-item {
        border-bottom: 1px solid #eee;
    }

    .accordion-header {
        padding: 18px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 1px;
        font-weight: 500;
    }

    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease;
        font-size: 13px;
        color: #666;
        line-height: 1.6;
    }

    .accordion-item.active .accordion-content {
        max-height: 200px;
        padding-bottom: 20px;
    }

    .accordion-item.active .bi-chevron-down {
        transform: rotate(180deg);
    }

    /* Related Products & Recently Viewed Slider */
    .related-products, .recently-viewed {
        margin-top: 100px;
        text-align: center;
        padding: 0 40px;
    }

    .related-title, .recently-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 24px;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 50px;
    }

    .related-slider {
        padding-bottom: 50px !important;
        height: 70vh;
    }

    .swiper-slide {
        height: 100%;
    }

    .related-card, .recent-card {
        text-decoration: none;
        color: inherit;
        text-align: left;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    /* Grid for Recently Viewed */
    .recent-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 30px;
        margin-top: 30px;
    }

    .recent-card {
        height: 70vh;
    }

    .related-image, .recent-image {
        width: 100%;
        flex: 1;
        overflow: hidden;
        background: #f5f5f5;
        border-radius: 8px;
    }

    .related-image img, .recent-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .related-card:hover img, .recent-card:hover img {
        transform: scale(1.05);
    }

    /* Product Info Styling (Consistent with Shop) */
    .product-info {
        padding: 15px 0;
        text-align: center;
    }

    .brand-name {
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 5px;
    }

    .product-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 16px;
        color: #000;
        text-transform: uppercase;
        margin: 5px 0;
        letter-spacing: 1px;
    }

    .price {
        font-size: 14px;
        font-weight: 400;
        color: #333;
    }

    /* Swiper Navigation Customization */
    .swiper-button-next, .swiper-button-prev {
        color: #000;
        background: rgba(255, 255, 255, 0.8);
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    .swiper-button-next::after, .swiper-button-prev::after {
        font-size: 18px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .product-layout {
            grid-template-columns: 1fr;
        }
        .product-thumbnails {
            flex-direction: row;
            order: 2;
            position: static;
        }
        .thumb-item {
            width: 80px;
        }
        .product-main-image {
            position: static;
        }
        .product-meta {
            order: 3;
        }
        .related-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .product-detail-container {
            padding: 100px 20px 40px;
        }
        .product-title-row h1 {
            font-size: 22px;
        }
        .related-title {
            font-size: 20px;
        }
    }
</style>

<div class="product-detail-container">
    <div class="product-layout">
        <!-- Left: Main Image -->
        <div class="product-main-image">
            <img id="mainImg" src="<?= htmlspecialchars($gallery[0] ?? 'assets/images/placeholder.webp') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- Middle: Thumbnails -->
        <div class="product-thumbnails">
            <?php foreach ($gallery as $idx => $imgSrc): ?>
            <div class="thumb-item <?= ($idx === 0) ? 'active' : '' ?>" onclick="updateMainImage(this)">
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="View <?= $idx + 1 ?>">
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Right: Info -->
        <div class="product-meta">
            <div class="product-category"><?= htmlspecialchars($product['category_slug'] ?? 'Couture') ?></div>
            <div class="product-title-row">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <div class="product-actions-icons">
                    <i class="bi bi-share" id="shareBtn" style="cursor: pointer;" title="Share Product"></i>
                </div>
            </div>

            <div class="product-description">
                <?= nl2br(htmlspecialchars($product['short_description'] ?? 'Experience the epitome of luxury with this handcrafted masterpiece. Detailed with intricate embroidery and tailored from premium fabrics, this ensemble is designed for unforgettable celebrations.')) ?>
            </div>

            <div class="product-price">
                MRP: ₹<?= number_format((float)($product['price'] ?? 0), 2) ?>
                <div class="price-subtext">Price included of all taxes</div>
            </div>

            <div class="product-color">
                <strong>Colour:</strong> <?= htmlspecialchars($product['color'] ?? 'Classic Edition') ?>
            </div>

            <div class="size-section">
                <div class="size-header">
                    <span class="size-label">Size</span>
                    <span class="size-guide-link" onclick="document.getElementById('appoint-sizeChartModal').classList.add('open')">Size Guide</span>
                </div>
                <div class="size-options">
                    <?php 
                    $displayed_sizes = [];
                    if (!empty($variants)): 
                        foreach ($variants as $idx => $v): 
                            $displayed_sizes[] = strtoupper(trim($v['size']));
                    ?>
                            <div class="size-pill <?= ($idx === 0) ? 'selected' : '' ?>" 
                                 onclick="selectSize(this)" 
                                 data-vid="<?= $v['id'] ?>"
                                 data-price="<?= number_format((float)($v['price'] ?: $product['price']), 2) ?>">
                                <?= htmlspecialchars($v['size']) ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php 
                        $default_sizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'];
                        foreach ($default_sizes as $sz):
                            $displayed_sizes[] = $sz;
                        ?>
                            <div class="size-pill <?= ($sz === 'S') ? 'selected' : '' ?>" onclick="selectSize(this)"><?= $sz ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php 
                    // Add 3XL and 4XL if they weren't in variants
                    $extra_sizes = ['3XL', '4XL'];
                    foreach ($extra_sizes as $extra):
                        if (!in_array($extra, $displayed_sizes)):
                    ?>
                        <div class="size-pill" onclick="selectSize(this)"><?= $extra ?></div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    <div class="size-pill custom-size" onclick="selectSize(this)">Custom Size</div>
                </div>
            </div>

            <div class="quantity-section">
                <span class="quantity-label">Quantity</span>
                <div class="quantity-controls">
                    <button class="qty-btn" onclick="updateQty(-1)">-</button>
                    <input type="text" class="qty-input" value="1" id="qtyInput">
                    <button class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
            </div>

            <!-- custom size block -->
            <div id="customSizeBlock" class="mt-3 p-3 border rounded" style="display:none;">
              <div class="muted-small mb-3">Please provide measurements in centimeters. We'll contact you if we need clarifications.</div>
              <div class="d-flex flex-wrap gap-2">
                <?php
                  $measures = ['Shoulder','Bust','Waist','Hip','Length','Arm Round','Thigh'];
                  foreach ($measures as $m):
                    $m_id = 'm_'.str_replace(' ','_',strtolower($m));
                ?>
                  <div class="form-floating measurement-field" style="min-width:120px;">
                    <input type="text" class="form-control" id="<?= $m_id ?>" name="measurements[<?= $m ?>]" placeholder="<?= $m ?>">
                    <label for="<?= $m_id ?>"><?= $m ?></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="made-to-order">Made to order: 8-10 weeks</div>

            <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart</button>

            <div class="product-accordions">
                <div class="accordion-item" onclick="toggleAccordion(this)">
                    <div class="accordion-header">
                        Product Description <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <?= nl2br(htmlspecialchars($product['long_description'] ?? $product['description'] ?? 'Experience the epitome of luxury with this handcrafted masterpiece. Detailed with intricate embroidery and tailored from premium fabrics, this ensemble is designed for unforgettable celebrations.')) ?>
                    </div>
                </div>
                <div class="accordion-item" onclick="toggleAccordion(this)">
                    <div class="accordion-header">
                        Product Details <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <?= nl2br(htmlspecialchars($product['details'] ?? 'This ensemble is meticulously crafted using high-quality materials. Features intricate hand embroidery, premium lining, and expert tailoring to ensure a perfect fit and luxurious feel.')) ?>
                    </div>
                </div>
                <div class="accordion-item" onclick="toggleAccordion(this)">
                    <div class="accordion-header">
                        Shipping, Packaging & Returns <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Complimentary shipping on all luxury orders. Comes in signature Inanna packaging. As this is a made-to-order piece, returns are only accepted in case of manufacturing defects.
                    </div>
                </div>
                <div class="accordion-item" onclick="toggleAccordion(this)">
                    <div class="accordion-header">
                        Disclaimer <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        Product color may slightly vary due to photographic lighting sources or your monitor settings. Each piece is handcrafted, so minor variations in embroidery are expected.
                    </div>
                </div>
                <div class="accordion-item" onclick="toggleAccordion(this)">
                    <div class="accordion-header">
                        Legal <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        All designs are property of INANNA. Intellectual property rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <div class="related-products">
        <h2 class="related-title">You May Also Like</h2>
        <div class="swiper related-slider">
            <div class="swiper-wrapper">
                <?php foreach ($relatedProducts as $p): ?>
                    <div class="swiper-slide">
                        <a href="product-detail.php?id=<?= $p['id'] ?>" class="related-card">
                            <div class="related-image">
                                <img src="<?= htmlspecialchars(resolve_img($p['img'])) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
                            </div>
                            <div class="product-info">
                                <div class="brand-name">INANNA</div>
                                <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
                                <div class="price">₹<?= number_format((float)$p['price'], 2) ?></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recently Viewed -->
    <div class="recently-viewed" id="recentSection" style="display: none;">
        <h2 class="recently-title">Recently Viewed</h2>
        <div class="recent-grid" id="recentWrapper">
            <!-- JS will inject products here -->
        </div>
    </div>
</div>

<!-- Size Chart Modal -->
<div class="appoint-size-chart-modal" id="appoint-sizeChartModal">
  <div class="appoint-size-chart-inner">
    <button class="appoint-size-chart-close" onclick="document.getElementById('appoint-sizeChartModal').classList.remove('open')">&times;</button>
    <h2 class="appoint-size-chart-title">Size Guide</h2>
    <p class="appoint-size-chart-subtitle">All measurements are in inches. When between sizes, size up.</p>
 
    <div class="appoint-size-chart-tabs">
      <button class="appoint-size-tab active" onclick="switchTab('female', this)">Women</button>
      <button class="appoint-size-tab" onclick="switchTab('male', this)">Men</button>
      <button class="appoint-size-tab" onclick="switchTab('how', this)">How to Measure</button>
    </div>
 
    <!-- WOMEN -->
    <div class="appoint-size-chart-body active" id="appoint-tab-female">
      <div class="appoint-size-table-wrap">
        <table class="appoint-size-table">
          <thead>
            <tr>
              <th>Size</th>
              <th>Bust</th>
              <th>Waist</th>
              <th>Hips</th>
              <th>Shoulder</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>XS</td><td>32</td><td>26</td><td>35</td><td>13.5</td></tr>
            <tr><td>S</td><td>34</td><td>28</td><td>37</td><td>14</td></tr>
            <tr><td>M</td><td>36</td><td>30</td><td>39</td><td>14.5</td></tr>
            <tr><td>L</td><td>38</td><td>32</td><td>41</td><td>15</td></tr>
            <tr><td>XL</td><td>40</td><td>34</td><td>43</td><td>15.5</td></tr>
            <tr><td>XXL</td><td>42</td><td>36</td><td>45</td><td>16</td></tr>
          </tbody>
        </table>
      </div>
      <p class="appoint-size-note">All measurements are in inches. For sarees and lehengas, hip and waist measurements are most important. For blouses and tops, go by bust and shoulder.</p>
    </div>
 
    <!-- MEN -->
    <div class="appoint-size-chart-body" id="appoint-tab-male">
      <div class="appoint-size-table-wrap">
        <table class="appoint-size-table">
          <thead>
            <tr>
              <th>Size</th>
              <th>Chest</th>
              <th>Waist</th>
              <th>Hips</th>
              <th>Shoulder</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>S</td><td>36</td><td>30</td><td>36</td><td>16</td></tr>
            <tr><td>M</td><td>38</td><td>32</td><td>38</td><td>17</td></tr>
            <tr><td>L</td><td>40</td><td>34</td><td>40</td><td>17.5</td></tr>
            <tr><td>XL</td><td>42</td><td>36</td><td>42</td><td>18</td></tr>
            <tr><td>XXL</td><td>44</td><td>38</td><td>44</td><td>18.5</td></tr>
            <tr><td>3XL</td><td>46</td><td>40</td><td>46</td><td>19</td></tr>
          </tbody>
        </table>
      </div>
      <p class="appoint-size-note">All measurements are in inches. For sherwanis and kurtas, chest and shoulder are the key measurements. Waist is critical for fitted bottoms.</p>
    </div>
 
    <!-- HOW TO MEASURE -->
    <div class="appoint-size-chart-body" id="appoint-tab-how">
      <div class="appoint-body-figure-wrap">
 
        <!-- Female figure SVG -->
        <div class="appoint-body-figure">
          <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle cx="50" cy="18" r="12" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Neck -->
            <line x1="50" y1="30" x2="50" y2="40" stroke="#000" stroke-width="1.5"/>
            <!-- Shoulders -->
            <path d="M28 45 Q50 38 72 45" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Bust line -->
            <path d="M30 62 Q50 57 70 62" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Body torso -->
            <path d="M28 45 L24 90 Q50 98 76 90 L72 45" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Waist line -->
            <path d="M26 78 Q50 72 74 78" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Hips line -->
            <path d="M20 105 Q50 100 80 105" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Skirt/legs -->
            <path d="M24 90 Q20 115 22 150 L40 150 L50 120 L60 150 L78 150 Q80 115 76 90 Q50 98 24 90Z" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Arms -->
            <path d="M28 45 L18 85" stroke="#000" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M72 45 L82 85" stroke="#000" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Shoulder arrow -->
            <text x="4" y="47" font-size="5.5" fill="#666" font-family="Montserrat">Shoulder</text>
            <!-- Bust arrow -->
            <text x="2" y="64" font-size="5.5" fill="#666" font-family="Montserrat">Bust</text>
            <!-- Waist arrow -->
            <text x="4" y="80" font-size="5.5" fill="#666" font-family="Montserrat">Waist</text>
            <!-- Hip arrow -->
            <text x="6" y="108" font-size="5.5" fill="#666" font-family="Montserrat">Hips</text>
          </svg>
          <span class="appoint-body-figure-label">Women</span>
        </div>
 
        <!-- Male figure SVG -->
        <div class="appoint-body-figure">
          <svg viewBox="0 0 100 220" xmlns="http://www.w3.org/2000/svg">
            <!-- Head -->
            <circle cx="50" cy="18" r="12" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Neck -->
            <line x1="50" y1="30" x2="50" y2="40" stroke="#000" stroke-width="1.5"/>
            <!-- Shoulders broad -->
            <path d="M22 48 Q50 40 78 48" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Chest line -->
            <path d="M24 62 Q50 58 76 62" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Torso -->
            <path d="M22 48 L24 95 Q50 100 76 95 L78 48" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Waist line -->
            <path d="M25 82 Q50 78 75 82" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Hip line -->
            <path d="M23 100 Q50 96 77 100" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="3,2"/>
            <!-- Legs -->
            <path d="M24 95 Q22 120 24 155 L42 155 L50 115 L58 155 L76 155 Q78 120 76 95 Q50 100 24 95Z" fill="none" stroke="#000" stroke-width="1.5"/>
            <!-- Arms -->
            <path d="M22 48 L14 90" stroke="#000" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M78 48 L86 90" stroke="#000" stroke-width="1.5" stroke-linecap="round"/>
            <!-- Labels -->
            <text x="2" y="50" font-size="5.5" fill="#666" font-family="Montserrat">Shoulder</text>
            <text x="2" y="64" font-size="5.5" fill="#666" font-family="Montserrat">Chest</text>
            <text x="4" y="84" font-size="5.5" fill="#666" font-family="Montserrat">Waist</text>
            <text x="6" y="102" font-size="5.5" fill="#666" font-family="Montserrat">Hips</text>
          </svg>
          <span class="appoint-body-figure-label">Men</span>
        </div>
 
        <!-- Measurement instructions -->
        <div class="appoint-measure-list">
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Bust / Chest</span>
            <span class="appoint-measure-where">Fullest part of your chest</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Waist</span>
            <span class="appoint-measure-where">Narrowest part of your torso</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Hips</span>
            <span class="appoint-measure-where">Fullest part of your hips</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Shoulder</span>
            <span class="appoint-measure-where">Across the back, shoulder to shoulder</span>
          </div>
          <div class="appoint-measure-item">
            <span class="appoint-measure-name">Length</span>
            <span class="appoint-measure-where">Shoulder to where you want it to end</span>
          </div>
        </div>
 
      </div>
      <p class="appoint-size-note">Use a soft measuring tape. Keep it parallel to the floor. Measure over your innerwear, not over heavy clothing. When in doubt, share your measurements with us and we'll guide you.</p>
    </div>
 
  </div>
</div>

<script>
    function switchTab(tab, el) {
        document.querySelectorAll('.appoint-size-chart-body').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.appoint-size-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('appoint-tab-' + tab).classList.add('active');
        el.classList.add('active');
    }

    // Close on outside click
    document.getElementById('appoint-sizeChartModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });
</script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    // --- Swiper Initializations ---
    const relatedSwiper = new Swiper('.related-slider', {
        slidesPerView: 2,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        speed: 800,
        breakpoints: {
            768: { slidesPerView: 3, spaceBetween: 30 },
            1024: { slidesPerView: 4, spaceBetween: 40 }
        }
    });

    // --- Recently Viewed Logic ---
    const currentProduct = {
        id: <?= $product['id'] ?>,
        title: "<?= addslashes($product['name']) ?>",
        price: "₹<?= number_format((float)$product['price'], 2) ?>",
        img: "<?= htmlspecialchars($gallery[0] ?? 'assets/images/placeholder.webp') ?>"
    };

    function updateRecentlyViewed() {
        let recent = JSON.parse(localStorage.getItem('recentProducts') || '[]');
        
        // Remove current product if it already exists to move it to the front
        recent = recent.filter(p => p.id !== currentProduct.id);
        
        // Add to the front
        recent.unshift(currentProduct);
        
        // Keep only top 8
        recent = recent.slice(0, 8);
        
        localStorage.setItem('recentProducts', JSON.stringify(recent));
        renderRecentlyViewed(recent);
    }

    function renderRecentlyViewed(recent) {
        const wrapper = document.getElementById('recentWrapper');
        const section = document.getElementById('recentSection');
        
        // Filter out the current product from display and limit to 4
        const displayList = recent.filter(p => p.id !== currentProduct.id).slice(0, 4);
        
        if (displayList.length === 0) return;
        
        section.style.display = 'block';
        wrapper.innerHTML = displayList.map(p => `
            <a href="product-detail.php?id=${p.id}" class="recent-card">
                <div class="recent-image">
                    <img src="${p.img}" alt="${p.title}">
                </div>
                <div class="product-info">
                    <div class="brand-name">INANNA</div>
                    <div class="product-title">${p.title}</div>
                    <div class="price">${p.price}</div>
                </div>
            </a>
        `).join('');
    }

    function addToCart() {
        const selectedSize = document.querySelector('.size-pill.selected');
        const variantId = selectedSize ? selectedSize.dataset.vid : '';
        const qty = document.getElementById('qtyInput').value;
        const productId = <?= $product['id'] ?>;

        if (!variantId && document.querySelectorAll('.size-pill[data-vid]').length > 0 && !selectedSize.classList.contains('custom-size') && !['XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', '4XL'].includes(selectedSize.textContent.trim())) {
            alert('Please select a size');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'add_to_cart.php';

        const fields = {
            product_id: productId,
            variant_id: variantId,
            qty: qty,
            redirect: 'cart.php'
        };

        // If no variantId but a standard size is selected, pass it as custom_size_text
        if (!variantId && selectedSize && !selectedSize.classList.contains('custom-size')) {
            fields.custom_size_text = 'Size: ' + selectedSize.textContent.trim();
        }

        for (const [name, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        // If custom size is selected, collect measurements
        if (selectedSize && selectedSize.classList.contains('custom-size')) {
            const measurements = document.querySelectorAll('#customSizeBlock .form-control');
            measurements.forEach(m => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = m.name;
                input.value = m.value;
                form.appendChild(input);
            });
            
            // Also set custom_size flag if needed by backend (though add_to_cart.php uses measurements array)
            const customFlag = document.createElement('input');
            customFlag.type = 'hidden';
            customFlag.name = 'custom_size';
            customFlag.value = '1';
            form.appendChild(customFlag);
        }

        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateRecentlyViewed();
        
        // Share Functionality
        const shareBtn = document.getElementById('shareBtn');
        if (shareBtn) {
            shareBtn.addEventListener('click', async () => {
                if (navigator.share) {
                    try {
                        await navigator.share({
                            title: "<?= addslashes($product['name']) ?>",
                            text: "Check out this <?= addslashes($product['name']) ?> at INANNA.",
                            url: window.location.href
                        });
                    } catch (err) {
                        console.error('Share failed:', err);
                    }
                } else {
                    // Fallback: Copy to clipboard
                    try {
                        await navigator.clipboard.writeText(window.location.href);
                        alert('Link copied to clipboard!');
                    } catch (err) {
                        console.error('Failed to copy:', err);
                    }
                }
            });
        }
    });

    // --- Original Functions ---
    function updateMainImage(thumb) {
        document.querySelectorAll('.thumb-item').forEach(i => i.classList.remove('active'));
        thumb.classList.add('active');
        document.getElementById('mainImg').src = thumb.querySelector('img').src;
    }

    function selectSize(pill) {
        document.querySelectorAll('.size-pill').forEach(p => p.classList.remove('selected'));
        pill.classList.add('selected');
        
        // Update price if variant price is different
        if (pill.dataset.price) {
            document.querySelector('.product-price').innerHTML = `MRP: ₹${pill.dataset.price} <div class="price-subtext">Price included of all taxes</div>`;
        }

        // Show/hide custom size block
        const customBlock = document.getElementById('customSizeBlock');
        if (pill.classList.contains('custom-size')) {
            customBlock.style.display = 'block';
        } else {
            customBlock.style.display = 'none';
        }
    }

    function updateQty(change) {
        const input = document.getElementById('qtyInput');
        let val = parseInt(input.value);
        val = isNaN(val) ? 1 : val + change;
        if (val < 1) val = 1;
        input.value = val;
    }

    function toggleAccordion(item) {
        const isActive = item.classList.contains('active');
        document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
