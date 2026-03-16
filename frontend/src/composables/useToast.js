<<<<<<< HEAD
import { ref } from 'vue'

const toasts = ref([])
let nextId = 1

const removeToast = (id) => {
  toasts.value = toasts.value.filter((t) => t.id !== id)
}

const pushToast = (message, { type = 'success', duration = 4500 } = {}) => {
  const text = String(message || '').trim()
  if (!text) return null

  const id = nextId++
  const toast = { id, message: text, type }
  toasts.value = [...toasts.value, toast].slice(-4)

  if (duration > 0) {
    window.setTimeout(() => removeToast(id), duration)
  }

  return id
}

export const useToast = () => ({
  toasts,
  pushToast,
  removeToast,
  success: (message, options) => pushToast(message, { ...options, type: 'success' }),
  error: (message, options) => pushToast(message, { ...options, type: 'error' }),
  info: (message, options) => pushToast(message, { ...options, type: 'info' }),
})
=======
const createLog = (type, message) => {
  const tag = type === 'success' ? '✅' : '⚠️'
  const text = typeof message === 'string' ? message : JSON.stringify(message || '')
  console[type === 'success' ? 'log' : 'error'](`${tag} ${text}`)
}

export const useToast = () => ({
  success: (message) => createLog('success', message),
  error: (message) => createLog('error', message),
})

export default useToast
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
