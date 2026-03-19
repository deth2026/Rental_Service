<script setup>
import { computed } from 'vue'
import { useToast, useToastState } from '../composables/useToast.js'

const toastState = useToastState()
const { removeToast } = useToast()

const items = computed(() => toastState.items)

const iconByType = (type) => {
  if (type === 'success') return 'fa-solid fa-circle-check'
  if (type === 'error') return 'fa-solid fa-circle-xmark'
  if (type === 'warning') return 'fa-solid fa-triangle-exclamation'
  return 'fa-solid fa-circle-info'
}
</script>

<template>
  <div class="toast-container" aria-live="polite" aria-atomic="true">
    <transition-group name="toast-list" tag="div">
      <div
        v-for="item in items"
        :key="item.id"
        class="toast"
        :class="`toast-${item.type}`"
        role="status"
      >
        <i :class="iconByType(item.type)" aria-hidden="true"></i>
        <span>{{ item.message }}</span>
        <button
          type="button"
          class="toast-close"
          @click="removeToast(item.id)"
          aria-label="Dismiss"
        >
          ×
        </button>
      </div>
    </transition-group>
  </div>
</template>
