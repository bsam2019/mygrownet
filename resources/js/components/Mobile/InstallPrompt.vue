<template>
  <Transition name="slide-up">
    <div
      v-if="showPrompt"
      class="fixed bottom-24 md:bottom-8 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-white rounded-lg shadow-2xl overflow-hidden"
    >
      <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
        <div class="flex items-start gap-3">
          <div class="flex-shrink-0">
            <img src="/logo.png" alt="MyGrowNet" class="w-12 h-12 rounded-lg" />
          </div>
          
          <div class="flex-1 min-w-0 text-white">
            <p class="text-sm font-semibold">Install MyGrowNet</p>
            <p class="text-xs text-blue-100 mt-0.5">
              Get quick access and work offline
            </p>
          </div>
          
          <button
            @click="dismiss"
            class="flex-shrink-0 text-white hover:text-blue-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <div class="p-4 bg-gray-50">
        <div class="space-y-2 mb-4">
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Works offline</span>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Faster loading</span>
          </div>
          <div class="flex items-center gap-2 text-sm text-gray-700">
            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>Home screen access</span>
          </div>
        </div>
        
        <button
          @click="install"
          class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold py-3 px-4 rounded-lg hover:from-blue-600 hover:to-blue-700 active:from-blue-700 active:to-blue-800 transition-all shadow-md"
        >
          Install App
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { usePWA } from '@/composables/usePWA';

const { showInstallPrompt, installApp, dismissInstallPrompt, isInstalled, isStandalone } = usePWA();
const showPrompt = ref(false);

// Watch for install prompt availability
watch(showInstallPrompt, (show) => {
  // Only show if not already installed
  if (!isInstalled.value && !isStandalone.value) {
    showPrompt.value = show;
  } else {
    showPrompt.value = false;
  }
});

// Watch for installation status changes
watch([isInstalled, isStandalone], ([installed, standalone]) => {
  if (installed || standalone) {
    showPrompt.value = false;
  }
});

onMounted(() => {
  // Hide prompt if already installed
  if (isInstalled.value || isStandalone.value) {
    showPrompt.value = false;
  }
});

const install = async () => {
  await installApp();
  showPrompt.value = false;
};

const dismiss = () => {
  dismissInstallPrompt();
  showPrompt.value = false;
};
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from {
  transform: translateY(100%);
  opacity: 0;
}

.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
