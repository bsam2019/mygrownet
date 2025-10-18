import { ref } from 'vue';

interface Notification {
    type: 'success' | 'error';
    message: string;
}

export function useNotifications() {
    const notification = ref<Notification & { show: boolean }>({
        type: 'success',
        message: '',
        show: false
    });

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        notification.value = {
            type,
            message,
            show: true
        };

        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const hideNotification = () => {
        notification.value.show = false;
    };

    return {
        notification,
        showNotification,
        hideNotification
    };
}
