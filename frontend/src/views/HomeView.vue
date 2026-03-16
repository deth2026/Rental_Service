<template>
  <div class="home-page">
    <header class="top-nav">
      <div class="brand-container">
        <svg class="brand-logo" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" style="stop-color:#1E40AF;stop-opacity:1" />
              <stop offset="100%" style="stop-color:#2563EB;stop-opacity:1" />
            </linearGradient>
          </defs>
          <rect width="120" height="120" rx="20" fill="url(#bgGrad)"/>
          <g transform="translate(30, 45)">
            <path d="M10 20c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm0-13c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5z" fill="white" opacity="0.9"/>
            <path d="M10 12c-4.4 0-8 3.6-8 8v2h20v-2c0-4.4-3.6-8-8-8z" fill="white" opacity="0.5"/>
            <g transform="translate(15, -5)">
              <rect x="0" y="0" width="30" height="18" rx="2" ry="2" fill="none" stroke="white" stroke-width="1.5"/>
              <circle cx="4" cy="14" r="1.5" fill="white" opacity="0.8"/>
              <circle cx="26" cy="14" r="1.5" fill="white" opacity="0.8"/>
              <path d="M0 8h30" stroke="white" stroke-width="1" opacity="0.6"/>
            </g>
          </g>
        </svg>
        <div class="brand-text">
          <div class="brand">Chong Choul</div>
          <!-- <div class="brand-tagline">Vehicle Rental</div> -->
        </div>
      </div>

      <div class="nav-auth">
        <RouterLink class="link-login" to="/login">Login</RouterLink>
        <RouterLink class="btn-signup" to="/chooserole">Sign Up</RouterLink>
      </div>
    </header>

    <section class="hero">
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
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section-head">
        <div>
          <h2>Browse by Category</h2>
          <p>Find the right ride for your journey</p>
        </div>
        
      </div>

      <div class="category-grid">
        <article
          v-for="item in categories"
          :key="item.title"
          class="category-card"
        >
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
          <article
            v-for="feature in features"
            :key="feature.title"
            class="feature-item"
          >
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
              <span
                ><strong>${{ ride.price }}</strong> /day</span
              >
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


  </div>
</template>

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
</script>

<style>
@import "../css/HomeView.css";
</style>

