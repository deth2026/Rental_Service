<template>
  <div class="notification-screen">
    <UserNavbar
      :nav-items="navItems"
      :active-label="activeNav"
      :show-fallback-message="false"
      @logout-request="handleLogout"
      @nav-click="setActiveNav"
    />

    <main class="notification-screen__body">
      <section class="notification-shell">
        <header class="notification-shell__header">
          <div>
            <p class="notification-shell__eyebrow">Notifications</p>
            <h1>Notification center</h1>
          </div>
          <div class="notification-shell__meta">
            <span>{{ unreadCount }} unread</span>
          </div>
        </header>

        <div class="notification-shell__filters">
          <button
            v-for="option in filterOptions"
            :key="option.value"
            :class="['filter-pill', { active: activeFilter === option.value }]"
            type="button"
            @click="activeFilter = option.value"
          >
            {{ option.label }}
          </button>
        </div>

        <div class="notification-list">
          <div v-if="isLoading" class="notification-list__state">Loading notifications...</div>
          <div v-else-if="error" class="notification-list__state notification-list__state--error">
            {{ error }}
          </div>
          <template v-else>
            <article
              v-for="item in filteredNotifications"
              :key="item.id"
              :class="['notification-row', { unread: item.status === 'unread' }]"
            >
              <img :src="item.user.avatar" :alt="item.user.name" class="notification-row__avatar" />
              <div class="notification-row__content">
                <div class="notification-row__heading">
                  <p class="notification-row__title">
                    <span class="notification-row__name">{{ item.user.name }}</span>
                    {{ item.action }}
                  </p>
                </div>
                <p class="notification-row__message">{{ item.message }}</p>
                <div class="notification-row__footer">
                  <span>{{ formatRelativeTime(item.timestamp) }}</span>
                  <button class="link-btn" type="button" @click="toggleReadStatus(item.id)">
                    {{ item.status === 'unread' ? 'Mark as read' : 'Mark as unread' }}
                  </button>
                </div>
              </div>
              <span class="notification-row__dot" :class="{ unread: item.status === 'unread' }" />
            </article>
            <p v-if="!filteredNotifications.length" class="notification-list__empty">
              Nothing new here yet. Start a booking or send a message to see updates.
            </p>
          </template>
        </div>

        <footer class="notification-shell__footer">
          <button class="mark-read" :disabled="!hasUnread || isLoading" @click="markAllAsRead">Mark all as read</button>
          <router-link to="/notifications" class="view-all">View all notifications</router-link>
        </footer>
      </section>
    </main>

    <CommonFooter />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { userService } from '@/services/database.js'
import UserNavbar from '@/components/UserNavbar.vue'
import CommonFooter from '@/components/CommonFooter.vue'
import { useNotifications } from '@/composables/useNotifications'

const router = useRouter()

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '/bookings' },
  { label: 'Notifications', route: '/notifications' },
  { label: 'Promotions', route: '/promotions' }
]

const activeNav = ref('Notifications')
const setActiveNav = (item) => {
  activeNav.value = item.label
}

const handleLogout = async () => {
  await userService.logout()
  router.push('/login')
}

const filterOptions = [
  { label: 'All', value: 'all' },
  { label: 'Unread', value: 'unread' }
]

const activeFilter = ref('all')
const {
  notifications,
  unreadCount,
  isLoading,
  error,
  loadNotifications,
  toggleReadStatus,
  markAllAsRead
} = useNotifications()

const hasUnread = computed(() => unreadCount.value > 0)

onMounted(() => {
  loadNotifications()
})

const filteredNotifications = computed(() => {
  const list = notifications.value || []
  if (activeFilter.value === 'unread') {
    return list.filter((item) => item.status === 'unread')
  }
  return list
})

const formatRelativeTime = (timestamp) => {
  if (!timestamp) return ''
  const now = Date.now()
  const target = new Date(timestamp).getTime()
  const diffMs = now - target
  const minutes = Math.floor(diffMs / 60000)
  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days}d ago`
  return new Date(timestamp).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
}

</script>

<style scoped>
.notification-screen {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f4f7fb;
}

.notification-screen__body {
  flex: 1;
  padding: 32px 24px;
}

.notification-shell {
  max-width: 960px;
  margin: 0 auto;
  background: linear-gradient(180deg, #fefefe 0%, #f2f7ff 100%);
  border-radius: 32px;
  padding: 32px;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.notification-shell__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
}

.notification-shell__eyebrow {
  margin: 0;
  font-size: 0.85rem;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #6366f1;
}

.notification-shell__header h1 {
  margin: 8px 0 0;
  font-size: 2rem;
  color: #0f172a;
}

.notification-shell__filters {
  display: flex;
  gap: 12px;
}

.filter-pill {
  border-radius: 999px;
  border: 1px solid #cbd5f5;
  padding: 10px 20px;
  background: #fff;
  color: #475569;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.filter-pill.active {
  background: #2563eb;
  color: #fff;
  border-color: transparent;
}

.notification-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.notification-list__state {
  padding: 14px;
  border-radius: 18px;
  background: #f8fafc;
  color: #475569;
  font-size: 0.9rem;
  text-align: center;
}

.notification-list__state--error {
  background: #fee2e2;
  color: #7f1d1d;
}

.notification-row {
  display: grid;
  grid-template-columns: 60px 1fr 14px;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 26px;
  background: #eef2ff;
  align-items: center;
  position: relative;
}

.notification-row.unread {
  background: #dde5ff;
  box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.25);
}

.notification-row__avatar {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  object-fit: cover;
  box-shadow: 0 12px 20px rgba(15, 23, 42, 0.18);
}

.notification-row__content {
  background: #fff;
  border-radius: 22px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
}

.notification-row__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
  display: flex;
  gap: 4px;
}

.notification-row__name {
  color: #4338ca;
}

.notification-row__message {
  margin: 0;
  color: #475569;
  font-size: 0.9rem;
}

.notification-row__footer {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  color: #94a3b8;
  align-items: center;
}

.link-btn {
  background: none;
  border: none;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
}

.notification-row__dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(99, 102, 241, 0.5);
}

.notification-row__dot.unread {
  background: #a855f7;
}

.notification-list__empty {
  text-align: center;
  color: #94a3b8;
  margin: 12px 0;
}

.notification-shell__footer {
  display: flex;
  gap: 12px;
}

.mark-read,
.view-all {
  flex: 1;
  border-radius: 14px;
  padding: 12px 0;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.mark-read {
  background: #fff;
  border: 1px solid #cbd5f5;
  color: #2563eb;
}

.mark-read:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.view-all {
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  color: #fff;
  text-align: center;
  text-decoration: none;
}

.notification-shell__meta {
  font-size: 0.9rem;
  color: #94a3b8;
}

@media (max-width: 860px) {
  .notification-shell {
    padding: 24px;
  }

  .notification-row {
    grid-template-columns: 52px 1fr 10px;
  }

  .notification-row__avatar {
    width: 52px;
    height: 52px;
  }
}

@media (max-width: 640px) {
  .notification-shell__filters {
    flex-direction: column;
  }

  .notification-shell__header {
    flex-direction: column;
  }

  .notification-shell__footer {
    flex-direction: column;
  }
}
</style>
