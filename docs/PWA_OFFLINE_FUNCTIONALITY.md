# PWA Offline Functionality Guide

**Last Updated:** November 17, 2025

## Quick Overview

MyGrowNet PWA uses a **smart caching strategy** to provide the best experience both online and offline.

## Caching Strategy Explained

### 1. Cache-First Strategy (Static Assets)
**Used for:** CSS, JavaScript, images, fonts, icons

**How it works:**
```
User Request → Check Cache → Found? → Serve from Cache (FAST!)
                           → Not Found? → Fetch from Network → Cache it → Serve
```

**Benefits:**
- ⚡ Instant loading
- 📱 Works offline
- 💾 Saves bandwidth

**Files cached:**
- `/logo.png`
- `/images/icon-192x192.png`
- `/images/icon-512x512.png`
- All CSS files from Vite build
- All JavaScript files from Vite build
- All fonts and icons

### 2. Network-First Strategy (Dynamic Content)
**Used for:** API calls, dashboard data, user info

**How it works:**
```
User Request → Try Network → Success? → Cache it → Serve (FRESH!)
                          → Failed? → Check Cache → Found? → Serve (STALE)
                                                 → Not Found? → Show Offline Page
```

**Benefits:**
- 🔄 Always fresh when online
- 📦 Fallback when offline
- 🎯 Best of both worlds

**Endpoints cached:**
- `/mygrownet/*` (dashboard, profile, team)
- `/api/*` (API endpoints)
- Dashboard data
- Team information
- Transaction history

### 3. Network-Only Strategy (Critical Operations)
**Used for:** Transactions, form submissions, authentication

**How it works:**
```
User Request → Try Network → Success? → Process
                          → Failed? → Show Error (requires internet)
```

**Operations that require internet:**
- Deposits and withdrawals
- Sending messages
- Updating profile
- Making purchases
- Submitting support tickets
- Authentication (login/logout)

## What Works Offline

### ✅ Fully Functional Offline:

**Dashboard:**
- View last known balance
- See cached statistics
- View team size (cached)
- Browse earnings breakdown (cached)

**Team:**
- View team members (cached)
- See referral link
- Browse level breakdown (cached)
- View member details (cached)

**Wallet:**
- View transaction history (cached)
- See last known balance
- Browse earnings breakdown (cached)

**Profile:**
- View profile information (cached)
- See account details (cached)

**Navigation:**
- Switch between tabs
- Navigate cached pages
- Use bottom navigation

### ⚠️ Limited Functionality Offline:

**Requires Internet:**
- Making deposits
- Requesting withdrawals
- Sending messages
- Updating profile
- Purchasing starter kits
- Applying for loans
- Submitting support tickets
- Real-time balance updates
- Live notifications

### 🔄 Syncs When Back Online:

**Automatic Sync:**
- Fresh balance loaded
- New transactions fetched
- Team updates retrieved
- Messages synchronized
- Notifications delivered

## User Experience Flow

### Scenario 1: First Visit (Online)
```
1. User visits MyGrowNet
2. Service worker installs
3. Essential assets cached
4. Dashboard loads normally
5. User can now work offline
```

### Scenario 2: Return Visit (Online)
```
1. User opens app
2. Cached assets load instantly (fast!)
3. Fresh data fetched in background
4. UI updates with latest data
5. Cache updated for next offline visit
```

### Scenario 3: Offline Visit
```
1. User opens app (no internet)
2. Cached assets load instantly
3. Last known data displayed
4. "Offline" indicator shown
5. User can browse cached content
6. Actions requiring internet are disabled
```

### Scenario 4: Back Online
```
1. Internet connection restored
2. Service worker detects online status
3. Fresh data fetched automatically
4. UI updates with current data
5. Disabled actions re-enabled
6. User can perform all operations
```

## Cache Management

### Cache Versions
Each deployment has a version number:
```javascript
const CACHE_VERSION = 'v1.0.1';
```

When this changes:
1. Old cache is deleted
2. New assets are cached
3. Users get fresh content

### Cache Size
Typical cache size: **5-10 MB**
- Static assets: ~3-5 MB
- API responses: ~2-5 MB
- Images: ~1-2 MB

### Cache Expiration
- **Static assets**: Never expire (version-based)
- **API responses**: Expire on new deployment
- **Old versions**: Deleted on service worker activation

## Offline Indicators

### Visual Indicators:
1. **Offline Page**: Shown when no cache available
2. **Disabled Buttons**: Grayed out when offline
3. **Toast Messages**: "You're offline" notifications
4. **Status Badge**: Connection status indicator

### User Feedback:
```
Online:  ✅ All features available
Offline: ⚠️ Limited to cached content
         ❌ Transactions disabled
```

## Technical Details

### Service Worker Lifecycle

**1. Install Phase:**
```javascript
// Cache essential assets
ASSETS_TO_CACHE = [
  '/',
  '/mobile-dashboard',
  '/manifest.json',
  '/logo.png',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
]
```

**2. Activate Phase:**
```javascript
// Clean up old caches
caches.keys().then(cacheNames => {
  return Promise.all(
    cacheNames.map(cacheName => {
      if (cacheName !== CACHE_NAME) {
        return caches.delete(cacheName);
      }
    })
  );
});
```

**3. Fetch Phase:**
```javascript
// Intercept requests
self.addEventListener('fetch', event => {
  // Apply caching strategy based on request type
});
```

### Cache Storage Structure
```
mygrownet-v1.0.1/          (Main cache)
├── /
├── /mobile-dashboard
├── /logo.png
└── /images/*

mygrownet-runtime-v1.0.1/  (Runtime cache)
├── /build/assets/*.js
├── /build/assets/*.css
└── fonts/*

mygrownet-api-v1.0.1/      (API cache)
├── /mygrownet/dashboard
├── /mygrownet/team
└── /api/*
```

## Best Practices for Users

### To Ensure Best Offline Experience:

1. **Load pages while online first**
   - Visit dashboard, team, wallet while connected
   - This caches the data for offline use

2. **Update regularly**
   - Click "Update" when notification appears
   - Ensures latest features and bug fixes

3. **Check connection before critical actions**
   - Verify internet before making transactions
   - Wait for "online" indicator before submitting forms

4. **Clear cache if issues occur**
   - Settings > Clear Cache
   - Reload page while online

## Developer Notes

### Adding New Cached Routes:
```javascript
// In public/sw.js
const ASSETS_TO_CACHE = [
  '/',
  '/mobile-dashboard',
  '/new-route',  // Add here
];
```

### Excluding Routes from Cache:
```javascript
// In public/sw.js fetch handler
if (url.pathname.startsWith('/admin/')) {
  // Don't cache admin routes
  return fetch(request);
}
```

### Testing Offline Mode:
1. Open DevTools (F12)
2. Go to Network tab
3. Check "Offline" checkbox
4. Reload page
5. Verify cached content loads

### Debugging Cache Issues:
```javascript
// Check what's in cache
caches.keys().then(console.log);

// Check specific cache
caches.open('mygrownet-v1.0.1').then(cache => {
  cache.keys().then(console.log);
});

// Clear all caches
caches.keys().then(keys => {
  keys.forEach(key => caches.delete(key));
});
```

## Performance Metrics

### Load Times:

**First Visit (Online):**
- Initial load: ~2-3 seconds
- Assets cached: ~1-2 seconds
- Total: ~3-5 seconds

**Return Visit (Online):**
- Cached assets: ~0.5 seconds
- Fresh data: ~1-2 seconds
- Total: ~1.5-2.5 seconds

**Offline Visit:**
- Cached assets: ~0.5 seconds
- Cached data: ~0.2 seconds
- Total: ~0.7 seconds ⚡

### Bandwidth Savings:

**With PWA Caching:**
- First visit: ~5 MB downloaded
- Return visits: ~0.5 MB (data only)
- Savings: ~90% bandwidth

**Without PWA Caching:**
- Every visit: ~5 MB downloaded
- No offline capability
- Higher data costs

## Summary

MyGrowNet PWA provides:
- ⚡ **Fast loading** with cache-first strategy
- 📱 **Offline access** to cached content
- 🔄 **Smart updates** when online
- 💾 **Bandwidth savings** with efficient caching
- 🎯 **Best experience** both online and offline

The service worker intelligently manages caching to ensure users always get the best experience, whether they're online or offline.
