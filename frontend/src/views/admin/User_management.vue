<template>
  <div class="user-management-page">
    <AdminNavbar />
    <section class="user-management-content">
      <div class="global-header">
        <div class="global-search-pill">
          <svg viewBox="0 0 24 24" role="presentation">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" fill="none" />
            <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
          </svg>
          <input
            aria-label="Search customers, owners, or admins"
            placeholder="Search customers, owners, or admins..."
            v-model="searchTerm"
          />
        </div>
        <div class="header-utility">
          <button class="icon-button" type="button" aria-label="Messages">
            <svg viewBox="0 0 24 24" role="presentation">
              <rect x="3" y="4" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.5" fill="none" />
              <path d="M3 7l9 7 9-7" fill="none" stroke="currentColor" stroke-width="1.5" />
            </svg>
          </button>
          <button class="icon-button" type="button" aria-label="Notifications">
            <svg viewBox="0 0 24 24" role="presentation">
              <path
                d="M12 3c-2.8 0-5 2.2-5 5v3.5L6 13v1h12v-1l-1-1.5V8c0-2.8-2.2-5-5-5z"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
              />
              <path d="M9 18a3 3 0 0 0 6 0" fill="none" stroke="currentColor" stroke-width="1.5" />
            </svg>
          </button>
          <div class="profile-chip">
            <img v-if="profileAvatar" :src="profileAvatar" alt="Profile avatar" />
            <span v-else class="profile-initials">{{ profileInitials }}</span>
            <div class="profile-details">
              <p>{{ profileName }}</p>
              <span>{{ profileEmail }}</span>
              <span class="profile-role-badge">{{ profileRoleLabel }}</span>
            </div>
          </div>
        </div>
      </div>

      <main class="customer-panel">
        <div class="panel-heading">
          <div>
            <p class="section-label">Customer</p>
            <h1>Customer List</h1>
          </div>
          <div class="panel-actions">
            <div class="panel-search">
              <svg viewBox="0 0 24 24" role="presentation">
                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" fill="none" />
                <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              </svg>
              <input type="search" aria-label="Search customers" placeholder="Search here..." v-model="searchTerm" />
            </div>
            <button class="ghost-button" type="button">
              <span>Filter</span>
              <svg viewBox="0 0 24 24" role="presentation">
                <path d="M6 7h12M10 12h4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              </svg>
            </button>
            <button class="primary-button" type="button" @click="openForm('create')">
              <span class="button-icon">+</span>
              Add New Customer
            </button>
          </div>
        </div>

        <div class="audience-tabs">
          <button
            v-for="tab in filterTabs"
            :key="tab.id"
            type="button"
            :class="['audience-tab', { active: tab.id === audienceFilter }]"
            @click="audienceFilter = tab.id"
          >
            <span class="tab-icon" aria-hidden="true">
              <svg :viewBox="getTabIcon(tab.icon).viewBox" role="presentation">
                <path
                  v-for="path in getTabIcon(tab.icon).paths"
                  :key="path"
                  :d="path"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.6"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </span>
            <div>
              <strong>{{ tab.label }}</strong>
              <small>{{ tab.description }}</small>
            </div>
            <span class="tab-count">{{ tab.count }}</span>
          </button>
        </div>

        <div class="panel-status" v-if="successMessage || errorMessage">
          <div v-if="successMessage" class="callout success">{{ successMessage }}</div>
          <div v-if="errorMessage" class="callout error">{{ errorMessage }}</div>
        </div>

        <div class="customer-table">
          <div v-if="isLoading" class="table-overlay">
            <span>Loading customers…</span>
          </div>
          <table>
            <thead>
              <tr>
                <th class="checkbox-cell">
                  <input type="checkbox" aria-label="Select all customers" />
                </th>
                <th>Name</th>
                <th>E-mail</th>
                <th>Phone</th>
                <th>Location</th>
                <th>Role</th>
                <th class="align-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in displayedCustomers" :key="customer.id">
                <td class="checkbox-cell">
                  <input type="checkbox" :id="`customer-${customer.id}`" />
                </td>
                <td class="name-cell">
                  <img :src="customer.avatar" :alt="customer.name" />
                  <span>{{ customer.name }}</span>
                </td>
                <td>{{ customer.email }}</td>
                <td>{{ customer.phone || '—' }}</td>
                <td>{{ customer.location }}</td>
                <td>{{ formatRoleLabel(customer.role) }}</td>
                <td class="align-right">
                  <div class="row-actions">
                    <button type="button" class="text-button" @click="editCustomer(customer)">
                      <span class="button-icon is-small">✏️</span>
                      Edit
                    </button>
                    <button type="button" class="text-button danger" @click="requestDeleteCustomer(customer)">
                      <span class="button-icon is-small danger">🗑️</span>
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!isLoading && displayedCustomers.length === 0">
                <td colspan="7" class="empty-row">
                  <span>No customers found. Try clearing the filters or reloading.</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="table-footer">
          <p class="footer-summary">{{ paginationSummary }}</p>
          <div class="pagination-controls">
            <button type="button" class="text-button" :disabled="!canPrev" @click="handlePrev">Prev</button>
            <button type="button" class="text-button" :disabled="!canNext" @click="handleNext">Next</button>
          </div>
        </div>
      </main>
    </section>

    <CommonFooter />

    <div v-if="formState.show" class="modal-backdrop" aria-modal="true">
      <div class="modal-card" role="dialog">
        <header class="modal-header">
          <div>
            <p class="section-label modal-label">
              {{ formState.mode === 'create' ? 'Create' : 'Edit' }} customer
            </p>
            <h2>{{ formState.mode === 'create' ? 'Add new customer' : 'Update customer' }}</h2>
          </div>
          <button type="button" class="close-button" aria-label="Close form" @click="closeForm">
            &times;
          </button>
        </header>
        <form class="modal-form" @submit.prevent="saveCustomer">
          <div class="form-grid">
            <label class="form-field">
              <span>Full name</span>
              <input type="text" v-model="formState.data.name" placeholder="Alex Johnson" required />
              <small v-if="formState.errors.name">{{ formState.errors.name[0] }}</small>
            </label>
            <label class="form-field">
              <span>E-mail</span>
              <input
                type="email"
                v-model="formState.data.email"
                :disabled="formState.mode === 'edit'"
                placeholder="alex@example.com"
                required
              />
              <small v-if="formState.errors.email">{{ formState.errors.email[0] }}</small>
            </label>
            <label class="form-field">
              <span>Phone</span>
              <input type="tel" v-model="formState.data.phone" placeholder="+1 234 567 890" />
              <small v-if="formState.errors.phone">{{ formState.errors.phone[0] }}</small>
            </label>
            <label class="form-field">
              <span>Role</span>
              <select v-model="formState.data.role">
                <option value="customer">Customer</option>
                <option value="shop_owner">Owner</option>
                <option value="admin">Admin</option>
              </select>
              <small v-if="formState.errors.role">{{ formState.errors.role[0] }}</small>
            </label>
            <label class="form-field">
              <span>Password</span>
              <input
                type="password"
                v-model="formState.data.password"
                :placeholder="
                  formState.mode === 'create'
                    ? 'Set a password'
                    : 'Leave blank to keep the current password'
                "
                :required="formState.mode === 'create'"
              />
              <small v-if="formState.errors.password">{{ formState.errors.password[0] }}</small>
            </label>
            <label class="form-field" v-if="formState.mode === 'create'">
              <span>Confirm password</span>
              <input type="password" v-model="formState.data.confirmPassword" placeholder="Repeat password" required />
              <small v-if="formState.errors.confirmPassword">{{ formState.errors.confirmPassword[0] }}</small>
            </label>
          </div>
          <div class="form-actions">
            <button type="button" class="ghost-button modal-cancel" @click="closeForm">Cancel</button>
            <button type="submit" class="primary-button" :disabled="formState.saving">
              {{ formState.saving ? 'Saving...' : formState.mode === 'create' ? 'Create customer' : 'Save changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="deleteModal.show" class="modal-backdrop delete-overlay" aria-modal="true">
      <div class="modal-card delete-card" role="alertdialog">
        <header class="modal-header">
          <div>
            <p class="section-label modal-label">Confirm delete</p>
            <h2>Delete customer</h2>
          </div>
        </header>
        <p class="delete-message">
          Are you sure you want to delete
          <strong>{{ deleteModal.customer?.name || 'this customer' }}</strong>? This action cannot be undone.
        </p>
        <div class="form-actions">
          <button type="button" class="ghost-button modal-cancel" @click="cancelDelete" :disabled="deleteModal.saving">
            Cancel
          </button>
          <button
            type="button"
            class="primary-button"
            :disabled="deleteModal.saving"
            @click="confirmDelete"
          >
            {{ deleteModal.saving ? 'Deleting...' : 'Delete customer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import { userApi } from '@/services/api'
import userService from '@/services/userService'
import AdminNavbar from '@/components/AdminNavbar.vue'
import CommonFooter from '@/components/CommonFooter.vue'

const DEFAULT_AVATAR = '/Images/default-avatar.svg'

const customers = ref([])
const isLoading = ref(false)
const searchTerm = ref('')
const pagination = reactive({
  page: 1,
  perPage: 15,
  total: 0,
  lastPage: 1,
})
const formState = reactive({
  show: false,
  mode: 'create',
  data: {
    id: null,
    name: '',
    email: '',
    phone: '',
    role: 'customer',
    password: '',
    confirmPassword: '',
  },
  errors: {},
  saving: false,
})
const deleteModal = reactive({
  show: false,
  customer: null,
  saving: false,
})
const successMessage = ref('')
const errorMessage = ref('')
const audienceFilter = ref('all')
let successTimeoutId = null
let errorTimeoutId = null

const formatRoleLabel = (value) => {
  if (!value) return ''
  return value
    .split(/[_\s]+/)
    .filter(Boolean)
    .map((segment) => `${segment.charAt(0).toUpperCase()}${segment.slice(1).toLowerCase()}`)
    .join(' ')
}

const resolveAvatar = (user) => {
  if (!user) return DEFAULT_AVATAR
  const avatar = userService.getAvatarUrl(
    user.avatar_url || user.img_url || user.profile_picture || ''
  )
  if (avatar) return avatar
  return DEFAULT_AVATAR
}

const tabIconData = {
  all: {
    viewBox: '0 0 24 24',
    paths: ['M4 7h16', 'M4 12h16', 'M4 17h10'],
  },
  owners: {
    viewBox: '0 0 24 24',
    paths: ['M3 9L12 3l9 6v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z', 'M9 21V12h6v9'],
  },
  users: {
    viewBox: '0 0 24 24',
    paths: ['M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z', 'M5 21c0-3.3 2.7-6 6-6s6 2.7 6 6v1H5v-1z'],
  },
}

const getTabIcon = (key) => tabIconData[key] || tabIconData.all

const currentUser = computed(() => userService.getCurrentUser())
const profileAvatar = computed(() => (currentUser.value ? resolveAvatar(currentUser.value) : ''))
const profileName = computed(() => currentUser.value?.name || 'Admin')
const profileEmail = computed(() => currentUser.value?.email || 'admin@example.com')
const profileRoleLabel = computed(() => formatRoleLabel(currentUser.value?.role || 'admin'))
const profileInitials = computed(() => {
  const nameParts = profileName.value.trim().split(/\s+/).filter(Boolean)
  if (nameParts.length === 0) return 'AD'
  if (nameParts.length === 1) return nameParts[0].slice(0, 2).toUpperCase()
  return `${nameParts[0][0] || ''}${nameParts[1][0] || ''}`.toUpperCase()
})

const ownerCount = computed(() => customers.value.filter((customer) => customer.role === 'shop_owner').length)
const userCount = computed(() => customers.value.filter((customer) => customer.role === 'customer').length)

const filterTabs = computed(() => [
  {
    id: 'all',
    label: 'All users',
    description: 'Customers, owners & admins',
    count: customers.value.length,
    icon: 'all',
  },
  {
    id: 'owners',
    label: 'Owners',
    description: `${ownerCount.value} shop owners`,
    count: ownerCount.value,
    icon: 'owners',
  },
  {
    id: 'users',
    label: 'Users',
    description: `${userCount.value} active customers`,
    count: userCount.value,
    icon: 'users',
  },
])

const filteredByAudience = computed(() => {
  if (audienceFilter.value === 'owners') {
    return customers.value.filter((customer) => customer.role === 'shop_owner')
  }
  if (audienceFilter.value === 'users') {
    return customers.value.filter((customer) => customer.role === 'customer')
  }
  return customers.value
})

const displayedCustomers = computed(() => {
  const query = searchTerm.value.trim().toLowerCase()
  if (!query) {
    return filteredByAudience.value
  }
  return filteredByAudience.value.filter((customer) => {
    const haystack = `${customer.name} ${customer.email} ${customer.phone ?? ''} ${customer.location ?? ''} ${customer.role}`
      .toLowerCase()
    return haystack.includes(query)
  })
})

const paginationSummary = computed(() => {
  if (!pagination.total) {
    return 'No customers yet'
  }
  const from = Math.min((pagination.page - 1) * pagination.perPage + 1, pagination.total)
  const to = Math.min(pagination.page * pagination.perPage, pagination.total)
  const activeTab = filterTabs.value.find((tab) => tab.id === audienceFilter.value)?.label || 'All users'
  return `${activeTab} · Showing ${from}-${to} of ${pagination.total}`
})

const canPrev = computed(() => pagination.page > 1)
const canNext = computed(() => pagination.page < pagination.lastPage)

const showSuccess = (message) => {
  successMessage.value = message
  errorMessage.value = ''
  if (successTimeoutId) {
    clearTimeout(successTimeoutId)
  }
  successTimeoutId = setTimeout(() => {
    successMessage.value = ''
  }, 4200)
}

const showError = (message) => {
  errorMessage.value = message
  successMessage.value = ''
  if (errorTimeoutId) {
    clearTimeout(errorTimeoutId)
  }
  errorTimeoutId = setTimeout(() => {
    errorMessage.value = ''
  }, 4200)
}

const fetchCustomers = async () => {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const response = await userApi.getAll({ page: pagination.page })
    const payload = response.data
    customers.value = (payload.data ?? payload).map((user) => ({
      ...user,
      avatar: resolveAvatar(user),
      location: user.location ?? '—',
      role: user.role ?? 'customer',
    }))
    pagination.perPage = payload.per_page ?? pagination.perPage
    pagination.total = payload.total ?? customers.value.length
    pagination.lastPage = payload.last_page ?? 1
  } catch (error) {
    console.error('Failed to load customers', error)
    showError('Unable to load customers. Please try again.')
  } finally {
    isLoading.value = false
  }
}

const resetFormFields = () => {
  formState.data.id = null
  formState.data.name = ''
  formState.data.email = ''
  formState.data.phone = ''
  formState.data.role = 'customer'
  formState.data.password = ''
  formState.data.confirmPassword = ''
  formState.errors = {}
}

const openForm = (mode, user = null) => {
  formState.mode = mode
  formState.errors = {}
  formState.saving = false
  if (mode === 'edit' && user) {
    formState.data.id = user.id
    formState.data.name = user.name ?? ''
    formState.data.email = user.email ?? ''
    formState.data.phone = user.phone ?? ''
    formState.data.role = user.role ?? 'customer'
  } else {
    resetFormFields()
  }
  formState.show = true
}

const closeForm = () => {
  formState.show = false
  resetFormFields()
}

const saveCustomer = async () => {
  formState.errors = {}
  formState.saving = true
  const trimmedName = formState.data.name.trim()
  if (!trimmedName) {
    formState.errors.name = ['Please enter a name.']
    formState.saving = false
    return
  }

  if (formState.mode === 'create') {
    if (!formState.data.email.trim()) {
      formState.errors.email = ['Email is required.']
      formState.saving = false
      return
    }
    if (!formState.data.password) {
      formState.errors.password = ['Password is required.']
      formState.saving = false
      return
    }
    if (formState.data.password !== formState.data.confirmPassword) {
      formState.errors.confirmPassword = ['Passwords do not match.']
      formState.saving = false
      return
    }
  }

  const payload = {
    name: trimmedName,
    phone: formState.data.phone.trim() || null,
    role: formState.data.role,
  }

  if (formState.mode === 'create') {
    payload.email = formState.data.email.trim()
    payload.password = formState.data.password
  } else if (formState.data.password) {
    payload.password = formState.data.password
  }

  try {
    if (formState.mode === 'create') {
      await userApi.create(payload)
      showSuccess('Customer added successfully.')
    } else if (formState.data.id) {
      await userApi.update(formState.data.id, payload)
      showSuccess('Customer updated.')
    }
    closeForm()
    await fetchCustomers()
  } catch (error) {
    const fieldErrors = error.response?.data?.errors
    if (fieldErrors) {
      formState.errors = fieldErrors
    } else {
      console.error('Failed to save customer', error)
      showError('Failed to save customer. Please try again.')
    }
  } finally {
    formState.saving = false
  }
}

const editCustomer = (customer) => openForm('edit', customer)

const requestDeleteCustomer = (customer) => {
  deleteModal.customer = customer
  deleteModal.show = true
}

const cancelDelete = () => {
  deleteModal.show = false
  deleteModal.customer = null
  deleteModal.saving = false
}

const confirmDelete = async () => {
  if (!deleteModal.customer) return
  deleteModal.saving = true
  try {
    await userApi.delete(deleteModal.customer.id)
    showSuccess('Customer removed.')
    await fetchCustomers()
    cancelDelete()
  } catch (error) {
    console.error('Delete failed', error)
    showError('Unable to delete customer.')
  } finally {
    deleteModal.saving = false
  }
}

const handlePrev = () => {
  if (!canPrev.value) return
  pagination.page -= 1
  fetchCustomers()
}

const handleNext = () => {
  if (!canNext.value) return
  pagination.page += 1
  fetchCustomers()
}

onMounted(fetchCustomers)

onBeforeUnmount(() => {
  if (successTimeoutId) {
    clearTimeout(successTimeoutId)
  }
  if (errorTimeoutId) {
    clearTimeout(errorTimeoutId)
  }
})
</script>

<style scoped>
.user-management-page {
  min-height: 100vh;
  background: radial-gradient(circle at top right, #f4f7fa 0%, #e8edf3 55%, #dfe4ea 100%);
  font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  color: var(--text-main);
  display: flex;
  flex-direction: column;
}

.user-management-content {
  flex: 1;
  padding: 24px 40px 48px;
}

.global-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 20px;
  margin-bottom: 24px;
}

.global-search-pill {
  flex: 1;
  max-width: 320px;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #f7f8fb;
  border: 1px solid rgba(31, 47, 74, 0.12);
  box-shadow: 0 1px 6px rgba(31, 47, 74, 0.08);
}

.global-search-pill svg {
  width: 16px;
  height: 16px;
  color: #4b5563;
  flex-shrink: 0;
}

.global-search-pill input {
  flex: 1;
  border: 0;
  outline: none;
  background: transparent;
  font-size: 14px;
  color: var(--text-main);
}

.header-utility {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-button {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: 1px solid rgba(31, 47, 74, 0.12);
  background: #fff;
  display: grid;
  place-items: center;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.icon-button svg {
  width: 20px;
  height: 20px;
  color: #1f2f4a;
}

.profile-chip {
  border-radius: 99px;
  padding: 6px 18px;
  background: linear-gradient(180deg, #ffffff 0%, #f3f6fb 100%);
  border: 1px solid rgba(31, 47, 74, 0.12);
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 10px 24px rgba(31, 47, 74, 0.15);
}

.profile-initials,
.profile-chip img {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  border: 2px solid #fff;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.15);
}

.profile-chip img {
  object-fit: cover;
  background: #e2e8f0;
}

.profile-initials {
  background: linear-gradient(135deg, #667eea, #764ba2);
  display: grid;
  place-items: center;
  color: #fff;
  font-weight: 700;
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.profile-details p {
  margin: 0;
  font-weight: 700;
}

.profile-role-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e0f2ff;
  color: #0b5f95;
  border-radius: 999px;
  padding: 2px 10px;
  font-size: 10px;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-top: 4px;
}

.profile-details span {
  display: block;
  font-size: 11px;
  color: #6e7f95;
}

.customer-panel {
  background: #fff;
  border-radius: 28px;
  padding: 32px;
  box-shadow: var(--shadow-lg);
  border: 1px solid rgba(31, 47, 74, 0.08);
}

.panel-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}

.section-label {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #6e7f95;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.panel-heading h1 {
  margin: 0;
  font-size: 28px;
  color: var(--text-main);
}

.panel-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.panel-search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fbfcfe;
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid rgba(31, 47, 74, 0.08);
  min-width: 200px;
  box-shadow: 0 2px 8px rgba(31, 47, 74, 0.08);
}

.panel-search svg {
  width: 16px;
  height: 16px;
  color: #4b5563;
}

.panel-search input {
  border: 0;
  outline: none;
  background: transparent;
  font-size: 13px;
  color: #1f2f4a;
  width: 100%;
}

.audience-tabs {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.audience-tab {
  border: 1px solid transparent;
  background: linear-gradient(135deg, rgba(31, 154, 229, 0.08), rgba(255, 255, 255, 0.9));
  border-radius: 18px;
  padding: 14px 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
  min-width: 220px;
  font-size: 0.95rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  box-shadow: inset 0 0 0 1px rgba(31, 47, 74, 0.05);
}

.audience-tab strong {
  display: block;
  font-size: 1rem;
  color: #1f2f4a;
}

.audience-tab small {
  display: block;
  color: #6e7f95;
  font-size: 0.78rem;
}

.audience-tab.active {
  background: linear-gradient(135deg, #1f9ae5, #1583c2);
  color: #fff;
  box-shadow: 0 16px 32px rgba(14, 124, 194, 0.25);
}

.audience-tab.active strong,
.audience-tab.active small,
.audience-tab.active .tab-count {
  color: #fff;
}

.audience-tab.active .tab-icon {
  transform: translateY(-2px);
}

.tab-icon {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.tab-icon svg {
  width: 22px;
  height: 22px;
}

.tab-count {
  margin-left: auto;
  font-weight: 700;
  color: #1f2f4a;
  font-size: 0.85rem;
}

.panel-status {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 12px;
}

.callout {
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid transparent;
}

.callout.success {
  background: #ecfccb;
  border-color: #bef264;
  color: #365314;
}

.callout.error {
  background: #fee2e2;
  border-color: #fecaca;
  color: #991b1b;
}

.customer-table {
  overflow-x: auto;
  position: relative;
}

.table-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.85);
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-weight: 600;
  color: #1f2f4a;
  z-index: 2;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
}

th,
td {
  text-align: left;
  padding: 16px 12px;
  border-bottom: 1px solid rgba(31, 47, 74, 0.08);
}

thead th {
  color: #6e7f95;
  font-weight: 600;
  background: rgba(31, 47, 74, 0.02);
}

.checkbox-cell {
  width: 48px;
  text-align: center;
}

.name-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.name-cell img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.align-right {
  text-align: right;
}

.row-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.text-button {
  border: 1px solid rgba(31, 47, 74, 0.2);
  background: #fff;
  border-radius: 999px;
  padding: 6px 14px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: border-color 0.2s ease, color 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.text-button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.text-button.danger {
  color: #c2394f;
  border-color: rgba(194, 57, 79, 0.45);
}

.button-icon {
  font-size: 1rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(31, 154, 229, 0.15);
}

.button-icon.is-small {
  width: 22px;
  height: 22px;
  font-size: 0.9rem;
}

.button-icon.is-small + span {
  margin-left: 2px;
}

.button-icon.is-small.danger {
  background: rgba(194, 57, 79, 0.2);
}

.empty-row {
  text-align: center;
  padding: 32px 0;
  color: #6e7f95;
  font-size: 14px;
}

.table-footer {
  margin-top: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.footer-summary {
  color: #6e7f95;
  font-size: 14px;
  margin: 0;
}

.pagination-controls {
  display: flex;
  gap: 8px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  display: grid;
  place-items: center;
  z-index: 20;
  padding: 20px;
}

.delete-overlay {
  z-index: 22;
}

.modal-card {
  width: min(520px, 100%);
  background: #fff;
  border-radius: 24px;
  padding: 28px;
  box-shadow: var(--shadow-lg);
  position: relative;
  overflow: hidden;
}

.delete-card {
  width: min(420px, 100%);
  text-align: center;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 16px;
}

.modal-header h2 {
  margin: 4px 0 0;
  font-size: 1.5rem;
  color: #1f2f4a;
}

.close-button {
  border: none;
  background: transparent;
  font-size: 1.5rem;
  font-weight: 600;
  color: #94a3b8;
  cursor: pointer;
  line-height: 1;
}

.close-button:hover {
  color: #1f2f4a;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 18px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-size: 14px;
}

.form-field input,
.form-field select {
  border-radius: 12px;
  border: 1px solid rgba(31, 47, 74, 0.2);
  padding: 10px 14px;
  font-size: 14px;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s ease;
}

.form-field input:focus,
.form-field select:focus {
  border-color: #1f9ae5;
  box-shadow: 0 0 0 1px rgba(31, 154, 229, 0.4);
}

.form-field small {
  color: #c2394f;
  font-size: 12px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.modal-cancel {
  padding: 10px 18px;
}

.delete-message {
  margin: 0 0 18px;
  color: #4b5563;
}

.ghost-button {
  border: 1px solid rgba(31, 47, 74, 0.15);
  background: #fff;
  border-radius: 999px;
  padding: 10px 16px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.primary-button {
  background: #1f9ae5;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 12px 22px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 12px 20px rgba(31, 154, 229, 0.35);
}

.primary-button:hover {
  background: #1583c2;
}

@media (max-width: 960px) {
  .user-management-content {
    padding: 24px;
  }

  .global-header {
    flex-direction: column;
    align-items: stretch;
  }

  .panel-heading {
    flex-direction: column;
    align-items: flex-start;
  }

  .panel-actions {
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .customer-panel {
    padding: 24px 20px;
  }

  .global-header {
    gap: 12px;
  }

  th,
  td {
    padding: 12px 8px;
  }

  .primary-button,
  .ghost-button {
    width: 100%;
    justify-content: center;
  }

  .panel-actions {
    flex-direction: column;
  }
}
</style>
