<script setup lang="ts">
import { ref } from 'vue';
import { ArrowUpTrayIcon } from '@heroicons/vue/24/outline';

interface Props {
    folderId?: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    upload: [files: FileList];
}>();

const fileInput = ref<HTMLInputElement>();
const isDragging = ref(false);

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        emit('upload', target.files);
        target.value = ''; // Reset input
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        emit('upload', event.dataTransfer.files);
    }
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

// Expose method for parent component
defineExpose({
    triggerFileInput,
});
</script>

<template>
    <div>
        <input
            ref="fileInput"
            type="file"
            multiple
            class="hidden"
            @change="handleFileSelect"
        />
        
        <button
            @click="triggerFileInput"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            aria-label="Upload files"
        >
            <ArrowUpTrayIcon class="h-5 w-5" aria-hidden="true" />
            <span>Upload</span>
        </button>
        
        <!-- Drag and drop overlay -->
        <div
            v-if="isDragging"
            @drop.prevent="handleDrop"
            @dragover.prevent="handleDragOver"
            @dragleave="handleDragLeave"
            class="fixed inset-0 bg-blue-600 bg-opacity-20 backdrop-blur-sm z-50 flex items-center justify-center"
        >
            <div class="bg-white rounded-lg shadow-xl p-8 text-center">
                <ArrowUpTrayIcon class="h-16 w-16 mx-auto text-blue-600 mb-4" aria-hidden="true" />
                <p class="text-lg font-semibold text-gray-900">Drop files to upload</p>
            </div>
        </div>
    </div>
</template>
