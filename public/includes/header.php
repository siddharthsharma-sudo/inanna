<?php
// public/includes/header.php
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

// Simple helper: get cart count (works if $_SESSION['cart'] is an array of items or IDs)
function getCartCount(): int {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) return 0;
    $count = 0;
    foreach ($_SESSION['cart'] as $k => $v) {
        if (is_array($v) && isset($v['qty'])) $count += (int)$v['qty'];
        else $count++;
    }
    return $count;
}

// Handle logout POST (safe, idempotent). This runs if header is included on a page that posts logout.
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

// Determine auth state - adapt to your project's user session structure if needed.
$isLoggedIn = isset($_SESSION['user']) && !empty($_SESSION['user']);
$userName = $isLoggedIn ? ( $_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Account' ) : null;
$isAdmin = $isLoggedIn && !empty($_SESSION['user']['is_admin']);

$cartCount = getCartCount();

// Optionally load categories from DB if you have $pdo / $conn available via config.php.
$categories = [];
if (isset($pdo) && $pdo instanceof PDO) {
    try {
        $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name LIMIT 50");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        // ignore DB errors here - categories will remain empty
    }
} elseif (isset($conn) && is_resource($conn)) {
    try {
        $res = mysqli_query($conn, "SELECT id, name, slug FROM categories ORDER BY name LIMIT 50");
        while ($r = mysqli_fetch_assoc($res)) $categories[] = $r;
    } catch (Throwable $e) {
        // ignore
    }
}

// Utility: active class by filename (optional)
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
            transition: left 0.35s ease;
            z-index: 99999;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar-close { font-size: 2rem; cursor: pointer; }

        .sidebar-links a { display:block; padding:15px 10px; font-size:20px; text-decoration:none; color:#000;}
        .sidebar-links a:hover { background:#f2f2f2; }

        .sidebar-bottom { margin-top:auto; padding:20px 10px; border-top:1px solid #ddd; font-size:22px; }

        /* NAVBAR STYLES */
        .main-navbar { height:120px; background:#fff !important; border-bottom:1px solid black; }
        .custom-navbar { max-width:1424px; }
        .cart-img { width:36px; height:36px; object-fit:contain; }

        @media (max-width: 768px) { .desktop-links { display:none !important; }
        .sidebar {
            background: #fff !important;    
        }
     }
        @media (min-width: 992px) { .mobile-hamburger { display:none !important; } }

        /* small active link tweak */
        .nav-link.active { font-weight:600; color:#000 !important; }
    </style>
</head>
<body>

<!-- ======================================
     FULL SCREEN SIDEBAR (Mobile)
======================================= -->
<div id="sidebar" class="sidebar" aria-hidden="true">
    <div class="sidebar-close mb-4" onclick="closeSidebar()" aria-label="Close menu">
        <i class="bi bi-arrow-left"></i>
    </div>

    <div class="sidebar-links">
        <a href="index.php">Home</a>
        <a href="woi.php">WOI</a>
        <a href="appointment.php">Appointment</a>
        <a href="products.php">Shop</a>
        <a href="collection.php">Our Collection</a>

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

<!-- ======================================
     MAIN NAVBAR
======================================= -->
<nav id="mainNav" class="navbar navbar-expand-lg main-navbar sticky-top">
    <div class="container custom-navbar d-flex align-items-center justify-content-between">

        <!-- HAMBURGER (Mobile Only) -->
        <button class="btn border-0 fs-1 mobile-hamburger" onclick="openSidebar()" aria-label="Open menu">
            <i class="bi bi-list"></i>
        </button>

        <!-- LEFT LINKS (Desktop Only) -->
        <ul class="navbar-nav gap-lg-4 desktop-links">
            <li class="nav-item"><a class="nav-link<?= isActive('index.php') ?>" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('woi.php') ?>" href="woi.php">WOI</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('appointment.php') ?>" href="appointment.php">Appointment</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('products.php') ?>" href="products.php">Shop</a></li>
            <li class="nav-item"><a class="nav-link<?= isActive('collection.php') ?>" href="collection.php">Our Collection</a></li>
        </ul>

        <!-- CENTER LOGO -->
        <a class="navbar-brand mx-auto" href="index.php">
            <img src="https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/w:1920/h:689/q:mauto/f:best/dpr:2/https://worldofinanna.org/wp-content/uploads/2024/05/cropped-Norris-logo-copy-01-1.png"
                height="55" alt="Logo">
        </a>

        <!-- RIGHT ICONS (Always Visible) -->
        <div class="d-flex align-items-center gap-4">

            <a href="cart.php" class="position-relative d-inline-block" aria-label="View cart">
                <img src="./assets/images/shopping-cart.webp" class="cart-img" alt="Cart">
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
        </div>

    </div>
</nav>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap JS bundle should remain (no harm). Add this fallback AFTER it. -->
<script>
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
