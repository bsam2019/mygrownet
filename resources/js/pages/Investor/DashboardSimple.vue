<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 py-4">
        <h1 class="text-2xl font-bold">Investor Dashboard</h1>
        <p>Welcome, {{ investor?.name || 'Investor' }}</p>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-8">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Debug Information</h2>
        <pre class="bg-gray-100 p-4 rounded overflow-auto">{{ JSON.stringify($props, null, 2) }}</pre>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  investor?: any;
  investmentMetrics?: any;
  round?: any;
  platformMetrics?: any;
}>();
</script>
