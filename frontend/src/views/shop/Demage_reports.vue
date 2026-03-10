<script setup>
import { ref, computed, onMounted } from 'vue'
import { couponApi } from '@/services/api'
import '../../css/Demage_Report.css'

const loading = ref(true)
const error = ref(null)

const formatDateOnly = (value) => {
  if (!value) return ''
  const datePart = String(value).split('T')[0]
  const d = new Date(datePart)
  if (Number.isNaN(d.getTime())) return datePart
  const day = String(d.getDate()).padStart(2, '0')
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const year = d.getFullYear()
  return `${day}/${month}/${year}`
}

// Fetch coupons from database
const fetchCoupons = async () => {
  try {
    loading.value = true
    const response = await couponApi.getAll()
    const data = response.data.data || response.data || []
    
    // Map database fields to expected format
    reports.value = data.map(c => ({
      id: c.id,
      vehicle: c.code || 'N/A',
      description: c.description || 'Discount coupon',
      discountPercent: Number(c.discount_percent || 0),
      date: formatDateOnly(c.valid_from || ''),
      validUntil: formatDateOnly(c.valid_until || ''),
      usageLimit: c.usage_limit || 'Unlimited',
      isActive: c.is_active || false,
      minimumAmount: c.minimum_amount || 0
    }))
  } catch (err) {
    console.error('Error fetching coupons:', err)
    error.value = 'Failed to load coupons data'
  } finally {
    loading.value = false
  }
}

// Fetch on mount
onMounted(() => {
  fetchCoupons()
})

// Empty reports data - connected to coupons table
const reports = ref([])

const modal = ref(false)
const selectedReport = ref(null)

const openDetails = (r) => {
  selectedReport.value = r
  modal.value = true
}

const closeModal = () => {
  modal.value = false
  selectedReport.value = null
}
</script>

<template>
  <div class="damage-container">
    <!-- Page Title -->
    <h1 class="page-title">Damage Reports</h1>

    <!-- Damage Reports Table -->
    <div class="table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading data...</p>
      </div>
      
      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchCoupons">Retry</button>
      </div>

      <!-- Data Table -->
      <table v-else class="damage-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>VEHICLE</th>
            <th>DESCRIPTION</th>
            <th>PENALTY</th>
            <th>DATE</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="reports.length === 0">
            <td colspan="5" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                <p>No damage reports found</p>
              </div>
            </td>
          </tr>
          <tr v-for="r in reports" :key="r.id" @click="openDetails(r)" class="clickable-row">
            <td class="id-cell">#{{ r.id }}</td>
            <td class="vehicle-cell">{{ r.vehicle }}</td>
            <td class="description-cell">{{ r.description }}</td>
            <td class="penalty-cell">{{ r.discountPercent }}%</td>
            <td class="date-cell">{{ r.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Details Modal -->
    <div v-if="modal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Damage Report Details</h2>
          <button class="close-btn" @click="closeModal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="modal-body" v-if="selectedReport">
          <div class="detail-row">
            <span class="detail-label">Report ID</span>
            <span class="detail-value">#{{ selectedReport.id }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Coupon Code</span>
            <span class="detail-value">{{ selectedReport.vehicle }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Description</span>
            <span class="detail-value">{{ selectedReport.description }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Discount Amount</span>
            <span class="detail-value penalty">{{ selectedReport.discountPercent }}%</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Valid From</span>
            <span class="detail-value">{{ selectedReport.date }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Valid Until</span>
            <span class="detail-value">{{ selectedReport.validUntil }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Usage Limit</span>
            <span class="detail-value">{{ selectedReport.usageLimit }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Minimum Amount</span>
            <span class="detail-value">${{ selectedReport.minimumAmount }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Status</span>
            <span :class="['status-badge', selectedReport.isActive ? 'active' : 'inactive']">
              {{ selectedReport.isActive ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>

        <div class="modal-footer">
          <button class="close-modal-btn" @click="closeModal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

