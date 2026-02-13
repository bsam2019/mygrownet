<template>
  <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <!-- Offline Icon -->
      <div class="mb-6">
        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto">
          <WifiIcon class="h-12 w-12 text-gray-400" aria-hidden="true" />
        </div>
      </div>

      <!-- Title -->
      <h1 class="text-2xl font-bold text-gray-900 mb-2">
        You're Offline
      </h1>

      <!-- Description -->
      <p class="text-gray-600 mb-8">
        It looks like you've lost your internet connection. Don't worry, your data is safe and will sync when you're back online.
      </p>

      <!-- Status -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-center gap-3 mb-4">
          <div class="w-3 h-3 bg-red-500 rounded-full animate-pulse"></div>
          <span class="text-sm font-medium text-gray-700">No Connection</span>
        </div>
        
        <p class="text-xs text-gray-500">
          Checking connection status...
        </p>
      </div>

      <!-- Actions -->
      <div class="space-y-3">
        <button
          @click="retry"
          class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
        >
          Try Again
        </button>

        <button
          @click="goToDashboard"
          class="w-full px-4 py-3 bg-white text-gray-700 rounded-lg font-medium border border-gray-300 hover:bg-gray-50 transition"
        >
          Go to Dashboard
        </button>
      </div>

      <!-- Tips -->
      <div class="mt-8 text-left bg-blue-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">
          While you're offline:
        </h3>
        <ul class="text-xs text-blue-800 space-y-1">
          <li>• View cached pages and data</li>
          <li>• Your changes will sync automatically</li>
          <li>• Some features may be limited</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { WifiIcon } from '@heroicons/vue/24/outline';

const retry = () => {
  window.location.reload();
};

const goToDashboard = () => {
  router.visit('/cms/dashboard');
};
</script>
