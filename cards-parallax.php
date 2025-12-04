<!-- ===================== GSAP CDN ===================== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/Draggable.min.js"></script>

<!-- ===================== PARALLAX SHOWCASE SECTION ===================== -->

<section class="parallax-showcase">

    <div class="ps-left">
        <h2>New Season<br>New Looks</h2>
        <p>
            Discover the new arrivals — effortless, modern and timeless styles crafted for you.
        </p>
        <button class="ps-btn">SHOP THE COLLECTION</button>

        
    </div>

    <!-- PARALLAX CARD TRACK -->
    <div class="ps-right">
        <div class="ps-track">

            <!-- 6 CARDS -->
            <div class="ps-card"><img src="uploads/large.jpg"></div>
            <div class="ps-card"><img src="uploads/look2.jpg"></div>
            <div class="ps-card"><img src="uploads/look3.jpg"></div>
            <div class="ps-card"><img src="uploads/look4.jpg"></div>
            <div class="ps-card"><img src="uploads/look5.jpg"></div>
            <div class="ps-card"><img src="uploads/look6.jpg"></div>

        </div>

        <!-- ARROWS -->
        <div class="ps-arrows">
            <button class="ps-prev">←</button>
            <button class="ps-next">→</button>
        </div>

        <!-- PROGRESS -->
        <div class="ps-progress"><span></span></div>

    </div>

</section>

<style>
/* ===================== LAYOUT ===================== */

.parallax-showcase {
    width: 100%;
    padding: 80px 40px;
    display: flex;
    align-items: center;
    gap: 60px;
    background: #f3f3f3;
}

.ps-left {
    flex: 1;
}

.ps-left h2 {
    font-size: 42px;
    line-height: 1.3;
    margin-bottom: 20px;
}

.ps-left p {
    max-width: 320px;
    opacity: 0.7;
    line-height: 1.6;
}

.ps-btn {
    margin-top: 20px;
    padding: 12px 28px;
    border: 1px solid #000;
    background: #fff;
    cursor: pointer;
}

.ps-share {
    margin-top: 40px;
    opacity: 0.7;
}

/* ===================== CARD TRACK ===================== */

.ps-right {
    flex: 2;
    position: relative;
    overflow: hidden;
    height: 420px;
}

.ps-track {
    display: flex;
    gap: 40px;
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
}

.ps-card {
    width: 260px;
    height: 350px;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 18px 40px rgba(0,0,0,0.15);
    cursor: grab;
}

.ps-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ===================== ARROWS ===================== */

.ps-arrows {
    position: absolute;
    bottom: -50px;
    right: 10px;
    display: flex;
    gap: 20px;
}

.ps-arrows button {
    background: #fff;
    border: 1px solid #000;
    padding: 8px 18px;
    cursor: pointer;
}

/* ===================== PROGRESS BAR ===================== */

.ps-progress {
    width: 300px;
    height: 2px;
    background: #ddd;
    position: absolute;
    bottom: -50px;
    left: 50%;
    transform: translateX(-50%);
}

.ps-progress span {
    width: 40px;
    height: 100%;
    background: #000;
    display: block;
    transition: width 0.4s ease;
}

/* ===================== MOBILE ===================== */

@media (max-width: 768px) {
    .parallax-showcase {
        flex-direction: column;
        height: auto;
    }
    .ps-right { width: 100%; }
    .ps-track { gap: 20px; }
    .ps-card { width: 200px; height: 280px; }
}
</style>

<script>
/* ===========================================================
   GSAP Parallax Slider
   =========================================================== */

const track = document.querySelector(".ps-track");
const cards = document.querySelectorAll(".ps-card");
const nextBtn = document.querySelector(".ps-next");
const prevBtn = document.querySelector(".ps-prev");
const progress = document.querySelector(".ps-progress span");

let cardWidth = 300; // card + gap
let index = 0;
let maxIndex = cards.length - 1;

/* ------------------ Infinite GSAP Slider (no hover tilt) ------------------ */

const originalCards = Array.from(cards);

// Measure dynamic card stride (width + gap) from layout
function measureStride() {
  if (originalCards.length < 2) return 300;
  const r1 = originalCards[0].getBoundingClientRect();
  const r2 = originalCards[1].getBoundingClientRect();
  return Math.round(r2.left - r1.left);
}
cardWidth = measureStride();

// Clone to both ends for seamless loop
const prependClones = originalCards.map(c => c.cloneNode(true));
prependClones.forEach(c => track.insertBefore(c, track.firstChild));
const appendClones = originalCards.map(c => c.cloneNode(true));
appendClones.forEach(c => track.appendChild(c));

const allCards = Array.from(track.children);
const total = originalCards.length;

// Start centered on the original set
let visualIndex = total; // index within allCards
let logicalIndex = 0;    // 0..total-1
gsap.set(track, { x: -visualIndex * cardWidth });

// Parallax state
function applyParallax() {
  const center = visualIndex + 1; // approximate center
  allCards.forEach((card, i) => {
    const d = Math.abs(i - center);
    const y = Math.min(d * 6, 24);
    const s = d === 0 ? 1 : 0.97;
    const o = d > 5 ? 0.35 : 0.8;
    gsap.to(card, { y, scale: s, opacity: o, duration: 0.6, ease: "power2.out" });
  });
}

function updateProgress() {
  const pct = ((logicalIndex + 1) / total) * 100;
  progress.style.width = pct + "%";
}

let animating = false;
function go(delta) {
  if (animating) return;
  animating = true;
  visualIndex += delta;
  logicalIndex = (logicalIndex + delta + total) % total;
  gsap.to(track, {
    x: -visualIndex * cardWidth,
    duration: 0.9,
    ease: "power3.inOut",
    onUpdate: applyParallax,
    onComplete: () => {
      // Snap back into middle block when crossing edges
      if (visualIndex >= total * 2) visualIndex -= total;
      if (visualIndex < total) visualIndex += total;
      gsap.set(track, { x: -visualIndex * cardWidth });
      applyParallax();
      updateProgress();
      animating = false;
    }
  });
}

nextBtn.onclick = () => go(1);
prevBtn.onclick = () => go(-1);

// Auto-slide every 4s (pauses during animation)
setInterval(() => { if (!animating) go(1); }, 4000);

// Touch swipe for mobile
let startX = 0;
track.addEventListener("touchstart", e => { startX = e.touches[0].clientX; }, { passive: true });
track.addEventListener("touchmove", e => {
  const diff = e.touches[0].clientX - startX;
  if (diff > 80) { go(-1); startX = e.touches[0].clientX; }
  if (diff < -80) { go(1); startX = e.touches[0].clientX; }
}, { passive: true });

// Initial state
applyParallax();
updateProgress();
</script>
