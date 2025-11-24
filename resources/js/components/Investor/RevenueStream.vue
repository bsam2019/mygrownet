<template>
  <div class="space-y-2">
    <div class="flex justify-between items-center">
      <span class="text-sm font-medium text-gray-700">{{ name }}</span>
      <span class="text-sm font-semibold text-gray-900">{{ amount }}</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2">
      <div
        class="bg-gradient-to-r from-blue-600 to-indigo-600 h-2 rounded-full transition-all"
        :style="{ width: `${percentage}%` }"
      ></div>
    </div>
    <div class="text-xs text-gray-500">{{ percentage }}% of total revenue</div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  name: string
  percentage: string | number
  amount: string
}

defineProps<Props>()
</script>
