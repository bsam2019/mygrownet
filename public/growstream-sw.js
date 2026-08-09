// GrowStream Service Worker
// Caches the app shell + build assets for offline/PWA support.
// CRITICAL: never intercept Cloudflare Stream video playback (watch.cloudflarestream.com,
// *.cloudflarestream.com, customer subdomains) — that broke playback before. Those are
// bypassed entirely so range requests / signed URLs reach the player untouched.
const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `growstream-${CACHE_VERSION}`;
const RUNTIME_CACHE = `growstream-runtime-${CACHE_VERSION}`;
const API_CACHE = `growstream-api-${CACHE_VERSION}`;
const OFFLINE_PAGE = '/growstream-offline.html';

// Hosts that must ALWAYS bypass the service worker (video delivery).
const VIDEO_HOSTS = [
  'watch.cloudflarestream.com',
  'cloudflarestream.com',
  '.cloudflarestream.com',
  '.cloudflare.com',
];

function isVideoRequest(url) {
  const host = url.hostname;
  return VIDEO_HOSTS.some((h) => host === h || host.endsWith(h));
}

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
        keys.filter((k) => (k.startsWith('growstream-runtime-') || k.startsWith('growstream-api-')) && !k.includes(CACHE_VERSION))
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Only GET + http(s)
  if (request.method !== 'GET' || !url.protocol.startsWith('http')) return;

  // Never touch admin pages or video delivery
  if (url.pathname.startsWith('/admin/')) return;
  if (isVideoRequest(url)) return;

  // API calls (growstream subdomain uses /api/v1/growstream + route names).
  if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/growstream/api/')) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(API_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request).then((cached) => cached || new Response(JSON.stringify({ error: 'offline' }), { status: 503, headers: { 'Content-Type': 'application/json' } })))
    );
    return;
  }

  // Build assets + static files (network-first so new deploys win, cache on success)
  if (url.pathname.includes('/build/') || url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|webp|woff2?|ttf)$/i)) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request).then((cached) => cached || Response.error()))
    );
    return;
  }

  // HTML pages / Inertia — network-first, fall back to cache then offline page
  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(
      fetch(request).then((res) => {
        if (res.status === 200) { const c = res.clone(); caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, c)); }
        return res;
      }).catch(() => caches.match(request).then((cached) => cached || caches.match(OFFLINE_PAGE).then((off) => off || Response.error())))
    );
    return;
  }

  event.respondWith(fetch(request).catch(() => caches.match(request).then((cached) => cached || Response.error())));
});

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
  if (event.data?.type === 'CLEAR_CACHE') {
    caches.keys().then((keys) => Promise.all(keys.filter((k) => k.startsWith('growstream-')).map((k) => caches.delete(k))));
  }
});
