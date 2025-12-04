<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Compatibility: if older code set separate session vars, normalize them into $_SESSION['user']
if (empty($_SESSION['user'])) {
    // public user id/name shape
    if (!empty($_SESSION['user_id'])) {
        $_SESSION['user'] = [
            'id' => (int)$_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'username' => $_SESSION['user_email'] ?? null,
            'is_admin' => !empty($_SESSION['is_admin']) ? 1 : 0
        ];
    }
    // admin login shape
    if (!empty($_SESSION['admin_id'])) {
        $_SESSION['user'] = [
            'id' => (int)$_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'] ?? '',
            'username' => $_SESSION['admin_email'] ?? null,
            'is_admin' => 1
        ];
    }
}

// Optionally load a config file if you have DB connection or helper functions there.
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

// Simple helper: get cart count
function getCartCount(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $k => $v) {
        if (is_array($v) && isset($v['qty'])) $count += (int)$v['qty'];
        else $count++;
    }
    return $count;
}

// Handle logout POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_action']) && $_POST['logout_action'] === 'logout') {
    // Clear session data & redirect to index
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

// Determine auth state
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']);
$userName = $isLoggedIn ? ( $_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Account' ) : null;
$isAdmin = $isLoggedIn && !empty($_SESSION['user']['is_admin']);

$cartCount = getCartCount();

// Optionally load categories from DB
$categories = [];
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name LIMIT 50");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        // ignore DB errors here
    }
} elseif (isset($conn) && is_resource($conn)) {
    try {
        $res = mysqli_query($conn, "SELECT id, name, slug FROM categories ORDER BY name LIMIT 50");
        while ($r = mysqli_fetch_assoc($res)) $categories[] = $r;
    } catch (Throwable $e) {
        // ignore
    }
}

// Utility: active class by filename
function isActive($filename) {
    $current = basename($_SERVER['PHP_SELF'] ?? 'index.php');
    return $current === $filename ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($page_title ?? 'Inanna'); ?></title>
    <?php if (!empty($meta_description)): ?>
      <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>" />
    <?php endif; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/footer_logo.webp">
    
</head>
<body>

<div id="sidebar" class="sidebar" aria-hidden="true">
    <div class="sidebar-close mb-4" onclick="closeSidebar()" aria-label="Close menu">
        <i class="bi bi-arrow-left"></i>
    </div>

    <div class="sidebar-links">
        <a href="index.php">Home</a>
        <a href="woi.php">WOI</a>
        <a href="appointment.php">Appointment</a>
        <a href="products.php">Shop</a>
        <div>
            <strong>Our Collection</strong>
            <div style="padding-left:10px;">
                <a href="no-plus-one.php">No Plus One</a>
                <a href="the-guest-list.php">The Guest List</a>
                <a href="till-sunrise.php">Till Sunrise</a>
            </div>
        </div>

        <?php if (!empty($categories)): ?>
            <hr>
            <strong>Categories</strong>
            <?php foreach ($categories as $cat): ?>
                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"><?= htmlspecialchars($cat['name']) ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="sidebar-bottom">
        <i class="bi bi-person fs-3"></i>
        <?php if ($isLoggedIn): ?>
            <div><?= htmlspecialchars($userName) ?></div>
            <form method="post" class="mt-2">
                <input type="hidden" name="logout_action" value="logout">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
            </form>
        <?php else: ?>
            <div class="mt-2">
                <a href="login.php" class="text-decoration-none">Login</a> ·
                <a href="register.php" class="text-decoration-none">Register</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<nav id="mainNav" class="navbar navbar-expand-lg main-navbar fixed-top">
    <div class="container custom-navbar d-flex align-items-center justify-content-between">

        <ul class="navbar-nav gap-lg-4 desktop-links">
            <li class="nav-item"><a class="nav-link<?= isActive('index.php') ?>" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('woi.php') ?>" href="woi.php">WOI</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('appointment.php') ?>" href="appointment.php">Appointment</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('products.php') ?>" href="products.php">Shop</a></li>
            <li class="nav-item our-collection">
              <a class="nav-link" href="#" role="button">Our Collection</a>
              <button class="collections-dot d-lg-none" type="button" aria-label="Open collections"></button>
              <div class="mega-collections">
                <div class="mega-inner">
                  <div class="collection-list">
                  <div class="collection-heading">Collections</div>
                    <a class="collection-item is-active" href="no-plus-one.php" data-title="No Plus One" data-images="assets/images/noplus/img5.webp,assets/images/noplus/img7.webp,assets/images/noplus/img8.webp">No Plus One</a>
                    <a class="collection-item" href="the-guest-list.php" data-title="The Guest List" data-images="assets/images/theguestlist/img-1.webp,assets/images/theguestlist/img-2.webp,assets/images/theguestlist/img-3.webp">The Guest List</a>
                    <a class="collection-item" href="till-sunrise.php" data-title="Till Sunrise" data-images="assets/images/tillsunrise/img1.avif,assets/images/tillsunrise/img3.webp,assets/images/tillsunrise/img7.webp">Till Sunrise</a>
                  </div>
                  <div class="collection-preview">
                    <div class="preview-title">No Plus One</div>
                    <div class="preview-grid">
                      <img alt="" />
                      <img alt="" />
                      <img alt="" />
                    </div>
                  </div>
                </div>
              </div>
            </li>
        </ul>

        <div class="navbar-brand mx-auto d-flex align-items-center justify-content-center">
            <img id="navLogo" class="navbar-logo" src="assets/images/logo-inanna.avif" alt="Inanna">
        </div>
        
        <div class="d-flex align-items-center gap-2">

        <a href="cart.php" class="position-relative d-inline-block" aria-label="View cart">
                <img src="assets/images/shopping-bag.svg" class="cart-img" alt="Cart">
            <?php if ($cartCount > 0): ?>
                <span style="position:absolute; top:-6px; right:-6px;" class="badge rounded-pill bg-danger">
                    <?= $cartCount ?>
                </span>
            <?php endif; ?>
        </a>

            <?php if ($isLoggedIn): ?>
                <div class="dropdown">
                    <a class="text-dark text-decoration-none dropdown-toggle" href="#" role="button" id="accountMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= htmlspecialchars($userName) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="accountMenu">
                        <li><a class="dropdown-item" href="account.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" class="px-3 py-2 m-0">
                                <input type="hidden" name="logout_action" value="logout">
                                <button type="submit" class="btn btn-link text-decoration-none p-0">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div>
                    <a href="login.php" class="text-dark text-decoration-none me-2">Login</a>
                    <a href="register.php" class="text-dark text-decoration-none">Register</a>
                </div>
            <?php endif; ?>

            <button class="btn border-0 fs-1 mobile-hamburger d-lg-none" onclick="openSidebar()" aria-label="Open menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function(){
  var oc = document.querySelector('.our-collection');
  if (!oc) return;
  var panel = oc.querySelector('.mega-collections');
  var trigger = oc.querySelector('.nav-link');
  var hideTimer;
  function openPanel(){ oc.classList.add('open'); }
  function scheduleClose(){ clearTimeout(hideTimer); hideTimer = setTimeout(function(){ oc.classList.remove('open'); }, 300); }
  function cancelClose(){ clearTimeout(hideTimer); }
  ['mouseenter','focusin'].forEach(function(evt){ oc.addEventListener(evt, function(){ openPanel(); }); });
  ['mouseleave','focusout'].forEach(function(evt){ oc.addEventListener(evt, function(e){ if (!oc.contains(e.relatedTarget)) scheduleClose(); }); });
  if (panel) {
    panel.addEventListener('mouseenter', cancelClose);
    panel.addEventListener('mouseleave', scheduleClose);
  }
  if (trigger) {
    trigger.addEventListener('mouseenter', openPanel);
    trigger.addEventListener('mouseleave', scheduleClose);
    trigger.addEventListener('focusin', openPanel);
    trigger.addEventListener('focusout', scheduleClose);
  }
  var items = oc.querySelectorAll('.collection-item');
  var titleEl = oc.querySelector('.preview-title');
  var imgs = oc.querySelectorAll('.preview-grid img');
  function applyItem(btn){
    items.forEach(function(b){ b.classList.remove('is-active'); });
    btn.classList.add('is-active');
    var t = btn.getAttribute('data-title') || '';
    var arr = (btn.getAttribute('data-images') || '').split(',');
    if (titleEl) titleEl.textContent = t;
    imgs.forEach(function(img, i){
      img.classList.remove('loaded');
      var src = arr[i] || '';
      if (src) {
        if (img.src === src) {
          setTimeout(function(){ img.classList.add('loaded'); }, 30);
        } else {
          img.onload = function(){ img.classList.add('loaded'); img.onload = null; };
          img.src = src;
        }
      } else {
        img.removeAttribute('src');
      }
    });
  }
  var initial = oc.querySelector('.collection-item.is-active') || items[0];
  if (initial) applyItem(initial);
  items.forEach(function(btn){
    btn.addEventListener('mouseenter', function(){ if (window.innerWidth >= 992) applyItem(btn); });
    btn.addEventListener('click', function(){ /* allow navigation */ });
  });
  var dot = oc.querySelector('.collections-dot');
  if (dot) dot.addEventListener('click', function(){ oc.classList.toggle('open'); });
});
</script>

<script>
function openSidebar() {
    document.getElementById("sidebar").style.left = "0";
    document.getElementById("sidebar").setAttribute('aria-hidden','false');
}
function closeSidebar() {
    document.getElementById("sidebar").style.left = "-100%";
    document.getElementById("sidebar").setAttribute('aria-hidden','true');
}
</script>

<script>
// === SIMPLE NAVBAR LOGO SCROLL ANIMATION ===
(function(){
  var nav = document.getElementById('mainNav');
  var logo = document.getElementById('navLogo');
  var ticking = false;
  var path = window.location.pathname.split('/').pop(); 
var isHome = (path === '' || path === 'index.php');

  function applyTransform(y){
    if (!logo) return;
    if (!isHome) { logo.style.transform = 'scale(1) translateY(0)'; return; }
    var p = Math.min(Math.max(y / 150, 0), 1);
    var scale = 2.8 + (1 - 2.8) * p;
    var ty = 40 + (0 - 40) * p;
    logo.style.transform = 'scale(' + scale + ') translateY(' + ty + 'px)';
  }

  function update(){
    var y = window.scrollY || 0;
    applyTransform(y);
    if (y > 50) nav.classList.add('scrolled'); else nav.classList.remove('scrolled');
    ticking = false;
  }

  function onScroll(){
    if (!ticking) { requestAnimationFrame(update); ticking = true; }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('load', function(){ update(); });
  window.addEventListener('resize', function(){ update(); });
  update();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// (Your custom Bootstrap dropdown fallback logic remains here)
(function () {
 // Safe DOM-ready
 function ready(fn) {
 if (document.readyState !== 'loading') fn();
 else document.addEventListener('DOMContentLoaded', fn);
 }

 ready(function () {
 // Find all dropdown containers
 document.querySelectorAll('.dropdown').forEach(function(drop) {
 var toggle = drop.querySelector('.dropdown-toggle');
 var menu = drop.querySelector('.dropdown-menu');

 if (!toggle || !menu) return;

 // Ensure aria attributes exist
 toggle.setAttribute('aria-haspopup', 'true');
 if (!toggle.hasAttribute('aria-expanded')) toggle.setAttribute('aria-expanded', 'false');

 // Toggle handler
 toggle.addEventListener('click', function (ev) {
 ev.preventDefault();
 ev.stopPropagation(); // avoid document click handler immediately closing it

 var isOpen = menu.classList.contains('show');
 // close any other open dropdowns first
 document.querySelectorAll('.dropdown-menu.show').forEach(function (m) {
 if (m !== menu) {
 m.classList.remove('show');
 var t = m.parentElement && m.parentElement.querySelector('.dropdown-toggle');
 if (t) t.setAttribute('aria-expanded', 'false');
 }
 });

 if (isOpen) {
 menu.classList.remove('show');
 toggle.setAttribute('aria-expanded', 'false');
 } else {
 menu.classList.add('show');
 toggle.setAttribute('aria-expanded', 'true');
 }
 });

 // Prevent clicks inside menu from closing it immediately
 menu.addEventListener('click', function (ev) {
 ev.stopPropagation();
 });
 });

 // Close dropdowns when clicking outside
 document.addEventListener('click', function () {
 document.querySelectorAll('.dropdown-menu.show').forEach(function (m) {
 m.classList.remove('show');
 var t = m.parentElement && m.parentElement.querySelector('.dropdown-toggle');
 if (t) t.setAttribute('aria-expanded', 'false');
 });
 });

 // Close on Escape
 document.addEventListener('keydown', function (ev) {
 if (ev.key === 'Escape' || ev.key === 'Esc') {
 document.querySelectorAll('.dropdown-menu.show').forEach(function (m) {
 m.classList.remove('show');
 var t = m.parentElement && m.parentElement.querySelector('.dropdown-toggle');
 if (t) t.setAttribute('aria-expanded', 'false');
 });
 }
 });
 });
})();
</script>
