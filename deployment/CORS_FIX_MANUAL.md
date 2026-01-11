# CORS Fix for GrowBuilder Subdomains

## Problem
Subdomains like `ndelimas.mygrownet.com` cannot load assets from `mygrownet.com` due to CORS policy.

Error in console:
```
Access to script at 'https://mygrownet.com/build/assets/app-CUWImF-X.js' from origin 'https://ndelimas.mygrownet.com' has been blocked by CORS policy
```

## Solution
Add CORS headers to nginx configuration.

---

## EASIEST METHOD (Recommended)

### 1. SSH into server
```bash
ssh sammy@138.197.187.134
```
Password: `Bsam@2025!!`

### 2. Run the automated fix script
```bash
cd /var/www/mygrownet.com
sudo bash deployment/fix-subdomain-cors-nosudo.sh
```

### 3. Test
Visit: https://ndelimas.mygrownet.com

Done! ✅

---

## MANUAL METHOD (If script fails)

### 1. SSH into server
```bash
ssh sammy@138.197.187.134
```

### 2. Edit nginx configuration
```bash
sudo nano /etc/nginx/sites-available/mygrownet.com
```

### 3. Add CORS configuration
Find the `server` block and add this **BEFORE** the `location / {` block:

```nginx
# CORS for static assets (add this BEFORE location / block)
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;
    expires 1y;
    add_header Cache-Control "public, immutable";
    
    if ($request_method = OPTIONS) {
        return 204;
    }
}

# CORS for build assets
location /build/ {
    add_header Access-Control-Allow-Origin "*" always;
    add_header Access-Control-Allow-Methods "GET, OPTIONS" always;
    add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;
    expires 1y;
    add_header Cache-Control "public, immutable";
    
    if ($request_method = OPTIONS) {
        return 204;
    }
}
```

### 4. Test nginx configuration
```bash
sudo nginx -t
```

### 5. If test passes, reload nginx
```bash
sudo systemctl reload nginx
```

### 6. Test the subdomain
Visit: https://ndelimas.mygrownet.com

The CORS error should be gone!

## Quick One-Liner (Alternative)

If you prefer, run this single command:

```bash
sudo sed -i '/location \/ {/i\    # CORS for static assets\n    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {\n        add_header Access-Control-Allow-Origin "*" always;\n        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;\n        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;\n        expires 1y;\n        add_header Cache-Control "public, immutable";\n        if ($request_method = OPTIONS) {\n            return 204;\n        }\n    }\n\n    # CORS for build assets\n    location /build/ {\n        add_header Access-Control-Allow-Origin "*" always;\n        add_header Access-Control-Allow-Methods "GET, OPTIONS" always;\n        add_header Access-Control-Allow-Headers "Origin, X-Requested-With, Content-Type, Accept" always;\n        expires 1y;\n        add_header Cache-Control "public, immutable";\n        if ($request_method = OPTIONS) {\n            return 204;\n        }\n    }\n' /etc/nginx/sites-available/mygrownet.com && sudo nginx -t && sudo systemctl reload nginx
```

## Verification

After applying the fix, check the browser console. The CORS error should be gone and the subdomain should load properly.
