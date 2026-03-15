<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: 'Confirm' },
  message: { type: String, default: '' },
  confirmText: { type: String, default: 'Yes' },
  cancelText: { type: String, default: 'Cancel' },
  variant: { type: String, default: 'primary' }, // primary | danger
})

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

const confirmClass = computed(() => (props.variant === 'danger' ? 'btn-danger' : 'btn-primary'))

const close = () => emit('update:modelValue', false)

const onCancel = () => {
  emit('cancel')
  close()
}

const onConfirm = () => {
  emit('confirm')
  close()
}

const onKeydown = (e) => {
  if (!props.modelValue) return
  if (e.key === 'Escape') onCancel()
}

onMounted(() => window.addEventListener('keydown', onKeydown))
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <div v-if="modelValue" class="confirm-backdrop" role="dialog" aria-modal="true" @click.self="onCancel">
      <div class="confirm-modal">
        <div class="confirm-head">
          <div class="confirm-title">{{ title }}</div>
          <button type="button" class="confirm-close" aria-label="Close" @click="onCancel">×</button>
        </div>
        <div class="confirm-message">{{ message }}</div>
        <div class="confirm-actions">
          <button type="button" class="btn btn-muted" @click="onCancel">{{ cancelText }}</button>
          <button type="button" class="btn" :class="confirmClass" @click="onConfirm">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.55);
  display: grid;
  place-items: center;
  z-index: 9999;
  padding: 18px;
}

.confirm-modal {
  width: min(520px, 100%);
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid rgba(15, 23, 42, 0.12);
  box-shadow: 0 18px 45px rgba(2, 6, 23, 0.25);
  padding: 16px;
}

.confirm-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.confirm-title {
  font-weight: 800;
  font-size: 18px;
  letter-spacing: -0.2px;
  color: #0f172a;
}

.confirm-close {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: transparent;
  cursor: pointer;
  color: rgba(15, 23, 42, 0.7);
  font-size: 20px;
  line-height: 1;
  display: grid;
  place-items: center;
}

.confirm-close:hover {
  background: rgba(148, 163, 184, 0.18);
}

.confirm-message {
  margin-top: 10px;
  color: rgba(15, 23, 42, 0.75);
  line-height: 1.5;
  font-size: 14px;
}

.confirm-actions {
  margin-top: 16px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.btn {
  border-radius: 12px;
  padding: 10px 14px;
  border: 1px solid rgba(15, 23, 42, 0.12);
  background: #ffffff;
  cursor: pointer;
  font-weight: 700;
}

.btn:hover {
  border-color: rgba(14, 165, 233, 0.35);
}

.btn-muted {
  color: rgba(15, 23, 42, 0.75);
  background: rgba(148, 163, 184, 0.12);
  border-color: rgba(148, 163, 184, 0.16);
}

.btn-primary {
  color: #ffffff;
  background: #2563eb;
  border-color: rgba(37, 99, 235, 0.6);
}

.btn-primary:hover {
  background: #1d4ed8;
}

.btn-danger {
  color: #ffffff;
  background: #dc2626;
  border-color: rgba(220, 38, 38, 0.6);
}

.btn-danger:hover {
  background: #b91c1c;
}

@media (prefers-color-scheme: dark) {
  .confirm-modal {
    background: #0b1220;
    border-color: rgba(148, 163, 184, 0.18);
  }
  .confirm-title {
    color: #e5e7eb;
  }
  .confirm-message {
    color: rgba(226, 232, 240, 0.78);
  }
  .confirm-close {
    color: rgba(226, 232, 240, 0.8);
    border-color: rgba(148, 163, 184, 0.2);
  }
  .btn {
    border-color: rgba(148, 163, 184, 0.2);
    background: rgba(148, 163, 184, 0.08);
    color: #e2e8f0;
  }
  .btn-muted {
    background: rgba(148, 163, 184, 0.12);
  }
}
</style>

