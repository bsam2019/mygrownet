import { ref } from 'vue';
import axios from 'axios';
import type { StorageFolder, StorageFile, StorageUsage } from '@/types/storage';

export function useStorage() {
    const folders = ref<StorageFolder[]>([]);
    const files = ref<StorageFile[]>([]);
    const usage = ref<StorageUsage | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const fetchContents = async (folderId: string | null = null) => {
        loading.value = true;
        error.value = null;

        try {
            const params = folderId ? { parent_id: folderId } : {};
            const response = await axios.get('/api/storage/folders', { params });
            folders.value = response.data.folders;
            files.value = response.data.files;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to load contents';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const fetchUsage = async () => {
        try {
            const response = await axios.get('/api/storage/usage');
            usage.value = response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to load usage';
            throw err;
        }
    };

    const createFolder = async (name: string, parentId: string | null = null) => {
        try {
            const response = await axios.post('/api/storage/folders', {
                name,
                parent_id: parentId,
            });
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to create folder';
            throw err;
        }
    };

    const renameFolder = async (folderId: string, name: string) => {
        try {
            const response = await axios.patch(`/api/storage/folders/${folderId}`, { name });
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to rename folder';
            throw err;
        }
    };

    const moveFolder = async (folderId: string, newParentId: string | null) => {
        try {
            const response = await axios.post(`/api/storage/folders/${folderId}/move`, {
                new_parent_id: newParentId,
            });
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to move folder';
            throw err;
        }
    };

    const deleteFolder = async (folderId: string) => {
        try {
            await axios.delete(`/api/storage/folders/${folderId}`);
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to delete folder';
            throw err;
        }
    };

    const renameFile = async (fileId: string, name: string) => {
        try {
            const response = await axios.patch(`/api/storage/files/${fileId}`, { name });
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to rename file';
            throw err;
        }
    };

    const moveFile = async (fileId: string, folderId: string | null) => {
        try {
            const response = await axios.post(`/api/storage/files/${fileId}/move`, {
                folder_id: folderId,
            });
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to move file';
            throw err;
        }
    };

    const deleteFile = async (fileId: string) => {
        try {
            await axios.delete(`/api/storage/files/${fileId}`);
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to delete file';
            throw err;
        }
    };

    const downloadFile = async (fileId: string) => {
        try {
            // This function is deprecated - use direct URL instead
            // Kept for backward compatibility
            const response = await axios.get(`/api/storage/files/${fileId}/download`);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || 'Failed to download file';
            throw err;
        }
    };

    return {
        folders,
        files,
        usage,
        loading,
        error,
        fetchContents,
        fetchUsage,
        createFolder,
        renameFolder,
        moveFolder,
        deleteFolder,
        renameFile,
        moveFile,
        deleteFile,
        downloadFile,
    };
}
