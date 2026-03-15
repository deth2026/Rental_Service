<script setup>
import { useRouter } from 'vue-router'
import '../../css/Logout.css'
import '../../css/userDashboard.css'

const router = useRouter()

const doLogout = () => {
  localStorage.removeItem('authToken')
  router.push('/')
}

const closeModal = () => {
  router.back()
}
</script>

<template>
  <div class="confirm-overlay" @click="closeModal">
    <div class="confirm-modal" @click.stop>
      <div class="confirm-icon">
        <i class="fa-solid fa-right-from-bracket"></i>
      </div>
      <h3 class="confirm-title">Logout</h3>
      <p class="confirm-text">Are you sure you want to logout from the shop owner account?</p>
      <div class="confirm-actions">
        <button class="confirm-cancel-btn" @click="closeModal">
          Cancel
        </button>
        <button class="confirm-logout-btn danger" @click="doLogout">
          Logout
        </button>
      </div>
    </div>
  </div>
</template>


