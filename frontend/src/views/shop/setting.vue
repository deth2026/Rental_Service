<template>
  <section class="setting-page">
    <div class="frame">
      <main class="panel">
        <section class="content-card">
          <div class="section-head">
            <p class="section-title">{{ t('settingOwnership') }}</p>
            <button type="button" class="btn btn-logout" @click="logout">{{ t('logout') }}</button>
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
              <button type="button" class="btn btn-primary" :disabled="avatarUploadLoading || avatarRemoveLoading" @click="triggerAvatarPicker">
                {{ avatarUploadLoading ? t('uploading') : t('uploadProfile') }}
              </button>
              <button type="button" class="btn btn-muted" :disabled="avatarUploadLoading || avatarRemoveLoading" @click="openRemoveConfirm">
                {{ avatarRemoveLoading ? t('removing') : t('removeProfile') }}
              </button>
            </div>
          </div>
          <input ref="avatarInputRef" type="file" accept="image/*" class="sr-only-file" @change="onAvatarChange" />

          <form @submit.prevent="saveSettings" class="form-grid">
            <label class="field">
              <span>{{ t('fullName') }}</span>
              <input v-model="form.name" type="text" required />
            </label>

            <label class="field">
              <span>{{ t('emailAddress') }}</span>
              <div class="verified-wrap">
                <input v-model="form.email" type="email" required />
                <small class="verified">{{ t('verified') }}</small>
              </div>
            </label>

            <label class="field">
              <span>{{ t('shopName') }}</span>
              <input v-model="form.shop_name" type="text" required />
            </label>

            <label class="field">
              <span>{{ t('phoneNumber') }}</span>
              <input v-model="form.phone" type="text" />
            </label>

            <label class="field">
              <span>{{ t('city') }}</span>
              <select v-model.number="form.city_id">
                <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
              </select>
            </label>

            <label class="field">
              <span>{{ t('password') }}</span>
              <div class="password-wrap">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  minlength="8"
                  autocomplete="new-password"
                  :placeholder="t('passwordPlaceholder')"
                />
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
              <span>{{ t('shopAddress') }}</span>
              <textarea v-model="form.shop_address" rows="5"></textarea>
            </label>

            <!-- Language Selector near Notifications -->
            <div class="field notification-field language-field">
              <span>{{ t('language') }}</span>
              <div class="notify-box language-box">
                <div>
                  <strong>{{ t('selectLanguage') }}</strong>
                  <p>{{ t('languageDescription') }}</p>
                </div>
                <div class="language-options">
                  <button 
                    type="button" 
                    class="lang-btn" 
                    :class="{ active: currentLanguage === 'en' }"
                    @click="changeLanguage('en')"
                  >
                    {{ t('english') }}
                  </button>
                  <button 
                    type="button" 
                    class="lang-btn" 
                    :class="{ active: currentLanguage === 'kh' }"
                    @click="changeLanguage('kh')"
                  >
                    {{ t('khmer') }}
                  </button>
                </div>
              </div>
            </div>

            <div class="field notification-field">
              <span>{{ t('notifications') }}</span>
              <div class="notify-box">
                <div>
                  <strong>{{ t('enableNotifications') }}</strong>
                  <p>{{ t('receiveOrderUpdates') }}</p>
                </div>
                <label class="switch">
                  <input v-model="form.notifications_enabled" type="checkbox" />
                  <span class="slider"></span>
                </label>
              </div>
            </div>
            <div class="full action-row">
              <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? t('saving') : t('saveSetting') }}</button>
            </div>
          </form>

          <p v-if="success" class="feedback ok">{{ success }}</p>
          <p v-if="error" class="feedback err">{{ error }}</p>

        </section>
      </main>
    </div>

    <div v-if="showSuccessToast" class="success-toast" role="status" aria-live="polite">
      <span>{{ successToastMessage }}</span>
      <button type="button" class="success-toast-close" @click="showSuccessToast = false">×</button>
    </div>

    <div v-if="showRemoveConfirm" class="confirm-overlay" @click="closeRemoveConfirm">
      <div class="confirm-modal" @click.stop>
        <p class="confirm-title">{{ t('deleteProfile') }}</p>
        <p class="confirm-text">{{ t('confirmDelete') }}</p>
        <div class="confirm-actions">
          <button type="button" class="btn btn-muted" @click="closeRemoveConfirm">{{ t('cancel') }}</button>
          <button type="button" class="btn btn-primary" :disabled="avatarUploadLoading || avatarRemoveLoading" @click="confirmRemoveAvatar">
            {{ t('confirm') }}
          </button>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { logoutUser } from '../../services/auth';
import { setLanguage, getCurrentLanguage } from '../../i18n';

const { t } = useI18n();
const router = useRouter();

const currentLanguage = ref(getCurrentLanguage());

const changeLanguage = (lang) => {
  setLanguage(lang);
  currentLanguage.value = lang;
};

const loading = ref(false);
const avatarUploadLoading = ref(false);
const avatarRemoveLoading = ref(false);
const showPassword = ref(false);
const showRemoveConfirm = ref(false);
const success = ref('');
const error = ref('');
const showSuccessToast = ref(false);
const successToastMessage = ref('');
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
    if (normalized.startsWith('storage/')) {
      return `${apiOrigin.value}/${normalized}`;
    }
    if (normalized.includes('/')) {
      return `${apiOrigin.value}/storage/${normalized}`;
    }
    return `${apiOrigin.value}/storage/avatars/${normalized}`;
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
let successToastTimer = null;
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

const getStoredUser = () => {
  try {
    const raw = localStorage.getItem('user');
    if (!raw) return null;
    const parsed = JSON.parse(raw);
    return parsed && typeof parsed === 'object' ? parsed : null;
  } catch {
    return null;
  }
};

const loadData = async () => {
  error.value = '';
  try {
    const storedUserId = getStoredUserId();
    const storedUser = getStoredUser();

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
    const selectedUser =
      (usersData?.id ? usersData : null) ||
      users.find((entry) => Number(entry.id) === Number(storedUserId)) ||
      users.find((entry) => storedUser?.email && entry.email === storedUser.email) ||
      users[0] ||
      storedUser ||
      null;
    if (!selectedUser) {
      throw new Error('Cannot load user data. Ensure backend is running and login again.');
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
    form.password = '';

    const notifyRaw = localStorage.getItem(`${NOTIFY_KEY_PREFIX}${user.id}`);
    form.notifications_enabled = notifyRaw === null ? true : notifyRaw === '1';
  } catch (err) {
    error.value = err.response?.data?.message || err.message || t('failedLoadSettings');
  }
};

const onAvatarChange = (event) => {
  const file = event.target.files?.[0] || null;
  if (!file) return;
  uploadAvatar(file);
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
  if (!user.id) {
    error.value = t('userNotFound');
    return;
  }

  avatarRemoveLoading.value = true;
  success.value = '';
  error.value = '';

  try {
    const { data } = await api.delete(`/users/${user.id}/avatar`);
    const updatedUser = data?.user || {};

    user.img_url = updatedUser.img_url || null;
    user.avatar_url = updatedUser.avatar_url || null;
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

    localStorage.setItem('user', JSON.stringify(user));
    success.value = data?.message || t('avatarRemoved');
  } catch (err) {
    error.value = err.response?.data?.message || err.message || t('failedRemoveAvatar');
  } finally {
    avatarRemoveLoading.value = false;
  }
};

const uploadAvatar = async (file) => {
  if (!user.id) {
    error.value = t('userNotFound');
    return;
  }

  avatarUploadLoading.value = true;
  success.value = '';
  error.value = '';

  const formData = new FormData();
  formData.append('avatar', file);

  try {
    const previewUrl = URL.createObjectURL(file);
    if (localPreviewUrl.value) {
      URL.revokeObjectURL(localPreviewUrl.value);
    }
    localPreviewUrl.value = previewUrl;
    avatarFile.value = file;

    const { data } = await api.post(`/users/${user.id}/avatar`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    const updatedUser = data?.user || {};
    user.img_url = updatedUser.img_url || null;
    user.avatar_url = updatedUser.avatar_url || null;
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

    localStorage.setItem('user', JSON.stringify(user));
    success.value = data?.message || t('avatarUploaded');
  } catch (err) {
    if (localPreviewUrl.value) {
      URL.revokeObjectURL(localPreviewUrl.value);
      localPreviewUrl.value = null;
    }
    avatarFile.value = null;
    error.value = err.response?.data?.message || err.message || t('failedUploadAvatar');
  } finally {
    avatarUploadLoading.value = false;
  }
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
      if (!form.password) {
        throw new Error(t('password') + ' is required');
      }
      userResponse = await api.post('/users', {
        ...userPayload,
        password: userPayload.password
      });
    }

    Object.assign(user, userResponse.data || {});
    localStorage.setItem('user', JSON.stringify(user));

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
    success.value = t('settingsSaved');
    successToastMessage.value = success.value;
    showSuccessToast.value = true;
    if (successToastTimer) {
      clearTimeout(successToastTimer);
    }
    successToastTimer = setTimeout(() => {
      showSuccessToast.value = false;
      successToastTimer = null;
    }, 3200);
    try {
      await loadData();
    } catch {
      // loadData already handles its own error message
    }
  } catch (err) {
    error.value = err.response?.data?.message || err.message || t('failedSaveSettings');
    form.password = '';
  } finally {
    loading.value = false;
  }
};

const logout = async () => {
  await logoutUser();
  router.push('/login');
};

onMounted(loadData);
</script>

<style>
@import "../../assets/setting.css";

/* Language Selector Styles - Near Notifications */
.language-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  width: 100%;
}

.language-box .language-options {
  display: flex;
  gap: 8px;
}

.language-field {
  margin-top: 8px;
}

.lang-btn {
  padding: 8px 16px;
  border: 1px solid #d5e7eb;
  background: #ffffff;
  color: var(--ink-600);
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.lang-btn:hover {
  background: #eef5f7;
}

.lang-btn.active {
  background: linear-gradient(135deg, var(--teal-700), var(--teal-600));
  color: #fff;
  border-color: var(--teal-600);
}

.success-toast {
  position: fixed;
  top: 16px;
  right: 16px;
  z-index: 1200;
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 260px;
  max-width: min(92vw, 420px);
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #bfe9d2;
  background: #ecfbf3;
  color: #136b44;
  box-shadow: 0 8px 28px rgba(0, 0, 0, 0.12);
}

.success-toast-close {
  margin-left: auto;
  border: none;
  background: transparent;
  color: #136b44;
  cursor: pointer;
  font-size: 22px;
  line-height: 1;
  padding: 0;
}
</style>
