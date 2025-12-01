# Deployment Quick Reference

## Server Access

```bash
# SSH to production server
ssh sammy@138.197.187.134

# Navigate to project
cd /var/www/mygrownet.com
```

**Credentials:**
- IP: 138.197.187.134
- User: sammy
- Password: Bsam@2025!!
- Project Path: /var/www/mygrownet.com

## Common Deployment Tasks

### 1. Deploy Latest Code

```bash
# Using deployment script
cd /var/www/mygrownet.com
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
./scripts/fix-vite-manifest.sh
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

### 2. Fix Mobile White Screen

```bash
# Quick fix
mkdir -p public/build/.vite
cp public/build/manifest.json public/build/.vite/manifest.json
php artisan optimize:clear
php artisan optimize
```

### 3. Clear All Caches

```bash
php artisan optimize:clear
# Or individually:
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 4. Rebuild Assets

```bash
npm run build
./scripts/fix-vite-manifest.sh
```

### 5. Check Application Status

```bash
# Check if site is up
curl -I https://mygrownet.com

# Check recent logs
tail -50 storage/logs/laravel.log

# Check for errors
tail -100 storage/logs/laravel.log | grep -i error

# Check disk space
df -h

# Check processes
ps aux | grep php
```

### 6. Database Operations

```bash
# Run migrations
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback --step=1

# Check migration status
php artisan migrate:status

# Seed database
php artisan db:seed
```

### 7. Queue Management

```bash
# Start queue worker
php artisan queue:work --daemon

# Check queue status
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### 8. Permission Fixes

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# After operations, restore secure permissions
sudo chmod -R 755 storage bootstrap/cache
```

## Troubleshooting

### White Screen on Mobile

**Cause:** Vite manifest in wrong location  
**Fix:**
```bash
mkdir -p public/build/.vite
cp public/build/manifest.json public/build/.vite/manifest.json
php artisan optimize:clear
```

### 500 Internal Server Error

**Check:**
1. Laravel logs: `tail -50 storage/logs/laravel.log`
2. Nginx logs: `sudo tail -50 /var/log/nginx/error.log`
3. PHP logs: `sudo tail -50 /var/log/php8.2-fpm.log`

**Common fixes:**
```bash
# Clear caches
php artisan optimize:clear

# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Assets Not Loading

**Check:**
```bash
# Verify build directory
ls -la public/build/

# Verify manifest files
ls -la public/build/manifest.json
ls -la public/build/.vite/manifest.json

# Rebuild if needed
npm run build
./scripts/fix-vite-manifest.sh
```

### Database Connection Issues

**Check:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check .env file
cat .env | grep DB_
```

## Service Management

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Check service status
sudo systemctl status php8.2-fpm
sudo systemctl status nginx

# View service logs
sudo journalctl -u php8.2-fpm -n 50
sudo journalctl -u nginx -n 50
```

## Monitoring

```bash
# Watch logs in real-time
tail -f storage/logs/laravel.log

# Monitor system resources
htop

# Check disk usage
du -sh storage/logs/*
du -sh public/build/*

# Check database size
php artisan tinker
>>> DB::select('SELECT table_schema AS "Database", ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS "Size (MB)" FROM information_schema.TABLES GROUP BY table_schema');
```

## Backup

```bash
# Backup database
php artisan backup:run

# Manual database backup
mysqldump -u root -p mygrownet > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d_%H%M%S).tar.gz storage/ public/uploads/
```

## Emergency Procedures

### Site Down - Quick Recovery

```bash
# 1. Check if services are running
sudo systemctl status nginx php8.2-fpm

# 2. Restart services
sudo systemctl restart php8.2-fpm nginx

# 3. Clear all caches
php artisan optimize:clear

# 4. Check logs
tail -50 storage/logs/laravel.log
```

### Rollback Deployment

```bash
# 1. Checkout previous commit
git log --oneline -10
git checkout <previous-commit-hash>

# 2. Rebuild
composer install --no-dev
npm run build
./scripts/fix-vite-manifest.sh

# 3. Clear caches
php artisan optimize:clear
php artisan optimize
```

## Useful Scripts

All scripts are in `scripts/` directory:

- `fix-vite-manifest.sh` - Fix Vite manifest location
- `test-messaging-system.php` - Test messaging functionality
- `debug-wallet-balance.php` - Debug wallet issues
- `test-gift-limits.php` - Test gift system

## Important Files

- `.env` - Environment configuration
- `storage/logs/laravel.log` - Application logs
- `public/build/` - Compiled assets
- `bootstrap/cache/` - Laravel cache files

## Notes

- Always test changes locally first
- Keep backups before major changes
- Monitor logs after deployment
- Clear caches after configuration changes
- Run `fix-vite-manifest.sh` after building assets
