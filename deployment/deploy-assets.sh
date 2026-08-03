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
echo ""

# Ensure manifest is in correct location before upload (if main module exists)
if [ -f "public/build/manifest.json" ]; then
    echo "📦 Ensuring Vite manifest is in correct location..."
    mkdir -p public/build/.vite
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest copied to .vite directory"
    echo ""
fi

# Upload built assets using rsync (incremental)
echo "📤 Uploading built assets to droplet..."
echo ""

# Detect which modules have been built locally
MODULES_TO_DEPLOY=()
if [ -d "public/build/assets" ] && [ -f "public/build/manifest.json" ]; then
    MODULES_TO_DEPLOY+=("main")
    echo "  ✓ main module detected"
fi

for module in admin bizboost bizdocs bms employee growbuilder growfinance growmart grownet growstream lifephus marketplace primeedge stockflow venture zamstay; do
    if [ -d "public/build/$module" ]; then
        MODULES_TO_DEPLOY+=("$module")
        echo "  ✓ $module module detected"
    fi
done

if [ ${#MODULES_TO_DEPLOY[@]} -eq 0 ]; then
    echo "❌ No built modules found in public/build/"
    echo "Please run 'npm run build' or 'npm run build:MODULE' first"
    exit 1
fi

echo ""
echo "📦 Deploying ${#MODULES_TO_DEPLOY[@]} module(s) incrementally..."
echo ""

# Use rsync for incremental deployment (only uploads changed files)
if command -v rsync &> /dev/null; then
    echo "🔄 Using rsync for efficient incremental upload..."
    for module in "${MODULES_TO_DEPLOY[@]}"; do
        if [ "$module" = "main" ]; then
            echo "  Syncing main module assets (incremental, no delete)..."
            rsync -avz public/build/assets/ ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/build/assets/
            rsync -avz public/build/manifest.json ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/build/
            if [ -d "public/build/.vite" ]; then
                rsync -avz public/build/.vite/ ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/build/.vite/
            fi
        else
            echo "  Syncing $module module..."
            rsync -avz --delete public/build/$module/ ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/build/$module/
        fi
    done
else
    echo "⚠️  rsync not found, using tar method (will preserve other modules)..."
    echo "⚠️  You may be prompted for the SSH password..."
    
    # Create tar with only the modules we're deploying
    cd public
    tar -czf build-partial.tar.gz \
        $([ -d "build/assets" ] && echo "build/assets") \
        $([ -f "build/manifest.json" ] && echo "build/manifest.json") \
        $([ -d "build/.vite" ] && echo "build/.vite") \
        $(for m in "${MODULES_TO_DEPLOY[@]}"; do [ "$m" != "main" ] && [ -d "build/$m" ] && echo "build/$m"; done)
    
    scp build-partial.tar.gz ${DROPLET_USER}@${DROPLET_IP}:${PROJECT_PATH}/public/
    rm build-partial.tar.gz
    cd ..
fi

# SSH and run deployment commands
ssh ${DROPLET_USER}@${DROPLET_IP} << ENDSSH

cd ${PROJECT_PATH}

# Extract uploaded assets (only if using tar method, rsync already synced)
if [ -f public/build-partial.tar.gz ]; then
    echo "📦 Extracting assets (incremental update)..."
    cd public
    tar -xzf build-partial.tar.gz
    rm build-partial.tar.gz
    cd ..
else
    echo "✓ Assets already synced via rsync"
fi

# Verify manifest location on server
echo "📦 Verifying Vite manifest location..."
mkdir -p public/build/.vite
if [ -f public/build/manifest.json ]; then
    cp public/build/manifest.json public/build/.vite/manifest.json
    echo "✅ Vite manifest verified in .vite directory"
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Fix permissions
echo "🔧 Fixing permissions..."
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown -R www-data:www-data storage bootstrap/cache
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod -R 775 storage bootstrap/cache

# Fix config/modules.php permissions (needs to be writable by web server for module toggle)
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chown www-data:www-data config/modules.php
echo '${DROPLET_SUDO_PASSWORD}' | sudo -S chmod 664 config/modules.php

# Rebuild caches
echo "🚀 Rebuilding caches..."
php artisan route:cache
php artisan config:cache
php artisan optimize

echo ""
echo "📊 Updating workspace catalog..."
php artisan db:seed --class=ApplicationRegistrySeeder --force
php artisan db:seed --class=WorkspaceDataSeeder --force
echo "✅ Workspace catalog updated"

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
echo "  • Modules in build dir: \$(ls -d public/build/*/ 2>/dev/null | wc -l) subdirectories"
echo "  • Modules deployed this run: ${#MODULES_TO_DEPLOY[@]} (${MODULES_TO_DEPLOY[@]})"
echo "  • Main manifest: \$([ -f public/build/manifest.json ] && echo '✓' || echo '✗')"
echo "  • Total asset files: \$(find public/build -name '*.js' -o -name '*.css' 2>/dev/null | wc -l)"
echo ""

ENDSSH

echo "🎉 Assets deployed successfully!"
