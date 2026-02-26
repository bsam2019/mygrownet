import { ref, computed } from 'vue';

export function useFileSelection() {
    const selectedFiles = ref<Set<string>>(new Set());
    const selectedFolders = ref<Set<string>>(new Set());

    const hasSelection = computed(() => {
        return selectedFiles.value.size > 0 || selectedFolders.value.size > 0;
    });

    const selectionCount = computed(() => {
        return selectedFiles.value.size + selectedFolders.value.size;
    });

    const toggleFileSelection = (fileId: string) => {
        if (selectedFiles.value.has(fileId)) {
            selectedFiles.value.delete(fileId);
        } else {
            selectedFiles.value.add(fileId);
        }
        // Trigger reactivity
        selectedFiles.value = new Set(selectedFiles.value);
    };

    const toggleFolderSelection = (folderId: string) => {
        if (selectedFolders.value.has(folderId)) {
            selectedFolders.value.delete(folderId);
        } else {
            selectedFolders.value.add(folderId);
        }
        // Trigger reactivity
        selectedFolders.value = new Set(selectedFolders.value);
    };

    const selectAll = (fileIds: string[], folderIds: string[]) => {
        selectedFiles.value = new Set(fileIds);
        selectedFolders.value = new Set(folderIds);
    };

    const clearSelection = () => {
        selectedFiles.value.clear();
        selectedFolders.value.clear();
        // Trigger reactivity
        selectedFiles.value = new Set();
        selectedFolders.value = new Set();
    };

    const isFileSelected = (fileId: string) => {
        return selectedFiles.value.has(fileId);
    };

    const isFolderSelected = (folderId: string) => {
        return selectedFolders.value.has(folderId);
    };

    return {
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
    };
}
