import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

// Simple toast notification system
const showNotification = (
    type: 'success' | 'error' | 'warning' | 'info',
    title: string,
    message: string
) => {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border-l-4 p-4 transform transition-all duration-300 ${
        type === 'success' ? 'border-green-500' :
        type === 'error' ? 'border-red-500' :
        type === 'warning' ? 'border-amber-500' :
        'border-blue-500'
    }`;
    
    notification.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                ${type === 'success' ? `
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                ` : type === 'error' ? `
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                ` : type === 'warning' ? `
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                ` : `
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                `}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">${title}</h3>
                <p class="text-sm text-gray-600 mt-1">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
};

interface OfflineAction {
    id?: string;
    endpoint: string;
    method: 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    payload: any;
    description?: string;
    timestamp?: number;
    retryCount?: number;
}

interface QueuedAction extends OfflineAction {
    id: string;
    timestamp: number;
    retryCount: number;
}

export function useOfflineSync() {
    const isOnline = ref(navigator.onLine);
    const queuedActions = ref<QueuedAction[]>([]);
    const isSyncing = ref(false);

    // Queue an action for offline sync
    const queueAction = async (action: OfflineAction): Promise<boolean> => {
        if (!('serviceWorker' in navigator) || !navigator.serviceWorker.controller) {
            showNotification('error', 'Offline Mode Unavailable', 'Unable to queue action for sync');
            return false;
        }

        try {
            const messageChannel = new MessageChannel();
            
            return new Promise((resolve) => {
                messageChannel.port1.onmessage = (event) => {
                    if (event.data.success) {
                        loadQueuedActions();
                        resolve(true);
                    } else {
                        resolve(false);
                    }
                };

                navigator.serviceWorker.controller!.postMessage(
                    {
                        type: 'QUEUE_ACTION',
                        action: {
                            endpoint: action.endpoint,
                            method: action.method,
                            payload: action.payload,
                            description: action.description,
                        },
                    },
                    [messageChannel.port2]
                );
            });
        } catch (error) {
            showNotification('error', 'Queue Failed', 'Failed to queue action for sync');
            return false;
        }
    };

    // Load queued actions from service worker
    const loadQueuedActions = async () => {
        if (!('serviceWorker' in navigator) || !navigator.serviceWorker.controller) {
            return;
        }

        try {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                queuedActions.value = event.data.actions || [];
            };

            navigator.serviceWorker.controller.postMessage(
                { type: 'GET_QUEUED_ACTIONS' },
                [messageChannel.port2]
            );
        } catch (error) {
            // Silent fail - not critical
        }
    };

    // Clear all queued actions
    const clearQueue = async (): Promise<boolean> => {
        if (!('serviceWorker' in navigator) || !navigator.serviceWorker.controller) {
            return false;
        }

        try {
            const messageChannel = new MessageChannel();
            
            return new Promise((resolve) => {
                messageChannel.port1.onmessage = (event) => {
                    if (event.data.success) {
                        queuedActions.value = [];
                        resolve(true);
                    } else {
                        resolve(false);
                    }
                };

                navigator.serviceWorker.controller!.postMessage(
                    { type: 'CLEAR_QUEUED_ACTIONS' },
                    [messageChannel.port2]
                );
            });
        } catch (error) {
            console.error('[Offline Sync] Failed to clear queue:', error);
            return false;
        }
    };

    // Trigger background sync
    const triggerSync = async (): Promise<boolean> => {
        if (!('serviceWorker' in navigator)) {
            return false;
        }

        try {
            const registration = await navigator.serviceWorker.ready;
            
            if ('sync' in registration) {
                await (registration as any).sync.register('bizboost-sync');
                showNotification('info', 'Syncing', 'Syncing your offline changes...');
                return true;
            } else {
                showNotification('warning', 'Manual Sync Required', 'Background sync not supported. Please stay online.');
                return false;
            }
        } catch (error) {
            showNotification('error', 'Sync Failed', 'Failed to trigger sync. Please try again.');
            return false;
        }
    };

    // Handle form submission with offline support
    const submitWithOfflineSupport = async (
        endpoint: string,
        method: 'POST' | 'PUT' | 'PATCH' | 'DELETE',
        data: any,
        options?: {
            description?: string;
            onSuccess?: (response: any) => void;
            onError?: (error: any) => void;
            onQueued?: () => void;
        }
    ) => {
        if (isOnline.value) {
            // Online - submit directly using Inertia
            try {
                router.visit(endpoint, {
                    method: method.toLowerCase() as any,
                    data,
                    preserveScroll: true,
                    onSuccess: (page) => {
                        options?.onSuccess?.(page);
                    },
                    onError: (errors) => {
                        options?.onError?.(errors);
                    },
                });
            } catch (error) {
                console.error('[Offline Sync] Submission failed:', error);
                options?.onError?.(error);
            }
        } else {
            // Offline - queue for later
            const queued = await queueAction({
                endpoint,
                method,
                payload: data,
                description: options?.description,
            });

            if (queued) {
                showNotification(
                    'info',
                    'Saved Offline',
                    'Your changes will sync automatically when you\'re back online'
                );
                options?.onQueued?.();
            } else {
                showNotification(
                    'error',
                    'Save Failed',
                    'Unable to save your changes. Please try again.'
                );
                options?.onError?.({ message: 'Failed to queue offline action' });
            }
        }
    };

    // Update online status
    const updateOnlineStatus = () => {
        const wasOffline = !isOnline.value;
        isOnline.value = navigator.onLine;
        
        if (isOnline.value && wasOffline) {
            // Just came back online
            showNotification(
                'success',
                'Back Online',
                'Connection restored. Syncing your changes...'
            );
            
            if (queuedActions.value.length > 0) {
                triggerSync();
            }
        } else if (!isOnline.value && !wasOffline) {
            // Just went offline
            showNotification(
                'warning',
                'You\'re Offline',
                'Your changes will be saved and synced when connection is restored'
            );
        }
    };

    // Listen for service worker messages
    const handleServiceWorkerMessage = (event: MessageEvent) => {
        if (event.data.type === 'SYNC_COMPLETE') {
            isSyncing.value = false;
            loadQueuedActions();
            
            // Show user-friendly notifications
            if (event.data.synced > 0) {
                // Success notification
                showNotification(
                    'success',
                    'Synced Successfully',
                    `${event.data.synced} ${event.data.synced === 1 ? 'action' : 'actions'} synced successfully`
                );
            }
            
            if (event.data.failed > 0) {
                // Warning notification
                showNotification(
                    'warning',
                    'Sync Issues',
                    `${event.data.failed} ${event.data.failed === 1 ? 'action' : 'actions'} failed to sync. Will retry later.`
                );
            }
        }
    };

    // Setup
    onMounted(() => {
        // Load initial queued actions
        loadQueuedActions();

        // Listen for online/offline events
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);

        // Listen for service worker messages
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', handleServiceWorkerMessage);
        }
    });

    // Cleanup
    onUnmounted(() => {
        window.removeEventListener('online', updateOnlineStatus);
        window.removeEventListener('offline', updateOnlineStatus);
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.removeEventListener('message', handleServiceWorkerMessage);
        }
    });

    return {
        isOnline,
        queuedActions,
        isSyncing,
        queueAction,
        loadQueuedActions,
        clearQueue,
        triggerSync,
        submitWithOfflineSupport,
    };
}
