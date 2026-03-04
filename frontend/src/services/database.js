const USERS_KEY = 'rental_users'
const CURRENT_USER_KEY = 'rental_current_user'
const BOOKINGS_KEY = 'rental_bookings'

const seedShops = [
  {
    id: 1,
    name: 'Phnom Penh City Rides',
    address: 'Street 178, Daun Penh, Phnom Penh',
    phone: '+855 12 345 678',
    email: 'cityrides@example.com',
    rating: 4.9,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1590674899484-d5640e854abe?auto=format&fit=crop&w=600&q=80',
  },
  {
    id: 2,
    name: 'Siem Reap Wheels',
    address: 'Wat Bo Road, Siem Reap',
    phone: '+855 77 222 111',
    email: 'siemreapwheels@example.com',
    rating: 4.7,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=600&q=80',
  },
  {
    id: 3,
    name: 'Battambang Motion Hub',
    address: 'National Road 5, Battambang',
    phone: '+855 98 444 555',
    email: 'motionhub@example.com',
    rating: 4.6,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=600&q=80',
  },
]

const seedVehicles = [
  {
    id: 101,
    shop_id: 1,
    brand: 'Honda',
    model: 'PCX 160',
    type: 'bike',
    transmission: 'Auto',
    fuel_type: 'Petrol',
    seats: 2,
    price_per_day: 14,
    average_rating: 4.8,
    ratings_count: 93,
    year: '2024',
    status: 'available',
    image_url: 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=1000&q=80',
    created_at: '2026-02-25T07:00:00.000Z',
    updated_at: '2026-03-01T07:00:00.000Z',
  },
  {
    id: 102,
    shop_id: 1,
    brand: 'Toyota',
    model: 'Corolla Cross',
    type: 'car',
    transmission: 'Auto',
    fuel_type: 'Hybrid',
    seats: 5,
    price_per_day: 48,
    average_rating: 4.9,
    ratings_count: 74,
    year: '2023',
    status: 'available',
    image_url: 'https://images.unsplash.com/photo-1549399542-7e82138bc3f8?auto=format&fit=crop&w=1000&q=80',
    created_at: '2026-02-24T07:00:00.000Z',
    updated_at: '2026-03-01T07:00:00.000Z',
  },
  {
    id: 103,
    shop_id: 2,
    brand: 'Yamaha',
    model: 'NMAX 155',
    type: 'bike',
    transmission: 'Auto',
    fuel_type: 'Petrol',
    seats: 2,
    price_per_day: 12,
    average_rating: 4.7,
    ratings_count: 61,
    year: '2024',
    status: 'available',
    image_url: 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1000&q=80',
    created_at: '2026-02-26T07:00:00.000Z',
    updated_at: '2026-03-02T07:00:00.000Z',
  },
  {
    id: 104,
    shop_id: 2,
    brand: 'Ford',
    model: 'Everest',
    type: 'car',
    transmission: 'Auto',
    fuel_type: 'Diesel',
    seats: 7,
    price_per_day: 62,
    average_rating: 4.8,
    ratings_count: 42,
    year: '2022',
    status: 'available',
    image_url: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1000&q=80',
    created_at: '2026-02-20T07:00:00.000Z',
    updated_at: '2026-03-01T07:00:00.000Z',
  },
  {
    id: 105,
    shop_id: 3,
    brand: 'Giant',
    model: 'Escape 3',
    type: 'bicycle',
    transmission: 'Manual',
    fuel_type: 'Electric',
    seats: 1,
    price_per_day: 8,
    average_rating: 4.5,
    ratings_count: 28,
    year: '2025',
    status: 'available',
    image_url: 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?auto=format&fit=crop&w=1000&q=80',
    created_at: '2026-02-28T07:00:00.000Z',
    updated_at: '2026-03-03T07:00:00.000Z',
  },
]

const delay = (ms = 150) => new Promise((resolve) => setTimeout(resolve, ms))

const read = (key, fallback) => {
  const raw = localStorage.getItem(key)
  if (!raw) return fallback
  try {
    return JSON.parse(raw)
  } catch {
    return fallback
  }
}

const write = (key, value) => {
  localStorage.setItem(key, JSON.stringify(value))
}

const ensureUsers = () => {
  const users = read(USERS_KEY, null)
  if (Array.isArray(users) && users.length) return users

  const defaults = [
    {
      id: 1,
      name: 'Demo User',
      username: 'demo',
      email: 'demo@example.com',
      password: 'password123',
      created_at: new Date().toISOString(),
    },
  ]
  write(USERS_KEY, defaults)
  return defaults
}

const withoutPassword = (user) => {
  if (!user) return null
  const { password, ...safe } = user
  return safe
}

export const userService = {
  getCurrentUser() {
    const localUser = read(CURRENT_USER_KEY, null)
    if (localUser) return localUser

    const backendUserRaw = localStorage.getItem('user')
    if (!backendUserRaw) return null
    try {
      return JSON.parse(backendUserRaw)
    } catch {
      return null
    }
  },

  isAuthenticated() {
    return Boolean(
      this.getCurrentUser() || localStorage.getItem('auth_token') || localStorage.getItem('token')
    )
  },

  async login(payload) {
    await delay()
    const users = ensureUsers()
    const email = String(payload?.email || '').trim().toLowerCase()
    const password = String(payload?.password || '')

    const found = users.find((user) => user.email.toLowerCase() === email && user.password === password)
    if (!found) throw new Error('Invalid email or password.')

    const current = withoutPassword(found)
    write(CURRENT_USER_KEY, current)
    return current
  },

  async register(payload) {
    await delay()
    const users = ensureUsers()

    const username = String(payload?.username || '').trim()
    const email = String(payload?.email || '').trim().toLowerCase()
    const password = String(payload?.password || '')

    if (!username || !email || !password) {
      throw new Error('Please fill all required fields.')
    }

    const exists = users.some((user) => user.email.toLowerCase() === email)
    if (exists) throw new Error('Email already exists. Please login.')

    const newUser = {
      id: Date.now(),
      name: username,
      username,
      email,
      password,
      created_at: new Date().toISOString(),
    }

    const nextUsers = [...users, newUser]
    write(USERS_KEY, nextUsers)

    const current = withoutPassword(newUser)
    write(CURRENT_USER_KEY, current)
    return current
  },

  async logout() {
    await delay(50)
    localStorage.removeItem(CURRENT_USER_KEY)
    localStorage.removeItem('auth_token')
    localStorage.removeItem('token')
    localStorage.removeItem('user')
  },
}

export const shopService = {
  async getShops() {
    await delay()

    const token = localStorage.getItem('auth_token')
    const headers = {
      Accept: 'application/json',
    }

    if (token) {
      headers.Authorization = `Bearer ${token}`
    }

    try {
      const response = await fetch('/api/shops', {
        method: 'GET',
        headers,
      })

      if (!response.ok) {
        throw new Error('Failed to fetch shops from API.')
      }

      const data = await response.json()
      const shops = Array.isArray(data) ? data : Array.isArray(data?.data) ? data.data : []

      return shops
    } catch {
      // Fallback to local seed data if API is unavailable.
      return [...seedShops]
    }
  },
}

export const vehicleService = {
  async getVehicles(params = {}) {
    await delay()
    const shopId = Number(params.shop_id)
    if (!shopId) return [...seedVehicles]
    return seedVehicles.filter((vehicle) => Number(vehicle.shop_id) === shopId)
  },

  async getVehicle(id) {
    await delay()
    const vehicle = seedVehicles.find((item) => Number(item.id) === Number(id))
    if (!vehicle) throw new Error('Vehicle not found.')
    return { ...vehicle }
  },
}

export const bookingService = {
  async createBooking(payload) {
    await delay()

    const currentUser = userService.getCurrentUser()
    if (!currentUser) throw new Error('Please login first.')

    const vehicle = seedVehicles.find((item) => Number(item.id) === Number(payload?.vehicle_id))
    if (!vehicle) throw new Error('Vehicle not found.')

    const bookings = read(BOOKINGS_KEY, [])
    const booking = {
      id: Date.now(),
      user_id: currentUser.id,
      vehicle_id: vehicle.id,
      shop_id: vehicle.shop_id,
      days: Number(payload?.days || 1),
      start_date: payload?.start_date || new Date().toISOString().slice(0, 10),
      created_at: new Date().toISOString(),
    }

    write(BOOKINGS_KEY, [...bookings, booking])
    return booking
  },
}
