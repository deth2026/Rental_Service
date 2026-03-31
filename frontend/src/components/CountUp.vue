<template>
  <span>{{ formattedValue }}</span>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  value: {
    type: [Number, String],
    required: true
  },
  formatter: {
    type: Function,
    default: (value) => value
  }
})

const count = ref(0)
const formattedValue = computed(() => props.formatter(count.value))

// Initialize count to 0
watch(() => props.value, (newValue) => {
  const target = Number(newValue)
  if (isNaN(target)) return
  
  // Animate the count from current value to target
  const duration = 1000 // 1 second animation
  const start = count.value
  const change = target - start
  const startTime = Date.now()
  
  const animateCount = () => {
    const elapsed = Date.now() - startTime
    const progress = Math.min(elapsed / duration, 1)
    
    // Using easeOutCubic for smooth animation
    const easeProgress = 1 - Math.pow(1 - progress, 3)
    
    count.value = start + (change * easeProgress)
    
    if (progress < 1) {
      requestAnimationFrame(animateCount)
    } else {
      count.value = target
    }
  }
  
  animateCount()
}, { immediate: true })
</script>
