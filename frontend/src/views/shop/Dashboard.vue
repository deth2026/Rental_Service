<template>
  <main class="dashboard-page">
    <section class="dashboard-header">
      <h1>Shop Dashboard</h1>
      <p>Manage your shop activity from one place.</p>
    </section>

    <section class="stats-grid">
      <article class="stat-card">
        <h2>Coupons</h2>
        <p>Create and manage discount codes for your customers.</p>
        <router-link to="/setting" class="card-link">Open Settings</router-link>
      </article>
      <article class="stat-card">
        <h2>Reports</h2>
        <p>Review damage and service history quickly.</p>
        <router-link to="/setting" class="card-link">View Reports</router-link>
      </article>
      <article class="stat-card">
        <h2>Vehicles</h2>
        <p>Track fleet status and update availability.</p>
        <router-link to="/setting" class="card-link">Manage Fleet</router-link>
      </article>
    </section>
  </main>
</template>

<script setup>
</script>

<style scoped>
.dashboard-page {
  max-width: 1100px;
  margin: 0 auto;
  padding: 24px;
}

.dashboard-header h1 {
  margin: 0;
  color: #0f172a;
  font-size: 2rem;
  font-weight: 700;
}

.dashboard-header p {
  margin: 8px 0 0;
  color: #475569;
}

.stats-grid {
  margin-top: 24px;
  display: grid;
  gap: 16px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
}

.stat-card h2 {
  margin: 0;
  color: #1e293b;
  font-size: 1.125rem;
}

.stat-card p {
  margin: 10px 0 14px;
  color: #64748b;
  font-size: 0.95rem;
  line-height: 1.5;
}

.card-link {
  color: #2563eb;
  text-decoration: none;
  font-weight: 600;
}

.card-link:hover {
  text-decoration: underline;
}
</style>

