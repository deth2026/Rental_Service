<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { userService } from '@/services/database.js'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import NotificationMenu from '@/components/NotificationMenu.vue'
import { useNotifications } from '@/composables/useNotifications'

const defaultNavItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Booking', route: '/my-bookings'},
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
  width: 100%;
  height: 74px;
  padding: 0 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  background: #ffffff;
  border-bottom: 1px solid #e4e7f0;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.12);
  position: sticky;
  top: 0;
  z-index: 40;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1d4ed8;
}

.brand span {
  color: #1d4ed8;
}

.brand-icon {
  width: 46px;
  height: 46px;
  border-radius: 16px;
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  display: grid;
  place-items: center;
  box-shadow: inset 0 0 0 1px rgba(224, 229, 255, 0.9);
}

.brand-icon i {
  color: #2563eb;
  font-size: 1.1rem;
}

.nav-links {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
}

.nav-link {
  padding: 10px 26px;
  border-radius: 999px;
  border: 1px solid transparent;
  color: #475569;
  font-size: 0.95rem;
  font-weight: 600;
  background: transparent;
  cursor: pointer;
  transition: all 0.25s ease;
}

.nav-link:hover {
  background: #eef2ff;
  color: #1d4ed8;
}

.nav-link.active {
  background: #dbeafe;
  color: #1d4ed8;
  border-color: #c7d2fe;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.18);
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 14px;
}

.notification-wrapper {
  position: relative;
}

.notification-btn {
  width: 48px;
  height: 48px;
  border-radius: 24px;
  border: none;
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  color: #fff;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 12px 30px rgba(124, 58, 237, 0.35);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.notification-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 30px rgba(124, 58, 237, 0.45);
}

.notification-count {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  padding: 1px 5px;
  border-radius: 999px;
  background: #ef4444;
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
}

.user-display-name {
  font-weight: 600;
  color: #334155;
}

@media (max-width: 1100px) {
  .topbar {
    padding: 0 20px;
  }

  .nav-links {
    gap: 12px;
  }
}

@media (max-width: 820px) {
  .topbar {
    flex-wrap: wrap;
    justify-content: center;
    height: auto;
    padding: 12px 20px;
  }

  .nav-links {
    width: 100%;
    order: 3;
    flex-wrap: wrap;
    justify-content: center;
  }

  .top-actions {
    order: 2;
    margin-top: 6px;
  }

  .brand {
    order: 1;
    width: 100%;
    justify-content: center;
  }
}
</style>
