<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const headerVisible = ref(false);
const featuresVisible = ref<boolean[]>([]);

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

  const featuresObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        featuresVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const headerElement = document.querySelector('.why-choose-header');
  if (headerElement) headerObserver.observe(headerElement);

  document.querySelectorAll('.why-choose-feature').forEach((feature) => {
    featuresObserver.observe(feature);
  });
});
</script>

<template>
  <section class="py-20 bg-gradient-to-br from-blue-700 to-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div 
        class="why-choose-header text-center mb-16 transition-all duration-700"
        :class="headerVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        <h2 class="text-3xl md:text-4xl font-bold mb-4">
          Why Choose Geopamu?
        </h2>
        <p class="text-xl text-blue-100 max-w-3xl mx-auto">
          We combine quality, speed, and expertise to deliver exceptional results
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div
          v-for="(feature, index) in features"
          :key="feature.title"
          :data-index="index"
          class="why-choose-feature text-center transition-all duration-700"
          :class="featuresVisible[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
          :style="{ transitionDelay: `${index * 150}ms` }"
        >
          <div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-full flex items-center justify-center mx-auto mb-6 transform transition-all duration-300 hover:scale-110 hover:bg-white/20 hover:rotate-6">
            <component 
              :is="feature.icon" 
              class="h-8 w-8 text-red-400 transition-transform duration-300"
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
