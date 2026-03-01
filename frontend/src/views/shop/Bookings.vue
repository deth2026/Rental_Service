<script setup>
import { ref, computed } from 'vue'

const bookingFilter = ref('all')
const modal = ref(false)
const selected = ref(null)

// Sample booking data with counts matching the spec
const bookings = ref([])

// Stat cards data - computed from bookings (empty state)
const pendingCount = computed(() => bookings.value.filter(b => b.status === 'Pending').length || 0)
const approvedCount = computed(() => bookings.value.filter(b => b.status === 'Approved').length || 0)
const completedCount = computed(() => bookings.value.filter(b => b.status === 'Completed').length || 0)
const cancelledCount = computed(() => bookings.value.filter(b => b.status === 'Cancelled').length || 0)

const filtered = computed(() => {
  if (bookingFilter.value === 'all') return bookings.value
  return bookings.value.filter(b => b.status.toLowerCase() === bookingFilter.value)
})

const setBooking = (id, status) => {
  const b = bookings.value.find(x => x.id === id)
  if (b) b.status = status
}

const openDetails = (b) => { selected.value = b; modal.value = true }
const closeModal = () => { modal.value = false; selected.value = null }
</script>

<template>
  <div class="bookings-container">
    <!-- Page Title -->
    <h1 class="page-title">Bookings</h1>

    <!-- Stat Cards Row -->
    <div class="stats-grid">
      <!-- Pending Card -->
      <div class="stat-card pending-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Pending</span>
          <span class="stat-number">{{ pendingCount }}</span>
        </div>
      </div>

      <!-- Approved Card -->
      <div class="stat-card approved-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Approved</span>
          <span class="stat-number">{{ approvedCount }}</span>
        </div>
      </div>

      <!-- Completed Card -->
      <div class="stat-card completed-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Completed</span>
          <span class="stat-number">{{ completedCount }}</span>
        </div>
      </div>

      <!-- Cancelled Card -->
      <div class="stat-card cancelled-card">
        <div class="stat-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </div>
        <div class="stat-content">
          <span class="stat-label">Cancelled</span>
          <span class="stat-number">{{ cancelledCount }}</span>
        </div>
      </div>
    </div>

    <!-- Filter Buttons (Sub-menu) -->
    <div class="filter-bar">
      <button 
        :class="['filter-btn', bookingFilter === 'all' ? 'active' : '']" 
        @click="bookingFilter = 'all'"
      >
        All
      </button>
      <button 
        :class="['filter-btn', 'pending-btn', bookingFilter === 'pending' ? 'active' : '']" 
        @click="bookingFilter = 'pending'"
      >
        New / Pending
      </button>
      <button 
        :class="['filter-btn', 'approved-btn', bookingFilter === 'approved' ? 'active' : '']" 
        @click="bookingFilter = 'approved'"
      >
        Approved
      </button>
      <button 
        :class="['filter-btn', 'completed-btn', bookingFilter === 'completed' ? 'active' : '']" 
        @click="bookingFilter = 'completed'"
      >
        Completed
      </button>
      <button 
        :class="['filter-btn', 'cancelled-btn', bookingFilter === 'cancelled' ? 'active' : '']" 
        @click="bookingFilter = 'cancelled'"
      >
        Cancelled
      </button>
    </div>

    <!-- Bookings Table -->
    <div class="table-container">
      <table class="bookings-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>CUSTOMER</th>
            <th>VEHICLE</th>
            <th>DATE</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filtered.length === 0">
            <td colspan="6" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <p>No bookings found</p>
              </div>
            </td>
          </tr>
          <tr v-for="b in filtered" :key="b.id">
            <td class="id-cell">#{{ b.id }}</td>
            <td class="customer-cell">{{ b.customer }}</td>
            <td class="vehicle-cell">{{ b.vehicle }}</td>
            <td class="date-cell">{{ b.date }}</td>
            <td class="status-cell">
              <span :class="['status-badge', b.status.toLowerCase()]">
                {{ b.status }}
              </span>
            </td>
            <td class="actions-cell">
              <button class="action-btn view-btn" @click="openDetails(b)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
                View
              </button>
              <button 
                v-if="b.status === 'Pending'" 
                class="action-btn accept-btn" 
                @click="setBooking(b.id, 'Approved')"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Accept
              </button>
              <button 
                v-if="b.status === 'Pending'" 
                class="action-btn reject-btn" 
                @click="setBooking(b.id, 'Cancelled')"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Reject
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Details Modal -->
    <div v-if="modal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Booking Details</h2>
          <button class="close-btn" @click="closeModal">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="detail-row">
            <span class="detail-label">Booking ID</span>
            <span class="detail-value">#{{ selected.id }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Vehicle</span>
            <span class="detail-value">{{ selected.vehicle }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Customer</span>
            <span class="detail-value">{{ selected.customer }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Pickup Date & Time</span>
            <span class="detail-value">{{ selected.pickup }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Dropoff Date & Time</span>
            <span class="detail-value">{{ selected.dropoff }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Total Amount</span>
            <span class="detail-value">${{ selected.total }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Status</span>
            <span :class="['status-badge', selected.status.toLowerCase()]">
              {{ selected.status }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Notes</span>
            <span class="detail-value notes">{{ selected.notes || 'No notes' }}</span>
          </div>
        </div>

        <div class="modal-footer">
          <button class="cancel-btn" @click="closeModal">Close</button>
          <button class="save-btn" @click="closeModal">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Container */
.bookings-container {
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
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

/* Stat Cards */
.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Pending Card */
.pending-card {
  background: #fefaf0;
  border-color: #fed7aa;
}

.pending-card .stat-icon {
  color: #ea580c;
}

.pending-card .stat-label {
  color: #ea580c;
}

.pending-card .stat-number {
  color: #c2410c;
}

/* Approved Card */
.approved-card {
  background: #eff6ff;
  border-color: #bfdbfe;
}

.approved-card .stat-icon {
  color: #2563eb;
}

.approved-card .stat-label {
  color: #2563eb;
}

.approved-card .stat-number {
  color: #1d4ed8;
}

/* Completed Card */
.completed-card {
  background: #f0fdf4;
  border-color: #bbf7d0;
}

.completed-card .stat-icon {
  color: #16a34a;
}

.completed-card .stat-label {
  color: #16a34a;
}

.completed-card .stat-number {
  color: #15803d;
}

/* Cancelled Card */
.cancelled-card {
  background: #fef2f2;
  border-color: #fecaca;
}

.cancelled-card .stat-icon {
  color: #dc2626;
}

.cancelled-card .stat-label {
  color: #dc2626;
}

.cancelled-card .stat-number {
  color: #b91c1c;
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
  gap: 8px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.filter-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.filter-btn.active {
  background: #1e293b;
  color: #fff;
  border-color: #1e293b;
}

.filter-btn.pending-btn.active {
  background: #ea580c;
  border-color: #ea580c;
}

.filter-btn.approved-btn.active {
  background: #2563eb;
  border-color: #2563eb;
}

.filter-btn.completed-btn.active {
  background: #16a34a;
  border-color: #16a34a;
}

.filter-btn.cancelled-btn.active {
  background: #dc2626;
  border-color: #dc2626;
}

/* Table Container */
.table-container {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
}

.bookings-table {
  width: 100%;
  border-collapse: collapse;
}

.bookings-table th,
.bookings-table td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.bookings-table th {
  background: #f8fafc;
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.bookings-table tbody tr {
  transition: background 0.15s;
}

.bookings-table tbody tr:hover {
  background: #f8fafc;
}

.bookings-table tbody tr:last-child td {
  border-bottom: none;
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

/* ID Cell */
.id-cell {
  font-weight: 600;
  color: #475569;
}

/* Customer Cell */
.customer-cell {
  font-weight: 500;
  color: #1e293b;
}

/* Vehicle Cell */
.vehicle-cell {
  color: #475569;
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

.status-badge.pending {
  background: #fef3c7;
  color: #b45309;
}

.status-badge.approved {
  background: #dbeafe;
  color: #1d4ed8;
}

.status-badge.completed {
  background: #dcfce7;
  color: #15803d;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #b91c1c;
}

/* Actions Cell */
.actions-cell {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  color: #475569;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.action-btn svg {
  width: 14px;
  height: 14px;
}

.action-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.view-btn:hover {
  color: #2563eb;
  border-color: #2563eb;
}

.accept-btn:hover {
  color: #16a34a;
  border-color: #16a34a;
}

.reject-btn:hover {
  color: #dc2626;
  border-color: #dc2626;
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
  max-width: 560px;
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

.detail-value.notes {
  max-width: 280px;
  text-align: right;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.cancel-btn {
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

.cancel-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.save-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background: #1e293b;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.save-btn:hover {
  background: #334155;
}
</style>
