<template>
    <div v-if="modelValue" class="modal-backdrop" role="dialog" aria-modal="true" @click.self="onCancel">
    <div class="modal" :data-variant="variant" :class="{ 'modal--with-hero': showHero }">
      <div
        v-if="showHero"
        class="modal-hero"
        :class="`modal-hero--${variant}`"
      >
        <span class="modal-hero-text">Heads up</span>
      </div>
      <div class="modal-head">
        <div>
          <div class="modal-title">{{ title }}</div>
          <div v-if="subtitle" class="modal-sub">{{ subtitle }}</div>
        </div>
        <button type="button" class="icon-action" title="Close" @click="onCancel">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="modal-body">
        <p class="modal-message">{{ message }}</p>
        <p class="modal-note">{{ friendlyNote }}</p>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost" @click="onCancel">
          {{ cancelText }}
        </button>
        <button 
          type="button" 
          class="btn btn-primary" 
          :class="{ 'btn-danger': confirmButtonVariant === 'danger' }" 
          @click="onConfirm"
        >
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

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
  helperText: {
    type: String,
    default: 'We just tidy up the booking list—your history stays safe.'
  },
  showHero: {
    type: Boolean,
    default: true
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
  },
  confirmButtonVariant: {
    type: String,
    default: '' // '', 'danger' - allows specific button styling
  }
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
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.25), rgba(15, 23, 42, 0.85));
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: linear-gradient(180deg, #ffffff 0%, #f8fafc 60%);
  border-radius: 24px;
  width: 90%;
  max-width: 440px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.35);
  position: relative;
}
.modal::after {
  content: '';
  position: absolute;
  inset: 1px;
  border-radius: 22px;
  border: 1px solid rgba(59, 130, 246, 0.25);
  pointer-events: none;
}

.modal-hero {
  height: 110px;
  border-radius: 16px 16px 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #fff;
  box-shadow: inset 0 -6px 12px rgba(0, 0, 0, 0.15);
}
.modal-hero--danger {
  background: linear-gradient(135deg, #f97316, #dc2626);
}
.modal-hero--warning {
  background: linear-gradient(135deg, #facc15, #f97316);
}
.modal-hero--success {
  background: linear-gradient(135deg, #34d399, #10b981);
}
.modal-hero--primary {
  background: linear-gradient(135deg, #60a5fa, #2563eb);
}

.modal-hero-text {
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem 0.75rem;
  border-bottom: 1px solid rgba(226, 232, 240, 1);
}
.modal:not(.modal--with-hero) .modal-head {
  padding-top: 1.5rem;
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
  padding: 1.25rem 1.5rem 1.5rem;
  flex: 1;
  color: #0f172a;
  font-size: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 0 0 24px 24px;
}

.modal-message {
  margin: 0;
  font-size: 1rem;
  line-height: 1.5;
}

.modal-note {
  margin: 0;
  font-size: 0.85rem;
  color: #475569;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.25rem 1.5rem 1.5rem;
  border-top: none;
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
  background: #f8fafc;
  color: #475569;
  border: none;
  box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.4);
}

.btn-ghost:hover {
  background: #f5f5f5;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb, #3b82f6);
  color: white;
  box-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
}

.btn-primary:hover {
  background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.btn-danger {
  background: #dc2626;
  color: white;
  border: none;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

.btn-danger:hover {
  background: #b91c1c;
  box-shadow: 0 6px 16px rgba(220, 38, 38, 0.5);
  transform: translateY(-1px);
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

.modal-head .icon-action {
  background: rgba(255, 255, 255, 0.85);
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 12px rgba(15, 23, 42, 0.15);
}

.modal-head .icon-action:hover {
  background: white;
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
