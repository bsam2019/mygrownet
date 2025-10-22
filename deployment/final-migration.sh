#!/bin/bash
# Final migration script - executes commands with proper sudo handling

# Load credentials
source .deploy-credentials

echo "🔧 Executing domain migration..."
echo ""

# Create a temporary script on the server
cat > /tmp/migrate.sh << 'SCRIPT'
#!/bin/bash
set -e

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"
SUDO_PASS="Bsam@2025!!"

echo "🔧 Starting domain migration..."
echo ""

# 1. Backup
echo "📋 Backing up nginx config..."
echo "$SUDO_PASS" | sudo -S cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup.$(date +%Y%m%d_%H%M%S)
echo "✓ Backup created"
echo ""

# 2. Update nginx config
echo "🔧 Updating nginx configuration..."
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

# 3. Test nginx
echo "🧪 Testing nginx..."
echo "$SUDO_PASS" | sudo -S nginx -t
echo ""

# 4. Reload nginx
echo "🔄 Reloading nginx..."
echo "$SUDO_PASS" | sudo -S systemctl reload nginx
echo "✓ Nginx reloaded"
echo ""

# 5. Update .env
echo "📝 Updating .env..."
cd $APP_PATH
echo "$SUDO_PASS" | sudo -S sed -i "s|APP_URL=https://$OLD_DOMAIN|APP_URL=https://$NEW_DOMAIN|g" .env
if grep -q "ASSET_URL" .env; then
    echo "$SUDO_PASS" | sudo -S sed -i "s|ASSET_URL=.*|ASSET_URL=https://$NEW_DOMAIN|g" .env
else
    echo "ASSET_URL=https://$NEW_DOMAIN" | echo "$SUDO_PASS" | sudo -S tee -a .env > /dev/null
fi
echo "✓ .env updated"
echo ""

# 6. Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo ""

# 7. Optimize
echo "🚀 Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo ""

# 8. Fix permissions
echo "🔧 Fixing permissions..."
echo "$SUDO_PASS" | sudo -S chown -R www-data:www-data $APP_PATH/storage $APP_PATH/bootstrap/cache
echo "$SUDO_PASS" | sudo -S chmod -R 775 $APP_PATH/storage $APP_PATH/bootstrap/cache
echo "✓ Permissions fixed"
echo ""

# 9. SSL
echo "🔒 Setting up SSL..."
DNS_CHECK=\$(dig +short mygrownet.com | head -n1)
if [ "\$DNS_CHECK" = "138.197.187.134" ]; then
    echo "✓ DNS propagated"
    echo "$SUDO_PASS" | sudo -S certbot --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com --redirect
    echo "✓ SSL configured"
else
    echo "⚠️  DNS not propagated (got: \$DNS_CHECK)"
    echo "Run manually: sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com"
fi
echo ""

echo "✅ Migration complete!"
echo "🌐 Site: https://mygrownet.com"
SCRIPT

# Copy and execute the script
scp /tmp/migrate.sh $DROPLET_USER@$DROPLET_IP:/tmp/migrate.sh
ssh $DROPLET_USER@$DROPLET_IP "chmod +x /tmp/migrate.sh && /tmp/migrate.sh && rm /tmp/migrate.sh"

echo ""
echo "🎉 Done! Testing the site..."
curl -I https://mygrownet.com 2>/dev/null | head -5
