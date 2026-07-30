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

echo "🚀 Deploying assets to MyGrowNet droplet..."
echo "📍 Server: $DROPLET_IP"

# Ensure manifest is in correct location before upload
echo "📦 Ensuring Vite manifest is in correct location..."
mkdir -p public/build/.vite
if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest copied to .vite directory"
fi

# Upload built assets using scp
echo "📤 Uploading built assets to droplet..."
echo "⚠️  You may be prompted for the SSH password..."
# Create a tar archive for faster upload
cd public
tar -czf build.tar.gz build/
scp build.tar.gz ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/
rm build.tar.gz
cd ..

# SSH and run deployment commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

# Extract uploaded assets
echo "📦 Extracting assets..."
cd public
tar -xzf build.tar.gz
rm build.tar.gz
cd ..

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

# Fix config/modules.php permissions (needs to be writable by web server for module toggle)
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown www-data:www-data config/modules.php
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod 664 config/modules.php

# Optimize
echo "🚀 Optimizing..."
php artisan optimize

# Clean up old unused asset files (keep files referenced in current manifests)
echo "🧹 Cleaning up old unused asset files..."

# Function to clean assets for a module
cleanup_module_assets() {
    local module_dir=\$1
    if [ -d "public/build/\${module_dir}/assets" ]; then
        cd "public/build/\${module_dir}/assets"
        
        # Get list of files referenced in this module's manifest
        if [ -f "../manifest.json" ]; then
            MANIFEST_FILES=\$(grep -oP '"file":\\s*"assets/\\K[^"]+' ../manifest.json | sort | uniq)
            
            # Remove files NOT in manifest (older versions)
            for file in *; do
                if [ -f "\$file" ]; then
                    if ! echo "\$MANIFEST_FILES" | grep -q "^\$file\$"; then
                        # Only remove if file is older than 1 hour (safety check)
                        if [ \$(find "\$file" -mmin +60 2>/dev/null | wc -l) -gt 0 ]; then
                            echo "  [\${module_dir}] Removing old file: \$file"
                            rm "\$file"
                        fi
                    fi
                fi
            done
        fi
        cd ../../..
    fi
}

# Clean up main build assets
cleanup_module_assets ""

# Clean up module-specific assets
for module_dir in admin bizboost bizdocs bms employee growbuilder growfinance growmart grownet growstream lifephus marketplace primeedge stockflow venture zamstay; do
    cleanup_module_assets "\$module_dir"
done

echo "✅ Cleanup complete"

echo ""
echo "✅ Deployment complete!"
echo ""
echo "📊 Deployment verification:"
echo "  • Modules deployed: \$(ls -d public/build/*/ 2>/dev/null | wc -l) subdirectories"
echo "  • Main manifest: \$([ -f public/build/manifest.json ] && echo '✓' || echo '✗')"
echo "  • Main assets: \$(find public/build/assets -type f 2>/dev/null | wc -l) files"
echo ""

ENDSSH

echo "🎉 Assets deployed successfully!"
