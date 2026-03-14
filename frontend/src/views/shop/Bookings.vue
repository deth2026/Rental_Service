<script setup>
import { computed, onMounted, ref } from 'vue'
import { bookingApi } from '@/services/api'
import '../../css/Booking.css'

const activeTab = ref('all')
const searchQuery = ref('')

const bookings = ref([])
const loading = ref(true)
const error = ref('')

const fetchBookings = async () => {
  try {
    loading.value = true
    error.value = ''
    const response = await bookingApi.getAll()
    const data = response.data?.data || response.data || []

    bookings.value = data.map((b) => {
      const vehicle = b.vehicle || null
      const shop = vehicle?.shop || null

      const startDate = b.start_date ? String(b.start_date).split('T')[0] : ''
      const totalDays = Number(b.total_days || 0) || 0
      const end = (() => {
        if (!startDate) return ''
        const start = new Date(startDate)
        if (Number.isNaN(start.getTime())) return ''
        const d = new Date(start.getTime() + Math.max(1, totalDays) * 24 * 60 * 60 * 1000)
        return d.toISOString().split('T')[0]
      })()

      return {
        id: b.id,
        booking_code: b.id ? `BKG-${String(b.id).padStart(6, '0')}` : 'BKG-—',
        vehicle_name: vehicle ? `${vehicle.brand || ''} ${vehicle.model || ''}`.trim() : `Vehicle #${b.vehicle_id}`,
        shop_name: shop?.name || `Shop #${vehicle?.shop_id || '—'}`,
        image: vehicle?.image_url_full || '',
        status: b.status || 'pending',
        start_date: startDate,
        end_date: end,
        total_price: Number(b.total_price || 0) || 0,
      }
    })
  } catch (err) {
    console.error('Error fetching bookings:', err)
    error.value = err?.response?.data?.message || err?.message || 'Failed to load bookings.'
    bookings.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchBookings)

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
      .join(' ')
      .toLowerCase()

    return haystack.includes(q)
  })
})

const getStatusClass = (status) => {
  const map = {
    pending: 'status-pending',
    confirmed: 'status-confirmed',
    completed: 'status-completed',
    cancelled: 'status-cancelled',
  }
  return map[status?.toLowerCase()] || 'status-pending'
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
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
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
  const diff = Math.abs(new Date(end) - new Date(start))
  return Math.ceil(diff / (1000 * 60 * 60 * 24)) || 1
}
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
      <div v-if="loading" class="empty-state">Loading bookings from database...</div>
      <div v-else-if="error" class="empty-state">{{ error }}</div>
      <div v-else-if="filteredBookings.length === 0" class="empty-state">No bookings found for your search.</div>

      <article v-for="booking in filteredBookings" :key="booking.id" class="booking-card">
        <div class="booking-image">
          <img v-if="booking.image" :src="booking.image" :alt="booking.vehicle_name" class="vehicle-img" />
          <div v-else class="vehicle-img placeholder"></div>
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

          <button class="details-btn">View Details</button>
        </div>

        <div class="booking-price">
          <span class="price-value">{{ formatCurrency(booking.total_price) }}</span>
        </div>
      </article>
    </section>
  </div>
</template>


