<script setup>
const stats = [
  { label: 'Average Rating', value: '0.0 / 5' },
  { label: 'Total Reviews', value: '0' },
  { label: 'Reply Rate', value: '0%' },
]
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Reviews &amp; Feedback</h2>
        <p>See what customers say and reply to reviews.</p>
      </div>
    </div>

    <div class="section-grid">
      <div v-for="s in stats" :key="s.label" class="section-card">
        <div class="card-label">{{ s.label }}</div>
        <div class="card-value">{{ s.value }}</div>
      </div>
    </div>

    <div class="section-card">
      <div class="empty-state">No reviews yet.</div>
    </div>
  </div>
</template>

