<template>
  <Transition
    enter-active-class="transition ease-out duration-300"
    enter-from-class="translate-y-full opacity-0"
    enter-to-class="translate-y-0 opacity-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="translate-y-0 opacity-100"
    leave-to-class="translate-y-full opacity-0"
  >
    <div
      v-if="showInstallPrompt"
      class="fixed bottom-20 left-4 right-4 bg-white rounded-xl shadow-2xl border border-gray-200 p-4 z-50 md:bottom-4 md:left-auto md:right-4 md:max-w-sm"
    >
      <button
        @click="dismissInstallPrompt"
        class="absolute top-2 right-2 p-1 rounded-full hover:bg-gray-100 transition-colors"
      >
        <XMarkIcon class="h-5 w-5 text-gray-400" />
      </button>
      
      <div class="flex items-start gap-3 pr-6">
        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
          <ArrowDownTrayIcon class="h-6 w-6 text-blue-600" />
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900 mb-1">
            Install MyGrowNet
          </h3>
          <p class="text-xs text-gray-600 mb-3">
            Get quick access and work offline. Install our app for the best experience.
          </p>
          <div class="flex gap-2">
            <button
              @click="installApp"
              class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
            >
              Install
            </button>
            <button
              @click="dismissInstallPrompt"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors"
            >
              Not now
            </button>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ArrowDownTrayIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { usePWA } from '@/composables/usePWA';

const { showInstallPrompt, installApp, dismissInstallPrompt } = usePWA();
</script>
