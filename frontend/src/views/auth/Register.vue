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
        <h2>Welcome to Register</h2>
        <p class="form-intro">Create account to start booking from your favorite shop.</p>

        <form @submit.prevent="handleRegister">
          <div class="input-group">
            <label>Username</label>
            <div class="input-wrapper">
              <i class="icon-at"></i>
              <input type="text" v-model.trim="form.username" placeholder="john" required />
            </div>
          </div>

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
            {{ isSubmitting ? 'Registering...' : 'Register' }}
          </button>
        </form>

        <p class="login-link">Already have an account? <router-link to="/login">Login</router-link></p>
        <p class="login-link"><router-link to="/home">Come Back</router-link></p>

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
  username: '',
  email: '',
  password: '',
})

const handleRegister = async () => {
  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await userService.register(form)
    await router.push('/dashboard')
  } catch (error) {
    errorMessage.value = error.message || 'Registration failed.'
  } finally {
    isSubmitting.value = false
  }
}
</script>
