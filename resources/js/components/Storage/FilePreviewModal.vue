<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { XMarkIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import type { StorageFile } from '@/types/storage';

interface Props {
    show: boolean;
    file: StorageFile | null;
    downloadUrl: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    download: [fileId: string];
}>();

const isLoading = ref(true);

const fileType = computed(() => {
    if (!props.file) return 'unknown';
    
    const mime = props.file.mime_type.toLowerCase();
    if (mime.startsWith('image/')) return 'image';
    if (mime === 'application/pdf') return 'pdf';
    if (mime.startsWith('video/')) return 'video';
    if (mime.startsWith('audio/')) return 'audio';
    return 'unsupported';
});

const canPreview = computed(() => {
    return ['image', 'pdf'].includes(fileType.value);
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        isLoading.value = true;
    }
});

const handleLoad = () => {
    isLoading.value = false;
};

const handleDownload = () => {
    if (props.file) {
        emit('download', props.file.id);
    }
};
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
                v-if="show && file"
                class="fixed inset-0 z-50 overflow-y-auto"
                @click.self="emit('close')"
            >
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/75 backdrop-blur-sm"></div>
                
                <!-- Modal -->
                <div class="relative min-h-screen flex items-center justify-center p-4">
                    <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] flex flex-col">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <div class="flex-1 min-w-0 mr-4">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ file.original_name }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ file.formatted_size }} • {{ file.mime_type }}
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <button
                                    @click="handleDownload"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Download file"
                                >
                                    <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                
                                <button
                                    @click="emit('close')"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                                    aria-label="Close preview"
                                >
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 overflow-auto bg-gray-50 p-6">
                            <!-- Loading State -->
                            <div v-if="isLoading && canPreview" class="flex items-center justify-center h-full">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                            </div>
                            
                            <!-- Image Preview -->
                            <div v-if="fileType === 'image' && downloadUrl" class="flex items-center justify-center h-full">
                                <img
                                    :src="downloadUrl"
                                    :alt="file.original_name"
                                    class="max-w-full max-h-full object-contain"
                                    :class="{ 'hidden': isLoading }"
                                    @load="handleLoad"
                                    @error="handleLoad"
                                />
                            </div>
                            
                            <!-- PDF Preview -->
                            <div v-else-if="fileType === 'pdf' && downloadUrl" class="h-full">
                                <iframe
                                    :src="downloadUrl"
                                    class="w-full h-full min-h-[600px] border-0"
                                    :class="{ 'hidden': isLoading }"
                                    @load="handleLoad"
                                ></iframe>
                            </div>
                            
                            <!-- Unsupported File Type -->
                            <div v-else class="flex flex-col items-center justify-center h-full text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-200 mb-4">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <h4 class="text-lg font-medium text-gray-900 mb-2">
                                    Preview not available
                                </h4>
                                <p class="text-sm text-gray-500 mb-6">
                                    This file type cannot be previewed in the browser.
                                </p>
                                <button
                                    @click="handleDownload"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <ArrowDownTrayIcon class="h-5 w-5" aria-hidden="true" />
                                    <span>Download File</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
