<script setup>
import { computed, ref, onMounted } from 'vue'
import '../../css/Booking.css'

const activeTab = ref('all')
const searchQuery = ref('')
const bookings = ref([])
const loading = ref(true)
const error = ref(null)
const processing = ref(false)
const confirmationModal = ref({
  visible: false,
  booking: null,
  action: ''
})

const getStoredToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token') || ''

// Get user's shop info from localStorage or fetch
const userShopId = ref(null)

const fetchUserShop = async () => {
  try {
    const token = getStoredToken()
    const headers = { Accept: 'application/json' }
    if (token) headers.Authorization = `Bearer ${token}`
    
    // Fetch current user to get their shop
    const response = await fetch('/api/auth/me', { headers })
    if (response.ok) {
      const userData = await response.json()
      // Check if user has a shop_id property or get from shops
      if (userData.shop_id) {
        userShopId.value = userData.shop_id
      }
    }
  } catch (e) {
    console.error('Error fetching user shop:', e)
  }
}

const fetchShopBookings = async () => {
  loading.value = true
  error.value = null
  try {
    const token = getStoredToken()
    console.log('Token available:', !!token)
    
    const headers = { Accept: 'application/json' }
    if (token) headers.Authorization = `Bearer ${token}`
    
    // Use the main bookings endpoint and filter on frontend
    console.log('Fetching from /api/bookings...')
    const response = await fetch('/api/bookings', { headers })
    console.log('Response status:', response.status)
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      const errorMsg = errorData.message || errorData.error || `HTTP error! status: ${response.status}`
      throw new Error(errorMsg)
    }
    
    const data = await response.json()
    console.log('Bookings raw data:', data)
    
    // Get bookings from data.data (pagination) or directly
    const allBookings = Array.isArray(data) ? data : (data.data || [])
    
    // Format the bookings data for display
    bookings.value = allBookings.map(booking => {
      // Get vehicle image - try different fields
      const vehicleImage = booking.vehicle?.image_url_full || booking.vehicle?.image_url || booking.vehicle?.img_url || ''
      console.log('Vehicle image for booking', booking.id, ':', vehicleImage)
      
      return {
        id: booking.id,
        vehicle_id: booking.vehicle_id,
        vehicle_name: booking.vehicle?.name || booking.vehicle?.brand + ' ' + booking.vehicle?.model || 'N/A',
        booking_code: 'BK-' + (booking.created_at ? new Date(booking.created_at).toISOString().slice(0,10).replace(/-/g,'') : '') + '-' + String(booking.id).padStart(4, '0'),
        shop_name: booking.shop?.name || 'N/A',
        shop_image: booking.shop?.img_url_full || booking.shop?.img_url || '',
        start_date: booking.start_date,
        end_date: booking.start_date ? new Date(new Date(booking.start_date).getTime() + (booking.total_days - 1) * 24 * 60 * 60 * 1000).toISOString().slice(0, 10) : null,
        total_price: booking.total_price,
        status: booking.status,
        image: vehicleImage,
        total_days: booking.total_days,
        daily_rate: booking.daily_rate,
        customer_name: booking.user?.name || 'N/A',
        customer_email: booking.user?.email || 'N/A',
        customer_phone: booking.user?.phone || 'N/A',
      }
    })
    
    console.log('Formatted bookings:', bookings.value)
  } catch (e) {
    error.value = e.message || 'Unable to load bookings'
    console.error('Error fetching shop bookings:', e)
  } finally {
    loading.value = false
  }
}

const updateBookingStatus = async (bookingId, newStatus) => {
  processing.value = true
  try {
    const token = getStoredToken()
    const headers = { 
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
    if (token) headers.Authorization = `Bearer ${token}`
    
    const response = await fetch(`/api/bookings/${bookingId}`, {
      method: 'PUT',
      headers,
      body: JSON.stringify({ status: newStatus })
    })
    
    if (!response.ok) {
      throw new Error('Failed to update booking')
    }
    
    // Refresh bookings after update
    await fetchShopBookings()
    return true
  } catch (e) {
    error.value = e.message || 'Failed to update booking'
    console.error('Error updating booking:', e)
    return false
  } finally {
    processing.value = false
  }
}

const openBookingConfirmation = (booking, action) => {
  confirmationModal.value = {
    visible: true,
    booking,
    action
  }
}

const closeBookingConfirmation = () => {
  confirmationModal.value = {
    visible: false,
    booking: null,
    action: ''
  }
}

const confirmBookingAction = async () => {
  const booking = confirmationModal.value.booking
  if (!booking) return

  const targetStatus = confirmationModal.value.action === 'accept' ? 'confirmed' : 'cancelled'
  const success = await updateBookingStatus(booking.id, targetStatus)

  if (success) {
    closeBookingConfirmation()
  }
}

onMounted(() => {
  fetchShopBookings()
})

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
      b.customer_name,
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
  if (Number.isNaN(diff)) return 0
  return Math.ceil(diff / (1000 * 60 * 60 * 24)) || 1
}
</script>

<template>
  <div class="bookings-page">
    <section class="bookings-panel">
      <div class="panel-head">
        <h1>Shop Bookings</h1>
        <p>View and manage customer bookings for your shop.</p>
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
            placeholder="Search booking, customer, vehicle, or status..."
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
      <div v-if="loading" class="empty-state">Loading bookings...</div>
      <div v-else-if="error" class="empty-state">{{ error }}</div>
      <div v-else-if="filteredBookings.length === 0" class="empty-state">No bookings found for your search.</div>

      <article v-for="booking in filteredBookings" :key="booking.id" class="booking-card">
        <div class="booking-image">
          <img :src="booking.image" :alt="booking.vehicle_name" class="vehicle-img" />
        </div>

        <div class="booking-info">
          <div class="booking-header">
            <h3 class="vehicle-name">{{ booking.vehicle_name }}</h3>
            <span :class="['status-pill', getStatusClass(booking.status)]">{{ getStatusLabel(booking.status) }}</span>
          </div>

          <p><span class="meta-label">Booking ID:</span> {{ booking.booking_code || 'N/A' }}</p>
          <p><span class="meta-label">Customer:</span> {{ booking.customer_name || 'N/A' }}</p>
          <p><span class="meta-label">Contact:</span> {{ booking.customer_phone || booking.customer_email || 'N/A' }}</p>
          <p>
            <span class="meta-label">Date:</span>
            {{ formatDate(booking.start_date) }} to {{ formatDate(booking.end_date) }}
            ({{ getTotalDays(booking.start_date, booking.end_date) }} days)
          </p>

          <div class="action-buttons" v-if="booking.status === 'pending'">
            <button
              type="button"
              class="accept-btn"
              @click="openBookingConfirmation(booking, 'accept')"
              :disabled="processing"
            >
              Accept
            </button>
            <button
              type="button"
              class="reject-btn"
              @click="openBookingConfirmation(booking, 'reject')"
              :disabled="processing"
            >
              Reject
            </button>
          </div>
          <button v-else class="details-btn">View Details</button>
        </div>

        <div class="booking-price">
          <span class="price-value">{{ formatCurrency(booking.total_price) }}</span>
        </div>
      </article>
    </section>
    <div
      v-if="confirmationModal.visible"
      class="confirm-modal-backdrop"
      role="dialog"
      aria-modal="true"
      @click.self="closeBookingConfirmation"
    >
      <div class="confirm-modal-card">
        <div class="confirm-modal-header">
          <h2>
            {{ confirmationModal.action === 'accept' ? 'Confirm acceptance' : 'Confirm rejection' }}
          </h2>
        </div>
        <div class="confirm-modal-body">
          <p>
            You are about to
            <strong>{{ confirmationModal.action === 'accept' ? 'accept' : 'reject' }}</strong>
            the reservation for
            <strong>{{ confirmationModal.booking?.vehicle_name || 'the vehicle' }}</strong>
            booked by
            <strong>{{ confirmationModal.booking?.customer_name || 'the customer' }}</strong>.
          </p>
          <p class="confirm-modal-period">
            {{ formatDate(confirmationModal.booking?.start_date) }} to
            {{ formatDate(confirmationModal.booking?.end_date) }}
            ({{ getTotalDays(confirmationModal.booking?.start_date, confirmationModal.booking?.end_date) }} days)
          </p>
          <p class="confirm-modal-detail">
            <span>Total:</span>
            {{ formatCurrency(confirmationModal.booking?.total_price || 0) }}
          </p>
        </div>
        <div class="confirm-modal-actions">
          <button type="button" class="confirm-cancel-btn" @click="closeBookingConfirmation">
            Cancel
          </button>
          <button
            type="button"
            class="confirm-submit-btn"
            :class="{ reject: confirmationModal.action === 'reject' }"
            :disabled="processing"
            @click="confirmBookingAction"
          >
            {{ processing
              ? 'Processing...'
              : confirmationModal.action === 'accept'
                ? 'Yes, accept the booking'
                : 'Yes, reject the booking' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>


