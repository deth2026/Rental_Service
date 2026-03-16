<template>
  <section class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Admin Dashboard</h1>
        <p>This page is ready.</p>
      </div>
      <router-link to="/admin/users" class="action-button">User Management</router-link>
    </div>
  </section>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const menu = ['Dashboard', 'User Management', 'Shop Management', 'Vehicle Management', 'Bookings', 'Reports', 'Settings'];

const stats = [
  { label: 'Total Users', value: '2,482', trend: 12, icon: '👥', bg: 'bg-blue-50' },
  { label: 'Total Shops', value: '156', trend: 4, icon: '🏪', bg: 'bg-purple-50' },
  { label: 'Total Vehicles', value: '892', trend: -2, icon: '🚗', bg: 'bg-orange-50' },
  { label: 'Total Bookings', value: '12,302', trend: 18, icon: '🛍️', bg: 'bg-green-50' },
  { label: 'Total Revenue', value: '$45.2k', trend: 24, icon: '💵', bg: 'bg-cyan-50' }
];

const users = [
  { id: 1, name: 'John Doe', email: 'john@example.com', role: 'customer', status: 'active' },
  { id: 2, name: 'Shop Owner', email: 'shop@example.com', role: 'shop', status: 'verified' },
  { id: 3, name: 'New User', email: 'new@example.com', role: 'customer', status: 'pending' }
];

const statusClass = (status) => {
  if (status === 'active' || status === 'verified') return 'bg-green-100 text-green-600';
  if (status === 'pending') return 'bg-orange-100 text-orange-600';
  return 'bg-red-100 text-red-600';
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await fetch('/api/users/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};
</script>

<style scoped>
.dashboard {
  padding: 2rem;
  min-height: 100vh;
  background: #f1f5f9;
}

.dashboard h1 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.dashboard p {
  color: #64748b;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.action-button {
  background: #1f9ae5;
  color: #fff;
  border-radius: 999px;
  padding: 0.65rem 1.6rem;
  font-weight: 600;
  transition: background 0.2s ease, transform 0.2s ease;
}

.action-button:hover {
  background: #1682be;
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard {
    padding: 1.5rem;
  }

  .dashboard h1 {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .dashboard {
    padding: 1rem;
  }

  .dashboard h1 {
    font-size: 1.25rem;
  }

  .dashboard p {
    font-size: 0.9rem;
  }
}
</style>
