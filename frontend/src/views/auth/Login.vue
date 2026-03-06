
<template>
  <div class="container">
    <!-- LEFT SIDE -->
    <div class="left">
        <div class="overlay">
          <div class="logo">
            <div class="logo-pic"></div>
            <span>GoRent</span>
          </div>

          <div class="left-content">
            <h1>Start your journey<br />in minutes.</h1>
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

        <form @submit.prevent="handleLogin">
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
                placeholder="••••••••" 
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
            <span class="error-text" v-if="errors.password">{{ errors.password }}</span>
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

const form = reactive({
  email: "",
  password: "",
  remember: false,
});

const handleLogin = async () => {
  errors.value = {};
  
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
<<<<<<< HEAD
    const data = await loginUser({
      email: form.email,
      password: form.password,
    });

=======
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

    let response = await fetch('/api/users/login', requestInit);
    if (response.status === 404) {
      response = await fetch('/api/login', requestInit);
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

    // Store the token
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

>>>>>>> view_shop
    console.log('Login successful:', data);

    // Redirect based on user role
    const userRole = data.user?.role;
    if (userRole === 'admin') {
      router.push('/admin');
    } else {
<<<<<<< HEAD
      router.push('/dashboard');
=======
      router.push('/view_shop');
>>>>>>> view_shop
    }
  } catch (error) {
    console.error('Login error:', error);
    const apiMessage = error?.response?.data?.message;
    errors.value.password = apiMessage || error.message || 'Invalid email or password';
  } finally {
    isLoading.value = false;
  }
};
</script>
<<<<<<< HEAD

=======
>>>>>>> view_shop
