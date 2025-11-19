<template>
  <Transition name="slide-up">
    <div
      v-if="showNotification"
      class="fixed bottom-24 md:bottom-8 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow-2xl p-4"
    >
      <div class="flex items-start gap-3">
        <div class="flex-shrink-0 mt-0.5">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </div>
        
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold">Update Available</p>
          <p class="text-xs text-blue-100 mt-0.5">
            A new version of MyGrowNet is ready. Update now for the latest features and improvements.
          </p>
        </div>
      </div>
      
      <div class="flex gap-2 mt-3">
        <button
          @click="applyUpdate"
          class="flex-1 bg-white text-blue-600 font-semibold text-sm py-2 px-4 rounded-lg hover:bg-blue-50 active:bg-blue-100 transition-colors"
        >
          Update Now
        </button>
        <button
          @click="dismissNotification"
          class="px-4 py-2 text-sm text-blue-100 hover:text-white transition-colors"
        >
          Later
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePWA } from '@/composables/usePWA';

const { updateAvailable, applyUpdate: pwaApplyUpdate } = usePWA();
const showNotification = ref(false);

// Watch for update availability
watch(updateAvailable, (available) => {
  if (available) {
    showNotification.value = true;
  }
});

const applyUpdate = () => {
  showNotification.value = false;
  pwaApplyUpdate();
};

const dismissNotification = () => {
  showNotification.value = false;
  // Show again after 5 minutes if still available
  setTimeout(() => {
    if (updateAvailable.value) {
      showNotification.value = true;
    }
  }, 5 * 60 * 1000);
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
