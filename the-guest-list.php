<?php
$page_title = 'THE GUEST LIST | INANNA';
$meta_description = 'A curated drop for weddings, ceremonies, and every room worth dressing up for. Made for moments that matter.';
include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
<!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,600;1,800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>

  <style>
    /* =========================
       Base Styles & Tokens
    ========================== */
    :root {
      --bg: #ffffff;
      --text: #212529;
      --accent-dark: #1a1a1a;
      --pure-white: #ffffff;
      --muted: #b0b0b0;
      --danger: #ff5353;
      --border-subtle: rgb(91 18 18 / 8%);
      --transition-fast: 0.25s ease;
      --transition-slow: 0.5s ease;
      --nav-height: 76px;
      --radius-soft: 10px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      line-height: 1.5;
      -webkit-font-smoothing: antialiased;
      overflow-x: hidden;
      /* padding-top: var(--nav-height); */
    }

    /* Custom Font Definitions */
        .font-serif-cormorant { font-family: 'Cormorant Garamond', serif; }
        .font-sans-montserrat { font-family: 'Montserrat', sans-serif; }

        /* Custom Colors for Tailwind */
        .bg-brand-black { background-color: var(--color-black); }
        .text-brand-black { color: var(--color-black); }
        .bg-brand-gray { background-color: var(--color-gray); }
        .bg-brand-burgundy { background-color: var(--color-burgundy); }
        .text-brand-burgundy { color: var(--color-burgundy); }
        
        /* Utility Classes for Premium Look */
        .container-xl { max-width: 1200px; }
        .constrain-1200,
        .manifesto-section,
        .categories,
        .products,
        .shop-look,
        .video-banner,
        .atelier,
        .spotlight,
        .style-points,
        .lookbook
         { max-width: 1200px; width: 100%; margin-left: auto; margin-right: auto; padding-left: 24px; padding-right: 24px; }

        /* Animations */
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
        }

        /* Static Hero Image Style */
        .static-hero-img {
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 100%;
        }

    img {
      display: block;
      width: 100%;
      height: auto;
    }

    a {
      color: inherit;
      text-decoration: none;
    }

    button {
      font-family: inherit;
    }

    .page-wrapper {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 24px 80px;
    }

    section {
      position: relative;
    }

    .section-label {
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 11px;
      color: var(--muted);
      margin-bottom: 10px;
    }

    .section-heading {
      font-family: "Playfair Display", serif;
      font-size: clamp(28px, 4vw, 40px);
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .subcopy {
      color: var(--muted);
      font-size: 14px;
    }

    /* =========================
       Reveal Animations
    ========================== */
    .reveal {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Tailwind helper classes from No Plus One hero */
    .font-serif-cormorant { font-family: 'Cormorant Garamond', serif; }
    .font-sans-montserrat { font-family: 'Montserrat', sans-serif; }
    .animate-fade-in { animation: fade-in 1s ease-out forwards; }
    @keyframes fade-in { from { opacity:0; transform: translateY(20px);} to { opacity:1; transform: translateY(0);} }
    .static-hero-img { background-size: cover; background-position: center; width: 100%; height: 100%; }
    .hero { position: relative; width: 100%; height: 100vh; overflow: hidden; }
    .hero .static-hero-img { position: absolute; inset: 0; }
    .hero .overlay-content { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding: 1rem; }

    

    

    /* =========================
       Curated Categories
    ========================== */
    .categories { margin-bottom: 100px; }

    .categories-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 28px;
    }

    .category-card { position: relative; overflow: hidden; border-radius: 0; background: var(--accent-dark); cursor: pointer; aspect-ratio: 1/1; height: auto; display: flex; align-items: flex-end; isolation: isolate; }

    .category-image {
      position: absolute;
      inset: 0;
      background-position: center;
      background-size: cover;
      transform: scale(1);
      transition: transform var(--transition-slow), filter var(--transition-slow), opacity var(--transition-slow);
      opacity: 0.9;
    }

    .category-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to top, rgba(5, 5, 5, 0.9), rgba(5, 5, 5, 0.1));
      transition: background var(--transition-slow);
    }

    .category-meta {
      position: relative;
      padding: 22px 22px 24px;
      z-index: 2;
    }

    .category-title {
      font-family: "Playfair Display", serif;
      font-size: 18px;
      margin-bottom: 6px;
      color: var(--danger);
    }

    .category-line {
      width: 0%;
      height: 1px;
      background: var(--pure-white);
      margin-top: 8px;
      transition: width 0.4s ease;
    }

    .category-sub {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      color: var(--muted);
    }

    .category-card:hover .category-image {
      transform: scale(1.05);
      opacity: 1;
    }

    .category-card:hover .category-overlay {
      background: linear-gradient(to top, rgba(5, 5, 5, 0.7), rgba(5, 5, 5, 0.05));
    }

    .category-card:hover .category-line {
      width: 100%;
    }

    /* =========================
       Featured Products (Shop Grid)
    ========================== */
    .products { margin-bottom: 100px; }

    .products-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 24px;
    }

    .product-card {
      cursor: pointer;
      font-size: 13px;
    }

    .product-media { position: relative; border-radius: 0; overflow: hidden; background: var(--accent-dark); aspect-ratio: 1 / 1; margin-bottom: 10px; }

    .product-image,
    .product-image-secondary {
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      transition: opacity var(--transition-fast), transform var(--transition-slow);
    }

    .product-image-secondary {
      opacity: 0;
      transform: scale(1.03);
    }

    .product-card:hover .product-image {
      opacity: 0;
      transform: scale(1.03);
    }

    .product-card:hover .product-image-secondary {
      opacity: 1;
      transform: scale(1.02);
    }

    .product-quick-add {
      position: absolute;
      right: 10px;
      bottom: 10px;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: 1px solid var(--pure-white);
      background: rgba(5, 5, 5, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      transform: translateY(12px);
      opacity: 0;
      transition: transform var(--transition-fast), opacity var(--transition-fast), background var(--transition-fast), color var(--transition-fast);
    }

    .product-card:hover .product-quick-add {
      opacity: 1;
      transform: translateY(0);
    }

    .product-quick-add:hover {
      background: var(--pure-white);
      color: #000;
    }

    .product-name {
      font-family: "Playfair Display", serif;
      font-size: 14px;
      margin-bottom: 2px;
    }

    .product-meta {
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      color: var(--muted);
      margin-bottom: 4px;
    }

    .product-price {
      font-size: 13px;
    }

    /* =========================
       Shop The Look (Hotspots)
    ========================== */
    .shop-look { margin-bottom: 50px; }

    .shop-look-wrapper {
      position: relative;
      border-radius: 0;
      overflow: hidden;
      background: var(--accent-dark);
      height: 60vh;
      min-height: 420px;
    }

    .shop-look-image {
      position: absolute;
      inset: 0;
      
      background-size: cover;
      background-position: center;
      transition: filter var(--transition-fast);
    }

    .shop-look-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, rgba(5,5,5,0.7), rgba(5,5,5,0.2));
      pointer-events: none;
    }

    .shop-look-wrapper.hotspot-active .shop-look-image {
      filter: brightness(0.6);
    }

    .hotspot {
      position: absolute;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      border: 1px solid var(--pure-white);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .hotspot-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--pure-white);
      animation: pulse 1.6s infinite;
    }

    @keyframes pulse {
      0% { transform: scale(1); opacity: 1; }
      70% { transform: scale(1.7); opacity: 0; }
      100% { transform: scale(1.7); opacity: 0; }
    }

    .hotspot-card {
      position: absolute;
      min-width: 180px;
      background: #0e0e0e;
      border-radius: 12px;
      padding: 8px 10px 10px;
      border: 1px solid var(--border-subtle);
      font-size: 11px;
      display: none;
      max-width: 42vw;
    }

    .hotspot-card.visible {
      display: block;
    }

    .hotspot-card-header {
      display: flex;
      gap: 8px;
      margin-bottom: 6px;
    }

    .hotspot-card-thumb {
      width: 64px;
      height:64px;
      overflow: hidden;
      background-size: cover;
      background-position: center;
    }

    .hotspot-card-title {
      font-family: "Playfair Display", serif;
      font-size: 12px;
      color: var(--muted);
    }

    .hotspot-card-price {
      color: var(--muted);
      margin-top: 2px;
    }

    .hotspot-card button {
      margin-top: 4px;
      width: 100%;
      padding: 4px 8px;
      border-radius: 999px;
      border: 1px solid var(--pure-white);
      background: var(--pure-white);
      color: #000;
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease;
    }

    .hotspot-card button:hover {
      background: transparent;
      color: var(--pure-white);
    }

    /* =========================
       Video Banner
    ========================== */
    .video-banner { margin-bottom: 100px; border-radius: 0; overflow: hidden; position: relative; background: radial-gradient(circle at top, #222 0, #050505 60%); min-height: 360px; display: flex; align-items: center; justify-content: center; }

    .video-banner-content {
      text-align: center;
      z-index: 2;
    }

    .video-banner-title {
      font-family: "Playfair Display", serif;
      font-size: clamp(24px, 4vw, 32px);
      margin-bottom: 18px;
    }

    .video-play-btn {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      border: 1px solid var(--pure-white);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      background: transparent;
      transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
    }

    .video-play-btn:hover {
      background: var(--pure-white);
      color: #000;
      transform: translateY(-1px);
    }

    .video-play-btn::before {
      content: "";
      width: 0;
      height: 0;
      border-left: 12px solid currentColor;
      border-top: 8px solid transparent;
      border-bottom: 8px solid transparent;
      margin-left: 2px;
    }

    /* =========================
       Atelier Sticky Section
    ========================== */
    .atelier { margin-bottom: 100px; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr); gap: 42px; align-items: flex-start; }

    .atelier-left {
      position: sticky;
      top: calc(var(--nav-height) + 40px);
      align-self: flex-start;
      line-height:2.2;
    }

    .atelier-title {
      font-family: "Playfair Display", serif;
      font-size: 30px;
      margin-bottom: 12px;
    }

    .atelier-sub {
      font-size: 14px;
      color: var(--muted);
      margin-bottom: 20px;
    }

    .atelier-process {
      margin-top: 12px;
      border-top: 1px solid var(--border-subtle);
      padding-top: 16px;
    }

    .atelier-step {
      display: flex;
      gap: 10px;
      font-size: 13px;
      margin-bottom: 8px;
    }

    .atelier-step-label {
      font-size: 11px;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--muted);
      min-width: 80px;
    }

    .atelier-right {
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .atelier-image { border-radius: 0; overflow: hidden; background: var(--accent-dark); background-position: center; background-size: cover; aspect-ratio: 1/1; }

    /* =========================
       Product Spotlight
    ========================== */
    .spotlight { margin-bottom: 100px; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr); gap:6rem; align-items: center; }

    .spotlight-image { border-radius: 0; overflow: hidden; background-position: center; background-size: cover; aspect-ratio: 1/1; background-image: url("https://picsum.photos/800/1000?random=90&grayscale"); }

    .spotlight-meta {
      max-width: 420px;
      line-height:2.2;
    }

    .spotlight-name {
      font-family: "Playfair Display", serif;
      font-size: 26px;
      margin-bottom: 6px;
    }

    .spotlight-price {
      font-size: 14px;
      margin-bottom: 10px;
    }

    

    .spotlight-desc {
      font-size: 13px;
      /* color: var(--muted); */
      margin-bottom: 14px;
    }

    .spotlight-features {
      list-style: none;
      margin-bottom: 18px;
      font-size: 13px;
      color: var(--muted);
    }

    .spotlight-features li::before {
      content: "✓";
      margin-right: 6px;
      color: var(--muted);
    }

    .spotlight-size-label {
      font-size: 11px;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 6px;
    }

    .spotlight-sizes {
      display: flex;
      gap: 8px;
      margin-bottom: 18px;
    }

    .size-pill {
      width: 34px;
      height: 30px;
      border-radius: 6px;
      border: 1px solid var(--border-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    .size-pill.selected {
      background: var(--pure-white);
      color: #000;
      border-color: var(--pure-white);
    }

    .spotlight-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 280px;
    }

    .btn-primary {
      padding: 10px 16px;
      border-radius: 999px;
      border: none;
      background: var(--pure-white);
      color: #000;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 11px;
      cursor: pointer;
      transition: background 0.2s ease, transform 0.2s ease;
    }

    .btn-primary:hover {
      background: #eaeaea;
      transform: translateY(-1px);
    }

    .btn-outline {
      padding: 10px 16px;
      border-radius: 999px;
      border: 1px solid var(--border-subtle);
      background: transparent;
      color: var(--text);
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-size: 11px;
      cursor: pointer;
      transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .btn-outline:hover {
      background: var(--pure-white);
      color: #000;
      transform: translateY(-1px);
    }

    /* =========================
       Marquee
    ========================== */
    .marquee { position: relative; margin: 0 -24px 100px; background: #A47B67; color: #000; overflow: hidden; white-space: nowrap; }
    .marquee::before, .marquee::after { content: ""; position: absolute; left: 0; right: 0; height: 1px; background: rgba(0,0,0,0.12); pointer-events: none; }
    .marquee::before { top: 0; }
    .marquee::after { bottom: 0; }

    .marquee-inner {
      display: inline-flex;
      width: max-content;
      animation: marquee 18s linear infinite;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.24em;
    }

    .marquee-inner > span {
      padding: 12px 24px;
    }

    @keyframes marquee {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); }
    }

    /* =========================
       Style Points
    ========================== */
    .style-points { margin-bottom: 100px; }

    .style-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 24px;
    }

    .style-card { border-radius: 0; overflow: hidden; background: var(--accent-dark); display: flex; flex-direction: column; }

    .style-image { filter: grayscale(1); transition: filter var(--transition-fast), transform var(--transition-slow); transform: scale(1.02); }
    .style-image img { width: 100%; height: 100%; object-fit: cover; aspect-ratio: 1/1; }

    .style-card:hover .style-image {
      filter: grayscale(0);
      transform: scale(1.06);
    }

    .style-body {
      padding: 14px 14px 16px;
    }

    .style-title {
      font-family: "Playfair Display", serif;
      font-size: 16px;
      margin-bottom: 4px;
      color: var(--muted);
    }

    .style-copy {
      font-size: 12px;
      color: var(--muted);
    }

    /* =========================
       Lookbook - Masonry
    ========================== */
    .lookbook { margin-bottom: 100px; }

    .lookbook-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      grid-auto-rows: 140px;
      gap: 14px;
    }

    .look-tile { position: relative; border-radius: 0; overflow: hidden; background-position: center; background-size: cover; filter: grayscale(1); transition: filter var(--transition-fast), transform var(--transition-slow); cursor: pointer; }

    .look-tile.tall {
      grid-row: span 2;
    }

    .look-tile.wide {
      grid-column: span 2;
    }

    .look-tile.large {
      grid-column: span 2;
      grid-row: span 2;
    }

    .look-label {
      position: absolute;
      left: 12px;
      bottom: 12px;
      padding: 4px 10px;
      border-radius: 999px;
      border: 1px solid rgba(255, 255, 255, 0.7);
      background: rgba(5, 5, 5, 0.6);
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.16em;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    .look-tile:hover {
      filter: grayscale(0);
      transform: scale(1.03);
    }

    .look-tile:hover .look-label {
      opacity: 1;
      transform: translateY(0);
    }

    
    /* =========================
       Responsive
    ========================== */
    @media (max-width: 1024px) {
      .page-wrapper {
        padding: 0 20px 64px;
      }

      .atelier,
      .spotlight {
        grid-template-columns: minmax(0, 1fr);
      }

      .spotlight-image {
        min-height: 300px;
      }

      .shop-look-wrapper {
        height: 54vh;
        min-height: 360px;
      }

      .products-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .lookbook-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }

      .footer-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }
    }

    @media (max-width: 768px) {
      .nav {
        padding-inline: 16px;
      }

      .nav-links-desktop {
        display: none;
      }

      .nav-hamburger {
        display: block;
      }

      .nav-left {
        gap: 14px;
      }

      .page-wrapper {
        padding-inline: 16px;
      }

      .hero {
        align-items: flex-end;
        padding-bottom: 40px;
      }

      .categories-grid,
      .products-grid,
      .style-grid {
        grid-template-columns: minmax(0, 1fr);
      }

      .products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .lookbook-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .atelier-left {
        position: static;
      }

      .marquee {
        margin-inline: -16px;
      }

      .shop-look-wrapper {
        height: 50vh;
        min-height: 300px;
      }

      .hotspot {
        width: 18px;
        height: 18px;
      }

      .hotspot-card {
        min-width: 160px;
        left: auto;
        right: 4%;
        max-width: calc(100% - 24px);
      }

      .hotspot-card-thumb {
        width: 60px;
        height: 60px;
      }
    }

    @media (max-width: 500px) {
      .hero-title {
        letter-spacing: 0.12em;
      }

      .nav-logo {
        letter-spacing: 0.22em;
        font-size: 18px;
      }

      .products-grid {
        grid-template-columns: minmax(0, 1fr);
      }

      .lookbook-grid {
        grid-template-columns: minmax(0, 1fr);
      }

      .footer-grid {
        grid-template-columns: minmax(0, 1fr);
        text-align: left;
      }

      .hotspot-card {
        min-width: 150px;
      }
    }
  </style>
</head>
<body>
  

  <!-- <main class="page-wrapper"> -->
    <!-- =========================
         Hero
    ========================== -->
    <section id="hero" class="hero bg-white">
      <div class="static-hero-img" 
           style="background-image: url('assets/images/theguestlist/hero-banner.webp');">
      </div>

      <div class="overlay-content z-10 text-center">
        <div class="text-center text-white max-w-4xl mx-auto">
          <p class="animate-fade-in text-sm md:text-base tracking-[0.5em] mb-6 uppercase" style="animation-delay: 0.5s;">
            The New Collection
          </p>
          <h1 class="animate-fade-in text-6xl md:text-8xl lg:text-[6rem] font-serif-cormorant font-bold tracking-tight leading-[0.9] mb-8" style="animation-delay: 0.7s;">
            THE GUEST LIST
          </h1>
           <p class="animate-fade-in hero-subtitle text-lg md:text-2xl font-light tracking-widest text-white mb-12 max-w-2xl mx-auto" style="animation-delay: 0.9s;">
            Where presence dresses the room — tailored for moments that matter.
          </p>
          <a 
            href="#shop"
            class="animate-fade-in group relative inline-flex items-center gap-3 px-10 py-4 overflow-hidden border border-white/30 hover:border-white transition-colors duration-500"
            style="animation-delay: 1.1s;"
          >
            <span class="absolute inset-0 bg-white transform -translate-x-full group-hover:translate-x-0 transition-transform duration-500 ease-in-out"></span>
            <span class="relative z-10 text-sm tracking-[0.2em] uppercase group-hover:text-black transition-colors duration-300 font-sans-montserrat">
              Explore the Drop
            </span>
            <svg class="relative z-10 group-hover:text-black transition-colors duration-300 group-hover:translate-x-1 w-4 h-4" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
          </a>
        </div>
      </div>
    </section>

    

    <!--========================
        NARRATIVE NEW 
    =========================== -->

    <section class="manifesto-section">
  <div class="manifesto-container">

    <!-- TEXT SIDE -->
    <div class="manifesto-text">
      <div class="line"></div>
      <p class="label">THE PHILOSOPHY</p>

      <h2 class="manifesto-heading">
        Not every outfit needs <span class="italic">context</span>.
      </h2>

      <p class="manifesto-para">
        Some just change the energy the second you walk in. Think sheer, structure, sharp tailoring — bold in detail, calm in attitude.
      </p>
      <p class="manifesto-para">
        This is the kind of look that doesn’t ask for approval, it just fits. We stripped away the noise to focus on silhouette and presence.
      </p>
    </div>

    <!-- IMAGE SIDE -->
    <div class="manifesto-images">

      <!-- LEFT IMAGE -->
      <div class="img-block img-left">
        <img src="assets/images/theguestlist/img-1.webp" alt="">
        <p class="img-label">STRUCTURE</p>
      </div>

      <!-- RIGHT IMAGE -->
      <div class="img-block img-right">
        <img src="assets/images/theguestlist/img-5.webp" alt="">
        <p class="img-label">FLUIDITY</p>
      </div>

    </div>
  </div>
</section>

<style>
/* ------------------------------
   Base
------------------------------ */
.manifesto-section {
  background: #fff;
  padding: 5rem 1.5rem;
}

.manifesto-container {
  max-width: 1400px;
  margin: auto;
  display: flex;
  align-items: center;
  gap: 5rem;
}

.manifesto-text {
  flex: 1;
}

.line {
  width: 50px;
  height: 1px;
  background: #000;
  margin-bottom: 10px;
}

.label {
  text-transform: uppercase;
  letter-spacing: 0.22em;
  font-size: 12px;
  color: #777;
  margin-bottom: 20px;
}

.manifesto-heading {
  font-family: "Playfair Display", serif;
  font-size: 60px;
  line-height: 1;
  margin-bottom: 25px;
}

.manifesto-heading .italic {
  font-style: italic;
  color: #a24f4f;
}

.manifesto-para {
  max-width: 420px;
  font-size: 16px;
  color: #555;
  margin-bottom: 20px;
  line-height: 1.7;
}

/* ------------------------------
   IMAGE GRID WITH STAGGER
------------------------------ */
.manifesto-images {
  flex: 1;
  display: flex;
  gap: 2rem;
  align-items: flex-start;
}

.img-block {
  width: 100%;
}

.img-block img {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
  display: block;
  filter: grayscale(1);
  transition: 0.5s ease;
}

.img-block img:hover {
  filter: grayscale(0);
}

.img-label {
  text-transform: uppercase;
  letter-spacing: 0.25em;
  font-size: 12px;
  margin-top: 10px;
  border-top: 1px solid #000;
  padding-top: 6px;
}

/* ------------------------------
   STAGGER (APPLIES TO ALL DEVICES)
------------------------------ */
.img-left {
  margin-top: 40px;
}

.img-right {
  margin-top: 0px;
}

/* ------------------------------
   Responsive
------------------------------ */

/* Tablets */
@media (max-width: 992px) {
  .manifesto-container {
    flex-direction: column;
    text-align: left;
    gap: 3rem;
  }

  .manifesto-images {
    width: 100%;
    justify-content: center;
  }

  .img-left {
    margin-top: 30px;
  }

  .img-right {
    margin-top: 0px;
  }
}

/* Mobiles */
@media (max-width: 600px) {
  .manifesto-images {
    gap: 1.2rem;
  }

  .manifesto-heading {
    font-size: 40px;
  }

  .img-left {
    margin-top: 25px;
  }
}
</style>

    
   


    <!-- =========================
         Curated Categories
    ========================== -->
    <section class="categories">
      <div class="section-label reveal">Curated Categories</div>
      <h2 class="section-heading reveal">Anchor the night your way</h2>

      <div class="categories-grid">
        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('assets/images/theguestlist/img-1.webp');"></div>
          <div class="category-overlay"></div>
          <div class="category-meta">
            <div class="category-sub">For after-hours architecture</div>
            <h3 class="category-title">The Evening</h3>
            <div class="category-line"></div>
          </div>
        </article>

        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('assets/images/theguestlist/img-2.webp');"></div>
          <div class="category-overlay"></div>
          <div class="category-meta">
            <div class="category-sub">Lines that refuse to blur</div>
            <h3 class="category-title">The Structure</h3>
            <div class="category-line"></div>
          </div>
        </article>

        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('assets/images/theguestlist/img-3.webp');"></div>
          <div class="category-overlay"></div>
          <div class="category-meta">
            <div class="category-sub">Opacity, on your terms</div>
            <h3 class="category-title">The Veil</h3>
            <div class="category-line"></div>
          </div>
        </article>
      </div>
    </section>

    <!-- =========================
         Featured Products
    ========================== -->
    <section class="products" id="shop">
      <div class="section-label reveal">Featured Products</div>
      <h2 class="section-heading reveal">Edit your entrance</h2>

      <div class="products-grid">
        <!-- Product 1 -->
        <article class="product-card reveal">
          <div class="product-media">
            <div class="product-image"
                 style="background-image:url('assets/images/theguestlist/img-2.webp');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('assets/images/theguestlist/img-3.webp');"></div>
            <button class="product-quick-add" aria-label="Quick add">+</button>
          </div>
          <div class="product-info">
            <div class="product-name">Midnight Column Dress</div>
            <div class="product-meta">Evening · Dress</div>
            <div class="product-price">₹18,900</div>
          </div>
        </article>

        <!-- Product 2 -->
        <article class="product-card reveal">
          <div class="product-media">
            <div class="product-image"
                 style="background-image:url('assets/images/theguestlist/img-4.webp');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('assets/images/theguestlist/img-6.webp');"></div>
            <button class="product-quick-add" aria-label="Quick add">+</button>
          </div>
          <div class="product-info">
            <div class="product-name">Onyx Tailored Blazer</div>
            <div class="product-meta">Structure · Jacket</div>
            <div class="product-price">₹22,500</div>
          </div>
        </article>

        <!-- Product 3 -->
        <article class="product-card reveal">
          <div class="product-media">
            <div class="product-image"
                 style="background-image:url('assets/images/theguestlist/img-6.webp');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('assets/images/theguestlist/img-1.webp');"></div>
            <button class="product-quick-add" aria-label="Quick add">+</button>
          </div>
          <div class="product-info">
            <div class="product-name">Veiled Spine Gown</div>
            <div class="product-meta">Veil · Gown</div>
            <div class="product-price">₹27,800</div>
          </div>
        </article>

        <!-- Product 4 -->
        <article class="product-card reveal">
          <div class="product-media">
            <div class="product-image"
                 style="background-image:url('assets/images/theguestlist/img-3.webp');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('assets/images/theguestlist/img-4.webp');"></div>
            <button class="product-quick-add" aria-label="Quick add">+</button>
          </div>
          <div class="product-info">
            <div class="product-name">Nocturne Satin Trousers</div>
            <div class="product-meta">Evening · Tailored</div>
            <div class="product-price">₹15,400</div>
          </div>
        </article>
      </div>
    </section>

    

    <!-- =========================
         Video Banner
    ========================== -->
    <section class="video-banner">
      <div class="video-banner-content reveal">
        <div class="section-label">Motion Edit</div>
        <h2 class="video-banner-title">Movement in every stitch</h2>
        <button class="video-play-btn" aria-label="Play lookbook film"></button>
      </div>
    </section>

    <!-- =========================
         Atelier Sticky
    ========================== -->
    <section class="atelier" id="atelier">
      <div class="atelier-left reveal">
        <div class="section-label">The Guest List</div>
        <h2 class="atelier-title">Inside The Guest List</h2>
        <p class="atelier-sub">
          Every piece in <em>THE GUEST LIST</em> is cut, pressed and finished in small, deliberate runs.
        </p>
        <div class="atelier-process">
          <div class="atelier-step">
            <div class="atelier-step-label">01 · Sourcing</div>
            <div>Weighted satins, smoked organza, and structured wool — chosen for how they hold silence.</div>
          </div>
          <div class="atelier-step">
            <div class="atelier-step-label">02 · Draping</div>
            <div>Shapes are built on the body, not the sketch. The line always follows the wearer, not the room.</div>
          </div>
          <div class="atelier-step">
            <div class="atelier-step-label">03 · Finish</div>
            <div>Hand-bound seams, hidden closures, and hems that never ask for a heel height.</div>
          </div>
        </div>
      </div>
      <div class="atelier-right">
        <div class="atelier-image reveal"
             style="background-image:url('assets/images/theguestlist/img-1.webp');"></div>
        <div class="atelier-image reveal"
             style="background-image:url('assets/images/theguestlist/img-4.webp');"></div>
        <div class="atelier-image reveal"
             style="background-image:url('assets/images/theguestlist/img-6.webp');"></div>
      </div>
    </section>

    <!-- =========================
         Product Spotlight
    ========================== -->
    <section class="spotlight">
      <div class="spotlight-image reveal" style="background-image:url('assets/images/theguestlist/img-2.webp');"></div>
      <div class="spotlight-meta reveal">
        <div class="section-label">Product spotlight</div>
        <h2 class="spotlight-name">Golden Hour Silk-Net Saree</h2>
        <div class="spotlight-price">₹42,900</div>
        <p class="spotlight-desc">
          A pre-draped silk-net saree drenched in golden hand and machine embroidery, paired with a halter-style blouse. Luxe, effortless, and dramatic — crafted for the spotlight.
        </p>
        <ul class="spotlight-features">
          <li>Golden floral embroidery throughout the saree</li>
           <li>Lightweight yet structured with lining & finishing</li>
          <li>Intricate scalloped embroidered border</li>
          <li>Pre-draped style for easy wear</li>
        </ul>

        <div class="spotlight-size-label">Select size</div>
        <div class="spotlight-sizes" id="sizeSelector">
          <div class="size-pill">XS</div>
          <div class="size-pill selected">S</div>
          <div class="size-pill">M</div>
          <div class="size-pill">L</div>
        </div>

        <div class="spotlight-actions">
        <a class="btn-primary  text-center"href="products.php">SHOP NOW</a>
        </div>
      </div>
    </section>

    <!-- =========================
         Marquee
    ========================== -->
    <section class="marquee">
      <div class="marquee-inner">
        <span>THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST —</span>
        <span aria-hidden="true">THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST — THE GUEST LIST —</span>
      </div>
    </section>

    <!-- =========================
         Style Points
    ========================== -->
    <section class="style-points">
      <div class="section-label reveal">Style points</div>
      <h2 class="section-heading reveal">You didn’t bring a plus one because you don’t need one.</h2>

      <div class="style-grid">
        <article class="style-card reveal">
          <div class="style-image">
            <img src="assets/images/theguestlist/img-1.webp" alt="Silhouette">
          </div>
          <div class="style-body">
            <div class="style-title">The IT Girl</div>
            <p class="style-copy">
              Long lines, clean breaks, and negative space that does most of the talking.
            </p>
          </div>
        </article>

        <article class="style-card reveal">
          <div class="style-image">
            <img src="assets/images/theguestlist/img-2.webp" alt="Palette">
          </div>
          <div class="style-body">
            <div class="style-title">The Palette</div>
            <p class="style-copy">
              Blacks, charcoals, and barely-there off-whites — edited like a late-night playlist.
            </p>
          </div>
        </article>

        <article class="style-card reveal">
          <div class="style-image">
            <img src="assets/images/theguestlist/img-3.webp" alt="The Night">
          </div>
          <div class="style-body">
            <div class="style-title">The Night</div>
            <p class="style-copy">
              Built for arrivals without exits. Fabrics that look better against streetlight.
            </p>
          </div>
        </article>
      </div>
    </section>

    <!-- =========================
         Lookbook
    ========================== -->
    <section class="shop-look" id="shopLookSection2">
      <div class="section-label reveal">Shop the look</div>
      <h2 class="section-heading reveal">Tap into the frame</h2>

      <div class="shop-look-wrapper reveal" id="shopLook2">
        <div class="shop-look-image" style="background-image:url('assets/images/theguestlist/img-5.webp');"></div>
        <div class="shop-look-overlay"></div>

        <div class="hotspot" data-hotspot="belt2" style="top: 32%; left: 28%;"><div class="hotspot-dot"></div></div>
        <div class="hotspot-card" id="hotspot-belt2" style="top: 36%; left: 32%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb" style="background-image:url('assets/images/theguestlist/img-4.webp');"></div>
            <div>
              <div class="hotspot-card-title">Structured Belt</div>
              <div class="hotspot-card-price">₹9,500</div>
            </div>
          </div>
          <button><a href="products.php">SHOP NOW</a></button>
        </div>

        <div class="hotspot" data-hotspot="bag2" style="top: 66%; left: 22%;"><div class="hotspot-dot"></div></div>
        <div class="hotspot-card" id="hotspot-bag2" style="top: 70%; left: 26%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb" style="background-image:url('assets/images/theguestlist/img-6.webp');"></div>
            <div>
              <div class="hotspot-card-title">Evening Bag</div>
              <div class="hotspot-card-price">₹12,800</div>
            </div>
          </div>
          <button><a href="products.php">SHOP NOW</a></button>
        </div>

        <div class="hotspot" data-hotspot="heels2" style="top: 74%; left: 56%;"><div class="hotspot-dot"></div></div>
        <div class="hotspot-card" id="hotspot-heels2" style="top: 68%; left: 60%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb" style="background-image:url('assets/images/theguestlist/img-3.webp');"></div>
            <div>
              <div class="hotspot-card-title">Noir Stiletto</div>
              <div class="hotspot-card-price">₹16,800</div>
            </div>
          </div>
          <button><a href="products.php">SHOP NOW</a></button>
        </div>

        <div class="hotspot" data-hotspot="necklace2" style="top: 18%; left: 62%;"><div class="hotspot-dot"></div></div>
        <div class="hotspot-card" id="hotspot-necklace2" style="top: 22%; left: 66%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb" style="background-image:url('assets/images/theguestlist/img-2.webp');"></div>
            <div>
              <div class="hotspot-card-title">Statement Necklace</div>
              <div class="hotspot-card-price">₹7,200</div>
            </div>
          </div>
          <button><a href="products.php">SHOP NOW</a></button>
        </div>

        <div class="hotspot" data-hotspot="cuff2" style="top: 48%; left: 74%;"><div class="hotspot-dot"></div></div>
        <div class="hotspot-card" id="hotspot-cuff2" style="top: 52%; left: 78%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb" style="background-image:url('assets/images/theguestlist/img-5.webp');"></div>
            <div>
              <div class="hotspot-card-title">Embroidered Cuff</div>
              <div class="hotspot-card-price">₹5,900</div>
            </div>
          </div>
          <button><a href="products.php">SHOP NOW</a></button>
        </div>
      </div>
    </section>

   <?php include __DIR__ . '/includes/footer.php';
?>
    
    
  <!-- </main> -->

  <script>
    // // =========================
    // // Sticky Nav on Scroll
    // // =========================
    // const nav = document.getElementById('mainNav');
    // window.addEventListener('scroll', () => {
    //   if (window.scrollY > 10) {
    //     nav.classList.add('scrolled');
    //   } else {
    //     nav.classList.remove('scrolled');
    //   }
    // });

    // // =========================
    // // Mobile Nav Toggle (guarded if local nav exists)
    // // =========================
    // const hamburger = document.getElementById('hamburger');
    // const mobileNav = document.getElementById('mobileNav');
    // if (hamburger && mobileNav) {
    //   hamburger.addEventListener('click', () => {
    //     hamburger.classList.toggle('active');
    //     mobileNav.classList.toggle('open');
    //   });
    //   mobileNav.querySelectorAll('a').forEach(link => {
    //     link.addEventListener('click', () => {
    //       hamburger.classList.remove('active');
    //       mobileNav.classList.remove('open');
    //     });
    //   });
    // }

    // =========================
    // IntersectionObserver Reveal
    // =========================
    const revealEls = document.querySelectorAll('.reveal');

    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.18 }
    );

    revealEls.forEach(el => observer.observe(el));

    // =========================
    // Hotspots Logic (bottom section)
    // =========================
    const shopLook2 = document.getElementById('shopLook2');
    if (shopLook2) {
      const hotspots2 = shopLook2.querySelectorAll('.hotspot');
      const hotspotCards2 = shopLook2.querySelectorAll('.hotspot-card');
      function closeAllHotspots2() {
        hotspotCards2.forEach(card => card.classList.remove('visible'));
        shopLook2.classList.remove('hotspot-active');
      }
      hotspots2.forEach(h => {
        function openCard() {
          const id = h.dataset.hotspot;
          closeAllHotspots2();
          const card = document.getElementById('hotspot-' + id);
          if (card) {
            card.classList.add('visible');
            shopLook2.classList.add('hotspot-active');
          }
        }
        h.addEventListener('mouseenter', openCard);
        h.addEventListener('click', function(e){ e.stopPropagation(); openCard(); });
      });
      document.addEventListener('click', closeAllHotspots2);
      shopLook2.addEventListener('mouseleave', closeAllHotspots2);
    }

    // =========================
    // Size Selector
    // =========================
    const sizeContainer = document.getElementById('sizeSelector');
    sizeContainer.addEventListener('click', e => {
      const pill = e.target.closest('.size-pill');
      if (!pill) return;
      sizeContainer.querySelectorAll('.size-pill').forEach(p => p.classList.remove('selected'));
      pill.classList.add('selected');
    });
  </script>
</body>
</html>