<script setup>
const items = [
  { id: 1, title: 'No damage reports yet', status: '—' },
]
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Damage Reports</h2>
        <p>Record issues and keep a clear history for each vehicle.</p>
      </div>
      <button type="button" class="section-btn" disabled title="Coming soon">
        New Report
      </button>
    </div>

    <div class="section-card">
      <div class="list">
        <div v-for="row in items" :key="row.id" class="list-row">
          <div class="list-title">{{ row.title }}</div>
          <div class="list-meta">Status: {{ row.status }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

