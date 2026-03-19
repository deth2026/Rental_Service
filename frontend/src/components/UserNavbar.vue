<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { userService } from '@/services/database.js'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const defaultNavItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'View Details', route: '#', fallbackMessage: 'My Bookings page is not available yet.' },
  { label: 'Promotions', route: '/promotions' }
]

const props = defineProps({
  navItems: Array,
  activeLabel: {
    type: String,
    default: ''
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
  return matchedItem?.label || resolvedNavItems.value[0]?.label || 'Home'
})

const userDisplayName = computed(() => userService.getCurrentUser()?.name || 'Guest User')

const notify = (message) => {
  window.alert(message)
}

const handleNavClick = (item) => {
  const hasRoute = item.route && item.route !== '#'
  if (hasRoute) {
    router.push(item.route)
  } else if (props.showFallbackMessage) {
    notify(item.fallbackMessage || 'My Bookings page is not available yet.')
  }

  emit('nav-click', item)
}

const openProfile = () => {
  router.push('/user/profile')
}

const requestLogout = () => {
  emit('logout-request')
}
</script>

<template>
  <header class="topbar">
    <div class="brand">
      <div class="brand-icon"><i class="fa-solid fa-gift" aria-hidden="true"></i></div>
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
      <span class="user-display-name">{{ userDisplayName }}</span>
      <UserProfileMenu @settings="openProfile" @logout="requestLogout" />
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 30;
  width: 110%;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  background: rgba(255, 255, 255, 0.95);
  border-bottom: 1px solid rgba(148, 163, 184, 0.2);
  backdrop-filter: blur(14px);
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.2rem;
  font-weight: 800;
  margin-left: -30px;
}

.brand-icon {
  display: flex;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #fff;
  background: #dbeafe;
}

.brand-icon i {
  color: #2563eb;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 12px;
}

.nav-link {
  padding: 10px 16px;
  border-radius: 999px;
  color: #4b5563;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.nav-link:hover,
.nav-link.active {
  background: #e8f0ff;
  color: #165df5;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-display-name {
  font-weight: 600;
  color: #334155;
}

@media (max-width: 1100px) {
  .topbar {
    padding: 16px 20px;
  }

  .nav-links {
    gap: 8px;
  }

  .brand {
    font-size: 1rem;
  }
}

@media (max-width: 820px) {
  .topbar {
    flex-wrap: wrap;
    justify-content: center;
  }

  .nav-links {
    order: 3;
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
  }

  .top-actions {
    order: 2;
  }
}
</style>
