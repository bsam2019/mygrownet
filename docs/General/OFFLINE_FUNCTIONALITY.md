# Offline Functionality

**Last Updated:** March 3, 2026
**Status:** Production

## Overview

MyGrowNet platform now supports offline functionality through Progressive Web App (PWA) technology. Users can access cached content and queue actions while offline, which automatically sync when the connection is restored.

## Implementation

### Service Worker (public/sw.js)

**Version:** v1.0.5

**Caching Strategy:**
- **HTML Pages**: Network-first, fallback to cache
- **API Requests**: Network-first, fallback to cache with offline indicator
- **Static Assets**: Cache-first for images/fonts, Network-first for JS/CSS
- **Build Assets**: Always fetch fresh to ensure updates

**Cached Routes:**
- Dashboard (`/`)
- GrowNet module (`/grownet`, `/grownet/dashboard`, `/grownet/network`, `/grownet/earnings`, `/grownet/team`)
- BizBoost module (various routes)
- GrowBiz module
- GrowFinance module

**Offline Pages:**
- Main: `/offline.html`
- GrowNet: `/grownet-offline.html`
- BizBoost: `/bizboost-offline.html`
- GrowBiz: `/growbiz-offline.html`
- GrowFinance: `/growfinance-offline.html`

### Service Worker Registration (resources/js/app.ts)

Service workers are registered based on the current route:
- `/grownet/*` → `/sw.js` (main service worker)
- `/bizboost/*` → `/bizboost-sw.js`
- `/growbiz/*` → `/growbiz-sw.js`
- `/growfinance/*` → `/growfinance-sw.js`
- `/cms/*` → `/cms-sw.js`
- Default → `/sw.js`

**Note:** Service workers are disabled in local development to prevent caching issues.

### Offline Sync Composable (resources/js/composables/useOfflineSync.ts)

Provides utilities for handling offline data:

```typescript
const {
    isOnline,           // Reactive online status
    queuedActions,      // Array of queued actions
    isSyncing,          // Sync in progress
    queueAction,        // Queue an action for sync
    triggerSync,        // Manually trigger sync
    submitWithOfflineSupport, // Submit form with offline support
} = useOfflineSync();
```

### Offline Sync Indicator Component (resources/js/components/OfflineSyncIndicator.vue)

Visual indicator showing:
- Online/offline status
- Number of queued actions
- Sync progress
- Action details (expandable)

## Usage

### Basic Form Submission with Offline Support

```vue
<script setup lang="ts">
import { useOfflineSync } from '@/composables/useOfflineSync';

const { submitWithOfflineSupport } = useOfflineSync();

const handleSubmit = () => {
    submitWithOfflineSupport(
        '/api/grownet/update-profile',
        'POST',
        formData,
        {
            description: 'Update profile',
            onSuccess: () => {
                console.log('Profile updated');
            },
            onQueued: () => {
                console.log('Update queued for sync');
            },
            onError: (error) => {
                console.error('Failed:', error);
            },
        }
    );
};
</script>
```

### Manual Action Queuing

```vue
<script setup lang="ts">
import { useOfflineSync } from '@/composables/useOfflineSync';

const { queueAction } = useOfflineSync();

const saveNote = async () => {
    const queued = await queueAction({
        endpoint: '/api/notes',
        method: 'POST',
        payload: { content: noteContent.value },
        description: 'Save note',
    });
    
    if (queued) {
        console.log('Note will sync when online');
    }
};
</script>
```

### Adding Offline Sync Indicator

```vue
<template>
    <div>
        <!-- Your page content -->
        
        <!-- Offline sync indicator -->
        <OfflineSyncIndicator />
    </div>
</template>

<script setup lang="ts">
import OfflineSyncIndicator from '@/components/OfflineSyncIndicator.vue';
</script>
```

## Features

### Automatic Caching
- Essential pages cached on service worker install
- Runtime caching for visited pages
- API responses cached for offline access

### Offline Queue
- Actions queued when offline
- Automatic sync when connection restored
- Retry logic (up to 3 attempts)
- Visual feedback via indicator component

### Background Sync
- Uses Background Sync API when available
- Syncs queued actions in the background
- Notifies user of sync completion

### Cache Management
- Automatic cache versioning
- Old caches cleaned up on activation
- Cache invalidation on version update

### Offline Detection
- Real-time online/offline status
- Automatic retry when back online
- User-friendly offline pages

## Troubleshooting

### Service Worker Not Registering

**Issue:** Service worker fails to register

**Solutions:**
1. Check browser console for errors
2. Ensure HTTPS (required for service workers)
3. Verify service worker file exists at `/sw.js`
4. Check if running in local development (service workers disabled)

### Actions Not Syncing

**Issue:** Queued actions don't sync when back online

**Solutions:**
1. Check browser console for sync errors
2. Verify Background Sync API support
3. Manually trigger sync using `triggerSync()`
4. Check network tab for failed requests

### Cached Content Not Updating

**Issue:** Old content showing after update

**Solutions:**
1. Increment `CACHE_VERSION` in `public/sw.js`
2. Clear browser cache manually
3. Unregister service worker and re-register
4. Check if update notification appeared

### Offline Page Not Showing

**Issue:** Generic error instead of offline page

**Solutions:**
1. Verify offline page exists at `/grownet-offline.html`
2. Check if page is in `ASSETS_TO_CACHE` array
3. Ensure module route pattern matches in `MODULE_ROUTES`
4. Clear cache and reinstall service worker

## Testing

### Test Offline Functionality

1. Open DevTools → Application → Service Workers
2. Check "Offline" checkbox
3. Navigate to GrowNet pages
4. Verify offline page appears
5. Try submitting a form
6. Uncheck "Offline"
7. Verify actions sync automatically

### Test Cache Updates

1. Increment `CACHE_VERSION` in `public/sw.js`
2. Deploy changes
3. Reload page
4. Verify update notification appears
5. Click "Update Now"
6. Verify new version loads

### Test Background Sync

1. Go offline
2. Submit a form
3. Verify action queued
4. Go back online
5. Check console for sync messages
6. Verify action completed on server

## Browser Support

- Chrome/Edge: Full support
- Firefox: Full support (except Background Sync)
- Safari: Partial support (no Background Sync)
- Mobile browsers: Full support on Android, partial on iOS

## Performance

- Initial cache: ~2-5 MB (essential assets)
- Runtime cache: Grows with usage
- Cache limit: Browser-dependent (typically 50-100 MB)
- Sync performance: Depends on queue size and network speed

## Security

- Service workers require HTTPS
- CSRF tokens handled automatically
- Queued actions include authentication headers
- Sensitive data not cached (API responses with auth)

## Future Enhancements

- [ ] IndexedDB for larger offline data storage
- [ ] Conflict resolution for concurrent edits
- [ ] Selective sync (user chooses what to sync)
- [ ] Offline analytics tracking
- [ ] Push notifications for sync completion

## Changelog

### March 3, 2026
- Added GrowNet offline support
- Created offline sync composable
- Added offline sync indicator component
- Updated service worker to v1.0.5
- Created GrowNet offline page
- Added comprehensive documentation
