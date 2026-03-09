<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const confirmLogout = ref(false)

const doLogout = () => {
	// clear auth tokens / session here if integrated
	localStorage.removeItem('authToken')
	router.push('/')
}
</script>

<template>
	<div class="panel" style="max-width:480px; margin:20px auto; text-align:center">
		<h3>Logout</h3>
		<p>Are you sure you want to logout from the shop owner account?</p>
		<div style="display:flex; gap:10px; justify-content:center; margin-top:12px">
			<button class="danger" @click="doLogout">Logout</button>
			<button @click="$router.back()">Cancel</button>
		</div>
	</div>
</template>

<style scoped>
.danger {
	background: #ef4444;
	color: #fff;
	border: 0;
	padding: 8px 12px;
	border-radius: 8px;
	cursor: pointer
}

button {
	border-radius: 8px;
	padding: 8px 12px;
	border: 0;
	cursor: pointer
}
</style>
