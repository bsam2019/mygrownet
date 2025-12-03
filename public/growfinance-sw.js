// GrowFinance Service Worker
const CACHE_NAME = 'growfinance-v1';
const OFFLINE_URL = '/growfinance/offline';

// Assets to cache on install
const PRECACHE_ASSETS = [
  '/growfinance-manifest.json',
  '/growfinance-assets/icon-192x192.png',
  '/growfinance-assets/icon-512x512.png'
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[GrowFinance SW] Caching essential assets');
        return cache.addAll(PRECACHE_ASSETS.filter(url => !url.includes('undefined')));
      })
      .catch((err) => {
        console.log('[GrowFinance SW] Cache failed:', err);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name.startsWith('growfinance-') && name !== CACHE_NAME)
          .map((name) => {
            console.log('[GrowFinance SW] Deleting old cache:', name);
            return caches.delete(name);
          })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) return;

  // Skip API requests (let them fail naturally)
  if (event.request.url.includes('/api/')) return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Clone the response before caching
        const responseClone = response.clone();
        
        // Cache successful responses for GrowFinance routes
        if (response.ok && event.request.url.includes('/growfinance')) {
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseClone);
          });
        }
        
        return response;
      })
      .catch(() => {
        // Try to return cached response
        return caches.match(event.request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }
          
          // For navigation requests, return offline page
          if (event.request.mode === 'navigate') {
            return caches.match(OFFLINE_URL);
          }
          
          return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
        });
      })
  );
});

// Handle messages from the app
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

console.log('[GrowFinance SW] Service Worker loaded');
