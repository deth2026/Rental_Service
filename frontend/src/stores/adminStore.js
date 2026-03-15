import { computed, reactive } from 'vue'
import { adminDataSource } from '../services/adminDataSource.js'
import { shopApi } from '../services/api.js'
import { cambodiaDateKey, cambodiaYear, cambodiaWeekdayLabel } from '../utils/cambodiaTime.js'

const state = reactive({
  source: 'demo',
  loading: false,
  error: '',
  api_totals: null,
  users: [],
  shops: [],
  vehicles: [],
  bookings: [],
  commission_rate: 0.15,
})

let hasLoadedOnce = false

const parseDate = (value) => {
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? null : d
}

const fmtNumber = (value) => {
  const n = Number(value || 0)
  return new Intl.NumberFormat('en-US').format(n)
}

const fmtMoneyCompact = (value) => {
  const n = Number(value || 0)
  if (n >= 1000000) return `$${(n / 1000000).toFixed(1)}m`
  if (n >= 1000) return `$${(n / 1000).toFixed(1)}k`
  return `$${n.toFixed(0)}`
}

const sum = (items, selector) => items.reduce((acc, item) => acc + (Number(selector(item)) || 0), 0)

const buildRangeDayKeys = (count) => {
  const now = Date.now()
  const msPerDay = 24 * 60 * 60 * 1000

  const keys = []
  for (let i = count - 1; i >= 0; i -= 1) {
    keys.push(cambodiaDateKey(new Date(now - i * msPerDay)))
  }

  return keys
}

const pctDelta = (current, previous) => {
  const c = Number(current || 0)
  const p = Number(previous || 0)
  if (p <= 0) return c > 0 ? 100 : 0
  return ((c - p) / p) * 100
}

export const useAdminStore = () => {
  const load = async ({ force = false } = {}) => {
    if (state.loading) return
    if (hasLoadedOnce && !force) return

    state.loading = true
    state.error = ''
    try {
      const data = await adminDataSource.load({ preferApi: true })
      state.source = data.source || 'demo'
      state.api_totals = data.api_totals || null
      state.users = Array.isArray(data.users) ? data.users : []
      state.shops = Array.isArray(data.shops) ? data.shops : []
      state.vehicles = Array.isArray(data.vehicles) ? data.vehicles : []
      state.bookings = Array.isArray(data.bookings) ? data.bookings : []
      state.commission_rate = Number(data.commission_rate || 0.15) || 0.15
      hasLoadedOnce = true
    } catch (err) {
      state.error = err?.message || 'Failed to load admin data.'
    } finally {
      state.loading = false
    }
  }

  // Preload data in background - doesn't block UI, uses cached data immediately
  const preload = async () => {
    // If we already have data, don't reload - just return immediately
    if (state.shops.length > 0 || state.users.length > 0) {
      return
    }
    // Otherwise load in background
    await load({ force: true })
  }

  // Force reload from API (clear cache and fetch fresh data)
  const forceReload = async () => {
    hasLoadedOnce = false
    await load({ force: true })
  }

  // Clear demo cache
  const clearDemoCache = () => {
    adminDataSource.clearDemo()
    hasLoadedOnce = false
  }

  const persistDemo = () => {
    adminDataSource.persistDemo({
      generated_at: new Date().toISOString(),
      users: state.users,
      shops: state.shops,
      vehicles: state.vehicles,
      bookings: state.bookings,
      commission_rate: state.commission_rate,
    })
  }

  const updateShopStatus = (shopId, nextStatus) => {
    const idx = state.shops.findIndex((s) => String(s.id) === String(shopId))
    if (idx < 0) return
    const normalized = String(nextStatus || '').toUpperCase()
    state.shops[idx] = { ...state.shops[idx], status: normalized }
    if (state.source === 'demo') persistDemo()
  }

  const setShopStatus = async (shopId, nextStatus) => {
    if (state.source === 'api') {
      const payload = { status: String(nextStatus || '').toLowerCase() }
      const { data } = await shopApi.update(shopId, payload)
      const updated = data?.data || data
      const idx = state.shops.findIndex((s) => String(s.id) === String(shopId))
      if (idx >= 0) state.shops[idx] = updated
      return updated
    }

    const normalized = String(nextStatus || '').toLowerCase()
    const mapped = normalized === 'active' ? 'VERIFIED' : normalized === 'inactive' ? 'PENDING' : String(nextStatus || '')
    updateShopStatus(shopId, mapped)
    return state.shops.find((s) => String(s.id) === String(shopId)) || null
  }

  const addShop = (payload) => {
    const name = String(payload?.name || '').trim()
    if (!name) return null

    const nextId = `SHP-${String(8000 + state.shops.length + Math.floor(Math.random() * 999)).slice(0, 4)}`
    const shop = {
      id: nextId,
      name,
      owner: String(payload?.owner || 'Alex Johnson').trim(),
      location: String(payload?.location || '—').trim(),
      fleet_size: Number(payload?.fleet_size || 0) || 0,
      status: String(payload?.status || 'PENDING').toUpperCase(),
      vehicle_type: String(payload?.vehicle_type || 'motorbike').toLowerCase(),
      created_at: new Date().toISOString(),
    }

    state.shops = [shop, ...state.shops]
    if (state.source === 'demo') persistDemo()
    return shop
  }

  const createShop = async (payload) => {
    if (state.source !== 'api') {
      return addShop({ ...payload, status: 'PENDING' })
    }

    const { data } = await shopApi.create(payload)
    const created = data?.data || data
    state.shops = [created, ...state.shops]
    return created
  }

  const updateShop = async (shopId, payload) => {
    if (state.source !== 'api') {
      const idx = state.shops.findIndex((s) => String(s.id) === String(shopId))
      if (idx < 0) return null
      state.shops[idx] = { ...state.shops[idx], ...payload }
      if (state.source === 'demo') persistDemo()
      return state.shops[idx]
    }

    const { data } = await shopApi.update(shopId, payload)
    const updated = data?.data || data
    const idx = state.shops.findIndex((s) => String(s.id) === String(shopId))
    if (idx >= 0) state.shops[idx] = updated
    return updated
  }

  const deleteShop = async (shopId) => {
    if (state.source !== 'api') {
      state.shops = state.shops.filter((s) => String(s.id) !== String(shopId))
      if (state.source === 'demo') persistDemo()
      return true
    }

    await shopApi.delete(shopId)
    state.shops = state.shops.filter((s) => String(s.id) !== String(shopId))
    return true
  }

  const addUser = (payload) => {
    const name = String(payload?.name || '').trim()
    const email = String(payload?.email || '').trim()
    if (!name || !email) return null

    const nextId = `AUSR-${String(9000 + state.users.length + Math.floor(Math.random() * 999)).slice(0, 4)}`
    const user = {
      id: nextId,
      name,
      email,
      role: String(payload?.role || 'CUSTOMER').toUpperCase(),
      status: String(payload?.status || 'ACTIVE').toUpperCase(),
      created_at: new Date().toISOString(),
    }
    state.users = [user, ...state.users]
    if (state.source === 'demo') persistDemo()
    return user
  }

  const updateUserStatus = (userId, nextStatus) => {
    const idx = state.users.findIndex((u) => String(u.id) === String(userId))
    if (idx < 0) return
    state.users[idx] = { ...state.users[idx], status: String(nextStatus || '').toUpperCase() }
    if (state.source === 'demo') persistDemo()
  }

  const bookingDates = computed(() =>
    state.bookings
      .map((b) => parseDate(b.created_at))
      .filter(Boolean)
      .sort((a, b) => a - b)
  )

  const last7DaysKeys = computed(() => buildRangeDayKeys(7))
  const last14DaysKeys = computed(() => buildRangeDayKeys(14))

  const bookingGrossByDay = computed(() => {
    const keys = last7DaysKeys.value
    const bucketIndex = new Map(keys.map((k, idx) => [k, idx]))

    const buckets = keys.map((key) => {
      const asDate = new Date(`${key}T00:00:00Z`)
      return {
        key,
        label: cambodiaWeekdayLabel(asDate),
        value: 0,
      }
    })

    state.bookings.forEach((b) => {
      const d = parseDate(b.created_at)
      if (!d) return
      const key = cambodiaDateKey(d)
      const idx = bucketIndex.get(key)
      if (idx == null) return
      buckets[idx].value += Number(b.gross_amount || 0)
    })

    return buckets
  })

  const vehicleTypeCounts = computed(() => {
    const counts = { motorbike: 0, bicycle: 0, car: 0, other: 0 }
    state.vehicles.forEach((v) => {
      const t = String(v.type || v.vehicle_type || '').toLowerCase()
      if (t === 'motorbike' || t === 'bike') counts.motorbike += 1
      else if (t === 'bicycle') counts.bicycle += 1
      else if (t === 'car') counts.car += 1
      else counts.other += 1
    })
    return counts
  })

  const totals = computed(() => {
    const totalUsers = Number(state.api_totals?.users ?? state.users.length) || 0
    const totalShops = Number(state.api_totals?.shops ?? state.shops.length) || 0
    const totalVehicles = Number(state.api_totals?.vehicles ?? state.vehicles.length) || 0
    const totalBookings = Number(state.api_totals?.bookings ?? state.bookings.length) || 0
    const totalRevenue = sum(state.bookings, (b) => b.gross_amount || 0)

    return { totalUsers, totalShops, totalVehicles, totalBookings, totalRevenue }
  })

  const trends = computed(() => {
    const keys = last14DaysKeys.value
    const prevKeys = new Set(keys.slice(0, 7))
    const currKeys = new Set(keys.slice(7))

    const itemsInKeySet = (items, keySet) =>
      items.filter((b) => {
        const d = parseDate(b.created_at)
        if (!d) return false
        return keySet.has(cambodiaDateKey(d))
      })

    const currentBookings = itemsInKeySet(state.bookings, currKeys)
    const previousBookings = itemsInKeySet(state.bookings, prevKeys)

    const currentRevenue = sum(currentBookings, (b) => b.gross_amount || 0)
    const previousRevenue = sum(previousBookings, (b) => b.gross_amount || 0)

    const currentUsers = itemsInKeySet(state.users, currKeys).length
    const previousUsers = itemsInKeySet(state.users, prevKeys).length

    const currentShops = itemsInKeySet(state.shops, currKeys).length
    const previousShops = itemsInKeySet(state.shops, prevKeys).length

    const currentVehicles = itemsInKeySet(state.vehicles, currKeys).length
    const previousVehicles = itemsInKeySet(state.vehicles, prevKeys).length

    return {
      users: Math.round(pctDelta(currentUsers, previousUsers)),
      shops: Math.round(pctDelta(currentShops, previousShops)),
      vehicles: Math.round(pctDelta(currentVehicles, previousVehicles)),
      bookings: Math.round(pctDelta(currentBookings.length, previousBookings.length)),
      revenue: Math.round(pctDelta(currentRevenue, previousRevenue)),
    }
  })

  const formatted = {
    fmtNumber,
    fmtMoneyCompact,
  }

  const recentShops = computed(() =>
    [...state.shops]
      .sort((a, b) => (parseDate(b.created_at) || 0) - (parseDate(a.created_at) || 0))
      .slice(0, 3)
  )

  const ytd = computed(() => {
    const year = cambodiaYear(new Date())
    const todayKey = cambodiaDateKey(new Date())

    const ytdBookings = state.bookings.filter((b) => {
      const d = parseDate(b.created_at)
      if (!d) return false
      if (cambodiaYear(d) !== year) return false
      return cambodiaDateKey(d) <= todayKey
    })
    const gross = sum(ytdBookings, (b) => b.gross_amount || 0)
    const commission = sum(ytdBookings, (b) => b.commission_amount || 0)
    const refundedCount = ytdBookings.filter((b) => Boolean(b.refunded)).length
    const refundRate = ytdBookings.length ? (refundedCount / ytdBookings.length) * 100 : 0

    const avgBookingValue = ytdBookings.length ? gross / ytdBookings.length : 0
    return { gross, commission, refundRate, avgBookingValue }
  })

  return {
    state,
    load,
    preload,
    updateShopStatus,
    setShopStatus,
    addShop,
    createShop,
    updateShop,
    deleteShop,
    addUser,
    updateUserStatus,
    bookingGrossByDay,
    vehicleTypeCounts,
    totals,
    trends,
    recentShops,
    ytd,
    bookingDates,
    formatted,
  }
}
