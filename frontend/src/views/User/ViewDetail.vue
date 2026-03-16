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

const startBooking = () => {
  toast.info('Booking flow coming soon.')
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
            <button type="button" class="btn-reset btn-primary" @click="startBooking">Book now</button>
            <button type="button" class="btn-reset btn-secondary" @click="router.push('/view_shop')">Explore more</button>
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
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.86);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
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
  font-weight: 800;
  font-size: 13px;
  border: 1px solid transparent;
}

.nav-link.active {
  background: rgba(37, 99, 235, 0.12);
  border-color: rgba(37, 99, 235, 0.18);
  color: #1d4ed8;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-display-name {
  font-weight: 800;
  color: rgba(15, 23, 42, 0.75);
  max-width: 24ch;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.content {
  width: min(1100px, 100%);
  margin: 0 auto;
  padding: 18px 16px 34px;
}

.back {
  color: #334155;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 12px;
}

.back:hover {
  background: rgba(148, 163, 184, 0.16);
}

.state {
  margin-top: 18px;
  padding: 16px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 14px 30px rgba(2, 6, 23, 0.06);
}

.state-error {
  border-color: rgba(239, 68, 68, 0.25);
  color: #b91c1c;
  background: rgba(254, 242, 242, 0.9);
}

.card {
  margin-top: 16px;
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 16px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  border-radius: 18px;
  box-shadow: 0 20px 60px rgba(2, 6, 23, 0.08);
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
  padding: 18px 18px 20px;
}

.title {
  margin: 0 0 10px;
  font-size: 1.55rem;
  letter-spacing: -0.02em;
}

.meta {
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: rgba(15, 23, 42, 0.72);
  font-size: 0.95rem;
  margin-bottom: 16px;
}

.price {
  display: grid;
  gap: 2px;
  padding: 14px;
  border-radius: 16px;
  background: rgba(37, 99, 235, 0.06);
  border: 1px solid rgba(37, 99, 235, 0.14);
  margin-bottom: 14px;
}

.price-value {
  font-weight: 900;
  font-size: 1.6rem;
  color: #1d4ed8;
}

.price-sub {
  color: rgba(15, 23, 42, 0.65);
  font-weight: 700;
}

.actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  padding: 11px 14px;
  border-radius: 14px;
  font-weight: 900;
}

.btn-primary {
  background: linear-gradient(135deg, #39a9e1, #2563eb);
  color: #ffffff;
  box-shadow: 0 18px 40px rgba(2, 6, 23, 0.14);
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.16);
  color: #0f172a;
}

@media (max-width: 900px) {
  .card {
    grid-template-columns: 1fr;
  }

  .media img {
    min-height: 240px;
  }
}
</style>

