<script setup lang="ts">
import { XCircleIcon } from '@heroicons/vue/24/outline';
import ShareFooter from '@/Components/Storage/ShareFooter.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 mb-4">
                    <span class="text-4xl">☁️</span>
                    <h1 class="text-2xl font-bold text-gray-900">GrowBackup</h1>
                </div>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <!-- Error Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center">
                        <XCircleIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
                    </div>
                </div>

                <!-- Message -->
                <h2 class="text-xl font-semibold text-gray-900 mb-2">
                    Link Expired or Unavailable
                </h2>
                <p class="text-gray-600 mb-6">
                    This share link has expired, reached its download limit, or has been deleted by the owner.
                </p>

                <!-- Actions -->
                <div class="space-y-3">
                    <p class="text-sm text-gray-500">
                        If you need access to this file, please contact the person who shared it with you.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <ShareFooter />
        </div>
    </div>
</template>
