<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { vehicleApi, shopApi } from '@/services/api'
import { userService } from '../../services/database.js'
import Logo from '@/components/Logo.vue'
import CommonFooter from '../../components/CommonFooter.vue'
import '../../css/ShopVehicle.css'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const route = useRoute()
const router = useRouter()

const shopId = computed(() => route.params.id)
const vehicles = ref([])
const selectedCategory = ref('all')
const shop = ref(null)
const isLoading = ref(true)
const error = ref('')
const isLoadingShop = ref(false)

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
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('blob:') || value.startsWith('data:')) {
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
  if (['motorbike', 'motorbikes', 'motor', 'moto', 'motorcycle', 'motorcycles', 'scooter', 'scooters', 'bike'].some(k => t.includes(k))) {
    return 'motorbike'
  }
  if (t.includes('bicy')) return 'bicycle'
  if (t.includes('car') || t.includes('suv')) return 'car'
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
  const normalizedType = normalizeType(vehicle.type || vehicle.category || vehicle.vehicle_type || vehicle.kind || vehicle.name)

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

const goBack = () => router.push('/view_shop')
const openProfile = () => router.push('/user/profile')

const handleLogout = async () => {
  await userService.logout()
  localStorage.removeItem('auth_token')
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

const viewVehicleDetails = (vehicle) => router.push(`/vehicles/${vehicle.id}`)

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
    const type = normalizeType(v.type || v.category || v.vehicle_type || v.kind || v.name)
    if (type === selectedCategory.value) return true
    const text = `${v.name || ''} ${v.brand || ''} ${v.model || ''} ${v.description || ''}`.toLowerCase()
    if (selectedCategory.value === 'motorbike') {
      return ['motor', 'moto', 'bike', 'scooter'].some((k) => type.includes(k) || text.includes(k))
    }
    if (selectedCategory.value === 'bicycle') {
      return ['bicy', 'cycle', 'bike'].some((k) => type.includes(k) || text.includes(k))
    }
    if (selectedCategory.value === 'car') {
      return ['car', 'suv', 'auto', 'sedan', 'truck'].some((k) => type.includes(k) || text.includes(k))
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

const googleMapsEmbedKey = import.meta.env?.VITE_GOOGLE_MAPS_EMBED_KEY || ''

const mapEmbedUrl = computed(() => {
  const coords = selectedShopCoords.value
  const name = shop.value?.name || 'Shop'
  const address = shop.value?.address || ''
  const fallback = 'Siem Reap, Cambodia'
  const queryParts = []
  if (name) queryParts.push(name)
  if (address) queryParts.push(address)
  const baseQuery = queryParts.join(', ') || fallback

  if (googleMapsEmbedKey) {
    const placeTarget = coords ? `${coords.lat},${coords.lng}` : baseQuery
    return `https://www.google.com/maps/embed/v1/place?key=${googleMapsEmbedKey}&q=${encodeURIComponent(placeTarget)}&maptype=satellite`
  }

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
</script>

<template>
  <div class="shop-vehicles-page">
    <header class="topbar">
      <div class="brand">
        <button class="back-btn" @click="goBack">
          <i class="fa-solid fa-arrow-left"></i>
        </button>
        <Logo class="brand-logo" to="/view_shop" size="md" :showTagline="false" />
      </div>
      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <button class="btn-reset avatar" @click="openProfile">
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile photo" class="avatar-image"
            @error="onAvatarError" />
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

      <div v-if="isOwnerRole" class="owner-shop-card">
        <div class="owner-top-row">
          <div class="owner-avatar">
            <img v-if="getShopImage()" :src="getShopImage()" alt="Shop" />
            <div v-else class="owner-avatar-placeholder">
              {{ shop?.name?.slice(0, 1) || 'S' }}
            </div>
          </div>
          <div class="owner-actions">
            <button class="owner-btn primary-btn" type="button">Change Image</button>
            <button class="owner-btn secondary-btn" type="button">Delete Image</button>
          </div>
          <span class="owner-status" :class="shop?.status === 'active' ? 'is-active' : 'is-inactive'">
            {{ shop?.status ? shop.status.charAt(0).toUpperCase() + shop.status.slice(1) : 'Draft' }}
          </span>
        </div>
        <div class="owner-info-grid">
          <div class="owner-info-card">
            <p>Shop Name</p>
            <strong>{{ shop?.name || 'Your Shop' }}</strong>
          </div>
          <div class="owner-info-card">
            <p>Owner</p>
            <strong>{{ shop?.owner_name || 'Owner Name' }}</strong>
          </div>
          <div class="owner-info-card">
            <p>Phone</p>
            <strong>{{ shop?.phone || 'N/A' }}</strong>
          </div>
          <div class="owner-info-card">
            <p>Address</p>
            <strong>{{ shop?.address || 'Add address' }}</strong>
          </div>
        </div>
      </div>

      <div class="filters-row" v-if="vehicles.length">
        <div class="filter-chips">
          <button v-for="category in categoryButtons" :key="category.value" class="chip"
            :class="{ active: selectedCategory === category.value }" @click="selectedCategory = category.value">
            {{ category.label }}
          </button>
        </div>
        <p class="results-text">{{ filteredVehicles.length }} vehicles found <span v-if="shop?.name">in {{ shop.name
            }}</span></p>
      </div>

      <div v-if="isLoading" class="status-box">Loading vehicles...</div>
      <div v-else-if="error" class="status-box error">{{ error }}</div>
      <div v-else-if="vehicles.length === 0" class="status-box">No vehicles available at this shop yet.</div>
      <div v-else-if="filteredVehicles.length === 0" class="status-box">No vehicles match this category.</div>

      <div v-else class="vehicles-grid">
        <article v-for="vehicle in filteredVehicles" :key="vehicle.id" class="vehicle-card">
          <div class="vehicle-card-image">
            <img v-if="vehicle.imageUrl" :src="vehicle.imageUrl" :alt="vehicle.name" @error="vehicle.imageUrl = ''" />
            <div v-else class="no-image"><i class="fa-solid fa-car"></i></div>
            <span :class="'status-badge ' + getStatusClass(vehicle.status)">{{ vehicle.status }}</span>
          </div>
          <div class="vehicle-card-content">
            <h3>{{ vehicle.name }}</h3>
            <p class="vehicle-type">{{ vehicle.type }} - {{ vehicle.brand }} {{ vehicle.model }}</p>
            <div class="vehicle-details">
              <div class="detail-item" v-if="vehicle.plate_number"><i class="fa-solid fa-tag"></i><span>{{
                vehicle.plate_number }}</span></div>
              <div class="detail-item" v-if="vehicle.fuel_type"><i class="fa-solid fa-gas-pump"></i><span>{{
                  vehicle.fuel_type }}</span></div>
              <div class="detail-item" v-if="vehicle.transmission"><i class="fa-solid fa-gear"></i><span>{{
                vehicle.transmission }}</span></div>
            </div>
            <div class="vehicle-price">
              <span class="price-amount">${{ vehicle.price_per_day }}</span>
              <span class="price-period">/day</span>
            </div>
            <button class="view-details-btn" @click="viewVehicleDetails(vehicle)">View Details</button>
          </div>
        </article>
      </div>

      <section class="map-section" v-if="shop">
        <div class="map">
          <iframe class="map-frame" :src="mapEmbedUrl" title="Google map location" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
          <button class="btn-reset open-map-btn" @click="openMap">Open in Google Maps</button>
        </div>
      </section>
    </main>
  </div>

  <!-- Common Footer -->
  <CommonFooter />
</template>


<style>
.topbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 4rem;
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

.brand-icon img {
  width: 70%;
  height: 70%;
  object-fit: contain;
  display: block;
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

.owner-shop-card {
  margin-bottom: 2rem;
  background: linear-gradient(180deg, #fafbff 0%, #f4f7ff 100%);
  border-radius: 26px;
  padding: 28px 32px;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.15);
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.owner-top-row {
  display: flex;
  align-items: center;
  gap: 28px;
  flex-wrap: wrap;
}

.owner-avatar {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  padding: 6px;
  background: #fff;
  border: 4px solid rgba(37, 99, 235, 0.25);
  box-shadow: 0 14px 40px rgba(15, 23, 42, 0.2);
}

.owner-avatar img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: block;
  object-fit: cover;
}

.owner-avatar-placeholder {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #2563eb;
  font-weight: 700;
  background: radial-gradient(circle, #e0eeff 0%, #cdd8f6 70%);
}

.owner-actions {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  flex: 1;
}

.owner-btn {
  flex: 1;
  padding: 0.85rem 1rem;
  border-radius: 28px;
  border: none;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
}

.primary-btn {
  background: linear-gradient(135deg, #1d4ed8, #3b82f6);
  color: #fff;
  box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
}

.secondary-btn {
  background: #e2e8f0;
  color: #1f2937;
  border: 1px solid #cfd7ea;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.owner-status {
  margin-left: auto;
  padding: 0.5rem 1.25rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  background: #ecffef;
  color: #047857;
  border: 1px solid rgba(4, 120, 87, 0.3);
}

.owner-status.is-inactive {
  background: #ffe8e8;
  color: #991b1b;
  border-color: rgba(153, 27, 27, 0.3);
}

.owner-info-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
}

.owner-info-card {
  background: #fff;
  border-radius: 18px;
  padding: 12px 16px;
  border: 1px solid #e0e6f6;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.owner-info-card p {
  margin: 0;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: #6b7280;
}

.owner-info-card strong {
  display: block;
  margin-top: 6px;
  font-size: 1.05rem;
  color: #111827;
  font-weight: 600;
}

.filters-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.filter-chips {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.55rem 1rem;
  border-radius: 999px;
  border: 1px solid #d8dee7;
  background: white;
  color: #334155;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.chip:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.chip.active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.15);
}

.results-text {
  color: #475569;
  font-weight: 600;
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
  grid-template-columns: repeat(3, minmax(260px, 1fr));
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
  background: #2563eb;
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

.map-section {
  margin-top: 2rem;
}

.map {
  position: relative;
  height: 340px;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #d8dee7;
}

.map-frame {
  width: 100%;
  height: 100%;
  border: 0;
}

.open-map-btn {
  position: absolute;
  left: 12px;
  bottom: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #ffffff;
  border: 1px solid #d8dee7;
  color: #1e40af;
  font-size: 13px;
  font-weight: 600;
}

@media (max-width: 768px) {
  .topbar {
    padding: 1rem;
  }

  .vehicles-content {
    padding: 1rem;
  }

  .vehicles-grid {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}
</style>
