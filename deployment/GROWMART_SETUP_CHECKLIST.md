# GrowMart Subdomain Setup Checklist

**Subdomain:** growmart.mygrownet.com  
**Date:** June 14, 2026

## Pre-Setup Verification

- [ ] Server credentials available (`.deploy-credentials` file exists)
- [ ] SSH access to server confirmed (`ssh sammy@138.197.187.134`)
- [ ] Wildcard SSL certificate active (`*.mygrownet.com`)
- [ ] GrowMart module code deployed to server
- [ ] Database migrations run (`growmart_*` tables exist)

## DNS Configuration

- [ ] Log in to Cloudflare account
- [ ] Select `mygrownet.com` domain
- [ ] Go to DNS → Records
- [ ] Add CNAME record:
  - Name: `growmart`
  - Target: `mygrownet.com`
  - Proxy: Enabled (orange cloud ☁️)
  - TTL: Auto
- [ ] Click "Save"
- [ ] Note the time (for propagation tracking)

## Server Setup

### Option 1: Automated (Recommended)

**Windows (PowerShell):**
```powershell
cd c:\Apache24\htdocs\mygrownet
.\deployment\setup-growmart-subdomain.ps1
```

**Windows (Git Bash) or Linux:**
```bash
cd c:\Apache24\htdocs\mygrownet
bash deployment/setup-growmart-subdomain.sh
```

- [ ] Script executed successfully
- [ ] No error messages displayed
- [ ] Configuration files uploaded
- [ ] Nginx reloaded successfully
- [ ] Laravel caches cleared

### Option 2: Manual

- [ ] Upload nginx config: `scp deployment/growmart-subdomain.conf sammy@138.197.187.134:/tmp/`
- [ ] SSH to server: `ssh sammy@138.197.187.134`
- [ ] Move config: `sudo mv /tmp/growmart-subdomain.conf /etc/nginx/sites-available/`
- [ ] Create symlink: `sudo ln -s /etc/nginx/sites-available/growmart-subdomain.conf /etc/nginx/sites-enabled/`
- [ ] Test config: `sudo nginx -t`
- [ ] Reload nginx: `sudo systemctl reload nginx`
- [ ] Clear Laravel cache:
  ```bash
  cd /var/www/mygrownet.com
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan config:cache
  php artisan route:cache
  ```

## DNS Propagation Wait

- [ ] Wait 5-30 minutes for DNS to propagate
- [ ] Check DNS: `nslookup growmart.mygrownet.com`
- [ ] Should return Cloudflare IPs (if proxied)

## Testing & Verification

### HTTP/HTTPS Testing

- [ ] Test HTTP redirect:
  ```bash
  curl -I http://growmart.mygrownet.com
  ```
  Expected: `301` or `302` redirect

- [ ] Test HTTPS:
  ```bash
  curl -I https://growmart.mygrownet.com
  ```
  Expected: `200 OK`

### SSL Certificate

- [ ] Verify SSL:
  ```bash
  echo | openssl s_client -servername growmart.mygrownet.com -connect growmart.mygrownet.com:443 2>/dev/null | openssl x509 -noout -subject -dates
  ```
- [ ] Certificate shows `*.mygrownet.com`
- [ ] Expiry date is in the future

### Browser Testing

#### Customer Frontend
- [ ] Home page loads: https://growmart.mygrownet.com
- [ ] Products page: https://growmart.mygrownet.com/products
- [ ] Product detail page (click any product)
- [ ] No console errors (F12 → Console)
- [ ] SSL padlock icon showing (secure connection)
- [ ] Images loading correctly
- [ ] CSS/JS loading correctly

#### Authenticated Features (Login Required)
- [ ] Cart page: https://growmart.mygrownet.com/cart
- [ ] Add product to cart functionality
- [ ] Checkout page: https://growmart.mygrownet.com/checkout
- [ ] Orders page: https://growmart.mygrownet.com/orders
- [ ] Wishlist: https://growmart.mygrownet.com/wishlist

#### Admin Panel (Admin Login Required)
- [ ] Admin dashboard: https://growmart.mygrownet.com/admin
- [ ] Products management
- [ ] Orders management
- [ ] Inventory management
- [ ] Categories management
- [ ] Coupons management
- [ ] Reviews management

### Performance Check

- [ ] Page load time < 3 seconds
- [ ] No 502 Bad Gateway errors
- [ ] No 404 errors on static assets
- [ ] PHP errors check: `ssh sammy@138.197.187.134 "sudo tail -50 /var/log/nginx/growmart-error.log"`

## Post-Setup Tasks

### Optional: Seed Demo Data

```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
php artisan db:seed --class=GrowMartDemoSeeder
```

- [ ] Demo products added
- [ ] Demo categories created
- [ ] Test orders created (optional)

### Configuration Review

- [ ] Payment gateway credentials in `.env`
- [ ] Email settings configured
- [ ] File upload limits appropriate (50MB default)
- [ ] Cloudflare settings reviewed:
  - [ ] SSL mode: "Full" or "Full (strict)"
  - [ ] Always Use HTTPS: Enabled
  - [ ] Auto Minify: Enabled (CSS, JS, HTML)

### Monitoring Setup

- [ ] Bookmark admin panel URL
- [ ] Add to uptime monitor (if using one)
- [ ] Set up error alerts (optional)
- [ ] Document admin credentials securely

## Rollback Plan (If Needed)

If something goes wrong:

```bash
ssh sammy@138.197.187.134
sudo rm /etc/nginx/sites-enabled/growmart-subdomain.conf
sudo systemctl reload nginx
```

- [ ] Nginx config removed
- [ ] Service reloaded
- [ ] Main site still working

## Documentation

- [ ] Read full setup guide: `deployment/GROWMART_SUBDOMAIN_SETUP.md`
- [ ] Bookmark admin URLs
- [ ] Share access info with team (securely)
- [ ] Update internal documentation

## Troubleshooting Reference

### Common Issues

**502 Bad Gateway:**
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

**404 on all pages:**
```bash
cd /var/www/mygrownet.com
php artisan config:clear
php artisan route:clear
php artisan config:cache
php artisan route:cache
```

**Assets not loading:**
```bash
cd /var/www/mygrownet.com
npm run build
sudo chown -R www-data:www-data public/build
```

**Check logs:**
```bash
# Nginx error log
sudo tail -f /var/log/nginx/growmart-error.log

# Laravel log
sudo tail -f /var/www/mygrownet.com/storage/logs/laravel.log
```

## Final Sign-Off

- [ ] All tests passed
- [ ] No errors in logs
- [ ] Team notified
- [ ] Documentation updated
- [ ] Setup complete! 🎉

---

**Setup completed by:** _________________  
**Date:** _________________  
**Issues encountered:** _________________  
**Resolution notes:** _________________
