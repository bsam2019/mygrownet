// Service Worker with proper cache versioning and update strategy
// Increment this version when deploying new code to force cache invalidation
const CACHE_VERSION = 'v1.0.4';
const CACHE_NAME = `mygrownet-${CACHE_VERSION}`;
const RUNTIME_CACHE = `mygrownet-runtime-${CACHE_VERSION}`;
const API_CACHE = `mygrownet-api-${CACHE_VERSION}`;
const OFFLINE_QUEUE_NAME = 'mygrownet-offline-queue';

// Track if we've already notified about an update
let updateNotified = false;

// Offline action queue (stored in IndexedDB via simple key-value)
let offlineQueue = [];

// Assets to cache on install
const ASSETS_TO_CACHE = [
  '/',
  '/dashboard',
  '/manifest.json',
  '/logo.png',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
  // BizBoost routes and assets
  '/bizboost',
  '/bizboost/products',
  '/bizboost/customers',
  '/bizboost/sales',
  '/bizboost/posts',
  '/bizboost/campaigns',
  '/bizboost/reminders',
  '/bizboost/locations',
  '/bizboost-manifest.json',
  '/bizboost-offline.html',
];

// BizBoost route patterns for offline fallback
const BIZBOOST_ROUTES = [
  /^\/bizboost/,
];

// Page name mapping for cached pages display
const PAGE_NAMES = {
  '/bizboost': 'Dashboard',
  '/bizboost/products': 'Products',
  '/bizboost/customers': 'Customers',
  '/bizboost/sales': 'Sales',
  '/bizboost/posts': 'Posts',
  '/bizboost/campaigns': 'Campaigns',
  '/bizboost/reminders': 'Reminders',
  '/bizboost/locations': 'Locations',
};

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

  // Skip non-GET requests - NEVER cache POST, PUT, DELETE, PATCH
  if (request.method !== 'GET') {
    event.respondWith(fetch(request));
    return;
  }

  // Skip chrome extensions and other non-http(s) requests
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // API requests - Network first, fallback to cache (GET only)
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/mygrownet/') || url.pathname.startsWith('/bizboost/api/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Handle 419 (CSRF token expired) - don't cache, let client handle
          if (response.status === 419) {
            console.warn('[SW] 419 CSRF error - not caching');
            return response;
          }
          
          // Only cache successful GET responses
          if (response.status === 200 && request.method === 'GET') {
            // Clone BEFORE caching to avoid "body already used" error
            const responseToCache = response.clone();
            caches.open(API_CACHE).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache on network error
          return caches.match(request).then((cached) => {
            if (cached) {
              console.log('[SW] Serving from cache (offline):', request.url);
              // Add offline indicator header
              const headers = new Headers(cached.headers);
              headers.set('X-Served-From', 'cache');
              return new Response(cached.body, {
                status: cached.status,
                statusText: cached.statusText,
                headers: headers,
              });
            }
            // Return JSON error for API requests
            return new Response(JSON.stringify({
              error: 'offline',
              message: 'You are offline. This data is not available.',
            }), {
              status: 503,
              statusText: 'Service Unavailable',
              headers: { 'Content-Type': 'application/json' },
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
            if (response.status === 200 && request.method === 'GET') {
              // Clone BEFORE caching to avoid "body already used" error
              const responseToCache = response.clone();
              caches.open(RUNTIME_CACHE).then((cache) => {
                cache.put(request, responseToCache);
              });
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
          if (response.status === 200 && request.method === 'GET') {
            // Clone BEFORE caching to avoid "body already used" error
            const responseToCache = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => {
              cache.put(request, responseToCache);
            });
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
          if (response.status === 200 && request.method === 'GET') {
            // Clone BEFORE caching to avoid "body already used" error
            const responseToCache = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cached) => {
            if (cached) {
              return cached;
            }
            // Check if this is a BizBoost route - serve BizBoost offline page
            const isBizBoostRoute = BIZBOOST_ROUTES.some(pattern => pattern.test(url.pathname));
            if (isBizBoostRoute) {
              return caches.match('/bizboost-offline.html').then((bizboostOffline) => {
                if (bizboostOffline) {
                  return bizboostOffline;
                }
                // Fallback to main offline page
                return caches.match('/offline.html');
              });
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

  // Default - Network first (GET only)
  event.respondWith(
    fetch(request)
      .then((response) => {
        if (response.status === 200 && request.method === 'GET') {
          // Clone BEFORE caching to avoid "body already used" error
          const responseToCache = response.clone();
          caches.open(RUNTIME_CACHE).then((cache) => {
            cache.put(request, responseToCache);
          });
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
  
  // Get cached BizBoost pages for offline display
  if (event.data && event.data.type === 'GET_CACHED_PAGES') {
    console.log('[SW] Getting cached pages');
    getCachedBizBoostPages().then((pages) => {
      event.ports[0].postMessage({ pages });
    });
  }
  
  // Get queued offline actions
  if (event.data && event.data.type === 'GET_QUEUED_ACTIONS') {
    console.log('[SW] Getting queued actions');
    event.ports[0].postMessage({ actions: offlineQueue });
  }
  
  // Queue an action for later sync
  if (event.data && event.data.type === 'QUEUE_ACTION') {
    console.log('[SW] Queuing action for sync:', event.data.action);
    const action = {
      id: Date.now().toString(),
      ...event.data.action,
      timestamp: Date.now(),
      retryCount: 0,
    };
    offlineQueue.push(action);
    event.ports[0].postMessage({ success: true, id: action.id });
  }
  
  // Clear queued actions (after successful sync)
  if (event.data && event.data.type === 'CLEAR_QUEUED_ACTIONS') {
    console.log('[SW] Clearing queued actions');
    offlineQueue = [];
    event.ports[0].postMessage({ success: true });
  }
});

// Helper function to get cached BizBoost pages
async function getCachedBizBoostPages() {
  const pages = [];
  
  try {
    const cacheNames = await caches.keys();
    
    for (const cacheName of cacheNames) {
      if (!cacheName.startsWith('mygrownet-')) continue;
      
      const cache = await caches.open(cacheName);
      const requests = await cache.keys();
      
      for (const request of requests) {
        const url = new URL(request.url);
        
        // Only include BizBoost HTML pages
        if (url.pathname.startsWith('/bizboost') && !url.pathname.includes('.')) {
          const pageName = PAGE_NAMES[url.pathname] || url.pathname.split('/').pop() || 'Page';
          
          // Avoid duplicates
          if (!pages.find(p => p.url === url.pathname)) {
            pages.push({
              url: url.pathname,
              name: pageName,
            });
          }
        }
      }
    }
  } catch (error) {
    console.error('[SW] Error getting cached pages:', error);
  }
  
  return pages;
}

// Sync queued actions when back online
self.addEventListener('sync', (event) => {
  if (event.tag === 'bizboost-sync') {
    console.log('[SW] Background sync triggered');
    event.waitUntil(syncQueuedActions());
  }
});

// Process queued actions
async function syncQueuedActions() {
  const actionsToSync = [...offlineQueue];
  const failedActions = [];
  
  for (const action of actionsToSync) {
    try {
      const response = await fetch(action.endpoint, {
        method: action.method || 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(action.payload),
      });
      
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }
      
      console.log('[SW] Synced action:', action.id);
    } catch (error) {
      console.error('[SW] Failed to sync action:', action.id, error);
      action.retryCount++;
      if (action.retryCount < 3) {
        failedActions.push(action);
      }
    }
  }
  
  offlineQueue = failedActions;
  
  // Notify clients about sync completion
  const clients = await self.clients.matchAll();
  clients.forEach((client) => {
    client.postMessage({
      type: 'SYNC_COMPLETE',
      synced: actionsToSync.length - failedActions.length,
      failed: failedActions.length,
    });
  });
}
