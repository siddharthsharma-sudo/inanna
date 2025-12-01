<?php
$page_title = 'NO PLUS ONE | INANNA';
$meta_description = 'A drop for the ones who arrive unforgettable. Statement silhouettes, modern embroidery, and unapologetic presence.';
include __DIR__ . '/../../includes/header.php';
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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Tailwind Configuration and Base Styles */
        :root {
            --color-black: #0a0a0a;
            --color-gray: #f4f4f4;
            --color-burgundy: #7c2d2d;
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
    </style>
</head>

<body class="bg-brand-gray text-brand-black font-sans-montserrat">
<section id="hero" class="relative h-screen w-full overflow-hidden bg-brand-black">
    
    <div class="static-hero-img absolute inset-0" 
         style="background-image: url('/inanna/public/assets/images/no-plus-one-banner.webp');">
    </div>

    <div class="absolute inset-0 z-10 flex flex-col items-center justify-center p-4">
        <div class="text-center text-white max-w-4xl mx-auto">
            
            <p class="animate-fade-in text-sm md:text-base tracking-[0.5em] mb-6 uppercase" style="animation-delay: 0.5s;">
                The New Collection
            </p>
            
            <h1 class="animate-fade-in text-6xl md:text-8xl lg:text-[6rem] font-serif-cormorant font-bold tracking-tight leading-[0.9] mb-8" style="animation-delay: 0.7s;">
                NO PLUS ONE
            </h1>
            
            <p class="animate-fade-in text-lg md:text-2xl font-light tracking-widest text-white/90 mb-12 max-w-2xl mx-auto" style="animation-delay: 0.9s;">
                A presence you don’t follow up with.
            </p>

            <a 
                href="#about"
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

<section id="about" class="py-24 md:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 md:gap-24 items-start">
            
            <div class="relative h-[500px] md:h-[700px] overflow-hidden">
                <div class="absolute inset-0 z-0 border border-brand-burgundy translate-x-3 translate-y-3 hidden md:block"></div>
                
                <img 
                    src="/inanna/public/assets/images/no-plus-one-about.webp" 
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

<section class="py-24 md:py-32 bg-brand-gray border-t border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <h2 class="text-center font-serif-cormorant text-3xl font-light mb-16">
            The Collection's Signature Details
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            <div class="text-center group p-4 animate-fade-in" style="animation-delay: 0.1s;">
                <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center rounded-full border border-brand-black transition-colors duration-500 group-hover:bg-brand-black">
                    <svg class="w-6 h-6 transition-colors duration-500 group-hover:text-white text-brand-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.09 2.09L21 8m-6-4l-1.05 1.05M5.91 18.09L8 20.2M15 9l-1.05 1.05M9 4l1.05 1.05M4 9l1.05 1.05M18 15l1.05 1.05M18 9l1.05 1.05"></path></svg>
                </div>
                <h3 class="font-serif-cormorant text-xl font-medium mb-3">Signature Detail</h3>
                <p class="text-sm text-gray-600">Hand-embroidered corsets, structured silks, and featherlight fabrics that catch the eye without trying.</p>
            </div>

            <div class="text-center group p-4 animate-fade-in" style="animation-delay: 0.3s;">
                <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center rounded-full border border-brand-black transition-colors duration-500 group-hover:bg-brand-black">
                    <svg class="w-6 h-6 transition-colors duration-500 group-hover:text-white text-brand-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </div>
                <h3 class="font-serif-cormorant text-xl font-medium mb-3">Modern Contrast</h3>
                <p class="text-sm text-gray-600">Muted black and nude tones punctuated by deep, strategic bursts of burgundy that assert presence.</p>
            </div>

            <div class="text-center group p-4 animate-fade-in" style="animation-delay: 0.5s;">
                <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center rounded-full border border-brand-black transition-colors duration-500 group-hover:bg-brand-black">
                    <svg class="w-6 h-6 transition-colors duration-500 group-hover:text-white text-brand-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </div>
                <h3 class="font-serif-cormorant text-xl font-medium mb-3">After Hours</h3>
                <p class="text-sm text-gray-600">Designed for the kind of night that becomes a story.</p>
            </div>

        </div>
    </div>
</section>

<section id="products" class="py-24 md:py-32 bg-white">
    <div class="container-xl mx-auto px-6 lg:px-8">
        
        <header class="flex flex-col md:flex-row justify-between items-center mb-12">
            <h2 class="font-serif-cormorant text-4xl font-light mb-6 md:mb-0">Featured Products</h2>
            
            <div class="flex space-x-6 text-sm uppercase tracking-widest font-light border-b border-gray-300">
                <button class="pb-2 border-b-2 border-brand-black font-semibold">All</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Tops</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Bottoms</button>
                <button class="pb-2 border-b-2 border-transparent hover:border-gray-500 transition-colors">Dresses</button>
            </div>
        </header>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
            <?php
            $products = [
                ["name" => "The Midnight Corset", "category" => "Tops", "price" => "₹ 18,999", "seed" => 10],
                ["name" => "Obsidian Blazer", "category" => "Jacket", "price" => "₹ 24,500", "seed" => 11],
                ["name" => "Sheer Desire Skirt", "category" => "Bottoms", "price" => "₹ 12,200", "seed" => 12],
                ["name" => "Velvet Slip Dress", "category" => "Dresses", "price" => "₹ 31,800", "seed" => 13],
                ["name" => "Structured Trousers", "category" => "Bottoms", "price" => "₹ 15,499", "seed" => 14],
                ["name" => "Burgundy Slink Cami", "category" => "Tops", "price" => "₹ 9,900", "seed" => 15],
            ];

            foreach ($products as $i => $product):
            ?>
            <div class="group relative overflow-hidden animate-fade-in" style="animation-delay: <?php echo $i * 0.1; ?>s;">
                <div class="product-card-img-wrapper overflow-hidden">
                    <img 
                        src="https://picsum.photos/seed/<?php echo $product['seed']; ?>/400/533" 
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



</body>
</html>
<?php include __DIR__ . '/../../includes/footer.php';?>