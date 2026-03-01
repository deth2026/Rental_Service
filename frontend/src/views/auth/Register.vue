<template>
  <div class="container" style="min-height: 100vh">
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
            Access a fleet of premium vehicles at your fingertips. Rent for an
            hour, drive for a lifetime.
          </p>
        </div>

        <div class="review-card">
          <div class="stars">★★★★★</div>
          <p class="review-text">
            "The easiest rental experience I've ever had. No paperwork, just
            pure driving pleasure."
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
          <h2>Register</h2>
          <p class="subtitle">Please enter your details to sign up</p>
        </div>

        <!-- Selected Role Display -->
        <div class="selected-role" v-if="selectedRole">
          <span class="role-label">Selected Role:</span>
          <span class="role-badge" :data-role="selectedRole">{{
            selectedRole === "customer"
              ? "Customer"
              : selectedRole === "shop_owner"
              ? "Shop Owner"
              : "Admin"
          }}</span>
          <button type="button" @click="changeRole" class="change-role-btn">
            Change
          </button>
        </div>

        <!-- Success Message -->
        <div class="message-block success" v-if="successMessage">
          <span class="message-icon">✓</span>
          <span class="message-text">{{ successMessage }}</span>
        </div>

        <!-- Error Message -->
        <div
          class="message-block error"
          v-if="Object.keys(errors).length > 0 && !successMessage"
        >
          <span class="message-icon">✗</span>
          <span class="message-text">Please fix errors below to continue.</span>
        </div>

        <form @submit.prevent="handleRegister">
          <div class="form-group">
            <label><span class="required-star">*</span> Full Name</label>
            <input
              type="text"
              placeholder="John Doe"
              v-model="form.fullName"
              :class="{ error: errors.fullName }"
              required
            />
            <span class="error-text" v-if="errors.fullName">{{
              errors.fullName
            }}</span>
          </div>

          <div class="form-group">
            <label><span class="required-star">*</span> Email Address</label>
            <input
              type="email"
              placeholder="name@example.com"
              v-model="form.email"
              :class="{ error: errors.email }"
              required
            />
            <span class="error-text" v-if="errors.email">{{
              errors.email
            }}</span>
          </div>

          <div class="form-group">
            <label><span class="required-star">*</span> Phone Number</label>
            <input
              type="tel"
              placeholder="+855 12 345 678"
              v-model="form.phone"
              :class="{ error: errors.phone }"
              required
            />
            <span class="error-text" v-if="errors.phone">{{
              errors.phone
            }}</span>
          </div>

          <div class="row">
            <div class="form-group">
              <label><span class="required-star">*</span> Password</label>
              <div class="password-input-wrapper">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  v-model="form.password"
                  :class="{ error: errors.password }"
                  required
                />
                <button
                  type="button"
                  class="toggle-password"
                  @click="showPassword = !showPassword"
                >
                  <svg
                    v-if="showPassword"
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path
                      d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                    ></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path
                      d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                    ></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <span class="error-text" v-if="errors.password">{{
                errors.password
              }}</span>
            </div>
            <div class="form-group">
              <label
                ><span class="required-star">*</span> Confirm Password</label
              >
              <div class="password-input-wrapper">
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  v-model="form.confirmPassword"
                  :class="{ error: errors.confirmPassword }"
                  required
                />
                <button
                  type="button"
                  class="toggle-password"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <svg
                    v-if="showConfirmPassword"
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path
                      d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                    ></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  <svg
                    v-else
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <path
                      d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                    ></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                  </svg>
                </button>
              </div>
              <span class="error-text" v-if="errors.confirmPassword">{{
                errors.confirmPassword
              }}</span>
            </div>
          </div>

          <button type="submit" class="login-btn" :disabled="isLoading">
            <span v-if="!isLoading">Register</span>
            <span v-else>Creating account...</span>
          </button>
        </form>

        <div class="register-prompt">
          <span>Already have an account?</span>
          <router-link to="/login" class="register-link">Login</router-link>
        </div>

        <div class="divider">
          <span>OR CONTINUE WITH</span>
        </div>

        <div class="social-buttons">
          <button class="social-btn">
            <img
              src="https://www.svgrepo.com/show/475656/google-color.svg"
              alt="Google"
              width="20"
            />
            Google
          </button>
          <button class="social-btn">
            <img
              src="https://www.svgrepo.com/show/475647/facebook-color.svg"
              alt="Facebook"
              width="20"
            />
            Facebook
          </button>
        </div>

        <p class="terms">
          By creating an account, you agree to GoRent's
          <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();
const isLoading = ref(false);
const errors = ref({});
const successMessage = ref("");
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Get role from query parameter or default to customer
const selectedRole = ref(route.query.role || "customer");

const form = reactive({
  fullName: "",
  email: "",
  phone: "",
  password: "",
  confirmPassword: "",
  role: selectedRole.value, // Set role from query parameter
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
  successMessage.value = "";
  errors.value = {};

  try {
    const response = await fetch("/api/register", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        name: form.fullName,
        email: form.email,
        phone: form.phone,
        password: form.password,
        password_confirmation: form.confirmPassword,
        role: selectedRole.value,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      successMessage.value =
        "Registration successful! Please check your email to verify your account.";

      // Reset form
      form.fullName = "";
      form.email = "";
      form.phone = "";
      form.password = "";
      form.confirmPassword = "";

      // Redirect to login after successful registration
      setTimeout(() => {
        router.push("/login");
      }, 100);
    } else {
      // Handle validation errors from backend
      if (data.errors) {
        const newErrors = {};
        Object.keys(data.errors).forEach((field) => {
          newErrors[field === "name" ? "fullName" : field] =
            data.errors[field][0];
        });
        errors.value = newErrors;
      } else {
        errors.value.email =
          data.message || "Registration failed. Please try again.";
      }
    }
  } catch (error) {
    console.error("Registration error:", error);
    errors.value.email =
      "Network error. Please check your connection and try again.";
  } finally {
    isLoading.value = false;
  }
};

const clearError = (field) => {
  if (errors.value[field]) {
    delete errors.value[field];
  }
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const toggleConfirmPassword = () => {
  showConfirmPassword.value = !showConfirmPassword.value;
};

const changeRole = () => {
  router.push("/chooserole");
};
</script>

<style>
@import "../../assets/auth.css";
</style>