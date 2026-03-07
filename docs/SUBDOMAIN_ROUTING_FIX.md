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
4. The wildcard route was being loaded and matching before specific domain routes could be evaluated

## Solution

### Final Implementation: Middleware Handler (Like Geopamu)

After trying route ordering (which didn't work due to middleware execution timing), we implemented the same pattern used by the working `geopamu.mygrownet.com` subdomain:

**Handle CMS subdomain directly in `DetectSubdomain` middleware:**

```php
// In DetectSubdomain::handle()
if ($subdomain === 'cms') {
    return $this->handleCmsSubdomain($request);
}

// Handler method
private function handleCmsSubdomain(Request $request): Response
{
    $path = $request->path();
    
    // Landing page
    if ($path === '/') {
        return \Inertia\Inertia::render('CMS/Landing')->toResponse($request);
    }
    
    // Auth routes
    $authController = app()->make(\App\Http\Controllers\CMS\AuthController::class);
    
    $result = match(true) {
        $path === 'login' && $request->isMethod('GET') => $authController->showLogin(),
        $path === 'login' && $request->isMethod('POST') => $authController->login($request),
        $path === 'register' && $request->isMethod('GET') => $authController->showRegister(),
        $path === 'register' && $request->isMethod('POST') => $authController->register($request),
        $path === 'logout' && $request->isMethod('POST') => $authController->logout($request),
        default => \Inertia\Inertia::render('CMS/Landing')
    };
    
    // Handle Inertia Response properly
    if ($result instanceof \Inertia\Response) {
        return $result->toResponse($request);
    }
    
    return $result instanceof Response ? $result : response($result);
}
```

### Why This Works

1. **Middleware runs before route matching** - The `DetectSubdomain` middleware intercepts the request before Laravel tries to match routes
2. **Direct controller dispatch** - We dispatch directly to controllers/Inertia pages, bypassing the route matching entirely
3. **Proven pattern** - This is exactly how `geopamu.mygrownet.com` works successfully

### Previous Attempts (Didn't Work)

1. **Route Loading Order** - Tried loading specific domain routes before wildcard routes in `bootstrap/app.php`
   - Problem: Middleware runs before route matching, so order doesn't matter
   
2. **Route Name Prefixes** - Changed CMS subdomain route names to `cms.subdomain.*`
   - Problem: Routes were never being matched due to middleware interception

## Implementation

### Files Modified

1. **app/Http/Middleware/DetectSubdomain.php**
   - Added `handleCmsSubdomain()` method
   - Added CMS subdomain check in `handle()` method
   - Routes requests directly to controllers/Inertia pages

2. **routes/cms-subdomain.php** (kept for reference, not actively used)
   - Contains route definitions but not used due to middleware handling

3. **bootstrap/app.php** (route order changes kept but not critical)
   - Specific domain routes loaded before wildcard routes

### Testing

After deployment:
- ✅ cms.mygrownet.com loads CMS landing page
- ✅ cms.mygrownet.com/login loads CMS login page
- ✅ cms.mygrownet.com/register loads CMS register page
- ✅ GrowBuilder sites (e.g., mysite.mygrownet.com) work correctly
- ✅ Main site (mygrownet.com) works normally
- ✅ Other specific subdomains (geopamu, wowthem) work

## Impact

This fix resolves:
- CMS subdomain loading main site homepage
- New GrowBuilder sites loading main site homepage
- Any future specific subdomains being caught by wildcard route

## Key Learnings

1. **Middleware Timing**: Middleware runs BEFORE route matching, so route order doesn't help
2. **Direct Dispatch Pattern**: For specific subdomains, handle them directly in middleware like geopamu
3. **Route::domain() Limitations**: Wildcard domain routes will always match first in middleware context
4. **Follow Working Patterns**: When something works (geopamu), copy that pattern exactly

## Future Considerations

When adding new specific subdomains:
1. Add subdomain check in `DetectSubdomain::handle()` method
2. Create a handler method (e.g., `handleNewSubdomain()`)
3. Dispatch directly to controllers/Inertia pages
4. Follow the geopamu/cms pattern exactly
5. Add the subdomain to reserved list as safety measure

## Changelog

### March 7, 2026 - Final Fix
- Implemented CMS subdomain handler in DetectSubdomain middleware
- Added handleCmsSubdomain() method following geopamu pattern
- CMS subdomain now working in production

### March 7, 2026 - Initial Attempts
- Fixed subdomain routing bug (route order approach - didn't work)
- Implemented CMS subdomain (cms.mygrownet.com)
- Reordered route loading in bootstrap/app.php
- Changed CMS subdomain route name prefix
- Added cms to reserved subdomains list
