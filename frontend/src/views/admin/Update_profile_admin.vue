<template>
  <div class="update-profile-page">
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
          <button class="header-icon-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notification-badge">3</span>
          </button>
          <button class="header-icon-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </button>
          <div class="header-divider"></div>
          <span class="page-title">Update Profile</span>
          <div class="header-avatar">
            <img src="https://ui-avatars.com/api/?name=Sovann&background=0066FF&color=fff" alt="User" />
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
              <div class="profile-photo-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
            </div>
            <div class="profile-photo-label">Your Photo</div>
            <div class="profile-photo-hint">Recommended: 400x400px (JPG, PNG)</div>
            
            <button class="btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              Upload New Photo
            </button>
            
            <button class="btn-remove">
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
                <span class="security-date">Last updated: 2 days ago</span>
              </div>
            </div>
          </div>

          <!-- Right Column - Personal Information Card -->
          <div class="personal-info-card">
            <h2 class="card-header">Personal Information</h2>
            
            <form class="personal-form">
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
                <button type="submit" class="btn-save">
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
import { reactive } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const formData = reactive({
  fullName: 'Sovann',
  jobTitle: 'Super Admin',
  email: 'sovann.admin@chongchoul',
  phone: '+855 12 345 678',
  bio: 'Experienced administrator with expertise in fleet logistics and regional operations management. Dedicated to ensuring seamless rental experiences across all locations.'
});

const navigateTo = (path) => {
  router.push(path);
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await fetch('/api/users/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
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
</script>

<style scoped>
/* CSS Variables */
.update-profile-page {
  --primary-color: #0066FF;
  --primary-hover: #0052CC;
  --bg-main: #F8FAFC;
  --sidebar-bg: #1A222F;
  --sidebar-text: #94A3B8;
  --sidebar-active: #0066FF;
  --sidebar-active-text: #FFFFFF;
  --input-bg: #E0E7FF;
  --input-border: #C7D2FE;
  --card-bg: #FFFFFF;
  --text-primary: #1E293B;
  --text-secondary: #64748B;
  --text-muted: #94A3B8;
  --notification-bg: #F5F3FF;
  --notification-icon-bg: #8B5CF6;
  --danger-color: #EF4444;
  --success-color: #10B981;
  --shadow-soft: 0 1px 3px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.1);
  --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
  --radius-card: 12px;
  --radius-input: 8px;
}

.update-profile-page {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg-main);
}

/* Sidebar */
.sidebar {
  width: 220px;
  background: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.brand-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, var(--primary-color) 0%, #3B82F6 100%);
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.brand-icon svg {
  width: 18px;
  height: 18px;
}

.brand-text {
  display: flex;
  flex-direction: column;
}

.brand-title {
  font-size: 1rem;
  font-weight: 700;
  color: white;
  letter-spacing: -0.025em;
}

.brand-subtitle {
  font-size: 11px;
  color: var(--sidebar-text);
}

.sidebar-nav {
  flex: 1;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.2s ease;
  margin: 0 4px;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-item.active {
  background: var(--sidebar-active);
  color: var(--sidebar-active-text);
}

.nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 12px 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-footer .nav-item.logout {
  color: var(--sidebar-text);
}

.sidebar-footer .nav-item.logout:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #EF4444;
}

/* Main Content */
.main-content {
  flex: 1;
  margin-left: 220px;
  display: flex;
  flex-direction: column;
}

/* Top Header */
.top-header {
  height: 64px;
  background: white;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 32px;
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon-btn {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: background 0.2s ease;
}

.header-icon-btn:hover {
  background: var(--bg-main);
}

.header-icon-btn svg {
  width: 20px;
  height: 20px;
  color: var(--text-secondary);
}

.notification-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 16px;
  height: 16px;
  background: var(--danger-color);
  color: white;
  font-size: 10px;
  font-weight: 600;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.header-divider {
  width: 1px;
  height: 24px;
  background: #E2E8F0;
}

.top-header .page-title {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
}

.header-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
}

.header-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Page Content */
.page-content {
  padding: 32px;
  flex: 1;
}

.page-header {
  margin-bottom: 32px;
}

.page-title-main {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 8px 0;
}

.page-subtitle {
  font-size: 15px;
  color: var(--text-secondary);
  margin: 0;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 24px;
  margin-bottom: 24px;
}

/* Profile Photo Card */
.profile-photo-card {
  background: var(--card-bg);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.profile-photo-container {
  margin-bottom: 16px;
}

.profile-photo-placeholder {
  width: 160px;
  height: 160px;
  border-radius: 50%;
  background: linear-gradient(135deg, #E8D5C4 0%, #D4C4B0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 4px solid white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-photo-placeholder svg {
  width: 64px;
  height: 64px;
  color: #8B7355;
}

.profile-photo-label {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 4px;
}

.profile-photo-hint {
  font-size: 13px;
  color: var(--text-muted);
  margin-bottom: 24px;
}

.btn-primary {
  width: 100%;
  padding: 12px 20px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--radius-input);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  margin-bottom: 12px;
}

.btn-primary:hover {
  background: var(--primary-hover);
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

.btn-remove {
  width: 100%;
  padding: 10px 20px;
  background: transparent;
  color: var(--danger-color);
  border: 1px solid transparent;
  border-radius: var(--radius-input);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-remove:hover {
  background: rgba(239, 68, 68, 0.08);
  border-color: var(--danger-color);
}

.btn-remove svg {
  width: 16px;
  height: 16px;
}

.security-status {
  margin-top: 24px;
  padding: 16px;
  background: var(--bg-main);
  border-radius: var(--radius-input);
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.shield-icon {
  width: 24px;
  height: 24px;
  color: var(--primary-color);
}

.security-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.security-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary);
}

.security-date {
  font-size: 12px;
  color: var(--text-muted);
}

/* Personal Info Card */
.personal-info-card {
  background: var(--card-bg);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  padding: 32px;
}

.card-header {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 24px 0;
  padding-bottom: 16px;
  border-bottom: 1px solid #E2E8F0;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group.full-width {
  grid-column: 1 / -1;
  margin-top: 4px;
}

.form-group label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary);
}

.form-group input,
.form-group textarea {
  padding: 12px 16px;
  background: var(--input-bg);
  border: 1px solid var(--input-border);
  border-radius: var(--radius-input);
  font-size: 14px;
  color: var(--text-primary);
  font-family: inherit;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: var(--text-muted);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.input-with-icon {
  position: relative;
}

.input-with-icon .input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: var(--text-muted);
}

.input-with-icon input {
  padding-left: 42px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #E2E8F0;
}

.btn-cancel {
  padding: 12px 24px;
  background: transparent;
  color: var(--text-secondary);
  border: none;
  border-radius: var(--radius-input);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: var(--bg-main);
  color: var(--text-primary);
}

.btn-save {
  padding: 12px 24px;
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--radius-input);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-save:hover {
  background: var(--primary-hover);
}

.btn-save svg {
  width: 18px;
  height: 18px;
}

/* Notifications Card */
.notifications-card {
  background: var(--card-bg);
  border-radius: var(--radius-card);
  box-shadow: var(--shadow-card);
  padding: 24px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.notification-icon-box {
  width: 52px;
  height: 52px;
  background: var(--notification-bg);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-icon-box svg {
  width: 26px;
  height: 26px;
  color: var(--notification-icon-bg);
}

.notification-text h3 {
  font-size: 16px;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0 0 4px 0;
}

.notification-text p {
  font-size: 14px;
  color: var(--text-secondary);
  margin: 0;
}

/* Toggle Switch */
.toggle-switch {
  width: 56px;
  height: 30px;
  background: #CBD5E1;
  border-radius: 15px;
  position: relative;
  cursor: pointer;
  transition: all 0.3s ease;
}

.toggle-switch.on {
  background: var(--primary-color);
}

.toggle-slider {
  position: absolute;
  top: 3px;
  left: 3px;
  width: 24px;
  height: 24px;
  background: white;
  border-radius: 50%;
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.toggle-switch.on .toggle-slider {
  left: 29px;
}

/* Responsive */
@media (max-width: 1200px) {
  .content-grid {
    grid-template-columns: 280px 1fr;
  }
}

@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .profile-photo-card {
    flex-direction: row;
    flex-wrap: wrap;
    text-align: left;
    gap: 20px;
  }
  
  .profile-photo-container {
    margin-bottom: 0;
  }
  
  .profile-photo-placeholder {
    width: 120px;
    height: 120px;
  }
  
  .profile-photo-placeholder svg {
    width: 48px;
    height: 48px;
  }
  
  .profile-photo-label,
  .profile-photo-hint {
    margin-bottom: 0;
  }
  
  .btn-primary,
  .btn-remove {
    width: auto;
    flex: 1;
  }
  
  .security-status {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .page-content {
    padding: 20px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .notifications-card {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .notification-content {
    flex-direction: column;
  }
}
</style>
