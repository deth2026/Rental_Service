<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCssModule } from 'vue'
import userService from '@/services/userService.js'

const styles = useCssModule()

// Get current logged-in user's ID
const getUserId = () => userService.getCurrentUserId()

// ─── State ───────────────────────────────────────────────────────────────
const profile = ref({ name: '', email: '', phone: '', profile_picture: null })
const originalProfile = ref({ name: '', email: '', phone: '', profile_picture: null }) // Store original values
const avatarPreview = ref(null)
const profileFile = ref(null)
const fileInput = ref(null)

const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

const showPwd = ref({ current: false, newPwd: false, confirm: false })
const loading = ref({ profile: false, password: false })
const touched = ref({ newPassword: false })

const toast = ref({ show: false, message: '', type: 'success' })
const profileErrors = ref({ name: '', email: '', phone: '' })
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
    // Reset input so the same file can be re-selected
    event.target.value = ''
}

// ─── Fetch profile ────────────────────────────────────────────────────────
async function fetchProfile() {
    const userId = getUserId()
    if (!userId) {
        showToast('Please login first.', 'danger')
        return
    }
    try {
        // Use getAuthUser to get the current authenticated user's profile
        const data = await userService.getAuthUser()
        profile.value.name = data.name || ''
        profile.value.email = data.email || ''
        profile.value.phone = data.phone || ''
        profile.value.profile_picture = data.avatar_url || data.profile_picture || data.img_url || null
        
        // Store original values for comparison
        originalProfile.value = {
            name: data.name || '',
            email: data.email || '',
            phone: data.phone || '',
            profile_picture: data.avatar_url || data.profile_picture || data.img_url || null
        }
    } catch {
        showToast('Could not load profile data.', 'danger')
    }
}

// ─── Save profile ─────────────────────────────────────────────────────────
function validateProfile() {
    profileErrors.value = { name: '', email: '', phone: '' }
    let hasError = false
    
    // Only validate fields that have been changed from original values
    // This allows users to update only specific fields without requiring all fields
    const hasNameChange = profile.value.name.trim() !== originalProfile.value.name
    const hasEmailChange = profile.value.email.trim() !== originalProfile.value.email
    const hasPhoneChange = (profile.value.phone || '').trim() !== (originalProfile.value.phone || '')
    const hasFileChange = profileFile.value !== null
    
    // If nothing changed, no validation needed
    if (!hasNameChange && !hasEmailChange && !hasPhoneChange && !hasFileChange) {
        showToast('No changes detected.', 'info')
        return 'No changes detected.'
    }
    
    // Validate name only if it was changed
    if (hasNameChange && !profile.value.name.trim()) {
        profileErrors.value.name = 'Full name cannot be empty when changed.'
        hasError = true
    }
    
    // Validate email only if it was changed
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
        
        // Only append fields that have been changed
        const hasNameChange = profile.value.name.trim() !== originalProfile.value.name
        const hasEmailChange = profile.value.email.trim() !== originalProfile.value.email
        const hasPhoneChange = (profile.value.phone || '').trim() !== (originalProfile.value.phone || '')
        
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

        const res = await userService.updateProfile(userId, fd)
        
        // Update original profile with new values after successful save
        if (res.user) {
            const resolvedProfilePicture = res.user.avatar_url || res.user.profile_picture || res.user.img_url || null
            originalProfile.value = {
                name: res.user.name || '',
                email: res.user.email || '',
                phone: res.user.phone || '',
                profile_picture: resolvedProfilePicture
            }
            profile.value.profile_picture = resolvedProfilePicture || profile.value.profile_picture

            // Keep local user snapshot in sync so header/profile components refresh immediately.
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

        <!-- ── Page Header ── -->
        <div :class="styles['page-header']">
            <div :class="styles['page-header__icon']">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path
                        d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </div>
            <div>
                <h1 :class="styles['page-header__title']">Account Settings</h1>
                <p :class="styles['page-header__sub']">Manage your personal information and security preferences.</p>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
         CARD 1 – Personal Information
    ═══════════════════════════════════════════════════════════ -->
        <div :class="styles['card']">
            <!-- Card Header -->
            <div :class="styles['section-header']">
                <div :class="styles['section-header__icon']">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <div>
                    <h2 :class="styles['section-header__title']">Personal Information</h2>
                    <p :class="styles['section-header__sub']">Update your name, email address, and phone number.</p>
                </div>
            </div>

            <!-- Profile Picture Row -->
            <div :class="styles['avatar-row']">
                <div :class="styles['avatar']" @click="triggerFileInput" role="button" tabindex="0"
                    aria-label="Upload profile picture" @keydown.enter="triggerFileInput"
                    @keydown.space.prevent="triggerFileInput">
                    <img v-if="avatarSrc" :src="avatarSrc" :class="styles['avatar__img']" alt="Profile photo" />
                    <span v-else :class="styles['avatar__initials']">{{ initials }}</span>
                    <div :class="styles['avatar__overlay']">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                    </div>
                </div>

                <div :class="styles['avatar-info']">
                    <p :class="styles['avatar-info__label']">Profile Picture</p>
                    <p :class="styles['avatar-info__hint']">Click the avatar to upload a new photo. Max 5MB.</p>
                    <button :class="styles['btn-outline']" type="button" @click="triggerFileInput">Upload Photo</button>
                </div>

                <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                    style="display:none" @change="handleFileChange" />
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

            <button :class="[styles['btn-primary'], loading.profile && styles['btn-primary--loading']]" type="button"
                :disabled="loading.profile" @click="saveProfile">
                <svg v-if="!loading.profile" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span :class="styles['spinner']" v-if="loading.profile"></span>
                {{ loading.profile ? 'Saving…' : 'Save Changes' }}
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
         CARD 2 – Change Password
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
                        <!-- eye -->
                        <svg v-if="showPwd.current" width="17" height="17" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <!-- eye-off -->
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
                {{ loading.password ? 'Updating…' : 'Update Password' }}
            </button>
        </div>

    </div>
</template>

<style module>
/* ─── Page Layout ──────────────────────────────────────────── */
.page {
    min-height: 100vh;
    background: var(--color-bg);
    padding: 32px 24px 60px;
    max-width: 560px;
    margin: 0 auto;
    position: relative;
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

/* ─── Page Header ──────────────────────────────────────────── */
.page-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}

.page-header__icon {
    width: 44px;
    height: 44px;
    background: var(--color-primary-light);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-primary);
    flex-shrink: 0;
}

.page-header__title {
    font-size: 1.45rem;
    font-weight: 700;
    color: var(--color-text-primary);
    line-height: 1.2;
}

.page-header__sub {
    font-size: 0.875rem;
    color: var(--color-text-secondary);
    margin-top: 3px;
}

/* ─── Card ─────────────────────────────────────────────────── */
.card {
    background: var(--color-card-bg);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    border: 1px solid var(--color-border);
    padding: 28px;
    margin-bottom: 20px;
}

/* ─── Section Header ───────────────────────────────────────── */
.section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--color-border);
}

.section-header__icon {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md);
    background: var(--color-primary-light);
    border: 1px solid var(--color-primary-mid);
    color: var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-header__title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--color-text-primary);
}

.section-header__sub {
    font-size: 0.82rem;
    color: var(--color-text-secondary);
    margin-top: 3px;
}

.text-blue {
    /* color: var(--color-primary); */
}

/* ─── Avatar Row ───────────────────────────────────────────── */
.avatar-row {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
}

/* The avatar circle – fixed dimensions; image never changes its size */
.avatar {
    width: 72px;
    height: 72px;
    min-width: 72px;
    border-radius: 50%;
    background: var(--color-avatar-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--color-avatar-text);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border: 3px solid var(--color-primary-mid);
    transition: border-color 0.2s;
}

.avatar:hover {
    border-color: var(--color-primary);
}

.avatar:focus-visible {
    outline: 3px solid var(--color-primary);
    outline-offset: 2px;
}

/* Image fills the circle; object-fit keeps it clean regardless of dimensions */
.avatar__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
}

.avatar__initials {
    user-select: none;
    letter-spacing: 0.5px;
}

.avatar__overlay {
    position: absolute;
    inset: 0;
    background: rgba(37, 99, 235, 0.55);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 50%;
}

.avatar:hover .avatar__overlay {
    opacity: 1;
}

.avatar-info {
    flex: 1;
}

.avatar-info__label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 3px;
}

.avatar-info__hint {
    font-size: 0.78rem;
    color: var(--color-text-secondary);
    margin-bottom: 10px;
}

/* ─── Form Fields ──────────────────────────────────────────── */
.field-group {
    margin-bottom: 18px;
}

.field-label {
    display: block;
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--color-text-primary);
    margin-bottom: 7px;
}

.field-label--danger {
    color: var(--color-danger);
}

.input-wrap {
    display: flex;
    align-items: center;
    gap: 0;
    /* background: var(--color-input-bg); */
    /* border: 1.5px solid var(--color-border); */
    border-radius: var(--radius-md);
    padding: 0 14px;
    transition: border-color 0.18s, box-shadow 0.18s;
}

/* .input-wrap:focus-within {
    border-color: var(--color-border-focus);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
    background: #fff;
} */

.input-icon {
    color: var(--color-input-icon);
    flex-shrink: 0;
    margin-right: 10px;
}

/* .input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 11px 0;
    font-size: 0.9rem;
    color: var(--color-text-primary);
    min-width: 0;
} */

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
    padding: 11px 24px;
    background: var(--color-primary);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.18s, transform 0.12s, box-shadow 0.18s;
    margin-top: 6px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
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

.btn-outline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: #fff;
    color: var(--color-text-primary);
    border: 1.5px solid var(--color-border);
    border-radius: var(--radius-md);
    font-size: 0.82rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.18s, background 0.18s;
}

.btn-outline:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
    background: var(--color-primary-light);
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

/* ─── Toast Notification ───────────────────────────────────── */
.toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 14px 16px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-toast);
    border: 1px solid transparent;
    min-width: 280px;
    max-width: 360px;
    background: #fff;
}

.toast--success {
    border-color: var(--color-success-border);
    border-left: 4px solid var(--color-success);
    background: var(--color-success-light);
}

.toast--danger {
    border-color: var(--color-danger-border);
    border-left: 4px solid var(--color-danger);
    background: var(--color-danger-light);
}

.toast__icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    margin-top: 1px;
}

.toast--success .toast__icon {
    color: var(--color-success);
}

.toast--danger .toast__icon {
    color: var(--color-danger);
}

.toast__msg {
    flex: 1;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--color-text-primary);
    line-height: 1.45;
}

.toast__close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    padding: 2px;
    margin-top: 1px;
    border-radius: 4px;
    transition: color 0.15s;
}

.toast__close:hover {
    color: var(--color-text-primary);
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
