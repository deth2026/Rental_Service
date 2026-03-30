import { computed, reactive } from 'vue'

const DEFAULT_DURATION = 3200
let nextId = 1

const state = reactive({
  items: [],
  // When true, only toasts marked important (or errors/successes) will be shown.
  showOnlyImportant: true,
})

const remove = (id) => {
  const index = state.items.findIndex((item) => item.id === id)
  if (index !== -1) state.items.splice(index, 1)
}

const push = (message, type = 'info', options = {}) => {
  const text = String(message || '').trim()
  if (!text) return null

  const duration = Number(options.duration ?? DEFAULT_DURATION)
  const id = nextId++

  // Determine whether this toast should be considered important.
  const isImportant = Boolean(options.important) || type === 'error' || type === 'success'

  // If configured to only show important toasts and this one is not important, skip it.
  if (state.showOnlyImportant && !isImportant) return null

  state.items.push({
    id,
    message: text,
    type,
    createdAt: Date.now(),
  })

  if (duration > 0) {
    window.setTimeout(() => {
      remove(id)
    }, duration)
  }

  return id
}

export const useToastState = () => state

export const useToast = () => {
  const latest = computed(() => {
    const last = state.items[state.items.length - 1]
    return last
      ? { message: last.message, type: last.type, show: true }
      : { message: '', type: 'info', show: false }
  })

  return {
    toast: latest,
    showToast: push,
    success: (message, options) => push(message, 'success', options),
    error: (message, options) => push(message, 'error', options),
    warning: (message, options) => push(message, 'warning', options),
    info: (message, options) => push(message, 'info', options),
    removeToast: remove,
  }
}
