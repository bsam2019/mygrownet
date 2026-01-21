# WWW to Non-WWW Redirect Setup

**Purpose:** Redirect www.subdomain.mygrownet.com to subdomain.mygrownet.com for all GrowBuilder sites.

**Status:** Certificate Generated - Ready to Apply

**Last Updated:** January 21, 2026

## Problem

When users visit `www.chisambofarms.mygrownet.com`, they get an SSL error because:
1. The wildcard SSL certificate `*.mygrownet.com` covers `subdomain.mygrownet.com`
2. But `www.subdomain.mygrownet.com` is a different pattern that needs explicit handling

## Solution

**Important:** The wildcard SSL certificate `*.mygrownet.com` does NOT cover `www.subdomain.mygrownet.com` (two-level wildcard). 

**Best Solution:** Use Cloudflare proxy (orange cloud) for the `*.www` DNS record. Cloudflare will handle SSL and redirect.

### Option 1: Cloudflare Proxy (Recommended)

1. In Cloudflare DNS, set `*.www` record to **Proxied** (orange cloud)
2. Cloudflare handles SSL automatically
3. Nginx redirect will work for both HTTP and HTTPS

### Option 2: HTTP-Only Redirect

If you keep DNS-only (gray cloud):
- HTTP redirect works: `http://www.subdomain` → `https://subdomain` ✅
- HTTPS gives SSL error: `https://www.subdomain` → SSL error ❌
- Users should use `subdomain.mygrownet.com` directly

## Current Status

✅ **SSL Certificate Generated:** `/etc/letsencrypt/live/www.mygrownet.com/`
✅ **DNS Configured:** `*.www` A record pointing to 138.197.187.134 (Proxied)
⏳ **Nginx Configuration:** Needs to be updated with certificate path

## Quick Fix (Run on Server)

**SSH into your server and run:**

```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
git pull
sudo bash deployment/apply-www-ssl-fix.sh
```

This will:
1. Find the SSL certificate at `/etc/letsencrypt/live/www.mygrownet.com/`
2. Update nginx configuration to use it
3. Test and reload nginx
4. Enable HTTPS redirect for `www.subdomain.mygrownet.com` → `subdomain.mygrownet.com`

## Complete Setup Guide (Already Done)

### Step 1: Generate SSL Certificate for *.www.mygrownet.com ✅ COMPLETE

**SSH into your server:**
```bash
ssh sammy@138.197.187.134
```

**Run certbot to generate the certificate:**
```bash
sudo certbot certonly \
    --manual \
    --preferred-challenges dns \
    --email admin@mygrownet.com \
    --agree-tos \
    --no-eff-email \
    -d '*.www.mygrownet.com'
```

**Follow the prompts:**
1. Certbot will ask you to add a TXT record to your DNS
2. Record name: `_acme-challenge.www.mygrownet.com`
3. Record value: (certbot will show you the value)

**Add the TXT record in Cloudflare:**
1. Go to Cloudflare DNS settings
2. Add record:
   - Type: TXT
   - Name: `_acme-challenge.www`
   - Content: (paste the value from certbot)
   - TTL: Auto
3. Wait 1-2 minutes for DNS propagation
4. Press Enter in certbot to continue

**Verify certificate was created:**
```bash
sudo ls -la /etc/letsencrypt/live/ | grep www
```

You should see a directory like `www.mygrownet.com` or similar.

### Step 2: Update Nginx Configuration ⏳ READY TO RUN

**The easy way - Run the automated script:**

```bash
# SSH into server
ssh sammy@138.197.187.134

# Pull latest code
cd /var/www/mygrownet.com
git pull

# Run the fix script
sudo bash deployment/apply-www-ssl-fix.sh
```

**Manual method (if you prefer):**

**Still on the server, run:**
```bash
cd /var/www/mygrownet.com

# Find the certificate directory
CERT_DIR=$(sudo ls -d /etc/letsencrypt/live/*www.mygrownet.com* 2>/dev/null | head -1)
echo "Certificate directory: $CERT_DIR"

# Update nginx config
sudo tee /etc/nginx/sites-available/www-redirect > /dev/null << EOF
# WWW to Non-WWW Redirect for GrowBuilder Subdomains
# Redirects www.subdomain.mygrownet.com to subdomain.mygrownet.com

server {
    listen 80;
    listen [::]:80;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com\$;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ~^www\.(?<subdomain>[a-z0-9-]+)\.mygrownet\.com\$;
    
    # SSL certificate for *.www.mygrownet.com
    ssl_certificate $CERT_DIR/fullchain.pem;
    ssl_certificate_key $CERT_DIR/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
    
    # Redirect to non-www
    return 301 https://\$subdomain.mygrownet.com\$request_uri;
}
EOF

# Test nginx configuration
sudo nginx -t

# If test passes, reload nginx
sudo systemctl reload nginx
```

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

### January 21, 2026 - 23:30
- ✅ SSL certificate successfully generated at `/etc/letsencrypt/live/www.mygrownet.com/`
- ✅ Created automated fix script: `deployment/apply-www-ssl-fix.sh`
- ⏳ Ready to apply nginx configuration update
- Next step: Run `sudo bash deployment/apply-www-ssl-fix.sh` on server

### January 21, 2026
- Created nginx configuration for www redirect
- Documented manual setup steps
- Ready for production deployment
