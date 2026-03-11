<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCssModule } from 'vue'
import { useRouter } from 'vue-router'
import userService from '@/services/userService.js'

const styles = useCssModule()
const router = useRouter()

// Get current logged-in user's ID
const getUserId = () => userService.getCurrentUserId()

// ─── State ───────────────────────────────────────────────────────────────
const profile = ref({ 
    name: '', 
    email: '', 
    phone: '', 
    profile_picture: null,
    background_picture: null,
    first_name: '',
    last_name: '',
    status: 'Active',
    role: 'User'
})
const originalProfile = ref({ 
    name: '', 
    email: '', 
    phone: '', 
    profile_picture: null,
    background_picture: null,
    first_name: '',
    last_name: '',
    status: 'Active',
    role: 'User'
})
const avatarPreview = ref(null)
const backgroundPreview = ref(null)
const profileFile = ref(null)
const backgroundFile = ref(null)
const fileInput = ref(null)
const backgroundInput = ref(null)

const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

const showPwd = ref({ current: false, newPwd: false, confirm: false })
const loading = ref({ profile: false, password: false })
const touched = ref({ newPassword: false })

const toast = ref({ show: false, message: '', type: 'success' })
const profileErrors = ref({ name: '', email: '', phone: '', first_name: '', last_name: '' })
const passwordErrors = ref({ current_password: '', new_password: '', new_password_confirmation: '' })
let toastTimer = null

// ─── Computed ────────────────────────────────────────────────────────────
const initials = computed(() => {
    if (!profile.value.name) return '?'
    return profile.value.name
        .split(' ')
        .map(w => w[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
})

const avatarSrc = computed(() => {
    if (avatarPreview.value) return avatarPreview.value
    if (profile.value.profile_picture) return userService.getAvatarUrl(profile.value.profile_picture)
    return null
})

const backgroundSrc = computed(() => {
    if (backgroundPreview.value) return backgroundPreview.value
    if (profile.value.background_picture) return userService.getBackgroundUrl(profile.value.background_picture)
    if (profile.value.background_picture_url) return profile.value.background_picture_url
    return null
})

// Default avatar based on user initials
const defaultAvatarColor = computed(() => {
    const colors = ['#3b82f6', '#8b5cf6', '#ec4899', '#14b8a6', '#f59e0b', '#ef4444'];
    const hash = profile.value.name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
    return colors[hash % colors.length];
})

const pwdStrength = computed(() => {
    const p = passwordForm.value.new_password
    if (!p) return null
    const score = [
        p.length >= 8,
        /[A-Z]/.test(p),
        /[a-z]/.test(p),
        /[0-9]/.test(p),
        /[^A-Za-z0-9]/.test(p),
    ].filter(Boolean).length
    if (score <= 2) return { label: 'Weak', level: 'weak' }
    if (score <= 4) return { label: 'Medium', level: 'medium' }
    return { label: 'Strong', level: 'strong' }
})

// ─── Toast ───────────────────────────────────────────────────────────────
function showToast(message, type = 'success') {
    clearTimeout(toastTimer)
    toast.value = { show: true, message, type }
    toastTimer = setTimeout(() => { toast.value.show = false }, 4000)
}

// ─── Profile picture ─────────────────────────────────────────────────────
function triggerFileInput() {
    fileInput.value.click()
}

function handleFileChange(event) {
    const file = event.target.files[0]
    if (!file) return
    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be less than 5 MB.', 'danger')
        return
    }
    profileFile.value = file
    const reader = new FileReader()
    reader.onload = e => { avatarPreview.value = e.target.result }
    reader.readAsDataURL(file)
    event.target.value = ''
}

function removeProfilePicture() {
    profileFile.value = null
    avatarPreview.value = null
    profile.value.profile_picture = null
}

// ─── Background picture ─────────────────────────────────────────────────
function triggerBackgroundInput() {
    backgroundInput.value.click()
}

function handleBackgroundChange(event) {
    const file = event.target.files[0]
    if (!file) return
    if (file.size > 5 * 1024 * 1024) {
        showToast('Image must be less than 5 MB.', 'danger')
        return
    }
    backgroundFile.value = file
    const reader = new FileReader()
    reader.onload = e => { backgroundPreview.value = e.target.result }
    reader.readAsDataURL(file)
    event.target.value = ''
}

function removeBackgroundPicture() {
    backgroundFile.value = null
    backgroundPreview.value = null
    profile.value.background_picture = null
}

// ─── Fetch profile ────────────────────────────────────────────────────────
async function fetchProfile() {
    const userId = getUserId()
    if (!userId) {
        showToast('Please login first.', 'danger')
        return
    }
    try {
        const data = await userService.getAuthUser()
        profile.value.name = data.name || ''
        profile.value.email = data.email || ''
        profile.value.phone = data.phone || ''
        profile.value.profile_picture = data.avatar_url || data.profile_picture || data.img_url || null
        profile.value.background_picture = data.background_picture_url || data.background_picture || null
        
        const nameParts = (data.name || '').split(' ')
        profile.value.first_name = nameParts[0] || ''
        profile.value.last_name = nameParts.slice(1).join(' ') || ''
        
        profile.value.status = data.status || 'Active'
        profile.value.role = data.role || 'User'
        
        originalProfile.value = {
            name: data.name || '',
            email: data.email || '',
            phone: data.phone || '',
            profile_picture: data.avatar_url || data.profile_picture || data.img_url || null,
            background_picture: data.background_picture_url || data.background_picture || null,
            first_name: profile.value.first_name,
            last_name: profile.value.last_name,
            status: profile.value.status,
            role: profile.value.role
        }
    } catch {
        showToast('Could not load profile data.', 'danger')
    }
}

// ─── Save profile ─────────────────────────────────────────────────────────
function validateProfile() {
    profileErrors.value = { name: '', email: '', phone: '', first_name: '', last_name: '' }
    let hasError = false
    
    const hasNameChange = profile.value.name.trim() !== originalProfile.value.name
    const hasEmailChange = profile.value.email.trim() !== originalProfile.value.email
    const hasPhoneChange = (profile.value.phone || '').trim() !== (originalProfile.value.phone || '')
    const hasFileChange = profileFile.value !== null
    const hasBackgroundChange = backgroundFile.value !== null
    
    if (!hasNameChange && !hasEmailChange && !hasPhoneChange && !hasFileChange && !hasBackgroundChange) {
        showToast('No changes detected.', 'info')
        return 'No changes detected.'
    }
    
    if (hasNameChange && !profile.value.name.trim()) {
        profileErrors.value.name = 'Full name cannot be empty when changed.'
        hasError = true
    }
    
    if (hasEmailChange) {
        if (!profile.value.email.trim()) {
            profileErrors.value.email = 'Email address cannot be empty when changed.'
            hasError = true
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.value.email)) {
            profileErrors.value.email = 'Please enter a valid email address.'
            hasError = true
        }
    }
    
    return hasError ? 'Please fix the errors above.' : null
}

async function saveProfile() {
    const err = validateProfile()
    if (err) { showToast(err, 'danger'); return }

    const userId = getUserId()
    if (!userId) {
        showToast('Please login first.', 'danger')
        return
    }

    loading.value.profile = true
    try {
        const fd = new FormData()
        
        const hasNameChange = profile.value.name.trim() !== originalProfile.value.name
        const hasEmailChange = profile.value.email.trim() !== originalProfile.value.email
        const hasPhoneChange = (profile.value.phone || '').trim() !== (originalProfile.value.phone || '')
        const hasProfilePictureRemoved = originalProfile.value.profile_picture && !profile.value.profile_picture
        const hasBackgroundPictureRemoved = originalProfile.value.background_picture && !profile.value.background_picture
        
        if (hasNameChange) {
            fd.append('name', profile.value.name.trim())
        }
        if (hasEmailChange) {
            fd.append('email', profile.value.email.trim())
        }
        if (hasPhoneChange) {
            fd.append('phone', profile.value.phone?.trim() || '')
        }
        if (profileFile.value) {
            fd.append('profile_picture', profileFile.value)
        }
        if (backgroundFile.value) {
            fd.append('background_picture', backgroundFile.value)
        }
        if (hasProfilePictureRemoved) {
            fd.append('remove_profile_picture', 'true')
        }
        if (hasBackgroundPictureRemoved) {
            fd.append('remove_background_picture', 'true')
        }

        const res = await userService.updateProfile(userId, fd)
        
        if (res.user) {
            const resolvedProfilePicture = res.user.avatar_url || res.user.profile_picture || res.user.img_url || null
            const resolvedBackgroundPicture = res.user.background_picture_url || res.user.background_picture || null
            originalProfile.value = {
                name: res.user.name || '',
                email: res.user.email || '',
                phone: res.user.phone || '',
                profile_picture: resolvedProfilePicture,
                background_picture: resolvedBackgroundPicture,
                first_name: profile.value.first_name,
                last_name: profile.value.last_name,
                status: profile.value.status,
                role: profile.value.role
            }
            profile.value.profile_picture = resolvedProfilePicture || profile.value.profile_picture
            profile.value.background_picture = resolvedBackgroundPicture || profile.value.background_picture

            try {
                const rawUser = localStorage.getItem('user')
                const localUser = rawUser ? JSON.parse(rawUser) : {}
                const nextUser = { ...localUser, ...res.user }
                localStorage.setItem('user', JSON.stringify(nextUser))
                window.dispatchEvent(new Event('user-updated'))
            } catch {
                // Ignore localStorage parse/write issues.
            }
        }
        
        profileFile.value = null
        backgroundFile.value = null
        showToast('Profile updated successfully!', 'success')
    } catch (e) {
        showToast(e.response?.data?.message || 'Failed to update profile.', 'danger')
    } finally {
        loading.value.profile = false
    }
}

// ─── Change password ──────────────────────────────────────────────────────
function validatePassword() {
    const p = passwordForm.value
    passwordErrors.value = { current_password: '', new_password: '', new_password_confirmation: '' }
    let hasError = false
    
    if (!p.current_password) {
        passwordErrors.value.current_password = 'Current password is required.'
        hasError = true
    }
    if (!p.new_password) {
        passwordErrors.value.new_password = 'New password is required.'
        hasError = true
    } else {
        if (p.new_password.length < 8) {
            passwordErrors.value.new_password = 'Password must be at least 8 characters.'
            hasError = true
        }
        if (!/[A-Z]/.test(p.new_password)) {
            passwordErrors.value.new_password = (passwordErrors.value.new_password ? passwordErrors.value.new_password + ' ' : '') + 'Must include uppercase letter.'
            hasError = true
        }
        if (!/[a-z]/.test(p.new_password)) {
            passwordErrors.value.new_password = (passwordErrors.value.new_password ? passwordErrors.value.new_password + ' ' : '') + 'Must include lowercase letter.'
            hasError = true
        }
        if (!/[0-9]/.test(p.new_password)) {
            passwordErrors.value.new_password = (passwordErrors.value.new_password ? passwordErrors.value.new_password + ' ' : '') + 'Must include number.'
            hasError = true
        }
        if (!/[^A-Za-z0-9]/.test(p.new_password)) {
            passwordErrors.value.new_password = (passwordErrors.value.new_password ? passwordErrors.value.new_password + ' ' : '') + 'Must include special character.'
            hasError = true
        }
    }
    if (p.new_password !== p.new_password_confirmation) {
        passwordErrors.value.new_password_confirmation = 'New passwords do not match.'
        hasError = true
    }
    return hasError ? 'Please fix the errors above.' : null
}

async function changePassword() {
    const err = validatePassword()
    if (err) { showToast(err, 'danger'); return }

    const userId = getUserId()
    if (!userId) {
        showToast('Please login first.', 'danger')
        return
    }

    loading.value.password = true
    try {
        await userService.changePassword(userId, {
            current_password: passwordForm.value.current_password,
            new_password: passwordForm.value.new_password,
            new_password_confirmation: passwordForm.value.new_password_confirmation,
        })
        showToast('Password changed successfully!', 'success')
        passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
    } catch (e) {
        showToast(e.response?.data?.message || 'Failed to change password.', 'danger')
    } finally {
        loading.value.password = false
    }
}

// ─── Navigation ────────────────────────────────────────────────────────────
function goBack() {
    router.back()
}

function cancelChanges() {
    profile.value = { ...originalProfile.value }
    avatarPreview.value = null
    backgroundPreview.value = null
    profileFile.value = null
    backgroundFile.value = null
    passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
    showToast('Changes discarded.', 'info')
}

onMounted(fetchProfile)
</script>

<template>
    <div :class="styles['page']">

        <!-- Notification popup -->
        <transition name="slide-right">
            <div v-if="toast.show" :class="[styles['right-toast'], toast.type === 'success' ? styles['right-toast--success'] : styles['right-toast--error']]">
                <svg v-if="toast.type === 'success'" :class="styles['right-toast__icon']" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                <svg v-else :class="styles['right-toast__icon']" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span>{{ toast.message }}</span>
            </div>
        </transition>

        <!-- ═══════════════════════════════════════════════════════════
         PROFILE HEADER SECTION - Full Width with Background
    ═══════════════════════════════════════════════════════════ -->
        <div :class="styles['profile-header']">
            <!-- Background Image -->
            <div :class="styles['profile-background']">
                <img v-if="backgroundSrc" :src="backgroundSrc" :class="styles['profile-background__img']" alt="Background" />
                <div v-else :class="styles['profile-background__default']"></div>
                <div :class="styles['profile-background__overlay']"></div>
                
                <!-- Background Upload Controls -->
                <div :class="styles['background-controls']">
                    <button :class="styles['bg-btn']" type="button" @click="triggerBackgroundInput">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        Upload Background
                    </button>
                    <button v-if="backgroundSrc || backgroundPreview" :class="styles['bg-btn-remove']" type="button" @click="removeBackgroundPicture">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                        Remove
                    </button>
                </div>
            </div>
            
            <!-- Back Button -->
            <button :class="styles['back-btn']" type="button" @click="goBack">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back
            </button>

            <!-- Profile Avatar Section -->
            <div :class="styles['profile-avatar-section']">
                <div :class="styles['profile-avatar']" @click="triggerFileInput" role="button" tabindex="0"
                    aria-label="Upload profile picture" @keydown.enter="triggerFileInput"
                    @keydown.space.prevent="triggerFileInput">
                    <img v-if="avatarSrc" :src="avatarSrc" :class="styles['profile-avatar__img']" alt="Profile photo" />
                    <div v-else :class="styles['profile-avatar__initials']" :style="{ backgroundColor: defaultAvatarColor }">
                        {{ initials }}
                    </div>
                    <div :class="styles['profile-avatar__overlay']">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                    </div>
                </div>

                <!-- User Name -->
                <h1 :class="styles['profile-name']">{{ profile.name || 'Your Name' }}</h1>
            </div>

            <!-- Profile Action Buttons -->
            <div :class="styles['profile-actions']">
                <button :class="styles['btn-upload']" type="button" @click="triggerFileInput">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    Upload Image
                </button>
                <button v-if="avatarSrc || avatarPreview" :class="styles['btn-remove']" type="button" @click="removeProfilePicture">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
                    Remove
                </button>
            </div>

            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                style="display:none" @change="handleFileChange" />
            <input ref="backgroundInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                style="display:none" @change="handleBackgroundChange" />
        </div>

        <!-- ═══════════════════════════════════════════════════════════
         CONTENT CONTAINER
    ═══════════════════════════════════════════════════════════ -->
        <div :class="styles['content-container']">

            <!-- ═══════════════════════════════════════════════════════════
             CARD 1 – Store Information
        ═══════════════════════════════════════════════════════════ -->
            <div :class="styles['card']">
                <!-- Card Header -->
                <div :class="styles['section-header']">
                    <div :class="styles['section-header__icon']">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </div>
                    <div>
                        <h2 :class="styles['section-header__title']">Store Information</h2>
                        <p :class="styles['section-header__sub']">Manage your personal information and account details.</p>
                    </div>
                </div>

                <!-- Store Info Grid -->
                <div :class="styles['info-grid']">
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">First Name</label>
                        <div :class="styles['info-value']">{{ profile.first_name || '-' }}</div>
                    </div>
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">Last Name</label>
                        <div :class="styles['info-value']">{{ profile.last_name || '-' }}</div>
                    </div>
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">Status</label>
                        <div :class="[styles['info-value'], styles['info-value--status']]">
                            <span :class="styles['status-badge']">{{ profile.status }}</span>
                        </div>
                    </div>
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">Role</label>
                        <div :class="styles['info-value']">{{ profile.role }}</div>
                    </div>
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">Contact</label>
                        <div :class="styles['info-value']">{{ profile.phone || '-' }}</div>
                    </div>
                    <div :class="styles['info-item']">
                        <label :class="styles['info-label']">Email</label>
                        <div :class="styles['info-value']">{{ profile.email || '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════
             CARD 2 – Edit Profile Information
        ═══════════════════════════════════════════════════════════ -->
            <div :class="styles['card']">
                <!-- Card Header -->
                <div :class="styles['section-header']">
                    <div :class="styles['section-header__icon']">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </div>
                    <div>
                        <h2 :class="styles['section-header__title']">Edit Profile Information</h2>
                        <p :class="styles['section-header__sub']">Update your personal details below.</p>
                    </div>
                </div>

                <!-- Fields -->
                <div :class="styles['field-group']">
                    <label :class="styles['field-label']">Full Name</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <input v-model="profile.name" :class="[styles['input'], profileErrors.name && styles['input--error']]" type="text"
                            placeholder="Enter your full name" autocomplete="name" />
                    </div>
                    <p v-if="profileErrors.name" :class="styles['field-error']">{{ profileErrors.name }}</p>
                </div>

                <div :class="styles['field-group']">
                    <label :class="styles['field-label']">Email Address</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2" />
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                        </svg>
                        <input v-model="profile.email" :class="[styles['input'], profileErrors.email && styles['input--error']]" type="email"
                            placeholder="Enter your email address" autocomplete="email" />
                    </div>
                    <p v-if="profileErrors.email" :class="styles['field-error']">{{ profileErrors.email }}</p>
                </div>

                <div :class="styles['field-group']">
                    <label :class="styles['field-label']">Phone Number</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.77 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 5.91 5.91l.82-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                        </svg>
                        <input v-model="profile.phone" :class="styles['input']" type="tel" placeholder="+1 (555) 123-4567"
                            autocomplete="tel" />
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════
             CARD 3 – Change Password
        ═══════════════════════════════════════════════════════════ -->
            <div :class="styles['card']">
                <!-- Card Header -->
                <div :class="styles['section-header']">
                    <div :class="styles['section-header__icon']">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <div>
                        <h2 :class="styles['section-header__title']">Change Password</h2>
                        <p :class="styles['section-header__sub']">
                            Keep <span :class="styles['text-blue']">your account secure</span> by using a strong password.
                        </p>
                    </div>
                </div>

                <!-- Current Password -->
                <div :class="styles['field-group']">
                    <label :class="[styles['field-label'], styles['field-label--danger']]">Current Password</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input v-model="passwordForm.current_password" :class="[styles['input'], passwordErrors.current_password && styles['input--error']]"
                            :type="showPwd.current ? 'text' : 'password'" placeholder="Enter current password"
                            autocomplete="current-password" />
                        <button :class="styles['eye-btn']" type="button" @click="showPwd.current = !showPwd.current"
                            :aria-label="showPwd.current ? 'Hide' : 'Show'">
                            <svg v-if="showPwd.current" width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="passwordErrors.current_password" :class="styles['field-error']">{{ passwordErrors.current_password }}</p>
                </div>

                <!-- New Password -->
                <div :class="styles['field-group']">
                    <label :class="[styles['field-label'], styles['field-label--danger']]">New Password</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input v-model="passwordForm.new_password" :class="styles['input']"
                            :type="showPwd.newPwd ? 'text' : 'password'" placeholder="Enter new password"
                            @blur="touched.newPassword = true" autocomplete="new-password" />
                        <button :class="styles['eye-btn']" type="button" @click="showPwd.newPwd = !showPwd.newPwd"
                            :aria-label="showPwd.newPwd ? 'Hide' : 'Show'">
                            <svg v-if="showPwd.newPwd" width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>

                    <!-- Strength indicator -->
                    <div v-if="pwdStrength" :class="styles['strength']">
                        <div :class="styles['strength__bars']">
                            <span :class="[styles['strength__bar'], styles[`strength__bar--${pwdStrength.level}`]]"></span>
                            <span
                                :class="[styles['strength__bar'], (pwdStrength.level === 'medium' || pwdStrength.level === 'strong') && styles[`strength__bar--${pwdStrength.level}`]]"></span>
                            <span
                                :class="[styles['strength__bar'], pwdStrength.level === 'strong' && styles['strength__bar--strong']]"></span>
                        </div>
                        <span :class="[styles['strength__label'], styles[`strength__label--${pwdStrength.level}`]]">{{
                            pwdStrength.label }}</span>
                    </div>
                    <p v-else-if="touched.newPassword" :class="styles['field-hint']">Min 8 chars · Uppercase · Lowercase · Number · Special character</p>
                    <p v-if="passwordErrors.new_password" :class="styles['field-error']">{{ passwordErrors.new_password }}</p>
                </div>

                <!-- Confirm New Password -->
                <div :class="styles['field-group']">
                    <label :class="[styles['field-label'], styles['field-label--danger']]">Confirm New Password</label>
                    <div :class="styles['input-wrap']">
                        <svg :class="styles['input-icon']" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        <input v-model="passwordForm.new_password_confirmation" :class="styles['input']"
                            :type="showPwd.confirm ? 'text' : 'password'" placeholder="Confirm new password"
                            autocomplete="new-password" />
                        <button :class="styles['eye-btn']" type="button" @click="showPwd.confirm = !showPwd.confirm"
                            :aria-label="showPwd.confirm ? 'Hide' : 'Show'">
                            <svg v-if="showPwd.confirm" width="17" height="17" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                    <p v-if="passwordErrors.new_password_confirmation" :class="styles['field-error']">{{ passwordErrors.new_password_confirmation }}</p>
                </div>

                <button :class="[styles['btn-primary'], loading.password && styles['btn-primary--loading']]" type="button"
                    :disabled="loading.password" @click="changePassword">
                    <span :class="styles['spinner']" v-if="loading.password"></span>
                    {{ loading.password ? 'Updating…' : 'Change Password' }}
                </button>
            </div>

            <!-- ═══════════════════════════════════════════════════════════
             ACTION BUTTONS - Cancel and Save
        ═══════════════════════════════════════════════════════════ -->
            <div :class="styles['action-buttons']">
                <button :class="styles['btn-cancel']" type="button" @click="cancelChanges">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Cancel
                </button>
                <button :class="[styles['btn-save'], loading.profile && styles['btn-save--loading']]" type="button"
                    :disabled="loading.profile" @click="saveProfile">
                    <svg v-if="!loading.profile" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span :class="styles['spinner']" v-if="loading.profile"></span>
                    {{ loading.profile ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>

        </div>
    </div>
</template>

<style module>
/* ─── Page Layout ──────────────────────────────────────────── */
.page {
    min-height: 100vh;
    background: var(--color-bg);
    padding-bottom: 60px;
    position: relative;
}

/* ─── Profile Header ──────────────────────────────────────────── */
.profile-header {
    position: relative;
    padding-bottom: 30px;
}

.profile-background {
    position: relative;
    height: 240px;
    overflow: hidden;
}

.profile-background__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-background__default {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.profile-background__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.3) 0%, transparent 50%);
}

.background-controls {
    position: absolute;
    bottom: 16px;
    right: 16px;
    display: flex;
    gap: 8px;
    z-index: 10;
}

.bg-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.bg-btn:hover {
    background: rgba(0, 0, 0, 0.8);
    border-color: #fff;
}

.bg-btn-remove {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(220, 38, 38, 0.9);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.bg-btn-remove:hover {
    background: #dc2626;
}

/* Back Button */
.back-btn {
    position: absolute;
    top: 16px;
    left: 16px;
    z-index: 10;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: rgba(0, 0, 0, 0.5);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(4px);
}

.back-btn:hover {
    background: rgba(0, 0, 0, 0.7);
    border-color: #fff;
}

/* Profile Avatar Section */
.profile-avatar-section {
    position: relative;
    z-index: 5;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: -70px;
}

.profile-avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border: 5px solid #fff;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease;
}

.profile-avatar:hover {
    transform: scale(1.05);
}

.profile-avatar__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-avatar__initials {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    letter-spacing: 1px;
}

.profile-avatar__overlay {
    position: absolute;
    inset: 0;
    background: rgba(37, 99, 235, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 50%;
}

.profile-avatar:hover .profile-avatar__overlay {
    opacity: 1;
}

.profile-name {
    margin-top: 14px;
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e293b;
    text-shadow: 0 1px 3px rgba(255, 255, 255, 0.8);
}

.profile-actions {
    position: relative;
    z-index: 5;
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 16px;
}

.btn-upload {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.btn-upload:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-remove {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    background: #fff;
    color: #dc2626;
    border: 1.5px solid #dc2626;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-remove:hover {
    background: #fef2f2;
}

/* ─── Content Container ──────────────────────────────────────────── */
.content-container {
    max-width: 720px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 5;
}

/* ─── Right Side Notification ───────────────────────────────── */
.right-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    padding: 16px 24px;
    border-radius: 10px;
    border: 2px solid;
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 99999;
    animation: slideInRight 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
}

.right-toast__icon {
    width: 22px;
    height: 22px;
    flex-shrink: 0;
    color: #10b981;
}

.right-toast--success {
    background: #d1fae5;
    color: #065f46;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2), 0 4px 12px rgba(16, 185, 129, 0.25);
}

.right-toast--success .right-toast__icon {
    color: #10b981;
}

.right-toast--error {
    background: #fee2e2;
    color: #991b1b;
    border-color: #dc2626;
    box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.2), 0 4px 12px rgba(220, 38, 38, 0.25);
}

.right-toast--error .right-toast__icon {
    color: #dc2626;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* ─── Card ─────────────────────────────────────────────────── */
.card {
    background: var(--color-card-bg);
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border: 1px solid var(--color-border);
    padding: 26px;
    margin-bottom: 20px;
}

/* ─── Section Header ───────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--color-border);
}

.section-header__icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-header__title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--color-text-primary);
}

.section-header__sub {
    font-size: 0.85rem;
    color: var(--color-text-secondary);
    margin-top: 4px;
}

.text-blue {
    color: #3b82f6;
    font-weight: 600;
}

/* ─── Info Grid ─────────────────────────────────────────────── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

@media (max-width: 600px) {
    .info-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.info-item {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
}

.info-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--color-text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}

.info-value {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--color-text-primary);
}

.info-value--status {
    display: flex;
    align-items: center;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    background: #10b981;
    color: #fff;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* ─── Form Fields ──────────────────────────────────────────── */
.field-group {
    margin-bottom: 18px;
}

.field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 8px;
}

.field-label--danger {
    color: var(--color-danger);
}

.input-wrap {
    display: flex;
    align-items: center;
    gap: 0;
    border-radius: 10px;
    padding: 0 14px;
    transition: border-color 0.18s, box-shadow 0.18s;
    border: 1.5px solid var(--color-border);
    background: #fff;
}

.input-wrap:focus-within {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background: #fff;
}

.input-icon {
    color: var(--color-input-icon);
    flex-shrink: 0;
    margin-right: 10px;
}

.input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 12px 0;
    font-size: 0.9rem;
    color: var(--color-text-primary);
    min-width: 0;
}

.input::placeholder {
    color: var(--color-text-muted);
}

.input--error {
    border-color: var(--color-danger) !important;
}

.input--error:focus-within {
    border-color: var(--color-danger) !important;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1) !important;
}

.field-error {
    color: var(--color-danger);
    font-size: 0.78rem;
    margin-top: 6px;
    font-weight: 500;
}

/* password eye toggle */
.eye-btn {
    background: none;
    border: none;
    padding: 6px 0 6px 8px;
    color: var(--color-input-icon);
    display: flex;
    align-items: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: color 0.15s;
}

.eye-btn:hover {
    color: var(--color-text-primary);
}

/* ─── Password Strength ────────────────────────────────────── */
.strength {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.strength__bars {
    display: flex;
    gap: 4px;
}

.strength__bar {
    width: 36px;
    height: 4px;
    border-radius: 2px;
    background: var(--color-border);
    transition: background 0.25s;
}

.strength__bar--weak {
    background: var(--color-danger);
}

.strength__bar--medium {
    background: var(--color-warning);
}

.strength__bar--strong {
    background: var(--color-success);
}

.strength__label {
    font-size: 0.75rem;
    font-weight: 600;
}

.strength__label--weak {
    color: var(--color-danger);
}

.strength__label--medium {
    color: var(--color-warning);
}

.strength__label--strong {
    color: var(--color-success);
}

.field-hint {
    font-size: 0.75rem;
    color: var(--color-text-muted);
    margin-top: 5px;
}

/* ─── Buttons ──────────────────────────────────────────────── */
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.45);
}

.btn-primary:active:not(:disabled) {
    transform: scale(0.98);
}

.btn-primary:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn-primary--loading {
    pointer-events: none;
}

/* Action Buttons - Cancel and Save */
.action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 28px;
    padding-top: 24px;
    border-top: 1px solid var(--color-border);
}

.btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: #fff;
    color: #64748b;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #334155;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 36px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.45);
}

.btn-save:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn-save--loading {
    pointer-events: none;
}

/* Spinner */
.spinner {
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<!-- ── Toast slide-in / slide-out transition ───────────────── -->
<style>
.toast-enter-active {
    animation: toast-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-leave-active {
    animation: toast-out 0.25s ease-in forwards;
}

@keyframes toast-in {
    from {
        opacity: 0;
        transform: translateY(60px) scale(0.92);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes toast-out {
    from {
        opacity: 1;
        transform: translateY(0);
    }

    to {
        opacity: 0;
        transform: translateY(60px);
    }
}
</style>
