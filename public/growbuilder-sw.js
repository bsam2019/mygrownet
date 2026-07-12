const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `growbuilder-${CACHE_VERSION}`;
const RUNTIME_CACHE = `growbuilder-runtime-${CACHE_VERSION}`;
const API_CACHE = `growbuilder-api-${CACHE_VERSION}`;
const OFFLINE_PAGE = '/growbuilder-offline.html';

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) =>
      cache.addAll(['/', OFFLINE_PAGE, '/manifest.json']).catch(() => {})
    )
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter((k) => (k.startsWith('growbuilder-runtime-') || k.startsWith('growbuilder-api-')) && !k.includes(CACHE_VERSION))
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  if (request.method !== 'GET' || !url.protocol.startsWith('http')) return;
  if (url.pathname.startsWith('/admin/')) return;

  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/growbuilder/api/')) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(API_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request).then((cached) => cached || new Response(JSON.stringify({ error: 'offline' }), { status: 503, headers: { 'Content-Type': 'application/json' } })))
    );
    return;
  }

  if (url.pathname.includes('/build/') || url.pathname.match(/\.(js|css|png|jpg|svg|woff2?)$/i)) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request))
    );
    return;
  }

  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_PAGE)))
    );
    return;
  }

  event.respondWith(fetch(request).catch(() => caches.match(request)));
});

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
  if (event.data?.type === 'CLEAR_CACHE') {
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k.startsWith('growbuilder-')).map((k) => caches.delete(k))));
  }
});
