# Mobile White Screen Fix

**Issue Date:** November 13, 2025  
**Status:** ✅ RESOLVED

## Problem

Mobile devices were showing a white screen when accessing the MyGrowNet platform, while desktop browsers loaded correctly.

## Root Cause

The Vite build process was placing the `manifest.json` file in `public/build/manifest.json`, but Laravel was looking for it in `public/build/.vite/manifest.json`. This caused the frontend assets to fail loading on mobile devices (which are more strict about asset loading).

## Solution Applied

### 1. Immediate Fix (Production)
```bash
# Created .vite directory and copied manifest
mkdir -p public/build/.vite
cp public/build/manifest.json public/build/.vite/manifest.json

# Cleared all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:cache
php artisan config:cache
```

### 2. Vite Configuration Update
Updated `vite.config.ts` to automatically place manifest in correct location:

```typescript
export default defineConfig({
    // ... other config
    build: {
        manifest: '.vite/manifest.json',
    },
    // ... rest of config
});
```

### 3. Deployment Script Update
Updated `deployment/force-deploy.sh` to include manifest fix:

```bash
echo "📦 Ensuring Vite manifest is in correct location..."
mkdir -p public/build/.vite
if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest copied to .vite directory"
fi
```

### 4. Standalone Fix Script
Created `scripts/fix-vite-manifest.sh` for quick fixes:

```bash
chmod +x scripts/fix-vite-manifest.sh
./scripts/fix-vite-manifest.sh
```

## Verification Steps

1. **Check manifest files exist:**
   ```bash
   ls -la public/build/manifest.json
   ls -la public/build/.vite/manifest.json
   ```

2. **Test on mobile device:**
   - Open https://mygrownet.com on mobile browser
   - Should load dashboard without white screen
   - Check browser console for any errors

3. **Test on desktop:**
   - Verify desktop still works correctly
   - No regression in functionality

## Prevention

### For Future Builds

1. **Always run after building:**
   ```bash
   npm run build
   ./scripts/fix-vite-manifest.sh
   ```

2. **Or use the updated Vite config** which automatically places manifest correctly

3. **Deployment checklist:**
   - [ ] Run `npm run build`
   - [ ] Verify manifest in both locations
   - [ ] Clear Laravel caches
   - [ ] Test on mobile device

## Technical Details

### Why This Happened

- Laravel Vite plugin expects manifest at `public/build/.vite/manifest.json`
- Default Vite build outputs to `public/build/manifest.json`
- Mobile browsers are stricter about asset loading failures
- Desktop browsers may have cached the old working version

### Files Modified

1. `vite.config.ts` - Added build.manifest configuration
2. `deployment/force-deploy.sh` - Added manifest copy step
3. `deployment/deploy-with-assets.sh` - Added manifest copy step (local & server)
4. `deployment/deploy-with-build.sh` - Added manifest copy step (server)
5. `scripts/fix-vite-manifest.sh` - New standalone fix script

## Related Issues

- Mobile-specific rendering issues
- Asset loading failures
- Inertia.js page component resolution

## Testing Checklist

- [x] Mobile Chrome - Working
- [x] Mobile Safari - Working  
- [x] Desktop Chrome - Working
- [x] Desktop Firefox - Working
- [x] Caches cleared
- [x] Routes cached
- [x] Config cached

## Quick Reference

**If white screen appears again:**

```bash
# SSH to server
ssh sammy@138.197.187.134

# Navigate to project
cd /var/www/mygrownet.com

# Run fix
mkdir -p public/build/.vite
cp public/build/manifest.json public/build/.vite/manifest.json

# Clear caches
php artisan optimize:clear
php artisan optimize

# Test
curl -I https://mygrownet.com
```

## Server Details

- **Server IP:** 138.197.187.134
- **User:** sammy
- **Project Path:** /var/www/mygrownet.com
- **Domain:** https://mygrownet.com

## Notes

- This fix is now permanent with the Vite config update
- Deployment script will automatically apply fix on future deploys
- No code changes required in Vue components
- No database changes required
