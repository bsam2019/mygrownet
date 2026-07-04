# MyGrowNet Project Memory

## Constraints
- NEVER run `npm run build` on the production server (droplet 138.197.187.134)
- `public/build/` is untracked from git — deployment of built assets must be handled separately
- No duplicate user accounts — single MyGrowNet user database

## Common Issues & Fixes
- **Login modal 404 or redirects to old page**: Cached routes in production don't include POST /login. **Fix**: SSH to server, run `php artisan route:clear && php artisan route:cache`. See `DEPLOYMENT_FIX.md` and `docs/LOGIN_MODAL_TROUBLESHOOTING.md`.
- **BIzBoost/GrowMart/ZamStay blank auth page**: `window.location.href` in Vue template compiles to `_ctx.window.location.href`. The render proxy doesn't always resolve `window` to global. **Fix**: Extract to script as `const currentUrl = encodeURIComponent(window.location.href)`, use `currentUrl` in template.
- **Google OAuth 500 error**: `laravel/socialite` package may be missing on production. Run `composer require laravel/socialite`.
- **HandleInertiaRequests**: Must skip Inertia for auth routes on main domain only. All subdomains (bizboost, zamstay, growmart) must keep Inertia processing.

## Deployment
1. Commit and push to `origin/main` on local
2. SSH to droplet: `cd /var/www/mygrownet.com && git pull`
3. **CRITICAL**: Clear and rebuild caches: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
4. Run `php artisan optimize` (config, routes, events cached)
5. Built frontend assets must be deployed separately (build locally then upload or use CI)

**Quick script**: `bash fix-production.sh` (runs all cache clear/rebuild commands)

## Routes
- Auth: `GET|HEAD auth/google` and `GET|HEAD auth/google/callback` — no prefix, no subdomain
- Each subdomain needs its own callback URL registered in Google Cloud Console

## Removed Files
- `resources/js/Pages/GrowNet/Dashboard.vue` — classic desktop GrowNet dashboard, replaced by `GrowNet/GrowNet.vue` (modern mobile SPA)
