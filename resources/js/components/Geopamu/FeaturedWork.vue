<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon } from '@heroicons/vue/24/outline';

const projects = [
  {
    id: 1,
    title: 'Corporate Brand Identity',
    category: 'Branding',
    image: 'https://images.unsplash.com/photo-1634942537034-2531766767d1?w=600&h=400&fit=crop'
  },
  {
    id: 2,
    title: 'Restaurant Menu Design',
    category: 'Print Design',
    image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=400&fit=crop'
  },
  {
    id: 3,
    title: 'Product Packaging',
    category: 'Packaging',
    image: 'https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=600&h=400&fit=crop'
  },
  {
    id: 4,
    title: 'Storefront Signage',
    category: 'Signage',
    image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&h=400&fit=crop'
  }
];
</script>

<template>
  <section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Featured Work
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Explore some of our recent projects and see the quality we deliver
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="project in projects"
          :key="project.id"
          class="group cursor-pointer"
        >
          <div class="relative overflow-hidden rounded-xl bg-gray-200 aspect-square mb-4">
            <img 
              :src="project.image" 
              :alt="project.title"
              class="w-full h-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-red-600 opacity-0 group-hover:opacity-90 transition-opacity flex items-center justify-center">
              <span class="text-white font-semibold">View Project</span>
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

      <div class="text-center mt-12">
        <Link
          href="/geopamu/portfolio"
          class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700 text-lg"
        >
          View Full Portfolio
          <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
        </Link>
      </div>
    </div>
  </section>
</template>
