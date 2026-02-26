import { ref } from 'vue';
import axios from 'axios';
import type { UploadProgress } from '@/types/storage';

export function useStorageUpload() {
    const uploads = ref<UploadProgress[]>([]);

    const updateProgress = (fileId: string, updates: Partial<UploadProgress>) => {
        const index = uploads.value.findIndex(u => u.file_id === fileId);
        if (index !== -1) {
            uploads.value[index] = { ...uploads.value[index], ...updates };
        }
    };

    const uploadFile = async (file: File, folderId: string | null = null) => {
        const uploadId = crypto.randomUUID();
        
        const progress: UploadProgress = {
            file_id: uploadId,
            filename: file.name,
            size: file.size,
            progress: 0,
            status: 'pending',
        };

        uploads.value.push(progress);

        try {
            // Step 1: Initialize upload and get presigned URL
            updateProgress(uploadId, { status: 'pending', progress: 5 });
            
            const initResponse = await axios.post('/api/storage/files/upload-init', {
                folder_id: folderId,
                filename: file.name,
                size: file.size,
                mime_type: file.type || 'application/octet-stream',
            });

            const { file_id, upload_url, s3_key } = initResponse.data;
            updateProgress(uploadId, { file_id, progress: 10 });

            // Step 2: Upload directly to S3 with real progress tracking
            updateProgress(file_id, { status: 'uploading' });
            
            console.log('Starting S3 upload:', {
                url: upload_url,
                filename: file.name,
                size: file.size,
                type: file.type,
            });
            
            await axios.put(upload_url, file, {
                headers: {
                    'Content-Type': file.type || 'application/octet-stream',
                },
                onUploadProgress: (progressEvent) => {
                    if (progressEvent.total) {
                        // Map upload progress from 10% to 90%
                        const uploadPercent = (progressEvent.loaded / progressEvent.total) * 80;
                        const newProgress = Math.round(10 + uploadPercent);
                        updateProgress(file_id, { progress: newProgress });
                        console.log('Upload progress:', newProgress + '%');
                    }
                },
            });
            
            console.log('S3 upload completed successfully');

            // Step 3: Complete upload
            updateProgress(file_id, { status: 'completing', progress: 95 });
            
            await axios.post('/api/storage/files/upload-complete', {
                file_id,
                s3_key,
            });

            updateProgress(file_id, { status: 'completed', progress: 100 });

            // Remove from list after 2 seconds
            setTimeout(() => {
                const index = uploads.value.findIndex((u) => u.file_id === file_id);
                if (index !== -1) {
                    uploads.value.splice(index, 1);
                }
            }, 2000);

            return file_id;
        } catch (err: any) {
            const currentFileId = uploads.value.find(u => u.filename === file.name)?.file_id || uploadId;
            updateProgress(currentFileId, { status: 'error' });
            
            console.error('Upload error:', err);
            
            // Provide detailed error messages
            let errorMessage = 'Upload failed';
            
            if (err.message === 'Network Error') {
                errorMessage = 'Network error - CORS may not be configured';
                console.error('CORS Error: Make sure CORS is configured on your S3 bucket');
            } else if (err.response?.status === 403) {
                errorMessage = 'Access denied - Check S3 credentials';
            } else if (err.response?.status === 404) {
                errorMessage = 'Bucket not found';
            } else if (err.response?.data?.error) {
                errorMessage = err.response.data.error;
            } else if (err.response?.data?.details) {
                errorMessage = JSON.stringify(err.response.data.details);
            } else if (err.message) {
                errorMessage = err.message;
            }
            
            updateProgress(currentFileId, { error: errorMessage });
            
            // Auto-remove failed uploads after 8 seconds
            setTimeout(() => {
                const index = uploads.value.findIndex((u) => u.file_id === currentFileId);
                if (index !== -1) {
                    uploads.value.splice(index, 1);
                }
            }, 8000);
            
            throw err;
        }
    };

    const removeUpload = (fileId: string) => {
        const index = uploads.value.findIndex((u) => u.file_id === fileId);
        if (index !== -1) {
            uploads.value.splice(index, 1);
        }
    };

    const clearCompleted = () => {
        uploads.value = uploads.value.filter((u) => u.status !== 'completed');
    };

    return {
        uploads,
        uploadFile,
        removeUpload,
        clearCompleted,
    };
}
