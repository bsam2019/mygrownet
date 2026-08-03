import { ref, watch } from 'vue';

const STORAGE_KEY = 'growstream.data_saver';

const dataSaver = ref<boolean>(false);

function load(): void {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        dataSaver.value = stored === '1' || stored === 'true';
    } catch {
        dataSaver.value = false;
    }
}

load();

function persist(): void {
    try {
        localStorage.setItem(STORAGE_KEY, dataSaver.value ? '1' : '0');
    } catch {
        // ignore storage errors
    }
}

watch(dataSaver, persist);

export function useDataSaver() {
    const toggle = (): void => {
        dataSaver.value = !dataSaver.value;
    };

    const setEnabled = (enabled: boolean): void => {
        dataSaver.value = enabled;
    };

    return {
        dataSaver,
        toggle,
        setEnabled,
    };
}
