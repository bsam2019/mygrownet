<script setup lang="ts">
import { CloudArrowUpIcon, FolderPlusIcon } from '@heroicons/vue/24/outline';

interface Props {
    type: 'root' | 'folder';
    folderName?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    upload: [];
    createFolder: [];
}>();
</script>

<template>
    <div class="text-center py-12 px-4">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
            <CloudArrowUpIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
        </div>
        
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            {{ type === 'root' ? 'Your storage is empty' : `"${folderName}" is empty` }}
        </h3>
        
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
            {{ type === 'root' 
                ? 'Start by uploading your first file or creating a folder to organize your files.' 
                : 'Upload files or create subfolders to organize your content.'
            }}
        </p>
        
        <div class="flex items-center justify-center gap-3">
            <button
                @click="emit('upload')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                <CloudArrowUpIcon class="h-5 w-5" aria-hidden="true" />
                <span>Upload Files</span>
            </button>
            
            <button
                @click="emit('createFolder')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <FolderPlusIcon class="h-5 w-5" aria-hidden="true" />
                <span>New Folder</span>
            </button>
        </div>
        
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-xs text-gray-500 mb-3">You can also:</p>
            <ul class="text-xs text-gray-600 space-y-2 max-w-xs mx-auto text-left">
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Drag and drop files directly into this area</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Upload multiple files at once</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-blue-600 mt-0.5">•</span>
                    <span>Create folders to organize your files</span>
                </li>
            </ul>
        </div>
    </div>
</template>
