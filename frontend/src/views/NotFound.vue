<script setup>
import { RouterLink } from 'vue-router'
import Logo from '@/components/Logo.vue'
</script>

<template>
  <main class="notfound">
    <header class="notfound__head">
      <Logo class="notfound__logo" theme="light" size="md" :showTagline="true" />
    </header>

    <section class="notfound__card" role="status" aria-live="polite">
      <h1>Page not found</h1>
      <p>The URL you entered doesn't match any page in this app.</p>
      <div class="notfound__actions">
        <RouterLink class="btn primary" to="/">Go Home</RouterLink>
        <RouterLink class="btn" to="/login">Login</RouterLink>
      </div>
    </section>
  </main>
</template>

<style scoped>
.notfound {
  min-height: 100vh;
  padding: 32px 18px;
  background: #f2f4f7;
  color: #0f172a;
  display: grid;
  place-items: center;
  gap: 18px;
}

.notfound__head {
  width: min(920px, 100%);
  display: flex;
  justify-content: center;
}

.notfound__card {
  width: min(920px, 100%);
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  padding: 28px 22px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.notfound__card h1 {
  margin: 0 0 6px;
  font-size: 1.8rem;
  letter-spacing: -0.02em;
}

.notfound__card p {
  margin: 0;
  color: #475569;
  line-height: 1.6;
}

.notfound__actions {
  margin-top: 16px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 14px;
  border-radius: 14px;
  text-decoration: none;
  font-weight: 650;
  color: #0f172a;
  background: #e2e8f0;
  border: 1px solid rgba(15, 23, 42, 0.08);
}

.btn.primary {
  background: linear-gradient(135deg, #39a9e1, #2563eb);
  border: 0;
  color: #ffffff;
}
</style>

