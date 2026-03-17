<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import '../../css/Payment.css'

const filter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const loading = ref(true)
const error = ref(null)

const normalizeStatus = (status) => {
  const raw = (status || '').toString().trim().toLowerCase()
  if (!raw) return 'pending'
  const cleaned = raw.replace(/[\s-]+/g, '_')
  const aliases = {
    canceled: 'cancelled',
    cancel: 'cancelled',
    cencel: 'cancelled',
    comfirmed: 'confirmed',
    process: 'processing',
    processing: 'processing',
    in_process: 'processing',
    in_progress: 'processing',
    success: 'paid',
    successful: 'paid',
    succeeded: 'paid',
    complete: 'completed',
    completed: 'completed',
    confirm: 'confirmed',
    confirmed: 'confirmed',
    paid: 'paid',
    pending_payment: 'pending'
  }
  return aliases[cleaned] || cleaned
}

const toIsoDate = (value) => {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toISOString().split('T')[0]
}

const toStatusLabel = (statusKey, rawStatus) => {
  const labels = {
    pending: 'Pending',
    paid: 'Paid',
    confirmed: 'Confirmed',
    completed: 'Completed',
    processing: 'Processing',
    cancelled: 'Cancelled',
    failed: 'Failed',
    refunded: 'Refunded'
  }
  if (labels[statusKey]) return labels[statusKey]
  const fallback = (rawStatus || '').toString().trim()
  if (!fallback) return 'Pending'
  return fallback
    .replace(/[_-]+/g, ' ')
    .replace(/\b\w/g, (match) => match.toUpperCase())
}

const mapPaymentToRow = (payment) => {
  const bookingValue = payment?.booking_id || payment?.bookingId || payment?.booking?.id
  const bookingId = bookingValue ? `BK${bookingValue}` : '-'
  const paymentId = payment?.transaction_id || payment?.payment_id || (payment?.id ? `PAY-${payment.id}` : '-')
  const amountValue = Number(payment?.amount ?? payment?.total_price ?? 0)
  const rawStatus =
    payment?.raw_status ??
    payment?.booking_status ??
    payment?.booking?.status ??
    payment?.status ??
    payment?.payment_status
  const rawStatusText = (rawStatus ?? '').toString().trim()
  const statusKey = normalizeStatus(rawStatusText || 'pending')
  const status = toStatusLabel(statusKey, rawStatusText || 'pending')
  const date = toIsoDate(payment?.paid_at || payment?.created_at)

  return {
    id: paymentId,
    bookingId,
    amount: Number.isFinite(amountValue) ? amountValue : 0,
    date,
    status,
    rawStatus: rawStatusText || status,
    statusKey
  }
}

// Fetch payments and map to payments UI
const fetchPayments = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await api.get('/shop-payments')
    const payload = response?.data
    const data = Array.isArray(payload)
      ? payload
      : Array.isArray(payload?.data)
        ? payload.data
        : Array.isArray(payload?.data?.data)
          ? payload.data.data
          : []

    payments.value = data.map(mapPaymentToRow)
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

const isPaidStatus = (status) => {
  const normalized = normalizeStatus(status)
  return normalized === 'paid' || normalized === 'confirmed' || normalized === 'completed'
}

// Computed stats
const totalEarnings = computed(() => {
  return payments.value
    .filter(p => isPaidStatus(p.statusKey))
    .reduce((a, b) => a + b.amount, 0) || 0
})

const monthlyIncome = computed(() => {
  const now = new Date()
  const firstDayOfMonth = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0]
  return payments.value
    .filter(p => isPaidStatus(p.statusKey) && p.date >= firstDayOfMonth)
    .reduce((a, b) => a + b.amount, 0) || 0
})

const todayEarnings = computed(() => {
  const today = new Date().toISOString().split('T')[0]
  return payments.value
    .filter(p => isPaidStatus(p.statusKey) && p.date === today)
    .reduce((a, b) => a + b.amount, 0) || 0
})

const filtered = computed(() => {
  return payments.value.filter(p => {
    if (filter.value !== 'all' && p.statusKey !== filter.value) return false
    if (dateFrom.value && p.date < dateFrom.value) return false
    if (dateTo.value && p.date > dateTo.value) return false
    return true
  })
})

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount || 0)
}

const clearFilters = () => {
  dateFrom.value = ''
  dateTo.value = ''
  filter.value = 'all'
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
        <option value="confirmed">Confirmed</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
      </select>
      <div class="date-filters">
        <input type="date" v-model="dateFrom" class="date-input" placeholder="From Date" />
        <span class="date-separator">to</span>
        <input type="date" v-model="dateTo" class="date-input" placeholder="To Date" />
        <button class="clear-btn" @click="clearFilters">Clear</button>
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
              <span :class="['payments-status-badge', p.statusKey || 'pending']">
                {{ p.rawStatus || p.status || 'Pending' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>


