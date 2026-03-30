<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<template>
  <main class="home">
    <h1>Rental Service</h1>
    <p>Frontend is running successfully.</p>
    <div class="links">
      <router-link to="/login">Login</router-link>
      <router-link to="/register">Register</router-link>
      <router-link to="/dashboard">User Dashboard</router-link>
      <router-link to="/admin/dashboard">Admin Dashboard</router-link>
    </div>
  </main>
</template>

<style scoped>
.home {
  min-height: 100vh;
  display: grid;
  place-content: center;
  gap: 12px;
  text-align: center;
}

.links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

.links a {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  text-decoration: none;
  color: #222;
}
</style>
=======
<script>
import { reactive } from "vue";
import "../assets/HomeView.css";
=======
>>>>>>> d9950b55d15a8c1d02e11f6e24682b1f5b876a67


<template>
  <div class="home-page">
=======
   <template>
  <main class="home-page">
>>>>>>> 4ecff457c455e0fa2d252bec5ddf48f9ed99a23b
    <header class="top-nav">
      <div class="brand-container">
        <Logo class="home-brand" theme="light" size="lg" :showTagline="true" src="/Images/logo-removebg.png" />
      </div>

      <div class="nav-auth">
        <RouterLink class="link-login" to="/login">Login</RouterLink>
        <span class="nav-sep" aria-hidden="true">|</span>
        <RouterLink class="btn-signup" to="/chooserole">Sign Up</RouterLink>
      </div>
    </header>

    <section class="hero">
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>
            <span class="hero-line">Find Your Perfect Ride and Explore</span>
            <span class="hero-line"><span>Cambodia</span> in Style!</span>
          </h1>
          <p class="hero-sub">
            Rent motorbikes, cars, and bicycles from trusted local shops—fast booking, fair prices, and flexible plans.
          </p>
          <div class="hero-actions">
            <RouterLink class="hero-btn primary" to="/register">Explore Vehicles</RouterLink>
            <RouterLink class="hero-btn ghost" to="/register">List Your Shop</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section-head section-head-center">
        <div>
          <h2>Browse by Category</h2>
          <p>Find the right ride for your journey</p>
        </div>
      </div>

      <div class="category-grid">
        <article v-for="item in categories" :key="item.title" class="category-card">
          <img :src="item.image" :alt="item.title" />
          <div class="card-overlay"></div>
          <div class="card-text">
            <span>{{ item.tag }}</span>
            <h3>{{ item.title }}</h3>
            <p>{{ item.availability }}</p>
          </div>
        </article>
      </div>
    </section>

    <section class="features">
      <div class="features-inner">
        <div class="trending-head">
          <h2>Browse by Category</h2>
        </div>
        <div class="features-divider"></div>
        <div class="features-grid">
          <article v-for="feature in features" :key="feature.title" class="feature-item">
            <div class="feature-content">
              <div class="feature-icon" aria-hidden="true">
                <span>{{ feature.icon }}</span>
              </div>
              <div class="feature-copy">
                <h4>
                  <span class="feature-step">{{ feature.step }}</span>
                  <span class="feature-title-text">{{ feature.title }}</span>
                </h4>
                <p>{{ feature.description }}</p>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="trending-head">
        <h2>Trending Rides</h2>
      </div>
      <div class="ride-grid">
        <article v-for="ride in rides" :key="ride.name" class="ride-card">
          <img :src="ride.image" :alt="ride.name" />
          <div class="ride-body">
            <h4>{{ ride.name }}</h4>
            <p>{{ ride.detail }}</p>
            <div class="ride-bottom">
              <span class="rating"><i class="fa-solid fa-star" aria-hidden="true"></i> {{ ride.rating }}</span>
              <span><strong>${{ ride.price }}</strong> /day</span>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="section cta-wrap">
      <div class="cta-card">
        <div class="cta-text">
          <h2>List Your Shop &amp; Start Earning Today</h2>
          <p>
            Join Cambodia's largest vehicle rental network and connect with more
            customers.
          </p>
          <RouterLink to="/register"><button type="button">Register Your Shop</button></RouterLink>
        </div>
        <div class="cta-device"></div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-main">
        <div class="footer-brand">
          <div class="footer-brand-head">
            <Logo class="footer-brand-logo" theme="dark" size="lg" :showTagline="true" src="/Images/logo-removebg.png" brandName="CHONG CHOUL" />
          </div>
          <p class="footer-desc">The premier vehicle rental marketplace in Cambodia.</p>
          <div class="footer-social" aria-label="Social media">
            <a class="footer-social-link" href="#" @click.prevent aria-label="Facebook">
              <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="Instagram">
              <i class="fa-brands fa-instagram" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="TikTok">
              <i class="fa-brands fa-tiktok" aria-hidden="true"></i>
            </a>
            <a class="footer-social-link" href="#" @click.prevent aria-label="X">
              <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
            </a>
          </div>
          <small class="footer-legal">© 2026 Chong Choul. All rights reserved.</small>
        </div>

        <div class="footer-col">
          <h5 class="footer-title">Payment Methods</h5>
          <ul class="footer-list">
            <li><span>Visa</span></li>
            <li><span>Mastercard</span></li>
            <li><span>Bank Transfer</span></li>
          </ul>
        </div>

        <div class="footer-col">
          <h5 class="footer-title">Quick Links</h5>
          <ul class="footer-list">
            <li><RouterLink class="footer-link" to="/">Home</RouterLink></li>
            <li><RouterLink class="footer-link" to="/view_shop">Explore Vehicles</RouterLink></li>
            <li><RouterLink class="footer-link" to="/register">List Your Shop</RouterLink></li>
            <li><RouterLink class="footer-link" to="/login">Login</RouterLink></li>
            <li><RouterLink class="footer-link" to="/chooserole">Sign Up</RouterLink></li>
          </ul>
        </div>

        <div class="footer-col">
          <h5 class="footer-title">Newsletter</h5>
          <p class="footer-note">Stay updated with the latest offers.</p>
          <form class="newsletter" @submit.prevent>
            <input type="email" placeholder="Your email" autocomplete="email" />
            <button type="submit">Send</button>
          </form>
        </div>
      </div>
    </footer>
  </main>
</template>

<script>
import Logo from '@/components/Logo.vue'
import '@/css/HomeView.css'

export default {
  components: {
    Logo,
  },
  data() {
    return {
      categories: [
        {
          tag: 'CITY',
          title: 'Cars',
          availability: '120+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/76/4d/1e/764d1e19a2fb69a9046e53ceb4381391.jpg'
        },
        {
          tag: 'POPULAR',
          title: 'Motorbikes',
          availability: '450+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/b3/a3/84/b3a384d5a8624aba2943bf7d41edd5e2.jpg'
        },
        {
          tag: 'ECO',
          title: 'Bicycles',
          availability: '85+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/9d/a8/87/9da8873b9c5bfdc2ac0dd4915e594d02.jpg'
        }
      ],
      features: [
        {
          icon: 'Q',
          step: '1.',
          title: 'Browse & Select',
          description: 'Filter by vehicle type, location, and your travel dates to find the best match.'
        },
        {
          icon: 'C',
          step: '2.',
          title: 'Secure Booking',
          description: 'Book instantly using ABA, Wing, or international cards. No hidden fees.'
        },
        {
          icon: 'K',
          step: '3.',
          title: 'Pick Up & Go',
          description: 'Meet your host at the shop or get the vehicle delivered to your hotel.'
        }
      ],
      rides: [
        {
          name: 'Small Camper Van/RV',
          detail: 'Small Camper Van/RV - Siem Reap',
          rating: '4.9',
          price: 120,
          image: 'https://i.pinimg.com/1200x/aa/4f/54/aa4f548f44f2ad9a5917c99bb85ad06a.jpg'
        },
        {
          name: 'Adventure Touring Motorcycle',
          detail: 'Motorbike - Siem Reap',
          rating: '4.8',
          price: 12,
          image: 'https://i.pinimg.com/1200x/29/d1/13/29d11367c93335cdd7232c6f0594c344.jpg'
        },
        {
          name: 'Road Bicycle',
          detail: 'Bicycle - Siem Reap',
          rating: '4.7',
          price: 5,
          image: 'https://i.pinimg.com/1200x/02/32/61/0232617f4158002b9f542b15f113cd9b.jpg'
        },
        {
          name: 'Step-through City Bicycle',
          detail: 'Bicycle - Siem Reap',
          rating: '5.0',
          price: 25,
          image: 'https://i.pinimg.com/1200x/26/88/ba/2688ba0b802e2a168450e8f598ddc8d9.jpg'
        }
      ]
    };
  }
};
</script>
<<<<<<< HEAD

<<<<<<< HEAD
>>>>>>> b11f4a5c301bc31d8d2aac6512ebafd9c7cae5ac
=======
<style>
@import "../css/HomeView.css";

.home-page .common-footer {
  margin-top: 0;
}
</style>



>>>>>>> d9950b55d15a8c1d02e11f6e24682b1f5b876a67
=======
>>>>>>> 4ecff457c455e0fa2d252bec5ddf48f9ed99a23b
