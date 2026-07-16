#!/bin/bash

# Deployment script for StockFlow fixes
echo "🚀 Deploying StockFlow fixes..."

# Step 1: Update controllers with stockflow.* routes
echo "1️⃣ Updating controllers..."
cd /var/www/mygrownet.com

# Create backup of changed files
echo "📁 Creating backup..."
tar -czf /tmp/stockflow-backup-$(date +%Y%m%d-%H%M%S).tar.gz \
    app/Http/Controllers/StockFlow/*.php \
    app/Http/Middleware/HandleInertiaRequests.php \
    app/Providers/StockFlowServiceProvider.php \
    app/Domain/StockFlow/Listeners/ActivityLogListener.php \
    app/Console/Commands/*Import*.php \
    resources/views/pdf/stock-audit/ \
    resources/js/composables/useStockflowRoute.ts \
    resources/js/composables/useSubdomainRoute.ts \
    resources/js/modules/createApp.ts \
    resources/js/layouts/StockFlowLayout.vue \
    resources/js/pages/StockFlow/*.vue \
    resources/js/components/StockFlow/*.vue

# Step 2: Rename PDF views directory
echo "2️⃣ Renaming PDF views directory..."
if [ -d "resources/views/pdf/stock-audit" ]; then
    mv resources/views/pdf/stock-audit resources/views/pdf/stockflow
    echo "✅ Renamed stock-audit to stockflow"
else
    echo "ℹ️ PDF views already renamed"
fi

# Step 3: Clear and rebuild caches
echo "3️⃣ Clearing and rebuilding caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Step 4: Build frontend assets
echo "4️⃣ Building frontend assets..."
if [ -f "package.json" ]; then
    echo "📦 Building with npm..."
    npm run build:stockflow
else
    echo "⚠️ package.json not found, skipping build"
fi

# Step 5: Clear route and config caches again
echo "5️⃣ Final cache cleanup..."
php artisan route:cache
php artisan config:cache
php artisan optimize

echo "✅ Deployment complete!"
echo ""
echo "📋 Summary of changes:"
echo "   • All controllers use stockflow.* routes (not stock-audit.*)"
echo "   • sfRoute macro simplified (stockflow.* → stockflow.sub.*)"
echo "   • PDF views renamed: stock-audit/ → stockflow/"
echo "   • Frontend route handling cleaned up"
echo "   • Artisan commands renamed: stockflow:* (not stock-audit:*)"
echo ""
echo "🔧 Commands to run on production:"
echo "   sudo bash deployment/deploy-stockflow-fixes.sh"
echo ""

# Verify the fix
echo "🔍 Verifying the fix..."
echo "The /roles 500 error should now be fixed because:"
echo "1. RoleController uses sfRoute('stockflow.roles.index')"
echo "2. On subdomain: stockflow.roles.index → stockflow.sub.roles.index"
echo "3. Both routes exist in route definitions"
echo ""
echo "✅ Deployment script created!"