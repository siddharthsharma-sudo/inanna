<?php
$page_title = 'His – Inanna';
include 'includes/header.php';
?>

<style>
    body {
        background-color: #1a1a1a;
        margin: 0;
    }

    .filter-section {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding: 10px 40px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 13px;
        margin-top: 90px;
        color: #fff;
        background-color: #1a1a1a;
        z-index: 99;
    }

    .filter-controls-right {
        display: flex;
        align-items: center;
        gap: 30px;
    }

    .grid-view-switcher {
        display: flex;
        gap: 12px;
        align-items: center;
        border-left: 1px solid rgba(255, 255, 255, 0.2);
        padding-left: 25px;
    }

    .grid-btn { cursor: pointer; opacity: 0.4; transition: opacity 0.3s; }
    .grid-btn.active { opacity: 1; }
    .icon-4 { display: grid; grid-template-columns: repeat(2, 6px); gap: 2px; }
    .icon-4 div { width: 6px; height: 6px; background: #fff; }
    .icon-2 { display: grid; grid-template-columns: 1fr; gap: 2px; }
    .icon-2 div { width: 14px; height: 6px; border: 1px solid #fff; }

    .results-info { display: flex; align-items: center; gap: 25px; }

    .sort-wrapper { position: relative; cursor: pointer; }
    .sort-trigger::after { content: " ▾"; font-size: 10px; margin-left: 5px; }
    .sort-dropdown {
        position: absolute; top: 100%; right: 0;
        background: #fffcf7; color: #333; min-width: 200px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        display: none; flex-direction: column; z-index: 1000;
        margin-top: 15px; border-radius: 2px;
    }
    .sort-dropdown.show { display: flex; }
    .sort-option { padding: 12px 20px; display: flex; align-items: center; gap: 12px; font-size: 14px; border-bottom: 1px solid #f0ede6; cursor: pointer; }
    .sort-option:hover { background: #f8f4eb; }
    .radio-circle { width: 16px; height: 16px; border: 1px solid #333; border-radius: 50%; position: relative; }
    .sort-option.active .radio-circle::after { content: ""; position: absolute; top: 3px; left: 3px; width: 8px; height: 8px; background: #b08d57; border-radius: 50%; }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 40px;
        background-color: #1a1a1a;
        transition: opacity 0.3s ease;
    }
    .product-grid.grid-2 { grid-template-columns: repeat(2, 1fr); padding: 40px 15%; }
    .product-card { display: flex; flex-direction: column; }
    .image-wrapper { position: relative; width: 100%; aspect-ratio: 2 / 3; background-color: #111; overflow: hidden; }
    .product-image { width: 100%; height: 100%; object-fit: cover; }
    .brand-name { font-size: 10px; color: #d4af37; text-transform: uppercase; margin-top: 15px; }
    .product-title { font-size: 12px; color: #fff; text-transform: uppercase; margin: 4px 0; }
    .price { font-size: 13px; font-weight: 600; color: #f0f0f0; }

    @media (max-width: 767px) {
        .filter-section { justify-content: space-between; padding: 10px 20px; }
        .filter-controls-right { width: 100%; justify-content: space-between; }
        .grid-view-switcher { display: flex; border-left: none; padding-left: 0; }
        .product-grid { grid-template-columns: repeat(2, 1fr); padding: 20px 10px; gap: 10px; }
        .product-grid.grid-2 { grid-template-columns: 1fr; padding: 20px 10px; }
    }
</style>

<section class="filter-section">
    <div class="filter-controls-right">
        <div class="results-info">
            <span>12 Results</span>
            <div class="sort-wrapper">
                <span class="sort-trigger">Sort</span>
                <div class="sort-dropdown" id="sortDropdown">
                    <div class="sort-option active" data-sort="default">
                        <div class="radio-circle"></div><span>Most Relevant</span>
                    </div>
                    <div class="sort-option" data-sort="low-high">
                        <div class="radio-circle"></div><span>Price Low To High</span>
                    </div>
                    <div class="sort-option" data-sort="high-low">
                        <div class="radio-circle"></div><span>Price High to Low</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="grid-view-switcher">
            <div class="grid-btn active" data-grid="4" title="4 Columns">
                <div class="icon-4"><div></div><div></div><div></div><div></div></div>
            </div>
            <div class="grid-btn" data-grid="2" title="2 Columns">
                <div class="icon-2"><div></div><div></div></div>
            </div>
        </div>
    </div>
</section>

<main class="product-grid" id="productGrid">
    <?php for ($i = 1; $i <= 12; $i++):
    $price = rand(45000, 250000);
?>
    <div class="product-card" data-price="<?php echo $price; ?>" data-index="<?php echo $i; ?>">
        <div class="image-wrapper">
            <img src="https://via.placeholder.com/400x600/111111/FFFFFF?text=His+<?php echo $i; ?>" class="product-image" alt="His Collection Item <?php echo $i; ?>">
        </div>
        <div class="brand-name">INANNA – HIS</div>
        <div class="product-title">PREMIER PIECE NO. <?php echo $i; ?></div>
        <div class="price">₹<?php echo number_format($price); ?></div>
    </div>
    <?php
endfor; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productGrid = document.getElementById('productGrid');
    const gridBtns = document.querySelectorAll('.grid-btn');
    const sortTrigger = document.querySelector('.sort-trigger');
    const sortDropdown = document.getElementById('sortDropdown');
    const sortOptions = document.querySelectorAll('.sort-option');

    gridBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            gridBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            productGrid.classList.toggle('grid-2', btn.dataset.grid === '2');
        });
    });

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

            const type = this.getAttribute('data-sort');
            const items = Array.from(productGrid.children);
            items.sort((a, b) => {
                const priceA = parseInt(a.dataset.price);
                const priceB = parseInt(b.dataset.price);
                const indexA = parseInt(a.dataset.index);
                const indexB = parseInt(b.dataset.index);
                if (type === 'low-high') return priceA - priceB;
                if (type === 'high-low') return priceB - priceA;
                return indexA - indexB;
            });

            productGrid.style.opacity = '0.5';
            setTimeout(() => {
                items.forEach(item => productGrid.appendChild(item));
                productGrid.style.opacity = '1';
            }, 150);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
