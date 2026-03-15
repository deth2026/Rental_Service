<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCssModule } from 'vue'
<<<<<<< HEAD
<<<<<<< HEAD
import { useRouter } from 'vue-router'
=======
import { useRoute, useRouter } from 'vue-router'
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
import { useRouter } from 'vue-router'
>>>>>>> dashboard/admin
import userService from '@/services/userService.js'
import CommonFooter from '../../components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'

const styles = useCssModule()
const router = useRouter()
<<<<<<< HEAD
<<<<<<< HEAD
=======
const route = useRoute()

const mapUserToProfile = (user = {}) => {
    const createdAt = user.created_at || user.create_at || ''
    return {
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        profile_picture: user.avatar_url || user.profile_picture || user.img_url || null,
        role: user.role || '',
        location: user.location || user.country || '',
        country: user.country || '',
        joined_at: user.joined_at || createdAt,
        created_at: createdAt,
    }
}

const storedUser = userService.getCurrentUser() || {}
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin

// Get current logged-in user's ID
const getUserId = () => userService.getCurrentUserId()

// ─── State ───────────────────────────────────────────────────────────────
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
=======
const profile = ref(mapUserToProfile(storedUser))
const originalProfile = ref({
    name: profile.value.name,
    email: profile.value.email,
    phone: profile.value.phone,
    profile_picture: profile.value.profile_picture,
}) // Store original values
const avatarPreview = ref(null)
const MAX_AVATAR_SIZE = 5 * 1024 * 1024
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
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

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
=======
const userDisplayName = computed(() => profile.value.name || 'Guest User')
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin

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

const heroRoleLabel = computed(() => profile.value.role || 'Member')
const heroLocationLabel = computed(() => profile.value.location || profile.value.country || 'Location not set')
const heroJoinedLabel = computed(() => {
    const raw = profile.value.joined_at || profile.value.created_at
    if (raw) {
        const parsed = new Date(raw)
        if (!Number.isNaN(parsed)) {
            return `Joined ${parsed.toLocaleString('en-US', { month: 'long', year: 'numeric' })}`
        }
    }
    return 'Joined date unavailable'
})
const heroTabs = ['Profile', 'Teams', 'Projects', 'Connections']
const aboutItems = computed(() => [
    { label: 'Full Name', value: profile.value.name || 'John Doe' },
    { label: 'Status', value: 'Active' },
    { label: 'Role', value: heroRoleLabel.value },
    { label: 'Country', value: heroLocationLabel.value },
    { label: 'Languages', value: profile.value.language || 'English' },
])

const contactItems = computed(() => [
    { label: 'Contact', value: profile.value.phone || '(123) 456-7890' },
    { label: 'Email', value: profile.value.email || 'john.doe@example.com' },
    { label: 'Skype', value: profile.value.skype || 'john.doe' },
])

const overviewMetrics = computed(() => [
    { label: 'Tasks Completed', value: '13.5k' },
    { label: 'Projects Completed', value: '146' },
    { label: 'Connections', value: '897' },
])

const navItems = [
    { label: 'Home', route: '/view_shop' },
    { label: 'My Bookings', route: '' },
    { label: 'Promotions', route: '/promotions' },
]

const activeNav = computed(() => {
    const currentPath = route.path
    const matched = navItems.find((item) => item.route && currentPath.startsWith(item.route))
    return matched?.label || 'Home'
})

const setActiveNav = (item) => {
    if (item.route) {
        router.push(item.route)
        return
    }
    notify('My Bookings page is not available yet.')
}

const notify = (message) => {
    console.log(message)
}

const timelineEvents = [
    {
        id: 1,
        title: '12 Invoices have been paid',
        description: 'Invoices have been paid to the company',
        time: '12 min ago',
        color: '#16a34a',
    },
    {
        id: 2,
        title: 'Client Meeting',
        description: 'Project meeting with John @10:15am',
        time: '45 min ago',
        color: '#4f46e5',
    },
    {
        id: 3,
        title: 'Create a new project for client',
        description: '6 team members in a project',
        time: '2 days ago',
        color: '#ea580c',
    },
]

const connectionList = [
    { name: 'Cecilia Payne', info: '45 Connections', color: '#a855f7', initials: 'CP' },
    { name: 'Curtis Fletcher', info: '1.32K Connections', color: '#3b82f6', initials: 'CF' },
    { name: 'Alice Stone', info: '125 Connections', color: '#ef4444', initials: 'AS' },
    { name: 'Darrell Barnes', info: '456 Connections', color: '#f97316', initials: 'DB' },
    { name: 'Eugenia Moore', info: '1.2K Connections', color: '#0891b2', initials: 'EM' },
]

const teamList = [
    { name: 'React Developers', members: '72', label: 'Developer', badgeKey: 'developer' },
    { name: 'Support Team', members: '122', label: 'Support', badgeKey: 'support' },
    { name: 'UI Designers', members: '7', label: 'Designer', badgeKey: 'designer' },
    { name: 'Vue.js Developers', members: '289', label: 'Developer', badgeKey: 'developer' },
    { name: 'Digital Marketing', members: '24', label: 'Marketing', badgeKey: 'marketing' },
];

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
    if (file.size > MAX_AVATAR_SIZE) {
        showToast('Image must be less than 5 MB.', 'danger')
        event.target.value = ''
        return
    }
    const reader = new FileReader()
    reader.onload = e => { avatarPreview.value = e.target.result }
    reader.readAsDataURL(file)
    event.target.value = ''
    profileFile.value = file
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

<<<<<<< HEAD
=======
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

>>>>>>> dashboard/admin
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
<<<<<<< HEAD
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
<<<<<<< HEAD
=======
        const mappedProfile = mapUserToProfile(data)
        Object.assign(profile.value, mappedProfile)

        // Store original values for comparison
        originalProfile.value = {
            name: mappedProfile.name,
            email: mappedProfile.email,
            phone: mappedProfile.phone,
            profile_picture: mappedProfile.profile_picture,
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
<<<<<<< HEAD
    const hasBackgroundChange = backgroundFile.value !== null
    
    if (!hasNameChange && !hasEmailChange && !hasPhoneChange && !hasFileChange && !hasBackgroundChange) {
=======

    // If nothing changed, no validation needed
    if (!hasNameChange && !hasEmailChange && !hasPhoneChange && !hasFileChange) {
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
    const hasBackgroundChange = backgroundFile.value !== null
    
    if (!hasNameChange && !hasEmailChange && !hasPhoneChange && !hasFileChange && !hasBackgroundChange) {
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
        const hasProfilePictureRemoved = originalProfile.value.profile_picture && !profile.value.profile_picture
        const hasBackgroundPictureRemoved = originalProfile.value.background_picture && !profile.value.background_picture
        
=======
        const hasFileChange = profileFile.value !== null

>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
        if (hasNameChange) {
            fd.append('name', profile.value.name.trim())
        }
        if (hasEmailChange) {
            fd.append('email', profile.value.email.trim())
        }
        if (hasPhoneChange) {
            fd.append('phone', profile.value.phone?.trim() || '')
        }
        if (hasFileChange && profileFile.value) {
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
<<<<<<< HEAD
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
<<<<<<< HEAD
=======
            const mappedProfile = mapUserToProfile(res.user)
            originalProfile.value = {
                name: mappedProfile.name,
                email: mappedProfile.email,
                phone: mappedProfile.phone,
                profile_picture: mappedProfile.profile_picture,
            }
            const avatar = mappedProfile.profile_picture || profile.value.profile_picture
            Object.assign(profile.value, mappedProfile, { profile_picture: avatar })
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin

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
<<<<<<< HEAD
<<<<<<< HEAD
        backgroundFile.value = null
=======
        avatarPreview.value = null
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
        backgroundFile.value = null
>>>>>>> dashboard/admin
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

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
=======
const handleLogout = async () => {
    try {
        await userService.logout()
    } finally {
        router.push('/login')
    }
}

const openProfile = () => {
    router.push('/user/profile')
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
}

onMounted(fetchProfile)
</script>
<template>
    <header class="topbar">
            <div class="brand">
                <div class="brand-icon"><i class="fa-solid fa-gift" aria-hidden="true"></i></div>
                <span>Chong Choul</span>
            </div>

            <nav class="nav-links">
                <button
                    v-for="item in navItems"
                    :key="item.label"
                    class="btn-reset nav-link"
                    :class="{ active: activeNav === item.label }"
                    @click="setActiveNav(item)"
                >
                    {{ item.label }}
                </button>
            </nav>

            <div class="top-actions">
                <span class="user-display-name">{{ userDisplayName }}</span>
                <UserProfileMenu @settings="openProfile" @logout="handleLogout" />
            </div>
    </header>
    <div :class="styles['page']">
        <transition name="slide-right">
            <div
                v-if="toast.show"
                :class="[
                    styles['toast'],
                    toast.type === 'success'
                        ? styles['toast--success']
                        : toast.type === 'info'
                            ? styles['toast--info']
                            : styles['toast--danger']
                ]"
            >
                <svg v-if="toast.type === 'success'" :class="styles['toast__icon']" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                <svg v-else :class="styles['toast__icon']" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <span :class="styles['toast__msg']">{{ toast.message }}</span>
            </div>
        </transition>

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
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
<<<<<<< HEAD
=======
        <section :class="styles['hero']">
            <div :class="styles['hero-stripes']">
                <span v-for="stripe in 5" :key="stripe" :class="styles['hero-stripe']"></span>
            </div>
            <div :class="styles['hero-content']">
            <div
                :class="[styles['hero-avatar'], loading.profile && styles['hero-avatar--loading']]"
                role="button"
                tabindex="0"
                @click="triggerFileInput"
                @keydown.enter="triggerFileInput"
                @keydown.space.prevent="triggerFileInput"
                :aria-busy="loading.profile"
            >
                <img v-if="avatarSrc" :src="avatarSrc" :class="styles['hero-avatar__img']" alt="Profile photo" />
                <span v-else :class="styles['hero-avatar__initials']">{{ initials }}</span>
                <div :class="styles['hero-avatar__overlay']">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
=======
            </div>

            <!-- Profile Action Buttons -->
            <div :class="styles['profile-actions']">
                <button :class="styles['btn-upload']" type="button" @click="triggerFileInput">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
>>>>>>> dashboard/admin
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
<<<<<<< HEAD
                </div>
                <div v-if="loading.profile" :class="styles['hero-avatar__loader']">
                    <span :class="styles['hero-avatar__loader-dot']"></span>
                </div>
            </div>
                <div :class="styles['hero-text']">
                    <h1 :class="styles['hero-name']">{{ profile.name || 'John Doe' }}</h1>
                    <p :class="styles['hero-role']">{{ heroRoleLabel }}</p>
                    <div :class="styles['hero-meta']">
                        <span>{{ heroLocationLabel }}</span>
                        <span>•</span>
                        <span>{{ heroJoinedLabel }}</span>
                    </div>
                </div>
                <button type="button" :class="styles['status-btn']">
                    <i class="fa-solid fa-user-check" aria-hidden="true"></i>
                    Connected
                </button>
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
            </div>
            <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                style="display:none" @change="handleFileChange" />
        </section>

<<<<<<< HEAD
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
=======
                    Upload Image
                </button>
                <button v-if="avatarSrc || avatarPreview" :class="styles['btn-remove']" type="button" @click="removeProfilePicture">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                    </svg>
>>>>>>> dashboard/admin
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
<<<<<<< HEAD

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
=======
        <!-- <nav :class="styles['tab-nav']">
            <button v-for="tab in heroTabs" :key="tab" type="button" :class="[styles['tab'], tab === heroTabs[0] ? styles['tab--active'] : '']">
                {{ tab }}
            </button>
        </nav> -->

        <div :class="styles['content-grid']">
            <div :class="styles['left-column']">
                <!-- <div :class="styles['info-card']">
                    <div :class="styles['card-header']">
                        <p :class="styles['card-title']">About</p>
                        <p :class="styles['card-subtitle']">Profile highlights</p>
                    </div>
                    <ul :class="styles['info-list']">
                        <li v-for="item in aboutItems" :key="item.label">
                            <span :class="styles['info-label']">{{ item.label }}</span>
                            <span :class="styles['info-value']">{{ item.value }}</span>
                        </li>
                    </ul>
                </div> -->
                <div :class="styles['info-card']">
                    <!-- <div :class="styles['card-header']">
                        <p :class="styles['card-title']">Contacts</p>
                        <p :class="styles['card-subtitle']">How to reach you</p>
                    </div> -->
                    <ul :class="styles['info-list']">
                        <li v-for="item in contactItems" :key="item.label">
                            <span :class="styles['info-label']">{{ item.label }}</span>
                            <span :class="styles['info-value']">{{ item.value }}</span>
                        </li>
                    </ul>
                </div>
                <!-- <div :class="styles['info-card']">
                    <div :class="styles['card-header']">
                        <p :class="styles['card-title']">Overview</p>
                        <p :class="styles['card-subtitle']">Your impact in numbers</p>
                    </div>
                    <div :class="styles['overview-grid']">
                        <div v-for="metric in overviewMetrics" :key="metric.label" :class="styles['overview-item']">
                            <p :class="styles['overview-value']">{{ metric.value }}</p>
                            <p :class="styles['overview-label']">{{ metric.label }}</p>
                        </div>
                    </div>
                </div> -->
            </div>
            <!-- <div :class="styles['right-column']">
                <div :class="styles['activity-card']">
                    <div :class="styles['card-header']">
                        <p :class="styles['card-title']">Activity Timeline</p>
                        <p :class="styles['card-subtitle']">Latest updates</p>
                    </div>
                    <div :class="styles['timeline']">
                        <div v-for="event in timelineEvents" :key="event.id" :class="styles['timeline-event']">
                            <div :class="styles['timeline-dot']" :style="{ background: event.color }"></div>
                            <div :class="styles['timeline-content']">
                                <p :class="styles['timeline-title']">{{ event.title }}</p>
                                <p :class="styles['timeline-desc']">{{ event.description }}</p>
                            </div>
                            <span :class="styles['timeline-time']">{{ event.time }}</span>
                        </div>
                    </div>
                </div>
                <div :class="styles['activity-card']">
                    <div :class="styles['card-header']">
                        <p :class="styles['card-title']">Connections</p>
                        <p :class="styles['card-subtitle']">Your network</p>
                    </div>
                    <div :class="styles['connections-list']">
                        <div v-for="connection in connectionList" :key="connection.name" :class="styles['connection-item']">
                            <div :class="styles['connection-avatar']" :style="{ background: connection.color }">
                                {{ connection.initials }}
                            </div>
                            <div :class="styles['connection-details']">
                                <span :class="styles['connection-name']">{{ connection.name }}</span>
                                <span :class="styles['connection-info']">{{ connection.info }}</span>
                            </div>
                            <button type="button" :class="styles['connection-action']">
                                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div :class="styles['activity-card']">
                    <div :class="styles['card-header']">
                        <p :class="styles['card-title']">Teams</p>
                        <p :class="styles['card-subtitle']">Groups you collaborate with</p>
                    </div>
                    <div :class="styles['teams-list']">
                        <div v-for="team in teamList" :key="team.name" :class="styles['team-item']">
                            <div>
                                <p :class="styles['team-name']">{{ team.name }}</p>
                                <p :class="styles['team-members']">{{ team.members }} Members</p>
                            </div>
                            <span :class="[styles['badge'], styles[`badge--${team.badgeKey}`]]">{{ team.label }}</span>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>

        <div :class="styles['forms-grid']">
            <div :class="styles['form-card']">
                <div :class="styles['form-card__header']">
                    <div>
                        <p :class="styles['form-card__title']">Personal Information</p>
                        <p :class="styles['form-card__sub']">Update your contact details.</p>
                    </div>
                </div>
                <div :class="styles['form-card__content']">
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
                <button
                    :class="[styles['btn-primary'], loading.profile && styles['btn-primary--loading']]"
                    type="button"
                    :disabled="loading.profile"
                    @click="saveProfile"
                >
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======

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
>>>>>>> dashboard/admin
                    <svg v-if="!loading.profile" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span :class="styles['spinner']" v-if="loading.profile"></span>
                    {{ loading.profile ? 'Saving…' : 'Save Changes' }}
                </button>
            </div>
<<<<<<< HEAD
<<<<<<< HEAD
=======

        </div>
    </div>
</template>
>>>>>>> dashboard/admin

=======
            <div :class="styles['form-card']">
                <div :class="styles['form-card__header']">
                    <div>
                        <p :class="styles['form-card__title']">Change Password</p>
                        <p :class="styles['form-card__sub']">Keep your account secure with a strong password.</p>
                    </div>
                </div>
                <div :class="styles['form-card__content']">
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
                </div>
                <button :class="[styles['btn-primary'], loading.password && styles['btn-primary--loading']]" type="button"
                    :disabled="loading.password" @click="changePassword">
                    <span :class="styles['spinner']" v-if="loading.password"></span>
                    {{ loading.password ? 'Updating…' : 'Update Password' }}
                </button>
            </div>
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
        </div>
    </div>

    <!-- Common Footer -->
    <CommonFooter />
</template>
<style module>
/* ─── Page Layout ──────────────────────────────────────────── */
.page {
    min-height: 100vh;
<<<<<<< HEAD
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
=======
    /* background: #eef1f7; */
    padding: 32px clamp(16px, 4vw, 48px) 60px;
    max-width: 80%;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-top: 20px;
    margin-bottom: 20px;
}


>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05

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

<<<<<<< HEAD
.text-blue {
    color: #3b82f6;
    font-weight: 600;
<<<<<<< HEAD
}
=======

>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05

/* ─── Info Grid ─────────────────────────────────────────────── */
.info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

<<<<<<< HEAD
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
=======
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
    position: relative;
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.15);
}

.hero-stripes {
    display: flex;
    height: 160px;
}

.hero-stripe {
    flex: 1;
}

.hero-stripe:nth-child(1) {
    background: #33c2c8;
}

.hero-stripe:nth-child(2) {
    background: #9dddcf;
}

.hero-stripe:nth-child(3) {
    background: #f6d4c2;
}

.hero-stripe:nth-child(4) {
    background: #f8b4c4;
}

.hero-stripe:nth-child(5) {
    background: #ffd8d8;
}

.hero-content {
    background: #fff;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 18px 32px 18px;
}

.hero-avatar {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: #eef1f7;
    display: grid;
    place-items: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    position: absolute;
    margin-bottom: 8%;
    cursor: pointer;
    border: 4px solid #fff;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
}

.hero-avatar__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.hero-avatar__initials {
    letter-spacing: 0.3px;
}

.hero-avatar__overlay {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(249, 115, 22, 0.16);
    display: grid;
    place-items: center;
    opacity: 0;
    transition: opacity 0.2s;
    color: #f97316;
}

.hero-avatar:hover .hero-avatar__overlay,
.hero-avatar:focus-visible .hero-avatar__overlay {
    opacity: 1;
}

.hero-avatar--loading {
    cursor: wait;
}

.hero-avatar__loader {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.45);
    display: grid;
    place-items: center;
    z-index: 2;
}

.hero-avatar__loader-dot {
    width: 22px;
    height: 22px;
    border: 3px solid rgba(255, 255, 255, 0.65);
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}

.hero-text {
    flex: 1;
    position: relative;
    top: 60px;
}

.hero-name {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.hero-role {
    margin: 0.1rem 0;
    font-size: 1rem;
    color: #4b5563;
    font-weight: 600;
}

.hero-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    color: #6b7280;
}



.status-btn {
    border: none;
    background: #2563eb;
    color: #fff;
    padding: 10px 18px;
    border-radius: 999px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
}

.tab-nav {
    background: #fff;
    border-radius: 24px;
    padding: 10px;
    display: flex;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    flex-wrap: wrap;
}

.tab {
    border: none;
    background: transparent;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 600;
    color: #4b5563;
    cursor: pointer;
}

.tab--active {
    background: #eef2ff;
    color: #4338ca;
}

.content-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-top: 60px;
}

.left-column,
.right-column {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.info-card,
.activity-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.card-header {
    margin-bottom: 16px;
}

.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
}

.card-subtitle {
    margin: 2px 0 0;
    font-size: 0.85rem;
    color: #6b7280;
}

.info-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.info-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
}

.info-value {
    font-size: 1rem;
    color: #111827;
    font-weight: 600;
}

.overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
}

.overview-item {
    background: #f9fafb;
    border-radius: 14px;
    padding: 12px 14px;
}

.overview-value {
    margin: 0;
    font-size: 1.4rem;
    font-weight: 700;
    color: #111827;
}

.overview-label {
    margin: 4px 0 0;
    font-size: 0.8rem;
    color: #6b7280;
}

.timeline {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.timeline-event {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 12px;
    align-items: center;
}

.timeline-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
}

.timeline-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.timeline-title {
    margin: 0;
    font-weight: 600;
    color: #111827;
}

.timeline-desc {
    margin: 0;
    font-size: 0.85rem;
    color: #6b7280;
}

.timeline-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.connections-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.connection-item {
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 10px 4px;
}

.connection-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-weight: 600;
    color: #fff;
}

.connection-details {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.connection-name {
    font-weight: 600;
    color: #111827;
}

.connection-info {
    font-size: 0.78rem;
    color: #6b7280;
}

.connection-action {
    border: none;
    background: #eef2ff;
    color: #4338ca;
    width: 36px;
    height: 36px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    cursor: pointer;
}

.teams-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.team-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.team-item:last-child {
    border-bottom: none;
}

.team-name {
    margin: 0;
    font-weight: 600;
    color: #111827;
}

.team-members {
    margin: 2px 0 0;
    font-size: 0.78rem;
    color: #6b7280;
}

.badge {
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge--developer {
    background: #ede9fe;
    color: #7c3aed;
}

.badge--support {
    background: #e0f2fe;
    color: #0369a1;
}

.badge--designer {
    background: #fce7f3;
    color: #be185d;
}

.badge--marketing {
    background: #fef3c7;
    color: #b45309;
}

.forms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 24px;
}

.form-card {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.form-card__header {
    margin-bottom: 18px;
}

.form-card__title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #111827;
}

.form-card__sub {
    margin: 4px 0 0;
    font-size: 0.85rem;
    color: #6b7280;
}

.form-card__content {
    display: flex;
    flex-direction: column;
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
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
>>>>>>> dashboard/admin
}

.field-group {
    margin-bottom: 18px;
}

.field-label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
<<<<<<< HEAD
    color: var(--color-text-primary);
    margin-bottom: 8px;
<<<<<<< HEAD
=======
    color: #4b5563;
    margin-bottom: 6px;
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
}

.field-label--danger {
    color: #dc2626;
}

.input-wrap {
    display: flex;
    align-items: center;
<<<<<<< HEAD
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
<<<<<<< HEAD
=======
    gap: 10px;
    border-radius: 14px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
}

.input-wrap:focus-within {
    border-color: #4338ca;
    box-shadow: 0 0 0 2px rgba(67, 56, 202, 0.15);
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
    background: #fff;
}

.input-icon {
    color: #94a3b8;
    flex-shrink: 0;
}

.input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
    padding: 12px 0;
    font-size: 0.9rem;
    color: var(--color-text-primary);
    min-width: 0;
<<<<<<< HEAD
=======
    font-size: 0.95rem;
    color: #111827;
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
}

.input::placeholder {
    color: #cbd5f5;
}

.input--error {
    border-color: #dc2626 !important;
}

.field-error {
    color: #dc2626;
    font-size: 0.78rem;
    margin-top: 6px;
}

.field-hint {
    font-size: 0.78rem;
    color: #6b7280;
    margin-top: 6px;
}

.eye-btn {
    background: none;
    border: none;
    padding: 6px 0 6px 8px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    cursor: pointer;
    flex-shrink: 0;
}

.eye-btn:hover {
    color: #111827;
}

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
    width: 32px;
    height: 4px;
    border-radius: 99px;
    background: #e5e7eb;
}

.strength__bar--weak {
    background: #dc2626;
}

.strength__bar--medium {
    background: #f97316;
}

.strength__bar--strong {
    background: #16a34a;
}

.strength__label {
    font-size: 0.75rem;
    font-weight: 600;
}

.strength__label--weak {
    color: #dc2626;
}

.strength__label--medium {
    color: #f97316;
}

.strength__label--strong {
    color: #16a34a;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
    padding: 12px 24px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.9rem;
=======
    justify-content: center;
    padding: 12px 20px;
    border-radius: 999px;
    background: #2563eb;
    color: #fff;
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
    font-weight: 600;
    border: none;
    cursor: pointer;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.45);
}

.btn-primary:active:not(:disabled) {
    transform: scale(0.98);
=======
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
    transition: transform 0.15s ease;
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    box-shadow: none;
}

.btn-primary--loading {
    pointer-events: none;
}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> dashboard/admin
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
=======
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
.spinner {
    width: 15px;
    height: 15px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

.toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #fff;
    padding: 14px 18px;
    border-radius: 14px;
    box-shadow: 0 25px 50px rgba(15, 23, 42, 0.25);
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid #e5e7eb;
    font-weight: 600;
    color: #111827;
}

.toast--success {
    border-color: #22c55e;
    color: #16a34a;
}

.toast--danger {
    border-color: #ef4444;
    color: #b91c1c;
}

.toast--info {
    border-color: #f59e0b;
    color: #a16207;
}

.toast__icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.toast__msg {
    font-size: 0.9rem;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
<<<<<<< HEAD
<<<<<<< HEAD
=======

@media (max-width: 960px) {
    .hero-content {
        flex-direction: column;
        align-items: flex-start;
    }
    .status-btn {
        align-self: stretch;
        justify-content: center;
    }
}
>>>>>>> 4ffa805566421966ff5189a6e66dbebf88990d05
=======
>>>>>>> dashboard/admin
</style>

<style>
.slide-right-enter-active {
    animation: slideInRight 0.35s ease;
}

.slide-right-leave-active {
    animation: slideOutRight 0.3s ease forwards;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(40px);
    }
}
</style>
