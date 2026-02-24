<template>
  <div class="home-page">
    <header class="topbar">
      <div class="container topbar-inner">
        <a href="#" class="logo" @click.prevent="scrollTo('hero')">
          <span class="logo-icon">🚘</span>
          <span class="logo-text">&#x1785;&#x1784;&#x17CB;&#x1787;&#x17BD;&#x179B;</span>
        </a>

        <nav class="main-nav">
          <a href="#" @click.prevent="scrollTo('hero')">Home</a>
          <a href="#" @click.prevent="scrollTo('categories')">Explore</a>
          <a href="#" @click.prevent="scrollTo('trending')">About</a>
          <a href="#" @click.prevent="scrollTo('footer')">Contact</a>
        </nav>

        <div class="auth-actions">
          <button class="login-btn" @click="comingSoon('Login')">Login</button>
          <button class="signup-btn" @click="comingSoon('Sign Up')">Sign Up</button>
        </div>
      </div>
    </header>

    <section id="hero" class="hero">
      <div class="hero-overlay">
        <div class="container hero-content">
          <h1>Rent Your Perfect Ride<br />in <span>Cambodia</span></h1>
          <p>
            Choose from hundreds of cars, motorbikes, and bicycles from verified
            local shops across the kingdom.
          </p>
        </div>
      </div>

      <form class="search-bar" @submit.prevent="searchNow">
        <div class="search-field">
          <label>Vehicle Type</label>
          <select v-model="filters.type">
            <option>All Vehicles</option>
            <option>Cars</option>
            <option>Motorbikes</option>
            <option>Bicycles</option>
          </select>
        </div>

        <div class="search-field">
          <label>Location</label>
          <select v-model="filters.location">
            <option>Phnom Penh</option>
            <option>Siem Reap</option>
            <option>Kampot</option>
          </select>
        </div>

        <div class="search-field">
          <label>Pick-up Date</label>
          <input v-model="filters.date" type="date" />
        </div>

        <button class="search-btn" type="submit">Search</button>
      </form>
    </section>

    <section id="categories" class="section section-white">
      <div class="container">
        <div class="section-head">
          <div>
            <h2>Browse by Category</h2>
            <p>Find the right ride for your journey</p>
          </div>
          <a href="#" class="view-all">View All →</a>
        </div>

        <div class="category-grid">
          <article
            v-for="item in categories"
            :key="item.name"
            class="category-card"
            :style="{ backgroundImage: `url(${item.image})` }"
          >
            <div class="overlay"></div>
            <div class="category-content">
              <span>{{ item.tag }}</span>
              <h3>{{ item.name }}</h3>
              <p>{{ item.count }}+ Vehicles Available</p>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-light">
      <div class="container feature-grid">
        <article v-for="feature in features" :key="feature.title" class="feature-item">
          <div class="feature-icon">{{ feature.icon }}</div>
          <h4>{{ feature.title }}</h4>
          <p>{{ feature.text }}</p>
        </article>
      </div>
    </section>

    <section id="trending" class="section section-light">
      <div class="container">
        <div class="trending-head">
          <h2>Trending Rides</h2>
          <div class="underline"></div>
        </div>

        <div class="ride-grid">
          <article v-for="ride in rides" :key="ride.id" class="ride-card">
            <div class="ride-image-wrap">
              <img :src="ride.image" :alt="ride.name" />
              <span class="badge">{{ ride.badge }}</span>
              <button class="fav-btn" @click="toggleFav(ride.id)">
                {{ favorites.includes(ride.id) ? '♥' : '♡' }}
              </button>
            </div>
            <div class="ride-content">
              <h3>{{ ride.name }}</h3>
              <p>{{ ride.type }} · {{ ride.location }}</p>
              <div class="ride-bottom">
                <strong>${{ ride.price }} <small>/day</small></strong>
                <button class="arrow-btn" @click="bookNow(ride.id)">→</button>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section section-light cta-wrap">
      <div class="container">
        <div class="cta-card">
          <div>
            <h2>List Your Shop & Start Earning Today</h2>
            <p>
              Join Cambodia's largest vehicle rental network. Partner with Chong Choul to
              reach thousands of customers.
            </p>
            <button class="cta-btn" @click="comingSoon('Register Your Shop')">Register Your Shop</button>
          </div>
          <div class="mockup-box">
            <div class="phone-mockup">
              <div class="line"></div>
              <div class="stat">Bookings +36%</div>
              <div class="stat">Revenue $1,280</div>
              <div class="stat">Rating 4.9</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <footer id="footer" class="footer">
      <div class="container footer-grid">
        <div>
          <h4>&#x1785;&#x1784;&#x17CB;&#x1787;&#x17BD;&#x179B;</h4>
          <p>
            The premier vehicle rental marketplace in Cambodia, connecting travelers with
            reliable local rental shops since 2024.
          </p>
        </div>

        <div>
          <h5>Company</h5>
          <p>About Us</p>
          <p>How it Works</p>
          <p>Careers</p>
          <p>Press</p>
        </div>

        <div>
          <h5>Support</h5>
          <p>Help Center</p>
          <p>Safety Information</p>
          <p>Cancellation Options</p>
          <p>Contact Us</p>
        </div>

        <div>
          <h5>Newsletter</h5>
          <p>Stay updated with the latest offers and destinations.</p>
          <div class="newsletter">
            <input v-model="email" type="email" placeholder="Your email" />
            <button @click="subscribe">➤</button>
          </div>
        </div>
      </div>

      <div class="container footer-bottom">
        <span>© 2024 Chong Choul. All rights reserved.</span>
        <div>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Cookie Settings</a>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const filters = ref({
  type: 'All Vehicles',
  location: 'Phnom Penh',
  date: ''
})

const email = ref('')
const favorites = ref([])

const categories = [
  {
    tag: 'Adventure',
    name: 'Cars',
    count: 120,
    image: 'https://i.pinimg.com/1200x/b0/6a/ef/b06aef17ee76c4bcb6817efd7ab7538f.jpg'
  },
  {
    tag: 'City Move',
    name: 'Motorbikes',
    count: 450,
    image: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=1200&q=80'
  },
  {
    tag: 'Eco Friendly',
    name: 'Bicycles',
    count: 85,
    image: 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=1200&q=80'
  }
]

const features = [
  {
    icon: '🛡️',
    title: 'Verified Shops',
    text: 'All our rental partners undergo strict background checks for your safety.'
  },
  {
    icon: '🏷️',
    title: 'Best Price Guarantee',
    text: "Find the same vehicle for less elsewhere and we'll match the price."
  },
  {
    icon: '🎧',
    title: '24/7 Support',
    text: 'Our local support team is ready to help you anytime, anywhere in Cambodia.'
  }
]

const rides = [
  {
    id: 1,
    badge: 'New',
    name: 'Lexus LX 570',
    type: 'Luxury SUV',
    location: 'Phnom Penh',
    price: 120,
    image: 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1200&q=80'
  },
  {
    id: 2,
    badge: 'Popular',
    name: 'Honda Click 160i',
    type: 'Motorbike',
    location: 'Siem Reap',
    price: 12,
    image: 'https://images.unsplash.com/photo-1571646750134-5f5df3f8a6f7?auto=format&fit=crop&w=1200&q=80'
  },
  {
    id: 3,
    badge: 'Popular',
    name: 'Giant Talon 2',
    type: 'Mountain Bike',
    location: 'Kampot',
    price: 5,
    image: 'https://images.unsplash.com/photo-1544191696-15693f3d8d3f?auto=format&fit=crop&w=1200&q=80'
  },
  {
    id: 4,
    badge: 'Popular',
    name: 'Honda CRF 250',
    type: 'Off-road',
    location: 'Siem Reap',
    price: 25,
    image: 'https://images.unsplash.com/photo-1558981285-6f0c94958bb6?auto=format&fit=crop&w=1200&q=80'
  }
]

function scrollTo(id) {
  const el = document.getElementById(id)
  if (!el) return
  const top = el.getBoundingClientRect().top + window.scrollY - 90
  window.scrollTo({ top, behavior: 'smooth' })
}

function searchNow() {
  console.log('Search:', filters.value)
}

function toggleFav(id) {
  if (favorites.value.includes(id)) {
    favorites.value = favorites.value.filter((item) => item !== id)
    return
  }
  favorites.value.push(id)
}

function bookNow(id) {
  console.log('Book ride:', id)
}

function subscribe() {
  console.log('Subscribe:', email.value)
}

function comingSoon(action) {
  console.log(`${action} is not connected yet`)
}
</script>

<style scoped>
.home-page {
  background: #eceeef;
  color: #121212;
  font-family: 'Segoe UI', 'Noto Sans Khmer', Tahoma, sans-serif;
}

.container {
  width: min(1240px, 94%);
  margin: 0 auto;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: #fff;
  border-bottom: 1px solid #e6e6e6;
}

.topbar-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 52px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}

.logo-icon {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  background: #007bff;
  color: #fff;
  font-size: 12px;
}

.logo-text {
  color: #007bff;
  font-weight: 700;
  font-size: 18px;
}

.main-nav {
  display: flex;
  gap: 24px;
}

.main-nav a {
  text-decoration: none;
  color: #5c5c5c;
  font-size: 12px;
}

.main-nav a:hover {
  color: #007bff;
}

.auth-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.login-btn {
  border: none;
  background: transparent;
  color: #007bff;
  font-size: 12px;
  cursor: pointer;
}

.signup-btn {
  border: none;
  background: #007bff;
  color: #fff;
  border-radius: 8px;
  padding: 7px 14px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.hero {
  min-height: 500px;
  position: relative;
  /* background: linear-gradient(100deg, #040a0f 0%, #0f161f 54%, #1b2530 100%); */
  overflow: hidden;
}

.hero::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 170px;
  /* background-image: url('https://i.pinimg.com/1200x/65/b2/65/65b2655edad4fcad81b501f6df7c4c2b.jpg'); */
  background-size: cover;
  background-position: center;
  filter: brightness(0.65);
}

.hero-overlay {
  position: relative;
  z-index: 1;
  padding-top: 84px;
}

.hero-content {
  text-align: center;
  color: #fff;
}

.hero h1 {
  margin: 0;
  font-size: 62px;
  line-height: 1.05;
  font-weight: 800;
}

.hero h1 span {
  color: #007bff;
}

.hero p {
  margin: 18px auto 0;
  max-width: 640px;
  font-size: 19px;
  color: #dce1e7;
}

.search-bar {
  position: relative;
  z-index: 3;
  width: min(980px, 92%);
  margin: 44px auto 0;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 20px 20px rgba(0, 0, 0, 0.05);
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
}

.search-field {
  padding: 14px 16px;
  border-right: 1px solid #ececec;
}

.search-field label {
  display: block;
  text-transform: uppercase;
  color: #8b8b8b;
  font-size: 10px;
  margin-bottom: 7px;
}

.search-field select,
.search-field input {
  width: 100%;
  border: none;
  font-size: 14px;
  color: #525252;
  background: transparent;
  outline: none;
}

.search-btn {
  margin: 10px;
  border: none;
  border-radius: 12px;
  background: #007bff;
  color: #fff;
  min-width: 130px;
  font-weight: 700;
  cursor: pointer;
}

.section {
  padding: 56px 0;
}

.section-white {
  background: #fff;
}

.section-light {
  background: #eceeef;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 24px;
}

.section-head h2 {
  font-size: 39px;
  margin: 0;
}

.section-head p {
  margin: 8px 0 0;
  color: #7c7c7c;
  font-size: 14px;
}

.view-all {
  text-decoration: none;
  font-size: 14px;
  color: #007bff;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.category-card {
  position: relative;
  height: 260px;
  border-radius: 16px;
  overflow: hidden;
  background-size: cover;
  background-position: center;
}

.overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.72), transparent 62%);
}

.category-content {
  position: absolute;
  left: 16px;
  right: 16px;
  bottom: 14px;
  color: #fff;
}

.category-content span {
  font-size: 10px;
  text-transform: uppercase;
  color: #00a0ff;
  font-weight: 700;
}

.category-content h3 {
  font-size: 37px;
  margin: 5px 0 3px;
}

.category-content p {
  margin: 0;
  font-size: 13px;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.feature-item {
  text-align: center;
  padding: 10px 12px;
}

.feature-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  margin: 0 auto 12px;
  background: #deebff;
}

.feature-item h4 {
  margin: 0;
  font-size: 22px;
}

.feature-item p {
  margin-top: 9px;
  color: #6f6f6f;
  font-size: 14px;
}

.trending-head {
  text-align: center;
  margin-bottom: 24px;
}

.trending-head h2 {
  margin: 0;
  font-size: 45px;
}

.underline {
  width: 52px;
  height: 3px;
  background: #007bff;
  margin: 10px auto 0;
}

.ride-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.ride-card {
  border: 1px solid #dfdfdf;
  border-radius: 12px;
  background: #fff;
  overflow: hidden;
}

.ride-image-wrap {
  position: relative;
  height: 160px;
}

.ride-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.badge {
  position: absolute;
  left: 8px;
  top: 8px;
  padding: 3px 8px;
  font-size: 10px;
  color: #fff;
  background: #007bff;
  border-radius: 999px;
}

.fav-btn {
  position: absolute;
  right: 8px;
  top: 8px;
  border: none;
  width: 24px;
  height: 24px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.92);
  cursor: pointer;
}

.ride-content {
  padding: 10px;
}

.ride-content h3 {
  margin: 0;
  font-size: 16px;
}

.ride-content p {
  margin: 6px 0;
  color: #707070;
  font-size: 12px;
}

.ride-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.ride-bottom strong {
  color: #007bff;
  font-size: 22px;
}

.ride-bottom small {
  color: #747474;
  font-weight: 500;
}

.arrow-btn {
  border: none;
  width: 26px;
  height: 26px;
  border-radius: 999px;
  background: #f1f1f1;
  cursor: pointer;
}

.cta-wrap {
  padding-top: 44px;
}

.cta-card {
  background: #007bff;
  color: #fff;
  border-radius: 16px;
  padding: 40px;
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  align-items: center;
}

.cta-card h2 {
  margin: 0;
  font-size: 52px;
  line-height: 1.04;
}

.cta-card p {
  margin: 14px 0;
  max-width: 560px;
  font-size: 18px;
}

.cta-btn {
  border: none;
  border-radius: 999px;
  background: #fff;
  color: #007bff;
  padding: 10px 20px;
  font-weight: 700;
  cursor: pointer;
}

.mockup-box {
  display: grid;
  place-items: center;
}

.phone-mockup {
  width: 220px;
  padding: 14px;
  border-radius: 18px;
  background: #fff;
  color: #121212;
  transform: rotate(12deg);
  box-shadow: 0 16px 24px rgba(0, 0, 0, 0.2);
}

.line {
  width: 72px;
  height: 7px;
  border-radius: 999px;
  background: #e5e5e5;
  margin: 0 auto 12px;
}

.stat {
  border: 1px solid #ededed;
  border-radius: 10px;
  padding: 9px;
  margin-bottom: 8px;
  font-size: 12px;
}

.footer {
  background: #121212;
  color: #fff;
  padding: 40px 0 26px;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.3fr 1fr 1fr 1fr;
  gap: 22px;
  margin-bottom: 24px;
}

.footer h4 {
  margin: 0 0 12px;
  color: #007bff;
  font-size: 20px;
}

.footer h5 {
  margin: 0 0 12px;
  font-size: 13px;
  text-transform: uppercase;
}

.footer p {
  margin: 6px 0;
  color: #b8b8b8;
  font-size: 13px;
}

.newsletter {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
  margin-top: 10px;
}

.newsletter input {
  background: #1d2738;
  border: 1px solid #2f3a4d;
  border-radius: 999px;
  color: #fff;
  padding: 9px 12px;
}

.newsletter button {
  border: none;
  width: 32px;
  border-radius: 999px;
  background: #007bff;
  color: #fff;
  cursor: pointer;
}

.footer-bottom {
  padding-top: 16px;
  border-top: 1px solid #242424;
  display: flex;
  justify-content: space-between;
  color: #909090;
  font-size: 12px;
}

.footer-bottom div {
  display: flex;
  gap: 14px;
}

.footer-bottom a {
  color: #909090;
  text-decoration: none;
}

@media (max-width: 1100px) {
  .hero h1 {
    font-size: 46px;
  }

  .search-bar {
    grid-template-columns: 1fr;
  }

  .search-field {
    border-right: none;
    border-bottom: 1px solid #ececec;
  }

  .search-btn {
    margin: 12px;
    min-height: 44px;
  }

  .category-grid,
  .feature-grid,
  .ride-grid,
  .footer-grid,
  .cta-card {
    grid-template-columns: 1fr;
  }

  .footer-bottom {
    flex-direction: column;
    gap: 10px;
  }

  .main-nav {
    display: none;
  }
}
</style>
