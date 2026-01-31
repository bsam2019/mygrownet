# GrowBuilder Custom Domain Setup Guide

**Last Updated:** January 30, 2026  
**Status:** Production Ready

## Overview

GrowBuilder allows users on Starter, Business, and Agency plans to connect their own custom domains (e.g., `mybusiness.com`) to their GrowBuilder sites instead of using the default subdomain (`mybusiness.mygrownet.com`).

## Requirements

### Plan Requirements
- **Free Plan**: Custom domains NOT available (subdomain only)
- **Starter Plan**: ✅ 1 custom domain included
- **Business Plan**: ✅ 1 custom domain included
- **Agency Plan**: ✅ Multiple custom domains (one per site)

### Technical Requirements
- You must own the domain name
- Access to your domain's DNS settings (via your domain registrar or DNS provider)
- Domain must be registered and active

## How It Works

### Architecture
1. User purchases domain from any registrar (Namecheap, GoDaddy, Cloudflare, etc.)
2. User adds DNS records pointing to MyGrowNet server
3. User enters custom domain in GrowBuilder site settings
4. System verifies DNS configuration
5. SSL certificate is automatically generated
6. Site becomes accessible via custom domain

### DNS Configuration
Users need to point their domain to MyGrowNet's server IP address:

**Server IP:** `138.197.187.134`

## Step-by-Step Setup Instructions

### For Users (Customer-Facing Guide)

#### Step 1: Purchase Your Domain
1. Buy a domain from any registrar:
   - Namecheap (recommended for Zambia)
   - GoDaddy
   - Cloudflare
   - Google Domains
   - Any other registrar

2. Complete domain registration and payment

#### Step 2: Configure DNS Records

Choose ONE option based on what type of domain you want to use:

---

**Option A: Using Root Domain (e.g., `mybusiness.com`) - RECOMMENDED**

You need BOTH records:

1. Add an **A Record** (REQUIRED):
   - **Type:** A
   - **Name:** @ (or leave blank)
   - **Value/Points to:** `138.197.187.134`
   - **TTL:** 3600 (or Auto)

2. Add a **CNAME Record** for www (OPTIONAL but recommended):
   - **Type:** CNAME
   - **Name:** www
   - **Value/Points to:** `mybusiness.com` (your root domain)
   - **TTL:** 3600 (or Auto)

**Result:** Both `mybusiness.com` and `www.mybusiness.com` will work

---

**Option B: Using Subdomain (e.g., `shop.mybusiness.com`)**

You only need ONE record:

Add a **CNAME Record**:
- **Type:** CNAME
- **Name:** shop (or your chosen subdomain)
- **Value/Points to:** `yoursitename.mygrownet.com`
- **TTL:** 3600 (or Auto)

**Result:** Only `shop.mybusiness.com` will work (not the root domain)

**Note:** This option is simpler but less common. Most users prefer Option A.

#### Step 3: Wait for DNS Propagation
- DNS changes can take 5 minutes to 48 hours to propagate
- Usually takes 15-30 minutes
- Check propagation status: https://dnschecker.org

#### Step 4: Add Domain in GrowBuilder
1. Log in to your MyGrowNet account
2. Go to **GrowBuilder** → **My Sites**
3. Click on your site
4. Go to **Settings** tab
5. Scroll to **Custom Domain** section
6. Enter your domain (e.g., `mybusiness.com`)
7. Click **Save Changes**

#### Step 5: Verify & Publish
1. System will verify DNS configuration
2. SSL certificate will be automatically generated (takes 2-5 minutes)
3. Your site will be accessible via your custom domain
4. Both `mybusiness.com` and `www.mybusiness.com` will work

### Common DNS Provider Instructions

#### Namecheap
1. Log in to Namecheap
2. Go to **Domain List** → Click **Manage** next to your domain
3. Go to **Advanced DNS** tab
4. Click **Add New Record**
5. Add the A and CNAME records as shown above
6. Click **Save All Changes**

#### GoDaddy
1. Log in to GoDaddy
2. Go to **My Products** → **Domains**
3. Click **DNS** next to your domain
4. Click **Add** to add new records
5. Add the A and CNAME records as shown above
6. Click **Save**

#### Cloudflare
1. Log in to Cloudflare
2. Select your domain
3. Go to **DNS** tab
4. Click **Add record**
5. Add the A and CNAME records as shown above
6. **Important:** Set Proxy status to **DNS only** (gray cloud, not orange)
7. Click **Save**

## Troubleshooting

### Issue: "Domain not found" or "DNS not configured"
**Cause:** DNS records not set up correctly or not propagated yet

**Solution:**
1. Verify DNS records are correct using https://dnschecker.org
2. Wait longer for DNS propagation (up to 48 hours)
3. Check that you entered the correct IP address: `138.197.187.134`
4. Ensure there are no typos in your domain name

### Issue: "SSL Certificate Error" or "Not Secure"
**Cause:** SSL certificate not generated yet or DNS not fully propagated

**Solution:**
1. Wait 5-10 minutes after DNS propagation
2. SSL certificates are generated automatically
3. If issue persists after 30 minutes, contact support

### Issue: "Domain already in use"
**Cause:** Another user has already connected this domain

**Solution:**
1. Verify you own the domain
2. Check if you have another GrowBuilder site using this domain
3. Contact support if you believe this is an error

### Issue: Site shows "404 Not Found"
**Cause:** Site not published or DNS pointing to wrong location

**Solution:**
1. Ensure your site is **Published** (not draft)
2. Verify DNS records are correct
3. Clear your browser cache
4. Try accessing in incognito/private mode

## Technical Details (For Developers)

### Server Configuration
- **Web Server:** Nginx
- **SSL:** Let's Encrypt (automatic via Certbot)
- **IP Address:** 138.197.187.134
- **Wildcard Support:** Yes (*.mygrownet.com)

### DNS Records Required
```
# For root domain (example.com)
Type: A
Name: @
Value: 138.197.187.134
TTL: 3600

# For www subdomain
Type: CNAME
Name: www
Value: example.com
TTL: 3600
```

### SSL Certificate Generation
- Automatic via Let's Encrypt
- Triggered when custom domain is added
- Certificates auto-renew every 90 days
- Supports both root and www domains

### Domain Verification Process
1. User enters domain in settings
2. System checks DNS A record points to correct IP
3. System verifies domain is not already in use
4. Nginx virtual host is created
5. SSL certificate is requested from Let's Encrypt
6. Domain is activated

### Nginx Configuration
Each custom domain gets its own server block:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name example.com www.example.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name example.com www.example.com;
    
    ssl_certificate /etc/letsencrypt/live/example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/example.com/privkey.pem;
    
    root /var/www/mygrownet.com/public;
    index index.php;
    
    # Route to Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Database Schema
```sql
-- growbuilder_sites table
custom_domain VARCHAR(255) NULLABLE UNIQUE
```

### Middleware Detection
The `DetectSubdomain` middleware checks for custom domains:
```php
// Check if request is for a custom domain
$domain = $request->getHost();
$site = GrowBuilderSite::where('custom_domain', $domain)
    ->orWhere('custom_domain', 'www.' . $domain)
    ->where('status', 'published')
    ->first();
```

## Security Considerations

### SSL/TLS
- All custom domains automatically get SSL certificates
- HTTP requests are redirected to HTTPS
- TLS 1.2 and 1.3 supported
- Strong cipher suites enabled

### Domain Validation
- Domains must be unique (one domain per site)
- DNS verification before activation
- Reserved domains blocked (mygrownet.com, localhost, etc.)

### Rate Limiting
- SSL certificate requests limited to prevent abuse
- Let's Encrypt rate limits apply (50 certs per domain per week)

## Pricing

### Domain Registration (Not Included)
Users must purchase domains separately:
- **.com domains:** ~$10-15/year
- **.co.zm domains:** ~K200-300/year
- **.zm domains:** ~K150-250/year

### GrowBuilder Plans
- **Starter:** K120/month (includes 1 custom domain connection)
- **Business:** K350/month (includes 1 custom domain connection)
- **Agency:** K900/month (includes multiple custom domain connections)

**Note:** Domain registration fees are separate and paid to domain registrars.

## Support

### For Users
- Email: support@mygrownet.com
- In-app chat support
- Knowledge base: https://mygrownet.com/help

### For Developers
- Technical documentation: `/docs/growbuilder/`
- API reference: `/docs/api/`
- GitHub issues: (if applicable)

## Frequently Asked Questions

**Q: Do I need to buy a domain from MyGrowNet?**  
A: No, you can use any domain from any registrar. We don't sell domains.

**Q: Can I use a domain I already own?**  
A: Yes! Just point the DNS records to our server and add it in your site settings.

**Q: What's the difference between Option A and Option B?**  
A: 
- **Option A (Root Domain):** Your site is at `mybusiness.com` - looks more professional, requires A record
- **Option B (Subdomain):** Your site is at `shop.mybusiness.com` - easier setup, only needs CNAME

**Q: Can I use just the CNAME record for my root domain?**  
A: No, DNS specifications don't allow CNAME records for root domains. You must use an A record for root domains.

**Q: Do I need both the A record AND the CNAME for www?**  
A: The A record is required. The www CNAME is optional but recommended so both `mybusiness.com` and `www.mybusiness.com` work.

**Q: If I use Option B (subdomain), will my root domain work?**  
A: No. If you set up `shop.mybusiness.com`, only that subdomain will work. The root domain `mybusiness.com` won't point to your site unless you also add an A record.

**Q: How long does setup take?**  
A: DNS propagation: 15 minutes to 48 hours. SSL generation: 2-5 minutes after DNS propagates.

**Q: Can I use multiple domains for one site?**  
A: Currently, one custom domain per site. You can redirect additional domains using your DNS provider.

**Q: What happens to my subdomain?**  
A: Your subdomain (e.g., `mybusiness.mygrownet.com`) continues to work even after adding a custom domain.

**Q: Can I change my custom domain later?**  
A: Yes, you can update it in site settings. The old domain will stop working.

**Q: Do you support email hosting?**  
A: No, custom domains are for websites only. For email, use Google Workspace, Microsoft 365, or your domain registrar's email service.

**Q: What about .co.zm or .zm domains?**  
A: Yes! Any valid domain extension works, including Zambian domains.

## Real-World Setup Example: flamesofhopechurch.com

### Issue Encountered
When setting up flamesofhopechurch.com (purchased on Cloudflare), the domain was initially configured with Cloudflare proxy enabled (orange cloud). This caused the DNS to point to Cloudflare's IPs instead of MyGrowNet's server.

**DNS Check Result:**
```
nslookup flamesofhopechurch.com
Addresses: 104.21.20.78, 172.67.191.229 (Cloudflare IPs)
Expected: 138.197.187.134 (MyGrowNet server)
```

### Solution
1. Log in to Cloudflare dashboard
2. Go to DNS settings for flamesofhopechurch.com
3. Find the A record pointing to 138.197.187.134
4. Click the orange cloud icon to change it to gray cloud (DNS only)
5. Wait 5-10 minutes for DNS to propagate
6. Verify with: `nslookup flamesofhopechurch.com` (should show 138.197.187.134)
7. Then run the server setup script

**Critical:** Cloudflare proxy MUST be disabled for custom domains to work with GrowBuilder.

## Changelog

### January 30, 2026
- Created comprehensive custom domain setup guide
- Added step-by-step instructions for users
- Included DNS provider-specific guides
- Added troubleshooting section
- Documented technical implementation details
- Added real-world example with Cloudflare proxy issue
