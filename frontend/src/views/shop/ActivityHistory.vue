<script setup>
import { ref, onMounted } from 'vue'
import { historiesApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)
const histories = ref([])

const fetchHistories = async () => {
  try {
    loading.value = true
    const response = await historiesApi.getAll()
    const data = response.data.data || response.data || []
    histories.value = data
  } catch (err) {
    console.error('Error fetching histories:', err)
    error.value = 'Failed to load activity history'
  } finally {
    loading.value = false
  }
}

onMounted(fetchHistories)

const formatDate = (date) => {
  if (!date) return '—'
  try {
    const d = new Date(date)
    if (isNaN(d.getTime())) return date
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const year = d.getFullYear()
    return `${day}/${month}/${year}`
  } catch {
    return date
  }
}
</script>

<template>
  <div class="history-container">
    <h1 class="page-title">Activity History</h1>

    <div class="table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading activity history...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchHistories">Retry</button>
      </div>

      <!-- Data Table -->
      <table v-else class="history-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>USER ID</th>
            <th>SHOP ID</th>
            <th>BOOKING ID</th>
            <th>RATING</th>
            <th>COMMENT</th>
            <th>CREATED AT</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="histories.length === 0">
            <td colspan="7" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M3 12a9 9 0 1 0 3-6.7"/>
                  <path d="M3 4v5h5"/>
                  <path d="M12 7v5l3 2"/>
                </svg>
                <p>No activity history found</p>
              </div>
            </td>
          </tr>
          <tr v-for="h in histories" :key="h.id">
            <td class="id-cell">{{ h.id }}</td>
            <td class="user-cell">{{ h.user_id }}</td>
            <td class="shop-cell">{{ h.shop_id || '—' }}</td>
            <td class="booking-cell">{{ h.booking_id || '—' }}</td>
            <td class="rating-cell">
              <span v-if="h.rating" class="rating-badge">⭐ {{ h.rating }}</span>
              <span v-else class="no-data">—</span>
            </td>
            <td class="comment-cell">{{ h.comment || '—' }}</td>
            <td class="date-cell">{{ formatDate(h.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.history-container {
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

.history-table {
  width: 100%;
  border-collapse: collapse;
}

.history-table th,
.history-table td {
  padding: 14px 20px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.history-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.history-table tbody tr {
  transition: background 0.15s;
}

.history-table tbody tr:hover {
  background: #f8fafc;
}

.history-table tbody tr:last-child td {
  border-bottom: none;
}

.id-cell {
  font-weight: 600;
  color: #475569;
}

.user-cell,
.shop-cell,
.booking-cell {
  color: #1e293b;
  font-weight: 500;
}

.rating-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  background: #fef3c7;
  color: #92400e;
  border-radius: 20px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.no-data {
  color: #94a3b8;
}

.comment-cell {
  color: #475569;
  max-width: 250px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.date-cell {
  color: #64748b;
  font-size: 0.875rem;
  white-space: nowrap;
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
