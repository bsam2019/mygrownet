<script setup lang="ts">
import { computed, watch } from 'vue';
import {
    FolderIcon,
    DocumentIcon,
    PhotoIcon,
    VideoCameraIcon,
    MusicalNoteIcon,
    EllipsisVerticalIcon,
} from '@heroicons/vue/24/outline';
import type { StorageFolder, StorageFile } from '@/types/storage';
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';

interface Props {
    folders: StorageFolder[];
    files: StorageFile[];
    loading?: boolean;
    selectionMode?: boolean;
    selectedFiles?: Set<string>;
    selectedFolders?: Set<string>;
}

const props = withDefaults(defineProps<Props>(), {
    selectionMode: false,
    selectedFiles: () => new Set(),
    selectedFolders: () => new Set(),
});

const emit = defineEmits<{
    folderClick: [folderId: string, folderName: string];
    fileClick: [file: StorageFile];
    fileDownload: [fileId: string];
    fileRename: [fileId: string, currentName: string];
    fileMove: [fileId: string];
    fileDelete: [fileId: string];
    fileShare: [fileId: string];
    folderRename: [folderId: string, currentName: string];
    folderMove: [folderId: string];
    folderDelete: [folderId: string];
    toggleFileSelection: [fileId: string];
    toggleFolderSelection: [folderId: string];
}>();

const getFileIcon = (mimeType: string) => {
    if (mimeType.startsWith('image/')) return PhotoIcon;
    if (mimeType.startsWith('video/')) return VideoCameraIcon;
    if (mimeType.startsWith('audio/')) return MusicalNoteIcon;
    return DocumentIcon;
};

const isEmpty = computed(() => props.folders.length === 0 && props.files.length === 0);

// Debug: Watch selectionMode prop
watch(() => props.selectionMode, (newValue) => {
    console.log('FileList selectionMode changed:', newValue);
}, { immediate: true });
</script>

<template>
    <div class="min-h-96">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="isEmpty" class="flex flex-col items-center justify-center py-12 text-center">
            <FolderIcon class="h-16 w-16 text-gray-300 mb-4" aria-hidden="true" />
            <h3 class="text-lg font-medium text-gray-900 mb-1">No files yet</h3>
            <p class="text-sm text-gray-500">Upload your first file to get started</p>
        </div>
        
        <!-- Content -->
        <div v-else class="divide-y divide-gray-200">
            <!-- Folders -->
            <div
                v-for="folder in folders"
                :key="folder.id"
                class="flex items-center gap-3 p-4 hover:bg-gray-50 group"
                :class="{ 'bg-blue-50': selectionMode && selectedFolders.has(folder.id) }"
            >
                <!-- Checkbox (selection mode) -->
                <div v-if="selectionMode" class="flex-shrink-0">
                    <input
                        type="checkbox"
                        :checked="selectedFolders.has(folder.id)"
                        @change="emit('toggleFolderSelection', folder.id)"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                </div>
                
                <div
                    @click="!selectionMode && emit('folderClick', folder.id, folder.name)"
                    class="flex items-center gap-3 flex-1 min-w-0"
                    :class="{ 'cursor-pointer': !selectionMode }"
                >
                    <FolderIcon class="h-6 w-6 text-blue-500 flex-shrink-0" aria-hidden="true" />
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ folder.name }}</p>
                        <p class="text-xs text-gray-500">Folder</p>
                    </div>
                </div>
                
                <Menu v-if="!selectionMode" as="div" class="relative">
                    <MenuButton class="p-2 rounded-lg hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity">
                        <EllipsisVerticalIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </MenuButton>
                    
                    <MenuItems class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('folderRename', folder.id, folder.name)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Rename
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('folderMove', folder.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Move
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('folderDelete', folder.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-red-600']"
                            >
                                Delete
                            </button>
                        </MenuItem>
                    </MenuItems>
                </Menu>
            </div>
            
            <!-- Files -->
            <div
                v-for="file in files"
                :key="file.id"
                class="flex items-center gap-3 p-4 hover:bg-gray-50 group"
                :class="{ 'bg-blue-50': selectionMode && selectedFiles.has(file.id) }"
            >
                <!-- Checkbox (selection mode) -->
                <div v-if="selectionMode" class="flex-shrink-0">
                    <input
                        type="checkbox"
                        :checked="selectedFiles.has(file.id)"
                        @change="emit('toggleFileSelection', file.id)"
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    />
                </div>
                
                <div 
                    @click="!selectionMode && emit('fileClick', file)"
                    class="flex items-center gap-3 flex-1 min-w-0"
                    :class="{ 'cursor-pointer': !selectionMode }"
                >
                    <component
                        :is="getFileIcon(file.mime_type)"
                        class="h-6 w-6 text-gray-400 flex-shrink-0"
                        aria-hidden="true"
                    />
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ file.original_name }}</p>
                        <p class="text-xs text-gray-500">{{ file.formatted_size }}</p>
                    </div>
                </div>
                
                <Menu v-if="!selectionMode" as="div" class="relative">
                    <MenuButton class="p-2 rounded-lg hover:bg-gray-100 opacity-0 group-hover:opacity-100 transition-opacity">
                        <EllipsisVerticalIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                    </MenuButton>
                    
                    <MenuItems class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('fileDownload', file.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Download
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('fileShare', file.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Share
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('fileRename', file.id, file.original_name)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Rename
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('fileMove', file.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-gray-700']"
                            >
                                Move
                            </button>
                        </MenuItem>
                        <MenuItem v-slot="{ active }">
                            <button
                                @click="emit('fileDelete', file.id)"
                                :class="[active ? 'bg-gray-50' : '', 'w-full text-left px-4 py-2 text-sm text-red-600']"
                            >
                                Delete
                            </button>
                        </MenuItem>
                    </MenuItems>
                </Menu>
            </div>
        </div>
    </div>
</template>
