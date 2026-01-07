<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
  PrinterIcon, 
  PaintBrushIcon, 
  ShoppingBagIcon,
  DocumentTextIcon,
  CubeIcon,
  SparklesIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline';

const services = [
  {
    icon: PrinterIcon,
    title: 'Digital & Offset Printing',
    description: 'High-quality printing for all your business needs',
    color: 'blue'
  },
  {
    icon: PaintBrushIcon,
    title: 'Brand Identity',
    description: 'Create memorable brand identities that stand out',
    color: 'red'
  },
  {
    icon: ShoppingBagIcon,
    title: 'Promotional Products',
    description: 'Custom branded merchandise for your business',
    color: 'blue'
  },
  {
    icon: DocumentTextIcon,
    title: 'Marketing Materials',
    description: 'Professional collateral that converts',
    color: 'red'
  },
  {
    icon: CubeIcon,
    title: 'Packaging Design',
    description: 'Eye-catching packaging solutions',
    color: 'blue'
  },
  {
    icon: SparklesIcon,
    title: 'Signage & Display',
    description: 'Maximize your business visibility',
    color: 'red'
  }
];
</script>

<template>
  <section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Our Services
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Comprehensive printing and branding solutions tailored to your business needs
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="service in services"
          :key="service.title"
          class="bg-white p-8 rounded-xl shadow-sm hover:shadow-lg transition-all group"
        >
          <div 
            :class="[
              'w-14 h-14 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform',
              service.color === 'blue' ? 'bg-blue-100' : 'bg-red-100'
            ]"
          >
            <component 
              :is="service.icon" 
              :class="[
                'h-7 w-7',
                service.color === 'blue' ? 'text-blue-600' : 'text-red-600'
              ]"
              aria-hidden="true"
            />
          </div>
          
          <h3 class="text-xl font-semibold text-gray-900 mb-3">
            {{ service.title }}
          </h3>
          <p class="text-gray-600 mb-4">
            {{ service.description }}
          </p>
          
          <a 
            href="#" 
            :class="[
              'inline-flex items-center font-medium group-hover:gap-2 transition-all',
              service.color === 'blue' ? 'text-blue-600' : 'text-red-600'
            ]"
          >
            Learn More
            <ArrowRightIcon class="ml-1 h-4 w-4" aria-hidden="true" />
          </a>
        </div>
      </div>

      <div class="text-center mt-12">
        <Link
          href="/geopamu/services"
          class="inline-flex items-center bg-blue-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl"
        >
          View All Services
          <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
        </Link>
      </div>
    </div>
  </section>
</template>
