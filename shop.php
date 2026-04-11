<?php
$page_title = 'Shop – Inanna';
require_once __DIR__ . '/includes/db.php';
include 'includes/header.php';

// Fetch all products from the database
try {
    $stmt = $pdo->query("SELECT id, name as title, price, image as img, gender as category, created_at as date FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $products = [];
}
?>

<style>
    body {
        background-color: #fff;
        margin: 0;
        font-family: 'Montserrat', sans-serif;
    }

    .shop-container {
        padding-top: 110px;
    }

    .shop-header {
        text-align: center;
        padding: 60px 0;
    }

    .shop-header h1 {
        font-family: 'Montserrat', sans-serif;
        font-size: 32px;
        font-weight: 400;
        letter-spacing: 12px;
        text-transform: uppercase;
        color: #333;
        margin: 0;
    }

    /* Header Visibility for Shop Page */
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

    /* Filter & Sort Bar */
    .filter-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 40px;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        font-size: 13px;
        background-color: #fff;
    }

    .filter-controls-left {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .filter-group {
        display: flex;
        gap: 20px;
    }

    .filter-link {
        text-decoration: none;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 500;
        transition: color 0.3s;
        cursor: pointer;
    }

    .filter-link.active {
        color: #000;
        border-bottom: 1px solid #000;
    }

    .filter-controls-right {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .results-info {
        display: flex;
        align-items: center;
        gap: 25px;
        color: #666;
    }

    /* Sort Dropdown */
    .sort-wrapper {
        position: relative;
        cursor: pointer;
    }

    .sort-trigger {
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sort-trigger::after {
        content: " \25BE";
        font-size: 10px;
        margin-left: 5px;
    }

    .sort-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: #fff;
        color: #333;
        min-width: 220px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: none;
        flex-direction: column;
        z-index: 1000;
        margin-top: 15px;
        border: 1px solid #eee;
    }

    .sort-dropdown.show {
        display: flex;
    }

    .sort-option {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        border-bottom: 1px solid #f9f9f9;
        cursor: pointer;
        transition: background 0.2s;
    }

    .sort-option:hover {
        background: #f9f9f9;
    }

    .radio-circle {
        width: 14px;
        height: 14px;
        border: 1px solid #ccc;
        border-radius: 50%;
        position: relative;
    }

    .sort-option.active .radio-circle {
        border-color: #000;
    }

    .sort-option.active .radio-circle::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 6px;
        height: 6px;
        background: #000;
        border-radius: 50%;
    }

    /* Grid View Switcher */
    .grid-view-switcher {
        display: flex;
        gap: 15px;
        align-items: center;
        border-left: 1px solid rgba(0, 0, 0, 0.1);
        padding-left: 25px;
    }

    .grid-btn {
        cursor: pointer;
        opacity: 0.3;
        transition: opacity 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .grid-btn.active {
        opacity: 1;
    }

    /* Grid Icons */
    .icon-2 {
        display: grid;
        grid-template-columns: repeat(2, 6px);
        gap: 2px;
    }
    .icon-2 div { width: 6px; height: 14px; border: 1px solid #000; }

    .icon-3 {
        display: grid;
        grid-template-columns: repeat(3, 4px);
        gap: 2px;
    }
    .icon-3 div { width: 4px; height: 14px; border: 1px solid #000; }

    .icon-4 {
        display: grid;
        grid-template-columns: repeat(4, 3px);
        gap: 2px;
    }
    .icon-4 div { width: 3px; height: 14px; border: 1px solid #000; }

    /* Product Grid */
    .product-grid {
        display: grid;
        gap: 30px;
        padding: 40px;
        background-color: #fff;
        transition: all 0.4s ease;
    }

    /* Grid Variants */
    .product-grid.grid-2 { grid-template-columns: repeat(2, 1fr); }
    .product-grid.grid-3 { grid-template-columns: repeat(3, 1fr); }
    .product-grid.grid-4 { grid-template-columns: repeat(4, 1fr); }

    .product-card {
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
        transition: transform 0.3s ease;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .image-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 3 / 4;
        background-color: #f5f5f5;
        overflow: hidden;
    }

    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

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

    /* Responsive */
    @media (max-width: 1024px) {
        .product-grid.grid-4 { grid-template-columns: repeat(3, 1fr); }
        .grid-btn[data-grid="4"] { display: none; }
    }

    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
            gap: 15px;
            padding: 15px 20px;
            height: auto;
            position: relative;
            top: 0;
        }
        .filter-controls-left, .filter-controls-right {
            width: 100%;
            justify-content: space-between;
        }
        .grid-view-switcher {
            border-left: none;
            padding-left: 0;
        }
        .product-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            padding: 20px;
            gap: 15px;
        }
        .grid-btn[data-grid="3"], .grid-btn[data-grid="4"] { display: none; }
    }
</style>

<div class="shop-container">
    <div class="shop-header">
        <h1>HIS & HERS COLLECTION</h1>
    </div>

    <section class="filter-section">
        <div class="filter-controls-left">
            <div class="filter-group">
                <a class="filter-link active" data-category="all">All</a>
                <a class="filter-link" data-category="men">Men</a>
                <a class="filter-link" data-category="women">Women</a>
            </div>
        </div>

        <div class="filter-controls-right">
            <div class="results-info">
                <span id="resultCount"><?php echo count($products); ?> LOOKS</span>
                <div class="sort-wrapper">
                    <span class="sort-trigger">Sort By</span>
                    <div class="sort-dropdown" id="sortDropdown">
                        <div class="sort-option active" data-sort="newest">
                            <div class="radio-circle"></div><span>Newest First</span>
                        </div>
                        <div class="sort-option" data-sort="low-high">
                            <div class="radio-circle"></div><span>Price: Low &rarr; High</span>
                        </div>
                        <div class="sort-option" data-sort="high-low">
                            <div class="radio-circle"></div><span>Price: High &rarr; Low</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid-view-switcher">
                <div class="grid-btn" data-grid="2" title="2 Grid">
                    <div class="icon-2"><div></div><div></div></div>
                </div>
                <div class="grid-btn active" data-grid="3" title="3 Grid">
                    <div class="icon-3"><div></div><div></div><div></div></div>
                </div>
                <div class="grid-btn" data-grid="4" title="4 Grid">
                    <div class="icon-4"><div></div><div></div><div></div><div></div></div>
                </div>
            </div>
        </div>
    </section>

    <main class="product-grid grid-3" id="productGrid">
        <?php
        foreach ($products as $p):
        ?>
            <a href="product-detail.php?id=<?= $p['id'] ?>" class="product-card" 
               data-category="<?= strtolower(htmlspecialchars($p['category'])) ?>" 
               data-price="<?= (float)$p['price'] ?>" 
               data-date="<?= htmlspecialchars($p['date']) ?>">
                <div class="image-wrapper">
                    <img src="<?= htmlspecialchars($p['img']) ?>" class="product-image" alt="<?= htmlspecialchars($p['title']) ?>">
                </div>
                <div class="product-info">
                    <div class="brand-name">INANNA</div>
                    <div class="product-title"><?= htmlspecialchars($p['title']) ?></div>
                    <div class="price">₹<?= number_format((float)$p['price'], 2) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productGrid = document.getElementById('productGrid');
    const gridBtns = document.querySelectorAll('.grid-btn');
    const filterLinks = document.querySelectorAll('.filter-link');
    const sortTrigger = document.querySelector('.sort-trigger');
    const sortDropdown = document.getElementById('sortDropdown');
    const sortOptions = document.querySelectorAll('.sort-option');
    const resultCount = document.getElementById('resultCount');
    const allProducts = Array.from(productGrid.children);

    // Grid Switcher
    gridBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const gridType = btn.dataset.grid;
            gridBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            productGrid.classList.remove('grid-2', 'grid-3', 'grid-4');
            productGrid.classList.add('grid-' + gridType);
        });
    });

    // Category Filter
    filterLinks.forEach(link => {
        link.addEventListener('click', () => {
            const cat = link.dataset.category;
            filterLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            
            filterProducts();
        });
    });

    // Sort Dropdown
    sortTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        sortDropdown.classList.toggle('show');
    });

    document.addEventListener('click', () => sortDropdown.classList.remove('show'));

    sortOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            sortOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            sortDropdown.classList.remove('show');
            
            filterProducts();
        });
    });

    function filterProducts() {
        const activeCategory = document.querySelector('.filter-link.active').dataset.category;
        const activeSort = document.querySelector('.sort-option.active').dataset.sort;

        let filtered = allProducts;
        
        // Apply category filter
        if (activeCategory !== 'all') {
            filtered = allProducts.filter(item => item.dataset.category === activeCategory);
        }

        // Apply sort
        filtered.sort((a, b) => {
            const priceA = parseInt(a.dataset.price);
            const priceB = parseInt(b.dataset.price);
            const dateA = new Date(a.dataset.date);
            const dateB = new Date(b.dataset.date);

            if (activeSort === 'low-high') return priceA - priceB;
            if (activeSort === 'high-low') return priceB - priceA;
            if (activeSort === 'newest') return dateB - dateA;
            return 0;
        });

        // Update UI
        productGrid.style.opacity = '0';
        setTimeout(() => {
            productGrid.innerHTML = '';
            filtered.forEach(item => productGrid.appendChild(item));
            resultCount.textContent = `${filtered.length} LOOKS`;
            productGrid.style.opacity = '1';
        }, 300);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
