# Subdomain Routing Fix

**Last Updated:** March 7, 2026  
**Status:** Production

## Problem

The CMS subdomain (`cms.mygrownet.com`) was loading the main site homepage instead of the CMS landing page. This was a recurring issue that also affected GrowBuilder sites.

### Root Cause

The wildcard route `{subdomain}.mygrownet.com` in `routes/subdomain.php` was matching ALL subdomains including `cms` BEFORE the specific `cms.mygrownet.com` route could match. This happened because:

1. Middleware runs before route matching
2. The wildcard pattern `{subdomain}.mygrownet.com` matches everything
3. Even though CMS routes were loaded first in `bootstrap/app.php`, the middleware was passing control to route matching which would hit the wildcard first

## Solution

Implemented complete subdomain handling in the `DetectSubdomain` middleware, following the same pattern as `geopamu` and `wowthem` subdomains.

### Implementation

All subdomain routing is now handled entirely in `app/Http/Middleware/DetectSubdomain.php`:

1. **CMS Subdomain** - Handled by `handleCmsSubdomain()` method
2. **Geopamu Subdomain** - Handled by `handleGeopamuSubdomain()` method  
3. **WowThem Subdomain** - Handled by `handleWowthemSubdomain()` method
4. **GrowBuilder Sites** - Handled by `renderSite()` method

### Route Names

To support dynamic route generation with the `route()` helper:

- **Local Development**: Uses `cms.*` route names (e.g., `cms.login`) with `/cms` prefix
- **Production Subdomain**: Uses `cms.subdomain.*` route names (e.g., `cms.subdomain.login`) without prefix
- **Backend-Driven**: Backend detects hostname and passes appropriate `routePrefix` prop to frontend
- **Frontend**: Uses computed properties based on `routePrefix` prop from backend

### Key Changes

**DetectSubdomain.php:**
- Added complete `handleCmsSubdomain()` method that dispatches to all CMS controllers
- Added helper methods for each CMS section: `handleCmsJobs()`, `handleCmsCustomers()`, `handleCmsInvoices()`, etc.
- All routes are matched and dispatched directly to controllers without relying on Laravel's route files

**bootstrap/app.php:**
- Loads both `routes/cms-subdomain.php` and `routes/cms.php` for route name generation
- Routes provide names for `route()` helper but actual routing is handled by middleware

**routes/cms-subdomain.php & routes/cms.php:**
- Both route files pass `routePrefix` prop to views
- `cms-subdomain.php` passes `'cms.subdomain'` prefix
- `cms.php` passes `'cms'` prefix

**Landing.vue:**
- Receives `routePrefix` prop from backend
- Uses computed properties (`loginRoute`, `registerRoute`) based on prop
- No hostname detection in frontend, no hardcoded URLs
- All routes are dynamic and backend-driven

### Benefits

1. **No Route Conflicts** - Middleware handles subdomains before route matching
2. **Consistent Pattern** - All special subdomains (cms, geopamu, wowthem) use same approach
3. **GrowBuilder Sites Protected** - User-created GrowBuilder sites continue working
4. **Dynamic Routes** - Route names work correctly in both local and production environments
5. **Maintainable** - Clear separation between platform subdomains and user subdomains

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
2. Click "Sign In" or "Get Started" - Should navigate to `/login` or `/register` (not `/cms/login`)
3. Visit `https://cms.mygrownet.com/dashboard` - Should show CMS dashboard (after login)
4. Visit any GrowBuilder site (e.g., `https://testsite.mygrownet.com/`) - Should still work
5. Visit `https://geopamu.mygrownet.com/` - Should still work
6. Visit `https://wowthem.mygrownet.com/` - Should still work
7. Local development at `http://localhost/cms` - Should work with `/cms` prefix

## Files Modified

- `app/Http/Middleware/DetectSubdomain.php` - Added complete CMS handling
- `bootstrap/app.php` - Loads both cms-subdomain.php and cms.php route files
- `routes/cms-subdomain.php` - Passes `routePrefix: 'cms.subdomain'` to views
- `routes/cms.php` - Passes `routePrefix: 'cms'` to views
- `resources/js/pages/CMS/Landing.vue` - Uses backend-provided `routePrefix` prop for dynamic routes

## Changelog

### March 7, 2026
- Fixed CMS subdomain routing by handling all routes in middleware
- Implemented backend-driven route prefix solution
- Backend passes `routePrefix` prop to frontend based on actual domain
- Frontend uses computed properties for dynamic route generation
- No hostname detection in Vue components, all logic in backend
- Deployed to production successfully
- All CMS routes now work correctly without `/cms` prefix on subdomain
