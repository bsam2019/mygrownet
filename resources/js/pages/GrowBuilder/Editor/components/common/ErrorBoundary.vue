<script setup lang="ts">
/**
 * Error Boundary Component
 * Catches errors in child components and displays recovery UI
 */
import { ref, onErrorCaptured } from 'vue';
import { ExclamationTriangleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    fallbackMessage?: string;
}>();

const emit = defineEmits<{
    (e: 'error', error: Error): void;
    (e: 'retry'): void;
}>();

const hasError = ref(false);
const errorMessage = ref('');
const errorStack = ref('');

// Capture errors from child components
onErrorCaptured((error: Error, instance, info) => {
    hasError.value = true;
    errorMessage.value = error.message || 'An unexpected error occurred';
    errorStack.value = error.stack || '';
    
    emit('error', error);
    
    // Log error for debugging
    console.error('ErrorBoundary caught:', error, info);
    
    // Return false to stop error propagation
    return false;
});

// Reset error state and retry
const retry = () => {
    hasError.value = false;
    errorMessage.value = '';
    errorStack.value = '';
    emit('retry');
};

// Copy error details to clipboard
const copyError = async () => {
    const errorDetails = `Error: ${errorMessage.value}\n\nStack:\n${errorStack.value}`;
    try {
        await navigator.clipboard.writeText(errorDetails);
    } catch (e) {
        console.error('Failed to copy error:', e);
    }
};
</script>

<template>
    <div v-if="hasError" class="flex flex-col items-center justify-center p-8 bg-red-50 border border-red-200 rounded-xl">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
            <ExclamationTriangleIcon class="w-8 h-8 text-red-600" aria-hidden="true" />
        </div>
        
        <h3 class="text-lg font-semibold text-red-900 mb-2">Something went wrong</h3>
        
        <p class="text-sm text-red-700 text-center mb-4 max-w-md">
            {{ fallbackMessage || errorMessage }}
        </p>
        
        <div class="flex items-center gap-3">
            <button
                @click="retry"
                class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
                <ArrowPathIcon class="w-4 h-4" aria-hidden="true" />
                Try Again
            </button>
            
            <button
                @click="copyError"
                class="px-4 py-2 text-red-700 border border-red-300 rounded-lg hover:bg-red-100 transition-colors"
            >
                Copy Error
            </button>
        </div>
        
        <!-- Expandable Error Details (for developers) -->
        <details class="mt-4 w-full max-w-lg">
            <summary class="text-xs text-red-600 cursor-pointer hover:underline">
                Technical Details
            </summary>
            <pre class="mt-2 p-3 bg-red-100 rounded-lg text-xs text-red-800 overflow-auto max-h-32">{{ errorStack }}</pre>
        </details>
    </div>
    
    <slot v-else></slot>
</template>
