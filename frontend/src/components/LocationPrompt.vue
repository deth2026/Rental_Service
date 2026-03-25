<template>
  <transition name="location-pop">
    <div v-if="show" class="location-popup-overlay" role="dialog" aria-modal="true" aria-labelledby="location-title">
      <div class="location-popup-card">
        <button type="button" class="close-btn" @click="$emit('close')" :disabled="loading" aria-label="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="icon-shell">
          <i class="fa-solid fa-location-crosshairs"></i>
        </div>

        <h2 id="location-title">Enable Location Access</h2>
        <p class="subtitle">
          We use your location to unlock Login and Register, and to show accurate routes to nearby shops.
        </p>

        <div class="benefit-list">
          <span><i class="fa-solid fa-check"></i> Find nearest shops instantly</span>
          <span><i class="fa-solid fa-check"></i> Navigate with real-time distance</span>
          <span><i class="fa-solid fa-check"></i> Faster booking recommendations</span>
        </div>

        <p v-if="error" class="error-text">{{ error }}</p>

        <div class="actions">
          <button type="button" class="cancel-btn" @click="$emit('close')" :disabled="loading">Not now</button>
          <button type="button" class="allow-btn" @click="$emit('confirm')" :disabled="loading">
            <i class="fa-solid fa-location-arrow"></i>
            {{ loading ? 'Allowing...' : 'Allow Location Access' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
})

defineEmits(['confirm', 'close'])
</script>

<style scoped>
.location-popup-overlay {
  position: fixed;
  inset: 0;
  z-index: 100000;
  background: radial-gradient(circle at top, rgba(15, 23, 42, 0.22), rgba(2, 6, 23, 0.58));
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.location-popup-card {
  position: relative;
  width: min(500px, 100%);
  border-radius: 26px;
  padding: 28px;
  background: linear-gradient(165deg, #ffffff 0%, #f6fbff 60%, #eef7ff 100%);
  border: 1px solid rgba(59, 130, 246, 0.2);
  box-shadow: 0 35px 60px rgba(2, 6, 23, 0.25);
}

.close-btn {
  position: absolute;
  right: 16px;
  top: 16px;
  border: none;
  background: #eff6ff;
  color: #334155;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  cursor: pointer;
}

.close-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.icon-shell {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: linear-gradient(135deg, #0ea5e9, #2563eb);
  color: #fff;
  display: grid;
  place-items: center;
  box-shadow: 0 16px 30px rgba(37, 99, 235, 0.35);
}

.icon-shell i {
  font-size: 1.5rem;
}

h2 {
  margin: 18px 0 8px;
  font-size: 1.5rem;
  color: #0f172a;
}

.subtitle {
  margin: 0;
  color: #475569;
  line-height: 1.55;
}

.benefit-list {
  margin-top: 16px;
  display: grid;
  gap: 8px;
}

.benefit-list span {
  font-size: 0.92rem;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}

.benefit-list i {
  color: #0284c7;
}

.error-text {
  margin: 14px 0 0;
  color: #b91c1c;
  background: #fee2e2;
  border: 1px solid #fecaca;
  border-radius: 10px;
  padding: 10px;
  font-size: 0.9rem;
}

.actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.cancel-btn,
.allow-btn {
  border-radius: 12px;
  padding: 11px 16px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.cancel-btn {
  color: #334155;
  background: #e2e8f0;
}

.allow-btn {
  background: linear-gradient(135deg, #1d4ed8, #0284c7);
  color: #fff;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.allow-btn:disabled,
.cancel-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.location-pop-enter-active,
.location-pop-leave-active {
  transition: opacity 0.25s ease;
}

.location-pop-enter-from,
.location-pop-leave-to {
  opacity: 0;
}
</style>
