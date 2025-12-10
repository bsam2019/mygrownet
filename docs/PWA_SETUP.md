# PWA Setup Documentation

**Last Updated:** December 10, 2025
**Status:** Production Ready

## Overview

MyGrowNet platform includes Progressive Web App (PWA) support for three sub-modules:
- **BizBoost** - Marketing Console (Purple theme: #7c3aed)
- **GrowBiz** - SME Management (Emerald theme: #059669)
- **GrowFinance** - Business Accounting (Emerald theme: #10b981)

Each module has its own service worker, manifest, offline page, and icon set.

## File Structure

```
public/
├── bizboost-manifest.json      # BizBoost PWA manifest
├── bizboost-sw.js              # BizBoost service worker
├── bizboost-offline.html       # BizBoost offline fallback page
├── bizboost-assets/            # BizBoost icons (72-512px)
│
├── growbiz-manifest.json       # GrowBiz PWA manifest
├── growbiz-sw.js               # GrowBiz service worker
├── growbiz-offline.html        # GrowBiz offline fallback page
├── growbiz-assets/             # GrowBiz icons (72-512px)
│
├── growfinance-manifest.json   # GrowFinance PWA manifest
├── growfinance-sw.js           # GrowFinance service worker
├── growfinance-offline.html    # GrowFinance offline fallback page
└── growfinance-assets/         # GrowFinance icons (72-512px)
```

## Service Worker Registration

Service workers are registered in `resources/js/app.ts` based on the current route:

- `/bizboost/*` → registers `bizboost-sw.js`
- `/growbiz/*` → registers `growbiz-sw.js`
- `/growfinance/*` → registers `growfinance-sw.js`

## Manifest Configuration

Each manifest includes:
- App name and short name
- Start URL and scope (module-specific)
- Theme and background colors
- Icons (72x72 to 512x512, including maskable)
- App shortcuts for quick actions
- Categories for app store listing

## Install Prompts

Install prompts are integrated into each module's layout:

| Module | Layout File | Component |
|--------|-------------|-----------|
| BizBoost | `BizBoostLayout.vue` | `InstallPrompt` |
| GrowBiz | `GrowBizLayout.vue` | `PwaInstallPrompt` |
| GrowFinance | `GrowFinanceLayout.vue` | `PwaInstallPrompt` |
| MyGrowNet | `AppSidebarLayout.vue` | `InstallPrompt` |

## Caching Strategy

All service workers use the same caching strategy:

1. **Install**: Pre-cache essential assets (manifest, offline page, icons)
2. **Static Assets**: Cache-first strategy for JS, CSS, images, fonts
3. **HTML Pages**: Network-first with cache fallback
4. **API Requests**: Network-only with offline error response

## Offline Support

When offline:
- Static assets served from cache
- Previously visited pages available from cache
- Module-specific offline page shown for uncached routes
- API requests return structured offline error

## usePWA Composable

The `usePWA` composable (`resources/js/composables/usePWA.ts`) provides:

```typescript
const {
    // State
    isInstalled,      // App is installed
    isInstallable,    // Install prompt available
    isOnline,         // Network status
    isStandalone,     // Running as PWA
    isUpdateAvailable,// New version available
    isSyncing,        // Background sync in progress
    pendingActionsCount,
    
    // Actions
    promptInstall,    // Trigger install prompt
    dismissInstallPrompt,
    shouldShowInstallPrompt,
    updateApp,        // Apply pending update
    
    // Offline sync
    queueOfflineAction,
    syncNow,
    clearCache,
    
    // Cache management
    getCachedPages,
    precachePage,
    getCacheStatus
} = usePWA();
```

## Icon Requirements

Each module needs icons in these sizes:
- 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512

Icons should be:
- PNG format
- Square aspect ratio
- Module-specific branding/colors
- Include both "any" and "maskable" purpose variants

## Testing PWA

1. Open Chrome DevTools → Application tab
2. Check "Service Workers" section for registration
3. Check "Manifest" section for proper configuration
4. Use "Offline" checkbox to test offline behavior
5. Use Lighthouse audit for PWA compliance

## Updating Service Workers

When updating service workers:
1. Increment `CACHE_VERSION` in the service worker file
2. Old caches are automatically cleaned on activation
3. Users see update notification via `isUpdateAvailable`
4. Call `updateApp()` to apply update

## Troubleshooting

### Service Worker Not Registering
- Ensure HTTPS (or localhost)
- Check browser console for errors
- Verify service worker file path

### Install Prompt Not Showing
- Must be served over HTTPS
- Must have valid manifest
- Must have registered service worker
- User must not have dismissed recently (7-day cooldown)

### Icons Not Loading
- Verify icon paths in manifest match actual file locations
- Check icon files exist in assets folder
- Ensure proper MIME types for PNG files
