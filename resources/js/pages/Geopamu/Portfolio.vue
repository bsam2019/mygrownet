<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import PageHeader from '@/Components/Geopamu/PageHeader.vue';
import PortfolioGrid from '@/Components/Geopamu/PortfolioGrid.vue';
import PortfolioFilter from '@/Components/Geopamu/PortfolioFilter.vue';

const categories = [
  { id: 'all', name: 'All Projects' },
  { id: 'branding', name: 'Branding' },
  { id: 'print', name: 'Print Design' },
  { id: 'packaging', name: 'Packaging' },
  { id: 'signage', name: 'Signage' },
  { id: 'promotional', name: 'Promotional' }
];

const projects = [
  {
    id: 1,
    title: 'Corporate Brand Identity',
    category: 'branding',
    image: 'https://images.unsplash.com/photo-1634942537034-2531766767d1?w=600&h=450&fit=crop',
    description: 'Complete brand identity package for a tech startup'
  },
  {
    id: 2,
    title: 'Restaurant Menu Design',
    category: 'print',
    image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=450&fit=crop',
    description: 'Elegant menu design with custom illustrations'
  },
  {
    id: 3,
    title: 'Product Packaging',
    category: 'packaging',
    image: 'https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=600&h=450&fit=crop',
    description: 'Eco-friendly packaging for organic products'
  },
  {
    id: 4,
    title: 'Storefront Signage',
    category: 'signage',
    image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&h=450&fit=crop',
    description: 'Modern illuminated signage for retail store'
  },
  {
    id: 5,
    title: 'Corporate Merchandise',
    category: 'promotional',
    image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=450&fit=crop',
    description: 'Branded merchandise for company events'
  },
  {
    id: 6,
    title: 'Marketing Brochure',
    category: 'print',
    image: 'https://images.unsplash.com/photo-1590859808308-3d2d9c515b1a?w=600&h=450&fit=crop',
    description: 'Multi-page brochure for real estate company'
  }
];

const activeCategory = ref('all');

const filteredProjects = computed(() => {
  if (activeCategory.value === 'all') return projects;
  return projects.filter(p => p.category === activeCategory.value);
});

const testimonialsHeaderVisible = ref(false);
const testimonialsVisible = ref<boolean[]>([]);

onMounted(() => {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const headerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        testimonialsHeaderVisible.value = true;
      }
    });
  }, observerOptions);

  const testimonialsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        testimonialsVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const headerElement = document.querySelector('.testimonials-header');
  if (headerElement) headerObserver.observe(headerElement);

  document.querySelectorAll('.testimonial-card').forEach((card) => {
    testimonialsObserver.observe(card);
  });
});
</script>

<template>
  <Head title="Portfolio - Geopamu" />
  
  <GeopamuLayout>
    <PageHeader
      title="Our Portfolio"
      subtitle="Explore our recent projects and see the quality of work we deliver"
    />

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <PortfolioFilter
          :categories="categories"
          v-model="activeCategory"
        />

        <PortfolioGrid :projects="filteredProjects" />
      </div>
    </section>

    <!-- Client Testimonials -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div 
          class="testimonials-header text-center mb-12 transition-all duration-700"
          :class="testimonialsHeaderVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Client Testimonials</h2>
          <p class="text-lg text-gray-600">What our clients say about working with us</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div 
            v-for="i in 3" 
            :key="i" 
            :data-index="i - 1"
            class="testimonial-card bg-white p-6 rounded-lg shadow-sm transform transition-all duration-700 hover:scale-105 hover:shadow-xl"
            :class="testimonialsVisible[i - 1] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
            :style="{ transitionDelay: `${(i - 1) * 150}ms` }"
          >
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-red-600 rounded-full mr-4 transform transition-transform duration-300 hover:rotate-12"></div>
              <div>
                <h4 class="font-semibold text-gray-900">Client Name</h4>
                <p class="text-sm text-gray-600">Company Name</p>
              </div>
            </div>
            <p class="text-gray-700 italic">
              "Exceptional quality and service. Geopamu transformed our brand identity and delivered beyond expectations."
            </p>
          </div>
        </div>
      </div>
    </section>
  </GeopamuLayout>
</template>
