<script>
import '../../assets/dashboard.css'
export default {
  name: 'dashboard'
}
</script>
<template>
<<<<<<< HEAD
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-50 font-sans">
    
    <aside class="w-full md:w-64 bg-slate-900 text-white p-6 flex flex-col">
      <div class="flex items-center gap-2 mb-10 text-xl font-bold">
        <div class="p-2 bg-blue-500 rounded-lg"></div> MOTOPREMIUM
      </div>
      
      <nav class="flex-1 space-y-4">
        <a v-for="item in menu" :key="item" href="#" 
           :class="item === 'Dashboard' ? 'bg-blue-500' : 'hover:bg-slate-800'"
           class="flex items-center p-3 rounded-lg transition-colors">
=======
  <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row font-sans">
    <aside class="w-full md:w-64 bg-slate-900 text-slate-100 p-6 flex flex-col">
      <div class="mb-8">
        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Rental Service</p>
        <h1 class="text-2xl font-bold mt-2">Shop Panel</h1>
      </div>

      <nav class="space-y-2 flex-1">
        <a
          v-for="item in menuItems"
          :key="item"
          href="#"
          :class="item === 'Dashboard' ? 'bg-cyan-500 text-slate-900' : 'hover:bg-slate-800'"
          class="block rounded-lg px-3 py-2 text-sm font-medium transition-colors"
        >
>>>>>>> view_shop
          {{ item }}
        </a>
      </nav>

<<<<<<< HEAD
      <button class="mt-auto pt-6 text-gray-400 hover:text-white flex items-center gap-2">
        <span>Logout</span>
=======
      <button
        @click="handleLogout"
        class="mt-6 rounded-lg border border-slate-700 px-3 py-2 text-left text-sm hover:bg-slate-800 transition-colors"
      >
        Logout
>>>>>>> view_shop
      </button>
    </aside>

    <main class="flex-1 p-4 md:p-8">
<<<<<<< HEAD
      
      <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Admin Dashboard Overview</h1>
          <p class="text-gray-500">Welcome back, Alex. Here's what's happening today.</p>
        </div>
        <button class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg flex items-center gap-2">
          <span>+</span> Add New Shop
        </button>
      </header>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div v-for="stat in stats" :key="stat.label" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
          <div class="flex justify-between items-start mb-4">
            <span class="p-2 rounded-lg" :class="stat.bg">{{ stat.icon }}</span>
            <span :class="stat.trend > 0 ? 'text-green-500' : 'text-red-500'" class="text-xs font-bold">
              {{ stat.trend > 0 ? '+' : '' }}{{ stat.trend }}%
            </span>
          </div>
          <p class="text-xs text-gray-400 uppercase font-bold">{{ stat.label }}</p>
          <h2 class="text-2xl font-bold text-gray-800">{{ stat.value }}</h2>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center">
          <h3 class="font-bold">Recent Shop Registrations</h3>
          <select class="text-sm border rounded p-1 text-gray-500"><option>All Status</option></select>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 text-gray-400 text-xs uppercase">
              <tr>
                <th class="p-4">Shop Name</th>
                <th class="p-4">Owner</th>
                <th class="p-4">Fleet Size</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="text-sm">
              <tr v-for="shop in shops" :key="shop.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                <td class="p-4 font-semibold">{{ shop.name }}</td>
                <td class="p-4 text-gray-600">{{ shop.owner }}</td>
                <td class="p-4 font-medium">{{ shop.fleet }} <span class="text-xs text-gray-400">BIKES</span></td>
                <td class="p-4">
                  <span :class="statusClass(shop.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
                    {{ shop.status }}
                  </span>
                </td>
                <td class="p-4 text-right">
                  <button class="text-blue-500 hover:underline">View</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

=======
      <header class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-8">
        <div>
          <h2 class="text-2xl font-bold text-slate-800">Shop Dashboard</h2>
          <p class="text-slate-500 text-sm">Monitor your vehicles, bookings, and shop performance.</p>
        </div>
        <button class="rounded-lg bg-cyan-500 text-slate-900 px-5 py-2 font-semibold hover:bg-cyan-400 transition-colors">
          + Add Vehicle
        </button>
      </header>

      <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        <article
          v-for="stat in stats"
          :key="stat.label"
          class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm"
        >
          <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">{{ stat.label }}</p>
          <div class="flex items-end justify-between">
            <p class="text-2xl font-bold text-slate-800">{{ stat.value }}</p>
            <span
              class="text-xs font-bold"
              :class="stat.trend >= 0 ? 'text-emerald-600' : 'text-rose-600'"
            >
              {{ stat.trend >= 0 ? '+' : '' }}{{ stat.trend }}%
            </span>
          </div>
        </article>
      </section>

      <section class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        <article class="xl:col-span-3 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-semibold text-slate-800">My Vehicles</h3>
            <button class="text-sm text-cyan-600 hover:underline">View all</button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 text-slate-400 text-xs uppercase">
                <tr>
                  <th class="px-5 py-3">Vehicle</th>
                  <th class="px-5 py-3">Price / Day</th>
                  <th class="px-5 py-3">Status</th>
                  <th class="px-5 py-3 text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="vehicle in vehicles"
                  :key="vehicle.id"
                  class="border-t border-slate-100 text-sm"
                >
                  <td class="px-5 py-4 font-medium text-slate-700">{{ vehicle.name }}</td>
                  <td class="px-5 py-4 text-slate-600">${{ vehicle.price }}</td>
                  <td class="px-5 py-4">
                    <span
                      class="px-2 py-1 text-xs rounded-full font-semibold"
                      :class="availabilityClass(vehicle.status)"
                    >
                      {{ vehicle.status }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <button class="text-cyan-600 hover:underline">Edit</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

        <article class="xl:col-span-2 rounded-xl bg-white border border-slate-200 shadow-sm">
          <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Recent Bookings</h3>
          </div>
          <ul class="divide-y divide-slate-100">
            <li v-for="booking in bookings" :key="booking.id" class="px-5 py-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-sm font-semibold text-slate-700">{{ booking.customer }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ booking.vehicle }} • {{ booking.date }}</p>
                </div>
                <span
                  class="px-2 py-1 text-[11px] uppercase rounded-full font-semibold"
                  :class="bookingClass(booking.status)"
                >
                  {{ booking.status }}
                </span>
              </div>
            </li>
          </ul>
        </article>
      </section>
>>>>>>> view_shop
    </main>
  </div>
</template>

<script setup>
<<<<<<< HEAD
const menu = ['Dashboard', 'Shop Management', 'User Management', 'Financials', 'Reports', 'Settings'];

const stats = [
  { label: 'Total Users', value: '2,482', trend: 12, icon: '👥', bg: 'bg-blue-50' },
  { label: 'Total Shops', value: '156', trend: 4, icon: '🏪', bg: 'bg-purple-50' },
  { label: 'Total Vehicles', value: '892', trend: -2, icon: '🚗', bg: 'bg-orange-50' },
  { label: 'Total Bookings', value: '12,302', trend: 18, icon: '🛍️', bg: 'bg-green-50' },
  { label: 'Total Revenue', value: '$45.2k', trend: 24, icon: '💵', bg: 'bg-cyan-50' }
];

const shops = [
  { id: 1, name: 'Munich Premium Motors', owner: 'Karl Schmidt', fleet: 42, status: 'verified' },
  { id: 2, name: 'Alpine Moto Hub', owner: 'Maria Rossi', fleet: 18, status: 'pending' },
  { id: 3, name: 'Sunset Rides Ibiza', owner: 'Julio Iglesias', fleet: 56, status: 'suspended' }
];

const statusClass = (status) => {
  if (status === 'verified') return 'bg-green-100 text-green-600';
  if (status === 'pending') return 'bg-orange-100 text-orange-600';
  return 'bg-red-100 text-red-600';
};
</script>
=======
import { useRouter } from 'vue-router';

const router = useRouter();

const menuItems = ['Dashboard', 'Vehicles', 'Bookings', 'Customers', 'Payments', 'Settings'];

const stats = [
  { label: 'Total Vehicles', value: '24', trend: 8 },
  { label: 'Active Bookings', value: '13', trend: 4 },
  { label: 'Monthly Revenue', value: '$6,430', trend: 12 },
  { label: 'Cancellation Rate', value: '2.1%', trend: -1 }
];

const vehicles = [
  { id: 1, name: 'Honda Click 125', price: 14, status: 'available' },
  { id: 2, name: 'Yamaha NMAX', price: 20, status: 'booked' },
  { id: 3, name: 'Toyota Prius', price: 45, status: 'maintenance' },
  { id: 4, name: 'Honda PCX 160', price: 18, status: 'available' }
];

const bookings = [
  { id: 1, customer: 'Sokha Chhun', vehicle: 'Yamaha NMAX', date: 'Mar 03, 2026', status: 'confirmed' },
  { id: 2, customer: 'Daniel Park', vehicle: 'Honda Click 125', date: 'Mar 02, 2026', status: 'pending' },
  { id: 3, customer: 'Mariya Ivanova', vehicle: 'Toyota Prius', date: 'Mar 01, 2026', status: 'completed' }
];

const availabilityClass = (status) => {
  if (status === 'available') return 'bg-emerald-100 text-emerald-700';
  if (status === 'booked') return 'bg-amber-100 text-amber-700';
  return 'bg-slate-200 text-slate-700';
};

const bookingClass = (status) => {
  if (status === 'confirmed') return 'bg-emerald-100 text-emerald-700';
  if (status === 'pending') return 'bg-amber-100 text-amber-700';
  return 'bg-sky-100 text-sky-700';
};

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await fetch('/api/users/logout', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
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
>>>>>>> view_shop
