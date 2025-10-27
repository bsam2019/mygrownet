#!/bin/bash

# MyGrowNet Production Deployment with Asset Build
# This script deploys code and builds frontend assets

set -e

SERVER="sammy@138.197.187.134"
APP_DIR="/var/www/mygrownet"

echo "🚀 Deploying with asset build to MyGrowNet droplet..."
echo "📍 Server: 138.197.187.134"

ssh $SERVER << 'ENDSSH'
cd /var/www/mygrownet

echo "📥 Pulling from GitHub..."
git pull origin main

echo "📦 Installing Node dependencies..."
npm ci --production=false

echo "🔨 Building frontend assets..."
npm run build

echo "🔄 Running migrations..."
php artisan migrate --force

echo "🧹 Clearing caches..."
php artisan optimize:clear

echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache public/build
sudo chmod -R 775 storage bootstrap/cache

echo "🚀 Optimizing..."
php artisan optimize

echo "🔒 Restoring secure permissions..."
sudo chown -R www-data:www-data /var/www/mygrownet
sudo find /var/www/mygrownet -type f -exec chmod 644 {} \;
sudo find /var/www/mygrownet -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/mygrownet/storage
sudo chmod -R 775 /var/www/mygrownet/bootstrap/cache

echo "✅ Deployment complete!"
ENDSSH

echo "🎉 All done!"
