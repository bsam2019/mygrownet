# MyGrowNet Project Memory

## Constraints
- NEVER run `npm run build` on the production server (droplet 138.197.187.134)
- `public/build/` is untracked from git — deployment of built assets must be handled separately
- No duplicate user accounts — single MyGrowNet user database

## Common Issues & Fixes
- **Login modal 404 or redirects to old page**: Cached routes in production don't include POST /login. **Fix**: SSH to server, run `php artisan route:clear && php artisan route:cache`. See `DEPLOYMENT_FIX.md` and `docs/LOGIN_MODAL_TROUBLESHOOTING.md`.
- **CMS company creation redirects to old login**: `EnsureCmsAccess` middleware was redirecting to main site login instead of CMS login. **Fix**: Changed to `route('cms.login')` and ensure session is saved after transaction.
- **Subdomains ask for login when already logged in**: Sessions not shared across subdomains. **Fix**: In production .env, set `SESSION_DOMAIN=.mygrownet.com` (note the leading dot). This shares sessions across all subdomains (bizboost, growmart, cms, etc.).
- **BIzBoost/GrowMart/ZamStay blank auth page**: `window.location.href` in Vue template compiles to `_ctx.window.location.href`. The render proxy doesn't always resolve `window` to global. **Fix**: Extract to script as `const currentUrl = encodeURIComponent(window.location.href)`, use `currentUrl` in template.
- **Google OAuth 500 error**: `laravel/socialite` package may be missing on production. Run `composer require laravel/socialite`.
- **HandleInertiaRequests**: Must skip Inertia for auth routes on main domain only. All subdomains (bizboost, zamstay, growmart) must keep Inertia processing.
- **ZamStay 500 error**: Caused by missing subdomain handler in `DetectSubdomain` middleware and unloaded zamstay migrations. **Fix**: Added `zamstay` handler to `DetectSubdomain.php`, created `ZamStayServiceProvider` to load `database/migrations/zamstay/`, removed double route loading from `RouteServiceProvider`. After pulling on production: run `php artisan migrate --path=database/migrations/zamstay` then `php artisan optimize`.

## Deployment
1. Commit and push to `origin/main` on local
2. SSH to droplet: `cd /var/www/mygrownet.com && git pull`
3. **CRITICAL**: Clear and rebuild caches: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
4. Run `php artisan optimize` (config, routes, events cached)
5. Built frontend assets must be deployed separately (build locally then upload or use CI)
6. **Production only**: Ensure `.env` has `SESSION_DOMAIN=.mygrownet.com` for cross-subdomain auth

**Quick script**: `bash fix-production.sh` (runs all cache clear/rebuild commands)

## Routes
- Auth: `GET|HEAD auth/google` and `GET|HEAD auth/google/callback` — no prefix, no subdomain
- Each subdomain needs its own callback URL registered in Google Cloud Console

## PrimeEdge Advisory Subdomain Setup
- **DNS**: Create `CNAME primeedge` pointing to `mygrownet.com` (or A record to droplet IP)
- **Middleware**: Handler added in `DetectSubdomain.php` at line 131 — calls `configureSubdomainUrl()` (same as bizboost/zamstay)
- **Routes**: `primeedge.mygrownet.com` group registered in `routes/primeedge.php` line 81 — serves all routes at root `/` with name prefix `primeedge.sub.`
- **Blade view**: `primeedge.mygrownet.com` → `primeedge` already mapped in `HandleInertiaRequests.php`
- **Note**: Subdomain routes use `primeedge.sub.*` name prefix to avoid collisions with main domain `primeedge.*` routes. Controllers that redirect (login, logout) currently use `primeedge.*` names. If activating subdomain, update redirect targets to detect current domain and use `primeedge.sub.*` names.

## Removed Files
- `resources/js/Pages/GrowNet/Dashboard.vue` — classic desktop GrowNet dashboard, replaced by `GrowNet/GrowNet.vue` (modern mobile SPA)
