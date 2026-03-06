<template>
  <section class="dashboard">
    <h1>Admin Dashboard</h1>
    <p>This page is ready.</p>
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
}
</style>
