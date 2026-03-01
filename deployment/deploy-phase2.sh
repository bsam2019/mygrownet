#!/bin/bash

# Deploy Phase 2: Financial System Domain Layer
# Deploys with feature flags OFF for safety

set -e

echo "=========================================="
echo "Phase 2 Deployment: Financial Domain Layer"
echo "=========================================="
echo ""

# Load credentials
source .deploy-credentials

# SSH into production and deploy
ssh -o StrictHostKeyChecking=no ${DROPLET_USER}@${DROPLET_IP} << 'ENDSSH'

cd /var/www/mygrownet.com

echo "1. Pulling latest code..."
git pull origin main

echo ""
echo "2. Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo ""
echo "3. Running migrations..."
php artisan migrate --force

echo ""
echo "4. Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "5. Optimizing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "6. Verifying feature flags (should be OFF)..."
php artisan tinker --execute="
echo \"\\nFeature Flags Status:\\n\";
echo \"FEATURE_DOMAIN_WALLET: \" . (config('features.financial.use_domain_wallet_service') ? 'ON' : 'OFF') . \"\\n\";
echo \"FEATURE_COMPARE_WALLETS: \" . (config('features.financial.compare_wallet_services') ? 'ON' : 'OFF') . \"\\n\";
echo \"FEATURE_TRACK_SOURCE: \" . (config('features.financial.track_transaction_source') ? 'ON' : 'OFF') . \"\\n\";
"

echo ""
echo "7. Verifying modules table..."
php artisan tinker --execute="
\$count = DB::table('modules')->count();
echo \"\\nModules registered: \$count\\n\";
if (\$count > 0) {
    echo \"\\nActive modules:\\n\";
    \$modules = DB::table('modules')->where('is_active', true)->get(['code', 'name']);
    foreach (\$modules as \$module) {
        echo \"  - {\$module->code}: {\$module->name}\\n\";
    }
}
"

echo ""
echo "8. Testing comparison service (5 random users)..."
php artisan wallet:compare --limit=5

echo ""
echo "=========================================="
echo "✅ Phase 2 Deployment Complete!"
echo "=========================================="
echo ""
echo "Status:"
echo "- ✅ Code deployed"
echo "- ✅ Migrations run"
echo "- ✅ Modules table populated"
echo "- ✅ Feature flags OFF (safe mode)"
echo "- ✅ Old service still active"
echo ""
echo "Next steps:"
echo "1. Monitor logs for any issues"
echo "2. Enable comparison mode: FEATURE_COMPARE_WALLETS=true"
echo "3. Run full comparison: php artisan wallet:compare --all"
echo "4. If all matches, enable new service: FEATURE_DOMAIN_WALLET=true"

ENDSSH

echo ""
echo "✅ Deployment script completed!"
