# PWA Issues and Fixes

**Last Updated:** November 17, 2025
**Status:** Implementation

## Issues Identified

### 1. PWA Install Prompt Not Persistent for Non-Installed Users
**Root Cause:** 24-hour cooldown is too aggressive; users dismiss once and don't see it again for a day.

**Fix:**
- Reduce cooldown to 7 days instead of 24 hours
- Show prompt on first visit and periodically after
- Use localStorage to track dismissals more intelligently

### 2. App Doesn't Update When New Changes Are Pushed
**Root Cause:** Service worker caching strategy doesn't force updates; users see stale content.

**Fix:**
- Implement version-based cache busting
- Add update notification UI
- Force refresh when update is available
- Check for updates on page visibility change

### 3. 419 CSRF Token Errors After 1-2 Days
**Root Cause:** Session expires but CSRF token isn't refreshed; PWA serves cached responses.

**Fix:**
- Extend session lifetime to 7 days (from 2 hours)
- Regenerate CSRF token on every GET request
- Add CSRF token to response headers
- Implement token refresh middleware
- Handle 419 errors gracefully with redirect to login

### 4. PWA Install Prompt Shows Then Disappears
**Root Cause:** Prompt timing is inconsistent; 3-second delay sometimes conflicts with page rendering.

**Fix:**
- Show prompt immediately on first visit
- Use more reliable detection for when to show
- Persist install state across sessions
- Add visual indicator when app is installable

## Implementation Changes

### Files Modified:
1. `public/sw.js` - Enhanced cache versioning and update strategy
2. `resources/js/composables/usePWA.ts` - Improved prompt logic and update handling
3. `resources/js/app.ts` - Better error handling and session management
4. `app/Http/Middleware/RefreshCsrfToken.php` - Token refresh on every request
5. `config/session.php` - Extended session lifetime
6. `app/Http/Kernel.php` - Added RefreshCsrfToken middleware

### Key Changes:

**Session Configuration:**
- Lifetime: 1440 minutes (24 hours) → 10080 minutes (7 days)
- Ensures users stay logged in longer

**CSRF Token Handling:**
- Regenerate token on every GET request
- Add token to response headers
- Middleware handles token refresh automatically

**Service Worker Updates:**
- Version-based cache busting
- Automatic update checking every 60 seconds
- User notification when update available
- One-click update with page reload

**Install Prompt:**
- Reduced cooldown from 24 hours to 7 days
- Show on first visit
- Better persistence tracking
- Avoid prompt fatigue

## Testing Checklist

- [ ] Install app on fresh device - prompt appears immediately
- [ ] Dismiss prompt - doesn't appear for 7 days
- [ ] Push new code - update notification appears
- [ ] Click update - page reloads with new version
- [ ] Wait 2+ days - session still valid, no 419 errors
- [ ] Logout after 2 days - works without 419
- [ ] Go offline - cached content loads
- [ ] Go online - fresh content fetches

## Deployment Steps

1. Run migrations (if any)
2. Deploy code changes
3. Clear browser cache
4. Test on mobile device
5. Monitor for 419 errors in logs
