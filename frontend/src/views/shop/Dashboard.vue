<script>
import '../../assets/dashboard.css'
export default {
  name: 'dashboard'
}
</script>
<template>
  <div class="flex flex-col md:flex-row min-h-screen bg-gray-50 font-sans">
    
    <aside class="w-full md:w-64 bg-slate-900 text-white p-6 flex flex-col">
      <div class="flex items-center gap-2 mb-10 text-xl font-bold">
        <div class="p-2 bg-blue-500 rounded-lg"></div> MOTOPREMIUM
      </div>
      
      <nav class="flex-1 space-y-4">
        <a v-for="item in menu" :key="item" href="#" 
           :class="item === 'Dashboard' ? 'bg-blue-500' : 'hover:bg-slate-800'"
           class="flex items-center p-3 rounded-lg transition-colors">
          {{ item }}
        </a>
      </nav>

      <button class="mt-auto pt-6 text-gray-400 hover:text-white flex items-center gap-2">
        <span>Logout</span>
      </button>
    </aside>

    <main class="flex-1 p-4 md:p-8">
      
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

    </main>
  </div>
</template>

<script setup>
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