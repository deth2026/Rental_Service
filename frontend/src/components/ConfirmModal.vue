<template>
  <div v-if="modelValue" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="onCancel">
    <div class="modal">
      <div class="modal-head">
        <div>
          <div class="modal-title">{{ title }}</div>
          <div class="modal-sub">{{ subtitle }}</div>
        </div>
        <button type="button" class="icon-action" title="Close" @click="onCancel">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body">
        <p>{{ message }}</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" @click="onCancel">
          {{ cancelText }}
        </button>
        <button type="button" class="btn btn-primary" @click="onConfirm">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm'
  },
  subtitle: {
    type: String,
    default: ''
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  variant: {
    type: String,
    default: 'primary' // primary, danger, warning, success
  }
})

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
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 400px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #eee;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.modal-sub {
  font-size: 0.875rem;
  color: #666;
  margin-top: 0.25rem;
}

.modal-body {
  padding: 1.5rem;
  flex: 1;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid #eee;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-ghost {
  background: transparent;
  color: #333;
  border: 1px solid #ddd;
}

.btn-ghost:hover {
  background: #f5f5f5;
}

.btn-primary {
  background: #2564eb;
  color: white;
}

.btn-primary:hover {
  background: #1e52bb;
}

.btn-danger {
  background: #dc2626;
  color: white;
}

.btn-danger:hover {
  background: #b91c1c;
}

.icon-action {
  background: transparent;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #666;
  font-size: 1.25rem;
}

.icon-action:hover {
  color: #333;
}

/* Variant-based styling */
.modal[data-variant="danger"] .btn-primary {
  background: #dc2626;
}

.modal[data-variant="danger"] .btn-primary:hover {
  background: #b91c1c;
}

.modal[data-variant="warning"] .btn-primary {
  background: #d97706;
}

.modal[data-variant="warning"] .btn-primary:hover {
  background: #b45309;
}

.modal[data-variant="success"] .btn-primary {
  background: #16a34a;
}

.modal[data-variant="success"] .btn-primary:hover {
  background: #15803d;
}
</style>