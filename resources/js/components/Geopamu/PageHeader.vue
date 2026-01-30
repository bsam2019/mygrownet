<script setup lang="ts">
import { ref, onMounted } from 'vue';

const props = defineProps<{
  title: string;
  subtitle?: string;
  image?: string;
}>();

const headerVisible = ref(false);

onMounted(() => {
  setTimeout(() => {
    headerVisible.value = true;
  }, 100);
});

// Default images for each page if not provided
const defaultImages: Record<string, string> = {
  'Our Services': 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=1600&h=600&fit=crop',
  'Our Portfolio': 'https://images.unsplash.com/photo-1634942537034-2531766767d1?w=1600&h=600&fit=crop',
  'About Geopamu': 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&h=600&fit=crop',
  'Get In Touch': 'https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1600&h=600&fit=crop',
  'Blog': 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1600&h=600&fit=crop'
};

const backgroundImage = props.image || defaultImages[props.title] || 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=1600&h=600&fit=crop';
</script>

<template>
  <div class="relative bg-gray-900 text-white py-24 md:py-32 overflow-hidden">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0">
      <img 
        :src="backgroundImage" 
        :alt="title"
        class="w-full h-full object-cover opacity-40"
      />
      <div class="absolute inset-0 bg-gradient-to-br from-blue-900/90 via-blue-800/85 to-red-900/90"></div>
      
      <!-- Animated Pattern Overlay -->
      <div class="absolute inset-0 opacity-10">
        <div class="absolute inset-0" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 70px);"></div>
      </div>
      
      <!-- Floating Shapes -->
      <div class="absolute top-10 left-10 w-32 h-32 bg-blue-500/20 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute bottom-10 right-10 w-40 h-40 bg-red-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
      <div class="absolute top-1/2 left-1/3 w-24 h-24 bg-white/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Content -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <!-- Decorative Line -->
      <div 
        class="w-20 h-1 bg-gradient-to-r from-transparent via-red-500 to-transparent mx-auto mb-6 transition-all duration-1000"
        :class="headerVisible ? 'opacity-100 scale-x-100' : 'opacity-0 scale-x-0'"
      ></div>
      
      <!-- Title -->
      <h1 
        class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6 transition-all duration-700"
        :class="headerVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
      >
        <span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-100 to-white">
          {{ title }}
        </span>
      </h1>
      
      <!-- Subtitle -->
      <p 
        v-if="subtitle" 
        class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto leading-relaxed transition-all duration-700"
        :class="headerVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        :style="{ transitionDelay: '200ms' }"
      >
        {{ subtitle }}
      </p>
      
      <!-- Decorative Bottom Line -->
      <div 
        class="w-32 h-1 bg-gradient-to-r from-transparent via-blue-400 to-transparent mx-auto mt-8 transition-all duration-1000"
        :class="headerVisible ? 'opacity-100 scale-x-100' : 'opacity-0 scale-x-0'"
        :style="{ transitionDelay: '400ms' }"
      ></div>
    </div>

    <!-- Bottom Wave -->
    <div class="absolute bottom-0 left-0 right-0">
      <svg class="w-full h-16 md:h-24 fill-current text-white" viewBox="0 0 1200 120" preserveAspectRatio="none">
        <path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" opacity=".25"></path>
        <path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" opacity=".5"></path>
        <path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z"></path>
      </svg>
    </div>
  </div>
</template>
