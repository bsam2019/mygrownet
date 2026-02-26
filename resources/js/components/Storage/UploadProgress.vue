<script setup lang="ts">
import { XMarkIcon, CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';
import type { UploadProgress } from '@/types/storage';

interface Props {
    uploads: UploadProgress[];
}

defineProps<Props>();
const emit = defineEmits<{
    remove: [fileId: string];
}>();

const getStatusColor = (status: UploadProgress['status']) => {
    switch (status) {
        case 'completed':
            return 'text-green-600';
        case 'error':
            return 'text-red-600';
        default:
            return 'text-blue-600';
    }
};

const getStatusIcon = (status: UploadProgress['status']) => {
    switch (status) {
        case 'completed':
            return CheckCircleIcon;
        case 'error':
            return ExclamationCircleIcon;
        default:
            return null;
    }
};
</script>

<template>
    <div class="fixed bottom-4 right-4 w-96 max-w-full z-40">
        <div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900">Uploading Files</h3>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
                <div
                    v-for="upload in uploads"
                    :key="upload.file_id"
                    class="px-4 py-3 border-b border-gray-100 last:border-b-0"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                {{ upload.filename }}
                            </p>
                            <p v-if="upload.error" class="text-xs text-red-600 mt-1 whitespace-normal">
                                {{ upload.error }}
                            </p>
                            <p v-if="upload.error && upload.error.includes('CORS')" class="text-xs text-blue-600 mt-1">
                                📖 See: docs/GrowBackup/CORS_SETUP.md
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <component
                                :is="getStatusIcon(upload.status)"
                                v-if="getStatusIcon(upload.status)"
                                :class="['h-5 w-5', getStatusColor(upload.status)]"
                                aria-hidden="true"
                            />
                            
                            <button
                                v-if="upload.status === 'completed' || upload.status === 'error'"
                                @click="emit('remove', upload.file_id)"
                                class="text-gray-400 hover:text-gray-600"
                                aria-label="Remove"
                            >
                                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                    
                    <div v-if="upload.status !== 'completed' && upload.status !== 'error'" class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                            <div
                                class="bg-blue-600 h-1.5 rounded-full transition-all duration-300"
                                :style="{ width: `${upload.progress}%` }"
                            ></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ upload.status === 'completing' ? 'Finalizing...' : upload.status === 'pending' ? 'Preparing...' : `${upload.progress}%` }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
