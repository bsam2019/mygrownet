/**
 * Auto-Save Composable
 * Automatically saves changes at intervals with debouncing
 */
import { ref, watch, onUnmounted } from 'vue';

export type AutoSaveStatus = 'idle' | 'pending' | 'saving' | 'saved' | 'error';

interface UseAutoSaveOptions {
    /** Delay in ms before auto-saving after changes (default: 30000 = 30s) */
    delay?: number;
    /** Callback to perform the save */
    onSave: () => Promise<void>;
    /** Called when save succeeds */
    onSuccess?: () => void;
    /** Called when save fails */
    onError?: (error: Error) => void;
}

export function useAutoSave(options: UseAutoSaveOptions) {
    const { delay = 30000, onSave, onSuccess, onError } = options;
    
    const status = ref<AutoSaveStatus>('idle');
    const lastSaved = ref<Date | null>(null);
    const error = ref<string | null>(null);
    const isDirty = ref(false);
    
    let saveTimeout: ReturnType<typeof setTimeout> | null = null;
    let savePromise: Promise<void> | null = null;
    
    /**
     * Mark content as changed (dirty)
     */
    const markDirty = () => {
        isDirty.value = true;
        status.value = 'pending';
        error.value = null;
        
        // Clear existing timeout
        if (saveTimeout) {
            clearTimeout(saveTimeout);
        }
        
        // Schedule auto-save
        saveTimeout = setTimeout(() => {
            save();
        }, delay);
    };
    
    /**
     * Perform save immediately
     */
    const save = async (): Promise<boolean> => {
        // If already saving, wait for it
        if (savePromise) {
            await savePromise;
            return status.value === 'saved';
        }
        
        // Clear pending timeout
        if (saveTimeout) {
            clearTimeout(saveTimeout);
            saveTimeout = null;
        }
        
        // Nothing to save
        if (!isDirty.value && status.value !== 'pending') {
            return true;
        }
        
        status.value = 'saving';
        error.value = null;
        
        try {
            savePromise = onSave();
            await savePromise;
            
            status.value = 'saved';
            lastSaved.value = new Date();
            isDirty.value = false;
            
            onSuccess?.();
            
            // Reset to idle after showing "saved" briefly
            setTimeout(() => {
                if (status.value === 'saved') {
                    status.value = 'idle';
                }
            }, 2000);
            
            return true;
        } catch (err) {
            status.value = 'error';
            error.value = err instanceof Error ? err.message : 'Save failed';
            
            onError?.(err instanceof Error ? err : new Error('Save failed'));
            
            return false;
        } finally {
            savePromise = null;
        }
    };
    
    /**
     * Cancel pending auto-save
     */
    const cancel = () => {
        if (saveTimeout) {
            clearTimeout(saveTimeout);
            saveTimeout = null;
        }
        if (status.value === 'pending') {
            status.value = 'idle';
        }
    };
    
    /**
     * Reset state
     */
    const reset = () => {
        cancel();
        isDirty.value = false;
        status.value = 'idle';
        error.value = null;
    };
    
    /**
     * Format last saved time
     */
    const formatLastSaved = (): string => {
        if (!lastSaved.value) return '';
        
        const now = new Date();
        const diff = now.getTime() - lastSaved.value.getTime();
        
        if (diff < 60000) {
            return 'Just now';
        } else if (diff < 3600000) {
            const mins = Math.floor(diff / 60000);
            return `${mins} min${mins > 1 ? 's' : ''} ago`;
        } else {
            return lastSaved.value.toLocaleTimeString([], { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        }
    };
    
    // Cleanup on unmount
    onUnmounted(() => {
        cancel();
    });
    
    return {
        status,
        lastSaved,
        error,
        isDirty,
        markDirty,
        save,
        cancel,
        reset,
        formatLastSaved,
    };
}
