import { ref, watch, type Ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    action_url?: string;
    created_at: string;
    read_at?: string;
}

interface TaskUpdate {
    task_id: number;
    title: string;
    old_status: string;
    new_status: string;
    assigned_to: number;
    updated_at: string;
}

interface TimeOffUpdate {
    request_id: number;
    type: string;
    start_date: string;
    end_date: string;
    status: string;
    action: string;
}

// Get Echo instance from window (set by @laravel/echo-vue)
const getEcho = (): any => {
    return (window as any).Echo || null;
};

/**
 * Composable for real-time employee notifications
 * Pass a reactive ref for employeeId to handle dynamic changes
 */
export function useEmployeeNotifications(employeeIdRef: Ref<number | null>) {
    const notifications = ref<Notification[]>([]);
    const unreadCount = ref(0);
    const isConnected = ref(false);
    const recentToasts = ref<Notification[]>([]);
    
    let channel: any = null;
    let retryTimeout: ReturnType<typeof setTimeout> | null = null;
    let currentEmployeeId: number | null = null;

    const playNotificationSound = () => {
        try {
            const audioContext = new (window.AudioContext || (window as any).webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.1;
            
            oscillator.start();
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Audio not supported or blocked
        }
    };

    const subscribe = (employeeId: number) => {
        if (!employeeId) {
            return;
        }

        const echo = getEcho();
        
        if (!echo) {
            console.warn('[Notifications] Echo not available. Will retry in 2 seconds...');
            retryTimeout = setTimeout(() => {
                subscribe(employeeId);
            }, 2000);
            return;
        }

        try {
            channel = echo.private(`employee.${employeeId}`);
            currentEmployeeId = employeeId;

            // Listen for new notifications
            channel.listen('.notification.created', (data: Notification) => {
                notifications.value.unshift(data);
                unreadCount.value++;
                
                // Add to toast queue
                recentToasts.value.push(data);
                
                // Auto-remove toast after 5 seconds
                setTimeout(() => {
                    const index = recentToasts.value.findIndex(t => t.id === data.id);
                    if (index > -1) {
                        recentToasts.value.splice(index, 1);
                    }
                }, 5000);

                playNotificationSound();
            });

            // Listen for task updates
            channel.listen('.task.status.updated', (data: TaskUpdate) => {
                if (window.location.pathname.includes('/tasks')) {
                    router.reload({ only: ['tasks', 'taskStats'] });
                }
            });

            // Listen for time off updates
            channel.listen('.timeoff.updated', (data: TimeOffUpdate) => {
                const notification: Notification = {
                    id: Date.now(),
                    type: 'time_off_' + data.action,
                    title: `Time Off ${data.action.charAt(0).toUpperCase() + data.action.slice(1)}`,
                    message: `Your ${data.type} leave request has been ${data.action}`,
                    action_url: '/employee/portal/time-off',
                    created_at: new Date().toISOString(),
                };
                
                notifications.value.unshift(notification);
                unreadCount.value++;
                recentToasts.value.push(notification);
            });

            isConnected.value = true;
            console.log('[Notifications] Connected to channel for employee:', employeeId);
        } catch (error) {
            console.warn('[Notifications] Failed to subscribe:', error);
            isConnected.value = false;
        }
    };

    const unsubscribe = () => {
        if (retryTimeout) {
            clearTimeout(retryTimeout);
            retryTimeout = null;
        }

        if (!currentEmployeeId) return;
        
        const echo = getEcho();
        if (echo && channel) {
            try {
                echo.leave(`employee.${currentEmployeeId}`);
            } catch (e) {
                // Ignore cleanup errors
            }
        }
        
        channel = null;
        currentEmployeeId = null;
        isConnected.value = false;
    };

    const markAsRead = async (notificationId: number) => {
        try {
            await router.patch(
                route('employee.portal.notifications.mark-read', notificationId),
                {},
                { preserveScroll: true }
            );
            
            const notification = notifications.value.find(n => n.id === notificationId);
            if (notification && !notification.read_at) {
                notification.read_at = new Date().toISOString();
                unreadCount.value = Math.max(0, unreadCount.value - 1);
            }
        } catch (error) {
            console.error('Failed to mark notification as read:', error);
        }
    };

    const markAllAsRead = async () => {
        try {
            await router.post(
                route('employee.portal.notifications.mark-all-read'),
                {},
                { preserveScroll: true }
            );
            
            notifications.value.forEach(n => {
                if (!n.read_at) {
                    n.read_at = new Date().toISOString();
                }
            });
            unreadCount.value = 0;
        } catch (error) {
            console.error('Failed to mark all notifications as read:', error);
        }
    };

    const dismissToast = (notificationId: number) => {
        const index = recentToasts.value.findIndex(t => t.id === notificationId);
        if (index > -1) {
            recentToasts.value.splice(index, 1);
        }
    };

    // Watch for employee ID changes and manage subscription
    watch(employeeIdRef, (newId, oldId) => {
        if (oldId && oldId !== newId) {
            unsubscribe();
        }
        if (newId) {
            // Delay to allow Echo to initialize
            setTimeout(() => {
                subscribe(newId);
            }, 500);
        }
    }, { immediate: true });

    return {
        notifications,
        unreadCount,
        isConnected,
        recentToasts,
        markAsRead,
        markAllAsRead,
        dismissToast,
        subscribe: () => employeeIdRef.value && subscribe(employeeIdRef.value),
        unsubscribe,
    };
}
