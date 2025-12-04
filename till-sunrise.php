<?php
$page_title = 'TILL SUNRISE | INANNA';
$meta_description = 'From moonlit rooftops to morning tides — sheer crochet, resort-ready cuts, and effortless sensuality that lingers till sunrise.';
include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400;1,600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script> 

<style>
/* ------------------------------
    BASE




/* ------------------------------
    GRID LAYOUT
------------------------------ */
.manifesto-container {
  max-width: 1200px;
  margin: auto;
  display: grid;
  grid-template-columns: 1fr;
  gap: 60px;
}

@media (min-width: 768px) {
  .manifesto-container {
    grid-template-columns: 7fr 5fr;
    gap: 16px;
  }
}

/* ------------------------------
    TEXT SECTION
------------------------------ */
.line {
  width: 60px;
  height: 1px;
  background: #000;
  margin-bottom: 10px;
}

.label {
  text-transform: uppercase;
  letter-spacing: 0.25em;
  font-size: 12px;
  color: #555;
  margin-bottom: 20px;
}



.text-block p {
  color: #555;
  line-height: 1.7;
  font-size: 18px;
  margin-bottom: 20px;
  max-width: 500px;
}

/* ------------------------------
    DESKTOP COLLAGE
------------------------------ */
.collage-desktop {
  position: relative;
  width: 100%;
  height: 600px;
}

@media (max-width: 767px) {
  .collage-desktop {
    display: none;
  }
}

.img-large {
  position: absolute;
  top: 0;
  right: 0;
  width: 85%;
  height: 85%;
  z-index: 2;
}

.img-small {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 55%;
  height: 55%;
  border: 8px solid #fff;
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
  z-index: 3;
}

.img-large img,
.img-small img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: grayscale(1);
  transition: 0.6s ease;
}

.img-large img:hover,
.img-small img:hover {
  filter: grayscale(0);
}

.border-outline {
  position: absolute;
  top: 40px;
  right: -20px;
  width: 100%;
  height: 100%;
  border: 1px solid rgba(124, 45, 45, 0.3);
  z-index: 0;
}

/* ------------------------------
    MOBILE COLLAGE (same staggered!)
------------------------------ */
.collage-mobile {
  position: relative;
  width: 100%;
  height: 480px;
  display: block;
}

@media (min-width: 768px) {
  .collage-mobile {
    display: none;
  }
}

.mobile-large {
  position: absolute;
  top: 0;
  left: 0;
  width: 85%;
  height: 75%;
  z-index: 2;
}

.mobile-small {
  position: absolute;
  bottom: 0;
  right:0;
  width: 60%;
  height: 48%;
  border: 8px solid #fff;
  box-shadow: 0 5px 20px rgba(0,0,0,0.15);
  z-index: 3;
}

.mobile-outline {
  position: absolute;
  top: 40px;
  left: 20px;
  width: calc(100% - 40px);
  height: 75%;
  border: 1px solid rgba(124, 45, 45, 0.25);
  z-index: 1;
}

.mobile-large img,
.mobile-small img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  filter: grayscale(1);
  transition: 0.6s ease;
}

.mobile-large img:hover,
.mobile-small img:hover {
  filter: grayscale(0);
}
        /* ============================
           RESET & TYPOGRAPHY
           ============================ */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        .serif { font-family: 'Cormorant Garamond', serif; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.25em; }

        /* ============================
           SCROLL REVEAL SECTION
           ============================ */
        .atelier-track {
            height: 300vh;
            position: relative;
            view-timeline-name: --atelier-scroll; 
            view-timeline-axis: block;
        }

        .atelier-sticky {
            position: sticky;
            top: 0;
            height: 100vh;
            width: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ============================
           LAYERS
           ============================ */
        .reveal-bg {
            position: absolute;
            inset: 0;
            background-image: url('assets/images/hero-banner-slide-1.avif');
            background-size: cover;
            background-position: center;
            z-index: 1;
            animation: bg-scale linear forwards;
            animation-timeline: --atelier-scroll;
            animation-range: entry 0% cover 80%;
        }

        .reveal-content {
            position: relative;
            z-index: 10;
            text-align: center;
            mix-blend-mode: difference;
            opacity: 0;
            animation: text-fade linear forwards;
            animation-timeline: --atelier-scroll;
            animation-range: entry 10% cover 60%;
        }

        .reveal-label { font-size: 12px; margin-bottom: 20px; display: block; }
        .reveal-title { font-size: 4rem; margin-bottom: 20px; line-height: 1; }
        .reveal-link { 
            font-size: 11px; text-decoration: none; color: #fff; 
            border-bottom: 1px solid rgba(255,255,255,0.5); padding-bottom: 5px;
        }

        .curtain {
            position: absolute;
            top: 0;
            height: 100%;
            width: 50%;
            z-index: 20;
            background-color: #f4f4f4;
            overflow: hidden;
            border-right: 1px solid rgba(0,0,0,0.1);
        }

        .curtain-inner {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .curtain-inner::after {
            content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.1);
        }

        .curtain.left {
            left: 0;
            background-color: #fff;
            animation: slide-left linear forwards;
            animation-timeline: --atelier-scroll;
            animation-range: entry 0% cover 80%;
        }

        .curtain.right {
            right: 0;
            background-color: #eee;
            animation: slide-right linear forwards;
            animation-timeline: --atelier-scroll;
            animation-range: entry 0% cover 80%;
        }

        .curtain-label { position: absolute; bottom: 3rem; color: #fff; z-index: 5; text-align: left; }
        .curtain.left .curtain-label { right: 3rem; text-align: right; }
        .curtain.right .curtain-label { left: 3rem; }
        .curtain-num { font-size: 2rem; display: block; font-style: italic; }
        .curtain-text { font-size: 0.75rem; font-weight: 500; opacity: 0.8; }

        @keyframes slide-left { to { transform: translateX(-100%); } }
        @keyframes slide-right { to { transform: translateX(100%); } }
        @keyframes bg-scale { from { transform: scale(1.2); } to { transform: scale(1); } }
        @keyframes text-fade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

      /* =========================
     PRODUCT SPOTLIGHT CSS
     ========================== */
      /* =========================
   PRODUCT SPOTLIGHT – CLEAN SINGLE CSS
========================== */

:root {
  --bg: #ffffff;
  --text: #212529;
  --accent-dark: #1a1a1a;
  --pure-white: #ffffff;
  --muted: #b0b0b0;
  --danger: #ff5353;
  --border-subtle: rgba(91, 18, 18, 0.08);
  --transition-fast: 0.25s ease;
  --transition-slow: 0.5s ease;
  --radius-soft: 10px;
}

/* =========================
   SECTION BASE
========================== */
.spotlight-section {
  background: var(--pure-white);
  width: 100%;
  padding: 4rem 0;
}

/* =========================
   LAYOUT WRAPPER
========================== */
.spotlight {
  max-width: 1400px;
  margin: 0 auto 100px;
  padding: 0 24px;
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr);
  gap: 6rem;
  align-items: center;
}

/* =========================
   IMAGE AREA
========================== */
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

/* =========================
   TEXT META
========================== */
.spotlight-meta {
  max-width: 420px;
  line-height: 2.1;
  color: var(--text);
}

.section-label {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 11px;
  color: var(--muted);
  margin-bottom: 10px;
}

.spotlight-name {
  font-family: 'Montserrat', sans-serif;
  font-size: 30px;
  margin-bottom: 8px;
  color: var(--text);
}

.spotlight-price {
  font-size: 14px;
  margin-bottom: 12px;
  color: var(--text);
}

.spotlight-desc {
  font-size: 13px;
  margin-bottom: 14px;
  color: var(--text);
}

/* =========================
   FEATURES
========================== */
.spotlight-features {
  list-style: none;
  margin-bottom: 18px;
  font-size: 13px;
  color: var(--muted);
  padding: 0;
}

.spotlight-features li {
  margin-bottom: 6px;
}

.spotlight-features li::before {
  content: "✓";
  margin-right: 6px;
  color: var(--muted);
}

/* =========================
   SIZE SELECTOR
========================== */
.spotlight-size-label {
  font-size: 11px;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  margin-bottom: 6px;
  color: var(--muted);
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
  background: var(--pure-white);
  color: var(--text);
  transition: background 0.2s ease, 
              color 0.2s ease, 
              border-color 0.2s ease,
              transform 0.2s ease;
}

.size-pill:hover,
.size-pill.selected {
  background: var(--text);
  color: var(--pure-white);
  border-color: var(--text);
}

/* =========================
   ACTION BUTTONS
========================== */
.spotlight-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 280px;
}

.btn-primary {
  padding: 10px 16px;
  border-radius: 999px;
  border: 1px solid var(--border-subtle);
  background: transparent;
  color: var(--text);
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 11px;
  cursor: pointer;
  transition: background 0.2s ease, 
              color 0.2s ease, 
              transform 0.2s ease;
}

.btn-primary:hover {
  background: var(--text);
  color: var(--pure-white);
  transform: translateY(-1px);
}

.btn-outline {
  padding: 10px 16px;
  border-radius: 999px;
  border: 1px solid var(--border-subtle);
  background: var(--danger);
  color: var(--text);
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 11px;
  cursor: pointer;
  transition: background 0.2s ease, 
              color 0.2s ease, 
              transform 0.2s ease;
}

.btn-outline:hover {
  background: var(--text);
  color: var(--pure-white);
  transform: translateY(-1px);
}

/* =========================
   RESPONSIVE
========================== */
@media (max-width: 1024px) {
  .spotlight {
    grid-template-columns: 1fr;
    gap: 3rem;
  }

  .spotlight-image {
    min-height: 300px;
  }
}

@media (max-width: 768px) {
  .spotlight {
    gap: 2.5rem;
  }
}



    

    /* =========================
        FULL CSS (Responsive)
    ========================== */
:root {
  --pure-white: #fff;
  --text: #212529;
  --muted: #b0b0b0;
  --border-subtle: rgb(91 18 18 / 8%);
}

/* ====================
   Marquee Section
========================= */

/* MARQUEE */
.marquee {
  margin: 0;
  background: #A47B67;
  color: #000;
  overflow: hidden;
  white-space: nowrap;
}
.marquee-inner {
  display: inline-flex;
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






/* ============================
   HERO SECTION
   ============================ */


       
        /* Custom Font Definitions */
        .font-serif-cormorant { font-family: 'Montserrat', sans-serif; }
        .font-sans-montserrat { font-family: 'Montserrat', sans-serif; }

        /* Custom Colors for Tailwind */
        .bg-brand-black { background-color: var(--color-black); }
        .text-brand-black { color: var(--color-black); }
        .bg-brand-gray { background-color: var(--color-gray); }
        .bg-brand-burgundy { background-color: var(--color-burgundy); }
        .text-brand-burgundy { color: var(--color-burgundy); }
        
        /* Utility Classes for Premium Look */
        .container-xl { max-width: 1400px; }

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

        

        #hero .hero-content { padding: 0; background: transparent; backdrop-filter: none; border-radius: 0; }
        #hero .hero-title { font-family: 'Cormorant Garamond', serif; text-shadow: 0 6px 24px rgba(0,0,0,0.45), 0 2px 6px rgba(0,0,0,0.35); }
        #hero .hero-subtitle { color: rgba(255,255,255,0.95); text-shadow: 0 2px 8px rgba(0,0,0,0.35); }
        #hero .hero-cta { background: rgba(255,255,255,0.92); color: #000; border-color: transparent; box-shadow: 0 10px 24px rgba(0,0,0,0.4); }
        #hero .hero-cta:hover { background: #ffffff; color: #000; } */

        /* Reveal Success */

       

     


</style>
 
<!-- ================================
   HERO SECTION
   ====================================-->
<body class="bg-brand-gray text-brand-black font-sans-montserrat">
<section id="hero" class="relative h-screen w-full overflow-hidden bg-brand-black">
    
    <div class="static-hero-img absolute inset-0" 
         style="background-image: url('assets/images/no-plus-one-banner.webp');">
    </div>

    <div class="absolute inset-0 z-10 flex flex-col items-center justify-center p-4">
        <div class="text-center text-white max-w-4xl mx-auto hero-content">
            <p class="animate-fade-in hero-tagline text-sm md:text-base tracking-[0.5em] mb-6 uppercase" style="animation-delay: 0.5s;">
                The New Collection
            </p>
            <h1 class="animate-fade-in text-6xl md:text-8xl lg:text-[6rem] font-serif-cormorant hero-title font-bold tracking-tight leading-[0.9] mb-8" style="animation-delay: 0.7s;">
                TILL SUNRISE
            </h1>
            
            
            <p class="animate-fade-in hero-subtitle text-lg md:text-2xl font-light tracking-widest text-white mb-12 max-w-2xl mx-auto" style="animation-delay: 0.9s;">
                The night never ended, it just shifted shades.
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
   

   

    <!-- =============================
         MANIFESTO SECTION
    ============================= -->
<section id="about" class="py-20 md:py-32 bg-white">

  <div class="manifesto-container">

    <!-- TEXT SIDE -->
    <div>
      <div class="line"></div>
      <p class="label">About the Collection</p>

      <div class="text-block">
        <p>There are looks that don’t clock out at midnight. They move with you — through dance floors, beach waves, and hotel balconies. Breathable, fluid, and a little dangerous, they catch the light at every hour.</p>
        <p>Think crochet, net, sheerness — clothes made to be worn where the rules don’t apply.</p>
      </div>
    </div>

    <!-- IMAGE SIDE -->
    <div>

      <!-- DESKTOP COLLAGE -->
      <div class="collage-desktop">
        <div class="img-large">
          <img src="assets/images/tillsunrise/img1.avif" />
        </div>

        <div class="img-small">
          <img src="assets/images/tillsunrise/img2.avif" />
        </div>

        <div class="border-outline"></div>
      </div>

      <!-- MOBILE COLLAGE (same staggered layout) -->
      <div class="collage-mobile">

        <div class="mobile-large">
          <img src="assets/images/tillsunrise/img3.webp" />
        </div>

        <div class="mobile-small">
          <img src="assets/images/tillsunrise/img4.webp" />
        </div>

        <div class="mobile-outline"></div>
      </div>

    </div>

  </div>

</section>

    <!--  =========
          Product GRID
        ============-->

        <section id="products" class="bg-white mb-12">
    <div class="container-xl mx-auto px-6 lg:px-8">
        
        <header class="flex flex-col md:flex-row justify-between  mb-8">
            <h2 class="font-serif-cormorant text-4xl font-light mb-6 md:mb-0">Featured Products</h2>
            
            <!-- <div class="flex space-x-6 text-sm uppercase tracking-widest font-light border-b border-gray-300">
                <button class="pb-2 border-b-2 border-brand-black font-semibold">All</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Dress</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Co-ord Set</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Suit</button>
            </div> -->
        </header>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
            <?php
            $products = [
                ["name" => "Lunar Lines Split Set", "category" => "Co-ord Set", "price" => "₹8,319.00", "seed" => 10],
                ["name" => "Tidal Romance Co-ord ", "category" => "Suit", "price" => "₹7,319.00", "seed" => 11],
                 ["name" => "Embrace", "category" => "Dresses", "price" => "₹4,000.00", "seed" => 13],
                ["name" => "Isle of petal", "category" => "Co-ord Set", "price" => "₹4,000.00", "seed" => 12],
               
                ["name" => "The Perisian Floral Co-ord Set", "category" => "Dress", "price" => "₹3,000.00", "seed" => 14],
                ["name" => "Tidal Romance Co-ord", "category" => "Dress", "price" => "₹7,319.00", "seed" => 15],
            ];
            // Map to local asset images (6 files)
            $imageFiles = [
                'img1.avif',
                'img2.avif',
                'img3.webp',
                'img4.webp',
                'img5.webp',
                'img6.webp',

            ];

            foreach ($products as $i => $product):
            ?>
            <div class="group relative overflow-hidden animate-fade-in" style="animation-delay: <?php echo $i * 0.1; ?>s;">
                <div class="product-card-img-wrapper overflow-hidden">
                    <img 
                        src="assets/images/tillsunrise/<?php echo $imageFiles[$i % count($imageFiles)]; ?>" 
                        alt="<?php echo $product['name']; ?>" 
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.05]"
                    />
                    
                    <button class="absolute bottom-0 left-0 w-full py-3 bg-brand-black text-white text-xs uppercase tracking-widest opacity-0 translate-y-full transition-all duration-300 group-hover:opacity-95 group-hover:translate-y-0">
                        Quick Add
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-[10px] uppercase tracking-widest text-gray-500 mb-1"><?php echo $product['category']; ?></p>
                    <h3 class="font-serif-cormorant text-lg font-medium tracking-tight"><?php echo $product['name']; ?></h3>
                    <p class="text-sm font-light mt-1"><?php echo $product['price']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

    <!-- MAIN COMPONENT -->
    <section class="atelier-track">
        <div class="atelier-sticky">
            
            <!-- 1. Background Image -->
            <div class="reveal-bg"></div>

            <!-- 2. Center Text -->
            <div class="absolute inset-0 z-[5] bg-gradient-to-b from-black/50 via-black/25 to-black/50 pointer-events-none"></div>

    <!-- 1. Background Image -->
    <div class="reveal-bg z-[1]"></div>

    <!-- ✅ 2. Center Text (FORCED VISIBILITY) -->
    <div class="reveal-content relative z-[10] text-center text-[#f5f5f5] drop-shadow-[0_4px_20px_rgba(0,0,0,0.75)]">

      <span class="reveal-label uppercase block mb-5 text-xs tracking-[0.3em] text-[#eaeaea]">
        The Process
      </span>

      <h2 class="reveal-title serif text-5xl md:text-6xl leading-none font-semibold mb-6 text-white drop-shadow-[0_6px_26px_rgba(0,0,0,0.85)]">
        From Sketch to<br> Unforgettable
      </h2>

      <a 
        href="#" 
        class="reveal-link uppercase inline-block text-[11px] tracking-[0.25em] text-white border-b border-white/60 pb-1 hover:text-white hover:border-white transition-all duration-300 drop-shadow-[0_2px_12px_rgba(0,0,0,0.9)]"
      >
        The party isn’t over. It just became yours.
      </a>

    </div>

            <!-- 3. Left Curtain -->
            <div class="curtain left">
                <div class="curtain-inner" style="background-image: url('assets/images/tillsunrise/img6.webp');">
                    <div class="curtain-label">
                        <span class="curtain-num serif">01.</span>
                        <span class="curtain-text uppercase">The Draft</span>
                    </div>
                </div>
            </div>

            <!-- 4. Right Curtain -->
            <div class="curtain right">
                <div class="curtain-inner" style="background-image: url('assets/images/tillsunrise/img7.webp');">
                    <div class="curtain-label">
                        <span class="curtain-num serif">02.</span>
                        <span class="curtain-text uppercase">The Material</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <script>
      window.addEventListener('scroll', () => {
  const track = document.querySelector('.atelier-track');
  const rect = track.getBoundingClientRect();
  // Calculate percentage (0 to 1) based on sticky container position
  const percent = Math.max(0, Math.min(1, -rect.top / (rect.height - window.innerHeight)));
  // Set CSS variable
  track.style.setProperty('--progress', percent);
});
    </script>

    </section>

    <!-- =========================
     PRODUCT SPOTLIGHT SECTION
    ========================== -->
  <section class="spotlight-section">
    <div class="spotlight">

    <!-- LEFT: IMAGE -->
    <div class="spotlight-image">
      <img src="assets/images/tillsunrise/img2.avif" alt="Golden Hour Silk-Net Saree">
    </div>

    <!-- RIGHT: CONTENT -->
    <div class="spotlight-meta">
      <div class="section-label">Product spotlight</div>

      <h2 class="spotlight-name">Tidal Romance Co-ord </h2>

      <div class="spotlight-price">₹7,319.00</div>

      <p class="spotlight-desc">
      The Tidal Romance Co-ord is your perfect sunset-to-sunrise outfit. The striped top is detailed with delicate hand embroidery and a sleek metal ring accent, while the fit flowing skirt that opens into multiple split panels when twirling, creating a dramatic ocean-inspired silhouette. Ideal for beachside evenings, island getaways, and destination weddings.
      </p>

      <ul class="spotlight-features">
        <li>Striped resortwear set in breathable cotton-linen blend</li>
        <li>Hand-embroidered detailing on both top and skirt</li>
        <li>Metal ring accent on top for a modern edge</li>
        <li>A skirt with  multiple split-panel construction for fluid movement </li>
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

  </div>

  </section>

  <!-- =========================
       MARQUEE
  ========================== --> 

  <section id="marquee-hotspots-section">

 
  <section class="marquee">
    <div class="marquee-inner">
      <span>TILL SUNRISE — TILL SUNRISE — TILL SUNRISE — TILL SUNRISE — TILL SUNRISE —</span>
      <span aria-hidden="true">TILL SUNRISE — TILL SUNRISE — TILL SUNRISE — TILL SUNRISE — TILL SUNRISE —</span>
    </div>
  </section>

<!-- Hotspots Section -->

  <!-- =========================
     SHOP THE LOOK (HOTSPOT)
========================== -->

<section class="shop-look">
  <div class="section-label">SHOP THE LOOK</div>
  <h2 class="section-heading">TAP INTO THE FRAME</h2>

  <div class="shop-look-wrapper" id="shopLook">

    <div class="shop-look-image"></div>
    <div class="shop-look-overlay"></div>

    <!-- HOTSPOT 1 -->
    <div class="hotspot" data-hotspot="belt" style="top: 32%; left: 28%;">
      <div class="hotspot-dot"></div>
    </div>

    <div class="hotspot-card" id="hotspot-belt" style="top: 36%; left: 32%;">
      <div class="hotspot-card-header">
        <div class="hotspot-card-thumb" style="background-image:url('assets/images/till-sunrise/img1.avif');"></div>
        <div>
          <div class="hotspot-card-title">Structured Belt</div>
          <div class="hotspot-card-price">₹9,500</div>
        </div>
      </div>
      <a href="/products.php">Add to bag</a>
    </div>

    <!-- HOTSPOT 2 -->
    <div class="hotspot" data-hotspot="heels" style="top: 70%; left: 62%;">
      <div class="hotspot-dot"></div>
    </div>

    <div class="hotspot-card" id="hotspot-heels" style="top: 64%; left: 66%;">
      <div class="hotspot-card-header">
        <div class="hotspot-card-thumb" style="background-image:url('assets/images/till-sunrise/img3.webp');"></div>
        <div>
          <div class="hotspot-card-title">Embrace</div>
          <div class="hotspot-card-price">₹3,500</div>
        </div>
      </div>
     <a href="/products.php">Add to bag</a>
    </div>

    <!--  HOTSPOT 3 -->

     <div class="hotspot" data-hotspot="heels" style="top: 30%; left: 62%;">
      <div class="hotspot-dot"></div>
    </div>

    <div class="hotspot-card" id="hotspot-heels" style="top: 60%; left: 43%;">
      <div class="hotspot-card-header">
        <div class="hotspot-card-thumb" style="background-image:url('assets/images/till-sunrise/img4.webp');"></div>
        <div>
          <div class="hotspot-card-title">Isle Of Petal</div>
          <div class="hotspot-card-price">₹4,000</div>
        </div>
      </div>
      <a href="/products.php">Add to bag</a>
     
    </div>

  </div>
</section>



<!-- =========================
     HOTSPOT CSS
========================== -->
<style>
.shop-look {
  padding: 4rem 1.5rem;
  max-width: 1200px;
  margin: auto;
}

.shop-look-wrapper {
  position: relative;
  height: 60vh;
  min-height: 420px;
  overflow: hidden;
  background: #0e0e0e;
}

.shop-look-image {
  position: absolute;
  inset: 0;
  background-image: url('assets/images/theguestlist/img-5.webp');
  background-size: cover;
  background-position: center;
  transition: filter 0.25s ease;
}

.shop-look-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right, rgba(0,0,0,0.7), rgba(0,0,0,0.2));
  pointer-events: none;
}

.shop-look-wrapper.hotspot-active .shop-look-image {
  filter: brightness(0.6);
}

/* HOTSPOTS */
.hotspot {
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1px solid #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 5;
}

.hotspot-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #fff;
  animation: pulse 1.6s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  70% { transform: scale(1.7); opacity: 0; }
  100% { transform: scale(1.7); opacity: 0; }
}

/* HOTSPOT CARDS */
.hotspot-card {
  position: absolute;
  min-width: 180px;
  background: #0e0e0e;
  border-radius: 12px;
  padding: 10px;
  border: 1px solid rgba(255,255,255,0.25);
  font-size: 11px;
  display: none;
  z-index: 10;
  color: #fff;
}

.hotspot-card.visible {
  display: block;
}

.hotspot-card-header {
  display: flex;
  gap: 10px;
  margin-bottom: 6px;
}

.hotspot-card-thumb {
  width: 64px;
  height: 64px;
  border-radius: 6px;
  background-size: cover;
  background-position: center;
}

.hotspot-card-title {
  font-size: 12px;
}

.hotspot-card-price {
  font-size: 11px;
  opacity: 0.7;
}

.hotspot-card button {
  margin-top: 6px;
  width: 100%;
  padding: 6px;
  border-radius: 999px;
  background: #fff;
  color: #000;
  border: none;
  cursor: pointer;
  font-size: 10px;
  text-transform: uppercase;
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
/* RESPONSIVE */
@media (max-width: 768px) {
  .shop-look-wrapper {
    height: 50vh;
    min-height: 320px;
  }

  .hotspot-card {
    min-width: 150px;
    right: 4%;
    left: auto !important;
  }
}
</style>

<!-- =========================
     HOTSPOT JS
========================== -->
<script>
const shopLook = document.getElementById('shopLook');

if (shopLook) {
  const hotspots = shopLook.querySelectorAll('.hotspot');
  const cards = shopLook.querySelectorAll('.hotspot-card');

  function closeAll() {
    cards.forEach(card => card.classList.remove('visible'));
    shopLook.classList.remove('hotspot-active');
  }

  hotspots.forEach(h => {
    const open = () => {
      const id = h.dataset.hotspot;
      closeAll();
      const card = document.getElementById('hotspot-' + id);
      if (card) {
        card.classList.add('visible');
        shopLook.classList.add('hotspot-active');
      }
    };

    h.addEventListener('mouseenter', open);
    h.addEventListener('click', e => {
      e.stopPropagation();
      open();
    });
  });

  document.addEventListener('click', closeAll);
  shopLook.addEventListener('mouseleave', closeAll);
}
</script>

 <?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>