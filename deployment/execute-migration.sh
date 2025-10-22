#!/bin/bash
# Execute domain migration on server
# This script runs all commands in a single SSH session

set -e

# Load credentials
source .deploy-credentials

echo "🔧 Executing domain migration on server..."
echo "📍 Server: $DROPLET_IP"
echo ""

# Execute all commands in one SSH session
ssh -t $DROPLET_USER@$DROPLET_IP << 'ENDSSH'

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"

echo "🔧 Starting domain migration..."
echo "📝 From: $OLD_DOMAIN"
echo "📝 To: $NEW_DOMAIN"
echo ""

# 1. Backup current config
echo "📋 Step 1: Backing up current nginx configuration..."
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup
echo "✓ Backup created"
echo ""

# 2. Create new nginx configuration
echo "🔧 Step 2: Creating new nginx configuration..."
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
echo "✓ Nginx configuration created"
echo ""

# 3. Test nginx configuration
echo "🧪 Step 3: Testing nginx configuration..."
sudo nginx -t
echo "✓ Nginx configuration is valid"
echo ""

# 4. Reload nginx
echo "🔄 Step 4: Reloading nginx..."
sudo systemctl reload nginx
echo "✓ Nginx reloaded"
echo ""

# 5. Update .env file
echo "📝 Step 5: Updating Laravel .env file..."
cd $APP_PATH
echo "Current APP_URL: $(grep APP_URL .env)"
sudo sed -i "s|APP_URL=https://$OLD_DOMAIN|APP_URL=https://$NEW_DOMAIN|g" .env

# Add ASSET_URL if it doesn't exist, or update it
if grep -q "ASSET_URL" .env; then
    sudo sed -i "s|ASSET_URL=https://$OLD_DOMAIN|ASSET_URL=https://$NEW_DOMAIN|g" .env
else
    echo "ASSET_URL=https://$NEW_DOMAIN" | sudo tee -a .env > /dev/null
fi
echo "New APP_URL: $(grep APP_URL .env)"
echo "New ASSET_URL: $(grep ASSET_URL .env)"
echo "✓ .env file updated"
echo ""

# 6. Clear and optimize Laravel caches
echo "🧹 Step 6: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "✓ Caches cleared"
echo ""

echo "🚀 Step 7: Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Laravel optimized"
echo ""

# 7. Fix permissions
echo "🔧 Step 8: Fixing permissions..."
sudo chown -R www-data:www-data $APP_PATH/storage
sudo chown -R www-data:www-data $APP_PATH/bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache
echo "✓ Permissions fixed"
echo ""

# 8. Setup SSL
echo "🔒 Step 9: Setting up SSL certificates..."
echo "Checking DNS propagation..."
DNS_CHECK=$(dig +short mygrownet.com | head -n1)
echo "DNS resolves to: $DNS_CHECK"

if [ "$DNS_CHECK" = "138.197.187.134" ]; then
    echo "✓ DNS is propagated correctly"
    echo "Obtaining SSL certificates..."
    sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com --redirect
    echo "✓ SSL certificates installed"
else
    echo "⚠️  DNS not fully propagated yet (expected: 138.197.187.134, got: $DNS_CHECK)"
    echo "Skipping SSL setup for now."
    echo "You can run SSL setup manually later with:"
    echo "sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com"
fi
echo ""

echo "✅ Domain migration complete!"
echo ""
echo "📋 Summary:"
echo "  ✓ Nginx configuration updated"
echo "  ✓ Laravel .env updated"
echo "  ✓ Caches cleared and optimized"
echo "  ✓ Permissions fixed"
if [ "$DNS_CHECK" = "138.197.187.134" ]; then
    echo "  ✓ SSL certificates configured"
else
    echo "  ⚠ SSL certificates pending (DNS not propagated)"
fi
echo ""
echo "🌐 Your site should be available at:"
echo "   https://mygrownet.com"
echo "   https://www.mygrownet.com"
echo ""

ENDSSH

echo ""
echo "🎉 Migration script completed!"
echo ""
echo "🔍 Test the site:"
echo "   curl -I https://mygrownet.com"
echo "   curl -I https://www.mygrownet.com"
echo ""
