<script setup>
import { computed } from 'vue'

const props = defineProps({
  navItems: {
    type: Array,
    default: () => []
  },
  activeNav: {
    type: String,
    default: ''
  },
  userDisplayName: {
    type: String,
    default: 'Guest User'
  },
  brandLabel: {
    type: String,
    default: 'Chong Choul'
  },
  brandIcon: {
    type: String,
    default: 'fa-solid fa-gift'
  },
  showBackButton: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['nav-select', 'settings', 'logout', 'back'])

const validNavItems = computed(() => props.navItems.filter((item) => Boolean(item)))

const normalizeLabel = (item) => {
  if (!item) return ''
  if (typeof item === 'string') return item
  return item.label || ''
}

const handleNavClick = (item) => {
  emit('nav-select', item)
}

const handleSettings = () => emit('settings')
const handleLogout = () => emit('logout')
const handleBack = () => emit('back')
</script>

<template>
  <header class="topbar">
    <div class="brand">
      <button
        v-if="showBackButton"
        type="button"
        class="btn-reset back-btn"
        @click="handleBack"
        aria-label="Go back"
      >
        <i class="fa-solid fa-arrow-left"></i>
      </button>
      <div class="brand-icon">
        <i :class="brandIcon" aria-hidden="true"></i>
      </div>
      <span>{{ brandLabel }}</span>
    </div>

    <nav v-if="validNavItems.length" class="nav-links">
      <button
        v-for="(item, index) in validNavItems"
        :key="normalizeLabel(item) || index"
        type="button"
        class="btn-reset nav-link"
        :class="{ active: activeNav === normalizeLabel(item) }"
        @click="handleNavClick(item)"
      >
        {{ normalizeLabel(item) }}
      </button>
    </nav>

    <div class="top-actions">
      <span class="user-display-name">{{ userDisplayName }}</span>
      <button type="button" class="settings-btn" @click="handleSettings" aria-label="Settings">
        <i class="fa-solid fa-gear"></i>
      </button>
      <button type="button" class="logout-btn" @click="handleLogout" aria-label="Logout">
        <i class="fa-solid fa-right-from-bracket"></i>
      </button>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  position: sticky;
  top: 0;
  z-index: 150;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  width: 100%;
  padding: 12px 24px;
  background: #ffffff;
  border-bottom: 1px solid rgba(37, 99, 235, 0.08);
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 20px;
  font-weight: 700;
  color: #2563eb;
  white-space: nowrap;
}

.brand-icon {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: #dbeafe;
}

.brand-icon i {
  color: #2563eb;
}

.nav-links {
  display: flex;
  gap: 10px;
  justify-self: center;
  background: #eef2ff;
  padding: 6px 12px;
  border-radius: 999px;
  box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.1);
}

.nav-link {
  padding: 6px 18px;
  border-radius: 999px;
  color: #4b5563;
  font-size: 13px;
  font-weight: 700;
  transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
  background: transparent;
}

.nav-link.active {
  background: #2563eb;
  color: #ffffff;
  box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
  transform: translateY(-1px);
}

.nav-link:hover {
  background: rgba(37, 99, 235, 0.12);
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-self: end;
  white-space: nowrap;
}

.user-display-name {
  font-size: 16px;
  font-weight: 800;
  color: #1f2937;
}

.back-btn {
  width: 34px;
  height: 34px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  background: #dbeafe;
  color: #2563eb;
}

.back-btn i {
  font-size: 0.85rem;
}

.btn-reset {
  border: none;
  background: transparent;
  font: inherit;
  color: inherit;
  cursor: pointer;
  padding: 0;
}

.settings-btn, .logout-btn {
  padding: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 16px;
}

@media (max-width: 1080px) {
  .topbar {
    grid-template-columns: 1fr;
    padding: 12px;
    gap: 12px;
  }

  .nav-links,
  .top-actions {
    justify-self: start;
    flex-wrap: wrap;
  }
}

@media (max-width: 640px) {
  .topbar {
    width: calc(100% + 24px);
    margin-left: -12px;
    margin-right: -12px;
  }

  .nav-links {
    flex-wrap: wrap;
  }
}
</style>
