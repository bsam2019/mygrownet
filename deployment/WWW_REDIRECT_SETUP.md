# WWW to Non-WWW Redirect Setup

**Purpose:** Redirect www.subdomain.mygrownet.com to subdomain.mygrownet.com for all GrowBuilder sites.

**Status:** Ready to deploy

## Problem

When users visit `www.chisambofarms.mygrownet.com`, they get an SSL error because:
1. The wildcard SSL certificate `*.mygrownet.com` covers `subdomain.mygrownet.com`
2. But `www.subdomain.mygrownet.com` is a different pattern that needs explicit handling

## Solution

Redirect all www.subdomain requests to subdomain (without www). This is the standard web practice.

## Manual Setup Steps

SSH into the production server and run these commands:

```bash
# 1. Copy the nginx configuration
cd /var/www/mygrownet.com
sudo cp deployment/nginx-www-redirect.conf /etc/nginx/sites-available/www-redirect

# 2. Enable the configuration
sudo ln -sf /etc/nginx/sites-available/www-redirect /etc/nginx/sites-enabled/www-redirect

# 3. Test nginx configuration
sudo nginx -t

# 4. If test passes, reload nginx
sudo systemctl reload nginx
```

## What This Does

The nginx configuration:
- Listens for requests to `www.*.mygrownet.com`
- Extracts the subdomain name
- Redirects with 301 (permanent) to `subdomain.mygrownet.com`

### Examples:
- `www.chisambofarms.mygrownet.com` → `chisambofarms.mygrownet.com`
- `www.anysite.mygrownet.com` → `anysite.mygrownet.com`

## Testing

After setup, test with:

```bash
# Should show 301 redirect
curl -I https://www.chisambofarms.mygrownet.com

# Should work normally
curl -I https://chisambofarms.mygrownet.com
```

Or visit in browser:
- https://www.chisambofarms.mygrownet.com (should redirect)
- https://chisambofarms.mygrownet.com (should work)

## Files

- `deployment/nginx-www-redirect.conf` - Nginx configuration
- `deployment/setup-www-redirect.sh` - Automated setup script (requires credentials)
- `deployment/check-ssl-certificates.sh` - Comprehensive SSL diagnostic tool
- `deployment/fix-www-ssl-auto.sh` - Auto-fix script for SSL issues
- `deployment/generate-wildcard-ssl.sh` - Generate new wildcard SSL certificate

## Notes

- Uses the existing wildcard SSL certificate
- No additional SSL certificates needed
- Works for all current and future GrowBuilder subdomains
- Standard SEO-friendly approach (301 redirect)

## Troubleshooting

### Quick Diagnostic

Run the comprehensive diagnostic script:

```bash
cd /var/www/mygrownet.com
sudo bash deployment/check-ssl-certificates.sh
```

This will check:
- SSL certificate locations and coverage
- Nginx configuration
- DNS resolution
- SSL handshake
- Error logs

### Auto-Fix Script

If you're getting `ERR_SSL_VERSION_OR_CIPHER_MISMATCH`, run the auto-fix script:

```bash
cd /var/www/mygrownet.com
sudo bash deployment/fix-www-ssl-auto.sh
```

This will:
- Automatically find the correct SSL certificate
- Update nginx configuration with correct paths
- Test and reload nginx
- Verify the fix

### Manual Troubleshooting

If auto-fix doesn't work:

1. **Check nginx syntax:**
   ```bash
   sudo nginx -t
   ```

2. **Check if config is enabled:**
   ```bash
   ls -la /etc/nginx/sites-enabled/ | grep www-redirect
   ```

3. **Check nginx error logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

4. **Verify SSL certificate covers wildcard:**
   ```bash
   sudo certbot certificates | grep mygrownet.com
   ```

5. **Check certificate details:**
   ```bash
   sudo openssl x509 -in /etc/letsencrypt/live/mygrownet.com/fullchain.pem -text -noout | grep -A 2 "Subject Alternative Name"
   ```

### Generate New Wildcard Certificate

If the certificate doesn't exist or doesn't cover the wildcard:

```bash
cd /var/www/mygrownet.com
sudo bash deployment/generate-wildcard-ssl.sh
```

This will generate a new wildcard certificate covering `*.mygrownet.com`

## Changelog

### January 21, 2026
- Created nginx configuration for www redirect
- Documented manual setup steps
- Ready for production deployment
