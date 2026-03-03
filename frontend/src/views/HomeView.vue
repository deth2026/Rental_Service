<template>
  <main class="home">
    <h1>Rental Service</h1>
    <p>Frontend is running successfully.</p>
    <div class="links">
      <router-link to="/login">Login</router-link>
      <router-link to="/register">Register</router-link>
      <router-link to="/dashboard">User Dashboard</router-link>
      <router-link to="/admin/dashboard">Admin Dashboard</router-link>
    </div>
  </main>
</template>

<style scoped>
.home {
  min-height: 100vh;
  display: grid;
  place-content: center;
  gap: 12px;
  text-align: center;
}

.links {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

.links a {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  text-decoration: none;
  color: #222;
}
</style>
