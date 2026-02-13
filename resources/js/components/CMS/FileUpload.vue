<script setup lang="ts">
import { ref, computed } from 'vue';
import { ArrowUpTrayIcon, XMarkIcon, DocumentIcon, PhotoIcon } from '@heroicons/vue/24/outline';

interface Props {
    modelValue?: File | null;
    accept?: string;
    maxSize?: number; // in MB
    label?: string;
    error?: string;
    preview?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    accept: 'image/*,application/pdf,.doc,.docx',
    maxSize: 5,
    label: 'Upload File',
    preview: true,
});

const emit = defineEmits<{
    'update:modelValue': [file: File | null];
    'error': [message: string];
}>();

const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string | null>(null);

const selectedFile = computed(() => props.modelValue);

const isImage = computed(() => {
    if (!selectedFile.value) return false;
    return selectedFile.value.type.startsWith('image/');
});

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const validateFile = (file: File): string | null => {
    // Check file size
    const maxBytes = props.maxSize * 1024 * 1024;
    if (file.size > maxBytes) {
        return `File size must be less than ${props.maxSize}MB`;
    }

    // Check file type
    if (props.accept !== '*') {
        const acceptedTypes = props.accept.split(',').map(t => t.trim());
        const fileExtension = '.' + file.name.split('.').pop()?.toLowerCase();
        const fileType = file.type;

        const isAccepted = acceptedTypes.some(type => {
            if (type.startsWith('.')) {
                return fileExtension === type.toLowerCase();
            }
            if (type.endsWith('/*')) {
                return fileType.startsWith(type.replace('/*', ''));
            }
            return fileType === type;
        });

        if (!isAccepted) {
            return 'File type not accepted';
        }
    }

    return null;
};

const handleFileSelect = (file: File) => {
    const error = validateFile(file);
    if (error) {
        emit('error', error);
        return;
    }

    emit('update:modelValue', file);

    // Generate preview for images
    if (props.preview && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleDrop = (e: DragEvent) => {
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        handleFileSelect(files[0]);
    }
};

const handleFileInputChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    const files = target.files;
    if (files && files.length > 0) {
        handleFileSelect(files[0]);
    }
};

const removeFile = () => {
    emit('update:modelValue', null);
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const openFileDialog = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
            {{ label }}
        </label>

        <!-- File Selected State -->
        <div v-if="selectedFile" class="space-y-3">
            <!-- Image Preview -->
            <div v-if="isImage && previewUrl" class="relative">
                <img
                    :src="previewUrl"
                    :alt="selectedFile.name"
                    class="w-full h-48 object-cover rounded-lg border border-gray-300"
                />
                <button
                    type="button"
                    @click="removeFile"
                    class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full hover:bg-red-700"
                    aria-label="Remove file"
                >
                    <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                </button>
            </div>

            <!-- File Info -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-300">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div class="flex-shrink-0">
                        <PhotoIcon v-if="isImage" class="h-8 w-8 text-blue-600" aria-hidden="true" />
                        <DocumentIcon v-else class="h-8 w-8 text-gray-600" aria-hidden="true" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ selectedFile.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ formatFileSize(selectedFile.size) }}
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="removeFile"
                    class="flex-shrink-0 ml-3 text-red-600 hover:text-red-800"
                    aria-label="Remove file"
                >
                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Upload Area -->
        <div
            v-else
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @click="openFileDialog"
            :class="[
                'relative border-2 border-dashed rounded-lg p-6 transition-colors cursor-pointer',
                isDragging
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 hover:border-gray-400 bg-gray-50 hover:bg-gray-100',
                error ? 'border-red-300 bg-red-50' : '',
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                @change="handleFileInputChange"
                class="hidden"
            />

            <div class="text-center">
                <ArrowUpTrayIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <p class="mt-2 text-sm font-medium text-gray-900">
                    {{ isDragging ? 'Drop file here' : 'Click to upload or drag and drop' }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                    Maximum file size: {{ maxSize }}MB
                </p>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
