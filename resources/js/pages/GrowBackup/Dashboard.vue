<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import { FolderPlusIcon, ChevronRightIcon, HomeIcon, FolderIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';
import GrowBackupLayout from '@/Layouts/GrowBackupLayout.vue';
import FileList from '@/Components/Storage/FileList.vue';
import UploadButton from '@/Components/Storage/UploadButton.vue';
import UploadProgress from '@/Components/Storage/UploadProgress.vue';
import UsageIndicator from '@/Components/Storage/UsageIndicator.vue';
import CreateFolderModal from '@/Components/Storage/CreateFolderModal.vue';
import EmptyState from '@/Components/Storage/EmptyState.vue';
import DropZone from '@/Components/Storage/DropZone.vue';
import FileListSkeleton from '@/Components/Storage/FileListSkeleton.vue';
import FilePreviewModal from '@/Components/Storage/FilePreviewModal.vue';
import DeleteConfirmModal from '@/Components/Storage/DeleteConfirmModal.vue';
import BulkActionsToolbar from '@/Components/Storage/BulkActionsToolbar.vue';
import ShareModal from '@/Components/Storage/ShareModal.vue';
import { useStorage } from '@/Composables/useStorage';
import { useStorageUpload } from '@/Composables/useStorageUpload';
import { useFileSelection } from '@/Composables/useFileSelection';
import { useToast } from '@/composables/useToast';
import type { StorageFile } from '@/types/storage';

const {
    folders,
    files,
    usage,
    loading,
    fetchContents,
    fetchUsage,
    createFolder,
    renameFile,
    renameFolder,
    deleteFile,
    deleteFolder,
    downloadFile,
} = useStorage();

const { uploads, uploadFile, removeUpload } = useStorageUpload();
const { 
    selectedFiles, 
    selectedFolders, 
    hasSelection, 
    selectionCount,
    toggleFileSelection,
    toggleFolderSelection,
    selectAll,
    clearSelection,
    isFileSelected,
    isFolderSelected,
} = useFileSelection();
const { toast } = useToast();

// Debug: Watch uploads array
watch(uploads, (newUploads) => {
    console.log('Uploads changed:', newUploads.length, newUploads);
}, { deep: true });

// SPA state management
const currentFolderId = ref<string | null>(null);
const folderHistory = ref<Array<{ id: string | null; name: string }>>([
    { id: null, name: 'My Storage' }
]);
const showCreateFolderModal = ref(false);
const showPreviewModal = ref(false);
const previewFile = ref<StorageFile | null>(null);
const previewUrl = ref<string | null>(null);
const uploadButtonRef = ref<InstanceType<typeof UploadButton> | null>(null);
const showDeleteModal = ref(false);
const deleteTarget = ref<{ type: 'file' | 'folder' | 'bulk', id?: string, name?: string } | null>(null);
const isDeleting = ref(false);
const selectionMode = ref(false);
const showShareModal = ref(false);
const shareFile = ref<{ id: string; original_name: string } | null>(null);

// Computed
const isEmpty = computed(() => {
    return !loading.value && folders.value.length === 0 && files.value.length === 0;
});

const currentFolderName = computed(() => {
    return folderHistory.value[folderHistory.value.length - 1]?.name || 'My Storage';
});

const allItemIds = computed(() => ({
    files: files.value.map(f => f.id),
    folders: folders.value.map(f => f.id),
}));

const deleteModalTitle = computed(() => {
    if (!deleteTarget.value) return '';
    if (deleteTarget.value.type === 'bulk') {
        return `Delete ${selectionCount.value} items?`;
    }
    return `Delete ${deleteTarget.value.type === 'file' ? 'file' : 'folder'}?`;
});

const deleteModalMessage = computed(() => {
    if (!deleteTarget.value) return '';
    if (deleteTarget.value.type === 'bulk') {
        return `Are you sure you want to delete ${selectionCount.value} selected items? This action cannot be undone.`;
    }
    return `Are you sure you want to delete "${deleteTarget.value.name}"? This action cannot be undone.`;
});

onMounted(async () => {
    await loadFolder(null);
});

const loadFolder = async (folderId: string | null) => {
    try {
        await Promise.all([
            fetchContents(folderId),
            fetchUsage(),
        ]);
    } catch (error: any) {
        console.error('Failed to load folder:', error);
        const errorMessage = error.response?.data?.error || error.message || 'Failed to load folder';
        toast.error(errorMessage);
    }
};

const handleFolderClick = async (folderId: string, folderName: string) => {
    currentFolderId.value = folderId;
    folderHistory.value.push({ id: folderId, name: folderName });
    await loadFolder(folderId);
};

const navigateToBreadcrumb = async (index: number) => {
    const targetFolder = folderHistory.value[index];
    currentFolderId.value = targetFolder.id;
    folderHistory.value = folderHistory.value.slice(0, index + 1);
    await loadFolder(targetFolder.id);
};

const handleCreateFolder = async (name: string) => {
    try {
        await createFolder(name, currentFolderId.value);
        await fetchContents(currentFolderId.value);
        toast.success('Folder created successfully');
        showCreateFolderModal.value = false;
    } catch (error: any) {
        console.error('Failed to create folder:', error);
        const errorMessage = error.response?.data?.error || error.message || 'Failed to create folder';
        toast.error(errorMessage);
    }
};

const handleUpload = async (fileList: FileList) => {
    const files = Array.from(fileList);
    
    console.log('Starting upload for', files.length, 'files');
    console.log('Current uploads:', uploads.value.length);
    
    for (const file of files) {
        try {
            console.log('Uploading:', file.name);
            await uploadFile(file, currentFolderId.value);
            console.log('Upload completed:', file.name);
            toast.success(`${file.name} uploaded successfully`);
        } catch (error: any) {
            console.error('Upload failed:', error);
            const errorMessage = error.response?.data?.error || error.response?.data?.details || error.message || 'Upload failed';
            toast.error(`${file.name}: ${errorMessage}`);
        }
    }
    
    console.log('All uploads finished, refreshing...');
    // Refresh after all uploads
    await fetchContents(currentFolderId.value);
    await fetchUsage();
};

const handleFilesDropped = (fileList: FileList) => {
    handleUpload(fileList);
};

const triggerUpload = () => {
    uploadButtonRef.value?.triggerFileInput();
};

const handleFileClick = async (file: StorageFile) => {
    // Use stream endpoint for preview (hides S3 URLs)
    const streamUrl = `/api/storage/files/${file.id}/stream`;
    previewFile.value = file;
    previewUrl.value = streamUrl;
    showPreviewModal.value = true;
};

const handleRename = async (id: string, newName: string, type: 'file' | 'folder') => {
    try {
        if (type === 'file') {
            await renameFile(id, newName);
        } else {
            await renameFolder(id, newName);
        }
        await fetchContents(currentFolderId.value);
        toast.success(`${type === 'file' ? 'File' : 'Folder'} renamed successfully`);
    } catch (error: any) {
        console.error('Failed to rename:', error);
        const errorMessage = error.response?.data?.error || error.message || 'Failed to rename';
        toast.error(errorMessage);
    }
};

const handleDelete = async (id: string, type: 'file' | 'folder') => {
    const item = type === 'file' 
        ? files.value.find(f => f.id === id)
        : folders.value.find(f => f.id === id);
    
    deleteTarget.value = {
        type,
        id,
        name: item?.original_name || item?.name || 'this item',
    };
    showDeleteModal.value = true;
};

const handleBulkDelete = () => {
    deleteTarget.value = { type: 'bulk' };
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    if (!deleteTarget.value) return;
    
    isDeleting.value = true;
    
    try {
        if (deleteTarget.value.type === 'bulk') {
            // Delete all selected items
            const deletePromises = [];
            
            console.log('Bulk delete starting:', {
                files: Array.from(selectedFiles.value),
                folders: Array.from(selectedFolders.value)
            });
            
            for (const fileId of selectedFiles.value) {
                console.log('Deleting file:', fileId);
                deletePromises.push(deleteFile(fileId));
            }
            
            for (const folderId of selectedFolders.value) {
                console.log('Deleting folder:', folderId);
                deletePromises.push(deleteFolder(folderId));
            }
            
            await Promise.all(deletePromises);
            
            toast.success(`${selectionCount.value} items deleted successfully`);
            clearSelection();
        } else if (deleteTarget.value.type === 'file') {
            console.log('Deleting single file:', deleteTarget.value.id);
            await deleteFile(deleteTarget.value.id!);
            toast.success('File deleted successfully');
        } else {
            console.log('Deleting single folder:', deleteTarget.value.id);
            await deleteFolder(deleteTarget.value.id!);
            toast.success('Folder deleted successfully');
        }
        
        await fetchContents(currentFolderId.value);
        await fetchUsage();
        showDeleteModal.value = false;
        deleteTarget.value = null;
    } catch (error: any) {
        console.error('Failed to delete:', error);
        console.error('Error response:', error.response?.data);
        const errorMessage = error.response?.data?.error || error.message || 'Failed to delete';
        toast.error(errorMessage);
    } finally {
        isDeleting.value = false;
    }
};

const cancelDelete = () => {
    showDeleteModal.value = false;
    deleteTarget.value = null;
};

const toggleSelectionMode = () => {
    selectionMode.value = !selectionMode.value;
    console.log('Selection mode toggled:', selectionMode.value);
    if (!selectionMode.value) {
        clearSelection();
    }
};

const handleDownload = async (fileId: string) => {
    try {
        // Use download endpoint which forces download
        window.location.href = `/api/storage/files/${fileId}/download`;
    } catch (error: any) {
        console.error('Failed to download:', error);
        const errorMessage = error.response?.data?.error || error.message || 'Failed to download file';
        toast.error(errorMessage);
    }
};

const handleShare = (fileId: string) => {
    const file = files.value.find(f => f.id === fileId);
    if (file) {
        shareFile.value = {
            id: file.id,
            original_name: file.original_name,
        };
        showShareModal.value = true;
    }
};

const handleShareCreated = () => {
    toast.success('Share link created successfully');
};
</script>

<template>
    <GrowBackupLayout title="GrowBackup - Cloud Storage">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    
                    <div class="flex items-center gap-4">
                        <!-- Selection Mode Toggle -->
                        <button
                            v-if="!isEmpty"
                            @click="toggleSelectionMode"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-colors"
                            :class="selectionMode 
                                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'"
                            aria-label="Toggle selection mode"
                        >
                            <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                            <span>{{ selectionMode ? 'Cancel' : 'Select' }}</span>
                        </button>
                        
                        <!-- Select All (only in selection mode) -->
                        <button
                            v-if="selectionMode && !hasSelection"
                            @click="selectAll(allItemIds.files, allItemIds.folders)"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Select All
                        </button>
                        
                        <button
                            v-if="!selectionMode"
                            @click="showCreateFolderModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            aria-label="Create new folder"
                        >
                            <FolderPlusIcon class="h-5 w-5" aria-hidden="true" />
                            <span>New Folder</span>
                        </button>
                        
                        <UploadButton
                            v-if="!selectionMode"
                            ref="uploadButtonRef"
                            :folder-id="currentFolderId"
                            @upload="handleUpload"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <!-- Quota Warning -->
                        <div 
                            v-if="usage && usage.percent_used >= 80"
                            class="mb-4 p-4 rounded-lg"
                            :class="usage.percent_used >= 90 ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200'"
                        >
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" :class="usage.percent_used >= 90 ? 'text-red-600' : 'text-amber-600'" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="text-sm font-medium" :class="usage.percent_used >= 90 ? 'text-red-800' : 'text-amber-800'">
                                        {{ usage.percent_used >= 90 ? 'Storage Almost Full' : 'Storage Running Low' }}
                                    </h3>
                                    <p class="mt-1 text-sm" :class="usage.percent_used >= 90 ? 'text-red-700' : 'text-amber-700'">
                                        You're using {{ usage.percent_used.toFixed(1) }}% of your storage. 
                                        {{ usage.percent_used >= 90 ? 'Upgrade now to avoid upload failures.' : 'Consider upgrading your plan.' }}
                                    </p>
                                    <a 
                                        href="/growbackup/subscription"
                                        class="mt-2 inline-flex items-center gap-1 text-sm font-medium hover:underline"
                                        :class="usage.percent_used >= 90 ? 'text-red-800' : 'text-amber-800'"
                                    >
                                        View Plans →
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Breadcrumbs -->
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1">
                                <li
                                    v-for="(crumb, index) in folderHistory"
                                    :key="index"
                                    class="inline-flex items-center"
                                >
                                    <ChevronRightIcon 
                                        v-if="index > 0"
                                        class="h-4 w-4 text-gray-400 mx-1" 
                                        aria-hidden="true" 
                                    />
                                    <button
                                        @click="navigateToBreadcrumb(index)"
                                        class="inline-flex items-center text-sm hover:text-blue-600 transition-colors"
                                        :class="index === folderHistory.length - 1 ? 'text-blue-600 font-medium' : 'text-gray-500'"
                                    >
                                        <HomeIcon v-if="index === 0" class="h-4 w-4 mr-1" aria-hidden="true" />
                                        <FolderIcon v-else class="h-4 w-4 mr-1" aria-hidden="true" />
                                        {{ crumb.name }}
                                    </button>
                                </li>
                            </ol>
                        </nav>

                        <!-- Bulk Actions Toolbar -->
                        <div v-if="selectionMode" class="mb-4">
                            <BulkActionsToolbar
                                :selection-count="selectionCount"
                                :has-selection="hasSelection"
                                @delete="handleBulkDelete"
                                @select-all="selectAll(allItemIds.files, allItemIds.folders)"
                                @cancel="toggleSelectionMode"
                            />
                        </div>

                        <!-- File List -->
                        <div class="bg-white rounded-lg shadow">
                            <!-- Loading State -->
                            <FileListSkeleton v-if="loading" :count="5" />
                            
                            <!-- Empty State -->
                            <EmptyState
                                v-else-if="isEmpty"
                                :type="currentFolderId ? 'folder' : 'root'"
                                :folder-name="currentFolderName"
                                @upload="triggerUpload"
                                @create-folder="showCreateFolderModal = true"
                            />
                            
                            <!-- File List -->
                            <FileList
                                v-else
                                :folders="folders"
                                :files="files"
                                :loading="loading"
                                :selection-mode="selectionMode"
                                :selected-files="selectedFiles"
                                :selected-folders="selectedFolders"
                                @folder-click="(id, name) => handleFolderClick(id, name)"
                                @file-click="handleFileClick"
                                @file-download="handleDownload"
                                @file-share="handleShare"
                                @file-rename="(id, name) => handleRename(id, name, 'file')"
                                @file-delete="(id) => handleDelete(id, 'file')"
                                @folder-rename="(id, name) => handleRename(id, name, 'folder')"
                                @folder-delete="(id) => handleDelete(id, 'folder')"
                                @toggle-file-selection="toggleFileSelection"
                                @toggle-folder-selection="toggleFolderSelection"
                            />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <UsageIndicator v-if="usage" :usage="usage" />
                    </div>
                </div>
        </div>

        <!-- Drag & Drop Zone -->
        <DropZone
            :folder-id="currentFolderId"
            @files-dropped="handleFilesDropped"
        />

        <!-- Upload Progress -->
        <UploadProgress
            v-if="uploads.length > 0"
            :uploads="uploads"
            @remove="removeUpload"
        />

        <!-- File Preview Modal -->
        <FilePreviewModal
            :show="showPreviewModal"
            :file="previewFile"
            :download-url="previewUrl"
            @close="showPreviewModal = false"
            @download="handleDownload"
        />

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmModal
            :show="showDeleteModal"
            :title="deleteModalTitle"
            :message="deleteModalMessage"
            :is-deleting="isDeleting"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />

        <!-- Create Folder Modal -->
        <CreateFolderModal
            :show="showCreateFolderModal"
            @close="showCreateFolderModal = false"
            @create="handleCreateFolder"
        />

        <!-- Share Modal -->
        <ShareModal
            :show="showShareModal"
            :file="shareFile"
            @close="showShareModal = false"
            @created="handleShareCreated"
        />
    </GrowBackupLayout>
</template>
