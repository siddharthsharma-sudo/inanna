<style>
    
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap');

    :root {
        /* No --bg-color defined here, relying on parent page background */
        --text-color: #ffff;
        --card-height: 560px;
    }

    /* --- Main Container Setup --- */
    .promo-container-3x, .promo-container-2x {
        /* Removed background-color: var(--bg-color); -> Now transparent */
        padding: 40px 20px;
        display: flex;
        width: 100%;
        gap: 20px;
        flex-wrap: wrap; 
        justify-content: center;
        font-family: 'Playfair Display', serif;
        box-sizing: border-box;
    }

    .promo-container-2x {
        padding-bottom: 0; /* Tighten gap between 2x and 3x */
    }

    /* --- Individual Card Styling --- */
    .promo-card-3x, .promo-card-2x {
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        /* Removed background-color: var(--bg-color); -> Now transparent */
        /* Initial state for animation */
        transform: translateY(100px);
        opacity: 0;
        transition: transform 0.8s ease-out, opacity 0.8s ease-out;
    }

    .promo-card-3x {
        flex: 1 1 30%; 
        min-width: 300px; 
        height: var(--card-height);
    }

    .promo-card-2x {
        flex: 1 1 48%; 
        min-width: 300px; 
        aspect-ratio: 1 / 1; /* Perfect square shape */
    }

    .promo-card-2x.yellow {
        background-color: #c3c342; /* Yellow */
    }

    .promo-card-2x.faded-white {
        background-color: #f5f5f5; /* Faded/Off-white */
    }

    /* --- Image Styling and Centering (Object-fit: cover) --- */
    .promo-card-3x img, .promo-card-2x img {
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

    /* --- Animation Trigger State (Visible State) --- */
    .promo-card-3x.visible, .promo-card-2x.visible {
        /* Final state: original position and fully visible */
        transform: translateY(0);
        opacity: 1;
    }

    /* --- Media Query for Mobile/Stacking --- */
    @media (max-width: 900px) {
        .promo-container-3x, .promo-container-2x {
            gap: 40px;
        }
        .promo-card-3x, .promo-card-2x {
            flex: 1 1 100%; 
            min-width: unset;
        }
        .promo-card-2x {
            aspect-ratio: unset;
            height: var(--card-height);
        }
    }
</style>

<!-- <div class="promo-container-2x">
    <a href="his" class="promo-card-2x">
        <img src="assets/images/hh-L.webp" alt="HIS">
        <div class="text-overlay-bottom">
            <h2 class="card-title-3x" style="color: #fff !important;">His</h2>
        </div>
    </a>

    <a href="her" class="promo-card-2x">
        <img src="assets/images/hh-P.webp" alt="HER'S">
        <div class="text-overlay-bottom">
            <h2 class="card-title-3x" style="color: #fff !important;">Her's</h2>
        </div>
    </a>
</div> -->

<div class="promo-container-3x">
    
    <a href="no-plus-one.php" class="promo-card-3x">
        <img src="assets/images/no-plus-one.webp" alt="NO PLUS ONE">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">NO PLUS ONE</h2>
        </div>
</a>

    <a href="the-guest-list.php" class="promo-card-3x">
        <img src="assets/images/theguestlist2.webp" alt="THE GUEST LIST">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">THE GUEST LIST</h2>
        </div>
</a>

    <a href="till-sunrise.php" class="promo-card-3x">
        <img src="assets/images/tillsunrise3.webp" alt="TILL SUNRISE">

        <div class="text-overlay-bottom">
            <h2 class="card-title-3x">TILL SUNRISE</h2>
        </div>
</a>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.promo-card-3x, .promo-card-2x');

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