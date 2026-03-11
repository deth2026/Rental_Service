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
              v-for="type in vehicleTypes"
              :key="type.key"
              class="filter-pill"
              :class="{ active: selectedType === type.key }"
              @click="selectedType = type.key"
            >
              <i :class="type.icon"></i>
              <span>{{ type.label }}</span>
            </button>
          </div>
        </div>

        <p v-if="isLoading" class="action-message">Loading vehicles from database...</p>
        <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>
        <p v-if="actionMessage" class="action-message">{{ actionMessage }}</p>

        <section class="grid">
          <article class="promo-card" v-for="vehicle in displayedVehicles" :key="vehicle.id">
            <div class="promo-media">
              <img :src="getVehicleImage(vehicle)" :alt="getVehicleName(vehicle)" />
              <span v-if="vehicle.bestValue" class="promo-ribbon">BEST VALUE</span>
              <!-- <span class="promo-type">{{ vehicle.type || 'vehicle' }}</span> -->
              
              <button
                class="btn-reset fav-btn"
                :class="{ active: favoriteIds.has(vehicle.id) }"
                @click="toggleFavorite(vehicle.id, getVehicleName(vehicle))"
                :aria-label="`Save ${getVehicleName(vehicle)}`"
              >
                <i :class="favoriteIds.has(vehicle.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" aria-hidden="true"></i>
              </button>
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
        </section>
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
import '../../css/VehicleByShop.css'

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
  actionMessage.value = `${item} opened.`;
};

const handleSearch = () => {
  actionMessage.value = `Search triggered for ${location}, ${dateRange}.`;
};

const openMainLocation = () => {
  window.open('https://maps.google.com/?q=Trip Zone Motorbike and Scooter Rental, Siem Reap, Cambodia', '_blank');
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




