<?php
// public/index.php - main homepage
// Use includes/header.php and includes/footer.php inside the public folder

// If you later use sessions, you can start here
// session_start();

include __DIR__ . '/includes/header.php';
?>
<?php require "Hero_banner.php"; ?>

<div class="py-5 text-center">
  <div class="container">
     <h1 class="display-5">Welcome to My Shop</h1>
    <p class="lead">This is the homepage. Replace this content with your homepage HTML.</p>
    <p><a href="product.php" class="btn btn-primary">View Products</a></p>
  </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
