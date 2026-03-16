<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '../../composables/useToast.js'
import { vehicleApi } from '@/services/api'
import { userService as localUserService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import Logo from '@/components/Logo.vue'

const router = useRouter()
const route = useRoute()
const toast = useToast()

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '/bookings' },
  { label: 'Promotion', route: '/promotions' },
]

const activeNav = computed(() => {
  const matchedItem = navItems.find((item) => item.route && route.path.startsWith(item.route))
  return matchedItem?.label || 'Home'
})

const setActiveNav = (item) => {
  if (item?.route) router.push(item.route)
}

const currentUser = computed(() => localUserService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')

const isLoading = ref(false)
const error = ref('')
const vehicle = ref(null)

const vehicleId = computed(() => Number(route.params.id))

const title = computed(() => {
  const v = vehicle.value || {}
  const brand = String(v.brand || '').trim()
  const model = String(v.model || '').trim()
  return [brand, model].filter(Boolean).join(' ') || `Vehicle #${vehicleId.value || ''}`
})

const pricePerDay = computed(() => Number(vehicle.value?.price_per_day || 0) || 0)

const primaryImage = computed(() => {
  const v = vehicle.value || {}
  return (
    v.image_url ||
    v.img_url ||
    v.image ||
    v.photo ||
    'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=1200&q=80'
  )
})

const handleLogout = async () => {
  try {
    await localUserService.logout()
  } finally {
    router.push('/login')
  }
}

const openProfile = () => {
  router.push('/user/profile')
}

const loadVehicle = async () => {
  if (!vehicleId.value) {
    error.value = 'Invalid vehicle id.'
    return
  }
  isLoading.value = true
  error.value = ''
  try {
    const { data } = await vehicleApi.getById(vehicleId.value)
    vehicle.value = data?.data || data || null
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Failed to load vehicle.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}

onMounted(loadVehicle)
</script>

<template>
  <div class="detail-page">
    <header class="topbar">
      <Logo class="brand" to="/view_shop" size="md" />

      <nav class="nav-links">
        <button
          v-for="item in navItems"
          :key="item.label"
          class="btn-reset nav-link"
          :class="{ active: activeNav === item.label }"
          @click="setActiveNav(item)"
        >
          {{ item.label }}
        </button>
      </nav>

      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <UserProfileMenu @settings="openProfile" @logout="handleLogout" />
      </div>
    </header>

    <main class="content">
      <button type="button" class="btn-reset back" @click="router.back()">
        <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back
      </button>

      <div v-if="isLoading" class="state">Loading vehicle...</div>
      <div v-else-if="error" class="state state-error">{{ error }}</div>

      <section v-else-if="vehicle" class="card">
        <div class="media">
          <img :src="primaryImage" :alt="title" />
        </div>
        <div class="body">
          <h1 class="title">{{ title }}</h1>
          <div class="meta">
            <span v-if="vehicle.year"><strong>Year:</strong> {{ vehicle.year }}</span>
            <span v-if="vehicle.transmission"><strong>Transmission:</strong> {{ vehicle.transmission }}</span>
            <span v-if="vehicle.fuel_type"><strong>Fuel:</strong> {{ vehicle.fuel_type }}</span>
            <span v-if="vehicle.seats"><strong>Seats:</strong> {{ vehicle.seats }}</span>
            <span v-if="vehicle.status"><strong>Status:</strong> {{ vehicle.status }}</span>
          </div>

          <div class="price">
            <div class="price-value">${{ pricePerDay.toFixed(2).replace('.00', '') }}</div>
            <div class="price-sub">per day</div>
          </div>

          <div class="actions">
            <button type="button" class="btn-reset btn-primary" @click="toast.info('Booking flow coming soon.')">
              Request Booking
            </button>
          </div>
        </div>
      </section>
    </main>

    <CommonFooter />
  </div>
</template>

<style scoped>
.detail-page {
  min-height: 100vh;
  background: #f5f7fb;
  color: #0f172a;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 16px;
  background: #ffffff;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  cursor: pointer;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  object-fit: contain;
  background: #dbeafe;
  padding: 2px;
}

.nav-links {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-reset {
  border: 0;
  background: transparent;
  font: inherit;
  cursor: pointer;
}

.nav-link {
  padding: 8px 12px;
  border-radius: 999px;
  color: #475569;
  font-weight: 700;
}

.nav-link.active {
  background: rgba(37, 99, 235, 0.12);
  color: #1d4ed8;
}

.top-actions {
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.user-display-name {
  font-weight: 700;
  color: #334155;
}

.content {
  max-width: 1100px;
  margin: 0 auto;
  padding: 18px 16px 30px;
}

.back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.10);
  background: #fff;
  margin-bottom: 14px;
}

.state {
  padding: 14px;
  border-radius: 14px;
  border: 1px solid rgba(15, 23, 42, 0.10);
  background: #fff;
  font-weight: 700;
}

.state-error {
  border-color: rgba(239, 68, 68, 0.25);
  background: rgba(239, 68, 68, 0.06);
  color: #b91c1c;
}

.card {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 16px;
  border-radius: 18px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.10);
  overflow: hidden;
}

.media img {
  width: 100%;
  height: 100%;
  min-height: 320px;
  object-fit: cover;
  display: block;
}

.body {
  padding: 16px;
}

.title {
  margin: 0;
  font-size: 26px;
}

.meta {
  margin-top: 10px;
  display: grid;
  gap: 6px;
  color: #475569;
  font-size: 14px;
}

.price {
  margin-top: 14px;
  padding: 12px;
  border-radius: 14px;
  background: rgba(37, 99, 235, 0.08);
  border: 1px solid rgba(37, 99, 235, 0.18);
}

.price-value {
  font-size: 22px;
  font-weight: 900;
  color: #1d4ed8;
}

.price-sub {
  margin-top: 2px;
  color: #475569;
  font-weight: 700;
  font-size: 12px;
}

.actions {
  margin-top: 12px;
}

.btn-primary {
  height: 40px;
  padding: 0 14px;
  border-radius: 12px;
  background: #2563eb;
  color: #fff;
  font-weight: 800;
}

@media (max-width: 900px) {
  .card {
    grid-template-columns: 1fr;
  }
}
</style>
