<script setup lang="ts">
/**
 * Reusable Media Upload Button Component
 * Provides a flexible upload button with progress tracking and error handling
 * Can be used across all modules for consistent upload UX
 */
import { ref } from 'vue';
import { PhotoIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import { useMediaUpload } from '@/composables/useMediaUpload';

interface Props {
    endpoint: string;
    maxSize?: number;
    acceptedTypes?: string[];
    accept?: string; // HTML accept attribute
    disabled?: boolean;
    variant?: 'button' | 'dashed' | 'icon';
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    maxSize: 5 * 1024 * 1024, // 5MB
    acceptedTypes: () => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    accept: 'image/*',
    disabled: false,
    variant: 'button',
    size: 'md',
});

const emit = defineEmits<{
    (e: 'success', url: string, file: File): void;
    (e: 'error', error: string): void;
    (e: 'uploading', progress: number): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);

const { state, handleFileChange } = useMediaUpload({
    endpoint: props.endpoint,
    maxSize: props.maxSize,
    acceptedTypes: props.acceptedTypes,
    onSuccess: (url, file) => emit('success', url, file),
    onError: (error) => emit('error', error),
});

// Watch progress and emit
const handleChange = async (event: Event) => {
    const result = await handleFileChange(event);
    if (state.value.uploading) {
        emit('uploading', state.value.progress);
    }
};

const triggerUpload = () => {
    fileInput.value?.click();
};

// Size classes
const sizeClasses = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
};

// Variant classes
const variantClasses = {
    button: 'bg-blue-600 text-white hover:bg-blue-700 rounded-lg font-medium transition-colors',
    dashed: 'border-2 border-dashed border-gray-300 hover:border-blue-400 text-gray-600 hover:text-blue-600 rounded-lg transition-colors',
    icon: 'p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors',
};
</script>

<template>
    <div class="inline-block">
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            @change="handleChange"
            class="hidden"
            :disabled="disabled || state.uploading"
        />
        
        <button
            type="button"
            @click="triggerUpload"
            :disabled="disabled || state.uploading"
            :class="[
                sizeClasses[size],
                variantClasses[variant],
                'inline-flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed'
            ]"
        >
            <!-- Loading spinner -->
            <svg
                v-if="state.uploading"
                class="animate-spin h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            
            <!-- Icon slot or default icon -->
            <slot v-else name="icon">
                <PhotoIcon v-if="variant === 'dashed'" class="w-5 h-5" aria-hidden="true" />
                <ArrowUpTrayIcon v-else class="w-5 h-5" aria-hidden="true" />
            </slot>
            
            <!-- Text slot with default -->
            <span v-if="variant !== 'icon'">
                <slot :uploading="state.uploading" :progress="state.progress">
                    {{ state.uploading ? `Uploading... ${state.progress}%` : 'Upload' }}
                </slot>
            </span>
        </button>
        
        <!-- Error message -->
        <p v-if="state.error" class="mt-1 text-xs text-red-600">
            {{ state.error }}
        </p>
    </div>
</template>
