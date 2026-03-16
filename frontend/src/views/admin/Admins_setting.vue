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
          <span class="brand-subtitle">{{ $t('rentalSystem') }}</span>
        </div>
      </div>
      
      <nav class="sidebar-nav">
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
          </span>
          <span>{{ $t('dashboard') }}</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </span>
          <span>{{ $t('shopManagement') }}</span>
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
          <span>{{ $t('userManagement') }}</span>
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
          <span>{{ $t('vehicleManagement') }}</span>
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
          <span>{{ $t('bookingManagement') }}</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
              <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
              <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/>
            </svg>
          </span>
          <span>{{ $t('coupons') }}</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
          </span>
          <span>{{ $t('categories') }}</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </span>
          <span>{{ $t('cities') }}</span>
        </a>
        <a href="#" class="nav-item">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="1" x2="12" y2="23"/>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
          </span>
          <span>{{ $t('financials') }}</span>
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
          <span>{{ $t('reports') }}</span>
        </a>
        <a href="#" class="nav-item active">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
          </span>
          <span>{{ $t('setting') }}</span>
        </a>
      </nav>
      
      <div class="sidebar-footer">
        <a href="#" class="nav-item logout">
          <span class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </span>
          <span>{{ $t('logout') }}</span>
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
              <span>AD</span>
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
        <!-- Page Header & Title -->
        <div class="page-header">
          <h1 class="page-title">{{ $t('adminSettings') }}</h1>
        </div>

        <!-- Tab Navigation -->
        <div class="tabs">
        </div>

        <!-- Settings Grid -->
        <div class="settings-grid">
          <!-- Profile Summary Card -->
          <div class="card profile-card">
            <div class="profile-avatar-large">
              <span>AD</span>
            </div>
            <h3 class="profile-name">Admin User</h3>
            <p class="profile-email">admin@chongchoul.com</p>
            <button class="btn-primary" @click="goToUpdateProfile">{{ $t('updateProfile') }}</button>
            <div class="profile-stats">
              <div class="stat-item">
                <span class="stat-value">12</span>
                <span class="stat-label">{{ $t('totalShops') }}</span>
              </div>
              <div class="stat-divider"></div>
              <div class="stat-item">
                <span class="stat-value">4.9</span>
                <span class="stat-label">{{ $t('rating') }}</span>
              </div>
            </div>
          </div>

          <!-- Right Column Cards -->
          <div class="right-column">
            <!-- General Preferences -->
            <div class="card preferences-card">
              <h3 class="card-title">{{ $t('generalPreferences') }}</h3>
              <div class="preference-section">
                <label class="preference-label">{{ $t('language') }}</label>
                <div class="toggle-group">
                  <button 
                    class="toggle-btn" 
                    :class="{ active: activeLanguage === 'English' }"
                    @click="handleLanguageChange('English')"
                  >English</button>
                  <button 
                    class="toggle-btn" 
                    :class="{ active: activeLanguage === 'Khmer' }"
                    @click="handleLanguageChange('Khmer')"
                  >Khmer</button>
                </div>
              </div>
              <div class="preference-section">
                <label class="preference-label">{{ $t('displayTheme') }}</label>
                <div class="toggle-group">
                  <button 
                    class="toggle-btn" 
                    :class="{ active: activeTheme === 'Light' }"
                    @click="setTheme('Light')"
                  >{{ $t('light') }}</button>
                  <button 
                    class="toggle-btn" 
                    :class="{ active: activeTheme === 'Dark' }"
                    @click="setTheme('Dark')"
                  >{{ $t('dark') }}</button>
                </div>
              </div>
            </div>

            <!-- Security Settings -->
            <div class="card security-card">
              <div class="security-header">
                <div class="security-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </div>
                <span class="badge-recommended">{{ $t('recommended') }}</span>
              </div>
              <h3 class="card-title">{{ $t('securitySettings') }}</h3>
              <div class="security-row">
                <div class="security-info">
                  <span class="security-title">{{ $t('twoFactorAuthentication') }}</span>
                  <span class="security-desc">{{ $t('twoFactorDescription') }}</span>
                </div>
                <label class="toggle-switch">
                  <input type="checkbox" checked />
                  <span class="toggle-slider"></span>
                </label>
              </div>
            </div>

            <!-- System Roles -->
            <div class="card roles-card">
              <h3 class="card-title">{{ $t('systemRoles') }}</h3>
              <div class="roles-list">
                <div class="role-item">
                  <span class="role-name">{{ $t('admin') }}</span>
                  <span class="role-badge admin">03 {{ $t('users') }}</span>
                </div>
                <div class="role-item">
                  <span class="role-name">{{ $t('shopOwner') }}</span>
                  <span class="role-badge shop">45 {{ $t('users') }}</span>
                </div>
                <div class="role-item">
                  <span class="role-name">{{ $t('customer') }}</span>
                  <span class="role-badge customer">2,434 {{ $t('users') }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Status Dashboard -->
        <div class="status-dashboard">
          <!-- Last Login -->
          <div class="card status-card">
            <div class="status-icon last-login">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
              </svg>
            </div>
            <div class="status-content">
              <span class="status-label">{{ $t('lastLogin') }}</span>
              <span class="status-value">{{ currentDateTime }}</span>
              <span class="status-detail">192.168.1.105</span>
            </div>
          </div>

          <!-- Active Sessions -->
          <div class="card status-card">
            <div class="status-icon sessions">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
              </svg>
            </div>
            <div class="status-content">
              <span class="status-label">{{ $t('activeSessions') }}</span>
              <span class="status-value">01 {{ $t('device') }}</span>
              <span class="status-detail">Windows</span>
            </div>
          </div>

          <!-- User Usage -->
          <div class="card status-card">
            <div class="status-icon storage">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <div class="status-content">
              <span class="status-label">{{ $t('userUsage') }}</span>
              <span class="status-value">{{ userUsagePercent }}% {{ $t('used') }}</span>
              <div class="progress-container">
                <div class="progress-bar" :style="{ width: userUsagePercent + '%' }"></div>
              </div>
              <span class="status-detail">{{ userUsagePercent }}%</span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { setLanguage } from '@/i18n'

const { locale } = useI18n()
const router = useRouter()
const route = useRoute()

const activeTab = ref('profile')
const activeLanguage = ref('English')
const activeTheme = ref('Light')
const currentDateTime = ref('')
const userUsagePercent = ref(0)

// Get current date and time in Cambodia timezone (UTC+7)
const getCurrentDateTime = () => {
  const now = new Date()
  // Cambodia is UTC+7
  const cambodiaTime = new Date(now.getTime() + 7 * 60 * 60 * 1000)
  
  const dateOptions = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  }
  
  const timeOptions = { 
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  }
  
  const date = cambodiaTime.toLocaleDateString('en-US', dateOptions)
  const time = cambodiaTime.toLocaleTimeString('en-US', timeOptions)
  
  return `${date} ${time}`
}

// Check route path to determine initial language and set i18n
// Set initial language based on route
const setInitialLanguage = () => {
  if (route.path.includes('-kh')) {
    activeLanguage.value = 'Khmer'
    locale.value = 'kh'
    localStorage.setItem('app_language', 'kh')
  } else {
    activeLanguage.value = 'English'
    locale.value = 'en'
    localStorage.setItem('app_language', 'en')
  }
}

onMounted(() => {
  setInitialLanguage()
  currentDateTime.value = getCurrentDateTime()
})

// Watch for route changes
watch(() => route.path, () => {
  setInitialLanguage()
})

const goToUpdateProfile = () => {
  router.push('/admin/profile')
}

const menuItems = [
  { id: 'dashboard', label: 'Dashboard', icon: 'grid' },
  { id: 'shop', label: 'Shop Management', icon: 'home' },
  { id: 'users', label: 'User Management', icon: 'users' },
  { id: 'vehicles', label: 'Vehicle Management', icon: 'car' },
  { id: 'bookings', label: 'Booking Management', icon: 'calendar' },
  { id: 'coupons', label: 'Coupons', icon: 'tag' },
  { id: 'categories', label: 'Categories', icon: 'folder' },
  { id: 'cities', label: 'Cities', icon: 'map-pin' },
  { id: 'financials', label: 'Financials', icon: 'dollar' },
  { id: 'reports', label: 'Reports', icon: 'file-text' },
  { id: 'settings', label: 'Settings', icon: 'settings', active: true }
]

const handleLanguageChange = (lang) => {
  activeLanguage.value = lang
  // Use 'en' or 'kh' for the i18n function
  const i18nLang = lang === 'English' ? 'en' : 'kh'
  
  // Set language in localStorage and i18n
  locale.value = i18nLang
  localStorage.setItem('app_language', i18nLang)
  
  // Navigate to the appropriate language route
  if (lang === 'Khmer') {
    router.push('/admin/settings-kh')
  } else {
    router.push('/admin/settings')
  }
}

const setTheme = (theme) => {
  activeTheme.value = theme
  // Store theme preference
  localStorage.setItem('admin_theme', theme)
  
  // Apply theme to the document
  if (theme === 'Dark') {
    document.documentElement.classList.add('dark-theme')
  } else {
    document.documentElement.classList.remove('dark-theme')
  }
}

const logout = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user')
  router.push('/login')
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

:root {
  --primary: #0066FF;
  --primary-hover: #0052CC;
  --background: #F8FAFC;
  --sidebar-bg: #1A222F;
  --sidebar-text: #94A3B8;
  --sidebar-active: #0066FF;
  --sidebar-active-text: #FFFFFF;
  --card-bg: #FFFFFF;
  --text-primary: #1E293B;
  --text-secondary: #64748B;
  --text-muted: #94A3B8;
  --border-color: #E2E8F0;
  --success: #10B981;
  --warning: #F59E0B;
  --danger: #EF4444;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --spacing-xs: 0.25rem;
  --spacing-sm: 0.5rem;
  --spacing-md: 1rem;
  --spacing-lg: 1.5rem;
  --spacing-xl: 2rem;
  --spacing-2xl: 3rem;
  --radius-sm: 6px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-full: 9999px;
  --transition-fast: 150ms ease;
  --transition-normal: 250ms ease;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--background);
}

.sidebar {
  width: 220px;
  min-height: 100vh;
  background: var(--sidebar-bg);
  display: flex;
  flex-direction: column;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 100;
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
  background: linear-gradient(135deg, var(--primary) 0%, #3B82F6 100%);
  border-radius: var(--radius-sm);
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
  font-size: 1rem;
  font-weight: 700;
  color: white;
  letter-spacing: -0.025em;
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
  margin: 0 4px;
  color: var(--sidebar-text);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  border-radius: 8px;
  transition: all var(--transition-fast);
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #FFFFFF;
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
  flex-shrink: 0;
}

.nav-icon svg {
  width: 18px;
  height: 18px;
}

.sidebar-footer {
  padding: 12px 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.nav-item.logout {
  color: var(--sidebar-text);
}

.nav-item.logout:hover {
  background: rgba(239, 68, 68, 0.15);
  color: var(--danger);
}

.main-content {
  flex: 1;
  margin-left: 220px;
  display: flex;
  flex-direction: column;
}

.top-header {
  height: 60px;
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  position: sticky;
  top: 0;
  z-index: 50;
}

.search-box {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  background: var(--background);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: var(--spacing-sm) var(--spacing-lg);
  width: 350px;
  margin-left: 20px;
  transition: all var(--transition-fast);
}

.search-box:focus-within {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
}

.search-icon {
  color: var(--text-muted);
}

.search-icon svg {
  width: 18px;
  height: 18px;
}

.search-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  color: var(--text-primary);
  outline: none;
}

.search-input::placeholder {
  color: var(--text-muted);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
  margin-left: auto;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: var(--background);
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-secondary);
  transition: all var(--transition-fast);
  position: relative;
}

.icon-btn:hover {
  background: var(--border-color);
  color: var(--text-primary);
}

.icon-btn svg {
  width: 20px;
  height: 20px;
}

.notification-badge {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 16px;
  height: 16px;
  background: var(--danger);
  color: white;
  font-size: 0.625rem;
  font-weight: 600;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-sm);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  margin-right: 32px;
}

.user-profile:hover {
  background: var(--background);
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, var(--primary) 0%, #3B82F6 100%);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.user-role {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.page-content {
  padding: 16px 24px;
  flex: 1;
}

.page-header {
  margin-bottom: 16px;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  margin-bottom: 8px;
}

.breadcrumb {
  color: var(--text-muted);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color var(--transition-fast);
}

.breadcrumb:hover {
  color: var(--primary);
}

.breadcrumb-separator {
  color: var(--text-muted);
  font-size: 0.875rem;
}

.breadcrumb-current {
  color: var(--text-secondary);
  font-size: 0.875rem;
  font-weight: 500;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  letter-spacing: -0.025em;
}

.tabs {
  display: flex;
  gap: 16px;
  border-bottom: 1px solid var(--border-color);
  margin-bottom: 16px;
}

.tab {
  padding: 8px 0;
  background: none;
  border: none;
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  position: relative;
  transition: color var(--transition-fast);
}

.tab:hover {
  color: var(--text-primary);
}

.tab.active {
  color: var(--primary);
}

.tab.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--primary);
  border-radius: 2px 2px 0 0;
}

.card {
  background: var(--card-bg);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  padding: 16px;
  transition: box-shadow var(--transition-normal);
}

.card:hover {
  box-shadow: var(--shadow-lg);
}

.card-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 12px;
}

.settings-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.profile-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.profile-avatar-large {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, var(--primary) 0%, #3B82F6 100%);
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 12px;
  box-shadow: 0 8px 16px rgba(0, 102, 255, 0.25);
}

.profile-name {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 2px;
}

.profile-email {
  font-size: 0.8125rem;
  color: var(--text-muted);
  margin-bottom: 12px;
}

.btn-primary {
  width: 100%;
  padding: var(--spacing-md) var(--spacing-lg);
  background: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.btn-primary:hover {
  background: var(--primary-hover);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 102, 255, 0.3);
}

.profile-stats {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--border-color);
  width: 100%;
}

.stat-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
}

.stat-label {
  font-size: 0.6875rem;
  color: var(--text-muted);
  margin-top: 2px;
}

.stat-divider {
  width: 1px;
  height: 30px;
  background: var(--border-color);
}

.right-column {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.preferences-card {
  grid-column: span 2;
}

.preference-section {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-md) 0;
  border-bottom: 1px solid var(--border-color);
}

.preference-section:last-child {
  border-bottom: none;
}

.preference-label {
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--text-primary);
}

.toggle-group {
  display: flex;
  background: var(--background);
  border-radius: var(--radius-sm);
  padding: 3px;
}

.toggle-btn {
  padding: var(--spacing-sm) var(--spacing-lg);
  background: transparent;
  border: none;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.toggle-btn:hover {
  color: var(--text-primary);
}

.toggle-btn.active {
  background: var(--primary);
  color: white;
  box-shadow: var(--shadow-sm);
}

.security-card {
  grid-column: span 2;
}

.security-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: var(--spacing-lg);
}

.security-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--success);
}

.security-icon svg {
  width: 24px;
  height: 24px;
}

.badge-recommended {
  padding: var(--spacing-xs) var(--spacing-md);
  background: rgba(16, 185, 129, 0.1);
  color: var(--success);
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: var(--radius-full);
}

.security-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-lg);
  background: var(--background);
  border-radius: var(--radius-md);
}

.security-info {
  display: flex;
  flex-direction: column;
}

.security-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: var(--spacing-xs);
}

.security-desc {
  font-size: 0.8125rem;
  color: var(--text-muted);
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--border-color);
  border-radius: var(--radius-full);
  transition: all var(--transition-fast);
}

.toggle-slider::before {
  position: absolute;
  content: '';
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background: white;
  border-radius: var(--radius-full);
  transition: all var(--transition-fast);
  box-shadow: var(--shadow-sm);
}

.toggle-switch input:checked + .toggle-slider {
  background: var(--primary);
}

.toggle-switch input:checked + .toggle-slider::before {
  transform: translateX(22px);
}

.roles-card {
  grid-column: span 2;
}

.roles-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.role-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--spacing-md) var(--spacing-lg);
  background: var(--background);
  border-radius: var(--radius-sm);
  transition: all var(--transition-fast);
}

.role-item:hover {
  background: var(--border-color);
}

.role-name {
  font-size: 0.9375rem;
  font-weight: 500;
  color: var(--text-primary);
}

.role-badge {
  padding: var(--spacing-xs) var(--spacing-md);
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: var(--radius-full);
}

.role-badge.admin {
  background: rgba(0, 102, 255, 0.1);
  color: var(--primary);
}

.role-badge.shop {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning);
}

.role-badge.customer {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.status-dashboard {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}

.status-card {
  display: flex;
  align-items: flex-start;
  gap: var(--spacing-lg);
}

.status-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.status-icon svg {
  width: 24px;
  height: 24px;
}

.status-icon.last-login {
  background: rgba(0, 102, 255, 0.1);
  color: var(--primary);
}

.status-icon.sessions {
  background: rgba(16, 185, 129, 0.1);
  color: var(--success);
}

.status-icon.storage {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning);
}

.status-content {
  display: flex;
  flex-direction: column;
  flex: 1;
}

.status-label {
  font-size: 0.8125rem;
  color: var(--text-muted);
  margin-bottom: var(--spacing-xs);
}

.status-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: var(--spacing-xs);
}

.status-detail {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.progress-container {
  width: 100%;
  height: 6px;
  background: var(--border-color);
  border-radius: var(--radius-full);
  margin: var(--spacing-sm) 0;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  border-radius: var(--radius-full);
  transition: width var(--transition-normal);
}

.progress-bar.warning {
  background: linear-gradient(90deg, var(--warning) 0%, #FCD34D 100%);
}

@media (max-width: 1280px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
  
  .right-column {
    grid-template-columns: 1fr;
  }
  
  .preferences-card,
  .security-card,
  .roles-card {
    grid-column: span 1;
  }
  
  .status-dashboard {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1024px) {
  .sidebar {
    width: 70px;
  }
  
  .sidebar-brand {
    padding: 12px 8px;
    justify-content: center;
  }
  
  .brand-text {
    display: none;
  }
  
  .nav-item span:not(.nav-icon) {
    display: none;
  }
  
  .nav-item {
    justify-content: center;
    padding: 8px;
  }
  
  .main-content {
    margin-left: 70px;
  }
  
  .search-box {
    width: 250px;
  }
}

@media (max-width: 768px) {
  .top-header {
    padding: 0 var(--spacing-lg);
  }
  
  .search-box {
    width: 200px;
  }
  
  .user-info {
    display: none;
  }
  
  .page-content {
    padding: var(--spacing-lg);
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .profile-stats {
    flex-direction: column;
    gap: var(--spacing-md);
  }
  
  .stat-divider {
    width: 40px;
    height: 1px;
  }
}

@media (max-width: 480px) {
  .sidebar {
    display: none;
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .search-box {
    flex: 1;
    min-width: 0;
  }
  
  .header-actions {
    gap: var(--spacing-sm);
  }
  
  .page-title {
    font-size: 1.25rem;
  }
  
  .tabs {
    gap: var(--spacing-md);
  }
  
  .tab {
    font-size: 0.8125rem;
  }
}

/* Dark Theme Styles */
.dark-theme {
  --background: #0F172A;
  --sidebar-bg: #0D1321;
  --card-bg: #1E293B;
  --text-primary: #F1F5F9;
  --text-secondary: #94A3B8;
  --text-muted: #64748B;
  --border-color: #334155;
}

.dark-theme .admin-layout {
  background: var(--background);
}

.dark-theme .sidebar {
  background: var(--sidebar-bg);
}

.dark-theme .top-header {
  background: var(--card-bg);
  border-bottom-color: var(--border-color);
}

.dark-theme .search-box {
  background: #334155;
  border-color: var(--border-color);
}

.dark-theme .search-input {
  color: var(--text-primary);
}

.dark-theme .icon-btn {
  background: #334155;
  color: var(--text-secondary);
}

.dark-theme .icon-btn:hover {
  background: #475569;
  color: var(--text-primary);
}

.dark-theme .user-profile:hover {
  background: #334155;
}

.dark-theme .user-name {
  color: var(--text-primary);
}

.dark-theme .page-title {
  color: var(--text-primary);
}

.dark-theme .tabs {
  border-bottom-color: var(--border-color);
}

.dark-theme .tab {
  color: var(--text-secondary);
}

.dark-theme .tab:hover {
  color: var(--text-primary);
}

.dark-theme .card {
  background: var(--card-bg);
}

.dark-theme .card-title {
  color: var(--text-primary);
}

.dark-theme .preference-label {
  color: var(--text-primary);
}

.dark-theme .toggle-group {
  background: #334155;
}

.dark-theme .toggle-btn {
  color: var(--text-secondary);
}

.dark-theme .toggle-btn:hover {
  color: var(--text-primary);
}

.dark-theme .security-row {
  background: #334155;
}

.dark-theme .security-title {
  color: var(--text-primary);
}

.dark-theme .role-item {
  background: #334155;
}

.dark-theme .role-item:hover {
  background: #475569;
}

.dark-theme .role-name {
  color: var(--text-primary);
}

.dark-theme .status-value {
  color: var(--text-primary);
}

.dark-theme .breadcrumb {
  color: var(--text-muted);
}

.dark-theme .breadcrumb-current {
  color: var(--text-secondary);
}

.dark-theme .profile-name {
  color: var(--text-primary);
}

.dark-theme .stat-value {
  color: var(--text-primary);
}
</style>
