# GrowMart Subdomain Setup Guide

**Last Updated:** June 14, 2026  
**Status:** Ready for Production  
**Subdomain:** growmart.mygrownet.com

## Overview

GrowMart is MyGrowNet's e-commerce marketplace, accessible via `growmart.mygrownet.com`. This guide covers the complete setup process for the subdomain on the production server.

## Prerequisites

✅ **Already in place:**
- Wildcard SSL certificate for `*.mygrownet.com`
- Laravel application with GrowMart module
- DetectSubdomain middleware configured
- GrowMart routes in `routes/growmart-subdomain.php`
- Database migrations for GrowMart tables

## Server Information

- **Server IP:** 138.197.187.134
- **SSH User:** sammy
- **Project Path:** /var/www/mygrownet.com
- **PHP Version:** 8.3
- **Web Server:** Nginx
- **SSL:** Let's Encrypt (Wildcard)

## Setup Steps

### 1. DNS Configuration (Cloudflare)

Add a CNAME record in Cloudflare DNS settings:

```
Type: CNAME
Name: growmart
Target: mygrownet.com
Proxy status: Proxied (orange cloud enabled)
TTL: Auto
```

**Alternative (A Record):**
```
Type: A
Name: growmart
IPv4 address: 138.197.187.134
Proxy status: Proxied (orange cloud enabled)
TTL: Auto
```

### 2. Run Deployment Script

From your local machine (Windows):

```powershell
# Navigate to project directory
cd c:\Apache24\htdocs\mygrownet

# Make script executable and run (using Git Bash or WSL)
bash deployment/setup-growmart-subdomain.sh
```

**What the script does:**
1. ✓ Uploads nginx configuration to server
2. ✓ Creates symlink in sites-enabled
3. ✓ Tests nginx configuration
4. ✓ Reloads nginx service
5. ✓ Clears Laravel caches
6. ✓ Tests HTTP/HTTPS connectivity
7. ✓ Verifies SSL certificate

### 3. Wait for DNS Propagation

DNS changes typically take **5-30 minutes** to propagate.

**Check DNS propagation:**
```bash
nslookup growmart.mygrownet.com
```

Expected result: Cloudflare IP addresses (if proxied)

### 4. Verify Setup

#### Test HTTP → HTTPS Redirect
```bash
curl -I http://growmart.mygrownet.com
# Should return: 301 or 302 redirect to https://
```

#### Test HTTPS
```bash
curl -I https://growmart.mygrownet.com
# Should return: 200 OK
```

#### Test in Browser
1. Visit: https://growmart.mygrownet.com
2. Expected: GrowMart home page loads
3. Verify: SSL padlock icon shows (secure connection)

## How It Works

### Request Flow

```
User → growmart.mygrownet.com
  ↓
Cloudflare DNS (CNAME → mygrownet.com)
  ↓
Nginx (growmart-subdomain.conf)
  ↓
DetectSubdomain Middleware
  ↓
growmart-subdomain.php routes
  ↓
GrowMart Controllers
  ↓
Response
```

### Middleware Detection

The `DetectSubdomain` middleware in `app/Http/Middleware/DetectSubdomain.php` detects the `growmart` subdomain and:

1. Sets the base URL to `https://growmart.mygrownet.com`
2. Configures asset URLs for proper resource loading
3. Routes requests to GrowMart-specific routes
4. Maintains clean URLs (no `/growmart` prefix visible)

### Route Configuration

Routes are defined in `routes/growmart-subdomain.php` with the domain constraint:

```php
Route::domain('growmart.mygrownet.com')->name('growmart.subdomain.')->group(function () {
    // Public routes
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    
    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::get('/cart', [CartController::class, 'index']);
        Route::get('/orders', [OrderController::class, 'index']);
    });
    
    // Admin routes
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
    });
});
```

## Features & URLs

### Customer Frontend
- **Home:** https://growmart.mygrownet.com
- **Products:** https://growmart.mygrownet.com/products
- **Product Details:** https://growmart.mygrownet.com/products/{slug}
- **Cart:** https://growmart.mygrownet.com/cart
- **Checkout:** https://growmart.mygrownet.com/checkout
- **Orders:** https://growmart.mygrownet.com/orders
- **Wishlist:** https://growmart.mygrownet.com/wishlist

### Admin Panel
- **Dashboard:** https://growmart.mygrownet.com/admin
- **Products:** https://growmart.mygrownet.com/admin/products
- **Orders:** https://growmart.mygrownet.com/admin/orders
- **Inventory:** https://growmart.mygrownet.com/admin/inventory
- **Categories:** https://growmart.mygrownet.com/admin/categories
- **Coupons:** https://growmart.mygrownet.com/admin/coupons
- **Reviews:** https://growmart.mygrownet.com/admin/reviews

### Legal Pages
- **Terms:** https://growmart.mygrownet.com/terms
- **Refund Policy:** https://growmart.mygrownet.com/refund-policy
- **Contact:** https://growmart.mygrownet.com/contact

## Configuration Files

### Nginx Configuration
**Location:** `/etc/nginx/sites-available/growmart-subdomain.conf`

Key settings:
- HTTP → HTTPS redirect
- PHP 8.3 FPM processing
- 50MB file upload limit (for product images)
- Static asset caching (1 year)
- Security headers
- Dedicated log files

### Laravel Configuration
The middleware automatically configures:
```php
URL::forceRootUrl('https://growmart.mygrownet.com');
config(['app.url' => 'https://growmart.mygrownet.com']);
config(['app.asset_url' => 'https://growmart.mygrownet.com']);
```

## Database Tables

GrowMart uses the following tables:
- `growmart_categories` - Product categories
- `growmart_products` - Products
- `growmart_product_images` - Product images
- `growmart_warehouses` - Warehouse locations
- `growmart_inventory` - Stock levels
- `growmart_carts` - Shopping carts
- `growmart_cart_items` - Cart items
- `growmart_orders` - Orders
- `growmart_order_items` - Order items
- `growmart_wishlist_items` - Wishlists
- `growmart_coupons` - Discount coupons
- `growmart_reviews` - Product reviews

## Logs & Monitoring

### Nginx Logs
```bash
# Access log
sudo tail -f /var/log/nginx/growmart-access.log

# Error log
sudo tail -f /var/log/nginx/growmart-error.log
```

### Laravel Logs
```bash
# Application log
sudo tail -f /var/www/mygrownet.com/storage/logs/laravel.log
```

### Check Nginx Status
```bash
sudo systemctl status nginx
```

### Test Nginx Configuration
```bash
sudo nginx -t
```

## Troubleshooting

### Issue: Subdomain not resolving

**Possible causes:**
1. DNS record not added in Cloudflare
2. DNS propagation not complete (wait 30 minutes)
3. Cloudflare proxy not enabled

**Solution:**
```bash
# Check DNS resolution
nslookup growmart.mygrownet.com

# Check if server is reachable
ping growmart.mygrownet.com

# Test direct IP connection
curl -I https://138.197.187.134 -H "Host: growmart.mygrownet.com"
```

### Issue: 502 Bad Gateway

**Possible causes:**
1. PHP-FPM not running
2. Socket file permission issues

**Solution:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Check socket file
ls -la /var/run/php/php8.3-fpm.sock
```

### Issue: 404 errors on all pages

**Possible causes:**
1. Laravel routes not cached
2. Middleware not detecting subdomain correctly

**Solution:**
```bash
cd /var/www/mygrownet.com

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### Issue: SSL certificate error

**Possible causes:**
1. Wildcard certificate not covering subdomain
2. Cloudflare SSL mode misconfigured

**Solution:**
```bash
# Verify certificate includes wildcard
sudo certbot certificates

# Check certificate on server
echo | openssl s_client -servername growmart.mygrownet.com -connect 138.197.187.134:443 2>/dev/null | openssl x509 -noout -text

# Cloudflare SSL mode should be "Full" or "Full (strict)"
```

### Issue: Assets not loading (CSS/JS)

**Possible causes:**
1. Asset URL not configured correctly
2. Vite manifest missing

**Solution:**
```bash
cd /var/www/mygrownet.com

# Rebuild frontend assets
npm run build

# Check file permissions
sudo chown -R www-data:www-data public/build

# Clear cache
php artisan config:clear
```

### Issue: Redirect loop

**Possible causes:**
1. Cloudflare SSL mode set to "Flexible"
2. Multiple redirects configured

**Solution:**
1. Set Cloudflare SSL mode to "Full" or "Full (strict)"
2. Check nginx configuration for double redirects
3. Clear browser cache

## Manual Setup (Alternative)

If the automated script fails, follow these manual steps:

### 1. Copy nginx configuration
```bash
scp deployment/growmart-subdomain.conf sammy@138.197.187.134:/tmp/
```

### 2. SSH to server
```bash
ssh sammy@138.197.187.134
```

### 3. Install configuration
```bash
sudo mv /tmp/growmart-subdomain.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/growmart-subdomain.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Update Laravel
```bash
cd /var/www/mygrownet.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

## Security Considerations

✓ **Implemented:**
- HTTPS enforced (HTTP redirects)
- Wildcard SSL certificate
- Security headers (HSTS, XSS Protection, etc.)
- File upload size limits
- Hidden file access denied
- Cloudflare DDoS protection (when proxied)

🔒 **Additional recommendations:**
- Enable Cloudflare WAF rules
- Set up rate limiting for API endpoints
- Monitor logs for suspicious activity
- Regular security updates

## Performance Optimization

✓ **Already configured:**
- Static asset caching (1 year expiry)
- Gzip compression
- Laravel route caching
- OpCache enabled

📈 **Future enhancements:**
- Redis cache for sessions
- CDN for product images
- Database query optimization
- Full-page caching for product listings

## Backup & Rollback

### Backup current configuration
```bash
ssh sammy@138.197.187.134
sudo cp /etc/nginx/sites-available/growmart-subdomain.conf /etc/nginx/sites-available/growmart-subdomain.conf.backup
```

### Rollback if needed
```bash
ssh sammy@138.197.187.134
sudo rm /etc/nginx/sites-enabled/growmart-subdomain.conf
sudo systemctl reload nginx
```

## Next Steps After Setup

1. ✅ **Test all functionality:**
   - Browse products
   - Add to cart
   - Complete checkout
   - Admin panel access
   - Review system

2. ✅ **Seed demo data (if needed):**
   ```bash
   php artisan db:seed --class=GrowMartDemoSeeder
   ```

3. ✅ **Configure payment gateways:**
   - Update `.env` with payment credentials
   - Test payment processing

4. ✅ **Monitor performance:**
   - Check response times
   - Monitor error logs
   - Set up uptime monitoring

5. ✅ **Documentation for users:**
   - Create user guides
   - Admin training materials
   - FAQ section

## Support & Maintenance

### Regular maintenance tasks
```bash
# Weekly: Clear expired sessions
php artisan session:clear

# Monthly: Check disk space
df -h

# Monthly: Review error logs
sudo tail -100 /var/log/nginx/growmart-error.log

# Quarterly: Update dependencies
composer update
npm update
```

### Emergency contacts
- **Server Admin:** sammy@138.197.187.134
- **Deployment credentials:** `.deploy-credentials` file

## Changelog

### June 14, 2026
- Initial subdomain configuration created
- Nginx config file generated
- Automated deployment script written
- Complete setup documentation

## References

- Main domain config: `deployment/mygrownet-nginx.conf`
- Geopamu subdomain setup: `deployment/GEOPAMU_SUBDOMAIN_SETUP.md`
- WWW redirect setup: `deployment/WWW_REDIRECT_SETUP.md`
- DetectSubdomain middleware: `app/Http/Middleware/DetectSubdomain.php`
- GrowMart routes: `routes/growmart-subdomain.php`

---

**For questions or issues, check the troubleshooting section or review server logs.**
