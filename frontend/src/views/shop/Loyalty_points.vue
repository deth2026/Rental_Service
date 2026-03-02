<script setup>
</script>

<template>
  <div class="loyalty-points-container p-6 max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Loyalty Points</h1>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50 text-left">
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Customer
              </th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Total Points <span class="ml-1">🎁</span>
              </th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Bookings
              </th>
              <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                Status
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.loyalty-points-container {
  /* min-height: 100%; */
  padding: 20px;
}
</style>
