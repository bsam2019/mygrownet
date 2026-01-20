# GrowBuilder Mobile & PWA Fix

**Last Updated:** January 20, 2026  
**Status:** In Progress

## Issue

When loading chisambofarms.mygrownet.com on mobile:
1. PWA install prompt shows MyGrowNet logo instead of Chisambo Farms branding
2. Site shows blank page on mobile devices
3. Desktop rendering works correctly

## Root Causes

### 1. Static PWA Manifest
- All GrowBuilder subdomains were using the static `/manifest.json` file
- This manifest contains MyGrowNet branding, not site-specific branding
- Mobile browsers cache the manifest aggressively

### 2. Potential Asset Loading Issues
- Mobile browsers may have stricter CORS policies
- JavaScript bundle might not be loading correctly on mobile
- Viewport meta tags might need adjustment

## Implementation

### Files Modified

1. **app/Http/Controllers/GrowBuilder/ManifestController.php** (NEW)
   - Dynamic manifest generation for each GrowBuilder site
   - Uses site's logo, favicon, theme colors, and name
   - Returns proper JSON manifest with site-specific branding

2. **routes/growbuilder.php**
   - Added route: `GET /sites/{subdomain}/manifest.json`
   - Route accessible without authentication

3. **resources/views/app.blade.php**
   - Updated to use dynamic manifest path for GrowBuilder subdomains
   - Changed from `/manifest.json` to `/sites/{subdomain}/manifest.json`
   - Passes subdomain from Inertia page props

4. **app/Http/Controllers/GrowBuilder/RenderController.php**
   - Ensures subdomain is passed in site props (already correct)

## Testing Steps

### 1. Clear Browser Cache
```bash
# On mobile device:
# 1. Open browser settings
# 2. Clear browsing data
# 3. Select "Cached images and files"
# 4. Clear data
```

### 2. Test Dynamic Manifest
```bash
# Visit in browser:
https://chisambofarms.mygrownet.com/sites/chisambofarms/manifest.json

# Should return JSON with:
# - name: "Chisambo Farms" (not MyGrowNet)
# - icons: Site's logo/favicon
# - theme_color: Site's primary color
```

### 3. Test PWA Install
1. Visit site on mobile
2. Trigger "Add to Home Screen"
3. Verify icon shows Chisambo Farms branding
4. Verify app name is "Chisambo Farms"

### 4. Test Page Rendering
1. Visit https://chisambofarms.mygrownet.com on mobile
2. Check browser console for errors (use remote debugging)
3. Verify page content loads
4. Check network tab for failed requests

## Deployment

```bash
# SSH into server
ssh user@server

# Navigate to project
cd /path/to/project

# Pull changes
git pull origin main

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

## Troubleshooting

### Issue: Manifest Still Shows MyGrowNet

**Cause:** Browser cached old manifest  
**Solution:**
1. Clear browser cache completely
2. Use incognito/private mode
3. Add version query parameter: `manifest.json?v=2`

### Issue: Page Still Blank on Mobile

**Possible Causes:**
1. JavaScript errors - check console
2. Asset loading failures - check network tab
3. CORS issues - check response headers
4. Viewport issues - check meta tags

**Debug Steps:**
```bash
# Enable remote debugging on mobile:
# Chrome: chrome://inspect
# Safari: Settings > Safari > Advanced > Web Inspector

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

### Issue: Assets Not Loading

**Check CORS Headers:**
```nginx
# In nginx config, ensure:
add_header Access-Control-Allow-Origin "*" always;
add_header Access-Control-Allow-Methods "GET, POST, OPTIONS" always;
add_header Access-Control-Allow-Headers "Content-Type, Authorization" always;
```

**Check Asset URLs:**
- Ensure assets use HTTPS
- Verify asset paths are absolute
- Check Vite manifest is built correctly

## Mobile-Specific Considerations

### Viewport Meta Tag
Already correct in `app.blade.php`:
```html
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
```

### PWA Meta Tags
Already correct for GrowBuilder sites:
```html
<meta name="theme-color" content="{site.theme.primaryColor}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{site.name}">
```

### Splash Screen
Disabled for GrowBuilder sites (correct):
```php
$showSplash = false; // For GrowBuilder sites
```

## Next Steps

1. **Deploy changes** to production server
2. **Test on actual mobile device** (not just responsive mode)
3. **Clear all caches** (browser, server, CDN if applicable)
4. **Monitor logs** for any errors during mobile access
5. **Test PWA installation** with new manifest

## Additional Debugging

### Check if Site is Published
```bash
php artisan tinker
>>> $site = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('subdomain', 'chisambofarms')->first();
>>> $site->status; // Should be 'published'
>>> $site->pages()->where('is_published', true)->count(); // Should be > 0
```

### Check DetectSubdomain Middleware Logs
```bash
# Check logs for subdomain detection
grep "DetectSubdomain" storage/logs/laravel.log | tail -20
```

### Test Manifest Generation
```bash
# Test manifest controller directly
php artisan tinker
>>> $controller = app(\App\Http\Controllers\GrowBuilder\ManifestController::class);
>>> $response = $controller->manifest(request(), 'chisambofarms');
>>> $response->getContent();
```

## Success Criteria

- [ ] Dynamic manifest returns site-specific branding
- [ ] PWA install shows correct logo and name
- [ ] Page renders correctly on mobile devices
- [ ] No JavaScript errors in mobile console
- [ ] All assets load successfully
- [ ] Theme colors match site configuration

## Changelog

### January 20, 2026
- Created ManifestController for dynamic PWA manifests
- Updated routes to serve dynamic manifests
- Modified app.blade.php to use dynamic manifest path
- Documented troubleshooting steps
