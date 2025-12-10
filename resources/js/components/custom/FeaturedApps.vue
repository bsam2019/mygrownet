<template>
  <section class="py-16 sm:py-20 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-12">
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-100 text-blue-700 text-sm font-medium mb-4">
          <SparklesIcon class="w-4 h-4" aria-hidden="true" />
          Business Tools
        </span>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          Powerful Apps to Grow Your Business
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Access our suite of business tools designed to help you manage, grow, and scale your business effectively.
        </p>
      </div>

      <!-- Apps Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <Link
          v-for="app in apps"
          :key="app.id"
          :href="getPublicRoute(app)"
          class="group relative bg-white rounded-2xl p-6 shadow-md border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
        >
          <!-- Icon -->
          <div 
            class="w-14 h-14 flex items-center justify-center rounded-xl mb-4 transition-transform duration-300 group-hover:scale-110"
            :style="{ backgroundColor: app.color || '#3B82F6' }"
          >
            <component 
              :is="getAppIcon(app.slug)" 
              class="w-7 h-7 text-white"
              aria-hidden="true"
            />
          </div>

          <!-- Content -->
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ app.name }}</h3>
          <p class="text-sm text-gray-600 mb-4">{{ getAppDescription(app.slug) }}</p>

          <!-- Features -->
          <ul class="space-y-2">
            <li v-for="feature in getAppFeatures(app.slug)" :key="feature" class="flex items-center gap-2 text-sm text-gray-500">
              <CheckCircleIcon class="w-4 h-4 text-emerald-500 flex-shrink-0" aria-hidden="true" />
              {{ feature }}
            </li>
          </ul>

          <!-- Status Badge -->
          <span 
            v-if="app.status === 'beta'" 
            class="absolute top-4 right-4 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700 rounded-full"
          >
            Beta
          </span>
          
          <!-- Arrow indicator -->
          <div class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity">
            <ArrowRightIcon class="w-5 h-5 text-blue-500" aria-hidden="true" />
          </div>
        </Link>
      </div>

      <!-- CTA -->
      <div class="text-center">
        <Link
          href="/apps"
          class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium rounded-full hover:from-blue-600 hover:to-blue-700 hover:scale-105 transition-all duration-300 shadow-md"
        >
          <CubeIcon class="w-5 h-5" aria-hidden="true" />
          View All Apps
          <ArrowRightIcon class="w-4 h-4" aria-hidden="true" />
        </Link>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
  SparklesIcon,
  CheckCircleIcon,
  ArrowRightIcon,
  CubeIcon,
  ClipboardDocumentCheckIcon,
  ChartBarIcon,
  ShoppingCartIcon,
  BanknotesIcon,
} from '@heroicons/vue/24/solid';

interface App {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  color: string | null;
  primary_route: string;
  status: string;
}

defineProps<{
  apps: App[];
}>();

const getAppIcon = (slug: string) => {
  const iconMap: Record<string, any> = {
    'bizboost': SparklesIcon,
    'growfinance': BanknotesIcon,
    'growbiz': ClipboardDocumentCheckIcon,
    'marketplace': ShoppingCartIcon,
  };
  return iconMap[slug] || CubeIcon;
};

const getAppDescription = (slug: string): string => {
  const descriptions: Record<string, string> = {
    'bizboost': 'Complete business management & marketing automation platform',
    'growfinance': 'Professional accounting & financial management for SMEs',
    'growbiz': 'Team management & employee productivity system',
    'marketplace': 'Browse and purchase products & services',
  };
  return descriptions[slug] || 'Business tool';
};

const getAppFeatures = (slug: string): string[] => {
  const features: Record<string, string[]> = {
    'bizboost': ['Marketing automation', 'CRM & leads', 'Business analytics'],
    'growfinance': ['Invoicing & billing', 'Expense tracking', 'Financial reports'],
    'growbiz': ['Employee management', 'Task tracking', 'Performance reviews'],
    'marketplace': ['Product catalog', 'Secure checkout', 'Order tracking'],
  };
  return features[slug] || [];
};

// Get public landing page route for each app
const getPublicRoute = (app: App): string => {
  const publicRoutes: Record<string, string> = {
    'bizboost': '/bizboost/welcome',
    'growfinance': '/growfinance',
    'growbiz': '/growbiz',
    'marketplace': '/marketplace',
  };
  return publicRoutes[app.slug] || app.primary_route || '/apps';
};
</script>
