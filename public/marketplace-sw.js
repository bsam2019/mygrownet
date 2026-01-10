// GrowNet Market Service Worker
// Version 2.0.0 - Simplified without precaching

const CACHE_NAME = 'grownet-market-v2';
const RUNTIME_CACHE = 'grownet-market-runtime-v2';
const IMAGE_CACHE = 'grownet-market-images-v2';

// Install event - skip precaching to avoid errors
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker v2.0.0...');
    // Skip waiting to activate immediately
    self.skipWaiting();
});

// Handle skip waiting message from client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => {
                        return cacheName.startsWith('grownet-market-') && 
                               cacheName !== CACHE_NAME &&
                               cacheName !== RUNTIME_CACHE &&
                               cacheName !== IMAGE_CACHE;
                    })
                    .map((cacheName) => {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip non-marketplace requests
    if (!url.pathname.startsWith('/marketplace')) {
        return;
    }

    // Handle different types of requests
    if (request.destination === 'image') {
        event.respondWith(handleImageRequest(request));
    } else if (url.pathname.includes('/api/')) {
        event.respondWith(handleApiRequest(request));
    } else {
        event.respondWith(handleNavigationRequest(request));
    }
});

// Handle image requests - cache first, then network
async function handleImageRequest(request) {
    try {
        const cache = await caches.open(IMAGE_CACHE);
        const cached = await cache.match(request);
        
        if (cached) {
            return cached;
        }

        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Image fetch failed:', error);
        return new Response('', { status: 404 });
    }
}

// Handle API requests - network first, then cache
async function handleApiRequest(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] API fetch failed, trying cache:', error);
        const cache = await caches.open(RUNTIME_CACHE);
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        return new Response(JSON.stringify({ error: 'Offline' }), {
            status: 503,
            headers: { 'Content-Type': 'application/json' }
        });
    }
}

// Handle navigation requests - network first, then cache
async function handleNavigationRequest(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(RUNTIME_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Navigation fetch failed, trying cache:', error);
        const cache = await caches.open(RUNTIME_CACHE);
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        return new Response('Offline', { status: 503 });
    }
}

// Background sync for cart updates
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);
    
    if (event.tag === 'sync-cart') {
        event.waitUntil(syncCart());
    }
});

async function syncCart() {
    try {
        const db = await openCartDB();
        const pendingUpdates = await getPendingCartUpdates(db);
        
        for (const update of pendingUpdates) {
            await fetch('/marketplace/cart/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(update)
            });
        }
        
        await clearPendingCartUpdates(db);
        console.log('[SW] Cart synced successfully');
    } catch (error) {
        console.error('[SW] Cart sync failed:', error);
    }
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'GrowNet Market';
    const options = {
        body: data.body || 'You have a new notification',
        data: data.url || '/marketplace'
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data || '/marketplace';
    event.waitUntil(clients.openWindow(url));
});

// Helper functions for IndexedDB
function openCartDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('GrowNetMarketCart', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pendingUpdates')) {
                db.createObjectStore('pendingUpdates', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getPendingCartUpdates(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingUpdates'], 'readonly');
        const store = transaction.objectStore('pendingUpdates');
        const request = store.getAll();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function clearPendingCartUpdates(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['pendingUpdates'], 'readwrite');
        const store = transaction.objectStore('pendingUpdates');
        const request = store.clear();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

console.log('[SW] Service worker v2.0.0 loaded');
