<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { vehicleService, userService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import '../../css/MyBookings.css'

const router = useRouter()

const activeTab = ref('all')
const searchQuery = ref('')
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')
const selectedBooking = ref(null)
const selectedVehicle = ref(null)

const bookings = ref([])
const loading = ref(true)
const error = ref('')

const getStoredToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token') || ''

const fetchBookings = async () => {
  loading.value = true
  error.value = ''
  try {
    const token = getStoredToken()
    const headers = { Accept: 'application/json' }
    if (token) headers.Authorization = `Bearer ${token}`
    
    const response = await fetch('/api/my-bookings', { headers })
    if (!response.ok) {
      throw new Error('Failed to fetch bookings')
    }
    const data = await response.json()
    bookings.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    error.value = e.message || 'Unable to load bookings'
    console.error('Error fetching bookings:', e)
  } finally {
    loading.value = false
  }
}

// Fetch bookings on mount
fetchBookings()

const filteredBookings = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return bookings.value.filter((b) => {
    const statusMatch = activeTab.value === 'all' || b.status === activeTab.value
    if (!statusMatch) return false
    if (!q) return true

    const haystack = [
      b.vehicle_name,
      b.booking_code,
      b.shop_name,
      b.status,
      formatDate(b.start_date),
      formatDate(b.end_date),
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(q)
  })
})

const getStatusClass = (status) => {
  const statusMap = {
    pending: 'status-pending',
    confirmed: 'status-confirmed',
    completed: 'status-completed',
    cancelled: 'status-cancelled',
  }
  return statusMap[status?.toLowerCase()] || 'status-pending'
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }
  return labels[status?.toLowerCase()] || 'Pending'
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const formatCurrency = (value) => {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const getTotalDays = (start, end) => {
  if (!start || !end) return 0
  const startDate = new Date(start)
  const endDate = new Date(end)
  const diffTime = Math.abs(endDate - startDate)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1
}

const getBookingImage = (booking) => {
  if (!booking) return ''
  return booking.vehicle_image || booking.image_url || booking.image || ''
}

const normalizeVehicle = (vehicle) => {
  if (!vehicle) return null
  return {
    id: vehicle.id || null,
    brand: vehicle.brand || '',
    model: vehicle.model || '',
    category: vehicle.category || vehicle.type || '',
    year: vehicle.year || '',
    fuel_type: vehicle.fuel_type || vehicle.fuel || '',
    transmission: vehicle.transmission || '',
    seats: vehicle.seats || '',
    plate: vehicle.plate_number || vehicle.plate || '',
    price_per_day: Number(vehicle.price_per_day || vehicle.price || 0),
    status: vehicle.status || '',
    image_url: vehicle.image_url || vehicle.image || '',
    description: vehicle.description || '',
  }
}

const fetchVehicleFromApi = async (id) => {
  const token = getStoredToken()
  const headers = { Accept: 'application/json' }
  if (token) headers.Authorization = `Bearer ${token}`

  const response = await fetch(`/api/vehicles/${id}`, { headers })
  if (!response.ok) {
    throw new Error('Failed to load vehicle from API')
  }
  const data = await response.json()
  return normalizeVehicle(data?.data || data)
}

const closeDetails = () => {
  showDetailModal.value = false
  selectedBooking.value = null
  selectedVehicle.value = null
  detailError.value = ''
}

const goHome = () => {
  router.push('/')
}

const openDetails = async (booking) => {
  selectedBooking.value = booking
  selectedVehicle.value = null
  detailError.value = ''
  detailLoading.value = true

  try {
    const vehicleId = Number(booking?.vehicle_id || 0)
    if (vehicleId) {
      try {
        selectedVehicle.value = await fetchVehicleFromApi(vehicleId)
      } catch {
        selectedVehicle.value = normalizeVehicle(await vehicleService.getVehicle(vehicleId))
      }
    } else {
      detailError.value = 'Vehicle ID not found for this booking.'
    }
  } catch {
    detailError.value = 'Unable to load vehicle details.'
  } finally {
    detailLoading.value = false
    showDetailModal.value = true
  }
}

const detailTitle = computed(() => {
  if (selectedVehicle.value?.brand || selectedVehicle.value?.model) {
    return `${selectedVehicle.value.brand || ''} ${selectedVehicle.value.model || ''}`.trim()
  }
  return selectedBooking.value?.vehicle_name || 'Vehicle'
})

const detailDays = computed(() => {
  if (!selectedBooking.value) return 1
  return getTotalDays(selectedBooking.value.start_date, selectedBooking.value.end_date) || 1
})

const detailPricePerDay = computed(() => {
  const fromVehicle = Number(selectedVehicle.value?.price_per_day || 0)
  if (fromVehicle > 0) return fromVehicle
  const total = Number(selectedBooking.value?.total_price || 0)
  const days = Number(detailDays.value || 1)
  return days > 0 ? total / days : total
})

const detailTotalAmount = computed(() => Number(selectedBooking.value?.total_price || 0))
</script>

<template>
  <div class="bookings-page">
    <section class="bookings-panel">
      <div class="panel-head">
        <div class="panel-head-text">
          <h1>My Bookings</h1>
          <p>View and manage your vehicle rentals.</p>
        </div>
      </div>

      <div class="controls-row">
        <div class="tabs-row">
          <button class="tab-btn" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">All Bookings</button>
          <button class="tab-btn" :class="{ active: activeTab === 'pending' }" @click="activeTab = 'pending'">Pending</button>
          <button class="tab-btn" :class="{ active: activeTab === 'confirmed' }" @click="activeTab = 'confirmed'">Confirmed</button>
          <button class="tab-btn" :class="{ active: activeTab === 'completed' }" @click="activeTab = 'completed'">Completed</button>
          <button class="tab-btn" :class="{ active: activeTab === 'cancelled' }" @click="activeTab = 'cancelled'">Cancelled</button>
        </div>

        <div class="search-row">
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Search booking, shop, status, or date..."
          />
          <button type="button" class="search-btn" aria-label="Search">
            <svg class="search-icon-svg" viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="11" cy="11" r="7"></circle>
              <line x1="16.65" y1="16.65" x2="21" y2="21"></line>
            </svg>
          </button>
        </div>
      </div>
    </section>

    <section class="booking-list-wrap">
      <div class="booking-list-header">
        <span class="booking-list-title">Bookings</span>
        <button class="back-home-btn" type="button" @click="goHome">
          Back to Home
        </button>
      </div>
      <div v-if="loading" class="empty-state">
        Loading bookings...
      </div>
      <div v-else-if="error" class="empty-state" style="color: red;">
        {{ error }}
      </div>
      <div v-else-if="filteredBookings.length === 0" class="empty-state">
        No bookings found for your search.
      </div>
      <article v-for="booking in filteredBookings" :key="booking.id" class="booking-card">
        <div class="booking-image">
          <img :src="getBookingImage(booking)" :alt="booking.vehicle_name" class="vehicle-img" />
        </div>

        <div class="booking-info">
          <div class="booking-header">
            <h3 class="vehicle-name">{{ booking.vehicle_name }}</h3>
            <span :class="['status-pill', getStatusClass(booking.status)]">{{ getStatusLabel(booking.status) }}</span>
          </div>

          <p><span class="meta-label">Booking ID:</span> {{ booking.booking_code }}</p>
          <p><span class="meta-label">Shop:</span> {{ booking.shop_name }}</p>
          <p>
            <span class="meta-label">Date:</span>
            {{ formatDate(booking.start_date) }} to {{ formatDate(booking.end_date) }}
            ({{ getTotalDays(booking.start_date, booking.end_date) }} days)
          </p>

          <button class="details-btn" @click="openDetails(booking)">View Details</button>
        </div>

        <div class="booking-price">
          <span class="price-value">{{ formatCurrency(booking.total_price) }}</span>
        </div>
      </article>
    </section>

    <div v-if="showDetailModal" class="detail-overlay" @click.self="closeDetails">
      <div class="detail-modal">
        <button class="detail-close" type="button" @click="closeDetails" aria-label="Close details">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="6" y1="6" x2="18" y2="18"></line>
            <line x1="18" y1="6" x2="6" y2="18"></line>
          </svg>
        </button>

        <div v-if="detailError" class="detail-state detail-error">{{ detailError }}</div>
        <div v-else-if="selectedVehicle" class="detail-content detail-grid-layout">
          <div class="detail-left">
            <div class="detail-image-wrap detail-image-main">
              <img
                class="detail-image"
                :src="getBookingImage(selectedBooking) || selectedVehicle.image_url"
                :alt="detailTitle"
              />
            </div>
            <div class="detail-thumbnail-row">
              <div v-for="n in 3" :key="n" class="thumbnail-placeholder"></div>
            </div>
          </div>

          <div class="detail-right">
            <div class="detail-title-row">
              <div>
                <h2>{{ detailTitle }}</h2>
                <p class="detail-booking-code">{{ selectedBooking?.booking_code || `BK-${selectedBooking?.id || ''}` }}</p>
              </div>
              <span class="detail-status-badge" :class="getStatusClass(selectedBooking?.status)">
                {{ getStatusLabel(selectedBooking?.status) }}
              </span>
            </div>

            <div class="detail-price-row">

                <div class="detail-total-box">
                <span>Total</span>
                <strong>{{ formatCurrency(detailTotalAmount) }}</strong>
              </div>
              <div>
                <p class="detail-price-label">Price per day</p>
                <strong class="detail-price">{{ formatCurrency(detailPricePerDay) }}</strong>
                <span class="detail-price-suffix">USD</span>
              </div>
            
            </div>

            <div class="detail-section detail-section--compact">
              <div class="detail-row"><span>Status</span><strong>{{ getStatusLabel(selectedBooking?.status) }}</strong></div>
              <div class="detail-row"><span>Shop</span><strong>{{ selectedBooking?.shop_name || 'N/A' }}</strong></div>
              <div class="detail-row"><span>Rental Date</span><strong>{{ formatDate(selectedBooking?.start_date) }} - {{ formatDate(selectedBooking?.end_date) }}</strong></div>
              <div class="detail-row"><span>Duration</span><strong>{{ detailDays }} day(s)</strong></div>
            </div>

            <div class="detail-section detail-section--full">
              <div class="detail-grid detail-grid--info">
                <div class="detail-item detail-item--info">
                  <span>Type</span>
                  <strong>{{ selectedVehicle.category || 'N/A' }}</strong>
                </div>
                <div class="detail-item detail-item--info">
                  <span>Brand</span>
                  <strong>{{ selectedVehicle.brand || 'N/A' }}</strong>
                </div>
                <div class="detail-item detail-item--info">
                  <span>Fuel</span>
                  <strong>{{ selectedVehicle.fuel_type || 'N/A' }}</strong>
                </div>
                <div class="detail-item detail-item--info">
                  <span>Transmission</span>
                  <strong>{{ selectedVehicle.transmission || 'N/A' }}</strong>
                </div>
                <div class="detail-item detail-item--info">
                  <span>Plate</span>
                  <strong>{{ selectedVehicle.plate || selectedVehicle.plate_number || 'N/A' }}</strong>
                </div>
                <div class="detail-item detail-item--info">
                  <span>Status</span>
                  <strong>{{ selectedVehicle.status || 'N/A' }}</strong>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Common Footer -->
  <CommonFooter />
</template>
