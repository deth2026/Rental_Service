<template>
  <section class="setting-page">
    <div class="frame">
      <main class="panel">
        <section class="content-card">
          <div class="section-head">
            <p class="section-title">Setting OwnerShop</p>
            <button type="button" class="btn btn-logout" @click="logout">Logout</button>
          </div>
          <div class="avatar-row">
            <img
              :key="displayImageUrl"
              :src="displayImageUrl"
              alt="Profile photo"
              class="profile-avatar"
              @error="onAvatarError"
            />
            <div class="avatar-actions">
              <button type="button" class="btn btn-primary" @click="triggerAvatarPicker">Upload Profile</button>
              <button type="button" class="btn btn-muted" :disabled="avatarUploading" @click="openRemoveConfirm">
                {{ avatarUploading ? 'Removing...' : 'Remove Profile' }}
              </button>
            </div>
          </div>
          <input ref="avatarInputRef" type="file" accept="image/*" class="sr-only-file" @change="onAvatarChange" />

          <form @submit.prevent="saveSettings" class="form-grid">
            <label class="field">
              <span>Full name</span>
              <input v-model="form.name" type="text" required />
            </label>

            <label class="field">
              <span>Email address</span>
              <div class="verified-wrap">
                <input v-model="form.email" type="email" required />
                <small class="verified">Verified</small>
              </div>
            </label>

            <label class="field">
              <span>Shop name</span>
              <input v-model="form.shop_name" type="text" required />
            </label>

            <label class="field">
              <span>Phone number</span>
              <input v-model="form.phone" type="text" />
            </label>

            <label class="field">
              <span>City</span>
              <select v-model.number="form.city_id">
                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
              </select>
            </label>

            <label class="field">
              <span>Password</span>
              <div class="password-wrap">
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" minlength="6" />
                <button
                  type="button"
                  class="eye-btn"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Hide password' : 'Show password'"
                  :title="showPassword ? 'Hide password' : 'Show password'"
                >
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                      d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <circle cx="12" cy="12" r="2.8" fill="none" stroke="currentColor" stroke-width="1.8" />
                    <path
                      v-if="!showPassword"
                      d="M4 20L20 4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.8"
                      stroke-linecap="round"
                    />
                  </svg>
                </button>
              </div>
            </label>

            <label class="field full">
              <span>Shop Address</span>
              <textarea v-model="form.shop_address" rows="5"></textarea>
            </label>

            <div class="field notification-field">
              <span>Notifications</span>
              <div class="notify-box">
                <div>
                  <strong>Enable notifications</strong>
                  <p>Receive order and account updates.</p>
                </div>
                <label class="switch">
                  <input v-model="form.notifications_enabled" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>
            <div class="full action-row">
              <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'Saving...' : 'Save Setting' }}</button>
            </div>
          </form>

          <p v-if="success" class="feedback ok">{{ success }}</p>
          <p v-if="error" class="feedback err">{{ error }}</p>

        </section>
      </main>
    </div>

    <div v-if="showRemoveConfirm" class="confirm-overlay" @click="closeRemoveConfirm">
      <div class="confirm-modal" @click.stop>
        <p class="confirm-title">Delete Profile</p>
        <p class="confirm-text">Are you sure you want to delete your profile?</p>
        <div class="confirm-actions">
          <button type="button" class="btn btn-muted" @click="closeRemoveConfirm">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="avatarUploading" @click="confirmRemoveAvatar">
            Confirm
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';
import { clearSession } from '../../services/auth';

const router = useRouter();
const loading = ref(false);
const avatarUploading = ref(false);
const showPassword = ref(true);
const showRemoveConfirm = ref(false);
const success = ref('');
const error = ref('');
const user = reactive({ id: null, name: '', email: '', role: '', avatar_url: null, img_url: null });
const avatarLoadFailed = ref(false);
const localPreviewUrl = ref(null);
const imageVersion = ref(Date.now());
const currentShopId = ref(null);
const NOTIFY_KEY_PREFIX = 'settings_notifications_enabled_';
const apiOrigin = computed(() => {
  try {
    return new URL(api.defaults.baseURL, window.location.origin).origin;
  } catch {
    return window.location.origin;
  }
});
const fallbackImageUrl = computed(() => {
  const identity = user.name || user.email || 'User';
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(identity)}&background=0D7CB7&color=ffffff&size=256`;
});
const profileImageUrl = computed(() => {
  if (user.avatar_url) {
    return user.avatar_url;
  }
  if (user.img_url) {
    if (/^https?:\/\//.test(user.img_url)) return user.img_url;
    const normalized = user.img_url.replace(/^\/+/, '');
    return `${apiOrigin.value}/${normalized}`;
  }
  return null;
});
const displayImageUrl = computed(() => {
  if (localPreviewUrl.value) return localPreviewUrl.value;
  if (avatarLoadFailed.value || !profileImageUrl.value) return fallbackImageUrl.value;
  return profileImageUrl.value;
});
const cities = ref([]);
const avatarFile = ref(null);
const avatarInputRef = ref(null);
const form = reactive({
  name: '',
  email: '',
  shop_name: '',
  phone: '',
  shop_address: '',
  notifications_enabled: true,
  city_id: null,
  password: '',
});

const extractCollection = (responseData) => {
  if (Array.isArray(responseData)) return responseData;
  if (Array.isArray(responseData?.data)) return responseData.data;
  if (Array.isArray(responseData?.data?.data)) return responseData.data.data;
  return [];
};

const getStoredUserId = () => {
  try {
    const raw = localStorage.getItem('user');
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    return parsed?.id || null;
  } catch {
    return null;
  }
};

const loadData = async () => {
  error.value = '';
  try {
    const storedUserId = getStoredUserId();

    const [citiesResult, usersResult, shopsResult] = await Promise.allSettled([
      api.get('/cities'),
      storedUserId ? api.get(`/users/${storedUserId}`) : api.get('/users'),
      api.get('/shops')
    ]);

    if (citiesResult.status === 'fulfilled') {
      cities.value = extractCollection(citiesResult.value.data);
    } else {
      cities.value = [];
    }

    let usersData = null;
    if (usersResult.status === 'fulfilled') {
      usersData = usersResult.value.data;
    } else if (storedUserId) {
      try {
        const fallbackUsers = await api.get('/users');
        usersData = fallbackUsers.data;
      } catch {
        usersData = null;
      }
    }

    const users = extractCollection(usersData);
    const selectedUser = usersData?.id ? usersData : (users.find((entry) => entry.id === storedUserId) || users[0] || null);
    if (!selectedUser) {
      throw new Error('No user found in database. Create at least one user first.');
    }

    Object.assign(user, selectedUser);
    imageVersion.value = Date.now();

    const shops = shopsResult.status === 'fulfilled' ? extractCollection(shopsResult.value.data) : [];
    const selectedShop = shops.find((shop) => Number(shop.owner_id) === Number(user.id)) || shops[0] || null;
    currentShopId.value = selectedShop?.id || null;

    form.name = user.name || '';
    form.email = user.email || '';
    form.phone = user.phone || '';
    form.shop_name = selectedShop?.name || '';
    form.shop_address = selectedShop?.address || '';
    form.city_id = selectedShop?.city_id || cities.value?.[0]?.id || null;

    const notifyRaw = localStorage.getItem(`${NOTIFY_KEY_PREFIX}${user.id}`);
    form.notifications_enabled = notifyRaw === null ? true : notifyRaw === '1';
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to load settings.';
  }
};

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null;
  if (!file) return;

  avatarFile.value = null;
  if (localPreviewUrl.value) {
    URL.revokeObjectURL(localPreviewUrl.value);
    localPreviewUrl.value = null;
  }
  if (avatarInputRef.value) {
    avatarInputRef.value.value = '';
  }
  error.value = 'Avatar upload endpoint is not configured in backend yet.';
};

const triggerAvatarPicker = () => {
  if (avatarInputRef.value) {
    avatarInputRef.value.value = '';
  }
  avatarInputRef.value?.click();
};

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const openRemoveConfirm = () => {
  showRemoveConfirm.value = true;
};

const closeRemoveConfirm = () => {
  showRemoveConfirm.value = false;
};

const confirmRemoveAvatar = async () => {
  showRemoveConfirm.value = false;
  await removeAvatar();
};

const removeAvatar = async () => {
  success.value = '';
  error.value = '';
  user.img_url = null;
  user.avatar_url = null;
  avatarLoadFailed.value = false;
  imageVersion.value = Date.now();
  if (localPreviewUrl.value) {
    URL.revokeObjectURL(localPreviewUrl.value);
    localPreviewUrl.value = null;
  }
  avatarFile.value = null;
  if (avatarInputRef.value) {
    avatarInputRef.value.value = '';
  }
  error.value = 'Avatar remove endpoint is not configured in backend yet.';
};

const saveSettings = async () => {
  loading.value = true;
  success.value = '';
  error.value = '';
  try {
    const userPayload = {
      name: form.name,
      email: form.email,
      phone: form.phone || null,
      role: user.role || 'owner',
    };
    if (form.password) {
      userPayload.password = form.password;
    }

    let userResponse;
    if (user.id) {
      userResponse = await api.put(`/users/${user.id}`, userPayload);
    } else {
      userResponse = await api.post('/users', {
        ...userPayload,
        password: userPayload.password || '123456'
      });
    }

    Object.assign(user, userResponse.data || {});

    const shopPayload = {
      owner_id: user.id,
      city_id: form.city_id ? Number(form.city_id) : null,
      name: form.shop_name,
      address: form.shop_address || '',
      status: 'active'
    };

    if (currentShopId.value) {
      await api.put(`/shops/${currentShopId.value}`, shopPayload);
    } else {
      const { data: createdShop } = await api.post('/shops', shopPayload);
      currentShopId.value = createdShop?.id || null;
    }

    localStorage.setItem(`${NOTIFY_KEY_PREFIX}${user.id}`, form.notifications_enabled ? '1' : '0');
    if (form.password) {
      form.password = '';
    }
    success.value = 'Settings saved to database successfully.';
    try {
      await loadData();
    } catch {
      // loadData already handles its own error message
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to save settings.';
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  await clearSession();
  router.push('/login');
};

onMounted(loadData);
</script>
