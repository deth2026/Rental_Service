<script setup>
const activities = [
  { id: 1, title: 'No activity yet', time: '—' },
]
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Activity History</h2>
        <p>Keep track of important actions for your shop.</p>
      </div>
    </div>

    <div class="section-card">
      <div class="list">
        <div v-for="a in activities" :key="a.id" class="list-row">
          <div class="list-title">{{ a.title }}</div>
          <div class="list-meta">{{ a.time }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

