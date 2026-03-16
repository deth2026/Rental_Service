<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api.js'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
import ConfirmModal from '../../components/ConfirmModal.vue'

const admin = useAdminStore()
const toast = useToast()
const route = useRoute()

const page = ref(1)
const perPage = 8


const showView = ref(false)
const showEdit = ref(false)
const showDeleteConfirm = ref(false)

const selected = ref(null)
const deleteTarget = ref(null)

const editForm = ref({
  id: null,
  user_id: null,
  vehicle_id: null,
  coupon_id: null,
  start_date: '',
  total_days: 1,
  total_price: 0,
  status: 'pending',
  deposit_amount: 0,
  deposit_status: 'unpaid',
})

const query = computed(() => String(route.query.q || '').trim().toLowerCase())

const bookings = computed(() => admin.state.bookings || [])

const normalizedBookings = computed(() => bookings.value.map((b) => ({
  ...b,
  id: b.id,
  user_id: b.user_id,
  vehicle_id: b.vehicle_id,
  status: b.status || 'pending',
  start_date: b.start_date || '',
  total_days: Number(b.total_days || 0) || 0,
  total_price: Number(b.total_price ?? b.gross_amount ?? 0) || 0,
  deposit_amount: Number(b.deposit_amount || 0) || 0,
  deposit_status: b.deposit_status || 'unpaid',
  created_at: b.created_at || '',
})))

const filteredBookings = computed(() => {
  const q = query.value
  if (!q) return normalizedBookings.value
  return normalizedBookings.value.filter((b) => {
    const hay = `${b.id || ''} ${b.user_id || ''} ${b.vehicle_id || ''} ${b.status || ''} ${b.start_date || ''}`.toLowerCase()
    return hay.includes(q)
  })
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredBookings.value.length / perPage)))
watch(totalPages, (next) => { if (page.value > next) page.value = next })

const pagedBookings = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredBookings.value.slice(start, start + perPage)
})

const statusBadgeClass = (status) => {
  const s = String(status || '').toLowerCase()
  if (s === 'confirmed' || s === 'completed') return 'badge badge-green'
  if (s === 'pending') return 'badge badge-yellow'
  if (s === 'cancelled' || s === 'canceled') return 'badge badge-red'
  return 'badge'
}

const openView = (booking) => {
  selected.value = booking
  showView.value = true
}

const openEdit = (booking) => {
  selected.value = booking
  editForm.value = {
    id: booking.id,
    user_id: booking.user_id ?? null,
    vehicle_id: booking.vehicle_id ?? null,
    coupon_id: booking.coupon_id ?? null,
    start_date: booking.start_date || '',
    total_days: Number(booking.total_days || 1) || 1,
    total_price: Number(booking.total_price ?? booking.gross_amount ?? 0) || 0,
    status: booking.status || 'pending',
    deposit_amount: Number(booking.deposit_amount || 0) || 0,
    deposit_status: booking.deposit_status || 'unpaid',
  }
  showEdit.value = true
}

const closeModals = () => {
  showView.value = false
  showEdit.value = false
}

const submitEdit = async () => {
  const id = editForm.value.id
  if (!id) return
  try {
    await api.put(`/bookings/${id}`, { ...editForm.value })
    toast.success('Booking updated successfully.')
    closeModals()
    await admin.load({ force: true }).catch(() => {})
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update booking.')
  }
}

const requestDelete = (booking) => {
  deleteTarget.value = booking
  showDeleteConfirm.value = true
}

const deleteBookingMessage = computed(() => {
  const id = deleteTarget.value?.id
  return id ? `Are you sure you want to delete booking #${id}?` : 'Are you sure you want to delete this booking?'
})

const confirmDelete = async () => {
  const booking = deleteTarget.value
  if (!booking?.id) return
  try {
    await api.delete(`/bookings/${booking.id}`)
    toast.success('Booking deleted.')
    await admin.load({ force: true }).catch(() => {})
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to delete booking.')
  } finally {
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

onMounted(async () => {
  await admin.load().catch(() => {})
})
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Booking Management</h1>
        <p class="page-subtitle">View and manage all bookings across the platform.</p>
      </div>
    </header>

    <section class="card">
      <div class="card-head">
        <div>
          <h2 class="card-title">Bookings</h2>
          <p class="card-subtitle">{{ admin.formatted.fmtNumber(filteredBookings.length) }} total</p>
        </div>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>BOOKING</th>
              <th class="num">USER ID</th>
              <th class="num">VEHICLE ID</th>
              <th>START DATE</th>
              <th class="num">TOTAL PRICE</th>
              <th>STATUS</th>
              <th class="actions">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in pagedBookings" :key="b.id">
              <td class="shop-cell">
                <span class="shop-icon square" aria-hidden="true"><i class="fa-regular fa-calendar-check"></i></span>
                <div class="shop-meta">
                  <div class="shop-name">Booking #{{ b.id }}</div>
                  <div class="shop-id">Days: {{ b.total_days || 0 }} • Created: {{ String(b.created_at || '').slice(0, 10) || '—' }}</div>
                </div>
              </td>
              <td class="num">{{ b.user_id }}</td>
              <td class="num">{{ b.vehicle_id }}</td>
              <td class="muted">{{ b.start_date || '—' }}</td>
              <td class="num"><strong>${{ Number(b.total_price || 0).toFixed(2) }}</strong></td>
              <td><span :class="statusBadgeClass(b.status)">{{ String(b.status || '').toUpperCase() }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="View" @click="openView(b)"><i class="fa-regular fa-eye"></i></button>
                <button type="button" class="icon-action" title="Edit" @click="openEdit(b)"><i class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="icon-action" title="Delete" @click="requestDelete(b)"><i class="fa-regular fa-trash-can"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="muted">
          SHOWING {{ (page - 1) * perPage + 1 }} TO {{ Math.min(page * perPage, filteredBookings.length) }} OF
          {{ admin.formatted.fmtNumber(filteredBookings.length) }} BOOKINGS
        </div>
        <div class="pager">
          <button type="button" class="pager-btn" :disabled="page === 1" @click="page -= 1">‹</button>
          <button type="button" class="pager-btn is-active">{{ page }}</button>
          <button type="button" class="pager-btn" :disabled="page === totalPages" @click="page += 1">›</button>
        </div>
      </div>
    </section>

    <div v-if="showView" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="showView = false">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Booking Details</div>
            <div class="modal-sub">Read-only view</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="showView = false"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
          <pre class="code-block">{{ JSON.stringify(selected, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <div v-if="showEdit" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Edit Booking</div>
            <div class="modal-sub">Update booking fields.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeModals"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Status</span>
            <input v-model="editForm.status" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Start Date</span>
            <input v-model="editForm.start_date" type="date" />
          </label>
          <label class="field">
            <span class="field-label">Total Days</span>
            <input v-model.number="editForm.total_days" type="number" min="1" />
          </label>
          <label class="field">
            <span class="field-label">Total Price</span>
            <input v-model.number="editForm.total_price" type="number" min="0" step="0.01" />
          </label>
          <label class="field">
            <span class="field-label">Deposit Amount</span>
            <input v-model.number="editForm.deposit_amount" type="number" min="0" step="0.01" />
          </label>
          <label class="field">
            <span class="field-label">Deposit Status</span>
            <input v-model="editForm.deposit_status" type="text" />
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="closeModals">Cancel</button>
          <button type="button" class="btn btn-primary" @click="submitEdit">Save Changes</button>
        </div>
      </div>
    </div>

    <ConfirmModal
      v-model="showDeleteConfirm"
      title="Delete Booking"
      :message="deleteBookingMessage"
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
