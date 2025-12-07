<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue';
import { PhotoIcon, XMarkIcon, CloudArrowUpIcon } from '@heroicons/vue/24/outline';

interface Props {
    modelValue: File | File[] | null;
    label?: string;
    currentImage?: string | null;
    multiple?: boolean;
    accept?: string;
    error?: string;
    hint?: string;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    accept: 'image/*',
    multiple: false,
    size: 'md',
});

const emit = defineEmits<{
    'update:modelValue': [value: File | File[] | null];
}>();

const previewUrls = ref<string[]>([]);
const isDragging = ref(false);

const sizeClasses = {
    sm: 'h-20 w-20',
    md: 'h-28 w-28',
    lg: 'h-36 w-36',
};

const iconSizes = {
    sm: 'h-8 w-8',
    md: 'h-10 w-10',
    lg: 'h-12 w-12',
};

const hasPreview = computed(() => {
    return previewUrls.value.length > 0 || props.currentImage;
});

const displayImage = computed(() => {
    if (previewUrls.value.length > 0) return previewUrls.value[0];
    return props.currentImage;
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (!target.files?.length) return;
    processFiles(target.files);
};

const processFiles = (files: FileList) => {
    // Clean up old preview URLs
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
    
    if (props.multiple) {
        const fileArray = Array.from(files);
        previewUrls.value = fileArray.map(f => URL.createObjectURL(f));
        emit('update:modelValue', fileArray);
    } else {
        previewUrls.value = [URL.createObjectURL(files[0])];
        emit('update:modelValue', files[0]);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    if (e.dataTransfer?.files?.length) {
        processFiles(e.dataTransfer.files);
    }
};

const clearImage = () => {
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
    previewUrls.value = [];
    emit('update:modelValue', null);
};

onUnmounted(() => {
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
});
</script>

<template>
    <div class="space-y-2">
        <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ label }}
        </label>
        <div class="flex items-start gap-5">
            <!-- Image Preview -->
            <div
                :class="[
                    'relative rounded-2xl bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center overflow-hidden',
                    'ring-1 ring-gray-200/80 shadow-sm',
                    'dark:from-gray-800 dark:to-gray-700 dark:ring-gray-600/50',
                    sizeClasses[size],
                ]"
            >
                <img
                    v-if="hasPreview"
                    :src="displayImage!"
                    class="h-full w-full object-cover"
                    alt="Preview"
                />
                <PhotoIcon
                    v-else
                    :class="['text-gray-300 dark:text-gray-500', iconSizes[size]]"
                    aria-hidden="true"
                />
                <button
                    v-if="previewUrls.length > 0"
                    type="button"
                    @click="clearImage"
                    class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white shadow-lg hover:bg-red-600 transition-all hover:scale-110"
                    aria-label="Remove image"
                >
                    <XMarkIcon class="h-3.5 w-3.5" aria-hidden="true" />
                </button>
            </div>
            
            <!-- Upload Area -->
            <div class="flex-1">
                <label
                    :class="[
                        'flex flex-col items-center justify-center px-6 py-5 rounded-2xl border-2 border-dashed cursor-pointer transition-all duration-200',
                        isDragging
                            ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20'
                            : 'border-gray-300 bg-gray-50/50 hover:border-violet-400 hover:bg-violet-50/50 dark:border-gray-600 dark:bg-gray-800/50 dark:hover:border-violet-500 dark:hover:bg-violet-900/10',
                    ]"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop"
                >
                    <CloudArrowUpIcon 
                        :class="[
                            'h-8 w-8 mb-2 transition-colors',
                            isDragging ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500'
                        ]" 
                        aria-hidden="true" 
                    />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ hasPreview ? 'Change image' : 'Upload image' }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Drag & drop or click to browse
                    </span>
                    <input
                        type="file"
                        :accept="accept"
                        :multiple="multiple"
                        class="hidden"
                        @change="handleFileChange"
                    />
                </label>
                <p v-if="hint && !error" class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ hint }}
                </p>
            </div>
        </div>
        <p v-if="error" class="flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400 mt-2">
            <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
        
        <!-- Multiple image previews -->
        <div v-if="multiple && previewUrls.length > 1" class="flex gap-3 mt-4 flex-wrap">
            <div
                v-for="(url, index) in previewUrls"
                :key="index"
                class="relative h-16 w-16 rounded-xl overflow-hidden ring-1 ring-gray-200 dark:ring-gray-600 shadow-sm"
            >
                <img :src="url" class="h-full w-full object-cover" :alt="`Preview ${index + 1}`" />
            </div>
        </div>
    </div>
</template>
