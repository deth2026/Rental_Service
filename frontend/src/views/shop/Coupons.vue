<script setup>
import { ref, onMounted, watch } from 'vue'
import { couponApi, shopApi } from '@/services/api'
import { getSessionUser } from '@/services/auth'
import '../../css/Coupons.css'

const props = defineProps({
  shopId: {
    type: [String, Number],
    default: null
  }
})

const loading = ref(true)
const error = ref(null)
const coupons = ref([])
const ownerShops = ref([])
const ownerShopsLoading = ref(false)

const normalizeActiveFlag = (value) => {
  if (value === undefined || value === null || value === '') return true
  if (value === false || value === 0 || value === '0' || value === 'false') return false
  return true
}

const normalizeCollection = (response) => {
  const payload = response?.data?.data || response?.data || []
  return Array.isArray(payload) ? payload : []
}

const getDefaultShopId = () => {
  if (props.shopId) {
    const parsed = Number(props.shopId)
    if (Number.isFinite(parsed) && parsed > 0) return parsed
  }

  if (ownerShops.value.length) {
    const first = ownerShops.value[0]
    const parsed = Number(first?.id)
    if (Number.isFinite(parsed) && parsed > 0) return parsed
  }

  return null
}

const parseShopId = (value) => {
  const parsed = Number(value)
  if (Number.isFinite(parsed) && parsed > 0) {
    return parsed
  }
  return null
}

const resolveShopFromForm = () => parseShopId(form.value.shopId) ?? getDefaultShopId()

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
    const response = await couponApi.getAll(props.shopId ? { shop_id: props.shopId } : {})
    const data = normalizeCollection(response)
    coupons.value = data.map((c) => ({
      id: c.id,
      code: c.code || '',
      discountPercent: c.discount_percent ?? null,
      discountAmount: c.discount_amount ?? null,
      createDate: c.valid_from || c.created_at || '',
      expireDate: c.valid_until || '',
      isActive: normalizeActiveFlag(c.is_active),
      shopId: parseShopId(c.shop_id)
    }))
  } catch (err) {
    console.error('Error fetching coupons:', err)
    error.value = 'Failed to load coupons'
  } finally {
    loading.value = false
  }
}

const fetchOwnerShops = async () => {
  const user = getSessionUser()
  if (!user?.id) {
    ownerShops.value = []
    return
  }

  try {
    ownerShopsLoading.value = true
    const response = await shopApi.getAll({ owner_id: user.id, active_only: true })
    ownerShops.value = normalizeCollection(response)
    updateDefaultShopSelection()
  } catch (err) {
    console.error('Failed to load owner shops:', err)
    ownerShops.value = []
  } finally {
    ownerShopsLoading.value = false
  }
}

onMounted(async () => {
  await fetchOwnerShops()
  await fetchCoupons()
})


// Create / Edit modal
const showModal = ref(false)
const editId = ref(null)
const form = ref({
  code: '',
  discountPercent: '',
  discountAmount: '',
  createDate: '',
  expireDate: '',
  isActive: true,
  shopId: getDefaultShopId()
})

const updateDefaultShopSelection = () => {
  const defaultShopId = getDefaultShopId()
  if (defaultShopId && parseShopId(form.value.shopId) !== defaultShopId) {
    form.value.shopId = defaultShopId
  }
}

watch(
  () => props.shopId,
  () => {
    updateDefaultShopSelection()
    fetchCoupons()
  }
)

watch(
  ownerShops,
  () => {
    updateDefaultShopSelection()
  }
)

const openCreate = () => {
  editId.value = null
  const today = new Date().toISOString().split('T')[0]
  form.value = {
    code: '',
    discountPercent: '',
    discountAmount: '',
    createDate: today,
    expireDate: '',
    isActive: true,
    shopId: getDefaultShopId()
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
  form.value.shopId = c.shopId ?? getDefaultShopId()
  showModal.value = true
}

const save = async () => {
  if (!ownerShops.value.length && !ownerShopsLoading.value) {
    await fetchOwnerShops()
  }
  const shopId = resolveShopFromForm()
  if (!form.value.code || !form.value.expireDate || !shopId) {
    showToast('Please fill in Code, Expire Date, and ensure a shop is available', 'error')
    return
  }
  const payload = {
    shop_id: shopId,
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
