<style>
    /* --------------------------------------
    FONTS & BASE LAYOUT
    -------------------------------------- */
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Dosis:wght@600&display=swap');

    .split-category-wrapper {
        display: flex;
        width: 100%;
        min-height: 100vh;
        font-family: Arial, sans-serif;
    }

    /* --------------------------------------
    LEFT PANEL: STATIC CONTENT (50% Width)
    -------------------------------------- */
    .left-content-panel {
        flex: 0 0 50%; /* Fixed 50% width */
        background-color: #6a1b1b;
        color: white;
        padding: 80px 60px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
    }

    .brand-new-arrivals {
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 10px;
        font-family: 'Dosis', sans-serif;
    }

    .brand-title {
        font-family: 'Playfair Display', serif;
        font-size: 6vw; 
        font-weight: 900;
        line-height: 0.9;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .brand-year {
        font-family: 'Playfair Display', serif;
        font-size: 5vw;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 30px;
    }

    .shop-now-btn {
        display: inline-block;
        color: white;
        text-decoration: none;
        font-weight: 600;
        padding-bottom: 5px;
        border-bottom: 2px solid white;
        letter-spacing: 2px;
        margin-bottom: 40px;
        transition: border-color 0.3s;
    }

    .brand-description {
        max-width: 450px;
        font-size: 1rem;
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .follow-us-text {
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        border-bottom: 1px solid white;
        padding-bottom: 5px;
    }

    /* --------------------------------------
    RIGHT PANEL: EXPANDING CARDS (50% Width)
    -------------------------------------- */
    .right-category-panel {
        flex: 0 0 50%; /* Fixed 50% width */
        display: flex;
        flex-direction: row;
        overflow: hidden;
        position: relative;
        /* Using a filter to detect hover on *any* card within the group */
        counter-reset: hoverState;
        --lane-width: 6%;
        --hover-dur: 400ms;
        --hover-ease: cubic-bezier(0.22, 0.61, 0.36, 1);
        
    }

    .right-category-panel::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-image: var(--active-bg);
        background-size: cover;
        background-position: center;
        z-index: 2;
        opacity: 0;
        transition: opacity var(--hover-dur) var(--hover-ease),
                    transform var(--hover-dur) var(--hover-ease),
                    filter var(--hover-dur) var(--hover-ease),
                    background-position var(--hover-dur) var(--hover-ease);
        transform: scale(1);
        filter: none;
        pointer-events: none;
    }

    .right-category-panel.hovering::before {
        opacity: 1;
        transform: scale(1.03);
        background-position: center 48%;
        filter: saturate(1.05) contrast(1.05) brightness(1.05);
    }

    .right-category-panel::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-image: linear-gradient(to right, transparent calc(33.333% - 0.5px), var(--partition-color, rgba(255,255,255,0.5)) calc(33.333%), transparent calc(33.333% + 0.5px)),
                          linear-gradient(to right, transparent calc(66.666% - 0.5px), var(--partition-color, rgba(255,255,255,0.5)) calc(66.666%), transparent calc(66.666% + 0.5px));
        z-index: 4;
        opacity: 0;
        transition: opacity var(--hover-dur) var(--hover-ease);
        pointer-events: none;
    }

    .right-category-panel.hovering::after {
        opacity: 1;
    }

    .vertical-category-card {
        flex: 1; /* Start with equal width (33.33% of the right panel) */
        min-width: 30px;
        transition: flex 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94), min-width 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94), opacity 0.3s;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: flex-end; /* Initial alignment for the bottom text */
        text-align: center;
        text-decoration: none;
        padding: 40px 20px;
        box-sizing: border-box;
    }

    /* Category Image/Background */
    .vertical-category-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        filter: grayscale(100%) brightness(40%);
        transition: filter 0.7s ease, transform 0.7s ease;
        z-index: 1;
        opacity: 0;
    }

    .vertical-category-card::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 42%;
        background-image: linear-gradient(to top, rgba(0,0,0,0.55), rgba(0,0,0,0));
        z-index: 3;
        opacity: 0;
        transition: opacity var(--hover-dur) var(--hover-ease);
        pointer-events: none;
    }

    /* --------------------------------------
    *** KEY HOVER LOGIC: FULL WIDTH & HIDE OTHERS ***
    -------------------------------------- */
    
    /* Step 1: Active card sits above dimmed lanes; overlay background is on panel */
    .vertical-category-card.is-active {
        z-index: 2;
        opacity: 1;
        background-color: transparent !important;
    }

    .vertical-category-card.is-active::after { opacity: 1; }

  
    
    
    .vertical-category-card {
        flex: 1 1 0;
    }
    
    /* When a card is NOT hovered, and *another* card IS hovered (pure CSS limitation: hard to hide siblings):
       Instead, we apply this with jQuery/JS for a perfect `display: none` effect, but for pure CSS,
       we minimize them significantly:
    */
    .right-category-panel.hovering .vertical-category-card:not(.is-active) {
        opacity: 0.25;
        z-index: 0;
        pointer-events: auto;
        transition: opacity 0.3s;
    }

    .right-category-panel.hovering .vertical-category-card:not(.is-active)::before {
        filter: grayscale(100%) brightness(25%);
        transform: none;
    }

    .right-category-panel.hovering .vertical-category-card:not(.is-active) .card-content {
        opacity: 1;
    }

    /* Image Effect on Hover */
    .vertical-category-card.is-active::before {
        opacity: 0;
        transform: none;
        filter: none;
    }
    
    /* --------------------------------------
    TEXT CONTENT - Centered at the Bottom on Hover
    -------------------------------------- */
    .card-content {
        position: relative;
        z-index: 4;
        color: white;
        transition: transform var(--hover-dur) var(--hover-ease), opacity var(--hover-dur) var(--hover-ease);
        /* Initial state is at the bottom (set by parent flex-align: flex-end) */
        align-self: flex-end; 
    }
    
    /* Hovered state: text remains at the bottom, but the size increases */
    .vertical-category-card.is-active .card-content {
        opacity: 1;
        transform: translateY(-8px);
    }

    .vertical-category-card.is-active .card-link {
        transform: translateY(-2px);
        transition: transform var(--hover-dur) var(--hover-ease), opacity var(--hover-dur) var(--hover-ease);
    }

    .card-title {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1.1;
        text-transform: uppercase;
        color: inherit;
        white-space: nowrap; 
    }
    
    .vertical-category-card.is-active .card-title {
        font-size: clamp(1rem, 3.2vw, 1.8rem);
        white-space: normal;
        max-width: 100%;
        overflow-wrap: anywhere;
    }

    .card-link {
        display: inline-block;
        margin-top: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 2px solid currentColor;
        padding-bottom: 5px;
        color: inherit;
        text-decoration: none;
        opacity: 1;
        
    }

   

    .lane-overlay-content {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 40px;
        z-index: 5;
        color: #fff;
        text-align: center;
        pointer-events: none;
        padding: 0 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .right-category-panel.hovering .lane-overlay-content { opacity: 1; }

    .lane-overlay-title {
        font-family: 'Playfair Display', serif;
        font-size: 4.5rem;
        font-weight: 700;
        line-height: 1.1;
        text-transform: uppercase;
    }

    .lane-overlay-link {
        display: inline-block;
        margin-top: 10px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 2px solid currentColor;
        padding-bottom: 5px;
        color: inherit;
        text-decoration: none;
        pointer-events: auto;
    }

    /* INDIVIDUAL CARD STYLES AND IMAGES */
    .card-dresses { background-color: #A3434B; color: #f7e0e0; }
    .card-dresses::before { background-image: url('https://picsum.photos/id/101/1000/1600'); }

    .card-resort { background-color: #556c80; color: #d8e5f2; }
    .card-resort::before { background-image: url('https://picsum.photos/id/102/1000/1600'); }

    .card-twins { background-color: #8c7880; color: #f2e3e9; }
    .card-twins::before { background-image: url('https://picsum.photos/id/103/1000/1600'); }

    /* --------------------------------------
    RESPONSIVE DESIGN (Stacking on Mobile)
    -------------------------------------- */
    @media screen and (max-width: 992px) {
        .split-category-wrapper { flex-direction: column; }
        .left-content-panel { flex: auto; width: 100%; padding: 60px 40px; text-align: center; align-items: center; }
        .brand-title, .brand-year { font-size: 10vw; line-height: 1; }
        .right-category-panel { flex: auto; width: 100%; min-height: 50vh; flex-direction: column; }
        
        /* Disable expansion and hide effects on small screens */
        .vertical-category-card { flex: 1; min-height: 150px; min-width: 100%; opacity: 1 !important; padding: 20px; }
        .right-category-panel:hover .vertical-category-card:not(:hover) { 
            flex: 1; min-width: 100%; opacity: 1; 
        }
        .vertical-category-card .card-content { align-self: flex-end; }
        .vertical-category-card::before { opacity: 0 !important; }
        .right-category-panel::before { display: none !important; }
        .hover-text-overlay { display: none; }
        .vertical-category-card .card-title { font-size: 2rem; }
        .vertical-category-card:hover .card-title { font-size: 2.2rem; }
    }
</style>

<section class="split-category-wrapper">
    
    <div class="left-content-panel">
        <span class="brand-new-arrivals">NEW ARRIVALS</span>
        <h1 class="brand-title">BARE ESSENCE</h1>
        <h1 class="brand-year">2025</h1>
        
        <a href="/shop-now" class="shop-now-btn">SHOP NOW</a>

        <p class="brand-description">
            Our new collection has landed—where bold design meets everyday magic. Think fresh cuts, rich colors, and the kind of details that make you look twice.
            <br><br>
            Explore the new. Embrace the now
        </p>

        <span class="follow-us-text">FOLLOW US —</span>
    </div>
    
    <div class="right-category-panel">
        
        <a href="/category/dresses" class="vertical-category-card card-dresses" data-bg="url('https://mld1qxcswytz.i.optimole.com/cb:G13G.df4/w:auto/h:auto/q:mauto/f:best/https://worldofinanna.org/wp-content/uploads/2025/05/20250424_144510_164-scaled.jpg')">
            <div class="card-content">
                <h2 class="card-title">DRESSES</h2>
                <span class="card-link">BROWSE ALL</span>
            </div>
        </a>
        
        <a href="/category/resort-wear" class="vertical-category-card card-resort" data-bg="url('https://picsum.photos/id/102/1000/1600')">
            <div class="card-content">
                <h2 class="card-title">RESORT WEAR</h2>
                <span class="card-link">BROWSE ALL</span>
            </div>
        </a>
        
        <a href="/category/the-twins" class="vertical-category-card card-twins" data-bg="url('https://picsum.photos/id/103/1000/1600')">
            <div class="card-content">
                <h2 class="card-title">THE TWINS</h2>
                <span class="card-link">BROWSE ALL</span>
            </div>
        </a>


        
        
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var panel = document.querySelector('.right-category-panel');
  if (!panel) return;
  var cards = Array.prototype.slice.call(panel.querySelectorAll('.vertical-category-card'));
  

  function setActive(card) {
    cards.forEach(function(c){ c.classList.remove('is-active'); });
    if (card) {
      card.classList.add('is-active');
      var bg = card.getAttribute('data-bg');
      if (bg) panel.style.setProperty('--active-bg', bg);
      
      
      panel.classList.add('hovering');
    } else {
      panel.classList.remove('hovering');
      panel.style.removeProperty('--active-bg');
      
      
    }
  }

  cards.forEach(function(card){
    card.addEventListener('mouseenter', function(){ setActive(card); });
    card.addEventListener('focus', function(){ setActive(card); });
  });

  panel.addEventListener('mouseleave', function(){ setActive(null); });
});
</script>
