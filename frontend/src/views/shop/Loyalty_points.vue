<script setup>
const tiers = [
  { label: 'Bronze', value: '0+ points' },
  { label: 'Silver', value: '200+ points' },
  { label: 'Gold', value: '500+ points' },
]
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Loyalty Points</h2>
        <p>Reward returning customers with points and perks.</p>
      </div>
    </div>

    <div class="section-grid">
      <div v-for="t in tiers" :key="t.label" class="section-card">
        <div class="card-label">{{ t.label }}</div>
        <div class="card-value">{{ t.value }}</div>
      </div>
    </div>

    <div class="section-card">
      <div class="empty-state">Loyalty program setup coming soon.</div>
    </div>
  </div>
</template>

