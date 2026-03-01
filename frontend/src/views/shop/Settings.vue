<script setup>
import { reactive, ref, onMounted } from 'vue'

const profileKey = 'shopProfile'
const profile = reactive({ name: 'Chong Choul', email: 'chong@example.com', phone: '+85512345678' })
const password = reactive({ current: '', newPass: '', confirm: '' })
const notifications = reactive({ email: true, sms: false })
const saved = ref(false)

const load = () => {
	try {
		const raw = localStorage.getItem(profileKey)
		if (raw) Object.assign(profile, JSON.parse(raw))
	} catch (e) {}
}
const saveProfile = () => { localStorage.setItem(profileKey, JSON.stringify(profile)); saved.value = true; setTimeout(()=>saved.value=false,2000) }
const changePassword = () => { if (!password.current || !password.newPass || password.newPass !== password.confirm) return; password.current=''; password.newPass=''; password.confirm=''; saved.value=true; setTimeout(()=>saved.value=false,2000) }
onMounted(load)
</script>

<template>
	<div class="panel">
		<div class="line"><h3>Settings</h3><div v-if="saved" class="note">Saved</div></div>

		<div class="settings-grid">
			<div class="card">
				<h4>Profile</h4>
				<div class="form">
					<label>Name<input v-model="profile.name" /></label>
					<label>Email<input v-model="profile.email" /></label>
					<label>Phone<input v-model="profile.phone" /></label>
				</div>
				<div class="line"><button class="primary" @click="saveProfile">Save Profile</button></div>
			</div>

			<div class="card">
				<h4>Change Password</h4>
				<div class="form">
					<label>Current Password<input type="password" v-model="password.current" /></label>
					<label>New Password<input type="password" v-model="password.newPass" /></label>
					<label>Confirm Password<input type="password" v-model="password.confirm" /></label>
				</div>
				<div class="line"><button class="primary" @click="changePassword">Change Password</button></div>
			</div>

			<div class="card" style="grid-column:1/-1">
				<h4>Notifications</h4>
				<label style="display:flex;align-items:center;gap:8px"><input type="checkbox" v-model="notifications.email" /> Email notifications</label>
				<label style="display:flex;align-items:center;gap:8px"><input type="checkbox" v-model="notifications.sms" /> SMS notifications</label>
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
@media(max-width:800px) { .settings-grid { grid-template-columns:1fr } }
</style>

