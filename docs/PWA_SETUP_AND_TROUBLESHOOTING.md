# PWA Setup and Troubleshooting Guide

**Last Updated:** November 17, 2025
**Status:** Production

## Overview

MyGrowNet is configured as a Progressive Web App (PWA) with offline support, automatic updates, and persistent installation. This guide addresses common PWA issues and explains the implementation.

## Issues Addressed and Fixed

### 1. PWA Install Prompt Not Persistent
**Problem:** Users who haven't installed the app don't see the install prompt consistently.

**Solution:**
- Reduced cooldown from 24 hours to 7 days (less aggressive)
- Prompt shows 1 second after page load (faster)
- Prompt only shows if user hasn't installed and hasn't dismissed recently
- Install state is properly tracked via `isStandalone` detection and localStorage
- Added `INSTALL_STATE_KEY` to persist install state across sessions

### 2. App Doesn't Update When New Changes Are Pushed
**Problem:** Users see stale content even after new code is deployed.

**Solution:**
- Implemented version-based cache busting (increment `CACHE_VERSION` in `public/sw.js`)
- Service worker checks for updates every 30 seconds (was 60)
- Update check also happens on page visibility change and focus
- User gets notification when update is available
- One-click update button reloads page with new version
- Service worker aggressively cleans old cache versions on activation

### 3. 419 CSRF Token Errors After 1-2 Days
**Problem:** Users get 419 errors when trying to logout or make requests after extended use.

**Solution:**
- Extended session lifetime from 2 hours to 7 days
- CSRF token is regenerated on every GET request (not just on install)
- Token is added to response headers for AJAX requests
- Service worker doesn't cache 419 responses
- Better error handling with graceful redirect to login
- Middleware added to web group for automatic token refresh

### 4. PWA Install Prompt Shows Then Disappears
**Problem:** Install prompt appears briefly then disappears, confusing users.

**Solution:**
- Reduced show delay from 3 seconds to 1 second
- Better detection of when prompt should be shown
- Prompt state is managed more reliably
- Added logging to track prompt lifecycle

## Configuration Changes

### Session Configuration (`config/session.php`)
```php
'lifetime' => (int) env('SESSION_LIFETIME', 10080), // 7 days
```

### Middleware (`app/Http/Kernel.php`)
Added `RefreshCsrfToken` middleware to web group:
```php
\App\Http\Middleware\RefreshCsrfToken::class,
```

### Service Worker (`public/sw.js`)
- Increment `CACHE_VERSION` when deploying new code
- Service worker checks for updates every 30 seconds
- Old caches are aggressively cleaned on activation
- 419 responses are not cached

### PWA Composable (`resources/js/composables/usePWA.ts`)
- Reduced install prompt cooldown to 7 days
- Faster update checking (every 30 seconds)
- Better install state persistence
- More reliable prompt timing

## Deployment Instructions

### When Deploying New Code:

1. **Update cache version** in `public/sw.js`:
   ```javascript
   const CACHE_VERSION = 'v1.0.2'; // Increment this
   ```

2. **Run deployment scripts**:
   ```bash
   deployment/deploy-with-migration.sh
   deployment/deploy-with-assets.sh
   ```

3. **Clear browser cache** (or users will see old version):
   - Users will get update notification automatically
   - They can click "Update" to refresh

### For Users:

1. **Install Prompt**: Shows 1 second after page load if not installed
2. **Update Notification**: Shows when new version is available
3. **Session**: Stays active for 7 days (no more 419 errors)
4. **Offline**: App works offline with cached content

## Testing Checklist

- [ ] Fresh install: Install prompt appears within 1 second
- [ ] Dismiss prompt: Doesn't appear for 7 days
- [ ] Deploy new code: Update notification appears within 30 seconds
- [ ] Click update: Page reloads with new version
- [ ] Wait 2+ days: Session still valid, no 419 errors
- [ ] Logout after 2 days: Works without 419 error
- [ ] Go offline: Cached content loads
- [ ] Go online: Fresh content fetches

## Troubleshooting

### Users Still See Old Version
1. Increment `CACHE_VERSION` in `public/sw.js`
2. Deploy code
3. Users will get update notification
4. They click "Update" to refresh

### 419 Errors Still Occurring
1. Check session lifetime in `config/session.php` (should be 10080)
2. Verify `RefreshCsrfToken` middleware is in web group
3. Check browser console for errors
4. Clear browser cache and try again

### Install Prompt Not Showing
1. Check browser console for errors
2. Verify `beforeinstallprompt` event is firing
3. Check if app is already installed (check `isStandalone`)
4. Try on different device/browser

### Updates Not Applying
1. Check service worker version in `public/sw.js`
2. Verify service worker is registered (check DevTools)
3. Check for JavaScript errors in console
4. Try clearing cache and refreshing

## Browser DevTools Debugging

### Chrome DevTools:
1. Open DevTools (F12)
2. Go to Application tab
3. Check Service Workers section
4. Check Cache Storage for cached assets
5. Check Application tab > Manifest for PWA info

### Firefox DevTools:
1. Open DevTools (F12)
2. Go to Storage tab
3. Check Service Workers
4. Check Cache Storage

## Performance Notes

- Service worker checks for updates every 30 seconds
- Update checks happen on visibility change and focus
- Session lifetime is 7 days (configurable via `SESSION_LIFETIME` env var)
- CSRF token is regenerated on every GET request
- Old caches are cleaned on service worker activation

