import { defineStore } from 'pinia'
import { ref } from 'vue'
import api, { shopApi } from '../services/api.js'

export const useAdminStore = defineStore('admin', () => {
  // Stats refs
  const totals = ref({ totalUsers: 0, totalShops: 0, totalVehicles: 0, totalBookings: 0 })
  const trends = ref({ users: 12, shops: 5, vehicles: 8, bookings: 15, revenue: 22 })
  const bookingGrossByDay = ref([])
  const paymentTotal = ref(0)
  const paymentRecords = ref([])
  const vehicleTypeCounts = ref({ motorbike: 0, bicycle: 0, car: 0, other: 0 })
  const recentShops = ref([])
  
  // Data state
  const state = ref({ vehicles: [], shops: [], users: [], bookings: [], source: 'api' })
  
  // Performance: Cache timestamp
  const lastLoad = ref(0)
  const CACHE_TTL = 30000 // 30 seconds cache

  const formatted = {
    fmtNumber: (num) => new Intl.NumberFormat().format(num || 0),
    fmtMoneyCompact: (num) => new Intl.NumberFormat('en-US', {
      style: 'currency', currency: 'USD', notation: 'compact'
    }).format(num || 0)
  }

  const toAmount = (booking) => Number(
    booking?.total_amount ??
    booking?.total_price ??
    booking?.price ??
    booking?.gross_amount ??
    booking?.amount ??
    0
  ) || 0

  const toDate = (value) => {
    if (!value) return null
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : d
  }

  const percentageTrend = (current, previous) => {
    const prev = Number(previous || 0)
    const curr = Number(current || 0)
    if (prev <= 0) return curr > 0 ? 100 : 0
    return Math.round(((curr - prev) / prev) * 100)
  }

  const recalculateTotals = () => {
    totals.value = {
      totalUsers: state.value.users.length,
      totalShops: state.value.shops.length,
      totalVehicles: state.value.vehicles.length,
      totalBookings: state.value.bookings.length
    }
  }

  const refreshRecentShops = () => {
    recentShops.value = [...state.value.shops]
      .sort((a, b) => (b.id || 0) - (a.id || 0))
      .slice(0, 5)
  }

  const upsertById = (items, item) => {
    if (!item || item.id == null) return items
    const id = Number(item.id)
    const idx = items.findIndex((x) => Number(x.id) === id)
    if (idx === -1) return [item, ...items]
    const clone = [...items]
    clone[idx] = { ...clone[idx], ...item }
    return clone
  }

  /**
   * Optimized load function with caching and efficient data processing using api.js
   */
  const load = async (options = {}) => {
    const now = Date.now()
    if (!options.force && now - lastLoad.value < CACHE_TTL && state.value.shops.length > 0) {
      return // Use cached data
    }

    try {
      // Parallel fetch for speed using api instance
      const [usersRes, shopsRes, vehiclesRes, bookingsRes, paymentsRes] = await Promise.allSettled([
        api.get('/users'),
        api.get('/shops'),
        api.get('/vehicles'),
        api.get('/bookings'),
        api.get('/payments')
      ])

      // Batch state updates to minimize reactivity triggers
      const nextState = { users: [], shops: [], vehicles: [], bookings: [], source: 'api' }

      if (usersRes.status === 'fulfilled') {
        const data = usersRes.value.data
        nextState.users = Array.isArray(data) ? data : (data.data || [])
      }
      if (shopsRes.status === 'fulfilled') {
        const data = shopsRes.value.data
        nextState.shops = Array.isArray(data) ? data : (data.data || [])
      }
      if (vehiclesRes.status === 'fulfilled') {
        const data = vehiclesRes.value.data
        nextState.vehicles = Array.isArray(data) ? data : (data.data || [])
      }
      if (bookingsRes.status === 'fulfilled') {
        const data = bookingsRes.value.data
        nextState.bookings = Array.isArray(data) ? data : (data.data || [])
      }
      if (paymentsRes.status === 'fulfilled') {
        const data = paymentsRes.value.data
        paymentRecords.value = Array.isArray(data) ? data : (data.data || [])
      } else {
        paymentRecords.value = []
      }

      // Normalize booking fields so all admin pages can read consistent values.
      nextState.bookings = nextState.bookings.map((booking) => {
        const amount = toAmount(booking)
        const totalDays = Number(booking.total_days ?? booking.days ?? 1) || 1
        return {
          ...booking,
          total_amount: amount,
          total_price: Number(booking.total_price ?? amount) || amount,
          price: Number(booking.price ?? amount) || amount,
          gross_amount: Number(booking.gross_amount ?? amount) || amount,
          days: totalDays,
          total_days: totalDays,
        }
      })

      // Single-pass data processing for stats
      const counts = { motorbike: 0, bicycle: 0, car: 0, other: 0 }
      nextState.vehicles.forEach(v => {
        const t = (v.type || v.vehicle_type || '').toLowerCase()
        if (t.includes('bike') && !t.includes('bicycle')) counts.motorbike++
        else if (t.includes('bicycle')) counts.bicycle++
        else if (t.includes('car')) counts.car++
        else counts.other++
      })

      // Revenue Calculation (Last 7 Days) - Only count bookings with paid/completed status
      const today = new Date()
      const grossByDayMap = new Map()
      const validStatuses = ['paid', 'completed', 'confirmed']
      for (let i = 0; i < 7; i++) {
        const d = new Date(today); d.setDate(d.getDate() - i)
        grossByDayMap.set(d.toISOString().split('T')[0], { val: 0, label: d.toLocaleDateString('en-US', { weekday: 'short' }) })
      }

      nextState.bookings.forEach(b => {
        const status = String(b.status || '').toLowerCase()
        if (validStatuses.includes(status)) {
          const bDate = String(b.start_date || b.created_at || '').split(' ')[0]
          if (grossByDayMap.has(bDate)) {
            const entry = grossByDayMap.get(bDate)
            entry.val += toAmount(b)
          }
        }
      })

      // Dynamic trend tracking based on latest 7 days vs previous 7 days
      const now = new Date()
      const currentStart = new Date(now); currentStart.setDate(currentStart.getDate() - 6)
      const previousStart = new Date(now); previousStart.setDate(previousStart.getDate() - 13)

      const isCurrentWindow = (d) => d && d >= currentStart
      const isPreviousWindow = (d) => d && d >= previousStart && d < currentStart

      const usersCurrent = nextState.users.filter((u) => isCurrentWindow(toDate(u.created_at))).length
      const usersPrevious = nextState.users.filter((u) => isPreviousWindow(toDate(u.created_at))).length

      const shopsCurrent = nextState.shops.filter((s) => isCurrentWindow(toDate(s.created_at))).length
      const shopsPrevious = nextState.shops.filter((s) => isPreviousWindow(toDate(s.created_at))).length

      const vehiclesCurrent = nextState.vehicles.filter((v) => isCurrentWindow(toDate(v.created_at))).length
      const vehiclesPrevious = nextState.vehicles.filter((v) => isPreviousWindow(toDate(v.created_at))).length

      const bookingsCurrent = nextState.bookings.filter((b) => isCurrentWindow(toDate(b.start_date || b.created_at))).length
      const bookingsPrevious = nextState.bookings.filter((b) => isPreviousWindow(toDate(b.start_date || b.created_at))).length

      const revenueCurrent = nextState.bookings
        .filter((b) => {
          const status = String(b.status || '').toLowerCase()
          return validStatuses.includes(status) && isCurrentWindow(toDate(b.start_date || b.created_at))
        })
        .reduce((sum, b) => sum + toAmount(b), 0)
      const revenuePrevious = nextState.bookings
        .filter((b) => {
          const status = String(b.status || '').toLowerCase()
          return validStatuses.includes(status) && isPreviousWindow(toDate(b.start_date || b.created_at))
        })
        .reduce((sum, b) => sum + toAmount(b), 0)

      // Update refs once all processing is done
      state.value = nextState
      recalculateTotals()
      vehicleTypeCounts.value = counts
      bookingGrossByDay.value = Array.from(grossByDayMap.entries())
        .map(([key, entry]) => ({ key, value: entry.val, label: entry.label }))
        .reverse()

      paymentTotal.value = paymentRecords.value.reduce((sum, item) => {
        const amt = Number(item.amount ?? item.total_amount ?? item.value ?? 0)
        return sum + (Number.isFinite(amt) ? amt : 0)
      }, 0)

      trends.value = {
        users: percentageTrend(usersCurrent, usersPrevious),
        shops: percentageTrend(shopsCurrent, shopsPrevious),
        vehicles: percentageTrend(vehiclesCurrent, vehiclesPrevious),
        bookings: percentageTrend(bookingsCurrent, bookingsPrevious),
        revenue: percentageTrend(revenueCurrent, revenuePrevious)
      }

      refreshRecentShops()

      lastLoad.value = now
    } catch (error) {
      console.error('Admin Store Load Error:', error)
    }
  }

  const setShopStatus = async (shopId, status) => {
    try {
      const response = await api.put(`/shops/${shopId}`, { status })
      const updated = response.data?.data || response.data
      if (updated?.id != null) {
        state.value = {
          ...state.value,
          shops: upsertById(state.value.shops, updated)
        }
        refreshRecentShops()
      }
    } catch (error) {
      console.error('Failed to update shop status:', error)
      throw error
    }
  }

  const updateShop = async (shopId, shopData) => {
    try {
      // If the payload is FormData, use shopApi.update which handles multipart + _method shim
      let response
      if (typeof FormData !== 'undefined' && shopData instanceof FormData) {
        response = await shopApi.update(shopId, shopData)
      } else {
        response = await api.put(`/shops/${shopId}`, shopData)
      }
      const updated = response.data?.data || response.data
      if (updated?.id != null) {
        state.value = {
          ...state.value,
          shops: upsertById(state.value.shops, updated)
        }
        refreshRecentShops()
      }
      return updated
    } catch (error) {
      console.error('Update shop failed:', error)
      throw error
    }
  }

  const createShop = async (shopData) => {
    try {
      let response
      if (typeof FormData !== 'undefined' && shopData instanceof FormData) {
        response = await shopApi.create(shopData)
      } else {
        response = await api.post('/shops', shopData)
      }
      const created = response.data?.data || response.data
      if (created?.id != null) {
        state.value = {
          ...state.value,
          shops: [created, ...state.value.shops]
        }
        recalculateTotals()
        refreshRecentShops()
      }
      return created
    } catch (error) {
      console.error('Create shop failed:', error)
      throw error
    }
  }

  const addUser = async (userData) => {
    try {
      const response = await api.post('/users', userData)
      const created = response.data?.data || response.data
      if (created?.id != null) {
        state.value = {
          ...state.value,
          users: [created, ...state.value.users]
        }
        recalculateTotals()
      }
      return created
    } catch (error) {
      console.error('Failed to add user:', error)
      throw error
    }
  }

  const updateUser = async (userId, userData) => {
    try {
      const response = await api.put(`/users/${userId}`, userData)
      const updated = response.data?.data || response.data
      if (updated?.id != null) {
        state.value = {
          ...state.value,
          users: upsertById(state.value.users, updated)
        }
      }
      return updated
    } catch (error) {
      console.error('Failed to update user:', error)
      throw error
    }
  }

  const deleteUser = async (userId) => {
    try {
      await api.delete(`/users/${userId}`)
      const id = Number(userId)
      state.value = {
        ...state.value,
        users: state.value.users.filter((u) => Number(u.id) !== id)
      }
      recalculateTotals()
    } catch (error) {
      console.error('Failed to delete user:', error)
      throw error
    }
  }

  const deleteShop = async (shopId) => {
    try {
      await api.delete(`/shops/${shopId}`)
      const id = Number(shopId)
      state.value = {
        ...state.value,
        shops: state.value.shops.filter((s) => Number(s.id) !== id)
      }
      recalculateTotals()
      refreshRecentShops()
    } catch (error) {
      console.error('Failed to delete shop:', error)
      throw error
    }
  }

  const deleteVehicle = async (vehicleId) => {
    try {
      await api.delete(`/vehicles/${vehicleId}`)
      await load({ force: true })
    } catch (error) {
      console.error('Failed to delete vehicle:', error)
      throw error
    }
  }

  const deleteBooking = async (bookingId) => {
    try {
      await api.delete(`/bookings/${bookingId}`)
      await load({ force: true })
    } catch (error) {
      console.error('Failed to delete booking:', error)
      throw error
    }
  }

  return {
    totals, trends, bookingGrossByDay, vehicleTypeCounts, paymentTotal,
    state, recentShops, formatted, load, setShopStatus, updateShop, addUser, updateUser, deleteUser, deleteShop, deleteVehicle, deleteBooking
  }
})
