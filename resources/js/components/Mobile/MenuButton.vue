<template>
  <button
    @click="$emit('click')"
    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors active:bg-gray-100"
  >
    <div class="flex items-center gap-3">
      <component :is="icon" class="h-5 w-5 text-gray-400 flex-shrink-0" aria-hidden="true" />
      <div class="text-left">
        <p class="text-sm font-medium text-gray-900">{{ label }}</p>
        <p v-if="subtitle" class="text-xs text-gray-500">{{ subtitle }}</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span 
        v-if="badge && badge > 0"
        class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full"
      >
        {{ badge }}
      </span>
      <ChevronRightIcon class="h-5 w-5 text-gray-400 flex-shrink-0" aria-hidden="true" />
    </div>
  </button>
</template>

<script setup lang="ts">
import { ChevronRightIcon } from '@heroicons/vue/24/outline';

defineProps<{
  label: string;
  subtitle?: string;
  icon: any;
  badge?: number | string;
}>();

defineEmits<{
  click: [];
}>();
</script>
