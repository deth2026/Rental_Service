<script setup>
import { RouterLink } from 'vue-router'

const quickLinks = [
  { label: 'Home', to: '/' },
  { label: 'My Booking', to: '/bookings' },
  { label: 'Promotions', to: '/promotions' }
]
const paymentMethods = ['Visa', 'Mastercard', 'Bank Transfer']
const socialLinks = [
  { label: 'Facebook', href: 'https://www.facebook.com', icon: 'fa-brands fa-facebook' },
  { label: 'Instagram', href: 'https://www.instagram.com', icon: 'fa-brands fa-instagram' },
  { label: 'TikTok', href: 'https://www.tiktok.com', icon: 'fa-brands fa-tiktok' }
]
const currentYear = new Date().getFullYear()
</script>

<template>
  <footer class="common-footer">
    <div class="common-footer__content">
      <div class="common-footer__brand">
        <h4>Chong Choul</h4>
        <p>
          Connecting adventurous travelers with the best local vehicle rentals across the Kingdom of Wonder.
        </p>
      </div>

      <div class="common-footer__col">
        <h5>Quick Links</h5>
        <RouterLink
          v-for="item in quickLinks"
          :key="item.label"
          :to="item.to"
          class="common-footer__link"
        >
          {{ item.label }}
        </RouterLink>
      </div>

      <div class="common-footer__col">
        <h5>Payment Methods</h5>
        <span v-for="method in paymentMethods" :key="method" class="common-footer__link">
          {{ method }}
        </span>
      </div>

      <div class="common-footer__col">
        <h5>Social Medias</h5>
        <a
          v-for="social in socialLinks"
          :key="social.label"
          :href="social.href"
          class="common-footer__link"
          target="_blank"
          rel="noopener noreferrer"
        >
          <i :class="social.icon" aria-hidden="true"></i>
          <span>{{ social.label }}</span>
        </a>
      </div>
    </div>

    <div class="common-footer__divider"></div>

    <div class="common-footer__bottom">
      <span>
        &copy; {{ currentYear }} Chong Choul Rides. Made with
        <span class="common-footer__heart" aria-hidden="true">&hearts;</span>
        in Siem Reap.
      </span>
      <div class="common-footer__legal">
        <a href="#" class="common-footer__legal-link">Privacy Policy</a>
        <a href="#" class="common-footer__legal-link">Terms of Service</a>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.common-footer {
  background: #f4f7fb;
  color: #1a2235;
  padding: 38px 24px 26px;
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.common-footer__content {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, minmax(180px, 1fr));
  gap: 32px;
  align-items: flex-start;
}

.common-footer__brand h4 {
  margin: 0 0 6px;
  font-size: 1.5rem;
  font-weight: 700;
  color: #0083ff;
}

.common-footer__brand p {
  margin: 0;
  color: #4e5c79;
  line-height: 1.6;
}

.common-footer__col h5 {
  margin: 0 0 10px;
  font-size: 0.95rem;
  letter-spacing: 0.08em;
  color: #2a324d;
}

.common-footer__link {
  display: block;
  font-size: 0.92rem;
  color: #4d586f;
  text-decoration: none;
  margin-bottom: 6px;
  transition: color 0.2s ease;
}

.common-footer__link i {
  margin-right: 6px;
  font-size: 0.95rem;
}

.common-footer__link:hover {
  color: #0b3ca1;
}

.common-footer__heart {
  display: inline-block;
  margin: 0 4px;
  color: #ff5d7d;
}

.common-footer__divider {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  border-top: 1px solid #cdd3e1;
}

.common-footer__bottom {
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.92rem;
  color: #5b6378;
  padding-top: 12px;
}

.common-footer__legal {
  display: flex;
  gap: 26px;
  flex-wrap: wrap;
  font-style: italic;
}

.common-footer__legal-link {
  color: #5b6378;
  text-decoration: none;
}

.common-footer__legal-link:hover {
  color: #0b3ca1;
}

@media (max-width: 1024px) {
  .common-footer__content {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  .common-footer__bottom {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
}

@media (max-width: 640px) {
  .common-footer {
    padding: 32px 16px 20px;
  }

  .common-footer__content {
    grid-template-columns: 1fr;
  }
}
</style>
