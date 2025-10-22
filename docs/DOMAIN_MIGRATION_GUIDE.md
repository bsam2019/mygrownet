# Domain Migration Guide: mygrownet.edulinkzm.com → mygrownet.com

## Current Server Configuration

**Server IP:** 138.197.187.134  
**Current Domain:** mygrownet.edulinkzm.com  
**New Domain:** mygrownet.com  
**App Path:** /var/www/mygrownet.com

### Current Nginx Configuration
```nginx
# HTTP to HTTPS redirect
server {
    listen 80;
    server_name mygrownet.edulinkzm.com www.mygrownet.edulinkzm.com 138.197.187.134;
    return 301 https://mygrownet.edulinkzm.com$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name mygrownet.edulinkzm.com;
    root /var/www/mygrownet.com/public;
    
    ssl_certificate /etc/letsencrypt/live/mygrownet.edulinkzm.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mygrownet.edulinkzm.com/privkey.pem;
    ...
}
```

### Current .env Configuration
```
APP_URL=https://mygrownet.edulinkzm.com
```

## Migration Steps

### Step 1: Update DNS Records (Do This First!)

Before running the migration script, update your DNS records:

**Required DNS Records:**
```
Type: A
Name: mygrownet.com
Value: 138.197.187.134
TTL: 3600 (or your preference)

Type: A
Name: www.mygrownet.com
Value: 138.197.187.134
TTL: 3600 (or your preference)
```

**Verify DNS Propagation:**
```bash
# Check if DNS is propagated
dig mygrownet.com
dig www.mygrownet.com

# Should show: 138.197.187.134
```

⚠️ **Wait for DNS propagation** (can take 5 minutes to 48 hours depending on TTL)

### Step 2: Run Migration Script

Once DNS is propagated, run the migration script:

```bash
bash deployment/update-server-config.sh
```

**What the script does:**
1. ✅ Shows current nginx configuration
2. ✅ Creates backup of current config
3. ✅ Updates nginx server block to use mygrownet.com
4. ✅ Tests nginx configuration
5. ✅ Reloads nginx
6. ✅ Prompts for SSL certificate setup (requires DNS propagation)
7. ✅ Updates Laravel .env file
8. ✅ Clears and optimizes Laravel caches
9. ✅ Fixes file permissions

### Step 3: SSL Certificate Setup

**Option A: During Script Execution**
- When prompted, confirm DNS is propagated
- Script will automatically obtain SSL certificates

**Option B: Manual Setup (if skipped during script)**
```bash
ssh sammy@138.197.187.134
sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com
```

### Step 4: Verify Migration

**Test the new domain:**
```bash
# Test HTTP redirect
curl -I http://mygrownet.com

# Test HTTPS
curl -I https://mygrownet.com

# Test www subdomain
curl -I https://www.mygrownet.com
```

**Check the site in browser:**
- https://mygrownet.com
- https://www.mygrownet.com

**Verify SSL certificate:**
- Check for valid SSL certificate (green padlock)
- Certificate should be issued for mygrownet.com

## Rollback Plan

If something goes wrong, you can rollback:

```bash
ssh sammy@138.197.187.134

# Restore backup
sudo cp /etc/nginx/sites-available/mygrownet.com.backup /etc/nginx/sites-available/mygrownet.com

# Test and reload nginx
sudo nginx -t
sudo systemctl reload nginx

# Restore .env
cd /var/www/mygrownet.com
sudo sed -i "s|APP_URL=https://mygrownet.com|APP_URL=https://mygrownet.edulinkzm.com|g" .env

# Clear caches
php artisan config:clear
php artisan cache:clear
```

## Post-Migration Checklist

- [ ] DNS records updated and propagated
- [ ] Migration script executed successfully
- [ ] SSL certificates obtained and installed
- [ ] Site accessible at https://mygrownet.com
- [ ] Site accessible at https://www.mygrownet.com
- [ ] HTTP redirects to HTTPS
- [ ] All pages load correctly
- [ ] Login/authentication works
- [ ] Database connections working
- [ ] File uploads working
- [ ] Email notifications working (check APP_URL in emails)

## Troubleshooting

### Issue: SSL Certificate Error
**Cause:** DNS not propagated or Certbot failed  
**Solution:**
```bash
# Wait for DNS propagation, then run:
ssh sammy@138.197.187.134
sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com
```

### Issue: 502 Bad Gateway
**Cause:** PHP-FPM not running or misconfigured  
**Solution:**
```bash
ssh sammy@138.197.187.134
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm
```

### Issue: Permission Denied Errors
**Cause:** Incorrect file permissions  
**Solution:**
```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Old Domain Still Showing
**Cause:** Laravel cache not cleared  
**Solution:**
```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## Important Notes

1. **Backup First:** The script automatically creates a backup, but you can create additional backups:
   ```bash
   ssh sammy@138.197.187.134
   sudo cp -r /var/www/mygrownet.com /var/www/mygrownet.com.backup
   ```

2. **DNS Propagation:** Don't skip this step! SSL certificates will fail without proper DNS.

3. **Old Domain:** The old domain (mygrownet.edulinkzm.com) will stop working after migration.

4. **Database:** No database changes are needed - only configuration files are updated.

5. **Downtime:** Minimal downtime expected (< 1 minute during nginx reload).

## Support

If you encounter issues:
1. Check nginx error logs: `sudo tail -f /var/log/nginx/error.log`
2. Check Laravel logs: `tail -f /var/www/mygrownet.com/storage/logs/laravel.log`
3. Verify nginx config: `sudo nginx -t`
4. Check PHP-FPM: `sudo systemctl status php8.3-fpm`
