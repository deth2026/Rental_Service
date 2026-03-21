<template>
  <span :class="logoClass" v-bind="attrs">
    <span class="app-logo__mark" aria-hidden="true">CC</span>
    <span class="app-logo__img">
      <span class="app-logo__name">Chong Choul</span>
      <small v-if="showTagline" class="app-logo__tagline">Vehicle Rentals</small>
    </span>
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { useAttrs } from 'vue'

const props = defineProps({
  theme: { type: String, default: 'light' },
  size: { type: String, default: 'md' },
  showTagline: { type: Boolean, default: false }
})

const attrs = useAttrs()

const logoClass = computed(() => [
  'app-logo',
  `app-logo--${props.theme}`,
  `app-logo--${props.size}`,
  attrs.class
].filter(Boolean))
</script>

<style scoped>
.app-logo {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  text-transform: none;
  font-family: inherit;
}

.app-logo__mark {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  background: linear-gradient(135deg, #39A9E1, #2563eb);
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.1rem;
  letter-spacing: -0.03em;
}

.app-logo__img {
  display: flex;
  flex-direction: column;
  line-height: 1.1;
}

.app-logo__name {
  font-weight: 850;
  letter-spacing: -0.02em;
  color: #0b2b73;
}

.app-logo__tagline {
  font-size: 0.65rem;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: #6b7280;
}

.app-logo--dark .app-logo__mark {
  background: #101828;
}

.app-logo--lg .app-logo__mark {
  width: 72px;
  height: 72px;
  font-size: 1.3rem;
}

.app-logo--lg .app-logo__name {
  font-size: 1.3rem;
}
</style>
