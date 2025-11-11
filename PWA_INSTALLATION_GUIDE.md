# MyGrowNet PWA Installation Guide

**Last Updated:** November 10, 2025
**Status:** ✅ Fully Implemented

## Overview

MyGrowNet is a Progressive Web App (PWA) that can be installed on mobile devices and desktops, providing an app-like experience without requiring download from app stores.

## Features

✅ **Install as App** - Add to home screen on mobile devices
✅ **Offline Support** - Service worker for offline functionality
✅ **App Icons** - Custom icons for all device sizes (72x72 to 512x512)
✅ **Splash Screen** - Branded loading screen
✅ **Standalone Mode** - Runs in full-screen without browser UI
✅ **App Shortcuts** - Quick access to Dashboard, Wallet, and Network
✅ **Auto-detect** - Prompts users to install when eligible

## How Users Install

### On Android (Chrome/Edge)

1. Visit `https://mygrownet.com` on Chrome or Edge browser
2. An install prompt will appear at the bottom of the screen
3. Tap "Install" button
4. The app will be added to your home screen
5. Open from home screen like any other app

**Alternative Method:**
1. Tap the three-dot menu (⋮) in Chrome
2. Select "Add to Home screen" or "Install app"
3. Confirm installation
4. App appears on home screen

### On iOS (Safari)

1. Visit `https://mygrownet.com` in Safari
2. Tap the Share button (square with arrow)
3. Scroll down and tap "Add to Home Screen"
4. Edit the name if desired
5. Tap "Add"
6. App appears on home screen

**Note:** iOS doesn't show automatic install prompts, users must manually add to home screen.

### On Desktop (Chrome/Edge)

1. Visit `https://mygrownet.com`
2. Look for install icon in address bar (⊕ or computer icon)
3. Click the icon
4. Click "Install" in the popup
5. App opens in standalone window
6. Access from Start Menu/Applications

## Technical Implementation

### Manifest Configuration

**File:** `public/manifest.json`

```json
{
  "name": "MyGrowNet",
  "short_name": "MyGrowNet",
  "start_url": "/mobile-dashboard",
  "display": "standalone",
  "theme_color": "#2563eb",
  "background_color": "#ffffff"
}
```

### Service Worker

**File:** `public/sw.js`

Handles:
- Offline caching
- Background sync
- Push notifications (future)

### Install Prompt Component

**File:** `resources/js/components/Mobile/InstallPrompt.vue`

- Automatically shows install prompt to eligible users
- Can be dismissed (stored in localStorage)
- Provides "Install App" button
- Shows app benefits

### PWA Composable

**File:** `resources/js/composables/usePWA.ts`

Provides:
- `showInstallPrompt` - Whether to show install UI
- `isInstalled` - Whether app is installed
- `isStandalone` - Whether running as installed app
- `installApp()` - Trigger installation
- `dismissInstallPrompt()` - Hide prompt

## App Shortcuts

When installed, users can long-press the app icon to access shortcuts:

1. **Dashboard** - Opens mobile dashboard
2. **Wallet** - Opens wallet page
3. **Network** - Opens network page

## Benefits for Users

### Mobile Users
- **No App Store** - Install directly from website
- **Smaller Size** - No large download required
- **Auto Updates** - Always latest version
- **Home Screen Icon** - Quick access like native app
- **Full Screen** - No browser UI clutter
- **Offline Access** - View cached content offline

### Desktop Users
- **Standalone Window** - Separate from browser
- **Start Menu/Dock** - Easy access
- **Notifications** - Desktop notifications (future)
- **Keyboard Shortcuts** - App-specific shortcuts

## User Experience

### First Visit
1. User visits website on mobile
2. After a few seconds, install prompt appears
3. User can install or dismiss
4. If dismissed, can still install from browser menu

### Installed App
1. Opens directly to mobile dashboard
2. No browser UI visible
3. Feels like native app
4. Can switch to desktop view from settings

### Updates
- App updates automatically when new version deployed
- No user action required
- Service worker handles cache updates

## Testing Installation

### Development
```bash
# Serve over HTTPS (required for PWA)
php artisan serve --host=0.0.0.0 --port=8000

# Access from mobile device on same network
https://192.168.x.x:8000
```

### Production
- Must be served over HTTPS
- Valid SSL certificate required
- Service worker only works on HTTPS

## Browser Support

| Browser | Platform | Install Support | Offline Support |
|---------|----------|----------------|-----------------|
| Chrome | Android | ✅ Full | ✅ Full |
| Chrome | Desktop | ✅ Full | ✅ Full |
| Edge | Android | ✅ Full | ✅ Full |
| Edge | Desktop | ✅ Full | ✅ Full |
| Safari | iOS | ⚠️ Manual | ⚠️ Limited |
| Safari | macOS | ⚠️ Manual | ⚠️ Limited |
| Firefox | Android | ⚠️ Limited | ✅ Full |
| Firefox | Desktop | ❌ No | ✅ Full |

## Troubleshooting

### Install Prompt Not Showing

**Possible Causes:**
1. Not served over HTTPS
2. Already installed
3. User dismissed recently
4. Browser doesn't support PWA
5. Manifest.json not found

**Solutions:**
- Check HTTPS is enabled
- Clear browser data and revisit
- Use browser menu to install manually
- Check browser console for errors

### App Not Working Offline

**Possible Causes:**
1. Service worker not registered
2. Cache not populated
3. Network-only resources

**Solutions:**
- Check service worker in DevTools
- Visit pages while online first
- Check sw.js configuration

### Icons Not Showing

**Possible Causes:**
1. Icon files missing
2. Wrong path in manifest
3. Wrong sizes specified

**Solutions:**
- Verify files exist in `/public/images/`
- Check manifest.json paths
- Regenerate icons if needed

## Icon Requirements

### Sizes Needed
- 72x72 (Android)
- 96x96 (Android)
- 128x128 (Android)
- 144x144 (Android)
- 152x152 (iOS)
- 192x192 (Android, Chrome)
- 384x384 (Android)
- 512x512 (Android, Splash)

### Format
- PNG format
- Transparent or solid background
- Square aspect ratio
- Purpose: "any maskable"

## Future Enhancements

### Planned Features
- [ ] Push notifications for commissions
- [ ] Background sync for offline actions
- [ ] Biometric authentication
- [ ] Share target (share to app)
- [ ] File handling
- [ ] Periodic background sync

### Considerations
- App store submission (optional)
- Deep linking
- App badging
- Contact picker integration

## Marketing to Users

### Messaging
"Install MyGrowNet on your phone for quick access to your dashboard, wallet, and network - no app store needed!"

### Benefits to Highlight
- Instant access from home screen
- Works offline
- Faster than website
- No download from app store
- Always up to date

## Summary

MyGrowNet's PWA implementation provides a native app-like experience without requiring app store distribution. Users can install directly from the website and enjoy offline access, home screen icons, and full-screen operation. The implementation is production-ready and works across all major browsers and platforms.
