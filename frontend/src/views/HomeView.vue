<script>
import '../assets/main.css'

export default {
  name: 'home'
}
</script>
<template>
  <div class="landing-container">
    <nav class="navbar">
      <div class="nav-logo">
        <div class="logo-icon"></div>
        <span class="logo-text">ChongChoul</span>
      </div>
      <div class="nav-links">
        <a href="#">How it works</a>
        <a href="#">Vehicles</a>
        <a href="#">Top Shops</a>
        <a href="#" class="partner-link">Become a Partner</a>
      </div>
      <div class="nav-auth">
        <button class="btn-login"><a href="/login">Login</a></button>
        <button class="btn-signup"><a href="/register">Sign Up</a></button>
      </div>
    </nav>

    <header class="hero">
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>Explore Cambodia at your <span class="italic-text">own pace</span></h1>
          <p>Connecting travelers with local trusted rental partners.</p>
          
          <div class="search-bar">
            <div class="search-field">
              <label>LOCATION</label>
              <input type="text" v-model="search.location" placeholder="Siem Reap, Phnom Penh">
            </div>
            <div class="search-field">
              <label>VEHICLE TYPE</label>
              <select v-model="search.type">
                <option>Motorbike</option>
                <option>Car/SUV</option>
                <option>Bicycle</option>
              </select>
            </div>
            <div class="search-field">
              <label>DATES</label>
              <input type="text" placeholder="Select dates">
            </div>
            <button class="search-btn">
              <span class="search-icon">🔍</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <section class="section-padding">
      <div class="section-header">
        <div>
          <p class="tagline">CATEGORIES</p>
          <h2>What are you looking for?</h2>
        </div>
        <a href="#" class="view-all">View All ></a>
      </div>
      
      <div class="category-grid">
        <div v-for="cat in categories" :key="cat.title" class="category-card">
          <img :src="cat.image" :alt="cat.title">
          <div class="card-info">
            <span class="availability">{{ cat.avail }}</span>
            <h3>{{ cat.title }}</h3>
            <p>{{ cat.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding bg-light">
      <div class="section-header">
        <div>
          <p class="tagline">TRUSTED PARTNERS</p>
          <h2>Top-Rated Local Shops</h2>
        </div>
      </div>

      <div class="shop-grid">
        <div v-for="shop in shops" :key="shop.name" class="shop-card">
          <img :src="shop.image" :alt="shop.name">
          <div class="shop-details">
            <div class="rating">⭐⭐⭐⭐⭐ <span>({{ shop.reviews }})</span></div>
            <h4>{{ shop.name }}</h4>
            <p class="location">📍 {{ shop.location }}</p>
            <div class="shop-footer">
              <span class="verified">✓ Verified</span>
              <a href="#" class="view-shop">View Shop</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="cta-card">
        <h2>Ready to start your adventure?</h2>
        <p>Join thousands of travelers who discovered Cambodia's hidden gems.</p>
        <div class="cta-buttons">
          <button class="btn-white">Search Vehicles</button>
          <button class="btn-outline">Become a Partner</button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const search = ref({
  location: '',
  type: 'Motorbike'
})

const categories = ref([
  { title: 'Motorbikes', desc: 'Perfect for city navigation', avail: '450+ Available', image: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=400' },
  { title: 'Cars & SUVs', desc: 'Spacious comfort for groups', avail: '120+ Available', image: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=500' },
  { title: 'Bicycles', desc: 'Eco-friendly garden paths', avail: '200+ Available', image: 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=500' }
])

const shops = ref([
  { name: 'Siem Reap Moto Plus', reviews: '350+', location: 'Old Market, Siem Reap', image: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=400' },
  { name: 'Kampot Green Tours', reviews: '180+', location: 'Riverside, Kampot', image: 'https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=500' },
  { name: 'Phnom Penh Auto Hub', reviews: '210+', location: 'BKK1, Phnom Penh', image: 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400' }
])
</script>
