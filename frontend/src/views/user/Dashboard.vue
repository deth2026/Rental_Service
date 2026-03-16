<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { shopService, userService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import Logo from '@/components/Logo.vue'

const router = useRouter()
const route = useRoute()

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '/bookings' },
  { label: 'Promotions', route: '/promotions' },
]

const activeNav = computed(() => {
  const matched = navItems.find((item) => item.route && route.path.startsWith(item.route))
  return matched?.label || 'Home'
})

const setActiveNav = (item) => {
  if (item?.route) router.push(item.route)
}

const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')

const query = ref('')
const isLoading = ref(false)
const error = ref('')
const shops = ref([])

const filteredShops = computed(() => {
  const q = String(query.value || '').trim().toLowerCase()
  if (!q) return shops.value
  return shops.value.filter((s) => `${s.name || ''} ${s.address || ''} ${s.location || ''}`.toLowerCase().includes(q))
})

const normalizeImageUrl = (value) => {
  const raw = String(value || '').trim()
  if (!raw) return ''
  if (/^(https?:\/\/|data:|blob:)/i.test(raw)) return raw
  const normalized = raw.replace(/\\/g, '/').replace(/^\/+/, '')
  if (normalized.startsWith('storage/')) return `/${normalized}`
  return `/storage/${normalized}`
}

const shopImage = (shop) => normalizeImageUrl(shop?.img_url || shop?.image || shop?.cover || '')

const loadShops = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const data = await shopService.getShops()
    shops.value = Array.isArray(data) ? data : []
  } catch (err) {
    error.value = err?.message || 'Failed to load shops.'
    shops.value = []
  } finally {
    isLoading.value = false
  }
}

const openShop = (shop) => {
  if (!shop?.id) return
  router.push({ name: 'shop-vehicles', params: { id: shop.id } })
}

const openProfile = () => {
  router.push('/user/profile')
}

const handleLogout = async () => {
  await userService.logout()
  router.push('/login')
}

onMounted(loadShops)
</script>

<template>
  <section class="user-dashboard">
    <header class="topbar">
      <Logo class="brand" to="/view_shop" size="md" />

      <nav class="nav-links">
        <button
          v-for="item in navItems"
          :key="item.label"
          type="button"
          class="nav-link"
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
      <section class="hero">
        <div class="hero-inner">
          <h1>Find your next ride in minutes</h1>
          <p>Browse verified rental shops and book cars, motorbikes, and bicycles across Cambodia.</p>
          <div class="hero-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input v-model="query" type="search" placeholder="Search shops by name or city..." />
          </div>
        </div>
      </section>

      <section class="shops">
        <div class="shops-head">
          <h2>Rental Shops</h2>
          <button type="button" class="reload" :disabled="isLoading" @click="loadShops">
            <i class="fa-solid fa-rotate" aria-hidden="true"></i> Refresh
          </button>
        </div>

        <div v-if="isLoading" class="state">Loading shops...</div>
        <div v-else-if="error" class="state state-error">{{ error }}</div>
        <div v-else-if="filteredShops.length === 0" class="state">No shops found.</div>

        <div v-else class="grid">
          <article v-for="shop in filteredShops" :key="shop.id" class="card" @click="openShop(shop)">
            <div class="cover">
              <img v-if="shopImage(shop)" :src="shopImage(shop)" :alt="shop.name" loading="lazy" />
              <div v-else class="cover-fallback">
                <i class="fa-solid fa-shop" aria-hidden="true"></i>
              </div>
            </div>
            <div class="card-body">
              <div class="card-title">{{ shop.name || 'Rental Shop' }}</div>
              <div class="card-meta">
                <span v-if="shop.location || shop.city">
                  <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                  {{ shop.location || shop.city }}
                </span>
                <span v-if="shop.phone">
                  <i class="fa-solid fa-phone" aria-hidden="true"></i>
                  {{ shop.phone }}
                </span>
              </div>
              <div class="card-foot">
                <span class="badge" :class="String(shop.status || '').toLowerCase() === 'active' ? 'ok' : 'muted'">
                  {{ String(shop.status || 'active').toUpperCase() }}
                </span>
                <span class="open">
                  View vehicles <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </span>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>

    <CommonFooter />
  </section>
</template>

<style scoped>
.user-dashboard {
  min-height: 100vh;
  background: #f5f7fb;
  color: #0f172a;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255, 255, 255, 0.82);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 900;
  cursor: pointer;
  white-space: nowrap;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  object-fit: contain;
  padding: 3px;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}

.nav-link {
  border: 1px solid transparent;
  background: transparent;
  padding: 8px 12px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 13px;
  color: #475569;
  cursor: pointer;
}

.nav-link.active {
  background: rgba(37, 99, 235, 0.12);
  border-color: rgba(37, 99, 235, 0.25);
  color: #1d4ed8;
}

.top-actions {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  white-space: nowrap;
}

.user-display-name {
  font-weight: 800;
  color: #334155;
  font-size: 13px;
}

.content {
  max-width: 1180px;
  margin: 0 auto;
  padding: 18px 16px 34px;
}

.hero {
  border-radius: 22px;
  padding: 22px;
  background: radial-gradient(800px 380px at 10% 0%, rgba(14, 165, 233, 0.22), transparent 60%),
    radial-gradient(700px 320px at 100% 0%, rgba(37, 99, 235, 0.18), transparent 55%),
    linear-gradient(180deg, #0b1220, #0f172a);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.hero-inner h1 {
  margin: 0;
  font-size: 28px;
  letter-spacing: -0.3px;
}

.hero-inner p {
  margin: 8px 0 0;
  color: rgba(226, 232, 240, 0.8);
  max-width: 680px;
  line-height: 1.5;
}

.hero-search {
  margin-top: 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  border-radius: 14px;
  background: rgba(148, 163, 184, 0.14);
  border: 1px solid rgba(148, 163, 184, 0.18);
}

.hero-search i {
  color: rgba(226, 232, 240, 0.7);
}

.hero-search input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: #e2e8f0;
}

.shops {
  margin-top: 16px;
}

.shops-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 10px;
}

.shops-head h2 {
  margin: 0;
  font-size: 18px;
  font-weight: 900;
}

.reload {
  height: 36px;
  padding: 0 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: #fff;
  font-weight: 800;
  cursor: pointer;
}

.state {
  padding: 12px;
  border-radius: 14px;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #fff;
  color: #475569;
  font-weight: 700;
}

.state-error {
  border-color: rgba(239, 68, 68, 0.25);
  background: rgba(239, 68, 68, 0.06);
  color: #b91c1c;
}

.grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.card {
  border-radius: 18px;
  overflow: hidden;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.1);
  cursor: pointer;
  transition: transform 150ms ease, box-shadow 150ms ease, border-color 150ms ease;
  box-shadow: 0 12px 30px rgba(2, 6, 23, 0.06);
}

.card:hover {
  transform: translateY(-2px);
  border-color: rgba(37, 99, 235, 0.25);
  box-shadow: 0 18px 44px rgba(2, 6, 23, 0.1);
}

.cover {
  height: 140px;
  background: rgba(148, 163, 184, 0.12);
}

.cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.cover-fallback {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
  color: #64748b;
  font-size: 22px;
}

.card-body {
  padding: 12px 12px 14px;
}

.card-title {
  font-weight: 900;
  font-size: 15px;
}

.card-meta {
  margin-top: 8px;
  display: grid;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
}

.card-meta i {
  width: 14px;
  text-align: center;
  margin-right: 6px;
  color: rgba(100, 116, 139, 0.9);
}

.card-foot {
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.badge {
  display: inline-flex;
  align-items: center;
  height: 26px;
  padding: 0 10px;
  border-radius: 999px;
  font-weight: 900;
  font-size: 11px;
  letter-spacing: 0.3px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  color: #475569;
  background: rgba(148, 163, 184, 0.12);
}

.badge.ok {
  border-color: rgba(34, 197, 94, 0.25);
  color: #166534;
  background: rgba(34, 197, 94, 0.12);
}

.open {
  color: #2563eb;
  font-weight: 900;
  font-size: 12px;
}

@media (max-width: 1050px) {
  .grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .nav-links {
    display: none;
  }
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
