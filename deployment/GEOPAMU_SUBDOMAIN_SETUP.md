# Geopamu Subdomain Setup

**Last Updated:** January 12, 2026
**Status:** Ready for deployment

## Overview

Geopamu is now accessible via `geopamu.mygrownet.com` subdomain, providing a dedicated URL for the Geopamu business website.

## DNS Configuration

### Cloudflare DNS Settings

Add the following DNS record in Cloudflare:

```
Type: CNAME
Name: geopamu
Target: mygrownet.com
Proxy status: Proxied (orange cloud)
TTL: Auto
```

**Or if using A record:**

```
Type: A
Name: geopamu
IPv4 address: 138.197.187.134
Proxy status: Proxied (orange cloud)
TTL: Auto
```

## How It Works

1. **User visits:** `geopamu.mygrownet.com`
2. **Middleware detects:** `geopamu` subdomain
3. **Redirects to:** `/geopamu` path on main site
4. **Result:** Geopamu website loads seamlessly

## Implementation

### Modified Files

**app/Http/Middleware/DetectSubdomain.php**
- Added special handling for `geopamu` subdomain
- Redirects `geopamu.mygrownet.com` → `/geopamu`
- Preserves path (e.g., `geopamu.mygrownet.com/services` → `/geopamu/services`)

### Routes

All existing Geopamu routes work automatically:
- `geopamu.mygrownet.com` → Home
- `geopamu.mygrownet.com/services` → Services
- `geopamu.mygrownet.com/portfolio` → Portfolio
- `geopamu.mygrownet.com/about` → About
- `geopamu.mygrownet.com/contact` → Contact
- `geopamu.mygrownet.com/blog` → Blog

## Testing

### After DNS Propagation (5-30 minutes)

1. **Visit:** `https://geopamu.mygrownet.com`
2. **Expected:** Geopamu home page loads
3. **Check:** All navigation links work
4. **Verify:** Blog and admin sections accessible

### Test URLs

```bash
# Home
curl -I https://geopamu.mygrownet.com

# Services
curl -I https://geopamu.mygrownet.com/services

# Blog
curl -I https://geopamu.mygrownet.com/blog
```

## Deployment Steps

### 1. Update Code on Server

```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
git pull origin main
php artisan config:clear
php artisan cache:clear
```

### 2. Add DNS Record in Cloudflare

1. Log in to Cloudflare
2. Select `mygrownet.com` domain
3. Go to DNS → Records
4. Click "Add record"
5. Add CNAME: `geopamu` → `mygrownet.com`
6. Enable proxy (orange cloud)
7. Save

### 3. Wait for DNS Propagation

- Usually takes 5-30 minutes
- Check status: `nslookup geopamu.mygrownet.com`

### 4. Test

Visit `https://geopamu.mygrownet.com` and verify it works.

## SSL Certificate

Cloudflare automatically handles SSL for the subdomain when proxied (orange cloud enabled).

**No additional SSL configuration needed.**

## Troubleshooting

### Issue: Subdomain not resolving

**Check:**
1. DNS record added correctly in Cloudflare?
2. Orange cloud (proxy) enabled?
3. Wait 30 minutes for propagation

**Test DNS:**
```bash
nslookup geopamu.mygrownet.com
# Should return Cloudflare IP addresses
```

### Issue: 404 or redirect loop

**Fix:**
```bash
# On server
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Issue: SSL certificate error

**Fix:**
- Ensure orange cloud (proxy) is enabled in Cloudflare
- Cloudflare handles SSL automatically
- Wait a few minutes for SSL to provision

## Benefits

1. **Professional URL:** `geopamu.mygrownet.com` instead of `mygrownet.com/geopamu`
2. **Brand separation:** Geopamu has its own identity
3. **SEO:** Better for search engine optimization
4. **Marketing:** Easier to promote and remember

## Future Enhancements

### Custom Domain (Optional)

If Geopamu wants their own domain (e.g., `geopamu.com`):

1. Point `geopamu.com` to `138.197.187.134`
2. Update middleware to detect `geopamu.com`
3. Add SSL certificate for custom domain

## Changelog

### January 12, 2026
- Initial subdomain setup
- Added middleware detection for `geopamu` subdomain
- Documented DNS configuration
- Ready for production deployment
