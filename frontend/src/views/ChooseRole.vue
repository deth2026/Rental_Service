<script>
import "../assets/chooserole.css";

export default {
  name: "chooserole",
};
</script>
<template>
  <div class="landing-container">
    <section class="roles-grid">
      <div v-for="role in roles" :key="role.title" class="role-card">
        <div class="icon-box">
          <i :class="role.icon"></i>
        </div>
        <h2>{{ role.title }}</h2>
        <p>{{ role.description }}</p>
        <button class="get-started-btn">Get Started →</button>
      </div>
    </section>

    <section class="info-banner">
      <div class="info-content">
        <span class="badge">NOW LIVE</span>
        <h2>Available across Cambodia</h2>
        <p>
          Find vehicles in Phnom Penh, Siem Reap, Sihanoukville, Kampot, and
          beyond. We connect you with local experts.
        </p>
        <div class="features">
          <span>✔️ Verified Shops</span>
          <span>💵 Secure Payment</span>
        </div>
      </div>
      <div class="info-image">
        <!-- <img src="/cambodia-map-graphic.png" alt="Cambodia Map" /> -->
      </div>
    </section>

    <footer class="footer">
      <div class="footer-left">
        <span class="logo-text-small">CHONG CHOUL</span>
        <p>© 2024 Vehicle Rental Marketplace. All rights reserved.</p>
      </div>
      <div class="footer-right">
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
    title: "Traveler",
    icon: "icon-map-pin",
    description:
      "Rent motorbikes, bicycles, or cars from verified local shops. Explore Cambodia at your own pace.",
  },
  {
    title: "Shop Owner",
    icon: "icon-store",
    description:
      "List your fleet, manage bookings, and grow your rental business. Connect with tourists worldwide.",
  },
  {
    title: "Administrator",
    icon: "icon-shield",
    description:
      "Monitor transactions, verify new shops, and manage users. Access the platform control center.",
  },
];
</script>