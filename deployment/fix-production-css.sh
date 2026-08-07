#!/bin/bash

# Load credentials
if [ -f ".deploy-credentials" ]; then
    source .deploy-credentials
else
    echo "❌ Error: .deploy-credentials file not found!"
    exit 1
fi

echo "🔍 Checking production CSS files..."
echo ""

ssh ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'
cd /var/www/mygrownet.com

echo "📊 Checking build directories..."
ls -lh public/build/ | grep "^d" | awk '{print $9}'

echo ""
echo "📊 Checking main CSS files..."
find public/build/assets -name "app-*.css" -type f | head -10

echo ""
echo "📊 Checking manifest.json..."
if [ -f "public/build/manifest.json" ]; then
    echo "✓ Main manifest exists"
    grep -c "app-" public/build/manifest.json
    echo "entries found in manifest"
else
    echo "✗ Main manifest missing!"
fi

echo ""
echo "📊 Checking .vite/manifest.json..."
if [ -f "public/build/.vite/manifest.json" ]; then
    echo "✓ Vite manifest exists"
else
    echo "✗ Vite manifest missing!"
fi

echo ""
echo "🧹 Clearing all caches..."
php artisan view:clear
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo ""
echo "🚀 Rebuilding caches..."
php artisan route:cache
php artisan config:cache
php artisan optimize

echo ""
echo "✅ Cache rebuild complete!"

echo ""
echo "📊 Final asset count:"
find public/build -name "*.css" | wc -l
echo "CSS files found"
find public/build -name "*.js" | wc -l
echo "JS files found"

ENDSSH

echo ""
echo "🎉 Production check complete!"
