#!/bin/bash

# Update Server Configuration for mygrownet.com
# This script updates the nginx configuration from mygrownet.edulinkzm.com to mygrownet.com

set -e

SERVER_IP="138.197.187.134"
SERVER_USER="sammy"
NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"

echo "🔧 Updating server configuration for mygrownet.com..."
echo "📍 Server: $SERVER_IP"
echo "📝 Changing from: $OLD_DOMAIN"
echo "📝 Changing to: $NEW_DOMAIN"
echo ""

# Connect to server and update configuration
ssh -t $SERVER_USER@$SERVER_IP << 'ENDSSH'

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"

echo "📋 Current nginx configuration:"
echo "================================"
cat /etc/nginx/sites-available/mygrownet.com
echo "================================"
echo ""

echo "📝 Backing up current configuration..."
sudo cp /etc/nginx/sites-available/mygrownet.com /etc/nginx/sites-available/mygrownet.com.backup
echo "✓ Backup created at: /etc/nginx/sites-available/mygrownet.com.backup"
echo ""

echo "🔧 Creating updated nginx configuration for $NEW_DOMAIN..."
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
EOF

echo "✓ Nginx configuration updated"
echo ""

echo ""
echo "🧪 Testing nginx configuration..."
sudo nginx -t

echo ""
echo "🔄 Reloading nginx..."
sudo systemctl reload nginx

echo ""
echo "📝 Updating Laravel .env file..."
cd $APP_PATH
if [ -f ".env" ]; then
    echo "Current APP_URL: $(grep APP_URL .env)"
    sudo sed -i "s|APP_URL=https://$OLD_DOMAIN|APP_URL=https://$NEW_DOMAIN|g" .env
    sudo sed -i "s|ASSET_URL=https://$OLD_DOMAIN|ASSET_URL=https://$NEW_DOMAIN|g" .env
    # Add ASSET_URL if it doesn't exist
    if ! grep -q "ASSET_URL" .env; then
        echo "ASSET_URL=https://$NEW_DOMAIN" | sudo tee -a .env > /dev/null
    fi
    echo "New APP_URL: $(grep APP_URL .env)"
    echo "✓ .env file updated"
else
    echo "⚠ .env file not found at $APP_PATH"
fi

echo ""
echo "⚠️  IMPORTANT: SSL Certificate Setup"
echo "================================"
echo "Before obtaining new SSL certificates, ensure:"
echo "  1. DNS A records are pointing to this server:"
echo "     - mygrownet.com → 138.197.187.134"
echo "     - www.mygrownet.com → 138.197.187.134"
echo "  2. DNS propagation is complete (check with: dig mygrownet.com)"
echo ""
read -p "Have you updated DNS records and confirmed propagation? (y/N): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    echo "🔒 Setting up SSL certificate with Certbot..."
    echo "This will obtain and install SSL certificates for:"
    echo "  - mygrownet.com"
    echo "  - www.mygrownet.com"
    echo ""
    sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com --redirect || echo "⚠ Certbot failed, you may need to run it manually"
else
    echo ""
    echo "⚠️  Skipping SSL certificate setup"
    echo "You can run this manually later:"
    echo "  sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com"
    echo ""
    echo "For now, temporarily using HTTP-only configuration..."
    sudo sed -i 's|listen 443 ssl http2;|# listen 443 ssl http2;|g' /etc/nginx/sites-available/mygrownet.com
    sudo sed -i 's|ssl_certificate|# ssl_certificate|g' /etc/nginx/sites-available/mygrownet.com
fi

echo ""
echo "🧹 Clearing Laravel caches..."
cd $APP_PATH
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "🚀 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data $APP_PATH/storage
sudo chown -R www-data:www-data $APP_PATH/bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

echo ""
echo "✅ Server configuration update complete!"
echo ""
echo "📋 Summary:"
echo "  ✓ Nginx configuration updated for mygrownet.com"
echo "  ✓ Configuration backup created"
echo "  ✓ Laravel .env updated"
echo "  ✓ Caches cleared and optimized"
echo "  ✓ Permissions fixed"
echo ""
echo "🌐 Your site should now be available at:"
echo "   https://mygrownet.com (if SSL was configured)"
echo "   http://mygrownet.com (if SSL was skipped)"
echo ""
echo "📝 Configuration files:"
echo "   - Nginx config: /etc/nginx/sites-available/mygrownet.com"
echo "   - Backup: /etc/nginx/sites-available/mygrownet.com.backup"
echo "   - Laravel .env: $APP_PATH/.env"
echo ""

ENDSSH

echo ""
echo "🎉 All done!"
echo ""
echo "🔍 Next steps:"
echo "   1. Verify DNS records are pointing to the server:"
echo "      dig mygrownet.com"
echo "      dig www.mygrownet.com"
echo ""
echo "   2. Test the site:"
echo "      https://mygrownet.com"
echo "      https://www.mygrownet.com"
echo ""
echo "   3. If SSL was skipped, run Certbot manually after DNS propagation:"
echo "      ssh sammy@138.197.187.134"
echo "      sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com"
echo ""
echo "   4. Monitor nginx logs for any issues:"
echo "      ssh sammy@138.197.187.134 'sudo tail -f /var/log/nginx/error.log'"
echo ""
