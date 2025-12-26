import { ref } from 'vue';

export interface Toast {
    id: number;
    type: 'success' | 'error' | 'info' | 'warning';
    message: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let toastId = 0;

export function useToast() {
    const addToast = (type: Toast['type'], message: string, duration = 3000) => {
        const id = ++toastId;
        toasts.value.push({ id, type, message, duration });
        
        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
        
        return id;
    };

    const removeToast = (id: number) => {
        const index = toasts.value.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (message: string, duration?: number) => addToast('success', message, duration);
    const error = (message: string, duration?: number) => addToast('error', message, duration);
    const info = (message: string, duration?: number) => addToast('info', message, duration);
    const warning = (message: string, duration?: number) => addToast('warning', message, duration);

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        info,
        warning,
    };
}
