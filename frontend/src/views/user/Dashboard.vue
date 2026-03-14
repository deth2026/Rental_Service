<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService, shopService } from '../../services/database.js'
import '../../css/userDashboard.css'

const router = useRouter()

const shops = ref([])
const isLoadingShops = ref(false)
const shopsError = ref('')
const isMobileMenuOpen = ref(false)
const showLogoutConfirm = ref(false)

// Navbar state
const location = ref('Phnom Penh')
const dateRange = ref('Mar 15 - Mar 18')
const navItems = ref(['Home', 'Viewdetails', 'Bookings'])
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
const avatarLoadFailed = ref(false)

const normalizeAvatarUrl = (url) => {
  if (!url) return ''
  if (/^(https?:\/\/|data:|blob:)/i.test(url)) return url
  const normalized = String(url).replace(/\\/g, '/').replace(/^\/+/, '')
  if (normalized.startsWith('storage/')) return `/${normalized}`
  return `/storage/${normalized}`
}

const userAvatarUrl = computed(() => {
  if (avatarLoadFailed.value) return ''
  const src = currentUser.value?.avatar_url || currentUser.value?.profile_picture || currentUser.value?.img_url || ''
  return normalizeAvatarUrl(src)
})

const onAvatarError = () => {
  avatarLoadFailed.value = true
}

const userInitials = computed(() => {
  const words = userDisplayName.value.trim().split(/\s+/).filter(Boolean)
  if (words.length === 0) return 'GU'
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

const withCacheBust = (url, version) => {
  if (!url || typeof url !== 'string') return url
  const separator = url.includes('?') ? '&' : '?'
  return `${url}${separator}v=${encodeURIComponent(String(version))}`
}

const getApiOrigin = () => {
  try {
    const currentOrigin = window.location.origin
    if (currentOrigin.includes('5173')) {
      return 'http://127.0.0.1:8000'
    }
    return currentOrigin
  } catch {
    return 'http://127.0.0.1:8000'
  }
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
  const clean = value.replace(/^\/+/, '')
  if (clean.startsWith('storage/')) {
    return `${getApiOrigin()}/${clean}`
  }
  return `${getApiOrigin()}/storage/${clean}`
}

const getInitials = (text, fallback = 'S') => {
  const value = String(text || '').trim()
  if (!value) return fallback
  const words = value.split(/\s+/).filter(Boolean)
  if (!words.length) return fallback
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
}

const normalizeShop = (shop) => ({
  id: shop.id,
  name: shop.name || `Shop #${shop.id}`,
  address: shop.address || 'No address available',
  phone: shop.phone || 'N/A',
  email: shop.email || shop.owner?.email || 'N/A',
  ownerName: shop.owner?.name || '',
  ownerAvatar: shop.owner?.img_url || shop.owner?.profile_picture || shop.owner?.avatar_url || '',
  rating: Number(shop.rating || 0),
  status: shop.status || 'active',
  image: (shop.img_url || shop.image_url || shop.image)
    ? withCacheBust(
        resolveShopImageUrl(shop.img_url || shop.image_url || shop.image),
        shop.updated_at || shop.id || Date.now()
      )
    : '',
})

const loadShops = async () => {
  isLoadingShops.value = true
  shopsError.value = ''

  try {
    const data = await shopService.getShops()
    shops.value = Array.isArray(data) ? data.map((shop) => normalizeShop(shop)) : []
  } catch (error) {
    shopsError.value = error.message || 'Failed to load shops.'
  } finally {
    isLoadingShops.value = false
  }
}

const viewShopVehicles = (shop) => {
  router.push({ name: 'vehicles-by-shop', query: { shop_id: String(shop.id) } })
}

const handleLogout = () => {
  showLogoutConfirm.value = true
}

const confirmLogout = async () => {
  showLogoutConfirm.value = false
  await userService.logout()
  router.push('/login')
}

const cancelLogout = () => {
  showLogoutConfirm.value = false
}

const openProfile = () => {
  router.push('/user/profile')
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
          <button class="btn-reset avatar" @click="openProfile">
            <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile photo" class="avatar-image" @error="onAvatarError" />
            <span v-else>{{ userInitials }}</span>
          </button>
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
              <img v-if="shop.image" :src="shop.image" :alt="shop.name" @error="shop.image = ''" />
              <div v-else class="status-box" style="margin: 10px">No shop image</div>
              <span :class="'status-badge status-' + (shop.status || 'active')">
                <span v-if="(shop.status || 'active') === 'active'" class="status-green-dot"></span>
                {{ shop.status || 'active' }}
              </span>
            </div>
            <div class="shop-card-top">
              <div class="shop-brand-mark">
                <img
                  v-if="shop.ownerAvatar"
                  :src="resolveShopImageUrl(shop.ownerAvatar)"
                  :alt="shop.ownerName || shop.name"
                  class="shop-owner-avatar-img"
                />
                <span v-else>{{ getInitials(shop.ownerName || shop.name, 'S') }}</span>
              </div>
              <div class="shop-title-block">
                <p class="shop-profile-eyebrow">Rental Shop</p>
                <h3>{{ shop.name }}</h3>
              </div>
            </div>

            <div class="shop-contact-grid">
              <div class="shop-info">
                <span>Email</span>
                <strong>{{ shop.email }}</strong>
              </div>
              <div class="shop-info">
                <span> Phone</span>
                <strong>{{ shop.phone }}</strong>
              </div>
              <div class="shop-info" style="grid-column: span 2">
                <span>Address</span>
                <strong>{{ shop.address }}</strong>
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

            <button class="view-shop-btn" type="button" @click="viewShopVehicles(shop)">View Vehicles</button>
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
            <a href="#">Home</a>
            <a href="#">My booking</a>
            <a href="#">Promotions</a>
          </div>

          <div class="footer-col">
            <h5>Support</h5>
            <a href="#">Help Center</a>
            <a href="#">Become a Partner</a>
            <a href="#">Contact Us</a>
          </div>
        </div>

        <div class="footer-bottom">
          <p class="footer-copy">&copy; 2026 Chong Choul Rides. Made with ❤️ in Phnom Penh.</p>
          <div class="footer-legal">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div v-if="showLogoutConfirm" class="confirm-overlay" @click="cancelLogout">
    <div class="confirm-modal" @click.stop>
      <div class="confirm-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
      </div>
      <p class="confirm-title">Logout</p>
      <p class="confirm-text">Are you sure you want to logout from your account?</p>
      <div class="confirm-actions">
        <button class="confirm-cancel-btn" @click="cancelLogout">No</button>
        <button class="confirm-logout-btn" @click="confirmLogout">Yes,Logout</button>
      </div>
    </div>
  </div>
</template>
