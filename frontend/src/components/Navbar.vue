<template>
  <header class="navbar">
    <RouterLink to="/home">Home</RouterLink>
    <RouterLink to="/login">Login</RouterLink>
    <RouterLink to="/register">Register</RouterLink>
    <RouterLink to="/dashboard">Dashboard</RouterLink>
    <RouterLink to="/setting">Setting</RouterLink>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

<style scoped>
.navbar {
  display: flex;
  gap: 1rem;
  padding: 1rem;
}
</style>
