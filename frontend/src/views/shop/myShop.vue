<script setup>
import { onMounted, reactive, ref } from 'vue'
import { shopApi } from '@/services/api'

const shop = ref(null)
const ownerName = ref('')

const showCreateModal = ref(false)
const showSuccessPopup = ref(false)
const loading = ref(false)
const error = ref('')

const createForm = reactive({
  name: '',
  description: '',
  address: '',
  status: 'active'
})

const getCachedShop = (ownerId) => {
  if (!ownerId) return null
  try {
    const raw = localStorage.getItem(`myshop_cache_${ownerId}`)
    if (!raw) return null
    const parsed = JSON.parse(raw)
    return parsed && typeof parsed === 'object' ? parsed : null
  } catch {
    return null
  }
}

const setCachedShop = (ownerId, shopData) => {
  if (!ownerId) return
  try {
    if (!shopData) {
      localStorage.removeItem(`myshop_cache_${ownerId}`)
      return
    }
    localStorage.setItem(`myshop_cache_${ownerId}`, JSON.stringify(shopData))
  } catch {
    // Ignore cache write errors.
  }
}

const getStoredUser = () => {
  try {
    const rawUser = localStorage.getItem('user')
    if (!rawUser) return null
    const parsed = JSON.parse(rawUser)
    return parsed && typeof parsed === 'object' ? parsed : null
  } catch {
    return null
  }
}

const getUserId = () => {
  const rawUser = localStorage.getItem('user')
  if (!rawUser) return 1

  try {
    const parsed = JSON.parse(rawUser)
    return parsed.id || 1
  } catch {
    return 1
  }
}

const asArray = (payload) => payload?.data || payload || []

const formatDateTime = (value) => {
  if (!value) return 'N/A'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return String(value)
  return date.toLocaleDateString()
}

const loadMyShop = async () => {
  const ownerId = getUserId()
  const storedUser = getStoredUser()
  ownerName.value = storedUser?.name || 'N/A'
  const cachedShop = getCachedShop(ownerId)
  if (cachedShop) {
    shop.value = cachedShop
  }

  try {
    const response = await shopApi.getAll()
    const shops = asArray(response.data)
    const myShops = shops.filter((item) => Number(item.owner_id) === Number(ownerId))

    if (!myShops.length) {
      if (!cachedShop) {
        shop.value = null
      }
      setCachedShop(ownerId, null)
      return
    }

    myShops.sort((a, b) => {
      const aTime = a.created_at ? new Date(a.created_at).getTime() : 0
      const bTime = b.created_at ? new Date(b.created_at).getTime() : 0

      if (bTime !== aTime) return bTime - aTime
      return Number(b.id || 0) - Number(a.id || 0)
    })

    shop.value = myShops[0]
    setCachedShop(ownerId, shop.value)
    ownerName.value = shop.value?.owner_name || shop.value?.owner?.name || storedUser?.name || 'N/A'
  } catch (e) {
    console.error('Failed to load shop', e)
    if (!cachedShop) {
      shop.value = null
    }
    ownerName.value = storedUser?.name || 'N/A'
  }
}

const resetForm = () => {
  createForm.name = ''
  createForm.description = ''
  createForm.address = ''
  createForm.status = 'active'
  error.value = ''
}

const openCreateModal = () => {
  resetForm()
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  error.value = ''
}

const createShop = async () => {
  loading.value = true
  error.value = ''

  try {
    const payload = {
      owner_id: getUserId(),
      name: createForm.name.trim(),
      description: createForm.description.trim(),
      address: createForm.address.trim(),
      status: createForm.status
    }

    const { data: created } = await shopApi.create(payload)
    const createdShop = created?.data || created || null
    if (createdShop && typeof createdShop === 'object') {
      shop.value = createdShop
      setCachedShop(getUserId(), createdShop)
    }
    await loadMyShop()

    showCreateModal.value = false
    showSuccessPopup.value = true
    resetForm()

    setTimeout(() => {
      showSuccessPopup.value = false
    }, 1400)
  } catch (e) {
    error.value = e?.response?.data?.message || 'Create shop failed.'
    console.error('Create shop error', e)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadMyShop()
})
</script>

<template>
  <div class="myshop-page">
    <div class="page-header">
      <h1>My Shop</h1>
      <button class="create-btn" @click="openCreateModal">
        Create Shop
      </button>
    </div>

    <div v-if="!shop" class="empty-state">
      <h3>No shop information yet</h3>
      <p>Click Create Shop and fill in your shop information.</p>
    </div>

    <div v-else class="shop-card">
      <div class="card-title-row">
        <h2>{{ shop.name || 'My Shop' }}</h2>
        <span :class="['status-badge', (shop.status || 'inactive').toLowerCase()]">
          {{ shop.status || 'inactive' }}
        </span>
      </div>

      <div class="shop-grid">
        <div class="field"><b>Shop Name</b><span>{{ shop.name || 'N/A' }}</span></div>
        <div class="field">
          <b>Status</b>
          <span :class="['status-text', (shop.status || 'inactive').toLowerCase()]">
            {{ shop.status || 'N/A' }}
          </span>
        </div>
        <div class="field"><b>Owner Name</b><span>{{ ownerName }}</span></div>
        <div class="field"><b>created_at</b><span>{{ formatDateTime(shop.created_at) }}</span></div>
        <div class="field field-wide"><b>Address</b><span>{{ shop.address || 'N/A' }}</span></div>
        <div class="field field-wide"><b>Description</b><span>{{ shop.description || 'N/A' }}</span></div>
      </div>
    </div>

    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-title-wrap">
            <p class="modal-kicker">Setup your business</p>
            <h2>Create Shop</h2>
          </div>
          <button class="close-btn" @click="closeCreateModal" aria-label="Close">x</button>
        </div>

        <form class="shop-form" @submit.prevent="createShop">
          <div class="form-grid">
            <label>
              Shop Name *
              <input v-model="createForm.name" required type="text" placeholder="Enter shop name" />
            </label>

            <label>
              Status
              <select v-model="createForm.status">
                <option value="active">active</option>
                <option value="inactive">inactive</option>
              </select>
            </label>

            <label class="wide">
              Address *
              <input v-model="createForm.address" required type="text" placeholder="Enter address" />
            </label>

            <label class="wide">
              Description
              <textarea v-model="createForm.description" rows="4" placeholder="Tell customers about your shop..."></textarea>
            </label>
          </div>

          <p v-if="error" class="error-text">{{ error }}</p>

          <div class="actions">
            <button type="button" class="btn-cancel" @click="closeCreateModal">Cancel</button>
            <button type="submit" class="btn-submit" :disabled="loading">
              {{ loading ? 'Creating...' : 'Create Shop' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showSuccessPopup" class="success-toast" role="status" aria-live="polite">
      <span class="toast-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <path d="M20 6L9 17l-5-5"></path>
        </svg>
      </span>
      <div class="toast-content">
        <strong>Success</strong>
        <p>Your shop was created.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.myshop-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px;
  background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
  border-radius: 16px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.page-header h1 {
  margin: 0;
  font-size: 30px;
  color: #1e293b;
}

.create-btn {
  border: 0;
  border-radius: 999px;
  background: #1d4ed8;
  color: #fff;
  padding: 11px 20px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 20px rgba(29, 78, 216, 0.25);
}

.create-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.empty-state {
  border: 1px dashed #bfdbfe;
  background: #f0f9ff;
  border-radius: 16px;
  padding: 48px 20px;
  text-align: center;
}

.empty-state h3 {
  margin: 0 0 8px;
  color: #0f172a;
}

.empty-state p {
  margin: 0;
  color: #475569;
}

.shop-card {
  border: 1px solid #dbeafe;
  background: #fff;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.card-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.card-title-row h2 {
  margin: 0;
  font-size: 22px;
  color: #0f172a;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border-radius: 999px;
  padding: 7px 12px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: capitalize;
  border: 1px solid transparent;
}

.status-badge::before {
  content: '';
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-badge.active {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
  color: #166534;
  border-color: #86efac;
}

.status-badge.active::before {
  background: #16a34a;
  box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
}

.status-badge.inactive {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  color: #991b1b;
  border-color: #fca5a5;
}

.status-badge.inactive::before {
  background: #dc2626;
  box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
}

.status-text {
  font-weight: 700;
  text-transform: capitalize;
}

.status-text.active {
  color: #15803d;
}

.status-text.inactive {
  color: #b91c1c;
}

.shop-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.field {
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  background: #ffffff;
}

.field b {
  font-size: 12px;
  color: #64748b;
}

.field span {
  color: #0f172a;
  font-size: 14px;
  word-break: break-word;
}

.field-wide {
  grid-column: span 2;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 1000;
}

.modal-card {
  width: min(820px, 100%);
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border-radius: 22px;
  overflow: hidden;
  border: 1px solid #dbeafe;
  box-shadow: 0 30px 70px rgba(2, 6, 23, 0.35);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px 18px;
  border-bottom: 1px solid #dbeafe;
  background: linear-gradient(90deg, #ecfeff 0%, #eff6ff 100%);
}

.modal-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 36px;
  line-height: 1.05;
}

.modal-title-wrap {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.modal-kicker {
  margin: 0;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #0ea5a0;
}

.close-btn {
  border: 1px solid #bfdbfe;
  background: #ffffff;
  color: #0f172a;
  border-radius: 12px;
  width: 38px;
  height: 38px;
  font-size: 24px;
  line-height: 1;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #eff6ff;
  border-color: #93c5fd;
}

.shop-form {
  padding: 20px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  padding: 16px;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
}

.form-grid label {
  display: flex;
  flex-direction: column;
  gap: 8px;
  font-size: 14px;
  color: #334155;
  font-weight: 600;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 12px 13px;
  font-size: 14px;
  width: 100%;
  box-sizing: border-box;
  background: #f8fafc;
  transition: all 0.2s ease;
}

.form-grid input:focus,
.form-grid select:focus,
.form-grid textarea:focus {
  outline: none;
  border-color: #22d3ee;
  background: #ffffff;
  box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.16);
}

.wide {
  grid-column: span 2;
}

.error-text {
  margin: 0;
  color: #dc2626;
  font-size: 13px;
  font-weight: 600;
}

.actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 16px;
}

.btn-cancel,
.btn-submit {
  border: 0;
  border-radius: 12px;
  padding: 11px 18px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel {
  background: #e2e8f0;
  color: #334155;
}

.btn-cancel:hover {
  background: #cbd5e1;
}

.btn-submit {
  background: #1d4ed8;
  color: #fff;
  min-width: 140px;
}

.btn-submit:hover {
  background: #1e40af;
  transform: translateY(-1px);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.success-toast {
  position: fixed;
  right: 20px;
  top: 18px;
  z-index: 1200;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  width: min(320px, calc(100vw - 24px));
  background: #ffffff;
  border: 1px solid #bfdbfe;
  border-left: 4px solid #1d4ed8;
  border-radius: 14px;
  padding: 12px;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.18);
  animation: toastIn 0.24s ease-out;
}

.toast-icon {
  width: 30px;
  height: 30px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #dbeafe;
  color: #1d4ed8;
  flex: 0 0 30px;
}

.toast-icon svg {
  width: 16px;
  height: 16px;
}

.toast-content {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.toast-content strong {
  display: block;
  font-size: 13px;
  color: #0f172a;
}

.toast-content p {
  margin: 0;
  font-size: 13px;
  color: #475569;
}

@keyframes toastIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 760px) {
  .myshop-page {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .shop-grid,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .field-wide,
  .wide {
    grid-column: span 1;
  }

  .shop-form {
    padding: 14px;
  }

  .modal-header {
    padding: 16px;
  }

  .modal-header h2 {
    font-size: 28px;
  }
}
</style>
