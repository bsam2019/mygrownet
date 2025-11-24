<template>
  <div class="flex items-center justify-between">
    <div class="flex-1">
      <div class="flex items-center justify-between mb-1">
        <span class="text-sm font-medium text-gray-700">{{ label }}</span>
        <span class="text-sm font-semibold text-gray-900">{{ amount }}</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="bg-blue-600 h-2 rounded-full transition-all"
          :style="{ width: `${percentage}%` }"
        ></div>
      </div>
    </div>
    <span class="ml-4 text-sm font-semibold text-gray-500">{{ percentage }}%</span>
  </div>
</template>

<script setup lang="ts">
interface Props {
  label: string
  percentage: string | number
  amount: string
}

defineProps<Props>()
</script>
