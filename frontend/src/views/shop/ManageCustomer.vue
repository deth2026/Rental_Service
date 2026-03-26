<script setup>
import { computed, ref, onMounted } from 'vue'
import '../../css/ManageCustomer.css'

const activeTab = ref('all')
const searchQuery = ref('')
const customers = ref([])
const loading = ref(true)
const error = ref(null)

const getStoredToken = () => localStorage.getItem('auth_token') || localStorage.getItem('token') || ''

// Fetch bookings and extract customer data
const fetchCustomers = async () => {
  loading.value = true
  error.value = null
  try {
    const token = getStoredToken()
    
    const headers = { Accept: 'application/json' }
    if (token) headers.Authorization = `Bearer ${token}`
    
    // Use the main bookings endpoint to get shop owner's bookings
    console.log('ManageCustomer - Fetching from /api/bookings...')
    console.log('ManageCustomer - Token available:', !!token)
    console.log('ManageCustomer - Response status:', response.status)
    const response = await fetch('/api/bookings', { headers })
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      const errorMsg = errorData.message || errorData.error || `HTTP error! status: ${response.status}`
      throw new Error(errorMsg)
    }
    
    const data = await response.json()
    console.log('ManageCustomer - Raw API response:', data)
    
    const bookings = Array.isArray(data) ? data : (data.data || [])
    console.log('ManageCustomer - Bookings array:', bookings)
    console.log('ManageCustomer - First booking sample:', bookings[0])
    
    // Group bookings by customer
    const customerMap = new Map()
    
    bookings.forEach(booking => {
      const user = booking.user
      if (!user || !user.id) return
      
      if (!customerMap.has(user.id)) {
        customerMap.set(user.id, {
          id: user.id,
          name: user.name || 'Unknown',
          email: user.email || '',
          phone: user.phone || '',
          avatar_url: user.avatar_url || user.img_url || '',
          totalBookings: 0,
          firstBookingDate: booking.created_at,
          lastBookingDate: booking.created_at,
          totalSpent: 0,
          pendingCount: 0,
          activeCount: 0,
          completedCount: 0,
          bookingStatuses: []
        })
      }
      
      const customer = customerMap.get(user.id)
      customer.totalBookings++
      customer.totalSpent += parseFloat(booking.total_price || 0)
      
      // Track booking statuses
      const status = booking.status?.toLowerCase()
      if (status === 'pending') {
        customer.pendingCount++
      } else if (status === 'confirmed' || status === 'in_use' || status === 'rented') {
        customer.activeCount++
      } else if (status === 'completed') {
        customer.completedCount++
      }
      
      if (booking.status && !customer.bookingStatuses.includes(booking.status)) {
        customer.bookingStatuses.push(booking.status)
      }
      
      // Update first and last booking dates
      const bookingDate = new Date(booking.created_at)
      const firstDate = new Date(customer.firstBookingDate)
      const lastDate = new Date(customer.lastBookingDate)
      
      if (bookingDate < firstDate) {
        customer.firstBookingDate = booking.created_at
      }
      if (bookingDate > lastDate) {
        customer.lastBookingDate = booking.created_at
      }
    })
    
    // Convert to array
    customers.value = Array.from(customerMap.values())
    console.log('ManageCustomer - Total customers found:', customers.value.length)
    console.log('ManageCustomer - Customer list:', customers.value)
    
  } catch (e) {
    error.value = e.message || 'Unable to load customers'
    console.error('Error fetching customers:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCustomers()
})

// Filter customers based on active tab and search
const filteredCustomers = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  
  return customers.value.filter(customer => {
    // Filter by tab based on booking status
    let tabMatch = true
    if (activeTab.value === 'new') {
      // New: Customers with pending bookings (not yet accepted)
      tabMatch = customer.pendingCount > 0
    } else if (activeTab.value === 'active') {
      // Active: Customers with active/confirmed bookings (not yet completed)
      tabMatch = customer.activeCount > 0
    } else if (activeTab.value === 'returning') {
      // Returning: Customers with completed bookings (returned vehicle)
      tabMatch = customer.completedCount > 0
    }
    
    if (!tabMatch) return false
    
    // Filter by search
    if (!q) return true
    
    const haystack = [
      customer.name,
      customer.email,
      customer.phone,
    ]
      .join(' ')
      .toLowerCase()
    
    return haystack.includes(q)
  })
})

const getStatusInfo = (customer) => {
  // Determine the most relevant status for display
  if (customer.pendingCount > 0) {
    return { type: 'new', label: 'New', class: 'status-new' }
  } else if (customer.activeCount > 0) {
    return { type: 'active', label: 'Active', class: 'status-active' }
  } else if (customer.completedCount > 0) {
    return { type: 'returning', label: 'Completed', class: 'status-returning' }
  }
  return { type: 'new', label: 'New', class: 'status-new' }
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

const getInitials = (name) => {
  if (!name) return 'U'
  const parts = name.split(/\s+/).filter(Boolean)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0]}${parts[1][0]}`.toUpperCase()
}

const normalizeAvatarUrl = (url) => {
  if (!url) return ''
  if (/^https?:\/\//.test(url) || /^data:image\//.test(url)) return url
  const normalized = String(url).replace(/^\/+/, '')
  if (normalized.startsWith('storage/')) return `/${normalized}`
  if (normalized.startsWith('avatars/')) return `/storage/${normalized}`
  return `/storage/avatars/${normalized}`
}
</script>

<template>
  <div class="customers-page">
    <section class="customers-panel">
      <div class="panel-head">
        <h1>Manage Customers</h1>
        <p>View and manage customers who have booked from your shop.</p>
      </div>

      <div class="controls-row">
        <div class="tabs-row">
          <button class="tab-btn" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">All</button>
          <button class="tab-btn" :class="{ active: activeTab === 'new' }" @click="activeTab = 'new'">New</button>
          <button class="tab-btn" :class="{ active: activeTab === 'active' }" @click="activeTab = 'active'">Active</button>
          <button class="tab-btn" :class="{ active: activeTab === 'returning' }" @click="activeTab = 'returning'">Completed</button>
        </div>

        <div class="search-row">
          <input
            v-model="searchQuery"
            type="text"
            class="search-input"
            placeholder="Search by name, email, or phone..."
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

    <section class="customer-list-wrap">
      <div v-if="loading" class="empty-state">Loading customers...</div>
      <div v-else-if="error" class="empty-state">{{ error }}</div>
      <div v-else-if="filteredCustomers.length === 0" class="empty-state">No customers found.</div>

      <table v-else class="customer-table">
        <thead>
          <tr>
            <th>Customer</th>
            <th>Total Bookings</th>
            <th>Total Spent</th>
            <th>First Booking</th>
            <th>Last Booking</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in filteredCustomers" :key="customer.id">
            <td>
              <div class="customer-info">
                <div class="customer-avatar">
                  <img 
                    v-if="customer.avatar_url" 
                    :src="normalizeAvatarUrl(customer.avatar_url)" 
                    :alt="customer.name"
                    @error="(e) => e.target.style.display = 'none'"
                  />
                  <span v-else class="avatar-initials">{{ getInitials(customer.name) }}</span>
                </div>
                <div class="customer-details">
                  <span class="customer-name">{{ customer.name }}</span>
                  <span class="customer-email">{{ customer.email }}</span>
                </div>
              </div>
            </td>
            <td class="bookings-count">{{ customer.totalBookings }}</td>
            <td class="total-spent">{{ formatCurrency(customer.totalSpent) }}</td>
            <td class="date-cell">{{ formatDate(customer.firstBookingDate) }}</td>
            <td class="date-cell">{{ formatDate(customer.lastBookingDate) }}</td>
            <td>
              <span :class="['status-pill', getStatusInfo(customer).class]">
                {{ getStatusInfo(customer).label }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>
