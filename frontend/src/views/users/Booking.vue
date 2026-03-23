<template>
  <div class="booking-checkout-page">
    <UserNavbar
      :nav-items="navItems"
      :active-label="activeNavLabel"
      :show-fallback-message="true"
      @logout-request="handleLogout"
    />

    <div class="page-container">

      <div class="page-heading-row">
        <div class="title-block">
          <h1>Checkout</h1>
          <span class="step-pill">Step 2 of 3</span>
        </div>
        <div class="top-actions">
          <button class="btn-back-top" type="button" @click="goHome">
            <span class="back-arrow" aria-hidden="true">←</span>
            Back to Home
          </button>
        </div>
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
                <span>${{ insuranceAmount.toFixed(2) }}</span>
              </div>
              <div class="row">
                <span>Taxes & Fees</span>
                <span>${{ taxesAmount.toFixed(2) }}</span>
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
              @submit.prevent="openConfirmModal('card')"
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
                  placeholder="0000-0000-0000-0000"
                  maxlength="19"
                  pattern="\d{4}-\d{4}-\d{4}-\d{4}"
                  @input="formatCardNumberInput"
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
                @click="openConfirmModal('bank')"
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
                  <div class="qr-active qr-active--asset">
                    <img
                      :src="paymentQrCodeImage"
                      alt="Payment QR Code"
                      class="qr-code-image"
                    />

                    <p class="payment-reference">Reference: {{ paymentId }}</p>

                    <div class="steps-grid" style="margin-bottom: 20px;">
                      <div class="step-item">
                        <span class="step-number">1</span>
                        <p>Open your banking app</p>
                      </div>
                      <div class="step-item">
                        <span class="step-number">2</span>
                        <p>Scan this QR code</p>
                      </div>
                      <div class="step-item">
                        <span class="step-number">3</span>
                        <p>Confirm payment of ${{ totalAmount.toFixed(2) }}</p>
                      </div>
                    </div>

                    <button
                      type="button"
                      class="btn-primary-pay"
                      :disabled="!isFormValid"
                      @click="openConfirmModal('qr')"
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

    <footer v-if="false" class="site-footer">
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

    <div
      v-if="showConfirmModal"
      class="confirm-modal-overlay"
      @click="closeConfirmModal"
    >
      <div class="confirm-modal" @click.stop>
        <div class="confirm-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 6L9 17l-5-5" />
          </svg>
        </div>
        <h2>Confirm Payment</h2>
        <p>Please review the details before confirming your payment.</p>

        <div class="confirm-details">
          <div class="confirm-detail">
            <span>Amount</span>
            <strong>${{ totalAmount.toFixed(2) }}</strong>
          </div>
          <div class="confirm-detail">
            <span>Method</span>
            <strong>{{ receiptPaymentLabel }}</strong>
          </div>
        </div>

        <div class="confirm-actions">
          <button class="btn-cancel" type="button" @click="closeConfirmModal">
            Cancel
          </button>
          <button class="btn-confirm" type="button" @click="confirmPayment">
            Confirm
          </button>
        </div>
      </div>
    </div>

    <div
      v-if="showSuccessModal"
      class="success-modal-overlay"
      @click="closeSuccessModal"
    >
      <div class="success-modal booking-status-modal payment-status-modal" @click.stop>
        <div class="booking-status-hero">
          <div v-if="paymentFlowState === 'processing'" class="booking-loader-wrap" aria-hidden="true">
            <div class="booking-loader-circle"></div>
            <div class="booking-loader-core"></div>
          </div>

          <div v-else class="booking-check-wrap" aria-hidden="true">
            <svg class="booking-check-svg" viewBox="0 0 120 120" fill="none">
              <circle class="booking-check-ring" cx="60" cy="60" r="46" />
              <path class="booking-check-path" d="M38 62l14 14 30-32" />
            </svg>
          </div>
        </div>

        <span class="booking-status-kicker">
          {{ paymentStatusKicker }}
        </span>
        <h3>{{ paymentStatusTitle }}</h3>
        <p class="booking-status-text payment-status-text-compact">
          {{ paymentStatusDescription }}
        </p>

        <div v-if="paymentFlowState === 'success'" class="payment-summary-shell">
          <div class="payment-summary-top">
            <div class="payment-summary-copy">
              <span>Vehicle</span>
              <strong>{{ rental.title }}</strong>
            </div>
            <div class="payment-summary-total">
              <span>Total</span>
              <strong>${{ totalAmount.toFixed(2) }}</strong>
            </div>
          </div>

          <div class="payment-summary-grid">
            <div class="payment-summary-item">
              <span>Booking ID</span>
              <strong>{{ bookingId || "Pending" }}</strong>
            </div>
            <div class="payment-summary-item">
              <span>Method</span>
              <strong>{{ completedPaymentLabel }}</strong>
            </div>
            <div class="payment-summary-item payment-summary-item-wide">
              <span>{{ paymentReferenceLabel }}</span>
              <strong>{{ paymentReferenceValue }}</strong>
            </div>
          </div>
        </div>

        <p v-if="paymentFlowState === 'success'" class="success-inline-note payment-inline-note">
          Pickup at {{ rental.location }} on {{ transactionDateTime }}
        </p>

        <div v-if="paymentFlowState === 'success'" class="success-actions">
          <button class="success-secondary-btn" type="button" @click="closeSuccessModal">
            Go to Dashboard
          </button>
          <button class="success-primary-btn" type="button" @click="downloadReceipt">
            Download Receipt
          </button>
        </div>
      </div>
    </div>
    <CommonFooter />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import paymentQrCodeImage from "@/assets/img/image.png";
import { userService } from "../../services/database.js";
import CommonFooter from '../../components/CommonFooter.vue';
import UserNavbar from '@/components/UserNavbar.vue';
import {
  calculateOverlappingBookedQuantity,
  getVehicleTotalQuantity,
  parseQuantityValue,
} from "@/utils/bookingAvailability";
import "../../assets/user/booking.css";

// Navigation
const router = useRouter();
const route = useRoute();

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Booking', route: '/my-bookings' },
  { label: 'Promotions', route: '/promotions' }
];
const activeNavLabel = computed(() => {
  const matched = navItems.find((item) => item.route && route.path.startsWith(item.route));
  return matched?.label || 'Home';
});

// Header functions
const handleLogout = async () => {
  await userService.logout();
  router.push('/login');
};

const goHome = () => {
  router.push('/');
};

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, "");
const fallbackImageByType = {
  motorbike: "https://i.pinimg.com/1200x/61/68/42/61684256edbd26664520bdfcf379c762.jpg",
  bicycle: "https://i.pinimg.com/1200x/2c/90/78/2c9078d8032d2e4ae3e737684317f814.jpg",
  car: "https://i.pinimg.com/1200x/d9/9e/06/d99e06bd9dd77fb2581170af2063b3b5.jpg",
};

const vehicle = ref(null);
const bookings = ref([]);
const shopsById = ref({});
const isLoading = ref(false);
const loadingError = ref("");
const parsePositiveIntQuery = (value) => {
  if (value === null || value === undefined) return null;
  const raw = Array.isArray(value) ? value[0] : value;
  const num = Number(raw);
  return Number.isInteger(num) && num > 0 ? num : null;
};

const vehicleId = computed(() => {
  const value = Number(route.params.id);
  return Number.isFinite(value) && value > 0 ? value : null;
});
const existingBookingRecordId = computed(() => parsePositiveIntQuery(route.query.bookingRecordId));

const getVehicleName = (item) => (item ? `${item.brand} ${item.model}` : "");
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
    const [vehicleList, shopList, bookingList] = await Promise.all([
      loadAllPages("vehicles"),
      loadAllPages("shops"),
      loadAllPages("bookings"),
    ]);

    shopsById.value = shopList.reduce((acc, shop) => {
      acc[shop.id] = shop;
      return acc;
    }, {});
    bookings.value = bookingList;

    const found = vehicleList.find((item) => Number(item.id) === vehicleId.value);
    if (!found) {
      throw new Error("Vehicle not found.");
    }

    vehicle.value = found;
    rental.value.title = getVehicleName(found) || rental.value.title;
    rental.value.location = vehicleLocation.value;
    rental.value.dailyRate = Number(found.price_per_day || 0);
    if (found.insurance_fee !== undefined && found.insurance_fee !== null) {
      rental.value.insurance = Number(found.insurance_fee || 0);
    }
    if (found.taxes_fee !== undefined && found.taxes_fee !== null) {
      rental.value.taxes = Number(found.taxes_fee || 0);
    }
    if (found.rider_details) {
      rental.value.riders = found.rider_details;
    }
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
const showSuccessModal = ref(false);
const showConfirmModal = ref(false);
const pendingPaymentAction = ref(null);
const paymentFlowState = ref("idle");
const paymentFlowMethod = ref("card");
const dateError = ref("");
const bookingId = ref("");
const paymentId = ref(
  "PAY" + Math.random().toString(36).slice(2, 11).toUpperCase()
);
const transactionId = ref(
  "TXN" + Math.random().toString(36).slice(2, 14).toUpperCase()
);

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

const includeInsurance = ref(true);
const includeTaxes = ref(true);

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

const parseDateFromQuery = (value) => {
  if (value === null || value === undefined) return null;
  const raw = Array.isArray(value) ? value[0] : value;
  const text = String(raw).trim();
  if (!/^\d{4}-\d{2}-\d{2}$/.test(text)) return null;
  return text;
};

const parseBooleanFlagFromQuery = (value) => {
  if (value === null || value === undefined) return null;
  const raw = Array.isArray(value) ? value[0] : value;
  const text = String(raw).trim().toLowerCase();
  if (!text) return null;
  if (text === "1" || text === "true" || text === "yes" || text === "on") return true;
  if (text === "0" || text === "false" || text === "no" || text === "off") return false;
  return null;
};

const formatCardNumberInput = (event) => {
  const input = event?.target;
  if (!input) return;

  const digits = String(input.value || "")
    .replace(/\D/g, "")
    .slice(0, 16);

  input.value = digits.replace(/(\d{4})(?=\d)/g, "$1-");
};

const applyRouteDefaults = () => {
  const insuranceFromQuery = parseNumberFromQuery(route.query.insuranceFee);
  if (insuranceFromQuery !== null) {
    rental.value.insurance = insuranceFromQuery;
  }

  const taxesFromQuery = parseNumberFromQuery(route.query.taxesFee);
  if (taxesFromQuery !== null) {
    rental.value.taxes = taxesFromQuery;
  }

  const includeInsuranceFromQuery = parseBooleanFlagFromQuery(route.query.includeInsurance);
  if (includeInsuranceFromQuery !== null) {
    includeInsurance.value = includeInsuranceFromQuery;
  }

  const includeTaxesFromQuery = parseBooleanFlagFromQuery(route.query.includeTaxes);
  if (includeTaxesFromQuery !== null) {
    includeTaxes.value = includeTaxesFromQuery;
  }

  const riderFromQuery = parseRiderDetailsFromQuery(route.query.riderDetails);
  if (riderFromQuery) {
    rental.value.riders = riderFromQuery;
  }

  const startDateFromQuery = parseDateFromQuery(route.query.startDate);
  if (startDateFromQuery) {
    rental.value.startDate = startDateFromQuery;
  }

  const totalDaysFromQuery = parsePositiveIntQuery(route.query.totalDays);
  if (rental.value.startDate && totalDaysFromQuery) {
    const end = new Date(rental.value.startDate);
    end.setDate(end.getDate() + totalDaysFromQuery);
    rental.value.endDate = end.toISOString().split("T")[0];
  }

  if (rental.value.startDate && rental.value.endDate) {
    formatPeriod();
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

const completedPaymentLabel = computed(() => {
  if (paymentFlowMethod.value === "qr") return "QR Payment";
  if (paymentFlowMethod.value === "bank_transfer") return "Bank Transfer";
  return "Credit Card";
});

const paymentStatusKicker = computed(() => {
  if (paymentFlowState.value === "processing") return "Processing Payment";
  if (paymentFlowMethod.value === "bank_transfer") return "Transfer Instructions Ready";
  return "Payment Confirmed";
});

const paymentStatusTitle = computed(() => {
  if (paymentFlowState.value === "processing") return "Please wait a moment";
  if (paymentFlowMethod.value === "bank_transfer") return "Bank Transfer Ready";
  return "Payment Successful";
});

const paymentStatusDescription = computed(() => {
  if (paymentFlowState.value === "processing") {
    return "We are confirming your payment now.";
  }

  if (paymentFlowMethod.value === "bank_transfer") {
    return "Your booking is ready and transfer instructions are prepared.";
  }

  return "Your payment is complete and your booking is ready.";
});

const paymentReferenceLabel = computed(() => {
  return paymentFlowMethod.value === "bank_transfer" ? "Transfer Ref" : "Reference";
});

const paymentReferenceValue = computed(() => {
  return paymentFlowMethod.value === "bank_transfer"
    ? paymentId.value
    : (transactionId.value || paymentId.value);
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
  return parseQuantityValue(rental.value.riders, 1);
});

const selectedDateAvailability = computed(() => {
  const totalVehicles = getVehicleTotalQuantity(vehicle.value);
  const bookedQuantity = calculateOverlappingBookedQuantity({
    bookings: bookings.value,
    vehicleId: vehicleId.value,
    startDate: rental.value.startDate,
    endDate: rental.value.endDate,
    ignoreBookingId: existingBookingRecordId.value,
  });
  const remainingQuantity = Math.max(totalVehicles - bookedQuantity, 0);
  const requestedQuantity = 1;

  return {
    totalVehicles,
    bookedQuantity,
    remainingQuantity,
    requestedQuantity,
    fitsRequest: remainingQuantity >= 1,
  };
});

const insuranceAmount = computed(() => {
  const base = Number(rental.value.insurance || 0);
  return includeInsurance.value ? base : 0;
});

const taxesAmount = computed(() => {
  const base = Number(rental.value.taxes || 0);
  return includeTaxes.value ? base : 0;
});

const totalAmount = computed(() => {
  const days = calculateDays();
  const riders = riderCount.value;
  const subtotal = days * rental.value.dailyRate * riders;
  rental.value.subtotal = subtotal;
  return rental.value.subtotal + insuranceAmount.value + taxesAmount.value;
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

  const availability = selectedDateAvailability.value;
  if (!availability.fitsRequest) {
    if (availability.remainingQuantity <= 0) {
      dateError.value = "Selected dates are fully booked for this vehicle.";
      return;
    }

    dateError.value = "";
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
    shop_id: vehicle.value?.shop_id ?? null,
    coupon_id: null,
    start_date: rental.value.startDate,
    total_days: calculateDays(),
    total_price: totalAmount.value,
    status: "pending",
    deposit_amount: 0,
    deposit_status: "unpaid",
    rider_details: rental.value.riders,
    daily_rate: rental.value.dailyRate,
    insurance_fee: insuranceAmount.value,
    taxes_fee: taxesAmount.value,
  };

  try {
    let recordId = existingBookingRecordId.value;

    if (recordId) {
      await api.put(`/bookings/${recordId}`, payload);
    } else {
      const response = await api.post("/bookings", payload);
      const record = response?.data?.data || response?.data;
      recordId = record?.id;
    }

    const nextBookingId = recordId ? `BK${recordId}` : bookingData?.bookingId;

    // Now save the payment record
    const paymentPayload = {
      booking_id: recordId,
      transaction_id: transactionId.value || paymentId.value,
      amount: totalAmount.value,
      payment_method: bookingData?.paymentMethod || method.value,
      payment_status: bookingData?.status === "confirmed" ? "paid" : "pending",
      paid_at: bookingData?.status === "confirmed" ? new Date().toISOString() : null,
    };

    // Save payment to payments table
    await api.post("/payments", paymentPayload);

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

const loadExistingBooking = async () => {
  if (!existingBookingRecordId.value) return;

  try {
    const response = await api.get(`/bookings/${existingBookingRecordId.value}`);
    const booking = response?.data?.data || response?.data;
    if (!booking) return;

    bookingId.value = `BK${booking.id}`;

    if (booking.start_date) {
      rental.value.startDate = booking.start_date;
    }

    if (booking.start_date && Number(booking.total_days || 0) > 0) {
      const end = new Date(booking.start_date);
      end.setDate(end.getDate() + Number(booking.total_days));
      rental.value.endDate = end.toISOString().split("T")[0];
    }

    if (booking.rider_details) {
      rental.value.riders = booking.rider_details;
    }

    if (booking.daily_rate !== undefined && booking.daily_rate !== null) {
      rental.value.dailyRate = Number(booking.daily_rate || 0);
    }

    rental.value.insurance = Number(booking.insurance_fee || 0);
    rental.value.taxes = Number(booking.taxes_fee || 0);
    includeInsurance.value = Number(booking.insurance_fee || 0) > 0;
    includeTaxes.value = Number(booking.taxes_fee || 0) > 0;

    if (rental.value.startDate && rental.value.endDate) {
      formatPeriod();
    }
  } catch (error) {
    console.error("Existing booking load error:", error);
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
    insurance: insuranceAmount.value,
    taxes: taxesAmount.value,
    totalAmount: totalAmount.value,
  },
  paymentMethod,
  status,
  createdAt: new Date().toISOString(),
});

let paymentFlowTimerId = null;

const clearPaymentFlowTimer = () => {
  if (paymentFlowTimerId) {
    window.clearTimeout(paymentFlowTimerId);
    paymentFlowTimerId = null;
  }
};

const waitForPaymentFlow = (duration = 900) =>
  new Promise((resolve) => {
    clearPaymentFlowTimer();
    paymentFlowTimerId = window.setTimeout(() => {
      paymentFlowTimerId = null;
      resolve();
    }, duration);
  });

const startPaymentFlow = (selectedMethod) => {
  paymentFlowMethod.value = selectedMethod;
  paymentFlowState.value = "processing";
  showSuccessModal.value = true;
};

const finishPaymentFlow = async (selectedMethod) => {
  paymentFlowMethod.value = selectedMethod;
  await waitForPaymentFlow();
  paymentFlowState.value = "success";
};

const resetPaymentFlow = () => {
  clearPaymentFlowTimer();
  paymentFlowState.value = "idle";
  paymentFlowMethod.value = "card";
  showSuccessModal.value = false;
};

const openConfirmModal = (action) => {
  pendingPaymentAction.value = action;
  showConfirmModal.value = true;
};

const closeConfirmModal = () => {
  showConfirmModal.value = false;
  pendingPaymentAction.value = null;
};

const confirmPayment = async () => {
  const action = pendingPaymentAction.value;
  closeConfirmModal();

  if (action === "bank") {
    await handleBankTransfer();
    return;
  }

  await handlePayment();
};

const handlePayment = async () => {
  await validateDates();
  if (dateError.value) return;

  startPaymentFlow(method.value);
  const result = await saveBookingToDatabase(buildBookingData(method.value, "confirmed"));

  if (result.success) {
    bookingId.value = result.bookingId;
    await finishPaymentFlow(method.value);
  } else {
    resetPaymentFlow();
    alert(result.message || "Payment failed. Please try again.");
  }
};

const handleBankTransfer = async () => {
  await validateDates();
  if (dateError.value) return;

  startPaymentFlow("bank_transfer");
  const result = await saveBookingToDatabase(
    buildBookingData("bank_transfer", "pending_payment")
  );

  if (!result.success) {
    resetPaymentFlow();
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

  await finishPaymentFlow("bank_transfer");
};

const closeSuccessModal = () => {
  if (paymentFlowState.value === "processing") return;
  resetPaymentFlow();
  router.push({ name: "view_shop" });
};

const downloadReceipt = () => {
  const receipt = `
BOOKING RECEIPT
================
Booking ID: ${bookingId.value}
${paymentReferenceLabel.value}: ${paymentReferenceValue.value}
Payment Method: ${completedPaymentLabel.value}
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
  a.download = `receipt_${paymentReferenceValue.value}.txt`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  window.URL.revokeObjectURL(url);
};

onBeforeUnmount(() => {
  clearPaymentFlowTimer();
});

const initDates = () => {
  if (rental.value.startDate && rental.value.endDate) {
    formatPeriod();
    return;
  }

  const start = new Date();
  const end = new Date(start);
  end.setDate(end.getDate() + 1);

  rental.value.startDate = start.toISOString().split("T")[0];
  rental.value.endDate = end.toISOString().split("T")[0];
  formatPeriod();
};

onMounted(async () => {
  await loadVehicleDetail();
  await loadExistingBooking();
  initDates();
});

watch(
  () => [route.query.bookingRecordId, route.query.insuranceFee, route.query.taxesFee, route.query.includeInsurance, route.query.includeTaxes, route.query.riderDetails, route.query.startDate, route.query.totalDays],
  () => {
    applyRouteDefaults();
  },
  { immediate: true }
);
</script>
