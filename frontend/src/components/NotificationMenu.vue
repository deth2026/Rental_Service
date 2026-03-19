<script setup>
import { computed, onMounted, ref } from 'vue'
import { useNotifications } from '@/composables/useNotifications'

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Unread', value: 'unread' }
]

const {
  notifications,
  unreadCount,
  isLoading,
  error,
  loadNotifications,
  markAllAsRead,
  toggleReadStatus
} = useNotifications()

const activeFilter = ref('all')

onMounted(() => {
  loadNotifications()
})

const filteredNotifications = computed(() => {
  const base = notifications.value || []
  if (activeFilter.value === 'unread') {
    return base.filter((item) => item.status === 'unread')
  }
  return base
})

const hasUnread = computed(() => unreadCount.value > 0)
const displayBadge = computed(() => {
  if (!unreadCount.value) return ''
  return unreadCount.value > 9 ? '9+' : String(unreadCount.value)
})

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
</script>

<template>
  <div class="notification-panel">
    <header class="notification-panel__header">
      <div>
        <p class="notification-panel__eyebrow">Notifications</p>
        <h3>Activity</h3>
      </div>
      <span v-if="hasUnread" class="notification-panel__badge">{{ displayBadge }}</span>
    </header>

    <div class="notification-panel__filters">
      <button
        v-for="filter in filters"
        :key="filter.value"
        :class="['filter-pill', { active: activeFilter === filter.value }]"
        type="button"
        @click="activeFilter = filter.value"
      >
        {{ filter.label }}
      </button>
    </div>

    <div class="notification-panel__list">
      <div v-if="isLoading" class="notification-panel__state">Loading notifications…</div>
      <div v-else-if="error" class="notification-panel__state notification-panel__state--error">
        {{ error }}
      </div>
      <template v-else>
        <article
          v-for="item in filteredNotifications"
          :key="item.id"
          class="notification-card"
          :class="{ unread: item.status === 'unread' }"
        >
          <img :src="item.user.avatar" :alt="item.user.name" class="notification-avatar" />
          <div class="notification-text">
            <p class="notification-text__title">
              <span class="notification-name">{{ item.user.name }}</span>
              {{ item.action }}
            </p>
            <p class="notification-text__body">{{ item.message }}</p>
            <div class="notification-meta">
              <span>{{ formatRelativeTime(item.timestamp) }}</span>
              <button class="notification-meta__action" type="button" @click="toggleReadStatus(item.id)">
                {{ item.status === 'unread' ? 'Mark as read' : 'Mark as unread' }}
              </button>
            </div>
          </div>
          <span class="notification-badge" :class="{ unread: item.status === 'unread' }" />
        </article>
        <p v-if="!filteredNotifications.length" class="notification-panel__empty">
          No notifications for now. Check back after creating a booking or receiving a message.
        </p>
      </template>
    </div>

    <footer class="notification-panel__footer">
      <button class="mark-read" :disabled="!hasUnread || isLoading" @click="markAllAsRead">Mark all as read</button>
      <router-link to="/notifications" class="view-all">View all notifications</router-link>
    </footer>
  </div>
</template>

<style scoped>
.notification-panel {
  width: 420px;
  padding: 22px;
  border-radius: 32px;
  background: linear-gradient(180deg, #ffffff 0%, #eff3ff 80%);
  box-shadow: 0 30px 65px rgba(15, 23, 42, 0.25);
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.notification-panel__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-panel__eyebrow {
  margin: 0;
  font-size: 0.75rem;
  letter-spacing: 0.3em;
  text-transform: uppercase;
  color: #94a3b8;
}

.notification-panel__header h3 {
  margin: 4px 0 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: #0f172a;
}

.notification-panel__badge {
  background: #7c3aed;
  color: #fff;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 999px;
}

.notification-panel__filters {
  display: flex;
  gap: 10px;
}

.filter-pill {
  flex: 1;
  border-radius: 999px;
  border: 1px solid #e0e7ff;
  background: #fff;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 600;
  padding: 10px 0;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.filter-pill.active {
  background: #eef2ff;
  color: #111827;
  border-color: #c4b5fd;
}

.notification-panel__list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 360px;
  overflow-y: auto;
}

.notification-panel__state {
  font-size: 0.85rem;
  color: #475569;
  padding: 14px;
  border-radius: 16px;
  background: #f8fafc;
  text-align: center;
}

.notification-panel__state--error {
  color: #be123c;
  background: #fee2e2;
}

.notification-card {
  display: grid;
  grid-template-columns: 58px 1fr 12px;
  gap: 16px;
  align-items: center;
  padding: 12px 12px 12px 0;
  border-radius: 24px;
  background: #f7f9ff;
  position: relative;
}

.notification-card.unread {
  background: #e0e7ff;
  box-shadow: 0 12px 24px rgba(99, 102, 241, 0.15);
}

.notification-avatar {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  object-fit: cover;
  margin-left: 12px;
  border: 2px solid #fff;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.25);
}

.notification-text {
  padding: 10px 16px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.notification-text__title {
  margin: 0;
  font-size: 0.95rem;
  color: #0f172a;
  font-weight: 600;
}

.notification-name {
  color: #4338ca;
}

.notification-text__body {
  margin: 0;
  font-size: 0.82rem;
  color: #475569;
}

.notification-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.78rem;
  color: #94a3b8;
  margin-top: 4px;
}

.notification-meta__action {
  border: none;
  background: none;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
}

.notification-badge {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #cbd5f5;
}

.notification-badge.unread {
  background: #a855f7;
}

.notification-panel__empty {
  margin: 0;
  text-align: center;
  color: #94a3b8;
  font-size: 0.85rem;
}

.notification-panel__footer {
  display: flex;
  gap: 12px;
}

.mark-read,
.view-all {
  flex: 1;
  border-radius: 14px;
  padding: 10px 0;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mark-read {
  background: #fff;
  border: 1px solid #cbd5f5;
  color: #2563eb;
}

.mark-read:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.view-all {
  background: linear-gradient(135deg, #7c3aed, #a855f7);
  color: #fff;
  text-align: center;
  text-decoration: none;
}

@media (max-width: 640px) {
  .notification-panel {
    width: 100%;
  }

  .notification-card {
    grid-template-columns: 48px 1fr 10px;
  }

  .notification-avatar {
    width: 48px;
    height: 48px;
  }
}
</style>
