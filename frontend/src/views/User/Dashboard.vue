<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { getSessionUser, logoutUser } from '@/services/auth'
import { shopApi } from '@/services/api'
import { useToast } from '@/composables/useToast'
import CommonFooter from '@/components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import '@/css/userDashboard.css'

const router = useRouter()
const { showToast, info } = useToast()

const LOCATION_CACHE_KEY = 'chong_choul_user_location'

const CAMBODIA_PROVINCES = [
  'Banteay Meanchey',
  'Battambang',
  'Kampong Cham',
  'Kampong Chhnang',
  'Kampong Speu',
  'Kampong Thom',
  'Kampot',
  'Kandal',
  'Kep',
  'Koh Kong',
  'Kratie',
  'Mondulkiri',
  'Oddar Meanchey',
  'Pailin',
  'Phnom Penh',
  'Preah Sihanouk',
  'Preah Vihear',
  'Prey Veng',
  'Pursat',
  'Ratanakiri',
  'Siem Reap',
  'Stung Treng',
  'Svay Rieng',
  'Takeo',
  'Tboung Khmum',
]

const currentUser = ref(getSessionUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Customer')

const isLoading = ref(false)
const dataError = ref('')
const shops = ref([])
const provinceQuery = ref('')
const selectedProvince = ref('All Cambodia')
const searchMessage = ref('')

const userLocation = ref(getStoredLocation())
const locationStatus = ref(userLocation.value ? 'Current location detected.' : 'Enable location for nearest distance.')
const locating = ref(false)
const showLocationModal = ref(false)

const mapContainer = ref(null)
let leafletLib = null
let mapInstance = null
let markerLayer = null

function getStoredLocation() {
  try {
    const raw = localStorage.getItem(LOCATION_CACHE_KEY)
    if (!raw) return null
    const parsed = JSON.parse(raw)
    const lat = Number(parsed?.lat)
    const lng = Number(parsed?.lng)
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null
    return { lat, lng }
  } catch {
    return null
  }
}

const openLogin = () => {
  if (!userLocation.value) {
    showLocationModal.value = true
    return
  }
  router.push('/login')
}

const openRegister = () => {
  if (!userLocation.value) {
    showLocationModal.value = true
    return
  }
  router.push('/register')
}

const normalizeProvinceName = (value) => {
  if (value === 'All Cambodia') return 'All Cambodia'
  const raw = String(value || '')
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase()

  if (!raw) return ''

  const aliases = {
    'banteay meanchey': 'Banteay Meanchey',
    battambang: 'Battambang',
    'kampong cham': 'Kampong Cham',
    'kampong chhnang': 'Kampong Chhnang',
    'kampong speu': 'Kampong Speu',
    'kampong thom': 'Kampong Thom',
    kampot: 'Kampot',
    kandal: 'Kandal',
    kep: 'Kep',
    'koh kong': 'Koh Kong',
    kratie: 'Kratie',
    mondulkiri: 'Mondulkiri',
    'oddar meanchey': 'Oddar Meanchey',
    pailin: 'Pailin',
    'phnom penh': 'Phnom Penh',
    'preah sihanouk': 'Preah Sihanouk',
    sihanoukville: 'Preah Sihanouk',
    'preah vihear': 'Preah Vihear',
    'prey veng': 'Prey Veng',
    pursat: 'Pursat',
    ratanakiri: 'Ratanakiri',
    'siem reap': 'Siem Reap',
    'stung treng': 'Stung Treng',
    'svay rieng': 'Svay Rieng',
    takeo: 'Takeo',
    'tbong khmum': 'Tboung Khmum',
    'tboung khmum': 'Tboung Khmum',
  }

  return aliases[raw] || raw.replace(/\b\w/g, (char) => char.toUpperCase())
}

const extractProvinceFromText = (value) => {
  const normalized = String(value || '').toLowerCase()
  if (!normalized) return ''
  return CAMBODIA_PROVINCES.find((province) => normalized.includes(province.toLowerCase())) || ''
}

const parseArrayPayload = (payload) => {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

const calculateDistanceKm = (lat1, lng1, lat2, lng2) => {
  const toRad = (deg) => (deg * Math.PI) / 180
  const earthRadiusKm = 6371

  const dLat = toRad(lat2 - lat1)
  const dLng = toRad(lng2 - lng1)

  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLng / 2) * Math.sin(dLng / 2)

  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a))
  return earthRadiusKm * c
}

const normalizeShop = (shop) => {
  const province = normalizeProvinceName(
    shop?.province || shop?.city?.name || extractProvinceFromText(shop?.location) || extractProvinceFromText(shop?.address)
  )

  return {
    id: Number(shop.id),
    name: shop.name || `Shop #${shop.id}`,
    address: shop.address || 'Address not available',
    location: shop.location || '',
    province,
    latitude: Number(shop.latitude ?? 0),
    longitude: Number(shop.longitude ?? 0),
    distanceKm: shop.distance ? Number(shop.distance) : null,
    isNearest: !!(shop.is_nearest || shop.suggested),
    vehiclesCount: Number(shop.vehicles_count || 0),
    status: String(shop.status || 'active').toLowerCase(),
    img_url: shop.img_url_full || shop.img_url || '/images/default-shop.png'
  }
}

const provinceOptions = computed(() => {
  const dynamic = shops.value.map((shop) => shop.province).filter(Boolean)
  return Array.from(new Set([...CAMBODIA_PROVINCES, ...dynamic])).sort((a, b) => a.localeCompare(b))
})

const provinceChips = computed(() => provinceOptions.value.slice(0, 12))

const selectedProvinceShops = computed(() => {
  const isAll = selectedProvince.value === 'All Cambodia'
  const normalized = normalizeProvinceName(selectedProvince.value)
  
  const inProvince = shops.value.filter((shop) => {
    if (isAll) return true
    return normalizeProvinceName(shop.province) === normalized
  })

  const withDistance = inProvince.map((shop) => {
    let distanceKm = shop.distanceKm || null
    if (!distanceKm && userLocation.value && Number.isFinite(shop.latitude) && Number.isFinite(shop.longitude)) {
      distanceKm = calculateDistanceKm(userLocation.value.lat, userLocation.value.lng, shop.latitude, shop.longitude)
    }
    return { ...shop, distanceKm }
  })

  withDistance.sort((a, b) => {
    if (a.distanceKm !== null && b.distanceKm !== null) return a.distanceKm - b.distanceKm
    if (a.distanceKm !== null) return -1
    if (b.distanceKm !== null) return 1
    return b.vehiclesCount - a.vehiclesCount || a.name.localeCompare(b.name)
  })

  const hasMultipleShops = withDistance.length >= 2

  return withDistance.map((shop, idx) => ({
    ...shop,
    isNearest: hasMultipleShops && idx === 0,
  }))
})

const findProvinceByInput = (input) => {
  const normalizedInput = normalizeProvinceName(input)
  const exact = provinceOptions.value.find(
    (province) => normalizeProvinceName(province) === normalizeProvinceName(normalizedInput)
  )
  if (exact) return exact
  return provinceOptions.value.find((province) => province.toLowerCase().includes(String(input).toLowerCase())) || ''
}

const searchProvince = () => {
  const query = provinceQuery.value.trim()
  if (!query) {
    searchMessage.value = 'Please type a province and click search.'
    return
  }

  const matched = findProvinceByInput(query)
  if (!matched) {
    searchMessage.value = 'Province not found. Please try another province.'
    return
  }

  selectedProvince.value = matched
  provinceQuery.value = matched
  searchMessage.value = `Showing shops in ${matched}.`
}

const selectProvince = (province) => {
  selectedProvince.value = province
  provinceQuery.value = province
  searchMessage.value = `Showing shops in ${province}.`
}

const loadShops = async () => {
  isLoading.value = true
  dataError.value = ''

  try {
    const params = { active_only: true }
    if (userLocation.value) {
      params.user_lat = userLocation.value.lat
      params.user_lng = userLocation.value.lng
    }
    const response = await shopApi.getAll(params)
    shops.value = parseArrayPayload(response.data)
      .map((shop) => normalizeShop(shop))
      .filter((shop) => shop.status === 'active' && shop.province)

    if (selectedProvince.value === 'All Cambodia') {
      provinceQuery.value = ''
    } else if (!shops.value.some((shop) => normalizeProvinceName(shop.province) === normalizeProvinceName(selectedProvince.value))) {
      selectedProvince.value = 'All Cambodia'
      provinceQuery.value = ''
    }
  } catch (error) {
    console.error(error)
    dataError.value = 'Unable to load shops from database.'
  } finally {
    isLoading.value = false
  }
}

const viewShop = (shop) => {
  router.push({ name: 'shop-vehicles', params: { id: String(shop.id) } })
}

const getDirections = (shop) => {
  if (!shop.latitude || !shop.longitude) {
    if (shop.location && shop.location.startsWith('http')) {
      window.open(shop.location, '_blank')
    } else {
      info('Shop location coordinates not available.')
    }
    return
  }

  let url = ''
  if (userLocation.value) {
    // Route from User to Shop
    url = `https://www.google.com/maps/dir/?api=1&origin=${userLocation.value.lat},${userLocation.value.lng}&destination=${shop.latitude},${shop.longitude}&travelmode=driving`
  } else {
    // Just show shop on map
    url = `https://www.google.com/maps/search/?api=1&query=${shop.latitude},${shop.longitude}`
  }
  window.open(url, '_blank')
}

const enableLocation = () => {
  if (locating.value) return
  if (!navigator?.geolocation) {
    locationStatus.value = 'Geolocation is not supported in this browser.'
    return
  }

  locating.value = true
  locationStatus.value = 'Detecting your location...'

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      userLocation.value = {
        lat: Number(position.coords.latitude),
        lng: Number(position.coords.longitude),
      }
      localStorage.setItem(LOCATION_CACHE_KEY, JSON.stringify(userLocation.value))
      locationStatus.value = 'Location detected successfully.'
      locating.value = false
      
      // Reload shops to get distances from backend
      await loadShops()
      renderMapMarkers()
      showLocationModal.value = false
    },
    () => {
      locating.value = false
      locationStatus.value = 'Could not detect location. Please allow GPS and try again.'
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
  )
}

const clearLocation = () => {
  localStorage.removeItem(LOCATION_CACHE_KEY)
  userLocation.value = null
  locationStatus.value = 'Location cleared. Enable location for nearest distance.'
  renderMapMarkers()
  loadShops()
  showLocationModal.value = true
}

const openProfile = () => router.push('/user/profile')
const openBookings = () => router.push('/my-bookings')

const onLogout = async () => {
  await logoutUser()
  router.push('/login')
}

const ensureLeaflet = async () => {
  if (typeof window !== 'undefined' && window.L) return window.L

  await new Promise((resolve, reject) => {
    if (typeof document === 'undefined') return reject(new Error('Document unavailable'))

    if (!document.getElementById('leaflet-css')) {
      const link = document.createElement('link')
      link.id = 'leaflet-css'
      link.rel = 'stylesheet'
      link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
      document.head.appendChild(link)
    }

    const existingScript = document.getElementById('leaflet-js')
    if (existingScript) {
      existingScript.addEventListener('load', () => resolve(), { once: true })
      existingScript.addEventListener('error', () => reject(new Error('Leaflet load failed')), { once: true })
      return
    }

    const script = document.createElement('script')
    script.id = 'leaflet-js'
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
    script.onload = () => resolve()
    script.onerror = () => reject(new Error('Leaflet load failed'))
    document.body.appendChild(script)
  })

  return window.L
}

const provinceBanners = {
  'Phnom Penh': 'https://images.unsplash.com/photo-1598509524136-421cbe2c19f3?q=80&w=1200', // Riverside/Royal Palace
  'Siem Reap': 'https://images.unsplash.com/photo-1500048993953-d23a436266cf?q=80&w=1200', // Angkor Wat
  'Preah Sihanouk': 'https://images.unsplash.com/photo-1554483731-866416049079?q=80&w=1200', // Beach
  'Kampot': 'https://images.unsplash.com/photo-1571216694665-68a867822941?q=80&w=1200',
  'Pursat': 'https://images.unsplash.com/photo-1619405399517-d7fce0f13302?q=80&w=1200', // Rice fields/nature
  'Battambang': 'https://images.unsplash.com/photo-1618336753974-aae8e04506aa?q=80&w=1200', // Bamboo train style
  'Mondulkiri': 'https://images.unsplash.com/photo-1581458023773-5856f64be87d?q=80&w=1200', // Waterfall
  'Kep': 'https://images.unsplash.com/photo-1548625361-58a9b86aa83b?q=80&w=1200', // Crab statue
  'All Cambodia': 'https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=1200',
  'default': 'https://images.unsplash.com/photo-1528127269322-539801943592?q=80&w=1200'
}

const currentBanner = computed(() => provinceBanners[selectedProvince.value] || provinceBanners['default'])

const createPinIcon = (shop, active) =>
  leafletLib.divIcon({
    className: 'dashboard-marker-container',
    html: `
      <div class="shop-marker-wrapper ${active ? 'active' : ''}">
        <div class="shop-marker-bubble">
          <i class="fa-solid fa-store"></i>
        </div>
        <div class="shop-marker-pin"></div>
      </div>
    `,
    iconSize: [40, 50],
    iconAnchor: [20, 45],
  })

const renderMapMarkers = () => {
  if (!leafletLib || !mapInstance || !markerLayer) return

  markerLayer.clearLayers()
  const isAll = selectedProvince.value === 'All Cambodia'
  const selected = normalizeProvinceName(selectedProvince.value)
  const allBounds = []
  const selectedBounds = []

  for (const shop of shops.value) {
    if (!Number.isFinite(shop.latitude) || !Number.isFinite(shop.longitude)) continue

    const isActive = isAll || normalizeProvinceName(shop.province) === selected
    const marker = leafletLib.marker([shop.latitude, shop.longitude], { 
      icon: createPinIcon(shop, isActive),
      zIndexOffset: isActive ? 1000 : 0
    })
    
    marker.bindPopup(`<strong>${shop.name}</strong><br/>${shop.province}<br/>${shop.vehiclesCount} vehicles available`)
    marker.on('click', () => {
      selectProvince(shop.province)
      mapInstance.setView([shop.latitude, shop.longitude], 14)
    })
    markerLayer.addLayer(marker)

    allBounds.push([shop.latitude, shop.longitude])
    if (isActive) selectedBounds.push([shop.latitude, shop.longitude])
  }

  if (userLocation.value) {
    markerLayer.addLayer(
      leafletLib.circleMarker([userLocation.value.lat, userLocation.value.lng], {
        radius: 8,
        color: '#fff',
        fillColor: '#2563eb',
        fillOpacity: 1,
        weight: 3,
      }).bindPopup('You are here')
    )
  }

  // Focus on Cambodia area
  if (isAll || selectedBounds.length === 0) {
     // Center of Cambodia
     mapInstance.setView([12.5657, 104.991], 7)
  } else {
    // Zoom to selected province shops
    if (selectedBounds.length > 0) {
      const bounds = leafletLib.latLngBounds(selectedBounds)
      mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 12 })
    }
  }
}

const initMap = async () => {
  try {
    leafletLib = await ensureLeaflet()
    if (!mapContainer.value) return

    // Limit map to Cambodia area
    const cambodiaBounds = [[10.0, 102.0], [15.0, 108.0]]

    mapInstance = leafletLib.map(mapContainer.value, {
      zoomControl: false,
      scrollWheelZoom: false,
      maxBounds: cambodiaBounds,
      maxBoundsViscosity: 1.0,
      minZoom: 6
    }).setView([12.5657, 104.991], 7)

    leafletLib
      .tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        bounds: cambodiaBounds
      })
      .addTo(mapInstance)

    markerLayer = leafletLib.layerGroup().addTo(mapInstance)
    renderMapMarkers()
  } catch (error) {
    console.error(error)
  }
}

watch([selectedProvince, shops, userLocation], () => renderMapMarkers(), { deep: true })

onMounted(async () => {
  console.log('[Dashboard] onMounted triggered')
  
  // Start location prompt timer immediately
  setTimeout(() => {
    console.log('[Dashboard] Location modal timer fired')
    // We force show it for every Guest so you can see it and test the flow
    if (!currentUser.value) {
      showLocationModal.value = true
    }
  }, 3000)

  await loadShops()
  provinceQuery.value = selectedProvince.value === 'All Cambodia' ? '' : selectedProvince.value
  await nextTick()
  await initMap()
})

onBeforeUnmount(() => {
  if (mapInstance) {
    mapInstance.remove()
    mapInstance = null
    markerLayer = null
  }
})
</script>

<template>
  <div class="customer-page">
    <header class="customer-topbar">
      <div class="brand-left">
        <img src="/images/logo-removebg.png" alt="Chong Choul logo" class="brand-logo" />
        <span>Chong Choul</span>
      </div>

      <nav class="top-links">
        <template v-if="currentUser">
          <button class="link-btn" @click="openBookings">My Bookings</button>
          <button class="link-btn" @click="openProfile">Profile</button>
        </template>
        <template v-else>
          <button class="link-btn" @click="openLogin">Login</button>
          <button class="link-btn" @click="openRegister">Register</button>
        </template>
      </nav>

      <div class="top-actions">
        <template v-if="currentUser">
          <span class="user-name">{{ userDisplayName }}</span>
          <UserProfileMenu @settings="openProfile" @logout="onLogout" />
        </template>
        <template v-else>
          <button class="login-accent-btn" @click="openLogin">Sign In</button>
        </template>
      </div>
    </header>

    <main class="customer-main">
      <section class="hero-banner-section" :style="{ backgroundImage: `linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(${currentBanner})` }">
        <div class="hero-banner-content">
          <h1>{{ selectedProvince }}</h1>
          <p>Explore the best rentals in {{ selectedProvince }}</p>
        </div>
      </section>

      <section class="search-layout">
        <div class="search-bar-wrap">
          <div class="search-input-container">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <input
              v-model="provinceQuery"
              type="text"
              class="province-search-input"
              placeholder="Search for a province (e.g., Siem Reap)..."
              @keydown.enter.prevent="searchProvince"
            />
          </div>
          <button class="province-search-btn" @click="searchProvince">
            Search
          </button>
        </div>

        <div class="status-row">
          <div class="location-status-box">
             <i class="fa-solid fa-location-dot"></i>
             <span>{{ locationStatus }}</span>
             <button v-if="userLocation" class="clear-loc-link" @click="clearLocation">Clear</button>
          </div>
          <button class="location-btn" :disabled="locating" @click="enableLocation">
            <i class="fa-solid fa-crosshairs"></i>
            {{ locating ? 'Detecting...' : 'Use My Current Location' }}
          </button>
        </div>
        <p v-if="dataError" class="error-note">{{ dataError }}</p>
      </section>

      <section class="map-section-full">
        <div class="map-container-box">
          <div ref="mapContainer" class="hero-map"></div>
        </div>
      </section>

      <section class="shop-result-section">
        <div class="section-header">
          <h2>Our Branches in {{ selectedProvince }}</h2>
          <span class="shop-count">{{ selectedProvinceShops.length }} branches</span>
        </div>

        <p v-if="isLoading" class="empty-state">Finding branches...</p>
        <p v-else-if="selectedProvinceShops.length === 0" class="empty-state">
          We don't have a branch in {{ selectedProvince }} yet. Try searching another province!
        </p>

        <div v-else class="shop-grid">
          <article v-for="shop in selectedProvinceShops" :key="shop.id" class="shop-card" :class="{ nearest: shop.isNearest }">
            <div class="shop-image-container">
              <img :src="shop.img_url" :alt="shop.name" class="shop-image" @error="(e) => e.target.src = 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?q=80&w=800'" />
              <div class="shop-badges">
                <span v-if="shop.isNearest" class="nearest-badge">Nearest</span>
                <span class="category-badge">Premium Shop</span>
              </div>
            </div>
            <div class="shop-content">
              <div class="shop-main-info">
                <h3>{{ shop.name }}</h3>
                <p class="shop-address"><i class="fa-solid fa-map-pin"></i> {{ shop.address }}</p>
              </div>
              
              <div class="shop-distance-meter" v-if="shop.distanceKm">
                <div class="distance-bar">
                   <i class="fa-solid fa-person-walking"></i>
                   <div class="bar-line"></div>
                   <i class="fa-solid fa-store"></i>
                </div>
                <span class="distance-text">
                  <strong>{{ shop.distanceKm.toFixed(1) }} km</strong> away
                </span>
              </div>
              <div class="shop-distance-meter" v-else-if="!userLocation">
                <span class="distance-text" style="font-size: 0.8rem; opacity: 0.7;">
                   Enable location to see distance
                </span>
              </div>

              <div class="shop-footer">
                <div class="shop-stats-pills">
                  <span><i class="fa-solid fa-motorcycle"></i> {{ shop.vehiclesCount }}</span>
                  <span><i class="fa-solid fa-star"></i> 4.8</span>
                </div>
                <div class="shop-actions-group">
                  <button class="visit-btn" @click="viewShop(shop)">View Details</button>
                  <button class="directions-btn" title="Open Google Maps Route" @click.stop="getDirections(shop)">
                    <i class="fa-solid fa-route"></i>
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>

    <CommonFooter />

    <!-- MANDATORY LOCATION MODAL -->
    <div v-if="showLocationModal" class="location-modal-overlay">
      <div class="location-modal-card">
        <div class="loc-icon-animate">
          <i class="fa-solid fa-location-dot"></i>
          <div class="loc-pulse"></div>
        </div>
        <h2>Access Required</h2>
        <p>To provide you with the nearest branches in Cambodia, we need to access your location before you can proceed to Login or Register.</p>
        <div class="loc-modal-actions">
          <button class="loc-btn cancel" @click="showLocationModal = false">Cancel</button>
          <button class="loc-btn open" @click="enableLocation">
            {{ locating ? 'Opening GPS...' : 'Allow Access' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
