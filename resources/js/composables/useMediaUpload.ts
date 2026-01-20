/**
 * Centralized Media Upload Composable
 * Handles file uploads with validation, preview, and error handling
 * Used across all modules: Marketplace, BizBoost, GrowBuilder, etc.
 */

import { ref } from 'vue';
import axios from 'axios';

export interface UploadOptions {
    maxSize?: number; // in bytes, default 5MB
    acceptedTypes?: string[]; // MIME types
    endpoint: string; // API endpoint for upload
    onSuccess?: (url: string, file: File) => void;
    onError?: (error: string) => void;
}

export interface UploadState {
    uploading: boolean;
    progress: number;
    error: string | null;
    previewUrl: string | null;
}

export function useMediaUpload(options: UploadOptions) {
    const {
        maxSize = 5 * 1024 * 1024, // 5MB default
        acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        endpoint,
        onSuccess,
        onError,
    } = options;

    const state = ref<UploadState>({
        uploading: false,
        progress: 0,
        error: null,
        previewUrl: null,
    });

    /**
     * Validate file before upload
     */
    const validateFile = (file: File): string | null => {
        // Check file type
        if (!acceptedTypes.includes(file.type)) {
            return `Invalid file type. Accepted types: ${acceptedTypes.join(', ')}`;
        }

        // Check file size
        if (file.size > maxSize) {
            const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(1);
            return `File size exceeds ${maxSizeMB}MB limit`;
        }

        return null;
    };

    /**
     * Create preview URL for image
     */
    const createPreview = (file: File) => {
        if (file.type.startsWith('image/')) {
            state.value.previewUrl = URL.createObjectURL(file);
        }
    };

    /**
     * Upload file to server
     */
    const upload = async (file: File): Promise<string | null> => {
        // Reset state
        state.value.error = null;
        state.value.progress = 0;

        // Validate file
        const validationError = validateFile(file);
        if (validationError) {
            state.value.error = validationError;
            onError?.(validationError);
            return null;
        }

        // Create preview
        createPreview(file);

        // Prepare form data
        const formData = new FormData();
        formData.append('file', file);

        try {
            state.value.uploading = true;

            const response = await axios.post(endpoint, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        state.value.progress = Math.round(
                            (progressEvent.loaded * 100) / progressEvent.total
                        );
                    }
                },
            });

            const url = response.data.url || response.data.path || response.data.media?.url;
            
            if (!url) {
                throw new Error('No URL returned from server');
            }

            onSuccess?.(url, file);
            return url;
        } catch (error: any) {
            const errorMessage = error.response?.data?.message || error.message || 'Upload failed';
            state.value.error = errorMessage;
            onError?.(errorMessage);
            return null;
        } finally {
            state.value.uploading = false;
        }
    };

    /**
     * Handle file input change
     */
    const handleFileChange = async (event: Event) => {
        const input = event.target as HTMLInputElement;
        const file = input.files?.[0];
        
        if (file) {
            return await upload(file);
        }
        
        return null;
    };

    /**
     * Handle drag and drop
     */
    const handleDrop = async (event: DragEvent) => {
        event.preventDefault();
        const file = event.dataTransfer?.files[0];
        
        if (file) {
            return await upload(file);
        }
        
        return null;
    };

    /**
     * Clear preview and reset state
     */
    const clearPreview = () => {
        if (state.value.previewUrl) {
            URL.revokeObjectURL(state.value.previewUrl);
        }
        state.value.previewUrl = null;
        state.value.error = null;
        state.value.progress = 0;
    };

    return {
        state,
        upload,
        handleFileChange,
        handleDrop,
        clearPreview,
        validateFile,
    };
}
