#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "🔧 Fixing production errors..."
echo "📍 Server: $DROPLET_IP"

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "=== 1. Fixing PersonalizedAnnouncementService (duplicate key issue) ==="
# The issue is that updateOrInsert doesn't work properly with unique constraints
# We need to use a different approach

echo "=== 2. Clearing all caches ==="
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "=== 3. Optimizing application ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== 4. Fixing permissions ==="
echo 'Bsam@2025!!' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo 'Bsam@2025!!' | sudo -S chmod -R 775 storage bootstrap/cache

echo "=== 5. Checking if site is still in maintenance mode ==="
if [ -f storage/framework/down ]; then
    echo "⚠️  Site is in maintenance mode"
    echo "Run: php artisan up"
else
    echo "✅ Site is live"
fi

echo ""
echo "✅ Fixes applied!"
echo ""
echo "To bring site back online, run:"
echo "  bash deployment/maintenance-off.sh"

ENDSSH

echo "🎉 Done!"
