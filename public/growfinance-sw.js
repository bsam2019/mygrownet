// GrowFinance Service Worker
const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `growfinance-${CACHE_VERSION}`;
const RUNTIME_CACHE = `growfinance-runtime-${CACHE_VERSION}`;
const OFFLINE_URL = '/growfinance-offline.html';

// Assets to cache on install
const PRECACHE_ASSETS = [
  '/growfinance',
  '/growfinance-manifest.json',
  '/growfinance-offline.html',
  '/growfinance-assets/icon-72x72.png',
  '/growfinance-assets/icon-96x96.png',
  '/growfinance-assets/icon-128x128.png',
  '/growfinance-assets/icon-144x144.png',
  '/growfinance-assets/icon-152x152.png',
  '/growfinance-assets/icon-192x192.png',
  '/growfinance-assets/icon-384x384.png',
  '/growfinance-assets/icon-512x512.png'
];

// Install event - cache essential assets
self.addEventListener('install', (event) => {
  console.log('[GrowFinance SW] Installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[GrowFinance SW] Caching essential assets');
        return cache.addAll(PRECACHE_ASSETS).catch((err) => {
          console.warn('[GrowFinance SW] Some assets failed to cache:', err);
        });
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[GrowFinance SW] Activating...');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name.startsWith('growfinance-') && !name.includes(CACHE_VERSION))
          .map((name) => {
            console.log('[GrowFinance SW] Deleting old cache:', name);
            return caches.delete(name);
          })
      );
    }).then(() => {
      console.log('[GrowFinance SW] Claiming clients');
      return self.clients.claim();
    }).then(() => {
      return self.clients.matchAll().then((clients) => {
        clients.forEach((client) => {
          client.postMessage({ type: 'SW_ACTIVATED', version: CACHE_VERSION });
        });
      });
    })
  );
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') return;

  // Skip cross-origin requests
  if (!url.origin.includes(self.location.origin)) return;

  // Skip API requests
  if (url.pathname.includes('/api/')) {
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
          if (response.ok && url.pathname.startsWith('/growfinance')) {
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
            if (url.pathname.startsWith('/growfinance')) {
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
    console.log('[GrowFinance SW] Skipping waiting');
    self.skipWaiting();
  }
  
  if (event.data?.type === 'CLEAR_CACHE') {
    console.log('[GrowFinance SW] Clearing caches');
    caches.keys().then((names) => {
      names.filter(n => n.startsWith('growfinance-')).forEach(n => caches.delete(n));
    });
  }
});

console.log('[GrowFinance SW] Service Worker loaded');
