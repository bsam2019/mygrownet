<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 translate-y-4 scale-90"
    enter-to-class="opacity-100 translate-y-0 scale-100"
    leave-active-class="transition-all duration-200 ease-in"
    leave-from-class="opacity-100 translate-y-0 scale-100"
    leave-to-class="opacity-0 translate-y-4 scale-90"
  >
    <button
      v-if="showButton"
      @click="scrollToTop"
      class="fixed bottom-24 right-4 z-40 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 active:scale-95 transition-all min-w-[48px] min-h-[48px] flex items-center justify-center"
      aria-label="Scroll to top"
    >
      <ChevronUpIcon class="h-6 w-6" aria-hidden="true" />
    </button>
  </Transition>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronUpIcon } from '@heroicons/vue/24/outline';

const showButton = ref(false);
let scrollTimeout: number | null = null;

const handleScroll = () => {
  // Debounce scroll event
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
  
  scrollTimeout = window.setTimeout(() => {
    showButton.value = window.scrollY > 300;
  }, 100);
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
  
  // Optional: Haptic feedback on mobile
  if ('vibrate' in navigator) {
    navigator.vibrate(10);
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
  if (scrollTimeout) {
    clearTimeout(scrollTimeout);
  }
});
</script>
