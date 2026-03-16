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
        <UserProfileMenu @settings="openProfile" @logout="handleLogout" />
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

        <p v-if="isLoading" class="action-message">Loading vehicles from database...</p>
        <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>
        <p v-if="actionMessage" class="action-message">{{ actionMessage }}</p>

        <div class="promo-grid">
          <article class="promo-card" v-for="vehicle in displayedVehicles" :key="vehicle.id">
            <div class="promo-media">
              <img :src="getVehicleImage(vehicle)" :alt="getVehicleName(vehicle)" />
              <span v-if="vehicle.bestValue" class="promo-ribbon">BEST VALUE</span>
              <span class="promo-type" @click="toggleFavorite(vehicle.id, getVehicleName(vehicle))" style="cursor: pointer;">
                <i :class="favoriteIds.has(vehicle.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" aria-hidden="true"></i>
              </span>
            </div>

            <div class="promo-body">
              <h3>{{ getVehicleName(vehicle) }}</h3>

              <div class="vehicle-meta">
                <span><i class="fa-solid fa-gear" aria-hidden="true"></i> {{ vehicle.transmission }}</span>
                <span><i class="fa-solid fa-gas-pump" aria-hidden="true"></i> {{ vehicle.fuel_type }}</span>
                <span><i class="fa-regular fa-star" aria-hidden="true"></i> {{ vehicle.rating }}</span>
              </div>

              <p class="shop"><i class="fa-regular fa-building" aria-hidden="true"></i> {{ getVehicleShop(vehicle) }}</p>

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
          <iframe
            class="map-frame"
            :src="mapEmbedUrl"
            title="Google map location"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
          <button class="btn-reset open-map-btn" @click="openMainLocation">
            Open in Google Maps
          </button>
          <button v-if="selectedShopLocationLink" class="btn-reset open-map-btn secondary" @click="openCustomLocation">
            Open Saved Location
          </button>
        </div>
      </section>
    </main>
  </div>


  <!-- Common Footer -->
  <CommonFooter />
</template>


<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';
import { userService } from '../../services/database.js';
import CommonFooter from '../../components/CommonFooter.vue'
import '../../css/VehicleByShop.css'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const location = ref('Siem Reap');
const formatDate = (date) =>
  `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(date)}/${date.getDate()}/${date.getFullYear()}`;

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
const activeFilter = ref('all');
const filterItems = [
  { id: 'all', label: 'All', icon: 'fa-solid fa-circle-dot' },
  { id: 'moto', label: 'Motorbike', icon: 'fa-solid fa-motorcycle' },
  { id: 'bike', label: 'Bicycles', icon: 'fa-solid fa-bicycle' },
  { id: 'car', label: 'Car', icon: 'fa-solid fa-car-side' }
];

const normalizeType = (raw, fallback = '') => {
  const t = String(raw || fallback || '').trim().toLowerCase();
  if (!t) return '';
  // Match exact types from database: 'moto', 'bike', 'car'
  if (['motorbike', 'motorbikes', 'motor', 'motorcycle', 'motorcycles', 'scooter', 'scooters'].some((k) => t.includes(k))) {
    return 'moto';
  }
  if (t.includes('bicy') || t === 'bike' || t.includes('bicycle')) {
    return 'bike';
  }
  if (t.includes('car') || t.includes('suv')) {
    return 'car';
  }
  return t;
};

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, '');
const fallbackImageByType = {
  moto: 'https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg',
  bike: 'https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg',
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
  if (typeof shop === 'object' && shop.address) {
    return shop.address;
  }
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

  // Build a clean query without "undefined"
  const queryParts = [];
  if (shopName) queryParts.push(shopName);
  if (shopAddress) queryParts.push(shopAddress);
  if (coords) queryParts.push(`${coords.lat},${coords.lng}`);
  const query = queryParts.join(' - ') || fallback;

  // When we have coordinates, also set ll (map center) to avoid the world view
  if (coords) {
    return `https://maps.google.com/maps?hl=en&ll=${coords.lat},${coords.lng}&q=${encodeURIComponent(query)}&t=k&z=18&output=embed`;
  }

  return `https://maps.google.com/maps?hl=en&q=${encodeURIComponent(query)}&t=k&z=16&output=embed`;
});
const currentUser = computed(() => userService.getCurrentUser());
const userDisplayName = computed(() => currentUser.value?.name || 'customer');

const getVehicleName = (vehicle) => `${vehicle.brand} ${vehicle.model}`;
const getVehicleShop = (vehicle) => {
  if (!vehicle.shop_id) return 'Unknown Shop';
  const shop = shopNamesById.value[vehicle.shop_id];
  if (!shop) return 'Unknown Shop';
  return typeof shop === 'object' ? shop.name : shop;
};

const favoriteIds = reactive(new Set());

const filteredVehicles = computed(() => {
  const source = selectedShopId.value
    ? vehicles.value.filter((v) => Number(v.shop_id) === selectedShopId.value)
    : vehicles.value;

if (activeFilter.value === 'all') {
    return source;
  }
  return source.filter(
    (v) => normalizeType(v.type || v.category, `${v.name} ${v.brand} ${v.model}`) === activeFilter.value
  );
});

const displayedVehicles = computed(() => filteredVehicles.value);

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

const loadAllPages = async (resource, extraParams = {}) => {
  let page = 1;
  let lastPage = 1;
  const results = [];


  while (page <= lastPage) {
    const response = await api.get(`/${resource}`, { params: { page, ...extraParams } });
    const payload = response.data;
    const data = Array.isArray(payload) ? payload : payload?.data || [];
    // Normalize types up front so filters work even if API returns "Motorbikes" etc.
    results.push(
      ...data.map((item) => ({
        ...item,
        type: normalizeType(item.type || item.category, `${item.name} ${item.brand} ${item.model}`)
      }))
    );
    const meta = payload?.meta;
    lastPage = meta?.last_page || payload?.last_page || lastPage;
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
      acc[shop.id] = shop;
      return acc;
    }, {});
  } catch (error) {
    loadingError.value = 'Could not load vehicles from database. Check backend server and API URL.';
    console.error(error);
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
  if (item === 'My Bookings') {
    router.push('/bookings');
    return;
  }
  if (item === 'Promotion') {
    router.push('/promotions');
    return;
  }
  actionMessage.value = `${item} opened.`;
};

const handleSearch = () => {
  actionMessage.value = `Search triggered for ${location}, ${dateRange}.`;
};

const openMainLocation = () => {
  const coords = selectedShopCoords.value;
  const shopAddress = selectedShopAddress.value;
  const shopName = selectedShopName.value || '';
  if (coords) {
    // Open directions to the exact coordinates; include address for clarity if available
    const destination = shopAddress
      ? `${shopName ? `${shopName}, ` : ''}${shopAddress} (${coords.lat},${coords.lng})`
      : `${shopName ? `${shopName}, ` : ''}${coords.lat},${coords.lng}`;
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(destination)}`, '_blank');
    return;
  }
  // Use address first, then shop name, then Trip Zone as fallback
  const query = shopAddress || shopName || 'Trip Zone Motorbike and Scooter Rental, Siem Reap, Cambodia';
  window.open(`https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(query)}`, '_blank');
};

const openCustomLocation = () => {
  const link = selectedShopLocationLink.value;
  if (!link) return;
  const target = link.startsWith('http') ? link : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(link)}`;
  window.open(target, '_blank');
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
  actionMessage.value = `Search clicked for ${activeFilter.value === 'all' ? 'all vehicles' : activeFilter.value}.`;
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
  router.push({
    name: 'vehicle-detail',
    params: { id: vehicle.id },
    query: selectedShopId.value ? { shop_id: String(selectedShopId.value) } : {}
  });
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
