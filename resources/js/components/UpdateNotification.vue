<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div
                v-if="hasUpdate"
                class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200 p-4"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <ArrowPathIcon class="h-6 w-6 text-blue-600" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-1">
                            Update Available
                        </h3>
                        <p class="text-sm text-gray-600 mb-3">
                            A new version of MyGrowNet is available. Refresh to get the latest features and improvements.
                        </p>
                        <div class="flex gap-2">
                            <button
                                @click="applyUpdate"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                Refresh Now
                            </button>
                            <button
                                @click="dismissUpdate"
                                class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                Later
                            </button>
                        </div>
                    </div>
                    <button
                        @click="dismissUpdate"
                        class="flex-shrink-0 text-gray-400 hover:text-gray-600"
                        aria-label="Close"
                    >
                        <XMarkIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup lang="ts">
import { ArrowPathIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { useVersionCheck } from '@/composables/useVersionCheck';

const { hasUpdate, applyUpdate, dismissUpdate } = useVersionCheck();
</script>
