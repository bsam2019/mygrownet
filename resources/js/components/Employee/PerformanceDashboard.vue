<template>
  <div class="bg-white rounded-lg shadow border p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">Performance Dashboard</h3>
      <button
        @click="$emit('view-analytics')"
        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
      >
        View Analytics
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="text-center p-4 bg-blue-50 rounded-lg">
        <p class="text-2xl font-bold text-blue-600">{{ stats?.averageScore || 0 }}%</p>
        <p class="text-xs text-gray-600">Average Score</p>
      </div>
      <div class="text-center p-4 bg-green-50 rounded-lg">
        <p class="text-2xl font-bold text-green-600">{{ stats?.topPerformers || 0 }}</p>
        <p class="text-xs text-gray-600">Top Performers</p>
      </div>
      <div class="text-center p-4 bg-yellow-50 rounded-lg">
        <p class="text-2xl font-bold text-yellow-600">{{ stats?.goalAchievementRate || 0 }}%</p>
        <p class="text-xs text-gray-600">Goal Achievement</p>
      </div>
      <div class="text-center p-4 bg-purple-50 rounded-lg">
        <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats?.totalCommissions || 0) }}</p>
        <p class="text-xs text-gray-600">Total Commissions</p>
      </div>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200">
      <button
        @click="$emit('manage-goals')"
        class="w-full bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md hover:bg-blue-700"
      >
        Manage Goals
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  }
})

defineEmits(['view-analytics', 'manage-goals'])

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(value)
}
</script>