<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useTheme } from '../../composables/useTheme'
import { userService } from '../../services/database.js'
import { useAdminStore } from '../../stores/adminStore.js'
import { CAMBODIA_TIMEZONE, cambodiaDateTimeLabel, cambodiaYear } from '../../utils/cambodiaTime.js'
import '../../css/DashboardAdmin.css'
import ConfirmModal from '../../components/ConfirmModal.vue'
import Logo from '../../components/Logo.vue'

const route = useRoute()
const router = useRouter()
const { isDark, toggleTheme } = useTheme()
const adminStore = useAdminStore()
const { t } = useI18n()

const searchQuery = ref('')
const showLogoutConfirm = ref(false)
let searchTimer = null
let clockTimer = null
const nowTick = ref(Date.now())

const navItems = [
  { label: 'Dashboard', to: '/admin/dashboard', icon: 'fa-solid fa-table-cells-large' },
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
const adminName = computed(() => currentUser.value?.name || 'Alex Johnson')
const adminRole = computed(() => (currentUser.value?.role || 'MASTER ADMIN').toString().toUpperCase())
const adminInitials = computed(() => {
  const parts = adminName.value.trim().split(/\s+/).filter(Boolean)
  if (parts.length === 0) return 'AJ'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase()
})

const activePath = computed(() => route.path)
const isActive = (path) => activePath.value === path || activePath.value.startsWith(`${path}/`)

const handleLogout = async () => {
  try {
    await userService.logout?.()
  } catch {
    // ignore
  }

  try {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token')
    if (token) {
      await fetch('/api/users/logout', {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
      })
    }
  } catch {
    // ignore
  } finally {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
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
    '/admin/coupons',
    '/admin/categories',
    '/admin/cities',
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

watch(
  () => route.path,
  () => {
    const next = String(route.query.q || '').trim()
    if (searchQuery.value !== next) searchQuery.value = next
  }
)

watch(searchQuery, (value) => {
  const path = route.path
  const searchable =
    path.startsWith('/admin/shops') ||
    path.startsWith('/admin/users') ||
    path.startsWith('/admin/vehicles') ||
    path.startsWith('/admin/bookings') ||
    path.startsWith('/admin/coupons') ||
    path.startsWith('/admin/categories') ||
    path.startsWith('/admin/cities')

  if (!searchable) return
  if (searchTimer) window.clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => {
    const q = String(value || '').trim()
    const nextQuery = { ...route.query }
    if (q) nextQuery.q = q
    else delete nextQuery.q
    router.replace({ query: nextQuery })
  }, 250)
})

onMounted(() => {
  adminStore.load().catch(() => {})
  clockTimer = window.setInterval(() => {
    nowTick.value = Date.now()
  }, 1000)
})

onBeforeUnmount(() => {
  if (searchTimer) window.clearTimeout(searchTimer)
  searchTimer = null
  if (clockTimer) window.clearInterval(clockTimer)
  clockTimer = null
})

const cambodiaClockLabel = computed(() => cambodiaDateTimeLabel(new Date(nowTick.value)))
const cambodiaCurrentYear = computed(() => cambodiaYear(new Date(nowTick.value)))
</script>

<template>
  <div class="admin-app">
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <Logo class="admin-brand-logo" to="/admin/dashboard" theme="dark" size="md" :stacked="true" :showTagline="false" />
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
          <button type="button" class="icon-btn" :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'" @click="toggleTheme">
            <i :class="isDark ? 'fa-regular fa-sun' : 'fa-regular fa-moon'" aria-hidden="true"></i>
          </button>
          <button type="button" class="icon-btn" title="Notifications">
            <i class="fa-regular fa-bell" aria-hidden="true"></i>
            <span class="notif-dot" aria-hidden="true"></span>
          </button>

          <div class="topbar-user">
            <div class="user-meta">
              <div class="user-name">{{ adminName }}</div>
              <div class="user-role">{{ adminRole }}</div>
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

  <ConfirmModal
    v-model="showLogoutConfirm"
    title="Logout"
    :message="t('confirmLogout') === 'confirmLogout' ? 'Are you sure you want to logout?' : t('confirmLogout')"
    :cancel-text="t('cancel') === 'cancel' ? 'Cancel' : t('cancel')"
    confirm-text="Yes"
    variant="danger"
    @confirm="handleLogout"
  />
</template>
