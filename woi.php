<?php
// PHP Setup
$page_title = 'WORLD OF INANNA | INANNA';
$meta_description = 'Editorial stories, atelier notes, and the movement behind the collections.';
include __DIR__ . '/includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CLOSET CHRONICLES | INANNA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $meta_description; ?>">

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* 1. CSS Variables for Consistency */
        :root {
            --color-bg-dark: #790000;
            --color-text-accent: #ce4a4a;
            --font-primary: 'Playfair Display', serif;
            --font-secondary: 'Cormorant Garamond', serif;
            --padding-side: 5%;
            
            /* Define key dimensions for precise layout cloning (Desktop) */
            --img-1-width: 320px;
            --img-2-width: 380px;
            --img-3-width: 280px;
            --desktop-gap: 30px; 
            --content-area-total-width: 800px; 
        }

        /* NEW: Set background color for the entire body to match the main content */
        body {
            background-color: var(--color-bg-dark); 
            margin: 0;
            padding: 0;
            overflow-x: hidden; 
        }

        /* Base & Reset */
        .main-container {
            margin-top: 10rem;
            background-color: var(--color-bg-dark);
            color: white;
            font-family: var(--font-secondary);
            min-height: 100vh;
            box-sizing: border-box;
            padding: 30px 0;
            margin-left: auto;
            margin-right: auto;
            max-width: 1200px; 
        }
        /* UPDATED: Added .gallery-image here to ensure object-fit: cover is applied */
        .main-image, .footer-image, .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Ensures images fill their container without stretching */
            display: block;
        }

        /* --- 2. HEADER STYLING (Desktop/Tablet default) --- */
        .section-header {
            display: flex;
            align-items: center;
            margin: 0 auto 20px auto;
            width: var(--content-area-total-width);
            padding-left: 0;
        }
        
        .section-header:not(:first-child) {
            margin-top: 100px;
        }

        .header-line {
            width: 72%;
            max-width: 450px;
            height: 1px;
            background-color: white;
            margin-right: 30px;
            position: relative;
            top: -46px; 
        }
        
        .main-container > .section-header:nth-of-type(2) .header-line {
            top: -20px;
        }

        .main-container > .section-header:nth-of-type(3) .header-line {
            top: -10px;
        }

        .header-title {
            font-family: var(--font-primary);
            font-size: 4.5em; 
            font-weight: 700;
            letter-spacing: 2px;
            margin: 0;
            line-height: 1;
        }


        /* --- 3. MAIN CONTENT (Desktop/Tablet default: Side-by-side) --- */
        .content-section {
            display: flex;
            position: relative;
            margin: 30px auto 0 auto;
            width: var(--content-area-total-width);
            padding: 0;
            align-items: flex-start;
        }

        .left-column {
            width: 40%; 
            display: flex;
            flex-direction: column;
            z-index: 10; 
            margin-right: var(--desktop-gap); 
        }

        .right-column {
            width: 60%; 
            position: relative;
            height: 650px; 
        }

        /* Specific Image Dimensions and Positioning for Desktop/Tablet */
        .image-1-container {
            width: var(--img-1-width); 
            height: 380px; 
            margin-bottom: 20px;
            margin-left: auto; 
            margin-right: auto;
        }

        .image-2-container {
            position: absolute;
            top: 0;
            left: 0; 
            width: var(--img-2-width); 
            height: 500px;
            z-index: 5;
        }

        .image-3-container {
            position: absolute;
            top: 150px;
            width: var(--img-3-width);
            height: 470px;
            z-index: 15;
            left: 25rem;
        }

        /* --- 4. TEXT STYLING (Default/Desktop/Tablet: Right-Aligned) --- */
        .tailored-duo-text {
            width: var(--img-1-width); 
            margin: 50px auto 0 auto; 
            text-align: right; 
        }

        .main-title {
            color: var(--color-text-accent);
            font-family: var(--font-primary);
            font-size: 3.5em; 
            line-height: 0.85;
            letter-spacing: 3px;
            margin-bottom: 15px;
            margin-top: 0;
            
            /* Stroke effect applied to main title */
            -webkit-text-fill-color: transparent;
            -webkit-text-stroke-color: currentColor;
            -webkit-text-stroke-width: var(--heading-stroke-size, 2px);
            font-family: "Playfair Display SC", Sans-serif;
            color: #FF0000;
        }

        .subtitle {
            font-family: var(--font-secondary);
            font-weight: 400;
            font-size: 1.1em;
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .description {
            font-family: var(--font-secondary);
            font-size: 1em;
            line-height: 1.5;
            margin: 0 auto 25px auto;
            max-width: 280px;
            text-align: right; /* Default for Desktop & Tablet */
        }
        
        .main-title.two-line {
            font-size: 3.5em; 
            line-height: 0.95;
        }

        .shop-now-link {
            text-decoration: none; 
            color: white;
            font-weight: 500;
            padding-bottom: 2px;
            display: inline-block;
            font-size: 0.9em;
            letter-spacing: 1px;
            position: relative; 
            transition: color 0.3s ease; 
        }

        /* Styling for the animated underline */
        .shop-now-link::after {
            content: '';
            position: absolute;
            width: 0; 
            height: 1px;
            bottom: 0;
            /* For a right-aligned link, ensure the underline starts on the right (Desktop/Tablet) */
            right: 0;
            left: auto; 
            background-color: white; 
            transition: width 0.3s ease-out; 
        }

        /* Hover effect */
        .shop-now-link:hover {
            color: var(--color-text-accent); /* Link color changes to ACCENT RED (#ce4a4a) */
        }

        .shop-now-link:hover::after {
            width: 100%; 
        }
        
        /* --- 5. BOTTOM IMAGES STYLING (4-Column Grid, all devices) --- */
        .footer-images {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 50px auto 0 auto; 
            width: var(--content-area-total-width); 
            padding: 0;
            flex-wrap: nowrap;
        }

        .footer-images.section-3-footer {
            margin-bottom: 50px;
        }

        .footer-images .image-box {
            /* Keep 4-column structure */
            flex: 1 1 23%; 
            height: 300px; 
            min-width: 0; 
        }

        /* Code for Peeka Boo Section */

        /* --- 6. NEW: GRID GALLERY STYLING (Peek a Boo) --- */
        .image-gallery-section {
            margin: 100px auto 0 auto;
            width: var(--content-area-total-width);
            padding: 0;
            text-align: center; /* Center the title */
        }

        .gallery-title {
            font-family: var(--font-primary);
            font-size: 4em; 
            font-weight: 700;
            letter-spacing: 2px;
            color: white;
            margin-bottom: 40px;
            line-height: 1;
        }

        .image-gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 columns for 8 images */
            gap: 15px;
            margin: 0;
        }

        .image-gallery-grid .gallery-box {
            height: 250px; /* Consistent height for grid items */
            overflow: hidden;
        }

        /* --- 7. NEW: WORLD OF INANNA TEXT SECTION --- */
        .world-of-inanna-section {
            margin: 80px auto 100px auto;
            /* width: var(--content-area-total-width); */
            padding: 0;
            text-align: center;
        }

        .world-of-inanna-heading {
            font-family: var(--font-primary);
            font-size: 4em; 
            font-weight: 700;
            color: white;
            line-height: 0.9;
            margin: 0;
        }
        
        .qodart-subheading {
            font-family: var(--font-secondary);
            font-size: 1.5em;
            font-weight: 500;
            color: var(--color-text-accent);
            margin: 10px 0 30px 0;
        }

        .inanna-description {
            font-family: var(--font-secondary);
            font-size: 1.1em;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto 15px auto;
            text-align: center;
            color:#fff;
        }
        
        /* =================================================== */
        /* === RESPONSIVENESS (Media Queries) ================ */
        /* =================================================== */

        /* Tablet View (1024px and below) */
        @media (max-width: 1024px) {
            :root {
                --content-area-total-width: 700px;
            }
            .main-container {
                margin-top: 8rem;
            }
            .section-header {
                width: 700px; 
            }
            .header-line {
                max-width: 150px;
            }
            .header-title {
                font-size: 3.5em;
            }
            
            .image-1-container, .tailored-duo-text {
                width: 280px;
            }
            .image-1-container {
                height: 350px;
            }
            .right-column {
                height: 550px; 
            }
            .image-2-container {
                width: 320px;
                height: 400px;
            }
            .image-3-container {
                width: 240px;
                height: 300px;
                left: 18rem; 
            }
            .main-title {
                font-size: 3em;
            }
            .footer-images {
                width: 700px; 
                gap: 10px;
            }
            .footer-images .image-box {
                height: 250px;
            }
            .section-header:not(:first-child) {
                margin-top: 80px;
            }

            /* Gallery/INANNA Updates for 1024px */
            .image-gallery-section,
            .world-of-inanna-section {
                width: 700px;
            }
            .gallery-title,
            .world-of-inanna-heading {
                font-size: 3em;
            }
            .qodart-subheading {
                font-size: 1.3em;
            }
            .image-gallery-grid .gallery-box {
                height: 200px;
            }
        }

        @media (max-width:768px){
            .image-3-container{
                width: 244px;
                height: 289px;
                left: 0;
                top: 26rem;
            }
            
            /* Gallery/INANNA Updates for 768px */
            .image-gallery-section,
            .world-of-inanna-section {
                width: 90%; 
                margin: 50px auto 0 auto;
            }

            .gallery-title {
                font-size: 2.2em;
                margin-bottom: 20px;
            }

            .image-gallery-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 columns on mobile */
                gap: 10px;
            }

            .image-gallery-grid .gallery-box {
                height: 180px;
            }
            
            .world-of-inanna-heading {
                font-size: 2.5em;
            }
            .qodart-subheading {
                font-size: 1.1em;
                margin-bottom: 20px;
            }
            .inanna-description {
                font-size: 1em;
            }
        }


        /* Mobile View (460px and below) - Structure breaks to single column, text aligns left */
        @media (max-width: 460px) {
            .main-container {
                padding: 20px 0;
                margin-top: 5rem;
            }
            .section-header {
                width: 90%; 
                padding: 0 5%;
            }
            .header-line {
                display: none; /* Hide header line on mobile */
            }
            .header-title {
                font-size: 2.5em;
                text-align: center;
                width: 100%;
                padding: 0; 
            }
            .section-header:not(:first-child) {
                margin-top: 50px;
            }
            
            /* Stack the main content vertically */
            .content-section {
                flex-direction: column;
                align-items: center;
                width: 100%; 
                margin: 10px auto;
            }

            .left-column, .right-column {
                width: 100%;
                margin: 0;
                padding: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                position: static; /* Remove absolute positioning */
                height: auto;
            }
            
            /* Standardize the images to nearly full width, centered */
            .image-1-container, 
            .image-2-container, .image-3-container {
                position: static;
                width: 90%; 
                max-width: 400px;
                height: 400px;
                margin: 15px 0;
            }
            
            /* 📣 Text content forced Left-Aligned on mobile */
            .tailored-duo-text {
                width: 90%; 
                max-width: 400px;
                text-align: left; /* Left align text for mobile */
                margin: 25px auto;
            }
            .description {
                text-align: left; /* Left align description for mobile */
                margin: 0 0 25px 0; 
                max-width: 100%; 
            }
            .shop-now-link {
                /* Ensures link aligns with the now left-aligned text */
                display: block; 
            }


            /* 📣 Keep 4-column flex for footer images on mobile */
            .footer-images {
                width: 90%; 
                margin: 30px auto;
                gap: 5px; 
            }
            .footer-images .image-box {
                flex: 1 1 23%; 
                height: 150px; 
                margin-bottom: 0;
            }
            
            /* IMPORTANT: Fix underline animation for left-aligned text on mobile */
            .shop-now-link::after {
                left: 0;
                right: auto; 
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        
        <header class="section-header">
            <div class="header-line"></div>
            <h1 class="header-title">CLOSET CHRONICLES</h1>
        </header>

        <div class="content-section">
            <div class="left-column">
                <div class="image-box image-1-container">
                    <img src="assets/images/noplus/img1.webp" alt="Model in green satin outfit" class="main-image image-1">
                </div>

                <div class="tailored-duo-text">
                    <h2 class="main-title">TAILORED DUO</h2>
                    <p class="subtitle">Step into the Unexpected</p>
                    <p class="description">Fashion meets art in our curated world of experimental silhouettes and expressive design. Designed with Intention—made to move with you.</p>
                    <a href="#" class="shop-now-link">SHOP NOW</a>
                </div>
            </div>

            <div class="right-column">
                <div class="image-box image-2-container">
                    <img src="assets/images/noplus/img2.webp" alt="Model in black leather top and trousers" class="main-image image-2">
                </div>
                <div class="image-box image-3-container">
                    <img src="assets/images/noplus/img4.webp" alt="Model showing back details of green dress" class="main-image image-3">
                </div>
            </div>
        </div>

        <div class="footer-images">
            <div class="image-box image-4-container">
                <img src="assets/images/tillsunrise/img7.webp" alt="Model in blue and white outfit" class="footer-image">
            </div>
            <div class="image-box image-5-container">
                <img src="assets/images/noplus/img6.webp" alt="Model in orange long jacket" class="footer-image">
            </div>
            <div class="image-box image-6-container">
                <img src="assets/images/theguestlist/img-3.webp" alt="Model in white embellished corset dress" class="footer-image">
            </div>
            <div class="image-box image-7-container">
                <img src="assets/images/theguestlist/img-4.webp" alt="Model in blue top and skirt" class="footer-image">
            </div>
        </div>
        
        
        <header class="section-header">
            <div class="header-line"></div>
            <h1 class="header-title">POISE PAIR</h1>
        </header>

        <div class="content-section">
            <div class="left-column">
                <div class="image-box image-1-container">
                    <img src="assets/images/noplus/img2.webp" alt="Section 2 Image 1" class="main-image image-1">
                </div>

                <div class="tailored-duo-text">
                    <h2 class="main-title">POISE PAIR</h2>
                    <p class="subtitle">Embrace the Chill</p>
                    <p class="description">Discover the clothes that speaks you and embodies timelessness death and soul-garments that tells your story as you wear them.</p>
                    <a href="#" class="shop-now-link">VIEW COLLECTION</a>
                </div>
            </div>

            <div class="right-column">
                <div class="image-box image-2-container">
                    <img src="assets/images/noplus/img8.webp" alt="Section 2 Image 2" class="main-image image-2">
                </div>
                <div class="image-box image-3-container">
                    <img src="assets/images/noplus/img4.webp" alt="Section 2 Image 3" class="main-image image-3">
                </div>
            </div>
        </div>

        <div class="footer-images">
            <div class="image-box image-4-container">
                <img src="assets/images/tillsunrise/img6.webp" alt="Section 2 Footer Image 1" class="footer-image">
            </div>
            <div class="image-box image-5-container">
                <img src="assets/images/noplus/img7.webp" alt="Section 2 Footer Image 2" class="footer-image">
            </div>
            <div class="image-box image-6-container">
                <img src="assets/images/theguestlist/img-5.webp" alt="Section 2 Footer Image 3" class="footer-image">
            </div>
            <div class="image-box image-7-container">
                <img src="assets/images/theguestlist/img-6.webp" alt="Section 2 Footer Image 4" class="footer-image">
            </div>
        </div>

        <header class="section-header">
            <div class="header-line"></div>
            <h1 class="header-title">THE LINE UP</h1>
        </header>

        <div class="content-section">
            <div class="left-column">
                <div class="image-box image-1-container">
                    <img src="assets/images/theguestlist/img-1.webp" alt="Section 3 Image 1" class="main-image image-1">
                </div>

                <div class="tailored-duo-text">
                    <h2 class="main-title two-line">Dress &<br>Sarees</h2>
                    <p class="subtitle">Redefine Casual</p>
                    <p class="description">Discover looks that take casual to a whole new level. From vivid patterns to bold cuts, redefine what casual means to you. Let your outfit speak before you do.</p>
                    <a href="#" class="shop-now-link">EXPLORE STYLES</a>
                </div>
            </div>

            <div class="right-column">
                <div class="image-box image-2-container">
                    <img src="assets/images/theguestlist/img-2.webp" alt="Section 3 Image 2" class="main-image image-2">
                </div>

                <div class="image-box image-3-container">
                    <img src="assets/images/theguestlist/img-7.webp" alt="Section 3 Image 3" class="main-image image-3">
                </div>
            </div>
        </div>

        <div class="footer-images section-3-footer">
            <div class="image-box image-4-container">
                <img src="assets/images/tillsunrise/img6.webp" alt="Section 3 Footer Image 1" class="footer-image">
            </div>
            <div class="image-box image-5-container">
                <img src="assets/images/tillsunrise/img8.avif" alt="Section 3 Footer Image 2" class="footer-image">
            </div>
            <div class="image-box image-6-container">
                <img src="assets/images/tillsunrise/img3.webp" alt="Section 3 Footer Image 3" class="footer-image">
            </div>
            <div class="image-box image-7-container">
                <img src="assets/images/tillsunrise/img4.webp" alt="Section 3 Footer Image 4" class="footer-image">
            </div>
        </div>
    </div>
    <hr>

    <section class="image-gallery-section">
        <h2 class="gallery-title">PEEK A BOO</h2>
        <div class="image-gallery-grid">
            <div class="gallery-box">
                <img src="assets/images/isle.webp" alt="Gallery Image 1" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/noplus/img4.webp" alt="Gallery Image 2" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/theguestlist/img-1.webp" alt="Gallery Image 3" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/noplus/img5.webp" alt="Gallery Image 4" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/tillsunrise/img1.avif" alt="Gallery Image 5" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/noplus/img2.webp" alt="Gallery Image 6" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/theguestlist/img-6.webp" alt="Gallery Image 7" class="gallery-image">
            </div>
            <div class="gallery-box">
                <img src="assets/images/tillsunrise/img1.avif" alt="Gallery Image 8" class="gallery-image">
            </div>
        </div>
    </section>

    <hr>
    
    <section class="world-of-inanna-section">
        <h2 class="world-of-inanna-heading">WORLD OF INANNA</h2>
        <p class="qodart-subheading">"QODART E-ILAHI"</p>
        
        <p class="inanna-description">Rooted in the belief that beauty exists in everything—even in what may traditionally be seen as flawed—I am inspired to *embrace and celebrate the unconventional*. This philosophy infuses my designs with a unique aesthetic that challenges the norms of fashion.</p>
        
        <p class="inanna-description">Guided by Alexander McQueen’s principle of mastering the rules before breaking them, my creations are not only innovative but deeply respectful of craftsmanship and tradition.</p>
        
        <p class="inanna-description">Introducing *INANNA, a brand born from passion and inspired by the Sumerian goddess of love and power. Built on the pillars of **boundless creativity, ethics, and slow fashion*, the world of INANNA seeks to inspire a broader appreciation of beauty, encouraging us all to look beyond societal norms and embrace diversity in all its forms.</p>
    </section>
    

<?php 
// Include the footer file.
include __DIR__ . '/includes/footer.php';
?>
</body>
</html>