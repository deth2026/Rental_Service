<script setup>
import { useToast } from '../composables/useToast'

const { toasts, removeToast } = useToast()
</script>

<template>
  <div class="toast-host" aria-live="polite" aria-relevant="additions removals">
    <div v-for="t in toasts" :key="t.id" class="toast" :class="t.type" role="status">
      <div class="toast-message">{{ t.message }}</div>
      <button type="button" class="toast-close" aria-label="Dismiss" @click="removeToast(t.id)">×</button>
    </div>
  </div>
</template>

<style scoped>
.toast-host {
  position: fixed;
  right: 18px;
  bottom: 18px;
  /* z-index: 99999; */
  display: grid;
  gap: 10px;
  pointer-events: none;
}

.toast {
  width: min(460px, calc(100vw - 36px));
  border-radius: 8px;
  padding: 10px 16px;
  /* background: #f8fafc; */
  /* color: #0f172a; */
  border: 1px solid rgba(15, 23, 42, 0.12);
  box-shadow: 0 18px 40px rgba(2, 6, 23, 0.18);
  display: grid;
  grid-template-columns: minmax(80, 1fr) auto;
  height: 50px;
  gap: 10px;
  align-items: center;
  pointer-events: auto;
  position: relative;
  overflow: hidden;
}

.toast::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 8px;
  background: rgba(34, 211, 238, 0.85);
}

.toast-message {
  font-size: 16px;
  line-height: 2;
  font-weight: 600;
  overflow-wrap: anywhere;
}

/* .toast-close {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: rgba(15, 23, 42, 0.03);
  color: rgba(15, 23, 42, 0.9);
  cursor: pointer;
  line-height: 1;
  display: grid;
  place-items: center;
  font-size: 18px;
} */

.toast.success {
  border-color: rgba(34, 197, 94, 0.32);
  /* background: rgba(34, 197, 94, 0.12); */
  color: #14532d;
}

.toast.success::before {
  background: rgba(34, 197, 94, 0.9);
}

.toast.error {
  border-color: rgba(239, 68, 68, 0.35);
  background: rgba(239, 68, 68, 0.10);
  color: #7f1d1d;
}

.toast.error::before {
  background: rgba(239, 68, 68, 0.9);
}

.toast.info {
  border-color: rgba(34, 211, 238, 0.35);
  background: rgba(34, 211, 238, 0.10);
  /* color: #0f172a; */
}

.toast.info::before {
  background: rgba(34, 211, 238, 0.9);
}

@media (prefers-reduced-motion: no-preference) {
  .toast {
    animation: toast-in 160ms ease-out;
  }
  @keyframes toast-in {
    from {
      transform: translateY(8px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }
}
</style>
