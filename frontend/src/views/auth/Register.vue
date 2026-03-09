<<<<<<< HEAD
<script>
=======
<template>
  <div class="container" style="min-height: 150vh">
    <!-- LEFT SIDE -->
    <div class="left">
      <div class="overlay">
        <div class="logo">
          <div class="logo-pic"></div>
          <span>GoRent</span>
        </div>
>>>>>>> 171e7d9c0bf0bc9904c7e3d2d0dffddd0252564f

import '../../assets/register.css'

export default {

<<<<<<< HEAD
  name: 'register'
=======
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
          <div class="header-top" style="display: flex">
            <h2 style="margin-right: 31vh">Register</h2>
            <button type="button" @click="goHome" class="back-home-btn">
              <svg
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
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
              Home
            </button>
          </div>
          <p class="subtitle">Please enter your details to sign up</p>
        </div>
>>>>>>> 171e7d9c0bf0bc9904c7e3d2d0dffddd0252564f

}

<<<<<<< HEAD
=======
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

const goHome = () => {
  router.push("/");
};
>>>>>>> 171e7d9c0bf0bc9904c7e3d2d0dffddd0252564f
</script>

<template>

  <div class="register-container">

    <div class="hero-section">

      <div class="hero-content">

        <div class="logo">

          <!-- <img src="/logo-icon.png" alt="Logo" class="logo-img" /> -->

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

        <h2>Welcom to Register</h2>

        <p class="form-intro">Enter your details to get started with your adventure.</p>



        <form @submit.prevent="handleRegister">



          <div class="input-group">

            <label>Username</label>

            <div class="input-wrapper">

              <i class="icon-at"></i>

              <input type="text" v-model="form.username" placeholder="john" />

            </div>

          </div>



          <div class="input-group">

            <label>Email</label>

            <div class="input-wrapper">

              <i class="icon-mail"></i>

              <input type="email" v-model="form.email" placeholder="john@example.com" />

            </div>

          </div>



          <div class="row">

            <div class="input-group">

              <label>Password</label>

              <div class="input-wrapper">

                <i class="icon-lock"></i>

                <input type="password" v-model="form.password" placeholder="••••••••" />

              </div>

            </div>

          </div>



          <button type="submit" class="btn-register">Register</button>

        </form>



        <p class="login-link">Already have an account? <a href="/login">Login</a></p>

        <p class="login-link">Already have an account? <a href="/home">Come Back</a></p>



        <div class="divider">

          <span>Or sign up with</span>

        </div>



        <div class="social-btns">

          <!-- <button class="social-btn"><img src="/google.png" /> Google</button> -->

          <!-- <button class="social-btn"><img src="/facebook.png" /> Facebook</button> -->

        </div>



        <footer class="form-footer">

          <a href="#">Privacy Policy</a>

          <a href="#">Terms of Service</a>

          <a href="#">Support</a>

        </footer>

      </div>

      

      <button class="theme-toggle">

        <i class="icon-moon"></i>

      </button>

    </div>

  </div>

</template>



<script setup>

import { reactive } from 'vue';



const form = reactive({

  fullName: '',

  username: '',

  email: '',

  password: '',

  confirmPassword: ''

});



const handleRegister = () => {

  console.log('Form Submitted:', form);

};

</script>