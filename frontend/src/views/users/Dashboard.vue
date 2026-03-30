<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { logoutUser } from '@/services/auth'
import { shopApi } from '@/services/api'
import CommonFooter from '@/components/CommonFooter.vue'
import UserNavbar from '@/components/UserNavbar.vue'
import CambodiaMap from '@/components/CambodiaMap.vue'
import { readStoredLocation, saveLocationAccess } from '@/utils/locationAccess'
import '@/css/userDashboard.css'

const router = useRouter()

const SEARCH_HISTORY_KEY = 'chong_choul_province_searches'

const CAMBODIA_PROVINCES = [
  'Banteay Meanchey',
  'Battambang',
  'Kompong Cham',
  'Kompong Chhnang',
  'Kompong Speu',
  'Kompong Thom',
  'Kompot',
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

const isLoading = ref(false)
const dataError = ref('')
const shops = ref([])
const provinceQuery = ref('')
const showSuggestions = ref(false)
const selectedProvince = ref('Phnom Penh')
const searchMessage = ref('')

const suggestions = computed(() => {
  const query = provinceQuery.value.trim().toLowerCase()
  if (!query) return []
  return provinceOptions.value.filter(province => 
    province.toLowerCase().startsWith(query)
  ).slice(0, 5)
})

const selectSuggestion = (province) => {
  provinceQuery.value = province
  showSuggestions.value = false
  searchProvince()
}

const onSearchInput = () => {
  showSuggestions.value = true
}

const hideSuggestions = () => {
  setTimeout(() => {
    showSuggestions.value = false
  }, 200)
}

const userLocation = ref(getStoredLocation())
const locationStatus = ref(userLocation.value ? 'Current location detected.' : 'Enable location for nearest distance.')
const locating = ref(false)

const chipScrollContainer = ref(null)

function getStoredLocation() {
  const stored = readStoredLocation()
  if (!stored) return null
  return { lat: stored.lat, lng: stored.lng }
}

function getSearchHistory() {
  try {
    const raw = localStorage.getItem(SEARCH_HISTORY_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

function saveToSearchHistory(province) {
  const history = getSearchHistory().filter((p) => p !== province)
  history.unshift(province)
  localStorage.setItem(SEARCH_HISTORY_KEY, JSON.stringify(history.slice(0, 25)))
}

const normalizeProvinceName = (value) => {
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
  if (normalized.includes('phnom') && normalized.includes('penh')) return 'Phnom Penh'
  if (normalized.includes('preah') && normalized.includes('sihanouk')) return 'Preah Sihanouk'
  if (normalized.includes('sihanoukville')) return 'Preah Sihanouk'

  const flexibleMatches = [
    { province: 'Phnom Penh', patterns: [/phnom[\s\S]{0,160}penh/] },
    { province: 'Preah Sihanouk', patterns: [/preah[\s\S]{0,160}sihanouk/, /sihanoukville/] },
    { province: 'Banteay Meanchey', patterns: [/banteay[\s,/-]*meanchey/] },
    { province: 'Kampong Cham', patterns: [/kampong[\s,/-]*cham/] },
    { province: 'Kampong Chhnang', patterns: [/kampong[\s,/-]*chhnang/] },
    { province: 'Kampong Speu', patterns: [/kampong[\s,/-]*speu/] },
    { province: 'Kampong Thom', patterns: [/kampong[\s,/-]*thom/] },
    { province: 'Koh Kong', patterns: [/koh[\s,/-]*kong/] },
    { province: 'Oddar Meanchey', patterns: [/oddar[\s,/-]*meanchey/] },
    { province: 'Preah Vihear', patterns: [/preah[\s,/-]*vihear/] },
    { province: 'Prey Veng', patterns: [/prey[\s,/-]*veng/] },
    { province: 'Siem Reap', patterns: [/siem[\s,/-]*reap/] },
    { province: 'Stung Treng', patterns: [/stung[\s,/-]*treng/] },
    { province: 'Svay Rieng', patterns: [/svay[\s,/-]*rieng/] },
    { province: 'Tboung Khmum', patterns: [/tboung[\s,/-]*khmum/, /tbong[\s,/-]*khmum/] },
  ]

  for (const entry of flexibleMatches) {
    if (entry.patterns.some((pattern) => pattern.test(normalized))) {
      return entry.province
    }
  }

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
    shop?.province ||
      shop?.city?.name ||
      extractProvinceFromText(shop?.location) ||
      extractProvinceFromText(shop?.address) ||
      extractProvinceFromText(shop?.map_url)
  )

  return {
    id: Number(shop.id),
    name: shop.name || `Shop #${shop.id}`,
    address: shop.address || 'Address not available',
    province,
    latitude: Number(shop.latitude ?? shop.lat),
    longitude: Number(shop.longitude ?? shop.lng),
    map_url: shop.map_url || shop.location || '',
    vehiclesCount: Number(shop.vehicles_count || 0),
    rating:
      (shop.rating ?? shop.avg_rating ?? shop.rating_avg ?? shop.rating_score ?? shop.average_rating) !== undefined
        ? Number(shop.rating ?? shop.avg_rating ?? shop.rating_avg ?? shop.rating_score ?? shop.average_rating)
        : null,
    status: String(shop.status || 'active').toLowerCase(),
    img_url: shop.img_url_full || shop.img_url || '/Images/default-avatar.svg'
  }
}

const provinceOptions = computed(() => {
  const dynamic = shops.value.map((shop) => shop.province).filter(Boolean)
  return Array.from(new Set([...CAMBODIA_PROVINCES, ...dynamic])).sort((a, b) => a.localeCompare(b))
})

// Province chips randomized with search history priority
const provinceChips = computed(() => {
  const history = getSearchHistory()
  const allProvinces = [...provinceOptions.value]

  // Separate: history provinces first, then shuffle the rest
  const historySet = new Set(history)
  const historyProvinces = history.filter((p) => allProvinces.includes(p))
  const remaining = allProvinces.filter((p) => !historySet.has(p))

  // Shuffle remaining using Fisher-Yates
  for (let i = remaining.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[remaining[i], remaining[j]] = [remaining[j], remaining[i]]
  }

  return [...historyProvinces, ...remaining]
})

const selectedCategory = ref('All')
const categories = ['All', 'Motorcycles', 'Bicycles', 'Cars']

const selectedProvinceShops = computed(() => {
  const normalized = normalizeProvinceName(selectedProvince.value)
  let inProvince = shops.value.filter((shop) => normalizeProvinceName(shop.province) === normalized)

  // Filter by category if not 'All'
  if (selectedCategory.value !== 'All') {
    // In a real app, this would check if the shop has vehicles of this category
    // For now, we'll keep it simple or filter based on shop names/types if available
    // result = result.filter(shop => shop.category === selectedCategory.value)
  }

  const withDistance = inProvince.map((shop) => {
    let distanceKm = null
    if (userLocation.value && Number.isFinite(shop.latitude) && Number.isFinite(shop.longitude)) {
      distanceKm = calculateDistanceKm(userLocation.value.lat, userLocation.value.lng, shop.latitude, shop.longitude)
    }
    return { ...shop, distanceKm }
  })

  withDistance.sort((a, b) => {
    if (a.distanceKm !== null && b.distanceKm !== null) return a.distanceKm - b.distanceKm
    return a.name.localeCompare(b.name)
  })

  return withDistance.map((shop, idx) => ({
    ...shop,
    isNearest: shop.distanceKm !== null && idx === 0,
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
  saveToSearchHistory(matched)
}

const selectProvince = (province) => {
  selectedProvince.value = province
  provinceQuery.value = province
  searchMessage.value = `Showing shops in ${province}.`
  saveToSearchHistory(province)
}

const loadShops = async () => {
  isLoading.value = true
  dataError.value = ''

  try {
    const response = await shopApi.getAll({ active_only: true })
    shops.value = parseArrayPayload(response.data)
      .map((shop) => normalizeShop(shop))
      .filter((shop) => shop.status === 'active')

    if (!shops.value.some((shop) => normalizeProvinceName(shop.province) === normalizeProvinceName(selectedProvince.value))) {
      selectedProvince.value = shops.value[0]?.province || 'Phnom Penh'
      provinceQuery.value = selectedProvince.value
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

const enableLocation = () => {
  if (locating.value) return
  if (!navigator?.geolocation) {
    locationStatus.value = 'Geolocation is not supported in this browser.'
    return
  }

  locating.value = true
  locationStatus.value = 'Detecting your location...'

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const nextLocation = {
        lat: Number(position.coords.latitude),
        lng: Number(position.coords.longitude),
      }
      const saved = saveLocationAccess(nextLocation)
      if (!saved) {
        locating.value = false
        locationStatus.value = 'Could not save location. Please try again.'
        return
      }
      userLocation.value = { lat: saved.lat, lng: saved.lng }
      locationStatus.value = 'Location detected successfully.'
      locating.value = false
    },
    () => {
      locating.value = false
      locationStatus.value = 'Could not detect location. Please allow GPS and try again.'
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
  )
}

const dashboardNavItems = [
  { label: 'My Bookings', route: '/my-bookings' },
  { label: 'Profile', route: '/user/profile' },
]

const onLogout = async () => {
  await logoutUser()
  router.push('/login')
}

// Province banner images
const provinceBanners = {
  'Phnom Penh': 'https://images.pexels.com/photos/33871002/pexels-photo-33871002.jpeg',
  'Siem Reap': 'https://images.pexels.com/photos/11844578/pexels-photo-11844578.jpeg',
  'Preah Sihanouk': 'https://images.pexels.com/photos/1379944/pexels-photo-1379944.jpeg',
  'Kampot': 'https://images.pexels.com/photos/19525920/pexels-photo-19525920.jpeg',
  'Kep': 'https://images.pexels.com/photos/14774679/pexels-photo-14774679.png',
  'Battambang': 'https://images.pexels.com/photos/33529396/pexels-photo-33529396.jpeg',
  'Mondulkiri': 'https://images.pexels.com/photos/32257927/pexels-photo-32257927.jpeg',
  'Ratanakiri': 'https://images.pexels.com/photos/36416532/pexels-photo-36416532.jpeg',
  'Koh Kong': 'https://images.pexels.com/photos/12001663/pexels-photo-12001663.jpeg',
  'Kratie': 'https://images.pexels.com/photos/19063405/pexels-photo-19063405.jpeg',
  'Kandal': 'https://images.pexels.com/photos/36235065/pexels-photo-36235065.jpeg',
  'Kampong Cham': 'https://images.pexels.com/photos/34809450/pexels-photo-34809450.jpeg',
  'Stung Treng': 'https://images.pexels.com/photos/19063383/pexels-photo-19063383.jpeg',
  'Pursat': 'https://images.pexels.com/photos/34814739/pexels-photo-34814739.png',
  'Prey Veng': 'https://images.pexels.com/photos/34814741/pexels-photo-34814741.png',
  'default': 'https://images.pexels.com/photos/19063354/pexels-photo-19063354.jpeg'
}

const currentBanner = computed(() => provinceBanners[selectedProvince.value] || provinceBanners['default'])

const scrollChips = (direction) => {
  if (chipScrollContainer.value) {
    const scrollAmount = 200
    chipScrollContainer.value.scrollBy({
      left: direction === 'right' ? scrollAmount : -scrollAmount,
      behavior: 'smooth',
    })
  }
}

onMounted(async () => {
  await loadShops()
  provinceQuery.value = selectedProvince.value
})
</script>

<template>
  <div class="customer-page">
    <UserNavbar
      :nav-items="dashboardNavItems"
      :show-back-button="false"
      :show-fallback-message="false"
      @logout-request="onLogout"
    />

    <main class="customer-main">
      <!-- Hero Banner -->
      <section class="hero-banner-section" :style="{ backgroundImage: `linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.5)), url(${currentBanner})` }">
        <div class="hero-banner-content">
          <h1>{{ selectedProvince }}</h1>
          <p>Explore the best rentals in {{ selectedProvince }}</p>
        </div>
      </section>

      <!-- Map Section with overlaid search -->
      <section class="map-explorer-section">
        <div class="map-explorer-inner">
          <!-- Search bar overlaid on map -->
          <div class="map-search-overlay">
            <div class="search-bar-wrap">
              <div class="search-input-container">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input
                  v-model="provinceQuery"
                  type="text"
                  class="province-search-input"
                  placeholder="Search for a province (e.g., Siem Reap)..."
                  @input="onSearchInput"
                  @blur="hideSuggestions"
                  @keydown.enter.prevent="searchProvince"
                />
                <div v-if="showSuggestions && suggestions.length > 0" class="province-suggestions">
                  <div
                    v-for="suggestion in suggestions"
                    :key="suggestion"
                    class="suggestion-item"
                    @mousedown="selectSuggestion(suggestion)"
                  >
                    {{ suggestion }}
                  </div>
                </div>
              </div>
              <button class="province-search-btn" @click="searchProvince" aria-label="Search province">
                <i class="fa-solid fa-magnifying-glass"></i>
              </button>
            </div>

            <!-- Province chips row -->
            <div class="province-chips-row">
              <button class="chip-scroll-btn chip-scroll-left" @click="scrollChips('left')" aria-label="Scroll left">
                <i class="fa-solid fa-chevron-left"></i>
              </button>
              <div ref="chipScrollContainer" class="province-chips-scroll">
                <button
                  v-for="province in provinceChips"
                  :key="province"
                  class="province-chip"
                  :class="{ active: selectedProvince === province }"
                  @click="selectProvince(province)"
                >
                  {{ province }}
                </button>
              </div>
              <button class="chip-scroll-btn chip-scroll-right" @click="scrollChips('right')" aria-label="Scroll right">
                <i class="fa-solid fa-chevron-right"></i>
              </button>
            </div>
          </div>

          <!-- Cambodia SVG Map -->
          <CambodiaMap
            :shops="shops"
            :selectedProvince="selectedProvince"
            @select-province="selectProvince"
          />
        </div>

        <p v-if="searchMessage" class="search-message">{{ searchMessage }}</p>

        <!-- Location status -->
        <div class="status-row">
          <div class="location-status-box">
            <i class="fa-solid fa-location-dot"></i>
            <span>{{ locationStatus }}</span>
          </div>
          <button class="location-btn" :disabled="locating" @click="enableLocation">
            <i class="fa-solid fa-crosshairs"></i>
            {{ locating ? 'Detecting...' : 'Use My Location' }}
          </button>
        </div>
        <p v-if="dataError" class="error-note">{{ dataError }}</p>
      </section>

      <!-- Shop Results -->
      <section class="shop-result-section">
        <div class="section-header">
          <h2>Our Branches in {{ selectedProvince }}</h2>
          <div class="category-filters">
            <button
              v-for="cat in categories"
              :key="cat"
              class="category-btn"
              :class="{ active: selectedCategory === cat }"
              @click="selectedCategory = cat"
            >
              {{ cat }}
            </button>
          </div>
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

              <div class="shop-distance-meter" v-if="userLocation">
                <div class="distance-bar">
                  <i class="fa-solid fa-person-walking"></i>
                  <div class="bar-line"></div>
                  <i class="fa-solid fa-store"></i>
                </div>
                <span class="distance-text">
                  <strong>{{ shop.distanceKm ? shop.distanceKm.toFixed(2) : '...' }} km</strong> from your location
                </span>
              </div>

                <div class="shop-footer">
                <div class="shop-stats-pills">
                  <span title="Number of vehicles available"><i class="fa-solid fa-motorcycle"></i> {{ shop.vehiclesCount }}</span>
                  <span title="Average rating"><i class="fa-solid fa-star"></i> {{ typeof shop.rating === 'number' && !isNaN(shop.rating) ? shop.rating.toFixed(1) : '—' }}</span>
                </div>
                <button class="visit-btn" @click="viewShop(shop)">View Details</button>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>

    <CommonFooter />
  </div>
</template>

