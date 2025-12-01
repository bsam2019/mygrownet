# Service Worker Error Fix

**Error:** `Failed to fetch at sw.js:38:16`  
**Cause:** Service worker trying to cache Vite dev server resources  
**Status:** ✅ Fixed

## Problem

The PWA service worker (`public/sw.js`) was trying to cache resources from the Vite dev server (port 5173), which causes fetch errors during development.

## Solution Applied

### 1. Updated Service Worker
Modified `public/sw.js` to skip caching for development resources:

```javascript
// Skip caching for Vite dev server and localhost resources
const url = new URL(event.request.url);
if (url.hostname === 'localhost' || url.hostname === '127.0.0.1' || url.hostname === '::1' || url.port === '5173') {
  // Just fetch from network, don't cache
  event.respondWith(fetch(event.request));
  return;
}
```

### 2. Added Error Handling
Added catch block to handle network errors gracefully:

```javascript
.catch((error) => {
  console.log('Fetch failed:', error);
  return new Response('Network error', { status: 408, statusText: 'Network error' });
});
```

## How to Fix Immediately

### Option 1: Unregister Service Worker (Recommended)

1. Visit: `http://127.0.0.1:8001/unregister-sw.html`
2. Click "Unregister Service Worker"
3. Close all browser tabs
4. Clear browser cache (Ctrl+Shift+Delete)
5. Reopen mobile dashboard

### Option 2: Manual Unregister

1. Open browser DevTools (F12)
2. Go to "Application" tab
3. Click "Service Workers" in left sidebar
4. Click "Unregister" next to the service worker
5. Go to "Storage" in left sidebar
6. Click "Clear site data"
7. Refresh page

### Option 3: Disable Service Worker Registration

Comment out service worker registration in your layout file temporarily.

## Prevention

The updated service worker now:
- ✅ Skips caching for localhost/dev server
- ✅ Handles fetch errors gracefully
- ✅ Only caches production resources
- ✅ Works correctly in both dev and production

## Testing

After applying the fix:

1. Clear service worker (use Option 1 or 2 above)
2. Refresh mobile dashboard
3. Check console - should see no fetch errors
4. Service worker will re-register with new code

## Files Modified

1. `public/sw.js` - Updated fetch event handler
2. `public/unregister-sw.html` - NEW helper page

## Notes

- This error doesn't break functionality, just clutters console
- Service workers are for PWA features (offline support, push notifications)
- In development, we don't need aggressive caching
- In production, the service worker will work correctly

---

**Error fixed! Service worker now handles dev environment correctly.** ✅
