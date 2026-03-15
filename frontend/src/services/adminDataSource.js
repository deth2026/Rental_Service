import { cambodiaWeekdayLabel } from '../utils/cambodiaTime.js'

const STORAGE_KEY = 'chongchoul_admin_demo_v2'

const delay = (ms = 120) => new Promise((resolve) => setTimeout(resolve, ms))

const safeJson = async (response) => {
  try {
    return await response.json()
  } catch {
    return null
  }
}

const unwrapList = (payload) => {
  if (!payload) return []
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.result)) return payload.result
  return []
}

const extractTotal = (payload) => {
  const direct = Number(payload?.total)
  if (Number.isFinite(direct) && direct >= 0) return direct

  const meta = Number(payload?.meta?.total)
  if (Number.isFinite(meta) && meta >= 0) return meta

  const nested = Number(payload?.data?.total)
  if (Number.isFinite(nested) && nested >= 0) return nested

  return null
}

const normalizeApiBookings = (bookings, vehicles, { commissionRate = 0.15 } = {}) => {
  const vehicleById = new Map((vehicles || []).map((v) => [String(v.id), v]))
  const asDate = (value) => {
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : d
  }

  return (bookings || []).map((b) => {
    const total = Number(b?.total_price ?? b?.gross_amount ?? 0) || 0
    const createdAt = b?.created_at || b?.createdAt || null
    const d = createdAt ? asDate(createdAt) : null
    const dayLabel = d
      ? cambodiaWeekdayLabel(d)
      : null

    const vehicle = vehicleById.get(String(b?.vehicle_id ?? '')) || null
    const shopId = b?.shop_id ?? vehicle?.shop_id ?? null

    return {
      ...b,
      shop_id: shopId,
      gross_amount: total,
      commission_amount: Number((total * commissionRate).toFixed(2)),
      refunded: false,
      refund_amount: 0,
      day_label: dayLabel,
    }
  })
}

const apiFetch = async (path, options = {}) => {
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token')
  const headers = {
    Accept: 'application/json',
    ...(options.headers || {}),
  }
  if (token) headers.Authorization = `Bearer ${token}`

  const response = await fetch(path, { ...options, headers })
  if (!response.ok) {
    const body = await safeJson(response)
    const message = body?.message || `Request failed: ${response.status}`
    throw new Error(message)
  }
  return safeJson(response)
}

const mulberry32 = (seed) => {
  let value = seed >>> 0
  return () => {
    value += 0x6d2b79f5
    let t = Math.imul(value ^ (value >>> 15), 1 | value)
    t ^= t + Math.imul(t ^ (t >>> 7), 61 | t)
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296
  }
}

const pick = (rng, items) => items[Math.floor(rng() * items.length)]

const startOfDay = (date) => new Date(date.getFullYear(), date.getMonth(), date.getDate())

const addDays = (date, days) => {
  const next = new Date(date)
  next.setDate(next.getDate() + days)
  return next
}

const formatDateIso = (date) => new Date(date).toISOString()

const clamp = (value, min, max) => Math.max(min, Math.min(max, value))

const toTitle = (value) => String(value || '').replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase())

const generateDemoData = () => {
  const rng = mulberry32(20240317)

  const shopOwners = ['Karl Schmidt', 'Maria Rossi', 'Julio Iglesias', 'Lucia Moretti', 'Sofia Nguyen', 'Daniel Park']
  const cities = [
    'Munich, Germany',
    'Innsbruck, Austria',
    'Ibiza, Spain',
    'Berlin, Germany',
    'Vienna, Austria',
    'Zurich, Switzerland',
    'Prague, Czechia',
    'Paris, France',
  ]
  const shopNames = [
    'Phnom Penh Chong Choul Rentals',
    'Alpine Moto Hub',
    'Sunset Rides Ibiza',
    'Berlin Elite Rentals',
    'Moto Venice',
    'Nordic Ride House',
    'Aegean Wheels',
    'Urban Swift Rentals',
    'Coastal Cruiser Co.',
    'Skyline Mobility',
  ]

  const statusPool = ['VERIFIED', 'PENDING', 'SUSPENDED']
  const vehicleTypePool = ['motorbike', 'bicycle', 'car']

  const today = startOfDay(new Date())

  const shops = []
  for (let i = 0; i < 156; i += 1) {
    const name = i < 3 ? shopNames[i] : `${pick(rng, shopNames)} ${i + 1}`
    const owner = i < 3 ? shopOwners[i] : pick(rng, shopOwners)
    const location = i < 3 ? cities[i] : pick(rng, cities)
    const status = i === 0 ? 'VERIFIED' : i === 1 ? 'PENDING' : i === 2 ? 'SUSPENDED' : pick(rng, statusPool)
    const fleetSize = i === 0 ? 42 : i === 1 ? 18 : i === 2 ? 56 : Math.floor(10 + rng() * 120)
    const vehicleType = i < 3 ? (i === 1 ? 'motorbike' : i === 2 ? 'car' : 'bicycle') : pick(rng, vehicleTypePool)

    shops.push({
      id: `SHP-${String(8800 + i)}`,
      name,
      owner,
      location,
      fleet_size: fleetSize,
      status,
      vehicle_type: vehicleType,
      created_at: formatDateIso(i < 3 ? addDays(today, -(i + 1)) : addDays(today, -(4 + i))),
    })
  }

  const users = []
  const customersCount = 2035
  const shopStaffCount = 372
  for (let i = 0; i < 2482; i += 1) {
    const first = pick(rng, ['Marcus', 'Elena', 'James', 'Aria', 'Alex', 'Liam', 'Noah', 'Mia', 'Emma'])
    const last = pick(rng, ['Weber', 'Rodriguez', 'Donovan', 'Thorne', 'Johnson', 'Nguyen', 'Kim', 'Singh'])
    const role = i < customersCount ? 'CUSTOMER' : i < customersCount + shopStaffCount ? 'SHOP STAFF' : 'ADMIN'
    const status = 'ACTIVE'
    const createdAt = i < 36 ? today : addDays(today, -Math.floor(rng() * 210))

    users.push({
      id: `AUSR-${String(8300 + i)}`,
      name: `${first} ${last}`,
      email: `${first[0].toLowerCase()}${last.toLowerCase()}${i}@example.com`,
      role,
      status,
      created_at: formatDateIso(createdAt),
    })
  }

  const vehicles = []
  const desiredCounts = { motorbike: 518, bicycle: 214, car: 160 }
  const rates = { motorbike: 18, bicycle: 9, car: 55 }
  const types = Object.keys(desiredCounts)

  let vehicleId = 1000
  types.forEach((type) => {
    for (let i = 0; i < desiredCounts[type]; i += 1) {
      const shop = shops[Math.floor(rng() * shops.length)]
      vehicles.push({
        id: `VEH-${vehicleId++}`,
        shop_id: shop.id,
        type,
        daily_rate: rates[type],
        created_at: formatDateIso(addDays(today, -Math.floor(rng() * 260))),
      })
    }
  })

  // Build bookings so that:
  // - total bookings (YTD) = 12,302
  // - last 7 days revenue (gross) sums to 45,200 with Sunday highest
  const last7DayLabels = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']
  const last7DayRevenue = [5200, 6100, 5800, 7500, 6900, 4900, 8800] // sum 45,200
  const last7DayBookings = [260, 290, 280, 310, 300, 270, 290] // sum 2,000

  const commissionRate = 0.15
  const bookings = []
  let bookingId = 20000

  const chooseVehicle = (preferredType) => {
    const pool = preferredType ? vehicles.filter((v) => v.type === preferredType) : vehicles
    return pool[Math.floor(rng() * pool.length)]
  }

  const normalizeGrossSlice = (startIndex, endIndex, targetTotal) => {
    const slice = bookings.slice(startIndex, endIndex)
    const current = slice.reduce((s, b) => s + Number(b.gross_amount || 0), 0)
    if (!current) return
    const factor = targetTotal / current

    let running = 0
    for (let i = 0; i < slice.length; i += 1) {
      const booking = bookings[startIndex + i]
      if (i === slice.length - 1) {
        const last = Math.max(0.5, Number((targetTotal - running).toFixed(2)))
        booking.gross_amount = last
      } else {
        const next = Math.max(0.5, Number((Number(booking.gross_amount || 0) * factor).toFixed(2)))
        booking.gross_amount = next
        running += next
      }
      booking.commission_amount = Number((booking.gross_amount * commissionRate).toFixed(2))
      if (booking.refunded) booking.refund_amount = booking.gross_amount
      else booking.refund_amount = 0
    }
  }

  for (let i = 0; i < 7; i += 1) {
    const dayRevenue = last7DayRevenue[i]
    const count = last7DayBookings[i]
    const avg = dayRevenue / count
    const startIndex = bookings.length
    for (let j = 0; j < count; j += 1) {
      const vehicleType = pick(rng, ['motorbike', 'motorbike', 'bicycle', 'car'])
      const vehicle = chooseVehicle(vehicleType)
      const user = users[Math.floor(rng() * users.length)]
      const createdAt = addDays(today, -(6 - i))
      createdAt.setHours(10 + Math.floor(rng() * 7), Math.floor(rng() * 60), 0, 0)
      const grossAmount = Math.max(1.25, avg + (rng() - 0.5) * avg * 0.45)

      const refunded = rng() < 0.024
      const refundAmount = refunded ? grossAmount : 0

      bookings.push({
        id: `BKG-${bookingId++}`,
        shop_id: vehicle.shop_id,
        user_id: user.id,
        vehicle_id: vehicle.id,
        vehicle_type: vehicle.type,
        created_at: formatDateIso(createdAt),
        gross_amount: Number(grossAmount.toFixed(2)),
        commission_amount: Number((grossAmount * commissionRate).toFixed(2)),
        refunded,
        refund_amount: Number(refundAmount.toFixed(2)),
        day_label: last7DayLabels[i],
      })
    }
    const endIndex = bookings.length
    normalizeGrossSlice(startIndex, endIndex, dayRevenue)
  }

  // Expand to year-to-date totals for reports (gross = 219,200, commission = 32,880).
  // We already have 45,200 gross from last 7 days. Add historical bookings to reach 219,200 gross.
  const targetYtdGross = 219200
  const remainingGross = Math.max(0, targetYtdGross - bookings.reduce((sum, b) => sum + b.gross_amount, 0))

  const historicalBookingsCount = 10302
  let remaining = remainingGross
  for (let i = 0; i < historicalBookingsCount; i += 1) {
    const vehicleType = pick(rng, ['motorbike', 'motorbike', 'car', 'bicycle'])
    const vehicle = chooseVehicle(vehicleType)
    const user = users[Math.floor(rng() * users.length)]

    const daysBack = 20 + Math.floor(rng() * 150)
    const createdAt = addDays(today, -daysBack)
    createdAt.setHours(9 + Math.floor(rng() * 9), Math.floor(rng() * 60), 0, 0)

    const remainingSlots = historicalBookingsCount - i
    const baseline = remainingSlots > 0 ? remaining / remainingSlots : 0
    const grossAmount = clamp(baseline * (0.6 + rng() * 0.9), 2.5, 55)
    remaining -= grossAmount

    const refunded = rng() < 0.024
    const refundAmount = refunded ? grossAmount : 0

    bookings.push({
      id: `BKG-${bookingId++}`,
      shop_id: vehicle.shop_id,
      user_id: user.id,
      vehicle_id: vehicle.id,
      vehicle_type: vehicle.type,
      created_at: formatDateIso(createdAt),
      gross_amount: Number(grossAmount.toFixed(2)),
      commission_amount: Number((grossAmount * commissionRate).toFixed(2)),
      refunded,
      refund_amount: Number(refundAmount.toFixed(2)),
      day_label: null,
    })
  }

  const currentYtd = bookings.reduce((s, b) => s + Number(b.gross_amount || 0), 0)
  const ytdDiff = Number((targetYtdGross - currentYtd).toFixed(2))
  if (Math.abs(ytdDiff) >= 0.01) {
    const last = bookings[bookings.length - 1]
    last.gross_amount = Math.max(0.5, Number((Number(last.gross_amount || 0) + ytdDiff).toFixed(2)))
  }

  bookings.forEach((b) => {
    b.commission_amount = Number((b.gross_amount * commissionRate).toFixed(2))
    b.refund_amount = b.refunded ? b.gross_amount : 0
  })

  return {
    generated_at: formatDateIso(new Date()),
    users,
    shops,
    vehicles,
    bookings,
    commission_rate: commissionRate,
    labels: { vehicle_types: vehicleTypePool.map(toTitle) },
  }
}

const readDemo = () => {
  const raw = localStorage.getItem(STORAGE_KEY)
  if (!raw) return null
  try {
    return JSON.parse(raw)
  } catch {
    return null
  }
}

const writeDemo = (value) => {
  if (!value) {
    localStorage.removeItem(STORAGE_KEY)
    return
  }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(value))
}

const getOrCreateDemo = () => {
  const existing = readDemo()
  if (existing?.users?.length && existing?.shops?.length) return existing
  const generated = generateDemoData()
  writeDemo(generated)
  return generated
}

export const adminDataSource = {
  clearDemo: () => {
    localStorage.removeItem(STORAGE_KEY)
  },

  async load({ preferApi = true } = {}) {
    await delay()

    if (preferApi) {
      try {
        const [shopsPayload, usersPayload] = await Promise.all([
          apiFetch('/api/shops', { method: 'GET' }),
          apiFetch('/api/users', { method: 'GET' }),
        ])

        const shops = unwrapList(shopsPayload)
        const users = unwrapList(usersPayload)
        const totalUsers = extractTotal(usersPayload)

        if (shops.length || users.length) {
          // Vehicles and bookings might be behind different endpoints; fall back to demo when absent.
          let vehicles = []
          let bookings = []
          let totalVehicles = null
          let totalBookings = null
          try {
            const vehiclesPayload = await apiFetch('/api/vehicles', { method: 'GET' })
            vehicles = unwrapList(vehiclesPayload)
            totalVehicles = extractTotal(vehiclesPayload)
          } catch {
            vehicles = []
          }
          try {
            const bookingsPayload = await apiFetch('/api/bookings', { method: 'GET' })
            bookings = unwrapList(bookingsPayload)
            totalBookings = extractTotal(bookingsPayload)
          } catch {
            bookings = []
          }

          // Normalize API bookings to the analytics-friendly shape expected by dashboard/report pages.
          if (bookings.length) {
            bookings = normalizeApiBookings(bookings, vehicles, { commissionRate: 0.15 })
          }

          return {
            source: 'api',
            users,
            shops,
            vehicles,
            bookings,
            commission_rate: 0.15,
            api_totals: {
              users: totalUsers,
              shops: shops.length,
              vehicles: totalVehicles,
              bookings: totalBookings,
            },
          }
        }
      } catch {
        // ignore and fall back to demo
      }
    }

    const demo = getOrCreateDemo()
    return { ...demo, source: 'demo' }
  },

  persistDemo(nextDemo) {
    writeDemo(nextDemo)
  },
}
