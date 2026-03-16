<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
import userService from '../../services/userService.js'
import CountUp from '../../components/CountUp.vue'

const toast = useToast()

const router = useRouter()
const admin = useAdminStore()

// Get current logged in user name
const currentUser = computed(() => userService.getCurrentUser())
const adminName = computed(() => {
  const user = currentUser.value
  if (user?.name) return user.name
  // Fallback to first name from name field
  const name = user?.name || 'Admin'
  return name.split(' ')[0]
})

const animated = ref(false)

const stats = computed(() => {
  const t = admin.totals.value
  const tr = admin.trends.value

  return [
    {
      key: 'users',
      label: 'TOTAL USERS',
      raw: Number(t.totalUsers || 0) || 0,
      formatter: (n) => admin.formatted.fmtNumber(n),
      trend: tr.users,
      icon: 'fa-solid fa-users',
      tint: 'tint-blue',
      to: '/admin/users',
    },
    {
      key: 'shops',
      label: 'TOTAL SHOPS',
      raw: Number(t.totalShops || 0) || 0,
      formatter: (n) => admin.formatted.fmtNumber(n),
      trend: tr.shops,
      icon: 'fa-regular fa-building',
      tint: 'tint-purple',
      to: '/admin/shops',
    },
    {
      key: 'vehicles',
      label: 'TOTAL VEHICLES',
      raw: Number(t.totalVehicles || 0) || 0,
      formatter: (n) => admin.formatted.fmtNumber(n),
      trend: tr.vehicles,
      icon: 'fa-solid fa-motorcycle',
      tint: 'tint-orange',
      to: '/admin/vehicles',
    },
    {
      key: 'bookings',
      label: 'TOTAL BOOKINGS',
      raw: Number(t.totalBookings || 0) || 0,
      formatter: (n) => admin.formatted.fmtNumber(n),
      trend: tr.bookings,
      icon: 'fa-regular fa-calendar-check',
      tint: 'tint-green',
      to: '/admin/bookings',
    },
    {
      key: 'revenue',
      label: 'TOTAL REVENUE',
      raw: Number(admin.bookingGrossByDay.value.reduce((s, d) => s + Number(d.value || 0), 0)) || 0,
      formatter: (n) => admin.formatted.fmtMoneyCompact(n),
      trend: tr.revenue,
      icon: 'fa-solid fa-sack-dollar',
      tint: 'tint-cyan',
      to: '/admin/reports',
    },
  ]
})

const revenueSeries = computed(() => {
  const points = admin.bookingGrossByDay.value
  const maxValue = Math.max(1, ...points.map((p) => p.value || 0))
  return points.map((p) => ({
    ...p,
    pct: (Number(p.value || 0) / maxValue) * 100,
  }))
})

const fleetTypes = computed(() => {
  const counts = admin.vehicleTypeCounts.value
  const total = counts.motorbike + counts.bicycle + counts.car + counts.other
  const pct = (value) => (total ? Math.round((value / total) * 100) : 0)
  return [
    { key: 'motorbikes', label: 'Motorbikes', value: pct(counts.motorbike), icon: 'fa-solid fa-motorcycle', bar: 'bar-cyan' },
    { key: 'bicycles', label: 'Bicycles', value: pct(counts.bicycle), icon: 'fa-solid fa-person-biking', bar: 'bar-green' },
    { key: 'cars', label: 'Cars', value: pct(counts.car), icon: 'fa-solid fa-car-side', bar: 'bar-orange' },
  ]
})

const statusBadgeClass = (status) => {
  const value = String(status || '').trim().toUpperCase()
  if (value === 'VERIFIED' || value === 'ACTIVE') return 'badge badge-green'
  if (value === 'INACTIVE' || value === 'PENDING') return 'badge badge-yellow'
  if (value === 'SUSPENDED' || value === 'BLOCKED') return 'badge badge-red'
  return 'badge'
}

const vehicleIcon = (type) => {
  const value = String(type || '').toLowerCase()
  if (value.includes('bike') && !value.includes('bicycle')) return 'fa-solid fa-motorcycle'
  if (value.includes('bicycle')) return 'fa-solid fa-person-biking'
  if (value.includes('car')) return 'fa-solid fa-car-side'
  return 'fa-solid fa-shop'
}

const statusLabel = (status) => {
  const raw = String(status || '').trim()
  const upper = raw.toUpperCase()
  if (upper === 'VERIFIED' || upper === 'ACTIVE') return 'ACTIVE'
  if (upper === 'INACTIVE') return 'PENDING'
  if (upper === 'PENDING') return 'PENDING'
  if (upper === 'SUSPENDED') return 'SUSPENDED'
  if (upper === 'BLOCKED') return 'SUSPENDED'
  if (raw.toLowerCase() === 'active') return 'ACTIVE'
  if (raw.toLowerCase() === 'inactive') return 'PENDING'
  return upper || '—'
}

const ownerName = (shop) => shop?.owner?.name || shop?.owner_name || shop?.owner || '—'
const shopLocation = (shop) => shop?.location || shop?.address || shop?.city || '—'

const fleetCountByShop = computed(() => {
  const map = new Map()
  admin.state.vehicles.forEach((v) => {
    const key = String(v.shop_id || v.shop?.id || '')
    if (!key) return
    map.set(key, (map.get(key) || 0) + 1)
  })
  return map
})

const onVerify = (shop) => admin.setShopStatus(shop.id, 'active')
const onReactivate = (shop) => admin.setShopStatus(shop.id, 'active')

const openStat = (card) => {
  if (card?.to) router.push(card.to)
}

const openShops = () => router.push('/admin/shops')
const addNewShop = () => router.push('/admin/shops?create=1')

// Modal state for shop view/edit
const showView = ref(false)
const showEdit = ref(false)
const selectedShop = ref(null)
const editForm = ref({
  id: null,
  name: '',
  location: '',
  status: 'PENDING'
})

// View/Edit shop handlers - inline modals
const openView = (shop) => {
  selectedShop.value = shop
  showView.value = true
}

const openEdit = (shop) => {
  editForm.value = {
    id: shop.id,
    name: shop.name || '',
    location: shop.location || '',
    status: shop.status || 'PENDING'
  }
  showEdit.value = true
}

const closeView = () => {
  showView.value = false
  selectedShop.value = null
}

const closeEdit = () => {
  showEdit.value = false
  editForm.value = { id: null, name: '', location: '', status: 'PENDING' }
}

const submitEdit = async () => {
  const id = editForm.value.id
  if (!id) return
  const name = String(editForm.value.name || '').trim()
  if (!name) {
    toast.error('Shop name is required.')
    return
  }

  try {
    await admin.updateShop(id, editForm.value)
    toast.success('Shop updated successfully.')
    closeEdit()
    // trigger reload
    await admin.load({ force: true })
  } catch (err) {
    toast.error(err?.message || 'Failed to update shop.')
  }
}

const triggerAnimation = async () => {
  animated.value = false
  await nextTick()
  requestAnimationFrame(() => {
    animated.value = true
  })
}

onMounted(async () => {
  await admin.load().catch(() => {})
  triggerAnimation()
})

watch(
  () => admin.bookingGrossByDay.value.map((d) => d.value).join(','),
  () => triggerAnimation()
)
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Admin Dashboard Overview</h1>
        <p class="page-subtitle">
          Welcome back, {{ adminName }}. Here's what's happening today.
        </p>
      </div>

      <button type="button" class="btn btn-primary" @click="addNewShop">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        <span>Add New Shop</span>
      </button>
    </header>

    <div class="stat-grid">
      <article v-for="card in stats" :key="card.key" class="stat-card" role="button" tabindex="0" @click="openStat(card)">
        <div class="stat-top">
          <div class="stat-icon" :class="card.tint" aria-hidden="true">
            <i :class="card.icon"></i>
          </div>
          <div class="stat-trend" :class="card.trend >= 0 ? 'trend-up' : 'trend-down'">
            <span>{{ card.trend >= 0 ? '+' : '' }}{{ card.trend }}%</span>
          </div>
        </div>

        <div class="stat-value"><CountUp :value="card.raw" :formatter="card.formatter" /></div>
        <div class="stat-label">{{ card.label }}</div>
      </article>
    </div>

    <section class="card">
      <div class="card-head">
        <div>
          <h2 class="card-title">Recent Shop Registrations</h2>
          <p class="card-subtitle">Monitoring latest pending and verified shops</p>
        </div>
        <button type="button" class="btn btn-ghost" @click="openShops">View All</button>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>SHOP NAME</th>
              <th>OWNER</th>
              <th>LOCATION</th>
              <th class="num">FLEET SIZE</th>
              <th>STATUS</th>
              <th class="actions">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="shop in admin.recentShops.value" :key="shop.id">
              <td class="shop-cell">
                <span class="shop-icon" aria-hidden="true"><i :class="vehicleIcon(shop.vehicle_type)"></i></span>
                <div class="shop-meta">
                  <div class="shop-name">{{ shop.name }}</div>
                  <div class="shop-id">ID: #{{ shop.id }}</div>
                </div>
              </td>
              <td>{{ ownerName(shop) }}</td>
              <td class="muted">{{ shopLocation(shop) }}</td>
              <td class="num">
                <strong>{{ fleetCountByShop.get(String(shop.id)) || shop.fleet_size || 0 }}</strong> <span class="muted">VEHICLES</span>
              </td>
              <td><span :class="statusBadgeClass(shop.status)">{{ statusLabel(shop.status) }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="View" @click="openView(shop)"><i class="fa-regular fa-eye"></i></button>
                <button type="button" class="icon-action" title="Edit" @click="openEdit(shop)"><i class="fa-regular fa-pen-to-square"></i></button>
                <button
                  v-if="statusLabel(shop.status) === 'PENDING'"
                  type="button"
                  class="btn btn-soft"
                  @click="onVerify(shop)"
                >
                  Verify
                </button>
                <button
                  v-else-if="statusLabel(shop.status) === 'SUSPENDED'"
                  type="button"
                  class="btn btn-soft warn"
                  @click="onReactivate(shop)"
                >
                  Reactivate
                </button>
                <span v-else class="muted">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="grid-2">
      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Revenue Overview – Last 7 Days</h2>
          <div class="card-chip">Last 7 Days</div>
        </div>

        <div class="chart-bars" :class="{ animated }">
          <div v-for="point in revenueSeries" :key="point.key" class="bar-col">
            <div class="bar">
              <div class="bar-fill" :style="{ height: `${point.pct}%` }"></div>
            </div>
            <div class="bar-label">{{ point.label }}</div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Popular Fleet Types</h2>
        </div>

        <div class="fleet-list">
          <div v-for="item in fleetTypes" :key="item.key" class="fleet-row">
            <div class="fleet-left">
              <span class="fleet-icon" aria-hidden="true"><i :class="item.icon"></i></span>
              <span class="fleet-name">{{ item.label }}</span>
            </div>
            <div class="fleet-right">
              <div class="fleet-bar">
                <div class="fleet-bar-fill" :class="item.bar" :style="{ width: `${item.value}%` }"></div>
              </div>
              <div class="fleet-pct">{{ item.value }}%</div>
            </div>
          </div>
        </div>
      </section>

    <!-- View Modal -->
    <div v-if="showView" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="closeView">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">{{ selectedShop?.name || 'Shop Details' }}</div>
            <div class="modal-sub">Read-only view</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeView"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <label class="field"><span class="field-label">ID</span><input :value="selectedShop?.id || ''" readonly /></label>
            <label class="field"><span class="field-label">Name</span><input :value="selectedShop?.name || ''" readonly /></label>
            <label class="field"><span class="field-label">Owner</span><input :value="ownerName(selectedShop)" readonly /></label>
            <label class="field"><span class="field-label">Location</span><input :value="shopLocation(selectedShop)" readonly /></label>
            <label class="field"><span class="field-label">Status</span><input :value="statusLabel(selectedShop?.status)" readonly /></label>
            <label class="field span-2"><span class="field-label">Fleet Size</span><input :value="fleetCountByShop.get(String(selectedShop?.id)) || selectedShop?.fleet_size || 0" readonly /></label>
          </div>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-primary" @click="closeView">Close</button>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEdit" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Edit Shop</div>
            <div class="modal-sub">Update shop information.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeEdit"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Shop ID</span>
            <input v-model="editForm.id" readonly />
          </label>
          <label class="field">
            <span class="field-label">Name *</span>
            <input v-model="editForm.name" type="text" placeholder="Shop name" />
          </label>
          <label class="field">
            <span class="field-label">Location</span>
            <input v-model="editForm.location" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Status</span>
            <select v-model="editForm.status">
              <option value="PENDING">Pending</option>
              <option value="ACTIVE">Active</option>
              <option value="SUSPENDED">Suspended</option>
            </select>
          </label>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="closeEdit">Cancel</button>
          <button type="button" class="btn btn-primary" @click="submitEdit">Save Changes</button>
        </div>
      </div>
    </div>

    </section>
  </section>
</template>
