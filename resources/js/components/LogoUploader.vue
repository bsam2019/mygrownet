<script setup lang="ts">
/**
 * Logo Uploader Component
 * Specialized component for uploading and displaying logos
 * Includes preview, remove functionality, and drag-and-drop support
 */
import { ref, computed } from 'vue';
import { XMarkIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { useMediaUpload } from '@/composables/useMediaUpload';

interface Props {
    modelValue?: string | null;
    endpoint: string;
    maxSize?: number;
    label?: string;
    hint?: string;
    required?: boolean;
    disabled?: boolean;
    aspectRatio?: 'square' | 'wide' | 'auto';
    showRemove?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    maxSize: 5 * 1024 * 1024, // 5MB
    label: 'Logo',
    hint: 'PNG, JPG, or SVG up to 5MB',
    required: false,
    disabled: false,
    aspectRatio: 'auto',
    showRemove: true,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
    (e: 'uploaded', url: string): void;
    (e: 'removed'): void;
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

const { state, upload, handleDrop, clearPreview } = useMediaUpload({
    endpoint: props.endpoint,
    maxSize: props.maxSize,
    acceptedTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
    onSuccess: (url) => {
        emit('update:modelValue', url);
        emit('uploaded', url);
    },
});

const currentLogo = computed(() => props.modelValue || state.value.previewUrl);

const handleFileChange = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (file) {
        await upload(file);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const handleDropEvent = async (event: DragEvent) => {
    isDragging.value = false;
    await handleDrop(event);
};

const removeLogo = () => {
    emit('update:modelValue', null);
    emit('removed');
    clearPreview();
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerUpload = () => {
    fileInput.value?.click();
};

// Aspect ratio classes
const aspectClasses = {
    square: 'aspect-square',
    wide: 'aspect-video',
    auto: 'aspect-auto',
};
</script>

<template>
    <div class="space-y-2">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>
        
        <!-- Upload Area -->
        <div
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDropEvent"
            :class="[
                'relative rounded-lg border-2 transition-all',
                isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]"
        >
            <!-- Hidden file input -->
            <input
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
                :disabled="disabled || state.uploading"
            />
            
            <!-- Preview with logo -->
            <div v-if="currentLogo" class="relative">
                <div :class="['w-full overflow-hidden rounded-lg bg-gray-50 p-4', aspectClasses[aspectRatio]]">
                    <img
                        :src="currentLogo"
                        :alt="label"
                        class="w-full h-full object-contain"
                    />
                </div>
                
                <!-- Remove button -->
                <button
                    v-if="showRemove && !disabled"
                    type="button"
                    @click.stop="removeLogo"
                    class="absolute top-2 right-2 p-1.5 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors shadow-lg"
                    aria-label="Remove logo"
                >
                    <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                </button>
                
                <!-- Change button overlay -->
                <div
                    v-if="!disabled"
                    @click="triggerUpload"
                    class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 hover:opacity-100 rounded-lg"
                >
                    <span class="px-4 py-2 bg-white text-gray-900 rounded-lg font-medium text-sm shadow-lg">
                        Change Logo
                    </span>
                </div>
            </div>
            
            <!-- Empty state / Upload prompt -->
            <div
                v-else
                @click="triggerUpload"
                class="flex flex-col items-center justify-center py-12 px-4"
            >
                <!-- Loading state -->
                <div v-if="state.uploading" class="text-center">
                    <svg class="w-12 h-12 mx-auto animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-3 text-sm text-gray-600">Uploading... {{ state.progress }}%</p>
                </div>
                
                <!-- Upload prompt -->
                <div v-else class="text-center">
                    <PhotoIcon class="w-12 h-12 mx-auto text-gray-400" aria-hidden="true" />
                    <p class="mt-3 text-sm font-medium text-gray-700">
                        <slot name="prompt">
                            Click to upload or drag and drop
                        </slot>
                    </p>
                    <p v-if="hint" class="mt-1 text-xs text-gray-500">{{ hint }}</p>
                </div>
            </div>
        </div>
        
        <!-- Error message -->
        <p v-if="state.error" class="text-xs text-red-600">
            {{ state.error }}
        </p>
    </div>
</template>
