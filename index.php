<?php
$page_title = 'HIS & HERS - Wedding Guest Wardrobe by INANA';
include __DIR__ . '/includes/header.php';
?>

<!-- Toastify CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<style>
    /* --------------------------------------
    FONTS & BASE LAYOUT
    -------------------------------------- */
    @import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600;700&family=Montserrat:wght@200;300;400;500&family=Great+Vibes&display=swap');

    body, html, .collab-page-wrapper {
        background-color: #faf8f0 !important;
        color: #000;
        font-family: 'Montserrat', sans-serif;
        margin: 0;
        padding: 0;
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
        justify-content: center;
        align-items: center;
        color: #fff;
        overflow: hidden;
    }

    .collab-hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('assets/images/his-her/img-16.webp');
        background-size: cover;
        background-position: center;
        z-index: 1;
    }

    .collab-hero-content {
        position: relative;
        z-index: 3;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0 5%;
        box-sizing: border-box;
    }

    .hero-side-row {
        position: absolute;
        top: 30%; 
        left: 57%;
        transform: translateX(-50%);
        width: 76%; 
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 4;
        pointer-events: none;
        box-sizing: border-box;
    }

    .hero-side-text {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(2rem, 4.5vw, 4rem);
        color: #fff;
        font-weight: 300;
        letter-spacing: 0.25em;
        margin: 0;
        opacity: 0.75;
        text-transform: uppercase;
    }

    .hero-bottom-group {
        position: absolute;
        bottom: 8%;
        text-align: center;
        z-index: 5;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .luxury-brand {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(1.8rem, 4vw, 3.8rem);
        font-weight: 300;
        color: #fff;
        text-transform: uppercase;
        letter-spacing: 0.10em;
        margin: 0;
        line-height: 1.1;
    }

    .presentation-text {
        font-family: 'Great Vibes', cursive;
        font-size: clamp(2.5rem, 5.5vw, 5rem);
        color: #fff;
        margin: -12px 0 4px;
        font-weight: 400;
        opacity: 0.50;
    }

    .redefining-prestige {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(0.6rem, 1vw, 1rem);
        letter-spacing: 8px;
        text-transform: uppercase;
        color: rgba(0, 0, 0, 1);
        margin: 0;
        font-weight: 700;
        margin-top: 1rem;
    }

    .collab-hero-arrow {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        font-size: 1.2rem;
        animation: bounce 2s infinite;
        opacity: 0.5;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
        40% { transform: translateY(-10px) translateX(-50%); }
        60% { transform: translateY(-5px) translateX(-50%); }
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
        flex-wrap: nowrap;
        width: 100%;
        max-width: 1350px;
        height: auto;
        margin: 0 auto;
        padding: 0;
        gap: 25px;
        box-sizing: border-box;
    }

    .collab-media-item {
        flex: 1; 
        position: relative;
        overflow: hidden;
        height: 900px;
    }

    .collab-media-img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    /* --------------------------------------
    RUNWAY SECTION (Marquee)
    -------------------------------------- */
    .collab-runway {
        padding: 160px 0;
        background-color: #faf8f0;
        overflow: hidden;
    }

    .collab-runway.collab-runway-secondary {
        padding: 60px 0;
    }

    .runway-card {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .runway-img-wrapper {
        width: auto;
        height: 450px;
        flex-shrink: 0;
        overflow: hidden;
        background-color: #faf8f0;
        border: 1px solid rgba(0, 0, 0, 0.08);
        padding: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
    }

    .runway-img-wrapper img {
        width: auto;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .runway-caption {
        margin-top: 8px;
        text-align: center;
        font-family: 'Montserrat', sans-serif;
        font-size: 0.98rem;
        letter-spacing: 0.06em;
        color: #212529;
        line-height: 1.3;
        z-index: 999;
        font-weight:bold;
    }

    .runway-marquee {
        display: flex;
        gap: 4rem;
        animation: marquee 25s linear infinite;
        width: max-content;
    }

    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-6 * (max(25vw, 300px) + 4rem))); }
    }

    /* --------------------------------------
    BOTTOM GRID SECTION
    -------------------------------------- */
    .collab-bottom-grid {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        max-width: 1350px;
        margin: 0 auto;
        padding: 0 40px 100px;
        gap: 40px;
        box-sizing: border-box;
    }

    .bottom-grid-heading {
        max-width: 900px;
        margin: 0 auto 12px;
        text-align: center;
        padding: 12px 40px;
        box-sizing: border-box;

    }

    .bottom-grid-title {
        font-family: 'Montserrat', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 0.35em;
        text-transform: uppercase;
        font-weight: 600;
        margin: 0 0 10px;
        color: #212529;
    }

    .bottom-grid-subtitle {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        line-height: 1.8;
        color: #555;
        letter-spacing: 0.02em;
        margin: 0;
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
    TEXT GRID SECTION
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
        text-align: justify;
    }

    /* Video Controls */
    .video-container {
        position: relative;
    }

    .video-control-btn {
        position: absolute;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border: 2px solid #fff;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.2);
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        color: #fff;
        font-size: 20px;
    }

    .video-control-btn:hover {
        background: rgba(0, 0, 0, 0.5);
        
    }

    .video-control-btn i {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Media Item Video Controls */
    .collab-media-item .video-control-btn {
        bottom: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    /* --------------------------------------
    VIDEO/MEDIA SECTION
    -------------------------------------- */
    .collab-video-section {
        width: 100%;
        padding: 0 40px 0px;
        box-sizing: border-box;
    }

    .video-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        background: #000;
    }

    .responsive-media-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .portrait-img { display: none; }
    .landscape-img { display: block; }

    @media screen and (max-aspect-ratio: 1/1) {
        .portrait-img { display: block; }
        .landscape-img { display: none; }
        .video-container { aspect-ratio: 3/4; }
    }

    @media screen and (min-aspect-ratio: 1/1) {
        .portrait-img { display: none; }
        .landscape-img { display: block; }
        .video-container { aspect-ratio: 16/9; }
    }

    .collab-video-footer {
        padding: 40px 40px 100px;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 10;
        background: #faf8f0;
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

    /* --------------------------------------
    PRODUCT SPOTLIGHT SECTION
    -------------------------------------- */
    .spotlight-section {
        background: #faf8f0;
        width: 100%;
        padding: 4rem 0;
    }

    .spotlight {
        max-width: 1350px;
        margin: 0 auto -2px;
        padding: 0 38px;
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
        gap: 2rem;
        align-items: center;
    }

    .spotlight-image {
        width: 100%;
        border-radius: 0;
        overflow: hidden;
        aspect-ratio: 4 / 5; 
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .spotlight-image img {
        width: 100%;
        height: 100%;
        object-fit: contain; 
        display: block;
    }

    .spotlight-meta {
        /* max-width: 420px; */
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
        color:#564d4d;
        padding: 0;
        font-weight:700;
    }

    .spotlight-features li::before {
        content: "✓";
        margin-right: 6px;
        color: #b0b0b0;
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
    
    .divider{
         display:inline-block;
         width:1.5px;
         height:22px;
         background:rgba(91, 18, 18, 0.6);
         margin:0 15px;
        vertical-align:middle;
    }
    /* --------------------------------------
    EDITORIAL COLLAB FEATURE
    -------------------------------------- */
    .editorial-feature {
        background: #faf8f0;
        padding-bottom: 40px;
    }

    .editorial-hero {
        position: relative;
        width: 100%;
        height: 85vh;
        min-height: 450px;
        overflow: hidden;
    }

    .editorial-hero img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 40%;
    }

    .editorial-hero-content {
        position: absolute;
        bottom: 4%;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
        color: #fff;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.6);
        width: 100%;
        padding: 0 20px;
    }

    .editorial-main-text {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        flex-direction: column;
         
    }

    .thank-you-text {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        line-height: 1.1;
        white-space: nowrap;
    }

    .your-time-text {
        font-family: 'Great Vibes', cursive;
        font-size: clamp(2.2rem, 5vw, 3.5rem);
        font-weight: 400;
        opacity: 0.9;
        white-space: nowrap;
    }

    .redefining-prestige-editorial {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(0.6rem, 1vw, 0.8rem);
        letter-spacing: 0.8em;
        text-transform: uppercase;
        margin-top: 10px;
        opacity: 0.8;
    }

    .editorial-subtext {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(0.7rem, 1.2vw, 0.85rem);
        font-weight: 300;
        letter-spacing: 0.05em;
        max-width: 450px;
        margin: 20px auto 0;
        line-height: 1.6;
        opacity: 0.9;
    }

    .editorial-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        max-width: 1350px; 
        margin: 0 auto; 
        padding: 0 8px 8px;
        overflow: hidden;
        box-sizing: border-box;
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

    .living-craft {
        background: #faf8f0;
        padding: 15px 0 24px;
    }

    .living-craft-inner {
        max-width: 900px;
        margin: 0 auto 24px;
        text-align: center;
        padding: 0 40px;
        box-sizing: border-box;
    }

    .living-craft-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: clamp(2rem, 4vw, 3.5rem);
        letter-spacing: 0.09em;
        text-transform: uppercase;
        font-weight: 400;
        color: #212529;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.15em;
        flex-wrap: wrap;
    }

    .inline-logo {
        height: 1.4em;
        width: auto;
        vertical-align: middle;
        display: inline-block;
        
    }

    /* Mobile/Tablet specific logo sizing */
    @media (max-width: 1024px) {
        .inline-logo {
            height: 1.6em;
        }
    }

    @media (max-width: 768px) {
        .inline-logo {
            height: 1.6em;
        }
        .living-craft-title {
            gap: 0.1em;
        }
    }

    .living-craft-sub {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.95rem;
        line-height: 1.9;
        color: #666;
        max-width: 720px;
        margin: 0 auto;
        opacity: 0.9;
        text-align: justify;
    }

    .living-craft-tagline {
        font-family: 'Montserrat', sans-serif;
        font-size: 0.9rem;
        font-style: italic;
        color: #5a5a5a;
        max-width: 680px;
        margin: 4px auto 12px;
    }

    /* .living-craft-badge {
        margin-top: 16px;
        display: flex;
        justify-content: center;
    }

    .living-craft-badge .badge-box {
        background: #000;
        padding: 12px;
        display: inline-block;
        border-radius: 2px;
    }

    .living-craft-badge img {
        width: 110px;
        height: auto;
        display: block;
    } */

    .living-craft-strip {
        max-width: 1350px;
        margin: 28px auto 60px;
        padding: 0 8px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
        box-sizing: border-box;
    }

    .living-craft-img {
        aspect-ratio: 4/3;
        overflow: hidden;
    }

    .living-craft-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    /* --------------------------------------
    SPLIT FORM LAYOUT
    -------------------------------------- */
    .collab-form-wrapper {
        padding: 80px 40px;
        background: #faf8f0;
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
        background: #faf8f0;
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

    /* --------------------------------------
    RESPONSIVE MEDIA QUERIES
    -------------------------------------- */

    /* Tablet ≤ 1024px */
    @media screen and (max-width: 1024px) {
        .hero-side-row { display: none; } 
        .collab-hero { height: 100vh; min-height: unset; padding: 0; }
        .hero-bottom-group { position: absolute; top: 85%; left: 50%; transform: translate(-50%, -50%); width: 90%; bottom: auto; }
        .luxury-brand { font-size: 3rem; }
        .presentation-text { font-size: 3.5rem; }
        .redefining-prestige { font-size: 0.75rem; letter-spacing: 0.4em; }
        .collab-intro { padding: 60px 24px; }
        .collab-intro-subtitle { padding: 0 20px; }
        .collab-media-grid { max-width: 100%; padding: 0 24px; height: auto; flex-wrap: wrap; gap: 25px; }
        .collab-media-item { flex: 0 0 100%; min-height: 500px; }
        .collab-runway { padding: 60px 0; }
        .runway-img-wrapper { width: 40vw; min-width: 220px; }
        .collab-bottom-grid { padding: 0 24px 60px; gap: 24px; max-width: 100%; }
        .bottom-grid-item { min-width: 280px; height: 480px; }
        .collab-text-grid { padding: 0 24px 60px; gap: 40px; }
        .collab-video-section { padding: 0 24px; }
        .collab-video-footer { padding: 24px 16px 15px; }
        .spotlight { grid-template-columns: 1fr; gap: 3rem; margin-bottom: 60px; }
        .spotlight-image { aspect-ratio: 1367 / 2048; }
        .spotlight-meta { max-width: 100%; }
        .editorial-grid { grid-template-columns: repeat(2, 1fr); gap: 6px; padding: 0 24px 24px; max-width: 100%; }
        
        .thank-you-text { font-size: clamp(1.4rem, 3.5vw, 1.8rem); }
        .your-time-text { font-size: clamp(1.8rem, 4.5vw, 2.5rem); }
    }

    /* Split Form Tablet ≤ 992px */
    @media (max-width: 992px) {
        .collab-form-wrapper { padding: 60px 30px; }
        .collab-form-section { grid-template-columns: 1fr; max-width: 600px; }
        .collab-form-image { height: 65vh; }
        .collab-form-panel { padding: 40px 30px; }
        .collab-form-panel .form-row { grid-template-columns: 1fr; }
    }

    /* Mobile ≤ 768px */
    @media (max-width: 768px) {
        .editorial-hero { height: 70vh; }
        .thank-you-text { font-size: clamp(1.2rem, 6vw, 1.5rem); }
        .your-time-text { font-size: clamp(1.6rem, 8vw, 2.2rem); }
        .redefining-prestige-editorial { letter-spacing: 0.6em; }
        .living-craft { padding: 10px 0 16px; }
        .living-craft-strip { grid-template-columns: repeat(2, 1fr); gap: 6px; padding: 0 16px; }
        .living-craft-inner { padding: 0 24px; }
    }

    /* Mobile ≤ 576px */
    @media screen and (max-width: 576px) {
        .collab-hero { height: 100vh; padding: 0; }
        .hero-bottom-group { top: 86%; transform: translate(-50%, -50%); width: 95%; bottom: auto; }
        .luxury-brand { font-size: 2rem; }
        .presentation-text { font-size: 2.8rem; margin: -5px 0 5px; }
        .redefining-prestige { font-size: 0.55rem; letter-spacing: 0.3em; }
        .collab-intro { padding: 48px 16px; }
        .collab-intro-title { font-size: 1rem; letter-spacing: 0.25em; margin-bottom: 30px; }
        .collab-intro-subtitle { padding: 0 8px; font-size: 0.88rem; }
        .collab-intro-body { font-size: 0.82rem; }
        .collab-explore-btn { padding: 14px 36px; font-size: 0.65rem; }
        .collab-media-grid { padding: 0 16px; height: auto; flex-wrap: wrap; gap: 20px; }
        .collab-media-item { min-height: 380px; }
        .collab-runway { padding: 40px 0; }
        .runway-img-wrapper { width: 60vw; min-width: 180px; }
        .collab-bottom-grid { flex-direction: row; flex-wrap: nowrap; padding: 0 16px 48px; gap: 12px; max-width: 100%; }
        .bottom-grid-item { flex: 1; width: auto; min-width: 0; height: 250px; }
        .bottom-grid-item img { object-fit: contain; background: rgba(0,0,0,0.02); }
        .collab-text-grid { flex-direction: column; padding: 0 16px 48px; gap: 32px; }
        .collab-text-item:nth-child(2) { display: none; }
        .collab-video-section { padding: 0 8px; }
        .collab-video-footer { padding: 24px 16px 15px; }
        .collab-video-footer h4 { font-size: 0.9rem; letter-spacing: 0.25em; margin-bottom: 16px; }
        .collab-video-footer p { font-size: 0.78rem; }
        .spotlight-section { padding: 2rem 0; }
        .spotlight { padding: 0 16px; gap: 2rem; margin-bottom: 40px; }
        .spotlight-image { aspect-ratio: 1367 / 2048; }
        .spotlight-name { font-size: 22px; }
        .spotlight-desc, .spotlight-features { font-size: 12px; }
        .editorial-grid { grid-template-columns: repeat(2, 1fr); gap: 4px; padding: 0 16px 16px; }
        .living-craft-strip { grid-template-columns: 1fr; }
        .collab-form-wrapper { padding: 40px 16px; }
        .collab-form-section { grid-template-columns: 1fr; }
        .collab-form-image { height: 65vh; }
        .collab-form-image-text h3 { font-size: 1.8rem; }
        .collab-form-image-text { left: 1.5rem; bottom: 1.5rem; }
        .collab-form-panel { padding: 32px 20px; }
        .collab-form-panel h3 { font-size: 1.6rem; margin-bottom: 24px; }
        .collab-form-panel .btn-submit { width: 100%; text-align: center; padding: 14px 20px; }
    }

    /* Extra Small ≤ 420px */
    @media (max-width: 420px) {
        .editorial-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="collab-page-wrapper">
    <!-- Hero Banner -->
    <section class="collab-hero">
        <div class="collab-hero-bg"></div>
        
        <div class="collab-hero-content">
            <div class="hero-side-row">
                <h2 class="hero-side-text">HIS</h2>
                <h2 class="hero-side-text">HERS</h2>
            </div>
            <div class="hero-bottom-group">
                <h1 class="luxury-brand">BY INANNA</h1>
                <!-- <p class="presentation-text">INANNA</p> -->
                <p class="redefining-prestige">Wedding Guest Collection</p>
            </div>
        </div>

        <div class="collab-hero-arrow">
            <i class="bi bi-chevron-down"></i>
        </div>
    </section>

    <!-- Introduction Text -->
    <section class="collab-intro">
        <h2 class="collab-intro-title">HIS & HERS</h2>

        <p class="collab-intro-subtitle">
            Celebrate togetherness with our exclusive His & Hers collection, thoughtfully designed for couples who appreciate elegance, harmony, and timeless fashion. At INANNA, we believe that style is not just individual  it is a shared expression of love, celebration, and unforgettable moments.

            From wedding festivities to festive gatherings and grand occasions, our coordinated ensembles are crafted to create a visually stunning and emotionally memorable presence. Each outfit reflects intricate craftsmanship, premium fabrics, and a refined aesthetic that embodies luxury and sophistication.


        </p>

        <p class="collab-intro-body">
           The His & Hers collection is a seamless blend of tradition and contemporary couture, designed to complement both personalities while maintaining individuality. Every piece is meticulously curated to ensure color harmony, exquisite detailing, and flawless tailoring that enhances the grace of both him and her.

           Whether it is a wedding celebration, engagement, festive occasion, or a special photoshoot, INANNA’s coordinated outfits redefine couple styling with unmatched elegance and prestige. Step into a world where fashion becomes a shared statement of luxury.

        </p>

        <a href="#Why-Inanna" class="collab-explore-btn">EXPLORE</a>
    </section>

    <!-- Media Grid -->
    <section class="collab-media-grid">
        <!-- Left Image () -->
        <div class="collab-media-item">
            <img src="assets/images/his-her/P-hh.webp"
                alt="Collab-Media" class="collab-media-img">
        </div>

        <!-- Right Image (Fallback for Video) -->
        <div class="collab-media-item">
            <video class="collab-media-img" autoplay muted loop playsinline>
                <source src="assets/video/in-hers.mp4" type="video/mp4">
                <img src="assets/images/his-her/P-hh.webp" alt="Collab-Media-Right" class="collab-media-img">
            </video>
            <div class="video-control-btn" onclick="toggleVideo(this)">
                <i class="bi bi-pause-fill"></i>
            </div>
        </div>
    </section>

    <!-- Runway Section (Marquee) -->
    <section class="collab-runway">
        <div class="runway-marquee">
            <?php
            $marquee_images = [11, 7, 18, 6, 12, 17];
            $marquee_captions = [
                'Sundown Sider Lehenga',
                'Ivory Fauna',
                'Modern Regal Statement',
                'Ivory Flora',
                'Rosé Festive Kurta Set',
                'Mandala Muse Cotton Linen Set'
            ];
            foreach ($marquee_images as $idx => $i): 
                $caption = $marquee_captions[$idx] ?? '';
            ?>
                <div class="runway-card">
                    <div class="runway-img-wrapper">
                        <img src="assets/images/his-her/image-<?php echo $i; ?>.webp" alt="Runway <?php echo $i; ?>">
                    </div>
                    <?php if (!empty($caption)): ?>
                        <div class="runway-caption"><?php echo $caption; ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Duplicate for infinite loop -->
            <?php foreach ($marquee_images as $idx => $i): 
                $caption = $marquee_captions[$idx] ?? '';
            ?>
                <div class="runway-card">
                    <div class="runway-img-wrapper">
                        <img src="assets/images/his-her/image-<?php echo $i; ?>.webp" alt="" aria-hidden="true">
                    </div>
                    <?php if (!empty($caption)): ?>
                        <div class="runway-caption" aria-hidden="true"><?php echo $caption; ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Bottom Grid Section -->
    <div class="bottom-grid-heading">
        <h3 class="bottom-grid-title">Wedding Wardrobe For His & Hers</h3>
        <p class="bottom-grid-subtitle">Curated Looks for Wedding Guest Couples, Not for Bride & Groom</p>
    </div>
    <section class="collab-bottom-grid">
        <div class="bottom-grid-item">
            <img src="assets/images/his-her/r-12.webp"
                alt="WOI Image 1">
        </div>
        <div class="bottom-grid-item">
            <img src="assets/images/his-her/r-3.webp"
                alt="WOI Image 2">
        </div>
    </section>

    <!-- Additional Collab Text -->
    <section class="collab-text-grid">
        <div class="collab-text-item">
            <h4>HER COLLECTIONS</h4>
            <p>Discover a curated selection of luxurious ensembles designed to celebrate the elegance and individuality of the modern woman. From graceful festive wear to statement couture pieces, each design reflects exquisite craftsmanship, rich fabrics, and intricate detailing. The Her Collection by INANNA embodies timeless beauty, blending tradition with contemporary style for unforgettable celebrations.</p>
        </div>
        <div class="collab-text-item">
            <h4>HIS COLLECTIONS</h4>
            <p>Experience refined ethnic wear crafted for the modern gentleman who values sophistication and tradition. The His Collection by INANNA features meticulously tailored silhouettes, premium fabrics, and subtle yet striking detailing. Designed for weddings, festive occasions, and grand celebrations, each ensemble reflects confidence, elegance, and timeless style.
            </p>
        </div>
    </section>

    <!-- Home Media Section -->
    <section class="collab-video-section">
        <div class="video-container">
            <video class="responsive-media-img" autoplay muted loop playsinline>
                <source src="assets/video/his-her-Collab.mp4" type="video/mp4">
                <img src="assets/images/his-her/P-hh.webp" alt="Portrait Home" class="responsive-media-img portrait-img">
                <img src="assets/images/his-her/l-hh.webp" alt="Landscape Home" class="responsive-media-img landscape-img">
            </video>
            <div class="video-control-btn" onclick="toggleVideo(this)">
                <i class="bi bi-pause-fill"></i>
            </div>
        </div>
    </section>

    <!-- Runway Section (Marquee) -->
    <section class="collab-runway collab-runway-secondary">
        <div class="runway-marquee">
            <div class="runway-img-wrapper"><img src="assets/images/his-her/image-3.webp" alt="Runway 8"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/run-13.webp" alt="Runway 2"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/r22.webp" alt="Runway 4"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/image-4.webp" alt="Runway 6"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/r24.webp" alt="Runway 5"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/run-10.webp" alt="Runway 5"></div>

            <!-- Duplicate for infinite loop -->
            <div class="runway-img-wrapper"><img src="assets/images/his-her/image-3.webp"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/run-13.webp"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/r22.webp"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/image-4.webp"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/r24.webp"></div>
            <div class="runway-img-wrapper"><img src="assets/images/his-her/run-10.webp"></div>
        </div>
    </section>

    <div class="collab-video-footer">
        <h4>HIS  & HERS COLLECTION</h4>
        <p>
            The His & Hers collections by INANNA celebrate the beauty of coordinated elegance, where individuality meets harmonious style. Thoughtfully designed for modern couples, each ensemble reflects refined craftsmanship, luxurious fabrics, and timeless aesthetics. Whether for weddings, festive occasions, or grand celebrations, these collections bring together tradition and contemporary couture to create moments that are as memorable as they are stylish.
        </p>
    </div>

    

    <!-- Product Spotlight -->

    <section class="spotlight-section" id="Why-Inanna">
        <div class="spotlight">
            <!-- LEFT: IMAGE -->
            <div class="spotlight-image">
                <img src="assets/images/his-her/image-20.webp"
                    alt="Featured Product">
            </div>

            <!-- RIGHT: CONTENT -->
            <div class="spotlight-meta">
                <!-- <div class="section-label">Product spotlight</div> -->
                <h2 class="spotlight-name">Why Choose HIS & HERS by INANNA?</h2>
                <div class="spotlight-price">Packages start from ₹99,999 <strong><i>(Six Sets)</i></strong> | Includes His & Hers outfits for <strong><i> Haldi, Sangeet & Wedding.</i></strong></div>
                <ul class="spotlight-features">
                    <li>Complete wedding guest wardrobe planned for you excluding Bride & Groom</li>
                    <li>Coordinated His & Hers looks for 3–4 functions</li>
                    <li>Designed to twin tastefully, not match loudly</li>
                    <li>Optional family / child styling available</li>
                    <li>Perfect for destination weddings & multi‑function celebrations</li>
                    <li>Personal consultation with INANA designers</li>
                    <li>Limited seasonal bookings to ensure exclusivity</li>
                </ul>
                <!-- <div class="spotlight-size-label">Select size</div>
                <div class="spotlight-sizes" id="sizeSelector">
                    <div class="size-pill">XS</div>
                    <div class="size-pill selected">S</div>
                    <div class="size-pill">M</div>
                    <div class="size-pill">L</div>
                </div> -->
                <div class="spotlight-actions">
                     <a class="btn-spotlight" href="appointment.php">BOOK CONSULTATION</a>
                     <!-- <span class="divider"></span>
                    <a class="btn-spotlight" href="shop.php">SHOP NOW</a> -->
                   
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Editorial Feature Section -->
<section class="editorial-feature">
    <!-- Hero Image with Centered Content -->
    <div class="editorial-hero">
        <img src="assets/images/his-her/hh-L.webp" alt="Thank You from Inanna">
        <div class="editorial-hero-content">
            <div class="editorial-main-text">
                <p class="thank-you-text">Attend the wedding.</p>
                <p class="your-time-text">We’ll plan the wardrobe.</p>
            </div>
            <!-- <div class="redefining-prestige-editorial">REDEFINING PRESTIGE</div> -->
            <!-- <p class="editorial-subtext">
                very great comfort, especially among beautiful and expensive surroundings. By all accounts he leads a life of considerable luxury
            </p> -->
        </div>
    </div>
</section>

<section class="living-craft">
    <div class="living-craft-inner">
        <div class="living-craft-title">WORLD OF<img src="https://worldofinanna.org/assets/images/logo-inanna.avif" alt="Inanna" class="inline-logo"></div>
        <div class="living-craft-tagline"><em>"QUDRAT-E-ILAHI"</em></div>
        <p class="living-craft-sub">
            Rooted in the belief that beauty exists in everything even in what may traditionally be seen as flawed I am inspired to <strong>embrace and celebrate the unconventional</strong>. This philosophy infuses my designs with a unique aesthetic that challenges the norms of fashion.
            Guided by Alexander McQueen’s principle of mastering the rules before breaking them, my creations are not only innovative but deeply respectful of craftsmanship and tradition.
            Introducing <strong>INANNA</strong>, a brand born from passion and inspired by the Sumerian goddess of love and power. Built on the pillars of <strong>boundless creativity, ethics, and slow fashion</strong>, the world of INANNA seeks to inspire a broader appreciation of beauty, encouraging us all to look beyond societal norms and embrace diversity in all its forms.
        </p>
    </div>
    <!-- <div class="living-craft-badge">
        <div class="badge-box">
            <img src="assets/images/footer-logo.png" alt="Inanna crest">
        </div>
    </div> -->
</section>

<!-- 4-column Image Grid -->
<div class="editorial-grid">
    <div class="editorial-grid-item">
        <img src="assets/images/his-her/image-21.WEBP"
            alt="Collab 1">
    </div>
    <div class="editorial-grid-item">
        <img src="assets/images/his-her/image-22.WEBP"
            alt="Collab 2">
    </div>
    <div class="editorial-grid-item">
        <img src="assets/images/his-her/image-23.WEBP"
            alt="Collab 3">
    </div>
    <div class="editorial-grid-item">
        <img src="assets/images/his-her/image-24.webp"
            alt="Collab 4">
    </div>
</div>

<!-- Consultation Form — Split Layout -->
<div class="collab-form-wrapper">
    <section class="collab-form-section">
        <!-- LEFT: Image Panel -->
        <div class="collab-form-image">
            <img src="assets/images/his-her/book.webp"
                alt="Consultation">
            <div class="collab-form-image-overlay"></div>
            <div class="collab-form-image-text">
                <h3>Begin Your<br>Story With Us</h3>
                <p>Curated Styling for Two</p>
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="collab-form-panel" id="booking-appointment">
            <div class="form-eyebrow">Private Consultation</div>
            <h3>Book Your Appointment</h3>

            <form action="send-lead.php" method="POST" class="lead-form">
                <div>
                    <label>NAME</label>
                    <input type="text" name="name" required>
                </div>

                <div>
                    <label>MOBILE NUMBER</label>
                    <input type="tel" name="mobile" required>
                </div>

                <div>
                    <label>EMAIL ID</label>
                    <input type="email" name="email" required>
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
        // --- Video Toggle Play/Pause ---
        window.toggleVideo = function(btn) {
            const video = btn.parentElement.querySelector('video');
            const icon = btn.querySelector('i');
            
            if (video.paused) {
                video.play();
                icon.classList.remove('bi-play-fill');
                icon.classList.add('bi-pause-fill');
            } else {
                video.pause();
                icon.classList.remove('bi-pause-fill');
                icon.classList.add('bi-play-fill');
            }
        };

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

        // --- Marquee Vertical Scale Emphasis ---
        const marqueeItems = document.querySelectorAll('.runway-img-wrapper');
        if (marqueeItems.length > 0) {
            function updateMarqueeScaling() {
                const viewportCenter = window.innerWidth / 2;
                marqueeItems.forEach(item => {
                    const rect = item.getBoundingClientRect();
                    // Only calculate if item is in or near viewport for performance
                    if (rect.right < 0 || rect.left > window.innerWidth) return;

                    const itemCenter = rect.left + rect.width / 2;
                    const distance = Math.abs(viewportCenter - itemCenter);
                    const threshold = window.innerWidth * 0.45;
                    let scaleValue = 1;

                    if (distance < threshold) {
                        const normalizedDist = 1 - (distance / threshold);
                        // Subtle scaling factor (1.15 max instead of 1.35)
                        scaleValue = 1 + (Math.pow(normalizedDist, 2) * 0.15);
                    }
                    
                    item.style.transform = `scaleY(${scaleValue})`;
                    
                    // Apply inverse scale to the image so it doesn't stretch or zoom
                    // Only the box (container) grew in height.
                    const img = item.querySelector('img');
                    if (img) {
                        const inverseScale = 1 / scaleValue;
                        img.style.transform = `scaleY(${inverseScale})`;
                    }
                });
                requestAnimationFrame(updateMarqueeScaling);
            }
            updateMarqueeScaling();
        }
    });
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
