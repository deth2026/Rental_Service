<template>
  <div class="choose-role-container">
    <div class="brand-section">
      <div class="logo">
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
      </div>
      <h1 class="brand-name">Chong Choul</h1>
      <p class="brand-subtitle">Vehicle Rental Platform</p>
    </div>

    <div class="role-selector">
      <div class="header">
        <h2>Choose your role</h2>
        <p>Join as a customer or shop owner</p>
      </div>

      <div class="role-cards">
        <div class="role-card customer" @click="selectRole('customer')">
          <div class="role-icon">👤</div>
          <h3>Customer</h3>
          <p>Rent vehicles for your trips and adventures</p>
          <div class="role-features">
            <span>📱 Browse catalog</span>
            <span>🚗 Book instantly</span>
            <span>💳 Easy payments</span>
          </div>
        </div>

        <div class="role-card shop" @click="selectRole('shop_owner')">
          <div class="role-icon">🏪</div>
          <h3>Shop Owner</h3>
          <p>List your vehicles and manage bookings</p>
          <div class="role-features">
            <span>🚙 Add vehicles</span>
            <span>📅 Manage bookings</span>
            <span>💰 Receive payments</span>
          </div>
        </div>
      </div>

      <div class="continue-btn" v-if="selectedRole">
        <button @click="continueRegistration" :disabled="!selectedRole">
          Continue as {{ selectedRole === 'customer' ? 'Customer' : 'Shop Owner' }}
        </button>
      </div>

      <div class="have-account">
        <p>Already have an account? <RouterLink to="/login">Sign in</RouterLink></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const selectedRole = ref('')

const selectRole = (role) => {
  selectedRole.value = role
}

const continueRegistration = () => {
  if (selectedRole.value) {
    localStorage.setItem('selectedRole', selectedRole.value)
    router.push('/register')
  }
}
</script>

<style scoped>
@import '../assets/chooserole.css';
</style>

