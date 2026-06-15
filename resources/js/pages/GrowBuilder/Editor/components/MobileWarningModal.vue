<script setup lang="ts">
import { DevicePhoneMobileIcon } from '@heroicons/vue/24/outline'

defineProps<{
    show: boolean
}>()

defineEmits<{
    (e: 'close'): void
    (e: 'back'): void
}>()
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full mx-4 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center">
                        <DevicePhoneMobileIcon class="w-8 h-8 text-amber-600" aria-hidden="true" />
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 text-center mb-3">Desktop Recommended</h3>
                <p class="text-gray-600 text-center mb-6">
                    The GrowBuilder editor works best on desktop devices with a larger screen. For the best editing experience, please use a laptop or desktop computer.
                </p>
                <div class="space-y-3">
                    <button
                        @click="$emit('close')"
                        class="w-full px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Continue Anyway
                    </button>
                    <button
                        @click="$emit('back')"
                        class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                    >
                        Back to Dashboard
                    </button>
                </div>
                <p class="text-xs text-gray-500 text-center mt-4">
                    Tip: Your published site is fully mobile-responsive and looks great on all devices!
                </p>
            </div>
        </div>
    </Teleport>
</template>
