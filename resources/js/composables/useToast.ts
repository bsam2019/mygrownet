/**
 * Toast Notification Composable
 * 
 * Provides a simple API for showing toast notifications.
 * 
 * Usage:
 * const { toast } = useToast();
 * toast.success('Task created!');
 * toast.error('Something went wrong');
 */

import { ref, reactive } from 'vue';

interface ToastMessage {
    id: number;
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
    duration: number;
}

const toasts = reactive<ToastMessage[]>([]);
let nextId = 0;

export function useToast() {
    const show = (
        message: string, 
        type: ToastMessage['type'] = 'info', 
        duration = 3000
    ) => {
        const id = nextId++;
        toasts.push({ id, message, type, duration });
        
        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }
        
        return id;
    };

    const remove = (id: number) => {
        const index = toasts.findIndex(t => t.id === id);
        if (index > -1) {
            toasts.splice(index, 1);
        }
    };

    const toast = {
        success: (message: string, duration?: number) => show(message, 'success', duration),
        error: (message: string, duration?: number) => show(message, 'error', duration),
        info: (message: string, duration?: number) => show(message, 'info', duration),
        warning: (message: string, duration?: number) => show(message, 'warning', duration),
    };

    return {
        toasts,
        toast,
        remove,
    };
}

// Global toast instance for use outside components
export const globalToast = {
    success: (message: string) => {
        const { toast } = useToast();
        toast.success(message);
    },
    error: (message: string) => {
        const { toast } = useToast();
        toast.error(message);
    },
    info: (message: string) => {
        const { toast } = useToast();
        toast.info(message);
    },
    warning: (message: string) => {
        const { toast } = useToast();
        toast.warning(message);
    },
};
