<script setup>
import { computed } from 'vue'
import ToastHost from './components/ToastHost.vue'

const getUserRole = () => {
  try {
    const raw = localStorage.getItem('user')
    if (!raw) return null
    const parsed = JSON.parse(raw)
    return parsed?.role || null
  } catch {
    return null
  }
}
</script>

<template>
  <div id="app">
    <router-view />
    <ToastHost />
  </div>
</template>

<style>
html,
body,
#app {
  width: 100%;
  max-width: 100%;
  margin: 0;
  overflow-x: hidden;
}

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  box-sizing: border-box;
}

img,
video,
canvas,
svg {
  max-width: 100%;
}

input,
select,
textarea,
button {
  font: inherit;
}
</style>
