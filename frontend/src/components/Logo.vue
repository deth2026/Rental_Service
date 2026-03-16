<template>
  <RouterLink
    :to="to"
    class="app-logo"
    :class="[`app-logo--${theme}`, `app-logo--${size}`]"
    aria-label="Chong Choul"
  >
    <span class="app-logo__text">
      <span class="app-logo__name" :class="{ 'app-logo__name--stacked': stacked }">
        <span class="brand-main">CHONG</span>
        <span class="brand-accent">CHOUL</span>
      </span>
      <span v-if="showTagline" class="app-logo__tagline">{{ tagline }}</span>
    </span>
    <span class="app-logo__mark" aria-hidden="true">
      <img class="app-logo__img" :src="logoUrl" alt="" />
    </span>
  </RouterLink>
</template>

<script setup>
import logoUrl from '@/assets/logo-removebg.png'

defineProps({
  to: {
    type: [String, Object],
    default: '/'
  },
  theme: {
    type: String,
    default: 'light' // light | dark
  },
  size: {
    type: String,
    default: 'md' // sm | md | lg
  },
  stacked: {
    type: Boolean,
    default: false
  },
  showTagline: {
    type: Boolean,
    default: true
  },
  tagline: {
    type: String,
    default: 'Vehicle Services'
  }
})
</script>

<style scoped>
.app-logo {
  --accent: #39a9e1;
  display: inline-flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  user-select: none;
}

.app-logo__mark {
  flex: 0 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: 0;
  border-radius: 14px;
}

.app-logo__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  filter: drop-shadow(0 10px 22px rgba(2, 6, 23, 0.18));
}

.app-logo__text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  line-height: 1.05;
}

.app-logo__name {
  display: inline-flex;
  gap: 10px;
  align-items: baseline;
  font-weight: 900;
  letter-spacing: -0.03em;
}

.app-logo__name--stacked {
  flex-direction: column;
  align-items: flex-start;
  gap: 2px;
  line-height: 1.05;
}

.app-logo__name--stacked .brand-main,
.app-logo__name--stacked .brand-accent {
  display: block;
}

.brand-accent {
  background: linear-gradient(90deg, var(--accent), #2563eb);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.app-logo__tagline {
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  font-size: 0.72rem;
  opacity: 0.88;
}

.app-logo--light .brand-main {
  color: #0b2b73;
}

.app-logo--light .app-logo__tagline {
  color: #6b7280;
}

.app-logo--dark .brand-main {
  color: #ffffff;
}

.app-logo--dark .brand-accent {
  background: linear-gradient(90deg, #7dd3fc, var(--accent), #60a5fa);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.app-logo--dark .app-logo__tagline {
  color: rgba(148, 163, 184, 0.95);
}

.app-logo--sm .app-logo__mark {
  width: 44px;
  height: 44px;
}

.app-logo--sm .app-logo__name {
  font-size: 1.1rem;
}

.app-logo--md .app-logo__mark {
  width: 56px;
  height: 56px;
}

.app-logo--md .app-logo__name {
  font-size: 1.35rem;
}

.app-logo--lg .app-logo__mark {
  width: 82px;
  height: 82px;
}

.app-logo--lg .app-logo__name {
  font-size: 1.8rem;
}
</style>
