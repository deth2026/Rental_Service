<template>
  <div class="landing-page">
    <nav class="navbar">
      <div class="logo">
        <img src="/logo.png" alt="Chong Choul" />
        <span>CHONG CHOUL</span>
      </div>
      <div class="nav-right">
        <span class="language-selector">🌐 English</span>
        <button class="help-btn">❓ Need Help?</button>
      </div>
    </nav>

    <main class="content">
      <header class="hero-section">
        <h1>Welcome - Choose Your Role</h1>
        <p>Connect with the best vehicle rental services across Cambodia. Select how you would like to use the platform to get started.</p>
      </header>

      <section class="roles-grid">
        <div v-for="role in roles" :key="role.title" class="role-card">
          <div class="icon-container">
            <span class="icon">{{ role.icon }}</span>
          </div>
          <h3>{{ role.title }}</h3>
          <p>{{ role.description }}</p>
          <button class="get-started-btn">Get Started →</button>
        </div>
      </section>

      <section class="location-banner">
        <div class="location-info">
          <span class="badge">NOW LIVE</span>
          <h2>Available across Cambodia</h2>
          <p>Find vehicles in Phnom Penh, Siem Reap, Sihanoukville, Kampot, and beyond. We connect you with local experts.</p>
          <div class="features">
            <span>✅ Verified Shops</span>
            <span>💵 Secure Payment</span>
          </div>
        </div>
        <div class="map-illustration">
          <div class="map-placeholder">🇰🇭</div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-left">
        <p>© 2024 Vehicle Rental Marketplace. All rights reserved.</p>
      </div>
      <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Support</a>
        <a href="#">About Us</a>
      </div>
    </footer>
  </div>
</template>

<script setup>
const roles = [
  {
    title: 'Traveler',
    icon: '📍',
    description: 'Rent motorbikes, bicycles, or cars from verified local shops. Explore Cambodia at your own pace.'
  },
  {
    title: 'Shop Owner',
    icon: '🏪',
    description: 'List your fleet, manage bookings, and grow your rental business. Connect with tourists worldwide.'
  },
  {
    title: 'Administrator',
    icon: '🛡️',
    description: 'Monitor transactions, verify new shops, and manage users. Access the platform control center.'
  }
]
</script>
