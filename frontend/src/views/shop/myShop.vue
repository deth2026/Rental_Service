<script setup>
import { reactive, ref, onMounted } from 'vue'
import { shopApi, cityApi } from '@/services/api'

// Form data for creating a shop
const createForm = reactive({
  name: '',
  description: '',
  address: '',
  city_id: ''
})

// Shop data after creation
const shop = ref(null)

// UI states
const loading = ref(false)
const showSuccess = ref(false)
const showCreateModal = ref(false)
const error = ref('')
const cities = ref([])

// Get current user from localStorage (set during login)
const getUserId = () => {
  const user = localStorage.getItem('user')
  if (user) {
    return JSON.parse(user).id || 1 // default to 1 for demo
  }
  return 1 // default for demo
}

// Load cities for dropdown
const loadCities = async () => {
  try {
    const response = await cityApi.getAll()
    cities.value = response.data.data || response.data || []
  } catch (e) {
    console.error('Failed to load cities:', e)
  }
}

// Check if shop already exists (load existing shop)
const loadShop = async () => {
  const userId = getUserId()
  try {
    const response = await shopApi.getAll()
    const shops = response.data.data || response.data || []
    // Find shop owned by current user
    const userShop = shops.find(s => s.owner_id === userId)
    if (userShop) {
      shop.value = userShop
    }
  } catch (e) {
    console.error('Failed to load shop:', e)
  }
}

// Create shop
const createShop = async () => {
  loading.value = true
  error.value = ''

  try {
    const userId = getUserId()
    const data = {
      owner_id: userId,
      city_id: createForm.city_id || null,
      name: createForm.name,
      description: createForm.description,
      address: createForm.address,
      latitude: null,
      longitude: null,
      total_reviews: 0,
      status: 'active'
    }

    const response = await shopApi.create(data)
    shop.value = response.data

    // Show success popup
    showSuccess.value = true
    setTimeout(() => {
      showSuccess.value = false
    }, 3000)

    // Reset form
    createForm.name = ''
    createForm.description = ''
    createForm.address = ''
    createForm.city_id = ''

    // Close modal
    showCreateModal.value = false
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to create shop'
    console.error('Create shop error:', e)
  } finally {
    loading.value = false
  }
}

// Get city name by ID
const getCityName = (cityId) => {
  const city = cities.value.find(c => c.id === cityId)
  return city ? city.name : 'N/A'
}

// Open create modal
const openCreateModal = () => {
  createForm.name = ''
  createForm.description = ''
  createForm.address = ''
  createForm.city_id = ''
  showCreateModal.value = true
}

// Close create modal
const closeCreateModal = () => {
  showCreateModal.value = false
}

onMounted(() => {
  loadCities()
  loadShop()
})
</script>

<template>
  <div class="myshop-page">
    <div v-if="showSuccess" class="success-toast">
      <span class="toast-dot"></span>
      <strong>Success:</strong> Shop created successfully.
    </div>

    <div v-if="showCreateModal" class="modal-overlay" @click.self="closeCreateModal">
      <div class="modal-panel">
        <div class="modal-head">
          <h3>Create New Shop</h3>
          <button class="ghost-btn" @click="closeCreateModal">Close</button>
        </div>

        <form @submit.prevent="createShop" class="form-stack">
          <label>
            Shop Name *
            <input v-model="createForm.name" type="text" placeholder="Enter shop name" required />
          </label>

          <label>
            City
            <select v-model="createForm.city_id">
              <option value="">Select a city</option>
              <option v-for="city in cities" :key="city.id" :value="city.id">
                {{ city.name }}
              </option>
            </select>
          </label>

          <label>
            Address *
            <textarea v-model="createForm.address" placeholder="Enter shop address" required></textarea>
          </label>

          <label>
            Description
            <textarea v-model="createForm.description" placeholder="Tell customers what makes your shop unique"></textarea>
          </label>

          <div v-if="error" class="error-message">{{ error }}</div>

          <div class="modal-actions">
            <button type="button" class="ghost-btn" @click="closeCreateModal">Cancel</button>
            <button type="submit" class="primary-btn" :disabled="loading">
              {{ loading ? 'Creating...' : 'Create Shop' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <section class="hero">
      <div class="hero-content">
        <p class="eyebrow">Shop Studio</p>
        <h2>Design your rental storefront</h2>
        <p class="hero-sub">
          Build a memorable profile with clear location details and a description that earns trust.
        </p>
        <div class="hero-actions">
          <button class="primary-btn" @click="openCreateModal">
            {{ shop ? 'Create Another Shop' : 'Create Shop' }}
          </button>
          <span class="hero-hint">{{ shop ? 'Your shop is active' : 'No shop profile yet' }}</span>
        </div>
      </div>
      <div class="hero-orb"></div>
    </section>

    <section v-if="!shop" class="empty-layout">
      <div class="empty-card intro-card">
        <h3>Launch your business profile</h3>
        <p>Once created, this shop will be used for your vehicles, bookings, and customer-facing identity.</p>
        <div class="checklist">
          <div class="check-item">Shop name and branding</div>
          <div class="check-item">Accurate location and city</div>
          <div class="check-item">Description for customer trust</div>
        </div>
      </div>

      <div class="empty-card form-card">
        <h3>Create Your Shop</h3>
        <p class="muted">Fill in the basics to get started.</p>
        <form @submit.prevent="createShop" class="form-stack">
          <label>
            Shop Name *
            <input v-model="createForm.name" type="text" placeholder="Chong Choul Rental" required />
          </label>

          <label>
            City
            <select v-model="createForm.city_id">
              <option value="">Select a city</option>
              <option v-for="city in cities" :key="city.id" :value="city.id">
                {{ city.name }}
              </option>
            </select>
          </label>

          <label>
            Address *
            <textarea v-model="createForm.address" placeholder="Street, district, province" required></textarea>
          </label>

          <label>
            Description
            <textarea v-model="createForm.description" placeholder="Short intro about your shop"></textarea>
          </label>

          <div v-if="error" class="error-message">{{ error }}</div>
          <button type="submit" class="primary-btn" :disabled="loading">
            {{ loading ? 'Creating...' : 'Create Shop' }}
          </button>
        </form>
      </div>
    </section>

    <section v-else class="shop-layout">
      <article class="profile-card">
        <div class="avatar">{{ shop.name?.charAt(0) || 'S' }}</div>
        <div class="profile-main">
          <h3>{{ shop.name }}</h3>
          <p class="muted">{{ getCityName(shop.city_id) }}</p>
          <p class="desc">{{ shop.description || 'No description provided yet.' }}</p>
        </div>
        <span :class="['status-chip', shop.status]">{{ shop.status }}</span>
      </article>

      <article class="metric-card">
        <h4>Overview</h4>
        <div class="metric-grid">
          <div class="metric">
            <span>Reviews</span>
            <strong>{{ shop.total_reviews || 0 }}</strong>
          </div>
          <div class="metric">
            <span>City</span>
            <strong>{{ getCityName(shop.city_id) }}</strong>
          </div>
          <div class="metric">
            <span>Address</span>
            <strong>{{ shop.address || 'N/A' }}</strong>
          </div>
          <div class="metric" v-if="shop.created_at">
            <span>Created</span>
            <strong>{{ new Date(shop.created_at).toLocaleDateString() }}</strong>
          </div>
        </div>
      </article>

      <article class="detail-card">
        <h4>Shop Information</h4>
        <div class="info-grid">
          <div class="info-group">
            <label>Shop Name</label>
            <p>{{ shop.name }}</p>
          </div>
          <div class="info-group">
            <label>City</label>
            <p>{{ getCityName(shop.city_id) }}</p>
          </div>
          <div class="info-group">
            <label>Address</label>
            <p>{{ shop.address }}</p>
          </div>
          <div class="info-group">
            <label>Description</label>
            <p>{{ shop.description || 'No description provided' }}</p>
          </div>
        </div>
      </article>
    </section>
  </div>
</template>

<style scoped>
.myshop-page {
  position: relative;
  padding: 6px;
}

.success-toast {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 1100;
  background: #0f172a;
  color: #fff;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  animation: toastIn 0.25s ease;
}

.toast-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #22c55e;
}

.hero {
  position: relative;
  overflow: hidden;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  border-radius: 18px;
  border: 1px solid #dce6fb;
  background:
    radial-gradient(circle at 20% 20%, #cfe0ff 0%, transparent 44%),
    linear-gradient(130deg, #f7fbff 0%, #edf4ff 55%, #f2f8ff 100%);
  padding: 24px;
  margin-bottom: 16px;
}

.hero-content {
  position: relative;
  z-index: 2;
  max-width: 640px;
}

.eyebrow {
  margin: 0;
  color: #183ea3;
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero h2 {
  margin: 8px 0;
  color: #14213d;
  font-size: 32px;
  line-height: 1.05;
}

.hero-sub {
  margin: 0;
  color: #63708a;
  max-width: 520px;
}

.hero-actions {
  margin-top: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.hero-hint {
  font-size: 13px;
  color: #475569;
}

.hero-orb {
  width: 170px;
  height: 170px;
  border-radius: 50%;
  background: linear-gradient(160deg, #2f6bff, #1e40af);
  box-shadow: inset 0 0 40px rgba(255, 255, 255, 0.4);
  opacity: 0.24;
}

.empty-layout {
  display: grid;
  grid-template-columns: minmax(250px, 1fr) minmax(320px, 1.35fr);
  gap: 14px;
}

.empty-card,
.profile-card,
.metric-card,
.detail-card {
  background: #fff;
  border: 1px solid #dbe4f2;
  border-radius: 14px;
  padding: 16px;
}

.intro-card h3,
.form-card h3,
.detail-card h4,
.metric-card h4 {
  margin: 0 0 8px;
  color: #14213d;
}

.intro-card p {
  margin: 0;
  color: #63708a;
  line-height: 1.5;
}

.checklist {
  margin-top: 14px;
  display: grid;
  gap: 8px;
}

.check-item {
  padding: 9px 12px;
  border-radius: 9px;
  border: 1px dashed #c8d7f2;
  color: #1e3a8a;
  background: #f8fbff;
  font-size: 13px;
}

.form-stack {
  margin-top: 14px;
  display: flex;
  flex-direction: column;
  gap: 13px;
}

label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-weight: 600;
  color: #25324b;
  font-size: 13px;
}

input,
select,
textarea {
  border: 1px solid #c7d3ea;
  border-radius: 10px;
  background: #fff;
  padding: 10px 12px;
  font-size: 14px;
  color: #0f172a;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

textarea {
  min-height: 88px;
  resize: vertical;
}

.muted {
  color: #63708a;
}

.error-message {
  color: #dc2626;
  font-size: 13px;
}

.primary-btn,
.ghost-btn {
  border: 0;
  border-radius: 10px;
  padding: 10px 14px;
  cursor: pointer;
  font-weight: 700;
  font-size: 13px;
}

.primary-btn {
  color: #fff;
  background: linear-gradient(140deg, #2563eb, #1e40af);
}

.primary-btn:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.ghost-btn {
  background: #eef2ff;
  color: #334155;
  border: 1px solid #d6def5;
}

.shop-layout {
  display: grid;
  gap: 14px;
}

.profile-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 14px;
  align-items: flex-start;
}

.avatar {
  width: 64px;
  height: 64px;
  border-radius: 14px;
  background: linear-gradient(145deg, #3b82f6, #1d4ed8);
  color: #fff;
  display: grid;
  place-items: center;
  font-size: 24px;
  font-weight: 700;
}

.profile-main h3 {
  margin: 0;
}

.desc {
  margin: 8px 0 0;
  color: #334155;
  line-height: 1.5;
}

.status-chip {
  align-self: start;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  text-transform: capitalize;
}

.status-chip.active {
  background: #dcfce7;
  color: #166534;
}

.status-chip.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.metric-grid {
  margin-top: 12px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.metric {
  border: 1px solid #e1e9f8;
  background: #f8fbff;
  border-radius: 10px;
  padding: 10px;
  min-height: 74px;
}

.metric span {
  display: block;
  font-size: 12px;
  color: #64748b;
  margin-bottom: 6px;
}

.metric strong {
  color: #0f172a;
  font-size: 14px;
  line-height: 1.3;
  word-break: break-word;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.info-group {
  border: 1px solid #e5ecf7;
  background: #fff;
  border-radius: 10px;
  padding: 10px;
}

.info-group label {
  color: #64748b;
  font-size: 12px;
}

.info-group p {
  margin: 4px 0 0;
  color: #0f172a;
  font-size: 14px;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 14px;
}

.modal-panel {
  width: 100%;
  max-width: 520px;
  background: #fff;
  border-radius: 14px;
  border: 1px solid #dbe4f3;
  padding: 16px;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.modal-head h3 {
  margin: 0;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@keyframes toastIn {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 980px) {
  .empty-layout {
    grid-template-columns: 1fr;
  }

  .hero {
    padding: 18px;
  }

  .hero h2 {
    font-size: 28px;
  }

  .hero-orb {
    display: none;
  }
}

@media (max-width: 720px) {
  .profile-card {
    grid-template-columns: 1fr;
  }

  .metric-grid,
  .info-grid {
    grid-template-columns: 1fr;
  }

  .success-toast {
    right: 14px;
    left: 14px;
    top: 14px;
    justify-content: center;
  }
}
</style>
