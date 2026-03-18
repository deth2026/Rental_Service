<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { shopApi } from "@/services/api";
import userService from "@/services/userService";
import "../../css/Myshop.css";

const shop = ref(null);
const currentUser = ref(null);
const ownerName = ref("");
const ownerEmail = ref("");

// Computed property to check if shop exists
const hasShop = computed(() => !!shop.value);

const showCreateModal = ref(false);
const showSuccessPopup = ref(false);
const showSingleShopAlert = ref(false);
const loading = ref(false);
const error = ref("");
const shopImageFile = ref(null);
const shopImagePreview = ref("");
const changeImagePreview = ref("");
const shopImageLoadFailed = ref(false);
const shopImageInputRef = ref(null);
const isShopImageDragOver = ref(false);
const changeImageInputRef = ref(null);
const isUpdatingImage = ref(false);

const createForm = reactive({
  name: "",
  phone: "",
  description: "",
  address: "",
  location: "",
  latitude: "",
  longitude: "",
  status: "active",
  font: "Modern Sans-serif",
  primary_color: "#2563eb",
  accent_color: "#10b981",
  instagram: "",
  facebook: "",
  img_url: "",
});
const fieldErrors = reactive({
  name: "",
  status: "",
  address: "",
  phone: "",
  description: "",
});

const colorPalette = [
  "#2563eb",
  "#0ea5e9",
  "#10b981",
  "#f97316",
  "#f43f5e",
  "#a855f7",
  "#fbbf24",
  "#94a3b8",
];
const accentPalette = ["#111827", "#1f2937", "#4c1d95", "#0f172a", "#1d4ed8"];
const fontOptions = [
  "Modern Sans-serif",
  "Classic Serif",
  "Minimalist",
  "Bold Display",
];

const setPrimaryColor = (color) => {
  createForm.primary_color = color;
};

const setAccentColor = (color) => {
  createForm.accent_color = color;
};

const asArray = (payload) => payload?.data || payload || [];

const formatDateTime = (value) => {
  if (!value) return "N/A";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString();
};

const getStoredUser = () => {
  if (typeof window === "undefined") return null;
  try {
    const raw = window.localStorage.getItem("user");
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === "object" ? parsed : null;
  } catch {
    return null;
  }
};

const getCachedShop = (userId) => {
  if (!userId || typeof window === "undefined") return null;
  try {
    const raw = window.localStorage.getItem(`settings_shop_${userId}`);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === "object" ? parsed : null;
  } catch {
    return null;
  }
};

const setCachedShop = (userId, shopData) => {
  if (!userId || typeof window === "undefined") return;
  try {
    if (!shopData) {
      window.localStorage.removeItem(`settings_shop_${userId}`);
      return;
    }
    window.localStorage.setItem(`settings_shop_${userId}`, JSON.stringify(shopData));
  } catch {
    // Ignore cache write failures.
  }
};

const ensureCurrentUser = async () => {
  if (currentUser.value) return currentUser.value;
  try {
    currentUser.value = await userService.getAuthUser();
  } catch (e) {
    console.error("Unable to fetch authenticated user", e);
  }
  return currentUser.value;
};

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
const API_ROOT = API_BASE_URL.replace(/\/api\/?$/, "").replace(/\/$/, "");

const getShopImageUrl = (url) => {
  if (!url) return "";
  // If it's already a data URL or full URL, return as-is
  const value = String(url).trim();
  if (!value) return "";
  if (/^(data:image|blob:)/i.test(value)) return value;
  if (/^https?:\/\//i.test(value)) return value;
  // For relative paths, normalize common Laravel storage variants
  const cleanUrl = value.replace(/^\/+/, "");
  if (cleanUrl.startsWith("storage/app/public/")) {
    return `${API_ROOT}/storage/${cleanUrl.replace(/^storage\/app\/public\//, "")}`;
  }
  if (cleanUrl.startsWith("app/public/")) {
    return `${API_ROOT}/storage/${cleanUrl.replace(/^app\/public\//, "")}`;
  }
  if (cleanUrl.startsWith("storage/")) return `${API_ROOT}/${cleanUrl}`;
  if (cleanUrl.startsWith("public/")) {
    return `${API_ROOT}/storage/${cleanUrl.replace(/^public\//, "")}`;
  }
  return `${API_ROOT}/storage/${cleanUrl}`;
};

const loadMyShop = async () => {
  error.value = "";
  const storedUser = getStoredUser();
  ownerName.value = storedUser?.name || "N/A";
  ownerEmail.value = storedUser?.email || "N/A";

  const cachedShop = getCachedShop(storedUser?.id);
  shop.value = cachedShop || null;

  const user = await ensureCurrentUser();
  const ownerId = user?.id || storedUser?.id;

  if (!ownerId) {
    error.value = "Unable to determine your owner account. Please log in.";
    return;
  }

  try {
    const response = await shopApi.getAll();
    const shops = asArray(response.data);
    const myShops = shops.filter(
      (item) => Number(item.owner_id) === Number(ownerId),
    );

    if (!myShops.length) {
      shop.value = null;
      setCachedShop(ownerId, null);
      ownerName.value = storedUser?.name || "N/A";
      ownerEmail.value = storedUser?.email || "N/A";
      return;
    }

    myShops.sort((a, b) => {
      const aTime = a.created_at ? new Date(a.created_at).getTime() : 0;
      const bTime = b.created_at ? new Date(b.created_at).getTime() : 0;

      if (bTime !== aTime) return bTime - aTime;
      return Number(b.id || 0) - Number(a.id || 0);
    });

    shop.value = myShops[0];
    shopImageLoadFailed.value = false;
    setCachedShop(ownerId, shop.value);
    ownerName.value =
      shop.value?.owner_name ||
      shop.value?.owner?.name ||
      user?.name ||
      storedUser?.name ||
      "N/A";
    ownerEmail.value =
      shop.value?.owner?.email ||
      shop.value?.owner_email ||
      user?.email ||
      storedUser?.email ||
      "N/A";
  } catch (e) {
    console.error("Failed to load shop", e);
    shop.value = null;
    error.value = "Could not load your shop. Please try again later.";
  }
};

const resetForm = () => {
  createForm.name = "";
  createForm.phone = "";
  createForm.description = "";
  createForm.address = "";
  createForm.location = "";
  createForm.latitude = "";
  createForm.longitude = "";
  createForm.status = "active";
  createForm.font = fontOptions[0];
  createForm.primary_color = colorPalette[0];
  createForm.accent_color = accentPalette[0];
  createForm.instagram = "";
  createForm.facebook = "";
  createForm.img_url = "";
  fieldErrors.name = "";
  fieldErrors.status = "";
  fieldErrors.address = "";
  fieldErrors.phone = "";
  fieldErrors.description = "";
  shopImageFile.value = null;
  if (shopImagePreview.value) {
    URL.revokeObjectURL(shopImagePreview.value);
    shopImagePreview.value = "";
  }
  error.value = "";
};

const clearFieldError = (field) => {
  if (fieldErrors[field]) fieldErrors[field] = "";
};

const validateCreateForm = () => {
  fieldErrors.name = "";
  fieldErrors.status = "";
  fieldErrors.address = "";
  fieldErrors.phone = "";
  fieldErrors.description = "";

  const name = createForm.name.trim();
  const status = createForm.status.trim();
  const address = createForm.address.trim();
  const phone = createForm.phone.trim();
  const description = createForm.description.trim();

  if (!name) fieldErrors.name = "Shop name is required.";
  if (!status) fieldErrors.status = "Status is required.";
  if (!address) fieldErrors.address = "Address is required.";
  if (!phone) {
    fieldErrors.phone = "Phone number is required.";
  } else if (!/^[0-9+\-\s()]{8,20}$/.test(phone)) {
    fieldErrors.phone = "Please enter a valid phone number.";
  }
  if (!description) fieldErrors.description = "Description is required.";

  return (
    !fieldErrors.name &&
    !fieldErrors.status &&
    !fieldErrors.address &&
    !fieldErrors.phone &&
    !fieldErrors.description
  );
};

const applyShopImageFile = (file) => {
  if (!file) return;
  if (!file.type.startsWith("image/")) {
    error.value = "Please select a valid image file.";
    return;
  }
  if (shopImagePreview.value) {
    URL.revokeObjectURL(shopImagePreview.value);
  }
  shopImageFile.value = file;
  createForm.img_url = "";
  shopImagePreview.value = URL.createObjectURL(file);
  error.value = "";
};

const onShopImageChange = (event) => {
  const file = event.target.files?.[0] || null;
  applyShopImageFile(file);
};

const triggerShopImagePicker = () => {
  if (shopImageInputRef.value) {
    shopImageInputRef.value.value = "";
    shopImageInputRef.value.click();
  }
};

const onShopImageDrop = (event) => {
  isShopImageDragOver.value = false;
  const file = event.dataTransfer?.files?.[0] || null;
  applyShopImageFile(file);
};

const openCreateModal = () => {
  resetForm();
  // Pre-fill phone from user if available
  if (currentUser.value?.phone) {
    createForm.phone = currentUser.value.phone;
  }
  showCreateModal.value = true;
};

const handleCreateClick = () => {
  if (hasShop.value) {
    showSingleShopAlert.value = true;
    return;
  }
  openCreateModal();
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  error.value = "";
};

const triggerChangeImagePicker = () => {
  if (changeImageInputRef.value) {
    changeImageInputRef.value.value = "";
    changeImageInputRef.value.click();
  }
};

const setChangeImagePreview = (file) => {
  if (changeImagePreview.value) {
    URL.revokeObjectURL(changeImagePreview.value);
    changeImagePreview.value = "";
  }
  if (!file) return;
  changeImagePreview.value = URL.createObjectURL(file);
};

const updateShopImage = async (file) => {
  if (!shop.value?.id) return;
  if (!file || !file.type.startsWith("image/")) {
    error.value = "Please select a valid image file.";
    return;
  }

  isUpdatingImage.value = true;
  error.value = "";
  shopImageLoadFailed.value = false;

  try {
    const payload = new FormData();
    payload.append("img_url", file);

    const { data } = await shopApi.update(shop.value.id, payload);
    const updatedShop = data?.data || data || null;
    if (updatedShop && typeof updatedShop === "object") {
      shop.value = updatedShop;
    }
    await loadMyShop();
    if (changeImagePreview.value) {
      URL.revokeObjectURL(changeImagePreview.value);
      changeImagePreview.value = "";
    }
  } catch (e) {
    error.value = e?.response?.data?.message || "Failed to update shop image.";
    console.error("Update shop image error", e);
  } finally {
    isUpdatingImage.value = false;
  }
};

const onChangeShopImage = async (event) => {
  const file = event.target.files?.[0] || null;
  if (file) {
    setChangeImagePreview(file);
  }
  await updateShopImage(file);
};

const onShopImageError = () => {
  shopImageLoadFailed.value = true;
};

const shopImageSrc = computed(() => {
  if (changeImagePreview.value) return changeImagePreview.value;
  if (shopImageLoadFailed.value) return "";
  // First check for img_url_full (provided by backend accessor)
  if (shop.value?.img_url_full) {
    return shop.value.img_url_full
  }
  const raw =
    shop.value?.img_url ||
    shop.value?.image_url ||
    shop.value?.image ||
    shop.value?.cover ||
    "";
  return getShopImageUrl(raw);
});

const removeShopImage = async () => {
  if (!shop.value?.id) return;

  isUpdatingImage.value = true;
  error.value = "";
  if (changeImagePreview.value) {
    URL.revokeObjectURL(changeImagePreview.value);
    changeImagePreview.value = "";
  }

  try {
    const payload = new FormData();
    payload.append("remove_img", "1");
    const { data } = await shopApi.update(shop.value.id, payload);
    const updatedShop = data?.data || data || null;
    if (updatedShop && typeof updatedShop === "object") {
      shop.value = updatedShop;
    }
    await loadMyShop();
  } catch (e) {
    error.value = e?.response?.data?.message || "Failed to delete shop image.";
    console.error("Delete shop image error", e);
  } finally {
    isUpdatingImage.value = false;
  }
};

const createShop = async () => {
  if (!validateCreateForm()) {
    error.value = "Please fill all required fields correctly.";
    return;
  }

  loading.value = true;
  error.value = "";

  try {
    const user = await ensureCurrentUser();
    const ownerId = user?.id;
    if (!ownerId) {
      error.value = "Unable to determine owner account. Please log in again.";
      return;
    }

    let payload;
    // if an image file has been selected, use FormData to send multipart request
    if (shopImageFile.value) {
      payload = new FormData();
      payload.append("owner_id", ownerId);
      payload.append("name", createForm.name.trim());
      payload.append("description", createForm.description.trim());
      payload.append("address", createForm.address.trim());
      if (createForm.location)
        payload.append("location", createForm.location.trim());
      if (createForm.latitude) payload.append("latitude", createForm.latitude);
      if (createForm.longitude)
        payload.append("longitude", createForm.longitude);
      payload.append("phone", createForm.phone.trim());
      payload.append("status", createForm.status);
      // the backend expects the field name img_url
      payload.append("img_url", shopImageFile.value);
    } else {
      payload = {
        owner_id: ownerId,
        name: createForm.name.trim(),
        description: createForm.description.trim(),
        address: createForm.address.trim(),
        location: createForm.location.trim() || null,
        latitude: createForm.latitude || null,
        longitude: createForm.longitude || null,
        phone: createForm.phone.trim(),
        status: createForm.status,
        img_url: createForm.img_url || null,
      };
    }

    const { data: created } = await shopApi.create(payload);
    const createdShop = created?.data || created || null;
    if (createdShop && typeof createdShop === "object") {
      shop.value = createdShop;
    }
    await loadMyShop();

    showCreateModal.value = false;
    showSuccessPopup.value = true;
    resetForm();

    setTimeout(() => {
      showSuccessPopup.value = false;
    }, 1400);
  } catch (e) {
    error.value = e?.response?.data?.message || "Create shop failed.";
    console.error("Create shop error", e);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  await loadMyShop();
});

onBeforeUnmount(() => {
  if (shopImagePreview.value) {
    URL.revokeObjectURL(shopImagePreview.value);
  }
  if (changeImagePreview.value) {
    URL.revokeObjectURL(changeImagePreview.value);
  }
});
</script>

<template>
  <div class="myshop-page">
    <div class="page-header">
      <h1>My Shop</h1>
      <button class="create-btn" @click="handleCreateClick">Create Shop</button>
    </div>

    <div v-if="!shop" class="empty-state">
      <h3>No shop information yet</h3>
      <p>Click Create Shop and fill in your shop information.</p>
    </div>

    <div v-else class="shop-settings-card">
      <div class="shop-settings-header">
        <div class="settings-title">
          <span class="settings-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
            >
              <circle cx="12" cy="12" r="3" />
              <path
                d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 0 1-4 0v-.2a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 0 1 0-4h.2a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4a2 2 0 0 1 4 0v.2a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 0 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6H20a2 2 0 0 1 0 4h-.2a1 1 0 0 0-.9.6z"
              />
            </svg>
          </span>
          <div>
            <h2>Ownership setting</h2>
            <p>Manage your shop profile details and contact info.</p>
          </div>
        </div>
        <span
          class="shop-status-pill"
          :class="(shop.status || 'inactive').toLowerCase()"
        >
          {{ shop.status || "inactive" }}
        </span>
      </div>

      <div class="shop-settings-profile">
        <div class="shop-avatar-stack">
          <img
            v-if="shopImageSrc"
            :src="shopImageSrc"
            alt="Shop Image"
            class="shop-cover-image"
            @error="onShopImageError"
          />
          <div v-else class="shop-cover-placeholder">
            <svg
              viewBox="0 0 24 24"
              width="32"
              height="32"
              fill="none"
              stroke="#94a3b8"
              stroke-width="1.5"
            >
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <circle cx="8.5" cy="8.5" r="1.5"></circle>
              <polyline points="21 15 16 10 5 21"></polyline>
            </svg>
          </div>
        </div>
        <input
          ref="changeImageInputRef"
          type="file"
          accept="image/png,image/jpeg,image/webp"
          class="hidden-file-input"
          @change="onChangeShopImage"
        />
        <div class="profile-actions">
          <button
            type="button"
            class="profile-btn primary"
            :disabled="isUpdatingImage"
            @click="triggerChangeImagePicker"
          >
            {{ isUpdatingImage ? "Updating..." : "Upload Profile" }}
          </button>
          <button
            type="button"
            class="profile-btn ghost"
            :disabled="isUpdatingImage || !shopImageSrc"
            @click="removeShopImage"
          >
            Remove Profile
          </button>
        </div>
      </div>

      <p
        v-if="error && !showCreateModal"
        class="error-text"
        style="margin: 10px 0 0"
      >
        {{ error }}
      </p>

      <div class="settings-form-grid">
        <label class="settings-field">
          <span>Full name</span>
          <input type="text" :value="ownerName || 'N/A'" readonly />
        </label>
        <label class="settings-field">
          <span>Email address</span>
          <div class="input-with-badge">
            <input type="text" :value="ownerEmail || 'N/A'" readonly />
            <span class="verified-badge">Verified</span>
          </div>
        </label>
        <label class="settings-field">
          <span>Shop name</span>
          <input type="text" :value="shop.name || 'N/A'" readonly />
        </label>
        <label class="settings-field">
          <span>Status</span>
          <select :value="(shop.status || 'inactive').toLowerCase()" disabled>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
          </select>
        </label>
        <label class="settings-field">
          <span>Phone number</span>
          <input type="text" :value="shop.phone || 'N/A'" readonly />
        </label>
        <label class="settings-field">
          <span>Password</span>
          <div class="password-field">
            <input
              type="password"
              placeholder="Enter new password or leave blank"
              disabled
            />
            <button type="button" class="eye-btn" disabled>
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
              >
                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </button>
          </div>
        </label>
        <label class="settings-field full">
          <span>Shop Address</span>
          <textarea
            rows="3"
            :value="shop.address || 'N/A'"
            readonly
          ></textarea>
        </label>
      </div>
    </div>

    <div
      v-if="showCreateModal"
      class="modal-overlay"
      @click.self="closeCreateModal"
    >
      <div class="modal-card create-shop-modal">
        <div class="modal-header create-shop-header">
          <div class="step-bar">
            <span class="step active"></span>
            <span class="step"></span>
          </div>
          <h3>Create Your Shop</h3>
          <button
            class="close-btn"
            @click="closeCreateModal"
            aria-label="Close"
          >
            x
          </button>
        </div>

        <form class="create-shop-form" @submit.prevent="createShop">
          <div class="create-grid">
            <section
              class="photo-panel"
              :class="{ dragging: isShopImageDragOver }"
              @dragover.prevent="isShopImageDragOver = true"
              @dragleave.prevent="isShopImageDragOver = false"
              @drop.prevent="onShopImageDrop"
            >
              <div class="photo-circle">
                <img
                  v-if="shopImagePreview"
                  :src="shopImagePreview"
                  alt="Shop preview"
                />
                <div v-else class="photo-placeholder">
                  <svg
                    viewBox="0 0 80 80"
                    fill="none"
                    stroke="#94a3b8"
                    stroke-width="1.6"
                  >
                    <rect x="8" y="18" width="64" height="44" rx="14" />
                    <path d="M16 38h48" />
                    <circle cx="34" cy="31" r="5" />
                    <path d="M34 45h12" />
                  </svg>
                </div>
              </div>
              <div class="photo-note">
                <p>Shop Profile Image</p>
                <button
                  type="button"
                  class="upload-btn"
                  @click="triggerShopImagePicker"
                >
                  {{ shopImageFile ? "Change Photo" : "Upload Photo" }}
                </button>
                <p class="upload-help">
                  Drag and drop or click to upload your shop's logo (PNG, JPG,
                  WEBP).
                </p>
              </div>
              <input
                ref="shopImageInputRef"
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden-file-input"
                @change="onShopImageChange"
              />
            </section>

            <section class="brand-panel">
              <div class="section-title-row">
                <div>
                  <p class="section-kicker">Customization & Branding</p>
                  <h4>Shop Color Palette</h4>
                </div>
                <p class="section-subtext">Pick a tone for your store front</p>
              </div>

              <div class="color-group">
                <div class="color-label">Primary Colour</div>
                <div class="color-palette-grid">
                  <button
                    v-for="color in colorPalette"
                    :key="`primary-${color}`"
                    type="button"
                    class="color-chip"
                    :style="{ backgroundColor: color }"
                    :class="{ selected: createForm.primary_color === color }"
                    @click="setPrimaryColor(color)"
                  ></button>
                </div>
              </div>

              <div class="color-group">
                <div class="color-label">Accent Colour</div>
                <div class="color-palette-grid">
                  <button
                    v-for="color in accentPalette"
                    :key="`accent-${color}`"
                    type="button"
                    class="color-chip accent"
                    :style="{ backgroundColor: color }"
                    :class="{ selected: createForm.accent_color === color }"
                    @click="setAccentColor(color)"
                  ></button>
                </div>
              </div>

              <label class="font-select">
                <span>Shop Font</span>
                <select v-model="createForm.font">
                  <option v-for="font in fontOptions" :key="font" :value="font">
                    {{ font }}
                  </option>
                </select>
              </label>

              <label class="about-field">
                <span>About Us Section</span>
                <textarea
                  v-model="createForm.description"
                  rows="3"
                  placeholder="Share your passion and the story behind your shop"
                  @input="clearFieldError('description')"
                ></textarea>
                <small v-if="fieldErrors.description" class="field-error">{{
                  fieldErrors.description
                }}</small>
              </label>

              <div class="basic-info-grid">
                <label>
                  <span>Shop Name *</span>
                  <input
                    v-model="createForm.name"
                    required
                    type="text"
                    placeholder="Enter shop name"
                    @input="clearFieldError('name')"
                  />
                  <small v-if="fieldErrors.name" class="field-error">{{
                    fieldErrors.name
                  }}</small>
                </label>

                <label>
                  <span>Status *</span>
                  <select
                    v-model="createForm.status"
                    @change="clearFieldError('status')"
                  >
                    <option value="active">active</option>
                    <option value="inactive">inactive</option>
                  </select>
                  <small v-if="fieldErrors.status" class="field-error">{{
                    fieldErrors.status
                  }}</small>
                </label>

                <label class="wide">
                  <span>Address *</span>
                  <input
                    v-model="createForm.address"
                    required
                    type="text"
                    placeholder="Enter address"
                    @input="clearFieldError('address')"
                  />
                  <small v-if="fieldErrors.address" class="field-error">{{
                    fieldErrors.address
                  }}</small>
                </label>

                <label>
                  <span>Phone *</span>
                  <input
                    v-model="createForm.phone"
                    required
                    type="text"
                    placeholder="Enter phone number"
                    @input="clearFieldError('phone')"
                  />
                  <small v-if="fieldErrors.phone" class="field-error">{{
                    fieldErrors.phone
                  }}</small>
                </label>
              </div>

              <div class="social-section">
                <p>Social Links</p>
                <label>
                  <span>Instagram</span>
                  <div class="addon-input">
                    <span>@</span>
                    <input
                      v-model="createForm.instagram"
                      type="text"
                      placeholder="yourhandle"
                    />
                  </div>
                </label>
                <label>
                  <span>Facebook</span>
                  <div class="addon-input">
                    <span>facebook.com/</span>
                    <input
                      v-model="createForm.facebook"
                      type="text"
                      placeholder="yourhandle"
                    />
                  </div>
                </label>
              </div>
            </section>
          </div>

          <p v-if="error" class="error-text form-error">{{ error }}</p>

          <div class="form-actions">
            <button type="button" class="ghost-btn" @click="closeCreateModal">
              Cancel
            </button>
            <button type="submit" class="primary-btn" :disabled="loading">
              {{ loading ? "Creating..." : "Create Shop" }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showSuccessPopup"
      class="success-toast"
      role="status"
      aria-live="polite"
    >
      <span class="toast-icon">
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2.2"
        >
          <path d="M20 6L9 17l-5-5"></path>
        </svg>
      </span>
      <div class="toast-content">
        <strong>Success</strong>
        <p>Your shop was created.</p>
      </div>
    </div>

    <div
      v-if="showSingleShopAlert"
      class="alert-overlay"
      @click.self="showSingleShopAlert = false"
    >
      <div class="alert-modal">
        <div class="alert-icon">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <h3>One Shop Only</h3>
        <p>You can only create one shop per account.</p>
        <button class="alert-btn" @click="showSingleShopAlert = false">
          OK
        </button>
      </div>
    </div>
  </div>
</template>
