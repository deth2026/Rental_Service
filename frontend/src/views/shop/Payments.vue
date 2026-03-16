<script setup>
const summary = [
  { label: 'Available Balance', value: '$0.00' },
  { label: 'Pending Payouts', value: '$0.00' },
  { label: 'Total Earnings', value: '$0.00' },
]
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Payments</h2>
        <p>Track earnings, payouts, and payment status for your bookings.</p>
      </div>
      <button type="button" class="section-btn" disabled title="Coming soon">
        Request Payout
      </button>
    </div>

    <div class="section-grid">
      <div v-for="item in summary" :key="item.label" class="section-card">
        <div class="card-label">{{ item.label }}</div>
        <div class="card-value">{{ item.value }}</div>
      </div>
    </div>

    <div class="section-card table-card">
      <div class="table-head">
        <h3>Recent Transactions</h3>
        <p>Transactions will appear here after real payments are connected.</p>
      </div>
      <div class="empty-state">No transactions yet.</div>
    </div>
  </div>
</template>

