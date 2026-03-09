# Session Memory - MyGrowNet Project

**Last Updated:** March 9, 2026

## Server Credentials
- **Server IP:** 138.197.187.134
- **User:** sammy
- **Project Path:** /var/www/mygrownet.com
- **PHP Version:** 8.3
- **Database:** MySQL

## Deployment Commands
```bash
# Quick deploy (code only)
bash deployment/deploy.sh

# Full deploy (with assets - takes 5+ minutes)
bash deployment/deploy-with-assets.sh

# SSH to server
ssh sammy@138.197.187.134
```

## Recent Work

### Custom Domain Favicon Fix (March 9, 2026)
- **Status:** FIXED ✅
- **Issue:** Custom domain showing MyGrowNet favicon instead of site's favicon
- **Root Cause:** app.blade.php wasn't detecting custom domains, only subdomains
- **Solution:** Added custom domain detection logic to app.blade.php
- **Changes:**
  - Added `$isCustomDomain` check for non-mygrownet.com domains
  - Updated condition to include custom domains: `if ($isGrowBuilderSite || $isGrowBuilderSubdomain || $isCustomDomain)`
  - Updated manifest path logic to handle custom domains
- **Test Domain:** flamesofhopechurch.com - should now show site's favicon

### Custom Domain 404 Fix (March 9, 2026)
- **Status:** FIXED ✅
- **Issue:** Custom domain pages returning 404 for services, ministries, visit, school
- **Root Cause:** DetectSubdomain middleware wasn't registered in global middleware, so it wasn't running before route matching
- **Solution:** Added DetectSubdomain to global $middleware array in app/Http/Kernel.php
- **Test Domain:** flamesofhopechurch.com - ALL pages now working
- **Site ID:** 15 (Flames of Hope)

### Custom Domain Setup (March 9, 2026)
- **Status:** Fixed and deployed
- **Issue:** Frontend was using wrong route names
- **Solution:** Updated Settings.vue to use `growbuilder.sites.domain.connect` instead of `growbuilder.custom-domain.connect`
- **Test Domain:** flamesofhopechurch.com (DNS correctly pointing to 138.197.187.134)

### Static Export Feature (March 9, 2026)
- **Status:** Implemented, not yet tested
- **Tier Access:** Business+ (Business and Agency tiers only)
- **Files Created:**
  - `app/Services/GrowBuilder/StaticExportService.php`
  - `app/Http/Controllers/GrowBuilder/ExportController.php`
  - `resources/js/pages/GrowBuilder/Sites/Export.vue`
  - `docs/growbuilder/STATIC_EXPORT.md`
- **Features:**
  - Generates standalone HTML/CSS/JS
  - Downloads all media files
  - Creates README with hosting instructions
  - Packages as downloadable ZIP

## Key Routes
```php
// Custom Domain
POST /growbuilder/sites/{id}/domain/connect
DELETE /growbuilder/sites/{id}/domain
GET /growbuilder/sites/{id}/domain/status

// Static Export
GET /growbuilder/sites/{id}/export
POST /growbuilder/sites/{id}/export
```

## Important Notes
- Assets build takes 5+ minutes - avoid running during development
- Custom domain setup requires sudo permissions (already configured)
- DNS must point to 138.197.187.134 before connecting domain
- Static export only works for Business+ tier users
- DetectSubdomain middleware MUST be in global middleware to intercept custom domain requests before routing

## Common Issues

### Custom Domain Not Working
1. Check DNS: `nslookup domain.com` should return 138.197.187.134
2. Check if DetectSubdomain is in global middleware (app/Http/Kernel.php)
3. Check sudo permissions: `deployment/setup-sudo-permissions.sh`
4. Check logs: `tail -100 storage/logs/laravel.log`
5. Clear caches: `php artisan cache:clear && php artisan route:clear && php artisan config:clear`

### Assets Not Building
- Build locally takes 5+ minutes
- Use `bash deployment/deploy.sh` for code-only deploys
- Assets are already built on production

## Testing Commands
```bash
# Test custom domain pages
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && php test-live-requests.php"

# Test subdomain pages
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && php test-subdomain-requests.php"

# Check DNS
nslookup flamesofhopechurch.com

# View logs
ssh sammy@138.197.187.134 "cd /var/www/mygrownet.com && tail -50 storage/logs/laravel.log"
```

## Next Steps
1. Test static export feature
2. Document any issues found
3. Clean up test files from repository
