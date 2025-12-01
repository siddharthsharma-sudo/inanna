<?php // index.php - INANNA "NO PLUS ONE" Landing Page ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>INANNA — NO PLUS ONE</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <!-- Typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,600;1,800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>

  <style>
    /* =========================
       Base Styles & Tokens
    ========================== */
    :root {
      --bg: #050505;
      --text: #f4f4f0;
      --accent-dark: #1a1a1a;
      --pure-white: #ffffff;
      --muted: #b0b0b0;
      --danger: #ff5353;
      --border-subtle: rgba(255, 255, 255, 0.08);
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

    /* =========================
       Navigation
    ========================== */
    .nav {
      position: sticky;
      top: 0;
      left: 0;
      z-index: 999;
      height: var(--nav-height);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      transition: background 0.3s ease, backdrop-filter 0.3s ease, border-color 0.3s ease;
      background: transparent;
      backdrop-filter: none;
      border-bottom: 1px solid transparent;
    }

    .nav.scrolled {
      background: rgba(5, 5, 5, 0.9);
      backdrop-filter: blur(18px);
      border-bottom: 1px solid var(--border-subtle);
    }

    .nav-left,
    .nav-right {
      display: flex;
      align-items: center;
      gap: 24px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
    }

    .nav-link {
      position: relative;
      color: var(--text);
      opacity: 0.8;
      transition: opacity var(--transition-fast);
    }

    .nav-link::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -6px;
      width: 0;
      height: 1px;
      background: var(--pure-white);
      transition: width 0.25s ease;
    }

    .nav-link:hover {
      opacity: 1;
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .nav-logo {
      font-family: "Playfair Display", serif;
      font-size: 22px;
      letter-spacing: 0.3em;
      text-transform: uppercase;
    }

    .nav-icons {
      display: flex;
      align-items: center;
      gap: 20px;
      font-size: 13px;
    }

    .icon-circle {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      border: 1px solid var(--border-subtle);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      cursor: pointer;
      position: relative;
      transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .icon-circle:hover {
      background: var(--pure-white);
      color: #000;
      border-color: var(--pure-white);
      transform: translateY(-1px);
    }

    .bag-icon-badge {
      position: absolute;
      top: -6px;
      right: -6px;
      width: 15px;
      height: 15px;
      border-radius: 999px;
      background: var(--pure-white);
      color: #000;
      font-size: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    .nav-hamburger {
      width: 22px;
      height: 18px;
      position: relative;
      cursor: pointer;
      display: none;
    }

    .nav-hamburger span {
      position: absolute;
      left: 0;
      width: 100%;
      height: 1.5px;
      background: var(--text);
      transition: transform 0.25s ease, top 0.25s ease, opacity 0.25s ease;
    }

    .nav-hamburger span:nth-child(1) { top: 0; }
    .nav-hamburger span:nth-child(2) { top: 8px; }
    .nav-hamburger span:nth-child(3) { top: 16px; }

    .nav-hamburger.active span:nth-child(1) {
      top: 8px;
      transform: rotate(45deg);
    }

    .nav-hamburger.active span:nth-child(2) {
      opacity: 0;
    }

    .nav-hamburger.active span:nth-child(3) {
      top: 8px;
      transform: rotate(-45deg);
    }

    .nav-links-desktop {
      display: flex;
      gap: 24px;
      align-items: center;
    }

    .nav-links-mobile {
      display: none;
      position: fixed;
      inset: var(--nav-height) 0 auto 0;
      background: rgba(5, 5, 5, 0.98);
      padding: 24px;
      border-bottom: 1px solid var(--border-subtle);
      z-index: 998;
    }

    .nav-links-mobile a {
      display: block;
      padding: 10px 0;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
    }

    .nav-links-mobile.open {
      display: block;
    }

    /* =========================
       Hero Section
    ========================== */
    .hero {
      min-height: calc(100vh - var(--nav-height));
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      margin-bottom: 80px;
    }

    .hero-media {
      position: absolute;
      inset: 0;
      background:
        linear-gradient(to top, rgba(5, 5, 5, 0.9), rgba(5, 5, 5, 0.4)),
        url("https://picsum.photos/1400/900?random=21&grayscale&blur=1");
      background-size: cover;
      background-position: center;
      filter: saturate(0.8);
    }

    .hero-content {
      position: relative;
      z-index: 2;
      text-align: center;
      padding: 40px 16px;
      max-width: 720px;
    }

    .hero-label {
      text-transform: uppercase;
      letter-spacing: 0.28em;
      font-size: 11px;
      color: var(--muted);
      margin-bottom: 12px;
    }

    .hero-title {
      font-family: "Playfair Display", serif;
      font-size: clamp(40px, 7vw, 70px);
      text-transform: uppercase;
      letter-spacing: 0.18em;
      font-weight: 700;
      margin-bottom: 14px;
    }

    .hero-sub {
      font-size: 14px;
      color: var(--muted);
      max-width: 440px;
      margin: 0 auto 28px;
    }

    .hero-cta {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 12px 32px;
      border-radius: 999px;
      border: 1px solid var(--pure-white);
      background: transparent;
      color: var(--pure-white);
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.2em;
      cursor: pointer;
      transition: background var(--transition-fast), color var(--transition-fast), transform 0.2s ease;
    }

    .hero-cta:hover {
      background: var(--pure-white);
      color: #000;
      transform: translateY(-1px);
    }

    /* =========================
       Narrative Section
    ========================== */
    .narrative {
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
      gap: 56px;
      margin-bottom: 120px;
      align-items: center;
    }

    .narrative-left {
      border-left: 1px solid var(--border-subtle);
      padding-left: 28px;
    }

    .narrative-heading {
      font-family: "Playfair Display", serif;
      font-size: 28px;
      margin-bottom: 16px;
    }

    .narrative-heading em {
      font-style: italic;
    }

    .narrative-body {
      font-size: 14px;
      color: var(--muted);
      max-width: 420px;
    }

    .narrative-body em {
      font-style: italic;
      color: var(--pure-white);
    }

    .narrative-right {
      position: relative;
      min-height: 320px;
    }

    .narrative-image {
      position: absolute;
      width: 60%;
      border-radius: 20px;
      overflow: hidden;
      background: var(--accent-dark);
    }

    .narrative-image.image-1 {
      top: 0;
      left: 0;
    }

    .narrative-image.image-2 {
      bottom: 0;
      right: 4%;
    }

    /* =========================
       Curated Categories
    ========================== */
    .categories {
      margin-bottom: 120px;
    }

    .categories-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 28px;
    }

    .category-card {
      position: relative;
      overflow: hidden;
      border-radius: 20px;
      background: var(--accent-dark);
      cursor: pointer;
      height: 420px;
      display: flex;
      align-items: flex-end;
      isolation: isolate;
    }

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
    .products {
      margin-bottom: 120px;
    }

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

    .product-media {
      position: relative;
      border-radius: 14px;
      overflow: hidden;
      background: var(--accent-dark);
      aspect-ratio: 3 / 4;
      margin-bottom: 10px;
    }

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
    .shop-look {
      margin-bottom: 120px;
    }

    .shop-look-wrapper {
      position: relative;
      border-radius: 20px;
      overflow: hidden;
      background: var(--accent-dark);
      min-height: 360px;
    }

    .shop-look-image {
      position: absolute;
      inset: 0;
      background-image: url("https://picsum.photos/1400/800?random=40&grayscale");
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
      background: rgba(5, 5, 5, 0.95);
      border-radius: 12px;
      padding: 10px 12px 12px;
      border: 1px solid var(--border-subtle);
      font-size: 11px;
      display: none;
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
      width: 40px;
      height: 50px;
      border-radius: 8px;
      overflow: hidden;
      background-size: cover;
      background-position: center;
    }

    .hotspot-card-title {
      font-family: "Playfair Display", serif;
      font-size: 12px;
    }

    .hotspot-card-price {
      color: var(--muted);
      margin-top: 2px;
    }

    .hotspot-card button {
      margin-top: 4px;
      width: 100%;
      padding: 6px 8px;
      border-radius: 999px;
      border: 1px solid var(--pure-white);
      background: var(--pure-white);
      color: #000;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.15em;
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
    .video-banner {
      margin-bottom: 120px;
      border-radius: 20px;
      overflow: hidden;
      position: relative;
      background: radial-gradient(circle at top, #222 0, #050505 60%);
      min-height: 360px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

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
    .atelier {
      margin-bottom: 120px;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
      gap: 42px;
      align-items: flex-start;
    }

    .atelier-left {
      position: sticky;
      top: calc(var(--nav-height) + 40px);
      align-self: flex-start;
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

    .atelier-image {
      border-radius: 16px;
      overflow: hidden;
      background: var(--accent-dark);
      min-height: 180px;
      background-position: center;
      background-size: cover;
    }

    /* =========================
       Product Spotlight
    ========================== */
    .spotlight {
      margin-bottom: 120px;
      display: grid;
      grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
      gap: 42px;
      align-items: center;
    }

    .spotlight-image {
      border-radius: 18px;
      overflow: hidden;
      background-position: center;
      background-size: cover;
      min-height: 360px;
      background-image: url("https://picsum.photos/800/1000?random=90&grayscale");
    }

    .spotlight-meta {
      max-width: 420px;
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

    .spotlight-rating {
      font-size: 12px;
      margin-bottom: 14px;
      color: var(--muted);
    }

    .spotlight-desc {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 14px;
    }

    .spotlight-features {
      list-style: none;
      margin-bottom: 18px;
      font-size: 13px;
    }

    .spotlight-features li::before {
      content: "✓";
      margin-right: 6px;
      color: var(--pure-white);
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
    .marquee {
      margin: 0 -24px 100px;
      background: var(--pure-white);
      color: #000;
      overflow: hidden;
      white-space: nowrap;
    }

    .marquee-inner {
      display: inline-flex;
      padding: 12px 24px;
      animation: marquee 18s linear infinite;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.24em;
    }

    @keyframes marquee {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); }
    }

    /* =========================
       Style Points
    ========================== */
    .style-points {
      margin-bottom: 100px;
    }

    .style-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 24px;
    }

    .style-card {
      border-radius: 16px;
      overflow: hidden;
      background: var(--accent-dark);
      display: flex;
      flex-direction: column;
    }

    .style-image {
      filter: grayscale(1);
      transition: filter var(--transition-fast), transform var(--transition-slow);
      transform: scale(1.02);
    }

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
    }

    .style-copy {
      font-size: 12px;
      color: var(--muted);
    }

    /* =========================
       Lookbook - Masonry
    ========================== */
    .lookbook {
      margin-bottom: 120px;
    }

    .lookbook-grid {
      margin-top: 32px;
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      grid-auto-rows: 140px;
      gap: 14px;
    }

    .look-tile {
      position: relative;
      border-radius: 16px;
      overflow: hidden;
      background-position: center;
      background-size: cover;
      filter: grayscale(1);
      transition: filter var(--transition-fast), transform var(--transition-slow);
      cursor: pointer;
    }

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
       Newsletter & Footer
    ========================== */
    .newsletter {
      margin-bottom: 80px;
      text-align: center;
      max-width: 480px;
      margin-inline: auto;
    }

    .newsletter-title {
      font-family: "Playfair Display", serif;
      font-size: 22px;
      margin-bottom: 10px;
    }

    .newsletter-copy {
      font-size: 13px;
      color: var(--muted);
      margin-bottom: 20px;
    }

    .newsletter-form {
      display: flex;
      align-items: center;
      gap: 10px;
      border-bottom: 1px solid var(--border-subtle);
      padding-bottom: 8px;
    }

    .newsletter-input {
      flex: 1;
      background: transparent;
      border: none;
      outline: none;
      color: var(--text);
      font-size: 13px;
      padding: 4px 0;
    }

    .newsletter-submit {
      border: none;
      background: transparent;
      color: var(--text);
      font-size: 16px;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .newsletter-submit:hover {
      transform: translateX(2px);
    }

    .footer {
      padding-top: 40px;
      border-top: 1px solid var(--border-subtle);
      text-align: center;
    }

    .footer-logo {
      font-family: "Playfair Display", serif;
      font-size: 22px;
      letter-spacing: 0.3em;
      text-transform: uppercase;
      margin-bottom: 28px;
    }

    .footer-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 18px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.18em;
      margin-bottom: 26px;
    }

    .footer-col-title {
      color: var(--muted);
      margin-bottom: 10px;
    }

    .footer-link {
      display: block;
      margin-bottom: 6px;
      opacity: 0.85;
    }

    .footer-link:hover {
      opacity: 1;
    }

    .footer-bottom {
      font-size: 11px;
      color: var(--muted);
      margin-top: 10px;
    }

    /* =========================
       Responsive
    ========================== */
    @media (max-width: 1024px) {
      .page-wrapper {
        padding: 0 20px 64px;
      }

      .narrative,
      .atelier,
      .spotlight {
        grid-template-columns: minmax(0, 1fr);
      }

      .spotlight-image {
        min-height: 300px;
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

      .narrative,
      .categories-grid,
      .products-grid,
      .style-grid {
        grid-template-columns: minmax(0, 1fr);
      }

      .narrative-left {
        border-left: none;
        border-top: 1px solid var(--border-subtle);
        padding-left: 0;
        padding-top: 16px;
      }

      .narrative-right {
        min-height: 260px;
        margin-top: 20px;
      }

      .narrative-image {
        width: 70%;
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
    }
  </style>
</head>
<body>
  <!-- =========================
       Navigation
  ========================== -->
  <header class="nav" id="mainNav">
    <div class="nav-left">
      <div class="nav-hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
      <nav class="nav-links-desktop">
        <a href="#shop" class="nav-link">Shop</a>
        <a href="#collections" class="nav-link">Collections</a>
        <a href="#atelier" class="nav-link">Atelier</a>
      </nav>
    </div>

    <div class="nav-logo">INANNA</div>

    <div class="nav-right">
      <div class="nav-icons">
        <div class="icon-circle" title="Search">⌀</div>
        <div class="icon-circle" title="Account">∕</div>
        <div class="icon-circle" title="Bag">
          ⌾
          <span class="bag-icon-badge">2</span>
        </div>
      </div>
    </div>
  </header>

  <nav class="nav-links-mobile" id="mobileNav">
    <a href="#shop">Shop</a>
    <a href="#collections">Collections</a>
    <a href="#atelier">Atelier</a>
  </nav>

  <main class="page-wrapper">
    <!-- =========================
         Hero
    ========================== -->
    <section class="hero">
      <div class="hero-media" aria-hidden="true"></div>
      <div class="hero-content reveal">
        <div class="hero-label">THE NEW DROP</div>
        <h1 class="hero-title">NO&nbsp;PLUS&nbsp;ONE</h1>
        <p class="hero-sub">A presence you don’t follow up with. Tailored for nights that never ask for permission.</p>
        <button class="hero-cta" onclick="document.getElementById('shop').scrollIntoView({behavior:'smooth'})">
          Explore the drop
        </button>
      </div>
    </section>

    <!-- =========================
         Narrative
    ========================== -->
    <section class="narrative" id="collections">
      <div class="narrative-left reveal">
        <div class="section-label">The Narrative</div>
        <h2 class="narrative-heading">
          Not every outfit needs <em>context</em>.
        </h2>
        <p class="narrative-body">
          <em>NO PLUS ONE</em> is a study in solitary presence. No entourage. No apology.
          Every seam is a line you don’t cross; every silhouette an answer that doesn’t wait
          for questions. These pieces don’t fill a void — they create one around you.
        </p>
      </div>
      <div class="narrative-right reveal">
        <div class="narrative-image image-1">
          <img src="https://picsum.photos/600/800?random=10&grayscale" alt="Editorial look 1">
        </div>
        <div class="narrative-image image-2">
          <img src="https://picsum.photos/600/800?random=11&grayscale" alt="Editorial look 2">
        </div>
      </div>
    </section>

    <!-- =========================
         Curated Categories
    ========================== -->
    <section class="categories">
      <div class="section-label reveal">Curated Categories</div>
      <h2 class="section-heading reveal">Anchor the night your way</h2>

      <div class="categories-grid">
        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('https://picsum.photos/600/900?random=20&grayscale');"></div>
          <div class="category-overlay"></div>
          <div class="category-meta">
            <div class="category-sub">For after-hours architecture</div>
            <h3 class="category-title">The Evening</h3>
            <div class="category-line"></div>
          </div>
        </article>

        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('https://picsum.photos/600/900?random=21&grayscale');"></div>
          <div class="category-overlay"></div>
          <div class="category-meta">
            <div class="category-sub">Lines that refuse to blur</div>
            <h3 class="category-title">The Structure</h3>
            <div class="category-line"></div>
          </div>
        </article>

        <article class="category-card reveal">
          <div class="category-image"
               style="background-image:url('https://picsum.photos/600/900?random=22&grayscale');"></div>
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
                 style="background-image:url('https://picsum.photos/600/800?random=30&grayscale');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('https://picsum.photos/600/800?random=31&grayscale');"></div>
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
                 style="background-image:url('https://picsum.photos/600/800?random=32&grayscale');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('https://picsum.photos/600/800?random=33&grayscale');"></div>
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
                 style="background-image:url('https://picsum.photos/600/800?random=34&grayscale');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('https://picsum.photos/600/800?random=35&grayscale');"></div>
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
                 style="background-image:url('https://picsum.photos/600/800?random=36&grayscale');"></div>
            <div class="product-image-secondary"
                 style="background-image:url('https://picsum.photos/600/800?random=37&grayscale');"></div>
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
         Shop The Look
    ========================== -->
    <section class="shop-look">
      <div class="section-label reveal">Shop the look</div>
      <h2 class="section-heading reveal">Tap into the frame</h2>

      <div class="shop-look-wrapper reveal" id="shopLook">
        <div class="shop-look-image"></div>
        <div class="shop-look-overlay"></div>

        <!-- Hotspot 1 -->
        <div class="hotspot" data-hotspot="choker" style="top: 38%; left: 46%;">
          <div class="hotspot-dot"></div>
        </div>
        <div class="hotspot-card" id="hotspot-choker" style="top: 45%; left: 50%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb"
                 style="background-image:url('https://picsum.photos/200/260?random=50&grayscale');"></div>
            <div>
              <div class="hotspot-card-title">Onyx Choker</div>
              <div class="hotspot-card-price">₹7,200</div>
            </div>
          </div>
          <button>Add to bag</button>
        </div>

        <!-- Hotspot 2 -->
        <div class="hotspot" data-hotspot="corset" style="top: 55%; left: 40%;">
          <div class="hotspot-dot"></div>
        </div>
        <div class="hotspot-card" id="hotspot-corset" style="top: 60%; left: 18%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb"
                 style="background-image:url('https://picsum.photos/200/260?random=51&grayscale');"></div>
            <div>
              <div class="hotspot-card-title">Midnight Corset</div>
              <div class="hotspot-card-price">₹19,300</div>
            </div>
          </div>
          <button>Add to bag</button>
        </div>

        <!-- Hotspot 3 -->
        <div class="hotspot" data-hotspot="heels" style="top: 75%; left: 55%;">
          <div class="hotspot-dot"></div>
        </div>
        <div class="hotspot-card" id="hotspot-heels" style="top: 68%; left: 60%;">
          <div class="hotspot-card-header">
            <div class="hotspot-card-thumb"
                 style="background-image:url('https://picsum.photos/200/260?random=52&grayscale');"></div>
            <div>
              <div class="hotspot-card-title">Noir Stiletto</div>
              <div class="hotspot-card-price">₹16,800</div>
            </div>
          </div>
          <button>Add to bag</button>
        </div>
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
        <div class="section-label">The Atelier</div>
        <h2 class="atelier-title">Inside the atelier</h2>
        <p class="atelier-sub">
          Every piece in <em>NO PLUS ONE</em> is cut, pressed and finished in small, deliberate runs.
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
             style="background-image:url('https://picsum.photos/800/600?random=60&grayscale');"></div>
        <div class="atelier-image reveal"
             style="background-image:url('https://picsum.photos/800/601?random=61&grayscale');"></div>
        <div class="atelier-image reveal"
             style="background-image:url('https://picsum.photos/800/602?random=62&grayscale');"></div>
      </div>
    </section>

    <!-- =========================
         Product Spotlight
    ========================== -->
    <section class="spotlight">
      <div class="spotlight-image reveal"></div>
      <div class="spotlight-meta reveal">
        <div class="section-label">Product spotlight</div>
        <h2 class="spotlight-name">The Midnight Corset</h2>
        <div class="spotlight-price">₹19,300</div>
        <div class="spotlight-rating">★★★★★ · 4.9 · 127 reviews</div>
        <p class="spotlight-desc">
          Bonded satin with internal boning and a hand-finished back closure. Built to hold its own
          above denim or under a blazer — no compromise, no filler.
        </p>
        <ul class="spotlight-features">
          <li>Structured yet breathable inner paneling</li>
          <li>Soft facing along all contact points</li>
          <li>Clean finish hem for seamless layering</li>
        </ul>

        <div class="spotlight-size-label">Select size</div>
        <div class="spotlight-sizes" id="sizeSelector">
          <div class="size-pill">XS</div>
          <div class="size-pill selected">S</div>
          <div class="size-pill">M</div>
          <div class="size-pill">L</div>
        </div>

        <div class="spotlight-actions">
          <button class="btn-primary">Add to bag</button>
          <button class="btn-outline">Wishlist</button>
        </div>
      </div>
    </section>

    <!-- =========================
         Marquee
    ========================== -->
    <section class="marquee">
      <div class="marquee-inner">
        UNAPOLOGETIC PRESENCE — NO PLUS ONE — UNAPOLOGETIC PRESENCE — NO PLUS ONE — UNAPOLOGETIC PRESENCE — NO PLUS ONE — UNAPOLOGETIC PRESENCE — NO PLUS ONE —
      </div>
    </section>

    <!-- =========================
         Style Points
    ========================== -->
    <section class="style-points">
      <div class="section-label reveal">Style points</div>
      <h2 class="section-heading reveal">Where the night sharpens</h2>

      <div class="style-grid">
        <article class="style-card reveal">
          <div class="style-image">
            <img src="https://picsum.photos/600/500?random=70&grayscale" alt="Silhouette">
          </div>
          <div class="style-body">
            <div class="style-title">The Silhouette</div>
            <p class="style-copy">
              Long lines, clean breaks, and negative space that does most of the talking.
            </p>
          </div>
        </article>

        <article class="style-card reveal">
          <div class="style-image">
            <img src="https://picsum.photos/600/500?random=71&grayscale" alt="Palette">
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
            <img src="https://picsum.photos/600/500?random=72&grayscale" alt="The Night">
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
    <section class="lookbook">
      <div class="section-label reveal">Lookbook</div>
      <h2 class="section-heading reveal">No plus one, multiple looks</h2>

      <div class="lookbook-grid">
        <div class="look-tile tall reveal"
             style="background-image:url('https://picsum.photos/600/900?random=80&grayscale');">
          <div class="look-label">Look 01</div>
        </div>
        <div class="look-tile wide reveal"
             style="background-image:url('https://picsum.photos/900/600?random=81&grayscale');">
          <div class="look-label">Look 02</div>
        </div>
        <div class="look-tile reveal"
             style="background-image:url('https://picsum.photos/600/600?random=82&grayscale');">
          <div class="look-label">Look 03</div>
        </div>
        <div class="look-tile large reveal"
             style="background-image:url('https://picsum.photos/900/900?random=83&grayscale');">
          <div class="look-label">Look 04</div>
        </div>
        <div class="look-tile reveal"
             style="background-image:url('https://picsum.photos/600/600?random=84&grayscale');">
          <div class="look-label">Look 05</div>
        </div>
        <div class="look-tile tall reveal"
             style="background-image:url('https://picsum.photos/600/900?random=85&grayscale');">
          <div class="look-label">Look 06</div>
        </div>
      </div>
    </section>

    <!-- =========================
         Newsletter & Footer
    ========================== -->
    <section class="newsletter">
      <h2 class="newsletter-title">The Guest List</h2>
      <p class="newsletter-copy">
        Drops, edits, and after-hours fittings — straight to the inboxes we actually read.
      </p>
      <form class="newsletter-form" onsubmit="event.preventDefault();">
        <input type="email" class="newsletter-input" placeholder="Your email" required>
        <button class="newsletter-submit" aria-label="Join guest list">➝</button>
      </form>
    </section>

    <footer class="footer">
      <div class="footer-logo">INANNA</div>
      <div class="footer-grid">
        <div>
          <div class="footer-col-title">Contact</div>
          <a href="#" class="footer-link">Studio</a>
          <a href="#" class="footer-link">Support</a>
          <a href="#" class="footer-link">Press</a>
        </div>
        <div>
          <div class="footer-col-title">Explore</div>
          <a href="#shop" class="footer-link">Shop</a>
          <a href="#collections" class="footer-link">Collections</a>
          <a href="#atelier" class="footer-link">Atelier</a>
        </div>
        <div>
          <div class="footer-col-title">Legal</div>
          <a href="#" class="footer-link">Terms</a>
          <a href="#" class="footer-link">Privacy</a>
          <a href="#" class="footer-link">Cookies</a>
        </div>
        <div>
          <div class="footer-col-title">Social</div>
          <a href="#" class="footer-link">Instagram</a>
          <a href="#" class="footer-link">Lookbook</a>
          <a href="#" class="footer-link">Journal</a>
        </div>
      </div>
      <div class="footer-bottom">
        © <?php echo date('Y'); ?> INANNA · NO PLUS ONE COLLECTION
      </div>
    </footer>
  </main>

  <script>
    // =========================
    // Sticky Nav on Scroll
    // =========================
    const nav = document.getElementById('mainNav');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 10) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });

    // =========================
    // Mobile Nav Toggle
    // =========================
    const hamburger = document.getElementById('hamburger');
    const mobileNav = document.getElementById('mobileNav');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      mobileNav.classList.toggle('open');
    });

    mobileNav.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        mobileNav.classList.remove('open');
      });
    });

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
    // Hotspots Logic
    // =========================
    const shopLook = document.getElementById('shopLook');
    const hotspots = document.querySelectorAll('.hotspot');
    const hotspotCards = document.querySelectorAll('.hotspot-card');

    function closeAllHotspots() {
      hotspotCards.forEach(card => card.classList.remove('visible'));
      shopLook.classList.remove('hotspot-active');
    }

    hotspots.forEach(hotspot => {
      hotspot.addEventListener('mouseenter', () => {
        const id = hotspot.dataset.hotspot;
        closeAllHotspots();
        const card = document.getElementById('hotspot-' + id);
        if (card) {
          card.classList.add('visible');
          shopLook.classList.add('hotspot-active');
        }
      });
    });

    shopLook.addEventListener('mouseleave', () => {
      closeAllHotspots();
    });

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
