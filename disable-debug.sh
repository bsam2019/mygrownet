#!/bin/bash

# Disable debug mode on production server

SERVER_HOST="138.197.187.134"
SERVER_USER="sammy"

echo "🔒 Disabling debug mode on production..."

ssh "$SERVER_USER@$SERVER_HOST" bash << 'EOF'
SUDO_PASS="Bsam@2025!!"

cd /var/www/mygrownet.com

echo "🔧 Setting production environment..."
echo "$SUDO_PASS" | sudo -S -u www-data sed -i 's/APP_ENV=local/APP_ENV=production/' .env
echo "$SUDO_PASS" | sudo -S -u www-data sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env

echo ""
echo "📋 Current environment settings:"
grep "^APP_ENV=" .env
grep "^APP_DEBUG=" .env

echo ""
echo "🧹 Clearing and caching configuration..."
echo "$SUDO_PASS" | sudo -S -u www-data php artisan config:clear
echo "$SUDO_PASS" | sudo -S -u www-data php artisan config:cache
echo "$SUDO_PASS" | sudo -S -u www-data php artisan route:cache
echo "$SUDO_PASS" | sudo -S -u www-data php artisan view:cache

echo ""
echo "✅ Debug mode disabled - production mode active!"
echo "🌐 Site: https://mygrownet.edulinkzm.com"
EOF
