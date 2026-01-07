<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/24/outline';

defineProps<{
  icon: any;
  title: string;
  description: string;
  features: string[];
}>();
</script>

<template>
  <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all">
    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
      <component :is="icon" class="h-7 w-7 text-blue-600" aria-hidden="true" />
    </div>
    
    <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ title }}</h3>
    <p class="text-gray-600 mb-6">{{ description }}</p>
    
    <ul class="space-y-2">
      <li v-for="feature in features" :key="feature" class="flex items-start">
        <CheckIcon class="h-5 w-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" aria-hidden="true" />
        <span class="text-gray-700">{{ feature }}</span>
      </li>
    </ul>
  </div>
</template>
