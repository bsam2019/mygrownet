<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GeopamuLayout from '@/Layouts/GeopamuLayout.vue';
import PageHeader from '@/Components/Geopamu/PageHeader.vue';
import { 
  CheckCircleIcon,
  UserGroupIcon,
  LightBulbIcon,
  HeartIcon
} from '@heroicons/vue/24/outline';

const values = [
  {
    icon: CheckCircleIcon,
    title: 'Quality Excellence',
    description: 'We never compromise on quality. Every project receives meticulous attention to detail.'
  },
  {
    icon: UserGroupIcon,
    title: 'Client-Focused',
    description: 'Your success is our success. We build lasting relationships through exceptional service.'
  },
  {
    icon: LightBulbIcon,
    title: 'Innovation',
    description: 'We stay ahead of industry trends to provide cutting-edge solutions.'
  },
  {
    icon: HeartIcon,
    title: 'Integrity',
    description: 'Honest communication, transparent pricing, and reliable delivery every time.'
  }
];

const stats = [
  { number: '500+', label: 'Projects Completed' },
  { number: '200+', label: 'Happy Clients' },
  { number: '10+', label: 'Years Experience' },
  { number: '15+', label: 'Team Members' }
];
</script>

<template>
  <Head title="About Us - Geopamu" />
  
  <GeopamuLayout>
    <PageHeader
      title="About Geopamu"
      subtitle="Your trusted partner in printing and branding excellence"
    />

    <!-- Story Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Story</h2>
            <div class="space-y-4 text-gray-700">
              <p>
                Founded with a vision to transform businesses through exceptional printing and branding solutions, 
                Geopamu has grown to become a trusted name in the industry.
              </p>
              <p>
                We combine traditional craftsmanship with modern technology to deliver results that exceed expectations. 
                Our team of experienced designers and print specialists work collaboratively to bring your vision to life.
              </p>
              <p>
                From small startups to established enterprises, we've helped hundreds of businesses create powerful 
                brand identities and marketing materials that drive results.
              </p>
            </div>
          </div>
          <div class="rounded-lg h-96 overflow-hidden">
            <img 
              src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&h=600&fit=crop" 
              alt="Geopamu team at work"
              class="w-full h-full object-cover"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gradient-to-r from-blue-700 to-red-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div v-for="stat in stats" :key="stat.label">
            <div class="text-4xl font-bold mb-2">{{ stat.number }}</div>
            <div class="text-white/90">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Values</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            The principles that guide everything we do
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="value in values" :key="value.title" class="text-center">
            <component 
              :is="value.icon" 
              class="h-12 w-12 text-blue-600 mx-auto mb-4"
              aria-hidden="true"
            />
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ value.title }}</h3>
            <p class="text-gray-600">{{ value.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Team Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
          <p class="text-lg text-gray-600">
            Talented professionals dedicated to your success
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
          <div v-for="i in 4" :key="i" class="text-center">
            <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4"></div>
            <h3 class="text-lg font-semibold text-gray-900">Team Member</h3>
            <p class="text-gray-600">Position Title</p>
          </div>
        </div>
      </div>
    </section>
  </GeopamuLayout>
</template>
