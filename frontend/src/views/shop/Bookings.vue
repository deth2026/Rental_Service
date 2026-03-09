<script setup>
import { computed, ref } from 'vue'

const activeTab = ref('all')
const searchQuery = ref('')

const bookings = ref([])

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
      <div v-if="filteredBookings.length === 0" class="empty-state">No bookings found for your search.</div>

      <article v-for="booking in filteredBookings" :key="booking.id" class="booking-card">
        <div class="booking-image">
          <img :src="booking.image" :alt="booking.vehicle_name" class="vehicle-img" />
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

<style scoped>
.bookings-page {
  min-height: 100vh;
  background: #ffffff;
  padding: 8px 10px 8px 24px;
  color: #102f56;
  box-sizing: border-box;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.bookings-panel {
  background: #f7f9fc;
  border: 1px solid #d3dce8;
  border-radius: 14px;
  padding: 12px 12px 10px;
}

.panel-head h1 {
  margin: 0;
  font-size: 1.95rem;
  line-height: 1.08;
  color: #22232b;
  letter-spacing: -0.02em;
}

.panel-head p {
  margin: 8px 0 0;
  font-size: 0.84rem;
  color: #385f89;
}

.controls-row {
  margin-top: 10px;
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
  gap: 8px;
  flex-wrap: wrap;
}

.tab-btn {
  border: none;
  border-radius: 14px;
  background: #dfe4ea;
  color: #3f638d;
  padding: 8px 16px;
  font-size: 0.86rem;
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
  width: min(300px, 100%);
  max-width: 100%;
  margin-left: auto;
  flex: 0 0 auto;
  height: 36px;
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
  padding: 4px 10px;
  font-size: 0.74rem;
  outline: none;
}

.search-btn {
  border: none;
  background: #f7fbff;
  border-left: 2px solid #0b86c9;
  color: #2f3a48;
  width: 34px;
  flex: 0 0 34px;
  height: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  padding: 0;
  line-height: 0;
}

.search-icon-svg {
  width: 13px;
  height: 13px;
  display: block;
  fill: none;
  stroke: currentColor;
  stroke-width: 2.2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.booking-list-wrap {
  margin-top: 10px;
  background: #f7f9fc;
  border: 1px solid #d3dce8;
  border-radius: 14px;
  padding: 10px;
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
  grid-template-columns: 180px 1fr auto;
  gap: 10px;
  background: #eef2f7;
  border: 1px solid #cfd8e4;
  border-radius: 12px;
  padding: 10px;
}

.booking-image {
  width: 180px;
  height: 108px;
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
  gap: 2px;
  font-size: 0.9rem;
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
  font-size: 1.25rem;
  line-height: 1.1;
  color: #0f2f5c;
  font-weight: 800;
}

.status-pill {
  border-radius: 999px;
  padding: 4px 8px;
  font-size: 0.78rem;
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
  font-weight: 600;
}

.details-btn {
  margin-top: 3px;
  align-self: flex-start;
  border: none;
  border-radius: 10px;
  background: #2f6eea;
  color: #fff;
  font-size: 0.84rem;
  font-weight: 700;
  padding: 7px 14px;
  cursor: pointer;
}

.booking-price {
  display: flex;
  align-items: flex-start;
  justify-content: flex-end;
}

.price-value {
  background: #f6d9dc;
  color: #d42324;
  border-radius: 9px;
  padding: 5px 9px;
  font-size: 1.15rem;
  line-height: 1;
  font-weight: 800;
}

@media (max-width: 900px) {
  .bookings-page {
    padding: 8px;
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

  .tabs-row {
    overflow-x: auto;
    flex-wrap: nowrap;
  }

  .tab-btn {
    flex-shrink: 0;
  }

  .search-row {
    width: 100%;
  }
}
</style>
