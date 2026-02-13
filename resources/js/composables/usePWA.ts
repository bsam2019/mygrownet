import { ref, onMounted } from 'vue';

export function usePWA() {
    const isInstallable = ref(false);
    const isInstalled = ref(false);
    const isOnline = ref(navigator.onLine);
    const deferredPrompt = ref<any>(null);
    const registration = ref<ServiceWorkerRegistration | null>(null);

    // Check if app is installed
    const checkInstalled = () => {
        if (window.matchMedia('(display-mode: standalone)').matches) {
            isInstalled.value = true;
            return true;
        }
        
        if ((navigator as any).standalone === true) {
            isInstalled.value = true;
            return true;
        }
        
        return false;
    };

    // Register service worker
    const registerServiceWorker = async () => {
        if (!('serviceWorker' in navigator)) {
            console.log('Service Worker not supported');
            return null;
        }

        try {
            const reg = await navigator.serviceWorker.register('/cms-sw.js', {
                scope: '/cms/',
            });

            console.log('Service Worker registered:', reg);
            registration.value = reg;

            // Check for updates
            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                if (newWorker) {
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // New service worker available
                            if (confirm('New version available! Reload to update?')) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                                window.location.reload();
                            }
                        }
                    });
                }
            });

            return reg;
        } catch (error) {
            console.error('Service Worker registration failed:', error);
            return null;
        }
    };

    // Show install prompt
    const showInstallPrompt = async () => {
        if (!deferredPrompt.value) {
            alert('Install prompt not available. Please use browser menu to install.');
            return false;
        }

        deferredPrompt.value.prompt();
        const { outcome } = await deferredPrompt.value.userChoice;

        console.log('Install prompt outcome:', outcome);

        if (outcome === 'accepted') {
            isInstalled.value = true;
            isInstallable.value = false;
        }

        deferredPrompt.value = null;
        return outcome === 'accepted';
    };

    // Request notification permission
    const requestNotificationPermission = async () => {
        if (!('Notification' in window)) {
            console.log('Notifications not supported');
            return false;
        }

        if (Notification.permission === 'granted') {
            return true;
        }

        if (Notification.permission !== 'denied') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        return false;
    };

    // Subscribe to push notifications
    const subscribeToPush = async () => {
        if (!registration.value) {
            console.error('Service Worker not registered');
            return null;
        }

        try {
            const subscription = await registration.value.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    import.meta.env.VITE_VAPID_PUBLIC_KEY || ''
                ),
            });

            console.log('Push subscription:', subscription);
            return subscription;
        } catch (error) {
            console.error('Push subscription failed:', error);
            return null;
        }
    };

    // Cache important URLs
    const cacheUrls = async (urls: string[]) => {
        if (!registration.value) {
            return;
        }

        registration.value.active?.postMessage({
            type: 'CACHE_URLS',
            urls,
        });
    };

    // Save data for offline sync
    const saveOfflineAction = async (action: {
        url: string;
        method: string;
        headers: Record<string, string>;
        body: string;
    }) => {
        const db = await openDB();
        const transaction = db.transaction(['offline-actions'], 'readwrite');
        const store = transaction.objectStore('offline-actions');
        
        await store.add({
            ...action,
            timestamp: Date.now(),
        });
    };

    // Open IndexedDB
    const openDB = (): Promise<IDBDatabase> => {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open('cms-offline-db', 1);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
            
            request.onupgradeneeded = (event) => {
                const db = (event.target as IDBOpenDBRequest).result;
                if (!db.objectStoreNames.contains('offline-actions')) {
                    db.createObjectStore('offline-actions', { keyPath: 'id', autoIncrement: true });
                }
            };
        });
    };

    // Helper function
    const urlBase64ToUint8Array = (base64String: string) => {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        
        return outputArray;
    };

    // Setup event listeners
    onMounted(() => {
        // Check if already installed
        checkInstalled();

        // Listen for install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt.value = e;
            isInstallable.value = true;
            console.log('Install prompt available');
        });

        // Listen for app installed
        window.addEventListener('appinstalled', () => {
            console.log('App installed');
            isInstalled.value = true;
            isInstallable.value = false;
            deferredPrompt.value = null;
        });

        // Listen for online/offline
        window.addEventListener('online', () => {
            isOnline.value = true;
            console.log('App is online');
            
            // Trigger background sync
            if (registration.value && 'sync' in registration.value) {
                registration.value.sync.register('sync-offline-data');
            }
        });

        window.addEventListener('offline', () => {
            isOnline.value = false;
            console.log('App is offline');
        });

        // Register service worker
        registerServiceWorker();
    });

    return {
        isInstallable,
        isInstalled,
        isOnline,
        registration,
        showInstallPrompt,
        requestNotificationPermission,
        subscribeToPush,
        cacheUrls,
        saveOfflineAction,
    };
}
