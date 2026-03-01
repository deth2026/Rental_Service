<script setup>
import { reactive, ref, onMounted } from 'vue'

const storageKey = 'shopDetails'

const khTime = () => {
	const p = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Phnom_Penh', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).formatToParts(new Date()).map(x => [x.type, x.value]))
	return `${p.year}-${p.month}-${p.day} ${p.hour}:${p.minute}:${p.second}`
}

const form = reactive({
	name: 'Chong Choul',
	shopName: 'Chong Choul',
	location: 'Phnom Penh',
	address: 'Russian Market, Phnom Penh',
	phone: '+855 12 345 678',
	email: 'chong@example.com',
	status: 'active',
	description: 'Vehicle rental shop in Phnom Penh',
	updatedAt: ''
})

const saved = ref(false)

const load = () => {
	try {
		const raw = localStorage.getItem(storageKey)
		if (raw) {
			const obj = JSON.parse(raw)
			Object.assign(form, obj)
		}
	} catch (e) {
		// ignore
	}
}

const save = () => {
	form.updatedAt = khTime()
	localStorage.setItem(storageKey, JSON.stringify(form))
	saved.value = true
	setTimeout(() => saved.value = false, 2000)
}

onMounted(load)
</script>

<template>
	<div class="panel">
		<div class="line">
			<h3>My Shop</h3>
			<div>
				<button class="primary" @click="save">Save Changes</button>
			</div>
		</div>

		<div class="shop-grid">
			<div class="card">
				<div class="logo large">CC</div>
				<h4>{{ form.shopName }}</h4>
				<p class="muted">{{ form.location }}</p>
				<p>{{ form.description }}</p>
				<p class="muted">Phone: {{ form.phone }} · Email: {{ form.email }}</p>
				<p class="muted">Status: <strong>{{ form.status }}</strong></p>
				<p v-if="form.updatedAt" class="muted">Updated at: {{ form.updatedAt }}</p>
			</div>

			<div class="form">
				<label>Shop Display Name<input v-model="form.shopName" /></label>
				<label>Owner Name<input v-model="form.name" /></label>
				<label>Location<select v-model="form.location"><option>Phnom Penh</option><option>Siem Reap</option><option>Battambang</option></select></label>
				<label>Address<textarea v-model="form.address"></textarea></label>
				<label>Phone<input v-model="form.phone" /></label>
				<label>Email<input v-model="form.email" /></label>
				<label>Status<select v-model="form.status"><option>active</option><option>inactive</option></select></label>
				<label>Description<textarea v-model="form.description"></textarea></label>
			</div>
		</div>

		<div v-if="saved" class="note">Saved</div>
	</div>
</template>

<style scoped>
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px }
.line { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px }
.shop-grid { display:grid; grid-template-columns:280px 1fr; gap:12px }
.card { background:#f8fafc; border:1px solid #e6eef8; border-radius:12px; padding:14px; text-align:center }
.logo.large { width:72px; height:72px; border-radius:12px; background:#2f6bff; color:#fff; display:grid; place-items:center; font-weight:700; font-size:20px; margin:0 auto 10px }
.muted { color:#64748b; font-size:13px }
.form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px }
label { display:flex; flex-direction:column; gap:6px }
input, select, textarea { padding:8px; border:1px solid #cbd5e1; border-radius:8px }
textarea { min-height:80px }
.primary { background:#1d4ed8; color:#fff; border:0; padding:8px 12px; border-radius:8px; cursor:pointer }
.note { margin-top:12px; color:green }
@media(max-width:900px) { .shop-grid { grid-template-columns:1fr } .form { grid-template-columns:1fr } }
</style>

