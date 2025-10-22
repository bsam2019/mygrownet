# Manual Domain Setup Instructions

The automated script encountered sudo password issues. Follow these manual steps to complete the domain migration.

## Step 1: SSH into the Server

```bash
ssh sammy@138.197.187.134
```

Password: `Bsam@2025!!`

## Step 2: Backup Current Configuration

```bash
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup
```

## Step 3: Update Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/mygrownet.com
```

Replace the entire content with:

```nginx
# HTTP to HTTPS redirect
server {
    listen 80;
    server_name mygrownet.com www.mygrownet.com 138.197.187.134;
    return 301 https://mygrownet.com$request_uri;
}

# HTTPS server
server {
    listen 443 ssl http2;
    server_name mygrownet.com www.mygrownet.com;
    root /var/www/mygrownet.com/public;

    # SSL Configuration (will be updated by Certbot)
    ssl_certificate /etc/letsencrypt/live/mygrownet.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mygrownet.com/privkey.pem;

    # SSL Security Settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Increase upload size
    client_max_body_size 100M;

    index index.html index.htm index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Increase timeouts
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Save and exit (Ctrl+X, then Y, then Enter)

## Step 4: Test Nginx Configuration

```bash
sudo nginx -t
```

You should see: "syntax is okay" and "test is successful"

## Step 5: Reload Nginx

```bash
sudo systemctl reload nginx
```

## Step 6: Update Laravel .env File

```bash
cd /var/www/mygrownet.com
sudo nano .env
```

Find and update these lines:
```
APP_URL=https://mygrownet.com
ASSET_URL=https://mygrownet.com
```

Save and exit (Ctrl+X, then Y, then Enter)

## Step 7: Clear Laravel Caches

```bash
cd /var/www/mygrownet.com
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Step 8: Fix Permissions

```bash
sudo chown -R www-data:www-data /var/www/mygrownet.com/storage
sudo chown -R www-data:www-data /var/www/mygrownet.com/bootstrap/cache
sudo chmod -R 775 /var/www/mygrownet.com/storage
sudo chmod -R 775 /var/www/mygrownet.com/bootstrap/cache
```

## Step 9: Setup SSL Certificates

**IMPORTANT:** Make sure DNS is pointing to the server first!

Check DNS propagation:
```bash
dig mygrownet.com
dig www.mygrownet.com
```

Both should show: `138.197.187.134`

If DNS is propagated, run:
```bash
sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com
```

Follow the prompts:
- Enter email: `admin@mygrownet.com`
- Agree to terms: `Y`
- Share email: `N` (optional)
- Redirect HTTP to HTTPS: `2` (Yes)

## Step 10: Verify Setup

Test the site:
```bash
curl -I https://mygrownet.com
curl -I https://www.mygrownet.com
```

You should see `HTTP/2 200` responses.

## Step 11: Exit SSH

```bash
exit
```

## Verification Checklist

From your local machine, verify:

- [ ] https://mygrownet.com loads correctly
- [ ] https://www.mygrownet.com loads correctly
- [ ] HTTP redirects to HTTPS
- [ ] SSL certificate is valid (green padlock)
- [ ] Login works
- [ ] Dashboard loads
- [ ] Images and assets load

## Troubleshooting

### If SSL fails:
```bash
# Check DNS
dig mygrownet.com

# Check Certbot logs
sudo tail -f /var/log/letsencrypt/letsencrypt.log

# Try manual certificate
sudo certbot certonly --nginx -d mygrownet.com -d www.mygrownet.com
```

### If site doesn't load:
```bash
# Check nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM
sudo systemctl status php8.3-fpm
sudo systemctl restart php8.3-fpm

# Check nginx status
sudo systemctl status nginx
```

### If permissions error:
```bash
cd /var/www/mygrownet.com
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## Rollback (if needed)

If something goes wrong:
```bash
sudo cp /etc/nginx/sites-available/mygrownet.com.backup /etc/nginx/sites-available/mygrownet.com
sudo nginx -t
sudo systemctl reload nginx
```
