<script setup>
import { computed, ref } from 'vue'
import { vehicleService } from '../../services/database.js'

const activeTab = ref('all')
const searchQuery = ref('')
const showDetailModal = ref(false)
const detailLoading = ref(false)
const detailError = ref('')
const selectedBooking = ref(null)
const selectedVehicle = ref(null)

const bookings = ref([
  {
    id: 1,
    vehicle_id: 101,
    vehicle_name: 'BOO COO 2008',
    booking_code: 'BK-20260307-0008',
    shop_name: 'City Bike & Car Rental',
    customer_name: 'Main Shop Owner',
    customer_email: 'owner@example.com',
    start_date: '2026-03-07',
    end_date: '2026-03-08',
    total_price: 20,
    status: 'pending',
    image: 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=700&q=80',
  },
  {
    id: 2,
    vehicle_id: 102,
    vehicle_name: 'Toyota Corolla Cross 2023',
    booking_code: 'BK-20260307-0007',
    shop_name: 'City Bike & Car Rental',
    customer_name: 'Main Shop Owner',
    customer_email: 'owner@example.com',
    start_date: '2026-03-08',
    end_date: '2026-03-14',
    total_price: 525,
    status: 'confirmed',
    image: 'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=700&q=80',
  },
])

const filteredBookings = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  return bookings.value.filter((b) => {
    const statusMatch = activeTab.value === 'all' || b.status === activeTab.value
    if (!statusMatch) return false
    if (!q) return true

    const haystack = [
      b.vehicle_name,
      b.booking_code,
      b.customer_name,
      b.customer_email,
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

const canAcceptReject = (status) => status?.toLowerCase() === 'pending'
const canComplete = (status) => status?.toLowerCase() === 'confirmed'
const getViewBtnClass = (status) =>
  status?.toLowerCase() === 'confirmed' ? 'action-btn btn-complete' : 'action-btn btn-accept'

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

const getStoredToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token') || ''

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
        <h1>My Bookings</h1>
        <p>View and manage your vehicle rentals.</p>
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
      <div v-if="filteredBookings.length === 0" class="empty-state">
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
          <p><span class="meta-label">Customer:</span> {{ booking.customer_name }}</p>
          <p><span class="meta-label">Customer Email:</span> {{ booking.customer_email }}</p>
          <p>
            <span class="meta-label">Date:</span>
            {{ formatDate(booking.start_date) }} to {{ formatDate(booking.end_date) }}
            ({{ getTotalDays(booking.start_date, booking.end_date) }} days)
          </p>

          <div class="action-row">
            <button :class="getViewBtnClass(booking.status)" @click="openDetails(booking)">View</button>
            <button v-if="canAcceptReject(booking.status)" class="action-btn btn-accept">✓ Accept</button>
            <button v-if="canAcceptReject(booking.status)" class="action-btn btn-reject">✕ Reject</button>
            <button v-if="canComplete(booking.status)" class="action-btn btn-complete">★ Complete</button>
          </div>
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
        <div v-else-if="selectedVehicle" class="detail-content">
          <div class="detail-image-wrap">
            <img
              class="detail-image"
              :src="getBookingImage(selectedBooking) || selectedVehicle.image_url"
              :alt="detailTitle"
            />
          </div>

          <div class="detail-title-row">
            <div>
              <h2>{{ detailTitle }}</h2>
              <p class="detail-booking-code">{{ selectedBooking?.booking_code || `BK-${selectedBooking?.id || ''}` }}</p>
            </div>
            <span class="detail-status-badge" :class="getStatusClass(selectedBooking?.status)">
              {{ getStatusLabel(selectedBooking?.status) }}
            </span>
          </div>

          <div class="detail-section">
            <h3>Vehicle Details</h3>
            <div class="detail-grid">
              <div class="detail-item"><span>Type</span><strong>{{ selectedVehicle.category || 'N/A' }}</strong></div>
              <div class="detail-item"><span>Year</span><strong>{{ selectedVehicle.year || 'N/A' }}</strong></div>
              <div class="detail-item"><span>Brand</span><strong>{{ selectedVehicle.brand || 'N/A' }}</strong></div>
              <div class="detail-item"><span>Model</span><strong>{{ selectedVehicle.model || 'N/A' }}</strong></div>
              <div class="detail-item"><span>Fuel Type</span><strong>{{ selectedVehicle.fuel_type || 'N/A' }}</strong></div>
              <div class="detail-item"><span>Transmission</span><strong>{{ selectedVehicle.transmission || 'N/A' }}</strong></div>
            </div>
          </div>

          <div class="detail-section">
            <h3>Booking Details</h3>
            <div class="detail-rows">
              <div class="detail-row"><span>Shop</span><strong>{{ selectedBooking?.shop_name || 'N/A' }}</strong></div>
              <div class="detail-row"><span>Rental Date</span><strong>{{ formatDate(selectedBooking?.start_date) }} - {{ formatDate(selectedBooking?.end_date) }}</strong></div>
              <div class="detail-row"><span>Duration</span><strong>{{ detailDays }} day(s)</strong></div>
            </div>
          </div>

          <div class="detail-section">
            <h3>Payment Summary</h3>
            <div class="detail-rows">
              <div class="detail-row"><span>Price per day</span><strong>{{ formatCurrency(detailPricePerDay) }}</strong></div>
              <div class="detail-row"><span>Days</span><strong>x {{ detailDays }}</strong></div>
            </div>
            <div class="detail-total-row">
              <span>Total Amount</span>
              <strong>{{ formatCurrency(detailTotalAmount) }}</strong>
            </div>
          </div>

          <p v-if="selectedVehicle.description" class="detail-description">{{ selectedVehicle.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bookings-page {
  min-height: 100vh;
  background: #ffffff;
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px 40px 24px;
  color: #102f56;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.bookings-panel {
  background: #f7f9fc;
  border: 1px solid #d3dce8;
  border-radius: 14px;
  padding: 16px 16px 14px;
}

.panel-head h1 {
  margin: 0;
  font-size: 2.2rem;
  line-height: 1.08;
  color: #22232b;
  letter-spacing: -0.02em;
}

.panel-head p {
  margin: 8px 0 0;
  font-size: 0.92rem;
  color: #385f89;
}

.controls-row {
  margin-top: 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
}

.tabs-row {
  flex: 1 1 auto;
  min-width: 0;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.tab-btn {
  border: none;
  border-radius: 16px;
  background: #dfe4ea;
  color: #3f638d;
  padding: 10px 20px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
}

.tab-btn.active {
  background: #39a9e1;
  color: #fff;
  box-shadow: 0 8px 22px rgba(57, 169, 225, 0.35);
}

.search-row {
  display: flex;
  align-items: center;
  gap: 0;
  width: min(340px, 100%);
  max-width: 100%;
  margin-left: auto;
  flex: 0 0 auto;
  height: 42px;
  border-radius: 999px;
  border: 2px solid #0b86c9;
  background: #ffffff;
  overflow: hidden;
}

.search-input {
  flex: 1 1 auto;
  min-width: 0;
  border: none;
  background: #ffffff;
  color: #102f56;
  border-radius: 0;
  padding: 5px 12px;
  font-size: 0.8rem;
  outline: none;
}

.search-input:focus {
  box-shadow: none;
}

.search-btn {
  border: none;
  background: #f7fbff;
  border-left: 2px solid #0b86c9;
  color: #2f3a48;
  width: 38px;
  flex: 0 0 38px;
  align-self: center;
  height: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  line-height: 0;
}

.search-icon-svg {
  width: 15px;
  height: 15px;
  display: block;
  fill: none;
  stroke: currentColor;
  stroke-width: 2.2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.search-row:focus-within {
  box-shadow: 0 0 0 4px rgba(11, 134, 201, 0.22);
}

.booking-list-wrap {
  margin-top: 12px;
  background: #f7f9fc;
  border: 1px solid #d3dce8;
  border-radius: 14px;
  padding: 12px;
  display: grid;
  gap: 10px;
}

.empty-state {
  border: 1px dashed #c9d3e0;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  color: #5d7698;
  background: #eef2f7;
}

.booking-card {
  display: grid;
  grid-template-columns: 210px 1fr auto;
  gap: 12px;
  background: #eef2f7;
  border: 1px solid #cfd8e4;
  border-radius: 14px;
  padding: 12px;
}

.booking-image {
  width: 210px;
  height: 128px;
  border-radius: 10px;
  overflow: hidden;
}

.vehicle-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.booking-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
  font-size: 0.92rem;
  color: #4a6990;
}

.booking-info p {
  margin: 0;
}

.booking-header {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.vehicle-name {
  margin: 0;
  font-size: 1.55rem;
  line-height: 1.1;
  color: #0f2f5c;
  font-weight: 800;
}

.status-pill {
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 0.82rem;
  font-weight: 700;
}

.status-pending {
  background: #f5dfbf;
  color: #a25f15;
}

.status-confirmed {
  background: #ccdaf2;
  color: #1c4fcc;
}

.status-completed {
  background: #d3f4df;
  color: #0f8c4c;
}

.status-cancelled {
  background: #f4cfd4;
  color: #b11f38;
}

.meta-label {
  color: #3f618a;
  font-weight: 500;
}

.action-row {
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.action-btn {
  align-self: flex-start;
  border: none;
  border-radius: 16px;
  color: #fff;
  font-size: 0.88rem;
  font-weight: 800;
  line-height: 1;
  padding: 8px 16px;
  cursor: pointer;
  transition: filter 0.15s ease;
}

.action-btn:hover {
  filter: brightness(0.96);
}

.btn-accept {
  background: #22c55e;
}

.btn-reject {
  background: #ef4444;
}

.btn-complete {
  background: #f59e0b;
}

.booking-price {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
}

.price-value {
  background: #f6d9dc;
  color: #d42324;
  border-radius: 10px;
  padding: 6px 10px;
  font-size: 1.35rem;
  line-height: 1;
  font-weight: 800;
}

.detail-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.58);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 16px;
  z-index: 999;
  overflow-y: auto;
}

.detail-modal {
  width: min(640px, 100%);
  max-height: none;
  overflow: visible;
  background: #f2f4f8;
  border-radius: 14px;
  border: 1px solid #d7e0ec;
  padding: 14px;
  position: relative;
  margin: 8px 0;
}

.detail-close {
  position: absolute;
  top: 12px;
  right: 12px;
  border: none;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 999px;
  width: 32px;
  height: 32px;
  cursor: pointer;
  color: #1d2f49;
  line-height: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  font-weight: 600;
  transition: background-color 0.2s ease, transform 0.12s ease, box-shadow 0.2s ease;
}

.detail-close:hover {
  background: #2f6eea;
  color: #ffffff;
  box-shadow: 0 8px 18px rgba(47, 110, 234, 0.35);
  transform: translateY(-1px);
}

.detail-close:active {
  background: #1f55c2;
  color: #ffffff;
  transform: scale(0.96);
}

.detail-close:focus-visible {
  outline: 2px solid #2f6eea;
  outline-offset: 2px;
}

.detail-close svg {
  width: 16px;
  height: 16px;
  stroke: currentColor;
  stroke-width: 2.4;
  stroke-linecap: round;
  display: block;
}

.detail-state {
  padding: 18px;
  border-radius: 12px;
  background: #f2f6fb;
  color: #405f85;
}

.detail-error {
  background: #fdecec;
  color: #b4232f;
}

.detail-content {
  display: grid;
  gap: 8px;
  padding: 2px 4px 4px;
}

.detail-image-wrap {
  width: 100%;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #d3dce8;
}

.detail-image {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
}

.detail-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.detail-title-row h2 {
  margin: 0;
  color: #0d2a56;
  font-size: 1.2rem;
  line-height: 1.08;
}

.detail-booking-code {
  margin: 6px 0 0;
  color: #5d7698;
  font-size: 0.75rem;
}

.detail-status-badge {
  border-radius: 999px;
  padding: 5px 10px;
  font-size: 0.76rem;
  font-weight: 700;
}

.detail-status-badge.status-pending {
  background: #f5dfbf;
  color: #9a5b15;
}

.detail-status-badge.status-confirmed {
  background: #cfeedd;
  color: #0f7e4d;
}

.detail-status-badge.status-completed {
  background: #d6f5e3;
  color: #117b50;
}

.detail-status-badge.status-cancelled {
  background: #f4d6da;
  color: #b11f38;
}

.detail-section {
  border: 1px solid #d3dce8;
  border-radius: 12px;
  background: #f5f7fb;
  padding: 6px;
}

.detail-section h3 {
  margin: 0 0 5px;
  font-size: 0.85rem;
  color: #09254c;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 5px;
}

.detail-item {
  border: 1px solid #d4ddea;
  background: #f1f4f9;
  border-radius: 8px;
  padding: 7px;
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.detail-item span {
  font-size: 0.74rem;
  color: #5f7898;
}

.detail-item strong {
  font-size: 1.05rem;
  color: #0f2850;
  text-align: right;
}

.detail-rows {
  display: grid;
  gap: 6px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #d7dfeb;
  padding: 5px 2px;
  gap: 10px;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row span {
  color: #5b7596;
  font-size: 0.82rem;
}

.detail-row strong {
  color: #0d2851;
  font-size: 0.9rem;
}

.detail-total-row {
  margin-top: 8px;
  border-top: 2px solid #d4dce8;
  padding-top: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.detail-total-row span {
  color: #0f2d57;
  font-size: 1rem;
  font-weight: 700;
}

.detail-total-row strong {
  color: #0f7e7b;
  font-size: 1.25rem;
  line-height: 1;
  font-weight: 800;
}

.detail-description {
  margin: 0;
  border: 1px solid #dae3ee;
  border-radius: 10px;
  padding: 10px;
  color: #2f4f77;
  background: #f9fbfd;
}

@media (max-width: 1200px) {
  .panel-head h1 {
    font-size: 2rem;
  }

  .panel-head p,
  .tab-btn,
  .booking-info,
  .status-pill,
  .details-btn {
    font-size: 0.95rem;
  }

  .vehicle-name {
    font-size: 1.35rem;
  }

  .price-value {
    font-size: 1.2rem;
  }
}

@media (max-width: 900px) {
  .bookings-page {
    max-width: 100%;
    padding: 14px 18px 18px;
  }

  .booking-card {
    grid-template-columns: 1fr;
  }

  .booking-image {
    width: 100%;
    height: 210px;
  }

  .booking-price {
    justify-content: flex-start;
  }

  .controls-row {
    align-items: stretch;
    gap: 10px;
  }

  .tabs-row {
    overflow-x: auto;
    flex-wrap: nowrap;
  }

  .tab-btn {
    flex-shrink: 0;
  }

  .search-row {
    justify-content: flex-start;
    width: 100%;
  }

  .search-input {
    width: 100%;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .detail-title-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .detail-title-row h2 {
    font-size: 1.7rem;
  }

  .detail-item strong {
    font-size: 1.35rem;
  }

  .detail-total-row span {
    font-size: 1.3rem;
  }

  .detail-total-row strong {
    font-size: 1.65rem;
  }
}
</style>
