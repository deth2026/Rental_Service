<script setup>
<<<<<<< HEAD
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  duration: { type: Number, default: 900 },
  decimals: { type: Number, default: 0 },
  prefix: { type: String, default: '' },
  suffix: { type: String, default: '' },
  formatter: { type: Function, default: null }, // (n:number)=>string
})

const display = ref(0)
let rafId = null

const stop = () => {
  if (rafId) cancelAnimationFrame(rafId)
  rafId = null
}

const clamp = (n) => (Number.isFinite(n) ? n : 0)

const animate = (from, to) => {
  stop()
  const start = performance.now()
  const dur = Math.max(0, Number(props.duration || 0))

  const step = (t) => {
    const p = dur === 0 ? 1 : Math.min(1, (t - start) / dur)
    const eased = 1 - Math.pow(1 - p, 3)
    display.value = from + (to - from) * eased
    if (p < 1) rafId = requestAnimationFrame(step)
  }

  rafId = requestAnimationFrame(step)
}

watch(
  () => props.value,
  (next, prev) => {
    const to = clamp(Number(next))
    const from = clamp(Number(prev))
    if (from === to) {
      display.value = to
      return
    }
    animate(from, to)
  },
  { immediate: true }
)

onMounted(() => {
  display.value = clamp(Number(props.value))
})

onBeforeUnmount(stop)

const text = computed(() => {
  const n = clamp(display.value)
  const formatted = props.formatter
    ? props.formatter(n)
    : new Intl.NumberFormat('en-US', {
        minimumFractionDigits: props.decimals,
        maximumFractionDigits: props.decimals,
      }).format(n)

  return `${props.prefix}${formatted}${props.suffix}`
})
</script>

<template>
  <span>{{ text }}</span>
</template>

=======
import { computed } from 'vue'

const props = defineProps({
  value: {
    type: [Number, String],
    default: 0,
  },
  formatter: {
    type: Function,
    default: (val) => val,
  },
})

const displayed = computed(() => props.formatter(Number(props.value) || 0))
</script>

<template>
  <span>{{ displayed }}</span>
</template>
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
