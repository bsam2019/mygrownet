# Subdomain Routing Fix

**Last Updated:** March 7, 2026  
**Status:** Fixed

## Problem

The CMS subdomain (`cms.mygrownet.com`) was loading the main site homepage instead of the CMS landing page. This was a recurring issue that also affected GrowBuilder sites.

### Root Cause

The wildcard route `{subdomain}.mygrownet.com` in `routes/subdomain.php` was matching ALL subdomains including `cms` BEFORE the specific `cms.mygrownet.com` route could match. This happened because:

1. Middleware runs before route matching
2. The wildcard pattern `{subdomain}.mygrownet.com` matches everything
3. Even though CMS routes were loaded first in `bootstrap/app.php`, the middleware was passing control to route matching which would hit the wildcard first

### Previous Failed Attempts

1. **Loading CMS routes first** - Didn't work because wildcard still matched
2. **Excluding DetectSubdomain middleware** - Didn't work due to middleware timing
3. **Adding CMS to reserved subdomains** - Didn't work, wildcard still matched
4. **Partial middleware handling** - Caused Error 500 because `$next` closure wasn't available in private method scope

## Solution

Implemented complete subdomain handling in the `DetectSubdomain` middleware, following the same pattern as `geopamu` and `wowthem` subdomains.

### Implementation

All subdomain routing is now handled entirely in `app/Http/Middleware/DetectSubdomain.php`:

1. **CMS Subdomain** - Handled by `handleCmsSubdomain()` method
2. **Geopamu Subdomain** - Handled by `handleGeopamuSubdomain()` method  
3. **WowThem Subdomain** - Handled by `handleWowthemSubdomain()` method
4. **GrowBuilder Sites** - Handled by `renderSite()` method

### Key Changes

**DetectSubdomain.php:**
- Added complete `handleCmsSubdomain()` method that dispatches to all CMS controllers
- Added helper methods for each CMS section: `handleCmsJobs()`, `handleCmsCustomers()`, `handleCmsInvoices()`, etc.
- All routes are matched and dispatched directly to controllers without relying on Laravel's route files

**bootstrap/app.php:**
- Removed loading of `routes/cms-subdomain.php` (no longer needed)
- Removed loading of `routes/subdomain.php` (no longer needed)
- All subdomain routing now happens in middleware

### Benefits

1. **No Route Conflicts** - Middleware handles subdomains before route matching
2. **Consistent Pattern** - All special subdomains (cms, geopamu, wowthem) use same approach
3. **GrowBuilder Sites Protected** - User-created GrowBuilder sites continue working
4. **Maintainable** - Clear separation between platform subdomains and user subdomains

## CMS Routes Handled

The middleware now handles all CMS routes:

- `/` - Landing page
- `/login`, `/register`, `/logout` - Authentication
- `/dashboard` - Dashboard
- `/jobs/*` - Job management
- `/customers/*` - Customer management
- `/invoices/*` - Invoice management
- `/payments/*` - Payment management
- `/quotations/*` - Quotation management
- `/inventory/*` - Inventory management
- `/expenses/*` - Expense management
- `/reports` - Reports
- `/budgets/*` - Budget management
- `/settings` - Settings

## Testing

To test the fix:

1. Visit `https://cms.mygrownet.com/` - Should show CMS landing page
2. Visit `https://cms.mygrownet.com/login` - Should show CMS login page
3. Visit `https://cms.mygrownet.com/dashboard` - Should show CMS dashboard (after login)
4. Visit any GrowBuilder site (e.g., `https://testsite.mygrownet.com/`) - Should still work
5. Visit `https://geopamu.mygrownet.com/` - Should still work
6. Visit `https://wowthem.mygrownet.com/` - Should still work

## Files Modified

- `app/Http/Middleware/DetectSubdomain.php` - Added complete CMS handling
- `bootstrap/app.php` - Removed cms-subdomain.php and subdomain.php route loading

## Files No Longer Used

- `routes/cms-subdomain.php` - Routes now handled in middleware
- `routes/subdomain.php` - Routes now handled in middleware

These files can be kept for reference but are no longer loaded by the application.

## Future Considerations

If adding new platform subdomains (not user-created GrowBuilder sites):

1. Add subdomain check in `DetectSubdomain::handle()` method
2. Create a `handleXxxSubdomain()` method following the same pattern
3. Dispatch directly to controllers without relying on route files
4. This ensures no conflicts with the wildcard GrowBuilder pattern
