/**
 * Drag & Drop Upload Composable
 * Handle file drops anywhere on the canvas
 */
import { ref, onMounted, onUnmounted } from 'vue';

interface UseDragUploadOptions {
    onUpload: (file: File) => Promise<void>;
    acceptedTypes?: string[];
    maxSize?: number; // in bytes
}

export function useDragUpload(options: UseDragUploadOptions) {
    const {
        onUpload,
        acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        maxSize = 5 * 1024 * 1024, // 5MB default
    } = options;

    const isDraggingFile = ref(false);
    const dragCounter = ref(0);
    const uploadError = ref<string | null>(null);
    const isUploading = ref(false);

    const isValidFile = (file: File): boolean => {
        // Check type
        if (!acceptedTypes.includes(file.type)) {
            uploadError.value = `Invalid file type. Accepted: ${acceptedTypes.map(t => t.split('/')[1]).join(', ')}`;
            return false;
        }

        // Check size
        if (file.size > maxSize) {
            const maxMB = Math.round(maxSize / 1024 / 1024);
            uploadError.value = `File too large. Maximum size: ${maxMB}MB`;
            return false;
        }

        return true;
    };

    const handleDragEnter = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter.value++;

        if (e.dataTransfer?.types.includes('Files')) {
            isDraggingFile.value = true;
        }
    };

    const handleDragLeave = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();
        dragCounter.value--;

        if (dragCounter.value === 0) {
            isDraggingFile.value = false;
        }
    };

    const handleDragOver = (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (e.dataTransfer) {
            e.dataTransfer.dropEffect = 'copy';
        }
    };

    const handleDrop = async (e: DragEvent) => {
        e.preventDefault();
        e.stopPropagation();

        isDraggingFile.value = false;
        dragCounter.value = 0;
        uploadError.value = null;

        const files = e.dataTransfer?.files;
        if (!files || files.length === 0) return;

        const file = files[0];

        if (!isValidFile(file)) {
            return;
        }

        isUploading.value = true;

        try {
            await onUpload(file);
        } catch (error: any) {
            uploadError.value = error.message || 'Upload failed';
        } finally {
            isUploading.value = false;
        }
    };

    // Attach to specific element
    const attachToElement = (element: HTMLElement) => {
        element.addEventListener('dragenter', handleDragEnter);
        element.addEventListener('dragleave', handleDragLeave);
        element.addEventListener('dragover', handleDragOver);
        element.addEventListener('drop', handleDrop);

        return () => {
            element.removeEventListener('dragenter', handleDragEnter);
            element.removeEventListener('dragleave', handleDragLeave);
            element.removeEventListener('dragover', handleDragOver);
            element.removeEventListener('drop', handleDrop);
        };
    };

    // Global document listeners (for full-page drop zone)
    const attachGlobal = () => {
        document.addEventListener('dragenter', handleDragEnter);
        document.addEventListener('dragleave', handleDragLeave);
        document.addEventListener('dragover', handleDragOver);
        document.addEventListener('drop', handleDrop);
    };

    const detachGlobal = () => {
        document.removeEventListener('dragenter', handleDragEnter);
        document.removeEventListener('dragleave', handleDragLeave);
        document.removeEventListener('dragover', handleDragOver);
        document.removeEventListener('drop', handleDrop);
    };

    return {
        isDraggingFile,
        isUploading,
        uploadError,
        attachToElement,
        attachGlobal,
        detachGlobal,
    };
}
