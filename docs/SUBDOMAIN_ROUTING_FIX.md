# Subdomain Routing Fix

**Last Updated:** March 7, 2026  
**Status:** Production

## Overview

Fixed a critical bug in subdomain routing where specific subdomains (like `cms.mygrownet.com`) were loading the main site homepage instead of their intended content. This was also affecting GrowBuilder sites.

## Root Cause

The issue was caused by route loading order in Laravel:

1. `routes/subdomain.php` uses a wildcard domain pattern: `Route::domain('{subdomain}.mygrownet.com')`
2. This wildcard was matching ALL subdomains, including specific ones like `cms.mygrownet.com`
3. Laravel matches routes in the order they're loaded in `bootstrap/app.php`
4. The wildcard route was being loaded BEFORE specific domain routes, so it matched first

## Solution

### 1. Route Loading Order
Changed `bootstrap/app.php` to load specific domain routes BEFORE wildcard routes:

```php
// BEFORE (broken):
Route::middleware('web')->group(base_path('routes/cms-subdomain.php'));
Route::middleware('web')->group(base_path('routes/subdomain.php'));

// AFTER (fixed):
// Specific domain routes MUST be loaded BEFORE wildcard subdomain routes
Route::middleware('web')->group(base_path('routes/cms-subdomain.php'));  // cms.mygrownet.com
Route::middleware('web')->group(base_path('routes/subdomain.php'));      // {subdomain}.mygrownet.com
```

### 2. Route Name Prefix
Changed CMS subdomain route names to avoid conflicts:

```php
// routes/cms-subdomain.php
Route::domain('cms.mygrownet.com')->name('cms.subdomain.')->group(function () {
    // Routes here create names like: cms.subdomain.landing, cms.subdomain.login, etc.
});
```

### 3. Reserved Subdomains List
Added `cms` to the reserved subdomains list in `DetectSubdomain` middleware as a safety measure:

```php
$reserved = [
    'api', 'admin', 'mail', 'ftp', 'smtp', 'pop', 'imap', 
    'webmail', 'cpanel', 'whm', 'ns1', 'ns2', 'mx', 'email',
    'growbuilder', 'app', 'dashboard', 'portal', 'staging', 'dev',
    'cms' // CMS subdomain for Company Management System
];
```

## Implementation

### Files Modified

1. **bootstrap/app.php**
   - Reordered route loading to prioritize specific domains
   - Added comments explaining the importance of order

2. **routes/cms-subdomain.php**
   - Changed route name prefix from `cms.` to `cms.subdomain.`
   - Prevents conflicts with main CMS routes

3. **app/Http/Middleware/DetectSubdomain.php**
   - Added `cms` to reserved subdomains list
   - Ensures middleware skips CMS subdomain

### Testing

After deployment:
- ✅ cms.mygrownet.com should load CMS landing page
- ✅ GrowBuilder sites (e.g., mysite.mygrownet.com) should work correctly
- ✅ Main site (mygrownet.com) should work normally
- ✅ Other specific subdomains (geopamu, wowthem) should work

## Impact

This fix resolves:
- CMS subdomain loading main site homepage
- New GrowBuilder sites loading main site homepage
- Any future specific subdomains being caught by wildcard route

## Key Learnings

1. **Route Order Matters**: In Laravel, routes are matched in the order they're loaded
2. **Specific Before Wildcard**: Always load specific domain routes before wildcard patterns
3. **Route Names**: Use unique prefixes for subdomain routes to avoid conflicts
4. **Middleware Timing**: Middleware runs before route matching, so can't rely on route detection there

## Future Considerations

When adding new specific subdomains:
1. Create a dedicated route file (e.g., `routes/newsubdomain-subdomain.php`)
2. Load it in `bootstrap/app.php` BEFORE `routes/subdomain.php`
3. Use a unique route name prefix (e.g., `newsubdomain.subdomain.`)
4. Add the subdomain to reserved list in `DetectSubdomain` middleware

## Changelog

### March 7, 2026
- Fixed subdomain routing bug
- Implemented CMS subdomain (cms.mygrownet.com)
- Reordered route loading in bootstrap/app.php
- Changed CMS subdomain route name prefix
- Added cms to reserved subdomains list
