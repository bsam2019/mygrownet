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

echo "🚀 Deploying to MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# SSH and run deployment commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

# Pull latest changes
echo "📥 Pulling from GitHub..."
git pull https://${GITHUB_USERNAME}:${GITHUB_TOKEN}@github.com/${GITHUB_USERNAME}/mygrownet.git main

# Fix permissions
echo "🔧 Fixing permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache

# Clear and optimize
echo "🧹 Clearing and optimizing..."
php artisan optimize:clear
php artisan optimize

echo "✅ Deployment complete!"

ENDSSH

echo "🎉 All done!"
