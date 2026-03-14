<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { userService, shopService } from '../../services/database.js'
import Logo from '../../components/Logo.vue'
import ConfirmModal from '../../components/ConfirmModal.vue'
import '../../css/userDashboard.css'

const router = useRouter()
const route = useRoute()

const shops = ref([])
const isLoadingShops = ref(false)
const shopsError = ref('')
const showAllShops = ref(false)
const expandedShops = ref(new Set())
const isProfileDropdownOpen = ref(false)
const showLogoutConfirm = ref(false)

// Navigation items
const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '' },
  { label: 'Promotions', route: '/promotions' }
]

const activeNav = computed(() => {
  const currentPath = route.path
  const matchedItem = navItems.find((item) => item.route && currentPath.startsWith(item.route))
  return matchedItem?.label || 'Home'
})

const setActiveNav = (item) => {
  if (item.route) { router.push(item.route); return; }
  console.log('My Bookings page is not available yet.')
}

// User info
const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'customer')

const userInitials = computed(() => {
  const words = userDisplayName.value.trim().split(/\s+/).filter(Boolean)
  if (words.length === 0) return 'CU'
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

// Slideshow - Main Hero Background
const slides = ref([
  { 
    id: 1, 
    title: 'Explore New Places', 
    description: 'Adventure awaits! Rent a car and discover amazing destinations.', 
    image: 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?w=1920&q=80'
  },
  { 
    id: 2, 
    title: 'Discover Cambodia', 
    description: 'Experience the beauty of Siem Reap and beyond with our premium rentals.', 
    image: 'https://images.unsplash.com/photo-1537956965359-7573183d1f57?w=1920&q=80'
  },
  { 
    id: 3, 
    title: 'Premium Vehicles', 
    description: 'Choose from our wide selection of quality cars and motorbikes.', 
    image: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1920&q=80'
  }
])

const activeSlideIndex = ref(0)
const isAnimating = ref(false)

const goToSlide = (index) => {
  if (isAnimating.value || index === activeSlideIndex.value) return
  isAnimating.value = true
  activeSlideIndex.value = index
  setTimeout(() => {
    isAnimating.value = false
  }, 800)
}

const nextSlide = () => {
  const next = (activeSlideIndex.value + 1) % slides.value.length
  goToSlide(next)
}

const prevSlide = () => {
  const prevIndex = (activeSlideIndex.value - 1 + slides.value.length) % slides.value.length
  goToSlide(prevIndex)
}

let slideIntervalId = null
const startAutoSlide = () => {
  stopAutoSlide()
  slideIntervalId = setInterval(nextSlide, 5000)
}
const stopAutoSlide = () => {
  if (slideIntervalId) {
    clearInterval(slideIntervalId)
    slideIntervalId = null
  }
}

// Carousel - Right side images
const carouselImages = ref([
  { id: 1, image: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800&q=80', alt: 'Desert landscape with rocks' },
  { id: 2, image: 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=800&q=80', alt: 'Person driving car' },
  { id: 3, image: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=800&q=80', alt: 'Travel photo' }
])

const activeCarouselIndex = ref(0)

const goToCarouselSlide = (index) => {
  activeCarouselIndex.value = index
}

const nextCarouselSlide = () => {
  activeCarouselIndex.value = (activeCarouselIndex.value + 1) % carouselImages.value.length
}

const prevCarouselSlide = () => {
  activeCarouselIndex.value = (activeCarouselIndex.value - 1 + carouselImages.value.length) % carouselImages.value.length
}

let carouselIntervalId = null
const startCarouselAutoSlide = () => {
  stopCarouselAutoSlide()
  carouselIntervalId = setInterval(nextCarouselSlide, 4000)
}
const stopCarouselAutoSlide = () => {
  if (carouselIntervalId) {
    clearInterval(carouselIntervalId)
    carouselIntervalId = null
  }
}

// Helper functions for image URL resolution
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

const withCacheBust = (url, version) => {
  if (!url || typeof url !== 'string') return url
  const separator = url.includes('?') ? '&' : '?'
  return `${url}${separator}v=${encodeURIComponent(String(version))}`
}

// Shop loading
const normalizeShop = (shop) => ({
  id: shop.id,
  name: shop.name || `Shop #${shop.id}`,
  address: shop.address || 'No address available',
  phone: shop.phone || 'N/A',
  email: shop.email || shop.owner?.email || 'N/A',
  ownerName: shop.owner?.name || '',
  initials: getInitials(shop.name || 'Shop'),
  rating: Number(shop.rating || 0),
  status: shop.status || 'active',
  image: (shop.img_url || shop.image_url || shop.image)
    ? withCacheBust(
        resolveShopImageUrl(shop.img_url || shop.image_url || shop.image),
        shop.updated_at || shop.id || Date.now()
      )
    : '',
})

const getInitials = (text, fallback = 'S') => {
  const value = String(text || '').trim()
  if (!value) return fallback
  const words = value.split(/\s+/).filter(Boolean)
  if (!words.length) return fallback
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
}

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

const visibleShops = computed(() => showAllShops.value ? shops.value : shops.value.slice(0, 8))
const hasMoreShops = computed(() => shops.value.length > 8)

const toggleShopGrid = () => { 
  showAllShops.value = !showAllShops.value 
}

const viewShopVehicles = (shop) => router.push({ name: 'vehicles-by-shop', query: { shop_id: String(shop.id) } })

// Logout
const handleLogout = async () => {
  isProfileDropdownOpen.value = false
  showLogoutConfirm.value = true
}

const confirmLogout = async () => {
  await userService.logout()
  router.push('/login')
}

const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value
}

const openProfile = () => {
  router.push('/user/profile')
  isProfileDropdownOpen.value = false
}

const openSettings = () => {
  router.push('/user/settings')
  isProfileDropdownOpen.value = false
}

// Lifecycle
onMounted(() => {
  loadShops()
  startAutoSlide()
  startCarouselAutoSlide()
})

onBeforeUnmount(() => {
  stopAutoSlide()
  stopCarouselAutoSlide()
})
</script>

<template>
  <div class="user-dashboard">
    <!-- Navigation Bar -->
    <header class="topbar">
      <!-- Brand / Logo -->
      <div class="brand">
        <img class="brand-icon" src="/images/chong-choul-logo.svg" alt="Chong Choul Logo" />
        <span>Chong Choul</span>
      </div>

      <!-- Navigation Links -->
      <nav class="nav-links">
        <button 
          v-for="item in navItems" 
          :key="item.label" 
          class="btn-reset nav-link" 
          :class="{ active: activeNav === item.label }" 
          @click="setActiveNav(item)"
        >
          {{ item.label }}
        </button>
      </nav>

      <!-- User Area with Profile Dropdown -->
      <div class="top-actions">
        <div class="profile-menu" @click.outside="isProfileDropdownOpen = false">
          <button class="btn-reset avatar" @click="toggleProfileDropdown">
            <div class="user-avatar">{{ userInitials }}</div>
          </button>
          <div v-if="isProfileDropdownOpen" class="profile-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-user-info">
                <span class="dropdown-user-name">{{ userDisplayName }}</span>
                <span class="dropdown-user-email">{{ currentUser?.email }}</span>
              </div>
            </div>
            <div class="dropdown-menu-items">
              <button class="dropdown-item" @click="openProfile">
                <i class="fa-solid fa-user"></i>
                <span>Profile</span>
              </button>
              <button class="dropdown-item" @click="openSettings">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
              </button>
              <hr class="dropdown-divider">
              <button class="dropdown-item logout-item" @click="handleLogout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Hero Section with Slideshow -->
    <section class="slideshow-section">
      <div class="slideshow-container">
        <!-- Main Background Slides -->
        <div 
          v-for="(slide, index) in slides" 
          :key="slide.id"
          class="slide"
          :class="{ active: index === activeSlideIndex }"
        >
          <div 
            class="slide-background" 
            :style="`background-image: url('${slide.image}')`"
          ></div>
          
          <!-- Left Content -->
          <div class="slide-content">
            <div class="slide-content-inner">
              <h1 class="slide-title">{{ slide.title }}</h1>
              <p class="slide-subtitle">{{ slide.description }}</p>
              <button class="see-more-btn">See More</button>
            </div>
          </div>
        </div>

        <!-- Slide Navigation Dots -->
        <div class="slide-dots">
          <button 
            v-for="(slide, index) in slides" 
            :key="slide.id"
            class="slide-dot"
            :class="{ active: index === activeSlideIndex }"
            @click="goToSlide(index)"
          ></button>
        </div>
      </div>
    </section>

    <!-- Shops Section -->
    <section class="shops-section">
      <div class="results-head">
        <div class="choose-shop-box">
          <h2>Choose a Shop</h2>
          <p>Select one shop to view its vehicles.</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoadingShops" class="status-box">Loading shops...</div>
      
      <!-- Error State -->
      <div v-else-if="shopsError" class="status-box error">{{ shopsError }}</div>
      
      <!-- No Shops -->
      <div v-else-if="shops.length === 0" class="status-box">No shops found in the system.</div>
      
      <!-- Shops Grid -->
      <div v-else class="shops-grid">
        <article 
          v-for="shop in visibleShops" 
          :key="shop.id" 
          class="shop-card"
        >
          <div class="shop-card-image">
            <img 
              v-if="shop.image" 
              :src="shop.image" 
              :alt="shop.name" 
              @error="shop.image = ''" 
            />
            <div v-else class="status-box" style="margin: 10px">No shop image</div>
            
            <!-- Active Badge -->
            <span class="status-badge active">
              <span class="status-green-dot"></span>
              ACTIVE
            </span>
            
            <!-- Shop Initials Circle -->
            <div class="shop-brand-circle">
              {{ shop.initials }}
            </div>
          </div>
          
          <div class="shop-card-body">
            <h3 class="shop-name">{{ shop.name }}</h3>
            
            <!-- Action Buttons -->
            <div class="shop-card-actions">
              <button class="see-more-link">See more</button>
              <button class="view-vehicles-btn" @click="viewShopVehicles(shop)">
                View Vehicles
              </button>
            </div>
          </div>
        </article>
      </div>

      <!-- Show More Button -->
      <div v-if="hasMoreShops" class="shops-grid-footer" style="text-align: center; margin-top: 40px;">
        <button 
          class="btn-reset see-more-link" 
          style="font-size: 16px; color: #2563eb;" 
          type="button" 
          @click="toggleShopGrid"
        >
          {{ showAllShops ? 'Show less shops' : 'See more shops' }}
        </button>
      </div>
    </section>

    <!-- Footer -->
    <footer class="page-footer">
      <div class="footer-top">
        <div class="footer-brand">
          <h4>Chong Choul<span>Rides</span></h4>
          <p>Connecting adventurous travelers with the best local vehicle rentals across the Kingdom of Wonder.</p>
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

    <ConfirmModal
      v-model="showLogoutConfirm"
      title="Logout"
      message="Are you sure you want to logout?"
      cancel-text="Cancel"
      confirm-text="Yes"
      variant="danger"
      @confirm="confirmLogout"
    />
  </div>
</template>
