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
    notify(item.fallbackMessage || 'My Booking page is not available yet.')
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
  <header class="topbar user-navbar">
    <div class="brand-left">
      <img src="/Images/logo-removebg.png" alt="Chong Choul logo" class="brand-logo" />
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
.topbar.user-navbar {
  width: auto;
  height: 74px;
  margin-inline: calc(50% - 50vw);
  padding-inline: calc(32px + (50vw - 50%));
  padding-block: 14px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 32px;
  background: #ffffff;
  border-bottom: 1px solid #d8dee7;
  position: sticky;
  top: 0;
  z-index: 220;
  box-sizing: border-box;
}

.topbar.user-navbar .brand-left {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 20px;
  font-weight: 700;
  color: #1d4ed8;
  white-space: nowrap;
}

.topbar.user-navbar .brand-left span {
  color: #2563eb;
}

.topbar.user-navbar .brand-logo {
  width: 42px;
  height: 42px;
  object-fit: contain;
  border-radius: 10px;
}

.topbar.user-navbar .nav-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 24px;
  justify-self: center;
}

.topbar.user-navbar .nav-link {
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid transparent;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
  background: transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.topbar.user-navbar .nav-link:hover {
  background: #eef7ff;
  color: #2563eb;
}

.topbar.user-navbar .nav-link.active {
  background: #eef7ff;
  color: #2563eb;
  border-color: transparent;
  box-shadow: none;
}

.topbar.user-navbar .top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-self: end;
  white-space: nowrap;
}

.topbar.user-navbar .notification-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.topbar.user-navbar .notification-btn {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: 1px solid #d8dee7;
  background: #f4f8fc;
  color: #2563eb;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.topbar.user-navbar .notification-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(37, 99, 235, 0.12);
}

.topbar.user-navbar .notification-count {
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

.topbar.user-navbar .notification-popup {
  position: absolute;
  top: calc(100% + 12px);
  right: 0;
  z-index: 260;
}

.topbar.user-navbar .notification-popup::before {
  content: '';
  position: absolute;
  top: -8px;
  right: 18px;
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-left: 1px solid rgba(15, 23, 42, 0.08);
  border-top: 1px solid rgba(15, 23, 42, 0.08);
  transform: rotate(45deg);
}

.notification-fade-enter-active,
.notification-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.notification-fade-enter-from,
.notification-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
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
}

@media (max-width: 640px) {
  .topbar.user-navbar {
    height: auto;
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
    width: 30px;
    height: 30px;
  }

  .topbar.user-navbar .notification-btn {
    width: 36px;
    height: 36px;
  }

  .topbar.user-navbar .notification-popup {
    right: -6px;
  }
}
</style>
