<style>
        /* --- VARIABLES (scoped) --- */
        .runway-section {
            --c-black: #050505;
            --c-ivory: #F2F0E9;
            --c-gold: #C6A87C;
            --c-crimson: #4A0404;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        

        /* --- TYPOGRAPHY --- */
        h2 { font-family: 'Playfair Display', serif; }
        h4 { font-family: 'Playfair Display', serif; }
        .font-mono { font-family: 'Montserrat', sans-serif; letter-spacing: 0.1em; }

        /* --- SPACERS FOR SCROLL DEMO --- */
        .spacer {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        /* --- RUNWAY SECTION CONTAINER --- */
        .runway-section {
            /* 
               This height determines how "long" the scroll takes. 
               300vh means you have to scroll 3 screens worth of height 
               to finish the horizontal movement.
            */
            height: 400vh; 
            position: relative;
           
        }

        /* --- STICKY WRAPPER --- */
        /* This holds the viewport still while we scroll through the section height */
        .runway-sticky-wrapper {
            position: sticky;
            top: 0;
            height: 100vh;
            width: 100%;
            overflow: hidden; /* Hide the overflow of the long track */
            display: flex;
            align-items: center;
            
        }

        /* --- HEADER TITLE --- */
        .runway-header {
            position: absolute;
            top: 01rem;
            left: 3rem;
            z-index: 10;
        }
        .runway-header h2 {
            font-size: 2rem;
            color: var(--c-black);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .runway-header span {
            font-size: 0.9rem;
            opacity: 0.9;
            vertical-align: top;
            margin-left: 0.5rem;
        }

        /* --- THE TRACK (MOVES HORIZONTALLY) --- */
        .runway-track {
            display: flex;
            gap: 4rem;
            padding-left: 5vw; 
            padding-right: 10vw;
            /* Will be animated via JS */
            will-change: transform; 
        }

        /* --- INDIVIDUAL ITEM --- */
        .runway-item {
            position: relative;
            width: 25vw; /* Adjust based on preference */
            min-width: 300px;
            cursor: pointer;
            /* group: 'hover-target'; */
        }

        /* --- IMAGE WRAPPER & EFFECTS --- */
        .img-container {
            position: relative;
            aspect-ratio: 3/4;
            overflow: hidden;
            /* filter: grayscale(100%); */
            transition: filter 0.7s ease-out;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1.2s cubic-bezier(0.25, 1, 0.5, 1);
        }

        /* Spotlight Overlay */
        .spotlight {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent, transparent);
            opacity: 0.6;
            transition: opacity 0.5s ease;
        }

        /* --- TEXT REVEAL --- */
        .item-info {
            margin-top: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            opacity: 0.4;
            transform: translateY(10px);
            transition: all 0.5s ease-out;
        }

      

        .item-info h4 {
            font-size: 1.5rem;
            color: #0e0e0e;
            
        }

        .category {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            color: #0e0e0e;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 0.25rem;
            display: block;
        }

        .price {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            color: rgba(41, 0, 0, 1);
        }

        /* FILM GRAIN (Optional Polish) */
        .grain {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 999;
            opacity: 0.04;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }
    </style>
 

    <div class="grain"></div>

    <!-- Dummy Scroll Space Above -->
    <!-- <section class="spacer">
        <h1 style="font-family: 'Cinzel'; font-size: 3rem; color: #333;">SCROLL DOWN</h1>
    </section> -->

    <!-- RUNWAY SECTION -->
    <section class="runway-section" id="runway">
        <div class="runway-sticky-wrapper">
            
            <div class="runway-header">
                <h2>LOOKBOOK  <span>S/S  XXV</span></h2>
            </div>

            <!-- The Horizontal Track -->
            <div class="runway-track" id="track">
                
                <!-- Product 1 -->
                <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/img5.avif" alt="Obsidian Drape">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Obsidian Drape</h4>
                            <span class="category">Couture</span>
                        </div>
                        
                    </div>
                </div>

                <!-- Product 2 -->
                <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/img4.avif" alt="Ivory Structure">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Ivory Structure</h4>
                            <span class="category">Ready-to-Wear</span>
                        </div>
                        
                    </div>
                </div>

                <!-- Product 3 -->
                <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/img3.webp" alt="Gold Dust Gown">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Gold Dust Gown</h4>
                            <span class="category">Couture</span>
                        </div>
                       
                    </div>
                </div>

                <!-- Product 4 -->
                <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/img2.avif" alt="Crimson Veil">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Crimson Veil</h4>
                            <span class="category">Accessories</span>
                        </div>
                        
                    </div>
                </div>

                <!-- Product 5 -->
                <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/img1.avif" alt="Void Blazer">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Void Blazer</h4>
                            <span class="category">Outerwear</span>
                        </div>
                       
                    </div>
                </div>

                 <!-- Product 6 -->
                 <div class="runway-item">
                    <div class="img-container">
                        <img src="assets/images/noplus/fb-2.webp" alt="Lunar Silk">
                        <div class="spotlight" aria-hidden="true"></div>
                    </div>
                    <div class="item-info">
                        <div>
                            <h4>Lunar Silk</h4>
                            <span class="category">Dresses</span>
                        </div>
                       
                    </div>
                </div>
                
                

            </div>
        </div>
    </section>

    <!-- Dummy Scroll Space Below -->
    <!-- <section class="spacer">
        <h1 style="font-family: 'Cinzel'; font-size: 3rem; color: #333;">NEXT SECTION</h1>
    </section> -->

    <script>
        (function(){
          var section = document.getElementById('runway');
          var track = document.getElementById('track');
          if (!section || !track) return;
          var viewportHeight = window.innerHeight;
          var sectionTop = 0;
          var sectionHeight = 0;
          var maxTranslate = 0;
          function recalc(){
            viewportHeight = window.innerHeight;
            sectionTop = section.offsetTop;
            sectionHeight = section.offsetHeight;
            var trackWidth = track.scrollWidth;
            var vw = window.innerWidth;
            maxTranslate = Math.max(0, trackWidth - vw);
          }
          function update(){
            var start = sectionTop;
            var end = sectionTop + sectionHeight - viewportHeight;
            var progress = (window.scrollY - start) / (end - start);
            if (progress < 0) progress = 0;
            if (progress > 1) progress = 1;
            var currentX = -progress * maxTranslate;
            track.style.transform = 'translateX(' + currentX + 'px)';
          }
          recalc();
          update();
          window.addEventListener('scroll', update, { passive: true });
          window.addEventListener('resize', function(){ recalc(); update(); });
        })();
    </script>