<style>
    
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');

    :root {
        /* No --bg-color defined here, relying on parent page background */
        --text-color: #ffff;
        --card-height: 560px;
    }

    /* --- Main Container Setup --- */
    .promo-container-3x {
        /* Removed background-color: var(--bg-color); -> Now transparent */
        padding: 60px 20px;
        display: flex;
        max-width: 1440px;
        margin: 0 auto;
        gap: 20px;
        flex-wrap: wrap; 
        justify-content: center;
        font-family: 'Playfair Display', serif;
    }

    /* --- Individual Card Styling --- */
    .promo-card-3x {
        flex: 1 1 30%; 
        min-width: 300px; 
        height: var(--card-height);
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        /* Removed background-color: var(--bg-color); -> Now transparent */
    }

    /* --- Image Styling and Centering (Object-fit: contain) --- */
    .promo-card-3x img {
        position: absolute; 
        width: 100%;
        height: 100%;
        object-fit: cover; 
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%); 
        background-color: transparent; 
        z-index: 1; 
    }
    
    /* --- Text Overlay Container (Fixed at Bottom) --- */
    .text-overlay-bottom {
        position: absolute; 
        z-index: 2; 
       color: #ffff !important; 
        bottom: 30px; 
        left: 0;
        right: 0;
        padding: 0 20px;
        text-align: center; 
         
    }

    /* --- Typography --- */
    .card-title-3x {
        font-size: 1.5rem;
        font-weight: 600;
        line-height: 1.2;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
        
    }

    /* --- Animation Setup (Initial Hidden State) --- */
    .promo-card-3x {
        /* Initial state: pushed down and invisible */
        transform: translateY(100px);
        opacity: 0;
        transition: transform 0.8s ease-out, opacity 0.8s ease-out;
    }

    /* --- Animation Trigger State (Visible State) --- */
    .promo-card-3x.visible {
        /* Final state: original position and fully visible */
        transform: translateY(0);
        opacity: 1;
    }

    /* --- Media Query for Mobile/Stacking --- */
    @media (max-width: 900px) {
        .promo-container-3x {
            gap: 40px;
        }
        .promo-card-3x {
            flex: 1 1 100%; 
            min-width: unset;
        }
    }
</style>

<div class="promo-container-3x">
    
    <div class="promo-card-3x">
        <img src="https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/w:369/h:550/q:mauto/dpr:1.3/f:best/https://worldofinanna.org/wp-content/uploads/2025/08/IMG_5496_Original-scaled.webp" alt="NO PLUS ONE">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">NO PLUS ONE</h2>
        </div>
    </div>

    <div class="promo-card-3x">
        <img src="https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/w:374/h:550/q:mauto/dpr:1.3/f:best/https://worldofinanna.org/wp-content/uploads/2025/09/cropped.jpg" alt="THE GUEST LIST">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">THE GUEST LIST</h2>
        </div>
    </div>

    <div class="promo-card-3x">
        <img src="https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/rt:fill/w:249/h:550/q:mauto/dpr:2.0/f:best/https://worldofinanna.org/wp-content/uploads/2025/08/Pro-Capture-One-1209.jpg" alt="TILL SUNRISE">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">TILL SUNRISE</h2>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.promo-card-3x');

        const observerOptions = {
            root: null, 
            rootMargin: '0px 0px -100px 0px',
            threshold: 0.1 
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                const target = entry.target;
                
                if (entry.isIntersecting) {
                    target.classList.add('visible');
                } else {
                    target.classList.remove('visible');
                }
            });
        }, observerOptions);

        cards.forEach(card => {
            observer.observe(card);
        });
    });
</script>