<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const headerVisible = ref(false);
const cardsVisible = ref<boolean[]>([]);

onMounted(() => {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const headerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        headerVisible.value = true;
      }
    });
  }, observerOptions);

  const cardsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        cardsVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const headerElement = document.querySelector('.featured-header');
  if (headerElement) headerObserver.observe(headerElement);

  document.querySelectorAll('.featured-card').forEach((card) => {
    cardsObserver.observe(card);
  });
});
</script>

<template>
  <section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div 
        class="featured-header text-center mb-16 transition-all duration-700"
        :class="headerVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Featured Work
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Explore some of our recent projects and see the quality we deliver
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(project, index) in projects"
          :key="project.id"
          :data-index="index"
          class="featured-card group cursor-pointer transition-all duration-500"
          :class="cardsVisible[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
          :style="{ transitionDelay: `${index * 100}ms` }"
        >
          <div class="relative overflow-hidden rounded-xl bg-gray-200 aspect-square mb-4 transform transition-all duration-300 group-hover:scale-105 group-hover:shadow-2xl">
            <img 
              :src="project.image" 
              :alt="project.title"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-red-600 opacity-0 group-hover:opacity-90 transition-all duration-300 flex items-center justify-center">
              <span class="text-white font-semibold text-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">View Project</span>
            </div>
          </div>
          
          <div class="px-2">
            <p class="text-sm text-blue-600 font-medium mb-1 transition-colors duration-300 group-hover:text-red-600">{{ project.category }}</p>
            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
              {{ project.title }}
            </h3>
          </div>
        </div>
      </div>

      <div class="text-center mt-12">
        <Link
          href="/geopamu/portfolio"
          class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-700 text-lg group transition-all duration-300"
        >
          View Full Portfolio
          <ArrowRightIcon class="ml-2 h-5 w-5 transition-transform duration-300 group-hover:translate-x-2" aria-hidden="true" />
        </Link>
      </div>
    </div>
  </section>
</template>
