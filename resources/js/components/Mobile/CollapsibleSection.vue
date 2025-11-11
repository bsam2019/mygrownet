<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <button
      @click="isOpen = !isOpen"
      class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors"
    >
      <div class="flex items-center gap-3">
        <component v-if="icon" :is="icon" class="h-5 w-5 text-gray-400" />
        <div class="text-left">
          <h3 class="text-sm font-medium text-gray-900">{{ title }}</h3>
          <p v-if="subtitle" class="text-xs text-gray-500">{{ subtitle }}</p>
        </div>
      </div>
      <ChevronDownIcon
        class="h-5 w-5 text-gray-400 transition-transform"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>
    
    <div
      v-show="isOpen"
      class="px-4 pb-4 border-t border-gray-100"
    >
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

defineProps<{
  title: string;
  subtitle?: string;
  icon?: any;
  defaultOpen?: boolean;
}>();

const isOpen = ref(false);
</script>
