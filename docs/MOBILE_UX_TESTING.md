# Mobile UX Testing Guide

**Status:** Ready for Testing
**Date:** November 7, 2025

## ✅ Implementation Complete

All mobile UX components and PWA features have been implemented and built successfully.

## Quick Test Checklist

### 1. Local Testing (Desktop Browser)

```bash
# Start Laravel server
php artisan serve
```

Then visit: `http://localhost:8000`

**Check:**
- [ ] Site loads normally
- [ ] No console errors
- [ ] Desktop dashboard works

### 2. Mobile Dashboard Test

Visit: `http://localhost:8000/mygrownet/mobile-dashboard`

**Check:**
- [ ] Mobile dashboard loads
- [ ] Bottom navigation visible
- [ ] Balance card displays
- [ ] Stats cards show data
- [ ] Collapsible sections work
- [ ] All navigation links work

### 3. PWA Features Test (Requires HTTPS)

For PWA features, you need HTTPS. Use ngrok:

```bash
# In terminal 1: Start Laravel
php artisan serve

# In terminal 2: Start ngrok
ngrok http 8000
```

Copy the HTTPS URL (e.g., `https://abc123.ngrok.io`)

### 4. Mobile Device Testing

**On Android (Chrome):**
1. Visit ngrok HTTPS URL on mobile
2. Wait 30 seconds for install prompt
3. Tap "Install MyGrowNet"
4. Check home screen for app icon
5. Open app from home screen
6. Verify standalone mode (no browser UI)
7. Test navigation
8. Turn off WiFi - verify offline works

**On iOS (Safari):**
1. Visit ngrok HTTPS URL on mobile
2. Tap Share button (square with arrow)
3. Scroll down, tap "Add to Home Screen"
4. Tap "Add"
5. Check home screen for app icon
6. Open app from home screen
7. Test navigation

### 5. Chrome DevTools Testing

1. Open Chrome DevTools (F12)
2. Go to "Application" tab
3. Check "Manifest" section:
   - [ ] Name: MyGrowNet
   - [ ] Icons: 8 icons listed
   - [ ] Theme color: #2563eb
4. Check "Service Workers" section:
   - [ ] Status: Activated and running
   - [ ] Scope: /
5. Go to "Lighthouse" tab
6. Select "Progressive Web App"
7. Click "Generate report"
8. Target score: 90+

### 6. Responsive Design Test

In Chrome DevTools:
1. Toggle device toolbar (Ctrl+Shift+M)
2. Test these breakpoints:
   - [ ] Mobile (375px) - Bottom nav visible
   - [ ] Tablet (768px) - Bottom nav visible
   - [ ] Desktop (1024px) - Sidebar visible

## Expected Behavior

### Desktop (≥1024px)
- Sidebar navigation visible
- Regular dashboard layout
- No bottom navigation
- Install prompt may appear

### Mobile (<1024px)
- Sidebar hidden
- Bottom navigation visible
- Mobile-optimized dashboard
- Collapsible sections
- Install prompt appears

## Common Issues & Solutions

### Install Prompt Not Showing

**Possible causes:**
- Not using HTTPS (required for PWA)
- Already dismissed prompt
- Browser doesn't support PWA

**Solutions:**
- Use ngrok for HTTPS
- Clear browser data
- Try incognito mode
- Test on different device

### Service Worker Not Registering

**Check:**
- Console for errors
- `/sw.js` is accessible
- HTTPS is enabled

**Fix:**
```bash
# Clear cache
php artisan cache:clear

# Rebuild
npm run build

# Hard refresh browser (Ctrl+Shift+R)
```

### Icons Not Loading

**Check:**
- Files exist in `public/images/`
- Visit `/images/icon-192x192.png` directly
- Check manifest.json paths

**Fix:**
```bash
# Regenerate icons
php scripts/generate-pwa-icons.php

# Rebuild
npm run build
```

### Mobile Dashboard Not Loading

**Check:**
- Route exists in `routes/web.php`
- User is authenticated
- No console errors

**Fix:**
```bash
# Clear route cache
php artisan route:clear

# Check route exists
php artisan route:list | grep mobile-dashboard
```

## Performance Metrics

### Target Metrics:
- First Contentful Paint: <2s
- Time to Interactive: <3s
- Lighthouse PWA Score: 90+
- Mobile-friendly: Yes

### Check Performance:
1. Open Chrome DevTools
2. Go to "Lighthouse" tab
3. Select "Performance" + "Progressive Web App"
4. Click "Generate report"

## Files to Check

### Frontend:
- `resources/js/pages/MyGrowNet/MobileDashboard.vue`
- `resources/js/Components/Mobile/*.vue`
- `resources/js/composables/usePWA.ts`
- `resources/js/layouts/app/AppSidebarLayout.vue`

### Public:
- `public/manifest.json`
- `public/sw.js`
- `public/images/icon-*.png` (8 files)

### Backend:
- `routes/web.php` (mobile-dashboard route)
- `resources/views/app.blade.php` (PWA meta tags)

## Success Criteria

You're ready for production when:

- [x] All Phase 1 tasks complete (files created)
- [x] All Phase 2 tasks complete (integration)
- [ ] All Phase 3 tests pass (testing)
- [ ] PWA installs on mobile
- [ ] App works offline
- [ ] No console errors
- [ ] Lighthouse score 90+

## Next Steps

1. **Test locally** - Verify everything works on desktop
2. **Test with ngrok** - Test PWA features with HTTPS
3. **Test on mobile** - Install and test on real devices
4. **Fix any issues** - Address any problems found
5. **Deploy to production** - Once all tests pass

## Support

If you encounter issues:
1. Check console for errors
2. Review this testing guide
3. Check `docs/MOBILE_UX_IMPROVEMENT.md` for details
4. Verify all files are in place

---

**Remember:** PWA features require HTTPS. Use ngrok for local testing!
