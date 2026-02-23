<?php
$page_title = 'His & Hers | World Of Inanna';
include __DIR__ . '/includes/header.php';
?>

<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<style>
    /* --------------------------------------
    FONTS & BASE LAYOUT
    -------------------------------------- */
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@200;300;400;500&display=swap');

    .collab-page-wrapper {
        background-color: #fff;
        color: #000;
        font-family: 'Montserrat', sans-serif;
        overflow-x: hidden;
    }

    /* --------------------------------------
    HERO SECTION
    -------------------------------------- */
    .collab-hero {
        position: relative;
        width: 100%;
        height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* Move content to top */
        align-items: center;
        text-align: center;
        color: #fff;
        overflow: hidden;
    }

    .collab-hero-content {
        position: relative;
        z-index: 3;
        width: 100%;
        padding-top: 100px; /* Offset from top */
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .collab-hero-title-overlay {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(3rem, 10vw, 8rem);
        text-transform: uppercase;
        margin: 0;
        font-weight: 300;
        line-height: 1;
        letter-spacing: 0.1em;
        color: #fff;
        text-shadow: 0 4px 30px rgba(0, 0, 0, 0.4);
    }

    .collab-hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('assets/images/hero.webp');
        background-size: cover;
        background-position: center;
        z-index: 1;
        transform: scale(1.15); /* Static zoom */
    }

    .collab-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 2;
    }

    .collab-hero-content {
        position: relative;
        z-index: 3;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }


    .collab-hero-arrow {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        font-size: 1.5rem;
        animation: bounce 2s infinite;
        opacity: 0.7;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0) translateX(-50%);
        }

        40% {
            transform: translateY(-10px) translateX(-50%);
        }

        60% {
            transform: translateY(-5px) translateX(-50%);
        }
    }

    /* --------------------------------------
    INTRO SECTION
    -------------------------------------- */
    .collab-intro {
        padding: 100px 20px;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .collab-intro-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.25rem;
        letter-spacing: 0.4em;
        text-transform: uppercase;
        margin-bottom: 50px;
        font-weight: 600;
    }

    .collab-intro-subtitle {
        font-size: 0.95rem;
        line-height: 1.8;
        margin-bottom: 40px;
        font-weight: 400;
        color: #1a1a1a;
        font-style: italic;
        padding: 0 40px;
    }

    .collab-intro-body {
        font-size: 0.85rem;
        line-height: 2;
        color: #444;
        margin-bottom: 60px;
        font-weight: 300;
        text-align: center;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        letter-spacing: 0.02em;
    }

    .collab-explore-btn {
        display: inline-block;
        background-color: #000;
        color: #fff;
        text-decoration: none;
        padding: 16px 55px;
        font-size: 0.7rem;
        letter-spacing: 0.4em;
        text-transform: uppercase;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        border: 1px solid #000;
        font-weight: 500;
    }

    .collab-explore-btn:hover {
        background-color: transparent;
        color: #000;
    }

    /* --------------------------------------
    MEDIA GRID SECTION
    -------------------------------------- */
    .collab-media-grid {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        margin-bottom: 0;
        /* Changed to 0 as runway follows immediately */
    }

    /* --------------------------------------
    RUNWAY SECTION (Marquee)
    -------------------------------------- */
    .collab-runway {
        padding: 100px 0;
        background-color: #fff;
        overflow: hidden;
    }

    .collab-runway.collab-runway-secondary {
        padding: 20px 0;
    }

    .runway-img-wrapper {
        width: 25vw;
        min-width: 300px;
        aspect-ratio: 3/4;
        flex-shrink: 0;
        overflow: hidden;
    }

    .runway-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .runway-img-wrapper:hover img {
        transform: scale(1.05);
    }

    .runway-marquee {
        display: flex;
        gap: 4rem;
        animation: marquee 50s linear infinite;
        width: max-content;
    }

    @keyframes marquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(calc(-7 * (max(25vw, 300px) + 4rem)));
        }
    }

    /* --------------------------------------
    BOTTOM GRID SECTION
    -------------------------------------- */
    .collab-bottom-grid {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        padding: 0 40px 100px;
        gap: 40px;
        box-sizing: border-box;
    }

    .bottom-grid-item {
        flex: 1;
        min-width: 450px;
        height: 700px;
        overflow: hidden;
    }

    .bottom-grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* --------------------------------------
    ADDITIONAL COLLAB CONTENT
    -------------------------------------- */
    .collab-text-grid {
        display: flex;
        justify-content: space-between;
        padding: 0 40px 80px;
        gap: 60px;
        box-sizing: border-box;
    }

    .collab-text-item {
        flex: 1;
        text-align: center;
    }

    .collab-text-item h4 {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .collab-text-item p {
        font-size: 0.8rem;
        line-height: 1.8;
        color: #555;
        font-weight: 300;
        max-width: 450px;
        margin: 0 auto;
    }

    /* --------------------------------------
    VIDEO SECTION
    -------------------------------------- */
    .collab-video-section {
        width: 100%;
        padding: 0 40px 0px;
        box-sizing: border-box;
    }

    .video-container {
        position: relative;
        width: 100%;
        aspect-ratio: 16/9;
        overflow: hidden;
        background: #000;
    }

    .video-container video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-controls-bottom {
        position: absolute;
        bottom: 30px;
        right: 30px;
        z-index: 5;
    }

    .video-toggle-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: #fff;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        transition: background 0.3s;
    }

    .video-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    .collab-video-footer {
        padding: 40px 40px 100px;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 10;
        background: #fff;
    }

    .collab-video-footer h4 {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 0.4em;
        text-transform: uppercase;
        margin-bottom: 30px;
        font-weight: 600;
    }

    .collab-video-footer p {
        font-size: 0.85rem;
        line-height: 2;
        color: #444;
        font-weight: 300;
        letter-spacing: 0.02em;
    }

    /* =========================
       PRODUCT SPOTLIGHT CSS
    ========================== */
    .spotlight-section {
        background: #fff;
        width: 100%;
        padding: 4rem 0;
    }

    .spotlight {
        max-width: 1400px;
        margin: 0 auto 100px;
        padding: 0 24px;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
        gap: 6rem;
        align-items: center;
    }

    .spotlight-image {
        width: 100%;
        border-radius: 0;
        overflow: hidden;
        background-position: center;
        background-size: cover;
        aspect-ratio: 1 / 1;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .spotlight-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .spotlight-meta {
        max-width: 420px;
        line-height: 2.1;
        color: #212529;
    }

    .section-label {
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 11px;
        color: #b0b0b0;
        margin-bottom: 10px;
    }

    .spotlight-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 30px;
        margin-bottom: 8px;
        color: #212529;
    }

    .spotlight-price {
        font-size: 14px;
        margin-bottom: 12px;
        color: #212529;
    }

    .spotlight-desc {
        font-size: 13px;
        margin-bottom: 14px;
        color: #212529;
    }

    .spotlight-features {
        list-style: none;
        margin-bottom: 18px;
        font-size: 13px;
        color: #b0b0b0;
        padding: 0;
    }

    .spotlight-features li::before {
        content: "✓";
        margin-right: 6px;
        color: #b0b0b0;
    }

    .spotlight-size-label {
        font-size: 11px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        margin-bottom: 6px;
        color: #b0b0b0;
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
        border: 1px solid rgba(91, 18, 18, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        cursor: pointer;
        background: #fff;
        color: #212529;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .size-pill:hover,
    .size-pill.selected {
        background: #212529;
        color: #fff;
        border-color: #212529;
    }

    .btn-spotlight {
        padding: 10px 16px;
        border-radius: 999px;
        border: 1px solid rgba(91, 18, 18, 0.08);
        background: transparent;
        color: #212529;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-size: 11px;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-spotlight:hover {
        background: #212529;
        color: #fff;
        transform: translateY(-1px);
    }

    .collab-media-item {
        flex: 0 0 50%;
        min-height: 800px;
        position: relative;
        overflow: hidden;
    }

    .collab-media-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .collab-media-black {
        background-color: #000;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #fff;
        text-align: center;
        padding: 40px;
    }

    .collab-media-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.5rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        font-weight: 300;
    }

    .collab-media-title span {
        font-family: 'Montserrat', sans-serif;
        font-weight: 600;
        font-size: 2.2rem;
        letter-spacing: 0.05em;
    }

    /* Controls Overlay Placeholder */
    .media-controls {
        position: absolute;
        top: 30px;
        right: 30px;
        display: flex;
        gap: 15px;
        z-index: 10;
        opacity: 0.8;
    }

    .media-control-btn {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 1.2rem;
        cursor: pointer;
    }

    /* --------------------------------------
    RESPONSIVE DESIGN
    -------------------------------------- */

    /* --- Tablet ≤ 992px --- */
    @media screen and (max-width: 992px) {

        /* Hero Overlay Styles */
        .collab-hero { height: 90vh; }
        .collab-hero-content { padding-top: 80px; }
        .collab-hero-title-overlay {
            font-size: clamp(2.5rem, 12vw, 5rem);
        }

        /* Intro */
        .collab-intro {
            padding: 60px 24px;
        }

        .collab-intro-subtitle {
            padding: 0 20px;
        }

        /* Media Grid — stack panels vertically */
        .collab-media-item {
            flex: 0 0 100%;
            min-height: 500px;
        }

        /* Runway */
        .collab-runway {
            padding: 60px 0;
        }

        .runway-img-wrapper {
            width: 40vw;
            min-width: 220px;
        }

        /* Bottom Grid */
        .collab-bottom-grid {
            padding: 0 24px 60px;
            gap: 24px;
        }

        .bottom-grid-item {
            min-width: 280px;
            height: 480px;
        }

        /* Text Grid */
        .collab-text-grid {
            padding: 0 24px 60px;
            gap: 40px;
        }

        /* Video */
        .collab-video-section {
            padding: 0 24px;
        }

        .collab-video-footer {
            padding: 30px 24px 60px;
        }

        /* Spotlight */
        .spotlight {
            grid-template-columns: 1fr;
            gap: 3rem;
            margin-bottom: 60px;
        }

        .spotlight-image {
            aspect-ratio: 4/3;
        }

        .spotlight-meta {
            max-width: 100%;
        }
    }

    /* --- Mobile ≤ 576px --- */
    @media screen and (max-width: 576px) {

        /* Hero Overlay Mobile */
        .collab-hero { height: 75vh; }
        .collab-hero-content { padding-top: 60px; }
        .collab-hero-title-overlay {
            font-size: clamp(2.5rem, 18vw, 4rem);
            letter-spacing: 0.05em;
        }
        .collab-hero-eyebrow {
            font-size: 0.65rem;
            letter-spacing: 0.3em;
            margin-bottom: 16px;
        }

        /* Intro */
        .collab-intro {
            padding: 48px 16px;
        }

        .collab-intro-title {
            font-size: 1rem;
            letter-spacing: 0.25em;
            margin-bottom: 30px;
        }

        .collab-intro-subtitle {
            padding: 0 8px;
            font-size: 0.88rem;
        }

        .collab-intro-body {
            font-size: 0.82rem;
        }

        .collab-explore-btn {
            padding: 14px 36px;
            font-size: 0.65rem;
        }

        /* Media Grid */
        .collab-media-item {
            min-height: 380px;
        }

        .collab-media-title {
            font-size: 1.6rem;
        }

        .collab-media-title span {
            font-size: 1.4rem;
        }

        .collab-media-black {
            padding: 24px 16px;
        }

        /* Runway */
        .collab-runway {
            padding: 40px 0;
        }

        .runway-img-wrapper {
            width: 60vw;
            min-width: 180px;
        }

        /* Bottom Grid — full-width stacked */
        .collab-bottom-grid {
            flex-direction: column;
            padding: 0 16px 48px;
            gap: 16px;
        }

        .bottom-grid-item {
            flex: none;
            width: 100%;
            min-width: 0;
            height: 280px;
        }

        /* Text Grid — single column */
        .collab-text-grid {
            flex-direction: column;
            padding: 0 16px 48px;
            gap: 32px;
        }

        /* Video */
        .collab-video-section {
            padding: 0 8px;
        }

        .video-controls-bottom {
            bottom: 16px;
            right: 16px;
        }

        .collab-video-footer {
            padding: 24px 16px 48px;
        }

        .collab-video-footer h4 {
            font-size: 0.9rem;
            letter-spacing: 0.25em;
            margin-bottom: 16px;
        }

        .collab-video-footer p {
            font-size: 0.78rem;
        }

        /* Spotlight */
        .spotlight-section {
            padding: 2rem 0;
        }

        .spotlight {
            padding: 0 16px;
            gap: 2rem;
            margin-bottom: 40px;
        }

        .spotlight-image {
            aspect-ratio: 1/1;
        }

        .spotlight-name {
            font-size: 22px;
        }

        .spotlight-desc,
        .spotlight-features {
            font-size: 12px;
        }
    }
</style>

<div class="collab-page-wrapper">
    <!-- Hero Banner -->
    <section class="collab-hero">
        <div class="collab-hero-bg"></div>
        <div class="collab-hero-overlay"></div>
        <div class="collab-hero-content">
            <!-- <h1 class="collab-hero-title-overlay">HIS & HER'S</h1> -->
        </div>
        <div class="collab-hero-arrow">
            <i class="bi bi-chevron-down"></i>
        </div>
    </section>

    <!-- Introduction Text -->
    <section class="collab-intro">
        <h2 class="collab-intro-title">HIS & HERS</h2>

        <p class="collab-intro-subtitle">
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Id exercitationem dolorem reprehenderit, ex illum
            officiis, laboriosam quis voluptatum veniam est quam voluptatibus harum molestiae ipsa? Obcaecati nostrum
            dicta minus incidunt.
            Unde, nesciunt earum.
            Et facilis aperiam dolorum nobis adipisci omnis dolores laborum provident suscipit, culpa, ipsa fugit
            accusantium reiciendis accusamus a recusandae eos corrupti officia sint nulla! Dolore, impedit similique!


        </p>

        <p class="collab-intro-body">
            Lorem ipsum dolor sit amet consectetur adipisicing elit. Illum tenetur dolore voluptas id deleniti cum alias
            possimus fugit officiis repudiandae culpa, corrupti voluptatum. Quisquam sint accusamus molestias
            blanditiis, doloribus porro.
            Fugiat sit, reiciendis nemo voluptate quidem necessitatibus id illum vero qui perferendis sed, quis debitis
            ut nostrum modi consequatur dignissimos dolorem aliquid earum quo laudantium porro quos excepturi ea.
            Recusandae!

        </p>

        <a href="#" class="collab-explore-btn">EXPLORE</a>
    </section>

    <!-- Media Grid -->
    <section class="collab-media-grid">
        <!-- Left Image () -->
        <div class="collab-media-item">
            <img src="https://images.unsplash.com/photo-1596704017254-9b121068fb31?q=80&w=1974&auto=format&fit=crop"
                alt="Collab-Media" class="collab-media-img">
        </div>

        <!-- Right Graphic/Logo -->
        <div class="collab-media-item collab-media-black">
            <div class="media-controls">
                <button class="media-control-btn"><i class="bi bi-volume-mute"></i></button>
                <button class="media-control-btn"><i class="bi bi-pause"></i></button>
            </div>

            <h3 class="collab-media-title">
                WORLD OF <br><span>INANNA</span>
            </h3>
        </div>
    </section>

    <!-- Runway Section (Marquee) -->
    <section class="collab-runway">
        <div class="runway-marquee">
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1581338834647-b0fb40704e21?q=80&w=1974&auto=format&fit=crop"
                    alt="Runway 1"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1594750801103-625807afb2fb?q=80&w=2070&auto=format&fit=crop"
                    alt="Runway 2"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1521334885634-9552f9540871?q=80&w=2074&auto=format&fit=crop"
                    alt="Runway 3"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=2070&auto=format&fit=crop"
                    alt="Runway 4"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1539109132314-3477524c7540?q=80&w=1974&auto=format&fit=crop"
                    alt="Runway 5"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1582266255765-fa5cf1a1d501?q=80&w=2070&auto=format&fit=crop"
                    alt="Runway 6"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=1974&auto=format&fit=crop"
                    alt="Runway 7"></div>

            <!-- Duplicate for infinite loop -->
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1581338834647-b0fb40704e21?q=80&w=1974&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1594750801103-625807afb2fb?q=80&w=2070&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1521334885634-9552f9540871?q=80&w=2074&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=2070&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1539109132314-3477524c7540?q=80&w=1974&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1582266255765-fa5cf1a1d501?q=80&w=2070&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=1974&auto=format&fit=crop">
            </div>
        </div>
    </section>

    <!-- Bottom Grid Section -->
    <section class="collab-bottom-grid">
        <div class="bottom-grid-item">
            <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=2070&auto=format&fit=crop"
                alt="WOI Image 1">
        </div>
        <div class="bottom-grid-item">
            <img src="https://images.unsplash.com/photo-1469334031218-e382a71b716b?q=80&w=2070&auto=format&fit=crop"
                alt="WOI Image 2">
        </div>
    </section>

    <!-- Additional Collab Text -->
    <section class="collab-text-grid">
        <div class="collab-text-item">
            <h4>HIS & HER COLLECTIONS</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Adipisci cupiditate perspiciatis itaque
                voluptatum animi quos nihil voluptatibus cumque reiciendis nesciunt aliquid, magnam atque iusto tempore
                rerum necessitatibus totam numquam dignissimos!</p>
        </div>
        <div class="collab-text-item">
            <h4>HIS & HER COLLECTIONS</h4>
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Adipisci cupiditate perspiciatis itaque
                voluptatum animi quos nihil voluptatibus cumque reiciendis nesciunt aliquid, magnam atque iusto tempore
                rerum necessitatibus totam numquam dignissimos!
            </p>
        </div>
    </section>

    <!-- Video Section -->
    <section class="collab-video-section">
        <div class="video-container">
            <video id="collabVideo" autoplay muted loop playsinline>
                <source src="https://cdn.pixabay.com/video/2021/04/12/70914-538466632_large.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="video-controls-bottom">
                <button class="video-toggle-btn" id="videoToggle"><i class="bi bi-pause"></i></button>
            </div>
        </div>
    </section>

    <!-- Runway Section (Marquee) -->
    <section class="collab-runway collab-runway-secondary">
        <div class="runway-marquee">
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1581338834647-b0fb40704e21?q=80&w=1974&auto=format&fit=crop"
                    alt="Runway 1"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1594750801103-625807afb2fb?q=80&w=2070&auto=format&fit=crop"
                    alt="Runway 2"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1521334885634-9552f9540871?q=80&w=2074&auto=format&fit=crop"
                    alt="Runway 3"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=2070&auto=format&fit=crop"
                    alt="Runway 4"></div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1539109132314-3477524c7540?q=80&w=1974&auto=format&fit=crop"
                    alt="Runway 5"></div>

            <!-- Duplicate for infinite loop -->
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1581338834647-b0fb40704e21?q=80&w=1974&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1594750801103-625807afb2fb?q=80&w=2070&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1521334885634-9552f9540871?q=80&w=2074&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1512496015851-a90fb38ba796?q=80&w=2070&auto=format&fit=crop">
            </div>
            <div class="runway-img-wrapper"><img
                    src="https://images.unsplash.com/photo-1539109132314-3477524c7540?q=80&w=1974&auto=format&fit=crop">
            </div>
        </div>
    </section>

    <div class="collab-video-footer">
        <h4>HIS & HER COLLECTIONS</h4>
        <p>
            Lorem, ipsum dolor sit amet consectetur adipisicing elit. Molestias tenetur nesciunt non saepe iure
            quasi sit quia, delectus accusantium consectetur, sequi, facilis eveniet consequuntur! Alias illum ut
            doloremque quos dolor?
            Officiis sed modi commodi aliquid? Qui harum eos perferendis quibusdam sapiente ducimus, culpa
            exercitationem voluptatum minima cupiditate illo iste aliquid eaque enim. Explicabo, laudantium
            consequuntur quasi corrupti eaque nulla ducimus.
        </p>
    </div>

    <!--  -->

    <!-- Product Spotlight -->

    <section class="spotlight-section">
        <div class="spotlight">
            <!-- LEFT: IMAGE -->
            <div class="spotlight-image">
                <img src="https://images.unsplash.com/photo-1596704017254-9b121068fb31?q=80&w=1974&auto=format&fit=crop"
                    alt="Featured Product">
            </div>

            <!-- RIGHT: CONTENT -->
            <div class="spotlight-meta">
                <div class="section-label">Product spotlight</div>
                <h2 class="spotlight-name">Limited Edition Collaboration Piece</h2>
                <div class="spotlight-price">₹12,499.00</div>
                <p class="spotlight-desc">
                    Experience the pinnacle of our latest collaboration. This piece combines artisan craftsmanship with
                    modern silhouettes, creating a timeless addition to your wardrobe. Perfect for those who appreciate
                    fine detailing and unparalleled comfort.
                </p>
                <ul class="spotlight-features">
                    <li>Exclusive handcrafted embroidery</li>
                    <li>Premium breathable fabric blend</li>
                    <li>Signature collaboration branding</li>
                    <li>Limited edition production</li>
                </ul>
                <div class="spotlight-size-label">Select size</div>
                <div class="spotlight-sizes" id="sizeSelector">
                    <div class="size-pill">XS</div>
                    <div class="size-pill selected">S</div>
                    <div class="size-pill">M</div>
                    <div class="size-pill">L</div>
                </div>
                <div class="spotlight-actions">
                    <a class="btn-spotlight" href="products.php">SHOP NOW</a>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- ============================
     EDITORIAL COLLAB FEATURE
     ============================ -->
<style>
    /* --- Editorial Feature --- */
    .editorial-feature {
        background: #fff;
    }

    .editorial-hero {
        position: relative;
        width: 100%;
        height: 70vh;
        min-height: 450px;
        overflow: hidden;
    }

    .editorial-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 30%;
    }

    .editorial-hero-label {
        position: absolute;
        bottom: 2.5rem;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        color: #fff;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1.5rem;
        font-weight: 500;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.5);
        white-space: nowrap;
    }

    .editorial-body {
        text-align: center;
        padding: 60px 40px 40px;
        max-width: 580px;
        margin: 0 auto;
    }

    .editorial-body h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #1a1a1a;
        margin-bottom: 16px;
    }

    .editorial-body p {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.8rem;
        color: #555;
        letter-spacing: 0.03em;
        line-height: 1.8;
        margin-bottom: 20px;
    }

    .editorial-divider {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
    }

    .editorial-divider svg {
        width: 40px;
        opacity: 0.5;
    }

    .editorial-logo-divider {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
    }

    .editorial-logo {
        height: 90px;
        width: auto;
        opacity: 1;
    }

    .editorial-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        padding: 0 8px 8px;
        overflow: hidden;
    }

    .editorial-grid-item {
        aspect-ratio: 3/4;
        overflow: hidden;
    }

    .editorial-grid-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
    }

    .editorial-grid-item:hover img {
        transform: scale(1.08);
    }

    /* --- Split Form Layout --- */
    .collab-form-wrapper {
        padding: 80px 40px;
        background: #f9f7f4;
    }

    .collab-form-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 560px;
        max-width: 1100px;
        margin: 0 auto;
        border: 1px solid #d8d4ce;
        overflow: hidden;
    }

    .collab-form-image {
        position: relative;
        overflow: hidden;
    }

    .collab-form-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .collab-form-image-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.55) 0%, transparent 60%);
    }

    .collab-form-image-text {
        position: absolute;
        bottom: 3rem;
        left: 3rem;
        color: #fff;
    }

    .collab-form-image-text h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.6rem;
        font-weight: 400;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        line-height: 1.1;
        margin-bottom: 10px;
    }

    .collab-form-image-text p {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.7rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        opacity: 0.8;
    }

    .collab-form-panel {
        background: #fafaf8;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 70px 60px;
    }

    .collab-form-panel .form-eyebrow {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.65rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 14px;
    }

    .collab-form-panel h3 {
        font-family: 'Cormorant Garamond', serif;
        font-size: 2.2rem;
        font-weight: 400;
        color: #1a1a1a;
        letter-spacing: 0.04em;
        margin-bottom: 36px;
        line-height: 1.2;
    }

    .collab-form-panel .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .collab-form-panel .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .collab-form-panel .form-group.full-width {
        grid-column: 1 / -1;
    }

    .collab-form-panel label {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.6rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: #888;
    }

    .collab-form-panel input,
    .collab-form-panel textarea,
    .collab-form-panel select {
        border: none;
        border-bottom: 1px solid #c9c9c9;
        background: transparent;
        padding: 10px 0;
        font-family: 'Cormorant Garamond', serif;
        font-size: 1rem;
        color: #1a1a1a;
        outline: none;
        transition: border-color 0.3s ease;
        width: 100%;
    }

    .collab-form-panel input:focus,
    .collab-form-panel textarea:focus,
    .collab-form-panel select:focus {
        border-bottom-color: #1a1a1a;
    }

    .collab-form-panel textarea {
        resize: none;
        height: 80px;
    }

    .collab-form-panel .btn-submit {
        display: inline-block;
        margin-top: 32px;
        padding: 14px 46px;
        border: 1px solid #1a1a1a;
        background: #1a1a1a;
        color: #fff;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.65rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.3s, color 0.3s;
        align-self: flex-start;
    }

    .collab-form-panel .btn-submit:hover {
        background: transparent;
        color: #1a1a1a;
    }

    /* --- Tablet ≤ 992px --- */
    @media (max-width: 992px) {
        .editorial-hero {
            height: 55vh;
        }

        .editorial-hero-label {
            font-size: 1.1rem;
            white-space: normal;
            padding: 0 30px;
        }

        .editorial-body {
            padding: 50px 30px 30px;
        }

        .editorial-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
            padding: 0 6px 6px;
        }

        .collab-form-wrapper {
            padding: 60px 30px;
        }

        .collab-form-section {
            grid-template-columns: 1fr;
            max-width: 600px;
        }

        .collab-form-image {
            height: 45vh;
        }

        .collab-form-panel {
            padding: 40px 30px;
        }
        .collab-form-panel .form-row {
            grid-template-columns: 1fr;
        }
    }

    /* --- Mobile ≤ 576px --- */
    @media (max-width: 576px) {
        .editorial-hero {
            height: 45vh;
            min-height: 280px;
        }

        .editorial-hero-label {
            font-size: 0.85rem;
            letter-spacing: 0.1em;
            padding: 0 16px;
            bottom: 1.5rem;
        }

        .editorial-body {
            padding: 40px 20px 20px;
        }

        .editorial-body h3 {
            font-size: 1.5rem;
            letter-spacing: 0.1em;
        }

        .editorial-body p {
            font-size: 0.75rem;
        }

        .editorial-logo {
            height: 75px;
        }

        .editorial-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 4px;
            padding: 0 4px 4px;
        }

        .collab-form-wrapper {
            padding: 40px 16px;
        }

        .collab-form-section {
            grid-template-columns: 1fr;
        }

        .collab-form-image {
            height: 40vh;
        }

        .collab-form-image-text h3 {
            font-size: 1.8rem;
        }

        .collab-form-image-text {
            left: 1.5rem;
            bottom: 1.5rem;
        }

        .collab-form-panel {
            padding: 32px 20px;
        }

        .collab-form-panel h3 {
            font-size: 1.6rem;
            margin-bottom: 24px;
        }
        .collab-form-panel .btn-submit {
            width: 100%;
            text-align: center;
            padding: 14px 20px;
        }
    }

    /* --- Extra Small ≤ 420px --- */
    @media (max-width: 420px) {
        .editorial-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Editorial Feature Section -->
<section class="editorial-feature">
    <!-- Hero Image with Label -->
    <div class="editorial-hero">
        <img src="https://images.unsplash.com/photo-1615529182904-14819c35db37?q=80&w=2080&auto=format&fit=crop"
            alt="Collaboration Feature">
        <div class="editorial-hero-label">His &amp; Her Collections — The Collaboration</div>
    </div>

    <!-- Title & Description -->
    <div class="editorial-body">
        <h3>The Living Craft</h3>
        <p>
            A limited-edition collaboration that celebrates the art of togetherness.
            Handcrafted for those who believe that beauty is a shared language,
            in collaboration with The Inanna Art Foundation.
        </p>
        <!-- Site Logo as divider -->
        <div class="editorial-logo-divider">
            <img src="assets/images/footer_logo.webp" alt="Inanna" class="editorial-logo"
                style=" background-color: #8B0000;">
        </div>
    </div>

    <!-- 4-column Image Grid -->
    <div class="editorial-grid">
        <div class="editorial-grid-item">
            <img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=800&auto=format&fit=crop"
                alt="Collab 1">
        </div>
        <div class="editorial-grid-item">
            <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=800&auto=format&fit=crop"
                alt="Collab 2">
        </div>
        <div class="editorial-grid-item">
            <img src="https://images.unsplash.com/photo-1509631179647-0177331693ae?q=80&w=800&auto=format&fit=crop"
                alt="Collab 3">
        </div>
        <div class="editorial-grid-item">
            <img src="https://images.unsplash.com/photo-1583744946564-b52d01e7f922?q=80&w=800&auto=format&fit=crop"
                alt="Collab 4">
        </div>
    </div>
</section>

<!-- Consultation Form — Split Layout -->
<div class="collab-form-wrapper">
    <section class="collab-form-section">
        <!-- LEFT: Image Panel -->
        <div class="collab-form-image">
            <img src="https://images.unsplash.com/photo-1610030469983-98e550d6193c?q=80&w=1887&auto=format&fit=crop"
                alt="Consultation">
            <div class="collab-form-image-overlay"></div>
            <div class="collab-form-image-text">
                <h3>Begin Your<br>Story With Us</h3>
                <p>Curated Styling for Two</p>
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="collab-form-panel">
            <div class="form-eyebrow">Private Consultation</div>
            <h3>Book Your Appointment</h3>

            <form action="send-lead.php" method="POST" class="lead-form">
                <div>
                    <label>NAME</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>WEDDING DATE</label>
                    <input type="date" name="wedding_date" required>
                </div>

                <div>
                    <label>CITY</label>
                    <input type="text" name="city" required>
                </div>

                <div>
                    <label>FUNCTIONS</label>
                    <textarea name="functions" placeholder="Wedding, Reception, Mehendi, etc." required></textarea>
                </div>

                <button type="submit" class="btn-submit">Submit</button>
            </form>
        </div>
    </section>
</div>

<!-- Toastify JS -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var video = document.getElementById('collabVideo');
        var btn = document.getElementById('videoToggle');
        if (btn && video) {
            var icon = btn.querySelector('i');
            btn.addEventListener('click', function () {
                if (video.paused) {
                    video.play();
                    icon.classList.remove('bi-play');
                    icon.classList.add('bi-pause');
                } else {
                    video.pause();
                    icon.classList.remove('bi-pause');
                    icon.classList.add('bi-play');
                }
            });
        }

        // --- Lead Form AJAX Submission ---
        const leadForm = document.querySelector('.lead-form');
        if (leadForm) {
            leadForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;

                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                fetch('send-lead.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('successfully')) {
                        Toastify({
                            text: "Booking submitted successfully!",
                            duration: 4000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            stopOnFocus: true,
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)",
                            }
                        }).showToast();
                        leadForm.reset();
                    } else {
                        Toastify({
                            text: data || "Error submitting inquiry. Please try again.",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            stopOnFocus: true,
                            style: {
                                background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                            }
                        }).showToast();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Toastify({
                        text: "Something went wrong. Please check your connection.",
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        stopOnFocus: true,
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                        }
                    }).showToast();
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                });
            });
        }
    });
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>