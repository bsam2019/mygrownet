#!/bin/bash

# Fix Mobile PWA and Rendering Issues for GrowBuilder Sites
# This script deploys the dynamic manifest fix

set -e

echo "=========================================="
echo "Deploying Mobile PWA Fix"
echo "=========================================="

# Load credentials
if [ -f .deploy-credentials ]; then
    source .deploy-credentials
else
    echo "Error: .deploy-credentials file not found"
    exit 1
fi

echo "Connecting to server: $DROPLET_USER@$DROPLET_IP"

# Deploy via SSH
ssh $DROPLET_USER@$DROPLET_IP << ENDSSH
    set -e
    
    echo "Navigating to project directory..."
    cd /var/www/mygrownet.com
    
    echo "Pulling latest changes..."
    git pull origin main
    
    echo "Clearing Laravel caches..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    php artisan cache:clear
    
    echo "Optimizing for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    echo "Restarting PHP-FPM..."
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl restart php-fpm || echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl restart php8.2-fpm || echo "PHP-FPM restart skipped"
    
    echo "Reloading Nginx..."
    echo '$DROPLET_SUDO_PASSWORD' | sudo -S systemctl reload nginx
    
    echo "Deployment complete!"
ENDSSH

echo ""
echo "=========================================="
echo "Deployment Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Test dynamic manifest:"
echo "   https://chisambofarms.mygrownet.com/sites/chisambofarms/manifest.json"
echo ""
echo "2. Clear mobile browser cache completely"
echo ""
echo "3. Test site on mobile device:"
echo "   https://chisambofarms.mygrownet.com"
echo ""
echo "4. Check for errors:"
echo "   ssh $DROPLET_USER@$DROPLET_IP 'tail -f /var/www/mygrownet.com/storage/logs/laravel.log'"
echo ""
echo "5. If page is still blank, enable remote debugging:"
echo "   - Chrome: chrome://inspect"
echo "   - Safari: Settings > Safari > Advanced > Web Inspector"
echo ""
