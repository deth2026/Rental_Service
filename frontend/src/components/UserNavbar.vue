<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { userService } from '@/services/database.js'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import NotificationMenu from '@/components/NotificationMenu.vue'
import { useNotifications } from '@/composables/useNotifications'

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

const { unreadCount, loadNotifications } = useNotifications()
const showNotifications = ref(false)
const notificationRoot = ref(null)
const badgeDismissed = ref(false)
const previousUnread = ref(unreadCount.value)

const badgeLabel = computed(() => {
  if (badgeDismissed.value) return ''
  if (!unreadCount.value) return ''
  return unreadCount.value > 9 ? '9+' : String(unreadCount.value)
})

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) {
    badgeDismissed.value = true
  }
}

const closeNotifications = () => {
  showNotifications.value = false
}

const handleDocumentClick = (event) => {
  if (showNotifications.value && notificationRoot.value && !notificationRoot.value.contains(event.target)) {
    closeNotifications()
  }
}

onMounted(() => {
  loadNotifications()
  document.addEventListener('mousedown', handleDocumentClick)
})

watch(unreadCount, (value) => {
  if (value > previousUnread.value) {
    badgeDismissed.value = false
  }
  previousUnread.value = value
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleDocumentClick)
})

watch(
  () => route.fullPath,
  () => {
    closeNotifications()
  }
)

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
      <div class="notification-wrapper" ref="notificationRoot">
        <button
          type="button"
          class="icon-btn notification-btn"
          aria-label="Open notifications"
          @click.stop="toggleNotifications"
        >
          <i class="fa-regular fa-bell" aria-hidden="true"></i>
          <span v-if="badgeLabel" class="notification-count">{{ badgeLabel }}</span>
        </button>
        <transition name="notification-fade">
          <div v-if="showNotifications" class="notification-popup">
            <NotificationMenu />
          </div>
        </transition>
      </div>
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

.notification-wrapper {
  position: relative;
}

.notification-btn {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  border: none;
  background: linear-gradient(135deg, #6366f1, #a855f7);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  cursor: pointer;
  position: relative;
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.notification-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(99, 102, 241, 0.45);
}

.notification-count {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 20px;
  padding: 2px 4px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.notification-popup {
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  z-index: 40;
}

.notification-fade-enter-active,
.notification-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.notification-fade-enter-from,
.notification-fade-leave-to {
  opacity: 0;
  transform: translateY(6px);
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
