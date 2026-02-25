<script>
import "../assets/main.css";

export default {
  name: "home",
};
</script>
<template>
  <div class="landing-container">
    <nav class="navbar">
      <div class="nav-container">
        <div class="nav-logo" style="margin-right: 50px">
          <span class="logo-icon">🏍️</span>
          <span class="logo-text">MOTOPREMIUM</span>
        </div>

        <div class="nav-menu" style="margin-right: 400px">
          <div class="nav-links">
            <a href="#how-it-works" class="nav-link">How it works</a>
            <a href="#vehicles" class="nav-link">Vehicles</a>
            <a href="#top-shops" class="nav-link">Top Shops</a>
            <a href="#partner" class="nav-link partner-link"
              >Become a Partner</a
            >
          </div>

          <div class="nav-auth">
            <button class="btn-logins">
              <router-link to="/login">Login</router-link>
            </button>
            <button class="btn-signup">
              <router-link to="/register">Sign Up</router-link>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <header class="hero">
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>
            Discover Cambodia with
            <span class="italic-text">Premium Rides</span>
          </h1>
          <p>
            Your trusted partner for motorbike rentals across Cambodia. Explore
            with confidence and style.
          </p>

          <div class="search-bar">
            <div class="search-field">
              <label>📍 LOCATION</label>
              <input
                type="text"
                v-model="search.location"
                placeholder="Siem Reap, Phnom Penh"
              />
            </div>
            <div class="search-field">
              <label>🏍️ VEHICLE TYPE</label>
              <select v-model="search.type">
                <option>Motorbike</option>
                <option>Car/SUV</option>
                <option>Bicycle</option>
                <option>Electric Scooter</option>
              </select>
            </div>
            <div class="search-field">
              <label>📅 DATES</label>
              <input
                type="date"
                v-model="search.dates"
                placeholder="Select dates"
              />
            </div>
            <button class="search-btn" @click="handleSearch">
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
          <img :src="cat.image" :alt="cat.title" />
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
          <img :src="shop.image" :alt="shop.name" />
          <div class="shop-details">
            <div class="rating">
              ⭐⭐⭐⭐⭐ <span>({{ shop.reviews }})</span>
            </div>
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
        <p>
          Join thousands of travelers who discovered Cambodia's hidden gems.
        </p>
        <div class="cta-buttons">
          <button class="btn-white">Search Vehicles</button>
          <button class="btn-outline">Become a Partner</button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from "vue";

const isMobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const search = ref({
  location: "",
  type: "Motorbike",
  dates: "",
});

const handleSearch = () => {
  console.log("Searching for:", search.value);
  // Add search functionality here
};

const categories = ref([
  {
    title: "Motorbikes",
    desc: "Perfect for city navigation and temple tours",
    avail: "450+ Available",
    image:
      "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=500",
  },
  {
    title: "Cars & SUVs",
    desc: "Spacious comfort for groups and families",
    avail: "120+ Available",
    image:
      "https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=500",
  },
  {
    title: "Electric Scooters",
    desc: "Eco-friendly modern transportation",
    avail: "200+ Available",
    image:
      "https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=500",
  },
]);

const shops = ref([
  {
    name: "Siem Reap Moto Plus",
    reviews: "350+",
    location: "Old Market, Siem Reap",
    rating: 4.8,
    image:
      "https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=400",
  },
  {
    name: "Kampot Green Tours",
    reviews: "180+",
    location: "Riverside, Kampot",
    rating: 4.9,
    image:
      "https://images.unsplash.com/photo-1485965120184-e220f721d03e?auto=format&fit=crop&w=500",
  },
  {
    name: "Phnom Penh Auto Hub",
    reviews: "210+",
    location: "BKK1, Phnom Penh",
    rating: 4.7,
    image:
      "https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400",
  },
]);
</script>
