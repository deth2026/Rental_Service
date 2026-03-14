<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { vehicleApi, shopApi } from '@/services/api'
import { userService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import '../../css/ShopVehicle.css'

const route = useRoute()
const router = useRouter()

const shopId = computed(() => route.params.id)
const vehicles = ref([])
const selectedCategory = ref('all')
const shop = ref(null)
const isLoading = ref(true)
const error = ref('')
const isLoadingShop = ref(false)
const shopImageError = ref(false)

// Get shop from localStorage or use default
const getShopFromStorage = () => {
  try {
    const shopsData = localStorage.getItem('rental_shops')
    if (shopsData) {
      const shops = JSON.parse(shopsData)
      return shops.find(s => s.id === Number(shopId.value))
    }
  } catch (e) {
    console.error('Error getting shop:', e)
  }
  return null
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

const isOwnerRole = computed(() => {
  const role = String(currentUser.value?.role || '').toLowerCase()
  return role === 'shop_owner' || role === 'owner'
})

const getShopImage = () => {
  if (!shop.value) return ''
  const img = shop.value.img_url || shop.value.image || shop.value.cover || ''
  if (!img) return ''
  if (/^(https?:\/\/|data:|blob:)/i.test(img)) return img
  const normalized = String(img).replace(/^\/+/, '')
  if (normalized.startsWith('storage/')) return `${getApiOrigin()}/${normalized}`
  return `${getApiOrigin()}/storage/${normalized.replace(/^storage\//, '')}`
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

const resolveVehicleImageUrl = (value) => {
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

const normalizeType = (raw) => {
  const t = String(raw || '').trim().toLowerCase()
  if (!t) return ''

  // From DB: "moto" → motorbike, "bike" → bicycle, "car" → car
  if (['moto', 'motor', 'motorbike', 'motorcycle', 'scooter'].some((k) => t.includes(k))) return 'motorbike'
  if (['bike', 'bicy', 'cycle', 'bicycle'].some((k) => t.includes(k))) return 'bicycle'
  if (['car', 'suv', 'sedan', 'auto', 'truck', 'pickup'].some((k) => t.includes(k))) return 'car'

  return t
}

const normalizeVehicle = (vehicle) => {
  let parsedPhotos = []
  try {
    if (vehicle.photos) {
      if (Array.isArray(vehicle.photos)) {
        parsedPhotos = vehicle.photos
      } else if (typeof vehicle.photos === 'string') {
        parsedPhotos = JSON.parse(vehicle.photos)
      }
    }
  } catch (e) {
    parsedPhotos = []
  }

  const imageUrl = vehicle.image_url_full || vehicle.image_url || (parsedPhotos.length > 0 ? (vehicle.photo_urls && vehicle.photo_urls[0] ? vehicle.photo_urls[0] : parsedPhotos[0]) : '')

  const rawType =
    vehicle.type ||
    vehicle.category ||
    vehicle.category_name ||
    vehicle.categoryName ||
    vehicle.vehicle_type ||
    vehicle.vehicleCategory ||
    vehicle.vehicle_category ||
    vehicle?.category?.name ||
    vehicle?.vehicle_category?.name ||
    vehicle.kind ||
    vehicle.name

  const normalizedType = normalizeType(rawType)

  return {
    id: vehicle.id,
    name: vehicle.name || 'Unnamed Vehicle',
    brand: vehicle.brand || '',
    model: vehicle.model || '',
    type: normalizedType,
    plate_number: vehicle.plate_number || vehicle.plate || '',
    price_per_day: Number(vehicle.price_per_day || vehicle.price || 0),
    fuel_type: vehicle.fuel_type || vehicle.fuel || '',
    transmission: vehicle.transmission || '',
    status: vehicle.status || vehicle.state || 'Available',
    created_at: vehicle.created_at || vehicle.create_at || vehicle.createdAt || '',
    description: vehicle.description || '',
    imageUrl: resolveVehicleImageUrl(imageUrl),
    photos: parsedPhotos
  }
}

const fetchVehicles = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const allVehicles = []
    let page = 1
    let lastPage = 1

    do {
      const response = await vehicleApi.getAll({ shop_id: shopId.value, page })
      const payload = response.data
      const pageData = Array.isArray(payload) ? payload : payload?.data || []
      allVehicles.push(...pageData.map(normalizeVehicle))

      const meta = payload?.meta
      lastPage = meta?.last_page || payload?.last_page || lastPage
      page += 1
    } while (page <= lastPage)

    vehicles.value = allVehicles
  } catch (err) {
    console.error('Error fetching vehicles:', err)
    error.value = 'Failed to load vehicles. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const fetchShop = async () => {
  isLoadingShop.value = true
  try {
    const response = await shopApi.getById(shopId.value)
    shop.value = response.data?.data || response.data || null
  } catch (err) {
    console.error('Error fetching shop:', err)
    shop.value = getShopFromStorage()
  } finally {
    isLoadingShop.value = false
  }
}

const goBack = () => {
  router.push('/view_shop')
}

const openProfile = () => {
  router.push('/user/profile')
}

const handleLogout = async () => {
  await userService.logout()
  localStorage.removeItem('auth_token')
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

const viewVehicleDetails = (vehicle) => {
  router.push(`/vehicles/${vehicle.id}`)
}

const getStatusClass = (status) => {
  const s = String(status).toLowerCase()
  if (s === 'available') return 'status-available'
  if (s === 'rented') return 'status-rented'
  if (s === 'maintenance') return 'status-maintenance'
  return 'status-available'
}

const filteredVehicles = computed(() => {
  if (selectedCategory.value === 'all') return vehicles.value
  return vehicles.value.filter((v) => {
    const type = normalizeType(
      v.type ||
        v.category ||
        v.category_name ||
        v.categoryName ||
        v.vehicle_type ||
        v.vehicleCategory ||
        v.vehicle_category ||
        v?.category?.name ||
        v?.vehicle_category?.name ||
        v.kind ||
        v.name
    )
    if (type === selectedCategory.value) return true
    // looser matching: e.g., "motorbikes", "motor" or text mentions
    const text = `${v.name || ''} ${v.brand || ''} ${v.model || ''} ${v.description || ''}`.toLowerCase()
    if (selectedCategory.value === 'motorbike') {
      return ['moto', 'motor', 'motorbike', 'motorcycle', 'scooter'].some((k) => type.includes(k) || text.includes(k))
    }
    if (selectedCategory.value === 'bicycle') {
      return ['bike', 'bicy', 'cycle', 'bicycle'].some((k) => type.includes(k) || text.includes(k))
    }
    if (selectedCategory.value === 'car') {
      return ['car', 'suv', 'auto', 'sedan', 'truck', 'pickup'].some((k) => type.includes(k) || text.includes(k))
    }
    return false
  })
})

const categoryButtons = [
  { label: 'All', value: 'all' },
  { label: 'Motorbikes', value: 'motorbike' },
  { label: 'Bicycles', value: 'bicycle' },
  { label: 'Cars', value: 'car' }
]

onMounted(() => {
  fetchVehicles()
  fetchShop()
})

const selectedShopCoords = computed(() => {
  if (!shop.value) return null
  const lat = shop.value.latitude ?? shop.value.lat
  const lng = shop.value.longitude ?? shop.value.lng
  if (lat == null || lng == null) return null
  const parsedLat = Number(lat)
  const parsedLng = Number(lng)
  if (!Number.isFinite(parsedLat) || !Number.isFinite(parsedLng)) return null
  return { lat: parsedLat, lng: parsedLng }
})

// Optional: Google Maps Embed API key to render the richer place card UI when available
const googleMapsEmbedKey = import.meta.env?.VITE_GOOGLE_MAPS_EMBED_KEY || ''

const mapEmbedUrl = computed(() => {
  const coords = selectedShopCoords.value
  const name = shop.value?.name || 'Shop'
  const address = shop.value?.address || ''
  const fallback = 'Siem Reap, Cambodia'
  // Prefer name+address to trigger the place card; add coords only to help center
  const queryParts = []
  if (name) queryParts.push(name)
  if (address) queryParts.push(address)
  const baseQuery = queryParts.join(', ') || fallback

  // Use the official Embed API when a key is configured — this shows the place card UI like in the screenshot
  if (googleMapsEmbedKey) {
    const placeTarget = coords ? `${coords.lat},${coords.lng}` : baseQuery
    return `https://www.google.com/maps/embed/v1/place?key=${googleMapsEmbedKey}&q=${encodeURIComponent(placeTarget)}&maptype=satellite`
  }

  // Fallback: regular embed that still centers on the shop and uses satellite view
  if (coords) {
    return `https://www.google.com/maps?q=${encodeURIComponent(baseQuery)}&ll=${coords.lat},${coords.lng}&t=k&z=19&output=embed`
  }

  return `https://www.google.com/maps?q=${encodeURIComponent(baseQuery)}&t=k&z=19&output=embed`
})

const openMap = () => {
  const coords = selectedShopCoords.value
  const name = shop.value?.name || ''
  const address = shop.value?.address || ''
  const fallback = 'Siem Reap, Cambodia'
  const destination = `${name ? `${name}, ` : ''}${address || ''}`.trim() || fallback
  const destWithCoords = coords ? `${destination} (${coords.lat},${coords.lng})` : destination
  window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destWithCoords)}`, '_blank')
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  if (Number.isNaN(date.getTime())) return ''
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}/${month}/${year}`
}

const resolveShopImageUrl = (value) => {
  if (!value || typeof value !== 'string') return ''
  if (/^(https?:\/\/|data:|blob:)/i.test(value)) return value
  const clean = value.replace(/^\/+/, '')
  if (clean.startsWith('storage/')) return `${getApiOrigin()}/${clean}`
  return `${getApiOrigin()}/storage/${clean}`
}

const shopImageUrl = computed(() => {
  if (shopImageError.value) return ''
  const src = shop.value?.img_url || shop.value?.image_url || shop.value?.photo || shop.value?.image || ''
  return resolveShopImageUrl(src)
})

const onShopImageError = () => {
  shopImageError.value = true
}

const shopInitials = computed(() => {
  const name = shop.value?.name || 'Shop'
  const words = String(name).trim().split(/\s+/)
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

const ownerNameDisplay = computed(() => shop.value?.owner_name || shop.value?.owner?.name || userDisplayName.value)
const shopStatusLabel = computed(() => {
  const status = shop.value?.status || 'Active'
  const str = String(status)
  return str.charAt(0).toUpperCase() + str.slice(1)
})
const createdAtDisplay = computed(() => formatDate(shop.value?.created_at || shop.value?.create_at))

const getShopStatusClass = (status) => {
  const s = String(status || '').toLowerCase()
  if (s === 'active') return 'pill-active'
  if (s === 'inactive' || s === 'disabled') return 'pill-inactive'
  if (s === 'pending') return 'pill-pending'
  return 'pill-active'
}
</script>

<template>
  <div class="shop-vehicles-page">
    <header class="topbar">
      <div class="brand">
        <button class="back-btn" @click="goBack" aria-label="Back to shops">
          <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="brand-icon"><i class="fa-solid fa-car" aria-hidden="true"></i></div>
        <span>My Shop</span>
      </div>

      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <button class="btn-reset avatar" @click="openProfile">
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile photo" class="avatar-image" @error="onAvatarError" />
          <span v-else>{{ userInitials }}</span>
        </button>
        <button class="btn-reset logout-btn" @click="handleLogout">
          <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> <span>Logout</span>
        </button>
      </div>
    </header>

    <main class="shop-shell">
      <section class="shop-hero">
        <div>
          <p class="eyebrow">Dashboard / My Shop</p>
          <h1>My Shop</h1>
          <p class="subhead">Keep your shop profile up to date and manage vehicles in one place.</p>
        </div>
        <div class="hero-actions">
          <button class="ghost-btn" @click="goBack">Back</button>
          <button class="primary-btn create-btn" @click="router.push('/dashboard')">Create Shop</button>
        </div>
      </section>

      <section v-if="isLoadingShop" class="status-box large">Loading shop...</section>
      <section v-else-if="!shop" class="status-box large">
        No shop found. Create a shop to start adding vehicles.
      </section>

      <section v-else class="shop-profile-card">
        <span class="status-pill" :class="getShopStatusClass(shop.status)">{{ shopStatusLabel }}</span>

        <div class="avatar-wrap">
          <img
            v-if="shopImageUrl"
            :src="shopImageUrl"
            alt="Shop image"
            class="shop-avatar"
            @error="onShopImageError"
          />
          <div v-else class="shop-avatar placeholder">
            <span>{{ shopInitials }}</span>
          </div>
        </div>

        <div class="photo-actions">
          <button class="primary-btn">Change Image</button>
          <button class="ghost-btn">Delete Image</button>
        </div>

        <div class="info-grid">
          <div class="info-field">
            <p class="label">Shop Name</p>
            <p class="value">{{ shop.name || '—' }}</p>
          </div>
          <div class="info-field">
            <p class="label">Status</p>
            <p class="value success">{{ shopStatusLabel }}</p>
          </div>
          <div class="info-field">
            <p class="label">Owner Name</p>
            <p class="value">{{ ownerNameDisplay }}</p>
          </div>
          <div class="info-field">
            <p class="label">Created</p>
            <p class="value">{{ createdAtDisplay || '—' }}</p>
          </div>
          <div class="info-field wide">
            <p class="label">Phone</p>
            <p class="value">{{ shop.phone || '—' }}</p>
          </div>
          <div class="info-field wide">
            <p class="label">Address</p>
            <p class="value">{{ shop.address || shop.location || '—' }}</p>
          </div>
          <div class="info-field wide" v-if="shop.description">
            <p class="label">Description</p>
            <p class="value muted">{{ shop.description }}</p>
          </div>
        </div>
      </section>

      <section class="vehicles-section">
        <div class="section-head">
          <div>
            <p class="eyebrow">Inventory</p>
            <h2>Available Vehicles</h2>
            <p class="subhead">Browse vehicles from this shop</p>
          </div>
          <p class="results-text">
            {{ filteredVehicles.length }} vehicles found
            <span v-if="shop?.name">in {{ shop.name }}</span>
          </p>
        </div>

        <div class="filters-row" v-if="vehicles.length">
          <div class="filter-chips">
            <button
              v-for="category in categoryButtons"
              :key="category.value"
              class="chip"
              :class="{ active: selectedCategory === category.value }"
              @click="selectedCategory = category.value"
            >
              {{ category.label }}
            </button>
          </div>
        </div>

        <div v-if="isLoading" class="status-box">Loading vehicles...</div>
        <div v-else-if="error" class="status-box error">{{ error }}</div>
        <div v-else-if="vehicles.length === 0" class="status-box">
          No vehicles available at this shop yet.
        </div>
        <div v-else-if="filteredVehicles.length === 0" class="status-box">
          No vehicles match this category.
        </div>

        <div v-else class="vehicles-grid">
          <article v-for="vehicle in filteredVehicles" :key="vehicle.id" class="vehicle-card">
            <div class="vehicle-card-image">
              <img
                v-if="vehicle.imageUrl"
                :src="vehicle.imageUrl"
                :alt="vehicle.name"
                @error="vehicle.imageUrl = ''"
              />
              <div v-else class="no-image">
                <i class="fa-solid fa-car"></i>
              </div>
              <span :class="'status-badge ' + getStatusClass(vehicle.status)">
                {{ vehicle.status }}
              </span>
            </div>

            <div class="vehicle-card-content">
              <h3>{{ vehicle.name }}</h3>
              <p class="vehicle-type">{{ vehicle.type }} - {{ vehicle.brand }} {{ vehicle.model }}</p>

              <div class="vehicle-details">
                <div class="detail-item" v-if="vehicle.plate_number">
                  <i class="fa-solid fa-tag"></i>
                  <span>{{ vehicle.plate_number }}</span>
                </div>
                <div class="detail-item">
                  <i class="fa-solid fa-circle"></i>
                  <span>Status: {{ vehicle.status }}</span>
                </div>
                <div class="detail-item" v-if="vehicle.fuel_type">
                  <i class="fa-solid fa-gas-pump"></i>
                  <span>{{ vehicle.fuel_type }}</span>
                </div>
                <div class="detail-item" v-if="vehicle.transmission">
                  <i class="fa-solid fa-gear"></i>
                  <span>{{ vehicle.transmission }}</span>
                </div>
                <div class="detail-item" v-if="vehicle.created_at">
                  <i class="fa-solid fa-calendar-days"></i>
                  <span>Created: {{ formatDate(vehicle.created_at) }}</span>
                </div>
              </div>

              <div class="vehicle-price">
                <span class="price-amount">${{ vehicle.price_per_day }}</span>
                <span class="price-period">/day</span>
              </div>

              <button class="view-details-btn" @click="viewVehicleDetails(vehicle)">
                View Details
              </button>
            </div>
          </article>
        </div>
      </section>

      <section class="map-section" v-if="shop">
        <div class="map">
          <iframe
            class="map-frame"
            :src="mapEmbedUrl"
            title="Google map location"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
          <button class="btn-reset open-map-btn" @click="openMap">
            Open in Google Maps
          </button>
        </div>
      </section>
    </main>
  </div>

  <!-- Common Footer -->
  <CommonFooter />
</template>


