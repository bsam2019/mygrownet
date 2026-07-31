# Subdomain Fix Summary

## Changes Made

### 1. StockFlow Logout Route Fix
**Problem:** `Error: Ziggy error: 'account' parameter is required for route 'stockflow.sub.logout'`

**Solution:**
- Updated `routes/stockflow-subdomain.php` line 67 - moved logout route outside auth group but kept auth middleware
- Updated `resources/js/composables/useStockflowRoute.ts` - added `noAccountRoutes` array to exclude auth routes from auto-account injection
- Routes like `stockflow.sub.logout`, `stockflow.sub.login`, and `stockflow.sub.login.store` now use subdomain for account resolution instead of URL parameter

**Files Changed:**
- `routes/stockflow-subdomain.php`
- `resources/js/composables/useStockflowRoute.ts`

### 2. GrowFinance Subdomain Setup
**Problem:** `GET https://growfinance.mygrownet.com/ 404 (Not Found)`

**Solution:**
- Created `routes/growfinance-subdomain.php` with auth and landing routes
- Created `app/Http/Controllers/GrowFinance/AuthController.php`
- Created Vue pages:
  - `resources/js/Pages/GrowFinance/Landing.vue`
  - `resources/js/Pages/GrowFinance/Auth/Login.vue`
  - `resources/js/Pages/GrowFinance/Auth/Register.vue`
- Registered routes in `bootstrap/app.php` before main web.php
- `app/Http/Middleware/DetectSubdomain.php` already has GrowFinance handler (added in previous session)

**Files Created:**
- `routes/growfinance-subdomain.php`
- `app/Http/Controllers/GrowFinance/AuthController.php`
- `resources/js/Pages/GrowFinance/Landing.vue`
- `resources/js/Pages/GrowFinance/Auth/Login.vue`
- `resources/js/Pages/GrowFinance/Auth/Register.vue`

**Files Modified:**
- `bootstrap/app.php` (added growfinance-subdomain.php route registration)

### 3. BMS 404 Investigation
**Problem:** `https://bms.mygrownet.com/ 404 error`

**Current Status:**
- Routes exist in `routes/bms-subdomain.php` ✅
- DetectSubdomain middleware has BMS handler ✅
- Blade view exists at `resources/views/bms.blade.php` ✅
- Domain seeded in `database/seeders/ApplicationDomainsSeeder.php` ✅
- HandleInertiaRequests middleware resolves BMS view from domains table ✅

**Possible Causes:**
1. **Domain record not in production database** - Seeder not run
2. **DNS not resolving** - `bms.mygrownet.com` A record missing
3. **Route cache stale** - Old routes cached without bms-subdomain.php
4. **Domain record inactive** - `is_active = 0` in domains table

**How to Debug on Production:**
```bash
# Check if domain exists in database
php artisan tinker
>>> \App\Domain\Core\Models\Domain::where('domain', 'bms.mygrownet.com')->first();

# Check DNS resolution
dig bms.mygrownet.com

# Check if BMS routes are loaded
php artisan route:list | grep 'bms.subdomain'

# Verify DetectSubdomain middleware is in middleware stack
php artisan route:list | grep 'web' | head -5
```

## Deployment Steps

### On Production Server:

1. **Pull latest code:**
   ```bash
   cd /var/www/mygrownet.com
   git pull origin main
   ```

2. **Run deployment script:**
   ```bash
   bash deploy-subdomain-fixes.sh
   ```

   Or manually:
   ```bash
   php artisan route:clear
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   php artisan route:cache
   php artisan config:cache
   php artisan optimize
   php artisan db:seed --class=ApplicationDomainsSeeder
   ```

3. **Verify domains table:**
   ```bash
   php artisan tinker
   >>> \App\Domain\Core\Models\Domain::where('domain', 'like', '%mygrownet.com')->get(['domain', 'type', 'is_active', 'application_id']);
   ```

4. **Test URLs:**
   - BMS: https://bms.mygrownet.com/
   - GrowFinance: https://growfinance.mygrownet.com/
   - StockFlow: https://taradasi.mygrownet.com/ (or your company subdomain)

5. **If BMS still 404:**
   - Check Apache/Nginx vhost configuration for wildcard subdomain support
   - Verify DNS: `dig bms.mygrownet.com` should point to server IP
   - Check route priority: `php artisan route:list | grep '^GET\s*/'` - subdomain routes should come before web.php domain-less `/`

## Testing Locally

1. **Add to hosts file:**
   ```
   127.0.0.1 bms.mygrownet.test
   127.0.0.1 growfinance.mygrownet.test
   127.0.0.1 taradasi.mygrownet.test
   ```

2. **Update .env:**
   ```
   APP_URL=http://mygrownet.test:8001
   SESSION_DOMAIN=.mygrownet.test
   ```

3. **Test URLs:**
   - http://bms.mygrownet.test:8001/
   - http://growfinance.mygrownet.test:8001/
   - http://taradasi.mygrownet.test:8001/

## Files Modified (Git Commit)

### Modified:
- `routes/stockflow-subdomain.php` - Fixed logout route
- `resources/js/composables/useStockflowRoute.ts` - Excluded auth routes from account parameter
- `bootstrap/app.php` - Added GrowFinance subdomain routes
- `database/seeders/ApplicationDomainsSeeder.php` - Already had `growfinance.mygrownet.com` (from previous session)
- `app/Http/Middleware/DetectSubdomain.php` - Already had GrowFinance handler (from previous session)

### Created:
- `routes/growfinance-subdomain.php`
- `app/Http/Controllers/GrowFinance/AuthController.php`
- `resources/js/Pages/GrowFinance/Landing.vue`
- `resources/js/Pages/GrowFinance/Auth/Login.vue`
- `resources/js/Pages/GrowFinance/Auth/Register.vue`
- `deploy-subdomain-fixes.sh`
- `SUBDOMAIN_FIX_SUMMARY.md` (this file)

## Known Issues

### BMS 404 - Not Fixed Yet
**Status:** Routes and code exist, but 404 persists
**Next Steps:**
1. Run seeder on production: `php artisan db:seed --class=ApplicationDomainsSeeder`
2. Check DNS resolution: `dig bms.mygrownet.com`
3. Check Apache vhost for wildcard subdomain support
4. Verify route cache: `php artisan route:list | grep bms.subdomain`

### GrowFinance - Needs Testing
**Status:** Routes and pages created, but not tested on production
**Next Steps:**
1. Build frontend assets: `npm run build` (manually by user, not in deploy script)
2. Deploy built assets to production
3. Test landing page, login, register flows

### StockFlow Logout - Fixed
**Status:** ✅ Fixed locally, needs production deployment
**Next Steps:**
1. Deploy to production
2. Test logout from StockFlow subdomain

## Architecture Notes

### Subdomain Routing Order
Routes in `bootstrap/app.php` are loaded in order. Subdomain routes MUST be loaded before `web.php` to match before domain-less routes:

```php
// ✅ Correct order:
Route::middleware('web')->group(base_path('routes/bms-subdomain.php'));  // Has Route::domain()
Route::middleware('web')->group(base_path('routes/web.php'));             // Has domain-less routes

// ❌ Wrong order would cause bms.mygrownet.com to match web.php routes
```

### Blade View Resolution
`HandleInertiaRequests::rootView()` resolves subdomain blade views in this order:
1. Check if Identity Gateway (`identity.layout`)
2. Check if StockFlow company subdomain (`stockflow`)
3. Lookup domain in `domains` table → resolve application slug → return as view name
4. Fallback to path-based detection (`/primeedge` → `primeedge`)
5. Fallback to default `app` view

For BMS to work, there MUST be an entry in `domains` table:
```
domain: bms.mygrownet.com
type: application
is_active: 1
application_id: <BMS application ID>
```

The application slug (`bms`) is used as the blade view name (`resources/views/bms.blade.php`).

## Commit
```
commit 01e5f6e
Fix: Add GrowFinance subdomain routes and fix StockFlow logout
```
