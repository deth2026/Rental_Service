import { computed, onMounted, ref, watch } from 'vue'

const STORAGE_KEY = 'motopremium_theme'

const resolveInitialTheme = () => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved === 'dark' || saved === 'light') return saved
  if (typeof window !== 'undefined' && window.matchMedia) {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }
  return 'light'
}

export const useTheme = () => {
  const theme = ref(resolveInitialTheme())

  const applyTheme = (nextTheme) => {
    if (typeof document === 'undefined') return
    document.documentElement.dataset.theme = nextTheme
  }

  const toggleTheme = () => {
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
  }

  const isDark = computed(() => theme.value === 'dark')

  onMounted(() => {
    applyTheme(theme.value)
  })

  watch(theme, (nextTheme) => {
    localStorage.setItem(STORAGE_KEY, nextTheme)
    applyTheme(nextTheme)
  })

  return {
    theme,
    isDark,
    toggleTheme,
  }
}

