<script setup>
import { ref, computed, onMounted } from 'vue'
import { couponApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)

// Format date to show only day/month/year
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  try {
    const date = new Date(dateStr)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${day}/${month}/${year}`
  } catch (e) {
    return dateStr
  }
}

// Format penalty display - shows % or $
const getPenaltyDisplay = (penalty, desc) => {
  if (desc && desc.includes('%')) {
    return desc.replace('% discount', '%')
  }
  return '$' + penalty
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
      description: c.description || (c.discount_percent ? `${c.discount_percent}% discount` : `$${c.discount_amount} off`),
      penalty: c.discount_amount || c.discount_percent || 0,
      date: c.valid_from || '',
      validUntil: c.valid_until || '',
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
            <td class="penalty-cell">{{ getPenaltyDisplay(r.penalty, r.description) }}</td>
            <td class="date-cell">{{ formatDate(r.date) }}</td>
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
            <span class="detail-value penalty">{{ getPenaltyDisplay(selectedReport.penalty, selectedReport.description) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Valid From</span>
            <span class="detail-value">{{ formatDate(selectedReport.date) }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Valid Until</span>
            <span class="detail-value">{{ formatDate(selectedReport.validUntil) }}</span>
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

<style scoped>
/* Container */
.damage-container {
  padding: 20px;
  /* max-width: 1400px; */
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

/* Table Container */
.table-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.damage-table {
  width: 100%;
  border-collapse: collapse;
}

.damage-table th,
.damage-table td {
  padding: 16px 24px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.damage-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 500;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.damage-table tbody tr {
  transition: background 0.15s;
}

.damage-table tbody tr:hover {
  background: #f8fafc;
}

.damage-table tbody tr:last-child td {
  border-bottom: none;
}

.clickable-row {
  cursor: pointer;
}

/* ID Cell */
.id-cell {
  font-weight: 600;
  color: #475569;
}

/* Vehicle Cell */
.vehicle-cell {
  font-weight: 500;
  color: #1e293b;
}

/* Description Cell */
.description-cell {
  color: #475569;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Penalty Cell */
.penalty-cell {
  font-weight: 600;
  color: #dc2626;
}

/* Date Cell */
.date-cell {
  color: #64748b;
  font-size: 0.875rem;
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

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 1000;
}

/* Modal Content */
.modal-content {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.close-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}

.close-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

/* Modal Body */
.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 0.875rem;
  color: #1e293b;
  font-weight: 500;
  text-align: right;
}

.detail-value.penalty {
  color: #dc2626;
  font-weight: 600;
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

.status-badge.active {
  background: #dcfce7;
  color: #15803d;
}

.status-badge.inactive {
  background: #f1f5f9;
  color: #64748b;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.close-modal-btn {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.close-modal-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}
</style>
