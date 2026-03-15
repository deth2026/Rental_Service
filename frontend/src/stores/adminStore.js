import { reactive, ref } from 'vue'
import api from '../services/api.js'

const demoUsers = [
  {
    id: 'U-1001',
    name: 'Sophea Ny',
    email: 'sophea@tripgo.com',
    phone: '+855 12 345 678',
    role: 'customer',
    status: 'ACTIVE',
    is_verified: true,
    created_at: new Date(Date.now() - 86400000 * 2).toISOString(),
  },
  {
    id: 'U-1002',
    name: 'Chan Dara',
    email: 'chan@tripgo.com',
    phone: '+855 15 789 123',
    role: 'shop_owner',
    status: 'ACTIVE',
    is_verified: true,
    created_at: new Date(Date.now() - 86400000 * 4).toISOString(),
  },
  {
    id: 'U-1003',
    name: 'Tola Rith',
    email: 'tola@tripgo.com',
    phone: '+855 99 888 777',
    role: 'SHOP_STAFF',
    status: 'PENDING',
    is_verified: false,
    created_at: new Date(Date.now() - 86400000 * 5).toISOString(),
  },
  {
    id: 'U-1004',
    name: 'Maly Cheng',
    email: 'maly@tripgo.com',
    phone: '+855 90 111 222',
    role: 'admin',
    status: 'ACTIVE',
    is_verified: true,
    created_at: new Date(Date.now() - 86400000 * 7).toISOString(),
  },
  {
    id: 'U-1005',
    name: 'Vannaroth Oun',
    email: 'vannaroth@tripgo.com',
    phone: '+855 87 333 444',
    role: 'customer',
    status: 'BLOCKED',
    is_verified: false,
    created_at: new Date(Date.now() - 86400000).toISOString(),
  },
]

const state = reactive({
  source: 'demo',
  users: [...demoUsers],
})

const formatted = {
  fmtNumber: (value) => {
    const num = Number(value || 0)
    if (Number.isNaN(num)) return '0'
    return num.toLocaleString('en-US')
  },
}

const trends = ref({ users: 8 })

let loadingPromise

const load = async ({ force } = {}) => {
  if (!force && loadingPromise) return loadingPromise

  loadingPromise = (async () => {
    try {
      const response = await api.get('/users')
      const data = response?.data?.data ?? response?.data
      const processed = Array.isArray(data) ? data : []
      state.users = processed.map((user, index) => ({
        id: user.id ?? `U-${Date.now()}-${index}`,
        name: user.name || 'Unknown User',
        email: user.email || 'user@example.com',
        phone: user.phone || '',
        role: user.role || 'customer',
        status: (user.status || (user.is_verified ? 'ACTIVE' : 'PENDING')).toUpperCase(),
        is_verified: Boolean(user.is_verified),
        created_at: user.created_at || new Date().toISOString(),
      }))
      state.source = 'api'
    } catch (error) {
      if (!state.users.length) state.users = [...demoUsers]
      state.source = 'demo'
      throw error
    } finally {
      loadingPromise = null
    }
  })()

  return loadingPromise
}

const addUser = ({ name, email, role }) => {
  const id = `U-${Math.floor(Date.now() / 1000)}`
  const next = {
    id,
    name,
    email,
    phone: '',
    role,
    status: 'ACTIVE',
    is_verified: true,
    created_at: new Date().toISOString(),
  }
  state.users = [...state.users, next]
  return next
}

const updateUserStatus = (id, status) => {
  const normalized = String(status).toUpperCase()
  state.users = state.users.map((user) =>
    String(user.id) === String(id)
      ? {
          ...user,
          status: normalized,
          is_verified: normalized === 'ACTIVE',
        }
      : user
  )
}

export function useAdminStore() {
  return {
    state,
    formatted,
    trends,
    load,
    addUser,
    updateUserStatus,
  }
}
