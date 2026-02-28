<template>
  <div class="register-container">
    <div class="hero-section">
      <div class="hero-content">
        <h1 class="hero-title" style="margin-bottom: 20px">
          Start Your <br />
          <span class="highlight">Premium Journey</span>
        </h1>
        <p class="proof-label">
          Create your account and unlock exclusive access to our premium
          motorbike collection. Join thousands of riders who trust us for their
          adventures.
        </p>

        <div class="social-proof">
          <div class="proof-text">
            <p class="proof-label" style="margin-right: 70px">Bicycle</p>
            <p class="proof-label" style="margin-right: 70px">Motorbike</p>
            <p class="proof-label">Car</p>
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
        <h2>Register</h2>
        <p class="form-intro">
          Join us today and start your premium riding experience.
        </p>

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
          <span class="message-text"
            >Please fix the errors below to continue.</span
          >
        </div>

        <form @submit.prevent="handleRegister">
          <div class="input-group">
            <label>Full Name</label>
            <div class="input-wrapper" :class="{ error: errors.fullName }">
              <i class="icon-user" style="margin-right: 8px"></i>
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
              <i class="icon-phone" style="margin-right: 32px"></i>
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
                <i class="icon-lock" style="margin-right: 8px"></i>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="form.password"
                  placeholder="••••••••"
                  @input="clearError('password')"
                  :class="{ 'error-input': errors.password }"
                  required
                />
                <button
                  type="button"
                  @click="togglePassword"
                  class="password-toggle"
                  style="
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0;
                    margin-left: 8px;
                  "
                >
                  <img
                    :src="
                      showPassword
                        ? 'https://cdn-icons-png.flaticon.com/512/565/565655.png'
                        : 'https://cdn-icons-png.flaticon.com/512/709/709612.png'
                    "
                    style="width: 20px; height: 20px; opacity: 0.6"
                    alt="Toggle password visibility"
                  />
                </button>
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
                <i class="icon-lock" style="margin-right: 8px"></i>
                <input
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="form.confirmPassword"
                  placeholder="••••••••"
                  @input="clearError('confirmPassword')"
                  :class="{ 'error-input': errors.confirmPassword }"
                  required
                />
                <button
                  type="button"
                  @click="toggleConfirmPassword"
                  class="password-toggle"
                  style="
                    background: none;
                    border: none;
                    cursor: pointer;
                    padding: 0;
                    margin-left: 8px;
                  "
                >
                  <img
                    :src="
                      showConfirmPassword
                        ? 'https://cdn-icons-png.flaticon.com/512/565/565655.png'
                        : 'https://cdn-icons-png.flaticon.com/512/709/709612.png'
                    "
                    style="width: 20px; height: 20px; opacity: 0.6"
                    alt="Toggle password visibility"
                  />
                </button>
              </div>
              <span class="error-message" v-if="errors.confirmPassword">{{
                errors.confirmPassword
              }}</span>
            </div>
          </div>

          <button type="submit" class="btn-register" :disabled="isLoading">
            <span v-if="!isLoading">Register</span>
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
  } else if (form.password.length < 1) {
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
    const response = await fetch("http://localhost:8000/api/register", {
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
@import "../../assets/register.css";
</style>