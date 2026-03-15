<script setup>
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
