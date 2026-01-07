<script setup lang="ts">
defineProps<{
  projects: Array<{
    id: number;
    title: string;
    category: string;
    image: string;
    description: string;
  }>;
}>();
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mt-12">
    <div
      v-for="project in projects"
      :key="project.id"
      class="group cursor-pointer"
    >
      <div class="relative overflow-hidden rounded-xl bg-gray-200 aspect-[4/3] mb-4">
        <img 
          :src="project.image" 
          :alt="project.title"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-red-600 opacity-0 group-hover:opacity-90 transition-opacity flex items-center justify-center p-6">
          <div class="text-white text-center">
            <h4 class="font-semibold text-lg mb-2">{{ project.title }}</h4>
            <p class="text-sm text-red-100">{{ project.description }}</p>
          </div>
        </div>
      </div>
      
      <div class="px-2">
        <p class="text-sm text-blue-600 font-medium mb-1">{{ project.category }}</p>
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
          {{ project.title }}
        </h3>
      </div>
    </div>
  </div>
</template>
