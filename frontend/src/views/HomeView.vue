<<<<<<< HEAD
﻿<template>
  <main class="home-page">
=======
<template>
  <div class="home-page">
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
    <header class="top-nav">
      <div class="brand-container">
        <div class="brand-mark" aria-hidden="true">
          <img class="brand-logo" :src="logoUrl" alt="" />
        </div>
        <div class="brand-text">
          <div class="brand" aria-label="Chong Choul">
            <span class="brand-main">CHONG</span>
            <span class="brand-accent">CHOUL</span>
          </div>
          <div class="brand-tagline">Vehicle Services</div>
        </div>
      </div>

      <div class="nav-auth">
        <RouterLink class="link-login" to="/login">Login</RouterLink>
        <span class="nav-sep" aria-hidden="true">|</span>
        <RouterLink class="btn-signup" to="/chooserole">Sign Up</RouterLink>
      </div>
    </header>

    <section class="hero">
<<<<<<< HEAD
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>
            <span class="hero-line">Find Your Perfect Ride and Explore</span>
            <span class="hero-line"><span>Cambodia</span> in Style!</span>
          </h1>
          <p class="hero-sub">
            Rent motorbikes, cars, and bicycles from trusted local shops—fast booking, fair prices, and flexible plans.
          </p>
          <div class="hero-actions">
            <RouterLink class="hero-btn primary" to="/view_shop">Explore Vehicles</RouterLink>
            <RouterLink class="hero-btn ghost" to="/register">List Your Shop</RouterLink>
=======
      <div class="hero-slider">
        <div class="hero-containers">
          <div class="hero-slide">
            <article
              v-for="(slide, index) in heroOrderedSlides"
              :key="slide.id"
              :class="['hero-item', { 'hero-item--active': index === 0 }]"
              :style="`background-image: url('${slide.image}')`"
            >
              <div class="hero-item-content">
                <p class="hero-item-location">{{ slide.location }}</p>
                <h1>{{ slide.title }}</h1>
                <p class="hero-item-make">{{ slide.brand }} · {{ slide.model }}</p>
                <p>{{ slide.description }}</p>
                <div class="hero-item-actions">
                  <RouterLink class="hero-slide-button" to="/view_shop">Browse Shops</RouterLink>
                  <a
                    v-if="slide.link"
                    class="hero-model-link"
                    :href="slide.link"
                    target="_blank"
                    rel="noreferrer"
                  >
                    View {{ slide.brand }} lineup
                  </a>
                </div>
              </div>
            </article>
          </div>
          <div class="hero-slider-controls">
            <button
              class="hero-slider-btn"
              type="button"
              aria-label="Previous slide"
              @click="handlePrevHeroSlide"
            >
              ◀
            </button>
            <button
              class="hero-slider-btn"
              type="button"
              aria-label="Next slide"
              @click="handleNextHeroSlide"
            >
              ▶
            </button>
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section-head section-head-center">
        <div>
          <h2>Browse by Category</h2>
          <p>Find the right ride for your journey</p>
        </div>

      </div>

      <div class="category-grid">
        <article v-for="item in categories" :key="item.title" class="category-card">
          <img :src="item.image" :alt="item.title" />
          <div class="card-overlay"></div>
          <div class="card-text">
            <span>{{ item.tag }}</span>
            <h3>{{ item.title }}</h3>
            <p>{{ item.availability }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="features">
      <div class="features-inner">
        <div class="trending-head">
          <h2>Browse by Category</h2>
        </div>
        <div class="features-divider"></div>
        <div class="features-grid">
          <article v-for="feature in features" :key="feature.title" class="feature-item">
            <div class="feature-content">
              <div class="feature-icon" aria-hidden="true">
                <span>{{ feature.icon }}</span>
              </div>
              <div class="feature-copy">
                <h4>
                  <span class="feature-step">{{ feature.step }}</span>
                  <span class="feature-title-text">{{ feature.title }}</span>
                </h4>
                <p>{{ feature.description }}</p>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="trending-head">
        <h2>Trending Rides</h2>
      </div>
      <div class="ride-grid">
        <article v-for="ride in rides" :key="ride.name" class="ride-card">
          <img :src="ride.image" :alt="ride.name" />
          <div class="ride-body">
            <h4>{{ ride.name }}</h4>
            <p>{{ ride.detail }}</p>
            <div class="ride-bottom">
              <span class="rating"><i class="fa-solid fa-star" aria-hidden="true"></i> {{ ride.rating }}</span>
              <span><strong>${{ ride.price }}</strong> /day</span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="section cta-wrap">
      <div class="cta-card">
        <div class="cta-text">
          <h2>List Your Shop &amp; Start Earning Today</h2>
          <p>
            Join Cambodia's largest vehicle rental network and connect with more
            customers.
          </p>
          <RouterLink to="/register"><button type="button">Register Your Shop</button></RouterLink>
        </div>
        <div class="cta-device"></div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-main">
        <div class="footer-brand">
          <div class="footer-brand-head">
            <div class="footer-logo-wrap" aria-hidden="true">
              <img class="footer-logo" :src="logoUrl" alt="" />
            </div>
            <div class="footer-brand-text">
              <div class="footer-brand-name" aria-label="Chong Choul">
                <span class="brand-main">CHONG</span>
                <span class="brand-accent">CHOUL</span>
              </div>
              <div class="footer-brand-sub">Vehicle Services</div>
            </div>
          </div>
          <p class="footer-desc">The premier vehicle rental marketplace in Cambodia.</p>
          <div class="footer-social" aria-label="Social media">
            <a class="footer-social-link" href="#" @click.prevent aria-label="Facebook">
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="Instagram">
              <i class="fa-brands fa-instagram" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="TikTok">
              <i class="fa-brands fa-tiktok" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="X">
              <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
            </a>
          </div>
          <small class="footer-legal">© 2026 Chong Choul. All rights reserved.</small>
        </div>
        <div class="footer-col">
          <h5 class="footer-title">Payment Methods</h5>
          <ul class="footer-list">
            <li><span>Visa</span></li>
            <li><span>Mastercard</span></li>
            <li><span>Bank Transfer</span></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5 class="footer-title">Quick Links</h5>
          <ul class="footer-list">
            <li><RouterLink class="footer-link" to="/">Home</RouterLink></li>
            <li><RouterLink class="footer-link" to="/view_shop">Explore Vehicles</RouterLink></li>
            <li><RouterLink class="footer-link" to="/register">List Your Shop</RouterLink></li>
            <li><RouterLink class="footer-link" to="/login">Login</RouterLink></li>
            <li><RouterLink class="footer-link" to="/chooserole">Sign Up</RouterLink></li>
          </ul>
        </div>
        <div class="footer-col">
          <h5 class="footer-title">Newsletter</h5>
          <p class="footer-note">Stay updated with the latest offers.</p>
          <form class="newsletter" @submit.prevent>
            <input type="email" placeholder="Your email" autocomplete="email" />
            <button type="submit">Send</button>
          </form>
        </div>
      </div>
    </footer>
  </main>
</template>

<<<<<<< HEAD
<script>
 import logoUrl from '@/assets/logo-removebg.png';

export default {
  data() {
    return {
      logoUrl,
      search: {
        type: 'All Vehicles',
        location: 'Phnom Penh',
        pickupDate: ''
      },
      categories: [
        {
          tag: 'CITY',
          title: 'Cars',
          availability: '120+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/76/4d/1e/764d1e19a2fb69a9046e53ceb4381391.jpg'
        },
        {
          tag: 'POPULAR',
          title: 'Motorbikes',
          availability: '450+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/b3/a3/84/b3a384d5a8624aba2943bf7d41edd5e2.jpg'
        },
        {
          tag: 'ECO',
          title: 'Bicycles',
          availability: '85+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/9d/a8/87/9da8873b9c5bfdc2ac0dd4915e594d02.jpg'
        }
      ],
      features: [
        {
          icon: 'Q',
          step: '1.',
          title: 'Browse & Select',
          description: 'Filter by vehicle type, location, and your travel dates to find the best match.'
        },
        {
          icon: 'C',
          step: '2.',
          title: 'Secure Booking',
          description: 'Book instantly using ABA, Wing, or international cards. No hidden fees.'
        },
        {
          icon: 'K',
          step: '3.',
          title: 'Pick Up & Go',
          description: 'Meet your host at the shop or get the vehicle delivered to your hotel.'
        }
      ],
      rides: [
        {
          name: 'Small Camper Van/RV',
          detail: 'Small Camper Van/RV - Siem Reap',
          rating: '4.9',
          price: 120,
          image: 'https://i.pinimg.com/1200x/aa/4f/54/aa4f548f44f2ad9a5917c99bb85ad06a.jpg'
        },
        {
          name: 'Adventure Touring Motorcycle',
          detail: 'Motorbike - Siem Reap',
          rating: '4.8',
          price: 12,
          image: 'https://i.pinimg.com/1200x/29/d1/13/29d11367c93335cdd7232c6f0594c344.jpg'
        },
        {
          name: 'Road Bicycle',
          detail: 'Bicycle - Siem Reap',
          rating: '4.7',
          price: 5,
          image: 'https://i.pinimg.com/1200x/02/32/61/0232617f4158002b9f542b15f113cd9b.jpg'
        },
        {
          name: 'Step-through City Bicycle',
          detail: 'Bicycle - Siem Reap',
          rating: '5.0',
          price: 25,
          image: 'https://i.pinimg.com/1200x/26/88/ba/2688ba0b802e2a168450e8f598ddc8d9.jpg'
        }
      ]
    };
  }
};
=======
<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const categories = [
  {
    title: 'Cars',
    availability: '120+ Vehicles Available',
    image: 'https://i.pinimg.com/1200x/76/4d/1e/764d1e19a2fb69a9046e53ceb4381391.jpg'
  },
  {
    title: 'Motorbikes',
    availability: '450+ Vehicles Available',
    image: 'https://i.pinimg.com/1200x/b3/a3/84/b3a384d5a8624aba2943bf7d41edd5e2.jpg'
  },
  {
    title: 'Bicycles',
    availability: '85+ Vehicles Available',
    image: 'https://i.pinimg.com/1200x/9d/a8/87/9da8873b9c5bfdc2ac0dd4915e594d02.jpg'
  }
]

const features = [
  {
    icon: 'Q',
    step: '1.',
    title: 'Browse & Select',
    description: 'Filter by vehicle type, location, and your travel dates to find the best match.'
  },
  {
    icon: 'C',
    step: '2.',
    title: 'Secure Booking',
    description: 'Book instantly using ABA, Wing, or international cards. No hidden fees.'
  },
  {
    icon: 'K',
    step: '3.',
    title: 'Pick Up & Go',
    description: 'Meet your host at the shop or get the vehicle delivered to your hotel.'
  }
]

const rides = [
  {
    name: 'Small Camper Van/RV',
    detail: 'Small Camper Van/RV - Siem Reap',
    rating: '4.9',
    price: 120,
    image: 'https://i.pinimg.com/1200x/aa/4f/54/aa4f548f44f2ad9a5917c99bb85ad06a.jpg'
  },
  {
    name: 'Adventure Touring Motorcycle',
    detail: 'Motorbike - Siem Reap',
    rating: '4.8',
    price: 12,
    image: 'https://i.pinimg.com/1200x/29/d1/13/29d11367c93335cdd7232c6f0594c344.jpg'
  },
  {
    name: 'Road Bicycle',
    detail: 'Bicycle - Siem Reap',
    rating: '4.7',
    price: 5,
    image: 'https://i.pinimg.com/1200x/02/32/61/0232617f4158002b9f542b15f113cd9b.jpg'
  },
  {
    name: 'Step-through City Bicycle',
    detail: 'Bicycle - Siem Reap',
    rating: '5.0',
    price: 25,
    image: 'https://i.pinimg.com/1200x/26/88/ba/2688ba0b802e2a168450e8f598ddc8d9.jpg'
  }
]

const heroSlides = [
  {
    id: 1,
    title: 'Angkor Temple Patrol',
    brand: 'Toyota',
    model: 'Land Cruiser Prado',
    location: 'Angkor Wat, Siem Reap',
    description: 'The Land Cruiser Prado glides through the temple gates with 4WD grip and chilled interior climate.',
    image: 'https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1600&q=80',
    link: 'https://global.toyota/en/mobility/toyota-brand/gallery/'
  },
  {
    id: 2,
    title: 'Riverside Whisper Run',
    brand: 'BYD',
    model: 'Atto 3',
    location: 'Phnom Penh Riverside',
    description: 'BYD's Atto 3 glows beside the Mekong skyline, ensuring a quiet, electric cruise through Phnom Penh.',
    image: 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1600&q=80',
    link: 'https://www.byd.com/en/models'
  },
  {
    id: 3,
    title: 'Lexus Elevated Drive',
    brand: 'Lexus',
    model: 'NX 350h',
    location: 'Mondulkiri Highlands',
    description: 'The NX 350h blends hybrid efficiency with premium touches while climbing cool hill routes.',
    image: 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&w=1600&q=80',
    link: 'https://www.lexus.com/models'
  },
  {
    id: 4,
    title: 'Coastal Hilux Adventure',
    brand: 'Toyota',
    model: 'Hilux Double Cab',
    location: 'Kampot & Sihanoukville',
    description: 'The Hilux Double Cab hauls surfboards, beach gear, and island dreams with rugged dependability.',
    image: 'https://images.unsplash.com/photo-1472214103451-9374bd1c798e?auto=format&fit=crop&w=1600&q=80',
    link: 'https://global.toyota/en/mobility/toyota-brand/gallery/'
  },
  {
    id: 5,
    title: 'Pepper Route EV Run',
    brand: 'BYD',
    model: 'Tang EV',
    location: 'Kampot Pepper Shores',
    description: 'From pepper farms to coastal bridges, the Tang EV stays silent and steady on long drives.',
    image: 'https://images.unsplash.com/photo-1443890923422-7819ed4101c0?auto=format&fit=crop&w=1600&q=80',
    link: 'https://www.byd.com/en/models'
  }
]


const heroActiveSlideIndex = ref(0)

const heroOrderedSlides = computed(() => {
  if (!heroSlides.length) return []
  const start = heroActiveSlideIndex.value
  return heroSlides.map((_, index) => heroSlides[(start + index) % heroSlides.length])
})

const goToNextHeroSlide = () => {
  if (!heroSlides.length) return
  heroActiveSlideIndex.value = (heroActiveSlideIndex.value + 1) % heroSlides.length
}

const goToPrevHeroSlide = () => {
  if (!heroSlides.length) return
  heroActiveSlideIndex.value = (heroActiveSlideIndex.value - 1 + heroSlides.length) % heroSlides.length
}

const handleNextHeroSlide = () => {
  goToNextHeroSlide()
  restartHeroSlideTimer()
}

const handlePrevHeroSlide = () => {
  goToPrevHeroSlide()
  restartHeroSlideTimer()
}

let heroSlideIntervalId = null

const stopHeroSlideTimer = () => {
  if (heroSlideIntervalId) {
    clearInterval(heroSlideIntervalId)
    heroSlideIntervalId = null
  }
}

const startHeroSlideTimer = () => {
  if (typeof window === 'undefined') return
  stopHeroSlideTimer()
  heroSlideIntervalId = window.setInterval(goToNextHeroSlide, 6000)
}

const restartHeroSlideTimer = () => {
  startHeroSlideTimer()
}

onMounted(() => {
  startHeroSlideTimer()
})

onBeforeUnmount(() => {
  stopHeroSlideTimer()
})
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
</script>

<style scoped>
@import "../css/HomeView.css";
</style>
<<<<<<< HEAD
=======

>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
