<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRightIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const slides = [
  {
    title: 'Transform Your Brand with',
    highlight: 'Quality Printing',
    description: 'From business cards to large format printing, we deliver exceptional quality and creative solutions that make your brand stand out.',
    image: 'https://images.unsplash.com/photo-1611162617474-5b21e879e113?w=1920&q=80',
    badge: '✨ Professional Printing & Branding Solutions'
  },
  {
    title: 'Elevate Your Business with',
    highlight: 'Brand Identity',
    description: 'Create memorable brand identities that resonate with your audience and set you apart from the competition.',
    image: 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=1920&q=80',
    badge: '🎨 Creative Design Excellence'
  },
  {
    title: 'Stand Out with',
    highlight: 'Custom Merchandise',
    description: 'Premium promotional products and branded merchandise that leave a lasting impression on your customers.',
    image: 'https://images.unsplash.com/photo-1556155092-490a1ba16284?w=1920&q=80',
    badge: '🎁 Promotional Products'
  },
  {
    title: 'Make an Impact with',
    highlight: 'Professional Signage',
    description: 'Eye-catching signage and displays that maximize your business visibility and attract more customers.',
    image: 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1920&q=80',
    badge: '📢 Signage & Display Solutions'
  }
];

const currentSlide = ref(0);
let slideInterval: ReturnType<typeof setInterval> | null = null;

const nextSlide = () => {
  currentSlide.value = (currentSlide.value + 1) % slides.length;
};

const prevSlide = () => {
  currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
};

const goToSlide = (index: number) => {
  currentSlide.value = index;
};

onMounted(() => {
  slideInterval = setInterval(nextSlide, 7000);
});

onUnmounted(() => {
  if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
  <section class="relative bg-gradient-to-br from-blue-700 via-blue-800 to-gray-900 text-white overflow-hidden">
    <!-- Slideshow Container -->
    <div class="relative h-[600px] md:h-[650px]">
      <!-- Slides -->
      <div
        v-for="(slide, index) in slides"
        :key="index"
        class="absolute inset-0 transition-all duration-1000 ease-out"
        :class="index === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'"
      >
        <!-- Background Image with Ken Burns Effect -->
        <div class="absolute inset-0 overflow-hidden">
          <img
            :src="slide.image"
            :alt="slide.title"
            class="w-full h-full object-cover"
            :style="index === currentSlide ? { animation: 'kenBurnsZoom 10s ease-out forwards' } : {}"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-blue-900/90 via-blue-800/80 to-transparent"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center">
          <div class="max-w-3xl">
            <!-- Badge with Slide-in Animation -->
            <div
              class="inline-block bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full text-sm font-medium mb-6 transition-all duration-1000 ease-out"
              :class="index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
              :style="{ transitionDelay: index === currentSlide ? '200ms' : '0ms' }"
            >
              {{ slide.badge }}
            </div>

            <!-- Title with Slide-in Animation -->
            <h1
              class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 leading-tight transition-all duration-1000 ease-out"
              :class="index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
              :style="{ transitionDelay: index === currentSlide ? '400ms' : '0ms' }"
            >
              {{ slide.title }}
              <span class="text-red-400">{{ slide.highlight }}</span>
            </h1>

            <!-- Description with Slide-in Animation -->
            <p
              class="text-xl text-blue-100 mb-8 max-w-2xl transition-all duration-1000 ease-out"
              :class="index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
              :style="{ transitionDelay: index === currentSlide ? '600ms' : '0ms' }"
            >
              {{ slide.description }}
            </p>

            <!-- Buttons with Slide-in Animation -->
            <div
              class="flex flex-col sm:flex-row gap-4 transition-all duration-1000 ease-out"
              :class="index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
              :style="{ transitionDelay: index === currentSlide ? '800ms' : '0ms' }"
            >
              <Link
                href="/geopamu/services"
                class="inline-flex items-center justify-center bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold hover:bg-gray-100 hover:scale-105 transition-all shadow-lg hover:shadow-xl"
              >
                Explore Services
                <ArrowRightIcon class="ml-2 h-5 w-5" aria-hidden="true" />
              </Link>

              <Link
                href="/geopamu/portfolio"
                class="inline-flex items-center justify-center bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white/10 transition-all"
              >
                View Portfolio
              </Link>
            </div>

            <!-- Stats with Slide-in Animation -->
            <div
              class="grid grid-cols-3 gap-6 mt-12 transition-all duration-1000 ease-out"
              :class="index === currentSlide ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'"
              :style="{ transitionDelay: index === currentSlide ? '1000ms' : '0ms' }"
            >
              <div>
                <div class="text-3xl font-bold">500+</div>
                <div class="text-blue-200 text-sm">Projects</div>
              </div>
              <div>
                <div class="text-3xl font-bold">200+</div>
                <div class="text-blue-200 text-sm">Clients</div>
              </div>
              <div>
                <div class="text-3xl font-bold">10+</div>
                <div class="text-blue-200 text-sm">Years</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Dots -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-3 bg-gradient-to-r from-black/30 via-black/20 to-black/30 backdrop-blur-md px-6 py-3 rounded-full border border-white/10 shadow-2xl">
        <button
          v-for="(_, index) in slides"
          :key="index"
          @click="goToSlide(index)"
          class="transition-all duration-300 rounded-full"
          :class="index === currentSlide ? 'w-10 h-3 bg-white shadow-lg' : 'w-3 h-3 bg-white/40 hover:bg-white/70'"
          :aria-label="`Go to slide ${index + 1}`"
        ></button>
      </div>

      <!-- Navigation Arrows -->
      <button
        @click="prevSlide"
        class="absolute left-4 sm:left-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-16 sm:h-16 bg-white/15 hover:bg-white/25 rounded-full flex items-center justify-center text-white border border-white/20 shadow-2xl group transition-all duration-300 hover:scale-110"
        aria-label="Previous slide"
      >
        <ChevronLeftIcon class="w-6 h-6 sm:w-8 sm:h-8 transition-transform group-hover:-translate-x-1" />
      </button>
      <button
        @click="nextSlide"
        class="absolute right-4 sm:right-8 top-1/2 -translate-y-1/2 z-20 w-12 h-12 sm:w-16 sm:h-16 bg-white/15 hover:bg-white/25 rounded-full flex items-center justify-center text-white border border-white/20 shadow-2xl group transition-all duration-300 hover:scale-110"
        aria-label="Next slide"
      >
        <ChevronRightIcon class="w-6 h-6 sm:w-8 sm:h-8 transition-transform group-hover:translate-x-1" />
      </button>
    </div>
  </section>
</template>

<style scoped>
@keyframes kenBurnsZoom {
  0% {
    transform: scale(1);
  }
  100% {
    transform: scale(1.1);
  }
}
</style>
