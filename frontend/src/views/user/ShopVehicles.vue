<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { vehicleApi } from '@/services/api'
import { userService } from '../../services/database.js'
import '../../css/ShopVehicle.css'

const route = useRoute()
const router = useRouter()

const shopId = computed(() => route.params.id)
const vehicles = ref([])
const shop = ref(null)
const isLoading = ref(true)
const error = ref('')

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

  return {
    id: vehicle.id,
    name: vehicle.name || 'Unnamed Vehicle',
    brand: vehicle.brand || '',
    model: vehicle.model || '',
    type: vehicle.type || vehicle.category || '',
    plate_number: vehicle.plate_number || vehicle.plate || '',
    price_per_day: Number(vehicle.price_per_day || vehicle.price || 0),
    fuel_type: vehicle.fuel_type || vehicle.fuel || '',
    transmission: vehicle.transmission || '',
    status: vehicle.status || 'Available',
    description: vehicle.description || '',
    imageUrl: resolveVehicleImageUrl(imageUrl),
    photos: parsedPhotos
  }
}

const fetchVehicles = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const response = await vehicleApi.getAll({ shop_id: shopId.value })
    const data = response.data.data || response.data || []
    vehicles.value = data.map(normalizeVehicle)
  } catch (err) {
    console.error('Error fetching vehicles:', err)
    error.value = 'Failed to load vehicles. Please try again.'
  } finally {
    isLoading.value = false
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

onMounted(() => {
  fetchVehicles()
})
</script>

<template>
  <div class="shop-vehicles-page">
    <header class="topbar">
      <div class="brand">
        <button class="back-btn" @click="goBack">
          <i class="fa-solid fa-arrow-left"></i>
        </button>
        <div class="brand-icon"><i class="fa-solid fa-car" aria-hidden="true"></i></div>
        <span>Shop Vehicles</span>
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

    <main class="vehicles-content">
      <div class="page-header">
        <h1>Available Vehicles</h1>
        <p>Browse vehicles from this shop</p>
      </div>

      <div v-if="isLoading" class="status-box">Loading vehicles...</div>
      <div v-else-if="error" class="status-box error">{{ error }}</div>
      <div v-else-if="vehicles.length === 0" class="status-box">
        No vehicles available at this shop yet.
      </div>

      <div v-else class="vehicles-grid">
        <article v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-card">
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
              <div class="detail-item" v-if="vehicle.fuel_type">
                <i class="fa-solid fa-gas-pump"></i>
                <span>{{ vehicle.fuel_type }}</span>
              </div>
              <div class="detail-item" v-if="vehicle.transmission">
                <i class="fa-solid fa-gear"></i>
                <span>{{ vehicle.transmission }}</span>
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
    </main>
  </div>
</template>


