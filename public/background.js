// Background sync worker for Life+ mobile app
// This runs periodically to sync offline data

addEventListener('syncData', async (event) => {
    console.log('[Background] Starting data sync...');

    try {
        // Check if we have network connectivity
        const online = navigator.onLine;

        if (!online) {
            console.log('[Background] Offline, skipping sync');
            return;
        }

        // Open IndexedDB
        const db = await openDatabase();

        // Get pending sync items
        const pendingItems = await getPendingSyncItems(db);

        if (pendingItems.length === 0) {
            console.log('[Background] No pending items to sync');
            return;
        }

        console.log(`[Background] Syncing ${pendingItems.length} items...`);

        // Process each item
        for (const item of pendingItems) {
            try {
                const response = await fetch(`/api/lifeplus/${item.entity}/sync`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: item.action,
                        data: item.data,
                    }),
                });

                if (response.ok) {
                    await removeFromSyncQueue(db, item.id);
                    console.log(`[Background] Synced item: ${item.id}`);
                }
            } catch (error) {
                console.error(`[Background] Failed to sync item: ${item.id}`, error);
            }
        }

        console.log('[Background] Sync complete');
    } catch (error) {
        console.error('[Background] Sync error:', error);
    }
});

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('lifeplus_offline', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function getPendingSyncItems(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction('syncQueue', 'readonly');
        const store = transaction.objectStore('syncQueue');
        const request = store.getAll();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function removeFromSyncQueue(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction('syncQueue', 'readwrite');
        const store = transaction.objectStore('syncQueue');
        const request = store.delete(id);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}
