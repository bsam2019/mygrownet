// Service Worker with proper cache versioning and update strategy
// Increment this version when deploying new code to force cache invalidation
const CACHE_VERSION = 'v1.0.2';
const CACHE_NAME = `mygrownet-${CACHE_VERSION}`;
const RUNTIME_CACHE = `mygrownet-runtime-${CACHE_VERSION}`;
const API_CACHE = `mygrownet-api-${CACHE_VERSION}`;

// Track if we've already notified about an update
let updateNotified = false;

// Assets to cache on install
const ASSETS_TO_CACHE = [
  '/',
  '/mobile-dashboard',
  '/manifest.json',
  '/logo.png',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[SW] Caching essential assets');
      return cache.addAll(ASSETS_TO_CACHE).catch((error) => {
        console.warn('[SW] Some assets failed to cache:', error);
        // Don't fail install if some assets can't be cached
      });
    }).then(() => {
      // Force the waiting service worker to become the active service worker
      return self.skipWaiting();
    })
  );
});

// Activate event - clean up old caches and claim clients
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          // Delete old cache versions - be aggressive about cleanup
          if (cacheName.startsWith('mygrownet-') && !cacheName.includes(CACHE_VERSION)) {
            console.log('[SW] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      // Claim all clients immediately to take control
      console.log('[SW] Claiming all clients');
      return self.clients.claim();
    }).then(() => {
      // Notify all clients about the update
      return self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
          client.postMessage({
            type: 'SW_ACTIVATED',
            version: CACHE_VERSION,
          });
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
  if (request.method !== 'GET') {
    return;
  }

  // Skip chrome extensions and other non-http(s) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // API requests - Network first, fallback to cache
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/mygrownet/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Handle 419 (CSRF token expired) - don't cache, let client handle
          if (response.status === 419) {
            console.warn('[SW] 419 CSRF error - not caching');
            return response;
          }
          
          // Only cache successful responses
          if (response.status === 200) {
            const cache = caches.open(API_CACHE);
            cache.then((c) => c.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache on network error
          return caches.match(request).then((cached) => {
            if (cached) {
              console.log('[SW] Serving from cache (offline):', request.url);
              return cached;
            }
            // Return offline page if available
            return caches.match('/offline.html').catch(() => {
              return new Response('Offline - Please check your connection', {
                status: 503,
                statusText: 'Service Unavailable',
              });
            });
          });
        })
    );
    return;
  }

  // Static assets - Network first for build assets (to get latest), cache first for images
  if (
    url.pathname.match(/\.(js|css|png|jpg|jpeg|svg|gif|webp|woff|woff2|ttf|eot)$/i) ||
    url.pathname.includes('/images/')
  ) {
    // For build assets (JS/CSS with hashes), always fetch fresh to ensure updates
    if (url.pathname.includes('/build/')) {
      event.respondWith(
        fetch(request)
          .then((response) => {
            if (response.status === 200) {
              const cache = caches.open(RUNTIME_CACHE);
              cache.then((c) => c.put(request, response.clone()));
            }
            return response;
          })
          .catch(() => {
            // Fallback to cache only if network fails
            return caches.match(request);
          })
      );
      return;
    }
    
    // For other static assets (images, fonts), use cache first
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) {
          return cached;
        }
        return fetch(request).then((response) => {
          if (response.status === 200) {
            const cache = caches.open(RUNTIME_CACHE);
            cache.then((c) => c.put(request, response.clone()));
          }
          return response;
        });
      })
    );
    return;
  }

  // HTML pages - Network first, fallback to cache
  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.status === 200) {
            const cache = caches.open(RUNTIME_CACHE);
            cache.then((c) => c.put(request, response.clone()));
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cached) => {
            if (cached) {
              return cached;
            }
            return caches.match('/offline.html').catch(() => {
              return new Response('Offline - Please check your connection', {
                status: 503,
                statusText: 'Service Unavailable',
              });
            });
          });
        })
    );
    return;
  }

  // Default - Network first
  event.respondWith(
    fetch(request)
      .then((response) => {
        if (response.status === 200) {
          const cache = caches.open(RUNTIME_CACHE);
          cache.then((c) => c.put(request, response.clone()));
        }
        return response;
      })
      .catch(() => {
        return caches.match(request);
      })
  );
});

// Handle messages from clients
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    console.log('[SW] Skipping waiting and activating new version');
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    console.log('[SW] Clearing all caches');
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          console.log('[SW] Deleting cache:', cacheName);
          return caches.delete(cacheName);
        })
      );
    });
  }
  
  if (event.data && event.data.type === 'CHECK_UPDATE') {
    console.log('[SW] Checking for updates');
    // Service worker is already running, just acknowledge
    event.ports[0].postMessage({ type: 'UPDATE_CHECK_DONE' });
  }
});
