<script setup lang="ts">
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
    features: ['Business Cards', 'Flyers & Brochures', 'Posters & Banners', 'Catalogs & Magazines']
  },
  {
    icon: PaintBrushIcon,
    title: 'Brand Identity Design',
    description: 'Create a memorable brand identity that resonates with your target audience and stands out.',
    features: ['Logo Design', 'Brand Guidelines', 'Visual Identity', 'Brand Strategy']
  },
  {
    icon: ShoppingBagIcon,
    title: 'Promotional Products',
    description: 'Custom branded merchandise to promote your business and increase brand visibility.',
    features: ['T-Shirts & Apparel', 'Mugs & Drinkware', 'Bags & Accessories', 'Corporate Gifts']
  },
  {
    icon: DocumentTextIcon,
    title: 'Marketing Materials',
    description: 'Professional marketing collateral designed to convert prospects into customers.',
    features: ['Brochures', 'Presentation Folders', 'Sales Sheets', 'Direct Mail']
  },
  {
    icon: CubeIcon,
    title: 'Packaging Design',
    description: 'Eye-catching packaging solutions that protect your products and enhance brand perception.',
    features: ['Product Packaging', 'Label Design', 'Box Design', 'Custom Solutions']
  },
  {
    icon: SparklesIcon,
    title: 'Signage & Display',
    description: 'Indoor and outdoor signage solutions to maximize your business visibility.',
    features: ['Storefront Signs', 'Vehicle Wraps', 'Trade Show Displays', 'Window Graphics']
  }
];
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
          <ServiceCard
            v-for="service in services"
            :key="service.title"
            :icon="service.icon"
            :title="service.title"
            :description="service.description"
            :features="service.features"
          />
        </div>
      </div>
    </section>

    <!-- Process Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Process</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Simple, transparent, and efficient workflow to bring your vision to life
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div v-for="(step, index) in 4" :key="index" class="text-center">
            <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">
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
