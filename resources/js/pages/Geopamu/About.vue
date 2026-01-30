<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const storyVisible = ref(false);
const statsVisible = ref<boolean[]>([]);
const valuesHeaderVisible = ref(false);
const valuesVisible = ref<boolean[]>([]);
const teamHeaderVisible = ref(false);
const teamVisible = ref<boolean[]>([]);

onMounted(() => {
  const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
  };

  const storyObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        storyVisible.value = true;
      }
    });
  }, observerOptions);

  const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        statsVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const valuesHeaderObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        valuesHeaderVisible.value = true;
      }
    });
  }, observerOptions);

  const valuesObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        valuesVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const teamHeaderObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        teamHeaderVisible.value = true;
      }
    });
  }, observerOptions);

  const teamObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const index = parseInt(entry.target.getAttribute('data-index') || '0');
        teamVisible.value[index] = true;
      }
    });
  }, observerOptions);

  const storyElement = document.querySelector('.story-section');
  if (storyElement) storyObserver.observe(storyElement);

  document.querySelectorAll('.stat-item').forEach((stat) => {
    statsObserver.observe(stat);
  });

  const valuesHeader = document.querySelector('.values-header');
  if (valuesHeader) valuesHeaderObserver.observe(valuesHeader);

  document.querySelectorAll('.value-item').forEach((value) => {
    valuesObserver.observe(value);
  });

  const teamHeader = document.querySelector('.team-header');
  if (teamHeader) teamHeaderObserver.observe(teamHeader);

  document.querySelectorAll('.team-member').forEach((member) => {
    teamObserver.observe(member);
  });
});
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
        <div 
          class="story-section grid grid-cols-1 lg:grid-cols-2 gap-12 items-center transition-all duration-700"
          :class="storyVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
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
          <div class="rounded-lg h-96 overflow-hidden transform transition-all duration-500 hover:scale-105 hover:shadow-2xl">
            <img 
              src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=800&h=600&fit=crop" 
              alt="Geopamu team at work"
              class="w-full h-full object-cover transition-transform duration-500 hover:scale-110"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gradient-to-r from-blue-700 to-red-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div 
            v-for="(stat, index) in stats" 
            :key="stat.label"
            :data-index="index"
            class="stat-item transition-all duration-700"
            :class="statsVisible[index] ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"
            :style="{ transitionDelay: `${index * 100}ms` }"
          >
            <div class="text-4xl font-bold mb-2 transform transition-all duration-300 hover:scale-110">{{ stat.number }}</div>
            <div class="text-white/90">{{ stat.label }}</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div 
          class="values-header text-center mb-12 transition-all duration-700"
          :class="valuesHeaderVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Our Values</h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            The principles that guide everything we do
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div 
            v-for="(value, index) in values" 
            :key="value.title" 
            :data-index="index"
            class="value-item text-center transition-all duration-700"
            :class="valuesVisible[index] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
            :style="{ transitionDelay: `${index * 150}ms` }"
          >
            <component 
              :is="value.icon" 
              class="h-12 w-12 text-blue-600 mx-auto mb-4 transform transition-all duration-300 hover:scale-110 hover:rotate-12"
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
        <div 
          class="team-header text-center mb-12 transition-all duration-700"
          :class="teamHeaderVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
          <p class="text-lg text-gray-600">
            Talented professionals dedicated to your success
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-8">
          <div 
            v-for="i in 4" 
            :key="i"
            :data-index="i - 1"
            class="team-member text-center transition-all duration-700"
            :class="teamVisible[i - 1] ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
            :style="{ transitionDelay: `${(i - 1) * 100}ms` }"
          >
            <div class="w-32 h-32 bg-gradient-to-br from-blue-600 to-red-600 rounded-full mx-auto mb-4 transform transition-all duration-300 hover:scale-110 hover:rotate-6"></div>
            <h3 class="text-lg font-semibold text-gray-900">Team Member</h3>
            <p class="text-gray-600">Position Title</p>
          </div>
        </div>
      </div>
    </section>
  </GeopamuLayout>
</template>
