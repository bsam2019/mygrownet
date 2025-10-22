#!/bin/bash

# Complete Domain Setup with Password Authentication
# This script completes the domain migration with proper sudo authentication

set -e

# Load credentials
source .deploy-credentials

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"

echo "🔧 Completing domain setup for mygrownet.com..."
echo "📍 Server: $DROPLET_IP"
echo ""

# Execute commands with password
sshpass -p "$DROPLET_SUDO_PASSWORD" ssh -t $DROPLET_USER@$DROPLET_IP << ENDSSH
set -e

NEW_DOMAIN="mygrownet.com"
OLD_DOMAIN="mygrownet.edulinkzm.com"
APP_PATH="/var/www/mygrownet.com"

echo "🔧 Completing nginx and SSL setup..."
echo ""

# Test nginx configuration
echo "🧪 Testing nginx configuration..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S nginx -t

# Reload nginx
echo "🔄 Reloading nginx..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S systemctl reload nginx

# Update .env file
echo "📝 Updating .env file..."
cd \$APP_PATH
echo "$DROPLET_SUDO_PASSWORD" | sudo -S sed -i "s|APP_URL=https://\$OLD_DOMAIN|APP_URL=https://\$NEW_DOMAIN|g" .env

# Add ASSET_URL if missing
if ! grep -q "ASSET_URL" .env; then
    echo "ASSET_URL=https://\$NEW_DOMAIN" | echo "$DROPLET_SUDO_PASSWORD" | sudo -S tee -a .env > /dev/null
fi

echo "Current APP_URL: \$(grep APP_URL .env)"

# Fix permissions
echo "🔧 Fixing permissions..."
echo "$DROPLET_SUDO_PASSWORD" | sudo -S chown -R www-data:www-data \$APP_PATH/storage
echo "$DROPLET_SUDO_PASSWORD" | sudo -S chown -R www-data:www-data \$APP_PATH/bootstrap/cache
echo "$DROPLET_SUDO_PASSWORD" | sudo -S chmod -R 775 \$APP_PATH/storage
echo "$DROPLET_SUDO_PASSWORD" | sudo -S chmod -R 775 \$APP_PATH/bootstrap/cache

echo ""
echo "🔒 Setting up SSL certificates..."
echo "This will obtain SSL certificates for mygrownet.com and www.mygrownet.com"
echo ""

# Setup SSL with Certbot
echo "$DROPLET_SUDO_PASSWORD" | sudo -S certbot --nginx -d mygrownet.com -d www.mygrownet.com --non-interactive --agree-tos --email admin@mygrownet.com --redirect

echo ""
echo "✅ Domain setup complete!"
echo ""
echo "🌐 Your site is now available at:"
echo "   https://mygrownet.com"
echo "   https://www.mygrownet.com"
echo ""

ENDSSH

echo ""
echo "🎉 Migration complete!"
echo ""
echo "🔍 Verify the setup:"
echo "   curl -I https://mygrownet.com"
echo "   curl -I https://www.mygrownet.com"
echo ""
