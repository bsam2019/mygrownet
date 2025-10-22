#!/bin/bash
# Fix SSL and complete migration

source .deploy-credentials

cat > /tmp/fix-ssl.sh << 'SCRIPT'
#!/bin/bash
set -e

SUDO_PASS="Bsam@2025!!"

echo "🔧 Fixing SSL and completing migration..."
echo ""

# 1. Restore old config temporarily
echo "📋 Restoring old config temporarily..."
echo "$SUDO_PASS" | sudo -S cp /etc/nginx/sites-available/mygrownet.com.backup /etc/nginx/sites-available/mygrownet.com
echo "$SUDO_PASS" | sudo -S nginx -t
echo "$SUDO_PASS" | sudo -S systemctl reload nginx
echo "✓ Old config restored"
echo ""

# 2. Get SSL certificates for new domain
echo "🔒 Obtaining SSL certificates for mygrownet.com..."
echo "$SUDO_PASS" | sudo -S certbot certonly --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com
echo "✓ SSL certificates obtained"
echo ""

# 3. Now update nginx config with new domain
echo "🔧 Updating nginx config for mygrownet.com..."
echo "$SUDO_PASS" | sudo -S tee /etc/nginx/sites-available/mygrownet.com > /dev/null << 'EOF'
# HTTP to HTTPS redirect
server {
    listen 80;
    server_name mygrownet.com www.mygrownet.com 138.197.187.134;
    return 301 https://mygrownet.com\$request_uri;
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
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
        fastcgi_send_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF
echo "✓ Nginx config updated"
echo ""

# 4. Test and reload
echo "🧪 Testing nginx..."
echo "$SUDO_PASS" | sudo -S nginx -t
echo ""

echo "🔄 Reloading nginx..."
echo "$SUDO_PASS" | sudo -S systemctl reload nginx
echo "✓ Nginx reloaded"
echo ""

# 5. Update .env
echo "📝 Updating .env..."
cd /var/www/mygrownet.com
echo "$SUDO_PASS" | sudo -S sed -i "s|APP_URL=https://mygrownet.edulinkzm.com|APP_URL=https://mygrownet.com|g" .env
if grep -q "ASSET_URL" .env; then
    echo "$SUDO_PASS" | sudo -S sed -i "s|ASSET_URL=.*|ASSET_URL=https://mygrownet.com|g" .env
else
    echo "ASSET_URL=https://mygrownet.com" | echo "$SUDO_PASS" | sudo -S tee -a .env > /dev/null
fi
echo "Current URLs:"
grep -E "APP_URL|ASSET_URL" .env
echo "✓ .env updated"
echo ""

# 6. Clear and optimize
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Caches optimized"
echo ""

# 7. Fix permissions
echo "🔧 Fixing permissions..."
echo "$SUDO_PASS" | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo "$SUDO_PASS" | sudo -S chmod -R 775 storage bootstrap/cache
echo "✓ Permissions fixed"
echo ""

echo "✅ Migration complete!"
echo "🌐 Your site is now at: https://mygrownet.com"
SCRIPT

scp /tmp/fix-ssl.sh $DROPLET_USER@$DROPLET_IP:/tmp/fix-ssl.sh
ssh $DROPLET_USER@$DROPLET_IP "chmod +x /tmp/fix-ssl.sh && /tmp/fix-ssl.sh && rm /tmp/fix-ssl.sh"

echo ""
echo "🎉 Testing the site..."
curl -I https://mygrownet.com 2>/dev/null | head -10
