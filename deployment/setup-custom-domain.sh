#!/bin/bash

# Setup Custom Domain for GrowBuilder
# Usage: ./setup-custom-domain.sh flamesofhopechurch.com flamesofhope [sudo_password]

DOMAIN=$1
SUBDOMAIN=$2
SUDO_PASSWORD=$3

if [ -z "$DOMAIN" ] || [ -z "$SUBDOMAIN" ]; then
    echo "Usage: ./setup-custom-domain.sh <domain> <subdomain> [sudo_password]"
    echo "Example: ./setup-custom-domain.sh flamesofhopechurch.com flamesofhope mypassword"
    exit 1
fi

echo "🚀 Setting up custom domain: $DOMAIN for subdomain: $SUBDOMAIN"
echo ""

# Step 1: Update database
echo "📝 Step 1: Updating database..."
cd /var/www/mygrownet.com
php artisan tinker --execute="
\$site = App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::where('subdomain', '$SUBDOMAIN')->first();
if (\$site) {
    echo 'Found site: ' . \$site->name . PHP_EOL;
    \$site->custom_domain = '$DOMAIN';
    \$site->save();
    echo '✅ Custom domain set to: $DOMAIN' . PHP_EOL;
} else {
    echo '❌ Site not found with subdomain: $SUBDOMAIN' . PHP_EOL;
    exit(1);
}
"

if [ $? -ne 0 ]; then
    echo "❌ Failed to update database"
    exit 1
fi

# Step 2: Create nginx config
echo ""
echo "🔧 Step 2: Creating nginx configuration..."
echo "$SUDO_PASSWORD" | sudo -S tee /etc/nginx/sites-available/$DOMAIN > /dev/null <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;
    
    root /var/www/mygrownet.com/public;
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/$DOMAIN-access.log;
    error_log /var/log/nginx/$DOMAIN-error.log;
    
    # Laravel routing
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param HTTP_HOST \$host;
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
    
    # Static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

echo "✅ Nginx config created"

# Step 3: Enable site
echo ""
echo "🔗 Step 3: Enabling site..."
echo "$SUDO_PASSWORD" | sudo -S ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
echo "✅ Site enabled"

# Step 4: Test nginx config
echo ""
echo "🧪 Step 4: Testing nginx configuration..."
echo "$SUDO_PASSWORD" | sudo -S nginx -t
if [ $? -ne 0 ]; then
    echo "❌ Nginx configuration test failed"
    exit 1
fi
echo "✅ Nginx config is valid"

# Step 5: Reload nginx
echo ""
echo "🔄 Step 5: Reloading nginx..."
echo "$SUDO_PASSWORD" | sudo -S systemctl reload nginx
echo "✅ Nginx reloaded"

# Step 6: Test HTTP
echo ""
echo "🌐 Step 6: Testing HTTP connection..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://$DOMAIN)
echo "HTTP response code: $HTTP_CODE"

# Step 7: Setup SSL
echo ""
echo "🔒 Step 7: Setting up SSL certificate..."
echo "This may take a few minutes..."
echo "$SUDO_PASSWORD" | sudo -S certbot --nginx -d $DOMAIN -d www.$DOMAIN --non-interactive --agree-tos --email support@mygrownet.com --redirect

if [ $? -eq 0 ]; then
    echo "✅ SSL certificate installed successfully"
else
    echo "⚠️  SSL certificate installation failed. You may need to:"
    echo "   1. Wait for DNS to fully propagate"
    echo "   2. Run manually: sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi

# Final test
echo ""
echo "🎉 Setup complete!"
echo ""
echo "Test your site:"
echo "  http://$DOMAIN"
echo "  https://$DOMAIN"
echo "  https://www.$DOMAIN"
echo ""
echo "Check logs if issues:"
echo "  sudo tail -f /var/log/nginx/$DOMAIN-error.log"
