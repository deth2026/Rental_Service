<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
import { cityApi } from '../../services/api.js'
import ConfirmModal from '../../components/ConfirmModal.vue'

const admin = useAdminStore()
const route = useRoute()
const router = useRouter()
const toast = useToast()

const activeTab = ref('all')
const sortKey = ref('newest')
const showSortMenu = ref(false)
const page = ref(1)
const perPage = 6

const cities = ref([])
const loadingCities = ref(false)

const showCreate = ref(false)
const createForm = ref({ name: '', city_id: '', address: '', location: '', phone: '', description: '', img_url: '' })
const createImageFile = ref(null)
const createImagePreview = ref('')

const showEdit = ref(false)
const editForm = ref({ id: null, name: '', city_id: '', address: '', location: '', phone: '', description: '', img_url: '' })
const editImageFile = ref(null)
const editImagePreview = ref('')

const showDeleteConfirm = ref(false)
const deleteTarget = ref(null)
const isCreating = ref(false)
const isUpdating = ref(false)
const deletingShopId = ref(null)
const statusChangingShopId = ref(null)

const normalizedQuery = computed(() => String(route.query.q || '').trim().toLowerCase())
const focusShopId = computed(() => Number(route.query.focusShop || 0))

const deleteShopMessage = computed(() => {
  const name = String(deleteTarget.value?.name || '').trim()
  if (name) return `Are you sure you want to delete "${name}"?`
  return 'Are you sure you want to delete this shop?'
})

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

const matchesTab = (shop) => {
  const label = statusLabel(shop.status)
  if (activeTab.value === 'all') return true
  if (activeTab.value === 'active') return label === 'ACTIVE'
  if (activeTab.value === 'pending') return label === 'PENDING'
  return true
}

const ownerName = (shop) => shop?.owner?.name || shop?.owner_name || shop?.owner || '—'
const shopLocation = (shop) => shop?.location || shop?.address || shop?.city || '—'

const matchesQuery = (shop) => {
  const q = normalizedQuery.value
  if (!q) return true
  const hay = `${shop.name || ''} ${ownerName(shop)} ${shopLocation(shop)} ${shop.id || ''}`.toLowerCase()
  return hay.includes(q)
}

const sortedShops = computed(() => {
  const items = admin.state.shops.filter((s) => matchesTab(s) && matchesQuery(s))
  if (sortKey.value === 'newest') {
    return [...items].sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
  }
  if (sortKey.value === 'oldest') {
    return [...items].sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0))
  }
  if (sortKey.value === 'name_asc') {
    return [...items].sort((a, b) => (a.name || '').localeCompare(b.name || ''))
  }
  if (sortKey.value === 'name_desc') {
    return [...items].sort((a, b) => (b.name || '').localeCompare(a.name || ''))
  }
  return items
})

const toDate = (value) => {
  if (!value) return null
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? null : date
}

const reportSummary = computed(() => {
  const shops = admin.state.shops || []
  const vehicles = admin.state.vehicles || []
  const bookings = admin.state.bookings || []
  const now = new Date()
  const monthStart = new Date(now.getFullYear(), now.getMonth(), 1)

  const activeShops = shops.filter((shop) => statusLabel(shop.status) === 'ACTIVE').length
  const pendingShops = shops.filter((shop) => statusLabel(shop.status) === 'PENDING').length
  const bookingsThisMonth = bookings.filter((booking) => {
    const bookingDate = toDate(booking?.start_date || booking?.created_at || booking?.booking_date || booking?.date)
    return bookingDate && bookingDate >= monthStart && bookingDate <= now
  }).length

  return {
    activeShops,
    pendingShops,
    totalVehicles: vehicles.length,
    bookingsThisMonth,
  }
})

const reportMonthSeries = computed(() => {
  const now = new Date()
  const buckets = []

  for (let i = 5; i >= 0; i -= 1) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
    const key = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
    buckets.push({
      key,
      label: date.toLocaleDateString('en-US', { month: 'short' }),
      count: 0,
    })
  }

  const byKey = new Map(buckets.map((bucket) => [bucket.key, bucket]))
  ;(admin.state.shops || []).forEach((shop) => {
    const created = toDate(shop?.created_at || shop?.updated_at)
    if (!created) return
    const key = `${created.getFullYear()}-${String(created.getMonth() + 1).padStart(2, '0')}`
    const bucket = byKey.get(key)
    if (bucket) bucket.count += 1
  })

  const maxCount = Math.max(1, ...buckets.map((bucket) => bucket.count))
  return buckets.map((bucket) => ({
    ...bucket,
    heightPercent: bucket.count > 0 ? Math.max(16, Math.round((bucket.count / maxCount) * 100)) : 0,
  }))
})

const reportGrowthLabel = computed(() => {
  const series = reportMonthSeries.value
  if (series.length < 2) return 'No change'
  const current = Number(series[series.length - 1]?.count || 0)
  const previous = Number(series[series.length - 2]?.count || 0)

  if (previous <= 0) {
    if (current <= 0) return 'No change'
    return '+100% vs last month'
  }

  const growth = Math.round(((current - previous) / previous) * 100)
  const sign = growth > 0 ? '+' : ''
  return `${sign}${growth}% vs last month`
})

const reportLastUpdateLabel = computed(() => {
  const latestDate = (admin.state.shops || [])
    .map((shop) => toDate(shop?.updated_at || shop?.created_at))
    .filter(Boolean)
    .sort((a, b) => b - a)[0]

  if (!latestDate) return 'No update yet'
  return latestDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
})

// Download functionality
const downloadShops = () => {
  const shops = sortedShops.value
  if (!shops.length) {
    toast.error('No shops to download')
    return
  }
  // Create CSV content
  const headers = ['ID', 'Name', 'Owner', 'Address', 'Phone', 'Status', 'Created At']
  const rows = shops.map(s => [
    s.id,
    s.name,
    ownerName(s),
    s.address || '',
    s.phone || '',
    s.status || '',
    s.created_at || ''
  ])
  const csvContent = [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `shops_export_${new Date().toISOString().split('T')[0]}.csv`
  link.click()
  URL.revokeObjectURL(url)
  toast.success(`Exported ${shops.length} shops`)
}


const totalPages = computed(() => Math.max(1, Math.ceil(sortedShops.value.length / perPage)))
const showingStart = computed(() => (sortedShops.value.length ? ((page.value - 1) * perPage) + 1 : 0))

watch(totalPages, (next) => {
  if (page.value > next) page.value = next
})

const pagedShops = computed(() => {
  const start = (page.value - 1) * perPage
  return sortedShops.value.slice(start, start + perPage)
})

const bookingCountByShop = computed(() => {
  const map = new Map()
  admin.state.bookings.forEach((b) => {
    const key = String(b.shop_id || '')
    map.set(key, (map.get(key) || 0) + 1)
  })
  return map
})

const fleetCountByShop = computed(() => {
  const map = new Map()
  admin.state.vehicles.forEach((v) => {
    const key = String(v.shop_id || v.shop?.id || '')
    if (!key) return
    map.set(key, (map.get(key) || 0) + 1)
  })
  return map
})

const statusBadgeClass = (status) => {
  const label = statusLabel(status)
  if (label === 'ACTIVE') return 'badge badge-green'
  if (label === 'PENDING') return 'badge badge-yellow'
  if (label === 'SUSPENDED') return 'badge badge-red'
  return 'badge'
}

const resolveShopImageUrl = (value) => {
  const image = value ? String(value).trim() : ''
  if (!image) return ''
  if (/^(https?:\/\/|data:|blob:)/i.test(image)) return image
  const normalized = image.replace(/\\/g, '/').replace(/^\/+/, '')
  if (normalized.startsWith('storage/')) return `/${normalized}`
  return `/storage/${normalized.replace(/^storage\//, '')}`
}

const shopImage = (shop) => {
  const value = shop?.img_url_full || shop?.img_url || shop?.image_url || shop?.image || shop?.photo
  return resolveShopImageUrl(value)
}

const onCreateImageChange = (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  if (!file.type?.startsWith('image/')) {
    toast.error('Please select a valid image file.')
    return
  }
  createImageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    createImagePreview.value = String(e.target?.result || '')
    createForm.value.img_url = createImagePreview.value
  }
  reader.readAsDataURL(file)
}

const onEditImageChange = (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  if (!file.type?.startsWith('image/')) {
    toast.error('Please select a valid image file.')
    return
  }
  editImageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    editImagePreview.value = String(e.target?.result || '')
    editForm.value.img_url = editImagePreview.value
  }
  reader.readAsDataURL(file)
}

const openCreate = () => {
  showCreate.value = true
}

const closeCreate = () => {
  showCreate.value = false
  createForm.value = { name: '', address: '', location: '', phone: '', description: '', img_url: '' }
  createImageFile.value = null
  createImagePreview.value = ''
  if (route.query.create) {
    const nextQuery = { ...route.query }
    delete nextQuery.create
    router.replace({ query: nextQuery })
  }
}

const submitCreate = async () => {
  if (isCreating.value) return
  const name = String(createForm.value.name || '').trim()
  const address = String(createForm.value.address || '').trim()
  if (!name || !address) {
    toast.error('Please fill required fields: Shop Name and Address.')
    return
  }

  try {
    isCreating.value = true
    let payload = {
      name,
      address,
      city_id: createForm.value.city_id || null,
      location: createForm.value.location || null,
      phone: createForm.value.phone || null,
      description: createForm.value.description || null,
      img_url: createForm.value.img_url || null,
      status: 'inactive',
    }


    if (createImageFile.value) {
      const formData = new FormData()
      formData.append('name', name)
      formData.append('address', address)
      if (createForm.value.city_id) formData.append('city_id', createForm.value.city_id)
      if (createForm.value.location) formData.append('location', createForm.value.location)
      if (createForm.value.phone) formData.append('phone', createForm.value.phone)
      if (createForm.value.description) formData.append('description', createForm.value.description)
      formData.append('status', 'inactive')
      formData.append('img_url', createImageFile.value)
      payload = formData
    }

    await admin.createShop(payload)
    toast.success('Shop created successfully.')
    closeCreate()
    activeTab.value = 'all'
    page.value = 1
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to create shop.')
  } finally {
    isCreating.value = false
  }
}

const openEdit = (shop) => {
  const id = shop?.id
  editForm.value = {
    id,
    name: shop?.name || '',
    city_id: shop?.city_id || '',
    address: shop?.address || '',
    location: shop?.location || '',
    phone: shop?.phone || '',
    description: shop?.description || '',
    img_url: shop?.img_url || '',
  }
  editImageFile.value = null
  editImagePreview.value = ''
  showEdit.value = true
}

const closeEdit = () => {
  showEdit.value = false
  editForm.value = { id: null, city_id: '', name: '', address: '', location: '', phone: '', description: '', img_url: '' }
  editImageFile.value = null
  editImagePreview.value = ''
}

const submitEdit = async () => {
  if (isUpdating.value) return
  const id = editForm.value.id
  if (!id) return
  const name = String(editForm.value.name || '').trim()
  const address = String(editForm.value.address || '').trim()
  if (!name || !address) {
    toast.error('Please fill required fields: Shop Name and Address.')
    return
  }

  try {
    isUpdating.value = true
    let payload = {
      name,
      address,
      city_id: editForm.value.city_id || null,
      location: editForm.value.location || null,
      phone: editForm.value.phone || null,
      description: editForm.value.description || null,
      img_url: editForm.value.img_url || null,
    }

    if (editImageFile.value) {
      const formData = new FormData()
      formData.append('name', name)
      formData.append('address', address)
      if (editForm.value.city_id) formData.append('city_id', editForm.value.city_id)
      if (editForm.value.location) formData.append('location', editForm.value.location)
      if (editForm.value.phone) formData.append('phone', editForm.value.phone)
      if (editForm.value.description) formData.append('description', editForm.value.description)
      formData.append('img_url', editImageFile.value)
      payload = formData
    }

    await admin.updateShop(id, payload)
    toast.success('Shop updated successfully.')
    closeEdit()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update shop.')
  } finally {
    isUpdating.value = false
  }
}

const loadCities = async () => {
  loadingCities.value = true
  try {
    const { data } = await cityApi.getAll()
    cities.value = data?.data || data || []
  } catch (e) {
    console.error('Failed to load cities', e)
  } finally {
    loadingCities.value = false
  }
}

const requestDelete = (shop) => {
  deleteTarget.value = shop
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  const shop = deleteTarget.value
  if (!shop?.id) return
  if (deletingShopId.value) return
  try {
    deletingShopId.value = shop.id
    await admin.deleteShop(shop.id)
    toast.success('Shop deleted.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to delete shop.')
  } finally {
    deletingShopId.value = null
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

const approve = async (shop) => {
  if (!shop?.id || statusChangingShopId.value) return
  try {
    statusChangingShopId.value = shop.id
    await admin.setShopStatus(shop.id, 'active')
    toast.success('Shop approved.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to approve shop.')
  } finally {
    statusChangingShopId.value = null
  }
}

const disable = async (shop) => {
  if (!shop?.id || statusChangingShopId.value) return
  try {
    statusChangingShopId.value = shop.id
    await admin.setShopStatus(shop.id, 'inactive')
    toast.info('Shop disabled.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to disable shop.')
  } finally {
    statusChangingShopId.value = null
  }
}

watch(
  () => route.query.create,
  (value) => {
    showCreate.value = Boolean(value)
  },
  { immediate: true }
)

watch(
  [() => focusShopId.value, () => (admin.state.shops || []).length],
  ([focus]) => {
    if (!focus) return
    const shop = (admin.state.shops || []).find((item) => Number(item.id) === focus)
    if (!shop) return
    openEdit(shop)
    const nextQuery = { ...route.query }
    delete nextQuery.focusShop
    router.replace({ query: nextQuery })
  }
)

onMounted(() => {
  loadCities()
  // Use cached data first, then force-refresh if empty so Admin page always tries to recover.
  admin.load()
    .then(() => {
      if ((admin.state.shops || []).length === 0) {
        return admin.load({ force: true })
      }
      return null
    })
    .catch(() => {})
})
</script>


<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Shop Management</h1>
        <p class="page-subtitle">Manage, verify, and monitor all registered rental shops.</p>
      </div>
      <button type="button" class="btn btn-primary" style="max-width: 200px; justify-content: center;" @click="openCreate">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        <span>Add New Shop</span>
      </button>
    </header>

    <section class="card shops-report">
      <div class="shops-report-head">
        <div>
          <p class="shops-report-kicker">REPORT</p>
          <h2 class="shops-report-title">Shop Operations Overview</h2>
          <p class="shops-report-subtitle">Only the most important numbers for daily checks.</p>
        </div>
        <div class="shops-report-updated">
          <i class="fa-regular fa-calendar"></i>
          <span>Updated: {{ reportLastUpdateLabel }}</span>
        </div>
      </div>

      <div class="shops-report-metrics">
        <article class="shops-metric-card">
          <p class="shops-metric-label">Pending Approval</p>
          <p class="shops-metric-value">{{ reportSummary.pendingShops }}</p>
          <p class="shops-metric-note">Shops waiting for review</p>
        </article>
        <article class="shops-metric-card">
          <p class="shops-metric-label">Active Shops</p>
          <p class="shops-metric-value">{{ reportSummary.activeShops }}</p>
          <p class="shops-metric-note">Ready to take bookings</p>
        </article>
        <article class="shops-metric-card">
          <p class="shops-metric-label">Bookings This Month</p>
          <p class="shops-metric-value">{{ admin.formatted.fmtNumber(reportSummary.bookingsThisMonth) }}</p>
          <p class="shops-metric-note">{{ admin.formatted.fmtNumber(reportSummary.totalVehicles) }} vehicles in system</p>
        </article>
      </div>

      <div class="shops-report-bottom">
        <div class="shops-mini-chart">
          <div class="shops-mini-chart-head">
            <h3>New Shops (Last 6 Months)</h3>
            <span>{{ reportGrowthLabel }}</span>
          </div>
          <div class="shops-mini-bars">
            <div v-for="item in reportMonthSeries" :key="item.key" class="shops-mini-col">
              <div class="shops-mini-track">
                <div class="shops-mini-fill" :style="{ height: `${item.heightPercent}%` }"></div>
              </div>
              <span class="shops-mini-count">{{ item.count }}</span>
              <span class="shops-mini-label">{{ item.label }}</span>
            </div>
          </div>
        </div>

        <aside class="shops-report-note">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
          <p>Approve pending shops daily so new vehicles can appear and bookings stay smooth.</p>
        </aside>
      </div>
    </section>

    <section class="card">
      <div class="card-toolbar">
        <div class="tabs">
          <button type="button" class="tab" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">All Shops</button>
          <button type="button" class="tab" :class="{ active: activeTab === 'active' }" @click="activeTab = 'active'">Active</button>
          <button type="button" class="tab" :class="{ active: activeTab === 'inactive' }" @click="activeTab = 'inactive'">Inactive</button>
        </div>

        <div class="toolbar-right">
          <div class="sort">
            <!-- <span class="muted">Sort by:</span> -->
            <div class="sort-dropdown">
              <button type="button" class="btn btn-chip" @click="showSortMenu = !showSortMenu">
                {{ sortKey === 'newest' ? 'Newest' : sortKey === 'oldest' ? 'Oldest' : sortKey === 'name_asc' ? 'Name (A-Z)' : sortKey === 'name_desc' ? 'Name (Z-A)' : 'Newest' }} 
                <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
              </button>
              <div v-if="showSortMenu" class="sort-menu">
                <button type="button" @click="sortKey = 'newest'; showSortMenu = false">Newest</button>
                <button type="button" @click="sortKey = 'oldest'; showSortMenu = false">Oldest</button>
                <button type="button" @click="sortKey = 'name_asc'; showSortMenu = false">Name (A-Z)</button>
                <button type="button" @click="sortKey = 'name_desc'; showSortMenu = false">Name (Z-A)</button>
              </div>
            </div>
          </div>
          <button type="button" class="icon-btn" title="Download" @click="downloadShops">
            <i class="fa-solid fa-download" aria-hidden="true"></i>
          </button>
        </div>
      </div>


      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>SHOP NAME</th>
              <th>OWNER</th>
              <th class="num">FLEET COUNT</th>
              <th class="num">TOTAL BOOKINGS</th>
              <th>STATUS</th>
              <th class="actions">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="shop in pagedShops" :key="shop.id">
              <td class="shop-cell">
                <span class="shop-icon square" aria-hidden="true">
                  <img v-if="shopImage(shop)" class="shop-photo" :src="shopImage(shop)" :alt="shop.name" />
                  <i v-else class="fa-solid fa-shop"></i>
                </span>
                <div class="shop-meta">
                  <div class="shop-name">{{ shop.name }}</div>
                  <div class="shop-id">ID: {{ shop.id }}</div>
                </div>
              </td>
              <td>{{ ownerName(shop) }}</td>
              <td class="num">
                <strong>{{ fleetCountByShop.get(String(shop.id)) || 0 }}</strong> <span class="muted">VEHICLES</span>
              </td>
              <td class="num">
                {{ admin.formatted.fmtNumber(bookingCountByShop.get(String(shop.id)) || 0) }}
              </td>
              <td><span :class="statusBadgeClass(shop.status)">{{ statusLabel(shop.status) }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="Edit" :disabled="isUpdating || isCreating || deletingShopId === shop.id || statusChangingShopId === shop.id" @click="openEdit(shop)"><i class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="icon-action" title="Delete" :disabled="deletingShopId === shop.id || isUpdating || isCreating" @click="requestDelete(shop)"><i class="fa-regular fa-trash-can"></i></button>
                <button type="button" class="icon-action" title="Disable" :disabled="statusChangingShopId === shop.id || isUpdating || isCreating" @click="disable(shop)"><i class="fa-regular fa-circle-xmark"></i></button>
                <button v-if="statusLabel(shop.status) === 'PENDING'" type="button" class="btn btn-soft" :disabled="statusChangingShopId === shop.id || isUpdating || isCreating" @click="approve(shop)">
                  Approve
                </button>
              </td>
            </tr>
            <tr v-if="!pagedShops.length">
              <td colspan="7" style="text-align:center; padding: 30px; color: var(--mp-muted);">
                {{ normalizedQuery ? `No shops matched "${route.query.q}". Try clearing search.` : 'No shops found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="muted">
          SHOWING {{ showingStart }} TO {{ Math.min(page * perPage, sortedShops.length) }} OF
          {{ admin.formatted.fmtNumber(sortedShops.length) }} SHOPS
        </div>
        <div class="pager">
          <button type="button" class="pager-btn" :disabled="page === 1" @click="page -= 1">&lt;</button>
          <button type="button" class="pager-btn is-active">{{ page }}</button>
          <button type="button" class="pager-btn" :disabled="page === totalPages" @click="page += 1">&gt;</button>
        </div>
      </div>
    </section>

    <div v-if="showCreate" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal modal--create-shop">
        <div class="modal-head">
          <div>
            <div class="modal-title">Create New Shop</div>
            <div class="modal-sub">Add a new rental shop (pending approval by default).</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeCreate"><i class="fa-solid fa-xmark"></i></button>
        </div>


        <div class="modal-body create-shop-layout">
          <div class="create-shop-preview">
            <div class="create-shop-preview__image">
              <img
                v-if="createImagePreview"
                :src="createImagePreview"
                alt="Shop preview"
              />
              <div v-else class="create-shop-preview__placeholder">
                <i class="fa-solid fa-shop"></i>
              </div>
            </div>
            <div class="create-shop-preview__info">
              <p class="create-shop-preview__title">
                {{ createForm.name || 'Untitled shop' }}
              </p>
              <p class="create-shop-preview__subtitle">
                {{ createForm.address || 'Add a location and contact details' }}
              </p>
              <p class="create-shop-preview__location">
                {{ createForm.location || 'City, Country' }}
              </p>
            </div>
            <div class="create-shop-preview__meta">
              <span class="muted">Status</span>
              <strong>Pending</strong>
            </div>
          </div>

          <div class="create-shop-form">
            <label class="upload-drop">
              <input type="file" accept="image/*" @change="onCreateImageChange" />
              <div class="upload-drop__content">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <strong>Upload shop cover</strong>
                <span>Drop an image or click to browse files</span>
              </div>
            </label>
            <div class="field-grid two-column">
              <label class="field">
                <span class="field-label">Province/City</span>
                <select v-model="createForm.city_id" class="form-select" style="width: 100%; border-radius: 12px; border: 1px solid #d6dbec; padding: 10px 12px;">
                  <option value="" disabled>Select Province</option>
                  <option v-for="city in cities" :key="city.id" :value="city.id">
                    {{ city.name }}
                  </option>
                </select>
              </label>
              <label class="field">
                <span class="field-label">Shop Name</span>
                <input
                  v-model="createForm.name"
                  type="text"
                  placeholder="e.g. Berlin Elite Rentals"
                />
              </label>
            </div>
            <div class="field-grid two-column">
              <label class="field">
                <span class="field-label">Location</span>
                <input v-model="createForm.location" type="text" placeholder="City, Country" />
              </label>
              <label class="field">
                <span class="field-label">Phone</span>
                <input v-model="createForm.phone" type="text" placeholder="+855..." />
              </label>
            </div>
            <label class="field">
              <span class="field-label">Description</span>
              <textarea
                v-model="createForm.description"
                rows="4"
                placeholder="Describe the shop, services or fleet."
              ></textarea>
            </label>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" :disabled="isCreating" @click="closeCreate">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="isCreating" @click="submitCreate">
            {{ isCreating ? 'Creating...' : 'Create Shop' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showEdit" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Edit Shop</div>
            <div class="modal-sub">Update shop information and image.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeEdit"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Province/City</span>
            <select v-model="editForm.city_id" class="form-select" style="width: 100%; border-radius: 12px; border: 1px solid #d6dbec; padding: 10px 12px;">
              <option value="" disabled>Select Province</option>
              <option v-for="city in cities" :key="city.id" :value="city.id">
                {{ city.name }}
              </option>
            </select>
          </label>
          <label class="field">
            <span class="field-label">Shop Name</span>
            <input v-model="editForm.name" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Address</span>
            <input v-model="editForm.address" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Location</span>
            <input v-model="editForm.location" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Phone</span>
            <input v-model="editForm.phone" type="text" />
          </label>
          <label class="field span-2">
            <span class="field-label">Description</span>
            <textarea v-model="editForm.description" rows="3"></textarea>
          </label>
          <label class="field span-2">
            <span class="field-label">Shop Image</span>
            <input type="file" accept="image/*" @change="onEditImageChange" />
            <div v-if="editImagePreview || editForm.img_url" class="image-preview">
              <img :src="editImagePreview || resolveShopImageUrl(editForm.img_url)" alt="Shop preview" />
            </div>
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" :disabled="isUpdating" @click="closeEdit">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="isUpdating" @click="submitEdit">
            {{ isUpdating ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmModal
      v-model="showDeleteConfirm"
      title="Delete Shop"
      :message="deleteShopMessage"
      cancel-text="Cancel"
      confirm-text="Yes"
      variant="danger"
      @confirm="confirmDelete"
    />
  </section>
</template>

<style>
  .shops-report {
    border-radius: 18px;
    border: 1px solid #d7e1f0;
    background: linear-gradient(145deg, #f9fbff 0%, #f2f6ff 100%);
  }

  .shops-report-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 14px;
  }

  .shops-report-kicker {
    margin: 0;
    font-size: 11px;
    letter-spacing: 0.1em;
    color: #5c7292;
    font-weight: 800;
  }

  .shops-report-title {
    margin: 2px 0 4px;
    font-size: 1.35rem;
    color: #102d4a;
    font-weight: 800;
  }

  .shops-report-subtitle {
    margin: 0;
    color: #627791;
    font-size: 0.9rem;
  }

  .shops-report-updated {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid #d5deed;
    color: #5f738f;
    padding: 0.45rem 0.72rem;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .shops-report-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
  }

  .shops-metric-card {
    border: 1px solid #d9e2f0;
    border-radius: 14px;
    background: #ffffff;
    padding: 12px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
  }

  .shops-metric-label {
    margin: 0;
    color: #56708f;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-size: 0.74rem;
    font-weight: 800;
  }

  .shops-metric-value {
    margin: 8px 0 4px;
    color: #0f2944;
    font-weight: 900;
    font-size: 1.8rem;
    line-height: 1;
  }

  .shops-metric-note {
    margin: 0;
    color: #64748b;
    font-size: 0.83rem;
    font-weight: 600;
  }

  .shops-report-bottom {
    margin-top: 14px;
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(220px, 1fr);
    gap: 12px;
  }

  .shops-mini-chart {
    border: 1px solid #d9e2f0;
    border-radius: 14px;
    background: #fff;
    padding: 12px;
  }

  .shops-mini-chart-head {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    align-items: baseline;
    margin-bottom: 10px;
  }

  .shops-mini-chart-head h3 {
    margin: 0;
    color: #173a60;
    font-size: 0.95rem;
    font-weight: 800;
  }

  .shops-mini-chart-head span {
    color: #1e40af;
    font-size: 0.82rem;
    font-weight: 700;
  }

  .shops-mini-bars {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 10px;
  }

  .shops-mini-col {
    text-align: center;
    display: grid;
    gap: 4px;
    justify-items: center;
  }

  .shops-mini-track {
    width: 100%;
    max-width: 34px;
    height: 88px;
    border-radius: 10px;
    background: #eaf0fa;
    position: relative;
    overflow: hidden;
  }

  .shops-mini-fill {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 10px 10px 0 0;
    background: linear-gradient(180deg, #4d93ff 0%, #1e63d6 100%);
    transition: height 320ms ease;
  }

  .shops-mini-count {
    font-size: 0.78rem;
    color: #1e3a5f;
    font-weight: 800;
  }

  .shops-mini-label {
    color: #637790;
    font-size: 0.74rem;
    font-weight: 700;
  }

  .shops-report-note {
    border: 1px solid #d9e2f0;
    border-radius: 14px;
    background: #fff;
    padding: 12px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    color: #4d6784;
  }

  .shops-report-note i {
    margin-top: 2px;
    color: #f59e0b;
  }

  .shops-report-note p {
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.45;
    font-weight: 600;
  }

  .sort-menu {
    padding: 10px;
  }
  .sort-menu button {
    margin: 4px;
  }

  .create-shop-layout {
    display: grid;
    grid-template-columns: minmax(240px, 300px) minmax(0, 1fr);
    gap: 24px;
    align-items: start;
    overflow-x: hidden;
  }

  .create-shop-layout > * {
    min-width: 0;
  }

  .create-shop-preview {
    background: #f8fafc;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
    text-align: center;
  }

  .create-shop-preview__image {
    width: 100%;
    padding-top: 56%;
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    box-shadow: inset 0 0 0 1px #e5e7eb;
  }

  .create-shop-preview__image img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .create-shop-preview__placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 32px;
  }

  .create-shop-preview__info {
    text-align: left;
  }

  .create-shop-preview__title {
    margin: 0;
    font-weight: 700;
    font-size: 1.15rem;
    color: #0f172a;
  }

  .create-shop-preview__subtitle,
  .create-shop-preview__location {
    margin: 4px 0 0;
    color: #475569;
    font-size: 0.9rem;
  }

  .create-shop-preview__meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #e5e7eb;
    color: #475569;
    font-size: 0.85rem;
  }

  .create-shop-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
    min-width: 0;
  }

  .upload-drop {
    border: 2px dashed #cbd5f5;
    border-radius: 18px;
    padding: 32px;
    text-align: center;
    background: #fdfdfd;
    cursor: pointer;
    transition: border-color 0.2s ease;
  }

  .upload-drop:hover {
    border-color: #2563eb;
  }

  .upload-drop input {
    display: none;
  }

  .upload-drop__content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #475569;
  }

  .upload-drop__content i {
    font-size: 32px;
    color: #2563eb;
  }

  .field-grid {
    display: grid;
    gap: 16px;
  }

  .field-grid.two-column {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .create-shop-form .field {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
  }

  .create-shop-form .field input,
  .create-shop-form .field textarea {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    box-sizing: border-box;
    border-radius: 12px;
    border: 1px solid #d6dbec;
    padding: 10px 12px;
    font-size: 0.95rem;
    font-family: 'Inter', system-ui, sans-serif;
  }

  .create-shop-form .field textarea {
    min-height: 120px;
    resize: vertical;
  }

  .modal--create-shop {
    max-width: 960px;
  }

  @media (max-width: 980px) {
    .shops-report-metrics {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .shops-report-bottom {
      grid-template-columns: 1fr;
    }

    .modal--create-shop {
      max-width: 760px;
    }

    .create-shop-layout {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }

  @media (max-width: 760px) {
    .shops-report-head {
      flex-direction: column;
      align-items: flex-start;
    }

    .shops-report-metrics {
      grid-template-columns: 1fr;
    }

    .create-shop-form .field-grid.two-column {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    .upload-drop {
      padding: 22px 14px;
    }
  }
</style>
