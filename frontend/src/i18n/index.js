import { reactive, computed } from 'vue'
import en from './en.js'
import kh from './kh.js'

// Messages
const messages = { en, kh }

// Reactive state
const state = reactive({
  locale: localStorage.getItem('locale') || 'en'
})

// Get current translations
const translations = computed(() => messages[state.locale])

// Translation function
export function t(key) {
  return translations.value[key] || key
}

// Set locale
export function setLocale(locale) {
  if (locale === 'en' || locale === 'kh') {
    state.locale = locale
    localStorage.setItem('locale', locale)
  }
}

// Get locale
export function getLocale() {
  return state.locale
}

// Get reactive state for use in templates
export function useI18n() {
  return {
    t,
    locale: computed({
      get: () => state.locale,
      set: (val) => setLocale(val)
    }),
    state
  }
}

// Export for global use
export default {
  install(app) {
    app.config.globalProperties.$t = t
    app.config.globalProperties.$setLocale = setLocale
    app.config.globalProperties.$getLocale = getLocale
  }
}

export { state }
