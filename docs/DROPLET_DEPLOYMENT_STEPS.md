# Droplet Deployment Steps

**Date:** November 20, 2025
**Status:** Ready for Deployment

---

## Quick Deployment

### Step 1: SSH into Droplet
```bash
ssh root@your-droplet-ip
# or
ssh user@your-droplet-ip
```

### Step 2: Navigate to Project
```bash
cd /path/to/mygrownet
# Usually: cd /var/www/mygrownet or similar
```

### Step 3: Pull Latest Changes
```bash
git pull origin main
```

### Step 4: Run Deployment Script
```bash
bash deployment/deploy-with-assets.sh
```

---

## What the Deployment Script Does

The `deploy-with-assets.sh` script should:
1. Install/update dependencies
2. Build frontend assets
3. Clear Laravel caches
4. Run migrations (if any)
5. Restart services
6. Verify deployment

---

## Manual Deployment (if script doesn't exist)

### Step 1: Pull Changes
```bash
git pull origin main
```

### Step 2: Install Dependencies
```bash
composer install --no-dev
npm install
```

### Step 3: Build Assets
```bash
npm run build
```

### Step 4: Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Step 5: Restart Services
```bash
# If using supervisor
sudo supervisorctl restart all

# If using systemd
sudo systemctl restart mygrownet
sudo systemctl restart mygrownet-queue

# If using Docker
docker-compose restart
```

### Step 6: Verify
```bash
# Check if site is accessible
curl https://yourdomain.com

# Check service worker
curl https://yourdomain.com/sw.js | head -20
```

---

## Verification After Deployment

### 1. Check Service Worker
```bash
curl https://yourdomain.com/sw.js | grep "CACHE_VERSION"
# Should show: const CACHE_VERSION = 'v1.0.3';
```

### 2. Check Admin Dashboard
- Open browser
- Log in as admin
- Navigate to Admin Dashboard
- Verify all metrics display
- Check browser console (F12) for errors

### 3. Check User Login
- Log out
- Log in as regular user
- Verify dashboard loads
- Check for white pages

### 4. Monitor Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check web server logs
tail -f /var/log/nginx/error.log
# or
tail -f /var/log/apache2/error.log
```

---

## Rollback (if needed)

### Step 1: Revert Changes
```bash
git revert HEAD
```

### Step 2: Rebuild
```bash
npm run build
```

### Step 3: Restart Services
```bash
sudo supervisorctl restart all
# or
sudo systemctl restart mygrownet
```

---

## Troubleshooting

### If Assets Not Loading
```bash
# Rebuild assets
npm run build

# Clear browser cache
# Hard refresh: Ctrl+Shift+R
```

### If Service Worker Not Updating
```bash
# Check service worker file
ls -la public/sw.js

# Check if it's being served
curl https://yourdomain.com/sw.js | head -5
```

### If Admin Dashboard Still Crashing
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check for errors
grep -i error storage/logs/laravel.log | tail -20
```

### If White Pages
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart services
sudo supervisorctl restart all
```

---

## What Was Deployed

### Code Changes
- ✅ Service worker fixed (public/sw.js)
- ✅ Admin dashboard controller fixed
- ✅ Vue component safeguards added
- ✅ Cache buster added
- ✅ Link component import added

### Documentation
- ✅ 8 comprehensive documentation files
- ✅ User guide for cache clearing
- ✅ Deployment procedures
- ✅ Testing checklist

---

## Expected Results

### User Experience
- ✅ No white pages
- ✅ Admin dashboard accessible
- ✅ Faster page loads
- ✅ Automatic cache management

### Console
- ✅ No service worker errors
- ✅ No clone errors
- ✅ No Link component warnings
- ✅ Clean console

### Performance
- ✅ Proper caching
- ✅ Fast page loads
- ✅ No unnecessary requests

---

## Monitoring

### First Hour
- Monitor error logs
- Check admin dashboard access
- Monitor user login success
- Watch for white page reports

### First Day
- Monitor error tracking
- Check user feedback
- Verify cache hit rates
- Confirm no errors

### First Week
- Analyze performance metrics
- Check user engagement
- Monitor for regressions

---

## Support

### If Issues Occur
1. Check logs: `tail -f storage/logs/laravel.log`
2. Check browser console (F12)
3. Verify service worker: `curl https://yourdomain.com/sw.js`
4. Check admin dashboard loads
5. Rollback if critical issues

### Contact
- Technical: [Your contact]
- Operations: [Your contact]
- Support: [Your contact]

---

**Status:** Ready for Deployment
**Version:** 1.0.4
**Date:** November 20, 2025
