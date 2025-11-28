<!-- ======================================
     DRIBBBLE STYLE FASHION SLIDER
======================================= -->

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<style>
    .fashion-slider {
       
        max-width: 100%;
        height: 840px;
        border-radius: 20px;
        overflow: hidden;
        background: #f7f7f7;
    }

    .fashion-slide {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 70px;
    }

    .fashion-text {
        max-width: 45%;
    }

    .fashion-text h1 {
        font-size: 58px;
        font-weight: 700;
        margin-bottom: 20px;
        line-height: 1.1;
    }

    .fashion-text p {
        font-size: 20px;
        opacity: 0.8;
        margin-bottom: 25px;
    }

    .fashion-btn {
        padding: 12px 32px;
        background: #000;
        color: #fff;
        text-decoration: none;
        border-radius: 50px;
        font-size: 18px;
    }

    .fashion-img {
        width: 50%;
        height: 100%;
        object-fit: cover;
        border-radius: 20px;
    }

    .swiper-pagination-bullet {
        background: #000 !important;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #000 !important;
    }

    @media (max-width: 768px) {
        .fashion-slide {
            flex-direction: column;
            padding: 40px 20px;
            text-align: center;
        }
        .fashion-text {
            max-width: 90%;
        }
        .fashion-img {
            width: 100%;
            height: auto;
            margin-top: 20px;
        }
        .fashion-text h1 {
            font-size: 36px;
        }
    }
</style>


<div class="container d-flex justify-content-center mt-0">
  <div class="swiper fashion-slider">

    <div class="swiper-wrapper">

      <!-- Slide 1 -->
      <div class="swiper-slide fashion-slide">
        <div class="fashion-text">
          <h1>Minimal Street Fashion</h1>
          <p>Explore curated looks that blend comfort and luxury.</p>
          <a href="#" class="fashion-btn">Shop Now</a>
        </div>
        <img src="https://images.unsplash.com/photo-1520975918318-d50340cadfcc" class="fashion-img" />
      </div>

      <!-- Slide 2 -->
      <div class="swiper-slide fashion-slide">
        <div class="fashion-text">
          <h1>Luxury Women's Wear</h1>
          <p>A new era of elegance and bold silhouettes.</p>
          <a href="#" class="fashion-btn">Discover</a>
        </div>
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d" class="fashion-img" />
      </div>

      <!-- Slide 3 -->
      <div class="swiper-slide fashion-slide">
        <div class="fashion-text">
          <h1>Urban Winter Essentials</h1>
          <p>Style meets comfort in our winter edit.</p>
          <a href="#" class="fashion-btn">View Collection</a>
        </div>
        <img src="https://images.unsplash.com/photo-1512436991641-6745cdb1723f" class="fashion-img" />
      </div>

    </div>

    <!-- Pagination -->
    <div class="swiper-pagination"></div>

    <!-- Navigation Arrows -->
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>

  </div>
</div>


<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  var swiper = new Swiper(".fashion-slider", {
    loop: true,
    autoplay: {
      delay: 2600,
      disableOnInteraction: false,
    },
    speed: 900,
    effect: "fade",
    fadeEffect: {
      crossFade: true,
    },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
  });
</script>
