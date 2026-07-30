# Workspace App Launch Fix - Production Deployment

## Problem
- CORS errors when clicking apps in workspace
- Backend route changed from POST to GET ✓
- Frontend code already updated in AppTile.vue ✓
- **But production still running old JavaScript bundle that uses POST**

## Solution: Deploy Backend + Rebuild Main Module

### Step 1: Deploy Backend Changes (1 minute)

```bash
# SSH to production
ssh root@138.197.187.134

# Pull latest code
cd /var/www/mygrownet.com
git pull origin main

# Clear and rebuild caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan route:cache
php artisan config:cache
php artisan optimize
```

### Step 2: Build Main Module Only (5-10 minutes)

**On your local machine:**

```bash
# Build only the main app (app.ts) - NOT the full build
npm run build:main
```

This builds ONLY `resources/js/app.ts` which contains the workspace AppTile component.

Output will be in: `public/build/assets/`

### Step 3: Deploy Main Assets to Production

**Option A: Using rsync (recommended)**

```bash
# From local project root
rsync -avz --progress public/build/assets/ root@138.197.187.134:/var/www/mygrownet.com/public/build/assets/
```

**Option B: Manual SCP**

```bash
# Upload the built assets
scp -r public/build/assets/* root@138.197.187.134:/var/www/mygrownet.com/public/build/assets/
```

**Option C: Use existing deploy-assets.sh script**

```bash
# If the script exists
./deployment/deploy-assets.sh
```

### Step 4: Clear Browser Cache + Test

1. **Hard refresh** the workspace page: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)
2. Or open in **incognito/private window**
3. Click any app tile (e.g., GrowBuilder, GrowMart)
4. Should navigate cleanly to subdomain without CORS errors

## Expected Behavior After Fix

**Before:**
- Click app → Brief flash of `/workspace/launch/5` → CORS error → Stuck

**After:**
- Click app → Browser navigates to `https://mygrownet.com/workspace/launch/5`
- Backend returns 302 redirect to `https://growbuilder.mygrownet.com/dashboard`
- Browser follows redirect → User lands on subdomain dashboard
- No CORS, clean navigation, correct entry point

## Why This Works

1. **Route is GET** (line 385 in routes/web.php) ✓
2. **Frontend uses window.location.href** (AppTile.vue already correct) ✓
3. **New JavaScript bundle deployed** (Step 2-3) → Browser loads new code
4. **No AJAX/XMLHttpRequest** → No CORS preflight → Clean navigation

## Troubleshooting

### If CORS errors persist after deployment:
- Check browser console: Should see GET request, not POST
- If still POST: Hard refresh not working, clear all site data
- Chrome: DevTools → Application → Clear storage → Clear site data

### If landing on wrong page (e.g., public page not dashboard):
- Check `AppLaunchService::buildLaunchUrl()` - should append `/dashboard` to subdomain URLs
- Check subdomain's `DetectSubdomain` middleware - should detect subdomain correctly
- Check `domains` table - verify subdomain entries exist (run `ApplicationDomainsSeeder`)

### If 404 on /workspace/launch/X:
- Route cache not cleared - run `php artisan route:clear && php artisan route:cache`
- Check route exists: `php artisan route:list | grep workspace.launch`

### If session lost on subdomain:
- Check `.env` has `SESSION_DOMAIN=.mygrownet.com` (with leading dot)
- Restart queue worker: `php artisan queue:restart`

## Files Modified

- ✅ `routes/web.php` line 385 - POST → GET
- ✅ `resources/js/Components/Workspace/AppTile.vue` - router.post() → window.location.href
- ✅ `database/seeders/ApplicationRegistrySeeder.php` - URLs now subdomain format
- ✅ `app/Domain/Workspace/Services/AppLaunchService.php` - appends /dashboard

## No Need to Rebuild Other Modules

Only the **main module** (`app.ts`) needs rebuilding because:
- AppTile.vue is only imported by workspace pages
- Workspace pages use the main app entry point
- Other modules (bizboost, growmart, etc.) don't need workspace launching code

Full rebuild would take 30+ minutes and risks memory errors. This fix takes <10 minutes.
