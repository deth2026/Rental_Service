<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
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

const showCreate = ref(false)
const createForm = ref({ name: '', address: '', location: '', phone: '', description: '', img_url: '' })
const createImageFile = ref(null)
const createImagePreview = ref('')
const createImageInput = ref(null)

const showEdit = ref(false)
const editForm = ref({ id: null, name: '', address: '', location: '', phone: '', description: '', img_url: '' })
const editImageFile = ref(null)
const editImagePreview = ref('')

const showDeleteConfirm = ref(false)
const deleteTarget = ref(null)

const normalizedQuery = computed(() => String(route.query.q || '').trim().toLowerCase())

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

const ratingForShop = (shop) => {
  const str = String(shop.id || shop.name || '')
  let hash = 0
  for (let i = 0; i < str.length; i += 1) hash = (hash * 31 + str.charCodeAt(i)) >>> 0
  const val = 4 + (hash % 10) / 10
  return Math.min(4.9, Math.max(4.1, Number(val.toFixed(1))))
}

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
  const value = shop?.img_url || shop?.image_url || shop?.image || shop?.photo
  return resolveShopImageUrl(value)
}

const handleCreateImageFile = (file) => {
  if (!file) return
  if (!file.type?.startsWith('image/')) {
    toast.error('Please select a valid image file.')
    return
  }

  createImageFile.value = file
  const reader = new FileReader()
  reader.onload = (e) => {
    const preview = String(e.target?.result || '')
    createImagePreview.value = preview
    createForm.value.img_url = preview
  }
  reader.readAsDataURL(file)
}

const onCreateImageDrop = (event) => {
  event.preventDefault()
  const file = event.dataTransfer?.files?.[0]
  handleCreateImageFile(file)
}

const onCreateImageChange = (event) => {
  const file = event.target.files?.[0]
  handleCreateImageFile(file)
  if (event.target) {
    event.target.value = ''
  }
}

const removeCreateImage = () => {
  createImageFile.value = null
  createImagePreview.value = ''
  createForm.value.img_url = ''
  if (createImageInput.value) {
    createImageInput.value.value = ''
  }
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
  removeCreateImage()
  if (route.query.create) {
    const nextQuery = { ...route.query }
    delete nextQuery.create
    router.replace({ query: nextQuery })
  }
}

const submitCreate = async () => {
  const name = String(createForm.value.name || '').trim()
  const address = String(createForm.value.address || '').trim()
  if (!name || !address) {
    toast.error('Please fill required fields: Shop Name and Address.')
    return
  }

  try {
    let payload = {
      name,
      address,
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
  }
}

const openEdit = (shop) => {
  const id = shop?.id
  editForm.value = {
    id,
    name: shop?.name || '',
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
  editForm.value = { id: null, name: '', address: '', location: '', phone: '', description: '', img_url: '' }
  editImageFile.value = null
  editImagePreview.value = ''
}

const submitEdit = async () => {
  const id = editForm.value.id
  if (!id) return
  const name = String(editForm.value.name || '').trim()
  const address = String(editForm.value.address || '').trim()
  if (!name || !address) {
    toast.error('Please fill required fields: Shop Name and Address.')
    return
  }

  try {
    let payload = {
      name,
      address,
      location: editForm.value.location || null,
      phone: editForm.value.phone || null,
      description: editForm.value.description || null,
      img_url: editForm.value.img_url || null,
    }

    if (editImageFile.value) {
      const formData = new FormData()
      formData.append('name', name)
      formData.append('address', address)
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
  }
}

const requestDelete = (shop) => {
  deleteTarget.value = shop
  showDeleteConfirm.value = true
}

const confirmDelete = async () => {
  const shop = deleteTarget.value
  if (!shop?.id) return
  try {
    await admin.deleteShop(shop.id)
    toast.success('Shop deleted.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to delete shop.')
  } finally {
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

const approve = async (shop) => {
  try {
    await admin.setShopStatus(shop.id, 'active')
    toast.success('Shop approved.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to approve shop.')
  }
}

const disable = async (shop) => {
  try {
    await admin.setShopStatus(shop.id, 'inactive')
    toast.info('Shop disabled.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to disable shop.')
  }
}

watch(
  () => route.query.create,
  (value) => {
    showCreate.value = Boolean(value)
  },
  { immediate: true }
)

onMounted(() => {
  // Use cached data immediately - no loading delay
  admin.load().catch(() => {})
})
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Shop Management</h1>
        <p class="page-subtitle">Manage, verify, and monitor all registered rental shops.</p>
      </div>
      <button type="button" class="btn btn-primary" @click="openCreate">
        <i class="fa-solid fa-plus" aria-hidden="true"></i>
        <span>Add New Shop</span>
      </button>
    </header>

    <section class="card">
      <div class="card-toolbar">
        <div class="tabs">
          <button type="button" class="tab" :class="{ active: activeTab === 'all' }" @click="activeTab = 'all'">All Shops</button>
          <button type="button" class="tab" :class="{ active: activeTab === 'active' }" @click="activeTab = 'active'">Active</button>
          <button type="button" class="tab" :class="{ active: activeTab === 'pending' }" @click="activeTab = 'pending'">Pending</button>
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
              <th class="num">RATING</th>
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
                  <div class="shop-id">ID: #{{ shop.id }}</div>
                </div>
              </td>
              <td>{{ ownerName(shop) }}</td>
              <td class="num">
                <strong>{{ fleetCountByShop.get(String(shop.id)) || 0 }}</strong> <span class="muted">VEHICLES</span>
              </td>
              <td class="num">
                {{ admin.formatted.fmtNumber(bookingCountByShop.get(String(shop.id)) || 0) }}
              </td>
              <td class="num">
                <span class="rating"><i class="fa-solid fa-star" aria-hidden="true"></i> {{ ratingForShop(shop) }}</span>
              </td>
              <td><span :class="statusBadgeClass(shop.status)">{{ statusLabel(shop.status) }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="Edit" @click="openEdit(shop)"><i class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="icon-action" title="Delete" @click="requestDelete(shop)"><i class="fa-regular fa-trash-can"></i></button>
                <button type="button" class="icon-action" title="Disable" @click="disable(shop)"><i class="fa-regular fa-circle-xmark"></i></button>
                <button v-if="statusLabel(shop.status) === 'PENDING'" type="button" class="btn btn-soft" @click="approve(shop)">
                  Approve
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="muted">
          SHOWING {{ (page - 1) * perPage + 1 }} TO {{ Math.min(page * perPage, sortedShops.length) }} OF
          {{ admin.formatted.fmtNumber(sortedShops.length) }} SHOPS
        </div>
        <div class="pager">
          <button type="button" class="pager-btn" :disabled="page === 1" @click="page -= 1">‹</button>
          <button type="button" class="pager-btn is-active">{{ page }}</button>
          <button type="button" class="pager-btn" :disabled="page === totalPages" @click="page += 1">›</button>
        </div>
      </div>
    </section>

    <div v-if="showCreate" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Create New Shop</div>
            <div class="modal-sub">Add a new rental shop (pending approval by default).</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeCreate"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Shop Name</span>
            <input v-model="createForm.name" type="text" placeholder="e.g. Berlin Elite Rentals" />
          </label>
          <label class="field">
            <span class="field-label">Address</span>
            <input v-model="createForm.address" type="text" placeholder="Street, City, Country" />
          </label>
          <label class="field">
            <span class="field-label">Location</span>
            <input v-model="createForm.location" type="text" placeholder="City, Country" />
          </label>
          <label class="field">
            <span class="field-label">Phone</span>
            <input v-model="createForm.phone" type="text" placeholder="+855..." />
          </label>
          <label class="field span-2">
            <span class="field-label">Description</span>
            <textarea v-model="createForm.description" rows="3" placeholder="Short shop description (optional)"></textarea>
          </label>
          <label class="field span-2 upload-field">
            <span class="field-label">Shop Image</span>
            <div
              class="upload-dropzone"
              role="button"
              tabindex="0"
              aria-label="Upload shop image"
              @click="createImageInput?.click()"
              @keydown.enter.prevent="createImageInput?.click()"
              @keydown.space.prevent="createImageInput?.click()"
              @dragover.prevent
              @drop.prevent="onCreateImageDrop"
            >
              <input
                ref="createImageInput"
                type="file"
                accept="image/*"
                class="upload-input"
                @change="onCreateImageChange"
              />
              <div v-if="createImagePreview" class="upload-preview">
                <img :src="createImagePreview" alt="Shop preview" />
                <button type="button" class="upload-remove" @click.stop="removeCreateImage">
                  <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                  Remove photo
                </button>
              </div>
              <div v-else class="upload-placeholder">
                <i class="fa-solid fa-image" aria-hidden="true"></i>
                <p class="upload-title">Click or drop an image</p>
                <small class="muted">JPG, PNG, max 5 MB</small>
              </div>
            </div>
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="closeCreate">Cancel</button>
          <button type="button" class="btn btn-primary" @click="submitCreate">Create Shop</button>
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
          <button type="button" class="btn btn-ghost" @click="closeEdit">Cancel</button>
          <button type="button" class="btn btn-primary" @click="submitEdit">Save Changes</button>
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
.sort-menu {
  padding: 10px;
}
.sort-menu  button {
margin: 4px;
}
</style>
