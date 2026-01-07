<script setup lang="ts">
import { 
  CheckBadgeIcon,
  ClockIcon,
  UserGroupIcon,
  CurrencyDollarIcon
} from '@heroicons/vue/24/outline';

const features = [
  {
    icon: CheckBadgeIcon,
    title: 'Premium Quality',
    description: 'We use state-of-the-art equipment and premium materials to ensure exceptional results'
  },
  {
    icon: ClockIcon,
    title: 'Fast Turnaround',
    description: 'Quick delivery without compromising quality. Most projects completed within 3-5 days'
  },
  {
    icon: UserGroupIcon,
    title: 'Expert Team',
    description: 'Experienced designers and print specialists dedicated to bringing your vision to life'
  },
  {
    icon: CurrencyDollarIcon,
    title: 'Competitive Pricing',
    description: 'Transparent pricing with no hidden fees. Get the best value for your investment'
  }
];
</script>

<template>
  <section class="py-20 bg-gradient-to-br from-blue-700 to-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Why Choose Geopamu?
        </h2>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto">
          We combine quality, speed, and expertise to deliver exceptional results
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="text-center"
        >
          <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6">
            <component 
              :is="feature.icon" 
              class="h-8 w-8 text-red-400"
              aria-hidden="true"
            />
          </div>
          
          <h3 class="text-xl font-semibold mb-3">
            {{ feature.title }}
          </h3>
          <p class="text-blue-100">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
