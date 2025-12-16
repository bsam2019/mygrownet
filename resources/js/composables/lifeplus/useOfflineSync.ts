import { ref, onMounted, onUnmounted } from 'vue';

const DB_NAME = 'lifeplus_offline';
const DB_VERSION = 1;

interface SyncQueueItem {
    id: string;
    action: 'create' | 'update' | 'delete';
    entity: string;
    data: any;
    timestamp: number;
    retryCount: number;
}

export function useOfflineSync() {
    const isOnline = ref(navigator.onLine);
    const isSyncing = ref(false);
    const pendingCount = ref(0);
    const db = ref<IDBDatabase | null>(null);

    // Initialize IndexedDB
    const initDB = (): Promise<IDBDatabase> => {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => {
                db.value = request.result;
                resolve(request.result);
            };

            request.onupgradeneeded = (event) => {
                const database = (event.target as IDBOpenDBRequest).result;

                // Create stores for offline data
                if (!database.objectStoreNames.contains('expenses')) {
                    database.createObjectStore('expenses', { keyPath: 'localId' });
                }
                if (!database.objectStoreNames.contains('tasks')) {
                    database.createObjectStore('tasks', { keyPath: 'localId' });
                }
                if (!database.objectStoreNames.contains('habits')) {
                    database.createObjectStore('habits', { keyPath: 'localId' });
                }
                if (!database.objectStoreNames.contains('notes')) {
                    database.createObjectStore('notes', { keyPath: 'localId' });
                }
                if (!database.objectStoreNames.contains('syncQueue')) {
                    const store = database.createObjectStore('syncQueue', { keyPath: 'id' });
                    store.createIndex('timestamp', 'timestamp');
                }
            };
        });
    };

    // Save data locally
    const saveLocal = async (storeName: string, data: any): Promise<string> => {
        if (!db.value) await initDB();

        const localId = `local_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const item = { ...data, localId, isSynced: false };

        return new Promise((resolve, reject) => {
            const transaction = db.value!.transaction(storeName, 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.put(item);

            request.onsuccess = () => {
                addToSyncQueue('create', storeName, item);
                resolve(localId);
            };
            request.onerror = () => reject(request.error);
        });
    };

    // Get local data
    const getLocal = async (storeName: string): Promise<any[]> => {
        if (!db.value) await initDB();

        return new Promise((resolve, reject) => {
            const transaction = db.value!.transaction(storeName, 'readonly');
            const store = transaction.objectStore(storeName);
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    };

    // Delete local data
    const deleteLocal = async (storeName: string, localId: string): Promise<void> => {
        if (!db.value) await initDB();

        return new Promise((resolve, reject) => {
            const transaction = db.value!.transaction(storeName, 'readwrite');
            const store = transaction.objectStore(storeName);
            const request = store.delete(localId);

            request.onsuccess = () => {
                addToSyncQueue('delete', storeName, { localId });
                resolve();
            };
            request.onerror = () => reject(request.error);
        });
    };

    // Add item to sync queue
    const addToSyncQueue = async (action: SyncQueueItem['action'], entity: string, data: any) => {
        if (!db.value) await initDB();

        const item: SyncQueueItem = {
            id: `sync_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
            action,
            entity,
            data,
            timestamp: Date.now(),
            retryCount: 0,
        };

        return new Promise<void>((resolve, reject) => {
            const transaction = db.value!.transaction('syncQueue', 'readwrite');
            const store = transaction.objectStore('syncQueue');
            const request = store.put(item);

            request.onsuccess = () => {
                updatePendingCount();
                resolve();
            };
            request.onerror = () => reject(request.error);
        });
    };

    // Get pending sync items
    const getPendingSyncItems = async (): Promise<SyncQueueItem[]> => {
        if (!db.value) await initDB();

        return new Promise((resolve, reject) => {
            const transaction = db.value!.transaction('syncQueue', 'readonly');
            const store = transaction.objectStore('syncQueue');
            const index = store.index('timestamp');
            const request = index.getAll();

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    };

    // Remove from sync queue
    const removeFromSyncQueue = async (id: string): Promise<void> => {
        if (!db.value) await initDB();

        return new Promise((resolve, reject) => {
            const transaction = db.value!.transaction('syncQueue', 'readwrite');
            const store = transaction.objectStore('syncQueue');
            const request = store.delete(id);

            request.onsuccess = () => {
                updatePendingCount();
                resolve();
            };
            request.onerror = () => reject(request.error);
        });
    };

    // Update pending count
    const updatePendingCount = async () => {
        const items = await getPendingSyncItems();
        pendingCount.value = items.length;
    };

    // Sync with server
    const sync = async () => {
        if (!isOnline.value || isSyncing.value) return;

        isSyncing.value = true;
        const items = await getPendingSyncItems();

        for (const item of items) {
            try {
                const endpoint = `/lifeplus/${item.entity}/sync`;
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        action: item.action,
                        data: item.data,
                    }),
                });

                if (response.ok) {
                    await removeFromSyncQueue(item.id);
                } else if (item.retryCount >= 3) {
                    // Remove after 3 failed attempts
                    await removeFromSyncQueue(item.id);
                }
            } catch (error) {
                console.error('Sync failed for item:', item.id, error);
            }
        }

        isSyncing.value = false;
    };

    // Handle online/offline events
    const handleOnline = () => {
        isOnline.value = true;
        sync();
    };

    const handleOffline = () => {
        isOnline.value = false;
    };

    onMounted(async () => {
        await initDB();
        await updatePendingCount();

        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        // Sync on mount if online
        if (isOnline.value) {
            sync();
        }
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);
    });

    return {
        isOnline,
        isSyncing,
        pendingCount,
        saveLocal,
        getLocal,
        deleteLocal,
        sync,
    };
}
