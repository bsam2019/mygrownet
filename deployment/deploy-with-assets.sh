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

# Ensure manifest is in correct location before upload
echo "📦 Ensuring Vite manifest is in correct location..."
mkdir -p public/build/.vite
if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest copied to .vite directory"
fi

# Upload built assets using rsync with compression
# Note: Vite generates new hashed filenames each build, so most files will be "new"
# But rsync still helps with compression (-z) and is more reliable than scp
echo "📤 Uploading built assets to droplet..."
rsync -avz --progress --delete public/build/ ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/build/

# SSH and run deployment commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

# Verify manifest location on server
echo "📦 Verifying Vite manifest location..."
mkdir -p public/build/.vite
if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest verified in .vite directory"
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan optimize:clear

# Fix permissions
echo "🔧 Fixing permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache

# Optimize
echo "🚀 Optimizing..."
php artisan optimize

echo "✅ Deployment complete!"

ENDSSH

echo "🎉 Assets deployed successfully!"
