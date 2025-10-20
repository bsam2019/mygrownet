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

echo "🚀 Building and deploying assets to MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# Build assets locally (from project root)
echo "🔨 Building assets locally..."
cd "$PROJECT_ROOT"
npm run build

# Check if build was successful
if [ $? -ne 0 ]; then
    echo "❌ Build failed! Aborting deployment."
    exit 1
fi

echo "📦 Assets built successfully!"

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

# Copy built assets to droplet
echo "📤 Uploading built assets to droplet..."
scp -r public/build ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/

echo "🎉 Assets deployed successfully!"
