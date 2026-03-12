# GrowBuilder Route Loading Issue Fix

**Last Updated:** March 12, 2026
**Status:** Fixed

## Problem

Users (including admins) were experiencing redirects to `/dashboard` when trying to access:
- GrowBuilder (`/growbuilder`)
- GrowFinance (`/growfinance`)
- Quick Invoice (`/quick-invoice`)
- Other module routes

## Root Cause

**Missing route file registration in RouteServiceProvider**

The `routes/growbuilder.php` and several other route files were not being loaded in `app/Providers/RouteServiceProvider.php`. This meant:

1. Routes like `/growbuilder` were not registered
2. Laravel couldn't find the route
3. Requests fell back to default behavior (redirect to dashboard)
4. Users experienced "bouncing back to dashboard"

## Solution

Added missing route files to `RouteServiceProvider.php`:

```php
Route::middleware('web')
    ->group(base_path('routes/growbuilder.php'));
    
Route::middleware('web')
    ->group(base_path('routes/growfinance.php'));
    
Route::middleware('web')
    ->group(base_path('routes/quick-invoice.php'));
    
Route::middleware('web')
    ->group(base_path('routes/cms.php'));
    
Route::middleware('web')
    ->group(base_path('routes/venture.php'));
    
Route::middleware('web')
    ->group(base_path('routes/bgf.php'));
    
Route::middleware('web')
    ->group(base_path('routes/ubumi.php'));
```

## Files Modified

- `app/Providers/RouteServiceProvider.php` - Added missing route file registrations
- `resources/js/layouts/AppLayout.vue` - Added missing prop definitions (secondary issue)

## Commands Run

```bash
php artisan route:clear
php artisan config:clear
```

## Status

✅ **FIXED** - All module routes now properly load, no more dashboard bouncing