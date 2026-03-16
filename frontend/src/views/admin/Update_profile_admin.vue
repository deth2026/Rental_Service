<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M8 12h8M12 8v8"/>
          </svg>
        </div>
        <div class="brand-text">
          <span class="brand-title">CHONG CHOUL</span>
          <span class="brand-subtitle">Rental System</span>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="#" class="nav-item" @click.prevent="navigateTo('/admin')">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
          </span>
          <span>Dashboard</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </span>
          <span>Shop Management</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </span>
          <span>User Management</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
              <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
              <path d="M5 17h-2v-6l2-5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6"/>
              <path d="M6 6h8l4 5h-12"/>
            </svg>
          </span>
          <span>Vehicle Management</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </span>
          <span>Booking Management</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
              <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
              <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/>
            </svg>
          </span>
          <span>Coupons</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
          </span>
          <span>Categories</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </span>
          <span>Cities</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </span>
          <span>Financials</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
          </span>
          <span>Reports</span>
        </a>
        <a href="#" class="nav-item active">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
          </span>
          <span>Settings</span>
        </a>
      </nav>
      
      <div class="sidebar-footer">
        <a href="#" class="nav-item logout" @click.prevent="handleLogout">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </span>
          <span>Logout</span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Header -->
      <header class="top-header">
        <div class="header-actions">
          <button class="icon-btn notification-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notification-badge">3</span>
          </button>
          <button class="icon-btn help-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </button>
          <div class="user-profile">
            <div class="user-avatar">
              <img :src="photoPreview || `https://ui-avatars.com/api/?name=${encodeURIComponent(formData.fullName || 'Admin')}&background=0066FF&color=fff`" alt="User" />
            </div>
            <div class="user-info">
              <span class="user-name">Admin Panel</span>
              <span class="user-role">Administrator</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <div class="page-content">
        <!-- Page Header -->
        <div class="page-header">
          <h1 class="page-title-main">Update Profile</h1>
          <p class="page-subtitle">Manage your personal information and public profile details.</p>
        </div>

        <!-- Main Grid -->
        <div class="content-grid">
          <!-- Left Column - Profile Photo Card -->
          <div class="profile-photo-card">
            <div class="profile-photo-container">
              <div class="profile-photo-wrapper" @click="triggerFileDialog">
                <img
                  v-if="photoPreview"
                  :src="photoPreview"
                  alt="Profile photo"
                  class="profile-photo-img"
                />
                <div v-else class="profile-photo-placeholder">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </div>
              </div>
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="sr-only"
                @change="onFileChange"
              />
            </div>
            <div class="profile-photo-label">Your Photo</div>
            <div class="profile-photo-hint">Recommended: 400x400px (JPG, PNG)</div>
            
            <button class="btn-primary" type="button" @click="triggerFileDialog" :disabled="loading">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              Upload New Photo
            </button>
            
            <button class="btn-remove" type="button" @click="removePhoto" :disabled="removingPhoto || loading || !photoPreview">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              </svg>
              Remove
            </button>
            
            <div class="security-status">
              <svg class="shield-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              <div class="security-info">
                <span class="security-label">Account Security</span>
                <span class="security-date">Last updated: {{ lastUpdatedLabel }}</span>
              </div>
            </div>
          </div>

          <!-- Right Column - Personal Information Card -->
          <div class="personal-info-card">
            <h2 class="card-header">Personal Information</h2>
            
            <form class="personal-form" @submit.prevent="submitForm">
              <div v-if="message.text" :class="['alert', message.type === 'error' ? 'alert-error' : 'alert-success']">
                {{ message.text }}
              </div>
              <div class="form-grid">
                <div class="form-group">
                  <label>Full Name</label>
                  <input type="text" v-model="formData.fullName" placeholder="Enter your full name" />
                </div>
                
                <div class="form-group">
                  <label>Job Title/Role</label>
                  <input type="text" v-model="formData.jobTitle" placeholder="Enter your job title" />
                </div>
                
                <div class="form-group">
                  <label>Email Address</label>
                  <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                      <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    <input type="email" v-model="formData.email" placeholder="Enter your email" />
                  </div>
                </div>
                
                <div class="form-group">
                  <label>Phone Number</label>
                  <div class="input-with-icon">
                    <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    <input type="tel" v-model="formData.phone" placeholder="Enter your phone number" />
                  </div>
                </div>
              </div>
              
              <div class="form-group full-width">
                <label>Bio/Description</label>
                <textarea v-model="formData.bio" placeholder="Describe your role and responsibilities..." rows="4"></textarea>
              </div>
              
              <div class="form-actions">
                <button type="button" class="btn-cancel" @click="navigateTo('/admin/settings')">Cancel</button>
                <button type="submit" class="btn-save" :disabled="loading">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                  </svg>
                  Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Notifications Section -->
        <div class="notifications-card">
          <div class="notification-content">
            <div class="notification-icon-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </div>
            <div class="notification-text">
              <h3>Email Notifications</h3>
              <p>Receive updates about bookings, new vehicles, and customer activity</p>
            </div>
          </div>
          <div class="toggle-switch on">
            <div class="toggle-slider"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const formData = reactive({
  fullName: '',
  jobTitle: 'Super Admin',
  email: '',
  phone: '',
  bio: '',
});

const photoFile = ref(null);
const photoPreview = ref('');
const userId = ref(null);
const lastUpdated = ref(null);
const loading = ref(false);
const removingPhoto = ref(false);
const message = reactive({ type: '', text: '' });
const fileInput = ref(null);

const resolveImageUrl = (path) => {
  if (!path) return '';
  return path.startsWith('http') ? path : `/storage/${path}`;
};

const lastUpdatedLabel = computed(() => {
  if (!lastUpdated.value) return 'Not updated yet';
  const delta = Date.now() - new Date(lastUpdated.value).getTime();
  if (isNaN(delta)) return 'Not updated yet';
  const days = Math.floor(delta / 86400000);
  if (days <= 0) return 'Just now';
  if (days === 1) return '1 day ago';
  return `${days} days ago`;
});

const navigateTo = (path) => {
  router.push(path);
};

const setMessage = (type, text) => {
  message.type = type;
  message.text = text;
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await fetch('/api/users/logout', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};

const loadProfile = async () => {
  const token = localStorage.getItem('auth_token');
  if (!token) {
    router.push('/login');
    return;
  }
  try {
    const res = await fetch('/api/auth/me', {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!res.ok) throw new Error('Unable to load your profile');

    const data = await res.json();
    const user = data.user || {};

    userId.value = user.id;
    formData.fullName = user.name || '';
    formData.jobTitle = user.job_title || user.role || 'Super Admin';
    formData.email = user.email || '';
    formData.phone = user.phone || '';
    formData.bio = user.bio || '';
    photoPreview.value = resolveImageUrl(user.profile_picture) || user.avatar_url || '';
    lastUpdated.value = user.updated_at;
  } catch (error) {
    setMessage('error', error.message);
  }
};

const triggerFileDialog = () => {
  fileInput.value?.click();
};

const onFileChange = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;
  photoFile.value = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    photoPreview.value = e.target?.result || '';
  };
  reader.readAsDataURL(file);
};

const persistToLocalStorage = (user) => {
  localStorage.setItem('user_name', user.name || formData.fullName);
  localStorage.setItem('user_email', user.email || formData.email);
  localStorage.setItem('user_phone', user.phone || formData.phone || '');
  localStorage.setItem('user_job_title', user.job_title || formData.jobTitle || '');
  localStorage.setItem('user_bio', user.bio || formData.bio || '');
  localStorage.setItem('user_profile_picture', user.profile_picture || '');
};

const submitForm = async () => {
  if (!userId.value) return;
  loading.value = true;
  setMessage('', '');
  try {
    const token = localStorage.getItem('auth_token');
    const form = new FormData();
    form.append('name', formData.fullName);
    form.append('job_title', formData.jobTitle || '');
    form.append('email', formData.email);
    form.append('phone', formData.phone || '');
    form.append('bio', formData.bio || '');
    if (photoFile.value) {
      form.append('profile_picture', photoFile.value);
    }

    const res = await fetch(`/api/users/${userId.value}/update-profile`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
      body: form,
    });

    const responseData = await res.json();
    if (!res.ok) {
      throw new Error(responseData.message || 'Failed to save changes');
    }

    const user = responseData.user || {};
    formData.fullName = user.name ?? formData.fullName;
    formData.jobTitle = user.job_title ?? formData.jobTitle;
    formData.email = user.email ?? formData.email;
    formData.phone = user.phone ?? formData.phone;
    formData.bio = user.bio ?? formData.bio;
    photoPreview.value = resolveImageUrl(user.profile_picture) || photoPreview.value;
    lastUpdated.value = user.updated_at || new Date().toISOString();
    photoFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
    persistToLocalStorage(user);
    setMessage('success', 'Profile updated successfully.');
    setTimeout(() => router.push('/admin/settings'), 600);
  } catch (error) {
    setMessage('error', error.message);
  } finally {
    loading.value = false;
  }
};

const removePhoto = async () => {
  if (!userId.value || !photoPreview.value) return;
  removingPhoto.value = true;
  setMessage('', '');
  try {
    const token = localStorage.getItem('auth_token');
    const res = await fetch(`/api/users/${userId.value}/avatar`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    });

    if (!res.ok) {
      const data = await res.json();
      throw new Error(data.message || 'Unable to remove photo');
    }

    photoPreview.value = '';
    photoFile.value = null;
    if (fileInput.value) fileInput.value.value = '';
    persistToLocalStorage({ profile_picture: '' });
    lastUpdated.value = new Date().toISOString();
    setMessage('success', 'Photo removed.');
  } catch (error) {
    setMessage('error', error.message);
  } finally {
    removingPhoto.value = false;
  }
};

onMounted(() => {
  loadProfile();
});
</script>




