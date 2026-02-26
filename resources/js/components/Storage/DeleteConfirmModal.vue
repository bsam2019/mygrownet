<script setup lang="ts">
import { ExclamationTriangleIcon, XMarkIcon } from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    isDeleting?: boolean;
}

withDefaults(defineProps<Props>(), {
    confirmText: 'Delete',
    cancelText: 'Cancel',
    isDeleting: false,
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Teleport to="body">
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
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="emit('cancel')"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
                
                <!-- Modal -->
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full">
                        <!-- Header -->
                        <div class="flex items-start gap-4 p-6">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                                <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ title }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ message }}
                                </p>
                            </div>
                            
                            <button
                                @click="emit('cancel')"
                                class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 rounded-lg transition-colors"
                                aria-label="Close"
                            >
                                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 rounded-b-lg">
                            <button
                                @click="emit('cancel')"
                                :disabled="isDeleting"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ cancelText }}
                            </button>
                            
                            <button
                                @click="emit('confirm')"
                                :disabled="isDeleting"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <span v-if="isDeleting" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span>{{ isDeleting ? 'Deleting...' : confirmText }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
