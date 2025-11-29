<?php
// public/index.php - main homepage
// Use includes/header.php and includes/footer.php inside the public folder

// If you later use sessions, you can start here
// session_start();

$pageTitle = 'Inanna · Home';

// include header
include __DIR__ . '/includes/header.php';

// include hero banner (use __DIR__ to ensure correct path)
if (file_exists(__DIR__ . '/Hero_banner.php')) {
    require __DIR__ . '/Hero_banner.php';
}
?>

<!-- =========== Product Listing ====== -->
<?php
// include products grid component (ensure file exists at public/includes/products_grid.php)
$productsGridPath = __DIR__ . '/includes/products_grid.php';
if (file_exists($productsGridPath)) {
    include $productsGridPath;
} else {
    // friendly fallback so page doesn't break
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Products grid missing: includes/products_grid.php</div></div></section>';
}
?>

<!-- ====== Second section (promo) ========================== -->
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');

:root {
    --accent-gradient-start: #e53c7a;
    --accent-gradient-end: #c90f5c;
    --text-color: #fff;
}

.promo-container {
    display: flex;
    max-width: 1200px;
    margin: 0 auto;
    gap: 20px;
    padding: 40px 20px;
    flex-wrap: wrap;
    justify-content: center;
    font-family: 'Playfair Display', serif;
}

.promo-card {
    flex: 1 1 45%;
    min-width: 350px;
    height: 600px;
    position: relative;
    overflow: hidden;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 30px;
}

.promo-card img {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    object-fit: cover;
    mix-blend-mode: luminosity;
    opacity: 0.8;
    z-index: 1;
}

.text-content-positioned {
    position: absolute;
    z-index: 2;
    color: var(--text-color);
    padding: 30px;
    box-sizing: border-box;
    width: 100%;
    top: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.main-headline {
    font-size: 2.5rem;
    font-weight: 600;
    line-height: 1.1;
    text-transform: uppercase;
    letter-spacing: 2px;
    margin: 0;
    padding-top: 50px;
}

.main-headline span { display: block; }

.card-note {
    font-size: 1rem;
    font-weight: 400;
    margin-top: 20px;
    line-height: 1.5;
    margin-bottom: auto;
}

.card-one .main-headline { text-align: right; margin-left: auto; max-width: 80%; }
.card-one .card-note     { text-align: right; margin-left: auto; max-width: 60%; }
.card-one .cta-button-wrapper { align-self: flex-end; }

.card-two .main-headline { text-align: left; margin-right: auto; max-width: 80%; }
.card-two .card-note     { text-align: left; margin-right: auto; max-width: 70%; }
.card-two .cta-button-wrapper { align-self: flex-start; }

.cta-button-wrapper { position: relative; z-index: 2; margin-top: auto; display: flex; padding-top: 20px; }

.cta-button {
    background: linear-gradient(to right, var(--accent-gradient-start), var(--accent-gradient-end));
    color: var(--text-color);
    border: none;
    padding: 15px 30px;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    border-radius: 5px;
    transition: opacity 0.3s ease;
    white-space: nowrap;
}

.cta-button:hover { opacity: 0.85; }

@media (max-width: 768px) {
    .promo-card { flex: 1 1 100%; height: 500px; }
    .card-one .main-headline,
    .card-one .card-note,
    .card-one .cta-button-wrapper,
    .card-two .main-headline,
    .card-two .card-note,
    .card-two .cta-button-wrapper {
        text-align: center;
        margin-left: auto;
        margin-right: auto;
        max-width: 90%;
        align-self: center;
        justify-content: center;
    }
    .cta-button-wrapper { justify-content: center; }
    .main-headline { font-size: 2rem; padding-top: 30px; }
    .card-note { font-size: 0.9rem; }
}
</style>

<div class="promo-container">
    <div class="promo-card card-one">
        <img src="images/model_yellow_card1.jpg" alt="Model sitting on a box in a yellow and red setting.">
        <div class="text-content-positioned">
            <div>
                <h2 class="main-headline">
                    WE DON'T RESTOCK 
                    <span>WE REINVENT</span>
                </h2>
                <p class="card-note">INANNA isn't about trends—it's about transformation.</p>
            </div>
            <div class="cta-button-wrapper">
                <a href="products.php" class="cta-button">Secure your piece</a>
            </div>
        </div>
    </div>

    <div class="promo-card card-two">
        <img src="images/model_yellow_card2.jpg" alt="Model lying down in a brown dress in a yellow and red setting.">
        <div class="text-content-positioned">
            <div>
                <h2 class="main-headline">
                    EXCLUSIVITY 
                    <span>ISN'T OFFERED</span>
                    <span>IT'S CREATED</span>
                </h2>
                <p class="card-note">Every 15 days a new drop. <br>No repeats no apologies.</p>
            </div>
            <div class="cta-button-wrapper">
                <a href="products.php" class="cta-button">SHOP NOW</a>
            </div>
        </div>
    </div>
</div>

<!-- ========Collection============= -->
<?php
if (file_exists(__DIR__ . '/collection.php')) {
    require __DIR__ . '/collection.php';
}

if (file_exists(__DIR__ . '/bare-essence.php')) {
    require __DIR__ . '/bare-essence.php';
}

if (file_exists(__DIR__ . '/scrollable.php')) {
    require __DIR__ . '/scrollable.php';
}
?>

<div class="py-5 text-center">
  <div class="container">
     <h1 class="display-5">Welcome to My Shop</h1>
    <p class="lead">This is the homepage. Replace this content with your homepage HTML.</p>
    <p><a href="products.php" class="btn btn-primary">View Products</a></p>
  </div>
</div>

<?php
// include footer
include __DIR__ . '/includes/footer.php';
