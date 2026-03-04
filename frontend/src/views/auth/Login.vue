<template>
  <div class="register-container">
    <div class="hero-section">
      <div class="hero-content">
        <div class="logo">
          <span>MOTOPREMIUM</span>
        </div>

        <h1 class="hero-title">
          Ride the Ride of <br />
          <span class="highlight">Your Dreams</span>
        </h1>

        <p class="hero-subtitle">
          Join our exclusive community of riders and explore the most iconic routes with our premium motorbike rentals.
        </p>

        <div class="social-proof">
          <div class="avatar-group">
            <img src="https://i.pravatar.cc/40?u=1" alt="user" />
            <img src="https://i.pravatar.cc/40?u=2" alt="user" />
            <img src="https://i.pravatar.cc/40?u=3" alt="user" />
          </div>
          <p>Join 50k+ riders worldwide</p>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-wrapper">
        <h2>Welcome to Login</h2>
        <p class="form-intro">Enter your details to get started with your adventure.</p>

        <form @submit.prevent="handleLogin">
          <div class="input-group">
            <label>Email</label>
            <div class="input-wrapper">
              <i class="icon-mail"></i>
              <input type="email" v-model.trim="form.email" placeholder="john@example.com" required />
            </div>
          </div>

          <div class="row">
            <div class="input-group">
              <label>Password</label>
              <div class="input-wrapper">
                <i class="icon-lock"></i>
                <input type="password" v-model="form.password" placeholder="••••••••" required />
              </div>
            </div>
          </div>

          <p v-if="errorMessage" class="login-link" style="color: #b4002a">{{ errorMessage }}</p>
          <button type="submit" class="btn-register" :disabled="isSubmitting">
            {{ isSubmitting ? 'Logging in...' : 'Login' }}
          </button>
        </form>

        <p class="login-link">No account yet? <router-link to="/register">Register</router-link></p>

        <footer class="form-footer">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Support</a>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '../../services/database.js'

const router = useRouter()
const isSubmitting = ref(false)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: '',
})

const handleLogin = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await userService.login(form)
    await router.push('/dashboard')
  } catch (error) {
    errorMessage.value = error.message || 'Login failed.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
