<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import PageHeader from '@/Components/Geopamu/PageHeader.vue';
import ServiceCard from '@/Components/Geopamu/ServiceCard.vue';
import { 
  PrinterIcon, 
  PaintBrushIcon, 
  ShoppingBagIcon,
  DocumentTextIcon,
  CubeIcon,
  SparklesIcon
} from '@heroicons/vue/24/outline';

const services = [
  {
    icon: PrinterIcon,
    title: 'Digital & Offset Printing',
    description: 'High-quality printing services for all your business needs. From business cards to large format banners.',
    features: ['Business Cards', 'Flyers & Brochures', 'Posters & Banners', 'Catalogs & Magazines'],
    image: 'https://images.unsplash.com/photo-1565373679540-5c2b8c2e9d5e?w=600&h=400&fit=crop'
  },
  {
    icon: PaintBrushIcon,
    title: 'Brand Identity Design',
    description: 'Create a memorable brand identity that resonates with your target audience and stands out.',
    features: ['Logo Design', 'Brand Guidelines', 'Visual Identity', 'Brand Strategy'],
    image: 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=600&h=400&fit=crop'
  },
  {
    icon: ShoppingBagIcon,
    title: 'Promotional Products',
    description: 'Custom branded merchandise to promote your business and increase brand visibility.',
    features: ['T-Shirts & Apparel', 'Mugs & Drinkware', 'Bags & Accessories', 'Corporate Gifts'],
    image: 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=600&h=400&fit=crop'
  },
  {
    icon: DocumentTextIcon,
    title: 'Marketing Materials',
    description: 'Professional marketing collateral designed to convert prospects into customers.',
    features: ['Brochures', 'Presentation Folders', 'Sales Sheets', 'Direct Mail'],
    image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=600&h=400&fit=crop'
  },
  {
    icon: CubeIcon,
    title: 'Packaging Design',
    description: 'Eye-catching packaging solutions that protect your products and enhance brand perception.',
    features: ['Product Packaging', 'Label Design', 'Box Design', 'Custom Solutions'],
    image: 'https://images.unsplash.com/photo-1612198188060-c7c2a3b66eae?w=600&h=400&fit=crop'
  },
  {
    icon: SparklesIcon,
    title: 'Signage & Display',
    description: 'Indoor and outdoor signage solutions to maximize your business visibility.',
    features: ['Storefront Signs', 'Vehicle Wraps', 'Trade Show Displays', 'Window Graphics'],
    image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=600&h=400&fit=crop'
  }
];

const servicesVisible = ref<boolean[]>([]);
const processHeaderVisible = ref(false);
const processStepsVisible = ref<boolean[]>([]);

onMounted(() => {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const servicesObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        servicesVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const processHeaderObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        processHeaderVisible.value = true;
      }
    });
  }, observerOptions);

  const processStepsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        processStepsVisible.value[index] = true;
      }
    });
  }, observerOptions);

  document.querySelectorAll('.service-card-wrapper').forEach((card) => {
    servicesObserver.observe(card);
  });

  const processHeader = document.querySelector('.process-header');
  if (processHeader) processHeaderObserver.observe(processHeader);

  document.querySelectorAll('.process-step').forEach((step) => {
    processStepsObserver.observe(step);
  });
});
</script>

<template>
  <Head title="Our Services - Geopamu" />
  
  <GeopamuLayout>
    <PageHeader
      title="Our Services"
      subtitle="Comprehensive printing and branding solutions tailored to your business needs"
    />

    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="(service, index) in services"
            :key="service.title"
            :data-index="index"
            class="service-card-wrapper transition-all duration-700"
            :class="servicesVisible[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
            :style="{ transitionDelay: `${index * 100}ms` }"
          >
            <ServiceCard
              :icon="service.icon"
              :title="service.title"
              :description="service.description"
              :features="service.features"
              :image="service.image"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Process Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div 
          class="process-header text-center mb-12 transition-all duration-700"
          :class="processHeaderVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Process</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Simple, transparent, and efficient workflow to bring your vision to life
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div 
            v-for="(step, index) in 4" 
            :key="index" 
            :data-index="index"
            class="process-step text-center transition-all duration-700"
            :class="processStepsVisible[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
            :style="{ transitionDelay: `${index * 150}ms` }"
          >
            <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 transform transition-all duration-300 hover:scale-110 hover:bg-red-600 hover:rotate-12">
              {{ index + 1 }}
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
              {{ ['Consultation', 'Design', 'Production', 'Delivery'][index] }}
            </h3>
            <p class="text-gray-600">
              {{ [
                'Discuss your requirements and vision',
                'Create mockups and refine designs',
                'High-quality production process',
                'Timely delivery to your location'
              ][index] }}
            </p>
          </div>
        </div>
      </div>
    </section>
  </GeopamuLayout>
</template>
