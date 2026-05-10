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

echo "🚀 Deploying with migrations to MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and run deployment commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

# Fix all file permissions for git operations
echo "🔧 Fixing file permissions for deployment..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R sammy:sammy .

# Reset local changes (server should match repo)
echo "🔄 Resetting local changes..."
git reset --hard HEAD
git clean -fd

# Pull latest changes
echo "📥 Pulling from GitHub..."
git pull https://${GITHUB_USERNAME}:${GITHUB_TOKEN}@github.com/${GITHUB_USERNAME}/mygrownet.git main

# Fix stuck migration if needed (bizboost_integrations table exists but migration not marked complete)
echo "🔧 Checking for stuck migrations..."
php artisan db:seed --class=FixStuckMigrationSeeder --force 2>/dev/null || true

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force

# Clear first
echo "🧹 Clearing caches..."
php artisan optimize:clear

# Fix permissions
echo "🔧 Fixing permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 777 storage/logs bootstrap/cache

# Fix config/modules.php permissions (needs to be writable by web server for module toggle)
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown www-data:www-data config/modules.php
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod 664 config/modules.php

# Optimize
echo "🚀 Optimizing..."
php artisan optimize

# Restore secure permissions
echo "🔒 Restoring secure permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache

echo "✅ Deployment complete!"

ENDSSH

echo "🎉 All done!"
