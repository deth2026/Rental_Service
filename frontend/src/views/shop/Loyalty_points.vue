<script setup>
import { ref, onMounted } from 'vue'
import { loyaltyPointApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)
const loyaltyPoints = ref([])

const fetchLoyaltyPoints = async () => {
  try {
    loading.value = true
    const response = await loyaltyPointApi.getAll()
    const data = response.data.data || response.data || []
    loyaltyPoints.value = data
  } catch (err) {
    console.error('Error fetching loyalty points:', err)
    error.value = 'Failed to load loyalty points'
  } finally {
    loading.value = false
  }
}

onMounted(fetchLoyaltyPoints)

const getStatusClass = (status) => {
  if (status === null || status === undefined) return 'status-active'
  const s = String(status).toLowerCase()
  if (s === 'active' || s === '1' || s === 'true') return 'status-active'
  return 'status-inactive'
}

const getStatusLabel = (status) => {
  if (status === null || status === undefined) return 'Active'
  const s = String(status).toLowerCase()
  if (s === 'active' || s === '1' || s === 'true') return 'Active'
  if (s === 'inactive' || s === '0' || s === 'false') return 'Inactive'
  return String(status)
}

const getInitial = (lp) => {
  if (lp.user?.name) return lp.user.name.charAt(0).toUpperCase()
  if (lp.user_id) return String(lp.user_id).charAt(0)
  return 'U'
}

const getCustomerName = (lp) => {
  if (lp.user?.name) return lp.user.name
  return `Customer #${lp.user_id}`
}
</script>

<template>
  <div class="loyalty-container">
    <h1 class="page-title">Loyalty Points</h1>

    <div class="table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading loyalty points...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchLoyaltyPoints">Retry</button>
      </div>

      <!-- Data Table -->
      <table v-else class="loyalty-table">
        <thead>
          <tr>
            <th>CUSTOMER</th>
            <th>TOTAL POINTS 🎁</th>
            <th>BOOKINGS</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loyaltyPoints.length === 0">
            <td colspan="4" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="9" width="18" height="12" rx="2"/>
                  <path d="M12 9v12M3 13h18"/>
                  <path d="M12 9s-3.5-1.5-4.5-3A2.2 2.2 0 0 1 11 4.2c.7 1 .9 2.3 1 4.8zM12 9s3.5-1.5 4.5-3A2.2 2.2 0 0 0 13 4.2c-.7 1-.9 2.3-1 4.8z"/>
                </svg>
                <p>No loyalty points data found</p>
              </div>
            </td>
          </tr>
          <tr v-for="lp in loyaltyPoints" :key="lp.id">
            <td class="customer-cell">
              <div class="customer-info">
                <div class="customer-avatar">{{ getInitial(lp) }}</div>
                <span class="customer-name">{{ getCustomerName(lp) }}</span>
              </div>
            </td>
            <td class="points-cell">
              <span class="points-badge">{{ lp.points ?? lp.total_points ?? 0 }}</span>
            </td>
            <td class="bookings-cell">{{ lp.bookings_count ?? lp.total_bookings ?? '—' }}</td>
            <td>
              <span :class="['status-badge', getStatusClass(lp.status)]">
                {{ getStatusLabel(lp.status) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.loyalty-container {
  padding: 20px;
  max-width: 1400px;
  font-family: 'Inter', sans-serif;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 24px;
}

.table-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.loyalty-table {
  width: 100%;
  border-collapse: collapse;
}

.loyalty-table th,
.loyalty-table td {
  padding: 16px 24px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.loyalty-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.loyalty-table tbody tr {
  transition: background 0.15s;
}

.loyalty-table tbody tr:hover {
  background: #f8fafc;
}

.loyalty-table tbody tr:last-child td {
  border-bottom: none;
}

/* Customer cell */
.customer-cell {
  min-width: 200px;
}

.customer-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.customer-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #2f6bff;
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.customer-name {
  font-weight: 500;
  color: #1e293b;
}

/* Points cell */
.points-cell {
  font-weight: 600;
}

.points-badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 14px;
  background: #f0fdf4;
  color: #15803d;
  border-radius: 20px;
  font-weight: 700;
  font-size: 0.9375rem;
}

/* Bookings cell */
.bookings-cell {
  color: #475569;
  font-weight: 500;
}

/* Status badge */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-active {
  background: #dcfce7;
  color: #15803d;
}

.status-inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #dc2626;
}

.retry-btn {
  margin-top: 12px;
  padding: 10px 20px;
  border: 1px solid #dc2626;
  border-radius: 8px;
  background: #fff;
  color: #dc2626;
  font-size: 0.875rem;
  cursor: pointer;
  transition: background 0.15s;
}

.retry-btn:hover {
  background: #fef2f2;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 16px !important;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #94a3b8;
}

.empty-content svg {
  width: 48px;
  height: 48px;
  opacity: 0.5;
}

.empty-content p {
  margin: 0;
  font-size: 0.9375rem;
}
</style>
