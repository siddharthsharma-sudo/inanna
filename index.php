<?php
// index.php - main homepage
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
// include products grid component (ensure file exists at includes/products_grid.php)
$productsGridPath = __DIR__ . '/includes/products_grid.php';
if (file_exists($productsGridPath)) {
    include $productsGridPath;
} else {
    // friendly fallback so page doesn't break
    echo '<section class="py-5"><div class="container"><div class="alert alert-warning">Products grid missing: includes/products_grid.php</div></div></section>';
}
?>


<!-- ========Collection============= -->
<?php

if (file_exists(__DIR__ . '/runway.php')) {
    require __DIR__ . '/runway.php';
}

if (file_exists(__DIR__ . '/collection.php')) {
    require __DIR__ . '/collection.php';
}

if (file_exists(__DIR__ . '/bare-essence.php')) {
    require __DIR__ . '/bare-essence.php';
}

if (file_exists(__DIR__ . '/scrollable.php')) {
    require __DIR__ . '/scrollable.php';
}
if (file_exists(__DIR__ . '/two-col-slider.php')) {
    require __DIR__ . '/two-col-slider.php';
}
?>



<?php
// include footer
include __DIR__ . '/includes/footer.php';
