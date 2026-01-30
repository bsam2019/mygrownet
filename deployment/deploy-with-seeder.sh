#!/bin/bash

# Get script directory and project root
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Load credentials from .deploy-credentials file
if [ -f "$PROJECT_ROOT/.deploy-credentials" ]; then
    source "$PROJECT_ROOT/.deploy-credentials"
else
    echo "❌ Error: .deploy-credentials file not found!"
    echo "Please create .deploy-credentials file in project root."
    exit 1
fi

echo "🚀 Running MarketplaceCategorySeeder on MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and run seeder
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

echo "🌱 Running MarketplaceCategorySeeder..."
php artisan db:seed --class=MarketplaceCategorySeeder --force

echo "✅ Seeder completed!"

ENDSSH

echo "🎉 All done!"
# Clear first
echo "🧹 Clearing caches..."
php artisan optimize:clear

# Fix permissions - set www-data as owner and sammy as group member
echo "🔧 Fixing permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S usermod -a -G www-data sammy

# Run seeder
echo "🌱 Running production seeder..."
php artisan db:seed --class=ProductionSeeder

# Set proper permissions for optimization
echo "🔧 Setting permissions for optimization..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 777 storage/logs bootstrap/cache

# Optimize
echo "🚀 Optimizing..."
php artisan optimize

# Restore proper permissions
echo "🔒 Restoring secure permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deployment complete!"

ENDSSH

echo "🎉 All done!"
