<template>
  <div v-if="modelValue" class="confirm-backdrop" role="dialog" aria-modal="true" @click.self="onCancel">
    <div class="confirm-card" :class="`confirm-card--${variant}`">
      <div class="confirm-icon" aria-hidden="true">
        <div class="confirm-icon__circle">
          <i class="fa-solid fa-trash"></i>
        </div>
      </div>
      <h3 class="confirm-title">{{ title }}</h3>
      <p class="confirm-message">{{ message }}</p>
      <span v-if="subtitle" class="confirm-pill">{{ subtitle }}</span>
      <p class="confirm-note">{{ friendlyNote }}</p>
      <div class="confirm-actions">
        <button type="button" class="confirm-btn confirm-btn--ghost" @click="onCancel">{{ cancelText }}</button>
        <button type="button" class="confirm-btn confirm-btn--danger" @click="onConfirm">{{ confirmText }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm',
  },
  subtitle: {
    type: String,
    default: '',
  },
  helperText: {
    type: String,
    default: 'This action cannot be undone; all related data will be permanently removed.',
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?',
  },
  cancelText: {
    type: String,
    default: 'Cancel',
  },
  confirmText: {
    type: String,
    default: 'Confirm',
  },
  variant: {
    type: String,
    default: 'danger',
  },
})

const friendlyNote = computed(() => props.helperText)

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const onCancel = () => {
  emit('update:modelValue', false)
  emit('cancel')
}

const onConfirm = () => {
  emit('update:modelValue', false)
  emit('confirm')
}
</script>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 24px 16px;
}

.confirm-card {
  width: min(92vw, 420px);
  background: #ffffff;
  border-radius: 32px;
  padding: 32px 28px;
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  text-align: center;
}

.confirm-icon__circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: #fee2e2;
  border: 1px solid rgba(220, 38, 38, 0.4);
  display: grid;
  place-items: center;
  color: #dc2626;
  font-size: 32px;
}

.confirm-title {
  font-size: 1.4rem;
  margin: 0;
  font-weight: 700;
  color: #0f172a;
}

.confirm-message {
  margin: 0;
  color: #475569;
  font-size: 1rem;
  line-height: 1.5;
}

.confirm-pill {
  border-radius: 999px;
  padding: 4px 16px;
  background: #fee2e2;
  color: #dc2626;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.confirm-note {
  margin: 0;
  color: #64748b;
  font-size: 0.85rem;
  line-height: 1.4;
}

.confirm-actions {
  width: 100%;
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 12px;
}

.confirm-btn {
  border-radius: 999px;
  min-height: 44px;
  padding: 0 28px;
  font-weight: 600;
  font-size: 0.95rem;
  border: 1px solid transparent;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.confirm-btn--ghost {
  background: #f8fafc;
  color: #0f172a;
  border-color: #e2e8f0;
}

.confirm-btn--ghost:hover {
  transform: translateY(-1px);
}

.confirm-btn--danger {
  background: #dc2626;
  color: #fff;
  border-color: #dc2626;
  box-shadow: 0 10px 30px rgba(220, 38, 38, 0.35);
}

.confirm-btn--danger:hover {
  transform: translateY(-1px);
}

@media (max-width: 480px) {
  .confirm-card {
    padding: 24px 20px;
  }
}
</style>
