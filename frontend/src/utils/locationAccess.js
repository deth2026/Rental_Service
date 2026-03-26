export const LOCATION_ACCESS_KEY = 'chong_choul_user_location'
export const LEGACY_LOCATION_ACCESS_KEY = 'user_location'

const safeSessionStorage = () => {
  try {
    return window.sessionStorage
  } catch {
    return null
  }
}

const clearPersistentLegacyStorage = () => {
  try {
    localStorage.removeItem(LOCATION_ACCESS_KEY)
    localStorage.removeItem(LEGACY_LOCATION_ACCESS_KEY)
  } catch {
    // Ignore environments where localStorage is unavailable.
  }
}

const parseLocation = (raw) => {
  if (!raw) return null
  try {
    const parsed = JSON.parse(raw)
    const lat = Number(parsed?.lat)
    const lng = Number(parsed?.lng)
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null
    return {
      lat,
      lng,
      timestamp: Number(parsed?.timestamp) || Date.now(),
    }
  } catch {
    return null
  }
}

export const readStoredLocation = () => {
  const storage = safeSessionStorage()
  const current = storage ? parseLocation(storage.getItem(LOCATION_ACCESS_KEY)) : null
  if (current) return current
  // Clean up old persistent location so new sessions always ask again.
  clearPersistentLegacyStorage()
  return null
}

export const hasLocationAccess = () => Boolean(readStoredLocation())

export const saveLocationAccess = (coords) => {
  const location = {
    lat: Number(coords?.lat),
    lng: Number(coords?.lng),
    timestamp: Date.now(),
  }
  if (!Number.isFinite(location.lat) || !Number.isFinite(location.lng)) {
    return null
  }
  const storage = safeSessionStorage()
  if (!storage) return null
  storage.setItem(LOCATION_ACCESS_KEY, JSON.stringify(location))
  storage.removeItem(LEGACY_LOCATION_ACCESS_KEY)
  clearPersistentLegacyStorage()
  return location
}

export const clearLocationAccess = () => {
  const storage = safeSessionStorage()
  if (storage) {
    storage.removeItem(LOCATION_ACCESS_KEY)
    storage.removeItem(LEGACY_LOCATION_ACCESS_KEY)
  }
  clearPersistentLegacyStorage()
}
