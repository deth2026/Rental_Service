<script setup>
import { ref, computed, onMounted } from 'vue'
import { paymentApi } from '@/services/api'
import '../../css/Payment.css'

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


