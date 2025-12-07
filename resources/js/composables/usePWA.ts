import { ref, onMounted, onUnmounted, readonly, computed } from 'vue';

interface BeforeInstallPromptEvent extends Event {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

// QueuedAction interface for offline sync queue
export interface QueuedAction {
    id: number;
    type: string;
    endpoint: string;
    method: string;
    payload: Record<string, unknown>;
    timestamp: number;
    retryCount: number;
}

interface SWMessage {
    type: string;
    count?: number;
    success?: number;
    failed?: number;
    version?: string;
    timestamp?: number;
}

const isInstalled = ref(false);
const isInstallable = ref(false);
const isOnline = ref(navigator.onLine);
const isStandalone = ref(false);
const isUpdateAvailable = ref(false);
const isSyncing = ref(false);
const pendingActionsCount = ref(0);
const deferredPrompt = ref<BeforeInstallPromptEvent | null>(null);

let initialized = false;

export function usePWA() {
    const initPWA = () => {
        if (initialized) return;
        initialized = true;

        // Check if running as standalone PWA
        isStandalone.value = 
            window.matchMedia('(display-mode: standalone)').matches ||
            (window.navigator as any).standalone === true ||
            document.referrer.includes('android-app://');

        // Check if already installed (heuristic)
        isInstalled.value = isStandalone.value;

        // Service worker is registered in app.ts for BizBoost routes
        // Here we just set up listeners for the existing registration
        if ('serviceWorker' in navigator) {
            // Get existing registration and set up update listener
            navigator.serviceWorker.ready.then((registration) => {
                console.log('[PWA] Service worker ready');

                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                isUpdateAvailable.value = true;
                            }
                        });
                    }
                });
            }).catch((error) => {
                console.log('[PWA] No service worker registration found:', error);
            });

            // Listen for messages from service worker
            navigator.serviceWorker.addEventListener('message', handleSWMessage);
        }

        // Listen for install prompt
        window.addEventListener('beforeinstallprompt', handleInstallPrompt);

        // Listen for app installed
        window.addEventListener('appinstalled', handleAppInstalled);

        // Listen for online/offline
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Listen for display mode changes
        window.matchMedia('(display-mode: standalone)').addEventListener('change', (e) => {
            isStandalone.value = e.matches;
            isInstalled.value = e.matches;
        });
    };

    const handleInstallPrompt = (e: Event) => {
        e.preventDefault();
        deferredPrompt.value = e as BeforeInstallPromptEvent;
        isInstallable.value = true;
        console.log('[PWA] Install prompt captured');
    };

    const handleAppInstalled = () => {
        isInstalled.value = true;
        isInstallable.value = false;
        deferredPrompt.value = null;
        console.log('[PWA] App installed');
    };

    const handleOnline = () => {
        isOnline.value = true;
    };

    const handleOffline = () => {
        isOnline.value = false;
    };

    const handleSWMessage = (event: MessageEvent<SWMessage>) => {
        const { type } = event.data || {};
        
        switch (type) {
            case 'SYNC_STARTED':
                isSyncing.value = true;
                console.log('[PWA] Sync started');
                break;
                
            case 'SYNC_COMPLETED':
                isSyncing.value = false;
                updatePendingActionsCount();
                console.log('[PWA] Sync completed:', event.data);
                break;
                
            case 'ONLINE':
                isOnline.value = true;
                break;
                
            case 'OFFLINE':
                isOnline.value = false;
                break;
                
            case 'UPDATE_AVAILABLE':
                isUpdateAvailable.value = true;
                break;
        }
    };

    const promptInstall = async (): Promise<boolean> => {
        if (!deferredPrompt.value) {
            console.log('[PWA] No install prompt available');
            return false;
        }

        try {
            await deferredPrompt.value.prompt();
            const { outcome } = await deferredPrompt.value.userChoice;
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted install');
                isInstallable.value = false;
                deferredPrompt.value = null;
                return true;
            } else {
                console.log('[PWA] User dismissed install');
                return false;
            }
        } catch (error) {
            console.error('[PWA] Install prompt error:', error);
            return false;
        }
    };

    const dismissInstallPrompt = () => {
        isInstallable.value = false;
        // Store dismissal in localStorage to not show again for a while
        localStorage.setItem('bizboost-pwa-dismissed', Date.now().toString());
    };

    const shouldShowInstallPrompt = (): boolean => {
        if (!isInstallable.value || isInstalled.value) return false;
        
        const dismissed = localStorage.getItem('bizboost-pwa-dismissed');
        if (dismissed) {
            const dismissedTime = parseInt(dismissed, 10);
            const daysSinceDismissed = (Date.now() - dismissedTime) / (1000 * 60 * 60 * 24);
            // Show again after 7 days
            if (daysSinceDismissed < 7) return false;
        }
        
        return true;
    };

    const updateApp = () => {
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    };

    /**
     * Queue an action for offline sync
     */
    const queueOfflineAction = async (action: {
        type: string;
        endpoint: string;
        method?: string;
        payload: Record<string, unknown>;
    }): Promise<boolean> => {
        if (!navigator.serviceWorker?.controller) {
            console.warn('[PWA] No service worker controller available');
            return false;
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data?.success ?? false);
                updatePendingActionsCount();
            };

            navigator.serviceWorker.controller.postMessage(
                { type: 'QUEUE_ACTION', payload: action },
                [messageChannel.port2]
            );
        });
    };

    /**
     * Trigger immediate sync of queued actions
     */
    const syncNow = async (): Promise<boolean> => {
        if (!navigator.serviceWorker?.controller) {
            return false;
        }

        // Try background sync first if available
        try {
            const registration = await navigator.serviceWorker.ready;
            if ('sync' in registration) {
                await (registration as any).sync.register('bizboost-sync');
                return true;
            }
        } catch (e) {
            console.log('[PWA] Background sync not available, using fallback');
        }

        // Fallback to direct message
        const controller = navigator.serviceWorker.controller;
        if (!controller) return false;

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data?.success ?? false);
            };

            controller.postMessage(
                { type: 'SYNC_NOW' },
                [messageChannel.port2]
            );
        });
    };

    /**
     * Get count of pending offline actions
     */
    const updatePendingActionsCount = async () => {
        if (!navigator.serviceWorker?.controller) {
            pendingActionsCount.value = 0;
            return;
        }

        return new Promise<void>((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                pendingActionsCount.value = event.data?.actions?.length ?? 0;
                resolve();
            };

            navigator.serviceWorker.controller!.postMessage(
                { type: 'GET_QUEUED_ACTIONS' },
                [messageChannel.port2]
            );
        });
    };

    /**
     * Clear all caches
     */
    const clearCache = async (): Promise<boolean> => {
        if (!navigator.serviceWorker?.controller) {
            return false;
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data?.success ?? false);
            };

            navigator.serviceWorker.controller!.postMessage(
                { type: 'CLEAR_CACHE' },
                [messageChannel.port2]
            );
        });
    };

    /**
     * Get list of cached pages available offline
     */
    const getCachedPages = async (): Promise<Array<{ url: string; name: string; cached: boolean; inertia?: boolean }>> => {
        if (!navigator.serviceWorker?.controller) {
            return [];
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data?.pages ?? []);
            };

            navigator.serviceWorker.controller!.postMessage(
                { type: 'GET_CACHED_PAGES' },
                [messageChannel.port2]
            );

            // Timeout after 3 seconds
            setTimeout(() => resolve([]), 3000);
        });
    };

    /**
     * Pre-cache a specific page for offline access
     */
    const precachePage = async (url: string): Promise<boolean> => {
        if (!navigator.serviceWorker?.controller) {
            return false;
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data?.success ?? false);
            };

            navigator.serviceWorker.controller!.postMessage(
                { type: 'PRECACHE_PAGE', payload: { url } },
                [messageChannel.port2]
            );

            // Timeout after 10 seconds
            setTimeout(() => resolve(false), 10000);
        });
    };

    /**
     * Get cache status for debugging
     */
    const getCacheStatus = async (): Promise<{ caches: Record<string, number>; version: string; timestamp: number } | null> => {
        if (!navigator.serviceWorker?.controller) {
            return null;
        }

        return new Promise((resolve) => {
            const messageChannel = new MessageChannel();
            
            messageChannel.port1.onmessage = (event) => {
                resolve(event.data ?? null);
            };

            navigator.serviceWorker.controller!.postMessage(
                { type: 'GET_CACHE_STATUS' },
                [messageChannel.port2]
            );

            setTimeout(() => resolve(null), 3000);
        });
    };

    /**
     * Check if we have pending actions to sync
     */
    const hasPendingActions = computed(() => pendingActionsCount.value > 0);

    const getIOSInstallInstructions = () => {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isSafari = /Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent);
        
        if (isIOS && isSafari && !isStandalone.value) {
            return {
                show: true,
                steps: [
                    'Tap the Share button at the bottom of the screen',
                    'Scroll down and tap "Add to Home Screen"',
                    'Tap "Add" to install BizBoost'
                ]
            };
        }
        
        return { show: false, steps: [] };
    };

    onMounted(() => {
        initPWA();
    });

    onMounted(() => {
        // Update pending actions count on mount
        updatePendingActionsCount();
        
        // Sync when coming back online
        if (isOnline.value && hasPendingActions.value) {
            syncNow();
        }
    });

    onUnmounted(() => {
        window.removeEventListener('beforeinstallprompt', handleInstallPrompt);
        window.removeEventListener('appinstalled', handleAppInstalled);
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
        
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.removeEventListener('message', handleSWMessage);
        }
    });

    return {
        // State (readonly)
        isInstalled: readonly(isInstalled),
        isInstallable: readonly(isInstallable),
        isOnline: readonly(isOnline),
        isStandalone: readonly(isStandalone),
        isUpdateAvailable: readonly(isUpdateAvailable),
        isSyncing: readonly(isSyncing),
        pendingActionsCount: readonly(pendingActionsCount),
        hasPendingActions,
        
        // Actions
        promptInstall,
        dismissInstallPrompt,
        shouldShowInstallPrompt,
        updateApp,
        getIOSInstallInstructions,
        
        // Offline sync actions
        queueOfflineAction,
        syncNow,
        clearCache,
        updatePendingActionsCount,
        
        // Cache management
        getCachedPages,
        precachePage,
        getCacheStatus
    };
}
