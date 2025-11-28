<?php
// If session needed later, enable:
// if (session_status() === PHP_SESSION_NONE) session_start();
?>
<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inanna</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        /* ------------------------------------
           SIDEBAR (MOBILE ONLY)
        ------------------------------------ */
        .sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: #fff;
            transition: left 0.35s ease;
            z-index: 99999;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        /* Close Arrow */
        .sidebar-close {
            font-size: 2rem;
            cursor: pointer;
        }

        /* Sidebar links list */
        .sidebar-links a {
            display: block;
            padding: 15px 10px;
            font-size: 20px;
            text-decoration: none;
            color: #000;
        }

        .sidebar-links a:hover { background: #f2f2f2; }

        /* Account button pinned at bottom */
        .sidebar-bottom {
            margin-top: auto;
            padding: 20px 10px;
            border-top: 1px solid #ddd;
            font-size: 22px;
        }

        /* ------------------------------------
           NAVBAR (DESKTOP + MOBILE)
        ------------------------------------ */
        .main-navbar {
            height: 120px;
            background: #fff !important;
            border-bottom: 1px solid #e5e5e5;
        }
        .custom-navbar {
            max-width: 1424px;
        }

        /* Cart image */
        .cart-img {
            width: 36px; height: 36px;
        }

        /* Hide desktop nav on mobile */
        @media (max-width: 768px) {
            .desktop-links {
                display: none !important;
            }
        }

        /* Hide hamburger on desktop */
        @media (min-width: 992px) {
            .mobile-hamburger {
                display: none !important;
            }
        }
    </style>

</head>

<body>

<!-- ======================================
     FULL SCREEN SIDEBAR (Mobile)
======================================= -->
<div id="sidebar" class="sidebar">

    <!-- Close Arrow -->
    <div class="sidebar-close mb-4" onclick="closeSidebar()">
        <i class="bi bi-arrow-left"></i>
    </div>

    <!-- Sidebar Links -->
    <div class="sidebar-links">
        <a href="#">Home</a>
        <a href="#">WOI</a>
        <a href="#">Appointment</a>
        <a href="#">Shop</a>
        <a href="#">Our Collection</a>
    </div>

    <!-- Bottom Account -->
    <div class="sidebar-bottom">
        <i class="bi bi-person fs-3"></i>Account</div>
   </div>



<!-- ======================================
     MAIN NAVBAR
======================================= -->
<nav id="mainNav" class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container custom-navbar d-flex align-items-center justify-content-between">

        <!-- HAMBURGER (Mobile Only) -->
        <button class="btn border-0 fs-1 mobile-hamburger" onclick="openSidebar()">
            <i class="bi bi-list"></i>
        </button>

        <!-- LEFT LINKS (Desktop Only) -->
        <ul class="navbar-nav gap-lg-4 desktop-links">
            <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="#">WOI</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Appointment</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Shop</a></li>
            <li class="nav-item"><a class="nav-link" href="#">Our Collection</a></li>
        </ul>

        <!-- CENTER LOGO -->
        <a class="navbar-brand mx-auto" href="#">
            <img src="https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/w:1920/h:689/q:mauto/f:best/dpr:2/https://worldofinanna.org/wp-content/uploads/2024/05/cropped-Norris-logo-copy-01-1.png"
             height="55" alt="Logo">
        </a>

        <!-- RIGHT ICONS (Always Visible) -->
        <div class="d-flex align-items-center gap-4">
            <a href="#"><img src="public/assets/images/shopping-cart.webp" class="cart-img"></a>
            <a href="#" class="text-dark text-decoration-none">Account</a>
        </div>

    </div>
</nav>


<!-- PAGE CONTENT -->



<!-- JAVASCRIPT -->
<script>
function openSidebar() {
    document.getElementById("sidebar").style.left = "0";
}
function closeSidebar() {
    document.getElementById("sidebar").style.left = "-100%";
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

