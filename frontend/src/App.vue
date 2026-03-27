<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import SplashScreen from './components/SplashScreen.vue'
import LocationPrompt from './components/LocationPrompt.vue'
import { hasLocationAccess, saveLocationAccess } from './utils/locationAccess'

const router = useRouter()
const route = useRoute()

const showSplash = ref(true)
const showLocationPrompt = ref(false)
const locationGranted = ref(false)
const requestingLocation = ref(false)
const locationError = ref('')
const LOCATION_PROMPT_DISMISSED_KEY = 'chong_choul_location_prompt_dismissed'

const hasDismissedLocationPrompt = () => {
  try {
    return sessionStorage.getItem(LOCATION_PROMPT_DISMISSED_KEY) === '1'
  } catch {
    return false
  }
}

const setLocationPromptDismissed = (dismissed) => {
  try {
    if (dismissed) sessionStorage.setItem(LOCATION_PROMPT_DISMISSED_KEY, '1')
    else sessionStorage.removeItem(LOCATION_PROMPT_DISMISSED_KEY)
  } catch {
    // Ignore storage write errors.
  }
}

const syncLocationFromStorage = () => {
  locationGranted.value = hasLocationAccess()
  if (locationGranted.value) setLocationPromptDismissed(false)
}

onMounted(() => {
  locationGranted.value = hasLocationAccess()
  window.addEventListener('location-access-updated', syncLocationFromStorage)

  setTimeout(() => {
    showSplash.value = false
    if (!locationGranted.value && !hasDismissedLocationPrompt()) {
      setTimeout(() => openLocationPrompt(), 300)
    }
  }, 2500)
})

onBeforeUnmount(() => {
  window.removeEventListener('location-access-updated', syncLocationFromStorage)
})

const handleAllowLocation = () => {
  if (requestingLocation.value) return

  if (!navigator?.geolocation) {
    locationError.value = 'Geolocation is not supported by your browser.'
    return
  }

  requestingLocation.value = true
  locationError.value = ''

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const location = saveLocationAccess({
        lat: position.coords.latitude,
        lng: position.coords.longitude,
      })

      if (!location) {
        locationError.value = 'Could not save your location. Please try again.'
        requestingLocation.value = false
        return
      }

      locationGranted.value = true
      showLocationPrompt.value = false
      requestingLocation.value = false
      setLocationPromptDismissed(false)

      window.dispatchEvent(
        new CustomEvent('location-access-updated', {
          detail: { granted: true, location },
        })
      )
    },
    (error) => {
      const denied = error?.code === 1
      locationError.value = denied
        ? 'Location access was denied. Please allow location to continue.'
        : 'Unable to detect your location. Please try again.'
      requestingLocation.value = false
      locationGranted.value = false
    },
    { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
  )
}

const handleCloseLocation = () => {
  showLocationPrompt.value = false
  if (!locationGranted.value) setLocationPromptDismissed(true)
}

const openLocationPrompt = () => {
  locationError.value = ''
  setLocationPromptDismissed(false)
  showLocationPrompt.value = true
}

const topLevelRoutes = new Set([
  '/',
  '/home',
  '/view_shop',
  '/dashboard',
  '/admin',
])

const showGlobalBackButton = computed(() => {
  if (showSplash.value) return false
  if (route.path.startsWith('/admin')) return false
  if (route.path.startsWith('/shop/notifications')) return false
  if (route.path.startsWith('/booking')) return false
  if (usesUserNavbar.value) return false
  return !topLevelRoutes.has(route.path)
})

const isAuthEntryPage = computed(() => {
  return route.path === '/login' || route.path === '/register'
})

const usesUserNavbar = computed(() => {
  const path = route.path
  return (
    path.startsWith('/my-bookings') ||
    path.startsWith('/promotions') ||
    path.startsWith('/notifications') ||
    path.startsWith('/settings') ||
    path.startsWith('/user/profile') ||
    path.startsWith('/vehicles') ||
    /^\/shop\/[^/]+\/vehicles\/?$/.test(path)
  )
})

const getFallbackRoute = () => {
  try {
    const rawUser = localStorage.getItem('user')
    if (rawUser) {
      const parsedUser = JSON.parse(rawUser)
      const role = String(parsedUser?.role || '').toLowerCase()
      if (role === 'admin') return '/admin'
      if (role === 'shop_owner') return '/dashboard'
      if (role) return '/view_shop'
    }
  } catch {
    // Keep default fallback route.
  }

  return '/view_shop'
}

const goBack = () => {
  if (isAuthEntryPage.value) {
    router.push('/')
    return
  }

  if (window.history.length > 1) {
    router.back()
    return
  }
  router.push(getFallbackRoute())
}
</script>

<template>
  <div id="app">
    <SplashScreen :show="showSplash" :location-required="!locationGranted" />

    <LocationPrompt
      :show="showLocationPrompt"
      :loading="requestingLocation"
      :error="locationError"
      @confirm="handleAllowLocation"
      @close="handleCloseLocation"
    />

    <router-view
      v-if="!showSplash"
      :locationGranted="locationGranted"
      @request-location="openLocationPrompt"
    />

    <button
      v-if="showGlobalBackButton"
      type="button"
      class="global-back-btn"
      @click="goBack"
      :aria-label="isAuthEntryPage ? 'Go to Home View' : 'Go back'"
      :title="isAuthEntryPage ? 'Go to Home View' : 'Go back'"
    >
      <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <span>{{ isAuthEntryPage ? 'Home View' : 'Back' }}</span>
    </button>
  </div>
</template>

<style>
* {
  box-sizing: border-box;
}

html,
body,
#app {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.global-back-btn {
  position: fixed;
  top: 18px;
  left: 18px;
  z-index: 110000;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: none;
  border-radius: 999px;
  padding: 10px 14px;
  font-weight: 700;
  font-size: 0.88rem;
  color: #0f2b66;
  background: rgba(255, 255, 255, 0.9);
  box-shadow: 0 12px 28px rgba(2, 6, 23, 0.18);
  backdrop-filter: blur(8px);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.global-back-btn svg {
  width: 18px;
  height: 18px;
}

.global-back-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 30px rgba(2, 6, 23, 0.22);
}

@media (max-width: 640px) {
  .global-back-btn {
    top: 12px;
    left: 12px;
    padding: 8px 12px;
    font-size: 0.82rem;
  }
}
</style>
