<script setup>
import { ref, computed, onMounted } from 'vue'
import { couponApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)

// Toast notification
const toast = ref({ show: false, message: '', type: 'success' })
const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => toast.value.show = false, 3000)
}

// Fetch coupons from database
const fetchCoupons = async () => {
  try {
    loading.value = true
    const response = await couponApi.getAll()
    const data = response.data.data || response.data || []
    
    // Map database fields to expected format
    coupons.value = data.map(c => ({
      id: c.id,
      code: c.code || '',
      discountPercent: c.discount_percent || 0,
      discountAmount: c.discount_amount || 0,
      description: c.description || '',
      validFrom: c.valid_from || '',
      validUntil: c.valid_until || '',
      usageLimit: c.usage_limit || null,
      minimumAmount: c.minimum_amount || 0,
      isActive: c.is_active !== false
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

// Empty coupons data - connected to coupons table
const coupons = ref([])

const modal = ref(false)
const editId = ref(null)

const form = ref({ 
  code: '', 
  discount: '',
  discountType: 'percent',
  description: '', 
  validUntil: '', 
  maxUses: ''
})

const openCreate = () => { 
  editId.value = null
  form.value = { 
    code: '', 
    discount: '',
    discountType: 'percent',
    description: '', 
    validUntil: '', 
    maxUses: ''
  }
  modal.value = true 
}

const openEdit = (c) => { 
  editId.value = c.id
  form.value = { 
    code: c.code, 
    discount: c.discountPercent || c.discountAmount || '',
    discountType: c.discountPercent ? 'percent' : 'fixed',
    description: c.description || '', 
    validUntil: c.validUntil, 
    maxUses: c.usageLimit || ''
  }
  modal.value = true 
}

const save = async () => {
  if (!form.value.code || !form.value.discount || !form.value.validUntil) {
    showToast('Please fill in all required fields', 'error')
    return
  }
  
  const payload = {
    code: form.value.code.toUpperCase(),
    discount_percent: form.value.discountType === 'percent' ? form.value.discount : null,
    discount_amount: form.value.discountType === 'fixed' ? form.value.discount : null,
    description: form.value.description || null,
    valid_from: new Date().toISOString().split('T')[0],
    valid_until: form.value.validUntil,
    usage_limit: form.value.maxUses ? parseInt(form.value.maxUses) : null,
    is_active: true
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
    modal.value = false
  } catch (err) {
    console.error('Error saving coupon:', err)
    showToast('Failed to save coupon. Please try again.', 'error')
  }
}

const removeCoupon = async (id) => {
  if (!confirm('Are you sure you want to delete this coupon?')) return
  try {
    await couponApi.delete(id)
    await fetchCoupons()
  } catch (err) {
    console.error('Error deleting coupon:', err)
  }
}

// Format discount display
const formatDiscount = (c) => {
  if (c.discountPercent) return `${c.discountPercent}%`
  if (c.discountAmount) return `$${c.discountAmount}`
  return '0%'
}

// Check if coupon is expired
const isExpired = (validUntil) => {
  if (!validUntil) return false
  return new Date(validUntil) < new Date()
}

// Format date for display
const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('en-GB')
}
</script>

<template>
  <div class="coupons-container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Coupons</h1>
      <button class="create-btn" @click="openCreate">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        New Coupon
      </button>
    </div>

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

    <!-- Coupons List -->
    <div v-else class="coupons-list">
      <!-- Empty State -->
      <div v-if="coupons.length === 0" class="empty-state">
        <div class="empty-content">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"></path>
            <path d="M15 6v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V6"></path>
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
          </svg>
          <p>No coupons yet</p>
          <button class="create-btn small" @click="openCreate">Create Your First Coupon</button>
        </div>
      </div>

      <!-- Coupon Cards -->
      <div v-else v-for="c in coupons" :key="c.id" class="coupon-card">
        <div class="coupon-left">
          <div class="coupon-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6"></path>
              <path d="M15 6v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2V6"></path>
            </svg>
          </div>
          <div class="coupon-info">
            <span class="coupon-code">{{ c.code }}</span>
            <span class="coupon-expires">Expires: {{ formatDate(c.validUntil) }}</span>
          </div>
        </div>
        
        <div class="coupon-right">
          <span class="coupon-discount" :class="{ 'has-percent': c.discountPercent, 'has-amount': c.discountAmount }">
            {{ formatDiscount(c) }}
          </span>
          <span :class="['status-badge', c.isActive && !isExpired(c.validUntil) ? 'active' : 'expired']">
            {{ c.isActive && !isExpired(c.validUntil) ? 'Active' : 'Expired' }}
          </span>
          <div class="coupon-actions">
            <button class="action-btn edit" @click="openEdit(c)" title="Edit">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
            </button>
            <button class="action-btn delete" @click="removeCoupon(c.id)" title="Delete">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Transition name="modal">
      <div v-if="modal" class="modal-overlay" @click.self="modal = false">
        <div class="modal-content">
          <!-- Modal Header -->
          <div class="modal-header">
            <h2 class="modal-title">+ New Coupon</h2>
            <button class="close-btn" @click="modal = false">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          
          <!-- Modal Body -->
          <div class="modal-body">
            <!-- Coupon Code -->
            <div class="form-group">
              <label for="code">Coupon Code <span class="required">*</span></label>
              <input 
                id="code" 
                v-model="form.code" 
                type="text" 
                placeholder="e.g., SUMMER2024"
                class="form-input"
              />
            </div>

            <!-- Discount Row -->
            <div class="form-row">
              <div class="form-group flex-2">
                <label for="discount">Discount <span class="required">*</span></label>
                <div class="input-with-suffix">
                  <input 
                    id="discount" 
                    v-model="form.discount" 
                    type="number" 
                    placeholder="Amount"
                    class="form-input"
                  />
                  <span class="input-suffix">{{ form.discountType === 'percent' ? '%' : '$' }}</span>
                </div>
              </div>
              <div class="form-group flex-1">
                <label for="discountType">Type</label>
                <select id="discountType" v-model="form.discountType" class="form-select">
                  <option value="percent">%</option>
                  <option value="fixed">$</option>
                </select>
              </div>
            </div>

            <!-- Description -->
            <div class="form-group">
              <label for="description">Description</label>
              <input 
                id="description" 
                v-model="form.description" 
                type="text" 
                placeholder="e.g., Summer sale discount"
                class="form-input"
              />
            </div>

            <!-- Expiry Date -->
            <div class="form-group">
              <label for="validUntil">Expiry Date <span class="required">*</span></label>
              <div class="date-input-wrapper">
                <input 
                  id="validUntil" 
                  v-model="form.validUntil" 
                  type="date" 
                  class="form-input"
                />
                <svg class="date-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                  <line x1="16" y1="2" x2="16" y2="6"></line>
                  <line x1="8" y1="2" x2="8" y2="6"></line>
                  <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
              </div>
            </div>

            <!-- Max Uses -->
            <div class="form-group">
              <label for="maxUses">Max Uses <span class="optional">(optional)</span></label>
              <input 
                id="maxUses" 
                v-model="form.maxUses" 
                type="number" 
                placeholder="Unlimited"
                class="form-input"
              />
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="modal-footer">
            <button class="submit-btn" @click="save">
              Add Coupon
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Toast Notification -->
    <Transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        {{ toast.message }}
      </div>
    </Transition>
  </div>
</template>

<style scoped>
/* Container */
.coupons-container {
  padding: 20px;
  max-width: 900px;
  margin: 0 auto;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

/* Page Title */
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

/* Create Button */
.create-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.9375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.create-btn:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.create-btn.small {
  padding: 10px 16px;
  font-size: 0.875rem;
}

.create-btn svg {
  width: 18px;
  height: 18px;
}

/* Coupons List */
.coupons-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Coupon Card */
.coupon-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  transition: box-shadow 0.2s;
}

.coupon-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Coupon Left */
.coupon-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.coupon-icon {
  width: 44px;
  height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #eff6ff;
  border-radius: 10px;
  color: #2563eb;
}

.coupon-icon svg {
  width: 24px;
  height: 24px;
}

.coupon-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.coupon-code {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.coupon-expires {
  font-size: 0.875rem;
  color: #64748b;
}

/* Coupon Right */
.coupon-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.coupon-discount {
  font-size: 1.5rem;
  font-weight: 700;
}

.coupon-discount.has-percent {
  color: #0d9488;
}

.coupon-discount.has-amount {
  color: #059669;
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

.status-badge.expired {
  background: #f1f5f9;
  color: #64748b;
}

/* Coupon Actions */
.coupon-actions {
  display: flex;
  gap: 8px;
  margin-left: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}

.action-btn:hover {
  background: #f1f5f9;
}

.action-btn.edit:hover {
  color: #2563eb;
  border-color: #2563eb;
}

.action-btn.delete:hover {
  color: #dc2626;
  border-color: #dc2626;
}

.action-btn svg {
  width: 16px;
  height: 16px;
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
  padding: 60px 20px;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  color: #94a3b8;
}

.empty-content svg {
  width: 64px;
  height: 64px;
  opacity: 0.5;
}

.empty-content p {
  margin: 0;
  font-size: 1rem;
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
  backdrop-filter: blur(4px);
}

/* Modal Content */
.modal-content {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-title {
  font-size: 1.375rem;
  font-weight: 700;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
}

.required {
  color: #dc2626;
}

.optional {
  color: #9ca3af;
  font-weight: 400;
}

.form-input {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1f2937;
  transition: all 0.15s;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.form-input::placeholder {
  color: #9ca3af;
}

.form-select {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.9375rem;
  color: #1f2937;
  background: #fff;
  cursor: pointer;
  transition: all 0.15s;
}

.form-select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

/* Form Row */
.form-row {
  display: flex;
  gap: 12px;
}

.form-row .flex-1 {
  flex: 1;
}

.form-row .flex-2 {
  flex: 2;
}

/* Input with suffix */
.input-with-suffix {
  position: relative;
}

.input-suffix {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  font-weight: 600;
  font-size: 0.9375rem;
  pointer-events: none;
}

.input-with-suffix .form-input {
  padding-right: 36px;
}

/* Date Input Wrapper */
.date-input-wrapper {
  position: relative;
}

.date-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #9ca3af;
  pointer-events: none;
}

/* Modal Footer */
.modal-footer {
  padding: 16px 24px 24px;
}

/* Submit Button */
.submit-btn {
  width: 100%;
  padding: 14px 24px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.submit-btn:hover {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}

.submit-btn:active {
  transform: translateY(0);
}

/* Modal Transitions */
.modal-enter-active {
  animation: modal-in 0.25s ease-out;
}

.modal-leave-active {
  animation: modal-in 0.2s ease-in reverse;
}

@keyframes modal-in {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Toast Notification */
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 20px;
  border-radius: 10px;
  font-size: 0.9375rem;
  font-weight: 500;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 2000;
  animation: slideIn 0.3s ease-out;
}

.toast.success {
  background: #d1fae5;
  color: #047857;
}

.toast.error {
  background: #fee2e2;
  color: #b91c1c;
}

.toast svg {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.toast-enter-active {
  animation: slideIn 0.3s ease-out;
}

.toast-leave-active {
  animation: slideIn 0.2s ease-in reverse;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}</style>
