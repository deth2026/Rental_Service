<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { userService, shopService } from '../../services/database.js'
import '../../css/userDashboard.css'
import CommonFooter from '../../components/CommonFooter.vue'
import UserNavbar from '@/components/UserNavbar.vue'

const router = useRouter()

const showLogoutConfirm = ref(false)
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

const shops = ref([])
const isLoadingShops = ref(false)
const shopsError = ref('')
const showAllShops = ref(false)
const expandedShops = ref(new Set())
const shopDetailRefs = new Map()
const scrollableShopIds = ref(new Set())

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
  // Use img_url_full accessor from backend if available
  image: shop.img_url_full 
    ? withCacheBust(shop.img_url_full, shop.updated_at || shop.id || Date.now())
    : (shop.img_url || shop.image_url || shop.image)
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

const visibleShops = computed(() => {
  if (showAllShops.value) return shops.value
  return shops.value.slice(0, 6)
})

const hasMoreShops = computed(() => shops.value.length > 6)

const toggleShopGrid = () => {
  showAllShops.value = !showAllShops.value
  scheduleScrollableUpdate()
}

const toggleShopDetails = (shopId) => {
  const nextSet = new Set(expandedShops.value)
  if (nextSet.has(shopId)) {
    nextSet.delete(shopId)
  } else {
    nextSet.add(shopId)
  }
  expandedShops.value = nextSet
  scheduleScrollableUpdate()
}

const isShopExpanded = (shopId) => expandedShops.value.has(shopId)
const isShopScrollable = (shopId) => scrollableShopIds.value.has(shopId)

const viewShopVehicles = (shop) => {
  router.push({ name: 'vehicles-by-shop', query: { shop_id: String(shop.id) } })
}

watch(shops, () => {
  expandedShops.value = new Set()
  showAllShops.value = false
  scheduleScrollableUpdate()
})

watch([showAllShops, expandedShops], () => {
  scheduleScrollableUpdate()
})

const setShopDetailRef = (shopId) => (el) => {
  if (el) {
    shopDetailRefs.set(shopId, el)
  } else {
    shopDetailRefs.delete(shopId)
  }
}

const updateScrollableShops = () => {
  const nextSet = new Set()
  shopDetailRefs.forEach((el, shopId) => {
    if (!el) return
    if (!expandedShops.value.has(shopId)) return
    const maxHeightValue = Number.parseFloat(window.getComputedStyle(el).maxHeight || '0')
    const maxHeight = Number.isFinite(maxHeightValue) && maxHeightValue > 0 ? maxHeightValue : el.clientHeight
    if (el.scrollHeight > maxHeight + 1) {
      nextSet.add(shopId)
    }
  })
  scrollableShopIds.value = nextSet
}

const scheduleScrollableUpdate = () => {
  nextTick(updateScrollableShops)
}

const slides = ref([
  {
    id: 1,
    title: 'Rent Your Perfect Ride',
    description: 'Choose from our wide selection of quality vehicles for your journey.',
    image: 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=1920&q=80'
  },
  {
    id: 2,
    title: 'Explore New Places',
    description: 'Adventure awaits! Rent a car and discover amazing destinations.',
    image: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=1920&q=80'
  },
  {
    id: 3,
    title: 'Premium Vehicles',
    description: 'Experience comfort and style with our premium rental fleet.',
    image: 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=1920&q=80'
  },
  {
    id: 4,
    title: 'Easy Booking',
    description: 'Simple and fast rental process - book your vehicle in minutes.',
    image: 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=1920&q=80'
  },
  {
    id: 5,
    title: 'Affordable Rates',
    description: 'Get the best deals on quality vehicle rentals.',
    image: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1920&q=80'
  },
  {
    id: 6,
    title: '24/7 Support',
    description: 'We are here to help you anytime, anywhere.',
    image: 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?w=1920&q=80'
  }
])

const activeSlideIndex = ref(0)
const orderedSlides = computed(() => {
  if (!slides.value.length) return []
  const start = activeSlideIndex.value
  return slides.value.map((_, index) => slides.value[(start + index) % slides.value.length])
})

const goToNextSlide = () => {
  if (!slides.value.length) return
  activeSlideIndex.value = (activeSlideIndex.value + 1) % slides.value.length
}

const goToPrevSlide = () => {
  if (!slides.value.length) return
  activeSlideIndex.value =
    (activeSlideIndex.value - 1 + slides.value.length) % slides.value.length
}

const handleNextSlide = () => {
  goToNextSlide()
  restartSlideTimer()
}

const handlePrevSlide = () => {
  goToPrevSlide()
  restartSlideTimer()
}

let slideIntervalId = null

const stopSlideTimer = () => {
  if (slideIntervalId) {
    clearInterval(slideIntervalId)
    slideIntervalId = null
  }
}

const startSlideTimer = () => {
  if (typeof window === 'undefined') return
  stopSlideTimer()
  slideIntervalId = window.setInterval(goToNextSlide, 5000)
}

const restartSlideTimer = () => {
  startSlideTimer()
}

onMounted(() => {
  loadShops()
  startSlideTimer()
  window.addEventListener('resize', updateScrollableShops)
})

onBeforeUnmount(() => {
  stopSlideTimer()
  window.removeEventListener('resize', updateScrollableShops)
})
</script>

<template>
  <div class="dashboard-wrapper">
  <div class="rides-page">
    <div class="rides-shell">
      <UserNavbar @logout-request="handleLogout" />

      <section class="slideshow-section">
      <div class="containers">
        <div class="slide">
          <article
            v-for="(slide, index) in orderedSlides"
            :key="slide.id"
            class="item"
            :class="`item-${index + 1}`"
            :style="`background-image: url('${slide.image}')`"
          >
            <div class="content">
              <div class="name">{{ slide.title }}</div>
              <div class="des">{{ slide.description }}</div>
              <button type="button">See More</button>
            </div>
          </article>
        </div>
          <div class="button">
            <button class="btn-reset prev" type="button" aria-label="Previous slide" @click="handlePrevSlide">
              <i class="fa-solid fa-arrow-left"></i>
            </button>
            <button class="btn-reset next" type="button" aria-label="Next slide" @click="handleNextSlide">
              <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </section>

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
          <article
            v-for="shop in visibleShops"
            :key="shop.id"
            :class="['shop-card', { 'shop-card--collapsed': !isShopExpanded(shop.id) }]"
          >
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

            <div
              class="shop-card-details"
              :class="{ 'shop-card-details--collapsed': !isShopExpanded(shop.id) }"
              :ref="setShopDetailRef(shop.id)"
            >
              <div class="shop-scroll-hint" :class="{ 'shop-scroll-hint--active': isShopScrollable(shop.id) }">
                {{ isShopScrollable(shop.id) ? 'Can scroll' : 'Cannot scroll' }}
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

            </div>

            <div class="shop-card-footer">
              <button class="shop-card-toggle" type="button" @click="toggleShopDetails(shop.id)">
                {{ isShopExpanded(shop.id) ? 'Hide information' : 'See more' }}
              </button>
              <button class="view-shop-btn" type="button" @click="viewShopVehicles(shop)">View Vehicles</button>
            </div>
          </article>
        </div>

        <div v-if="hasMoreShops" class="shops-grid-footer">
          <button class="btn-reset see-more-shops" type="button" @click="toggleShopGrid">
            {{ showAllShops ? 'Show less shops' : 'See more shops' }}
          </button>
        </div>
      </section>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div v-if="showLogoutConfirm" class="confirm-overlay" @click="cancelLogout">
    <div class="confirm-modal" @click.stop>
      <div class="confirm-icon">
        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
      </div>
      <p class="confirm-title">Logout</p>
      <p class="confirm-text">Are you sure you want to logout?</p>
      <div class="confirm-actions">
        <button class="confirm-cancel-btn" @click="cancelLogout">No</button>
        <button class="confirm-logout-btn" @click="confirmLogout">Yes</button>
      </div>
    </div>
  </div>

  <!-- Common Footer -->
  <UserFooter />
  </div>
</template>
