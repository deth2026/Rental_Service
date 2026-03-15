<template>
  <main>
    <Logo />
    <header class="top-nav">
      <div class="brand-container">
<<<<<<< Updated upstream
<<<<<<< HEAD
        <img class="brand-logo" src="@/assets/Logo.png" alt="CHONG CHOUL VEHICLE SERVICES" />
=======
        <img class="brand-logo" src="/images/chong-choul-logo.svg" alt="CHONG CHOUL VEHICLE SERVICES" />
>>>>>>> dashboard/admin
=======
        <img class="brand-logo" src="@/assets/logo.png" alt="CHONG CHOUL VEHICLE SERVICES" />
>>>>>>> Stashed changes
        <div class="brand-text">
          <div class="brand">Chong Choul</div>
          <!-- <div class="brand-tagline">Vehicle Rental</div> -->
        </div>
      </div>

      <div class="nav-auth">
        <RouterLink class="link-login" to="/login">Login</RouterLink>
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
        </div>
      </div>
    </section>

    <section class="section">
      <div class="section-head">
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

<<<<<<< Updated upstream
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
=======
>>>>>>> Stashed changes
    <footer class="footer">
      <div class="footer-main">
        <div>
          <h4>Chong Choul</h4>
          <p>The premier vehicle rental marketplace in Cambodia.</p>
        </div>
        <div>
          <h5>Payment Methods</h5>
          <a href="#">Visa </a>
          <a href="#">Mastercard</a>
          <a href="#">Bank Transfer</a>
        </div>
        <div>
          <h5>Social Medias</h5>
          <a href="#">Facebook</a>
          <a href="#">Instagram</a>
          <a href="#">TikTok</a>
        </div>
        <div>
          <h5>Newsletter</h5>
          <p>Stay updated with latest offers.</p>
          <div class="newsletter">
            <input type="text" placeholder="Your email" />
            <button type="button">Send</button>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <small> 2026 Chong Choul. All rights reserved.</small>
      </div>
    </footer>
  </main>
<<<<<<< Updated upstream
<<<<<<< HEAD
=======

  </div>
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
=======
>>>>>>> Stashed changes
</template>

<script>
import Logo from '@/components/Logo.vue';

export default {
  components: { Logo },
  data() {
    return {
      search: {
        type: 'All Vehicles',
        location: 'Phnom Penh',
        pickupDate: ''
      },
      categories: [
        {
          title: 'Cars',
          availability: '120+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/76/4d/1e/764d1e19a2fb69a9046e53ceb4381391.jpg'
        },
        {
          title: 'Motorbikes',
          availability: '450+ Vehicles Available',
          image: 'https://i.pinimg.com/1200x/b3/a3/84/b3a384d5a8624aba2943bf7d41edd5e2.jpg'
        },
        {
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

<style scoped>
@import "../css/HomeView.css";
</style>
