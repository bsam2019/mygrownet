#!/bin/bash
# Copy and paste these commands directly into your SSH session
# SSH into server first: ssh sammy@138.197.187.134

# 1. Backup current config
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup

# 2. Create new nginx configuration
sudo tee /etc/nginx/sites-available/mygrownet.com > /dev/null << 'EOF'
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

    # SSL Configuration
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
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# 3. Test nginx configuration
sudo nginx -t

# 4. Reload nginx
sudo systemctl reload nginx

# 5. Update .env file
cd /var/www/mygrownet.com
sudo sed -i 's|APP_URL=https://mygrownet.edulinkzm.com|APP_URL=https://mygrownet.com|g' .env
sudo sed -i 's|ASSET_URL=https://mygrownet.edulinkzm.com|ASSET_URL=https://mygrownet.com|g' .env

# Add ASSET_URL if it doesn't exist
if ! grep -q "ASSET_URL" .env; then
    echo "ASSET_URL=https://mygrownet.com" | sudo tee -a .env
fi

# 6. Clear and optimize Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Setup SSL (make sure DNS is propagated first!)
echo "⚠️  Before running Certbot, verify DNS is propagated:"
echo "dig mygrownet.com"
echo "dig www.mygrownet.com"
echo ""
echo "If DNS shows 138.197.187.134, run:"
echo "sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com"
