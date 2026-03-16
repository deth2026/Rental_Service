<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { couponApi } from '../../services/api.js'
import { useToast } from '../../composables/useToast.js'
import ConfirmModal from '../../components/ConfirmModal.vue'

const toast = useToast()
const route = useRoute()

// Format date to YYYY-MM-DD
const formatDate = (dateStr) => {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return d.toISOString().split('T')[0]
}

const loading = ref(false)
const items = ref([])

const page = ref(1)
const perPage = 8

const showCreate = ref(false)
const showEdit = ref(false)
const showView = ref(false)
const showDeleteConfirm = ref(false)

const selected = ref(null)
const deleteTarget = ref(null)

const form = ref({
  id: null,
  code: '',
  discount_percent: null,
  discount_amount: null,
  minimum_amount: null,
  valid_from: '',
  valid_until: '',
  usage_limit: null,
  is_active: true,
})

const query = computed(() => String(route.query.q || '').trim().toLowerCase())

const load = async () => {
  loading.value = true
  try {
    const { data } = await couponApi.getAll()
    const payload = data?.data?.data || data?.data || data
    items.value = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to load coupons.')
    items.value = []
  } finally {
    loading.value = false
  }
}

const filtered = computed(() => {
  const q = query.value
  if (!q) return items.value
  return items.value.filter((c) => `${c.code || ''} ${c.id || ''}`.toLowerCase().includes(q))
})

const totalPages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
watch(totalPages, (next) => { if (page.value > next) page.value = next })

const paged = computed(() => {
  const start = (page.value - 1) * perPage
  return filtered.value.slice(start, start + perPage)
})

const openCreate = () => {
  showCreate.value = true
  showEdit.value = false
  form.value = {
    id: null,
    code: '',
    discount_percent: null,
    discount_amount: null,
    minimum_amount: null,
    valid_from: '',
    valid_until: '',
    usage_limit: null,
    is_active: true,
  }
}

const openView = (c) => { selected.value = c; showView.value = true }

const openEdit = (c) => {
  selected.value = c
  showEdit.value = true
  showCreate.value = false
  form.value = {
    id: c.id,
    code: c.code || '',
    discount_percent: c.discount_percent ?? null,
    discount_amount: c.discount_amount ?? null,
    minimum_amount: c.minimum_amount ?? null,
    valid_from: c.valid_from || '',
    valid_until: c.valid_until || '',
    usage_limit: c.usage_limit ?? null,
    is_active: Boolean(c.is_active),
  }
}

const closeModals = () => {
  showCreate.value = false
  showEdit.value = false
  showView.value = false
}

const submitCreate = async () => {
  const code = String(form.value.code || '').trim()
  if (!code) { toast.error('Coupon code is required.'); return }
  if (!form.value.valid_from || !form.value.valid_until) { toast.error('Valid from / until is required.'); return }
  try {
    await couponApi.create({ ...form.value, code })
    toast.success('Coupon created successfully.')
    closeModals()
    await load()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to create coupon.')
  }
}

const submitEdit = async () => {
  const id = form.value.id
  if (!id) return
  const code = String(form.value.code || '').trim()
  if (!code) { toast.error('Coupon code is required.'); return }
  try {
    await couponApi.update(id, { ...form.value, code })
    toast.success('Coupon updated successfully.')
    closeModals()
    await load()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update coupon.')
  }
}

const requestDelete = (c) => { deleteTarget.value = c; showDeleteConfirm.value = true }


const deleteMessage = computed(() => {
  const code = String(deleteTarget.value?.code || '').trim()
  return code ? `Are you sure you want to delete coupon "${code}"?` : 'Are you sure you want to delete this coupon?'
})

const confirmDelete = async () => {
  const c = deleteTarget.value
  if (!c?.id) return
  try {
    await couponApi.delete(c.id)
    toast.success('Coupon deleted.')
    await load()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to delete coupon.')
  } finally {
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

onMounted(load)
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Coupon Management</h1>
        <p class="page-subtitle">Create and manage discount coupons.</p>
      </div>
      <button type="button" class="btn btn-primary" @click="openCreate">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        <span>Add Coupon</span>
      </button>
    </header>

    <section class="card">
      <div class="card-head">
        <div>
          <h2 class="card-title">Coupons</h2>
          <p class="card-subtitle">{{ filtered.length }} total</p>
        </div>
      </div>

      <div v-if="loading" class="muted">Loading…</div>
      <div v-else class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>CODE</th>
              <th class="num">PERCENT</th>
              <th class="num">AMOUNT</th>
              <th>VALID</th>
              <th>ACTIVE</th>
              <th class="actions">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in paged" :key="c.id">
              <td class="shop-cell">
                <span class="shop-icon square" aria-hidden="true"><i class="fa-solid fa-ticket"></i></span>
                <div class="shop-meta">
                  <div class="shop-name">{{ c.code }}</div>
                  <div class="shop-id">ID: {{ c.id }}</div>
                </div>
              </td>
              <td class="num">{{ c.discount_percent ?? '—' }}</td>
              <td class="num">{{ c.discount_amount ?? '—' }}</td>
              <td class="muted">{{ formatDate(c.valid_from) }} → {{ formatDate(c.valid_until) }}</td>
              <td><span :class="c.is_active ? 'badge badge-green' : 'badge badge-red'">{{ c.is_active ? 'YES' : 'NO' }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="View" @click="openView(c)"><i class="fa-regular fa-eye"></i></button>
                <button type="button" class="icon-action" title="Edit" @click="openEdit(c)"><i class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="icon-action" title="Delete" @click="requestDelete(c)"><i class="fa-regular fa-trash-can"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="muted">
          SHOWING {{ (page - 1) * perPage + 1 }} TO {{ Math.min(page * perPage, filtered.length) }} OF {{ filtered.length }} COUPONS
        </div>
        <div class="pager">
          <button type="button" class="pager-btn" :disabled="page === 1" @click="page -= 1">‹</button>
          <button type="button" class="pager-btn is-active">{{ page }}</button>
          <button type="button" class="pager-btn" :disabled="page === totalPages" @click="page += 1">›</button>
        </div>
      </div>
    </section>

    <div v-if="showCreate || showEdit" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">{{ showEdit ? 'Edit Coupon' : 'Create Coupon' }}</div>
            <div class="modal-sub">Set discount rules and validity.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeModals"><i class="fa-solid fa-xmark"></i></button>
        </div>


        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Code</span>
            <input v-model="form.code" type="text" placeholder="SAVE10" />
          </label>
          <label class="field">
            <span class="field-label">Usage Limit</span>
            <input v-model.number="form.usage_limit" type="number" min="0" />
          </label>
          <label class="field">
            <span class="field-label">Discount Percent</span>
            <input v-model.number="form.discount_percent" type="number" min="0" step="0.01" />
          </label>
          <label class="field">
            <span class="field-label">Discount Amount</span>
            <input v-model.number="form.discount_amount" type="number" min="0" step="0.01" />
          </label>
          <label class="field">
            <span class="field-label">Minimum Amount</span>
            <input v-model.number="form.minimum_amount" type="number" min="0" step="0.01" />
          </label>
          <label class="field">
            <span class="field-label">Active</span>
            <select v-model="form.is_active">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </label>
          <label class="field">
            <span class="field-label">Valid From</span>
            <input v-model="form.valid_from" type="date" />
          </label>
          <label class="field">
            <span class="field-label">Valid Until</span>
            <input v-model="form.valid_until" type="date" />
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="closeModals">Cancel</button>
          <button v-if="showEdit" type="button" class="btn btn-primary" @click="submitEdit">Save Changes</button>
          <button v-else type="button" class="btn btn-primary" @click="submitCreate">Create Coupon</button>
        </div>
      </div>
    </div>

    <div v-if="showView" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="showView = false">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Coupon Details</div>
            <div class="modal-sub">Read-only view</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="showView = false"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
          <pre class="code-block">{{ JSON.stringify(selected, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <ConfirmModal
      v-model="showDeleteConfirm"
      title="Delete Coupon"
      :message="deleteMessage"
      cancel-text="Cancel"
      confirm-text="Yes"
      variant="danger"
      @confirm="confirmDelete"
    />
  </section>
</template>

<style scoped>
.code-block {
  margin: 0;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid var(--mp-border);
  background: rgba(148, 163, 184, 0.08);
  overflow: auto;
  max-height: 52vh;
  font-size: 12px;
}
</style>
