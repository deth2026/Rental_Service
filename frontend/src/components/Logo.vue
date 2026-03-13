<template>
  <img class="app-logo" :src="logoUrl" alt="Chong Choul Logo" />
</template>

<script setup>
import logoUrl from '@/assets/Logo.png'
</script>

<style scoped>
.app-logo {
  height: 40px;
  width: auto;
  max-width: 100%;
  padding: 6px 10px;
  object-fit: contain;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  display: inline-block;
  text-decoration: none;
}
</style>
