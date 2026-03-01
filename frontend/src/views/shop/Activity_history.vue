<script setup>
import { ref, computed } from 'vue'

const search = ref('')
const histories = ref([
  { id: 1, action: 'New booking received for Toyota Camry', time: '2 min ago' },
  { id: 2, action: 'Payment of $45 received from Sokha', time: '15 min ago' },
  { id: 3, action: 'Vehicle Honda Wave marked as Maintenance', time: '1 hour ago' },
  { id: 4, action: 'New review: 5 stars from Dara', time: '3 hours ago' }
])

const filtered = computed(() => {
  const s = search.value.trim().toLowerCase()
  if (!s) return histories.value
  return histories.value.filter(h => h.action.toLowerCase().includes(s) || h.time.toLowerCase().includes(s))
})
</script>

<template>
  <div class="activity-panel">
    <div class="line">
      <h3>Activity History</h3>
      <input v-model="search" placeholder="Search activity" />
    </div>

    <div class="panel">
      <div v-if="filtered.length === 0" class="row">No activity found</div>
      <div v-for="h in filtered" :key="h.id" class="row">
        <div>
          <strong>{{ h.action }}</strong>
          <div class="muted">{{ h.time }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.activity-panel .line { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px }
.activity-panel input { width:220px; padding:8px; border:1px solid #cbd5e1; border-radius:8px }
.panel { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px }
.row { padding:10px 0; border-bottom:1px solid #f1f5f9 }
.muted { font-size:12px; color:#64748b; margin-top:4px }
</style>
