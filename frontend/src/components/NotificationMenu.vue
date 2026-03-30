<script setup>
import { computed, ref, watch } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const tabOptions = [
  { label: 'View All', value: 'all' },
  { label: 'New Order', value: 'booking' },
  { label: 'Weekly Update', value: 'general' }
]

const props = defineProps({
  shopId: {
    type: [String, Number],
    default: null
  }
})

const {
  notifications,
  unreadCount,
  isLoading,
  error,
  loadNotifications,
  markAllAsRead
} = useNotifications()

const activeTab = ref('all')

const refreshNotifications = () => {
  loadNotifications(props.shopId).catch(() => {})
}

watch(
  () => props.shopId,
  () => {
    refreshNotifications()
  },
  { immediate: true }
)

const baseNotifications = computed(() => {
  if (!props.shopId) {
    return notifications.value || []
  }
  return (notifications.value || []).filter(
    (item) => String(item.shopId) === String(props.shopId),
  )
})

const filteredForTab = computed(() => {
  const base = baseNotifications.value
  if (activeTab.value === 'booking') {
    return base.filter((item) => item.type === 'booking')
  }
  if (activeTab.value === 'general') {
    return base.filter((item) => item.type === 'general')
  }
  return base
})

const hasUnread = computed(() => unreadCount.value > 0)

const formatRelativeTime = (timestamp) => {
  if (!timestamp) return 'Just now'
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

const formatNotificationTitle = (item) => {
  const verbMap = {
    booking: 'New order for',
    message: 'Message from',
    general: 'Update from'
  }
  const verb = verbMap[item.type] || 'Notification from'
  return `${verb} ${item.user?.name || 'Customer'}`
}
</script>

<template>
  <div class="notification-panel panel-slim">
    <header class="notification-panel__header">
      <div>
        <h3>Notifications</h3>
        <p class="notification-panel__subtitle">Stay on top of incoming orders and updates.</p>
      </div>
      <button class="header-menu" type="button" aria-label="Open menu">
        <span class="dot" />
        <span class="dot" />
        <span class="dot" />
      </button>
    </header>

    <div class="notification-tabs">
      <button
        v-for="tab in tabOptions"
        :key="tab.value"
        :class="['notification-tab', { active: activeTab === tab.value }]"
        type="button"
        @click="activeTab = tab.value"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="notification-panel__list">
      <div v-if="isLoading" class="notification-panel__state">Loading notifications...</div>
      <div v-else-if="error" class="notification-panel__state notification-panel__state--error">
        {{ error }}
      </div>
      <template v-else>
        <article
          v-for="item in filteredForTab"
          :key="item.id"
          class="notification-item notification-card"
        >
          <div class="notification-item__avatar">
            <img v-if="item.user?.avatar" :src="item.user.avatar" :alt="item.user?.name || 'avatar'" />
            <span v-else>{{ item.user?.name?.charAt(0) || 'N' }}</span>
            <span v-if="item.status === 'unread'" class="badge-dot" />
          </div>
          <div class="notification-item__body">
            <h4 class="notification-item__title">Notification from {{ item.user?.name || 'User' }}</h4>
            <p class="notification-item__description">{{ item.message || item.action }}</p>
            <span class="notification-item__time">{{ formatRelativeTime(item.timestamp) }}</span>
          </div>
        </article>
        <p v-if="!filteredForTab.length" class="notification-panel__empty">
          No notifications yet. Check back after you receive activity.
        </p>
      </template>
    </div>

    <footer class="notification-panel__footer">
      <button class="mark-read" :disabled="!hasUnread || isLoading" @click="markAllAsRead(props.shopId)">
        Mark all as read
      </button>
      <router-link :to="{ name: 'notifications' }" class="view-all">View all notifications</router-link>
    </footer>
  </div>
</template>

<style scoped>
.notification-panel {
  width: min(420px, calc(100vw - 24px));
  padding: 22px;
  border-radius: 28px;
  background: #ffffff;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.panel-slim {
  min-width: min(360px, calc(100vw - 24px));
}

.notification-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-panel__header h3 {
  margin: 0;
  font-size: 1.35rem;
  color: #111827;
}

.notification-panel__subtitle {
  margin: 4px 0 0;
  font-size: 0.85rem;
  color: #6b7280;
}

.header-menu {
  border: none;
  background: transparent;
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 6px;
  cursor: pointer;
}

.header-menu .dot {
  width: 4px;
  height: 4px;
  background: #6b7280;
  border-radius: 50%;
}

.notification-tabs {
  display: flex;
  gap: 8px;
}

.notification-tab {
  flex: 1;
  border-radius: 999px;
  padding: 10px 0;
  border: none;
  font-weight: 600;
  background: #f3f4ff;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-tab.active {
  background: #eef2ff;
  color: #4338ca;
}

.notification-panel__list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 360px;
  overflow-y: auto;
  padding-right: 4px;
}

.notification-panel__state {
  font-size: 0.9rem;
  color: #475569;
  padding: 18px;
  border-radius: 16px;
  background: #f8fafc;
  text-align: center;
}

.notification-panel__state--error {
  border: 1px solid #fecaca;
  color: #9f1239;
}

.notification-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  border-radius: 24px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
  margin-bottom: 6px;
}

.notification-item__avatar {
  position: relative;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #eef2ff;
  display: grid;
  place-items: center;
  font-weight: 700;
  color: #1d4ed8;
  font-size: 1rem;
  min-width: 48px;
  min-height: 48px;
  overflow: hidden;
}

.notification-item__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}

.badge-dot {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #ef4444;
  border: 2px solid #ffffff;
}

.notification-item__body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.notification-item__content {
  gap: 6px;
}

.notification-item__content > div {
  min-height: 52px;
}

.notification-item__title {
  margin: 0;
  font-weight: 700;
  font-size: 1.15rem;
  color: #0f172a;
}

.notification-item__description {
  margin: 4px 0 0.2rem;
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.4;
}

.notification-item__time {
  font-size: 0.8rem;
  color: #94a3b8;
}


.notification-panel__empty {
  font-size: 0.9rem;
  color: #6b7280;
  text-align: center;
  padding: 20px;
}

.notification-panel__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
}

.mark-read {
  border-radius: 999px;
  border: 1px solid #e0e7ff;
  background: #fff;
  padding: 10px 18px;
  font-weight: 600;
  color: #4338ca;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.mark-read:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.view-all {
  font-weight: 600;
  color: #4338ca;
}

@media (max-width: 640px) {
  .notification-panel {
    width: calc(100vw - 40px);
  }

  .notification-tabs {
    flex-wrap: wrap;
  }

  .notification-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .notification-panel__footer {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
