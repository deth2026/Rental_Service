
<template>
  <!-- Modern Splash Screen -->
  <transition name="fade">
    <div v-if="showSplash" class="location-splash">
      <div class="splash-content">
        <div class="splash-logo-container">
          <img src="/Images/logo-removebg.png" alt="Chong Choul Logo" class="splash-img" />
          <h1 class="splash-brand">CHONG CHOUL</h1>
        </div>
        <div class="splash-loader-container">
          <div class="splash-loader-bar"></div>
        </div>
        <p class="splash-tagline">Premium Vehicle Rental in Cambodia</p>
      </div>
    </div>
  </transition>

  <!-- Location Permission Popup -->
  <transition name="modal-fade">
    <div v-if="showLocationPopup" class="location-popup-overlay">
      <div class="location-popup-card">
        <div class="popup-brand-header">
           <img src="/Images/logo-removebg.png" alt="Chong Choul Logo" class="popup-logo-big" />
           <h3 class="popup-brand-name">CHONG CHOUL</h3>
        </div>
        
        <div class="location-popup-header">
          <div class="red-circle-x">
            <i class="fa-solid fa-xmark"></i>
          </div>
        </div>
        <div class="location-popup-body">
          <h2 class="khmer-text">ស្នើសុំការអនុញ្ញាតប្រើទីតាំង</h2>
          <p class="english-text">Allow location so we can show rental shops near you</p>
          
          <div class="location-status">
            <div class="pulse-ring"></div>
            <i class="fa-solid fa-location-crosshairs location-pin-icon"></i>
            <span class="detecting-text">Detecting your location...</span>
          </div>
        </div>
        <div class="location-popup-footer">
          <button class="cancel-btn" @click="closeLocationPopup">
            <i class="fa-solid fa-xmark"></i> Cancel
          </button>
          <button class="allow-btn-green" @click="handleAllowLocation">
            <i class="fa-solid fa-check"></i> Allow
          </button>
        </div>
      </div>
    </div>
  </transition>

  <div v-if="!showSplash" class="home-page">
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
        <template v-if="isLocationAllowed">
          <RouterLink class="link-login" to="/login">Login</RouterLink>
          <RouterLink class="btn-signup" to="/chooserole">Sign Up</RouterLink>
        </template>
        <template v-else>
          <button class="link-login disabled-link" @click="showLocationPopup = true">Login</button>
          <button class="btn-signup disabled-btn" @click="showLocationPopup = true">Sign Up</button>
        </template>
      </div>
    </header>

    <section class="hero">
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>
            <span class="hero-line">Find Your Perfect Ride and Explore</span>
            <span class="hero-line"><span>Cambodia</span> in Style!</span>
          </h1>
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
import { reactive, onMounted, ref } from 'vue'

const showSplash = ref(true)
const showLocationPopup = ref(false)
const isLocationAllowed = ref(localStorage.getItem('chong_choul_location_granted') === 'true')
const LOCATION_CACHE_KEY = 'chong_choul_user_location'

const closeLocationPopup = () => {
  showLocationPopup.value = false
}

const handleAllowLocation = () => {
  requestLocation()
  // Securely grant access only on Allow click
  localStorage.setItem('chong_choul_location_granted', 'true')
  isLocationAllowed.value = true
  showLocationPopup.value = false
  console.log("Location access granted by user");
}

const requestLocation = () => {
  if (localStorage.getItem(LOCATION_CACHE_KEY)) return

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const userLoc = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        }
        localStorage.setItem(LOCATION_CACHE_KEY, JSON.stringify(userLoc))
      },
      (error) => {
        console.warn('Location access denied or unavailable:', error.message)
      },
      { enableHighAccuracy: true, timeout: 5000, maximumAge: 0 }
    )
  }
}

onMounted(() => {
  // Splash screen duration (flash)
  setTimeout(() => {
    showSplash.value = false
    
    // Show the location permission popup 2 seconds AFTER the splash screen is gone
    // ONLY if it hasn't been allowed yet (Security Measure)
    setTimeout(() => {
      if (!isLocationAllowed.value) {
        showLocationPopup.value = true
      }
    }, 2000)
  }, 2500)
})

const search = reactive({
  type: 'All Vehicles',
  location: 'Phnom Penh',
  pickupDate: ''
})

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
</script>


<style>
@import "../css/HomeView.css";
</style>
