<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { CloudArrowUpIcon } from '@heroicons/vue/24/outline';

interface Props {
    folderId: string | null;
}

defineProps<Props>();

const emit = defineEmits<{
    filesDropped: [files: FileList];
}>();

const isDragging = ref(false);
const dragCounter = ref(0);

const handleDragEnter = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounter.value++;
    if (e.dataTransfer?.types.includes('Files')) {
        isDragging.value = true;
    }
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    dragCounter.value--;
    if (dragCounter.value === 0) {
        isDragging.value = false;
    }
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.dataTransfer) {
        e.dataTransfer.dropEffect = 'copy';
    }
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    isDragging.value = false;
    dragCounter.value = 0;
    
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        emit('filesDropped', files);
    }
};

onMounted(() => {
    document.addEventListener('dragenter', handleDragEnter);
    document.addEventListener('dragleave', handleDragLeave);
    document.addEventListener('dragover', handleDragOver);
    document.addEventListener('drop', handleDrop);
});

onUnmounted(() => {
    document.removeEventListener('dragenter', handleDragEnter);
    document.removeEventListener('dragleave', handleDragLeave);
    document.removeEventListener('dragover', handleDragOver);
    document.removeEventListener('drop', handleDrop);
});
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isDragging"
            class="fixed inset-0 z-50 bg-blue-600/90 backdrop-blur-sm flex items-center justify-center"
        >
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-white/20 mb-6 animate-bounce">
                    <CloudArrowUpIcon class="h-12 w-12 text-white" aria-hidden="true" />
                </div>
                <h3 class="text-2xl font-semibold text-white mb-2">
                    Drop files to upload
                </h3>
                <p class="text-blue-100">
                    Release to start uploading
                </p>
            </div>
        </div>
    </Transition>
</template>
