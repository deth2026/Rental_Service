<template>
  <div class="vehicles-page">
    <header class="topbar">
      <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-gift" aria-hidden="true"></i></div>
        <span>Cambodia Rides</span>
      </div>

      <button class="search-pill btn-reset" @click="handleSearch">
        <span class="pill-item"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ location }}</span>
        <span class="pill-sep"></span>
        <span class="pill-item"><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ dateRange }}</span>
        <span class="pill-search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
      </button>

      <div class="top-actions">
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

        <button class="btn-reset icon-btn" @click="notify('Notifications opened')"><i class="fa-regular fa-bell" aria-hidden="true"></i></button>
        <button class="btn-reset avatar" @click="notify('Profile opened')">U</button>
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
        <h1>{{ displayedVehicles.length }} vehicles found in {{ location }}</h1>
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
            <img :src="getVehicleImage(vehicle)" :alt="getVehicleName(vehicle)" />
          </div>

          <div class="card-body">
            <div class="card-top">
              <h3>{{ getVehicleName(vehicle) }}</h3>
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
import axios from 'axios';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

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

const navItems = ['Home', 'My Bookings', 'Support'];
const router = useRouter();
const activeNav = ref('Home');
const actionMessage = ref('');
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

const getVehicleName = (vehicle) => `${vehicle.brand} ${vehicle.model}`;
const getVehicleShop = (vehicle) => (vehicle.shop_id ? (shopNamesById.value[vehicle.shop_id] || 'Unknown Shop') : 'Unknown Shop');

const favoriteIds = reactive(new Set());

const filteredVehicles = computed(() => {
  if (selectedType.value === 'all') {
    return vehicles.value;
  }
  return vehicles.value.filter((v) => String(v.type || '').toLowerCase() === selectedType.value);
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
    const response = await axios.get(`${API_BASE_URL}/${resource}`, { params: { page } });
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
  router.push({ name: 'vehicle-detail', params: { id: vehicle.id } });
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
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
  gap: 18px;
  align-items: center;
  padding: 14px 20px;
  background: #fff;
  border-bottom: 1px solid var(--line);
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 20px;
  font-weight: 700;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: #daf2ff;
}

.search-pill {
  max-width: 430px;
  justify-self: center;
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid var(--line);
  border-radius: 999px;
  background: #f6f8fb;
  padding: 8px 12px;
}

.pill-item {
  font-size: 14px;
  color: #33435d;
}

.pill-sep {
  width: 1px;
  height: 18px;
  background: var(--line);
}

.pill-search {
  margin-left: auto;
  color: var(--brand);
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-links {
  display: flex;
  gap: 8px;
  padding-right: 12px;
  border-right: 1px solid var(--line);
}

.nav-link {
  padding: 7px 12px;
  border-radius: 8px;
  color: #4a556b;
  font-size: 14px;
}

.nav-link.active,
.nav-link:hover {
  background: #eef7ff;
  color: var(--brand);
}

.icon-btn,
.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: #f4f8fc;
}

.icon-btn {
  color: #0ea5e9;
}

.brand-icon i {
  color: #0ea5e9;
}

.pill-item i {
  margin-right: 6px;
}

.pill-search i,
.icon-btn i {
  font-size: 14px;
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

.avatar {
  border: 2px solid #c9ebff;
  color: var(--brand-dark);
  font-weight: 700;
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

.open-map-btn {
  position: absolute;
  left: 12px;
  bottom: 12px;
  padding: 8px 12px;
  border-radius: 8px;
  background: #ffffff;
  border: 1px solid var(--line);
  color: var(--brand-dark);
  font-size: 13px;
  font-weight: 600;
}

.footer {
  margin-top: 28px;
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
    gap: 10px;
  }

  .search-pill {
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
  .nav-links {
    border-right: 0;
    padding-right: 0;
  }

  .filters {
    flex-direction: column;
  }

  .grid {
    grid-template-columns: 1fr;
  }
}
</style>
