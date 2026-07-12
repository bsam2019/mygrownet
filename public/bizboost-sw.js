// BizBoost Service Worker — scoped to bizboost.mygrownet.com
const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `bizboost-${CACHE_VERSION}`;
const RUNTIME_CACHE = `bizboost-runtime-${CACHE_VERSION}`;
const API_CACHE = `bizboost-api-${CACHE_VERSION}`;

const OFFLINE_PAGE = '/bizboost-offline.html';

// Install - cache offline page
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) =>
      cache.addAll(['/', OFFLINE_PAGE, '/manifest.json']).catch(() => {})
    )
  );
});

// Activate - clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((k) => (k.startsWith('bizboost-runtime-') || k.startsWith('bizboost-api-')) && !k.includes(CACHE_VERSION))
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

// Fetch - network-first with offline fallback
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== 'GET') return;
  if (!url.protocol.startsWith('http')) return;

  // Admin routes - never cache
  if (url.pathname.startsWith('/admin/')) return;

  // API - network first, cache fallback
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/bizboost/api/')) {
    event.respondWith(
      fetch(request)
        .then((res) => {
          if (res.status === 200) {
            const clone = res.clone();
            caches.open(API_CACHE).then((c) => c.put(request, clone));
          }
          return res;
        })
        .catch(() =>
          caches.match(request).then((cached) => {
            if (cached) return cached;
            return new Response(JSON.stringify({ error: 'offline' }), {
              status: 503,
              headers: { 'Content-Type': 'application/json' },
            });
          })
        )
    );
    return;
  }

  // Build assets - network first
  if (url.pathname.includes('/build/') || url.pathname.match(/\.(js|css|png|jpg|svg|woff2?)$/i)) {
    event.respondWith(
      fetch(request)
        .then((res) => {
          if (res.status === 200) {
            const clone = res.clone();
            caches.open(RUNTIME_CACHE).then((c) => c.put(request, clone));
          }
          return res;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // HTML pages (Inertia SPA) - network first, offline fallback
  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then((res) => {
          if (res.status === 200) {
            const clone = res.clone();
            caches.open(RUNTIME_CACHE).then((c) => c.put(request, clone));
          }
          return res;
        })
        .catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_PAGE)))
    );
    return;
  }

  // Default
  event.respondWith(fetch(request).catch(() => caches.match(request)));
});

// Messages
self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
  if (event.data?.type === 'CLEAR_CACHE') {
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k.startsWith('bizboost-')).map((k) => caches.delete(k))));
  }
});
