<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService, shopService } from '../../services/database.js'
import '../css/userDashboard.css'

const router = useRouter()

const shops = ref([])
const isLoadingShops = ref(false)
const shopsError = ref('')
const isMobileMenuOpen = ref(false)

// Navbar state
const location = ref('Phnom Penh')
const dateRange = ref('Mar 15 - Mar 18')
const navItems = ref(['Home', 'My Bookings', 'About'])
const activeNav = ref('Home')

const setActiveNav = (item) => {
  activeNav.value = item
}

const handleSearch = () => {
  console.log('Search clicked')
}

const notify = (message) => {
  console.log(message)
}

const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')
const userInitials = computed(() => {
  const words = userDisplayName.value.trim().split(/\s+/).filter(Boolean)
  if (words.length === 0) return 'GU'
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

const shopImages = [
  'https://images.unsplash.com/photo-1590674899484-d5640e854abe?auto=format&fit=crop&w=600&q=80',
  'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80',
  'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=600&q=80',
  'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?auto=format&fit=crop&w=600&q=80',
  'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=600&q=80',
  'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=600&q=80',
]

const withCacheBust = (url, version) => {
  if (!url || typeof url !== 'string') return url
  const separator = url.includes('?') ? '&' : '?'
  return `${url}${separator}v=${encodeURIComponent(String(version))}`
}

const resolveShopImageUrl = (value) => {
  if (!value || typeof value !== 'string') return value
  if (
    value.startsWith('http://') ||
    value.startsWith('https://') ||
    value.startsWith('blob:') ||
    value.startsWith('data:')
  ) {
    return value
  }
  if (value.startsWith('/')) {
    return value
  }
  return `/${value}`
}

const normalizeShop = (shop, index) => ({
  id: shop.id,
  name: shop.name || `Shop #${shop.id}`,
  address: shop.address || 'No address available',
  phone: shop.phone || 'N/A',
  email: shop.email || 'N/A',
  rating: Number(shop.rating || 0),
  status: shop.status || 'active',
  image: shop.image
    ? withCacheBust(resolveShopImageUrl(shop.image), shop.updated_at || shop.id || Date.now())
    : shopImages[index % shopImages.length],
})

const loadShops = async () => {
  isLoadingShops.value = true
  shopsError.value = ''

  try {
    const data = await shopService.getShops()
    shops.value = Array.isArray(data) ? data.map((shop, index) => normalizeShop(shop, index)) : []
  } catch (error) {
    shopsError.value = error.message || 'Failed to load shops.'
  } finally {
    isLoadingShops.value = false
  }
}

const handleLogout = async () => {
  await userService.logout()
  router.push('/login')
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

onMounted(() => {
  loadShops()
})
</script>

<template>
  <div class="rides-page">
    <div class="rides-shell">
      <header class="topbar">
        <div class="brand">
          <div class="brand-icon"><i class="fa-solid fa-gift" aria-hidden="true"></i></div>
          <span>Chong Choul</span>
        </div>

        <nav class="nav-links">
          <button
            v-for="item in navItems"
            :key="item"
            class="btn-reset nav-link"
            :class="{ active: activeNav === item }"
            @click="setActiveNav(item)"
          >
            {{ item }}
          </button>
        </nav>

        <div class="top-actions">
          <span class="user-display-name">{{ userDisplayName }}</span>
          <button class="btn-reset avatar" @click="notify('Profile opened')">{{ userInitials }}</button>
          <button class="btn-reset logout-btn" @click="handleLogout"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> <span>Logout</span></button>
        </div>
      </header>

      <section class="shops-section">
        <div class="results-head">
          <div class="choose-shop-box">
            <h2>Choose a Shop</h2>
            <p>Select one shop to view its vehicles.</p>
          </div>
        </div>

        <div v-if="isLoadingShops" class="status-box">Loading shops...</div>
        <div v-else-if="shopsError" class="status-box error">{{ shopsError }}</div>
        <div v-else-if="shops.length === 0" class="status-box">No shops found in the system.</div>

        <div v-else class="shops-grid">
          <article v-for="shop in shops" :key="shop.id" class="shop-card">
            <div class="shop-card-image">
              <img
                :src="shop.image"
                :alt="shop.name"
                @error="$event.target.src = 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80'"
              />
              <span :class="'status-badge status-' + (shop.status || 'active')">
                <span v-if="(shop.status || 'active') === 'active'" class="status-green-dot"></span>
                {{ shop.status || 'active' }}
              </span>
            </div>
            <div class="shop-card-top">
              <div class="shop-brand-mark">{{ (shop.name || 'S').charAt(0).toUpperCase() }}</div>
              <div class="shop-title-block">
                <p class="shop-profile-eyebrow">Rental Shop</p>
                <h3>{{ shop.name }}</h3>
                <p>Shop ID #{{ shop.id }}</p>
              </div>
            </div>

            <div class="shop-chip-row">
              <span class="shop-chip">📍 {{ shop.address.substring(0, 20) }}{{ shop.address.length > 20 ? '...' : '' }}</span>
              <span class="shop-chip">⭐ {{ shop.rating.toFixed(1) }} Rating</span>
            </div>

            <div class="shop-contact-grid">
              <div class="shop-info">
                <span>📍 Address</span>
                <strong>{{ shop.address }}</strong>
              </div>
              <div class="shop-info">
                <span>📞 Phone</span>
                <strong>{{ shop.phone }}</strong>
              </div>
              <div class="shop-info" style="grid-column: span 2">
                <span>✉️ Email</span>
                <strong>{{ shop.email }}</strong>
              </div>
            </div>

            <div class="shop-kpi-row">
              <div class="shop-kpi">
                <span>Customer Rating</span>
                <strong>⭐ {{ shop.rating.toFixed(1) }}</strong>
              </div>
              <div class="shop-kpi">
                <span>Shop Status</span>
                <strong>{{ shop.status === 'active' ? '🟢 Open' : '🔴 Closed' }}</strong>
              </div>
            </div>

            <button class="view-shop-btn" type="button">View Vehicles</button>
          </article>
        </div>
      </section>

      <footer class="page-footer">
        <div class="footer-top">
          <div class="footer-brand">
            <h4>Chong Choul<span>Rides</span></h4>
            <p>
              Connecting adventurous travelers with the best local vehicle rentals across the
              Kingdom of Wonder. Explore Cambodia on your own terms.
            </p>
          </div>

          <div class="footer-col">
            <h5>Quick Links</h5>
            <a href="#">How it works</a>
            <a href="#">Trust & Safety</a>
            <a href="#">Rental Policies</a>
          </div>

          <div class="footer-col">
            <h5>Support</h5>
            <a href="#">Help Center</a>
            <a href="#">Become a Partner</a>
            <a href="#">Contact Us</a>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="footer-copy">&copy; 2023 Chong Choul Rides. Made with ❤️ in Phnom Penh.</p>
          <div class="footer-legal">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>
