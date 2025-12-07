/**
 * BizBoost Toast Notification Utility
 * 
 * Usage:
 * import { toast } from '@/utils/bizboost-toast';
 * 
 * toast.success('Sale recorded!', 'K1,250 from John Banda');
 * toast.error('Failed to publish', 'Please check your connection');
 * toast.warning('Low stock alert', '3 products need restocking');
 * toast.info('Tip', 'Schedule posts for better engagement');
 */

interface ToastOptions {
    title: string;
    message?: string;
    duration?: number;
    action?: { label: string; onClick: () => void };
}

type ToastType = 'success' | 'error' | 'warning' | 'info';

const showToast = (type: ToastType, options: ToastOptions | string, message?: string) => {
    const detail = typeof options === 'string'
        ? { type, title: options, message }
        : { type, ...options };

    window.dispatchEvent(new CustomEvent('bizboost:toast', { detail }));
};

export const toast = {
    success: (titleOrOptions: ToastOptions | string, message?: string) => 
        showToast('success', titleOrOptions, message),
    
    error: (titleOrOptions: ToastOptions | string, message?: string) => 
        showToast('error', titleOrOptions, message),
    
    warning: (titleOrOptions: ToastOptions | string, message?: string) => 
        showToast('warning', titleOrOptions, message),
    
    info: (titleOrOptions: ToastOptions | string, message?: string) => 
        showToast('info', titleOrOptions, message),
    
    custom: (type: ToastType, options: ToastOptions) => 
        showToast(type, options),
};

export default toast;
