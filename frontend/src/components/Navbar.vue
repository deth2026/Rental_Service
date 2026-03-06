<template>
  <header class="navbar">
    <nav class="nav-links">
      <RouterLink to="/" class="nav-link">{{ t('home') }}</RouterLink>
      <RouterLink to="/login" class="nav-link">{{ t('login') }}</RouterLink>
      <RouterLink to="/register" class="nav-link">{{ t('register') }}</RouterLink>
      <RouterLink to="/dashboard" class="nav-link">{{ t('dashboard') }}</RouterLink>
      <RouterLink to="/setting" class="nav-link setting-link">{{ t('setting') }}</RouterLink>
    </nav>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-links {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.nav-link {
  color: #fff;
  text-decoration: none;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.3);
  font-weight: 600;
}

.setting-link {
  background: #f59e0b;
  color: #fff;
  font-weight: 600;
}

.setting-link:hover {
  background: #d97706;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.setting-link.router-link-active {
  background: #d97706;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
