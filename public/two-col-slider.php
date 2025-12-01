<!-- ===================== HTML ===================== -->

<section class="product-gallery-section">
    <div class="left-image-wrapper">
        <img id="largeImage" src="" alt="Large Product Image">
    </div>

    <div class="right-image-wrapper">
        <div class="right-image-content">
            <img id="smallImage" src="" alt="Small Product Image">
            <div class="slider-controls">
                <button id="prevSlide" class="slider-button disabled"></button>
                <button id="nextSlide" class="slider-button"></button>
            </div>
        </div>
    </div>
</section>


<!-- ===================== CSS ===================== -->

<style>
/* Main wrapper with perfect equal height */
.product-gallery-section {
    width: 100%;
    max-width: 1424px;
    margin: 50px auto;
    display: flex;
    background-color: white;
    height: 850px;
    overflow: hidden;
}

/* LEFT IMAGE SECTION */
.left-image-wrapper {
    width: 700px;
    height: 850px;
    overflow: hidden;
    background-color: #F8F8F8;
    position: relative;
}

.left-image-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover; 
    transition: opacity 0.4s ease-in-out;
    position:absolute;
    left:0;
    top:0;
    will-change: opacity;
}

/* RIGHT IMAGE SECTION */
.right-image-wrapper {
    width:700px;
    padding: 40px;
    background-color: #13adbd;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-sizing: border-box;
    }

.right-image-content {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    position: relative;
    
    
}

.right-image-content img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.5s ease, opacity 0.4s ease-in-out;
    will-change: transform, opacity;
}

/* Slider Buttons */
.slider-controls {
    position: absolute;
    bottom: 12px;
    left: 35px;
    right: 35px;
    padding: 0 6px;
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.slider-button {
    background: transparent;
    border: none;
    width: 48px;
    height: 16px;
    font-size: 0;
    cursor: pointer;
    position: relative;
}

.slider-button::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 10px;
    right: 10px;
    height: 2px;
    background: rgba(255,255,255,0.95);
    transform: translateY(-50%);
    border-radius: 2px;
    filter: drop-shadow(0 0 2px rgba(0,0,0,0.6));
}

#prevSlide::after {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    width: 0; height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-right: 8px solid rgba(255,255,255,0.95);
    transform: translateY(-50%);
    filter: drop-shadow(0 0 2px rgba(0,0,0,0.6));
}

#nextSlide::after {
    content: "";
    position: absolute;
    right: 0;
    top: 50%;
    width: 0; height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-left: 8px solid rgba(255,255,255,0.95);
    transform: translateY(-50%);
    filter: drop-shadow(0 0 2px rgba(0,0,0,0.6));
}

.slider-button:hover:not(.disabled) {
    background-color: #e0e0e0;
}

.slider-button.disabled {
    opacity: 1;
    cursor: default;
}

/* ================= MOBILE STYLING ================= */
@media (max-width: 992px) {
    .product-gallery-section {
        flex-direction: column;
        height: auto;
    }

    .left-image-wrapper {
        height: 420px;
        width: 100%;
        flex: 0 0 auto;
    }

    .right-image-wrapper {
        height: auto;
        padding: 16px;
        width: 100%;
        flex: 0 0 auto;
    }

    .right-image-content {
        height: 420px;
        max-height: none;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
    }

    .slider-controls {
        bottom: 10px;
        gap:8rem;
        align-items: center;
        justify-content: center;
    }
}
</style>

<style>
.left-image-wrapper .large-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transform: translateX(100%) scale(0.98);
    transition: transform 0.6s ease;
    will-change: transform;
}
/* small overlay for right image slider */
.right-image-content .small-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    transform: translateX(100%) scale(0.98);
    transition: transform 0.6s ease;
    will-change: transform;
}
</style>


<!-- ===================== JS ===================== -->

<script>
document.addEventListener("DOMContentLoaded", () => {

    /* -----------------------------------------------------
       JUST EDIT THESE IMAGE PAIRS BELOW ⬇️
       ----------------------------------------------------- */
    const sliderImages = [
        {
            large: "uploads/large.jpg",
            small: "uploads/small.jpg",
            altLarge: "First Large",
            altSmall: "First Small"
        },
        {
            large: "uploads/medium.jpg",
            small: "uploads/large.jpg",
            altLarge: "Second Large",
            altSmall: "Second Small"
        },
        {
            large: "uploads/large.jpg",
            small: "uploads/small.jpg",
            altLarge: "Third Large",
            altSmall: "Third Small"
        },
        {
            large: "uploads/medium.jpg",
            small: "uploads/large.jpg",
            altLarge: "Fourth Large",
            altSmall: "Fourth Small"
        }
    ];
    /* ----------------------------------------------------- */

    const largeImage = document.getElementById("largeImage");
    const smallImage = document.getElementById("smallImage");
    const nextBtn = document.getElementById("nextSlide");
    const prevBtn = document.getElementById("prevSlide");
    const rightContent = document.querySelector('.right-image-content');
    let overlay = document.createElement('img');
    overlay.className = 'large-overlay';
    largeImage.parentElement.appendChild(overlay);
    let smallOverlay = document.createElement('img');
    smallOverlay.className = 'small-overlay';
    rightContent.appendChild(smallOverlay);
    const cache = {};
    function preload(src){
        if(cache[src]) return Promise.resolve(cache[src]);
        return new Promise(res=>{ const img=new Image(); img.onload=()=>{ cache[src]=img; res(img); }; img.src=src; });
    }

    let index = 0;
    let animating = false;

    async function updateSlider(dir) {
        if (animating && dir) return;
        const s = sliderImages[index];
        if (!dir) {
            largeImage.src = s.large;
            largeImage.alt = s.altLarge;
            smallImage.src = s.small;
            smallImage.alt = s.altSmall;
            prevBtn.classList.toggle("disabled", index === 0);
            nextBtn.classList.toggle("disabled", index === sliderImages.length - 1);
            return;
        }
        animating = true;
        await preload(s.large);
        overlay.src = s.large;
        overlay.alt = s.altLarge;
        overlay.style.transform = dir === 'prev' ? 'translateX(-100%) scale(0.98)' : 'translateX(100%) scale(0.98)';
        requestAnimationFrame(() => { overlay.style.transform = 'translateX(0) scale(1)'; });
        overlay.addEventListener('transitionend', function onEnd(){
            overlay.removeEventListener('transitionend', onEnd);
            largeImage.src = s.large;
            largeImage.alt = s.altLarge;
            overlay.style.transition = 'none';
            overlay.style.transform = 'translateX(100%) scale(0.98)';
            void overlay.offsetWidth;
            overlay.style.transition = 'transform 0.6s ease';
        });

        await preload(s.small);
        smallOverlay.src = s.small;
        smallOverlay.alt = s.altSmall;
        smallOverlay.style.transform = dir === 'prev' ? 'translateX(100%) scale(0.98)' : 'translateX(-100%) scale(0.98)';
        requestAnimationFrame(() => { smallOverlay.style.transform = 'translateX(0) scale(1)'; });
        smallOverlay.addEventListener('transitionend', function onEndSmall(){
            smallOverlay.removeEventListener('transitionend', onEndSmall);
            smallImage.src = s.small;
            smallImage.alt = s.altSmall;
            smallOverlay.style.transition = 'none';
            smallOverlay.style.transform = 'translateX(100%) scale(0.98)';
            void smallOverlay.offsetWidth;
            smallOverlay.style.transition = 'transform 0.6s ease';
            animating = false;
        });

        prevBtn.classList.toggle("disabled", index === 0);
        nextBtn.classList.toggle("disabled", index === sliderImages.length - 1);
    }

    nextBtn.addEventListener("click", () => {
        if (animating || index >= sliderImages.length - 1) return;
        if (index < sliderImages.length - 1) {
            index++;
            updateSlider('next');
        }
    });

    prevBtn.addEventListener("click", () => {
        if (animating || index <= 0) return;
        if (index > 0) {
            index--;
            updateSlider('prev');
        }
    });

    updateSlider();
});
</script>
