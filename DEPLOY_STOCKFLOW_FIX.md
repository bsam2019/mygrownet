# Deploy StockFlow Subdomain Fix to Production

## Issue Summary
The StockFlow subdomain (taradasi.mygrownet.com) is showing "Page not found: ./pages/auth/Login.vue" error when trying to access the login page.

## What Was Changed
1. Added debug logging to `AuthController::showLogin()` method
2. Created comprehensive troubleshooting guide in `docs/STOCKFLOW_SUBDOMAIN_FIX.md`
3. Created quick fix script `fix-stockflow-production.sh`

## Deployment Instructions

### Option 1: Automated Fix (Recommended)

SSH to production and run the fix script:

```bash
ssh sammy@138.197.187.134

# Enter password: Bsam@2025!!

cd /var/www/mygrownet.com

# Pull latest changes
git pull origin main

# Make script executable
chmod +x fix-stockflow-production.sh

# Run the fix script
bash fix-stockflow-production.sh
```

### Option 2: Manual Fix

If the automated script fails, run these commands manually:

```bash
ssh sammy@138.197.187.134

# Enter password: Bsam@2025!!

cd /var/www/mygrownet.com

# 1. Pull latest code
git pull origin main

# 2. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Rebuild caches
php artisan config:cache
php artisan route:cache

# 4. Optimize
php artisan optimize

# 5. Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# 6. Test the fix
curl -I https://taradasi.mygrownet.com/login
```

## Verification Steps

### 1. Check Routes

```bash
php artisan route:list --name=stockflow.sub.login
```

Expected output:
```
GET|HEAD  {account}.mygrownet.com/login  stockflow.sub.login
```

### 2. Test with Browser

1. Open browser and visit: https://taradasi.mygrownet.com/login
2. Should see:
   - Taradasi Dental Clinic branding
   - Login form with email and password fields
   - No "Page not found" errors

### 3. Check Logs

```bash
tail -50 storage/logs/laravel.log
```

Look for debug messages like:
```
StockFlow Login - Route Name: stockflow.sub.login
StockFlow Login - Company ID: 1
StockFlow Login - Host: taradasi.mygrownet.com
```

### 4. Test Login

Use these credentials (from seeded data):
- Email: `admin@taradasi.com`
- Password: `password`

Should successfully login and redirect to dashboard.

## If Issue Persists

### Check Frontend Build

The issue might be that the frontend assets are outdated. Check if StockAudit components exist:

```bash
ls -la public/build/assets/ | grep -i stock
cat public/build/manifest.json | grep -i stockaudit
```

If components are missing, rebuild frontend:

**On Development Machine:**
```bash
cd C:\Apache24\htdocs\mygrownet
npm run build
```

**Upload to Production:**
```bash
scp -r public/build/* sammy@138.197.187.134:/var/www/mygrownet.com/public/build/
```

Or use the GitHub Actions deploy workflow (recommended).

### Check Database

Verify Taradasi company exists:

```bash
php artisan tinker

\App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyModel::where('subdomain', 'taradasi')->first();
```

Should return company with:
- id: 1
- name: "Taradasi Dental Clinic"
- subdomain: "taradasi"
- status: "active"

If company doesn't exist, run seeder:

```bash
php artisan db:seed --class=StockAuditSeeder
```

### Check Middleware

Verify DetectSubdomain middleware is running:

```bash
php artisan route:list --name=stockflow.sub.login --verbose
```

Check middleware column should show:
- web
- stockflow.company

## Rollback (if needed)

If the fix causes issues, rollback:

```bash
cd /var/www/mygrownet.com
git log --oneline -5
git revert HEAD
php artisan optimize
sudo systemctl restart php8.2-fpm
```

## Additional Notes

### WebSocket Errors (Expected)

You may see these errors in browser console:
```
WebSocket connection to 'wss://localhost:8080/...' failed
```

This is expected because Reverb (WebSocket server) is not configured for StockFlow. These errors can be ignored.

### Service Workers

StockFlow subdomains automatically disable service workers via the `__sfSubdomain` flag. This is intentional.

## Files Changed

- `app/Http/Controllers/StockAudit/AuthController.php` - Added debug logging
- `docs/STOCKFLOW_SUBDOMAIN_FIX.md` - Comprehensive fix guide
- `fix-stockflow-production.sh` - Automated fix script
- `DEPLOY_STOCKFLOW_FIX.md` - This deployment guide

## Support

If issues persist after following these steps:

1. Check logs: `tail -100 storage/logs/laravel.log`
2. Enable Laravel debug mode temporarily in `.env`: `APP_DEBUG=true`
3. Test route matching: `php artisan route:list | grep taradasi`
4. Verify server requirements: `php artisan about`

## Success Criteria

Fix is successful when:
1. ✅ https://taradasi.mygrownet.com/login loads without errors
2. ✅ Taradasi branding is displayed
3. ✅ Login form accepts credentials
4. ✅ Successful login redirects to StockFlow dashboard
5. ✅ Debug logs show correct route name and company ID

## Timeline

- **Estimated deployment time:** 5-10 minutes
- **Recommended deployment window:** Anytime (low-risk changes, only cache clear and debug logging)
- **Rollback time:** < 2 minutes if needed
