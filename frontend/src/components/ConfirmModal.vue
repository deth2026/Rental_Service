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
  gap: 10px;
  margin-bottom: 10px;
}

.confirm-title {
  font-weight: 850;
  color: #0f172a;
  letter-spacing: -0.01em;
}

.confirm-close {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  border: 1px solid rgba(15, 23, 42, 0.08);
  background: rgba(2, 6, 23, 0.02);
  color: rgba(15, 23, 42, 0.72);
  cursor: pointer;
  font-size: 20px;
  line-height: 1;
  transition: background 160ms ease, color 160ms ease, transform 160ms ease;
}

.confirm-close:hover {
  background: rgba(2, 6, 23, 0.05);
  color: #0f172a;
  transform: translateY(-1px);
}

.confirm-message {
  color: rgba(15, 23, 42, 0.8);
  line-height: 1.6;
  margin: 10px 0 14px;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 12px;
}

.btn {
  border: 0;
  border-radius: 12px;
  padding: 10px 14px;
  font-weight: 800;
  cursor: pointer;
  transition: transform 160ms ease, filter 160ms ease, box-shadow 160ms ease;
}

.btn:active {
  transform: translateY(1px);
}

.btn-muted {
  background: rgba(148, 163, 184, 0.18);
  color: rgba(15, 23, 42, 0.8);
}

.btn-primary {
  background: linear-gradient(135deg, #39a9e1, #2563eb);
  color: #ffffff;
  box-shadow: 0 14px 34px rgba(2, 6, 23, 0.16);
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #b91c1c);
  color: #ffffff;
  box-shadow: 0 14px 34px rgba(2, 6, 23, 0.16);
}
</style>

