# StockFlow Subdomain Issue - Complete Summary

## Problem Description

When visiting `https://taradasi.mygrownet.com/login`, the following errors appear in the browser console:

1. **WebSocket connection errors** (expected, can be ignored)
2. **Critical: "Page not found: ./pages/auth/Login.vue"** - Inertia.js is looking for the wrong Vue component

## Root Cause

The error indicates that Inertia is trying to load `./pages/auth/Login.vue` instead of the correct path `./pages/StockAudit/Login.vue`.

### Possible Causes:
1. **Route cache outdated** - Production route cache might be stale
2. **Frontend build outdated** - Built assets don't include StockAudit components
3. **Middleware chain issue** - DetectSubdomain not properly detecting the company
4. **Wrong route matching** - web.php login route matching instead of stockflow-subdomain.php login route

## Architecture Overview

### How StockFlow Subdomains Work:

1. **DNS Level**: `taradasi.mygrownet.com` points to droplet (138.197.187.134)

2. **DetectSubdomain Middleware** (runs first, prepended to web middleware):
   - Detects subdomain from hostname
   - Looks up company in database (`sa_companies` table)
   - Sets `stock_audit_company_id` in request attributes
   - Configures subdomain URL

3. **Route Matching** (`routes/stockflow-subdomain.php` loaded before `web.php`):
   ```php
   Route::domain('{account}.mygrownet.com')
       ->middleware(['web', 'stockflow.company'])
       ->group(function () {
           Route::get('/login', [AuthController::class, 'showLogin'])
               ->name('stockflow.sub.login');
       });
   ```

4. **StockFlowCompany Middleware**:
   - Validates `stock_audit_company_id` exists
   - Stores company ID in session
   - Aborts with 404 if company not found

5. **AuthController**:
   ```php
   return Inertia::render('StockAudit/Login', [
       'company' => $company->toArray(),
   ]);
   ```

6. **Frontend Resolution** (`resources/js/app.ts`):
   ```javascript
   resolvePageComponent(
       `./pages/${name}.vue`,
       import.meta.glob('./pages/**/*.vue')
   )
   ```
   Should resolve: `StockAudit/Login` → `./pages/StockAudit/Login.vue`

7. **Blade Template** (`resources/views/app.blade.php`):
   - Sets `window.__sfSubdomain = true` when route name starts with `stockflow.sub.`
   - Disables WebSocket/Reverb for StockFlow subdomains
   - Disables service workers for StockFlow subdomains

## Solution Applied

### Changes Made:

1. **Added Debug Logging** (`app/Http/Controllers/StockAudit/AuthController.php`):
   ```php
   \Log::info('StockFlow Login Debug', [
       'route_name' => $request->route()?->getName(),
       'company_id' => $request->attributes->get('stock_audit_company_id'),
       'host' => $request->getHost(),
   ]);
   ```

2. **Created Comprehensive Troubleshooting Guide**:
   - `docs/STOCKFLOW_SUBDOMAIN_FIX.md` - Technical deep dive
   - `DEPLOY_STOCKFLOW_FIX.md` - Step-by-step deployment instructions
   - `fix-stockflow-production.sh` - Automated fix script

3. **Updated AGENTS.md** with the new issue and solution

## Deployment Instructions

### Quick Fix (Run on Production Server):

```bash
# SSH to server
ssh sammy@138.197.187.134

# Navigate to project
cd /var/www/mygrownet.com

# Pull latest changes
git pull origin main

# Run automated fix
bash fix-stockflow-production.sh
```

### Manual Fix (if automated fails):

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Optimize
php artisan optimize

# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Test
curl -I https://taradasi.mygrownet.com/login
```

## Verification Steps

1. **Check Routes**:
   ```bash
   php artisan route:list --name=stockflow.sub.login
   ```

2. **Test in Browser**:
   - Visit: https://taradasi.mygrownet.com/login
   - Should show Taradasi Dental Clinic branding
   - Should display login form

3. **Check Logs**:
   ```bash
   tail -50 storage/logs/laravel.log
   ```
   Look for debug messages with route name and company ID

4. **Test Login**:
   - Email: `admin@taradasi.com`
   - Password: `password`
   - Should redirect to dashboard

## If Issue Persists

### Check Frontend Build:

The frontend assets might be outdated. Verify:

```bash
ls -la public/build/assets/ | grep -i stock
cat public/build/manifest.json | grep -i stockaudit
```

If StockAudit components are missing, rebuild:

**On Development Machine:**
```bash
npm run build
```

**Upload to Production:**
```bash
scp -r public/build/* sammy@138.197.187.134:/var/www/mygrownet.com/public/build/
```

### Check Database:

Verify Taradasi company exists:

```bash
php artisan tinker

\App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::where('subdomain', 'taradasi')->first();
```

If missing, run seeder:
```bash
php artisan db:seed --class=StockAuditSeeder
```

## Files Modified

1. `app/Http/Controllers/StockAudit/AuthController.php` - Added debug logging
2. `docs/STOCKFLOW_SUBDOMAIN_FIX.md` - Technical troubleshooting guide
3. `DEPLOY_STOCKFLOW_FIX.md` - Deployment instructions
4. `fix-stockflow-production.sh` - Automated fix script
5. `AGENTS.md` - Updated with new issue
6. `STOCKFLOW_ISSUE_SUMMARY.md` - This file

## Key Files Reference

### Backend:
- `routes/stockflow-subdomain.php` - StockFlow subdomain routes
- `app/Http/Middleware/DetectSubdomain.php` - Subdomain detection
- `app/Http/Middleware/StockFlowCompany.php` - Company validation
- `app/Http/Controllers/StockAudit/AuthController.php` - Authentication controller
- `bootstrap/app.php` - Route loading order (stockflow-subdomain.php BEFORE web.php)

### Frontend:
- `resources/js/pages/StockAudit/Login.vue` - Login component
- `resources/js/app.ts` - Page resolver configuration
- `resources/js/modules/createApp.ts` - Inertia setup, __sfSubdomain handling
- `resources/views/app.blade.php` - Main Blade template, sets __sfSubdomain flag

### Database:
- `database/migrations/stockflow/*.php` - StockFlow tables (sa_* prefix)
- `database/seeders/StockAuditSeeder.php` - Sample data (Taradasi)

## Expected Behavior After Fix

1. ✅ Visit https://taradasi.mygrownet.com/login
2. ✅ See Taradasi Dental Clinic branding (green theme)
3. ✅ Login form with email and password fields
4. ✅ WebSocket errors in console (expected, can be ignored)
5. ✅ No "Page not found" errors
6. ✅ Successful login redirects to StockFlow dashboard
7. ✅ Dashboard shows company-specific data

## Notes

- WebSocket errors are expected (Reverb not configured for StockFlow)
- Service workers are automatically disabled for StockFlow subdomains
- The `__sfSubdomain` flag prevents main site features from loading
- StockFlow uses a separate auth guard (`stockflow`) instead of `web`
- Company subdomain must be unique and registered in `sa_companies` table

## Next Steps

1. Deploy the fix to production using instructions above
2. Test with browser at https://taradasi.mygrownet.com/login
3. Verify debug logs show correct information
4. If issue persists, check frontend build and database
5. Document any additional findings in AGENTS.md

## Timeline

- **Issue Reported**: [Current session]
- **Fix Developed**: [Current session]
- **Deployment**: Pending
- **Estimated Fix Time**: 5-10 minutes
- **Risk Level**: Low (only cache clear and debug logging)
