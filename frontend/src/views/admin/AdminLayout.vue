<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme } from '../../composables/useTheme'
import userService from '../../services/userService.js'
import { useAdminStore } from '../../stores/adminStore.js'
import { CAMBODIA_TIMEZONE, cambodiaDateTimeLabel, cambodiaYear } from '../../utils/cambodiaTime.js'
import '../../css/DashboardAdmin.css'
import ConfirmModal from '../../components/ConfirmModal.vue'
import ToastStack from '../../components/ToastStack.vue'

const route = useRoute()
const router = useRouter()
const { isDark, toggleTheme } = useTheme()
const adminStore = useAdminStore()
const { t } = useI18n()

// Professional Logo path from public folder
const logoUrl = '/images/logo-removebg.png'

const searchQuery = ref('')
const showLogoutConfirm = ref(false)
let searchTimer = null
let clockTimer = null
const nowTick = ref(Date.now())

const navItems = [
  { label: 'Dashboard', to: '/admin', icon: 'fa-solid fa-table-cells-large' },
  { label: 'Shop Management', to: '/admin/shops', icon: 'fa-regular fa-building' },
  { label: 'User Management', to: '/admin/users', icon: 'fa-regular fa-user' },
  { label: 'Vehicle Management', to: '/admin/vehicles', icon: 'fa-solid fa-motorcycle' },
  { label: 'Booking Management', to: '/admin/bookings', icon: 'fa-regular fa-calendar-check' },
  { label: 'Coupons', to: '/admin/coupons', icon: 'fa-solid fa-ticket' },
  { label: 'Categories', to: '/admin/categories', icon: 'fa-solid fa-tags' },
  { label: 'Cities', to: '/admin/cities', icon: 'fa-solid fa-location-dot' },
  { label: 'Financials', to: '/admin/financials', icon: 'fa-solid fa-dollar-sign' },
  { label: 'Reports', to: '/admin/reports', icon: 'fa-solid fa-chart-column' },
  { label: 'Settings', to: '/admin/settings', icon: 'fa-solid fa-gear' },
]

const currentUser = computed(() => userService.getCurrentUser())
const adminName = computed(() => currentUser.value?.name || 'Admin')
const adminRole = computed(() => (currentUser.value?.role || 'Admin').toString().toUpperCase())
const isAdmin = computed(() => {
  const u = currentUser.value || {}
  if (u.is_admin === true) return true
  const role = String(u.role || u.user_type || '').toLowerCase()
  return role === 'admin'
})
const adminInitials = computed(() => {
  const name = adminName.value.trim()
  if (!name) return 'A'
  const parts = name.split(/\s+/).filter(Boolean)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase()
})

const activePath = computed(() => route.path)
const isActive = (path) => {
  if (path === '/admin') return activePath.value === '/admin' || activePath.value === '/admin/'
  return activePath.value.startsWith(path)
}

const handleLogout = async () => {
  try {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  } catch (error) {
    console.error('Logout failed:', error)
  }
}

const onSearchSubmit = () => {
  const q = searchQuery.value.trim()
  if (!q) return

  const path = route.path
  const searchable = [
    '/admin/shops',
    '/admin/users',
    '/admin/vehicles',
    '/admin/bookings',
  ]

  const target = searchable.find((p) => path.startsWith(p)) || '/admin/shops'
  router.push({ path: target, query: { q } })
}

watch(
  () => route.query.q,
  (q) => {
    const next = String(q || '').trim()
    if (searchQuery.value !== next) searchQuery.value = next
  },
  { immediate: true }
)

watch(searchQuery, (value) => {
  if (searchTimer) window.clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => {
    const q = String(value || '').trim()
    const currentQ = String(route.query.q || '').trim()
    if (q === currentQ) return

    const searchable = [
      '/admin/shops',
      '/admin/users',
      '/admin/vehicles',
      '/admin/bookings',
    ]
    if (!searchable.some(p => route.path.startsWith(p))) return

    const nextQuery = { ...route.query }
    if (q) nextQuery.q = q
    else delete nextQuery.q
    router.replace({ query: nextQuery })
  }, 400)
})

onMounted(() => {
  adminStore.load().catch(() => {})
  clockTimer = window.setInterval(() => {
    nowTick.value = Date.now()
  }, 1000)
})

onBeforeUnmount(() => {
  if (searchTimer) window.clearTimeout(searchTimer)
  if (clockTimer) window.clearInterval(clockTimer)
})

const cambodiaClockLabel = computed(() => cambodiaDateTimeLabel(new Date(nowTick.value)))
const cambodiaCurrentYear = computed(() => cambodiaYear(new Date(nowTick.value)))
</script>

<template>
  <div class="admin-app" :class="{ 'is-admin': isAdmin }">
    <aside class="admin-sidebar">
       <div class="admin-brand">
         <div class="brand-badge" aria-hidden="true">
           <img class="brand-logo" :src="logoUrl" alt="Chong Choul" style="background-color: white; padding: 6px; border-radius: 150px; width: 96px; height: 80px; object-fit: contain;" />
         </div>
         <div class="brand-text">
           <span class="brand-name">Chong <span class="brand-cyan">Choul</span></span>
         </div>
       </div>

      <nav class="admin-nav">
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="admin-nav-item"
          :class="{ active: isActive(item.to) }"
        >
          <i :class="item.icon" aria-hidden="true"></i>
          <span>{{ item.label }}</span>
        </RouterLink>
      </nav>

      <button type="button" class="admin-logout" @click="showLogoutConfirm = true">
        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
        <span>Logout</span>
      </button>
    </aside>

    <div class="admin-main">
      <header class="admin-topbar">
        <form class="topbar-search" @submit.prevent="onSearchSubmit">
          <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search for shops, bookings, or users..."
            aria-label="Search"
          />
        </form>

        <div class="topbar-actions">
          <div class="topbar-time" :title="`Cambodia time (${CAMBODIA_TIMEZONE})`">
            <span class="topbar-time-year">{{ cambodiaCurrentYear }}</span>
            <span class="dot">•</span>
            <span class="topbar-time-clock">{{ cambodiaClockLabel }}</span>
          </div>
          <button 
            type="button" 
            class="icon-btn" 
            :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'" 
            @click="toggleTheme"
          >
            <i :class="isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon'" aria-hidden="true"></i>
          </button>
          <button type="button" class="icon-btn" title="Notifications">
            <i class="fa-regular fa-bell" aria-hidden="true"></i>
            <span class="notif-dot" aria-hidden="true"></span>
          </button>

            <div class="topbar-user">
              <div class="user-meta">
                <div class="user-name">{{ adminName }}</div>
                <div class="user-status">{{ adminRole }}</div>
              </div>
              <div class="user-avatar" :aria-label="adminName">
                {{ adminInitials }}
              </div>
            </div>
        </div>
      </header>

      <main class="admin-content">
        <div class="admin-content-inner">
          <RouterView />
          <footer class="admin-footer">
            <span>© 2026 Chong Choul Rental Management System. All rights reserved.</span>
          </footer>
        </div>
      </main>
    </div>
  </div>

   <ToastStack />

   <ConfirmModal
     v-model="showLogoutConfirm"
     title="Logout"
     message="Are you sure you want to logout?"
     cancel-text="Cancel"
     confirm-text="Yes, Logout"
     variant="danger"
     @confirm="handleLogout"
   />
</template>
