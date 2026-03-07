
<template>
  <div class="container">
    <!-- LEFT SIDE -->
    <div class="left">
        <div class="overlay">
        <div class="logo">
            <div class="logo-icon-wrap">
              <svg width="34" height="34" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Location Pin -->
                <path d="M30 2C23.37 2 17 7.37 17 14C17 21.5 30 30 30 30C30 30 43 21.5 43 14C43 7.37 36.63 2 30 2Z" fill="white"/>
                <circle cx="30" cy="14" r="5" fill="rgba(25,100,210,0.6)"/>
                <!-- Car Cabin -->
                <path d="M18 42L21 33Q23 30 27 30H33Q37 30 39 33L42 42Z" fill="white"/>
                <!-- Car Body -->
                <rect x="6" y="42" width="48" height="15" rx="4" fill="white"/>
                <!-- Windshield -->
                <path d="M23 41L25 32H35L37 41Z" fill="rgba(25,100,210,0.45)"/>
                <!-- Left Wheel -->
                <circle cx="16" cy="53" r="5" fill="rgba(25,100,210,0.55)"/>
                <!-- Right Wheel -->
                <circle cx="44" cy="53" r="5" fill="rgba(25,100,210,0.55)"/>
              </svg>
            </div>
            <span>GoRent</span>
          </div>

          <div class="left-content">
            <div class="hero-badge">
              <span class="hero-badge-dot"></span>
              Premium Car Rental
            </div>
            <h1>Start your journey<br /><span class="journey-highlight">in minutes.</span></h1>
            <p>
              Access a fleet of premium vehicles at your fingertips.
              Rent for an hour, drive for a lifetime.
            </p>
          </div>

          <div class="review-card">
            <div class="stars">★★★★★</div>
            <p class="review-text">
              "The easiest rental experience I've ever had.
              No paperwork, just pure driving pleasure."
            </p>
            <div class="review-user">
              <strong>Alex Rivera</strong>
              <span>Platinum Member</span>
            </div>
          </div>
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div class="right">
      <!-- Back to Home -->
      <router-link to="/" class="back-home-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Back to Home
      </router-link>

      <!-- Floating decorative elements for right side -->
      <div class="right-decor">
        <div class="decor-circle circle-1"></div>
        <div class="decor-circle circle-2"></div>
        <div class="decor-circle circle-3"></div>
        <div class="decor-dot dot-1"></div>
        <div class="decor-dot dot-2"></div>
        <div class="decor-dot dot-3"></div>
        <div class="decor-dot dot-4"></div>
      </div>
      <div class="form-box">
        <div class="form-header">
          <h2>Welcome Back</h2>
          <p class="subtitle">Please enter your details to sign in</p>
        </div>

        <form @submit.prevent="handleLogin" novalidate>
          <!-- Error Alert -->
          <div v-if="generalError" class="error-alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ generalError }}</span>
          </div>

          <div class="form-group">
            <label><span class="required-star">*</span> Email Address</label>
            <input 
              type="email" 
              placeholder="name@example.com" 
              v-model="form.email" 
              :class="{ 'error': errors.email }"
              required 
            />
            <span class="error-text" v-if="errors.email">{{ errors.email }}</span>
          </div>

          <div class="form-group">
            <label><span class="required-star">*</span> Password</label>
            <div class="password-input-wrapper">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Enter your password" 
                v-model="form.password"
                autocomplete="current-password"
                :class="{ 'error': errors.password }"
                required 
              />
              <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
              </button>
            </div>
            <span class="error-text danger-text" v-if="errors.password">{{ errors.password }}</span>
          </div>

          <div class="form-options">
            <label class="checkbox">
              <input type="checkbox" v-model="form.remember" />
              <span>Remember me</span>
            </label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="login-btn" :disabled="isLoading">
            <span v-if="!isLoading">Sign In</span>
            <span v-else>Signing in...</span>
          </button>
        </form>

        <div class="register-prompt">
          <span>Don't have an account?</span>
          <router-link to="/register" class="register-link">Register</router-link>
        </div>

        <div class="divider">
          <span>OR CONTINUE WITH</span>
        </div>

        <div class="social-buttons">
          <button class="social-btn">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" width="20" />
            Google
          </button>
          <button class="social-btn">
            <img src="https://www.svgrepo.com/show/475647/facebook-color.svg" alt="Facebook" width="20" />
            Facebook
          </button>
        </div>

        <p class="terms">
          By signing in, you agree to GoRent's
          <a href="#">Terms of Service</a> and
          <a href="#">Privacy Policy</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { loginUser } from "../../services/auth";
import "../css/login.css";

const router = useRouter();
const isLoading = ref(false);
const errors = ref({});
const showPassword = ref(false);
const generalError = ref("");

const form = reactive({
  email: "",
  password: "",
  remember: false,
});

const handleLogin = async () => {
  errors.value = {};
  generalError.value = "";
  
  if (!form.email) {
    errors.value.email = "Email is required";
    return;
  }
  
  if (!form.password) {
    errors.value.password = "Password is required";
    return;
  }

  isLoading.value = true;

  try {
    const email = form.email.trim().toLowerCase();
    const requestInit = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email,
        password: form.password,
      }),
    };

    // Try /api/login first (points to UserController)
    let response = await fetch('/api/login', requestInit);
    
    // If 404, try /api/users/login as fallback
    if (response.status === 404) {
      response = await fetch('/api/users/login', requestInit);
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      // Handle Laravel validation error format
      let errorMessage = 'Login failed. Please try again.';
      
      if (data.errors) {
        // Get first field error
        const firstField = Object.keys(data.errors)[0];
        if (firstField && data.errors[firstField]) {
          errorMessage = Array.isArray(data.errors[firstField]) 
            ? data.errors[firstField][0] 
            : data.errors[firstField];
        }
      } else if (data.message) {
        errorMessage = data.message;
      }
      
      throw new Error(errorMessage);
    }

    // Store the token - handle both response formats
    const token = data.token || data.access_token;
    const user = data.user || data.data?.user;
    
    if (!token) {
      throw new Error('Invalid response: missing token');
    }

    localStorage.setItem('auth_token', token);
    localStorage.setItem('user', JSON.stringify(user));

    console.log('Login successful:', { user, token });

    // Redirect based on user role
    const userRole = user?.role;
    if (userRole === 'admin') {
      router.push('/admin');
    } else if (userRole === 'shop_owner') {
      router.push('/dashboard');
    } else {
      router.push('/view_shop');
    }
  } catch (error) {
    console.error('Login error:', error);
    const apiMessage = error?.response?.data?.message;
    generalError.value = apiMessage || error.message || 'Invalid email or password';
  } finally {
    isLoading.value = false;
  }
};
</script>
