<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 translate-y-4"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-4"
  >
    <div
      v-if="showPrompt"
      class="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-lg shadow-xl border border-gray-200 p-4 z-50"
    >
      <div class="flex items-start gap-3">
        <!-- Icon -->
        <div class="flex-shrink-0">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <ArrowDownTrayIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <h3 class="text-sm font-semibold text-gray-900 mb-1">
            Install CMS App
          </h3>
          <p class="text-xs text-gray-600 mb-3">
            Install our app for quick access and offline support. Works like a native app!
          </p>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button
              @click="install"
              class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition"
            >
              Install
            </button>
            <button
              @click="dismiss"
              class="px-3 py-1.5 bg-white text-gray-700 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition"
            >
              Not Now
            </button>
          </div>
        </div>

        <!-- Close -->
        <button
          @click="dismiss"
          class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 transition"
        >
          <XMarkIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { usePWA } from '@/composables/usePWA';

const { isInstallable, showInstallPrompt } = usePWA();
const showPrompt = ref(false);
const dismissed = ref(false);

onMounted(() => {
  // Check if user dismissed before
  const dismissedAt = localStorage.getItem('pwa-install-dismissed');
  if (dismissedAt) {
    const daysSinceDismissed = (Date.now() - parseInt(dismissedAt)) / (1000 * 60 * 60 * 24);
    if (daysSinceDismissed < 7) {
      dismissed.value = true;
      return;
    }
  }

  // Show prompt after 30 seconds if installable
  setTimeout(() => {
    if (isInstallable.value && !dismissed.value) {
      showPrompt.value = true;
    }
  }, 30000);
});

const install = async () => {
  const installed = await showInstallPrompt();
  if (installed) {
    showPrompt.value = false;
  }
};

const dismiss = () => {
  showPrompt.value = false;
  dismissed.value = true;
  localStorage.setItem('pwa-install-dismissed', Date.now().toString());
};
</script>
