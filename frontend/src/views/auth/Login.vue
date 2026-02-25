<script>
import "../../assets/auth.css";
export default {
  name: "login",
};
</script>
<template>
  <div class="login-container">
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title">
          Welcome Back to <br />
          <span class="highlight">Your Adventure</span>
        </h1>

        <!-- <p class="hero-subtitle">
          Sign in to continue your premium riding experience. Your next journey
          is just a login away.
        </p> -->

        <div class="features">
          <div class="feature-item">
            <span class="feature-icon"></span>
            <span>Secure Login</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon"></span>
            <span>Quick Access</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon"></span>
            <span>Personalized</span>
          </div>
        </div>

        <div class="social-proof">
          <div class="proof-text">
            <p class="proof-label">
              Sign in to continue your premium riding experience. Your next
              journey is just a login away.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-wrapper">
        <div class="back-home">
          <a href="/" class="back-home-btn">
            <span class="back-icon">←</span>
            Back to Home
          </a>
        </div>

        <h2>Login</h2>
        <p class="form-intro">
          Sign in to continue your premium riding experience.
        </p>

        <form @submit.prevent="handleLogin">
          <div class="input-group">
            <label>Email</label>
            <div class="input-wrapper" :class="{ error: errors.email }">
              <i class="icon-mail" style="margin-right: 34px"></i>
              <input
                type="email"
                v-model="form.email"
                placeholder="john@example.com"
                @input="clearError('email')"
                :class="{ 'error-input': errors.email }"
                required
              />
            </div>
            <span class="error-message" v-if="errors.email">{{
              errors.email
            }}</span>
          </div>

          <div class="input-group">
            <label>Password</label>
            <div class="input-wrapper" :class="{ error: errors.password }">
              <i class="icon-lock" style="margin-right: 34px"></i>
              <input
                type="password"
                v-model="form.password"
                placeholder="••••••••"
                @input="clearError('password')"
                :class="{ 'error-input': errors.password }"
                required
              />
            </div>
            <span class="error-message" v-if="errors.password">{{
              errors.password
            }}</span>
          </div>

          <div class="form-options">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.rememberMe" />
              <span class="checkmark"></span>
              Remember me
            </label>
            <a href="/forgot-password" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-login" :disabled="isLoading">
            <span v-if="!isLoading"
              ><a href="/chooserole" style="text-decoration: none; color: white"
                >Login</a
              ></span
            >
            <span v-else class="loading-spinner">
              <span class="spinner"></span>
              Signing In...
            </span>
          </button>
        </form>

        <p class="login-link">
          Don't have an account? <a href="/register">Sign In</a>
        </p>

        <div class="divider">
          <span>Or sign in with</span>
        </div>

        <div class="social-btns">
          <button class="social-btn">
            <img
              src="https://cdn-icons-png.flaticon.com/512/2991/2991148.png"
              width="18"
            />
            Google
          </button>
          <button class="social-btn">
            <img
              src="https://cdn-icons-png.flaticon.com/512/733/733547.png"
              width="18"
            />
            Facebook
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const isLoading = ref(false);
const errors = ref({});

const form = reactive({
  email: "",
  password: "",
  rememberMe: false,
});

const validateForm = () => {
  const newErrors = {};

  if (!form.email.trim()) {
    newErrors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    newErrors.email = "Please enter a valid email";
  }

  if (!form.password) {
    newErrors.password = "Password is required";
  } else if (form.password.length < 6) {
    newErrors.password = "Password must be at least 6 characters";
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

const handleLogin = async () => {
  if (!validateForm()) {
    return;
  }

  isLoading.value = true;

  try {
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 2000));

    console.log("Login Form Submitted:", form);

    // Show success message or redirect
    alert("Login successful! Welcome back.");
    router.push("/dashboard");
  } catch (error) {
    console.error("Login error:", error);
    alert("Login failed. Please check your credentials and try again.");
  } finally {
    isLoading.value = false;
  }
};

const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field];
  }
};
</script>