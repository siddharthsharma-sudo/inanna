<!-- ========================================================= -->
<!-- 1. HERO SECTION (Swiper Slider with Enhanced Controls) -->
<!-- ========================================================= -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css">
<section id="hero">
    <div class="fashion-hero-single">
        <video id="heroVideo" class="fashion-video-background" autoplay muted loop playsinline preload="none" poster="assets/images/hero-banner-slide-1.avif"></video>
        <div class="fashion-overlay" aria-hidden="true"></div>
        <div class="fashion-content">
            <p class="hero-tagline">A PRESENCE YOU DON'T FOLLOW UP WITH.</p>
            <h2 class="hero-title">FASHION WITH<br/>PURPOSE</h2>
            <a href="products.php" class="hero-cta">
                <span>EXPLORE</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
        <div class="hero-social">
            <span class="hero-social-label">FOLLOW US</span>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            
            <a href="https://www.instagram.com/worldofinanna?igsh=MTNoM2UxbHU4bW1iNA==" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        </div>
        <div class="hero-scroll"><span>SCROLL</span><span class="hero-scroll-line"></span></div>
    </div>

</section>

 
<script>
document.addEventListener('DOMContentLoaded', function () {
  var video = document.getElementById('heroVideo');
  if (!video) return;
  var isMobile = window.innerWidth < 768;
  video.src = isMobile ? 'assets/video/inanna-hero-mobile-ultra.mp4' : 'assets/video/inanna-hero-ultra.mp4';
  try { video.play(); } catch(e) {}
  window.addEventListener('pagehide', function(){ try { video.pause(); } catch(e){} });
});
</script>
<style>
/* Minimal styles for video-only hero */
#hero { position: relative; }
.fashion-hero-single { position: relative; overflow: hidden;  }

/* video fills slide */
.fashion-video-background {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 0;
}

/* optional subtle overlay to keep contrast for UI elements */
.fashion-overlay { position:absolute; inset:0; background: rgba(0,0,0,0.12); z-index:1; }

/* ensure arrow buttons are clickable and visible */
.hero-arrow { display:none; }
</style>
