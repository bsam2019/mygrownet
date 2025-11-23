<template>
  <div class="space-y-6 animate-pulse">
    <!-- Card Skeleton -->
    <div v-for="i in count" :key="i" class="bg-white rounded-xl shadow-sm p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <div class="h-5 bg-gray-200 rounded w-32"></div>
        <div class="h-4 bg-gray-200 rounded w-20"></div>
      </div>
      
      <!-- Content Lines -->
      <div class="space-y-3">
        <div class="h-4 bg-gray-200 rounded w-full"></div>
        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
        <div class="h-4 bg-gray-200 rounded w-4/6"></div>
      </div>
      
      <!-- Stats Grid (optional) -->
      <div v-if="showStats" class="grid grid-cols-2 gap-3 mt-4">
        <div class="h-20 bg-gray-100 rounded-lg"></div>
        <div class="h-20 bg-gray-100 rounded-lg"></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  count?: number;
  showStats?: boolean;
}

withDefaults(defineProps<Props>(), {
  count: 2,
  showStats: false
});
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
