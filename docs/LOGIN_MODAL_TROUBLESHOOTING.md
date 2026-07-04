# Login Modal Troubleshooting Guide

## Symptom
When clicking "Sign In" in the login modal on production:
- Redirects to 404 page
- Redirects to old/different login page
- Gets stuck loading
- Shows CSRF/419 errors

## Root Causes & Solutions

### 1. Cached Routes (Most Common)

**Problem:** Production has old route cache that doesn't include POST /login

**Solution:**
```bash
ssh user@138.197.187.134
cd /var/www/mygrownet.com
php artisan route:clear
php artisan route:cache
```

**Verification:**
```bash
php artisan route:list --name=login --method=POST
```

Should show: `POST login Auth\BladeAuthController@login`

### 2. Missing POST Route Definition

**Problem:** Route file doesn't define POST /login

**Check:** `routes/web.php` should have:
```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [BladeAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [BladeAuthController::class, 'login']); // ← This line
    Route::get('/register', [BladeAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [BladeAuthController::class, 'register']);
});
```

### 3. Stale Frontend Build

**Problem:** Frontend JavaScript has cached old route definitions

**Solution:**
```bash
# Local development
npm run build

# Production (if building on server)
cd /var/www/mygrownet.com
npm run build

# Or upload built assets from local
# (Build locally, then rsync public/build/)
```

### 4. CSRF Token Mismatch

**Problem:** Session expired or CSRF token invalid

**Check browser console for:**
- 419 errors
- "CSRF token mismatch" messages

**Solution:**
- Clear browser cookies for mygrownet.com
- Refresh the page before logging in
- Check `SESSION_DRIVER` in .env (should be `file` or `database`)

**Verify session config:**
```bash
php artisan config:show session
```

### 5. Middleware Interference

**Problem:** DetectSubdomain or other middleware catching request

**Debug:**
Add logging to `app/Http/Middleware/DetectSubdomain.php`:
```php
public function handle(Request $request, Closure $next): Response
{
    $host = $request->getHost();
    \Log::info('DetectSubdomain', [
        'host' => $host,
        'path' => $request->path(),
        'method' => $request->method(),
    ]);
    
    // ... rest of middleware
}
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

### 6. Browser Cache

**Problem:** Browser has cached old JavaScript/CSS

**Solution:**
- Hard refresh: Ctrl+Shift+R (Windows/Linux) or Cmd+Shift+R (Mac)
- Clear browser cache completely
- Open in incognito/private window
- Check network tab in DevTools for 304 responses

## Debug Checklist

Run through these steps in order:

### Step 1: Verify Route Registration
```bash
php artisan route:list | grep -E "login|register"
```

Expected output includes:
- `GET|HEAD login`
- `POST login`
- `GET|HEAD register`
- `POST register`

### Step 2: Check Controller Exists
```bash
ls -la app/Http/Controllers/Auth/BladeAuthController.php
```

### Step 3: Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize
```

### Step 4: Test Route Directly
```bash
# GET request (should return login page HTML)
curl -I https://mygrownet.com/login

# POST request (should return 302 redirect or validation errors)
curl -X POST https://mygrownet.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Step 5: Check Browser Console
Open DevTools (F12) → Console tab

Look for:
- `[LoginModal] Submitting login to: /login` ← Confirms route resolution
- Any errors about missing routes
- 404 or 419 status codes in Network tab

### Step 6: Check Network Request
DevTools → Network tab → Submit login

Look at the POST request:
- URL should be: `https://mygrownet.com/login`
- Method: `POST`
- Status: Should be 302 (redirect) or 422 (validation error)
- If 404: Route not registered
- If 419: CSRF token issue

## Production Deployment Checklist

After deploying new code:

```bash
cd /var/www/mygrownet.com

# 1. Pull code
git pull origin main

# 2. Install dependencies (if changed)
composer install --no-dev --optimize-autoloader
npm ci --production

# 3. Build frontend (or upload pre-built assets)
npm run build

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 5. Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Set permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 7. Restart services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
php artisan queue:restart
```

## Common Mistakes

### ❌ Wrong Route Name
```javascript
// Don't use hardcoded URL
router.post('/login', ...)

// Use route helper
router.post(route('login'), ...)
```

### ❌ Missing CSRF Token
Inertia handles CSRF automatically, but verify:
```html
<!-- In app.blade.php -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### ❌ Wrong HTTP Method
```javascript
// Login must be POST, not GET
router.post(route('login'), ...) // ✅
router.get(route('login'), ...)  // ❌
```

### ❌ Cached .env Changes
After changing `.env`:
```bash
php artisan config:clear
php artisan config:cache
```

## Emergency Fix

If nothing else works:

```bash
# Nuclear option - delete ALL caches
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*

# Rebuild
composer dump-autoload
php artisan optimize
php artisan config:cache
php artisan route:cache

# Restart everything
sudo systemctl restart php8.2-fpm nginx
```

## Prevention

### Add to GitHub Actions / Deployment Script
```yaml
- name: Optimize Laravel
  run: |
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
```

### Add Health Check
Create `routes/api.php`:
```php
Route::get('/health/routes', function () {
    return response()->json([
        'login_post' => Route::has('login') ? 'exists' : 'missing',
        'register_post' => Route::has('register') ? 'exists' : 'missing',
    ]);
});
```

Test: `curl https://mygrownet.com/api/health/routes`

## Still Not Working?

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs: `/var/log/nginx/error.log`
3. Check PHP-FPM logs: `/var/log/php8.2-fpm.log`
4. Enable debug mode temporarily: `APP_DEBUG=true` in `.env`
5. Contact server administrator

## Related Files

- `resources/js/components/LoginModal.vue` - Frontend component
- `routes/web.php` - Route definitions
- `app/Http/Controllers/Auth/BladeAuthController.php` - Backend handler
- `app/Http/Middleware/DetectSubdomain.php` - Subdomain routing
- `bootstrap/app.php` - Middleware configuration
