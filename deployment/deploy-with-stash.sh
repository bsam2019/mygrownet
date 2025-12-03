#!/bin/bash

# Load credentials
source .deploy-credentials

echo "🚀 Deploying with stash to MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and deploy
ssh -t $DROPLET_USER@$DROPLET_IP << 'ENDSSH'
cd /var/www/mygrownet.com

echo "💾 Stashing local changes..."
sudo git stash

echo "🗑️ Removing untracked file..."
sudo rm -f app/Console/Commands/ResetUserSession.php

echo "📥 Pulling from GitHub..."
sudo git pull origin main

echo "🔄 Running migrations..."
sudo php artisan migrate --force

echo "📦 Seeding modules..."
sudo php artisan db:seed --class=ModuleSeeder --force

echo "🧹 Clearing caches..."
sudo php artisan optimize:clear

echo "🔧 Fixing permissions..."
sudo chown -R www-data:www-data /var/www/mygrownet.com
sudo chmod -R 755 /var/www/mygrownet.com/storage
sudo chmod -R 755 /var/www/mygrownet.com/bootstrap/cache

echo "🚀 Optimizing..."
sudo php artisan optimize

echo "🔒 Restoring secure permissions..."
sudo chown -R www-data:www-data /var/www/mygrownet.com
sudo chmod -R 755 /var/www/mygrownet.com

echo "✅ Deployment complete!"
ENDSSH

echo "🎉 All done!"
