<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root { 
    --font-primary: 'Playfair Display', serif;
    --text-color: #333;
}

.expressive-wrapper {
    padding: 60px 0;
    text-align: center;
    font-family: var(--font-primary);
    color: var(--text-color);
}

.expressive-title {
    font-size: 2.5rem;
    letter-spacing: 5px;
    margin-bottom: 5px;
}

.expressive-subtitle {
    font-size: 1.2rem;
    letter-spacing: 12px;
    color: #555;
    margin-bottom: 50px;
}

.expressive-gallery {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 15px;
    flex-wrap: nowrap;
    position: relative;
}

.expressive-card {
    overflow: hidden;
    border-radius: 3px;
    transition: 0.3s ease;
}

.small { width: 170px; height: 250px; margin-bottom:5.6rem; }
.medium { width: 220px; height: 350px; margin-bottom:4rem;}
.large { width: 320px; height: 500px; }

.expressive-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ---------------- MOBILE STYLING ---------------- */
@media (max-width: 768px) {

    /* Show only first 3 cards */
    .expressive-gallery .expressive-card:nth-child(4),
    .expressive-gallery .expressive-card:nth-child(5) {
        display: none;
    }

    .expressive-gallery {
        justify-content: center;
        gap: 10px;
        flex-wrap: nowrap;
        align-items: flex-end;
    }

    /* LEFT (small) */
    .expressive-gallery .expressive-card:nth-child(1) {
        width: 95px;
        height: 135px;
        order: 1;
        margin-bottom:2rem;
    }

    /* CENTER (large) */
    .expressive-gallery .expressive-card:nth-child(3) {
        width: 150px;
        height: 240px;
        order: 2;
    }

    /* RIGHT (small)  */
    .expressive-gallery .expressive-card:nth-child(2) {
        width: 95px;
        height: 135px;
        order: 3;
        margin-bottom:2rem;
    }
}
</style>

<section class="expressive-wrapper">
    <h2 class="expressive-title">EXPRESSIVE</h2>
    <h3 class="expressive-subtitle">TIMELESS ELEGANT</h3>

    <div class="expressive-gallery">

        <div class="expressive-card small">
            <img src="assets/images/noplus/img1.webp" alt="">
        </div>

        <div class="expressive-card medium">
            <img src="assets/images/noplus/img2.webp" alt="">
        </div>

        <div class="expressive-card large">
            <img src="assets/images/tillsunrise/img1.avif" alt="">
        </div>

        <div class="expressive-card medium">
            <img src="assets/images/theguestlist/img-3.webp" alt="">
        </div>

        <div class="expressive-card small">
            <img src="assets/images/tillsunrise/img6.webp" alt="">
        </div>

    </div>
</section>