<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminStore } from '../../stores/adminStore.js'
import { useToast } from '../../composables/useToast.js'
import ConfirmModal from '../../components/ConfirmModal.vue'
import CountUp from '../../components/CountUp.vue'
import userService from '../../services/userService.js'

const admin = useAdminStore()
const route = useRoute()
const toast = useToast()

const page = ref(1)
const perPage = 8
const animated = ref(false)
const showOnlyMe = ref(false)

const showCreate = ref(false)
const createForm = ref({ name: '', email: '', phone: '', role: 'customer', password: '' })
const isCreating = ref(false)
const isUpdating = ref(false)
const togglingUserId = ref(null)
const deletingUserId = ref(null)

const showView = ref(false)
const showEdit = ref(false)
const showDeleteConfirm = ref(false)
const selectedUser = ref(null)
const deleteTarget = ref(null)

const editForm = ref({ id: null, name: '', phone: '', role: 'customer', is_verified: false })
const currentUser = computed(() => userService.getCurrentUser())
const currentUserId = computed(() => Number(currentUser.value?.id || 0))

const todayKey = () => new Date().toISOString().slice(0, 10)

const totals = computed(() => {
  const users = admin.state.users || []
  const activeUsers = users.filter((u) => {
    const rawStatus = String(u.status || '').toUpperCase()
    if (rawStatus) return rawStatus === 'ACTIVE'
    return Boolean(u.is_verified)
  }).length
  const joinedToday = users.filter((u) => String(u.created_at || '').slice(0, 10) === todayKey()).length
  return { activeUsers, joinedToday }
})

const query = computed(() => String(route.query.q || '').trim().toLowerCase())

const showRoleMenu = ref(false)
const showStatusMenu = ref(false)
const selectedRole = ref('')
const selectedStatus = ref('')

const closeFilterMenus = () => {
  showRoleMenu.value = false
  showStatusMenu.value = false
}

const toggleRoleMenu = () => {
  showRoleMenu.value = !showRoleMenu.value
  if (showRoleMenu.value) showStatusMenu.value = false
}

const toggleStatusMenu = () => {
  showStatusMenu.value = !showStatusMenu.value
  if (showStatusMenu.value) showRoleMenu.value = false
}

const sortedUsers = computed(() => {
  const users = [...(admin.state.users || [])]
  const meId = currentUserId.value
  return users.sort((a, b) => {
    const aIsMe = Number(a.id) === meId
    const bIsMe = Number(b.id) === meId
    if (aIsMe && !bIsMe) return -1
    if (!aIsMe && bIsMe) return 1
    return Number(b.id || 0) - Number(a.id || 0)
  })
})

const filteredUsers = computed(() => {
  const q = query.value
  const role = String(selectedRole.value || '').trim().toUpperCase()
  const status = String(selectedStatus.value || '').trim().toUpperCase()

  let items = sortedUsers.value
  if (showOnlyMe.value) items = items.filter((u) => Number(u.id) === currentUserId.value)
  if (q) items = items.filter((u) => `${u.name || ''} ${u.email || ''} ${u.phone || ''} ${u.role || ''} ${u.id || ''}`.toLowerCase().includes(q))
  if (role) items = items.filter((u) => String(u.role || '').trim().toUpperCase() === role)
  if (status) items = items.filter((u) => statusText(u) === status)

  return items
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)))
watch(totalPages, (next) => {
  if (page.value > next) page.value = next
})

watch([selectedRole, selectedStatus, showOnlyMe, query], () => {
  page.value = 1
})

const pagedUsers = computed(() => {
  const start = (page.value - 1) * perPage
  return filteredUsers.value.slice(start, start + perPage)
})

const statusBadgeClass = (status) => {
  const value = String(status || '').toUpperCase()
  if (value === 'ACTIVE') return 'badge badge-green'
  if (value === 'BLOCKED') return 'badge badge-red'
  return 'badge'
}

const statusText = (user) => {
  const raw = String(user?.status || '').trim()
  if (raw) return raw.toUpperCase()
  return user?.is_verified ? 'ACTIVE' : 'PENDING'
}

const statusClass = (user) => statusBadgeClass(statusText(user))

const roleLabel = (value) => {
  const role = String(value || '').trim().toUpperCase()
  if (!role) return 'All Roles'
  if (role === 'SHOP_STAFF') return 'Shop Staff'
  if (role === 'SHOP_OWNER') return 'Shop Owner'
  if (role === 'CUSTOMER') return 'Customer'
  if (role === 'ADMIN') return 'Admin'
  return role.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (m) => m.toUpperCase())
}

const statusLabel = (value) => {
  const s = String(value || '').trim().toUpperCase()
  if (!s) return 'Any Status'
  if (s === 'ACTIVE') return 'Active'
  if (s === 'PENDING') return 'Pending'
  if (s === 'BLOCKED') return 'Blocked'
  return s.replace(/_/g, ' ').toLowerCase().replace(/\b\w/g, (m) => m.toUpperCase())
}


const roleOptions = computed(() => {
  const seen = new Set()
  const roles = []

  const users = admin.state.users || []
  users.forEach((u) => {
    const role = String(u?.role || '').trim().toUpperCase()
    if (!role || seen.has(role)) return
    seen.add(role)
    roles.push(role)
  })

  const preferred = ['CUSTOMER', 'SHOP_OWNER', 'SHOP_STAFF', 'ADMIN']
  const orderIndex = (r) => {
    const idx = preferred.indexOf(r)
    return idx === -1 ? 999 : idx
  }
  roles.sort((a, b) => {
    const da = orderIndex(a)
    const db = orderIndex(b)
    if (da !== db) return da - db
    return a.localeCompare(b)
  })

  return ['', ...roles].map((r) => ({ value: r, label: roleLabel(r) }))
})

const statusOptions = computed(() => {
  const seen = new Set()
  const statuses = []

  const users = admin.state.users || []
  users.forEach((u) => {
    const s = statusText(u)
    if (!s || seen.has(s)) return
    seen.add(s)
    statuses.push(s)
  })

  const preferred = ['ACTIVE', 'PENDING', 'BLOCKED']
  const orderIndex = (s) => {
    const idx = preferred.indexOf(s)
    return idx === -1 ? 999 : idx
  }
  statuses.sort((a, b) => {
    const da = orderIndex(a)
    const db = orderIndex(b)
    if (da !== db) return da - db
    return a.localeCompare(b)
  })

  return ['', ...statuses].map((s) => ({ value: s, label: statusLabel(s) }))
})

const selectedRoleLabel = computed(() => roleLabel(selectedRole.value))
const selectedStatusLabel = computed(() => statusLabel(selectedStatus.value))

const roleBadgeClass = (role) => {
  const value = String(role || '').toUpperCase()
  if (value === 'CUSTOMER') return 'badge badge-blue'
  if (value.includes('SHOP')) return 'badge badge-cyan'
  if (value === 'ADMIN') return 'badge badge-purple'
  return 'badge'
}

const initials = (name) => {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return 'U'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase()
}

const isSelf = (user) => Number(user?.id) === currentUserId.value

const registrationSeries = computed(() => {
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const days = Array.from({ length: 7 }, (_, i) => {
    const d = new Date(today)
    d.setDate(d.getDate() - (6 - i))
    return d
  })
  const buckets = days.map((d) => ({
    key: d.toISOString().slice(0, 10),
    label: d.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase(),
    value: 0,
  }))
  const index = new Map(buckets.map((b, i) => [b.key, i]))
  
  const users = admin.state.users || []
  users.forEach((u) => {
    const key = String(u.created_at || '').slice(0, 10)
    const i = index.get(key)
    if (i == null) return
    buckets[i].value += 1
  })
  const max = Math.max(1, ...buckets.map((b) => b.value))
  return buckets.map((b) => ({ ...b, pct: (b.value / max) * 100 }))
})

const roleDistribution = computed(() => {
  const counts = { CUSTOMER: 0, 'SHOP STAFF': 0, ADMIN: 0 }
  const users = admin.state.users || []
  users.forEach((u) => {
    const r = String(u.role || '').toUpperCase()
    if (r === 'CUSTOMER') counts.CUSTOMER += 1
    else if (r.includes('SHOP')) counts['SHOP STAFF'] += 1
    else counts.ADMIN += 1
  })
  const total = counts.CUSTOMER + counts['SHOP STAFF'] + counts.ADMIN
  const pct = (n) => (total ? Math.round((n / total) * 100) : 0)
  return [
    { key: 'customers', label: 'CUSTOMERS', value: pct(counts.CUSTOMER), bar: 'bar-cyan' },
    { key: 'staff', label: 'SHOP STAFF', value: pct(counts['SHOP STAFF']), bar: 'bar-green' },
    { key: 'admins', label: 'ADMINS', value: pct(counts.ADMIN), bar: 'bar-blue' },
  ]
})

const triggerAnimation = async () => {
  animated.value = false
  await nextTick()
  requestAnimationFrame(() => {
    animated.value = true
  })
}

const openCreate = () => {
  showCreate.value = true
}

const closeCreate = () => {
  showCreate.value = false
  createForm.value = { name: '', email: '', phone: '', role: 'customer', password: '' }
}


const submitCreate = async () => {
  if (isCreating.value) return
  const name = String(createForm.value.name || '').trim()
  const email = String(createForm.value.email || '').trim()
  if (!name || !email) {
    toast.error('Name and email are required.')
    return
  }

  try {
    isCreating.value = true
    const password = String(createForm.value.password || '')
    if (!password) {
      toast.error('Password is required to create a user.')
      return
    }
    await admin.addUser({
      name,
      email,
      phone: createForm.value.phone || null,
      role: createForm.value.role || 'customer',
      password,
    })
    toast.success('User created successfully.')
    
    closeCreate()
    page.value = 1
    triggerAnimation()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to create user.')
  } finally {
    isCreating.value = false
  }
}

const toggleBlock = async (user) => {
  if (!user?.id || togglingUserId.value) return
  if (isSelf(user)) {
    toast.warning('You cannot block/unverify your own account.')
    return
  }
  try {
    togglingUserId.value = user.id
    const next = !Boolean(user.is_verified)
    await admin.updateUser(user.id, { is_verified: next, status: next ? 'active' : 'blocked' })
    toast.success(next ? 'User verified.' : 'User unverified.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update user.')
  } finally {
    togglingUserId.value = null
  }
}

const openView = (user) => {
  selectedUser.value = user
  showView.value = true
}

const openEdit = (user) => {
  selectedUser.value = user
  editForm.value = {
    id: user.id,
    name: user.name || '',
    phone: user.phone || '',
    role: String(user.role || 'customer'),
    is_verified: Boolean(user.is_verified),
  }
  showEdit.value = true
}

const closeEdit = () => {
  showEdit.value = false
  editForm.value = { id: null, name: '', phone: '', role: 'customer', is_verified: false }
}

const submitEdit = async () => {
  if (isUpdating.value) return
  const id = editForm.value.id
  if (!id) return
  const name = String(editForm.value.name || '').trim()
  if (!name) {
    toast.error('Name is required.')
    return
  }

  try {
    isUpdating.value = true
    await admin.updateUser(id, {
      name,
      phone: editForm.value.phone || null,
      role: editForm.value.role || 'customer',
      is_verified: Boolean(editForm.value.is_verified),
    })
    toast.success('User updated successfully.')

    closeEdit()
    triggerAnimation()
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update user.')
  } finally {
    isUpdating.value = false
  }
}

const requestDelete = (user) => {
  deleteTarget.value = user
  showDeleteConfirm.value = true
}

const deleteMessage = computed(() => {
  const name = String(deleteTarget.value?.name || '').trim()
  return name ? `Are you sure you want to delete "${name}"?` : 'Are you sure you want to delete this user?'
})

const confirmDelete = async () => {
  const user = deleteTarget.value
  if (!user?.id) return
  if (isSelf(user)) {
    toast.warning('You cannot delete your own account.')
    showDeleteConfirm.value = false
    deleteTarget.value = null
    return
  }
  if (deletingUserId.value) return

  try {
    deletingUserId.value = user.id
    await admin.deleteUser(user.id)
    toast.success('User deleted.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to delete user.')
  } finally {
    deletingUserId.value = null
    showDeleteConfirm.value = false
    deleteTarget.value = null
  }
}

const exportList = () => {
  const payload = JSON.stringify(filteredUsers.value.slice(0, 500), null, 2)
  const blob = new Blob([payload], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'chong-choul-users.json'
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(async () => {
  await admin.load().catch(() => { })
  triggerAnimation()
})

watch(
  () => admin.state.users?.length,
  () => triggerAnimation()
)
</script>

<template>
  <section class="admin-page" @click="closeFilterMenus">
    <header class="page-head">
      <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage platform participants, monitor activity, and regulate access.</p>
      </div>
      <div class="head-actions">
        <button type="button" class="btn btn-ghost" @click="exportList">
          <i class="fa-solid fa-download" aria-hidden="true"></i>
          <span>Export List</span>
        </button>
        <button type="button" class="btn btn-primary" @click="openCreate">
          <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
          <span>Create User</span>
        </button>
      </div>
    </header>

    <div class="split-stats">
      <section class="card stat-wide">
        <div class="wide-stat">
          <div>
            <div class="stat-label">TOTAL ACTIVE USERS</div>
            <div class="wide-value">
              <CountUp :value="Number(totals.activeUsers || 0)" :formatter="(n) => admin.formatted.fmtNumber(n)" />
            </div>
            <div class="stat-trend trend-up"><span>+{{ Math.max(0, admin.trends.users) }}%</span></div>
          </div>
          <div class="stat-icon tint-cyan" aria-hidden="true"><i class="fa-solid fa-user-group"></i></div>
        </div>
      </section>

      <section class="card stat-wide">
        <div class="wide-stat">
          <div>
            <div class="stat-label">NEWLY JOINED TODAY</div>
            <div class="wide-value">
              <CountUp :value="Number(totals.joinedToday || 0)" :formatter="(n) => admin.formatted.fmtNumber(n)" />
            </div>
            <div class="stat-trend trend-up"><span>+8</span></div>
          </div>
          <div class="stat-icon tint-green" aria-hidden="true"><i class="fa-solid fa-user-check"></i></div>
        </div>
      </section>
    </div>

    <section class="card">
      <div class="card-head">
        <div>
          <h2 class="card-title">User List</h2>
          <p class="card-subtitle"><strong>94%</strong> Active Rate</p>
        </div>
        <div class="filters">
          <button
            type="button"
            class="btn btn-chip"
            :class="{ 'is-active': showOnlyMe }"
            @click="showOnlyMe = !showOnlyMe"
          >
            {{ showOnlyMe ? 'Showing: My Account' : 'My Account' }}
          </button>
          <div class="filter-dropdown" @click.stop>
            <button type="button" class="btn btn-chip" :aria-expanded="showRoleMenu" @click="toggleRoleMenu">
              {{ selectedRoleLabel }} <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <div v-if="showRoleMenu" class="filter-menu" role="menu">
              <button
                v-for="opt in roleOptions"
                :key="opt.value || 'all'"
                type="button"
                :class="{ 'is-active': String(selectedRole || '') === String(opt.value || '') }"
                @click="selectedRole = opt.value; closeFilterMenus()"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>


          <div class="filter-dropdown" @click.stop>
            <button type="button" class="btn btn-chip" :aria-expanded="showStatusMenu" @click="toggleStatusMenu">
              {{ selectedStatusLabel }} <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
            </button>
            <div v-if="showStatusMenu" class="filter-menu" role="menu">
              <button
                v-for="opt in statusOptions"
                :key="opt.value || 'any'"
                type="button"
                :class="{ 'is-active': String(selectedStatus || '') === String(opt.value || '') }"
                @click="selectedStatus = opt.value; closeFilterMenus()"
              >
                {{ opt.label }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>USER DETAILS</th>
              <th>EMAIL</th>
              <th>DATE JOINED</th>
              <th>ROLE</th>
              <th>STATUS</th>
              <th class="actions">ACTIONS</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in pagedUsers" :key="user.id">
              <td class="shop-cell">
                <span class="user-bubble" aria-hidden="true">{{ initials(user.name) }}</span>
                <div class="shop-meta">
                  <div class="shop-name">
                    {{ user.name }}
                    <span v-if="isSelf(user)" class="badge badge-blue" style="margin-left:8px;">YOU</span>
                  </div>
                  <div class="shop-id">ID: {{ user.id }}</div>
                </div>
              </td>
              <td class="muted">{{ user.email }}</td>
              <td>{{ String(user.created_at || '').slice(0, 10) }}</td>
              <td><span :class="roleBadgeClass(user.role)">{{ String(user.role || '').toUpperCase() }}</span></td>
              <td><span :class="statusClass(user)">{{ statusText(user) }}</span></td>
              <td class="actions">
                <button type="button" class="icon-action" title="View" @click="openView(user)"><i
                    class="fa-regular fa-eye"></i></button>
                <button type="button" class="icon-action" title="Edit" :disabled="isUpdating || togglingUserId === user.id || deletingUserId === user.id" @click="openEdit(user)"><i
                    class="fa-regular fa-pen-to-square"></i></button>
                <button type="button" class="icon-action" title="Delete" :disabled="isSelf(user) || deletingUserId === user.id || isUpdating" @click="requestDelete(user)"><i
                    class="fa-regular fa-trash-can"></i></button>
                <button v-if="statusText(user) === 'BLOCKED'" type="button" class="btn btn-soft" :disabled="isSelf(user) || togglingUserId === user.id || deletingUserId === user.id"
                  @click="toggleBlock(user)">
                  {{ togglingUserId === user.id ? 'Processing...' : 'Unblock/Verify' }}
                </button>
                <button v-else type="button" class="btn btn-soft warn" :disabled="isSelf(user) || togglingUserId === user.id || deletingUserId === user.id" @click="toggleBlock(user)">
                  {{ togglingUserId === user.id ? 'Processing...' : 'Block/Unverify' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="muted">
          SHOWING {{ (page - 1) * perPage + 1 }} TO {{ Math.min(page * perPage, filteredUsers.length) }} OF
          {{ admin.formatted.fmtNumber(filteredUsers.length) }} USERS
        </div>
        <div class="pager">
          <button type="button" class="pager-btn" :disabled="page === 1" @click="page -= 1">‹</button>
          <button type="button" class="pager-btn is-active">{{ page }}</button>
          <button type="button" class="pager-btn" :disabled="page === totalPages" @click="page += 1">›</button>
        </div>
      </div>
    </section>

    <section class="grid-2 wide">
      <section class="card">
        <div class="card-head">
          <h2 class="card-title">User Registration Trends</h2>
        </div>
        <div class="chart-bars small" :class="{ animated }">
          <div v-for="point in registrationSeries" :key="point.key" class="bar-col">
            <div class="bar">
              <div class="bar-fill light" :style="{ height: `${point.pct}%` }"></div>
            </div>
            <div class="bar-label">{{ point.label }}</div>
          </div>
        </div>
      </section>


      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Role Distribution</h2>
        </div>
        <div class="fleet-list">
          <div v-for="row in roleDistribution" :key="row.key" class="fleet-row">
            <div class="fleet-left">
              <span class="fleet-name muted">{{ row.label }}</span>
            </div>
            <div class="fleet-right">
              <div class="fleet-bar">
                <div class="fleet-bar-fill" :class="row.bar" :style="{ width: `${row.value}%` }"></div>
              </div>
              <div class="fleet-pct">{{ row.value }}%</div>
            </div>
          </div>
        </div>

        <div class="platform-health">
          <div class="health-dot" aria-hidden="true"></div>
          <div>
            <div class="muted small">PLATFORM HEALTH</div>
            <div class="health-text">System security is stable</div>
          </div>
        </div>
      </section>
    </section>

    <div v-if="showCreate" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Create User</div>
            <div class="modal-sub">Add a new platform user.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeCreate"><i
              class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Full Name</span>
            <input v-model="createForm.name" type="text" placeholder="e.g. Marcus Weber" />
          </label>
          <label class="field">
            <span class="field-label">Email</span>
            <input v-model="createForm.email" type="email" placeholder="name@example.com" />
          </label>
          <label class="field">
            <span class="field-label">Phone</span>
            <input v-model="createForm.phone" type="text" placeholder="+855..." />
          </label>
          <label class="field">
            <span class="field-label">Role</span>
            <select v-model="createForm.role">
              <option value="customer">customer</option>
              <option value="admin">admin</option>
              <option value="shop_owner">shop_owner</option>
              <option value="shop_staff">shop_staff</option>
            </select>
          </label>
          <label class="field">
            <span class="field-label">Password</span>
            <input v-model="createForm.password" type="password" placeholder="Strong password" />
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" :disabled="isCreating" @click="closeCreate">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="isCreating" @click="submitCreate">
            {{ isCreating ? 'Creating...' : 'Create' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showView" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="showView = false">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">User Details</div>
            <div class="modal-sub">Read-only view</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="showView = false"><i
              class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
          <pre class="code-block">{{ JSON.stringify(selectedUser, null, 2) }}</pre>
        </div>
      </div>
    </div>

    <div v-if="showEdit" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Edit User</div>
            <div class="modal-sub">Update user profile and role.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="closeEdit"><i
              class="fa-solid fa-xmark"></i></button>
        </div>


        <div class="modal-body form-grid">
          <label class="field">
            <span class="field-label">Full Name</span>
            <input v-model="editForm.name" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Phone</span>
            <input v-model="editForm.phone" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Role</span>
            <input v-model="editForm.role" type="text" />
          </label>
          <label class="field">
            <span class="field-label">Verified</span>
            <select v-model="editForm.is_verified">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" :disabled="isUpdating" @click="closeEdit">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="isUpdating" @click="submitEdit">
            {{ isUpdating ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <ConfirmModal v-model="showDeleteConfirm" title="Delete User" :message="deleteMessage" cancel-text="Cancel"
      confirm-text="Yes" variant="danger" @confirm="confirmDelete" />
  </section>
</template>

<style scoped>
.code-block {
  margin: 0;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid var(--mp-border);
  background: rgba(148, 163, 184, 0.08);
  overflow: auto;
  max-height: 52vh;
  font-size: 12px;
}
</style>
