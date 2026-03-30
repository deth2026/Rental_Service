<template>
  <section class="admin-notifications-page">
    <header class="admin-notifications-page__header">
      <div>
        <p class="eyebrow">Notifications</p>
        <h1>System activity</h1>
        <p class="subhead">
          Receive real-time updates when users register, shops go live, or reports land so you can manage the
          platform with confidence.
        </p>
      </div>
      <div class="admin-notifications-page__header-actions">
        <div class="unread-pill" aria-live="polite">
          <span class="unread-pill__count">{{ unreadCountLabel }}</span>
        </div>
      </div>
    </header>

    <div class="admin-notifications-page__toolbar">
      <div class="notification-search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input
          v-model="searchText"
          class="notification-search__input"
          type="search"
          placeholder="Search notifications (title, message, type)"
        />
        <button v-if="hasSearchTerm" type="button" class="ghost-pill ghost-pill--mini" @click="clearSearch">
          Clear
        </button>
      </div>
      <button
        type="button"
        class="ghost-pill ghost-pill--mini"
        :class="{ active: showRecentOnly }"
        @click="toggleRecentView"
      >
        {{ showRecentOnly ? 'Showing last 24h' : 'Showing full history' }}
      </button>
    </div>

    <div class="admin-notifications-page__tabs">
      <div class="category-filters">
        <span class="category-filters__label">Filter</span>
        <button
          v-for="option in categoryFilters"
          :key="option.value"
          type="button"
          class="category-chip"
          :class="{ active: activeCategory === option.value }"
          @click="activeCategory = option.value"
        >
          {{ option.label }}
        </button>
      </div>
    </div>

    <div class="admin-notifications-page__list">
      <template v-if="isLoading">
        <div class="empty-state">Loading notifications…</div>
      </template>
      <template v-else-if="error">
        <div class="empty-state error">{{ error }}</div>
      </template>
      <template v-else-if="!filteredNotifications.length">
        <div class="empty-state">
          No notifications yet. Once the platform registers a user, creates a shop, or receives a report, the
          activity appears here.
        </div>
      </template>
      <template v-else>
        <article
          v-for="item in displayedNotifications"
          :key="item.id"
          class="notification-card"
          :class="[{ unread: item.status === 'unread' }, 'notification-card--clickable']"
          @click="openNotificationDetail(item)"
        >
          <div class="notification-card__avatar">
            <img v-if="item.user?.avatar" :src="item.user.avatar" :alt="`${senderName(item)} avatar`" />
            <span v-else>{{ getInitials(senderName(item)) }}</span>
            <span v-if="item.status === 'unread'" class="status-dot" />
          </div>
          <div class="notification-card__body">
            <p class="notification-card__title">Notification from {{ senderName(item) }}</p>
            <p class="notification-card__text">{{ notificationDetailText(item) }}</p>
            <div class="notification-card__meta">
              <span>{{ formatRelativeTime(item.timestamp) }}</span>
            </div>
          </div>
        </article>
          </template>
          <div v-if="canToggleView" class="notification-list__actions">
            <button type="button" class="ghost-pill" @click="toggleNotificationView">
              {{ showAllNotifications ? 'See less' : 'See more' }}
            </button>
          </div>
        </div>
  </section>
  <div v-if="platformPopupVisible" class="platform-popup" role="dialog" aria-modal="true">
    <div class="platform-popup__panel">
      <header class="platform-popup__header">
        <div>
          <p class="platform-popup__title">Share platform updates</p>
          <p class="platform-popup__subtitle">Send a direct message to the selected recipient.</p>
        </div>
        <button class="icon-btn platform-popup__close" type="button" @click="handlePlatformCancel">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          <span class="sr-only">Close</span>
        </button>
      </header>
      <p class="platform-popup__summary">
        Choose whether to notify a specific user or a shop owner, then pick the recipient from the list. If
        you want to send a ping to multiple recipients, repeat the action for each one.
      </p>
      <div class="platform-popup__selectors">
        <div class="platform-popup__selector-card">
          <span class="platform-popup__selector-label">Recipient type</span>
          <select v-model="platformTargetType" class="platform-popup__select">
            <option value="user">User</option>
            <option value="shop">Shop owner</option>
          </select>
          <span class="platform-popup__selector-hint">
            Selecting “Shop owner” lets you notify a specific shop instead of a single account.
          </span>
        </div>
        <div class="platform-popup__selector-card">
          <span class="platform-popup__selector-label">Recipient</span>
          <select
            id="platform-recipient-popup"
            v-model="platformSelectedKey"
            :disabled="!platformOptionsForType.length"
            class="platform-popup__select"
          >
            <option value="" disabled>Select recipient</option>
            <option
              v-for="recipient in platformOptionsForType"
              :key="`${recipient.type}-${recipient.id}`"
              :value="`${recipient.type}:${recipient.id}`"
            >
              {{ recipient.label }}
            </option>
          </select>
          <span class="platform-popup__selector-hint">
            {{ platformOptionsForType.length ? 'Use the dropdown to find the right recipient.' : 'No recipients available yet.' }}
          </span>
        </div>
      </div>
      <label class="platform-popup__field">
        <span>Title</span>
        <input
          v-model="platformTitle"
          type="text"
          maxlength="128"
          placeholder="E.g. Welcome to the platform"
        />
        <small class="platform-popup__helper">Keep it short—aim for 5-7 words so recipients can scan quickly.</small>
      </label>
      <label class="platform-popup__field">
        <span>Message</span>
        <textarea
          v-model="platformMessage"
          rows="4"
          placeholder="Enter the details you want to share."
        ></textarea>
        <small class="platform-popup__helper">Share the key updates and next steps; recipients will see this in their inbox.</small>
      </label>
      <div class="platform-popup__actions">
        <button
          type="button"
          class="ghost-pill platform-popup__cancel"
          :disabled="isSendingInfo"
          @click="handlePlatformCancel"
        >
          Cancel
        </button>
        <button
          type="button"
          class="primary-pill platform-popup__send"
          :disabled="!canSendPlatformMessage || isSendingInfo"
          @click="handlePlatformSendClick"
        >
          {{ isSendingInfo ? 'Sending…' : 'Send' }}
        </button>
      </div>
    </div>
  </div>
  <div
    v-if="detailModalVisible"
    class="notification-detail-modal"
    role="dialog"
    aria-modal="true"
    @click.self="closeDetailModal"
  >
    <div class="notification-detail-modal__panel">
      <header class="notification-detail-modal__header">
        <div>
          <p class="notification-detail-modal__title">{{ detailNotificationInfo.subject }}</p>
          <p class="notification-detail-modal__subtitle">
            Message from {{ detailNotificationInfo.senderName }}
          </p>
        </div>
        <button class="icon-btn notification-detail-modal__close" type="button" @click="closeDetailModal">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
          <span class="sr-only">Close</span>
        </button>
      </header>
      <div class="notification-detail-modal__meta">
        <span>{{ formatFullDate(detailNotificationInfo.timestamp) }}</span>
        <span>{{ detailNotificationInfo.categoryLabel }}</span>
      </div>
      <p class="notification-detail-modal__body">{{ detailNotificationInfo.body || 'No additional details provided.' }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useNotifications } from '@/composables/useNotifications'
import { useToast } from '@/composables/useToast'
import api from '@/services/api'
import {
  categorizeNotificationType,
  getNotificationCategoryLabel,
  PLATFORM_CATEGORY_KEY,
  DISCOUNT_CATEGORY_KEY
} from '@/utils/notificationHelpers'
import { useAdminStore } from '@/stores/adminStore'
import { useRoute } from 'vue-router'

const toast = useToast()
const {
  notifications,
  unreadCount,
  isLoading,
  error,
  loadNotifications,
  toggleReadStatus
} = useNotifications()
const adminStore = useAdminStore()
const route = useRoute()

const categoryFilters = [
  { label: 'All', value: 'all' },
  { label: 'Users', value: 'user' },
  { label: 'Shops', value: 'shop' },
  { label: 'Discount', value: DISCOUNT_CATEGORY_KEY },
  { label: 'Platform', value: PLATFORM_CATEGORY_KEY }
]

const activeCategory = ref('all')
const searchText = ref('')
const showRecentOnly = ref(true)
const platformTargetType = ref('user')
const platformSelectedKey = ref('')
const platformTitle = ref('')
const platformMessage = ref('')
const isSendingInfo = ref(false)
const platformPopupVisible = ref(false)
const detailModalVisible = ref(false)
const detailNotification = ref(null)

const detailNotificationInfo = computed(() => {
  const item = detailNotification.value
  if (!item) {
    return {
      senderName: '',
      subject: '',
      body: '',
      timestamp: null,
      categoryLabel: ''
    }
  }

  return {
    senderName: item.relatedSender?.name || item.user?.name || 'Sender',
    subject: item.detailSubject || item.action || 'Notification',
    body: item.detailBody || item.message || '',
    timestamp: item.timestamp,
    categoryLabel: getNotificationCategoryLabel(item.type)
  }
})

const senderName = (item) =>
  item.relatedSender?.name || item.user?.name || 'System'

const notificationDetailText = (item) =>
  item.detailBody || item.message || item.action || 'No additional details provided.'

const getInitials = (value) => {
  const text = String(value || '').trim()
  if (!text) return 'S'
  const parts = text.split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase()
}

const openNotificationDetail = (item) => {
  detailNotification.value = item
  detailModalVisible.value = true
}

const closeDetailModal = () => {
  detailModalVisible.value = false
  detailNotification.value = null
}

const NEW_WINDOW_MS = 1000 * 60 * 60 * 24

const normalizedSearchTerm = computed(() => String(searchText.value || '').trim().toLowerCase())
const hasSearchTerm = computed(() => Boolean(normalizedSearchTerm.value))

const matchesSearch = (item) => {
  if (!normalizedSearchTerm.value) return true
  const haystack = `${item.action || ''} ${item.message || ''} ${item.type || ''}`
  return haystack.toLowerCase().includes(normalizedSearchTerm.value)
}

const discountKeywordRegex = /\b(?:discount|coupon|promo|promotion|offer|sale|deal)\b/i
const hasDiscountKeyword = (item) => {
  const haystack = `${item.action || ''} ${item.message || ''} ${item.title || ''} ${item.type || ''}`.toLowerCase()
  return discountKeywordRegex.test(haystack)
}

const isShopRelatedNotification = (item) => {
  const identifier = categorizeNotificationType(item.type)
  if (identifier === 'shop') return true
  const relatedType = String(item.related_type || '').toLowerCase()
  if (item.shop_id || relatedType.includes('shop')) return true
  if (String(item.role || '').toLowerCase().includes('shop')) return true
  return false
}

const isUserRelatedNotification = (item) => {
  const identifier = categorizeNotificationType(item.type)
  if (identifier === 'user') return true
  const relatedType = String(item.related_type || '').toLowerCase()
  if (item.user_id || relatedType.includes('user')) return true
  if (String(item.role || '').toLowerCase().includes('user')) return true
  return false
}

function matchesCategory(item) {
  if (activeCategory.value === 'all') return true
  const identifier = categorizeNotificationType(item.type)
  if (activeCategory.value === DISCOUNT_CATEGORY_KEY) {
    return identifier === DISCOUNT_CATEGORY_KEY || hasDiscountKeyword(item)
  }
  if (activeCategory.value === 'shop') {
    return isShopRelatedNotification(item)
  }
  if (activeCategory.value === 'user') {
    return isUserRelatedNotification(item)
  }
  return identifier === activeCategory.value
}

const unreadCountLabel = computed(() => {
  const unread = unreadCount.value || 0
  if (unread === 0) return 'You have no unread notifications'
  return unread === 1 ? '1 unread notification' : `${unread} unread notifications`
})

const MAX_VISIBLE_NOTIFICATIONS = 4
const showAllNotifications = ref(false)
const shouldHideOlder = computed(() => showRecentOnly.value && !hasSearchTerm.value)
const filteredNotifications = computed(() => {
  let list = notifications.value || []
  if (shouldHideOlder.value) {
    list = list.filter((item) => isWithinNewWindow(item.timestamp))
  }
  return list.filter((item) => matchesCategory(item) && matchesSearch(item))
})
const displayedNotifications = computed(() => {
  if (showAllNotifications.value) return filteredNotifications.value
  return filteredNotifications.value.slice(0, MAX_VISIBLE_NOTIFICATIONS)
})
const canToggleView = computed(
  () => filteredNotifications.value.length > MAX_VISIBLE_NOTIFICATIONS
)
const toggleNotificationView = () => {
  showAllNotifications.value = !showAllNotifications.value
}
watch(filteredNotifications, () => {
  if (showAllNotifications.value && filteredNotifications.value.length <= MAX_VISIBLE_NOTIFICATIONS) {
    showAllNotifications.value = false
  }
})

const canManageReadState = (item) => item?.role === 'admin'

function isWithinNewWindow(timestamp) {
  const value = Number(new Date(timestamp)) || 0
  return value > 0 && Date.now() - value <= NEW_WINDOW_MS
}

const isNewNotification = (item) => item.status === 'unread' && isWithinNewWindow(item.timestamp)

const formatRelativeTime = (timestamp) => {
  const value = Number(new Date(timestamp))
  if (!Number.isFinite(value)) return 'Just now'
  const diff = Date.now() - value
  if (diff < 60000) return 'Moments ago'
  if (diff < 3600000) return `${Math.round(diff / 60000)} min ago`
  if (diff < 86400000) return `${Math.round(diff / 3600000)} hr ago`
  return `${Math.round(diff / 86400000)} day${Math.round(diff / 86400000) === 1 ? '' : 's'} ago`
}

const formatFullDate = (timestamp) => {
  const value = Number(new Date(timestamp))
  if (!Number.isFinite(value)) return '—'
  return new Date(value).toLocaleString('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

const platformRecipientOptions = computed(() => {
  const users = new Map()
  const shops = new Map()

  const ensureUserEntry = (entry) => {
    if (!entry?.id) return
    const id = Number(entry.id)
    if (!id || users.has(id)) return
    users.set(id, {
      id,
      type: 'user',
      label: entry.name || entry.title || `User #${id}`,
      secondary: entry.email || entry.message || ''
    })
  }

  const ensureShopEntry = (entry) => {
    if (!entry?.id) return
    const id = Number(entry.id)
    if (!id || shops.has(id)) return
    const ownerName = entry.owner?.name || entry.owner_name
    const displayTitle = ownerName ? `${ownerName} (${entry.name || `Shop #${id}`})` : entry.name || `Shop #${id}`
    shops.set(id, {
      id,
      type: 'shop',
      label: displayTitle,
      secondary: entry.message || entry.description || ''
    })
  }

  ;(notifications.value || []).forEach((item) => {
    const category = categorizeNotificationType(item.type)
    if (category === 'user') {
      ensureUserEntry({
        id: item.related?.id || item.user?.id || item.user_id,
        name: item.user?.name || item.action,
        email: item.user?.email,
        message: item.message
      })
    }
    if (category === 'shop') {
      ensureShopEntry({
        id: item.shopId || item.related?.id,
        name: item.action,
        owner_name: item.user?.name,
        description: item.message
      })
    }
  })

  ;(adminStore.state.users || []).forEach((user) => ensureUserEntry(user))
  ;(adminStore.state.shops || []).forEach((shop) => ensureShopEntry(shop))

  return {
    user: Array.from(users.values()),
    shop: Array.from(shops.values())
  }
})

const platformOptionsForType = computed(() => platformRecipientOptions.value[platformTargetType.value] || [])
const selectedPlatformRecipient = computed(() => {
  if (!platformSelectedKey.value) return null
  const [type, rawId] = platformSelectedKey.value.split(':')
  const pool = platformRecipientOptions.value[type] || []
  return pool.find((entry) => String(entry.id) === rawId) || null
})

watch(
  [() => platformTargetType.value, () => platformOptionsForType.value],
  ([type, options]) => {
    const pool = options || []
    platformSelectedKey.value = pool.length ? `${type}:${pool[0].id}` : ''
  },
  { immediate: true }
)

watch(
  () => platformRecipientOptions.value,
  () => {
    if (!platformOptionsForType.value.length) {
      platformSelectedKey.value = ''
    }
  }
)

const canSendPlatformMessage = computed(
  () => Boolean(selectedPlatformRecipient.value && platformTitle.value.trim() && platformMessage.value.trim())
)

const handlePlatformSendClick = async () => {
  if (!selectedPlatformRecipient.value) {
    toast.info('Select a recipient before sending an update.')
    return
  }
  const recipient = selectedPlatformRecipient.value
  const payload = {
    title: String(platformTitle.value || '').trim(),
    message: String(platformMessage.value || '').trim(),
    target: recipient.type === 'shop' ? 'shop_owner' : 'user'
  }
  if (recipient.type === 'shop') {
    payload.shop_id = recipient.id
  } else {
    payload.user_id = recipient.id
  }

  if (!payload.title || !payload.message) {
    toast.error('Please provide a title and message.')
    return
  }

  isSendingInfo.value = true
  try {
    await api.post('/notifications', payload)
    toast.success(`Information sent to ${recipient.label}.`)
    platformTitle.value = ''
    platformMessage.value = ''
    loadNotifications(null, { include_all: true })
    platformPopupVisible.value = false
    activeCategory.value = 'all'
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to send information.')
  } finally {
    isSendingInfo.value = false
  }
}

const handlePlatformReset = () => {
  platformTitle.value = ''
  platformMessage.value = ''
}

const handlePlatformCancel = () => {
  platformPopupVisible.value = false
  activeCategory.value = 'all'
  handlePlatformReset()
}

watch(
  () => activeCategory.value,
  (value) => {
    platformPopupVisible.value = value === PLATFORM_CATEGORY_KEY
    if (!platformPopupVisible.value) {
      handlePlatformReset()
    }
  }
)

const toggleRecentView = () => {
  showRecentOnly.value = !showRecentOnly.value
}

const clearSearch = () => {
  searchText.value = ''
  showRecentOnly.value = true
}

const refreshNotifications = () => loadNotifications(null, { include_all: true }).catch(() => {})

const handleToggleRead = async (item) => {
  if (!canManageReadState(item)) return
  try {
    await toggleReadStatus(item.id)
  } catch (err) {
    toast.error('Unable to change notification status.')
  }
}

onMounted(() => {
  adminStore.load().catch(() => {})
})

watch(
  () => route.name,
  (name) => {
    if (name === 'admin-notifications') {
      refreshNotifications()
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.admin-notifications-page {
  padding: 32px;
  background: #f8f9fc;
  border-radius: 24px;
  box-shadow: 0 14px 35px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.admin-notifications-page__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
}

.eyebrow {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #6b7280;
  margin-bottom: 4px;
}

.subhead {
  max-width: 32rem;
  color: #4b5563;
  margin-top: 0.25rem;
}

.admin-notifications-page__header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.unread-pill {
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-full);
  padding: 0.4rem 1rem;
  font-weight: 600;
  color: var(--color-text-primary);
  font-size: 0.9rem;
}

.admin-notifications-page__toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.notification-search {
  flex: 1;
  min-width: 220px;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 0.6rem;
  border-radius: var(--radius-md);
  background: var(--color-card-bg);
  border: 1px solid var(--color-border);
}

.notification-search__input {
  border: none;
  flex: 1;
  font-size: 0.9rem;
  background: transparent;
  color: var(--color-text-primary);
}

.notification-search__input:focus {
  outline: none;
}

.notification-search i {
  color: var(--color-text-muted);
}

.category-filters {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.category-filters__label {
  font-weight: 600;
  color: #374151;
}

.category-chip {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: 0.35rem 1rem;
  font-size: 0.85rem;
  cursor: pointer;
}

.category-chip.active {
  border-color: #2563eb;
  background: #fff;
  color: black;
}

.platform-send-panel {
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  background: var(--color-card-bg);
  padding: 1.2rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: var(--shadow-card);
}

.platform-send-panel__text {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.platform-send-panel__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
}

.platform-send-panel__description {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: 0.9rem;
}

.platform-send-panel__controls {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.platform-send-panel__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  align-items: center;
  margin-top: 0.4rem;
}
.platform-send-panel__actions .ghost-pill {
  padding: 0.5rem 0.9rem;
}

.platform-send-panel__selector-grid {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 0.65rem;
  align-items: center;
}

.platform-send-panel__selector-grid select {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0.45rem 0.75rem;
  background: var(--color-input-bg);
  color: var(--color-text-primary);
}

.platform-send-panel__field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
}

.platform-send-panel__field input,
.platform-send-panel__field textarea {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-md);
  padding: 0.55rem;
  font-size: 0.9rem;
  font-family: inherit;
  background: var(--color-input-bg);
}

.platform-send-panel__field textarea {
  resize: vertical;
}

.platform-send-panel__hint {
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.platform-popup {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 60;
  padding: 1.5rem;
}
.platform-popup__panel {
  background: var(--color-card-bg);
  border-radius: 20px;
  padding: 1.5rem;
  width: min(480px, 100%);
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.35);
  display: flex;
  flex-direction: column;
  gap: 1rem;
  position: relative;
}
.platform-popup__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}
.platform-popup__title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #111827;
}
.platform-popup__subtitle {
  margin: 0;
  font-size: 0.85rem;
  color: #475569;
}
.platform-popup__close {
  border: none;
  background: transparent;
  font-size: 1rem;
  color: #6b7280;
  padding: 0;
}
.platform-popup__summary {
  font-size: 0.9rem;
  color: #4b5563;
  margin: 0;
  background: #eef2ff;
  border-radius: 12px;
  padding: 0.85rem 1rem;
  border: 1px solid #c7d2fe;
  box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.15);
}
.platform-popup__selectors {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.85rem;
}
.platform-popup__selector-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 0.75rem 0.85rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}
.platform-popup__selector-label {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: #6b7280;
}
.platform-popup__select {
  border: none;
  font-size: 0.95rem;
  padding: 0.6rem 0.75rem;
  border-radius: 12px;
  background: #f8fafc;
  box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.08);
  appearance: none;
  width: 100%;
  background-image: linear-gradient(45deg, transparent 50%, #0f172a 50%),
    linear-gradient(135deg, #0f172a 50%, transparent 50%);
  background-repeat: no-repeat;
  background-position: calc(100% - 16px) calc(50% - 6px), calc(100% - 12px) calc(50% - 6px);
  background-size: 6px 6px, 6px 6px;
}
.platform-popup__select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.platform-popup__selector-hint {
  font-size: 0.75rem;
  color: #94a3b8;
}
.platform-popup__field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--color-text-secondary);
}
.platform-popup__field input,
.platform-popup__field textarea {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 14px;
  padding: 0.75rem 0.9rem;
  font-size: 0.95rem;
  font-family: inherit;
  background: #f8fafc;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  min-height: 0;
}
.platform-popup__field input:focus,
.platform-popup__field textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
  background: #ffffff;
}
.platform-popup__actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}
.platform-popup__actions button {
  min-width: 120px;
  padding: 0.75rem 1.5rem;
  border-radius: 999px;
  font-weight: 600;
  letter-spacing: 0.02em;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.platform-popup__actions button:hover:enabled {
  transform: translateY(-1px);
  box-shadow: 0 12px 20px rgba(15, 23, 42, 0.15);
}
.primary-pill.platform-popup__send {
  background: linear-gradient(135deg, #1d4ed8, #2563eb);
  color: #ffffff;
  border: none;
}
.primary-pill.platform-popup__send:disabled {
  background: #93c5fd;
}
.ghost-pill.platform-popup__cancel {
  background: #ef4444;
  color: #ffffff;
  border: none;
  box-shadow: 0 10px 20px rgba(239, 68, 68, 0.25);
}
.platform-popup__helper {
  font-size: 0.75rem;
  color: #475569;
  margin: 0;
}

.admin-notifications-page__list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-list__actions {
  display: flex;
  justify-content: center;
  padding-top: 0.5rem;
}

.notification-list__actions .ghost-pill {
  min-width: 160px;
  font-weight: 600;
}

.empty-state {
  padding: 2rem;
  border-radius: 18px;
  background: white;
  text-align: center;
  color: #6b7280;
  font-weight: 500;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.2);
}

.empty-state.error {
  color: #dc2626;
}

.notification-card {
  background: white;
  border-radius: 18px;
  padding: 1.3rem;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 1rem;
  align-items: center;
  border: 1px solid #e5e7eb;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.notification-card--clickable {
  cursor: pointer;
}
.notification-card--clickable:hover {
  border-color: #1d4ed8;
  box-shadow: 0 18px 30px rgba(37, 99, 235, 0.25);
}

.notification-card.unread {
  border-color: #2563eb;
  box-shadow: 0 14px 30px rgba(20, 168, 222, 0.15);
}

.notification-card__avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #eef2ff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #1d4ed8;
  font-size: 1rem;
  overflow: hidden;
  position: relative;
}

.notification-card__avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  display: block;
}

.status-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 12px;
  height: 12px;
  background: #ef4444;
  border-radius: 50%;
  border: 2px solid #ffffff;
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
}

.notification-card__title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #111827;
}

.notification-card__text {
  margin: 0.35rem 0 0.6rem;
  color: #3b4b63;
  font-size: 0.95rem;
  line-height: 1.4;
}

.notification-card__meta {
  font-size: 0.85rem;
  color: #94a3b8;
}

.ghost-pill {
  background: transparent;
  border: 1px solid #d1d5db;
  border-radius: 999px;
  padding: 0.35rem 0.9rem;
  cursor: pointer;
  font-weight: 600;
  color: #374151;
}

.primary-pill {
  background: linear-gradient(135deg, var(--color-success), var(--color-success-border));
  color: white;
  border: none;
  border-radius: var(--radius-full);
  padding: 0.65rem 1.4rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s ease;
}

.primary-pill:disabled,
.ghost-pill:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.notification-detail-modal,
.platform-popup {
  z-index: 65;
}

.notification-detail-modal {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
}

.notification-detail-modal__panel {
  background: var(--color-card-bg);
  border-radius: 20px;
  padding: 1.5rem;
  width: min(520px, 100%);
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.45);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.notification-detail-modal__header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.notification-detail-modal__title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #0f172a;
}

.notification-detail-modal__subtitle {
  margin: 0.15rem 0 0;
  font-size: 0.85rem;
  color: #475569;
}

.notification-detail-modal__meta {
  display: flex;
  gap: 1rem;
  font-size: 0.85rem;
  color: #6b7280;
}

.notification-detail-modal__body {
  margin: 0;
  color: #111827;
  white-space: pre-wrap;
  line-height: 1.5;
}

.notification-detail-modal__close {
  border: none;
  background: transparent;
  color: #6b7280;
  font-size: 1rem;
  padding: 0;
  cursor: pointer;
}

@media (max-width: 768px) {
  .admin-notifications-page {
    padding: 20px;
  }

  .admin-notifications-page__header {
    flex-direction: column;
  }

  .notification-card {
    grid-template-columns: auto 1fr;
  }

  .notification-card__actions {
    align-items: flex-start;
  }

  .platform-send-panel__selector-grid {
    grid-template-columns: 1fr;
  }
}
</style>
