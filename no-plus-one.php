<?php
$page_title = 'NO PLUS ONE | INANNA';
$meta_description = 'A drop for the ones who arrive unforgettable. Statement silhouettes, modern embroidery, and unapologetic presence.';
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
        /* Custom Tailwind Configuration and Base Styles */
        :root {
            --color-black: #0a0a0a;
            --color-gray: #f4f4f4;
            --color-burgundy: #7c2d2d;
            
        }

       
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

        /* Product Card Aspect Ratio */
        .product-card-img-wrapper {
            aspect-ratio: 3 / 4;
        }

        .spotlight { margin-bottom: 100px; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.1fr); gap: 6rem; align-items: center; }
        .spotlight-image { border-radius: 0; overflow: hidden; background-position: center; background-size: cover; aspect-ratio: 1/1; }
        .spotlight-meta { max-width: 420px; line-height: 2.2; }
        .spotlight-name { font-family: 'Playfair Display', serif; font-size: 26px; margin-bottom: 6px; }
        .spotlight-price { font-size: 14px; margin-bottom: 10px; }
        .spotlight-desc { font-size: 13px; margin-bottom: 14px; }
        .spotlight-features { list-style: none; margin-bottom: 18px; font-size: 13px; }
        .spotlight-features li::before { content: "✓"; margin-right: 6px; }
        .spotlight-size-label { font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; margin-bottom: 6px; }
        .spotlight-sizes { display: flex; gap: 8px; margin-bottom: 18px; }
        .size-pill { width: 34px; height: 30px; border-radius: 6px; border: 1px solid rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; font-size: 11px; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
        .size-pill.selected { background: #fff; color: #000; border-color: #fff; }
        .spotlight-actions { display: flex; flex-direction: column; gap: 10px; max-width: 280px; }
        .btn-primary { padding: 10px 16px; border-radius: 999px; border: none; background: #fff; color: #000; text-transform: uppercase; letter-spacing: 0.18em; font-size: 11px; cursor: pointer; transition: background 0.2s ease, transform 0.2s ease; }
        .btn-primary:hover { background: #eaeaea; transform: translateY(-1px); }
        .btn-outline { padding: 10px 16px; border-radius: 999px; border: 1px solid rgba(0,0,0,0.15); background: transparent; color: #000; text-transform: uppercase; letter-spacing: 0.18em; font-size: 11px; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease; }
        .btn-outline:hover { background: #fff; color: #000; transform: translateY(-1px); }

        @media (max-width: 1024px) { .spotlight { grid-template-columns: minmax(0, 1fr); } .spotlight-image { min-height: 300px; } }

        #hero .hero-content { padding: 0; background: transparent; backdrop-filter: none; border-radius: 0; }
        #hero .hero-title { font-family: 'Cormorant Garamond', serif; text-shadow: 0 6px 24px rgba(0,0,0,0.45), 0 2px 6px rgba(0,0,0,0.35); }
        #hero .hero-subtitle { color: rgba(255,255,255,0.95); text-shadow: 0 2px 8px rgba(0,0,0,0.35); }
        #hero .hero-cta { background: rgba(255,255,255,0.92); color: #000; border-color: transparent; box-shadow: 0 10px 24px rgba(0,0,0,0.4); }
        #hero .hero-cta:hover { background: #ffffff; color: #000; }

    </style>
</head>

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
                NO PLUS ONE
            </h1>
            
            
            <p class="animate-fade-in hero-subtitle text-lg md:text-2xl font-light tracking-widest text-white mb-12 max-w-2xl mx-auto" style="animation-delay: 0.9s;">
                A presence you don’t follow up with.
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

<section id="about" class="py-20 md:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 md:gap-24 items-start">
            
            <div class="relative h-[500px] md:h-[700px] overflow-hidden">
                <div class="absolute inset-0 z-0 border border-brand-burgundy translate-x-3 translate-y-3 hidden md:block"></div>
                
                <img 
                    src="assets/images/no-plus-one-about.webp" 
                    alt="Model in elegant minimal dress" 
                    class="w-full h-full object-cover relative z-10 animate-fade-in"
                />
            </div>

            <div class="order-2 md:order-1 space-y-8 pr-0 md:pr-12 animate-fade-in" style="animation-delay: 0.3s;">
                <div class="flex items-center gap-4">
                    <span class="h-[1px] w-12 bg-brand-black"></span>
                    <span class="text-xs font-bold tracking-[0.2em] uppercase text-gray-500">About the Collection</span>
                </div>
                
                <h2 class="text-4xl md:text-6xl font-serif-cormorant leading-tight">
                    Not every outfit needs context.
                </h2>
                
                <div class="space-y-6 text-gray-600 font-light text-lg leading-relaxed">
                    <p>
                        Some just change the energy the second you walk in. Think sheer, structure, sharp tailoring — bold in detail, calm in attitude.
                    </p>
                    <p>
                        This is the kind of look that doesn’t ask for approval, it just fits. We stripped away the noise to focus on silhouette and presence.
                    </p>
                </div>

                <div class="pt-4">
                    <a href="#style-points" class="inline-block border-b border-brand-black pb-1 text-sm tracking-widest uppercase hover:text-brand-burgundy hover:border-brand-burgundy transition-colors">
                        Read the Story
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>



<section id="products" class="bg-white">
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
                ["name" => "Crimson Wraith", "category" => "Co-ord Set", "price" => "₹ 18,999", "seed" => 10],
                ["name" => "Citrus Garden", "category" => "Suit", "price" => "₹ 24,500", "seed" => 11],
                ["name" => "Fuschia Banarasi Drape", "category" => "Co-ord Set", "price" => "₹ 12,200", "seed" => 12],
                ["name" => "Golden hour heiress", "category" => "Dresses", "price" => "₹ 31,800", "seed" => 13],
                ["name" => "Pastel power Jacquard set", "category" => "Dress", "price" => "₹ 15,499", "seed" => 14],
                ["name" => "Whirlwind Serenade", "category" => "Dress", "price" => "₹ 9,900", "seed" => 15],
            ];
            // Map to local asset images (6 files)
            $imageFiles = [
                'img1.webp',
                'img2.webp',
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
                        src="assets/images/noplus/<?php echo $imageFiles[$i % count($imageFiles)]; ?>" 
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

<!-- Product Spotlight -->

<!-- =========================
     PRODUCT SPOTLIGHT SECTION
========================== -->
<section class="spotlight-section">
  
  <div class="spotlight">
    
    <div class="spotlight-image">
      <img src="assets/images/noplus/img2.webp" alt="Golden Hour Silk-Net Saree">
    </div>

    <div class="spotlight-meta">
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

     

      <div class="spotlight-actions">
      <a class="btn-primary text-center"href="products.php">SHOP NOW</a>
      </div>
    </div>

  </div>
</section>

<style>
/* =========================
     PRODUCT SPOTLIGHT CSS
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

/* Entire section background */
.spotlight-section {
  background: var(--pure-white);
  width: 100%;
  padding: 4rem 0;
}

/* Layout container */
.spotlight {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 24px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
}

/* LEFT: IMAGE */
.spotlight-image {
  width: 100%;
  display: flex;
  justify-content: center;
}

.spotlight-image img {
  width: 100%;
  height: auto;
  object-fit: contain;
  display: block;
}

/* RIGHT: TEXT */
.spotlight-meta {
  max-width: 480px;
  line-height: 1.8;
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
  font-size: 34px;
  margin-bottom: 10px;
  color: var(--text);
}

.spotlight-price {
  font-size: 15px;
  margin-bottom: 14px;
  color: var(--text);
}

.spotlight-desc {
  font-size: 14px;
  margin-bottom: 16px;
  color: var(--text);
}

.spotlight-features {
  list-style: none;
  padding: 0;
  margin-bottom: 20px;
  font-size: 14px;
  color: var(--muted);
}

.spotlight-features li {
  margin-bottom: 6px;
}

.spotlight-features li::before {
  content: "✓ ";
  color: var(--muted);
}

/* SIZES */
.spotlight-size-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: var(--muted);
  margin-bottom: 6px;
}

.spotlight-sizes {
  display: flex;
  gap: 10px;
  margin-bottom: 22px;
}

.size-pill {
  width: 36px;
  height: 32px;
  border: 1px solid var(--border-subtle);
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  transition: 0.3s ease;
  background: var(--pure-white);
  color: var(--text);
}

.size-pill.selected,
.size-pill:hover {
  background: var(--text);
  color: var(--pure-white);
  border-color: var(--text);
}

/* BUTTONS */
.spotlight-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 260px;
}

.btn-primary {
  padding: 10px 16px;
  border-radius: 999px;
  border: none;
   border: 1px solid var(--border-subtle);
  color: var(--text);
  letter-spacing: 0.18em;
  font-size: 11px;
  cursor: pointer;
  text-transform: uppercase;
  transition: 0.2s ease;
}

.btn-primary:hover {
  opacity: 0.8;
  background: var(--text);
  color: var(--pure-white);
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
  transition: 0.2s ease;
}

.btn-outline:hover {
  background: var(--text);
  color: var(--pure-white);
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .spotlight {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }
}
</style>

<!-- Marquee -->

   <section id="marquee-hotspots-section">

  <!-- =========================
       MARQUEE
  ========================== -->
  <section class="marquee">
    <div class="marquee-inner">
      <span>TILL  — NO PLUS ONE — NO PLUS ONE — NO PLUS ONE — NO PLUS ONE —</span>
      <span aria-hidden="true">NO PLUS ONE — NO PLUS ONE — NO PLUS ONE — NO PLUS ONE — NO PLUS ONE —</span>
    </div>
  </section>

  <!-- =========================
       SHOP THE LOOK (HOTSPOTS)
  ========================== -->
  <section class="shop-look">
    <div class="section-label">Shop the look</div>
    <h2 class="section-heading">Tap into the frame</h2>

    <div class="shop-look-wrapper" id="shopLook">
      <div class="shop-look-image" 
           style="background-image:url('assets/images/noplus/img8.webp');"></div>

      <div class="shop-look-overlay"></div>

      <!-- Hotspot #1 -->
      <div class="hotspot" data-hotspot="belt" style="top: 32%; left: 28%;">
        <div class="hotspot-dot"></div>
      </div>

      <div class="hotspot-card" id="hotspot-belt" style="top: 36%; left: 32%;">
        <div class="hotspot-card-header">
          <div class="hotspot-card-thumb" style="background-image:url('assets/images/noplus/img1.webp');"></div>
          <div>
            <div class="hotspot-card-title">Structured Belt</div>
            <div class="hotspot-card-price">₹9,500</div>
          </div>
        </div>
        <button>Add to bag</button>
      </div>

      <!-- Hotspot #2 -->
      <div class="hotspot" data-hotspot="bag" style="top: 66%; left: 22%;">
        <div class="hotspot-dot"></div>
      </div>

      <div class="hotspot-card" id="hotspot-bag" style="top: 70%; left: 26%;">
        <div class="hotspot-card-header">
          <div class="hotspot-card-thumb" style="background-image:url('assets/images/noplus/img2.webp');"></div>
          <div>
            <div class="hotspot-card-title">Evening Bag</div>
            <div class="hotspot-card-price">₹12,800</div>
          </div>
        </div>
        <button>Add to bag</button>
      </div>

      <!-- Hotspot #3 -->
      <div class="hotspot" data-hotspot="heels" style="top: 74%; left: 56%;">
        <div class="hotspot-dot"></div>
      </div>

      <div class="hotspot-card" id="hotspot-heels" style="top: 68%; left: 60%;">
        <div class="hotspot-card-header">
          <div class="hotspot-card-thumb" style="background-image:url('assets/images/noplus/img3.webp');"></div>
          <div>
            <div class="hotspot-card-title">Noir Stiletto</div>
            <div class="hotspot-card-price">₹16,800</div>
          </div>
        </div>
        <button>Add to bag</button>
      </div>

      <!-- Hotspot #4 -->
      <div class="hotspot" data-hotspot="necklace" style="top: 18%; left: 62%;">
        <div class="hotspot-dot"></div>
      </div>

      <div class="hotspot-card" id="hotspot-necklace" style="top: 22%; left: 66%;">
        <div class="hotspot-card-header">
          <div class="hotspot-card-thumb" style="background-image:url('assets/images/noplus/img4.webp');"></div>
          <div>
            <div class="hotspot-card-title">Statement Necklace</div>
            <div class="hotspot-card-price">₹7,200</div>
          </div>
        </div>
        <button>Add to bag</button>
      </div>

    </div>
  </section>

</section>



<!-- =========================
     FULL CSS (Responsive)
========================== -->
<style>
:root {
  --pure-white: #fff;
  --text: #212529;
  --muted: #b0b0b0;
  --border-subtle: rgba(255,255,255,0.25);
  --accent-dark: #0e0e0e;
  --transition: 0.25s ease;
}


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


/* SHOP THE LOOK */
.shop-look {
  padding: 3rem 1.5rem;
  max-width: 1200px;
  margin: auto;
}

.section-label {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 11px;
  color: var(--muted);
}
.section-heading {
  font-family: 'Montserrat', sans-serif;
  font-size: 2rem;
  margin-bottom: 1.5rem;
}

.shop-look-wrapper {
  position: relative;
  height: 60vh;
  min-height: 420px;
  overflow: hidden;
  background: var(--accent-dark);
}

.shop-look-image {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  transition: filter var(--transition);
}

.shop-look-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to right,
    rgba(0,0,0,0.6),
    rgba(0,0,0,0.1)
  );
}


/* HOTSPOTS */
.hotspot {
  position: absolute;
  width: 20px;
  height: 20px;
  border: 1px solid var(--pure-white);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 4;
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


/* HOTSPOT CARDS */
.hotspot-card {
  position: absolute;
  width: 180px;
  background: #0e0e0e;
  color: var(--pure-white);
  border: 1px solid var(--border-subtle);
  padding: 10px;
  border-radius: 12px;
  font-size: 11px;
  display: none;
  z-index: 5;
}

.hotspot-card.visible { display: block; }

.hotspot-card-header {
  display: flex;
  gap: 10px;
  margin-bottom: 6px;
}

.hotspot-card-thumb {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  background-size: cover;
  background-position: center;
}

.hotspot-card button {
  margin-top: 4px;
  width: 100%;
  padding: 6px;
  border-radius: 999px;
  background: var(--pure-white);
  color: #000;
  border: none;
  cursor: pointer;
  font-size: 10px;
  text-transform: uppercase;
}


/* =========================
     RESPONSIVE
========================== */
@media (max-width: 1024px) {
  .shop-look-wrapper {
    height: 50vh;
  }
}

@media (max-width: 768px) {
  .shop-look-wrapper {
    height: 45vh;
    min-height: 360px;
  }
  .shop-look-image { filter: blur(2px); transform: scale(1.02); }
  .hotspot {
    width: 18px; height: 18px;
  }
  .hotspot-card {
    width: 160px;
  }
  .hotspot[data-hotspot="necklace"], #hotspot-necklace { display: none !important; }
}

@media (max-width: 480px) {
  .section-heading {
    font-size: 1.6rem;
  }
  .hotspot-card {
    width: 150px;
  }
}
</style>



<!-- =========================
     FULL JS
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
    // Open card
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
    h.addEventListener('click', e => { e.stopPropagation(); open(); });
  });

  document.addEventListener('click', closeAll);
  shopLook.addEventListener('mouseleave', closeAll);
}
</script>

<script>
  const sizeContainer = document.getElementById('sizeSelector');

  if (sizeContainer) {
    sizeContainer.addEventListener('click', e => {
      const pill = e.target.closest('.size-pill');
      if (!pill) return;

      sizeContainer.querySelectorAll('.size-pill')
        .forEach(p => p.classList.remove('selected'));

      pill.classList.add('selected');
    });
  }
</script>


<?php include __DIR__ . '/includes/footer.php';?>
</body>
</html>