// GrowNet Market Service Worker
// Version 1.0.1

const CACHE_NAME = 'grownet-market-v1.0.1';
const RUNTIME_CACHE = 'grownet-market-runtime-v1.0.1';
const IMAGE_CACHE = 'grownet-market-images-v1.0.1';

// Assets to cache on install
const PRECACHE_ASSETS = [
    '/marketplace',
    '/marketplace/cart',
    '/marketplace/search',
];

// Install event - cache core assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Precaching assets');
                // Cache assets individually to handle failures gracefully
                return Promise.allSettled(
                    PRECACHE_ASSETS.map(url => 
                        cache.add(url).catch(err => {
                            console.warn('[SW] Failed to cache:', url, err);
                            return Promise.resolve();
                        })
                    )
                );
            })
            .then(() => self.skipWaiting())
    );
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
    const cache = await caches.open(IMAGE_CACHE);
    const cached = await cache.match(request);
    
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Image fetch failed:', error);
        // Return placeholder image
        return new Response('', { status: 404 });
    }
}

// Handle API requests - network first, then cache
async function handleApiRequest(request) {
    const cache = await caches.open(RUNTIME_CACHE);
    
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] API fetch failed, trying cache:', error);
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
    const cache = await caches.open(RUNTIME_CACHE);
    
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Navigation fetch failed, trying cache:', error);
        const cached = await cache.match(request);
        if (cached) {
            return cached;
        }
        
        // Return offline page
        return caches.match('/marketplace');
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
        // Get pending cart updates from IndexedDB
        const db = await openCartDB();
        const pendingUpdates = await getPendingCartUpdates(db);
        
        // Sync each update
        for (const update of pendingUpdates) {
            await fetch('/marketplace/cart/sync', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(update)
            });
        }
        
        // Clear pending updates
        await clearPendingCartUpdates(db);
        console.log('[SW] Cart synced successfully');
    } catch (error) {
        console.error('[SW] Cart sync failed:', error);
        throw error;
    }
}

// Push notifications
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'GrowNet Market';
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/marketplace-assets/icon-192x192.png',
        badge: '/marketplace-assets/icon-96x96.png',
        data: data.url || '/marketplace',
        actions: [
            { action: 'open', title: 'View' },
            { action: 'close', title: 'Dismiss' }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked:', event.action);
    
    event.notification.close();
    
    if (event.action === 'open' || !event.action) {
        const url = event.notification.data || '/marketplace';
        event.waitUntil(
            clients.openWindow(url)
        );
    }
});

// Helper functions for IndexedDB (cart sync)
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

console.log('[SW] Service worker loaded');
