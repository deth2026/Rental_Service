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

// Fast access helpers (non-reactive for core logic)
const getArr = (v) => v || []
const getObj = (v, d = {}) => ({ ...d, ...v })

const currentUser = computed(() => userService.getCurrentUser())
const isAdmin = computed(() => {
  const role = String(currentUser.value?.role || currentUser.value?.user_type || '').toLowerCase()
  return role === 'admin'
})
const adminName = computed(() => {
  const name = currentUser.value?.name
  return name ? name.split(' ')[0] : 'Admin'
})


const animated = ref(false)
const hoveredRevenueIndex = ref(null)
const money0 = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 })

// Optimized stats computation (Direct access, no spread if possible)
const stats = computed(() => {
  const t = admin.totals
  const tr = admin.trends
  const fmtNumber = admin.formatted.fmtNumber
  const fmtMoney = admin.formatted.fmtMoneyCompact

  return [
    { key: 'users', label: 'TOTAL USERS', raw: t.totalUsers, formatter: fmtNumber, trend: tr.users, icon: 'fa-solid fa-users', tint: 'tint-blue', to: '/admin/users' },
    { key: 'shops', label: 'TOTAL SHOPS', raw: t.totalShops, formatter: fmtNumber, trend: tr.shops, icon: 'fa-regular fa-building', tint: 'tint-purple', to: '/admin/shops' },
    { key: 'vehicles', label: 'TOTAL VEHICLES', raw: t.totalVehicles, formatter: fmtNumber, trend: tr.vehicles, icon: 'fa-solid fa-motorcycle', tint: 'tint-orange', to: '/admin/vehicles' },
    { key: 'bookings', label: 'TOTAL BOOKINGS', raw: t.totalBookings, formatter: fmtNumber, trend: tr.bookings, icon: 'fa-regular fa-calendar-check', tint: 'tint-green', to: '/admin/bookings' },
    { key: 'revenue', label: 'TOTAL REVENUE', raw: admin.paymentTotal, formatter: fmtMoney, trend: tr.revenue, icon: 'fa-solid fa-sack-dollar', tint: 'tint-cyan', to: '/admin/reports' }
  ]
})

const buildFallbackRevenueDays = () => {
  const today = new Date()
  const days = []
  for (let i = 6; i >= 0; i -= 1) {
    const d = new Date(today)
    d.setDate(today.getDate() - i)
    days.push({
      key: d.toISOString().split('T')[0],
      label: d.toLocaleDateString('en-US', { weekday: 'short' }),
      value: 0
    })
  }
  return days
}

const commissionRate = computed(() => Number(admin.state?.commission_rate || 0.15))
const revenueDailySeries = computed(() => {
  const raw = getArr(admin.bookingGrossByDay)
  const points = raw.length ? raw : buildFallbackRevenueDays()
  return points.map((point) => {
    const earnings = Number(point.value || 0)
    const commission = Math.round(earnings * commissionRate.value)
    return {
      key: point.key,
      label: point.label,
      earnings,
      commission,
      net: Math.max(0, earnings - commission)
    }
  })
})

const niceMax = (value, floor = 1000) => {
  if (value <= 0) return floor
  if (value < 2000) return Math.ceil(value / 250) * 250
  return Math.ceil(value / 500) * 500
}

const revenueBarMax = computed(() => {
  const peak = Math.max(...revenueDailySeries.value.flatMap((row) => [row.earnings, row.commission, row.net]), 0)
  return niceMax(peak, 1000)
})

const revenueTooltipData = computed(() => {
  const idx = hoveredRevenueIndex.value
  if (idx == null || idx < 0 || idx >= revenueDailySeries.value.length) return null
  const row = revenueDailySeries.value[idx]
  const centerX = 92 + (idx * 92)
  const width = 202
  const height = 96
  const peak = Math.max(row.earnings, row.commission, row.net)
  const peakY = 246 - ((peak / (revenueBarMax.value || 1)) * 210)
  const x = Math.max(20, Math.min(centerX - (width / 2), 716 - width))
  const y = Math.max(12, peakY - height - 10)
  return { row, x, y, width, height }
})

const fleetTypes = computed(() => {
  const c = admin.vehicleTypeCounts
  const total = c.motorbike + c.bicycle + c.car + c.other || 1
  const getPct = (v) => Math.round((v / total) * 100)
  return [
    { key: 'motorbikes', label: 'Motorbikes', value: getPct(c.motorbike), icon: 'fa-solid fa-motorcycle', bar: 'bar-cyan' },
    { key: 'bicycles', label: 'Bicycles', value: getPct(c.bicycle), icon: 'fa-solid fa-person-biking', bar: 'bar-green' },
    { key: 'cars', label: 'Cars', value: getPct(c.car), icon: 'fa-solid fa-car-side', bar: 'bar-orange' }
  ]
})

// Lightweight status mapping
const statusStyles = {
  VERIFIED: 'badge badge-green', ACTIVE: 'badge badge-green',
  INACTIVE: 'badge badge-yellow', PENDING: 'badge badge-yellow',
  SUSPENDED: 'badge badge-red', BLOCKED: 'badge badge-red'
}
const statusBadgeClass = (s) => statusStyles[String(s || '').toUpperCase()] || 'badge'

const vehicleIcon = (type) => {
  const t = String(type || '').toLowerCase()
  if (t.includes('bike') && !t.includes('bicycle')) return 'fa-solid fa-motorcycle'
  if (t.includes('bicycle')) return 'fa-solid fa-person-biking'
  if (t.includes('car')) return 'fa-solid fa-car-side'
  return 'fa-solid fa-shop'
}

const statusLabel = (s) => {
  const r = String(s || '').toUpperCase()
  if (['VERIFIED', 'ACTIVE'].includes(r)) return 'ACTIVE'
  if (['INACTIVE', 'PENDING'].includes(r)) return 'PENDING'
  if (['SUSPENDED', 'BLOCKED'].includes(r)) return 'SUSPENDED'
  return r || '—'
}

const ownerName = (s) => s?.owner?.name || s?.owner_name || s?.owner || '—'
const shopLocation = (s) => s?.location || s?.address || s?.city || '—'

const resolveShopImageUrl = (value) => {
  // Use userService helper to build full URL for stored images (handles storage/, absolute URLs, data URIs)
  try {
    return userService.getAvatarUrl(value) || ''
  } catch (e) {
    const image = value ? String(value).trim() : ''
    if (!image) return ''
    if (/^(https?:\/\/|data:|blob:)/i.test(image)) return image
    const normalized = image.replace(/\\/g, '/').replace(/^\/+/, '')
    if (normalized.startsWith('storage/')) return `/${normalized}`
    return `/storage/${normalized.replace(/^storage\//, '')}`
  }
}

const shopImage = (shop) => {
  const value = shop?.img_url_full || shop?.img_url || shop?.image_url || shop?.image || shop?.photo || ''
  if (typeof value !== 'string') return ''
  return resolveShopImageUrl(value)
}

// Optimized fleet mapping using a simple object instead of Map for faster lookups in template
const fleetCountMap = computed(() => {
  const res = {}
  getArr(admin.state.vehicles).forEach(v => {
    const id = String(v.shop_id || v.shop?.id || '')
    if (id) res[id] = (res[id] || 0) + 1
  })
  return res
})

const statusUpdatingShopId = ref(null)
const editSaving = ref(false)
const editImageFile = ref(null)
const editImagePreview = ref('')

const onVerify = async (shop) => {
  if (!shop?.id || statusUpdatingShopId.value) return
  try {
    statusUpdatingShopId.value = shop.id
    await admin.setShopStatus(shop.id, 'active')
    toast.success('Shop verified.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to verify shop.')
  } finally {
    statusUpdatingShopId.value = null
  }
}

const onReactivate = async (shop) => {
  if (!shop?.id || statusUpdatingShopId.value) return
  try {
    statusUpdatingShopId.value = shop.id
    await admin.setShopStatus(shop.id, 'active')
    toast.success('Shop reactivated.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to reactivate shop.')
  } finally {
    statusUpdatingShopId.value = null
  }
}

const openStat = (c) => c.to && router.push(c.to)
const openShops = () => router.push('/admin/shops')
const addNewShop = () => router.push('/admin/shops?create=1')

const showView = ref(false)
const showEdit = ref(false)
const selectedShop = ref(null)
const editForm = ref({ id: null, name: '', location: '', status: 'inactive', img_url: '' })

const openView = (s) => { selectedShop.value = s; showView.value = true }
const openEdit = (s) => {
  editForm.value = {
    id: s.id,
    name: s.name || '',
    location: s.location || '',
    status: String(s.status || 'inactive').toLowerCase(),
    img_url: s.img_url || ''
  }
  editImageFile.value = null
  editImagePreview.value = ''
  showEdit.value = true
}
const closeView = () => { showView.value = false; selectedShop.value = null }
const closeEdit = () => {
  showEdit.value = false
  editSaving.value = false
  editImageFile.value = null
  editImagePreview.value = ''
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
  }
  reader.readAsDataURL(file)
}

const submitEdit = async () => {
  if (editSaving.value) return
  const id = editForm.value.id
  if (!id || !editForm.value.name.trim()) {
    if (!editForm.value.name.trim()) toast.error('Shop name is required.')
    return
  }
  try {
    editSaving.value = true
    let payload = {
      name: editForm.value.name.trim(),
      location: editForm.value.location || null,
      status: editForm.value.status || 'inactive',
      img_url: editForm.value.img_url || null
    }
    if (editImageFile.value) {
      const formData = new FormData()
      formData.append('name', editForm.value.name.trim())
      if (editForm.value.location) formData.append('location', editForm.value.location)
      formData.append('status', editForm.value.status || 'inactive')
      formData.append('img_url', editImageFile.value)
      payload = formData
    }
    await admin.updateShop(id, payload)
    toast.success('Shop updated.')
    closeEdit()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Update failed.')
  } finally {
    editSaving.value = false
  }
}

const showRevenueTooltip = (idx) => { hoveredRevenueIndex.value = idx }
const hideRevenueTooltip = () => { hoveredRevenueIndex.value = null }

const triggerAnimation = () => {
  animated.value = false
  nextTick(() => { requestAnimationFrame(() => { animated.value = true }) })
}

onMounted(() => {
  admin.load().then(triggerAnimation).catch(() => {})
})

// Fast watch using stringified key to avoid deep watch overhead
watch(
  () => getArr(admin.bookingGrossByDay).map((d) => `${d.key}:${d.value || 0}`).join('|'),
  triggerAnimation
)
</script>

<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-50 font-sans">
    
    <aside class="w-full md:w-64 bg-slate-900 text-white p-6 flex flex-col">
      <div class="flex items-center gap-2 mb-10 text-xl font-bold">
        <div class="p-2 bg-red-500 rounded-lg">⚙️</div> ADMIN PANEL
      </div>
      
      <nav class="flex-1 space-y-4">
        <a v-for="item in menu" :key="item" href="#" 
           :class="item === 'Dashboard' ? 'bg-red-500' : 'hover:bg-slate-800'"
           class="flex items-center p-3 rounded-lg transition-colors">
          {{ item }}
        </a>
      </nav>

      <button @click="handleLogout" class="mt-auto pt-6 text-gray-400 hover:text-white flex items-center gap-2">
        <span>Logout</span>
      </button>
    </aside>

    <main class="flex-1 p-4 md:p-8">
      
      <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard</h1>
          <p class="text-gray-500">Welcome back, Admin. Here's what's happening today.</p>
        </div>
        <button class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg flex items-center gap-2">
          <span>+</span> Add New Admin
        </button>
      </header>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div v-for="stat in stats" :key="stat.label" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
          <div class="flex justify-between items-start mb-4">
            <span class="p-2 rounded-lg" :class="stat.bg">{{ stat.icon }}</span>
            <span :class="stat.trend > 0 ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold">
              {{ stat.trend > 0 ? '+' : '' }}{{ stat.trend }}%
            </span>
          </div>
          <p class="text-xs text-gray-400 uppercase font-bold">{{ stat.label }}</p>
          <h2 class="text-2xl font-bold text-gray-800">{{ stat.value }}</h2>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center">
          <h3 class="font-bold">Recent User Registrations</h3>
          <select class="text-sm border rounded p-1 text-gray-500"><option>All Status</option></select>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-400 text-xs uppercase">
              <tr>
                <th class="p-4">User Name</th>
                <th class="p-4">Email</th>
                <th class="p-4">Role</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <tr v-for="user in users" :key="user.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="p-4 font-semibold">{{ user.name }}</td>
                <td class="p-4 text-gray-600">{{ user.email }}</td>
                <td class="p-4 font-medium">{{ user.role }}</td>
                <td class="p-4">
                  <span :class="statusClass(user.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                    {{ user.status }}
                  </span>
                </td>
                <td class="p-4 text-right">
                  <button class="text-blue-500 hover:underline">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const menu = ['Dashboard', 'User Management', 'Shop Management', 'Vehicle Management', 'Bookings', 'Reports', 'Settings'];

const stats = [
  { label: 'Total Users', value: '2,482', trend: 12, icon: '👥', bg: 'bg-blue-50' },
  { label: 'Total Shops', value: '156', trend: 4, icon: '🏪', bg: 'bg-purple-50' },
  { label: 'Total Vehicles', value: '892', trend: -2, icon: '🚗', bg: 'bg-orange-50' },
  { label: 'Total Bookings', value: '12,302', trend: 18, icon: '🛍️', bg: 'bg-green-50' },
  { label: 'Total Revenue', value: '$45.2k', trend: 24, icon: '💵', bg: 'bg-cyan-50' }
];

const users = [
  { id: 1, name: 'John Doe', email: 'john@example.com', role: 'customer', status: 'active' },
  { id: 2, name: 'Shop Owner', email: 'shop@example.com', role: 'shop', status: 'verified' },
  { id: 3, name: 'New User', email: 'new@example.com', role: 'customer', status: 'pending' }
];

const statusClass = (status) => {
  if (status === 'active' || status === 'verified') return 'bg-green-100 text-green-600';
  if (status === 'pending') return 'bg-orange-100 text-orange-600';
  return 'bg-red-100 text-red-600';
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await fetch('/api/users/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};
</script>
