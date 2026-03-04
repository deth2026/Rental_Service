<<<<<<< HEAD
<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService, vehicleService, shopService, bookingService } from '../../services/database.js'
import '../../Css/userDashboard.css'

const router = useRouter()

const search = ref('')
const selectedType = ref('All')
const selectedTransmission = ref('All')
const selectedBrand = ref('All')
const selectedFuel = ref('All')
const maxPrice = ref(500)

const shops = ref([])
const selectedShop = ref(null)
const vehicles = ref([])
const bookedCount = ref(0)
const selectedVehicle = ref(null)
const isLoadingVehicleDetail = ref(false)
const vehicleDetailError = ref('')
const selectedGalleryImage = ref(0)
const selectedAddOns = ref(['insurance'])
const bookingDays = ref(3)
const isSubmittingBooking = ref(false)
const showBookingSentPopup = ref(false)

const isLoadingShops = ref(false)
const shopsError = ref('')
const isLoadingVehicles = ref(false)
const vehiclesError = ref('')

const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')
const userInitials = computed(() => {
  const words = userDisplayName.value.trim().split(/\s+/).filter(Boolean)
  if (words.length === 0) return 'GU'
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

const fallbackImages = [
  'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1549399542-7e82138bc3f8?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1000&q=80',
  'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
]

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
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('blob:') || value.startsWith('data:')) {
    return value
  }
  if (value.startsWith('/')) {
    return value
  }
  return `/${value}`
}

const getShopNameById = (shopId) => {
  if (!shopId) return ''
  const match = shops.value.find((shop) => Number(shop.id) === Number(shopId))
  return match?.name || ''
}

const typeToDisplay = (value) => {
  const type = String(value || '').toLowerCase()
  if (type.includes('bike') && !type.includes('cycle')) return 'Motorbikes'
  if (type.includes('cycle')) return 'Bicycles'
  if (type.includes('car')) return 'Cars'
  return value || 'Cars'
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

const normalizeVehicle = (vehicle, index) => {
  const brand = vehicle.brand || 'Unknown'
  const model = vehicle.model || 'Vehicle'
  const displayType = typeToDisplay(vehicle.type)
  const resolvedShopName =
    vehicle.shop?.name ||
    vehicle.shop_name ||
    getShopNameById(vehicle.shop_id) ||
    selectedShop.value?.name ||
    'Unknown Shop'

  return {
    id: vehicle.id,
    shopId: vehicle.shop_id ?? null,
    name: `${brand} ${model}`.trim(),
    brand,
    model,
    type: displayType,
    transmission: vehicle.transmission || vehicle.transmission_type || 'Auto',
    fuel: vehicle.fuel_type || 'Petrol',
    seats: Number(vehicle.seats || 5),
    pricePerDay: Number(vehicle.price_per_day ?? 0),
    rating: Number(vehicle.average_rating || 4.8) || 4.8,
    ratingCount: Number(vehicle.ratings_count || 0),
    year: vehicle.year || '2024',
    status: vehicle.status || 'available',
    image: vehicle.image_url || vehicle.image || fallbackImages[index % fallbackImages.length],
    featured: index % 3 === 0,
    shopName: resolvedShopName,
    createdAt: vehicle.created_at || '',
    updatedAt: vehicle.updated_at || '',
  }
}

const formatShortDate = (value) => {
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('en-US', { month: '2-digit', day: '2-digit', year: 'numeric' })
}

const pickupDate = computed(() => {
  const base = selectedVehicle.value?.createdAt || new Date().toISOString()
  return formatShortDate(base)
})

const dropoffDate = computed(() => {
  const base = new Date(selectedVehicle.value?.createdAt || Date.now())
  base.setDate(base.getDate() + bookingDays.value)
  return formatShortDate(base.toISOString())
})

const vehicleGallery = computed(() => {
  if (!selectedVehicle.value) return []
  const base = [selectedVehicle.value.image, ...fallbackImages]
  return [...new Set(base.filter(Boolean))].slice(0, 4)
})

const activeVehicleImage = computed(() => {
  const images = vehicleGallery.value
  if (!images.length) return ''
  return images[selectedGalleryImage.value] || images[0]
})

const detailShop = computed(() => {
  if (!selectedVehicle.value) return null
  const bySelected = selectedShop.value && Number(selectedShop.value.id) === Number(selectedVehicle.value.shopId)
    ? selectedShop.value
    : null
  if (bySelected) return bySelected
  return shops.value.find((shop) => Number(shop.id) === Number(selectedVehicle.value.shopId)) || null
})

const addOnOptions = [
  { key: 'helmet', name: 'Premium Helmets (2x)', pricePerDay: 0 },
  { key: 'insurance', name: 'Theft Insurance', pricePerDay: 2 },
  { key: 'gps', name: 'GPS Navigation Tablet', pricePerDay: 3 },
]

const rentalDays = computed(() => bookingDays.value)

const selectedAddOnItems = computed(() =>
  addOnOptions.filter((option) => selectedAddOns.value.includes(option.key))
)

const addOnsTotal = computed(() =>
  selectedAddOnItems.value.reduce((sum, option) => sum + option.pricePerDay * rentalDays.value, 0)
)

const rentalBaseTotal = computed(() =>
  (selectedVehicle.value?.pricePerDay || 0) * rentalDays.value
)

const marketplaceFee = computed(() => 2.5)

const bookingTotal = computed(() =>
  rentalBaseTotal.value + addOnsTotal.value + marketplaceFee.value
)

const uniqueBrands = computed(() => ['All', ...new Set(vehicles.value.map((v) => v.brand).filter(Boolean))])

const categoryTabs = computed(() => {
  const counts = vehicles.value.reduce((acc, vehicle) => {
    acc[vehicle.type] = (acc[vehicle.type] || 0) + 1
    return acc
  }, {})

  return [
    { key: 'All', label: 'All', count: vehicles.value.length },
    ...Object.keys(counts).map((type) => ({ key: type, label: type, count: counts[type] })),
  ]
})

const filteredVehicles = computed(() =>
  vehicles.value.filter((vehicle) => {
    const query = search.value.trim().toLowerCase()
    const matchesSearch = query.length === 0 || vehicle.name.toLowerCase().includes(query)
    const matchesType = selectedType.value === 'All' || vehicle.type === selectedType.value
    const matchesTransmission =
      selectedTransmission.value === 'All' || vehicle.transmission === selectedTransmission.value
    const matchesBrand = selectedBrand.value === 'All' || vehicle.brand === selectedBrand.value
    const matchesFuel = selectedFuel.value === 'All' || vehicle.fuel === selectedFuel.value
    const matchesPrice = vehicle.pricePerDay <= Number(maxPrice.value)

    return matchesSearch && matchesType && matchesTransmission && matchesBrand && matchesFuel && matchesPrice
  })
)

const resetVehicleFilters = () => {
  search.value = ''
  selectedType.value = 'All'
  selectedTransmission.value = 'All'
  selectedBrand.value = 'All'
  selectedFuel.value = 'All'
  maxPrice.value = 500
}

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

const loadVehiclesForShop = async (shopId) => {
  isLoadingVehicles.value = true
  vehiclesError.value = ''

  try {
    const data = await vehicleService.getVehicles({ shop_id: shopId })
    vehicles.value = Array.isArray(data) ? data.map(normalizeVehicle) : []
  } catch (error) {
    vehiclesError.value = error.message || 'Failed to load vehicles for this shop.'
  } finally {
    isLoadingVehicles.value = false
  }
}

const selectShop = async (shop) => {
  closeVehicleDetail()
  selectedShop.value = shop
  resetVehicleFilters()
  await loadVehiclesForShop(shop.id)
}

const backToShops = () => {
  closeVehicleDetail()
  selectedShop.value = null
  vehicles.value = []
  vehiclesError.value = ''
  resetVehicleFilters()
}

const openVehicleDetail = async (vehicle) => {
  selectedVehicle.value = vehicle
  selectedGalleryImage.value = 0
  selectedAddOns.value = ['insurance']
  bookingDays.value = 3
  vehicleDetailError.value = ''
  isLoadingVehicleDetail.value = true

  try {
    const data = await vehicleService.getVehicle(vehicle.id)
    selectedVehicle.value = normalizeVehicle(data, 0)
  } catch (error) {
    vehicleDetailError.value = error.message || 'Failed to load full vehicle details.'
  } finally {
    isLoadingVehicleDetail.value = false
  }
}

const closeVehicleDetail = () => {
  selectedVehicle.value = null
  selectedGalleryImage.value = 0
  selectedAddOns.value = ['insurance']
  bookingDays.value = 3
  isLoadingVehicleDetail.value = false
  vehicleDetailError.value = ''
}

const updateBookingDays = (delta) => {
  const next = Number(bookingDays.value) + delta
  bookingDays.value = Math.min(30, Math.max(1, next))
}

const confirmBooking = async () => {
  if (!selectedVehicle.value) return

  if (!userService.isAuthenticated()) {
    alert('Please login first to book this vehicle.')
    await router.push('/login')
    return
  }

  isSubmittingBooking.value = true
  try {
    const payload = {
      vehicle_id: selectedVehicle.value.id,
      days: rentalDays.value,
      start_date: new Date().toISOString().slice(0, 10),
    }
    await bookingService.createBooking(payload)
    bookedCount.value += 1
    closeVehicleDetail()
    showBookingSentPopup.value = true
  } catch (error) {
    alert(error.message || 'Failed to send booking request.')
  } finally {
    isSubmittingBooking.value = false
  }
}

const closeBookingSentPopup = () => {
  showBookingSentPopup.value = false
}

const handleLogout = async () => {
  await userService.logout()
  await router.push('/login')
}

onMounted(() => {
  loadShops()
})
</script>

<template>
  <div class="rides-page">
    <div class="rides-shell">
      <Teleport to="body">
        <div v-if="showBookingSentPopup" class="booking-popup-overlay" @click.self="closeBookingSentPopup">
          <div class="booking-popup">
            <div class="booking-popup-icon">✓</div>
            <h3>Booking Sent</h3>
            <p>Your booking request was sent to the shop owner. Please wait for confirmation.</p>
            <button class="booking-popup-btn" @click="closeBookingSentPopup">OK</button>
          </div>
        </div>
      </Teleport>

      <header class="top-header">
        <div class="brand">Cambodia <span>Rides</span></div>

        <div class="search-chip-group">
          <div class="search-chip">👋 {{ userDisplayName }}</div>
          <div class="search-chip">🏬 {{ shops.length }} Shops</div>
        </div>

        <nav class="top-nav">
          <a class="active" href="#">Home</a>
          <a href="#">My Bookings</a>
          <a href="#">Support</a>
        </nav>

        <div class="header-actions">
          <span class="notify">🔔</span>
          <div class="avatar">{{ userInitials }}</div>
          <button class="logout-btn" @click="handleLogout">Logout</button>
        </div>
      </header>

      <section v-if="!selectedShop" class="shops-section">
        <div class="results-head">
          <div>
            <h1>Choose a Shop</h1>
            <p>Select one shop to view its vehicles.</p>
          </div>
        </div>

        <div v-if="isLoadingShops" class="status-box">Loading shops...</div>
        <div v-else-if="shopsError" class="status-box error">{{ shopsError }}</div>
        <div v-else-if="shops.length === 0" class="status-box">No shops found in the system.</div>

        <div v-else class="shops-grid">
          <article v-for="shop in shops" :key="shop.id" class="shop-card" @click="selectShop(shop)">
            <div class="shop-card-image">
              <img
                :src="shop.image"
                :alt="shop.name"
                @error="$event.target.src = 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80'"
              />
              <span :class="'status-badge status-' + (shop.status || 'active')">
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
              <div class="shop-info" style="grid-column: span 2;">
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

            <button class="view-shop-btn" @click.stop="selectShop(shop)">View Vehicles</button>
          </article>
        </div>
      </section>

      <template v-else>
        <section class="results-head">
          <div>
            <button class="back-btn" @click="backToShops">Back to Shops</button>
            <h1>{{ selectedShop.name }}</h1>
            <p>{{ filteredVehicles.length }} vehicles found in this shop.</p>
          </div>
          <input v-model="search" class="search-input" type="text" placeholder="Search vehicle" />
        </section>

        <section class="filter-bar">
          <button
            v-for="tab in categoryTabs"
            :key="tab.key"
            class="type-pill"
            :class="{ active: selectedType === tab.key }"
            @click="selectedType = tab.key"
          >
            {{ tab.label }}
            <small>{{ tab.count }}</small>
          </button>

          <select v-model="maxPrice" class="filter-select">
            <option :value="50">Price up to $50</option>
            <option :value="100">Price up to $100</option>
            <option :value="200">Price up to $200</option>
            <option :value="500">Price up to $500</option>
            <option :value="1000">Price up to $1000</option>
          </select>

          <select v-model="selectedTransmission" class="filter-select">
            <option value="All">Transmission</option>
            <option value="Auto">Auto</option>
            <option value="Semi-Auto">Semi-Auto</option>
            <option value="Manual">Manual</option>
          </select>

          <select v-model="selectedBrand" class="filter-select">
            <option value="All">Brand</option>
            <option v-for="brand in uniqueBrands.slice(1)" :key="brand" :value="brand">{{ brand }}</option>
          </select>

          <select v-model="selectedFuel" class="filter-select">
            <option value="All">Fuel</option>
            <option value="Petrol">Petrol</option>
            <option value="Diesel">Diesel</option>
            <option value="Electric">Electric</option>
            <option value="Hybrid">Hybrid</option>
          </select>
        </section>

        <section v-if="isLoadingVehicles" class="status-box">Loading vehicles...</section>
        <section v-else-if="vehiclesError" class="status-box error">{{ vehiclesError }}</section>
        <section v-else-if="filteredVehicles.length === 0" class="status-box">No vehicles in this shop.</section>

        <section v-else class="vehicle-grid">
          <article v-for="vehicle in filteredVehicles" :key="vehicle.id" class="vehicle-card">
            <div class="image-wrap">
              <img
                :src="vehicle.image"
                :alt="vehicle.name"
                @error="$event.target.src = 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=400&q=80'"
              />
              <span class="badge">{{ vehicle.status }}</span>
            </div>
            <div class="vehicle-body">
              <div class="title-row">
                <h3>{{ vehicle.name }}</h3>
                <span class="rating">{{ vehicle.rating.toFixed(1) }} ★</span>
              </div>
              <p class="meta">{{ vehicle.shopName }} · {{ vehicle.year }} · {{ vehicle.ratingCount }} ratings</p>
              <div class="specs">
                <span>{{ vehicle.type }}</span>
                <span>{{ vehicle.transmission }}</span>
                <span>{{ vehicle.fuel }}</span>
                <span>{{ vehicle.seats }} seats</span>
              </div>

              <div class="card-footer">
                <p class="price">${{ vehicle.pricePerDay }}<small>/day</small></p>
                <button class="book-btn" @click="openVehicleDetail(vehicle)">Book Now</button>
              </div>
            </div>
          </article>
        </section>

        <section class="map-wrap">
          <div class="map-toolbar">
            <button>+</button>
            <button>−</button>
          </div>
          <div class="price-marker marker-a">$12</div>
          <div class="price-marker marker-b">$8</div>
          <div class="price-marker marker-c">$16</div>
        </section>
      </template>

      <section v-if="selectedVehicle" class="vehicle-detail-overlay" @click.self="closeVehicleDetail">
        <article class="vehicle-detail-modal">
          <button class="vehicle-detail-close" @click="closeVehicleDetail">✕</button>

          <div v-if="isLoadingVehicleDetail" class="status-box">Loading vehicle details...</div>
          <div v-else class="vehicle-detail-page">
            <p class="vehicle-breadcrumb">HOME › {{ selectedVehicle.shopName.toUpperCase() }} › {{ selectedVehicle.name.toUpperCase() }}</p>

            <div class="vehicle-detail-main-grid">
              <section class="vehicle-showcase">
                <div class="vehicle-detail-hero">
                  <img class="vehicle-detail-image" :src="activeVehicleImage" :alt="selectedVehicle.name" />
                </div>

                <div class="vehicle-gallery-row">
                  <button
                    v-for="(image, index) in vehicleGallery"
                    :key="`${selectedVehicle.id}-${index}`"
                    class="vehicle-thumb"
                    :class="{ active: selectedGalleryImage === index }"
                    @click="selectedGalleryImage = index"
                  >
                    <img :src="image" :alt="`${selectedVehicle.name} ${index + 1}`" />
                  </button>
                </div>

                <div class="vehicle-info-panel">
                  <div class="vehicle-name-row">
                    <div>
                      <h2>{{ selectedVehicle.name }}</h2>
                      <p>⭐ {{ selectedVehicle.rating.toFixed(1) }} · {{ selectedVehicle.ratingCount }} bookings · <strong>{{ selectedVehicle.shopName }}</strong></p>
                    </div>
                    <span class="hero-status" :class="`hero-status-${String(selectedVehicle.status || '').toLowerCase()}`">
                      {{ selectedVehicle.status }}
                    </span>
                  </div>

                  <div class="vehicle-spec-grid">
                    <div class="spec-box"><span>Year</span><strong>{{ selectedVehicle.year }}</strong></div>
                    <div class="spec-box"><span>Transmission</span><strong>{{ selectedVehicle.transmission }}</strong></div>
                    <div class="spec-box"><span>Fuel</span><strong>{{ selectedVehicle.fuel }}</strong></div>
                    <div class="spec-box"><span>Seats</span><strong>{{ selectedVehicle.seats }}</strong></div>
                  </div>
                </div>

                <div class="managed-by-wrap">
                  <h3>Managed by</h3>
                  <div class="managed-card">
                    <img :src="detailShop?.image || selectedVehicle.image" :alt="detailShop?.name || selectedVehicle.shopName" />
                    <div class="managed-content">
                      <strong>{{ detailShop?.name || selectedVehicle.shopName }}</strong>
                      <p>{{ detailShop?.address || 'No shop address provided' }}</p>
                      <small>{{ detailShop?.phone || 'No phone number' }} · {{ detailShop?.email || 'No email' }}</small>
                    </div>
                    <button class="contact-btn" type="button">Contact Shop</button>
                  </div>
                </div>
              </section>

              <aside class="booking-summary-card">
                <div class="summary-top">
                  <p>${{ selectedVehicle.pricePerDay }}<small>/day</small></p>
                  <span>Instant Book</span>
                </div>

                <div class="date-grid">
                  <div><span>Pick-up</span><strong>{{ pickupDate }}</strong></div>
                  <div><span>Drop-off</span><strong>{{ dropoffDate }}</strong></div>
                </div>

                <div class="days-editor">
                  <span>Booking days</span>
                  <div class="days-controls">
                    <button type="button" @click="updateBookingDays(-1)">−</button>
                    <input
                      v-model.number="bookingDays"
                      type="number"
                      min="1"
                      max="30"
                      @change="bookingDays = Math.min(30, Math.max(1, Number(bookingDays) || 1))"
                    />
                    <button type="button" @click="updateBookingDays(1)">+</button>
                  </div>
                </div>

                <h4>Optional add-ons</h4>
                <div class="addons-list">
                  <label
                    v-for="option in addOnOptions"
                    :key="option.key"
                    class="addon-item"
                    :class="{ active: selectedAddOns.includes(option.key) }"
                  >
                    <input
                      type="checkbox"
                      :checked="selectedAddOns.includes(option.key)"
                      @change="
                        selectedAddOns = selectedAddOns.includes(option.key)
                          ? selectedAddOns.filter((item) => item !== option.key)
                          : [...selectedAddOns, option.key]
                      "
                    />
                    <span>{{ option.name }}</span>
                    <strong>{{ option.pricePerDay === 0 ? 'Free' : `+$${option.pricePerDay}/day` }}</strong>
                  </label>
                </div>

                <div class="cost-row">
                  <span>${{ selectedVehicle.pricePerDay.toFixed(2) }} x {{ rentalDays }} days</span>
                  <strong>${{ rentalBaseTotal.toFixed(2) }}</strong>
                </div>
                <div class="cost-row">
                  <span>Add-ons</span>
                  <strong>${{ addOnsTotal.toFixed(2) }}</strong>
                </div>
                <div class="cost-row">
                  <span>Marketplace fee</span>
                  <strong>${{ marketplaceFee.toFixed(2) }}</strong>
                </div>
                <div class="cost-row total">
                  <span>Total (USD)</span>
                  <strong>${{ bookingTotal.toFixed(2) }}</strong>
                </div>

                <div class="review-box">
                  <h5>Guest Reviews</h5>
                  <p>{{ selectedVehicle.rating.toFixed(1) }}</p>
                  <span>Based on {{ selectedVehicle.ratingCount || 1 }} reviews</span>
                </div>
              </aside>
            </div>

            <p v-if="vehicleDetailError" class="status-box error">{{ vehicleDetailError }}</p>

            <div class="vehicle-detail-actions">
              <button class="secondary-btn" @click="closeVehicleDetail">Close</button>
              <button class="book-btn" :disabled="isSubmittingBooking" @click="confirmBooking">
                {{ isSubmittingBooking ? 'Sending...' : 'Book Now' }}
              </button>
            </div>
          </div>
        </article>
      </section>

      <footer class="page-footer">
        <div class="footer-brand">
          <h4>Cambodia<span>Rides</span></h4>
          <p>
            Connecting adventurous travelers with the best local vehicle rentals in Cambodia.
            Explore on your own terms.
          </p>
          <small>Booked this session: {{ bookedCount }}</small>
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
      </footer>
    </div>
  </div>
</template>
=======
<template>
  <h1>Welcome to User Dashboard</h1>
</template>
>>>>>>> develop
