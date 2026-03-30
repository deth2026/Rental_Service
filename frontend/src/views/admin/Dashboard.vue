<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
import userService from '../../services/userService.js'
import CountUp from '../../components/CountUp.vue'
import '../../css/DashboardAdmin.css'

const toast = useToast()
const router = useRouter()
const admin = useAdminStore()

// Fast access helpers (non-reactive for core logic)
const getArr = (v) => v || []

const currentUser = computed(() => userService.getCurrentUser())
const adminName = computed(() => {
  const name = currentUser.value?.name
  return name ? name.split(' ')[0] : 'Admin'
})

const animated = ref(false)

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

const menu = [
  { label: 'Dashboard', icon: 'fa-solid fa-chart-line', to: '/admin', active: true },
  { label: 'User Management', icon: 'fa-solid fa-users', to: '/admin/users' },
  { label: 'Shop Management', icon: 'fa-solid fa-shop', to: '/admin/shops' },
  { label: 'Vehicle Management', icon: 'fa-solid fa-motorcycle', to: '/admin/vehicles' },
  { label: 'Bookings', icon: 'fa-solid fa-calendar-check', to: '/admin/bookings' },
  { label: 'Reports', icon: 'fa-solid fa-file-invoice-dollar', to: '/admin/reports' },
  { label: 'Settings', icon: 'fa-solid fa-gear', to: '/admin/settings' }
];

const statusBadgeClass = (status) => {
  const s = String(status || '').toUpperCase()
  if (s === 'ACTIVE' || s === 'VERIFIED') return 'badge-green';
  if (s === 'PENDING') return 'badge-yellow';
  return 'badge-red';
};

const handleLogout = async () => {
  try {
    await userService.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error);
    router.push('/login');
  }
};

const triggerAnimation = () => {
  animated.value = false
  nextTick(() => { requestAnimationFrame(() => { animated.value = true }) })
}

onMounted(() => {
  admin.load().then(triggerAnimation).catch(() => {})
})

watch(
  () => getArr(admin.bookingGrossByDay).map((d) => `${d.key}:${d.value || 0}`).join('|'),
  triggerAnimation
)
</script>

<template>
  <div class="admin-app">
    
    <aside class="admin-sidebar">
      <div class="admin-brand">
        <div class="brand-badge">
          <img class="brand-logo" src="/images/logo-removebg.png" alt="Logo" />
        </div>
        <div class="brand-text">
          <div class="brand-name">CHONG <span class="brand-cyan">CHOUL</span></div>
          <div class="user-status">ADMIN PANEL</div>
        </div>
      </div>
      
      <nav class="admin-nav">
        <router-link v-for="item in menu" :key="item.label" :to="item.to" 
           :class="{ 'active': item.active }"
           class="admin-nav-item">
          <i :class="item.icon"></i>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <button @click="handleLogout" class="admin-logout">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
      </button>
    </aside>

    <main class="admin-main">
      <header class="admin-topbar">
        <div class="topbar-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Search for users, shops, vehicles..." />
        </div>
        
        <div class="topbar-actions">
          <button class="icon-btn">
            <i class="fa-regular fa-bell"></i>
            <span class="notif-dot"></span>
          </button>
          
          <div class="topbar-user">
            <div class="user-meta">
              <div class="user-name">{{ adminName }}</div>
              <div class="user-role">Super Admin</div>
            </div>
            <div class="user-avatar">
              {{ adminName.charAt(0) }}
            </div>
          </div>
        </div>
      </header>

      <div class="admin-content">
        <div class="admin-content-inner">
          <header class="page-head">
            <div>
              <div class="breadcrumb">DASHBOARD <span class="dot">•</span> OVERVIEW</div>
              <h1 class="page-title">Admin Dashboard</h1>
              <p class="page-subtitle">Welcome back, {{ adminName }}. Here's a snapshot of your rental ecosystem.</p>
            </div>
            <div class="head-actions">
              <button class="btn btn-primary" @click="router.push('/admin/users?create=1')">
                <i class="fa-solid fa-plus"></i> Add New Admin
              </button>
            </div>
          </header>

          <div class="stat-grid mt-24">
            <div v-for="stat in stats" :key="stat.label" class="stat-card" @click="router.push(stat.to)">
              <div class="stat-top">
                <div class="stat-icon" :class="stat.tint">
                  <i :class="stat.icon"></i>
                </div>
                <div class="stat-trend" :class="stat.trend >= 0 ? 'trend-up' : 'trend-down'">
                  <i :class="stat.trend >= 0 ? 'fa-solid fa-arrow-trend-up' : 'fa-solid fa-arrow-trend-down'"></i>
                  {{ Math.abs(stat.trend) }}%
                </div>
              </div>
              <div class="stat-label">{{ stat.label }}</div>
              <div class="stat-value">
                <CountUp v-if="animated" :end-val="stat.raw" :options="{ prefix: stat.key === 'revenue' ? '$' : '' }" />
                <span v-else>{{ stat.formatter(stat.raw) }}</span>
              </div>
            </div>
          </div>

          <div class="card mt-24">
            <div class="card-head">
              <div>
                <h3 class="card-title">Recent User Registrations</h3>
                <p class="card-subtitle">Manage newly joined customers and shop owners</p>
              </div>
              <div class="filter-dropdown">
                <button class="btn btn-chip">
                  All Status <i class="fa-solid fa-chevron-down ms-8"></i>
                </button>
              </div>
            </div>
            
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>User Details</th>
                    <th>Email Address</th>
                    <th>Platform Role</th>
                    <th>Current Status</th>
                    <th class="actions">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in admin.state.users.slice(0, 5)" :key="user.id">
                    <td>
                      <div class="shop-cell">
                        <div class="user-bubble">
                          <img v-if="user.img_url" :src="userService.getAvatarUrl(user.img_url)" class="user-bubble__img" />
                          <span v-else>{{ user.name.charAt(0) }}</span>
                        </div>
                        <div class="shop-meta">
                          <span class="shop-name">{{ user.name }}</span>
                          <span class="shop-id">ID: #{{ user.id }}</span>
                        </div>
                      </div>
                    </td>
                    <td>{{ user.email }}</td>
                    <td>
                      <span class="badge" :class="user.role === 'shop_owner' ? 'badge-purple' : 'badge-blue'">
                        {{ user.role === 'shop_owner' ? 'Shop Owner' : 'Customer' }}
                      </span>
                    </td>
                    <td>
                      <span class="badge" :class="statusBadgeClass(user.status)">
                        {{ String(user.status || 'PENDING').toUpperCase() }}
                      </span>
                    </td>
                    <td class="actions">
                      <button class="icon-action" title="View details" @click="router.push('/admin/users')">
                        <i class="fa-solid fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="!admin.state.users.length">
                    <td colspan="5" class="p-24 text-center muted">No recent registrations found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <footer class="admin-footer">
            &copy; 2026 CHONG CHOUL RENTAL SERVICE <span class="dot">•</span> POWERED BY ECO-SYSTEM
          </footer>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.mt-24 { margin-top: 24px; }
.ms-8 { margin-left: 8px; }
</style>
