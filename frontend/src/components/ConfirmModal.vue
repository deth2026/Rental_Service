<script setup>
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Confirm',
  },
  message: {
    type: String,
    default: 'Are you sure?',
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
    default: 'primary',
  },
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const close = () => emit('update:modelValue', false)
const confirm = () => {
  emit('confirm')
  close()
}
</script>

<template>
  <div v-if="modelValue" class="confirm-overlay" role="dialog" aria-modal="true">
    <div class="confirm-card">
      <header class="confirm-header">
        <div>
          <p class="confirm-title">{{ title }}</p>
          <p class="confirm-message">{{ message }}</p>
        </div>
        <button type="button" class="confirm-close" aria-label="Close" @click="close">
          <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
      </header>
      <footer class="confirm-actions">
        <button type="button" class="btn btn-ghost" @click="close">{{ cancelText }}</button>
        <button
          type="button"
          class="btn btn-primary"
          :class="{ 'btn-danger': variant === 'danger' }"
          @click="confirm"
        >
          {{ confirmText }}
        </button>
      </footer>
    </div>
  </div>
</template>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 60;
}

.confirm-card {
  width: min(420px, 100%);
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.2);
}

.confirm-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}

.confirm-title {
  font-weight: 600;
  margin: 0;
}

.confirm-message {
  margin: 0.4rem 0 0;
  font-size: 0.95rem;
  color: #475467;
}

.confirm-close {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: #0f172a;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: 1.25rem;
}

.btn-danger {
  background-color: #dc2626;
  border-color: #dc2626;
}
</style>
