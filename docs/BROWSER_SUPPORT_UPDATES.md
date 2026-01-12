# Browser Support & Automatic Updates

**Last Updated:** January 12, 2026  
**Status:** Production

## Overview

MyGrowNet implements automatic version checking and cache busting to ensure users always have the latest features without manual intervention.

## Implementation

### 1. Browser Requirements Page

**Route:** `/browser-requirements`  
**File:** `resources/js/pages/BrowserRequirements.vue`

Provides users with:
- Recommended browsers list
- Minimum system requirements
- Troubleshooting steps
- Cache clearing instructions

### 2. Automatic Version Checking

**Composable:** `resources/js/composables/useVersionCheck.ts`

**How it works:**
1. Checks app version every 5 minutes
2. Compares current version with server version
3. Shows update notification when new version detected
4. Automatically clears cache on update

**Version Storage:**
- Stored in `localStorage` as `app_version`
- Meta tag in HTML: `<meta name="app-version" content="...">`
- Config: `config/app.php` → `APP_VERSION`

### 3. Update Notification

**Component:** `resources/js/components/UpdateNotification.vue`

**Features:**
- Non-intrusive bottom-right notification
- "Refresh Now" button (clears cache + reloads)
- "Later" button (dismisses, checks again in 5 min)
- Auto-dismissible

## Browser Support

### Fully Supported
- **Chrome 90+** - Best experience, full PWA support
- **Firefox 88+** - Full features, good performance
- **Opera 76+** - Full features, good performance
- **Safari 14+** - Full features on iOS/macOS
- **Edge 90+** - Full features

### Limited Support
- **Google App Browser** - No file upload support, use Chrome instead
- **UC Browser** - May have compatibility issues
- **Older browsers** - May not support modern JavaScript features

### Minimum Requirements
- **OS:** Android 8.0+ or iOS 12+
- **RAM:** 2GB minimum (4GB recommended)
- **Internet:** 3G minimum (4G/WiFi recommended)
- **JavaScript:** Must be enabled

## Marketplace Guest Access

**Yes, marketplace supports guest browsing:**

- **Public Routes** (no auth required):
  - `/growmarket` - Browse all products
  - `/growmarket/product/{slug}` - View product details
  - `/growmarket/shop/{id}` - View seller shop and offerings
  - `/growmarket/category/{slug}` - Browse by category
  - `/growmarket/search` - Search products

- **Auth Required** (for purchasing):
  - Checkout
  - Order tracking
  - Reviews

**Seller shops are fully accessible to guests** - they can browse products, see seller info, and view reviews without logging in.

## Deployment Process

### When Deploying Updates

1. **Update version in `.env`:**
   ```env
   APP_VERSION=2026.01.12.001
   ```

2. **Build assets:**
   ```bash
   npm run build
   ```

3. **Deploy to production:**
   ```bash
   git push origin main
   # On server:
   git pull
   php artisan config:clear
   php artisan cache:clear
   ```

4. **Users automatically notified:**
   - Within 5 minutes, active users see update notification
   - They click "Refresh Now"
   - Cache cleared automatically
   - Page reloads with new version

## Troubleshooting

### User Reports "App Won't Load"

**Checklist:**
1. Which browser? (Chrome recommended)
2. Browser version? (Must be recent)
3. Tried clearing cache?
4. Tried different browser?
5. Internet connection stable?

**Common Issues:**

**Issue:** Chrome won't load app  
**Solution:** Clear Chrome data or reinstall Chrome

**Issue:** File upload doesn't work  
**Solution:** Use Chrome/Firefox, not Google app browser

**Issue:** Blank white screen  
**Solution:** Clear cache, update browser, check internet

**Issue:** Infinite loading  
**Solution:** Check network tab for failed requests, clear service worker

### Clearing Service Workers

If PWA service worker is causing issues:

```javascript
// In browser console:
navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(reg => reg.unregister());
});
```

Then refresh page.

## Version Numbering

Format: `YYYY.MM.DD.XXX`

Example: `2026.01.12.001`
- `2026` - Year
- `01` - Month
- `12` - Day
- `001` - Build number (increment for multiple deploys same day)

## Files Modified

### New Files
- `resources/js/pages/BrowserRequirements.vue`
- `resources/js/composables/useVersionCheck.ts`
- `resources/js/components/UpdateNotification.vue`
- `docs/BROWSER_SUPPORT_UPDATES.md`

### Modified Files
- `routes/web.php` - Added `/browser-requirements` route
- `resources/views/app.blade.php` - Added version meta tag
- `resources/js/layouts/app/AppSidebarLayout.vue` - Added UpdateNotification component

## User Communication

### Help Center Article

Add to help center:

**Title:** "Browser Requirements & Troubleshooting"

**Content:**
- Link to `/browser-requirements` page
- Recommended browsers
- How to clear cache
- Common issues and solutions

### In-App Links

Add browser requirements link to:
- Footer
- Help menu
- Error pages (when app fails to load)

## Monitoring

### Metrics to Track
- Browser usage distribution
- Update notification acceptance rate
- Cache-related errors
- Version adoption speed

### Analytics Events
```javascript
// Track update notifications
gtag('event', 'update_notification_shown');
gtag('event', 'update_accepted');
gtag('event', 'update_dismissed');
```

## Changelog

### January 12, 2026
- Initial implementation
- Added automatic version checking
- Created browser requirements page
- Implemented update notifications
- Documented marketplace guest access
