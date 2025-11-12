#!/bin/bash

# Force deployment with stash
# This script stashes local changes, pulls, and attempts to reapply them

SERVER_USER="sammy"
SERVER_IP="138.197.187.134"
APP_DIR="/var/www/html/mygrownet"

echo "🚀 Force deploying to MyGrowNet droplet..."
echo "📍 Server: $SERVER_IP"

# SSH and execute commands
ssh $SERVER_USER@$SERVER_IP << 'ENDSSH'
cd /var/www/html/mygrownet

echo "📥 Stashing local changes..."
git stash

echo "📥 Pulling from GitHub..."
git pull origin main

echo "🔄 Running migrations..."
php artisan migrate --force

echo "🧹 Clearing caches..."
php artisan optimize:clear

echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "🚀 Optimizing..."
php artisan optimize

echo "🔒 Restoring secure permissions..."
sudo chmod -R 755 storage bootstrap/cache

echo "✅ Deployment complete!"

ENDSSH

echo "🎉 All done!"
