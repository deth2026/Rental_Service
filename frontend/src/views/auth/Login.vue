<script>

import '../../assets/register.css'

export default {

  name: 'register'

}

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

        <h2>Welcom to Login</h2>

        <p class="form-intro">Enter your details to get started with your adventure.</p>



        <form @submit.prevent="handleRegister">



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



          <button type="submit" class="btn-register"><a href="/dashboard">Login</a></button>

        </form>



        <p class="login-link">Already have an account? <a href="/register">Login</a></p>



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