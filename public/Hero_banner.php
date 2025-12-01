<!-- ========================================================= -->
<!-- 1. HERO SECTION (Swiper Slider with Enhanced Controls) -->
<!-- ========================================================= -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="/inanna/public/assets/css/style.css">
<section id="hero">
    
    <div class="swiper fashion-hero-slider">
    
        <div class="swiper-wrapper">

            <div class="swiper-slide">
                <img class="fashion-image-background" src="/inanna/public/assets/images/hero-banner-slide-1.avif" alt="Cultural Elegance">
                <div class="fashion-overlay"></div>
                <div class="fashion-content">
                    <div class="hero-tagline">NEW TREND</div>
                    <h1 class="hero-title">CULTURAL<br/>ELEGANCE</h1>
                    <a href="#" class="hero-cta">
                        <span>OUR STUDIO</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="swiper-slide">
                <img class="fashion-image-background" src="/inanna/public/assets/images/slider-3.webp" alt="Fashion With Purpose">
                <div class="fashion-overlay"></div>
                <div class="fashion-content">
                    <div class="hero-tagline">A PRESENCE YOU DON'T FOLLOW UP WITH.</div>
                    <h1 class="hero-title">FASHION WITH<br/>PURPOSE</h1>
                    <a href="#" class="hero-cta">
                        <span>EXPLORE</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="swiper-slide">
                <img class="fashion-image-background" src="/inanna/public/assets/images/no-plus-one-banner.webp" alt="Timeless Elegance">
                <div class="fashion-overlay"></div>
                <div class="fashion-content">
                    <div class="hero-tagline">THE NEW COLLECTION: NO PLUS ONE.</div>
                    <h1 class="hero-title">TIMELESS<br/>ELEGANCE</h1>
                    <a href="#" class="hero-cta">
                        <span>SHOP NOW</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- Navigation Arrows -->
        <button class="hero-arrow hero-arrow-prev" aria-label="Previous">
            <svg class="hero-arrow-icon" viewBox="0 0 24 24">
                <path d="M15 6l-6 6 6 6" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <line x1="8" y1="12" x2="20" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></line>
            </svg>
        </button>
        <button class="hero-arrow hero-arrow-next" aria-label="Next">
            <svg class="hero-arrow-icon" viewBox="0 0 24 24">
                <path d="M9 6l6 6-6 6" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                <line x1="4" y1="12" x2="16" y2="12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></line>
            </svg>
        </button>
        
        <!-- Pagination Bars -->
        <div class="hero-pagination"></div>

        <!-- Social Icons -->
        <div class="hero-social">
            <span class="hero-social-label">FOLLOW US</span>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="hero-scroll"><span>SCROLL</span><span class="hero-scroll-line"></span></div>

    </div>

</section>

 
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Swiper
        var swiper = new Swiper(".fashion-hero-slider", {
            loop: true,
            autoplay: {
                delay: 7000, 
                disableOnInteraction: false,
            },
            speed: 1200, 
            effect: "fade",
            fadeEffect: {
                crossFade: true,
            },
            navigation: {
                nextEl: ".hero-arrow-next",
                prevEl: ".hero-arrow-prev",
            },
            pagination: {
                el: ".hero-pagination",
                type: "bullets",
                clickable: true,
                renderBullet: function (index, className) {
                    return '<span class="' + className + ' hero-bullet"><span class="hero-bullet-fill"></span></span>';
                }
            },
            on: {
                init: function () {
                    const pag = document.querySelector('.hero-pagination');
                    if (pag) {
                        pag.style.setProperty('--bullet-duration', this.params.autoplay.delay + 'ms');
                    }
                }
            }
        });
    });
</script>
