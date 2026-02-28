<template>
  <main class="home">
    <h1>Welcome</h1>
    <p>Choose an option to continue.</p>
    <div class="actions">
      <RouterLink to="/login">Login</RouterLink>
      <RouterLink to="/register">Register</RouterLink>
    </div>
  </main>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

<style scoped>
.home {
  min-height: 100vh;
  display: grid;
  place-content: center;
  gap: 0.75rem;
  text-align: center;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}
</style>
