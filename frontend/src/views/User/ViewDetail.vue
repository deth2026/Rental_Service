<template>
  <div class="motoride-container">
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
          <i :class="getNavIcon(item)" class="nav-icon"></i>
          {{ item }}
        </button>
      </nav>

      <div class="top-actions">
        <span class="user-display-name">{{ userDisplayName }}</span>
        <UserProfileMenu @settings="openProfile" @logout="handleLogout" />
      </div>
    </header>

    <main class="content">
      <p v-if="isLoading" class="action-message">Loading vehicle details...</p>
      <p v-else-if="loadingError" class="action-message">{{ loadingError }}</p>

      <template v-else>
        <!-- Top Row: Image Vehicle and Booking Details -->
        <div class="top-row">
          <div class="detail-left">
            <section class="image-vehicle">
              <img :src="vehicleImage" :alt="vehicleName" class="main-img" />
              <div class="image-overlay">
                <button class="btn-reset image-zoom-btn" @click="openImageModal">
                  <i class="fas fa-search-plus"></i>
                </button>
                <button class="btn-reset image-favorite-btn" @click="toggleFavorite">
                  <i :class="isFavorite ? 'fas fa-heart' : 'far fa-heart'"></i>
                </button>
              </div>
              <div class="gallery-controls">
                <div class="dots">
                  <span v-for="(dot, index) in 3" :key="index" 
                        :class="{ active: index === currentImageIndex }"
                        @click="setImageIndex(index)"></span>
                </div>
                <button class="view-all" @click="viewAllPhotos">
                  <i class="fas fa-images"></i>
                  View All Photos
                </button>
              </div>
            </section>
            <section class="detail-hero">
              <div class="name-label">
                <h2>{{ vehicleName }}</h2>
                <p class="vehicle-meta">{{ vehicleShopName }} • {{ vehicleRating }} ★</p>
              </div>

              <div class="chip-row">
                <span class="chip">
                  <i class="fas fa-cog"></i>
                  {{ vehicleTransmission }}
                </span>
                <span class="chip">
                  <i class="fas fa-gas-pump"></i>
                  {{ vehicleFuel }}
                </span>
                <span class="chip">
                  <i class="fas fa-motorcycle"></i>
                  {{ vehicleType }}
                </span>
              </div>
            </section>
          </div>

          <aside class="booking-details">
            <div class="booking-card-header">
              <div class="header-icon">
                <i class="fas fa-calendar-check"></i>
              </div>
              <div class="header-text">
                <h3>Booking Details</h3>
                <p class="booking-card-subtitle">Select options and review the total.</p>
              </div>
            </div>
            <div class="price-row">
              <span class="price-label">
                <i class="fas fa-user"></i>
                Rider Details
              </span>
              <select v-model="riderDetails" class="rider-select">
                <option value="1 Rider">1 Rider</option>
                <option value="2 Riders">2 Riders</option>
                <option value="3 Riders">3 Riders</option>
                <option value="4+ Riders">4+ Riders</option>
              </select>
            </div>
            <div class="price-row">
              <span class="price-label">
                <i class="fas fa-tag"></i>
                Daily Rate (per rider)
              </span>
              <span class="price-value">{{ formatCurrency(dailyRate) }}</span>
            </div>
            <div class="price-row">
              <span class="price-label">
                <i class="fas fa-users"></i>
                Riders
              </span>
              <span class="price-value"
                >{{ riderCount }}
                {{ riderCount === 1 ? "Rider" : "Riders" }}</span
              >
            </div>
            <div class="price-row price-row-toggle">
              <label class="toggle-label">
                <input v-model="includeInsurance" class="toggle-checkbox" type="checkbox" />
                <span class="toggle-text">
                  <i class="fas fa-shield-alt"></i>
                  Insurance fee
                </span>
              </label>
              <span class="price-value" :class="{ muted: !includeInsurance }">{{ formatCurrency(insuranceFee) }}</span>
            </div>
            <div class="price-row price-row-toggle">
              <label class="toggle-label">
                <input v-model="includeTaxes" class="toggle-checkbox" type="checkbox" />
                <span class="toggle-text">
                  <i class="fas fa-receipt"></i>
                  Taxes & Fees
                </span>
              </label>
              <span class="price-value" :class="{ muted: !includeTaxes }">{{ formatCurrency(taxesFee) }}</span>
            </div>
            <div class="total-row">
              <span class="total-label">
                <i class="fas fa-calculator"></i>
                Total Price
              </span>
              <span class="price-blue">{{ formatCurrency(totalPrice) }}</span>
            </div>
            <button class="book-btn" @click="goToBooking">
              <i class="fas fa-check-circle"></i>
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
              <h4>
                <i class="fas fa-list-ul"></i>
                Specifications
              </h4>
              <div class="spec-list">
                <div class="spec-item">
                  <span class="spec-label">
                    <i class="fas fa-cog"></i>
                    Transmission
                  </span>
                  <span class="spec-value">{{ vehicleTransmission }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">
                    <i class="fas fa-gas-pump"></i>
                    Fuel Type
                  </span>
                  <span class="spec-value">{{ vehicleFuel }}</span>
                </div>
                <div class="spec-item">
                  <span class="spec-label">
                    <i class="fas fa-motorcycle"></i>
                    Vehicle Type
                  </span>
                  <span class="spec-value">{{ vehicleType }}</span>
                </div>
              </div>
            </div>

            <div class="content-box">
              <h4>
                <i class="fas fa-star"></i>
                Features
              </h4>
              <div class="feature-list">
                <div class="feature-item">
                  <i class="fas fa-map-marked-alt"></i>
                  GPS Navigation
                </div>
                <div class="feature-item">
                  <i class="fas fa-mobile-alt"></i>
                  Phone Mount
                </div>
                <div class="feature-item">
                  <i class="fas fa-suitcase"></i>
                  Luggage Storage
                </div>
                <div class="feature-item">
                  <i class="fas fa-headset"></i>
                  24/7 Support
                </div>
              </div>
            </div>
          </div>

          <!-- Full Width Box -->
          <div class="full-width-box">
            <h4>
              <i class="fas fa-crown"></i>
              Premium Rental Benefits
            </h4>
            <div class="benefits-grid">
              <div class="benefit-item">
                <i class="fas fa-tools"></i>
                <span>Top-of-the-line maintenance</span>
              </div>
              <div class="benefit-item">
                <i class="fas fa-shield-alt"></i>
                <span>Comprehensive insurance coverage</span>
              </div>
              <div class="benefit-item">
                <i class="fas fa-headset"></i>
                <span>Dedicated customer support</span>
              </div>
              <div class="benefit-item">
                <i class="fas fa-road"></i>
                <span>Ultimate riding adventure</span>
              </div>
            </div>
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
            <h4>
              <i class="fas fa-info-circle"></i>
              Description
            </h4>
            <div class="description-highlights">
              <div class="highlight-item">
                <i class="fas fa-check-circle"></i>
                <span>Smooth {{ vehicleTransmission }} ride</span>
              </div>
              <div class="highlight-item">
                <i class="fas fa-leaf"></i>
                <span>{{ vehicleFuel }} efficiency</span>
              </div>
              <div class="highlight-item">
                <i class="fas fa-city"></i>
                <span>Perfect for urban commuting</span>
              </div>
              <div class="highlight-item">
                <i class="fas fa-mountain"></i>
                <span>Great for long-distance adventures</span>
              </div>
            </div>
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
      <div class="footer-wrap">
        <div class="footer-top">
          <div class="footer-brand">
            <div class="footer-brand-title">CHONG CHOUL</div>
            <div class="footer-brand-slogan">Secure rides, fast bookings.</div>

            <div class="footer-social">
              <button class="btn-reset social-btn" type="button">F</button>
              <button class="btn-reset social-btn" type="button">T</button>
              <button class="btn-reset social-btn" type="button">L</button>
              <button class="btn-reset social-btn" type="button">W</button>
              <button class="btn-reset social-btn" type="button">I</button>
            </div>
            <div class="footer-social-label">Follow Us</div>
          </div>

          <div class="footer-nav">
            <div class="footer-nav-links">
              <button class="btn-reset footer-nav-link" type="button">About</button>
              <button class="btn-reset footer-nav-link" type="button">Blog</button>
              <button class="btn-reset footer-nav-link" type="button">Menu</button>
              <button class="btn-reset footer-nav-link" type="button">Services</button>
              <button class="btn-reset footer-nav-link" type="button">FAQ</button>
              <button class="btn-reset footer-nav-link" type="button">Support</button>
            </div>

            <div class="footer-about">
              <div class="footer-section-title">About Us</div>
              <p>
                Secure, fast, and transparent bookings across Cambodia with verified partners.
              </p>
            </div>
          </div>

          <div class="footer-contact">
            <div class="footer-contact-row">
              <div class="footer-contact-label">Call :</div>
              <div class="footer-contact-value">+0123 456 789 00</div>
            </div>
            <div class="footer-contact-row">
              <div class="footer-contact-label">Email:</div>
              <div class="footer-contact-value">user@example.com</div>
            </div>

            <div class="footer-newsletter">
              <input class="footer-input" type="text" placeholder="Write Email" />
              <button class="btn-reset footer-send" type="button">➤</button>
            </div>
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
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { userService } from "../../services/database.js";
import UserProfileMenu from '@/components/UserProfileMenu.vue';

const router = useRouter();
const route = useRoute();

const navItems = ["Home", "View Details", "Bookings"];
const activeNav = ref("Home");
const actionMessage = ref("");
const avatarLoadFailed = ref(false);
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
const includeInsurance = ref(false);
const includeTaxes = ref(false);
const riderDetails = ref("1 Rider");
const currentImageIndex = ref(0);
const isFavorite = ref(false);
const vehicleId = computed(() => {
  const value = Number(route.params.id);
  return Number.isFinite(value) && value > 0 ? value : null;
});

const getVehicleName = (item) => (item ? `${item.brand} ${item.model}` : "");
const getVehicleShop = (item) =>
  item?.shop_id
    ? shopNamesById.value[item.shop_id] || "Unknown Shop"
    : "Unknown Shop";

const getVehicleImage = (item) => {
  // First check for full URL (provided by backend accessor)
  if (item?.image_url_full) {
    return item.image_url_full
  }
  // Check photo_urls array
  if (item?.photo_urls && Array.isArray(item.photo_urls) && item.photo_urls.length > 0) {
    return item.photo_urls[0]
  }
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

const insuranceAmount = computed(() =>
  includeInsurance.value ? Number(insuranceFee.value || 0) : 0,
);

const taxesAmount = computed(() =>
  includeTaxes.value ? Number(taxesFee.value || 0) : 0,
);

const totalPrice = computed(() => baseAmount.value + insuranceAmount.value + taxesAmount.value);

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
  const insuranceValue = Number(insuranceAmount.value);
  const query = {};
  if (Number.isFinite(insuranceValue)) {
    query.insuranceFee = insuranceValue;
  }
  if (typeof includeInsurance.value === "boolean") {
    query.includeInsurance = includeInsurance.value ? "1" : "0";
  }
  if (typeof includeTaxes.value === "boolean") {
    query.includeTaxes = includeTaxes.value ? "1" : "0";
  }
  if (riderDetails.value) {
    query.riderDetails = riderDetails.value;
  }

  if (vehicleId) {
    router.push({ name: "user-booking", params: { id: vehicleId }, query });
  } else {
    router.push({ name: "user-booking", params: { id: 1 }, query }); // Fallback ID
  }
};

// New UX enhancement functions
const getNavIcon = (item) => {
  const iconMap = {
    'Home': 'fas fa-home',
    'View Details': 'fas fa-eye',
    'Bookings': 'fas fa-calendar-alt'
  };
  return iconMap[item] || 'fas fa-circle';
};

const openImageModal = () => {
  // Future: Open image modal/lightbox
  console.log('Opening image modal');
};

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value;
  // Future: Save to backend/localStorage
  console.log('Favorite toggled:', isFavorite.value);
};

const setImageIndex = (index) => {
  currentImageIndex.value = index;
};

const viewAllPhotos = () => {
  // Future: Navigate to photo gallery
  console.log('Viewing all photos');
};

onMounted(() => {
  loadVehicleDetail();
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
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  padding: 14px 24px;
  background: #fff;
  border-bottom: 1px solid #d8dee7;
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
}
.brand span {
  margin-right: 0;
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
  margin-right: 0;
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
  max-width: 1200px;
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

.detail-left {
  display: grid;
  gap: 18px;
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
  aspect-ratio: 16 / 9;
  max-height: 520px;
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

/* Enhanced Image Overlay */
.image-overlay {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: flex;
  gap: 0.5rem;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-vehicle:hover .image-overlay {
  opacity: 1;
}

.image-zoom-btn,
.image-favorite-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  color: #1a1d23;
}

.image-zoom-btn:hover,
.image-favorite-btn:hover {
  background: #ffffff;
  transform: scale(1.1);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.image-favorite-btn {
  color: #e53e3e;
}

.image-favorite-btn:hover {
  background: #fed7d7;
}

/* Enhanced Price Labels and Values */
.price-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #475569;
}

.price-label i {
  color: #3b82f6;
  font-size: 14px;
}

.price-value {
  font-weight: 700;
  color: #1a1d23;
}

.muted {
  color: #94a3b8;
  opacity: 0.7;
}

/* Enhanced Toggle Labels */
.toggle-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.toggle-text {
  display: flex;
  align-items: center;
  gap: 6px;
  font-weight: 600;
  color: #475569;
}

.toggle-text i {
  color: #3b82f6;
  font-size: 14px;
}

/* Enhanced Chips with Icons */
.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid #e2e8f0;
  transition: all 0.2s ease;
}

.chip:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.chip i {
  font-size: 12px;
  color: #3b82f6;
}

/* Enhanced Content Boxes */
.content-box h4 {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1a1d23;
  margin-bottom: 16px;
}

.content-box h4 i {
  color: #3b82f6;
  font-size: 16px;
}

/* Enhanced Specification Items */
.spec-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f1f5f9;
  transition: background-color 0.2s ease;
}

.spec-item:hover {
  background: #f8fafc;
  margin: 0 -8px;
  padding: 12px 8px;
  border-radius: 8px;
}

.spec-item:last-child {
  border-bottom: none;
}

.spec-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #475569;
}

.spec-label i {
  color: #3b82f6;
  font-size: 14px;
}

.spec-value {
  font-weight: 700;
  color: #1a1d23;
}

/* Enhanced Feature Items */
.feature-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 0;
  transition: transform 0.2s ease;
}

.feature-item:hover {
  transform: translateX(4px);
}

.feature-item i {
  color: #10b981;
  font-size: 16px;
  width: 20px;
  text-align: center;
}

/* Benefits Grid */
.benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin: 20px 0;
}

.benefit-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.benefit-item:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.benefit-item i {
  color: #3b82f6;
  font-size: 18px;
  width: 24px;
  text-align: center;
}

.benefit-item span {
  font-weight: 600;
  color: #475569;
  font-size: 0.9rem;
}

/* Description Highlights */
.description-highlights {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
  margin: 16px 0;
}

.highlight-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: #f0f9ff;
  border-radius: 8px;
  border: 1px solid #bae6fd;
  transition: all 0.2s ease;
}

.highlight-item:hover {
  background: #e0f2fe;
  transform: translateY(-1px);
}

.highlight-item i {
  color: #0ea5e9;
  font-size: 14px;
  width: 16px;
  text-align: center;
}

.highlight-item span {
  font-weight: 600;
  color: #0c4a6e;
  font-size: 0.85rem;
}

/* Enhanced Book Button */
.book-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(29, 78, 216, 0.3);
}

.book-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(29, 78, 216, 0.4);
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}

.book-btn:active {
  transform: translateY(0);
}

.book-btn i {
  font-size: 18px;
}

/* Enhanced Gallery Dots */
.dots {
  display: flex;
  gap: 8px;
}

.dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: all 0.3s ease;
}

.dots span.active {
  width: 24px;
  border-radius: 4px;
  background: white;
}

.dots span:hover:not(.active) {
  background: rgba(255, 255, 255, 0.8);
}

/* Enhanced View All Button */
.view-all {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 20px;
  color: #1a1d23;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.view-all:hover {
  background: white;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.view-all i {
  font-size: 12px;
}

/* Enhanced Navigation Icons */
.nav-icon {
  margin-right: 6px;
  font-size: 12px;
  transition: transform 0.2s ease;
}

.nav-link:hover .nav-icon {
  transform: translateY(-1px);
}

.booking-details {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  padding: 22px;
  box-shadow:
    0 18px 50px rgba(15, 23, 42, 0.12);
  height: fit-content;
  position: sticky;
  top: 96px;
}

.booking-details h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0;
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

.booking-card-header {
  padding: 10px 10px 18px;
  border-bottom: 1px solid #eef2f7;
  margin: -6px -6px 4px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 16px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.header-text h3 {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0;
  text-align: left;
}

.header-text h3::after {
  display: none;
}

.booking-card-subtitle {
  margin: 10px 0 0;
  text-align: center;
  color: #64748b;
  font-size: 0.92rem;
  font-weight: 600;
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
  margin-bottom: 12px;
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

.detail-hero {
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 18px 20px;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.chip-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.chip {
  display: inline-flex;
  align-items: center;
  padding: 8px 12px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #334155;
  font-weight: 700;
  font-size: 0.85rem;
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
  padding: 14px 10px;
  font-size: 0.98rem;
  border-bottom: 1px solid #f1f5f9;
}

.price-row-toggle {
  gap: 12px;
}

.toggle-label {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  font-weight: 600;
  color: #64748b;
}

.toggle-checkbox {
  width: 18px;
  height: 18px;
  accent-color: #1d4ed8;
  cursor: pointer;
}

.muted {
  color: #94a3b8;
}

.price-row span {
  color: #64748b;
  font-weight: 700;
}

.price-row span:last-child {
  color: #1a1d23;
  font-weight: 700;
  font-size: 1.05rem;
}

.price-row:first-of-type {
  border-top: none;
}

.rider-select {
  padding: 10px 12px;
  border: 1px solid #dbe4f0;
  border-radius: 8px;
  background: white;
  color: #1a1d23;
  font-weight: 600;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 110px;
  min-width: 0;
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

.total-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  padding: 18px 10px 6px;
  border-top: 1px solid #e8eef7;
  border-bottom: none;
  margin-top: 6px;
}

.total-row span:first-child {
  color: #0f172a;
  font-weight: 800;
  font-size: 1.02rem;
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
  margin-top: 18px;
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
  background: #ffffff;
  color: #0f172a;
}

.footer-wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 22px 2rem 0;
}

.footer-top {
  display: grid;
  grid-template-columns: 1fr 1.6fr 1fr;
  gap: 26px;
  padding: 20px 0 22px;
  border-bottom: 1px solid #e6edf6;
  align-items: start;
}

.footer-brand-title {
  font-size: 1.15rem;
  font-weight: 900;
  letter-spacing: 0.01em;
  color: #0f172a;
}

.footer-brand-slogan {
  margin-top: 6px;
  font-size: 0.88rem;
  font-weight: 700;
  color: #3b82f6;
}

.footer-social {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.social-btn {
  width: 30px;
  height: 30px;
  border-radius: 999px;
  border: 1px solid #cfd9e6;
  color: #334155;
  font-weight: 800;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.social-btn:first-child {
  border-color: #3b82f6;
  background: #3b82f6;
  color: #ffffff;
}

.social-btn:hover {
  background: #3b82f6;
  border-color: #3b82f6;
  color: #ffffff;
}

.footer-social-label {
  margin-top: 10px;
  font-size: 0.9rem;
  font-weight: 800;
  color: #0f172a;
}

.footer-nav-links {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  justify-content: center;
}

.footer-nav-link {
  font-size: 0.9rem;
  font-weight: 800;
  color: #475569;
  transition: color 0.2s ease;
}

.footer-nav-link:first-child {
  color: #3b82f6;
}

.footer-nav-link:hover {
  color: #3b82f6;
}

.footer-about {
  margin-top: 18px;
  padding-top: 18px;
  border-top: 1px solid #e6edf6;
}

.footer-section-title {
  font-size: 1rem;
  font-weight: 900;
  color: #0f172a;
  text-align: center;
}

.footer-about p {
  margin: 10px auto 0;
  max-width: 520px;
  text-align: center;
  color: #52627b;
  line-height: 1.7;
  font-size: 0.92rem;
}

.footer-contact {
  border-left: 1px solid #e6edf6;
  padding-left: 22px;
}

.footer-contact-row {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 10px;
  margin-bottom: 10px;
  align-items: baseline;
}

.footer-contact-label {
  font-weight: 900;
  color: #0f172a;
  font-size: 0.88rem;
}

.footer-contact-value {
  font-weight: 700;
  color: #52627b;
  font-size: 0.88rem;
}

.footer-newsletter {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr auto;
  border: 1px solid #d8e2f0;
  border-radius: 10px;
  overflow: hidden;
  background: #ffffff;
}

.footer-input {
  border: 0;
  background: transparent;
  color: #0f172a;
  height: 36px;
  padding: 0 12px;
  font-size: 0.88rem;
  outline: none;
}

.footer-input::placeholder {
  color: #94a3b8;
}

.footer-send {
  width: 40px;
  background: #3b82f6;
  color: #ffffff;
  font-weight: 900;
}

.footer-bottom {
  margin: 0;
  padding: 14px 0 22px;
  border-top: 0;
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

.footer-bottom-links span {
  color: #475569;
  font-weight: 800;
  font-size: 0.82rem;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  transition:
    background 0.18s ease,
    border-color 0.18s ease,
    color 0.18s ease;
}

.footer-bottom-links span:hover {
  color: #0f172a;
  border-color: #3b82f6;
  background: #eff6ff;
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
  .topbar {
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

  .footer-top {
    grid-template-columns: 1fr;
  }

  .footer-contact {
    border-left: 0;
    padding-left: 0;
    padding-top: 18px;
    border-top: 1px solid #e6edf6;
  }
}

@media (max-width: 480px) {
  .topbar {
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
