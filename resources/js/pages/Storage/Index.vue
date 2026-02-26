<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { FolderPlusIcon, ChevronRightIcon, HomeIcon } from '@heroicons/vue/24/outline';
import AppLayout from '@/Layouts/AppLayout.vue';
import FileList from '@/Components/Storage/FileList.vue';
import UploadButton from '@/Components/Storage/UploadButton.vue';
import UploadProgress from '@/Components/Storage/UploadProgress.vue';
import UsageIndicator from '@/Components/Storage/UsageIndicator.vue';
import CreateFolderModal from '@/Components/Storage/CreateFolderModal.vue';
import { useStorage } from '@/Composables/useStorage';
import { useStorageUpload } from '@/Composables/useStorageUpload';
import { useToast } from '@/composables/useToast';

interface Props {
    currentFolderId?: string;
}

const props = defineProps<Props>();

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
const { toast } = useToast();

const breadcrumbs = ref([{ id: null, name: 'My Storage' }]);
const showCreateFolderModal = ref(false);

onMounted(async () => {
    await Promise.all([
        fetchContents(props.currentFolderId),
        fetchUsage(),
    ]);
});

const handleFolderClick = (folderId: string) => {
    router.visit(`/my-storage?folder=${folderId}`);
};

const handleCreateFolder = async (name: string) => {
    try {
        await createFolder(name, props.currentFolderId);
        await fetchContents(props.currentFolderId);
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
    
    for (const file of files) {
        try {
            await uploadFile(file, props.currentFolderId);
        } catch (error) {
            console.error('Upload failed:', error);
        }
    }
    
    // Refresh contents and usage after uploads
    await Promise.all([
        fetchContents(props.currentFolderId),
        fetchUsage(),
    ]);
};

const handleFileRename = async (fileId: string, currentName: string) => {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        try {
            await renameFile(fileId, newName);
            await fetchContents(props.currentFolderId);
        } catch (error) {
            alert('Failed to rename file');
        }
    }
};

const handleFolderRename = async (folderId: string, currentName: string) => {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        try {
            await renameFolder(folderId, newName);
            await fetchContents(props.currentFolderId);
        } catch (error) {
            alert('Failed to rename folder');
        }
    }
};

const handleFileDelete = async (fileId: string) => {
    if (confirm('Are you sure you want to delete this file?')) {
        try {
            await deleteFile(fileId);
            await Promise.all([
                fetchContents(props.currentFolderId),
                fetchUsage(),
            ]);
        } catch (error) {
            alert('Failed to delete file');
        }
    }
};

const handleFolderDelete = async (folderId: string) => {
    if (confirm('Are you sure you want to delete this folder? It must be empty.')) {
        try {
            await deleteFolder(folderId);
            await fetchContents(props.currentFolderId);
        } catch (error) {
            alert('Failed to delete folder. Make sure it is empty.');
        }
    }
};

const handleFileDownload = async (fileId: string) => {
    try {
        await downloadFile(fileId);
    } catch (error) {
        alert('Failed to download file');
    }
};
</script>

<template>
    <AppLayout title="Storage">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">My Storage</h1>
                    
                    <div class="flex items-center gap-4">
                        <button
                            @click="showCreateFolderModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <FolderPlusIcon class="h-5 w-5" aria-hidden="true" />
                            <span>New Folder</span>
                        </button>
                        
                        <UploadButton
                            :folder-id="currentFolderId"
                            @upload="handleUpload"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-3">
                        <!-- Breadcrumbs -->
                        <nav class="flex mb-4" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2">
                                <li v-for="(crumb, index) in breadcrumbs" :key="index">
                                    <div class="flex items-center">
                                        <ChevronRightIcon
                                            v-if="index > 0"
                                            class="h-4 w-4 text-gray-400 mx-2"
                                            aria-hidden="true"
                                        />
                                        <HomeIcon
                                            v-if="index === 0"
                                            class="h-4 w-4 text-gray-500 mr-2"
                                            aria-hidden="true"
                                        />
                                        <a
                                            :href="crumb.id ? `/my-storage?folder=${crumb.id}` : '/my-storage'"
                                            class="text-sm text-gray-500 hover:text-gray-700"
                                        >
                                            {{ crumb.name }}
                                        </a>
                                    </div>
                                </li>
                            </ol>
                        </nav>

                        <!-- File Manager -->
                        <div class="bg-white rounded-lg shadow">
                            <FileList
                                :folders="folders"
                                :files="files"
                                :loading="loading"
                                @folder-click="handleFolderClick"
                                @file-download="handleFileDownload"
                                @file-rename="handleFileRename"
                                @file-delete="handleFileDelete"
                                @folder-rename="handleFolderRename"
                                @folder-delete="handleFolderDelete"
                            />
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1">
                        <UsageIndicator v-if="usage" :usage="usage" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Progress -->
        <UploadProgress
            v-if="uploads.length > 0"
            :uploads="uploads"
            @remove="removeUpload"
        />

        <!-- Create Folder Modal -->
        <CreateFolderModal
            :show="showCreateFolderModal"
            @close="showCreateFolderModal = false"
            @create="handleCreateFolder"
        />
    </AppLayout>
</template>
