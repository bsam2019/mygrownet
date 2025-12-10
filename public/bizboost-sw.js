// BizBoost Service Worker for PWA
const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `bizboost-${CACHE_VERSION}`;
const RUNTIME_CACHE = `bizboost-runtime-${CACHE_VERSION}`;
const OFFLINE_URL = '/bizboost-offline.html';

// Assets to cache on install
const PRECACHE_ASSETS = [
    '/bizboost',
    '/bizboost-manifest.json',
    '/bizboost-offline.html',
    '/bizboost-assets/icon-72x72.png',
    '/bizboost-assets/icon-96x96.png',
    '/bizboost-assets/icon-128x128.png',
    '/bizboost-assets/icon-144x144.png',
    '/bizboost-assets/icon-152x152.png',
    '/bizboost-assets/icon-192x192.png',
    '/bizboost-assets/icon-384x384.png',
    '/bizboost-assets/icon-512x512.png'
];

// BizBoost routes for offline caching
const BIZBOOST_ROUTES = [
    '/bizboost',
    '/bizboost/products',
    '/bizboost/customers',
    '/bizboost/sales',
    '/bizboost/posts',
    '/bizboost/campaigns',
    '/bizboost/reminders',
    '/bizboost/locations'
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
    console.log('[BizBoost SW] Installing...');
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[BizBoost SW] Caching essential assets');
                return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                    console.warn('[BizBoost SW] Some assets failed to cache:', err);
                });
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[BizBoost SW] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name.startsWith('bizboost-') && !name.includes(CACHE_VERSION))
                    .map((name) => {
                        console.log('[BizBoost SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => {
            console.log('[BizBoost SW] Claiming clients');
            return self.clients.claim();
        }).then(() => {
            // Notify clients about activation
            return self.clients.matchAll().then((clients) => {
                clients.forEach((client) => {
                    client.postMessage({ type: 'SW_ACTIVATED', version: CACHE_VERSION });
                });
            });
        })
    );
});


// Fetch event - intelligent caching strategy
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip cross-origin requests
    if (!url.origin.includes(self.location.origin)) return;

    // Skip API requests - let them fail naturally for proper error handling
    if (url.pathname.includes('/api/') || url.pathname.includes('/bizboost/api/')) {
        event.respondWith(
            fetch(request).catch(() => {
                return new Response(JSON.stringify({
                    error: 'offline',
                    message: 'You are offline. Please check your connection.'
                }), {
                    status: 503,
                    headers: { 'Content-Type': 'application/json' }
                });
            })
        );
        return;
    }

    // Static assets - cache first
    if (url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|gif|webp|woff|woff2|ttf|eot|ico)$/i)) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const responseClone = response.clone();
                        caches.open(RUNTIME_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                });
            })
        );
        return;
    }

    // HTML pages - network first, fallback to cache
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok && url.pathname.startsWith('/bizboost')) {
                        const responseClone = response.clone();
                        caches.open(RUNTIME_CACHE).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then((cached) => {
                        if (cached) return cached;
                        // Return offline page for BizBoost routes
                        if (url.pathname.startsWith('/bizboost')) {
                            return caches.match(OFFLINE_URL);
                        }
                        return new Response('Offline', { status: 503 });
                    });
                })
        );
        return;
    }

    // Default - network first
    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok) {
                    const responseClone = response.clone();
                    caches.open(RUNTIME_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => caches.match(request))
    );
});

// Handle messages from the app
self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        console.log('[BizBoost SW] Skipping waiting');
        self.skipWaiting();
    }

    if (event.data?.type === 'CLEAR_CACHE') {
        console.log('[BizBoost SW] Clearing caches');
        caches.keys().then((names) => {
            names.filter(n => n.startsWith('bizboost-')).forEach(n => caches.delete(n));
        });
    }

    if (event.data?.type === 'GET_CACHE_STATUS') {
        getCacheStatus().then((status) => {
            event.ports[0]?.postMessage(status);
        });
    }
});

// Get cache status for debugging
async function getCacheStatus() {
    const cacheNames = await caches.keys();
    const status = { version: CACHE_VERSION, caches: {} };

    for (const name of cacheNames.filter(n => n.startsWith('bizboost-'))) {
        const cache = await caches.open(name);
        const keys = await cache.keys();
        status.caches[name] = keys.length;
    }

    return status;
}

console.log('[BizBoost SW] Service Worker loaded');
