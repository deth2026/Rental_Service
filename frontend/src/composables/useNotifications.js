import { computed, ref } from 'vue'
import api from '@/services/api'
import userService from '@/services/userService'

const READ_STATE_KEY = 'rental_user_notification_state'

const bookingStatusTemplates = {
  pending: {
    action: 'Booking request sent',
    message: ({ vehicleName, shopName }) => `Your request for ${vehicleName} at ${shopName} is awaiting confirmation.`,
    type: 'booking',
    defaultStatus: 'unread'
  },
  approved: {
    action: 'Booking confirmed',
    message: ({ vehicleName, shopName }) => `${shopName} confirmed your ${vehicleName} booking. Pick-up is on schedule.`,
    type: 'approval',
    defaultStatus: 'unread'
  },
  rejected: {
    action: 'Booking rejected',
    message: ({ vehicleName, shopName }) => `${shopName} could not confirm ${vehicleName} for the selected dates.`,
    type: 'rejection',
    defaultStatus: 'unread'
  },
  cancelled: {
    action: 'Booking cancelled',
    message: ({ vehicleName, shopName }) => `Your ${vehicleName} booking at ${shopName} was cancelled. Please review the booking details.`,
    type: 'rejection',
    defaultStatus: 'unread'
  },
  completed: {
    action: 'Booking completed',
    message: ({ vehicleName, shopName }) => `Enjoyed your ${vehicleName} trip with ${shopName}? Leave feedback for the team.`,
    type: 'booking',
    defaultStatus: 'read'
  },
  default: {
    action: 'Booking update',
    message: ({ vehicleName, shopName, booking }) =>
      `Status updated to "${booking.status || 'pending'}" for ${vehicleName} at ${shopName}.`,
    type: 'booking',
    defaultStatus: 'unread'
  }
}

const notifications = ref([])
const readStates = ref({})
const isLoading = ref(false)
const error = ref('')

const normalizeAvatar = (name = 'Shop') => {
  const normalized = encodeURIComponent(name.trim() || 'Shop')
  return `https://ui-avatars.com/api/?name=${normalized}&background=7c3aed&color=fff&rounded=true`
}

const formatVehicleName = (vehicle = {}) => {
  const brand = String(vehicle.brand || vehicle.name || '').trim()
  const model = String(vehicle.model || '').trim()
  const fallback = vehicle.name || 'vehicle'
  const text = [brand, model].filter(Boolean).join(' ').trim()
  return text || fallback
}

const applyReadState = (notification) => {
  const overridden = readStates.value[notification.id]
  return {
    ...notification,
    status: overridden ?? notification.status ?? 'unread'
  }
}

const notificationsWithState = computed(() => notifications.value.map(applyReadState))
const unreadCount = computed(() => notificationsWithState.value.filter((item) => item.status === 'unread').length)

const persistReadStates = () => {
  if (typeof window === 'undefined') return
  try {
    localStorage.setItem(READ_STATE_KEY, JSON.stringify(readStates.value))
  } catch (e) {
    console.error('Unable to persist notification read state', e)
  }
}

const loadReadStates = () => {
  if (typeof window === 'undefined') return
  try {
    const stored = localStorage.getItem(READ_STATE_KEY)
    if (!stored) return
    readStates.value = JSON.parse(stored)
  } catch {
    readStates.value = {}
  }
}

const normalizeBookingNotification = (booking) => {
  const statusKey = String(booking.status || 'pending').toLowerCase()
  const template = bookingStatusTemplates[statusKey] ?? bookingStatusTemplates.default
  const vehicleName = formatVehicleName(booking.vehicle)
  const shopName = booking.shop?.name || 'the shop'
  const message = typeof template.message === 'function'
    ? template.message({ booking, vehicleName, shopName })
    : template.message
  const shopOwner = booking.shop?.owner
  const fallbackUser = booking.user || userService.getCurrentUser() || {}
  const displayUser = (shopOwner && shopOwner.name ? shopOwner : fallbackUser) || {}
  const userName = displayUser.name || shopName
  const avatar =
    displayUser.avatar_url ||
    displayUser.profile_picture ||
    displayUser.img_url ||
    normalizeAvatar(userName)

  return {
    id: `booking-${booking.id}`,
    user: {
      name: userName,
      avatar,
    },
    action: template.action,
    message,
    timestamp: booking.updated_at || booking.start_date || booking.created_at || new Date().toISOString(),
    type: template.type,
    status: template.defaultStatus || 'unread'
  }
}

const loadNotifications = async () => {
  if (isLoading.value) return
  isLoading.value = true
  error.value = ''
  try {
    const response = await api.get('/my-bookings')
    const data = Array.isArray(response?.data) ? response.data : []
    notifications.value = data.map(normalizeBookingNotification)
  } catch (err) {
    error.value = err?.response?.data?.message || err?.message || 'Unable to load notifications.'
  } finally {
    isLoading.value = false
  }
}

const setReadState = (id, nextStatus) => {
  readStates.value = {
    ...readStates.value,
    [id]: nextStatus
  }
  persistReadStates()
}

const markAllAsRead = () => {
  const nextState = { ...readStates.value }
  notificationsWithState.value.forEach((notification) => {
    if (notification.status === 'unread') {
      nextState[notification.id] = 'read'
    }
  })
  readStates.value = nextState
  persistReadStates()
}

const toggleReadStatus = (id) => {
  const current = notificationsWithState.value.find((notification) => notification.id === id)?.status || 'unread'
  const next = current === 'unread' ? 'read' : 'unread'
  setReadState(id, next)
}

loadReadStates()

export function useNotifications() {
  return {
    notifications: notificationsWithState,
    unreadCount,
    isLoading,
    error,
    loadNotifications,
    markAllAsRead,
    toggleReadStatus
  }
}
