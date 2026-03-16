<template>
  <div class="booking-checkout-page">
    <header class="topbar">
      <div class="topbar-inner">
        <Logo class="brand" to="/view_shop" size="sm" />

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
      </div>
    </header>

    <div class="page-container">

      <div class="page-heading-row">
        <h1>Checkout</h1>
        <span class="step-pill">Step 2 of 3</span>
      </div>

      <div class="checkout-progress" aria-hidden="true">
        <span class="progress-value"></span>
      </div>

      <p v-if="isLoading" class="page-subtitle">Loading vehicle details...</p>
      <p v-else-if="loadingError" class="page-subtitle">{{ loadingError }}</p>
      <p v-else class="page-subtitle">
        Securely complete your rental booking for the {{ rental.title }}
      </p>

      <div class="checkout-layout">
        <aside class="sidebar">
          <div class="card summary-card">
            <div class="motorcycle-image">
              <img
                :src="vehicleImage"
                :alt="vehicleName"
              />
              <span class="vehicle-tag">{{ vehicleTag }}</span>
            </div>

            <h2 class="item-title">{{ rental.title }}</h2>
            <p class="item-location">{{ rental.location }}</p>

            <div class="detail-list">
              <div class="row">
                <span class="label">Rental Period</span>
                <span class="value">{{ rental.period || "Select dates below" }}</span>
              </div>
              <div class="row">
                <span class="label">Rider Details</span>
                <span class="value">{{ rental.riders }}</span>
              </div>
              <div class="row">
                <span class="label">Daily Rate</span>
                <span class="value">${{ rental.dailyRate.toFixed(2) }}/day</span>
              </div>
            </div>

          <div class="pricing-list">
            <div class="row">
              <span>Daily Rate x {{ calculateDays() }} day(s) x {{ riderCount }} rider(s)</span>
              <span>${{ rental.subtotal.toFixed(2) }}</span>
            </div>
              <div class="row">
                <span>Insurance</span>
                <span>${{ rental.insurance.toFixed(2) }}</span>
              </div>
              <div class="row">
                <span>Taxes & Fees</span>
                <span>${{ rental.taxes.toFixed(2) }}</span>
              </div>
              <hr class="dashed-divider" />
              <div class="row total-row">
                <span>Total Amount</span>
                <span class="total-price">${{ totalAmount.toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <div class="card promo-card">
            <h3>Have a promo code?</h3>
            <div class="promo-input-group">
              <input
                type="text"
                placeholder="Enter code"
                v-model="promoCode"
              />
              <button class="btn-secondary" type="button">Apply</button>
            </div>
          </div>

          <div class="card encryption-card">
            <h4>Secure Transaction</h4>
            <p>
              Your payment is encrypted and protected. We do not store your full
              payment details.
            </p>
          </div>
        </aside>

        <main class="payment-main">
          <div class="card checkout-form-card">
            <div class="checkout-header">
              <h2>{{ methodTitle }}</h2>
              <p>{{ methodDescription }}</p>
            </div>


            <div class="form-divider">
              <span>OR PAY WITH CARD</span>
            </div>

            <div class="payment-tabs">
              <button
                class="tab"
                :class="{ active: method === 'card' }"
                type="button"
                @click="method = 'card'"
              >
                Credit Card
              </button>
              <button
                class="tab"
                :class="{ active: method === 'qr' }"
                type="button"
                @click="method = 'qr'"
              >
                QR Code
              </button>
            </div>

            <div class="date-selection">
              <h3>Rental dates</h3>
              <div class="date-inputs">
                <div class="input-group">
                  <label>Pick-up</label>
                  <input
                    type="date"
                    v-model="rental.startDate"
                    :min="minDate"
                    @change="validateDates"
                    required
                  />
                </div>
                <div class="input-group">
                  <label>Drop-off</label>
                  <input
                    type="date"
                    v-model="rental.endDate"
                    :min="rental.startDate || minDate"
                    @change="validateDates"
                    required
                  />
                </div>
              </div>

              <div class="date-summary" v-if="rental.startDate && rental.endDate">
                <span class="days-count">{{ calculateDays() }} days</span>
                <span class="daily-rate">${{ rental.dailyRate.toFixed(2) }}/day</span>
              </div>

              <p class="date-error" v-if="dateError">{{ dateError }}</p>
            </div>

            <form
              v-if="method === 'card'"
              class="card-details-form"
              autocomplete="on"
              @submit.prevent="handlePayment"
            >
              <div class="input-group">
                <label>Cardholder Name</label>
                <input type="text" placeholder="Johnathan Doe" required />
              </div>

              <div class="input-group">
                <label>Card Number</label>
                <input
                  type="text"
                  name="cc-number"
                  autocomplete="cc-number"
                  inputmode="numeric"
                  placeholder="0000 0000 0000 0000"
                  required
                />
              </div>

              <div class="input-row">
                <div class="input-group">
                  <label>Expiry Date</label>
                  <input
                    type="text"
                    name="cc-exp"
                    autocomplete="cc-exp"
                    inputmode="numeric"
                    placeholder="MM / YY"
                    pattern="(0[1-9]|1[0-2])\s?\/\s?\d{2}"
                    required
                  />
                </div>
                <div class="input-group">
                  <label>CVV / CVC</label>
                  <input
                    type="password"
                    name="cc-csc"
                    autocomplete="cc-csc"
                    inputmode="numeric"
                    placeholder="***"
                    maxlength="3"
                    pattern="\d{3}"
                    required
                  />
                </div>
              </div>

              <div class="secure-info-box">
                <p>
                  Your payment is processed securely via 256-bit SSL encryption.
                  We never store your full card details.
                </p>
              </div>

              <button
                type="submit"
                class="btn-primary-pay"
                :disabled="!isFormValid"
              >
                Pay ${{ totalAmount.toFixed(2) }} Now
              </button>
            </form>

            <div v-if="method === 'bank'" class="bank-transfer-payment">
              <div class="bank-header">
                <h3>Bank Transfer Payment</h3>
                <p>Transfer funds directly to our bank account.</p>
              </div>

              <div class="bank-info-card">
                <div class="bank-row">
                  <span class="bank-label">Account Name</span>
                  <span class="bank-value">Moto Rental Pty Ltd</span>
                </div>
                <div class="bank-row">
                  <span class="bank-label">Bank</span>
                  <span class="bank-value">National Australia Bank</span>
                </div>
                <div class="bank-row">
                  <span class="bank-label">BSB</span>
                  <span class="bank-value">082-123</span>
                </div>
                <div class="bank-row">
                  <span class="bank-label">Account</span>
                  <span class="bank-value">1234 5678 9012</span>
                </div>
                <div class="bank-row">
                  <span class="bank-label">Reference</span>
                  <span class="bank-value reference">{{ paymentId }}</span>
                </div>
              </div>

              <div class="transfer-instructions">
                <h4>How to pay</h4>
                <ol>
                  <li>Open your bank app and start a transfer.</li>
                  <li>Send exactly ${{ totalAmount.toFixed(2) }}.</li>
                  <li>Use <strong>{{ paymentId }}</strong> as your reference.</li>
                  <li>Click confirm below after transfer.</li>
                </ol>
              </div>

              <div class="important-note">
                <div class="note-content">
                  Payment verification may take 1-2 business days.
                </div>
              </div>

              <button
                type="button"
                class="btn-primary-pay"
                :disabled="!isFormValid"
                @click="handleBankTransfer"
              >
                Confirm bank transfer
              </button>
            </div>

            <div v-if="method === 'qr'" class="qr-payment">
              <div class="qr-payment-card">
                <div class="qr-header">
                  <h3>QR Code Payment</h3>
                  <p>Pay directly with your banking app.</p>
                </div>

                <div class="qr-amount-section">
                  <span class="amount-label">Total Amount</span>
                  <span class="amount-value">${{ totalAmount.toFixed(2) }}</span>
                </div>

                <div class="qr-code-section">
                  <div v-if="!showQR" class="qr-placeholder">
                    <h4>Ready to generate your QR code</h4>
                    <p>Click below and scan it with your mobile banking app.</p>

                    <button
                      type="button"
                      class="qr-generate-btn"
                      :disabled="!isFormValid"
                      @click="generateQRCode"
                    >
                      Generate QR Code
                    </button>
                  </div>

                  <div v-else class="qr-active">
                    <img
                      v-if="qrCodeUrl"
                      :src="qrCodeUrl"
                      alt="Payment QR Code"
                      class="qr-code-image"
                    />

                    <p class="payment-reference">Reference: {{ paymentId }}</p>

                    <div class="timer-pill">Code expires in 14:59</div>

                    <div class="steps-grid" style="margin-bottom: 20px;">
                      <div class="step-item">
                        <span class="step-number">1</span>
                        <p>Open your banking app</p>
                      </div>
                      <div class="step-item">
                        <span class="step-number">2</span>
                        <p>Scan the QR code</p>
                      </div>
                      <div class="step-item">
                        <span class="step-number">3</span>
                        <p>Confirm payment of ${{ totalAmount.toFixed(2) }}</p>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn-primary-pay"
                      @click="handlePayment"
                    >
                      I have paid
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="pci-footer">
            <span class="pci-check">PCI Compliant</span>
          </div>
        </main>
      </div>
    </div>

    <footer class="site-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <Logo class="booking-footer-logo" theme="light" size="md" :showTagline="false" />
          <p>Secure, fast, and transparent bookings across Cambodia.</p>
          <div class="footer-badges">
            <span class="badge-pill">Secure Checkout</span>
            <span class="badge-pill">PCI Compliant</span>
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

    <div
      v-if="showSuccessModal"
      class="success-modal-overlay"
      @click="closeSuccessModal"
    >
      <div class="success-modal" @click.stop>
        <h2>Payment Successful</h2>
        <p>Your booking has been confirmed.</p>

        <div class="transaction-receipt">
          <div class="receipt-row">
            <span class="label">Booking ID</span>
            <span class="value">{{ bookingId }}</span>
          </div>
          <div class="receipt-row">
            <span class="label">Transaction ID</span>
            <span class="value">{{ transactionId }}</span>
          </div>
          <div class="receipt-row">
            <span class="label">Payment Method</span>
            <span class="value">{{ receiptPaymentLabel }}</span>
          </div>
          <div class="receipt-row">
            <span class="label">Amount Paid</span>
            <span class="value amount">${{ totalAmount.toFixed(2) }}</span>
          </div>
          <div class="receipt-row">
            <span class="label">Date</span>
            <span class="value">{{ transactionDateTime }}</span>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-download" type="button" @click="downloadReceipt">
            Download receipt
          </button>
          <button class="btn-close" type="button" @click="closeSuccessModal">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { userService } from "../../services/database.js";
import Logo from "@/components/Logo.vue";
import "../../assets/user/booking.css";

// Navigation
const router = useRouter();
const route = useRoute();

// Header data from VehiclesByShop.vue
const navItems = ['Home', 'Viewdetails', 'Bookings'];
const activeNav = ref('Home');
const actionMessage = ref('');
const avatarLoadFailed = ref(false);
const LAST_VEHICLE_ID_KEY = 'last_vehicle_id';

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

const userInitials = computed(() => {
  const words = String(userDisplayName.value).trim().split(/\s+/).filter(Boolean);
  if (words.length === 0) return 'CU';
  if (words.length === 1) return words[0].slice(0, 2).toUpperCase();
  return `${words[0][0] || ''}${words[1][0] || ''}`.toUpperCase();
});

// Header functions
const setActiveNav = (item) => {
  activeNav.value = item;
  if (item === 'Home') {
    router.replace('/view_shop');
    return;
  }
  actionMessage.value = `${item} is not available yet.`;
};

const openProfile = () => {
  router.push('/user/profile');
};

const handleLogout = async () => {
  await userService.logout();
  router.push('/login');
};

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, "");
const fallbackImageByType = {
  motorbike: "https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg",
  bicycle: "https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg",
  car: "https://i.pinimg.com/1200x/d9/9e/06/d99e06bd9dd77fb2581170af2063b3b5.jpg",
};

const vehicle = ref(null);
const shopsById = ref({});
const isLoading = ref(false);
const loadingError = ref("");
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
const getVehicleImage = (item) => {
  const image = item?.image_url ? String(item.image_url).trim() : "";
  if (image) {
    if (image.startsWith("http://") || image.startsWith("https://")) return image;
    if (image.startsWith("/")) return `${API_ROOT}${image}`;
    return `${API_ROOT}/storage/${image.replace(/^storage\//, "")}`;
  }
  const normalizedType = String(item?.type || "").toLowerCase();
  return fallbackImageByType[normalizedType] || fallbackImageByType.motorbike;
};

const stripMapLinks = (value) => {
  if (!value) return "";
  return String(value)
    .replace(/https?:\/\/maps\.app\.goo\.gl\/\S+/gi, "")
    .replace(/https?:\/\/(www\.)?google\.com\/maps\/\S+/gi, "")
    .replace(/\s{2,}/g, " ")
    .trim();
};

const getShopLocation = (shop) => {
  const raw = shop?.address || shop?.name || "Unknown Shop";
  const cleaned = stripMapLinks(raw);
  if (cleaned) return cleaned;
  return shop?.name || "Unknown Shop";
};

const vehicleName = computed(() => getVehicleName(vehicle.value) || "Vehicle");
const vehicleType = computed(() => vehicle.value?.type || "");
const vehicleTag = computed(() => (vehicleType.value ? String(vehicleType.value).toUpperCase() : "RENTAL"));
const vehicleImage = computed(() => getVehicleImage(vehicle.value));
const vehicleLocation = computed(() => {
  const shop = vehicle.value?.shop_id ? shopsById.value[vehicle.value.shop_id] : null;
  return getShopLocation(shop);
});

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

    shopsById.value = shopList.reduce((acc, shop) => {
      acc[shop.id] = shop;
      return acc;
    }, {});

    const found = vehicleList.find((item) => Number(item.id) === vehicleId.value);
    if (!found) {
      throw new Error("Vehicle not found.");
    }

    vehicle.value = found;
    rental.value.title = getVehicleName(found) || rental.value.title;
    rental.value.location = vehicleLocation.value;
    rental.value.dailyRate = Number(found.price_per_day || 0);
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

const method = ref("card");
const promoCode = ref("");
const showQR = ref(false);
const showSuccessModal = ref(false);
const dateError = ref("");
const bookingId = ref("");
const paymentId = ref(
  "PAY" + Math.random().toString(36).slice(2, 11).toUpperCase()
);
const transactionId = ref(
  "TXN" + Math.random().toString(36).slice(2, 14).toUpperCase()
);
const qrCodeUrl = ref("");

const rental = ref({
  title: "Vehicle",
  location: "Unknown Shop",
  startDate: "",
  endDate: "",
  period: "",
  riders: "1 Rider",
  dailyRate: 0,
  subtotal: 0,
  insurance: 75,
  taxes: 0,
});

const parseNumberFromQuery = (value) => {
  if (value === null || value === undefined) return null;
  const raw = Array.isArray(value) ? value[0] : value;
  if (raw === "") return null;
  const num = Number(raw);
  return Number.isFinite(num) ? num : null;
};

const parseRiderDetailsFromQuery = (value) => {
  if (value === null || value === undefined) return null;
  const raw = Array.isArray(value) ? value[0] : value;
  const text = String(raw).trim();
  if (!text) return null;
  if (/^\d+$/.test(text)) {
    const count = Number(text);
    if (!Number.isFinite(count) || count <= 0) return null;
    return `${count} ${count === 1 ? "Rider" : "Riders"}`;
  }
  return text;
};

const applyRouteDefaults = () => {
  const insuranceFromQuery = parseNumberFromQuery(route.query.insuranceFee);
  if (insuranceFromQuery !== null) {
    rental.value.insurance = insuranceFromQuery;
  }

  const riderFromQuery = parseRiderDetailsFromQuery(route.query.riderDetails);
  if (riderFromQuery) {
    rental.value.riders = riderFromQuery;
  }
};

const methodTitle = computed(() => {
  if (method.value === "qr") return "QR Code Payment";
  if (method.value === "bank") return "Bank Transfer";
  return "Secure Checkout";
});

const methodDescription = computed(() => {
  if (method.value === "qr") {
    return "Pay directly using your banking app.";
  }
  if (method.value === "bank") {
    return "Complete payment using a direct bank transfer.";
  }
  return "Complete your rental booking by choosing a payment method below.";
});

const receiptPaymentLabel = computed(() => {
  if (method.value === "qr") return "QR Payment";
  if (method.value === "bank") return "Bank Transfer";
  return "Credit Card";
});

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split("T")[0];
});

const calculateDays = () => {
  if (!rental.value.startDate || !rental.value.endDate) return 0;

  const start = new Date(rental.value.startDate);
  const end = new Date(rental.value.endDate);
  const diff = end.getTime() - start.getTime();
  const days = Math.ceil(diff / (1000 * 60 * 60 * 24));

  return days > 0 ? days : 0;
};

const riderCount = computed(() => {
  const match = String(rental.value.riders).match(/^\s*(\d+)/);
  if (!match) return 1;
  const count = Number(match[1]);
  return Number.isFinite(count) && count > 0 ? count : 1;
});

const totalAmount = computed(() => {
  const days = calculateDays();
  const riders = riderCount.value;
  const subtotal = days * rental.value.dailyRate * riders;
  rental.value.subtotal = subtotal;
  rental.value.taxes = subtotal * 0.1;
  return rental.value.subtotal + rental.value.insurance + rental.value.taxes;
});

const isFormValid = computed(() => {
  return (
    !!rental.value.startDate &&
    !!rental.value.endDate &&
    calculateDays() > 0 &&
    !dateError.value
  );
});

const transactionDateTime = computed(() => {
  return new Date().toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
});

const formatPeriod = () => {
  const start = new Date(rental.value.startDate);
  const end = new Date(rental.value.endDate);
  const opts = { month: "short", day: "numeric", year: "numeric" };
  const days = calculateDays();
  rental.value.period = `${start.toLocaleDateString("en-US", opts)} - ${end.toLocaleDateString("en-US", opts)} (${days} ${days === 1 ? "day" : "days"})`;
};

const isDateAvailable = async (startDate, endDate) => {
  const existingBookings = [
    { startDate: "2026-04-10", endDate: "2026-04-12" },
    { startDate: "2026-04-20", endDate: "2026-04-23" },
  ];

  const requestedStart = new Date(startDate);
  const requestedEnd = new Date(endDate);

  return existingBookings.every((booking) => {
    const existingStart = new Date(booking.startDate);
    const existingEnd = new Date(booking.endDate);
    return requestedEnd < existingStart || requestedStart > existingEnd;
  });
};

const validateDates = async () => {
  dateError.value = "";

  if (!rental.value.startDate || !rental.value.endDate) return;

  const start = new Date(rental.value.startDate);
  const end = new Date(rental.value.endDate);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (start < today) {
    dateError.value = "Pick-up date cannot be in the past.";
    return;
  }

  if (end <= start) {
    dateError.value = "Drop-off date must be after pick-up date.";
    return;
  }

  const available = await isDateAvailable(rental.value.startDate, rental.value.endDate);
  if (!available) {
    dateError.value = "Selected dates are not available. Please choose different dates.";
    return;
  }

  formatPeriod();
};

const saveBookingToDatabase = async (bookingData) => {
  const currentUser = userService.getCurrentUser();
  if (!currentUser?.id) {
    return { success: false, message: "Please login first." };
  }

  if (!vehicleId.value) {
    return { success: false, message: "Vehicle not found." };
  }

  const payload = {
    user_id: currentUser.id,
    vehicle_id: vehicleId.value,
    coupon_id: null,
    start_date: rental.value.startDate,
    total_days: calculateDays(),
    total_price: totalAmount.value,
    status: bookingData?.status || "pending",
    deposit_amount: 0,
    deposit_status: "unpaid",
    rider_details: rental.value.riders,
    daily_rate: rental.value.dailyRate,
    insurance_fee: rental.value.insurance,
    taxes_fee: rental.value.taxes,
  };

  try {
    const response = await api.post("/bookings", payload);
    const record = response?.data?.data || response?.data;
    const recordId = record?.id;
    const nextBookingId = recordId ? `BK${recordId}` : bookingData?.bookingId;

    return {
      success: true,
      bookingId: nextBookingId || "",
    };
  } catch (error) {
    console.error("Booking create error:", error);
    const message =
      error?.response?.data?.message ||
      error?.message ||
      "Failed to create booking.";
    return { success: false, message };
  }
};

const buildBookingData = (paymentMethod, status) => ({
  bookingId: bookingId.value,
  transactionId: transactionId.value,
  paymentId: paymentId.value,
  rental: {
    title: rental.value.title,
    location: rental.value.location,
    startDate: rental.value.startDate,
    endDate: rental.value.endDate,
    dailyRate: rental.value.dailyRate,
    days: calculateDays(),
    subtotal: rental.value.subtotal,
    insurance: rental.value.insurance,
    taxes: rental.value.taxes,
    totalAmount: totalAmount.value,
  },
  paymentMethod,
  status,
  createdAt: new Date().toISOString(),
});

const handlePayment = async () => {
  await validateDates();
  if (dateError.value) return;

  await new Promise((resolve) => setTimeout(resolve, 1200));
  const result = await saveBookingToDatabase(buildBookingData(method.value, "confirmed"));

  if (result.success) {
    bookingId.value = result.bookingId;
    showSuccessModal.value = true;
  } else {
    alert(result.message || "Payment failed. Please try again.");
  }
};

const handleBankTransfer = async () => {
  await validateDates();
  if (dateError.value) return;

  const result = await saveBookingToDatabase(
    buildBookingData("bank_transfer", "pending_payment")
  );

  if (!result.success) {
    alert(result.message || "Failed to create bank transfer instructions.");
    return;
  }

  bookingId.value = result.bookingId;

  const instructions = `
BANK TRANSFER INSTRUCTIONS
============================
Payment ID: ${paymentId.value}
Amount: $${totalAmount.value.toFixed(2)}
Booking ID: ${bookingId.value}

BANK DETAILS:
- Account Name: Moto Rental Pty Ltd
- Bank: National Australia Bank
- BSB: 082-123
- Account: 1234 5678 9012
- Reference: ${paymentId.value}
  `.trim();

  const blob = new Blob([instructions], { type: "text/plain" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `bank_transfer_${paymentId.value}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);

  showSuccessModal.value = true;
};

const generateABAQRData = () => {
  const qrData = {
    merchant: "MOTORAL-001",
    name: "Moto Rental",
    amount: totalAmount.value.toFixed(2),
    currency: "USD",
    ref: paymentId.value,
    desc: `Payment for ${rental.value.title}`,
    time: new Date().toISOString(),
  };

  return `aba://payment?merchant=${qrData.merchant}&name=${encodeURIComponent(
    qrData.name
  )}&amount=${qrData.amount}&currency=${qrData.currency}&ref=${qrData.ref}&desc=${encodeURIComponent(
    qrData.desc
  )}&time=${qrData.time}`;
};

const generateQRCode = async () => {
  await validateDates();
  if (dateError.value) return;

  const qrPayload = generateABAQRData();
  qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(
    qrPayload
  )}`;

  showQR.value = true;
};

const closeSuccessModal = () => {
  showSuccessModal.value = false;
};

const downloadReceipt = () => {
  const receipt = `
BOOKING RECEIPT
================
Booking ID: ${bookingId.value}
Transaction ID: ${transactionId.value}
Payment Method: ${receiptPaymentLabel.value}
Amount Paid: $${totalAmount.value.toFixed(2)}
Date: ${transactionDateTime.value}

Booking: ${rental.value.title}
Location: ${rental.value.location}
Period: ${rental.value.period}
  `.trim();

  const blob = new Blob([receipt], { type: "text/plain" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `receipt_${transactionId.value}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
};

const initDates = () => {
  const start = new Date();
  const end = new Date(start);
  end.setDate(end.getDate() + 1);

  rental.value.startDate = start.toISOString().split("T")[0];
  rental.value.endDate = end.toISOString().split("T")[0];
  formatPeriod();
};

onMounted(() => {
  loadVehicleDetail();
  if (vehicleId.value) {
    setLastVehicleId(vehicleId.value);
  }
});

watch(
  () => route.query.insuranceFee,
  () => {
    applyRouteDefaults();
  },
  { immediate: true }
);
initDates();
</script>
