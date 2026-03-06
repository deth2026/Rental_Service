<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '../../services/database.js'
import '../../assets/HomeView.css'

const router = useRouter()

const bookings = ref([])
const isLoading = ref(true)
const error = ref('')
const activeTab = ref('all')

const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')
const userInitials = computed(() => {
  const words = userDisplayName.value.trim().split(/\s+/).filter(Boolean)
  if (words.length === 0) return 'GU'
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase()
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase()
})

const filteredBookings = computed(() => {
  if (activeTab.value === 'all') return bookings.value
  return bookings.value.filter(b => b.status === activeTab.value)
})

const placeholderImage = 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=400&q=80'

const fetchBookings = async () => {
  isLoading.value = true
  error.value = ''
  
  try {
    const token = localStorage.getItem('auth_token')
    const response = await fetch('/api/my-bookings', {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    })
    
    if (!response.ok) {
      throw new Error('Failed to load bookings')
    }
    
    const data = await response.json()
    bookings.value = Array.isArray(data) ? data : (data.data || [])
  } catch (err) {
    error.value = err.message || 'Failed to load bookings'
    console.error('Error fetching bookings:', err)
  } finally {
    isLoading.value = false
  }
}

const getStatusClass = (status) => {
  const statusMap = {
    'pending': 'status-pending',
    'confirmed': 'status-confirmed',
    'completed': 'status-completed',
    'cancelled': 'status-cancelled',
    'active': 'status-confirmed'
  }
  return statusMap[status?.toLowerCase()] || 'status-pending'
}

const getStatusLabel = (status) => {
  const labels = {
    'pending': 'Pending',
    'confirmed': 'Confirmed',
    'completed': 'Completed',
    'cancelled': 'Cancelled',
    'active': 'Active'
  }
  return labels[status?.toLowerCase()] || 'Pending'
}

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}

const formatCurrency = (value) => {
  const amount = Number(value || 0)
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

const getVehicleImage = (booking) => {
  return booking.vehicle_image || booking.image || placeholderImage
}

const getVehicleName = (booking) => {
  return booking.vehicle_name || booking.vehicle?.name || 'Unknown Vehicle'
}

const getShopName = (booking) => {
  return booking.shop_name || booking.shop?.name || 'Shop Rental'
}

const getTotalDays = (start, end) => {
  if (!start || !end) return 0
  const startDate = new Date(start)
  const endDate = new Date(end)
  const diffTime = Math.abs(endDate - startDate)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) || 1
}

const handleLogout = async () => {
  await userService.logout()
  router.push('/login')
}

const goToHome = () => {
  router.push('/view_shop')
}

onMounted(() => {
  fetchBookings()
})
</script>

<template>
  <div class="home-page">
    <!-- Navigation Bar - HomeView Style -->
    <header class="top-nav">
      <div class="brand" @click="goToHome" style="cursor: pointer;">
        GoRent
      </div>

      <div class="nav-auth">
        <a href="#" @click.prevent="goToHome" class="link-login">Home</a>
        <a href="#" class="link-login active">My Bookings</a>
        <a href="#" class="link-login">Support</a>
        <div class="user-info">
          <span class="user-name">{{ userDisplayName }}</span>
          <div class="user-avatar">{{ userInitials }}</div>
        </div>
        <button class="btn-signup" @click="handleLogout">Logout</button>
      </div>
    </header>

    <!-- Main Content -->
    <section class="section bookings-section">
      <div class="section-head">
        <div>
          <h2>My Bookings</h2>
          <p>View and manage your vehicle rentals.</p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="tabs-container">
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'all' }"
          @click="activeTab = 'all'"
        >
          All Bookings
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'pending' }"
          @click="activeTab = 'pending'"
        >
          Pending
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'confirmed' }"
          @click="activeTab = 'confirmed'"
        >
          Confirmed
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'completed' }"
          @click="activeTab = 'completed'"
        >
          Completed
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'cancelled' }"
          @click="activeTab = 'cancelled'"
        >
          Cancelled
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="status-box">
        <div class="loading-spinner"></div>
        <p>Loading your bookings...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="status-box error">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchBookings">Try Again</button>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredBookings.length === 0" class="status-box">
        <div class="empty-icon">📋</div>
        <h3>No bookings found</h3>
        <p v-if="activeTab !== 'all'">You don't have any {{ activeTab }} bookings.</p>
        <p v-else>You haven't made any bookings yet.</p>
        <button class="view-shop-btn" @click="goToHome">Browse Shops</button>
      </div>

      <!-- Bookings List -->
      <div v-else class="booking-list">
        <article 
          v-for="booking in filteredBookings" 
          :key="booking.id" 
          class="booking-card"
        >
          <!-- Vehicle Image -->
          <div class="booking-image">
            <img 
              :src="getVehicleImage(booking)" 
              :alt="getVehicleName(booking)"
              class="vehicle-img"
            />
          </div>

          <!-- Booking Info -->
          <div class="booking-info">
            <div class="booking-header">
              <h3 class="vehicle-name">{{ getVehicleName(booking) }}</h3>
              <span :class="['status-pill', getStatusClass(booking.status)]">
                {{ getStatusLabel(booking.status) }}
              </span>
            </div>

            <div class="booking-meta">
              <div class="meta-item">
                <span class="meta-label">Booking ID</span>
                <span class="meta-value">{{ booking.booking_code || `#${booking.id}` }}</span>
              </div>
              <div class="meta-item">
                <span class="meta-label">Shop</span>
                <span class="meta-value">{{ getShopName(booking) }}</span>
              </div>
            </div>

            <div class="booking-dates">
              <div class="date-item">
                <span class="date-label">Pickup Date</span>
                <span class="date-value">{{ formatDate(booking.start_date) }}</span>
              </div>
              <div class="date-separator">→</div>
              <div class="date-item">
                <span class="date-label">Return Date</span>
                <span class="date-value">{{ formatDate(booking.end_date) }}</span>
              </div>
              <div class="date-badge">
                {{ getTotalDays(booking.start_date, booking.end_date) }} days
              </div>
            </div>

            <div class="booking-footer">
              <button class="details-btn">View Details</button>
            </div>
          </div>

          <!-- Price -->
          <div class="booking-price">
            <span class="price-label">Total</span>
            <span class="price-value">{{ formatCurrency(booking.total_price) }}</span>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Override HomeView.css variables for this page */
.home-page {
  --accent: #39A9E1;
  background: #f2f4f7;
  color: #0f172a;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
}

/* Section styling */
.bookings-section {
  width: min(1160px, 100%);
  margin: 0 auto;
  padding: 40px 20px;
}

.section-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 24px;
}

.section-head h2 {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.section-head p {
  color: #64748b;
  margin: 0;
}

/* User info in nav */
.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-right: 8px;
}

.user-name {
  font-weight: 500;
  color: #0f172a;
  font-size: 0.95rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--accent);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.85rem;
}

/* Active nav link */
.nav-auth .link-login.active {
  color: var(--accent);
  font-weight: 600;
}

/* Tabs */
.tabs-container {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.tab-btn {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  background: white;
  color: #64748b;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.tab-btn:hover {
  background: #f8fafc;
  color: var(--accent);
}

.tab-btn.active {
  background: var(--accent);
  color: white;
  box-shadow: 0 4px 12px rgba(57, 169, 225, 0.3);
}

/* Status Box (Loading, Error, Empty) */
.status-box {
  background: white;
  border-radius: 16px;
  padding: 60px 24px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.status-box.error {
  background: #fef2f2;
  color: #dc2626;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: var(--accent);
  border-radius: 50%;
  margin: 0 auto 16px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.status-box h3 {
  font-size: 1.5rem;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.status-box p {
  color: #64748b;
  margin: 0 0 24px 0;
}

.retry-btn,
.view-shop-btn {
  padding: 12px 28px;
  border-radius: 12px;
  border: none;
  background: var(--accent);
  color: white;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.retry-btn:hover,
.view-shop-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(57, 169, 225, 0.3);
}

/* Booking List */
.booking-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.booking-card {
  display: grid;
  grid-template-columns: 220px 1fr auto;
  gap: 20px;
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
}

.booking-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.booking-image {
  width: 220px;
  height: 140px;
  border-radius: 12px;
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
  gap: 12px;
}

.booking-header {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.vehicle-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.status-pill {
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
}

.status-confirmed {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-completed {
  background: #d1fae5;
  color: #047857;
}

.status-cancelled {
  background: #fee2e2;
  color: #b91c1c;
}

.booking-meta {
  display: flex;
  gap: 24px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.meta-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.meta-value {
  font-size: 0.95rem;
  color: #0f172a;
  font-weight: 500;
}

.booking-dates {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.date-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.date-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.date-value {
  font-size: 0.95rem;
  color: #0f172a;
  font-weight: 500;
}

.date-separator {
  color: #64748b;
  font-size: 1.2rem;
}

.date-badge {
  background: #e0f2fe;
  color: var(--accent);
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
}

.booking-footer {
  margin-top: auto;
}

.details-btn {
  padding: 10px 20px;
  border-radius: 10px;
  border: none;
  background: #f1f5f9;
  color: #0f172a;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.details-btn:hover {
  background: var(--accent);
  color: white;
}

/* Price Section */
.booking-price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
  gap: 4px;
}

.price-label {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

.price-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--accent);
}

/* Responsive */
@media (max-width: 900px) {
  .top-nav {
    padding: 0 20px;
  }

  .brand {
    font-size: 1.5rem;
  }

  .nav-auth {
    gap: 12px;
  }

  .user-name {
    display: none;
  }

  .booking-card {
    grid-template-columns: 1fr;
  }

  .booking-image {
    width: 100%;
    height: 180px;
  }

  .booking-price {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
  }
}

@media (max-width: 600px) {
  .tabs-container {
    overflow-x: auto;
    flex-wrap: nowrap;
    padding-bottom: 8px;
  }

  .tab-btn {
    flex-shrink: 0;
  }

  .nav-auth .link-login:not(.active) {
    display: none;
  }

  .booking-meta {
    flex-direction: column;
    gap: 12px;
  }

  .booking-dates {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .date-separator {
    display: none;
  }
}
</style>
