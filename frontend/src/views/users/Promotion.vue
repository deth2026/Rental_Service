<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { couponApi, vehicleApi, shopApi } from '@/services/api'
import { userService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import UserNavbar from '@/components/UserNavbar.vue'
import { getCachedSelectedShop } from '@/utils/shopSelectionCache'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref('')
const promotions = ref([])
const activeFilter = ref('all')
const dealsSection = ref(null)
const ownerShopIds = ref([])
const currentUser = computed(() => userService.getCurrentUser())
const currentUserRole = computed(() => {
  const role = String(currentUser.value?.role || currentUser.value?.user_type || 'customer').toLowerCase()
  return role
})
const isShopTeam = computed(() => {
  return ['shop_owner', 'owner', 'shop_staff'].includes(currentUserRole.value)
})

const selectedShopInfo = ref({ id: null, name: '' })

const parseShopIdParam = (value) => {
  if (value == null) return null
  const normalized = Number(String(value).trim())
  return Number.isFinite(normalized) && normalized > 0 ? normalized : null
}

const refreshSelectedShopFilter = () => {
  const queryId = parseShopIdParam(route.query.shop_id)
  if (queryId) {
    const cached = getCachedSelectedShop()
    const name = cached.id === queryId ? cached.name : ''
    selectedShopInfo.value = { id: queryId, name }
    return
  }
  selectedShopInfo.value = getCachedSelectedShop()
}


const selectedShopFilterId = computed(() => selectedShopInfo.value.id)
const selectedShopDisplayName = computed(() => selectedShopInfo.value.name)
const promotionsEmptyMessage = computed(() => {
  if (!selectedShopFilterId.value) {
    return 'No active promotions are available right now.'
  }
  const displayName = selectedShopDisplayName.value || 'this shop'
  return `No active promotions are available for ${displayName} at the moment.`
})

const navItems = [
  { label: 'My Bookings', route: '/my-bookings' },
  { label: 'Profile', route: '/user/profile' }
]

const filterItems = [
  { id: 'all', label: 'All Deals', icon: 'fa-solid fa-circle-dot' },
  { id: 'car', label: 'Cars', icon: 'fa-solid fa-car-side' },
  { id: 'moto', label: 'Motorcycles', icon: 'fa-solid fa-motorcycle' },
  { id: 'bike', label: 'Bikes', icon: 'fa-solid fa-bicycle' }
]

const categoryVisuals = {
  car: {
    image: 'https://images.unsplash.com/photo-1553440569-bcc63803a83d?auto=format&fit=crop&w=900&q=80',
    badge: 'car',
    titlePrefix: 'Weekend',
    description: 'Ideal for city escapes and smooth highway drives with extra comfort.',
    accent: '#1d6fff'
  },
  moto: {
    image: 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=900&q=80',
    badge: 'moto',
    titlePrefix: 'Adventure',
    description: 'Built for fast urban rides and flexible day trips around town.',
    accent: '#2563eb'
  },
  bike: {
    image: 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?auto=format&fit=crop&w=900&q=80',
    badge: 'bike',
    titlePrefix: 'Eco Ride',
    description: 'A simple choice for daily commuting and affordable local exploration.',
    accent: '#0f6ad9'
  }
}

const activeNavLabel = computed(() => {
  const currentPath = route.path
  const matchedItem = navItems.find((item) => item.route && currentPath.startsWith(item.route))
  return matchedItem?.label || 'My Bookings'
})

const handleLogout = async () => {
  await userService.logout()
  localStorage.removeItem('auth_token')
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

const inferCategory = (sourceValue, index = 0) => {
  const source = String(sourceValue || '').toLowerCase()
  if (/(bike|bicycle|cycle)/.test(source)) return 'bike'
  if (/(moto|motor|scooter)/.test(source)) return 'moto'
  if (/(car|sedan|suv|van)/.test(source)) return 'car'

  const fallback = ['car', 'moto', 'bike']
  return fallback[index % fallback.length]
}


const titleFromCode = (code, category) => {
  const cleaned = String(code || '')
    .replace(/[_-]+/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

  if (cleaned) {
    return cleaned.replace(/\b\w/g, (char) => char.toUpperCase())
  }

  return `${categoryVisuals[category].titlePrefix} Deal`
}

const buildDescription = (coupon, category) => {
  if (coupon.minimum_amount) {
    return `Valid for rentals from $${Number(coupon.minimum_amount).toFixed(0)} and above. ${categoryVisuals[category].description}`
  }

  return categoryVisuals[category].description
}

const normalizeDate = (value) => {
  if (!value) return null
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return null
  date.setHours(0, 0, 0, 0)
  return date
}

const resolveCouponStatus = (coupon) => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const isActiveFlag = !(
    coupon.is_active === false ||
    coupon.is_active === 0 ||
    coupon.is_active === '0'
  )

  if (!isActiveFlag) {
    return { key: 'inactive', label: 'Inactive', usable: false }
  }

  const validFrom = normalizeDate(coupon.valid_from)
  if (validFrom && validFrom > today) {
    return { key: 'scheduled', label: `Starts ${formatDate(validFrom)}`, usable: false }
  }

  const validUntil = normalizeDate(coupon.valid_until)
  if (validUntil && validUntil < today) {
    return { key: 'expired', label: 'Expired', usable: false }
  }

  if (!validUntil && !validFrom) {
    return { key: 'active', label: 'Active', usable: true }
  }

  const isEndingSoon = validUntil && validUntil.getTime() - today.getTime() <= 1000 * 60 * 60 * 24
  return {
    key: 'active',
    label: isEndingSoon ? 'Ends soon' : 'Active',
    usable: true
  }
}

const buildPrimaryValue = (coupon) => {
  if (coupon.discount_percent !== null && coupon.discount_percent !== undefined) {
    return `${Number(coupon.discount_percent).toFixed(Number(coupon.discount_percent) % 1 === 0 ? 0 : 2)}% OFF`
  }

  if (coupon.discount_amount !== null && coupon.discount_amount !== undefined) {
    return `$${Number(coupon.discount_amount).toFixed(Number(coupon.discount_amount) % 1 === 0 ? 0 : 2)} OFF`
  }

  return 'Special Deal'
}

const buildRibbon = (coupon) => {
  if (coupon.discount_percent !== null && coupon.discount_percent !== undefined) {
    return `${Number(coupon.discount_percent).toFixed(0)}% OFF`
  }

  if (coupon.discount_amount !== null && coupon.discount_amount !== undefined) {
    return `$${Number(coupon.discount_amount).toFixed(0)} OFF`
  }

  return 'LIMITED'
}

const formatDate = (value) => {
  if (!value) return 'No expiry date'

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }).format(date)
}

const isCouponActive = (coupon) => {
  if (coupon.is_active === false || coupon.is_active === 0 || coupon.is_active === '0') return false

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  if (coupon.valid_from) {
    const validFrom = new Date(coupon.valid_from)
    if (!Number.isNaN(validFrom.getTime())) {
      validFrom.setHours(0, 0, 0, 0)
      if (validFrom > today) return false
    }
  }

  if (!coupon.valid_until) return true

  const validUntil = new Date(coupon.valid_until)
  if (Number.isNaN(validUntil.getTime())) return false
  validUntil.setHours(0, 0, 0, 0)

  return validUntil >= today
}

const normalizeCollection = (response) => {
  const payload = response?.data?.data || response?.data || []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  return []
}

const loadVehiclesForShop = async (shopId) => {
  let currentPage = 1
  let lastPage = 1
  const vehicles = []

  while (currentPage <= lastPage) {
    const response = await vehicleApi.getAll({ shop_id: shopId, page: currentPage })
    const pageItems = normalizeCollection(response)
    vehicles.push(...pageItems)


    const pagination = response?.data
    lastPage = Number(pagination?.last_page || pagination?.meta?.last_page || 1)
    currentPage += 1
  }

  return vehicles
}

const loadOwnerShops = async () => {
  if (!isShopTeam.value || !currentUser.value?.id) {
    ownerShopIds.value = []
    return
  }

  try {
    const response = await shopApi.getAll({
      owner_id: currentUser.value.id,
      active_only: true
    })
    const shops = normalizeCollection(response)
    ownerShopIds.value = shops
      .map((shop) => Number(shop.id))
      .filter((id) => Number.isFinite(id) && id > 0)
  } catch (err) {
    console.error('Failed to load owner shops:', err)
    ownerShopIds.value = []
  }
}

const mapCouponsToPromotions = (coupons, vehiclesByShop) => {
  return coupons
    .map((coupon, couponIndex) => {
      const shopId = Number(coupon.shop_id)
      const vehicles = (vehiclesByShop.get(shopId) || []).filter((vehicle) => Number(vehicle.shop_id) === shopId)
      const vehicle = vehicles.find((v) => Number(v.id) === Number(coupon.vehicle_id)) || vehicles[0]

      if (!vehicle) {
        return null
      }

      const statusInfo = resolveCouponStatus(coupon)
      const titleSource = `${vehicle.type || ''} ${vehicle.brand || ''} ${vehicle.model || ''} ${vehicle.name || ''}`
      const category = inferCategory(titleSource, couponIndex)
      const visual = categoryVisuals[category]
      const vehicleTitle = [vehicle.brand, vehicle.model].filter(Boolean).join(' ') || vehicle.name || titleFromCode(coupon.code, category)
      const vehicleImage = vehicle.photo_urls?.[0] || vehicle.image_url_full || visual.image
      const shopName = vehicle.shop?.name || coupon.shop?.name || 'This shop'

      return {
        id: `${coupon.id}-${vehicle.id}`,
        couponId: coupon.id,
        vehicleId: vehicle.id,
        shopId,
        code: coupon.code || `COUPON-${coupon.id}`,
        category,
        title: vehicleTitle,
        description: `${buildPrimaryValue(coupon)} from ${shopName}. ${buildDescription(coupon, category)}`,
        validUntil: coupon.valid_until,
        validFrom: coupon.valid_from,
        minimumAmount: coupon.minimum_amount,
        usageLimit: coupon.usage_limit,
        primaryValue: buildPrimaryValue(coupon),
        ribbon: buildRibbon(coupon),
        image: vehicleImage,
        badge: visual.badge,
        accent: visual.accent,
        shopName,
        dailyRate: Number(vehicle.price_per_day || 0),
        insuranceFee: Number(vehicle.insurance_fee || 0),
        taxesFee: Number(vehicle.taxes_fee || 0),
        couponStatus: statusInfo.key,
        couponStatusLabel: statusInfo.label,
        couponUsable: statusInfo.usable,
      }
    })
    .filter(Boolean)
}

const fetchPromotions = async () => {
  try {
    loading.value = true
    error.value = ''

    const couponResponse = await couponApi.getAll()
    let coupons = normalizeCollection(couponResponse).filter((coupon) => Number(coupon.shop_id))
    if (isShopTeam.value && ownerShopIds.value.length) {
      coupons = coupons.filter((coupon) => ownerShopIds.value.includes(Number(coupon.shop_id)))
    }
    if (selectedShopFilterId.value) {
      coupons = coupons.filter((coupon) => Number(coupon.shop_id) === selectedShopFilterId.value)
    }
    const uniqueShopIds = [...new Set(coupons.map((coupon) => Number(coupon.shop_id)).filter(Boolean))]
    const vehiclesPerShop = await Promise.all(uniqueShopIds.map((shopId) => loadVehiclesForShop(shopId)))
    const vehiclesByShop = new Map(uniqueShopIds.map((shopId, index) => [shopId, vehiclesPerShop[index] || []]))

    promotions.value = mapCouponsToPromotions(coupons, vehiclesByShop)
  } catch (err) {
    console.error('Error fetching promotions:', err)
    error.value = 'Failed to load promotions.'
    promotions.value = []
  } finally {
    loading.value = false
  }
}

const filteredPromotions = computed(() => {
  if (activeFilter.value === 'all') return promotions.value
  return promotions.value.filter((item) => item.category === activeFilter.value)
})


const activePromotionCount = computed(() => promotions.value.length)

const scrollToDeals = () => {
  dealsSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const usePromotion = (item) => {
  if (!item.couponUsable) return
  router.push({
    name: 'booking',
    params: { id: item.vehicleId },
    query: {
      coupon_id: item.couponId,
      coupon_code: item.code,
      insuranceFee: item.insuranceFee,
      includeInsurance: 'true',
      includeTaxes: 'true',
      shop_id: item.shopId
    }
  })
}

const initializePromotions = async () => {
  refreshSelectedShopFilter()
  await loadOwnerShops()
  await fetchPromotions()
}

watch(
  () => route.query.shop_id,
  () => {
    refreshSelectedShopFilter()
    fetchPromotions()
  }
)

onMounted(initializePromotions)
</script>

<template>
  <div class="promotion-page">
    <UserNavbar
      :nav-items="navItems"
      :active-label="activeNavLabel"
      :show-fallback-message="false"
      @logout-request="handleLogout"
    />

    <main>
      <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <div class="hero-tag">
            <i class="fa-solid fa-sparkles"></i>
            <span>Limited Time Offers</span>
          </div>

          <h1>
            Exclusive
            <span>Promotions</span>
          </h1>

          <p>
            Save big on car, moto, and bike rentals. Grab our best deals before they expire.
          </p>

          <div class="hero-actions">
            <button class="hero-primary" @click="scrollToDeals">
              View All Deals
              <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button class="hero-secondary" @click="router.push('/view_shop')">Learn More</button>
          </div>
        </div>
      </section>

      <section ref="dealsSection" class="deals-section">
        <div class="section-header">
          <!-- <div class="section-badge">
            <i class="fa-solid fa-sparkles"></i>
            <span>HOT DEALS</span>
          </div> -->

          <h2>Current Promotions</h2>
          <p>Browse our latest offers across all vehicle categories and save on your next rental.</p>

          <div class="filter-row">
            <button
              v-for="item in filterItems"
              :key="item.id"
              class="filter-pill"
              :class="{ active: activeFilter === item.id }"
              @click="activeFilter = item.id"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
            </button>
          </div>
        </div>

        <div v-if="loading" class="status-card">Loading promotions...</div>
        <div v-else-if="error" class="status-card error">
          {{ error }}
        </div>
        <div v-else-if="filteredPromotions.length === 0" class="status-card">
          {{ promotionsEmptyMessage }}
        </div>

        <div v-else>
          <div class="promo-grid">
          <article v-for="item in filteredPromotions" :key="item.id" class="promo-card">
            <div class="promo-media">
              <img :src="item.image" :alt="item.title" />
              <span class="promo-ribbon">{{ item.ribbon }}</span>
              <span class="promo-type">{{ item.badge }}</span>
            </div>

            <div class="promo-body">
              <div class="promo-status" :class="`promo-status--${item.couponStatus}`">
                {{ item.couponStatusLabel }}
              </div>
              <h3>{{ item.title }}</h3>
              <p>{{ item.description }}</p>

              <div class="promo-validity">
                <i class="fa-regular fa-clock"></i>
                <span>Valid until {{ formatDate(item.validUntil) }}</span>
              </div>


              <div class="promo-meta">
                <div class="promo-value">
                  <span class="promo-code">{{ item.code }}</span>
                  <strong>{{ item.primaryValue }}</strong>
                  <small class="promo-shop">{{ item.shopName }} · ${{ item.dailyRate.toFixed(2) }}/day</small>
                </div>
                <button
                  class="book-btn"
                  :disabled="!item.couponUsable"
                  @click="usePromotion(item)"
                >
                  {{ item.couponUsable ? 'Use Now' : 'Unavailable' }}
                </button>
              </div>
            </div>
          </article>
          </div>
        </div>
      </section>
    </main>

    <!-- Common Footer -->
    <CommonFooter />
  </div>
</template>

<style scoped>
.promotion-page {
  min-height: 100vh;
  background: #f3f6fb;
  color: #10213a;
  overflow: hidden;
}

.btn-reset {
  border: 0;
  background: transparent;
  padding: 0;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 30;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 18px 60px;
  background: rgba(255, 255, 255, 0.95);
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  backdrop-filter: blur(14px);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.2rem;
  font-weight: 800;
}

.brand-icon {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #fff;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-link {
  padding: 10px 16px;
  border-radius: 999px;
  color: #4b5563;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-link:hover,
.nav-link.active {
  background: #e8f0ff;
  color: #165df5;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-display-name {
  font-weight: 600;
  color: #334155;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  border: none;
  overflow: hidden;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #3b82f6, #1e293b);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.logout-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 12px;
  border: none;
  background: #fee2e2;
  color: #dc2626;
  font-weight: 700;
  cursor: pointer;
}

.hero-section {
  position: relative;
  min-height: 600px;
  padding: 86px 24px 52px;
  background-image:
    linear-gradient(90deg, rgba(3, 17, 40, 0.86) 0%, rgba(3, 17, 40, 0.56) 33%, rgba(3, 17, 40, 0.18) 58%, rgba(3, 17, 40, 0.2) 100%),
    url('https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=1800&q=80');
  background-size: cover;
  background-position: center;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(255, 255, 255, 0) 60%, #f3f6fb 100%);
}

.hero-content {
  position: relative;
  z-index: 1;
  max-width: 560px;
  margin-left: 24px;
  color: #fff;
}

.hero-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  border-radius: 999px;
  background: rgba(19, 98, 255, 0.38);
  border: 1px solid rgba(255, 255, 255, 0.16);
  font-weight: 700;
  margin-bottom: 24px;
}

.hero-content h1 {
  margin: 0;
  font-size: clamp(3rem, 7vw, 5.4rem);
  line-height: 0.96;
  letter-spacing: -0.04em;
}

.hero-content h1 span {
  display: block;
  color: #1d75ff;
}

.hero-content p {
  margin: 24px 0 34px;
  font-size: 1.05rem;
  line-height: 1.65;
  color: rgba(255, 255, 255, 0.88);
}

.hero-actions {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.hero-primary,
.hero-secondary {
  min-width: 170px;
  padding: 16px 28px;
  border-radius: 16px;
  font-size: 1.05rem;
  font-weight: 700;
  cursor: pointer;
}


.hero-primary {
  border: none;
  background: linear-gradient(135deg, #1674ff, #1457ff);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  box-shadow: 0 18px 40px rgba(22, 116, 255, 0.35);
}

.hero-secondary {
  border: 1px solid rgba(255, 255, 255, 0.5);
  background: rgba(255, 255, 255, 0.16);
  color: #fff;
  backdrop-filter: blur(10px);
}

.deals-section {
  max-width: 1340px;
  margin: 12px auto 0;
  padding: 0 16px 40px;
}

.section-header {
  display: flex;
  flex-direction: column;
  text-align: center;
  padding: 40px 0 22px;
}

.section-badge {
  margin: 0 0 10px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #1d6fff;
  font-weight: 800;
  margin-bottom: 12px;
}

.section-header h2 {
  margin: 0 0 10px;
  margin-right: 35%;
  font-size: clamp(2rem, 4vw, 2.65rem);
  color: #18253b;
}

.section-header p {
  max-width: 620px;
  margin: 0 auto 24px;
  color: #64748b;
}

.filter-row {
  display: flex;
  justify-content: center;
  margin-right: 32%;
  gap: 12px;
  flex-wrap: wrap;
}

.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 18px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  background: #fff;
  color: #334155;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-pill.active,
.filter-pill:hover {
  background: #1d6fff;
  border-color: #1d6fff;
  color: #fff;
}

.status-card {
  padding: 34px 20px;
  border-radius: 24px;
  background: #fff;
  text-align: center;
  color: #475569;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
}

.status-card.error {
  color: #b91c1c;
  background: #fff1f2;
}

.promo-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}

.promo-card {
  overflow: hidden;
  border-radius: 18px;
  background: #fff;
  border: 1px solid #e2e8f0;
  box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
}

.promo-media {
  position: relative;
  height: 240px;
  background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
}

.promo-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.promo-ribbon,
.promo-type {
  position: absolute;
  top: 14px;
  display: inline-flex;
  align-items: center;
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
}

.promo-ribbon {
  left: 14px;
  background: #1d6fff;
  color: #fff;
}

.promo-type {
  right: 14px;
  background: rgba(255, 255, 255, 0.88);
  color: #475569;
  text-transform: lowercase;
}

.promo-body {
  padding: 22px 18px 16px;
}

.promo-status {
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 999px;
  padding: 4px 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  width: fit-content;
}
.promo-status--active {
  background: #e3f3ff;
  color: #1d6fff;
}
.promo-status--expired {
  background: #fee2e2;
  color: #dc2626;
}
.promo-status--inactive {
  background: #f4f4f5;
  color: #6b7280;
}
.promo-status--scheduled {
  background: #fef3c7;
  color: #b45309;
}

.promo-body h3 {
  margin: 0 0 10px;
  font-size: 1.35rem;
  color: #1e293b;
}

.promo-body p {
  margin: 0;
  min-height: 68px;
  color: #64748b;
  line-height: 1.6;
}

.promo-validity {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 16px;
  padding-bottom: 16px;
  border-bottom: 1px solid #e2e8f0;
  color: #64748b;
  font-size: 0.95rem;
}

.promo-meta {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 16px;
  padding-top: 16px;
}

.promo-value {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.promo-code {
  color: #94a3b8;
  text-decoration: line-through;
  font-size: 0.92rem;
  text-transform: uppercase;
}

.promo-value strong {
  color: #1363ea;
  font-size: 1.9rem;
  line-height: 1;
}

.promo-shop {
  color: #64748b;
  font-size: 0.85rem;
}


.book-btn {
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #1b75ff, #0d62df);
  color: #fff;
  padding: 13px 20px;
  font-weight: 800;
  cursor: pointer;
  white-space: nowrap;
}

@media (max-width: 1100px) {
  .topbar {
    padding: 16px 20px;
  }

  .promo-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 820px) {
  .topbar {
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-links,
  .top-actions {
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
  }

  .hero-content {
    margin-left: 0;
    max-width: 100%;
  }

  .promo-grid {
    grid-template-columns: 1fr;
  }

}

@media (max-width: 640px) {
  .hero-section {
    min-height: 520px;
    padding-top: 54px;
  }

  .hero-primary,
  .hero-secondary {
    width: 100%;
  }

  .promo-meta {
    flex-direction: column;
    align-items: stretch;
  }

  .book-btn {
    width: 100%;
  }
}
</style>
