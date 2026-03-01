<script setup>
import { ref } from 'vue'

const customers = ref([
	{ id: 1, name: 'Sokha', points: 120, perBooking: 15 },
	{ id: 2, name: 'Dara', points: 90, perBooking: 10 }
])

const modal = ref(false)
const form = ref({ id: null, name: '', points: 0, perBooking: 0 })

const openCreate = () => { form.value = { id: null, name: '', points: 0, perBooking: 0 }; modal.value = true }
const openEdit = (c) => { form.value = { id: c.id, name: c.name, points: c.points, perBooking: c.perBooking }; modal.value = true }
const save = () => {
	if (!form.value.name) return
	if (form.value.id) {
		const i = customers.value.findIndex(x => x.id === form.value.id)
		if (i >= 0) customers.value[i] = { ...customers.value[i], name: form.value.name, points: Number(form.value.points), perBooking: Number(form.value.perBooking) }
	} else {
		customers.value.unshift({ id: Date.now(), name: form.value.name, points: Number(form.value.points), perBooking: Number(form.value.perBooking) })
	}
	modal.value = false
}
const remove = (id) => { customers.value = customers.value.filter(c => c.id !== id) }
const addPoints = (c, n) => { c.points = Number(c.points) + Number(n) }
const deductPoints = (c, n) => { c.points = Math.max(0, Number(c.points) - Number(n)) }
</script>

<template>
	<div class="panel">
		<div class="line">
			<h3>Loyalty Points</h3>
			<button class="primary" @click="openCreate">Add Customer</button>
		</div>

		<div class="table">
			<table>
				<thead>
					<tr>
						<th>Customer</th>
						<th>Points</th>
						<th>Points / Booking</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="c in customers" :key="c.id">
						<td>{{ c.name }}</td>
						<td>{{ c.points }}</td>
						<td>{{ c.perBooking }}</td>
						<td>
							<button @click="openEdit(c)">Edit</button>
							<button @click="addPoints(c, c.perBooking)">+ Earn</button>
							<button @click="deductPoints(c, c.perBooking)" class="danger">- Redeem</button>
							<button class="danger" @click="remove(c.id)">Delete</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div v-if="modal" class="overlay">
			<div class="modal">
				<div class="line">
					<h3>{{ form.id ? 'Edit Customer' : 'Add Customer' }}</h3>
					<button @click="modal = false">Close</button>
				</div>
				<div class="form">
					<label>Name<input v-model="form.name" /></label>
					<label>Points<input v-model.number="form.points" type="number" /></label>
					<label>Points / Booking<input v-model.number="form.perBooking" type="number" /></label>
				</div>
				<div class="line"><button @click="modal = false">Discard</button><button class="primary" @click="save">Save</button></div>
			</div>
		</div>
	</div>
</template>

<style scoped>
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px }
.line { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px }
.table { overflow:auto; border:1px solid #e2e8f0; border-radius:10px }
table { width:100%; min-width:600px; border-collapse:collapse }
th, td { padding:10px; border-bottom:1px solid #e2e8f0; text-align:left }
th { font-size:12px; background:#f8fafc; text-transform:uppercase; color:#475569 }
.primary { background:#1d4ed8; color:#fff; border:0; padding:8px 10px; border-radius:8px; cursor:pointer }
.danger { background:#ef4444; color:#fff; border:0; padding:8px 10px; border-radius:8px; cursor:pointer }
.overlay { position:fixed; inset:0; background:rgba(2,6,23,.6); display:grid; place-items:center; padding:12px }
.modal { width:min(700px,100%); max-height:92vh; overflow:auto; background:#fff; border-radius:12px; padding:12px }
.form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin:10px 0 }
input[type="number"] { padding:8px }
</style>

