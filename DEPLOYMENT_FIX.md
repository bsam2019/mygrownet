# Production Login Issue - Deployment Fix

## Problem
Login modal is redirecting to 404 or old login page in production. This is caused by:
1. **Cached routes** in production that don't include the POST /login route
2. **Session/CSRF issues** causing 419 errors
3. **Stale configuration cache**

## Solution - Run on Production Server

SSH into the production server and run these commands **in this exact order**:

```bash
cd /var/www/mygrownet.com

# 1. Pull latest code
git pull origin main

# 2. Clear ALL caches (this is critical!)
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# 3. Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan event:cache

# 4. Restart queue workers (if running)
php artisan queue:restart

# 5. Verify routes are registered
php artisan route:list --name=login --columns=method,uri,name,action
```

## Expected Route Output

You should see these routes:

```
Method      URI                             Name        Action
GET|HEAD    login                           login       Auth\BladeAuthController@showLogin
POST        login                           (none)      Auth\BladeAuthController@login
GET|HEAD    register                        register    Auth\BladeAuthController@showRegister
POST        register                        (none)      Auth\BladeAuthController@register
```

**CRITICAL:** If POST /login is missing, the route cache is corrupted. Repeat steps 2-3.

## Alternative: Nuclear Option (if above doesn't work)

```bash
cd /var/www/mygrownet.com

# Remove ALL cache files manually
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*

# Recreate cache directory structure
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions

# Set correct permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Rebuild caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Verification

Test the login flow:

1. Open browser to `https://mygrownet.com`
2. Click login button to open modal
3. Submit login form
4. Should redirect to dashboard (not 404)

If still 404, check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

## Debug Commands (if still failing)

```bash
# See which routes are cached
php artisan route:list | grep -E "(login|register)"

# Check if route cache file exists
ls -la bootstrap/cache/routes-*.php

# Check Laravel version
php artisan --version

# Check environment
php artisan env
```

## Root Cause

The LoginModal component posts to `route('login')` which resolves to `/login` via Ziggy. However:

1. **Production had route cache** that didn't include the POST /login route
2. **DetectSubdomain middleware** runs first but wasn't the issue
3. **Cached Ziggy routes** in the frontend may also be stale

The fix is to **clear and rebuild all caches** after deploying code changes.

## Prevention

Always run these commands after deploying to production:

```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Or add to your deployment script/GitHub Actions workflow.
