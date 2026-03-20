import { computed, ref } from 'vue'
import api from '@/services/api'

const normalizeAvatar = (name = 'Notification') => {
  const sanitized = encodeURIComponent(name.trim() || 'Notification')
  return `https://ui-avatars.com/api/?name=${sanitized}&background=7c3aed&color=fff&rounded=true`
}

const parseId = (value) => {
  if (value === null || value === undefined) {
    return null
  }
  const id = Number(value)
  return Number.isFinite(id) ? id : null
}

const normalizeNotification = (record) => {
  const user = record.user || {}
  const userName = user.name || 'System'
  const avatar =
    user.avatar_url ||
    user.profile_picture ||
    user.img_url ||
    normalizeAvatar(userName)
  const normalizedUser = {
    id: parseId(user.id ?? record.user_id),
    name: userName,
    avatar,
    email: user.email || ''
  }

  const relatedSender = record.related?.sender
  const senderName = relatedSender?.name || 'Sender'
  const normalizedRelatedSender = relatedSender
    ? {
        id: parseId(relatedSender.id),
        name: senderName,
        avatar:
          relatedSender.avatar_url ||
          relatedSender.profile_picture ||
          relatedSender.img_url ||
          normalizeAvatar(senderName),
        email: relatedSender.email || ''
      }
    : null

  const relatedModel =
    record.related
      ? {
          ...record.related,
          id: parseId(record.related.id ?? record.related_id),
          type: record.related_type || record.related?.type || null
        }
      : null

  const detailSubject = record.related?.subject || record.title || 'Notification'
  const detailBody = record.related?.body || record.message || ''

  return {
    id: String(record.id),
    action: record.title || 'Notification',
    message: record.message || '',
    type: record.type || 'general',
    timestamp: record.created_at,
    status: record.is_read ? 'read' : 'unread',
    isRead: Boolean(record.is_read),
    shopId: record.shop_id || null,
    role: record.role || null,
    related: {
      id: record.related_id,
      type: record.related_type
    },
    user: normalizedUser,
    relatedSender: normalizedRelatedSender,
    relatedModel,
    detailSubject,
    detailBody
  }
}

const notifications = ref([])
const isLoading = ref(false)
const error = ref('')

const notificationsSorted = computed(() => {
  return [...notifications.value].sort((a, b) => {
    const timeA = a.timestamp ? new Date(a.timestamp).getTime() : 0
    const timeB = b.timestamp ? new Date(b.timestamp).getTime() : 0
    return timeB - timeA
  })
})

const unreadCount = computed(() =>
  notificationsSorted.value.filter((item) => item.status === 'unread').length
)

const buildQueryParams = (shopId, extraParams = {}) => {
  const params = { ...extraParams }
  if (shopId) {
    params.shop_id = shopId
  }
  return params
}

const loadNotifications = async (shopId = null, extraParams = {}) => {
  if (isLoading.value) return
  isLoading.value = true
  error.value = ''
  try {
    const response = await api.get('/notifications', {
      params: buildQueryParams(shopId, extraParams)
    })
    const payload = Array.isArray(response?.data) ? response.data : []
    notifications.value = payload.map(normalizeNotification)
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Unable to load notifications.'
  } finally {
    isLoading.value = false
  }
}

const patchNotification = async (id, updates) => {
  try {
    const response = await api.patch(`/notifications/${id}`, updates)
    return normalizeNotification(response.data)
  } catch (err) {
    console.error('Failed to patch notification', err)
    throw err
  }
}

const toggleReadStatus = async (id) => {
  const current = notifications.value.find((item) => item.id === id)
  if (!current) return
  const nextIsRead = !current.isRead
  const updated = await patchNotification(id, { is_read: nextIsRead })
  notifications.value = notifications.value.map((item) =>
    item.id === id ? { ...item, ...updated, isRead: nextIsRead, status: nextIsRead ? 'read' : 'unread' } : item
  )
}

const markAllAsRead = async (shopId = null) => {
  try {
    await api.patch('/notifications/mark-all', buildQueryParams(shopId))
    notifications.value = notifications.value.map((item) => ({
      ...item,
      isRead: true,
      status: 'read'
    }))
  } catch (err) {
    console.error('Unable to mark notifications as read', err)
    throw err
  }
}

export function useNotifications() {
  return {
    notifications: notificationsSorted,
    unreadCount,
    isLoading,
    error,
    loadNotifications,
    toggleReadStatus,
    markAllAsRead
  }
}
