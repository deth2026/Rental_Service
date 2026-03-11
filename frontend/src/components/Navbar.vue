<template>
  <nav>
    <Logo />
    <header class="navbar">
      <nav class="nav-links">
        <RouterLink to="/" class="nav-link">{{ t('home') }}</RouterLink>
        <RouterLink to="/login" class="nav-link">{{ t('login') }}</RouterLink>
        <RouterLink to="/register" class="nav-link">{{ t('register') }}</RouterLink>
        <RouterLink to="/dashboard" class="nav-link">{{ t('dashboard') }}</RouterLink>
        
        <!-- Profile Dropdown -->
        <div class="profile-dropdown" @mouseenter="showDropdown = true" @mouseleave="showDropdown = false">
          <RouterLink to="/setting" class="nav-link profile-link">
            <span class="profile-icon">
              <img v-if="userAvatar" :src="userAvatar" alt="Profile" class="profile-avatar-img" />
              <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <span class="profile-text">{{ t('profile') }}</span>
            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </RouterLink>
          
          <!-- Dropdown Menu -->
          <transition name="dropdown-fade">
            <div v-if="showDropdown" class="dropdown-menu">
              <RouterLink to="/setting" class="dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>{{ t('profile') }}</span>
              </RouterLink>
              <RouterLink to="/setting" class="dropdown-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3"/>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span>{{ t('settings') }}</span>
              </RouterLink>
              <button @click="handleLogout" class="dropdown-item dropdown-item--logout">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                  <polyline points="16 17 21 12 16 7"/>
                  <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>{{ t('logout') }}</span>
              </button>
            </div>
          </transition>
        </div>
      </nav>
    </header>
  </nav>
</template>

<script setup>
import Logo from '@/components/Logo.vue';
import { RouterLink, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { ref, computed, onMounted } from 'vue';
import userService from '@/services/userService.js';

const { t } = useI18n();
const router = useRouter();
const showDropdown = ref(false);

// Get user avatar
const userAvatar = computed(() => {
    try {
        const user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user.avatar_url || user.profile_picture || user.img_url) {
            return userService.getAvatarUrl(user.avatar_url || user.profile_picture || user.img_url);
        }
    } catch (e) {}
    return null;
});

function handleLogout() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    window.dispatchEvent(new Event('user-logged-out'));
    router.push('/login');
}

onMounted(() => {
    // Listen for user updates
    window.addEventListener('user-updated', () => {
        // Force re-render to update avatar
        showDropdown.value = false;
        setTimeout(() => showDropdown.value = false, 0);
    });
});
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-link {
  color: #fff;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.3);
  font-weight: 600;
}

/* Profile Dropdown Styles */
.profile-dropdown {
  position: relative;
}

.profile-link {
  cursor: pointer;
}

.profile-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-avatar-img {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  object-fit: cover;
}

.dropdown-arrow {
  transition: transform 0.3s ease;
}

.profile-dropdown:hover .dropdown-arrow {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  min-width: 180px;
  overflow: hidden;
  z-index: 1000;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.1rem;
  color: #333;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.2s ease;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f1f5f9;
  color: #1d4ed8;
}

.dropdown-item svg {
  flex-shrink: 0;
}

.dropdown-item--logout {
  color: #dc2626;
  border-top: 1px solid #e5e7eb;
}

.dropdown-item--logout:hover {
  background: #fef2f2;
  color: #dc2626;
}

/* Dropdown animation */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
