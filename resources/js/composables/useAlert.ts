import Swal from 'sweetalert2';

export function useAlert() {
    /**
     * Show a confirmation dialog
     */
    const confirm = async (options: {
        title?: string;
        message: string;
        confirmText?: string;
        cancelText?: string;
        type?: 'warning' | 'danger' | 'info';
    }): Promise<boolean> => {
        const result = await Swal.fire({
            title: options.title || 'Are you sure?',
            text: options.message,
            icon: options.type === 'danger' ? 'error' : options.type || 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmText || 'Yes, proceed',
            cancelButtonText: options.cancelText || 'Cancel',
            confirmButtonColor: options.type === 'danger' ? '#dc2626' : '#2563eb',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            focusCancel: true,
        });

        return result.isConfirmed;
    };

    /**
     * Show a success message
     */
    const success = (message: string, title?: string) => {
        Swal.fire({
            title: title || 'Success!',
            text: message,
            icon: 'success',
            confirmButtonText: 'OK',
            confirmButtonColor: '#059669',
            timer: 3000,
            timerProgressBar: true,
        });
    };

    /**
     * Show an error message
     */
    const error = (message: string, title?: string) => {
        Swal.fire({
            title: title || 'Error!',
            text: message,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc2626',
        });
    };

    /**
     * Show an info message
     */
    const info = (message: string, title?: string) => {
        Swal.fire({
            title: title || 'Information',
            text: message,
            icon: 'info',
            confirmButtonText: 'OK',
            confirmButtonColor: '#2563eb',
        });
    };

    /**
     * Show a warning message
     */
    const warning = (message: string, title?: string) => {
        Swal.fire({
            title: title || 'Warning',
            text: message,
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#d97706',
        });
    };

    /**
     * Show a loading indicator
     */
    const loading = (message?: string) => {
        Swal.fire({
            title: 'Please wait...',
            text: message || 'Processing your request',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            customClass: {
                container: 'swal2-container-high-z'
            },
            didOpen: () => {
                Swal.showLoading();
                // Ensure the modal is above everything (presentation viewer is z-index: 999999)
                const container = document.querySelector('.swal2-container') as HTMLElement;
                if (container) {
                    container.style.zIndex = '9999999';
                }
            },
        });
    };

    /**
     * Close any open alert
     */
    const close = () => {
        Swal.close();
    };

    /**
     * Show a toast notification
     */
    const toast = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        Toast.fire({
            icon: type,
            title: message,
        });
    };

    return {
        confirm,
        success,
        error,
        info,
        warning,
        loading,
        close,
        toast,
    };
}
