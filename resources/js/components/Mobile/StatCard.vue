<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <p class="text-xs text-gray-500 mb-1">{{ label }}</p>
        <p class="text-xl font-bold text-gray-900">{{ value }}</p>
        <p v-if="subtitle" class="text-xs text-gray-500 mt-1">{{ subtitle }}</p>
      </div>
      <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :class="iconBgClass">
        <component :is="icon" class="h-5 w-5" :class="iconColorClass" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  label: string;
  value: string | number;
  subtitle?: string;
  icon: any;
  iconBgClass?: string;
  iconColorClass?: string;
}>();
</script>
