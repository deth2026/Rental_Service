<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { vehicleApi } from '@/services/api'
import { userService } from '../../services/database.js'

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

  const imageUrl = vehicle.image_url || (parsedPhotos.length > 0 ? parsedPhotos[0] : '')

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

<style scoped>
.shop-vehicles-page {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 100;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.back-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
  color: #333;
  padding: 0.5rem;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.back-btn:hover {
  background-color: #f0f0f0;
}

.brand-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.25rem;
}

.brand span {
  font-weight: 700;
  font-size: 1.25rem;
  color: #333;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-display-name {
  font-weight: 500;
  color: #333;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  overflow: hidden;
  border: none;
  cursor: pointer;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.2s;
}

.logout-btn:hover {
  background: #fecaca;
}

.vehicles-content {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.75rem;
  color: #333;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #666;
}

.status-box {
  text-align: center;
  padding: 3rem;
  background: white;
  border-radius: 12px;
  color: #666;
  font-size: 1.1rem;
}

.status-box.error {
  color: #dc2626;
  background: #fee2e2;
}

.vehicles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.vehicle-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: transform 0.2s, box-shadow 0.2s;
}

.vehicle-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.vehicle-card-image {
  position: relative;
  height: 200px;
  background: #f8f9fa;
}

.vehicle-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.vehicle-card-image .no-image {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  color: #ccc;
}

.status-badge {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-available {
  background: #dcfce7;
  color: #166534;
}

.status-rented {
  background: #fef3c7;
  color: #92400e;
}

.status-maintenance {
  background: #fee2e2;
  color: #991b1b;
}

.vehicle-card-content {
  padding: 1.25rem;
}

.vehicle-card-content h3 {
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 0.25rem;
}

.vehicle-type {
  color: #666;
  font-size: 0.875rem;
  margin-bottom: 1rem;
}

.vehicle-details {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8rem;
  color: #666;
  background: #f3f4f6;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
}

.detail-item i {
  font-size: 0.75rem;
}

.vehicle-price {
  margin-bottom: 1rem;
}

.price-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #667eea;
}

.price-period {
  font-size: 0.875rem;
  color: #666;
}

.view-details-btn {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.view-details-btn:hover {
  opacity: 0.9;
}

@media (max-width: 768px) {
  .topbar {
    padding: 1rem;
  }

  .vehicles-content {
    padding: 1rem;
  }

  .vehicles-grid {
    grid-template-columns: 1fr;
  }
}
</style>
