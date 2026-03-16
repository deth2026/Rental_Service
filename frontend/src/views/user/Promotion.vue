<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { couponApi } from '@/services/api'
import { userService } from '../../services/database.js'
import Logo from '@/components/Logo.vue'
import CommonFooter from '../../components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref('')
const promotions = ref([])
const activeFilter = ref('all')
const dealsSection = ref(null)

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '/bookings' },
  { label: 'Promotions', route: '/promotions' }
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

const fallbackCoupons = [
  {
    id: 'fallback-car',
    code: 'WEEKEND30',
    discount_percent: 30,
    valid_from: '2026-03-01',
    valid_until: '2026-03-31',
    minimum_amount: 50,
    usage_limit: 100,
    is_active: true,
    category_hint: 'car'
  },
  {
    id: 'fallback-moto',
    code: 'CITY25',
    discount_percent: 25,
    valid_from: '2026-03-01',
    valid_until: '2026-04-15',
    minimum_amount: 35,
    usage_limit: 100,
    is_active: true,
    category_hint: 'moto'
  },
  {
    id: 'fallback-bike',
    code: 'BIKE40',
    discount_percent: 40,
    valid_from: '2026-03-01',
    valid_until: '2026-03-30',
    minimum_amount: 15,
    usage_limit: 100,
    is_active: true,
    category_hint: 'bike'
  }
]

const currentUser = computed(() => userService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')

const activeNav = computed(() => {
  const currentPath = route.path
  const matchedItem = navItems.find((item) => item.route && currentPath.startsWith(item.route))
  return matchedItem?.label || 'Promotions'
})

const notify = (message) => {
  window.alert(message)
}

const openProfile = () => {
  router.push('/user/profile')
}

const showLogoutConfirm = ref(false)
const handleLogout = () => { showLogoutConfirm.value = true }
const confirmLogout = async () => {
  await userService.logout()
  localStorage.removeItem('auth_token')
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/login')
}

const setActiveNav = (item) => {
  if (item.route) {
    router.push(item.route)
    return
  }

  notify('My Bookings page is not available yet.')
}

const inferCategory = (coupon, index) => {
  if (coupon.category_hint && categoryVisuals[coupon.category_hint]) return coupon.category_hint
  const source = `${coupon.code || ''} ${coupon.description || ''}`.toLowerCase()
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
  if (!coupon.valid_until) return true
  return new Date(coupon.valid_until) >= new Date(new Date().toDateString())
}

const mapCouponsToPromotions = (items) => {
  return items
    .filter((coupon) => isCouponActive(coupon))
    .map((coupon, index) => {
      const category = inferCategory(coupon, index)
      const visual = categoryVisuals[category]

      return {
        id: coupon.id,
        code: coupon.code || `COUPON-${coupon.id}`,
        category,
        title: titleFromCode(coupon.code, category),
        description: buildDescription(coupon, category),
        validUntil: coupon.valid_until,
        validFrom: coupon.valid_from,
        minimumAmount: coupon.minimum_amount,
        usageLimit: coupon.usage_limit,
        primaryValue: buildPrimaryValue(coupon),
        ribbon: buildRibbon(coupon),
        image: visual.image,
        badge: visual.badge,
        accent: visual.accent
      }
    })
}

const fetchPromotions = async () => {
  try {
    loading.value = true
    error.value = ''
    const response = await couponApi.getAll()
    const data = response.data.data || response.data || []
    promotions.value = mapCouponsToPromotions(data)

    if (promotions.value.length === 0) {
      promotions.value = mapCouponsToPromotions(fallbackCoupons)
    }
  } catch (err) {
    console.error('Error fetching promotions:', err)
    promotions.value = mapCouponsToPromotions(fallbackCoupons)
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

const copyCouponCode = async (code) => {
  try {
    if (navigator?.clipboard?.writeText) {
      await navigator.clipboard.writeText(code)
      return
    }
  } catch {
    // Ignore clipboard failures and fall back to alert.
  }

  window.alert(`Coupon code: ${code}`)
}

onMounted(fetchPromotions)
</script>

  <template>
   <div class="promotions">
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
          <div class="section-badge">
            <i class="fa-solid fa-sparkles"></i>
            <span>HOT DEALS</span>
          </div>

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
        <div v-else-if="filteredPromotions.length === 0" class="status-card">
          No active promotions found in the coupons table.
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
                </div>
                <button class="book-btn" @click="copyCouponCode(item.code)">Use Now</button>
              </div>
            </div>
          </article>
          </div>
        </div>
      </section>
    </main>

    <ConfirmModal
      v-model="showLogoutConfirm"
      title="Logout"
      message="Are you sure you want to logout?"
      cancel-text="Cancel"
      confirm-text="Yes"
      variant="danger"
      @confirm="confirmLogout"
    />
  </div>

  <!-- Common Footer -->
  <CommonFooter />
</template>

<style scoped>
.promotion-page {
  min-height: 100vh;
  background: #f3f6fb;
  color: #10213a;
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

.brand-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
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
  margin: -12px auto 0;
  padding: 0 16px 40px;
}

.section-header {
  text-align: center;
  padding: 40px 0 22px;
}

.section-badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #1d6fff;
  font-weight: 800;
  margin-bottom: 12px;
}

.section-header h2 {
  margin: 0 0 10px;
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

  .section-footer {
    flex-direction: column;
    text-align: center;
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
