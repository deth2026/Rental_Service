<template>
  <div
    v-if="modelValue"
    class="modal-backdrop"
    :class="`modal-backdrop--${layout}`"
    role="dialog"
    aria-modal="true"
    @click.self="onCancel"
  >
    <div class="modal" :data-variant="variant" :class="[{ 'modal--with-hero': showHero }, `modal--${layout}`]">
      <template v-if="layout === 'compact'">
        <div class="compact-icon-wrapper">
          <i :class="compactIconClass" aria-hidden="true"></i>
        </div>
        <div class="compact-title">{{ title }}</div>
        <p class="compact-message">{{ message }}</p>
        <div class="compact-actions">
          <button type="button" class="btn compact-btn compact-btn-cancel" @click="onCancel">
            {{ cancelText }}
          </button>
          <button type="button" class="btn compact-btn compact-btn-confirm" @click="onConfirm">
            {{ confirmText }}
          </button>
        </div>
      </template>

      <template v-else>
        <div v-if="showHero" class="modal-hero" :class="`modal-hero--${variant}`">
          <span class="modal-hero-text">HEADS UP</span>
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
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

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
    default: 'Your account data stays safe.'
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
  layout: {
    type: String,
    default: 'default' // default, compact
  },
  iconClass: {
    type: String,
    default: 'fa-solid fa-right-to-bracket'
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
const compactIconClass = computed(() => props.iconClass || 'fa-solid fa-right-to-bracket')

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
  inset: 0;
  background:
    radial-gradient(circle at 14% 12%, rgba(59, 130, 246, 0.36), transparent 46%),
    radial-gradient(circle at 86% 0%, rgba(14, 165, 233, 0.2), transparent 36%),
    rgba(15, 23, 42, 0.64);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1200;
  padding: 1rem;
}

.modal-backdrop--compact {
  background: rgba(15, 23, 42, 0.38);
  backdrop-filter: blur(2px);
}

.modal {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 62%, #f3f7ff 100%);
  border-radius: 26px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 34px 84px rgba(2, 18, 52, 0.35);
  position: relative;
}

.modal--compact {
  max-width: 440px;
  padding: 2rem 2rem 1.9rem;
  border-radius: 34px;
  text-align: center;
  background: #f8fafc;
  box-shadow: 0 26px 60px rgba(15, 23, 42, 0.24);
}

.modal::after {
  content: '';
  position: absolute;
  inset: 1px;
  border-radius: 24px;
  border: 1px solid rgba(148, 163, 184, 0.28);
  pointer-events: none;
}

.modal--compact::after {
  border-radius: 32px;
  border-color: rgba(148, 163, 184, 0.22);
}

.compact-icon-wrapper {
  width: 96px;
  height: 96px;
  border-radius: 999px;
  margin: 0 auto 1.3rem;
  display: grid;
  place-items: center;
  color: #f97316;
  font-size: 2rem;
  background: #fff1e9;
  border: 2px solid #fdba8c;
}

.compact-title {
  margin: 0;
  color: #0f172a;
  font-size: 2.2rem;
  line-height: 1.05;
  font-weight: 800;
}

.compact-message {
  margin: 0.65rem 0 1.65rem;
  color: #475569;
  font-size: 1.12rem;
  line-height: 1.45;
}

.compact-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.compact-btn {
  min-height: 56px;
  border-radius: 999px;
  font-size: 1.05rem;
  font-weight: 700;
}

.compact-btn-cancel {
  background: #f8fafc;
  color: #374151;
  border: 1px solid #cfd8e3;
}

.compact-btn-confirm {
  background: #2563eb;
  color: #ffffff;
  border: 1px solid #2563eb;
  box-shadow: 0 12px 24px rgba(37, 99, 235, 0.26);
}

.compact-btn:hover {
  transform: translateY(-1px);
}

.modal-hero {
  min-height: 104px;
  border-radius: 18px 18px 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.95rem;
  color: #fff;
  box-shadow: inset 0 -8px 18px rgba(2, 6, 23, 0.2);
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
  background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 45%, #1d4ed8 100%);
}

.modal-hero-text {
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.9rem;
  padding: 1.2rem 1.4rem 0.9rem;
  border-bottom: 1px solid #e5ecf7;
}

.modal:not(.modal--with-hero) .modal-head {
  padding-top: 1.35rem;
}

.modal-title {
  font-size: 1.9rem;
  font-weight: 800;
  color: #102a4c;
  line-height: 1.1;
}

.modal-sub {
  font-size: 0.86rem;
  color: #5f7088;
  margin-top: 0.28rem;
  line-height: 1.45;
}

.modal-body {
  margin: 0.9rem 1.35rem 0;
  padding: 1.15rem 1.1rem;
  flex: 1;
  color: #14243a;
  font-size: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.68rem;
  background: linear-gradient(180deg, #f8fbff 0%, #f3f7ff 100%);
  border-radius: 16px;
  border: 1px solid #e4ebf6;
}

.modal-message {
  margin: 0;
  font-size: 1.07rem;
  line-height: 1.45;
  font-weight: 700;
  color: #1e2e44;
}

.modal-note {
  margin: 0;
  font-size: 0.9rem;
  color: #54667d;
  line-height: 1.45;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.8rem;
  padding: 1.2rem 1.35rem 1.35rem;
}

.btn {
  min-width: 110px;
  min-height: 46px;
  padding: 0.65rem 1.12rem;
  border-radius: 11px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.18s ease;
}

.btn-ghost {
  background: #f2f7ff;
  color: #365372;
  border-color: #ccd9ec;
}

.btn-ghost:hover {
  background: #e8f1ff;
  border-color: #adc4e5;
}

.btn-primary {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 60%, #1e40af 100%);
  color: #fff;
  border-color: rgba(37, 99, 235, 0.45);
  box-shadow: 0 12px 26px rgba(37, 99, 235, 0.32);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.38);
}

.btn-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: #fff;
  border-color: rgba(220, 38, 38, 0.45);
  box-shadow: 0 10px 20px rgba(220, 38, 38, 0.32);
}

.btn-danger:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 24px rgba(220, 38, 38, 0.38);
}

.icon-action {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 999px;
  width: 42px;
  height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  padding: 0;
  cursor: pointer;
  color: #5b6b7f;
  font-size: 1.35rem;
  box-shadow: 0 10px 20px rgba(15, 23, 42, 0.14);
}

.icon-action:hover {
  color: #1f2f45;
  transform: translateY(-1px);
}

@media (max-width: 560px) {
  .modal {
    max-width: 94vw;
  }

  .modal--compact {
    padding: 1.6rem 1.2rem 1.35rem;
    border-radius: 28px;
  }

  .compact-icon-wrapper {
    width: 82px;
    height: 82px;
    font-size: 1.7rem;
    margin-bottom: 1rem;
  }

  .compact-title {
    font-size: 1.85rem;
  }

  .compact-message {
    font-size: 1rem;
    margin-bottom: 1.2rem;
  }

  .compact-btn {
    min-height: 50px;
    font-size: 1.05rem;
  }

  .modal-title {
    font-size: 1.55rem;
  }

  .modal-actions {
    justify-content: stretch;
  }

  .btn {
    flex: 1;
    min-width: 0;
  }
}
</style>
