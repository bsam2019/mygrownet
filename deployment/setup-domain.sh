#!/bin/bash

# MyGrowNet Domain Setup Script
# This script configures the server to use mygrownet.com

set -e

SERVER_IP="138.197.187.134"
SERVER_USER="sammy"
NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.bsamtech.com"
APP_PATH="/var/www/mygrownet.com"

echo "🌐 Setting up mygrownet.com domain..."
echo "📍 Server: $SERVER_IP"
echo ""

# Connect to server and update configuration
ssh -t $SERVER_USER@$SERVER_IP << 'ENDSSH'

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.bsamtech.com"
APP_PATH="/var/www/mygrownet.com"

echo "📝 Updating .env file..."
cd $APP_PATH
sudo sed -i "s|APP_URL=.*|APP_URL=https://$NEW_DOMAIN|g" .env
sudo sed -i "s|ASSET_URL=.*|ASSET_URL=https://$NEW_DOMAIN|g" .env

echo "🔧 Creating new Nginx configuration..."
sudo tee /etc/nginx/sites-available/$NEW_DOMAIN > /dev/null << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name mygrownet.com www.mygrownet.com;
    
    root /var/www/mygrownet.com/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Logging
    access_log /var/log/nginx/mygrownet-access.log;
    error_log /var/log/nginx/mygrownet-error.log;

    # Increase upload size
    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Increase timeouts
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

echo "🔗 Enabling new site configuration..."
sudo ln -sf /etc/nginx/sites-available/$NEW_DOMAIN /etc/nginx/sites-enabled/$NEW_DOMAIN

echo "🧪 Testing Nginx configuration..."
sudo nginx -t

echo "🔄 Reloading Nginx..."
sudo systemctl reload nginx

echo "🔒 Setting up SSL certificate with Certbot..."
sudo certbot --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com --redirect

echo "🧹 Clearing Laravel caches..."
cd $APP_PATH
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🚀 Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data $APP_PATH/storage
sudo chown -R www-data:www-data $APP_PATH/bootstrap/cache
sudo chmod -R 775 $APP_PATH/storage
sudo chmod -R 775 $APP_PATH/bootstrap/cache

echo ""
echo "✅ Domain setup complete!"
echo "🌐 Your site is now available at:"
echo "   https://mygrownet.com"
echo "   https://www.mygrownet.com"
echo ""
echo "📋 Next steps:"
echo "   1. Test the site: https://mygrownet.com"
echo "   2. Update any hardcoded URLs in your database"
echo "   3. Update DNS records if not done already"
echo ""

ENDSSH

echo "🎉 All done!"
echo ""
echo "🔍 Verify your site:"
echo "   https://mygrownet.com"
echo "   https://www.mygrownet.com"
