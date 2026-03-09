<script setup>
import { ref, computed, onMounted } from 'vue'
import { paymentApi } from '@/services/api'

const filter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const loading = ref(true)
const error = ref(null)

// Fetch payments from database
const fetchPayments = async () => {
  try {
    loading.value = true
    const response = await paymentApi.getAll()
    const data = response.data.data || response.data || []
    
    // Map database fields to expected format
    payments.value = data.map(p => ({
      id: p.transaction_id || `PAY-${p.id}`,
      bookingId: p.booking_id,
      amount: parseFloat(p.amount) || 0,
      date: p.created_at ? new Date(p.created_at).toISOString().split('T')[0] : '',
      status: p.payment_status || 'pending',
      paidAt: p.paid_at
    }))
  } catch (err) {
    console.error('Error fetching payments:', err)
    error.value = 'Failed to load payments data'
  } finally {
    loading.value = false
  }
}

// Fetch on mount
onMounted(() => {
  fetchPayments()
})

// Payments data
const payments = ref([])

// Computed stats
const totalEarnings = computed(() => {
  return payments.value
    .filter(p => p.status === 'paid' || p.status === 'Paid')
    .reduce((a, b) => a + b.amount, 0) || 0
})

const monthlyIncome = computed(() => {
  const now = new Date()
  const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
  return payments.value
    .filter(p => (p.status === 'paid' || p.status === 'Paid') && p.date >= firstDayOfMonth)
    .reduce((a, b) => a + b.amount, 0) || 0
})

const todayEarnings = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return payments.value
    .filter(p => (p.status === 'paid' || p.status === 'Paid') && p.date === today)
    .reduce((a, b) => a + b.amount, 0) || 0
})

const filtered = computed(() => {
  return payments.value.filter(p => {
    if (filter.value !== 'all' && p.status.toLowerCase() !== filter.value) return false
    if (dateFrom.value && p.date < dateFrom.value) return false
    if (dateTo.value && p.date > dateTo.value) return false
    return true
  })
})

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
}
</script>

<template>
  <div class="payments-container">
    <!-- Page Title -->
    <h1 class="page-title">Payments</h1>

    <!-- Stat Cards Row -->
    <div class="stats-grid">
      <!-- Total Earnings Card -->
      <div class="stat-card earnings-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Total Earnings</span>
          <span class="stat-number">{{ formatCurrency(totalEarnings) }}</span>
        </div>
      </div>

      <!-- Monthly Income Card -->
      <div class="stat-card monthly-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
            <polyline points="17 6 23 6 23 12"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Monthly Income</span>
          <span class="stat-number">{{ formatCurrency(monthlyIncome) }}</span>
        </div>
      </div>

      <!-- Today's Earnings Card -->
      <div class="stat-card today-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Today's Earnings</span>
          <span class="stat-number">{{ formatCurrency(todayEarnings) }}</span>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <select v-model="filter" class="filter-select">
        <option value="all">All Payments</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
      </select>
      <div class="date-filters">
        <input type="date" v-model="dateFrom" class="date-input" placeholder="From Date" />
        <span class="date-separator">to</span>
        <input type="date" v-model="dateTo" class="date-input" placeholder="To Date" />
        <button class="clear-btn" @click="dateFrom='';dateTo=''">Clear</button>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading payments...</p>
      </div>
      
      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchPayments">Retry</button>
      </div>

      <!-- Data Table -->
      <table v-else class="payments-table">
        <thead>
          <tr>
            <th>PAYMENT ID</th>
            <th>BOOKING</th>
            <th>AMOUNT</th>
            <th>DATE</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filtered.length === 0">
            <td colspan="5" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <line x1="12" y1="1" x2="12" y2="23"></line>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                <p>No payments found</p>
              </div>
            </td>
          </tr>
          <tr v-for="p in filtered" :key="p.id">
            <td class="id-cell">{{ p.id }}</td>
            <td class="booking-cell">#{{ p.bookingId }}</td>
            <td class="amount-cell">${{ p.amount }}</td>
            <td class="date-cell">{{ p.date }}</td>
            <td class="status-cell">
              <span :class="['status-badge', p.status.toLowerCase()]">
                {{ p.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
/* Container */
.payments-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

/* Page Title */
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
  text-align: left;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

/* Stat Cards */
.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Total Earnings Card */
.earnings-card {
  background: #f0fdf4;
  border-color: #bbf7d0;
}

.earnings-card .stat-icon {
  color: #16a34a;
}

.earnings-card .stat-label {
  color: #16a34a;
}

.earnings-card .stat-number {
  color: #15803d;
}

/* Monthly Income Card */
.monthly-card {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.monthly-card .stat-icon {
  color: #2563eb;
}

.monthly-card .stat-label {
  color: #2563eb;
}

.monthly-card .stat-number {
  color: #1d4ed8;
}

/* Today's Earnings Card */
.today-card {
  background: #fff7ed;
  border-color: #fed7aa;
}

.today-card .stat-icon {
  color: #ea580c;
}

.today-card .stat-label {
  color: #ea580c;
}

.today-card .stat-number {
  color: #c2410c;
}

/* Stat Icon */
.stat-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg {
  width: 36px;
  height: 36px;
}

/* Stat Content */
.stat-content {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-label {
  font-size: 0.875rem;
  font-weight: 500;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

/* Filter Bar */
.filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-select {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 0.875rem;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
}

.date-filters {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.date-input {
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #475569;
}

.date-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.date-separator {
  color: #94a3b8;
  font-size: 0.875rem;
}

.clear-btn {
  padding: 10px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #64748b;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.15s;
}

.clear-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

/* Table Container */
.table-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.payments-table {
  width: 100%;
  border-collapse: collapse;
}

.payments-table th,
.payments-table td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.payments-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.payments-table tbody tr {
  transition: background 0.15s;
}

.payments-table tbody tr:hover {
  background: #f8fafc;
}

.payments-table tbody tr:last-child td {
  border-bottom: none;
}

/* ID Cell */
.id-cell {
  font-weight: 600;
  color: #475569;
}

/* Booking Cell */
.booking-cell {
  font-weight: 500;
  color: #1e293b;
}

/* Amount Cell */
.amount-cell {
  font-weight: 600;
  color: #1e293b;
}

/* Date Cell */
.date-cell {
  color: #64748b;
  font-size: 0.875rem;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.paid {
  background: #dcfce7;
  color: #15803d;
}

.status-badge.pending {
  background: #fef3c7;
  color: #b45309;
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
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 16px !important;
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
