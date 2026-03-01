<script setup>
import { reactive, ref, onMounted } from 'vue'
import { t, setLocale, getLocale, useI18n, state } from '@/i18n/index.js'

const { locale } = useI18n()

const profileKey = 'shopProfile'
const profile = reactive({ name: 'Chong Choul', email: 'chong@example.com', phone: '+85512345678' })
const password = reactive({ current: '', newPass: '', confirm: '' })
const notifications = reactive({ email: true, sms: false })
const saved = ref(false)

// Language - use reactive reference
const currentLang = ref(getLocale())

const load = () => {
	try {
		const raw = localStorage.getItem(profileKey)
		if (raw) Object.assign(profile, JSON.parse(raw))
	} catch (e) {}
}

const saveProfile = () => { 
	localStorage.setItem(profileKey, JSON.stringify(profile))
	saved.value = true
	setTimeout(()=>saved.value=false, 2000)
}

const changePassword = () => { 
	if (!password.current || !password.newPass || password.newPass !== password.confirm) return
	password.current=''
	password.newPass=''
	password.confirm=''
	saved.value=true
	setTimeout(()=>saved.value=false, 2000)
}

const changeLanguage = (lang) => {
	setLocale(lang)
	currentLang.value = lang
}

onMounted(load)
</script>

<template>
	<div class="panel">
		<div class="line">
			<h3>{{ t('settings') }}</h3>
			<div v-if="saved" class="note">{{ t('saved') }}</div>
		</div>

		<div class="settings-grid">
			<!-- Language Selection -->
			<div class="card">
				<h4>{{ t('language') }}</h4>
				<div class="language-options">
					<label class="lang-option" :class="{ active: state.locale === 'en' }">
						<input type="radio" name="language" value="en" :checked="state.locale === 'en'" @change="changeLanguage('en')" />
						<span class="lang-flag">🇬🇧</span>
						<span>{{ t('english') }}</span>
					</label>
					<label class="lang-option" :class="{ active: state.locale === 'kh' }">
						<input type="radio" name="language" value="kh" :checked="state.locale === 'kh'" @change="changeLanguage('kh')" />
						<span class="lang-flag">🇰🇭</span>
						<span>{{ t('khmer') }}</span>
					</label>
				</div>
			</div>

			<!-- Profile -->
			<div class="card">
				<h4>{{ t('profile') }}</h4>
				<div class="form">
					<label>{{ t('name') }}<input v-model="profile.name" /></label>
					<label>{{ t('email') }}<input v-model="profile.email" /></label>
					<label>{{ t('phone') }}<input v-model="profile.phone" /></label>
				</div>
				<div class="line"><button class="primary" @click="saveProfile">{{ t('saveProfile') }}</button></div>
			</div>

			<!-- Change Password -->
			<div class="card">
				<h4>{{ t('changePassword') }}</h4>
				<div class="form">
					<label>{{ t('currentPassword') }}<input type="password" v-model="password.current" /></label>
					<label>{{ t('newPassword') }}<input type="password" v-model="password.newPass" /></label>
					<label>{{ t('confirmPassword') }}<input type="password" v-model="password.confirm" /></label>
				</div>
				<div class="line"><button class="primary" @click="changePassword">{{ t('changePasswordBtn') }}</button></div>
			</div>

			<!-- Notifications -->
			<div class="card" style="grid-column:1/-1">
				<h4>{{ t('notifications') }}</h4>
				<label style="display:flex;align-items:center;gap:8px">
					<input type="checkbox" v-model="notifications.email" />
					{{ t('emailNotifications') }}
				</label>
				<label style="display:flex;align-items:center;gap:8px">
					<input type="checkbox" v-model="notifications.sms" />
					{{ t('smsNotifications') }}
				</label>
			</div>
		</div>
	</div>
</template>

<style scoped>
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px }
.line { display:flex; justify-content:space-between; align-items:center }
.settings-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px }
.card { background:#f8fafc; border:1px solid #e6eef8; border-radius:10px; padding:12px }
.form { display:grid; gap:10px; margin:10px 0 }
label { display:flex; flex-direction:column }
input { padding:8px; border:1px solid #cbd5e1; border-radius:8px }
.primary { background:#1d4ed8; color:#fff; border:0; padding:8px 12px; border-radius:8px; cursor:pointer }
.note { color:green }

/* Language Options */
.language-options { display:flex; gap:12px; margin-top:10px }
.lang-option {
	display:flex;
	align-items:center;
	gap:8px;
	padding:10px 16px;
	border:2px solid #e2e8f0;
	border-radius:8px;
	cursor:pointer;
	transition:all 0.2s;
}
.lang-option:hover { border-color:#1d4ed8 }
.lang-option.active { border-color:#1d4ed8; background:#eff6ff }
.lang-option input { display:none }
.lang-flag { font-size:20px }

@media(max-width:800px) { .settings-grid { grid-template-columns:1fr } }
</style>
