<script setup>
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { userService } from '../../services/database.js';

const route = useRoute();
const router = useRouter();

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, '');
const fallbackImage = 'https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg';

const resolveImageUrl = (value) => {
  const image = value ? String(value).trim() : '';
  if (!image) return fallbackImage;
  if (image.startsWith('http://') || image.startsWith('https://')) return image;
  if (image.startsWith('/')) return `${API_ROOT}${image}`;
  return `${API_ROOT}/storage/${image.replace(/^storage\//, '')}`;
};

const vehicle = ref(null);
const isLoading = ref(false);
const loadError = ref('');
const shopNamesById = ref({});
const avatarLoadFailed = ref(false);

const currentUser = computed(() => userService.getCurrentUser());
const userDisplayName = computed(() => currentUser.value?.name || 'customer');
const normalizeAvatarUrl = (url) => {
  if (!url) return '';
  if (/^(https?:\/\/|data:|blob:)/i.test(url)) return url;
  const normalized = String(url).replace(/\\/g, '/').replace(/^\/+/, '');
  if (normalized.startsWith('storage/')) return `/${normalized}`;
  return `/storage/${normalized}`;
};

const userAvatarUrl = computed(() => {
  if (avatarLoadFailed.value) return '';
  const src = currentUser.value?.avatar_url || currentUser.value?.profile_picture || currentUser.value?.img_url || '';
  return normalizeAvatarUrl(src);
});

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const userInitials = computed(() => {
  const words = String(userDisplayName.value).trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) return 'CU';
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase();
});

const normalizeVehicle = (data) => ({
  ...data,
  rating: data?.rating ?? 4.8
});

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '' },
  { label: 'Promotions', route: '/promotions' }
];

const activeNav = computed(() => {
  const currentPath = route.path;
  const matched = navItems.find((item) => item.route && currentPath.startsWith(item.route));
  return matched?.label || 'Home';
});

const setActiveNav = (item) => {
  if (item.route) {
    router.push(item.route);
    return;
  }
  notify('My Bookings page is not available yet.');
};

const notify = (message) => {
  console.log(message);
};

const routeId = computed(() => Number(route.params.id));

const loadShopName = async (shopId) => {
  if (!shopId || shopNamesById.value[shopId]) return;
  try {
    const response = await axios.get(`${API_BASE_URL}/shops/${shopId}`);
    if (response.data?.name) {
      shopNamesById.value[shopId] = response.data.name;
    }
  } catch (error) {
    console.error(error);
  }
};

const loadVehicle = async (id) => {
  if (!id) {
    vehicle.value = null;
    return;
  }
  vehicle.value = null;

  isLoading.value = true;
  loadError.value = '';
  try {
    const response = await axios.get(`${API_BASE_URL}/vehicles/${id}`);
    vehicle.value = normalizeVehicle(response.data);
    await loadShopName(vehicle.value.shop_id);
  } catch (error) {
    vehicle.value = null;
    loadError.value = 'Could not load this vehicle from database.';
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const vehicleTitle = computed(() => {
  if (!vehicle.value) return '';
  return `${vehicle.value.brand} ${vehicle.value.model} ${vehicle.value.year}`;
});
const availabilityText = computed(() => {
  const status = String(vehicle.value?.status || '').trim().toLowerCase();
  return status === 'available' ? 'Available' : 'Unavailable';
});
const shopName = computed(() => (vehicle.value ? (shopNamesById.value[vehicle.value.shop_id] || 'Unknown Shop') : 'Unknown Shop'));
const primaryImage = computed(() => (vehicle.value ? resolveImageUrl(vehicle.value.image_url) : fallbackImage));
const pickupLocation = computed(() => shopName.value || 'Selected Shop');
const dropoffLocation = computed(() => (shopName.value ? `${shopName.value} Return` : 'Drop-Off Location'));
const pickupTime = ref('10:00 AM');
const dropoffTime = ref('10:00 AM');
const shopAddress = computed(() => vehicle.value?.address || shopName.value);

watch(
  routeId,
  (id) => {
    loadVehicle(id);
  },
  { immediate: true }
);

const pickupDate = ref('2023-11-20');
const dropoffDate = ref('2023-11-21');

const rentalDays = computed(() => {
  const start = new Date(`${pickupDate.value}T00:00:00`);
  const end = new Date(`${dropoffDate.value}T00:00:00`);
  const diffMs = end.getTime() - start.getTime();
  const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
  return Math.max(1, diffDays);
});

const theftInsuranceSelected = ref(false);
const gpsSelected = ref(false);

const addonRates = {
  insurance: 2,
  gps: 3
};

const baseTotal = computed(() => (vehicle.value ? Number(vehicle.value.price_per_day) * rentalDays.value : 0));
const selectedAddonLines = computed(() => {
  const lines = [];
  if (theftInsuranceSelected.value) {
    lines.push({
      key: 'insurance',
      label: 'Theft Insurance',
      amount: addonRates.insurance * rentalDays.value
    });
  }
  if (gpsSelected.value) {
    lines.push({
      key: 'gps',
      label: 'GPS Navigation Tablet',
      amount: addonRates.gps * rentalDays.value
    });
  }
  return lines;
});
const addonsTotal = computed(() => selectedAddonLines.value.reduce((sum, x) => sum + x.amount, 0));
const fee = computed(() => 0);
const grandTotal = computed(() => baseTotal.value + addonsTotal.value + fee.value);

const formatPrice = (n) => Number(n).toFixed(2).replace('.00', '');
const formatDisplayDate = (isoDate) => {
  const [year, month, day] = isoDate.split('-');
  return `${month}/${day}/${year}`;
};

const toFeatureList = (value, fallback = []) => {
  if (!value) return fallback;
  if (Array.isArray(value) && value.length) return value;
  if (typeof value === 'string') {
    const entries = value
      .split(',')
      .map((item) => item.trim())
      .filter(Boolean);
    if (entries.length) return entries;
  }
  return fallback;
};

const featureSections = computed(() => {
  if (!vehicle.value) return [];
  return [
    {
      title: 'Safety',
      caption: 'Stability & alerts',
      items: toFeatureList(vehicle.value.safety_features, ['ABS', 'Airbags', 'Lane keep assist', 'Parking sensors'])
    },
    {
      title: 'Connectivity',
      caption: 'Always connected',
      items: toFeatureList(vehicle.value.connectivity_features, ['Bluetooth', 'USB-C fast charge', 'Apple CarPlay', 'Wireless charging'])
    },
    {
      title: 'Comfort',
      caption: 'Relaxed driving',
      items: toFeatureList(vehicle.value.comfort_features, ['Dual-zone climate', 'Heated seats', 'Ambient lighting', 'Cruise control'])
    }
  ];
});

const journeySteps = computed(() => {
  if (!vehicle.value) return [];
  return [
    { label: 'Pick-Up Location', value: pickupLocation.value },
    { label: 'Pick-Up Date', value: formatDisplayDate(pickupDate.value) },
    { label: 'Pick-Up Time', value: pickupTime.value },
    { label: 'Drop-Off Location', value: dropoffLocation.value },
    { label: 'Drop-Off Date', value: formatDisplayDate(dropoffDate.value) },
    { label: 'Drop-Off Time', value: dropoffTime.value }
  ];
});

const priceRows = computed(() => {
  if (!vehicle.value) return [];
  const rows = [
    {
      label: `Car Rental (${rentalDays.value} day${rentalDays.value > 1 ? 's' : ''})`,
      value: `$${baseTotal.value.toFixed(2)}`
    }
  ];
  selectedAddonLines.value.forEach((line) => {
    rows.push({ label: line.label, value: `$${line.amount.toFixed(2)}` });
  });
  if (fee.value > 0) {
    rows.push({ label: 'Marketplace Fee', value: `$${fee.value.toFixed(2)}` });
  }
  return rows;
});

const totalPayment = computed(() => grandTotal.value.toFixed(2));
const vehicleLocation = computed(() => pickupLocation.value || shopAddress.value || 'Siem Reap, Cambodia');
const goBack = () => {
  const shopId = route.query.shop_id || vehicle.value?.shop_id;
  router.push({
    name: 'vehicles-by-shop',
    query: shopId ? { shop_id: String(shopId) } : {}
  });
};

const goHome = () => {
  router.push('/view_shop');
};

const openProfile = () => {
  router.push('/user/profile');
};

const handleLogout = async () => {
  await userService.logout();
  router.push('/login');
};

</script>
<template>
  <div class="detail-page">
    <header class="topbar">
      <div class="brand" @click="goHome">
        <div class="brand-icon"><i class="fa-solid fa-gift"></i></div>
        <span>Chong Choul</span>
      </div>

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
        <button class="btn-reset avatar" @click="openProfile">
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile photo" class="avatar-image" @error="onAvatarError" />
          <span v-else>{{ userInitials }}</span>
        </button>
        <button class="btn-reset logout-btn" @click="handleLogout">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Logout</span>
        </button>
      </div>
    </header>

    <main class="detail-wrapper">
      <div v-if="isLoading" class="status-card">
        <div class="loading-spinner"></div>
        <p>Loading vehicle details...</p>
      </div>

      <div v-else-if="loadError" class="status-card status-card--error">
        <p>{{ loadError }}</p>
        <button class="btn-reset text-btn" type="button" @click="goBack">Back to Vehicles</button>
      </div>

      <section v-else class="detail-card">
        <div class="detail-card__hero">
          <div>
            <p class="hero-eyebrow">DriveMatch</p>
            <h1>{{ vehicleTitle }}</h1>
            <p class="hero-subtitle">{{ shopName }}</p>
          </div>
          <div class="hero-price">
            <span class="hero-price__value">${{ formatPrice(vehicle.price_per_day) }}</span>
            <span class="hero-price__unit">per day</span>
            <small>Premium auto selection</small>
          </div>
        </div>

        <div class="detail-grid">
          <div class="detail-left">
            <div class="image-panel">
              <img :src="primaryImage" :alt="vehicleTitle" />
              <span class="status-pill">{{ availabilityText }}</span>
            </div>

            <div class="detail-actions">
              <button class="btn-ghost" type="button" @click="goBack">Back to vehicles</button>
              <button class="btn-solid" type="button" @click="goBack">Rent now</button>
            </div>

            <div class="feature-sections">
              <article v-for="section in featureSections" :key="section.title" class="feature-card">
                <div class="feature-card__head">
                  <h3>{{ section.title }}</h3>
                  <span>{{ section.caption }}</span>
                </div>
                <ul>
                  <li v-for="item in section.items.slice(0, 4)" :key="item">{{ item }}</li>
                </ul>
              </article>
            </div>
          </div>

          <aside class="detail-right">
            <article class="panel journey-panel">
              <div class="panel-head">
                <h3>Your Journey</h3>
                <span class="pill">Ready</span>
              </div>
              <ul>
                <li v-for="step in journeySteps" :key="step.label">
                  <span>{{ step.label }}</span>
                  <strong>{{ step.value }}</strong>
                </li>
              </ul>
            </article>

            <article class="panel price-panel">
              <div class="panel-head">
                <h3>Price Breakdown</h3>
                <span class="pill pill--muted">{{ rentalDays }} day{{ rentalDays > 1 ? 's' : '' }}</span>
              </div>
              <ul>
                <li v-for="row in priceRows" :key="row.label">
                  <span>{{ row.label }}</span>
                  <strong>{{ row.value }}</strong>
                </li>
              </ul>
              <div class="panel-total">
                <span>Total Payment</span>
                <strong>${{ totalPayment }}</strong>
              </div>
            </article>

            <article class="panel location-panel">
              <div class="panel-head">
                <h3>My Location</h3>
                <button class="btn-link" type="button" @click="openMainLocation">
                  Open map <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </button>
              </div>
              <p>{{ vehicleLocation }}</p>
              <p class="small-text">Pick-up at Trip Zone Motorbike and Scooter Rental</p>
            </article>
          </aside>
        </div>
      </section>
    </main>

    <footer class="page-footer">
      <div class="footer-col brand-col">
        <h4>Cambodia<span>Rides</span></h4>
        <p>
          Connecting adventurous travelers with the best local vehicle rentals across Cambodia.
        </p>
      </div>
      <div class="footer-col">
        <h5>QUICK LINKS</h5>
        <p>How it works</p>
        <p>Trust & Safety</p>
        <p>Rental Policies</p>
      </div>
      <div class="footer-col">
        <h5>SUPPORT</h5>
        <p>Help Center</p>
        <p>Become a Partner</p>
        <p>Contact Us</p>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.detail-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #f7f8fc 0%, #ecf2ff 100%);
  font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  color: #111a2c;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 5;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  padding: 14px 24px;
  background: #fff;
  border-bottom: 1px solid rgba(20, 33, 61, 0.08);
  box-shadow: 0 2px 12px rgba(15, 23, 42, 0.09);
}

.brand {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #2563eb;
  cursor: pointer;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: #dbeafe;
}

.brand-icon i {
  color: #2563eb;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 24px;
  justify-self: center;
}

.nav-link {
  padding: 10px 28px;
  border-radius: 999px;
  color: #4b5563;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  border: 1px solid transparent;
  transition: 0.2s ease;
}

.nav-link.active,
.nav-link:hover {
  background: #eef2ff;
  border-color: #c7d2fe;
  color: #4338ca;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-reset {
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font: inherit;
}

.user-display-name {
  font-size: 16px;
  font-weight: 800;
  color: #33435d;
  white-space: nowrap;
}

.avatar {
  width: 54px;
  height: 54px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: #f4f8fc;
  color: #9a6a32;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.logout-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  background: #2563eb;
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.logout-btn:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
}

.detail-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px 56px;
}

.status-card {
  margin: 24px 0;
  background: #fff;
  border-radius: 28px;
  padding: 36px;
  text-align: center;
  box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
}

.status-card--error {
  background: #fff0f0;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  margin: 0 auto 14px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-top-color: #1e88e5;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.detail-card {
  background: #fff;
  border-radius: 32px;
  padding: 32px;
  margin-top: 24px;
  box-shadow: 0 40px 90px rgba(15, 23, 42, 0.18);
  border: 1px solid rgba(19, 29, 58, 0.06);
}

.detail-card__hero {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 28px;
  margin-bottom: 28px;
}

.hero-eyebrow {
  margin: 0;
  font-size: 0.8rem;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  color: #7c849c;
}

.detail-card__hero h1 {
  margin: 6px 0;
  font-size: clamp(2.3rem, 3vw, 3rem);
  color: #0f1422;
}

.hero-subtitle {
  margin: 0;
  color: #56607e;
}

.hero-price {
  text-align: right;
}

.hero-price__value {
  display: block;
  font-size: 2.7rem;
  font-weight: 700;
  color: #111a2c;
}

.hero-price__unit {
  display: block;
  color: #71809c;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 0.95fr;
  gap: 32px;
}

.detail-left {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.image-panel {
  position: relative;
  border-radius: 26px;
  overflow: hidden;
  background: linear-gradient(180deg, #eef3ff, #d8e5ff);
  border: 1px solid #e1e7f4;
  box-shadow: inset 0 -30px 60px rgba(18, 45, 110, 0.08);
}

.image-panel img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

.status-pill {
  position: absolute;
  top: 18px;
  right: 18px;
  padding: 8px 14px;
  border-radius: 30px;
  font-size: 0.75rem;
  font-weight: 700;
  background: #fff;
  color: #1d4ed8;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.2);
}

.detail-actions {
  display: flex;
  gap: 10px;
}

.btn-ghost,
.btn-solid {
  flex: 1;
  padding: 14px 0;
  border-radius: 14px;
  font-weight: 700;
  font-size: 0.95rem;
  border: none;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.btn-ghost {
  background: #f5f7ff;
  color: #111a2c;
}

.btn-ghost:hover,
.btn-solid:hover {
  transform: translateY(-2px);
}

.btn-solid {
  background: linear-gradient(135deg, #ffd54f, #ffb300);
  color: #1b1730;
  box-shadow: 0 15px 35px rgba(255, 181, 0, 0.4);
}

.feature-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.feature-card {
  background: #f7f8fd;
  border-radius: 22px;
  padding: 18px;
  border: 1px solid rgba(17, 23, 40, 0.08);
}

.feature-card__head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.feature-card__head h3 {
  margin: 0;
  font-size: 1rem;
  color: #0f1422;
}

.feature-card__head span {
  font-size: 0.75rem;
  color: #6b7280;
}

.feature-card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.feature-card li {
  font-size: 0.9rem;
  color: #2a3150;
  font-weight: 600;
}

.detail-right {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.panel {
  background: #fff;
  border-radius: 26px;
  padding: 20px 24px;
  border: 1px solid rgba(28, 36, 74, 0.08);
  box-shadow: 0 30px 70px rgba(15, 23, 42, 0.12);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.panel-head h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #0f1422;
}

.pill {
  font-size: 0.75rem;
  padding: 6px 12px;
  border-radius: 999px;
  background: #f0f3ff;
  color: #1e3a8a;
  font-weight: 600;
}

.pill--muted {
  background: #f1f5f9;
  color: #475569;
}

.panel ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.panel li {
  display: flex;
  justify-content: space-between;
  color: #4c5469;
  font-size: 0.95rem;
}

.panel li strong {
  color: #151b2e;
}

.panel-total {
  margin-top: 14px;
  border-top: 1px solid #e6ebf4;
  padding-top: 12px;
  display: flex;
  justify-content: space-between;
  font-size: 1rem;
  font-weight: 700;
  color: #111a2c;
}

.location-panel p {
  margin: 0;
  color: #4e557f;
  font-size: 1rem;
}

.small-text {
  font-size: 0.85rem;
  color: #7b849d;
  margin-top: 6px;
}

.btn-link {
  background: none;
  border: none;
  color: #1d4ed8;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.page-footer {
  margin-top: 32px;
  padding: 32px 48px 48px;
  border-top: 1px solid #e1e6f0;
  background: #fff;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 24px;
  color: #4f5676;
}

.footer-col h4 {
  margin: 0 0 6px;
  font-size: 1.5rem;
}

.footer-col h4 span {
  color: #1d4ed8;
}

.footer-col h5 {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.75rem;
  color: #7b859b;
}

.footer-col p {
  margin: 4px 0;
  font-size: 0.95rem;
}

.brand-col p {
  max-width: 320px;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1024px) {
  .detail-card {
    padding: 28px 24px;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .topbar {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .detail-wrapper {
    padding: 0 16px 40px;
  }

  .detail-card {
    padding: 24px;
  }

  .feature-sections {
    grid-template-columns: 1fr;
  }

  .nav-links {
    display: none;
  }
}
</style>
