<script setup>
const coupons = []
</script>

<template>
  <div class="shop-section">
    <div class="section-head">
      <div>
        <h2>Coupons</h2>
        <p>Create discount codes to attract more bookings.</p>
      </div>
      <button type="button" class="section-btn" disabled title="Coming soon">
        Create Coupon
      </button>
    </div>

    <div class="section-card">
      <div class="empty-state" v-if="coupons.length === 0">No coupons yet.</div>
    </div>
  </div>
</template>

