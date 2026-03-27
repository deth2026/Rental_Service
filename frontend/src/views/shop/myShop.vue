<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from "vue";
import { shopApi, cityApi } from "@/services/api";
import "../../css/Myshop.css";

const shop = ref(null);
const ownerName = ref("");
<<<<<<< HEAD
const cities = ref([]);
const loadingCities = ref(false);
=======
const ownerEmail = ref("");
>>>>>>> 2d2a8b930983e188da51cace70b2691710da7b83

// Computed property to check if shop exists
const hasShop = computed(() => !!shop.value);
const shopMapLink = computed(() => {
  const primary = String(shop.value?.map_url || "").trim();
  if (primary) return primary;
  const fallback = String(shop.value?.location || "").trim();
  return /^https?:\/\//i.test(fallback) ? fallback : "";
});

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
  city_id: "",
  phone: "",
  description: "",
  address: "",
  location: "",
  latitude: "",
  longitude: "",
  map_url: "",
  status: "active",
  instagram: "",
  facebook: "",
  img_url: "",
});

const getCachedShop = (ownerId) => {
  if (!ownerId) return null;
  try {
    const raw = localStorage.getItem(`myshop_cache_${ownerId}`);
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === "object" ? parsed : null;
  } catch {
    return null;
  }
};

const setCachedShop = (ownerId, shopData) => {
  if (!ownerId) return;
  try {
    if (!shopData) {
      localStorage.removeItem(`myshop_cache_${ownerId}`);
      return;
    }
    localStorage.setItem(`myshop_cache_${ownerId}`, JSON.stringify(shopData));
  } catch {
    // Ignore cache write errors.
  }
};

const getStoredUser = () => {
  try {
    const rawUser = localStorage.getItem("user");
    if (!rawUser) return null;
    const parsed = JSON.parse(rawUser);
    return parsed && typeof parsed === "object" ? parsed : null;
  } catch {
    return null;
  }
};

const getUserId = () => {
  const rawUser = localStorage.getItem("user");
  if (!rawUser) return 1;

  try {
    const parsed = JSON.parse(rawUser);
    return parsed.id || 1;
  } catch {
    return 1;
  }
};

const asArray = (payload) => payload?.data || payload || [];

const formatDateTime = (value) => {
  if (!value) return "N/A";
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleDateString();
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
  const ownerId = getUserId();
  const storedUser = getStoredUser();
  ownerName.value = storedUser?.name || "N/A";
  ownerEmail.value = storedUser?.email || "N/A";

  // Clear shop first to ensure we get fresh data
  shop.value = null;
  const cachedShop = getCachedShop(ownerId);
  if (cachedShop) {
    shop.value = cachedShop;
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
      storedUser?.name ||
      "N/A";
    ownerEmail.value =
      shop.value?.owner?.email ||
      storedUser?.email ||
      "N/A";
  } catch (e) {
    console.error("Failed to load shop", e);
    if (!cachedShop) {
      shop.value = null;
    }
    ownerName.value = storedUser?.name || "N/A";
    ownerEmail.value = storedUser?.email || "N/A";
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
  createForm.map_url = "";
  createForm.status = "active";
  createForm.instagram = "";
  createForm.facebook = "";
  createForm.img_url = "";
  shopImageFile.value = null;
  if (shopImagePreview.value) {
    URL.revokeObjectURL(shopImagePreview.value);
    shopImagePreview.value = "";
  }
  error.value = "";
};

const validateCreateForm = () => {
  const errors = [];
  const name = createForm.name.trim();
  const address = createForm.address.trim();
  const phone = createForm.phone.trim();
  const description = createForm.description.trim();

  // Required fields
  if (!name) errors.push('Name');
  if (!address) errors.push('Address');
  if (!phone) errors.push('Phone');
  if (!description) errors.push('Description');

  // Phone validation
  if (phone && !/^[0-9+\-\s()]{8,20}$/.test(phone)) {
    errors.push('Invalid phone format');
  }

  if (errors.length > 0) {
    error.value = 'Please fill all required fields: ' + errors.join(', ');
    return false;
  }

  return true;
};

const extractCoordinatesFromMapUrl = (value) => {
  const url = String(value || "").trim();
  if (!url) return null;

  const patterns = [
    /@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/,
    /[?&](?:q|query|ll|destination|origin)=(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/,
    /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/,
  ];

  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (!match) continue;
    const lat = Number(match[1]);
    const lng = Number(match[2]);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) continue;
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) continue;
    return { lat, lng };
  }

  return null;
};

const onMapUrlBlur = () => {
  const coords = extractCoordinatesFromMapUrl(createForm.map_url);
  if (!coords) return;
  if (!createForm.latitude) {
    createForm.latitude = String(coords.lat);
  }
  if (!createForm.longitude) {
    createForm.longitude = String(coords.lng);
  }
  if (!String(createForm.location || '').trim()) {
    createForm.location = String(createForm.map_url || '').trim().slice(0, 255);
  }
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

const onShopImageDrop = (event) => {
  isShopImageDragOver.value = false;
  const file = event.dataTransfer?.files?.[0] || null;
  applyShopImageFile(file);
};

const openCreateModal = () => {
  resetForm();
  // Pre-fill phone from user if available
  const storedUser = getStoredUser();
  if (storedUser?.phone) {
    createForm.phone = storedUser.phone;
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
      setCachedShop(getUserId(), updatedShop);
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
  const raw =
    shop.value?.img_url ||
    shop.value?.image_url ||
    shop.value?.image ||
    shop.value?.cover ||
    "";
  return getShopImageUrl(raw);
});

const normalizedShopStatus = computed(() =>
  String(shop.value?.status || "active").toLowerCase(),
);

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
      setCachedShop(getUserId(), updatedShop);
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
    // Error message is already set in validateCreateForm
    return;
  }

  loading.value = true;
  error.value = "";

  const normalizedLocation =
    String(createForm.location || '').trim() ||
    String(createForm.map_url || '').trim().slice(0, 255) ||
    String(createForm.address || '').trim();

  try {
    let payload;
    // if an image file has been selected, use FormData to send multipart request
    if (shopImageFile.value) {
      payload = new FormData();
      payload.append("owner_id", getUserId());
      if (createForm.city_id) payload.append("city_id", createForm.city_id);
      payload.append("name", createForm.name.trim());
      payload.append("description", createForm.description.trim());
      payload.append("address", createForm.address.trim());
      if (normalizedLocation) payload.append("location", normalizedLocation);
      if (createForm.latitude) payload.append("latitude", createForm.latitude);
      if (createForm.longitude)
        payload.append("longitude", createForm.longitude);
      if (createForm.map_url) payload.append("map_url", createForm.map_url.trim());
      payload.append("phone", createForm.phone.trim());
      payload.append("status", createForm.status);
      // the backend expects the field name img_url
      payload.append("img_url", shopImageFile.value);
    } else {
      payload = {
        owner_id: getUserId(),
        city_id: createForm.city_id || null,
        name: createForm.name.trim(),
        description: createForm.description.trim(),
        address: createForm.address.trim(),
        location: normalizedLocation || null,
        latitude: createForm.latitude || null,
        longitude: createForm.longitude || null,
        map_url: createForm.map_url.trim() || null,
        phone: createForm.phone.trim(),
        status: createForm.status,
        img_url: createForm.img_url || null,
      };
    }

    const { data: created } = await shopApi.create(payload);
    const createdShop = created?.data || created || null;
    if (createdShop && typeof createdShop === "object") {
      shop.value = createdShop;
      setCachedShop(getUserId(), createdShop);
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

const loadCities = async () => {
  loadingCities.value = true;
  try {
    const { data } = await cityApi.getAll();
    cities.value = asArray(data);
  } catch (e) {
    console.error("Failed to load cities", e);
  } finally {
    loadingCities.value = false;
  }
};

onMounted(async () => {
  await loadMyShop();
  await loadCities();
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
          <div class="settings-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="12" r="3.2" />
              <path d="M19.4 15a1 1 0 0 0 .2 1.1l.1.1a2 2 0 0 1-2.8 2.8l-.1-.1a1 1 0 0 0-1.1-.2 1 1 0 0 0-.6.9V20a2 2 0 0 1-4 0v-.2a1 1 0 0 0-.6-.9 1 1 0 0 0-1.1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1 1 0 0 0 .2-1.1 1 1 0 0 0-.9-.6H4a2 2 0 0 1 0-4h.2a1 1 0 0 0 .9-.6 1 1 0 0 0-.2-1.1l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1 1 0 0 0 1.1.2 1 1 0 0 0 .6-.9V4a2 2 0 0 1 4 0v.2a1 1 0 0 0 .6.9 1 1 0 0 0 1.1-.2l.1-.1a2 2 0 0 1 2.8 2.8l-.1.1a1 1 0 0 0-.2 1.1 1 1 0 0 0 .9.6H20a2 2 0 0 1 0 4h-.2a1 1 0 0 0-.9.6z" />
            </svg>
          </div>
          <div>
            <h2>Ownership setting</h2>
            <p>Manage your shop profile details and contact info.</p>
          </div>
        </div>
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
            {{ isUpdatingImage ? "Uploading..." : "Upload Profile" }}
          </button>
          <button
            type="button"
            class="profile-btn ghost"
            :disabled="isUpdatingImage || !shop.img_url"
            @click="removeShopImage"
          >
            Remove Profile
          </button>
        </div>
      </div>

      <p
        v-if="error && !showCreateModal"
        class="error-text"
        style="margin: 0 0 18px"
      >
        {{ error }}
      </p>

      <div class="settings-form-grid">
        <label class="settings-field">
          <span>Full name</span>
          <input :value="ownerName" type="text" readonly />
        </label>

        <label class="settings-field">
          <span>Email address</span>
          <div class="input-with-badge">
            <input :value="ownerEmail" type="text" readonly />
            <span class="verified-badge">Verified</span>
          </div>
        </label>

        <label class="settings-field">
          <span>Shop name</span>
          <input :value="shop.name || 'N/A'" type="text" readonly />
        </label>

        <label class="settings-field">
          <span>Status</span>
          <select :value="normalizedShopStatus" disabled>
            <option :value="normalizedShopStatus">{{ normalizedShopStatus }}</option>
          </select>
        </label>

        <label class="settings-field">
          <span>Phone number</span>
          <input :value="shop.phone || 'N/A'" type="text" readonly />
        </label>

        <label class="settings-field">
          <span>Password</span>
          <div class="password-field">
            <input value="Enter new password or leave blank" type="text" readonly />
            <button type="button" class="eye-btn" aria-label="Password placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6Z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
            </button>
          </div>
        </label>

        <label class="settings-field full">
          <span>Shop Address</span>
          <textarea
            :value="shop.address || shop.location || 'N/A'"
            rows="4"
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
          <div>
            <h3>Create New Shop</h3>
            <p class="shop-header-sub">
              Add a new rental shop (pending approval by default).
            </p>
          </div>
          <button
            class="close-btn"
            @click="closeCreateModal"
            aria-label="Close"
          >
            x
          </button>
        </div>

        <form class="create-shop-form" @submit.prevent="createShop">
          <div class="shop-modal-grid">
            <article class="shop-preview-card">
              <div class="shop-preview-image">
                <img
                  v-if="shopImagePreview"
                  :src="shopImagePreview"
                  alt="Shop preview"
                />
                <div v-else class="shop-preview-placeholder">
                  <svg
                    viewBox="0 0 80 80"
                    fill="none"
                    stroke="#94a3b8"
                    stroke-width="1.5"
                  >
                    <rect x="14" y="20" width="52" height="36" rx="12" />
                    <path d="M24 36h32" />
                    <path d="M32 28h16" />
                  </svg>
                </div>
              </div>
              <div class="shop-preview-info">
                <h4>{{ createForm.name || "Untitled shop" }}</h4>
                <p>{{ createForm.description || "Add a location and contact details" }}</p>
                <p class="shop-preview-meta">
                  {{ createForm.address || "City, Country" }}
                </p>
              </div>
              <div class="shop-preview-status">
                <span>Status</span>
                <strong>Pending</strong>
              </div>
            </article>

            <section class="shop-panel">
              <label
                class="upload-card"
                :class="{ active: isShopImageDragOver }"
                @dragover.prevent="isShopImageDragOver = true"
                @dragleave.prevent="isShopImageDragOver = false"
                @drop.prevent="onShopImageDrop"
              >
                <input
                  ref="shopImageInputRef"
                  type="file"
                  accept="image/png,image/jpeg,image/webp"
                  class="hidden-file-input"
                  @change="onShopImageChange"
                />
                <div class="upload-content">
                  <div class="upload-icon">
                    <svg
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="#2563eb"
                      stroke-width="1.6"
                    >
                      <path d="M12 5v14m0-14l-4 4m4-4l4 4"></path>
                      <path d="M6 19h12a2 2 0 1 0 2-2v-5"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="upload-title">Upload shop cover</p>
                    <p class="upload-sub">Drop an image or click to browse files</p>
                  </div>
                </div>
              </label>

              <div class="field-grid">
                <label class="form-field">
                  <span>Province/City</span>
                  <select v-model="createForm.city_id" class="form-select">
                    <option value="" disabled>Select Province</option>
                    <option v-for="city in cities" :key="city.id" :value="city.id">
                      {{ city.name }}
                    </option>
                  </select>
                </label>
                <label class="form-field">
                  <span>Shop Name</span>
                  <input
                    v-model="createForm.name"
                    type="text"
                    placeholder="e.g. Berlin Elite Rentals"
                  />
                </label>
                <label class="form-field">
                  <span>Address</span>
                  <input
                    v-model="createForm.address"
                    type="text"
                    placeholder="Street, City, Country"
                  />
                </label>
                <label class="form-field">
                  <span>Location</span>
                  <input
                    v-model="createForm.location"
                    type="text"
                    placeholder="City, Country"
                  />
                </label>
                <label class="form-field">
                  <span>Latitude</span>
                  <input
                    v-model="createForm.latitude"
                    type="number"
                    step="any"
                    placeholder="e.g. 11.5564"
                  />
                </label>
                <label class="form-field">
                  <span>Longitude</span>
                  <input
                    v-model="createForm.longitude"
                    type="number"
                    step="any"
                    placeholder="e.g. 104.9282"
                  />
                </label>
                <label class="form-field full">
                  <span>Google Map URL</span>
                  <input
                    v-model="createForm.map_url"
                    type="url"
                    placeholder="https://maps.google.com/..."
                    @blur="onMapUrlBlur"
                  />
                </label>
                <label class="form-field">
                  <span>Phone</span>
                  <input
                    v-model="createForm.phone"
                    type="text"
                    placeholder="+855..."
                  />
                </label>
                <label class="form-field full">
                  <span>Description</span>
                  <textarea
                    v-model="createForm.description"
                    rows="4"
                    placeholder="Describe the shop, services or fleet."
                  ></textarea>
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
