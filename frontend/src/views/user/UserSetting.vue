<template>
  <section class="setting-page">
    <div class="frame">
      <main class="panel">
        <section class="content-card">
          <div class="section-head">
            <p class="section-title">{{ t('myProfile') }}</p>
            <button type="button" class="btn btn-logout creative-logout-btn" @click="openLogoutConfirm">
              <span class="logout-icon">↪</span>
              <span>{{ t('logout') }}</span>
            </button>
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
                <input v-model="form.email" type="email" required disabled />
                <small class="verified">{{ t('verified') }}</small>
              </div>
            </label>

            <label class="field">
              <span>{{ t('phoneNumber') }}</span>
              <input v-model="form.phone" type="text" />
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

            <!-- Language Selector -->
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
              <button type="button" class="btn btn-secondary" @click="goBack">{{ t('cancel') }}</button>
            </div>
          </form>

        </section>
      </main>
    </div>

    <div v-if="showToast" class="settings-toast" :class="toastType === 'error' ? 'is-error' : 'is-success'" role="status" aria-live="polite">
      <span>{{ toastMessage }}</span>
      <button type="button" class="settings-toast-close" @click="closeToast">×</button>
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

    <div v-if="showLogoutConfirm" class="confirm-overlay" @click="closeLogoutConfirm">
      <div class="confirm-modal logout-modal" @click.stop>
        <p class="confirm-title">{{ t('logout') }}</p>
        <p class="confirm-text">{{ confirmLogoutText }}</p>
        <div class="confirm-actions">
          <button type="button" class="btn btn-muted" @click="closeLogoutConfirm">{{ t('cancel') }}</button>
          <button type="button" class="btn btn-primary" @click="confirmLogout">{{ t('confirm') }}</button>
        </div>
      </div>
    </div>

  </section>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '../../services/api';
import { logoutUser } from '../../services/auth';
import { setLanguage, getCurrentLanguage } from '../../i18n';

const { t } = useI18n();
const router = useRouter();

const currentLanguage = ref(getCurrentLanguage());
const confirmLogoutText = computed(() => {
  const msg = t('confirmLogout');
  return msg === 'confirmLogout' ? 'Are you sure you want to logout?' : msg;
});

const changeLanguage = (lang) => {
  setLanguage(lang);
  currentLanguage.value = lang;
};

const loading = ref(false);
const avatarUploadLoading = ref(false);
const avatarRemoveLoading = ref(false);
const showPassword = ref(false);
const showRemoveConfirm = ref(false);
const showLogoutConfirm = ref(false);
const success = ref('');
const error = ref('');
const showToast = ref(false);
const toastType = ref('success');
const toastMessage = ref('');
const user = reactive({ id: null, name: '', email: '', role: '', avatar_url: null, img_url: null, phone: '' });
const avatarLoadFailed = ref(false);
const localPreviewUrl = ref(null);
const imageVersion = ref(Date.now());
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
const avatarFile = ref(null);
const avatarInputRef = ref(null);
let toastTimer = null;
const form = reactive({
  name: '',
  email: '',
  phone: '',
  notifications_enabled: true,
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
    return JSON.parse(raw);
  } catch {
    return null;
  }
};

const loadUserData = async () => {
  const storedUser = getStoredUser();
  if (storedUser) {
    user.id = storedUser.id;
    user.name = storedUser.name || '';
    user.email = storedUser.email || '';
    user.role = storedUser.role || 'user';
    user.avatar_url = storedUser.avatar_url || null;
    user.img_url = storedUser.img_url || null;
    user.phone = storedUser.phone || '';
    
    form.name = storedUser.name || '';
    form.email = storedUser.email || '';
    form.phone = storedUser.phone || '';
    
    // Load notification preference
    const notifyKey = NOTIFY_KEY_PREFIX + user.id;
    const storedNotify = localStorage.getItem(notifyKey);
    form.notifications_enabled = storedNotify !== 'false';
  }
};

const saveSettings = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    const userId = user.id;
    if (!userId) {
      throw new Error('User not found');
    }

    const updateData = {
      name: form.name,
      phone: form.phone || '',
    };

    // Only include password if it's not empty
    if (form.password && form.password.length >= 8) {
      updateData.password = form.password;
    }

    const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
    const response = await fetch(`/api/users/${userId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(updateData),
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || errorData.error || 'Failed to update profile');
    }

    const updatedUser = await response.json();
    
    // Update localStorage with new user data
    const currentUser = getStoredUser();
    const newUserData = { ...currentUser, ...updatedUser };
    localStorage.setItem('user', JSON.stringify(newUserData));
    
    // Update reactive user object
    user.name = updatedUser.name || user.name;
    user.phone = updatedUser.phone || user.phone;
    
    // Save notification preference
    const notifyKey = NOTIFY_KEY_PREFIX + user.id;
    localStorage.setItem(notifyKey, form.notifications_enabled ? 'true' : 'false');

    form.password = '';
    showToastMessage(t('profileUpdated') || 'Profile updated successfully!', 'success');
  } catch (err) {
    console.error('Save error:', err);
    showToastMessage(err.message || 'Failed to save settings', 'error');
  } finally {
    loading.value = false;
  }
};

const showToastMessage = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    closeToast();
  }, 4000);
};

const closeToast = () => {
  showToast.value = false;
  if (toastTimer) {
    clearTimeout(toastTimer);
    toastTimer = null;
  }
};

const triggerAvatarPicker = () => {
  avatarInputRef.value?.click();
};

const onAvatarChange = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  
  if (!file.type.startsWith('image/')) {
    showToastMessage('Please select an image file', 'error');
    return;
  }
  
  if (file.size > 5 * 1024 * 1024) {
    showToastMessage('Image size must be less than 5MB', 'error');
    return;
  }

  // Preview locally
  const reader = new FileReader();
  reader.onload = (e) => {
    localPreviewUrl.value = e.target?.result;
  };
  reader.readAsDataURL(file);

  avatarFile.value = file;
  await uploadAvatar(file);
};

const uploadAvatar = async (file) => {
  if (!user.id) return;
  
  avatarUploadLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
    const formData = new FormData();
    formData.append('avatar', file);

    const response = await fetch(`/api/users/${user.id}/avatar`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: formData,
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Failed to upload avatar');
    }

    const data = await response.json();
    
    // Update user data with new avatar
    user.img_url = data.img_url || data.avatar_url;
    
    // Update localStorage
    const currentUser = getStoredUser();
    const newUserData = { ...currentUser, img_url: data.img_url, avatar_url: data.avatar_url };
    localStorage.setItem('user', JSON.stringify(newUserData));
    
    imageVersion.value = Date.now();
    localPreviewUrl.value = null;
    showToastMessage(t('avatarUpdated') || 'Profile photo updated!', 'success');
  } catch (err) {
    console.error('Upload error:', err);
    showToastMessage(err.message || 'Failed to upload avatar', 'error');
    localPreviewUrl.value = null;
  } finally {
    avatarUploadLoading.value = false;
  }
};

const openRemoveConfirm = () => {
  showRemoveConfirm.value = true;
};

const closeRemoveConfirm = () => {
  showRemoveConfirm.value = false;
};

const confirmRemoveAvatar = async () => {
  if (!user.id) return;
  
  avatarRemoveLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token');
    const response = await fetch(`/api/users/${user.id}/avatar`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      throw new Error(errorData.message || 'Failed to remove avatar');
    }

    user.img_url = null;
    user.avatar_url = null;
    
    // Update localStorage
    const currentUser = getStoredUser();
    const newUserData = { ...currentUser, img_url: null, avatar_url: null };
    localStorage.setItem('user', JSON.stringify(newUserData));
    
    imageVersion.value = Date.now();
    showToastMessage(t('avatarRemoved') || 'Profile photo removed!', 'success');
  } catch (err) {
    console.error('Remove error:', err);
    showToastMessage(err.message || 'Failed to remove avatar', 'error');
  } finally {
    avatarRemoveLoading.value = false;
    closeRemoveConfirm();
  }
};

const onAvatarError = () => {
  avatarLoadFailed.value = true;
};

const openLogoutConfirm = () => {
  showLogoutConfirm.value = true;
};

const closeLogoutConfirm = () => {
  showLogoutConfirm.value = false;
};

const confirmLogout = async () => {
  await logoutUser();
  closeLogoutConfirm();
  router.push('/login');
};

const goBack = () => {
  router.back();
};

onMounted(() => {
  loadUserData();
});

onBeforeUnmount(() => {
  if (toastTimer) clearTimeout(toastTimer);
});
</script>

<style scoped>
.setting-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
  padding: 2rem;
  font-family: 'Segoe UI', system-ui, sans-serif;
}

.frame {
  max-width: 800px;
  margin: 0 auto;
}

.panel {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.content-card {
  padding: 2rem;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #eee;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
  margin: 0;
}

.avatar-row {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 12px;
}

.profile-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #e2e8f0;
}

.avatar-actions {
  display: flex;
  gap: 0.75rem;
}

.sr-only-file {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.field.full {
  grid-column: span 2;
}

.field span {
  font-weight: 500;
  color: #475569;
  font-size: 0.9rem;
}

.field input,
.field select,
.field textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.field input:focus,
.field select:focus,
.field textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.field input:disabled {
  background: #f1f5f9;
  cursor: not-allowed;
}

.verified-wrap {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.verified {
  color: #10b981;
  font-size: 0.75rem;
}

.password-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.password-wrap input {
  flex: 1;
  padding-right: 2.5rem;
}

.eye-btn {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: #64748b;
}

.eye-btn:hover {
  color: #334155;
}

.notification-field {
  grid-column: span 2;
}

.notify-box {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.notify-box strong {
  display: block;
  color: #334155;
  margin-bottom: 0.25rem;
}

.notify-box p {
  margin: 0;
  font-size: 0.85rem;
  color: #64748b;
}

.switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: 0.3s;
  border-radius: 26px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.switch input:checked + .slider {
  background-color: #3b82f6;
}

.switch input:checked + .slider:before {
  transform: translateX(22px);
}

.language-box {
  flex-direction: column;
  align-items: flex-start;
  gap: 1rem;
}

.language-options {
  display: flex;
  gap: 0.5rem;
  width: 100%;
}

.lang-btn {
  flex: 1;
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: #fff;
  cursor: pointer;
  transition: all 0.2s;
}

.lang-btn:hover {
  border-color: #3b82f6;
}

.lang-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.action-row {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-size: 0.95rem;
}

.btn-primary {
  background: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-primary:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn-secondary:hover {
  background: #e2e8f0;
}

.btn-muted {
  background: #f1f5f9;
  color: #64748b;
}

.btn-muted:hover {
  background: #e2e8f0;
}

.btn-logout {
  background: #fee2e2;
  color: #dc2626;
}

.btn-logout:hover {
  background: #fecaca;
}

.creative-logout-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logout-icon {
  font-size: 1.1rem;
}

.settings-toast {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  animation: slideIn 0.3s ease;
}

.settings-toast.is-success {
  background: #10b981;
}

.settings-toast.is-error {
  background: #ef4444;
}

.settings-toast-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}

.confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.confirm-modal {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  max-width: 400px;
  width: 90%;
  text-align: center;
}

.confirm-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
  color: #333;
}

.confirm-text {
  color: #64748b;
  margin: 0 0 1.5rem;
}

.confirm-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .field.full,
  .notification-field {
    grid-column: span 1;
  }
  
  .avatar-row {
    flex-direction: column;
    text-align: center;
  }
  
  .action-row {
    flex-direction: column;
  }
}
</style>
