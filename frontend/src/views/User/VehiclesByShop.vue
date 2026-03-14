<template>
  <div class="vehicles-page">
    <header class="topbar">
      <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-gift" aria-hidden="true"></i></div>
        <span>Chong Choul</span>
      </div>

      <nav class="nav-links">
        <button
          v-for="item in navItems"
          :key="item"
          class="btn-reset nav-link"
          :class="{ active: activeNav === item }"
          @click="setActiveNav(item)"
        >
          {{ item }}
        </button>
      </nav>

      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <button class="btn-reset avatar" @click="openProfile">
          <img v-if="userAvatarUrl" :src="userAvatarUrl" alt="Profile photo" class="avatar-image" @error="onAvatarError" />
          <span v-else>{{ userInitials }}</span>
        </button>
        <button class="btn-reset logout-btn" @click="handleLogout">
          <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
          <span>Logout</span>
        </button>
      </div>
    </header>

    <section class="filters">
      <div class="type-filters">
        <button
          v-for="type in vehicleTypes"
          :key="type.key"
          class="btn-reset chip"
          :class="{ active: selectedType === type.key }"
          @click="selectedType = type.key"
        >
          {{ type.label }}
        </button>
      </div>

      <div class="option-filters">
        <button class="btn-reset select-chip search-filter-btn" @click="runFilterSearch">
          Search
        </button>
      </div>
    </section>

    <main class="content">
      <div class="results-head">
        <h1>{{ displayedVehicles.length }} vehicles found in {{ selectedShopName || location }}</h1>
        <p>Available for your selected dates ({{ dateRange }})</p>
      </div>

      <p v-if="isLoading" class="action-message">Loading vehicles from database...</p>
      <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>
      <p v-if="actionMessage" class="action-message">{{ actionMessage }}</p>

      <section class="grid">
        <article class="card" v-for="vehicle in displayedVehicles" :key="vehicle.id">
          <span v-if="vehicle.bestValue" class="badge">BEST VALUE</span>

          <button
            class="btn-reset fav-btn"
            :class="{ active: favoriteIds.has(vehicle.id) }"
            @click="toggleFavorite(vehicle.id, getVehicleName(vehicle))"
            :aria-label="`Save ${getVehicleName(vehicle)}`"
          >
            <i :class="favoriteIds.has(vehicle.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" aria-hidden="true"></i>
          </button>

          <div class="card-image">
            <button
              class="btn-reset card-image-btn"
              @click="viewDetails(vehicle)"
              :aria-label="`View details for ${getVehicleName(vehicle)}`"
            >
              <img :src="getVehicleImage(vehicle)" :alt="getVehicleName(vehicle)" />
            </button>
          </div>

          <div class="card-body">
            <div class="card-top">
              <h3>
                <button
                  class="btn-reset card-title-btn"
                  @click="viewDetails(vehicle)"
                  :aria-label="`View details for ${getVehicleName(vehicle)}`"
                >
                  {{ getVehicleName(vehicle) }}
                </button>
              </h3>
              <div class="price">
                <strong>${{ vehicle.price_per_day }}</strong>
                <span>per day</span>
              </div>
            </div>

            <p class="shop"><i class="fa-regular fa-building" aria-hidden="true"></i> {{ getVehicleShop(vehicle) }}</p>

            <div class="meta">
              <span><i class="fa-solid fa-gear" aria-hidden="true"></i> {{ vehicle.transmission }}</span>
              <span><i class="fa-solid fa-gas-pump" aria-hidden="true"></i> {{ vehicle.fuel_type }}</span>
              <span><i class="fa-regular fa-star" aria-hidden="true"></i> {{ vehicle.rating }}</span>
            </div>

            <button class="btn-reset book" @click="bookNow(vehicle)">Book Now</button>
          </div>
        </article>
      </section>

      <section class="map-section">
        <div class="map">
          <iframe
            class="map-frame"
            :src="mapEmbedUrl"
            title="Google map location"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-brand">
        <strong>Cambodia<span>Rides</span></strong>
        <p>
          Connecting adventurous travelers with the best local rentals across Cambodia.
        </p>
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

const location = 'Siem Reap';
const formatDate = (date) =>
  `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(date)}/${date.getDate()}/${date.getFullYear()}`;

const buildRollingDateRange = () => {
  const today = new Date();
  return formatDate(today);
};

const dateRange = ref(buildRollingDateRange());
let dateRangeTimer = null;
const mapEmbedUrl = `https://maps.google.com/maps?hl=en&q=${encodeURIComponent('Trip Zone Motorbike and Scooter Rental, Siem Reap, Cambodia')}&t=k&z=18&output=embed`;

const route = useRoute();
const navItems = ['Home', 'Viewdetails', 'Bookings'];
const router = useRouter();
const activeNav = ref('Home');
const actionMessage = ref('');
const avatarLoadFailed = ref(false);
const LAST_VEHICLE_ID_KEY = 'last_vehicle_id';
const selectedType = ref('all');
const vehicleTypes = [
  { key: 'all', label: 'All' },
  { key: 'motorbike', label: 'Motorbikes' },
  { key: 'bicycle', label: 'Bicycles' },
  { key: 'car', label: 'Cars' }
];

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
  return shopNamesById.value[selectedShopId.value] || '';
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

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const userInitials = computed(() => {
  const words = String(userDisplayName.value).trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) return 'CU';
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase();
});

const getVehicleName = (vehicle) => `${vehicle.brand} ${vehicle.model}`;
const getVehicleShop = (vehicle) => (vehicle.shop_id ? (shopNamesById.value[vehicle.shop_id] || 'Unknown Shop') : 'Unknown Shop');

const favoriteIds = reactive(new Set());

const filteredVehicles = computed(() => {
  const source = selectedShopId.value
    ? vehicles.value.filter((v) => Number(v.shop_id) === selectedShopId.value)
    : vehicles.value;

  if (selectedType.value === 'all') {
    return source;
  }
  return source.filter((v) => String(v.type || '').toLowerCase() === selectedType.value);
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
  const normalizedType = String(vehicle.type || '').toLowerCase();
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
    results.push(...data);
    lastPage = payload?.last_page || 1;
    page += 1;
  }

  return results;
};

const loadVehiclesAndShops = async () => {
  isLoading.value = true;
  loadingError.value = '';
  try {
    const [vehicleList, shopList] = await Promise.all([
      loadAllPages('vehicles'),
      loadAllPages('shops')
    ]);

    vehicles.value = vehicleList.map((vehicle) => ({
      ...vehicle,
      rating: vehicle.rating ?? 4.8
    }));
    shopNamesById.value = shopList.reduce((acc, shop) => {
      acc[shop.id] = shop.name;
      return acc;
    }, {});
  } catch (error) {
    const status = error?.response?.status;
    const message = error?.response?.data?.message || error.message;
    console.error('API Error:', status, message);
    
    if (status === 401) {
      loadingError.value = 'Authentication required. Please log in.';
    } else if (status === 500) {
      loadingError.value = 'Server error. Please try again later.';
    } else {
      loadingError.value = `Could not load vehicles (${status || 'network error'}). Check backend server.`;
    }
  } finally {
    isLoading.value = false;
  }
};

const setActiveNav = (item) => {
  activeNav.value = item;
  if (item === 'Home') {
    router.push('/view_shop');
    return;
  }
  actionMessage.value = `${item} is not available yet.`;
};

const handleSearch = () => {
  actionMessage.value = `Search triggered for ${location}, ${dateRange}.`;
};


const notify = (message) => {
  actionMessage.value = message;
};

const openProfile = () => {
  router.push('/user/profile');
};

const handleLogout = async () => {
  await userService.logout();
  router.push('/login');
};

const runFilterSearch = () => {
  actionMessage.value = `Search clicked for ${selectedType.value === 'all' ? 'all vehicles' : selectedType.value}.`;
};

const buildVehicleDetailRoute = (vehicle) => {
  if (!vehicle || !vehicle.id) return null;
  return {
    name: 'vehicle-detail',
    params: { id: vehicle.id },
    query: selectedShopId.value ? { shop_id: String(selectedShopId.value) } : {}
  };
};

const setLastVehicleId = (id) => {
  if (!id) return;
  localStorage.setItem(LAST_VEHICLE_ID_KEY, String(id));
};

const getLastVehicleId = () => {
  const raw = localStorage.getItem(LAST_VEHICLE_ID_KEY);
  const parsed = Number(raw);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
};

const viewDetails = (vehicle) => {
  const routeTarget = buildVehicleDetailRoute(vehicle);
  if (!routeTarget) return;
  setLastVehicleId(vehicle.id);
  router.push(routeTarget);
};

const toggleFavorite = (id, name) => {
  if (favoriteIds.has(id)) {
    favoriteIds.delete(id);
    actionMessage.value = `${name} removed from favorites.`;
  } else {
    favoriteIds.add(id);
    actionMessage.value = `${name} added to favorites.`;
  }
};

const bookNow = (vehicle) => {
  const vehicleName = getVehicleName(vehicle);
  actionMessage.value = `Booking started for ${vehicleName}.`;
  const routeTarget = buildVehicleDetailRoute(vehicle);
  if (!routeTarget) return;
  setLastVehicleId(vehicle.id);
  router.push(routeTarget);
};

onMounted(() => {
  dateRangeTimer = window.setInterval(() => {
    dateRange.value = buildRollingDateRange();
  }, 60 * 1000);
  loadVehiclesAndShops();
});

onUnmounted(() => {
  if (dateRangeTimer) {
    window.clearInterval(dateRangeTimer);
  }
});

</script>

<style scoped>
.vehicles-page {
  --brand: #1d4ed8;
  --brand-dark: #1e40af;
  --bg: #eff1f4;
  --card: #ffffff;
  --line: #d8dee7;
  --text: #1e2430;
  --muted: #c4ccda;
  min-height: 100vh;
  padding: 0 40px;
  font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  background: var(--bg);
  color: var(--text);
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
  border-bottom: 1px solid var(--line);
  box-sizing: border-box;
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


.brand-icon i {
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

.fav-btn i {
  font-size: 16px;
}

.shop i,
.meta i {
  margin-right: 4px;
}

.meta .fa-star {
  color: #f6b200;
}

.filters {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 20px;
  background: #fff;
  border-bottom: 1px solid var(--line);
}

.type-filters,
.option-filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.chip,
.select-chip {
  padding: 8px 14px;
  border-radius: 9px;
  border: 1px solid var(--line);
  background: #f5f7fa;
  font-size: 14px;
}

.chip.active {
  background: var(--brand);
  color: #fff;
  border-color: var(--brand);
}

.select-chip {
  background: #fff;
}

.search-filter-btn {
  min-width: 92px;
  background: var(--brand);
  color: #fff;
  border-color: var(--brand);
  font-weight: 600;
}

.search-filter-btn:hover {
  background: var(--brand-dark);
  border-color: var(--brand-dark);
}

.content {
  max-width: 1240px;
  margin: 0 auto;
  padding: 22px 20px 36px;
}

.results-head h1 {
  margin: 0;
  font-size: 24px;
}

.results-head p {
  margin: 4px 0 12px;
  color: var(--muted);
}

.action-message {
  margin: 0 0 12px;
  color: #0f74ab;
  font-size: 14px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.card {
  position: relative;
  border: 1px solid var(--line);
  border-radius: 14px;
  background: var(--card);
  overflow: hidden;
  background-color:#eaf7fa;
  
}

.badge {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 2;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 10px;
  font-weight: 700;
  color: #fff;
  background: var(--brand);
}

.fav-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
  width: 30px;
  height: 30px;
  border-radius: 999px;
  background: #f8f6f6;
  border: 1px solid var(--line);
}

.fav-btn.active {
  color: #ef4f68;
}

.card-image {
  height: 172px;
  background: #ffffff;
  display: grid;
  place-items: center;
  width: 100%;
  /* padding: 0px; */
}

.card-image-btn {
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
}

.card-image img {
  width: 100%;
  max-height: 152px;
  object-fit: contain;
}

.card-body {
  padding: 12px;
  background-color:#eaf7fa;
}

.card-top {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.card-top h3 {
  margin: 0;
  font-size: 24px;
  line-height: 1.3;
}

.card-title-btn {
  text-align: left;
  font-size: inherit;
  font-weight: inherit;
  color: inherit;
}

.card-title-btn:hover {
  text-decoration: underline;
}

.price {
  text-align: right;
}

.price strong {
  color: var(--brand);
  font-size: 20px;
}

.price span {
  display: block;
  font-size: 11px;
  color: var(--muted);
}

.shop {
  margin: 4px 0 10px;
  font-size: 16px;
  color: #617086;
}

.meta {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  font-size: 13px;
  color: #59667b;
  margin-bottom: 12px;
}

.book {
  width: 100%;
  border-radius: 8px;
  padding: 10px 12px;
  background: var(--brand);
  color: #fff;
  font-weight: 600;
}

.book:hover {
  background: var(--brand-dark);
}

.map-section {
  margin-top: 26px;
}

.map {
  position: relative;
  height: 330px;
  border-radius: 14px;
  overflow: hidden;
  border: 1px solid var(--line);
}

.map-frame {
  width: 100%;
  height: 100%;
  border: 0;
}


.footer {
  margin-top: 28px;
  width: calc(100% + 80px);
  margin-left: -40px;
  margin-right: -40px;
  padding: 24px 20px;
  background: #fff;
  border-top: 1px solid var(--line);
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 28px;
}

.footer-brand strong {
  font-size: 24px;
}

.footer-brand strong span {
  color: var(--brand);
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
    margin-left: 0;
    margin-right: 0;
    gap: 10px;
  }

  .nav-links,
  .top-actions {
    justify-self: start;
  }

  .grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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
    width: calc(100% + 24px);
    margin-left: -12px;
    margin-right: -12px;
    padding: 12px;
  }

  .nav-links {
    flex-wrap: wrap;
  }

  .filters {
    flex-direction: column;
  }

  .grid {
    grid-template-columns: 1fr;
  }

  .footer {
    width: calc(100% + 24px);
    margin-left: -12px;
    margin-right: -12px;
  }
}
</style>
