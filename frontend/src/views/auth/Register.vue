<script>
import "../../assets/auth.css";
export default {
  name: "register",
};
</script>
<template>
  <div class="register-container">
    <div class="hero-section">
      <div class="hero-content">
        <div class="logo">
          <span class="logo-icon">🏍️</span>
          <span>MOTOPREMIUM</span>
        </div>

        <h1 class="hero-title">
          Start Your <br />
          <span class="highlight">Premium Journey</span>
        </h1>

        <p class="hero-subtitle">
          Create your account and unlock exclusive access to our premium
          motorbike collection. Join thousands of riders who trust us for their
          adventures.
        </p>

        <div class="features">
          <div class="feature-item">
            <span class="feature-icon">✨</span>
            <span>Premium Bikes</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">🛡️</span>
            <span>Insurance Included</span>
          </div>
          <div class="feature-item">
            <span class="feature-icon">📍</span>
            <span>Multiple Locations</span>
          </div>
        </div>

        <div class="social-proof">
          <div class="avatar-group">
            <img src="https://i.pravatar.cc/40?u=1" alt="user" />
            <img src="https://i.pravatar.cc/40?u=2" alt="user" />
            <img src="https://i.pravatar.cc/40?u=3" alt="user" />
            <img src="https://i.pravatar.cc/40?u=4" alt="user" />
          </div>
          <div class="proof-text">
            <p class="proof-number">50,000+</p>
            <p class="proof-label">Happy Riders</p>
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

        <h2>Create Account</h2>
        <p class="form-intro">
          Join us today and start your premium riding experience.
        </p>

        <form @submit.prevent="handleRegister">
          <div class="input-group">
            <label>Full Name</label>
            <div class="input-wrapper" :class="{ error: errors.fullName }">
              <i class="icon-user"></i>
              <input
                type="text"
                v-model="form.fullName"
                placeholder="John Doe"
                @input="clearError('fullName')"
                :class="{ 'error-input': errors.fullName }"
                required
              />
            </div>
            <span class="error-message" v-if="errors.fullName">{{
              errors.fullName
            }}</span>
          </div>

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
            <label>Phone Number</label>
            <div class="input-wrapper" :class="{ error: errors.phone }">
              <i class="icon-phone" style="margin-right: 34px"></i>
              <input
                type="tel"
                v-model="form.phone"
                placeholder="+855 12 345 678"
                @input="clearError('phone')"
                :class="{ 'error-input': errors.phone }"
                required
              />
            </div>
            <span class="error-message" v-if="errors.phone">{{
              errors.phone
            }}</span>
          </div>

          <div class="row">
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
            <div class="input-group">
              <label>Confirm Password</label>
              <div
                class="input-wrapper"
                :class="{ error: errors.confirmPassword }"
              >
                <i class="icon-lock" style="margin-right: 34px"></i>
                <input
                  type="password"
                  v-model="form.confirmPassword"
                  placeholder="••••••••"
                  @input="clearError('confirmPassword')"
                  :class="{ 'error-input': errors.confirmPassword }"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.confirmPassword">{{
                errors.confirmPassword
              }}</span>
            </div>
          </div>

          <button type="submit" class="btn-register" :disabled="isLoading">
            <span v-if="!isLoading">Create Account</span>
            <span v-else class="loading-spinner">
              <span class="spinner"></span>
              Creating Account...
            </span>
          </button>
        </form>

        <p class="login-link">
          Already have an account? <a href="/login">Login</a>
        </p>

        <div class="divider">
          <span>Or sign up with</span>
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
  fullName: "",
  email: "",
  phone: "",
  password: "",
  confirmPassword: "",
});

const validateForm = () => {
  const newErrors = {};

  if (!form.fullName.trim()) {
    newErrors.fullName = "Full name is required";
  } else if (form.fullName.length < 2) {
    newErrors.fullName = "Name must be at least 2 characters";
  }

  if (!form.email.trim()) {
    newErrors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    newErrors.email = "Please enter a valid email";
  }

  if (!form.phone.trim()) {
    newErrors.phone = "Phone number is required";
  } else if (!/^\+?[\d\s\-\(\)]{7,15}$/.test(form.phone.replace(/\s/g, ""))) {
    newErrors.phone = "Please enter a valid phone number";
  }

  if (!form.password) {
    newErrors.password = "Password is required";
  } else if (form.password.length < 8) {
    newErrors.password = "Password must be at least 8 characters";
  } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(form.password)) {
    newErrors.password =
      "Password must contain uppercase, lowercase, and number";
  }

  if (!form.confirmPassword) {
    newErrors.confirmPassword = "Please confirm your password";
  } else if (form.password !== form.confirmPassword) {
    newErrors.confirmPassword = "Passwords do not match";
  }

  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

const handleRegister = async () => {
  if (!validateForm()) {
    return;
  }

  isLoading.value = true;

  try {
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 2000));

    console.log("Registration Form Submitted:", form);

    // Show success message or redirect
    alert(
      "Registration successful! Please check your email to verify your account."
    );
    router.push("/login");
  } catch (error) {
    console.error("Registration error:", error);
    alert("Registration failed. Please try again.");
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