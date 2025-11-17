# PWA Deployment Guide

**Last Updated:** November 17, 2025
**Status:** Production Ready

## Overview

This guide covers deploying PWA fixes for MyGrowNet, including session management, cache updates, and offline functionality.

## Changes Summary

### 1. Session & CSRF Token Management
- **Session lifetime**: Extended from 2 hours to 7 days (10080 minutes)
- **CSRF token**: Regenerated on every request to prevent 419 errors
- **Middleware**: Added `RefreshCsrfToken` to web middleware group
- **Error handling**: Better 419 error detection and redirect to login

### 2. Service Worker Updates
- **Cache versioning**: Increment `CACHE_VERSION` to force updates
- **Update frequency**: Checks every 30 seconds (was 60)
- **Cache cleanup**: Aggressive cleanup of old cache versions
- **419 handling**: Don't cache 419 responses

### 3. Install Prompt Improvements
- **Cooldown**: Reduced from 24 hours to 7 days
- **Timing**: Shows 1 second after load (was 3 seconds)
- **Persistence**: Better state tracking across sessions
- **UI**: New InstallPrompt component with benefits list

### 4. Update Notifications
- **Real-time**: Users notified within 30 seconds of new deployment
- **UI**: New UpdateNotification component
- **One-click**: Users can update with single button click
- **Auto-reload**: Page reloads with new version after update

## Files Modified

### Backend:
1. `config/session.php` - Extended session lifetime
2. `app/Http/Middleware/RefreshCsrfToken.php` - Enhanced token refresh
3. `app/Http/Kernel.php` - Added middleware to web group

### Frontend:
1. `public/sw.js` - Enhanced service worker with better caching
2. `resources/js/composables/usePWA.ts` - Improved PWA logic
3. `resources/js/app.ts` - Better 419 error handling
4. `resources/js/components/Mobile/UpdateNotification.vue` - New component
5. `resources/js/components/Mobile/InstallPrompt.vue` - New component
6. `resources/js/pages/MyGrowNet/MobileDashboard.vue` - Added PWA components

### Documentation:
1. `docs/PWA_ISSUES_AND_FIXES.md` - Issues and solutions
2. `docs/PWA_SETUP_AND_TROUBLESHOOTING.md` - Complete guide
3. `docs/PWA_DEPLOYMENT_GUIDE.md` - This file

## Deployment Steps

### Step 1: Update Cache Version
Before deploying, increment the cache version in `public/sw.js`:

```javascript
const CACHE_VERSION = 'v1.0.2'; // Increment this number
```

This forces all users to download fresh assets.

### Step 2: Commit Changes
```bash
git add -A
git commit -m "Fix PWA issues: session management, updates, and install prompt"
git push origin main
```

### Step 3: Deploy with Migrations
```bash
deployment/deploy-with-migration.sh
```

This will:
- Pull latest code
- Run any database migrations
- Clear Laravel caches
- Restart services

### Step 4: Deploy Assets
```bash
deployment/deploy-with-assets.sh
```

This will:
- Build frontend assets with Vite
- Copy assets to public directory
- Clear browser caches

### Step 5: Verify Deployment
1. Open browser DevTools (F12)
2. Go to Application tab > Service Workers
3. Check that new version is registered
4. Verify cache version matches your deployment
5. Test offline functionality

## Testing Checklist

### Session & CSRF:
- [ ] Login and wait 2+ days - session should still be valid
- [ ] Try to logout after 2 days - should work without 419
- [ ] Make API calls - CSRF token should refresh automatically
- [ ] Check browser console for 419 errors (should be none)

### Service Worker Updates:
- [ ] Deploy new code with incremented cache version
- [ ] Wait 30 seconds - update notification should appear
- [ ] Click "Update Now" - page should reload with new version
- [ ] Check Application tab - old caches should be deleted

### Install Prompt:
- [ ] Visit site on fresh device/browser
- [ ] Install prompt should appear within 1 second
- [ ] Click "Install App" - app should install
- [ ] Dismiss prompt - shouldn't appear for 7 days
- [ ] Check home screen - app icon should be present

### Offline Functionality:
- [ ] Load dashboard while online
- [ ] Turn off internet/enable airplane mode
- [ ] Navigate to cached pages - should load
- [ ] Try to make transaction - should show offline message
- [ ] Turn internet back on - should sync automatically

## How Offline Mode Works

### Caching Strategy:

**1. Static Assets (Cache-First)**
- CSS, JavaScript, images, fonts
- Cached on first load
- Served from cache instantly
- Updated in background when online

**2. API Requests (Network-First)**
- Dashboard data, transactions, team info
- Tries network first
- Falls back to cache if offline
- Shows offline page if no cache

**3. HTML Pages (Network-First)**
- Dashboard, profile, team pages
- Fresh content when online
- Cached version when offline
- Offline page as last resort

### What Works Offline:

✅ **Available Offline:**
- View dashboard (last cached state)
- Browse team members (cached)
- View transaction history (cached)
- Read messages (cached)
- View profile (cached)
- Navigate between cached pages

❌ **Requires Internet:**
- Make deposits/withdrawals
- Send messages
- Update profile
- Make purchases
- Real-time balance updates
- Submit support tickets

### Cache Lifecycle:

1. **First Visit (Online)**
   - Service worker installs
   - Essential assets cached
   - User can now work offline

2. **Subsequent Visits (Online)**
   - Fresh content loaded
   - Cache updated in background
   - User sees latest data

3. **Offline Visit**
   - Cached content served
   - User sees last known state
   - "Offline" indicator shown

4. **Back Online**
   - Fresh content fetched
   - Cache updated
   - User sees current data

## Troubleshooting

### Users Still See Old Version

**Problem:** Users don't see new features after deployment.

**Solution:**
1. Verify cache version was incremented in `public/sw.js`
2. Check service worker is registered (DevTools > Application)
3. Force update: DevTools > Application > Service Workers > Update
4. Clear cache: DevTools > Application > Clear storage

### 419 Errors Still Occurring

**Problem:** Users get 419 errors when making requests.

**Solution:**
1. Verify session lifetime is 10080 in `config/session.php`
2. Check `RefreshCsrfToken` middleware is in web group
3. Clear Laravel cache: `php artisan cache:clear`
4. Check browser console for CSRF token in headers
5. Verify user is logged in (session not expired)

### Install Prompt Not Showing

**Problem:** Install prompt doesn't appear on mobile.

**Solution:**
1. Check browser console for errors
2. Verify manifest.json is accessible
3. Check HTTPS is enabled (required for PWA)
4. Verify `beforeinstallprompt` event fires (console log)
5. Check if app is already installed
6. Try different browser (Chrome, Edge, Safari)

### Offline Mode Not Working

**Problem:** App doesn't work offline.

**Solution:**
1. Verify service worker is registered
2. Check cache storage has assets (DevTools > Application)
3. Test with airplane mode (not just WiFi off)
4. Check service worker console for errors
5. Verify offline.html is accessible
6. Clear cache and reload while online first

## Monitoring

### Key Metrics to Track:

1. **Session Duration**
   - Average session length
   - Sessions lasting > 2 days
   - 419 error rate

2. **Service Worker**
   - Registration success rate
   - Update adoption rate
   - Cache hit rate

3. **Install Prompt**
   - Prompt shown rate
   - Install acceptance rate
   - Dismissal rate

4. **Offline Usage**
   - Offline page views
   - Cached page loads
   - Failed requests while offline

### Logging:

Check browser console for PWA logs:
- `[PWA]` - PWA composable logs
- `[SW]` - Service worker logs
- `[App]` - Application logs

Check Laravel logs for:
- 419 errors (should be minimal)
- Session expiration
- CSRF token issues

## Rollback Plan

If issues occur after deployment:

### 1. Quick Rollback (Frontend Only)
```bash
# Revert cache version
# Edit public/sw.js and decrement CACHE_VERSION
git checkout HEAD~1 public/sw.js
deployment/deploy-with-assets.sh
```

### 2. Full Rollback (Backend + Frontend)
```bash
git revert HEAD
git push origin main
deployment/deploy-with-migration.sh
deployment/deploy-with-assets.sh
```

### 3. Emergency Fix
```bash
# Disable service worker temporarily
# Edit public/sw.js and add at top:
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => {
  return self.clients.claim().then(() => {
    return self.registration.unregister();
  });
});
```

## Future Improvements

### Short Term:
- [ ] Add background sync for offline form submissions
- [ ] Implement push notifications for updates
- [ ] Add offline indicator in UI
- [ ] Cache user-specific data more aggressively

### Long Term:
- [ ] Implement IndexedDB for offline data storage
- [ ] Add conflict resolution for offline edits
- [ ] Implement progressive enhancement
- [ ] Add analytics for offline usage

## Support

For issues or questions:
1. Check browser console for errors
2. Review this guide and troubleshooting section
3. Check Laravel logs for backend errors
4. Test in incognito mode to rule out cache issues
5. Contact development team with logs and steps to reproduce

## Version History

### v1.0.1 (November 17, 2025)
- Extended session lifetime to 7 days
- Added CSRF token refresh middleware
- Improved service worker update strategy
- Enhanced install prompt with 7-day cooldown
- Added update notification component
- Better 419 error handling

### v1.0.0 (Initial Release)
- Basic PWA functionality
- Service worker with caching
- Install prompt
- Offline page
