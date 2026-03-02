<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { vehicleApi } from '@/services/api'

const categories = ['Car','Moto', 'Bike']
const statuses = ['Available', 'Rented', 'Maintenance']
const shops = ['Main Shop - Phnom Penh', 'Branch Shop - Siem Reap']

const search = ref('')
const categoryFilter = ref('All Categories')
const statusFilter = ref('All Status')
const modal = ref(false)
const editId = ref(null)
const loading = ref(false)

// Delete confirmation modal
const showDeleteModal = ref(false)
const deleteId = ref(null)
const deleteVehicleName = ref('')

const confirmDelete = (v) => {
    deleteId.value = v.id
    deleteVehicleName.value = v.name ? `${v.name} - ${v.plate || 'No plate'}` : v.plate || 'Unknown Vehicle'
    showDeleteModal.value = true
}

const cancelDelete = () => {
    deleteId.value = null
    deleteVehicleName.value = ''
    showDeleteModal.value = false
}

// Toast notification
const showToast = (message, type = 'info') => {
    const toast = document.createElement('div')
    toast.className = `toast toast-${type}`
    toast.textContent = message
    document.body.appendChild(toast)
    setTimeout(() => toast.classList.add('show'), 10)
    setTimeout(() => {
        toast.classList.remove('show')
        setTimeout(() => document.body.removeChild(toast), 300)
    }, 3000)
}

const khTime = () => {
    const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Phnom_Penh', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).formatToParts(new Date()).map(x => [x.type, x.value]))
    return `${p.year}-${p.month}-${p.day} ${p.hour}:${p.minute}:${p.second}`
}

const vehicles = ref([])

// Fetch vehicles from database
const fetchVehicles = async () => {
    try {
        loading.value = true
        const response = await vehicleApi.getAll()
        // Handle paginated response
        vehicles.value = response.data.data || response.data || []
        // Map database fields to frontend fields
        vehicles.value = vehicles.value.map(v => {
            // Parse photos - could be base64 array or JSON string
            let parsedPhotos = []
            try {
                if (v.photos) {
                    if (Array.isArray(v.photos)) {
                        parsedPhotos = v.photos
                    } else if (typeof v.photos === 'string') {
                        parsedPhotos = JSON.parse(v.photos)
                    }
                }
            } catch (e) {
                parsedPhotos = []
            }
            
            return {
                ...v,
                plate: v.plate_number,
                price: v.price_per_day,
                createdAt: v.create_at,
                updatedAt: v.updated_at,
                previewUrl: v.image_url || (parsedPhotos.length > 0 ? parsedPhotos[0] : sampleThumb),
                photos: parsedPhotos,
                base64Photos: parsedPhotos
            }
        })
    } catch (error) {
        console.error('Error fetching vehicles:', error)
    } finally {
        loading.value = false
    }
}

// Load vehicles on mount
onMounted(() => {
    fetchVehicles()
})

const sampleThumb = 'https://images.unsplash.com/photo-1549924231-f129b911e442?auto=format&fit=crop&w=900&q=80'
const photoPreview = ref(sampleThumb)
const form = reactive({ name: '', brand: '', model: '', category: '', shop: 'Main Shop - Phnom Penh', plate: '', description: '', fuel: '', transmission: '', price: '', status: 'Available', updatedAt: '', photos: [], base64Photos: [] })

// Format date to show only day, month, year
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  try {
    const date = new Date(dateStr)
    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = date.getFullYear()
    return `${day}/${month}/${year}`
  } catch (e) {
    return dateStr
  }
}

// Status icons
const getStatusIcon = (status) => {
  if (status === 'Available') {
    return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
  } else if (status === 'Rented') {
    return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#ef4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
  } else if (status === 'Maintenance') {
    return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#f59e0b" stroke-width="2.5"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>'
  }
  return ''
}

// Get status badge class
const getStatusClass = (status) => {
  if (status === 'Available') return 'status-available'
  if (status === 'Rented') return 'status-rented'
  if (status === 'Maintenance') return 'status-maintenance'
  return ''
}

const filteredVehicles = computed(() => {
    const s = search.value.trim().toLowerCase()
    return vehicles.value.filter(v => {
        const sOk = !s || 
            (v.name && v.name.toLowerCase().includes(s)) || 
            (v.category && v.category.toLowerCase().includes(s)) ||
            (v.brand && v.brand.toLowerCase().includes(s)) ||
            (v.model && v.model.toLowerCase().includes(s)) ||
            (v.plate && v.plate.toLowerCase().includes(s))
        const cOk = categoryFilter.value === 'All Categories' || v.category === categoryFilter.value
        const stOk = statusFilter.value === 'All Status' || v.status === statusFilter.value
        return sOk && cOk && stOk
    })
})

const totalVehicles = computed(() => vehicles.value.length)
const availableVehicles = computed(() => vehicles.value.filter(v => v.status === 'Available').length)
const maintenanceVehicles = computed(() => vehicles.value.filter(v => v.status === 'Maintenance').length)
const potentialPerDay = computed(() => vehicles.value.reduce((a, b) => a + Number(b.price || 0), 0))

const openCreate = () => {
    editId.value = null
    Object.assign(form, { name: '', brand: '', model: '', category: '', shop: 'Main Shop - Phnom Penh', plate: '', description: '', fuel: '', transmission: '', price: '', status: 'Available', updatedAt: khTime(), photos: [], base64Photos: [] })
    photoPreview.value = sampleThumb
    modal.value = true
}

const openEdit = (v) => {
    editId.value = v.id
    // Parse photos to get base64 data
    let photosData = v.photos || []
    if (typeof v.photos === 'string') {
        try {
            photosData = JSON.parse(v.photos)
        } catch (e) {
            photosData = []
        }
    }
    Object.assign(form, {
        name: v.name || '',
        brand: v.brand || '',
        model: v.model || '',
        category: v.category || '',
        shop: v.shop || 'Main Shop - Phnom Penh',
        plate: v.plate || '',
        description: v.description || '',
        fuel: v.fuel || '',
        transmission: v.transmission || '',
        price: v.price || '',
        status: v.status || 'Available',
        updatedAt: khTime(),
        photos: Array.isArray(photosData) ? photosData.map((_, i) => `Photo ${i + 1}`) : [],
        base64Photos: photosData || []
    })
    // Use the stored base64 image or the previewUrl
    photoPreview.value = (photosData && photosData.length > 0) ? photosData[0] : (v.previewUrl || sampleThumb)
    modal.value = true
}

const saveVehicle = async () => {
    if (!form.name || !form.category || !form.price) {
        alert('Please fill in all required fields: Name, Category, and Price')
        return
    }

    // Validate price is a valid number
    const priceValue = Number(form.price)
    if (isNaN(priceValue) || priceValue <= 0) {
        alert('Please enter a valid price per day')
        return
    }

    const payload = {
        name: form.name,
        brand: form.brand,
        model: form.model,
        category: form.category,
        plate: form.plate || `AUTO-${Date.now().toString().slice(-5)}`,
        price: priceValue,
        status: form.status,
        shop: form.shop,
        description: form.description,
        fuel: form.fuel,
        transmission: form.transmission,
        photos: form.base64Photos || [],
        previewUrl: photoPreview.value
    }
    
    // Close modal immediately and show success
    modal.value = false
    showToast('Vehicle saved successfully!', 'success')
    
    try {
        if (editId.value) {
            // Update existing vehicle
            const response = await vehicleApi.update(editId.value, payload)
            const updatedData = response.data
            const index = vehicles.value.findIndex(v => v.id === editId.value)
            if (index >= 0) {
                vehicles.value[index] = {
                    ...vehicles.value[index],
                    name: updatedData.name,
                    brand: updatedData.brand,
                    model: updatedData.model,
                    category: updatedData.type,
                    plate: updatedData.plate_number,
                    price: updatedData.price_per_day,
                    status: updatedData.status,
                    description: updatedData.description,
                    fuel: updatedData.fuel_type,
                    transmission: updatedData.transmission,
                    previewUrl: updatedData.image_url || (form.base64Photos && form.base64Photos[0]) || sampleThumb,
                    photos: form.base64Photos || [],
                    base64Photos: form.base64Photos || [],
                    updatedAt: khTime()
                }
            }
        } else {
            // Create new vehicle - add immediately to list
            const newVehicle = {
                id: Date.now(),
                name: form.name,
                brand: form.brand,
                model: form.model,
                category: form.category,
                plate: form.plate || `AUTO-${Date.now().toString().slice(-5)}`,
                price: priceValue,
                status: form.status,
                description: form.description,
                fuel: form.fuel,
                transmission: form.transmission,
                previewUrl: photoPreview.value,
                photos: form.base64Photos || [],
                base64Photos: form.base64Photos || [],
                createdAt: khTime(),
                updatedAt: khTime()
            }
            vehicles.value.unshift(newVehicle)
            
            // Save to backend in background
            try {
                const response = await vehicleApi.create(payload)
                const newData = response.data
                // Update with real ID from server
                const idx = vehicles.value.findIndex(v => v.id === newVehicle.id)
                if (idx >= 0) {
                    vehicles.value[idx].id = newData.id
                }
            } catch (e) {
                console.error('Background save error:', e)
            }
        }
    } catch (error) {
        console.error('Error saving vehicle:', error)
    }
}

const removeVehicle = async () => {
    if (!deleteId.value) return
    try {
        await vehicleApi.delete(deleteId.value)
        vehicles.value = vehicles.value.filter(v => v.id !== deleteId.value)
        showToast('Vehicle deleted successfully!', 'success')
    } catch (error) {
        console.error('Error deleting vehicle:', error)
        showToast('Failed to delete vehicle. Please try again.', 'error')
    } finally {
        cancelDelete()
    }
}

const onPhotos = async (e) => {
    const files = Array.from(e.target.files || [])

    // Convert files to base64 for persistent storage
    const convertToBase64 = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader()
            reader.onload = () => resolve(reader.result)
            reader.onerror = reject
            reader.readAsDataURL(file)
        })
    }

    try {
        const base64Images = await Promise.all(files.map(f => convertToBase64(f)))
        const names = files.map(f => f.name)
        form.photos = [...form.photos, ...names].slice(0, 8)

        // Store base64 data for persistence
        if (!form.base64Photos) form.base64Photos = []
        form.base64Photos = [...form.base64Photos, ...base64Images].slice(0, 8)

        if (files.length) photoPreview.value = base64Images[0]
    } catch (error) {
        console.error('Error converting images:', error)
        // Fallback to old behavior
        const names = files.map(f => f.name)
        form.photos = [...form.photos, ...names].slice(0, 8)
        if (files.length) photoPreview.value = URL.createObjectURL(files[0])
    }
}
const onPhotoDrop = async (e) => {
    const files = Array.from(e.dataTransfer?.files || [])

    // Convert files to base64 for persistent storage
    const convertToBase64 = (file) => {
        return new Promise((resolve, reject) => {
            const reader = new FileReader()
            reader.onload = () => resolve(reader.result)
            reader.onerror = reject
            reader.readAsDataURL(file)
        })
    }

    try {
        const base64Images = await Promise.all(files.map(f => convertToBase64(f)))
        const names = files.map(f => f.name)
        form.photos = [...form.photos, ...names].slice(0, 8)

        // Store base64 data for persistence
        if (!form.base64Photos) form.base64Photos = []
        form.base64Photos = [...form.base64Photos, ...base64Images].slice(0, 8)

        if (files.length) photoPreview.value = base64Images[0]
    } catch (error) {
        console.error('Error converting images:', error)
        // Fallback to old behavior
        const names = files.map(f => f.name)
        form.photos = [...form.photos, ...names].slice(0, 8)
        if (files.length) photoPreview.value = URL.createObjectURL(files[0])
    }
}
</script>

<template>
    <div class="panel vehicles-page">
        <div class="line top-line">
            <div>
                <h3>Manage Vehicles</h3>
                <p class="muted">Add, edit, and manage your rental fleet</p>
            </div>
            <div class="controls">
                <div class="search">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#64748b" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input v-model="search" placeholder="Search vehicle name, model, or plate" />
                </div>
                <div class="select-wrap">
                    <select v-model="categoryFilter" class="select">
                        <option>All Categories</option>
                        <option v-for="c in categories" :key="c">{{ c }}</option>
                    </select>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2">
                        <path d="M6 9l6 6 6-6"></path>
                    </svg>
                </div>
                <div class="select-wrap">
                    <select v-model="statusFilter" class="select">
                        <option>All Status</option>
                        <option v-for="s in statuses" :key="s">{{ s }}</option>
                    </select>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2">
                        <path d="M6 9l6 6 6-6"></path>
                    </svg>
                </div>
                <button class="primary add-btn" @click="openCreate"><svg viewBox="0 0 24 24" width="16" height="16"
                        fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg> Add New Vehicle</button>
            </div>
        </div>

        <div class="cards">
            <article>
                <div class="icon-box blue"><svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 13v-2a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v2"></path>
                        <circle cx="7.5" cy="17.5" r="1.5"></circle>
                        <circle cx="16.5" cy="17.5" r="1.5"></circle>
                    </svg></div>
                <div>
                    <h3>{{ totalVehicles }}</h3>
                    <span>Total Vehicles</span>
                </div>
            </article>
            <article>
                <div class="icon-box green"><svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M8 12.5l2.4 2.4L16 9.3"></path>
                    </svg></div>
                <div>
                    <h3>{{ availableVehicles }}</h3>
                    <span>Available</span>
                </div>
            </article>
            <article>
                <div class="icon-box yellow"><svg viewBox="0 0 24 24" width="20" height="20" fill="none"
                        stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3l3 3"></path>
                        <path d="M3 21l3.8-.7L19 8a2.1 2.1 0 0 0-3-3L3.8 17.3z"></path>
                    </svg></div>
                <div>
                    <h3>{{ maintenanceVehicles }}</h3>
                    <span>Maintenance</span>
                </div>
            </article>
        </div>

        <div class="table">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Vehicle</th>
                        <th>Brand / Model</th>
                        <th>Plate Number</th>
                        <th>Price/Day</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="v in filteredVehicles" :key="v.id">
                        <td>
                            <img :src="v.previewUrl || sampleThumb" alt="Vehicle" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;" />
                        </td>
                        <td>{{ v.name }}</td>
                        <td>{{ v.brand }} / {{ v.model }}</td>
                        <td>{{ v.plate }}</td>
                        <td>${{ v.price }}</td>
                        <td>
                            <span :class="['status-badge', getStatusClass(v.status)]">
                                <span v-html="getStatusIcon(v.status)"></span> {{ v.status }}
                            </span>
                        </td>
                        <td>{{ formatDate(v.createdAt) }}</td>
                        <td class="actions">
                            <button class="action-btn edit" @click="openEdit(v)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </button>
                            <button class="action-btn delete" @click="confirmDelete(v)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="filteredVehicles.length === 0" class="empty">
                <div class="empty-card">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.8">
                        <path d="M3 13v-2a3 3 0 0 1 3-3h12a3 3 0 0 1 3 3v2"></path>
                        <circle cx="7.5" cy="17.5" r="1.5"></circle>
                        <circle cx="16.5" cy="17.5" r="1.5"></circle>
                    </svg>
                    <p>No vehicles found. Click "Add New Vehicle" to get started.</p>
                </div>
            </div>
        </div>

        <div v-if="modal" class="add-vehicle-overlay" @click.self="modal = false">
            <div class="add-vehicle-modal">
                <!-- Header -->
                <div class="add-vehicle-header">
                    <h2>{{ editId ? 'Edit Vehicle' : 'Add New Vehicle' }}</h2>
                    <button class="close-btn" @click="modal = false">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="add-vehicle-content">
                    <!-- Left Column -->
                    <div class="add-vehicle-left">
                        <!-- Vehicle Identification -->
                        <div class="form-card">
                            <h3 class="card-title">Vehicle Identification</h3>
                            <div class="form-group">
                                <label>Vehicle Name</label>
                                <input v-model="form.name" placeholder="e.g. 2023 Luxury Sedan White" />
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Category</label>
                                    <select v-model="form.category">
                                        <option value="" disabled>Select Category</option>
                                        <option v-for="c in categories" :key="c">{{ c }}</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Shop Location</label>
                                    <select v-model="form.shop">
                                        <option value="" disabled>Select Shop</option>
                                        <option v-for="shop in shops" :key="shop">{{ shop }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-card">
                            <h3 class="card-title">Description</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Plate Number</label>
                                    <input v-model="form.plate" placeholder="e.g. ABC-1234" />
                                </div>
                                <div class="form-group">
                                    <label>Fuel Type</label>
                                    <select v-model="form.fuel">
                                        <option value="" disabled>Select Fuel</option>
                                        <option>Petrol</option>
                                        <option>Diesel</option>
                                        <option>Electric</option>
                                        <option>Hybrid</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Transmission</label>
                                    <select v-model="form.transmission">
                                        <option value="" disabled>Select Transmission</option>
                                        <option>Automatic</option>
                                        <option>Manual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mt-12">
                                <label>Description</label>
                                <textarea v-model="form.description" placeholder="Provide a detailed description of the vehicle features..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="add-vehicle-right">
                        <!-- Pricing & Status -->
                        <div class="form-card">
                            <h3 class="card-title">Pricing & Status</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Price per Day ($)</label>
                                    <input v-model="form.price" type="number" placeholder="$ 0.00" />
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select v-model="form.status">
                                        <option>Available</option>
                                        <option>Rented</option>
                                        <option>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Photos -->
                        <div class="form-card">
                            <h3 class="card-title">Vehicle Photos</h3>
                            <div class="photo-upload-area" @click="$refs.photos && $refs.photos.click()" @dragover.prevent @drop.prevent="onPhotoDrop">
                                <input ref="photos" type="file" multiple accept="image/png, image/jpeg" @change="onPhotos" style="display:none" />
                                <div v-if="!photoPreview || photoPreview === sampleThumb" class="upload-placeholder">
                                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 5 17 10"></polyline>
                                        <line x1="12" y1="5" x2="12" y2="16"></line>
                                    </svg>
                                    <p>Click to upload or drag and drop</p>
                                    <span>PNG, JPG up to 10MB</span>
                                </div>
                                <div v-else class="photo-preview">
                                    <img :src="photoPreview" alt="Vehicle preview" />
                                    <div class="photo-overlay">
                                        <span>Click to change</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="add-vehicle-footer">
                    <button class="discard-btn" @click="modal = false">Discard Draft</button>
                    <button class="store-btn" @click="saveVehicle">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        {{ editId ? 'Update Vehicle' : 'Store Product' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="delete-overlay" @click.self="cancelDelete">
        <div class="delete-modal">
            <div class="delete-icon-wrapper">
                <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <h3 class="delete-title">Delete Vehicle</h3>
            <p class="delete-message">Are you sure you want to delete this vehicle?</p>
            <p class="delete-vehicle-name">{{ deleteVehicleName }}</p>
            <p class="delete-warning">This action cannot be undone. All associated data (maintenance history, documents, photos, assignments) will be permanently removed.</p>
            <div class="delete-actions">
                <button class="delete-cancel-btn" @click="cancelDelete">Cancel</button>
                <button class="delete-confirm-btn" @click="removeVehicle">Delete</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.status-available {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-rented {
    background: #fee2e2;
    color: #dc2626;
}

.status-badge.status-maintenance {
    background: #fef3c7;
    color: #92400e;
}

.line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px
}

.cards {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin: 10px 0
}

.cards article {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px
}

.cards span {
    color: #64748b
}

.cards h3 {
    margin: 8px 0 0
}

.form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
    margin: 10px 0
}

input,
select {
    width: 100%;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px
}

.table {
    overflow: auto;
    border: 1px solid #e2e8f0;
    border-radius: 10px
}

table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse
}

th,
td {
    padding: 10px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left
}

th {
    font-size: 12px;
    background: #f8fafc;
    text-transform: uppercase;
    color: #475569
}

.primary,
button {
    border: 0;
    border-radius: 9px;
    padding: 8px 12px;
    cursor: pointer
}

.primary {
    background: #1d4ed8;
    color: #fff
}



.overlay {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 260px;
    right: 0;
    background: rgba(2, 6, 23, .35);
    display: grid;
    place-items: center;
    padding: 24px;
    z-index: 50;
}

.modal {
    width: min(900px, 100%);
    max-height: 92vh;
    overflow: auto;
    background: #fff;
    border-radius: 14px;
    padding: 14px
}

@media(max-width:1000px) {

    .cards,
    .form {
        grid-template-columns: 1fr 1fr
    }
}

@media(max-width:680px) {

    .cards,
    .form {
        grid-template-columns: 1fr
    }
}

/* custom styles to match screenshot */
.vehicles-page {
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial
}

.top-line {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px
}

.top-line h3 {
    margin: 0
}

.top-line .muted {
    margin: 4px 0 0
}

.controls {
    display: flex;
    gap: 10px;
    align-items: center
}

.select-wrap {
    position: relative;
    display: inline-flex;
    align-items: center;
}

.select-wrap svg {
    position: absolute;
    right: 12px;
    pointer-events: none;
}

.search {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid #e6eef8;
    padding: 8px 12px;
    border-radius: 10px
}

.search input {
    border: 0;
    outline: none;
    min-width: 280px
}

.select {
    background: #fff;
    border: 1px solid #e6eef8;
    padding: 8px 34px 8px 12px;
    border-radius: 10px;
    appearance: none;
    min-width: 188px;
}

.add-btn {
    background: #2563eb;
    color: #fff;
    display: inline-flex;
    gap: 8px;
    align-items: center;
    border-radius: 10px;
    padding: 10px 16px;
    font-weight: 600;
}

.cards article {
    display: flex;
    align-items: center;
    gap: 14px;
    min-height: 76px;
}

.icon-box {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: #2563eb
}

.icon-box.blue {
    background: #e8edff;
    color: #2f6bff
}

.icon-box.green {
    background: #e8f5ee;
    color: #22a36a
}

.icon-box.yellow {
    background: #f8efe2;
    color: #e09a21
}

.cards article div span {
    color: #64748b;
    font-size: 14px
}

.cards article div h3 {
    margin: 0;
    font-size: 38px;
    line-height: 1
}

.table {
    margin-top: 10px
}

.empty {
    padding: 18px
}

.empty-card {
    background: #fff;
    border: 1px solid #e6eef8;
    border-radius: 10px;
    padding: 36px;
    text-align: center;
    color: #94a3b8
}

.empty-card svg {
    display: block;
    margin: 0 auto 8px;
}

.actions {
    white-space: nowrap;
}

/* Action Buttons - matching Coupons.vue style */
.action-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s;
    margin-right: 8px;
}

.action-btn:last-child {
    margin-right: 0;
}

.action-btn:hover {
    background: #f1f5f9;
}

.action-btn.edit:hover {
    color: #2563eb;
    border-color: #2563eb;
}

.action-btn.delete:hover {
    color: #dc2626;
    border-color: #dc2626;
}

.action-btn svg {
    width: 16px;
    height: 16px;
}

/* modal form styles */
.form-modal {
    width: min(820px, 100%);
    padding: 18px
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px
}

.modal-header .back {
    background: transparent;
    border: 0;
    color: #334155;
    cursor: pointer
}

.card-section {
    background: #fbfdff;
    border: 1px solid #eef4fb;
    border-radius: 10px;
    padding: 14px;
    margin-bottom: 12px
}

.card-section h4 {
    margin: 0 0 8px
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px
}

.card-section small {
    color: #64748b;
    margin-bottom: 6px;
    display: block
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px
}

.full-line {
    grid-column: 1 / -1;
}

.desc-help {
    margin: 0 0 8px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.45
}

textarea {
    width: 100%;
    min-height: 96px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 10px;
    font-size: 14px;
    resize: vertical
}

.dropzone {
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    padding: 12px;
    cursor: pointer;
    background: #fcfeff
}

.dropzone .dz-inner {
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: center;
    padding: 14px
}

.dropzone .dz-text {
    color: #64748b;
    text-align: center
}

.dz-list {
    margin-top: 8px;
    color: #475569;
    font-size: 13px
}

.photo-help {
    margin: 0 0 10px;
    color: #64748b;
    font-size: 12px
}

.preview-row {
    margin-top: 10px;
    display: grid;
    grid-template-columns: 1.2fr 1fr 1fr 1fr;
    gap: 10px
}

.preview-main,
.preview-empty {
    height: 82px;
    border-radius: 10px;
    border: 1px dashed #cbd5e1;
    display: grid;
    place-items: center;
    font-size: 12px;
    color: #64748b;
    background: #f8fafc
}

.preview-main {
    background: linear-gradient(180deg, #29424f, #3f5e6c);
    color: #fff;
    font-weight: 600;
    border: 0
}

.preview-empty {
    font-size: 24px;
    color: #94a3b8
}

.upload-rule {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 12px
}

@media(max-width:900px) {

    .grid-2,
    .preview-row {
        grid-template-columns: 1fr
    }
}

/* Delete Modal Styles */
.delete-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.delete-modal {
    background: white;
    border-radius: 16px;
    padding: 32px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.delete-icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.delete-title {
    font-size: 24px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 12px;
}

.delete-message {
    font-size: 16px;
    color: #333;
    margin: 0 0 8px;
    font-weight: 500;
}

.delete-vehicle-name {
    font-size: 14px;
    color: #e74c3c;
    font-weight: 600;
    margin: 0 0 16px;
    padding: 8px 16px;
    background: #fef2f2;
    border-radius: 8px;
    display: inline-block;
}

.delete-warning {
    font-size: 13px;
    color: #666;
    margin: 0 0 24px;
    line-height: 1.5;
}

.delete-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.delete-cancel-btn {
    padding: 12px 24px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 120px;
}

.delete-cancel-btn:hover {
    border-color: #999;
    color: #333;
}

.delete-confirm-btn {
    padding: 12px 24px;
    border: none;
    background: #e74c3c;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 120px;
}

.delete-confirm-btn:hover {
    background: #c0392b;
}

@media (max-width: 480px) {
    .delete-modal {
        padding: 24px;
        margin: 16px;
    }
    
    .delete-actions {
        flex-direction: column;
    }
    
    .delete-cancel-btn,
    .delete-confirm-btn {
        width: 100%;
    }
}

/* Add Vehicle Modal Styles */
.add-vehicle-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.add-vehicle-modal {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 900px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
}

.add-vehicle-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e2e8f0;
}

.add-vehicle-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0;
}

.add-vehicle-header .close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: #f1f5f9;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    transition: all 0.2s;
}

.add-vehicle-header .close-btn:hover {
    background: #e2e8f0;
    color: #1a1a1a;
}

.add-vehicle-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    padding: 24px;
    overflow-y: auto;
    flex: 1;
}

.add-vehicle-left,
.add-vehicle-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}

.form-group {
    margin-bottom: 16px;
    flex: 1;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    margin-bottom: 6px;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1a1a1a;
    background: white;
    transition: all 0.2s;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #94a3b8;
}

.form-group select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 36px;
}

.form-group textarea {
    min-height: 80px;
    resize: vertical;
}

.form-row {
    display: flex;
    gap: 12px;
}

.mt-12 {
    margin-top: 12px;
}

.photo-upload-area {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 32px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.photo-upload-area:hover {
    border-color: #3b82f6;
    background: #f8fafc;
}

.upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.upload-placeholder p {
    margin: 0;
    font-size: 14px;
    color: #475569;
    font-weight: 500;
}

.upload-placeholder span {
    font-size: 12px;
    color: #94a3b8;
}

.photo-preview {
    position: relative;
    width: 100%;
    max-width: 200px;
}

.photo-preview img {
    width: 100%;
    border-radius: 8px;
    object-fit: cover;
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.photo-preview:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay span {
    color: white;
    font-size: 12px;
    font-weight: 500;
}

.add-vehicle-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

.discard-btn {
    padding: 10px 20px;
    border: 1px solid #cbd5e1;
    background: white;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.discard-btn:hover {
    border-color: #94a3b8;
    color: #1a1a1a;
}

.store-btn {
    padding: 10px 24px;
    border: none;
    background: #3b82f6;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.store-btn:hover {
    background: #2563eb;
}

@media (max-width: 768px) {
    .add-vehicle-content {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .add-vehicle-footer {
        flex-direction: column;
    }
    
    .discard-btn,
    .store-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
