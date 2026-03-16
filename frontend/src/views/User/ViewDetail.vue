<template>
  <div class="motoride-container">
    <header class="topbar">
      <div class="topbar-inner">
        <div class="brand">
          <div class="brand-icon">
            <i class="fa-solid fa-gift" aria-hidden="true"></i>
          </div>
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
            <img
              v-if="userAvatarUrl"
              :src="userAvatarUrl"
              alt="Profile photo"
              class="avatar-image"
              @error="onAvatarError"
            />
            <span v-else>{{ userInitials }}</span>
          </button>
          <button class="btn-reset logout-btn" @click="handleLogout">
            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
            <span>Logout</span>
          </button>
        </div>
      </div>
    </header>

    <main class="content">
      <p v-if="isLoading" class="action-message">Loading vehicle details...</p>
      <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>

      <template v-else>
        <!-- Top Row: Image Vehicle and Booking Details -->
        <div class="top-row">
          <div class="gg">
            <section class="image-vehicle" style="margin-bottom: 5vh;">
              <img :src="vehicleImage" :alt="vehicleName" class="main-img" />
              <div class="gallery-controls">
                <div class="dots">
                  <span></span><span class="active"></span><span></span>
                </div>
                <button class="view-all">View All Photos</button>
              </div>
            </section>
            <!-- Name Label below Image Vehicle -->
            <div class="name-label">
              <h2>{{ vehicleName }}</h2>
              <p class="vehicle-meta">
                {{ vehicleShopName }} • {{ vehicleRating }} ★
              </p>
            </div>
          </div>

          <aside class="booking-details">
            <h3>Booking Details</h3>
            <div class="price-row">
              <span>Rider Details</span>
              <select v-model="riderDetails" class="rider-select">
                <option value="1 Rider">1 Rider</option>
                <option value="2 Riders">2 Riders</option>
                <option value="3 Riders">3 Riders</option>
                <option value="4+ Riders">4+ Riders</option>
              </select>
            </div>
            <div class="price-row">
              <span>Daily Rate (per rider)</span>
              <span>{{ formatCurrency(dailyRate) }}</span>
            </div>
            <div class="price-row">
              <span>Riders</span>
              <span
                >{{ riderCount }}
                {{ riderCount === 1 ? "Rider" : "Riders" }}</span
              >
            </div>
            <div class="price-row">
              <span>Insurance fee</span>
              <span>{{ formatCurrency(insuranceFee) }}</span>
            </div>
            <div class="price-row">
              <span>Taxes & Fees</span>
              <span>{{ formatCurrency(taxesFee) }}</span>
            </div>
            <div class="total-row">
              <span>Total Price</span>
              <span class="price-blue">{{ formatCurrency(totalPrice) }}</span>
            </div>
            <button class="book-btn" @click="goToBooking">
              Book This Bike
            </button>
            <p class="disclaimer">
              No payment taken until booking is confirmed
            </p>
          </aside>
        </div>

        <!-- Middle Section: Name Label and Content Boxes -->
        <div class="middle-section">
          <!-- Two Side-by-Side Boxes -->
          <div class="two-box-row">
            <div class="content-box">
              <h4>Specifications</h4>
              <div class="spec-list">
                <div class="spec-item">
                  <span class="spec-label">Transmission</span>
                  <span class="spec-value">{{ vehicleTransmission }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">Fuel Type</span>
                  <span class="spec-value">{{ vehicleFuel }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">Vehicle Type</span>
                  <span class="spec-value">{{ vehicleType }}</span>
                </div>
              </div>
            </div>

            <div class="content-box">
              <h4>Features</h4>
              <div class="feature-list">
                <div class="feature-item">✓ GPS Navigation</div>
                <div class="feature-item">✓ Phone Mount</div>
                <div class="feature-item">✓ Luggage Storage</div>
                <div class="feature-item">✓ 24/7 Support</div>
              </div>
            </div>
          </div>

          <!-- Full Width Box -->
          <div class="full-width-box">
            <h4>Premium Rental Benefits</h4>
            <p>
              Experience the ultimate riding adventure with our premium rental
              service. Enjoy top-of-the-line maintenance, comprehensive
              insurance coverage, and dedicated customer support throughout your
              journey.
            </p>
          </div>
        </div>

        <!-- Bottom Section: Full Width Box -->
        <div class="bottom-section">
          <div class="full-width-box">
            <h4>Description</h4>
            <p>
              Enjoy the {{ vehicleName }} from
              {{ vehicleShopName || "our partners" }} with a smooth
              {{ vehicleTransmission }} ride and {{ vehicleFuel }} efficiency.
              Perfect for both urban commuting and long-distance adventures,
              this vehicle offers exceptional comfort and reliability.
            </p>
          </div>
        </div>
      </template>
    </main>

    <footer class="site-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <strong>Chong Choul<span>Rides</span></strong>
          <p>Secure, fast, and transparent bookings across Cambodia.</p>
          <div class="footer-badges">
            <span class="badge-pill">Secure Checkout</span>
            <span class="badge-pill">Verified Partners</span>
          </div>
        </div>
        <div class="footer-links">
          <h4>Support</h4>
          <p>Help Center</p>
          <p>Cancel your booking</p>
          <p>Contact Us</p>
        </div>
        <div class="footer-links">
          <h4>Terms &amp; Privacy</h4>
          <p>Terms of Service</p>
          <p>Privacy Policy</p>
          <p>Cookie Policy</p>
        </div>
        <div class="footer-links">
          <h4>Company</h4>
          <p>About</p>
          <p>Partners</p>
          <p>Careers</p>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Chong Choul. All rights reserved.</span>
        <div class="footer-bottom-links">
          <span>Security</span>
          <span>Accessibility</span>
          <span>Legal</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { userService } from "../../services/database.js";

const router = useRouter();
const route = useRoute();

const navItems = ["Home", "Viewdetails", "Bookings"];
const activeNav = ref("Home");
const actionMessage = ref("");
const avatarLoadFailed = ref(false);
const LAST_VEHICLE_ID_KEY = "last_vehicle_id";
const currentUser = computed(() => userService.getCurrentUser());
const userDisplayName = computed(() => currentUser.value?.name || "customer");

const normalizeAvatarUrl = (url) => {
  if (!url) return "";
  if (/^(https?:\/\/|data:|blob:)/i.test(url)) return url;
  const normalized = String(url).replace(/\\/g, "/").replace(/^\/+/, "");
  if (normalized.startsWith("storage/")) return `/${normalized}`;
  return `/storage/${normalized}`;
};

const userAvatarUrl = computed(() => {
  if (avatarLoadFailed.value) return "";
  const src =
    currentUser.value?.avatar_url ||
    currentUser.value?.profile_picture ||
    currentUser.value?.img_url ||
    "";
  return normalizeAvatarUrl(src);
});

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const userInitials = computed(() => {
  const words = String(userDisplayName.value)
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  if (words.length === 0) return "CU";
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
  return `${words[0][0] || ""}${words[1][0] || ""}`.toUpperCase();
});

const setActiveNav = (item) => {
  activeNav.value = item;
  if (item === "Home") {
    router.replace("/view_shop");
    return;
  }
  actionMessage.value = `${item} is not available yet.`;
};

const openProfile = () => {
  router.push("/user/profile");
};

const handleLogout = async () => {
  await userService.logout();
  router.push("/login");
};

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, "");
const fallbackImageByType = {
  motorbike:
    "https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg",
  bicycle:
    "https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg",
  car: "https://i.pinimg.com/1200x/d9/9e/06/d99e06bd9dd77fb2581170af2063b3b5.jpg",
};

const vehicle = ref(null);
const shopNamesById = ref({});
const isLoading = ref(false);
const loadingError = ref("");
const insuranceFee = ref(30);
const riderDetails = ref("1 Rider");
const vehicleId = computed(() => {
  const value = Number(route.params.id);
  return Number.isFinite(value) && value > 0 ? value : null;
});

const setLastVehicleId = (id) => {
  if (!id) return;
  localStorage.setItem(LAST_VEHICLE_ID_KEY, String(id));
};

const getLastVehicleId = () => {
  const raw = localStorage.getItem(LAST_VEHICLE_ID_KEY);
  const parsed = Number(raw);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
};

const getVehicleName = (item) => (item ? `${item.brand} ${item.model}` : "");
const getVehicleShop = (item) =>
  item?.shop_id
    ? shopNamesById.value[item.shop_id] || "Unknown Shop"
    : "Unknown Shop";

const getVehicleImage = (item) => {
  const image = item?.image_url ? String(item.image_url).trim() : "";
  if (image) {
    if (image.startsWith("http://") || image.startsWith("https://"))
      return image;
    if (image.startsWith("/")) return `${API_ROOT}${image}`;
    return `${API_ROOT}/storage/${image.replace(/^storage\//, "")}`;
  }
  const normalizedType = String(item?.type || "").toLowerCase();
  return fallbackImageByType[normalizedType] || fallbackImageByType.motorbike;
};

const vehicleName = computed(() => getVehicleName(vehicle.value) || "Vehicle");
const vehicleShopName = computed(() => getVehicleShop(vehicle.value));
const vehicleRating = computed(
  () => vehicle.value?.rating ?? vehicle.value?.average_rating ?? 4.8,
);
const vehicleTransmission = computed(
  () => vehicle.value?.transmission || "N/A",
);
const vehicleFuel = computed(() => vehicle.value?.fuel_type || "N/A");
const vehicleType = computed(() => vehicle.value?.type || "Vehicle");
const vehicleImage = computed(() => getVehicleImage(vehicle.value));

const dailyRate = computed(() => Number(vehicle.value?.price_per_day || 0));
const riderCount = computed(() => {
  const match = String(riderDetails.value).match(/^\s*(\d+)/);
  if (!match) return 1;
  const count = Number(match[1]);
  return Number.isFinite(count) && count > 0 ? count : 1;
});
const baseAmount = computed(() => dailyRate.value * riderCount.value);
const taxesFee = computed(() => baseAmount.value * 0.1);
const totalPrice = computed(
  () => baseAmount.value + insuranceFee.value + taxesFee.value,
);

const formatCurrency = (value) =>
  new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(
    Number(value || 0),
  );

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

const loadVehicleDetail = async () => {
  if (!vehicleId.value) {
    loadingError.value = "Vehicle not found.";
    return;
  }
  isLoading.value = true;
  loadingError.value = "";

  try {
    const [vehicleList, shopList] = await Promise.all([
      loadAllPages("vehicles"),
      loadAllPages("shops"),
    ]);

    shopNamesById.value = shopList.reduce((acc, shop) => {
      acc[shop.id] = shop.name;
      return acc;
    }, {});

    const found = vehicleList.find(
      (item) => Number(item.id) === vehicleId.value,
    );
    if (!found) {
      throw new Error("Vehicle not found.");
    }

    vehicle.value = {
      ...found,
      rating: found.rating ?? found.average_rating ?? 4.8,
    };
  } catch (error) {
    const status = error?.response?.status;
    const message = error?.response?.data?.message || error.message;
    console.error("API Error:", status, message);

    if (status === 401) {
      loadingError.value = "Authentication required. Please log in.";
    } else if (status === 500) {
      loadingError.value = "Server error. Please try again later.";
    } else {
      loadingError.value = `Could not load vehicle (${status || "network error"}). Check backend server.`;
    }
  } finally {
    isLoading.value = false;
  }
};

const goToBooking = () => {
  const vehicleId = route.params.id;
  const insuranceValue = Number(insuranceFee.value);
  const query = {};
  if (Number.isFinite(insuranceValue)) {
    query.insuranceFee = insuranceValue;
  }
  if (riderDetails.value) {
    query.riderDetails = riderDetails.value;
  }

  if (vehicleId) {
    setLastVehicleId(vehicleId);
    router.push({ name: "user-booking", params: { id: vehicleId }, query });
  } else {
    router.push({ name: "user-booking", params: { id: 1 }, query }); // Fallback ID
  }
};

onMounted(() => {
  loadVehicleDetail();
  if (vehicleId.value) {
    setLastVehicleId(vehicleId.value);
  }
});
</script>

<style scoped>
/* Modern Design System */
.motoride-container {
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
    Arial, sans-serif;
  color: #1a1d23;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  min-height: 100vh;
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
  background: #fff;
  border-bottom: 1px solid #d8dee7;
}

.topbar-inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 14px 2rem;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #1d4ed8;
  white-space: nowrap;
  padding-left: 6vh;
}
.brand span {
  margin-right: 35vh;
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
  gap: 50px;
  justify-self: center;
  margin-right: 28vh;
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
  color: #1d4ed8;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-self: end;
  white-space: nowrap;
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
  background: #1d4ed8;
  color: #fff;
  font-weight: 700;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  object-position: center;
}

.logout-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  background: #1d4ed8;
  color: #fff;
  font-size: 14px;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);
}

.logout-btn:hover {
  background: #1d4ed8;
}

/* Main Content */
.content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.action-message {
  margin: 0 0 12px;
  color: #1d4ed8;
  font-size: 14px;
}

/* Wireframe Layout Styles */
.top-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.image-vehicle {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  background: white;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 60vh;
}

.image-vehicle .main-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.image-vehicle:hover .main-img {
  transform: scale(1.02);
}

.image-vehicle .gallery-controls {
  position: absolute;
  bottom: 2rem;
  left: 2rem;
  right: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.booking-details {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  height: fit-content;
}

.booking-details h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 2rem 0;
  text-align: center;
  position: relative;
}

.booking-details h3::after {
  content: "";
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  border-radius: 2px;
}

.middle-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin-bottom: 2rem;
}

/* New Layout Styles for Wireframe */
.middle-section {
  margin-bottom: 2rem;
}

.name-label {
  margin-bottom: 2rem;
  text-align: left;
}

.name-label h2 {
  font-size: 2rem;
  font-weight: 800;
  color: #1a1d23;
  margin: 0 0 0.5rem 0;
  line-height: 1.1;
}

.vehicle-meta {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
  font-weight: 500;
}

.two-box-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.content-box {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  min-height: 200px;
}

.content-box h4 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 1.5rem 0;
  position: relative;
  padding-left: 1rem;
}

.content-box h4::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 20px;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  border-radius: 2px;
}

.spec-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.spec-item:last-child {
  border-bottom: none;
}

.spec-label {
  color: #64748b;
  font-weight: 500;
  font-size: 0.95rem;
}

.spec-value {
  color: #1a1d23;
  font-weight: 700;
  font-size: 0.95rem;
}

.feature-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.feature-item {
  color: #475569;
  font-size: 0.95rem;
  font-weight: 500;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.feature-item:last-child {
  border-bottom: none;
}

.full-width-box {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.full-width-box h4 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 1.5rem 0;
  position: relative;
  padding-left: 1rem;
}

.full-width-box h4::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  border-radius: 2px;
}

.full-width-box p {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #475569;
  margin: 0;
}

.bottom-section {
  margin-bottom: 2rem;
}

.info-box {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  min-height: 200px;
}

.bottom-rows {
  display: grid;
  grid-template-rows: 1fr 1fr;
  gap: 2rem;
}

.horizontal-box {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.dots {
  display: flex;
  gap: 0.5rem;
}

.dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  transition: all 0.3s ease;
}

.dots span.active {
  width: 24px;
  border-radius: 4px;
  background: white;
}

.view-all {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  color: #1a1d23;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.view-all:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Component Styles for New Layout */
.title-section {
  text-align: center;
}

.title-section h1 {
  font-size: 1.8rem;
  font-weight: 800;
  color: #1a1d23;
  margin: 0 0 1rem 0;
  line-height: 1.1;
}

.meta {
  font-size: 1rem;
  color: #64748b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  justify-content: center;
}

.badge {
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  display: inline-block;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);
}

.spec-cards {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

.spec-card {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  padding: 1rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.spec-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border-color: #1d4ed8;
}

.spec-card .icon {
  font-size: 1.2rem;
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spec-card .icon i {
  color: white;
}

.spec-card small {
  color: #64748b;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: block;
  margin-bottom: 0.25rem;
}

.spec-card p {
  margin: 0;
  font-weight: 700;
  font-size: 0.9rem;
  color: #1a1d23;
}

.additional-info h3 {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 1rem 0;
}

.additional-info ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.additional-info li {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: 0.9rem;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.additional-info li::before {
  content: "✓";
  color: #1d4ed8;
  font-weight: 700;
  font-size: 1rem;
}

.additional-info li:last-child {
  border-bottom: none;
}

/* Booking Details Styles */
.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 0;
  font-size: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.price-row span {
  color: #64748b;
  font-weight: 500;
}

.price-row span:last-child {
  color: #1a1d23;
  font-weight: 700;
  font-size: 1.05rem;
}

.total-row {
  border-bottom: none;
  border-top: 2px solid #e2e8f0;
  padding-top: 1.5rem;
  margin-top: 0.5rem;
}

.rider-select {
  padding: 8px 12px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #1a1d23;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
}

.rider-select:focus {
  outline: none;
  border-color: #1d4ed8;
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
}

.rider-select:hover {
  border-color: #1d4ed8;
}

.price-blue {
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8rem;
  font-weight: 800;
}

.book-btn {
  width: 100%;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  color: white;
  border: none;
  padding: 1.25rem;
  border-radius: 16px;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  margin-top: 2rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(29, 78, 216, 0.3);
}

.book-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(29, 78, 216, 0.4);
}

.disclaimer {
  text-align: center;
  color: #64748b;
  font-size: 0.9rem;
  margin-top: 1rem;
  font-style: italic;
}

/* Description and Terms Styles */
.description h3,
.rental-terms h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 1.5rem 0;
  position: relative;
  padding-left: 1rem;
}

.description h3::before,
.rental-terms h3::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: linear-gradient(135deg, #1d4ed8 0%, #1d4ed8 100%);
  border-radius: 2px;
}

.description p {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #475569;
  margin: 0;
}

.rental-terms {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.rental-terms ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.rental-terms li {
  padding: 1rem 0;
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  font-size: 1rem;
  color: #475569;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.rental-terms li::before {
  content: "✓";
  color: #1d4ed8;
  font-weight: 700;
  font-size: 1.2rem;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.rental-terms li:last-child {
  border-bottom: none;
}

/* Footer Styles */
.site-footer {
  margin-top: 28px;
  border-top: 1px solid #d8dee7;
  background: #fff;
}

.footer-inner {
  max-width: 1400px;
  margin: 0 auto;
  padding: 26px 2rem;
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr 1fr;
  gap: 24px;
}

.footer-brand strong {
  font-size: 24px;
}

.footer-brand strong span {
  color: #1d4ed8;
}

.footer-brand p {
  margin: 8px 0 14px;
  max-width: 420px;
  color: #66758d;
}

.footer-badges {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.badge-pill {
  background: #eef2ff;
  color: #1d4ed8;
  border: 1px solid #dbe4f0;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
}

.footer-links h4 {
  margin: 2px 0 10px;
  font-size: 15px;
}

.footer-links p {
  margin: 0 0 8px;
  color: #52627b;
}

.footer-bottom {
  max-width: 1400px;
  margin: 0 auto;
  padding: 14px 2rem 22px;
  border-top: 1px solid #d8dee7;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  color: #75839b;
  font-size: 0.85rem;
  flex-wrap: wrap;
}

.footer-bottom-links {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .top-row {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .two-box-row {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .booking-details {
    position: static;
  }
}

@media (max-width: 768px) {
  .topbar-inner {
    grid-template-columns: 1fr;
    gap: 10px;
    padding: 12px 16px;
  }

  .nav-links,
  .top-actions {
    justify-self: start;
  }

  .nav-links {
    flex-wrap: wrap;
  }

  .content {
    padding: 1rem;
  }

  .name-label h2 {
    font-size: 1.5rem;
  }

  .content-box,
  .full-width-box {
    padding: 1.5rem;
  }

  .footer-inner {
    grid-template-columns: 1fr;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
  }
}

@media (max-width: 480px) {
  .topbar-inner {
    padding: 12px 16px;
  }

  .brand {
    font-size: 18px;
  }

  .name-label h2 {
    font-size: 1.2rem;
  }

  .gallery-controls {
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
  }

  .view-all {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
}
</style>
