/**
 * useAutoSave composable
 *
 * Saves form data to localStorage after a debounce delay.
 * Shows a subtle status indicator: "Saving…" → "Draft saved"
 *
 * Usage:
 *   const { autoSaveStatus, clearDraft, hasDraft } = useAutoSave('cms-invoice-draft', form)
 */

import { ref, watch, onMounted, onUnmounted } from 'vue';

type AutoSaveStatus = 'idle' | 'saving' | 'saved';

export function useAutoSave<T extends object>(
    key: string,
    formData: T,
    options: { debounce?: number } = {}
) {
    const { debounce = 1500 } = options;

    const status = ref<AutoSaveStatus>('idle');
    let timer: ReturnType<typeof setTimeout> | null = null;
    let savedTimer: ReturnType<typeof setTimeout> | null = null;

    const save = () => {
        try {
            localStorage.setItem(key, JSON.stringify(formData));
            status.value = 'saved';

            // Reset to idle after 3 seconds
            if (savedTimer) clearTimeout(savedTimer);
            savedTimer = setTimeout(() => {
                status.value = 'idle';
            }, 3000);
        } catch {
            // localStorage might be full or unavailable — fail silently
        }
    };

    const debouncedSave = () => {
        status.value = 'saving';
        if (timer) clearTimeout(timer);
        timer = setTimeout(save, debounce);
    };

    // Deep watch the form data
    const stopWatch = watch(
        () => JSON.stringify(formData),
        debouncedSave,
        { deep: true }
    );

    const loadDraft = (): Partial<T> | null => {
        try {
            const raw = localStorage.getItem(key);
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    };

    const hasDraft = (): boolean => {
        try {
            return localStorage.getItem(key) !== null;
        } catch {
            return false;
        }
    };

    const clearDraft = () => {
        try {
            localStorage.removeItem(key);
            status.value = 'idle';
        } catch {}
    };

    onUnmounted(() => {
        stopWatch();
        if (timer) clearTimeout(timer);
        if (savedTimer) clearTimeout(savedTimer);
    });

    return {
        autoSaveStatus: status,
        loadDraft,
        hasDraft,
        clearDraft,
    };
}
