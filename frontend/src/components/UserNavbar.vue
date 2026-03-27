<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const defaultNavItems = [
  { label: 'My Bookings', route: '/my-bookings' },
  { label: 'Profile', route: '/user/profile' }
]

const props = defineProps({
  navItems: Array,
  activeLabel: {
    type: String,
    default: ''
  },
  showBackButton: {
    type: Boolean,
    default: false
  },
  backTo: {
    type: String,
    default: ''
  },
  backLabel: {
    type: String,
    default: 'Back'
  },
  showFallbackMessage: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['logout-request', 'nav-click'])
const router = useRouter()
const route = useRoute()

const resolvedNavItems = computed(() => {
  if (Array.isArray(props.navItems) && props.navItems.length) {
    return props.navItems
  }
  return defaultNavItems
})

const activeNav = computed(() => {
  if (props.activeLabel) return props.activeLabel

  const currentPath = route.path
  const matchedItem = resolvedNavItems.value.find(
    (item) => item.route && item.route !== '#' && currentPath.startsWith(item.route)
  )
  return matchedItem?.label || resolvedNavItems.value[0]?.label || 'My Bookings'
})

const shouldShowBackButton = computed(() => {
  if (!props.showBackButton) return false
  return !route.path.startsWith('/view_shop')
})

const notify = (message) => {
  window.alert(message)
}

const handleNavClick = (item) => {
  const hasRoute = item.route && item.route !== '#'
  if (hasRoute) {
    router.push(item.route)
  } else if (props.showFallbackMessage) {
    notify(item.fallbackMessage || 'This page is not available yet.')
  }

  emit('nav-click', item)
}

const openProfile = () => {
  router.push('/user/profile')
}

const requestLogout = () => {
  emit('logout-request')
}

const goBack = () => {
  if (props.backTo) {
    router.push(props.backTo)
    return
  }
  router.push('/view_shop')
}
</script>

<template>
  <header class="user-navbar-shell">
    <div class="topbar user-navbar">
      <div class="brand">
        <div class="brand-icon">
          <img src="/images/logo-removebg.png" alt="Chong Choul logo" class="brand-icon-image" />
        </div>
        <span>Chong Choul</span>
      </div>

      <nav class="nav-links">
        <button
          v-for="item in resolvedNavItems"
          :key="item.label"
          class="btn-reset nav-link"
          :class="{ active: activeNav === item.label }"
          @click="handleNavClick(item)"
        >
          {{ item.label }}
        </button>
      </nav>

      <div class="top-actions">
        <UserProfileMenu @settings="openProfile" @logout="requestLogout" />
      </div>
    </div>

    <div v-if="shouldShowBackButton" class="navbar-back-row">
      <button type="button" class="btn-reset navbar-back-btn" @click="goBack">
        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        <span>{{ backLabel }}</span>
      </button>
    </div>
  </header>
</template>

<style scoped>
.user-navbar-shell {
  width: auto;
  margin-inline: calc(50% - 50vw);
  position: sticky;
  top: 0;
  z-index: 220;
  background: #ffffff;
  border-bottom: 1px solid #e2e8f0;
}

.topbar.user-navbar {
  min-height: 80px;
  padding-inline: calc(34px + (50vw - 50%));
  padding-block: 12px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 36px;
  box-sizing: border-box;
}

.topbar.user-navbar .brand {
  display: flex;
  align-items: center;
  gap: 14px;
  font-size: 20px;
  font-weight: 800;
  color: #245bd4;
  white-space: nowrap;
}

.topbar.user-navbar .brand span {
  color: #245bd4;
}

.topbar.user-navbar .brand-icon {
  width: 62px;
  height: 62px;
  border-radius: 0;
  background: transparent;
  display: grid;
  place-items: center;
  overflow: hidden;
}

.topbar.user-navbar .brand-icon-image {
  width: 58px;
  height: 58px;
  object-fit: contain;
  transform: scale(1.14);
  transform-origin: center;
}

.topbar.user-navbar .nav-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 32px;
  justify-self: center;
}

.topbar.user-navbar .nav-link {
  padding: 6px 0;
  border-radius: 0;
  border: 1px solid transparent;
  color: #1f3657;
  font-size: 14px;
  font-weight: 700;
  background: transparent;
  cursor: pointer;
  transition: color 0.2s ease;
}

.topbar.user-navbar .nav-link:hover {
  color: #2563eb;
}

.topbar.user-navbar .nav-link.active {
  color: #2563eb;
}

.topbar.user-navbar .top-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-self: end;
  white-space: nowrap;
}

.navbar-back-row {
  padding-inline: calc(34px + (50vw - 50%));
  padding-bottom: 12px;
  display: flex;
  align-items: center;
}

.navbar-back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 42px;
  padding: 0 18px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1px solid #dbe4ef;
  color: #1e3a8a;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1;
  transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

.navbar-back-btn:hover {
  background: #e7f0ff;
  border-color: #bfd4f5;
  transform: translateY(-1px);
}

.navbar-back-btn i {
  font-size: 0.9rem;
}

@media (max-width: 1080px) {
  .topbar.user-navbar {
    grid-template-columns: 1fr;
    padding-inline: calc(20px + (50vw - 50%));
    gap: 10px;
  }

  .topbar.user-navbar .nav-links {
    justify-self: start;
  }

  .topbar.user-navbar .top-actions {
    justify-self: start;
  }

  .navbar-back-row {
    padding-inline: calc(20px + (50vw - 50%));
  }
}

@media (max-width: 640px) {
  .topbar.user-navbar {
    padding-block: 12px;
    padding-inline: calc(12px + (50vw - 50%));
  }

  .topbar.user-navbar .nav-links {
    flex-wrap: wrap;
    justify-content: flex-start;
  }

  .topbar.user-navbar .top-actions {
    gap: 8px;
  }

  .topbar.user-navbar .brand {
    font-size: 16px;
  }

  .topbar.user-navbar .brand-icon {
    width: 48px;
    height: 48px;
  }

  .topbar.user-navbar .brand-icon-image {
    width: 44px;
    height: 44px;
    transform: scale(1.1);
  }

  .navbar-back-row {
    padding-inline: calc(12px + (50vw - 50%));
    padding-bottom: 10px;
  }

  .navbar-back-btn {
    min-height: 38px;
    padding: 0 14px;
    font-size: 0.95rem;
  }
}
</style>
