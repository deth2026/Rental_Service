export const LOCATION_ACCESS_KEY = 'chong_choul_user_location'
export const LEGACY_LOCATION_ACCESS_KEY = 'user_location'

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
  const primary = parseLocation(localStorage.getItem(LOCATION_ACCESS_KEY))
  if (primary) return primary

  const legacy = parseLocation(localStorage.getItem(LEGACY_LOCATION_ACCESS_KEY))
  if (legacy) {
    localStorage.setItem(LOCATION_ACCESS_KEY, JSON.stringify(legacy))
    return legacy
  }

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
  localStorage.setItem(LOCATION_ACCESS_KEY, JSON.stringify(location))
  localStorage.removeItem(LEGACY_LOCATION_ACCESS_KEY)
  return location
}
