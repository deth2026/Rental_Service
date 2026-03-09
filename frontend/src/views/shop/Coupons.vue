<script setup>
import { ref, onMounted } from 'vue'
import { couponApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)
const coupons = ref([])

const normalizeActiveFlag = (value) => {
  if (value === undefined || value === null || value === '') return true
  if (value === false || value === 0 || value === '0' || value === 'false') return false
  return true
}

// Toast notification
const toast = ref({ show: false, message: '', type: 'success' })
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => (toast.value.show = false), 3000)
}

// Fetch coupons from database
const fetchCoupons = async () => {
  try {
    loading.value = true
    const response = await couponApi.getAll()
    const data = response.data.data || response.data || []
    coupons.value = data.map((c) => ({
      id: c.id,
      code: c.code || '',
      discountPercent: c.discount_percent ?? null,
      discountAmount: c.discount_amount ?? null,
      createDate: c.valid_from || c.created_at || '',
      expireDate: c.valid_until || '',
      isActive: normalizeActiveFlag(c.is_active)
    }))
  } catch (err) {
    console.error('Error fetching coupons:', err)
    error.value = 'Failed to load coupons'
  } finally {
    loading.value = false
  }
}

onMounted(fetchCoupons)

// Create / Edit modal
const showModal = ref(false)
const editId = ref(null)
const form = ref({
  code: '',
  discountPercent: '',
  discountAmount: '',
  createDate: '',
  expireDate: '',
  isActive: true
})

const openCreate = () => {
  editId.value = null
  const today = new Date().toISOString().split('T')[0]
  form.value = {
    code: '',
    discountPercent: '',
    discountAmount: '',
    createDate: today,
    expireDate: '',
    isActive: true
  }
  showModal.value = true
}

const openEdit = (c) => {
  editId.value = c.id
  form.value = {
    code: c.code,
    discountPercent: c.discountPercent ?? '',
    discountAmount: c.discountAmount ?? '',
    createDate: c.createDate ? c.createDate.split('T')[0] : '',
    expireDate: c.expireDate ? c.expireDate.split('T')[0] : '',
    isActive: c.isActive !== false
  }
  showModal.value = true
}

const save = async () => {
  if (!form.value.code || !form.value.expireDate) {
    showToast('Please fill in Code and Expire Date', 'error')
    return
  }
  const payload = {
    code: form.value.code.toUpperCase(),
    discount_percent: form.value.discountPercent !== '' ? Number(form.value.discountPercent) : null,
    discount_amount: form.value.discountAmount !== '' ? Number(form.value.discountAmount) : null,
    valid_from: form.value.createDate || new Date().toISOString().split('T')[0],
    valid_until: form.value.expireDate,
    is_active: form.value.isActive ? 1 : 0
  }
  try {
    if (editId.value) {
      await couponApi.update(editId.value, payload)
      showToast('Coupon updated successfully!', 'success')
    } else {
      await couponApi.create(payload)
      showToast('Coupon created successfully!', 'success')
    }
    await fetchCoupons()
    showModal.value = false
  } catch (err) {
    console.error('Error saving coupon:', err)
    showToast('Failed to save coupon. Please try again.', 'error')
  }
}

// Delete confirmation modal
const showDeleteModal = ref(false)
const deleteId = ref(null)
const deleteCouponCode = ref('')

const confirmDelete = (c) => {
  deleteId.value = c.id
  deleteCouponCode.value = c.code || 'Unknown Coupon'
  showDeleteModal.value = true
}

const cancelDelete = () => {
  deleteId.value = null
  deleteCouponCode.value = ''
  showDeleteModal.value = false
}

const removeCoupon = async () => {
  if (!deleteId.value) return
  try {
    await couponApi.delete(deleteId.value)
    await fetchCoupons()
    showToast('Coupon deleted successfully!', 'success')
  } catch (err) {
    console.error('Error deleting coupon:', err)
    showToast('Failed to delete coupon', 'error')
  } finally {
    cancelDelete()
  }
}

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

const formatId = (id) => `C${String(id).padStart(3, '0')}`

const isExpired = (expireDate) => {
  if (!expireDate) return false
  return new Date(expireDate) < new Date()
}

</script>

<template>
  <div class="panel coupons-page">
    <div class="line top-line">
      <div>
        <h3>Manage Coupons</h3>
        <p class="muted">Create and manage discount coupons</p>
      </div>
      <div class="controls">
        <button class="primary add-btn" @click="openCreate">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg> Add New Coupon
        </button>
      </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading coupons...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="retry-btn" @click="fetchCoupons">Retry</button>
      </div>

      <!-- Data Table -->
      <table v-else class="coupons-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>CODE</th>
            <th>DISCOUNT %</th>
            <th>DISCOUNT AMOUNT</th>
            <th>CREATE DATE</th>
            <th>EXPIRE DATE</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody>
          <!-- Empty State -->
          <tr v-if="coupons.length === 0">
            <td colspan="8" class="empty-state">
              <div class="empty-content">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M3 8a2 2 0 0 1 2-2h14v4a2 2 0 0 0 0 4v4H5a2 2 0 0 1-2-2z"/>
                  <path d="M9 6v12M15 6v12"/>
                </svg>
                <p>No coupons yet</p>
                <button class="create-btn-sm" @click="openCreate">Create Your First Coupon</button>
              </div>
            </td>
          </tr>

          <!-- Coupon Rows -->
          <tr v-for="c in coupons" :key="c.id">
            <td class="id-cell">{{ formatId(c.id) }}</td>
            <td class="code-cell">{{ c.code }}</td>
            <td class="percent-cell">
              <span v-if="c.discountPercent" class="discount-percent">{{ c.discountPercent }}%</span>
              <span v-else class="no-data">—</span>
            </td>
            <td class="amount-cell">
              <span v-if="c.discountAmount" class="discount-amount">${{ c.discountAmount }}</span>
              <span v-else class="no-data">—</span>
            </td>
            <td class="date-cell">{{ formatDate(c.createDate) }}</td>
            <td class="date-cell" :class="{ 'expired-date': isExpired(c.expireDate) }">
              {{ formatDate(c.expireDate) }}
            </td>
            <td class="status-cell">
              <div class="status-toggle">
                <span class="status-btn" :class="c.isActive ? 'active' : 'inactive'">
                  {{ c.isActive ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </td>
            <td class="actions-cell">
              <button class="action-btn edit" @click="openEdit(c)" title="Edit">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button class="action-btn delete" @click="confirmDelete(c)" title="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create / Edit Modal - Matching Vehicles.vue Style -->
    <div v-if="showModal" class="add-vehicle-overlay" @click.self="showModal = false">
      <div class="add-vehicle-modal">
        <!-- Header -->
        <div class="add-vehicle-header">
          <h2>{{ editId ? 'Edit Coupon' : 'Add New Coupon' }}</h2>
          <button class="close-btn" @click="showModal = false">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>

        <!-- Content -->
        <div class="add-vehicle-content">
          <!-- Left Column -->
          <div class="add-vehicle-left">
            <!-- Coupon Information -->
            <div class="form-card">
              <h3 class="card-title">Coupon Information</h3>
              <div class="form-group">
                <label>Coupon Code</label>
                <input v-model="form.code" placeholder="e.g., SUMMER2024" />
              </div>
            </div>

            <!-- Discount -->
            <div class="form-card">
              <h3 class="card-title">Discount</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Discount Percent (%)</label>
                  <input v-model="form.discountPercent" type="number" min="0" max="100" placeholder="0" />
                </div>
                <div class="form-group">
                  <label>Discount Amount ($)</label>
                  <input v-model="form.discountAmount" type="number" min="0" placeholder="0" />
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="add-vehicle-right">
            <!-- Validity Period -->
            <div class="form-card">
              <h3 class="card-title">Validity Period</h3>
              <div class="form-row">
                <div class="form-group">
                  <label>Create Date</label>
                  <input v-model="form.createDate" type="date" />
                </div>
                <div class="form-group">
                  <label>Expire Date</label>
                  <input v-model="form.expireDate" type="date" />
                </div>
              </div>
            </div>

            <!-- Status -->
            <div class="form-card">
              <h3 class="card-title">Status</h3>
              <div class="form-group">
                <label>Coupon Status</label>
                <select v-model="form.isActive">
                  <option :value="true">Active</option>
                  <option :value="false">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="add-vehicle-footer">
          <button class="discard-btn" @click="showModal = false">Cancel</button>
          <button class="store-btn" @click="save">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            {{ editId ? 'Update Coupon' : 'Store Coupon' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" :class="['toast', toast.type]">
      {{ toast.message }}
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div v-if="showDeleteModal" class="delete-overlay" @click.self="cancelDelete">
    <div class="delete-modal">
      <div class="delete-icon-wrapper">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          <line x1="10" y1="11" x2="10" y2="17"></line>
          <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
      </div>
      <h3 class="delete-title">Delete Coupon</h3>
      <p class="delete-message">Are you sure you want to delete this coupon?</p>
      <p class="delete-coupon-code">{{ deleteCouponCode }}</p>
      <p class="delete-warning">This action cannot be undone. All associated data will be permanently removed.</p>
      <div class="delete-actions">
        <button class="delete-cancel-btn" @click="cancelDelete">Cancel</button>
        <button class="delete-confirm-btn" @click="removeCoupon">Delete</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.panel {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
}

.line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.top-line {
  margin-bottom: 14px;
}

.top-line h3 {
  margin: 0;
}

.top-line .muted {
  margin: 4px 0 0;
  color: #64748b;
}

.controls {
  display: flex;
  gap: 10px;
  align-items: center;
}

.primary,
button {
  border: 0;
  border-radius: 9px;
  padding: 8px 12px;
  cursor: pointer;
}

.primary {
  background: #1d4ed8;
  color: #fff;
}

.add-btn {
  background: #2563eb;
  color: #fff;
  display: inline-flex;
  gap: 8px;
  align-items: center;
  border-radius: 10px;
  padding: 10px 16px;
  font-weight: 600;
}

.danger {
  background: #ef4444;
  color: #fff;
}

/* Table Styles */
.table-container {
  overflow: auto;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}

.coupons-table {
  width: 100%;
  min-width: 800px;
  border-collapse: collapse;
}

.coupons-table th,
.coupons-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #e2e8f0;
  text-align: left;
}

.coupons-table th {
  font-size: 12px;
  background: #f8fafc;
  text-transform: uppercase;
  color: #475569;
}

.coupons-table tbody tr:hover {
  background: #f8fafc;
}

.id-cell {
  font-weight: 600;
  color: #475569;
}

.code-cell {
  font-weight: 700;
  color: #1e293b;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.discount-percent {
  font-weight: 700;
  color: #059669;
}

.discount-amount {
  font-weight: 700;
  color: #dc2626;
}

.no-data {
  color: #94a3b8;
}

.date-cell {
  color: #64748b;
  font-size: 0.875rem;
}

.expired-date {
  color: #dc2626;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-active {
  background: #d1fae5;
  color: #065f46;
}

.status-expired {
  background: #fee2e2;
  color: #dc2626;
}

.status-expiring {
  background: #fef3c7;
  color: #92400e;
}

.status-inactive {
  background: #e2e8f0;
  color: #475569;
}

.status-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status-btn {
  padding: 6px 10px;
  border: 1px solid #d1d9e6;
  border-radius: 999px;
  background: #fff;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
  cursor: default;
  transition: all 0.2s;
}

.status-btn.active {
  background: #d1fae5;
  border-color: #86efac;
  color: #166534;
}

.status-btn.inactive {
  background: #fee2e2;
  border-color: #fecaca;
  color: #991b1b;
}

.actions-cell {
  display: flex;
  gap: 10px;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  background: #fff;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn svg {
  width: 18px;
  height: 18px;
}

.action-btn.edit:hover {
  background: #eff6ff;
  border-color: #2563eb;
  color: #2563eb;
  transform: translateY(-1px);
}

.action-btn.delete:hover {
  background: #fef2f2;
  border-color: #dc2626;
  color: #dc2626;
  transform: translateY(-1px);
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
  gap: 16px;
  color: #94a3b8;
}

.empty-content svg {
  width: 56px;
  height: 56px;
  opacity: 0.4;
}

.empty-content p {
  margin: 0;
  font-size: 1rem;
  font-weight: 500;
}

.create-btn-sm {
  padding: 10px 20px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
}

/* Toast */
.toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 14px 20px;
  border-radius: 8px;
  color: #0f172a;
  font-weight: 500;
  font-size: 14px;
  z-index: 9999;
  animation: slideIn 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  min-width: 220px;
}

.toast.success {
  background: #d1fae5;
  color: #065f46;
  border-left: 4px solid #10b981;
}

.toast.error {
  background: #fee2e2;
  color: #dc2626;
  border-left: 4px solid #ef4444;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }

  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Modal Styles - Matching Vehicles.vue */
.add-vehicle-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.add-vehicle-modal {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 800px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.add-vehicle-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.add-vehicle-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.add-vehicle-header .close-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #f1f5f9;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  transition: all 0.2s;
}

.add-vehicle-header .close-btn:hover {
  background: #e2e8f0;
  color: #1a1a1a;
}

.add-vehicle-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

.add-vehicle-left,
.add-vehicle-right {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 20px;
}

.card-title {
  font-size: 14px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #e2e8f0;
}

.form-group {
  margin-bottom: 16px;
  flex: 1;
}

.form-group:last-child {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 500;
  color: #475569;
  margin-bottom: 6px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  color: #1a1a1a;
  background: white;
  transition: all 0.2s;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input::placeholder {
  color: #94a3b8;
}

.form-row {
  display: flex;
  gap: 12px;
}

.add-vehicle-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.discard-btn {
  padding: 10px 20px;
  border: 1px solid #cbd5e1;
  background: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.discard-btn:hover {
  border-color: #94a3b8;
  color: #1a1a1a;
}

.store-btn {
  padding: 10px 24px;
  border: none;
  background: #3b82f6;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.store-btn:hover {
  background: #2563eb;
}

/* Delete Modal Styles */
.delete-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.delete-modal {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 420px;
  width: 90%;
  text-align: center;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.delete-icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #fef2f2;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.delete-title {
  font-size: 24px;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 12px;
}

.delete-message {
  font-size: 16px;
  color: #333;
  margin: 0 0 8px;
}

.delete-coupon-code {
  font-size: 14px;
  color: #e74c3c;
  font-weight: 600;
  margin: 0 0 16px;
  padding: 8px 16px;
  background: #fef2f2;
  border-radius: 8px;
  display: inline-block;
}

.delete-warning {
  font-size: 13px;
  color: #666;
  margin: 0 0 24px;
}

.delete-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.delete-cancel-btn {
  padding: 12px 24px;
  border: 2px solid #ddd;
  background: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #666;
  cursor: pointer;
  min-width: 120px;
}

.delete-cancel-btn:hover {
  border-color: #999;
}

.delete-confirm-btn {
  padding: 12px 24px;
  border: none;
  background: #e74c3c;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  color: white;
  cursor: pointer;
  min-width: 120px;
}

.delete-confirm-btn:hover {
  background: #c0392b;
}

@media (max-width: 768px) {
  .add-vehicle-content {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    flex-direction: column;
  }
}
</style>

