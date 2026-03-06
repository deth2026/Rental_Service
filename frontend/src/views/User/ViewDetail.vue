<template>
  <div class="detail-page">
    <header class="topbar">
      <div class="brand" @click="goBack">
        <i class="fa-solid fa-gift"></i>
        <span>Cambodia<span class="brand-accent">Rides</span></span>
      </div>
      <nav class="nav-links">
        <button class="btn-reset active">Home</button>
        <button class="btn-reset">My Bookings</button>
        <button class="btn-reset">Support</button>
      </nav>
      <div class="top-icons">
        <button class="btn-reset icon-btn"><i class="fa-regular fa-bell"></i></button>
        <button class="btn-reset icon-btn user"><i class="fa-regular fa-user"></i></button>
      </div>
    </header>

    <main class="content" v-if="vehicle">
      <p class="breadcrumbs">
        HOME <i class="fa-solid fa-angle-right"></i> SIEM REAP <i class="fa-solid fa-angle-right"></i> {{ vehicleTitle.toUpperCase() }}
      </p>

      <section class="hero-grid">
        <div class="left-panel">
          <div class="hero-image">
            <img :src="selectedImage || primaryImage" :alt="vehicleTitle" />
            <button class="photos-btn btn-reset">View All Photos</button>
          </div>

          <div class="thumbs">
            <button
              v-for="(img, idx) in galleryImages"
              :key="`${img}-${idx}`"
              class="btn-reset thumb-card"
              :class="{ active: selectedImage === img }"
              @click="selectedImage = img"
            >
              <img :src="img" :alt="`${vehicleTitle} ${idx + 1}`" />
            </button>
          </div>

          <article class="vehicle-card">
            <div class="vehicle-head">
              <div>
                <h1>{{ vehicleTitle }}</h1>
                <p class="rating-line">
                  <i class="fa-solid fa-star"></i> {{ vehicle.rating ?? 4.8 }}
                  <span class="dot">|</span> 128 Bookings
                  <span class="verified">Verified Shop</span>
                </p>
              </div>
            </div>

            <div class="spec-grid">
              <div class="spec-item">
                <span class="label">YEAR</span>
                <strong>{{ vehicle.year }}</strong>
              </div>
              <div class="spec-item">
                <span class="label">TRANS.</span>
                <strong>{{ vehicle.transmission }}</strong>
              </div>
              <div class="spec-item">
                <span class="label">FUEL</span>
                <strong>{{ vehicle.fuel_type }}</strong>
              </div>
              <div class="spec-item">
                <span class="label">SEATS</span>
                <strong>{{ vehicle.seats }}</strong>
              </div>
            </div>
          </article>

          <section class="managed-by">
            <h3>Managed by</h3>
            <div class="shop-card">
              <img class="shop-avatar" :src="primaryImage" alt="Shop" />
              <div>
                <h4>{{ shopName }}</h4>
                <p><span class="verified-text">Verified</span> | Member since 2019</p>
              </div>
              <button class="btn-reset contact-btn">Contact Shop</button>
            </div>
          </section>
        </div>

        <div class="side-stack">
          <aside class="booking-panel">
            <div class="price-box">
              <h2>${{ formatPrice(vehicle.price_per_day) }} <small>/ day</small></h2>
              <span class="instant">Instant Book</span>
            </div>

            <div class="date-grid">
              <div>
                <span>PICK-UP</span>
                <strong>{{ formatDisplayDate(pickupDate) }}</strong>
              </div>
              <div>
                <span>DROP-OFF</span>
                <strong>{{ formatDisplayDate(dropoffDate) }}</strong>
              </div>
            </div>

            <h4>OPTIONAL ADD-ONS</h4>
            <div class="addon-item active">
              <span class="addon-check-placeholder"></span>
              <div class="addon-main">
                <span>Helmets</span>
                <div class="qty-control">
                  <button class="btn-reset qty-btn" @click.stop.prevent="changeHelmetQty(-1)">-</button>
                  <span>{{ helmetQty }}</span>
                  <button class="btn-reset qty-btn" @click.stop.prevent="changeHelmetQty(1)">+</button>
                </div>
              </div>
              <strong>+$1/helmet/day</strong>
            </div>

            <label class="addon-item" :class="{ active: theftInsuranceSelected }">
              <input type="checkbox" v-model="theftInsuranceSelected" />
              <div class="addon-main">
                <span>Theft Insurance</span>
              </div>
              <strong>+$2/day</strong>
            </label>

            <label class="addon-item" :class="{ active: gpsSelected }">
              <input type="checkbox" v-model="gpsSelected" />
              <div class="addon-main">
                <span>GPS Navigation Tablet</span>
              </div>
              <strong>+$3/day</strong>
            </label>

            <div class="bill">
              <p><span>${{ formatPrice(vehicle.price_per_day) }} x {{ rentalDays }} day{{ rentalDays > 1 ? 's' : '' }}</span><strong>${{ baseTotal.toFixed(2) }}</strong></p>
              <p v-for="line in selectedAddonLines" :key="line.key"><span>{{ line.label }}</span><strong>${{ line.amount.toFixed(2) }}</strong></p>
              <p v-if="fee > 0"><span>Marketplace Fee</span><strong>${{ fee.toFixed(2) }}</strong></p>
            </div>

            <p class="total"><span>Total (USD)</span><strong>${{ grandTotal.toFixed(2) }}</strong></p>
            <button class="btn-reset request-btn">Request Booking</button>
          </aside>

          <section class="guest-reviews">
            <h3>Guest Reviews</h3>
            <div class="reviews-score">
              <strong>4.9</strong>
              <div>
                <p class="stars">*****</p>
                <small>Based on 48 reviews</small>
              </div>
            </div>
          </section>
        </div>
      </section>

      <section class="db-section">
        <h4>Vehicle Table Data</h4>
        <div class="db-grid">
          <p><strong>id:</strong> {{ vehicle.id }}</p>
          <p><strong>shop_id:</strong> {{ vehicle.shop_id ?? 'NULL' }}</p>
          <p><strong>type:</strong> {{ vehicle.type }}</p>
          <p><strong>brand:</strong> {{ vehicle.brand }}</p>
          <p><strong>model:</strong> {{ vehicle.model }}</p>
          <p><strong>year:</strong> {{ vehicle.year }}</p>
          <p><strong>price_per_day:</strong> {{ vehicle.price_per_day }}</p>
          <p><strong>fuel_type:</strong> {{ vehicle.fuel_type }}</p>
          <p><strong>transmission:</strong> {{ vehicle.transmission }}</p>
          <p><strong>seats:</strong> {{ vehicle.seats }}</p>
          <p><strong>status:</strong> {{ vehicle.status }}</p>
          <p><strong>created_at:</strong> {{ vehicle.created_at }}</p>
          <p><strong>updated_at:</strong> {{ vehicle.updated_at }}</p>
          <p><strong>image_url:</strong> {{ vehicle.image_url }}</p>
        </div>
      </section>
    </main>

    <main v-else-if="isLoading" class="not-found">
      <h2>Loading vehicle...</h2>
    </main>

    <main v-else class="not-found">
      <h2>Vehicle not found</h2>
      <p v-if="loadError">{{ loadError }}</p>
      <button class="btn-reset back-btn" @click="goBack">Back to Vehicles</button>
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

<script setup>
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

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

const normalizeVehicle = (data) => ({
  ...data,
  rating: data?.rating ?? 4.8
});

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
const shopName = computed(() => (vehicle.value ? (shopNamesById.value[vehicle.value.shop_id] || 'Unknown Shop') : 'Unknown Shop'));
const primaryImage = computed(() => (vehicle.value ? resolveImageUrl(vehicle.value.image_url) : fallbackImage));
const selectedImage = ref('');
const fallbackGallery = [
  'https://i.pinimg.com/1200x/13/01/89/130189a5dec1b3e227bf564fd6538c63.jpg',
  'https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg',
  'https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg',
  'https://i.pinimg.com/1200x/d9/9e/06/d99e06bd9dd77fb2581170af2063b3b5.jpg'
];

const galleryImages = computed(() => {
  if (!vehicle.value) return [];
  const seed = [primaryImage.value, ...fallbackGallery];
  return [...new Set(seed)].slice(0, 4);
});

watch(
  routeId,
  (id) => {
    loadVehicle(id);
  },
  { immediate: true }
);

watch(
  primaryImage,
  (img) => {
    selectedImage.value = img || fallbackImage;
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

const helmetQty = ref(0);
const theftInsuranceSelected = ref(false);
const gpsSelected = ref(false);

const changeHelmetQty = (step) => {
  const next = helmetQty.value + step;
  if (next < 0 || next > 3) return;
  helmetQty.value = next;
};

const addonRates = {
  helmet: 1,
  insurance: 2,
  gps: 3
};

const baseTotal = computed(() => (vehicle.value ? Number(vehicle.value.price_per_day) * rentalDays.value : 0));
const selectedAddonLines = computed(() => {
  const lines = [];
  const helmetAmount = helmetQty.value * addonRates.helmet * rentalDays.value;
  if (helmetQty.value > 0) {
    lines.push({
      key: 'helmet',
      label: `Helmets (${helmetQty.value}x)`,
      amount: helmetAmount
    });
  }
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
const goBack = () => router.push({ name: 'vehicles-by-shop' });
</script>

<style scoped>
.detail-page {
  min-height: 100vh;
  background: #f2f4f7;
  color: #1b2940;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.btn-reset {
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font: inherit;
}

.topbar {
  height: 62px;
  padding: 0 26px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  background: #ffffff;
  color: #1a2437;
  border-top: 2px solid #2ca6f8;
  border-bottom: 1px solid #dde4ed;
}

.brand {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 24px;
  font-weight: 700;
}

.brand i,
.brand-accent {
  color: #1ea8ff;
}

.nav-links {
  display: flex;
  gap: 18px;
  font-size: 12px;
}

.nav-links .active {
  font-weight: 700;
}

.top-icons {
  display: flex;
  gap: 10px;
  margin-left: auto;
}

.icon-btn {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: 1px solid #d7e0eb;
  color: #7a8fa9;
  display: grid;
  place-items: center;
  background: #fff;
}

.icon-btn.user {
  border-color: #f2c98f;
  color: #f2a84a;
}

.content {
  max-width: 1220px;
  margin: 0 auto;
  padding: 14px 10px 34px;
}

.breadcrumbs {
  color: #96a6ba;
  font-size: 11px;
  margin-bottom: 10px;
}

.hero-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 14px;
}

.left-panel {
  min-width: 0;
}

.hero-image {
  position: relative;
  height: 420px;
  border-radius: 9px;
  overflow: hidden;
  border: 1px solid #d8e1ed;
  background: #eef3f9;
}

.hero-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photos-btn {
  position: absolute;
  right: 10px;
  bottom: 10px;
  background: #ffffff;
  color: #22395a;
  border-radius: 8px;
  padding: 6px 10px;
  border: 1px solid #d7e1ed;
  font-size: 12px;
  font-weight: 600;
}

.thumbs {
  margin-top: 6px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}

.thumb-card {
  height: 140px;
  border-radius: 8px;
  border: 1px solid #cfd8e6;
  background: #eef2f7;
  padding: 6px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.thumb-card.active {
  border: 2px solid #1698f2;
  background: #ffffff;
  box-shadow: 0 0 0 2px rgba(22, 152, 242, 0.12);
}

.thumb-card img {
  height: 88px;
  width: 100%;
  object-fit: contain;
  border-radius: 6px;
  border: 0;
  background: transparent;
}

.thumb-card:hover {
  border-color: #8ecdf7;
}

.vehicle-card {
  margin-top: 8px;
  border-radius: 8px;
  border: 1px solid #d8e1ec;
  background: #fff;
  color: #13213f;
  padding: 14px;
}

h1 {
  margin: 0;
  font-size: 42px;
}

.rating-line {
  margin-top: 6px;
  color: #5b6d8f;
  font-size: 14px;
}

.rating-line i {
  color: #f59e0b;
}

.dot {
  margin: 0 8px;
}

.verified {
  margin-left: 8px;
  padding: 3px 8px;
  border-radius: 8px;
  background: #e7f4ff;
  color: #0f89de;
  font-weight: 600;
}

.spec-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.spec-item {
  background: #f5f8fc;
  border: 1px solid #e0e7f0;
  border-radius: 8px;
  padding: 9px;
}

.spec-item .label {
  display: block;
  font-size: 12px;
  color: #6780a3;
}

.spec-item strong {
  font-size: 13px;
}

.managed-by {
  margin-top: 12px;
}

.managed-by h3 {
  margin: 0 0 6px;
  font-size: 28px;
}

.shop-card {
  display: grid;
  grid-template-columns: 50px 1fr auto;
  align-items: center;
  gap: 10px;
  background: #fff;
  border-radius: 8px;
  border: 1px solid #d8e1ec;
  padding: 10px;
  color: #0e1e39;
}

.shop-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}

.shop-card h4 {
  margin: 0 0 2px;
  font-size: 12px;
}

.shop-card p {
  margin: 0;
  color: #61789b;
  font-size: 11px;
}

.verified-text {
  color: #0d88df;
  font-weight: 700;
}

.contact-btn {
  border: 1px solid #4ca9e6;
  border-radius: 8px;
  padding: 8px 14px;
  color: #1386de;
  font-weight: 700;
  font-size: 11px;
}

.side-stack {
  display: grid;
  gap: 10px;
  align-self: start;
}

.booking-panel {
  background: #fff;
  color: #1d2c4a;
  border-radius: 10px;
  border: 1px solid #d8e1ec;
  padding: 14px;
}

.price-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price-box h2 {
  margin: 0;
  font-size: 20px;
}

.price-box small {
  color: #7083a3;
  font-weight: 500;
  font-size: 12px;
}

.instant {
  color: #1394ea;
  font-weight: 700;
  font-size: 12px;
}

.date-grid {
  margin-top: 10px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  border: 1px solid #dbe3f0;
  border-radius: 10px;
  overflow: hidden;
}

.date-grid > div {
  padding: 8px;
}

.date-grid > div:first-child {
  border-right: 1px solid #dbe3f0;
}

.date-grid span {
  display: block;
  color: #7c8fac;
  font-size: 10px;
}

.date-grid strong {
  font-size: 16px;
}

.booking-panel h4 {
  margin: 12px 0 7px;
  font-size: 11px;
  letter-spacing: .06em;
  color: #7789a7;
}

.addon-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 8px;
  align-items: center;
  border: 1px solid #d6dfec;
  border-radius: 8px;
  padding: 6px 8px;
  margin-bottom: 6px;
  font-size: 12px;
}

.addon-main {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.qty-control {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #d1dceb;
  border-radius: 14px;
  padding: 2px 6px;
  background: #fff;
  font-size: 11px;
}

.qty-btn {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1px solid #c8d4e5;
  background: #f4f8fc;
  color: #1c446f;
  font-weight: 700;
  line-height: 1;
}

.addon-check-placeholder {
  width: 13px;
  height: 13px;
  border: 1px solid #d6dfec;
  border-radius: 3px;
}

.addon-item.active {
  border-color: #2aa0f5;
  background: #eef7ff;
}

.bill {
  border-top: 1px solid #dae3ef;
  margin-top: 8px;
  padding-top: 8px;
}

.bill p {
  display: flex;
  justify-content: space-between;
  margin: 4px 0;
  font-size: 12px;
}

.total {
  display: flex;
  justify-content: space-between;
  font-size: 22px;
  font-weight: 700;
  margin: 8px 0 10px;
}

.total strong {
  color: #1193e8;
}

.request-btn {
  width: 100%;
  background: #1ca0ef;
  color: #fff;
  border-radius: 8px;
  padding: 10px;
  font-weight: 700;
  font-size: 14px;
}

.guest-reviews {
  background: #fff;
  border: 1px solid #d8e1ec;
  border-radius: 10px;
  padding: 10px 12px;
}

.guest-reviews h3 {
  margin: 0 0 6px;
  font-size: 28px;
}

.reviews-score {
  display: flex;
  align-items: center;
  gap: 10px;
}

.reviews-score strong {
  font-size: 52px;
  line-height: 1;
}

.stars {
  margin: 0;
  color: #f59e0b;
  font-size: 14px;
}

.reviews-score small {
  color: #6d84a1;
  font-size: 11px;
}

.db-section {
  margin-top: 12px;
  background: #fff;
  border: 1px solid #d8e1ec;
  border-radius: 8px;
  padding: 10px 12px;
}

.db-section h4 {
  margin: 0 0 8px;
  font-size: 12px;
  color: #6f84a2;
}

.db-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px 12px;
}

.db-grid p {
  margin: 0;
  font-size: 12px;
  color: #294364;
}

.page-footer {
  margin-top: 24px;
  border-top: 1px solid #d8e1ed;
  background: #f7f9fc;
  padding: 24px 16px;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 24px;
}

.footer-col h4 {
  margin: 0;
  font-size: 32px;
}

.footer-col h4 span {
  color: #1ea8ff;
}

.footer-col h5 {
  margin: 0 0 8px;
  font-size: 11px;
  letter-spacing: .1em;
}

.footer-col p {
  margin: 0 0 6px;
  font-size: 12px;
  color: #6a7f9a;
}

.brand-col p {
  max-width: 300px;
}

.not-found {
  max-width: 560px;
  margin: 120px auto;
  text-align: center;
}

.back-btn {
  margin-top: 10px;
  background: #1ca0ef;
  color: #fff;
  padding: 10px 14px;
  border-radius: 8px;
}

@media (max-width: 1100px) {
  .hero-grid {
    grid-template-columns: 1fr;
  }

  .hero-image {
    height: 320px;
  }

  .page-footer {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .spec-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .thumbs {
    grid-template-columns: repeat(2, 1fr);
  }

  .thumb-card {
    height: 120px;
  }

  .topbar {
    height: auto;
    padding: 12px;
    flex-wrap: wrap;
    gap: 10px;
  }

  .db-grid {
    grid-template-columns: 1fr;
  }

  .nav-links {
    order: 3;
    width: 100%;
    justify-content: flex-start;
  }
}
</style>
