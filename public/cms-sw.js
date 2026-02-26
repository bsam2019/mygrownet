// CMS Service Worker
const CACHE_VERSION = 'cms-v1.0.0';
const CACHE_NAME = `cms-cache-${CACHE_VERSION}`;

// Assets to cache immediately
const PRECACHE_ASSETS = [
    '/cms/dashboard',
    '/cms/login',
    '/build/assets/app.css',
    '/build/assets/app.js',
];

// Cache strategies
const CACHE_STRATEGIES = {
    CACHE_FIRST: 'cache-first',
    NETWORK_FIRST: 'network-first',
    NETWORK_ONLY: 'network-only',
    CACHE_ONLY: 'cache-only',
};

// Install event - cache essential assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Precaching assets');
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((name) => name.startsWith('cms-cache-') && name !== CACHE_NAME)
                        .map((name) => {
                            console.log('[SW] Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - handle requests
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (url.origin !== location.origin) {
        return;
    }

    // Determine strategy based on request type
    const strategy = getStrategy(url);

    event.respondWith(handleRequest(request, strategy));
});

// Get caching strategy for URL
function getStrategy(url) {
    // API requests - network first
    if (url.pathname.startsWith('/api/') || url.pathname.includes('/cms/') && url.search) {
        return CACHE_STRATEGIES.NETWORK_FIRST;
    }

    // Static assets - cache first
    if (url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|woff|woff2)$/)) {
        return CACHE_STRATEGIES.CACHE_FIRST;
    }

    // HTML pages - network first
    return CACHE_STRATEGIES.NETWORK_FIRST;
}

// Handle request with strategy
async function handleRequest(request, strategy) {
    switch (strategy) {
        case CACHE_STRATEGIES.CACHE_FIRST:
            return cacheFirst(request);
        case CACHE_STRATEGIES.NETWORK_FIRST:
            return networkFirst(request);
        case CACHE_STRATEGIES.NETWORK_ONLY:
            return fetch(request);
        case CACHE_STRATEGIES.CACHE_ONLY:
            return caches.match(request);
        default:
            return networkFirst(request);
    }
}

// Cache first strategy
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Fetch failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        
        // Only show offline page in production, not in development
        // Check if we're in development by looking for localhost or local IP
        const isDevelopment = location.hostname === 'localhost' || 
                            location.hostname === '127.0.0.1' ||
                            location.hostname.startsWith('192.168.') ||
                            location.hostname.endsWith('.local');
        
        // In development, let the error propagate so you see the actual error
        if (isDevelopment) {
            return new Response('Network request failed - check if dev server is running', { 
                status: 503,
                statusText: 'Service Unavailable'
            });
        }
        
        // Return offline page for navigation requests in production only
        if (request.mode === 'navigate') {
            return caches.match('/cms/offline');
        }
        
        return new Response('Offline', { status: 503 });
    }
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);
    
    if (event.tag === 'sync-offline-data') {
        event.waitUntil(syncOfflineData());
    }
});

// Sync offline data when back online
async function syncOfflineData() {
    try {
        const db = await openDB();
        const offlineActions = await getOfflineActions(db);
        
        for (const action of offlineActions) {
            try {
                await fetch(action.url, {
                    method: action.method,
                    headers: action.headers,
                    body: action.body,
                });
                
                await deleteOfflineAction(db, action.id);
            } catch (error) {
                console.error('[SW] Failed to sync action:', error);
            }
        }
    } catch (error) {
        console.error('[SW] Sync failed:', error);
    }
}

// IndexedDB helpers
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('cms-offline-db', 1);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
        
        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('offline-actions')) {
                db.createObjectStore('offline-actions', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}

function getOfflineActions(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offline-actions'], 'readonly');
        const store = transaction.objectStore('offline-actions');
        const request = store.getAll();
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function deleteOfflineAction(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offline-actions'], 'readwrite');
        const store = transaction.objectStore('offline-actions');
        const request = store.delete(id);
        
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve();
    });
}

// Push notification handler
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'CMS Notification';
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/images/cms-icon-192.png',
        badge: '/images/cms-badge.png',
        data: data.url || '/cms/dashboard',
        actions: [
            { action: 'open', title: 'Open' },
            { action: 'close', title: 'Close' },
        ],
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'open' || !event.action) {
        const url = event.notification.data || '/cms/dashboard';
        event.waitUntil(
            clients.openWindow(url)
        );
    }
});

// Message handler for client communication
self.addEventListener('message', (event) => {
    console.log('[SW] Message received:', event.data);
    
    if (event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data.type === 'CACHE_URLS') {
        event.waitUntil(
            caches.open(CACHE_NAME)
                .then((cache) => cache.addAll(event.data.urls))
        );
    }
});
