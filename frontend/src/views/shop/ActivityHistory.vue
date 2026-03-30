<script setup>
import { computed, onMounted, ref, watch, nextTick } from 'vue'
import { bookingApi } from '@/services/api'
import '../../css/ActivityHistory.css'

const loading = ref(true)
const error = ref(null)
const bookings = ref([])
const payments = ref([])

const props = defineProps({
  focus: {
    type: String,
    default: ''
  }
})

const bookingsSection = ref(null)
const returnsSection = ref(null)
const paymentsSection = ref(null)
const ridersSection = ref(null)

const focusSection = computed(() => {
  const keyword = String(props.focus || '').trim().toLowerCase()
  if (!keyword) return null
  if (keyword.includes('payment')) return 'payments'
  if (keyword.includes('return') || keyword.includes('completed')) return 'returns'
  if (keyword.includes('rider') || keyword.includes('customer')) return 'riders'
  if (keyword.includes('booking') || keyword.includes('order')) return 'bookings'
  return 'bookings'
})

const sectionRefs = {
  bookings: bookingsSection,
  returns: returnsSection,
  payments: paymentsSection,
  riders: ridersSection
}

const scrollToSection = (section) => {
  const refTarget = sectionRefs[section]
  if (refTarget?.value && typeof refTarget.value.scrollIntoView === 'function') {
    refTarget.value.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }
}

watch(
  () => focusSection.value,
  (section) => {
    if (!section) return
    nextTick(() => {
      scrollToSection(section)
    })
  },
  { immediate: true }
)

const RETURN_KEYWORDS = ['return', 'complete', 'closed', 'finished']

const recentBookings = computed(() => bookings.value.slice(0, 6))
const returnBookings = computed(() =>
  bookings.value.filter((booking) =>
    isReturnStatus(booking.status)
  )
)
const recentReturns = computed(() => returnBookings.value.slice(0, 5))
const recentPayments = computed(() => payments.value.slice(0, 6))

const recentCustomers = computed(() => {
  const seen = new Map()
  bookings.value.forEach((booking) => {
    const identifier =
      booking.customer_email?.trim() ||
      booking.customer_phone ||
      booking.customer_name ||
      `customer-${booking.user_id || booking.id || Math.random()}`

    if (!identifier) {
      return
    }

    const joinedAt = booking.created_at || booking.start_date
    const existing = seen.get(identifier)
    if (!existing || (joinedAt && new Date(joinedAt) > new Date(existing.joinedAt || 0))) {
      seen.set(identifier, {
        name: booking.customer_name || 'Guest rider',
        email: booking.customer_email || '—',
        phone: booking.customer_phone || '—',
        joinedAt: joinedAt || booking.created_at || ''
      })
    }
  })

  return Array.from(seen.values())
    .sort((a, b) => {
      const aTs = a.joinedAt ? new Date(a.joinedAt).getTime() : 0
      const bTs = b.joinedAt ? new Date(b.joinedAt).getTime() : 0
      return bTs - aTs
    })
    .slice(0, 6)
})

const stats = computed(() => {
  const totalRevenue = payments.value.reduce((sum, payment) => {
    const amount = Number(payment.amount ?? payment?.payment?.amount ?? 0)
    return sum + (Number.isFinite(amount) ? amount : 0)
  }, 0)

  return {
    totalBookings: bookings.value.length,
    totalPayments: payments.value.length,
    totalRevenue,
    returns: returnBookings.value.length,
    newCustomers: recentCustomers.value.length
  }
})

const formatDate = (value) => {
  if (!value) return '—'
  try {
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return String(value)
    const day = String(parsed.getDate()).padStart(2, '0')
    const month = String(parsed.getMonth() + 1).padStart(2, '0')
    const year = parsed.getFullYear()
    return `${day}/${month}/${year}`
  } catch {
    return String(value)
  }
}

const formatCurrency = (value) => {
  const amount = Number(value)
  const normalized = Number.isFinite(amount) ? amount : 0
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 2
  }).format(normalized)
}

const isReturnStatus = (status) => {
  if (!status) return false
  const normalized = (status || '').toLowerCase()
  return RETURN_KEYWORDS.some((keyword) => normalized.includes(keyword))
}

const fetchReportData = async () => {
  loading.value = true
  error.value = null

  const [bookingResult, paymentResult] = await Promise.allSettled([
    bookingApi.getShopBookings({ per_page: 25 }),
    bookingApi.getShopPayments()
  ])

  const errorParts = []

  if (bookingResult.status === 'fulfilled') {
    const payload = bookingResult.value?.data ?? []
    const data = payload?.data || payload || []
    bookings.value = Array.isArray(data) ? data : []
  } else {
    errorParts.push('bookings')
  }

  if (paymentResult.status === 'fulfilled') {
    const payload = paymentResult.value?.data ?? []
    const data = payload?.data || payload || []
    payments.value = Array.isArray(data) ? data : []
  } else {
    errorParts.push('payments')
  }

  if (errorParts.length) {
    error.value = `Unable to load ${errorParts.join(' and ')} at the moment.`
  }

  loading.value = false
}

const refreshReport = () => {
  fetchReportData()
}

onMounted(fetchReportData)
</script>

<template>
  <div class="report-container">
    <header class="report-header">
      <div>
        <p class="eyebrow">Shop owner insights</p>
        <h1 class="report-title">Activity & booking report</h1>
        <p class="report-subtitle">
          See who booked, which vehicles returned, payments processed, and the latest
          riders joining your shop.
        </p>
      </div>
      <button class="refresh-btn" type="button" @click="refreshReport">
        Refresh report
      </button>
    </header>

    <template v-if="loading">
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Loading report...</p>
      </div>
    </template>

    <template v-else-if="error">
      <div class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="refreshReport">Try again</button>
      </div>
    </template>

    <template v-else>
      <section class="summary-grid">
        <article class="summary-card">
          <p class="summary-label">Bookings tracked</p>
          <strong>{{ stats.totalBookings }}</strong>
          <span class="summary-meta">Total reservations stored</span>
        </article>
        <article class="summary-card">
          <p class="summary-label">Vehicle returns</p>
          <strong>{{ stats.returns }}</strong>
          <span class="summary-meta">Orders with return or completion status</span>
        </article>
        <article class="summary-card">
          <p class="summary-label">Payments processed</p>
          <strong>{{ stats.totalPayments }}</strong>
          <span class="summary-meta">{{ formatCurrency(stats.totalRevenue) }} revenue</span>
        </article>
        <article class="summary-card">
          <p class="summary-label">New riders</p>
          <strong>{{ stats.newCustomers }}</strong>
          <span class="summary-meta">Most recent customers from bookings</span>
        </article>
      </section>

      <section
        class="section-card"
        :class="{ 'section-card--highlight': focusSection === 'bookings' }"
        ref="bookingsSection"
      >
        <header class="section-header">
          <div>
            <h2>Recent bookings</h2>
            <p class="section-subtitle">Latest reservations created at your shop</p>
          </div>
          <span class="section-pill">{{ stats.totalBookings }} total entries</span>
        </header>
        <div class="section-content">
          <table class="report-table" v-if="recentBookings.length">
            <thead>
              <tr>
                <th>Booking</th>
                <th>Vehicle</th>
                <th>Customer</th>
                <th>Period</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in recentBookings" :key="booking.id">
                <td>
                  <strong>{{ booking.booking_code || `BK-${booking.id}` }}</strong>
                  <p class="muted-text">#{{ booking.id }}</p>
                </td>
                <td>
                  <p>{{ booking.vehicle_name || 'Vehicle unknown' }}</p>
                  <p class="muted-text">{{ booking.vehicle_custom_name || booking.vehicle_brand || '' }}</p>
                </td>
                <td>
                  <p>{{ booking.customer_name || 'Guest' }}</p>
                  <p class="muted-text">{{ booking.customer_email || '—' }}</p>
                </td>
                <td>
                  <p>{{ formatDate(booking.start_date) }} – {{ formatDate(booking.end_date) }}</p>
                </td>
                <td>
                  <span class="status-badge">{{ booking.status || 'pending' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else class="empty-state">No bookings found yet.</p>
        </div>
      </section>

      <section
        class="section-card"
        :class="{ 'section-card--highlight': focusSection === 'returns' }"
        ref="returnsSection"
      >
        <header class="section-header">
          <div>
            <h2>Vehicle return log</h2>
            <p class="section-subtitle">
              Bookings that reached return or completion stages
            </p>
          </div>
        </header>
        <div class="section-content">
          <table class="report-table" v-if="recentReturns.length">
            <thead>
              <tr>
                <th>Customer</th>
                <th>Return date</th>
                <th>Branch / Vehicle</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="booking in recentReturns" :key="booking.id">
                <td>
                  <strong>{{ booking.customer_name || 'Guest rider' }}</strong>
                  <p class="muted-text">{{ booking.customer_email || booking.customer_phone || '—' }}</p>
                </td>
                <td>
                  {{ formatDate(booking.end_date) || formatDate(booking.updated_at) || formatDate(booking.created_at) }}
                </td>
                <td>
                  <p>{{ booking.shop_name || booking.shop?.name || 'Branch unknown' }}</p>
                  <p class="muted-text">{{ booking.vehicle_name || 'Vehicle' }}</p>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else class="empty-state">
            No recent returns detected.
          </p>
        </div>
      </section>

      <section
        class="section-card"
        :class="{ 'section-card--highlight': focusSection === 'payments' }"
        ref="paymentsSection"
      >
        <header class="section-header">
          <div>
            <h2>Payments overview</h2>
            <p class="section-subtitle">Latest transactions tied to your bookings</p>
          </div>
          <span class="section-pill">{{ stats.totalPayments }} payments</span>
        </header>
        <div class="section-content">
          <table class="report-table" v-if="recentPayments.length">
            <thead>
              <tr>
                <th>Payment</th>
                <th>Booking</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="payment in recentPayments" :key="payment.payment_id || payment.id">
                <td>
                  <p>{{ payment.transaction_id || `TX-${payment.booking_id}` }}</p>
                  <p class="muted-text">{{ formatDate(payment.paid_at || payment.created_at) }}</p>
                </td>
                <td>
                  <strong>#{{
                    payment.booking?.id || payment.booking_id || '—'
                  }}</strong>
                  <p class="muted-text">{{ payment.booking?.status || payment.status || 'pending' }}</p>
                </td>
                <td>{{ formatCurrency(payment.amount ?? payment.booking?.total_price) }}</td>
                <td>
                  <span class="payment-status">{{ payment.payment_status || payment.status || 'pending' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else class="empty-state">No payments recorded yet.</p>
        </div>
      </section>

      <section
        class="section-card"
        :class="{ 'section-card--highlight': focusSection === 'riders' }"
        ref="ridersSection"
      >
        <header class="section-header">
          <div>
            <h2>Recent riders</h2>
            <p class="section-subtitle">Customers who joined through reservations</p>
          </div>
          <span class="section-pill">{{ stats.newCustomers }} entries</span>
        </header>
        <ul class="customer-list" v-if="recentCustomers.length">
          <li v-for="customer in recentCustomers" :key="customer.email + customer.phone">
            <div>
              <strong>{{ customer.name }}</strong>
              <p class="muted-text">{{ customer.email }}</p>
            </div>
            <div class="customer-meta">
              <p>{{ customer.phone }}</p>
              <small>Joined {{ formatDate(customer.joinedAt) }}</small>
            </div>
          </li>
        </ul>
        <p v-else class="empty-state">No rider data available yet.</p>
      </section>
    </template>
  </div>
</template>

<style scoped>
.section-card--highlight {
  border-color: #2563eb;
  box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.35);
}
</style>
