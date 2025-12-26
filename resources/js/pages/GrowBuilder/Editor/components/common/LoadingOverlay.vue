<script setup lang="ts">
/**
 * Loading Overlay Component
 * Shows loading state with optional message
 */
defineProps<{
    show: boolean;
    message?: string;
    fullScreen?: boolean;
    transparent?: boolean;
}>();
</script>

<template>
    <Teleport v-if="fullScreen" to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                :class="[
                    'fixed inset-0 z-50 flex flex-col items-center justify-center',
                    transparent ? 'bg-white/80 backdrop-blur-sm' : 'bg-white'
                ]"
            >
                <div class="flex flex-col items-center">
                    <!-- Spinner -->
                    <div class="relative w-12 h-12 mb-4">
                        <div class="absolute inset-0 border-4 border-blue-200 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    
                    <!-- Message -->
                    <p v-if="message" class="text-gray-600 text-sm font-medium">{{ message }}</p>
                </div>
            </div>
        </Transition>
    </Teleport>
    
    <Transition
        v-else
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            :class="[
                'absolute inset-0 z-10 flex flex-col items-center justify-center rounded-lg',
                transparent ? 'bg-white/80 backdrop-blur-sm' : 'bg-white'
            ]"
        >
            <div class="flex flex-col items-center">
                <!-- Spinner -->
                <div class="relative w-10 h-10 mb-3">
                    <div class="absolute inset-0 border-3 border-blue-200 rounded-full"></div>
                    <div class="absolute inset-0 border-3 border-blue-600 rounded-full border-t-transparent animate-spin"></div>
                </div>
                
                <!-- Message -->
                <p v-if="message" class="text-gray-600 text-sm">{{ message }}</p>
            </div>
        </div>
    </Transition>
</template>
