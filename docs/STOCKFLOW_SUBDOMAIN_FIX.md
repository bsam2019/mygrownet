# StockFlow Subdomain Login Fix

## Issue
When visiting `https://taradasi.mygrownet.com/login`, the system shows these errors:
1. WebSocket connection errors (expected if Reverb is not configured)
2. **Page not found: ./pages/auth/Login.vue** (CRITICAL - wrong component path)

## Root Cause Analysis

The error "Page not found: ./pages/auth/Login.vue" indicates that Inertia is looking for the wrong Vue component. The correct path should be `./pages/StockAudit/Login.vue`.

### Possible Causes:
1. **Production cache issue** - Route cache pointing to old component names
2. **Frontend build outdated** - Built assets don't include StockAudit components
3. **Middleware chain issue** - DetectSubdomain not setting company ID correctly
4. **Route matching issue** - Wrong route being matched (web.php login instead of stockflow-subdomain.php login)

## Solution Steps

### Step 1: Verify Route Configuration on Production

```bash
cd /var/www/mygrownet.com

# Check if stockflow.sub.login route exists
php artisan route:list --name=stockflow.sub.login

# Should show:
# GET|HEAD  {account}.mygrownet.com/login  stockflow.sub.login
```

### Step 2: Clear All Caches

```bash
# Clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Optimize
php artisan optimize
```

### Step 3: Check Frontend Build

```bash
# Verify StockAudit Login component exists in build
ls -la public/build/assets/ | grep -i stock

# Check if manifest includes StockAudit pages
cat public/build/manifest.json | grep -i stockaudit
```

### Step 4: Rebuild Frontend if Needed

**IMPORTANT:** Do NOT run `npm run build` on the production server!

Instead:
1. Build locally on development machine:
   ```bash
   npm run build
   ```

2. Upload the `public/build/` directory to production:
   ```bash
   scp -r public/build/* sammy@138.197.187.134:/var/www/mygrownet.com/public/build/
   ```

### Step 5: Verify DetectSubdomain Middleware

The middleware should:
1. Detect `taradasi.mygrownet.com` subdomain
2. Look up company in database
3. Set `stock_audit_company_id` in request attributes
4. Pass request to next middleware/routes

Check logs:
```bash
tail -50 storage/logs/laravel.log
```

### Step 6: Test Route Matching

```bash
# Test with curl to see which route matches
curl -v https://taradasi.mygrownet.com/login

# Should return:
# - 200 OK
# - Inertia-enabled response
# - X-Inertia header
```

### Step 7: Check Database

```bash
php artisan tinker

# Check if Taradasi company exists
\App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::where('subdomain', 'taradasi')->first();

# Should return company with:
# - id
# - name: "Taradasi Dental Clinic"
# - subdomain: "taradasi"
# - status: "active"
```

## Quick Fix Script

Run this on production:

```bash
#!/bin/bash
cd /var/www/mygrownet.com

echo "Pulling latest changes..."
git pull origin main

echo "Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Rebuilding caches..."
php artisan config:cache
php artisan route:cache

echo "Optimizing..."
php artisan optimize

echo "Restarting PHP-FPM..."
sudo systemctl restart php8.2-fpm

echo "Testing..."
curl -sI https://taradasi.mygrownet.com/login | head -n 1

echo "Done!"
```

## Debugging Steps

### 1. Enable Debug Logging

Add to `AuthController::showLogin()`:

```php
\Log::info('StockFlow Login Debug', [
    'route_name' => $request->route()?->getName(),
    'company_id' => $request->attributes->get('stock_audit_company_id'),
    'host' => $request->getHost(),
    'subdomain' => explode('.', $request->getHost())[0],
]);
```

### 2. Check Inertia Component Resolution

In `resources/js/app.ts`, the page resolver uses:

```javascript
resolvePageComponent(
    `./pages/${name}.vue`,
    import.meta.glob<DefineComponent>('./pages/**/*.vue')
)
```

This means for `Inertia::render('StockAudit/Login')`, it will look for `./pages/StockAudit/Login.vue`.

### 3. Verify Component Exists

```bash
ls -la resources/js/pages/StockAudit/Login.vue
# Should exist

# Check if it's included in the Vite build
cat vite.config.ts | grep -A 10 input
```

## Common Issues & Solutions

### Issue: "Page not found: ./pages/auth/Login.vue"

**Cause:** Inertia is looking for the wrong component path.

**Solutions:**
1. Check if controller is really returning `'StockAudit/Login'`
2. Clear route cache on production
3. Rebuild frontend with latest code
4. Check if route is matching correctly (should be `stockflow.sub.login`, not `login`)

### Issue: WebSocket Connection Refused

**Cause:** Reverb is not configured or running.

**Solutions:**
1. This is expected if Reverb is not set up
2. Can be ignored for StockFlow (it doesn't need real-time features)
3. To fix: Configure Reverb in `.env` and start Reverb server

### Issue: 404 Not Found

**Cause:** Route not matching or company not found.

**Solutions:**
1. Verify company exists: `SELECT * FROM sa_companies WHERE subdomain = 'taradasi'`
2. Check if `DetectSubdomain` middleware is running
3. Verify `stockflow-subdomain.php` is loaded before `web.php` in `bootstrap/app.php`

## Files Modified in This Fix

1. `app/Http/Controllers/StockAudit/AuthController.php` - Added debug logging
2. `docs/STOCKFLOW_SUBDOMAIN_FIX.md` - This file
3. `fix-stockflow-production.sh` - Quick fix script

## Testing

After applying the fix:

```bash
# 1. Test login page loads
curl -I https://taradasi.mygrownet.com/login

# 2. Test with browser
# Visit: https://taradasi.mygrownet.com/login
# Expected: Login form with Taradasi branding

# 3. Test login
# Use credentials from sa_users table
# Should redirect to dashboard after successful login
```

## Additional Notes

- The `__sfSubdomain` flag in `app.blade.php` is set only when route name starts with `stockflow.sub.`
- This flag disables WebSocket/Reverb and service workers for StockFlow subdomains
- The `stockflow.company` middleware validates that the subdomain matches an active company
- The `DetectSubdomain` middleware must run before routes are matched (it's prepended to web middleware)

## Related Files

- `routes/stockflow-subdomain.php` - StockFlow subdomain routes
- `app/Http/Middleware/DetectSubdomain.php` - Subdomain detection logic
- `app/Http/Middleware/StockFlowCompany.php` - Company validation middleware
- `resources/js/pages/StockAudit/Login.vue` - Login component
- `resources/views/app.blade.php` - Main blade template (sets __sfSubdomain flag)
