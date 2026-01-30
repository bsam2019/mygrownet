<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/24/outline';

defineProps<{
  icon: any;
  title: string;
  description: string;
  features: string[];
  image: string;
}>();
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden group">
    <!-- Image Section -->
    <div class="relative h-48 overflow-hidden">
      <img 
        :src="image" 
        :alt="title"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
      
      <!-- Icon Badge -->
      <div class="absolute bottom-4 left-4 w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-lg transform transition-all duration-300 group-hover:scale-110 group-hover:rotate-6">
        <component :is="icon" class="h-6 w-6 text-blue-600" aria-hidden="true" />
      </div>
    </div>
    
    <!-- Content Section -->
    <div class="p-6">
      <h3 class="text-xl font-semibold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors">
        {{ title }}
      </h3>
      <p class="text-gray-600 mb-6">{{ description }}</p>
      
      <ul class="space-y-2">
        <li v-for="feature in features" :key="feature" class="flex items-start">
          <CheckIcon class="h-5 w-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" aria-hidden="true" />
          <span class="text-gray-700 text-sm">{{ feature }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>
