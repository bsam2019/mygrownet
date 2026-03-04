import { watch, onMounted, onUnmounted } from 'vue';
import { debounce } from 'lodash';

interface AutoSaveOptions {
    key: string;
    data: () => any;
    onRestore?: (data: any) => void;
    debounceMs?: number;
    exclude?: string[];
}

export function useAutoSave(options: AutoSaveOptions) {
    const {
        key,
        data,
        onRestore,
        debounceMs = 2000,
        exclude = []
    } = options;

    const STORAGE_KEY = `autosave_${key}`;
    const TIMESTAMP_KEY = `${STORAGE_KEY}_timestamp`;

    // Save data to localStorage
    const saveData = () => {
        try {
            const dataToSave = data();
            
            // Filter out excluded fields
            const filteredData = { ...dataToSave };
            exclude.forEach(field => {
                delete filteredData[field];
            });

            localStorage.setItem(STORAGE_KEY, JSON.stringify(filteredData));
            localStorage.setItem(TIMESTAMP_KEY, new Date().toISOString());
            
            console.log('[AutoSave] Data saved:', key);
        } catch (error) {
            console.error('[AutoSave] Failed to save:', error);
        }
    };

    // Debounced save function
    const debouncedSave = debounce(saveData, debounceMs);

    // Restore data from localStorage
    const restoreData = (): any | null => {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            const timestamp = localStorage.getItem(TIMESTAMP_KEY);
            
            if (saved && timestamp) {
                const savedData = JSON.parse(saved);
                const savedTime = new Date(timestamp);
                const now = new Date();
                const hoursSince = (now.getTime() - savedTime.getTime()) / (1000 * 60 * 60);
                
                // Only restore if saved within last 24 hours
                if (hoursSince < 24) {
                    console.log('[AutoSave] Restored data from:', savedTime.toLocaleString());
                    return savedData;
                } else {
                    console.log('[AutoSave] Saved data too old, clearing');
                    clearSavedData();
                }
            }
        } catch (error) {
            console.error('[AutoSave] Failed to restore:', error);
        }
        return null;
    };

    // Clear saved data
    const clearSavedData = () => {
        localStorage.removeItem(STORAGE_KEY);
        localStorage.removeItem(TIMESTAMP_KEY);
        console.log('[AutoSave] Cleared saved data');
    };

    // Get last save timestamp
    const getLastSaveTime = (): Date | null => {
        const timestamp = localStorage.getItem(TIMESTAMP_KEY);
        return timestamp ? new Date(timestamp) : null;
    };

    // Check if there's saved data
    const hasSavedData = (): boolean => {
        return !!localStorage.getItem(STORAGE_KEY);
    };

    // Setup auto-save watcher
    const setupAutoSave = () => {
        // Watch the data and save on changes
        watch(data, () => {
            debouncedSave();
        }, { deep: true });
    };

    // Cleanup on unmount
    onUnmounted(() => {
        debouncedSave.cancel();
    });

    // Restore on mount if callback provided
    onMounted(() => {
        if (onRestore) {
            const savedData = restoreData();
            if (savedData) {
                onRestore(savedData);
            }
        }
    });

    return {
        saveData,
        restoreData,
        clearSavedData,
        getLastSaveTime,
        hasSavedData,
        setupAutoSave,
    };
}
