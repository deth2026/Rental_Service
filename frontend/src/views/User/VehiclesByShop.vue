<template>
  <div class="vehicles-page">
    <header class="topbar">
      <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-gift"></i></div>
        <span>Chong Choul</span>
      </div>
      <nav class="nav-links">
        <button v-for="item in navItems" :key="item" class="btn-reset nav-link" :class="{ active: activeNav === item }"
          @click="setActiveNav(item)">
          {{ item }}
        </button>
      </nav>
      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <button class="btn-reset avatar" @click="openProfile">
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile" class="avatar-image" @error="onAvatarError" />
          <span v-else>{{ userInitials }}</span>
        </button>
        <button class="btn-reset logout-btn" @click="handleLogout">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>Logout</span>
        </button>
      </div>
    </header>

    <main class="content">
      <section class="deals-section">
        <div class="section-header">
          <div class="section-badge">
            <i class="fa-solid fa-car"></i>
            <span>AVAILABLE VEHICLES</span>
          </div>
          <h2>{{ displayedVehicles.length }} vehicles found in {{ selectedShopName || location }}</h2>
          <p>Available for your selected dates ({{ dateRange }})</p>
          <div class="filter-row">
            <button v-for="type in vehicleTypes" :key="type.key" class="filter-pill"
              :class="{ active: selectedType === type.key }" @click="selectedType = type.key">
              <i :class="type.icon"></i>
              <span>{{ type.label }}</span>
            </button>
          </div>
        </div>
        <p v-if="isLoading" class="action-message">Loading vehicles from database...</p>
        <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>
        <p v-if="actionMessage" class="action-message">{{ actionMessage }}</p>
        <div class="promo-grid">
          <article class="promo-card" v-for="vehicle in displayedVehicles" :key="vehicle.id">
            <div class="promo-media">
              <img :src="getVehicleImage(vehicle)" :alt="getVehicleName(vehicle)" />
              <span v-if="vehicle.bestValue" class="promo-ribbon">BEST VALUE</span>
              <span class="promo-type" @click="toggleFavorite(vehicle.id, getVehicleName(vehicle))">
                <i :class="favoriteIds.has(vehicle.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
              </span>
            </div>
            <div class="promo-body">
              <h3>{{ getVehicleName(vehicle) }}</h3>
              <div class="vehicle-meta">
                <span><i class="fa-solid fa-gear"></i> {{ vehicle.transmission }}</span>
                <span><i class="fa-solid fa-gas-pump"></i> {{ vehicle.fuel_type }}</span>
                <span><i class="fa-regular fa-star"></i> {{ vehicle.rating }}</span>
              </div>
              <p class="shop"><i class="fa-regular fa-building"></i> {{ getVehicleShop(vehicle) }}</p>
              <div class="promo-meta">
                <div class="promo-value">
                  <strong>${{ vehicle.price_per_day }}</strong>
                  <span>per day</span>
                </div>
                <button class="book-btn" @click="bookNow(vehicle)">Book Now</button>
              </div>
            </div>
          </article>
        </div>
      </section>
      <section class="map-section">
        <div class="map">
          <iframe class="map-frame" :src="mapEmbedUrl" title="Google map location" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
          <button class="btn-reset open-map-btn" @click="openMainLocation">Open in Google Maps</button>
          <button v-if="selectedShopLocationLink" class="btn-reset open-map-btn secondary"
            @click="openCustomLocation">Open Saved Location</button>
        </div>
      </section>
    </main>
    <footer class="footer">
      <div class="footer-brand">
        <strong>Cambodia<span>Rides</span></strong>
        <p>Connecting adventurous travelers with the best local rentals across Cambodia.</p>
      </div>
      <div class="footer-links">
        <h4>Quick Links</h4>
        <button class="btn-reset" @click="notify('How it works clicked')">How it works</button>
        <button class="btn-reset" @click="notify('Trust & Safety clicked')">Trust & Safety</button>
        <button class="btn-reset" @click="notify('Rental Policies clicked')">Rental Policies</button>
      </div>
      <div class="footer-links">
        <h4>Support</h4>
        <button class="btn-reset" @click="notify('Help Center clicked')">Help Center</button>
        <button class="btn-reset" @click="notify('Become a Partner clicked')">Become a Partner</button>
        <button class="btn-reset" @click="notify('Contact Us clicked')">Contact Us</button>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';
import { userService } from '../../services/database.js';
import '../../css/VehicleByShop.css';

const location = ref('Siem Reap');
const formatDate = (date) => `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(date)}/${date.getDate()}/${date.getFullYear()}`;
const buildRollingDateRange = () => {
  const today = new Date();
  return formatDate(today);
};
const dateRange = ref(buildRollingDateRange());
let dateRangeTimer = null;

const route = useRoute();
const navItems = ['Home', 'My Bookings', 'Promotion'];
const router = useRouter();
const activeNav = ref('Home');
const actionMessage = ref('');
const avatarLoadFailed = ref(false);
const selectedType = ref('all');
const vehicleTypes = [
  { key: 'all', label: 'All', icon: 'fa-solid fa-circle-dot' },
  { key: 'motorbike', label: 'Motorbikes', icon: 'fa-solid fa-motorcycle' },
  { key: 'bicycle', label: 'Bicycles', icon: 'fa-solid fa-bicycle' },
  { key: 'car', label: 'Cars', icon: 'fa-solid fa-car-side' }
];

const normalizeType = (raw, fallback = '') => {
  const t = String(raw || fallback || '').trim().toLowerCase();
  if (!t) return '';
  if (['motorbike', 'motorbikes', 'motor', 'motorcycle', 'motorcycles', 'scooter', 'scooters', 'bike'].some((k) => t.includes(k))) return 'motorbike';
  if (t.includes('bicy')) return 'bicycle';
  if (t.includes('car') || t.includes('suv')) return 'car';
  return t;
};

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, '');
const fallbackImageByType = {
  motorbike: 'https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg',
  bicycle: 'https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg',
  car: 'https://i.pinimg.com/1200x/d9/9e/06/d99e06bd9dd77fb2581170af2063b3b5.jpg'
};

const vehicles = ref([]);
const shopNamesById = ref({});
const isLoading = ref(false);
const loadingError = ref('');

const selectedShopId = computed(() => {
  const value = Number(route.query.shop_id);
  return Number.isFinite(value) && value > 0 ? value : null;
});

const selectedShopName = computed(() => {
  if (!selectedShopId.value) return '';
  const shop = shopNamesById.value[selectedShopId.value];
  return typeof shop === 'object' ? shop.name : shop;
});

const selectedShopLocation = computed(() => {
  if (!selectedShopId.value) return location.value;
  const shop = shopNamesById.value[selectedShopId.value];
  if (typeof shop === 'object' && shop.address) return shop.address;
  return location.value;
});

const selectedShopAddress = computed(() => {
  if (!selectedShopId.value) return '';
  const shop = shopNamesById.value[selectedShopId.value];
  return typeof shop === 'object' ? shop.address : '';
});

const selectedShopLocationLink = computed(() => {
  if (!selectedShopId.value) return '';
  const shop = shopNamesById.value[selectedShopId.value];
  if (!shop || typeof shop !== 'object') return '';
  const loc = shop.location || '';
  if (typeof loc !== 'string') return '';
  return loc.trim();
});

const selectedShopCoords = computed(() => {
  if (!selectedShopId.value) return null;
  const shop = shopNamesById.value[selectedShopId.value];
  if (!shop) return null;
  const lat = shop.latitude ?? shop.lat;
  const lng = shop.longitude ?? shop.lng;
  if (lat === null || lng === null || typeof lat === 'undefined' || typeof lng === 'undefined') return null;
  const parsedLat = Number(lat);
  const parsedLng = Number(lng);
  if (!Number.isFinite(parsedLat) || !Number.isFinite(parsedLng)) return null;
  return { lat: parsedLat, lng: parsedLng };
});

const mapEmbedUrl = computed(() => {
  const coords = selectedShopCoords.value;
  const shopAddress = selectedShopAddress.value;
  const shopName = selectedShopName.value || 'Shop';
  const fallback = 'Siem Reap, Cambodia';
  const queryParts = [];
  if (shopName) queryParts.push(shopName);
  if (shopAddress) queryParts.push(shopAddress);
  if (coords) queryParts.push(`${coords.lat},${coords.lng}`);
  const query = queryParts.join(' - ') || fallback;
  if (coords) {
    return `https://maps.google.com/maps?hl=en&ll=${coords.lat},${coords.lng}&q=${encodeURIComponent(query)}&t=k&z=18&output=embed`;
  }
  return `https://maps.google.com/maps?hl=en&q=${encodeURIComponent(query)}&t=k&z=16&output=embed`;
});

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

const onAvatarError = () => { avatarLoadFailed.value = true; };

const userInitials = computed(() => {
  const words = String(userDisplayName.value).trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) return 'CU';
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase();
});

const getVehicleName = (vehicle) => `${vehicle.brand} ${vehicle.model}`;
const getVehicleShop = (vehicle) => {
  if (!vehicle.shop_id) return 'Unknown Shop';
  const shop = shopNamesById.value[vehicle.shop_id];
  if (!shop) return 'Unknown Shop';
  return typeof shop === 'object' ? shop.name : shop;
};

const favoriteIds = reactive(new Set());

const filteredVehicles = computed(() => {
  const source = selectedShopId.value ? vehicles.value.filter((v) => Number(v.shop_id) === selectedShopId.value) : vehicles.value;
  if (selectedType.value === 'all') return source;
  return source.filter((v) => normalizeType(v.type || v.category, `${v.name} ${v.brand} ${v.model}`) === selectedType.value);
});

const displayedVehicles = computed(() => {
  const limit = selectedType.value === 'all' ? 8 : 20;
  return filteredVehicles.value.slice(0, limit);
});

const getVehicleImage = (vehicle) => {
  const image = vehicle.image_url ? String(vehicle.image_url).trim() : '';
  if (image) {
    if (image.startsWith('http://') || image.startsWith('https://')) return image;
    if (image.startsWith('/')) return `${API_ROOT}${image}`;
    return `${API_ROOT}/storage/${image.replace(/^storage\//, '')}`;
  }
  const normalizedType = normalizeType(vehicle.type || vehicle.category, `${vehicle.name} ${vehicle.brand} ${vehicle.model}`);
  return fallbackImageByType[normalizedType] || fallbackImageByType.motorbike;
};

const loadAllPages = async (resource) => {
  let page = 1;
  let lastPage = 1;
  const results = [];
  while (page <= lastPage) {
    const response = await api.get(`/${resource}`, { params: { page } });
    const payload = response.data;
    const data = Array.isArray(payload) ? payload : payload?.data || [];
    results.push(...data.map((item) => ({ ...item, type: normalizeType(item.type || item.category, `${item.name} ${item.brand} ${item.model}`) })));
    lastPage = payload?.last_page || 1;
    page += 1;
  }
  return results;
};

const loadVehiclesAndShops = async () => {
  isLoading.value = true;
  loadingError.value = '';
  try {
    const [vehicleList, shopList] = await Promise.all([loadAllPages('vehicles'), loadAllPages('shops')]);
    vehicles.value = vehicleList.map((vehicle) => ({ ...vehicle, rating: vehicle.rating ?? 4.8 }));
    shopNamesById.value = shopList.reduce((acc, shop) => { acc[shop.id] = shop; return acc; }, {});
  } catch (error) {
    loadingError.value = 'Could not load vehicles from database. Check backend server and API URL.';
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const setActiveNav = (item) => {
  activeNav.value = item;
  if (item === 'Home') { router.push('/view_shop'); return; }
  if (item === 'Promotion') { router.push('/promotions'); return; }
  actionMessage.value = `${item} opened.`;
};

const openMainLocation = () => {
  const coords = selectedShopCoords.value;
  const shopAddress = selectedShopAddress.value;
  const shopName = selectedShopName.value || '';
  if (coords) {
    const destination = shopAddress ? `${shopName ? `${shopName}, ` : ''}${shopAddress} (${coords.lat},${coords.lng})` : `${shopName ? `${shopName}, ` : ''}${coords.lat},${coords.lng}`;
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`, '_blank');
    return;
  }
  const query = shopAddress || shopName || 'Trip Zone Motorbike and Scooter Rental, Siem Reap, Cambodia';
  window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}`, '_blank');
};

const openCustomLocation = () => {
  const link = selectedShopLocationLink.value;
  if (!link) return;
  const target = link.startsWith('http') ? link : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(link)}`;
  window.open(target, '_blank');
};

const notify = (message) => { actionMessage.value = message; };
const openProfile = () => { router.push('/user/profile'); };
const handleLogout = async () => { await userService.logout(); router.push('/login'); };

const toggleFavorite = (id, name) => {
  if (favoriteIds.has(id)) { favoriteIds.delete(id); actionMessage.value = `${name} removed from favorites.`; }
  else { favoriteIds.add(id); actionMessage.value = `${name} added to favorites.`; }
};

const bookNow = (vehicle) => {
  const vehicleName = getVehicleName(vehicle);
  actionMessage.value = `Booking started for ${vehicleName}.`;
  router.push({ name: 'vehicle-detail', params: { id: vehicle.id }, query: selectedShopId.value ? { shop_id: String(selectedShopId.value) } : {} });
};

onMounted(() => {
  dateRangeTimer = window.setInterval(() => { dateRange.value = buildRollingDateRange(); }, 60 * 1000);
  loadVehiclesAndShops();
});

onUnmounted(() => { if (dateRangeTimer) window.clearInterval(dateRangeTimer); });
</script>

<style scoped>
.vehicles-page {
  min-height: 100vh;
  padding: 0 40px;
  font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  background: #eff1f4;
  color: #1e2430;
}

.btn-reset {
  border: 0;
  background: transparent;
  font: inherit;
  color: inherit;
  cursor: pointer;
}

.topbar {
  position: sticky;
  top: 0;
  z-index: 5;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  width: calc(100% + 80px);
  margin-left: -40px;
  margin-right: -40px;
  padding: 14px 20px;
  background: #fff;
  border-bottom: 1px solid #d8dee7;
}

.brand {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #2563eb;
  white-space: nowrap;
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

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-self: end;
  white-space: nowrap;
}

.nav-links {
  display: flex;
  gap: 24px;
  justify-self: center;
}

.nav-link {
  padding: 8px 16px;
  border-radius: 8px;
  color: #4a556b;
  font-size: 14px;
  font-weight: 700;
}

.nav-link.active,
.nav-link:hover {
  background: #eef7ff;
  color: #2563eb;
}

.user-display-name {
  font-size: 16px;
  font-weight: 800;
  color: #33435d;
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
}

.content {
  max-width: 1240px;
  margin: 0 auto;
  padding: 22px 20px 36px;
}

.action-message {
  margin: 0 0 12px;
  color: #0f74ab;
  font-size: 14px;
}

.deals-section {
  margin-bottom: 30px;
}

.section-header {
  margin-bottom: 20px;
}

.section-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #eef7ff;
  padding: 8px 16px;
  border-radius: 20px;
  color: #2563eb;
  font-weight: 700;
  margin-bottom: 12px;
}

.section-badge i {
  font-size: 16px;
}

.section-header h2 {
  margin: 0 0 4px;
  font-size: 24px;
}

.section-header p {
  margin: 0;
  color: #c4ccda;
}

.filter-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 16px;
}

.filter-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid #d8dee7;
  background: #fff;
  color: #4a556b;
  font-weight: 600;
  transition: all 0.2s;
}

.filter-pill:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.filter-pill.active {
  background: #2563eb;
  color: #fff;
  border-color: #2563eb;
}

.promo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.promo-card {
  background: #fff;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
  transition: transform 0.2s;
}

.promo-card:hover {
  transform: translateY(-4px);
}

.promo-media {
  position: relative;
  height: 180px;
  background: #f8f9fa;
}

.promo-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.promo-ribbon {
  position: absolute;
  top: 12px;
  left: 12px;
  background: #2563eb;
  color: #fff;
  padding: 4px 10px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 700;
}

.promo-type {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.promo-type i {
  color: #ef4444;
}

.promo-body {
  padding: 16px;
}

.promo-body h3 {
  margin: 0 0 8px;
  font-size: 18px;
}

.vehicle-meta {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  font-size: 13px;
  color: #59667b;
  margin-bottom: 8px;
}

.vehicle-meta i {
  margin-right: 4px;
}

.shop {
  font-size: 14px;
  color: #617086;
  margin: 0 0 12px;
}

.shop i {
  margin-right: 4px;
}

.promo-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.promo-value strong {
  font-size: 22px;
  color: #2563eb;
}

.promo-value span {
  font-size: 12px;
  color: #59667b;
}

.book-btn {
  padding: 10px 20px;
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.book-btn:hover {
  background: #1d4ed8;
}

.map-section {
  margin-top: 26px;
}

.map {
  position: relative;
  height: 330px;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid #d8dee7;
}

.map-frame {
  width: 100%;
  height: 100%;
  border: 0;
}

.open-map-btn {
  position: absolute;
  left: 12px;
  bottom: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #d8dee7;
  color: #1e40af;
  font-size: 13px;
  font-weight: 600;
}

.open-map-btn.secondary {
  left: auto;
  right: 12px;
}

.footer {
  margin-top: 28px;
  width: calc(100% + 80px);
  margin-left: -40px;
  margin-right: -40px;
  padding: 24px 20px;
  background: #fff;
  border-top: 1px solid #d8dee7;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 28px;
}

.footer-brand strong {
  font-size: 24px;
}

.footer-brand strong span {
  color: #2563eb;
}

.footer-brand p {
  margin: 8px 0 0;
  max-width: 420px;
  color: #66758d;
}

.footer-links h4 {
  margin: 2px 0 10px;
  font-size: 15px;
}

.footer-links button {
  display: block;
  margin: 0 0 8px;
  color: #52627b;
}

@media (max-width: 1080px) {
  .topbar {
    grid-template-columns: 1fr;
    width: 100%;
    margin: 0;
    gap: 10px;
  }

  .nav-links,
  .top-actions {
    justify-self: start;
  }

  .footer {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .vehicles-page {
    padding: 0 12px;
  }

  .topbar {
    width: 100%;
    margin: 0;
    padding: 12px;
  }

  .nav-links {
    flex-wrap: wrap;
  }

  .promo-grid {
    grid-template-columns: 1fr;
  }

  .footer {
    width: 100%;
    margin: 0;
  }
}
</style>