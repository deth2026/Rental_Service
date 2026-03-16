<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '../../composables/useToast.js'
import userService from '../../services/userService.js'
import { userService as localUserService } from '../../services/database.js'
import CommonFooter from '../../components/CommonFooter.vue'
import UserProfileMenu from '@/components/UserProfileMenu.vue'
import Logo from '@/components/Logo.vue'

const router = useRouter()
const route = useRoute()
const toast = useToast()

const navItems = [
  { label: 'Home', route: '/view_shop' },
  { label: 'My Bookings', route: '/bookings' },
  { label: 'Promotion', route: '/promotions' },
]

const activeNav = computed(() => {
  const matchedItem = navItems.find((item) => item.route && route.path.startsWith(item.route))
  return matchedItem?.label || 'Home'
})

const setActiveNav = (item) => {
  if (item?.route) router.push(item.route)
}

const currentUser = computed(() => localUserService.getCurrentUser())
const userDisplayName = computed(() => currentUser.value?.name || 'Guest User')

const profile = ref({ name: '', email: '', phone: '', profile_picture: null })
const originalProfile = ref({ name: '', email: '', phone: '', profile_picture: null })

const profileFile = ref(null)
const avatarPreview = ref('')
const fileInput = ref(null)

const avatarSrc = computed(() => avatarPreview.value || userService.getAvatarUrl(profile.value.profile_picture) || '')

const initials = computed(() => {
  const parts = String(profile.value.name || userDisplayName.value || '').trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return 'U'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase()
})

const loadingProfile = ref(false)
const loadingPassword = ref(false)

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

const onAvatarChange = (e) => {
  const file = e?.target?.files?.[0]
  if (!file) return
  profileFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
}

const loadProfile = async () => {
  const local = userService.getCurrentUser() || {}
  profile.value = {
    name: local.name || '',
    email: local.email || '',
    phone: local.phone || '',
    profile_picture: local.profile_picture || local.avatar_url || local.img_url || null,
  }
  originalProfile.value = { ...profile.value }

  try {
    const serverUser = await userService.getAuthUser()
    const next = {
      name: serverUser?.name || profile.value.name,
      email: serverUser?.email || profile.value.email,
      phone: serverUser?.phone || profile.value.phone,
      profile_picture:
        serverUser?.profile_picture || serverUser?.avatar_url || serverUser?.img_url || profile.value.profile_picture,
    }
    profile.value = next
    originalProfile.value = { ...next }
  } catch {
    // ignore (offline / not authenticated)
  }
}

const saveProfile = async () => {
  const userId = userService.getCurrentUserId()
  if (!userId) {
    toast.error('Please login first.')
    router.push('/login')
    return
  }

  loadingProfile.value = true
  try {
    const fd = new FormData()
    fd.append('name', String(profile.value.name || '').trim())
    fd.append('email', String(profile.value.email || '').trim())
    fd.append('phone', String(profile.value.phone || '').trim())
    if (profileFile.value) fd.append('profile_picture', profileFile.value)

    const res = await userService.updateProfile(userId, fd)
    const updated = res?.user || res?.data || res

    if (updated && typeof updated === 'object') {
      localStorage.setItem('user', JSON.stringify({ ...(userService.getCurrentUser() || {}), ...updated }))
      profile.value.profile_picture =
        updated.profile_picture || updated.avatar_url || updated.img_url || profile.value.profile_picture
      originalProfile.value = { ...profile.value }
    }

    toast.success('Profile updated.')
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to update profile.')
  } finally {
    loadingProfile.value = false
  }
}

const changePassword = async () => {
  const userId = userService.getCurrentUserId()
  if (!userId) {
    toast.error('Please login first.')
    router.push('/login')
    return
  }

  loadingPassword.value = true
  try {
    await userService.changePassword(userId, passwordForm.value)
    toast.success('Password updated.')
    passwordForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
  } catch (err) {
    toast.error(err?.response?.data?.message || err?.message || 'Failed to change password.')
  } finally {
    loadingPassword.value = false
  }
}

const openProfile = () => {
  router.push('/user/profile')
}

const handleLogout = async () => {
  try {
    await localUserService.logout()
  } finally {
    router.push('/login')
  }
}

onMounted(loadProfile)
</script>

<template>
  <div class="setting-page">
    <header class="topbar">
      <Logo class="brand" to="/view_shop" size="md" />

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

    <div class="frame">
      <section class="panel content-card">
        <div class="section-head">
          <h1 class="section-title">Profile Settings</h1>
        </div>

        <div class="avatar-panel">
          <div class="avatar-row">
            <img v-if="avatarSrc" class="profile-avatar" :src="avatarSrc" alt="Profile" />
            <div v-else class="profile-avatar profile-avatar--placeholder">{{ initials }}</div>
            <div>
              <input ref="fileInput" type="file" accept="image/*" hidden @change="onAvatarChange" />
              <button type="button" class="btn-reset btn-avatar" @click="fileInput?.click()">Change photo</button>
            </div>
          </div>
        </div>

        <form class="form-grid" @submit.prevent="saveProfile">
          <label class="field">
            <span class="label">Full name</span>
            <input v-model="profile.name" type="text" placeholder="Your name" />
          </label>
          <label class="field">
            <span class="label">Email</span>
            <input v-model="profile.email" type="email" placeholder="you@example.com" />
          </label>
          <label class="field">
            <span class="label">Phone</span>
            <input v-model="profile.phone" type="text" placeholder="+855..." />
          </label>

          <div class="actions-row">
            <button type="submit" class="btn-reset btn-primary" :disabled="loadingProfile">
              {{ loadingProfile ? 'Saving...' : 'Save Profile' }}
            </button>
          </div>
        </form>
      </section>

      <section class="panel content-card">
        <div class="section-head">
          <h2 class="section-title">Change Password</h2>
        </div>

        <form class="form-grid" @submit.prevent="changePassword">
          <label class="field">
            <span class="label">Current password</span>
            <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
          </label>
          <label class="field">
            <span class="label">New password</span>
            <input v-model="passwordForm.new_password" type="password" autocomplete="new-password" />
          </label>
          <label class="field">
            <span class="label">Confirm password</span>
            <input v-model="passwordForm.new_password_confirmation" type="password" autocomplete="new-password" />
          </label>

          <div class="actions-row">
            <button type="submit" class="btn-reset btn-primary" :disabled="loadingPassword">
              {{ loadingPassword ? 'Updating...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </section>

      <CommonFooter />
    </div>
  </div>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 16px;
  background: #ffffff;
  border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 800;
  cursor: pointer;
  color: #0f172a;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  object-fit: contain;
  background: #dbeafe;
  padding: 2px;
}

.nav-links {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-reset {
  border: 0;
  background: transparent;
  font: inherit;
  cursor: pointer;
}

.nav-link {
  padding: 8px 12px;
  border-radius: 999px;
  color: #475569;
  font-weight: 700;
}

.nav-link.active {
  background: rgba(37, 99, 235, 0.12);
  color: #1d4ed8;
}

.top-actions {
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.user-display-name {
  font-weight: 700;
  color: #334155;
}

.profile-avatar--placeholder {
  display: grid;
  place-items: center;
  background: #dbeafe;
  color: #1d4ed8;
  font-weight: 900;
}

.form-grid {
  margin-top: 14px;
  display: grid;
  gap: 12px;
  max-width: 560px;
}

.field .label {
  display: block;
  font-weight: 800;
  font-size: 12px;
  color: #64748b;
  margin-bottom: 6px;
}

.field input {
  width: 100%;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  outline: none;
}

.actions-row {
  margin-top: 6px;
}

.btn-primary {
  height: 40px;
  padding: 0 14px;
  border-radius: 12px;
  background: #2563eb;
  color: #fff;
  font-weight: 800;
}

.btn-avatar {
  height: 34px;
  padding: 0 12px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: #fff;
  font-weight: 700;
}
</style>
