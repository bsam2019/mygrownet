#!/bin/bash

# Production Login Fix Script
# Run this on the production server after deploying

set -e

echo "================================"
echo "MyGrowNet Production Login Fix"
echo "================================"
echo ""

# Navigate to project directory
cd /var/www/mygrownet.com

echo "✓ Pulling latest code..."
git pull origin main

echo ""
echo "✓ Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

echo ""
echo "✓ Rebuilding optimized caches..."
php artisan config:cache
php artisan route:cache
php artisan event:cache

echo ""
echo "✓ Restarting queue workers..."
php artisan queue:restart

echo ""
echo "✓ Verifying login routes..."
php artisan route:list --name=login --columns=method,uri,name,action

echo ""
echo "================================"
echo "✅ Fix complete!"
echo "================================"
echo ""
echo "Test the login modal at: https://mygrownet.com"
echo ""
echo "If still having issues:"
echo "1. Check logs: tail -f storage/logs/laravel.log"
echo "2. See: docs/LOGIN_MODAL_TROUBLESHOOTING.md"
echo ""
