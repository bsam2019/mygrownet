#!/bin/bash

# Phase 3 Deployment Script - Data Migration and Consolidation
# This script deploys Phase 3 changes to production

set -e  # Exit on error

echo "=========================================="
echo "Phase 3 Deployment - Data Migration"
echo "=========================================="
echo ""

# Configuration
REMOTE_USER="sammy"
REMOTE_HOST="138.197.187.134"
REMOTE_PATH="/var/www/mygrownet.com"
SSH_TARGET="${REMOTE_USER}@${REMOTE_HOST}"

echo "Target: ${SSH_TARGET}:${REMOTE_PATH}"
echo ""

# Step 1: Backup database
echo "Step 1: Creating database backup..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan backup:run --only-db" || {
    echo "❌ Database backup failed"
    exit 1
}
echo "✅ Database backup complete"
echo ""

# Step 2: Pull latest code
echo "Step 2: Pulling latest code from repository..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && git pull origin main" || {
    echo "❌ Git pull failed"
    exit 1
}
echo "✅ Code updated"
echo ""

# Step 3: Install dependencies
echo "Step 3: Installing dependencies..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && composer install --no-dev --optimize-autoloader" || {
    echo "❌ Composer install failed"
    exit 1
}
echo "✅ Dependencies installed"
echo ""

# Step 4: Clear caches
echo "Step 4: Clearing caches..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear" || {
    echo "❌ Cache clear failed"
    exit 1
}
echo "✅ Caches cleared"
echo ""

# Step 5: Test migration in dry-run mode
echo "Step 5: Testing migration in dry-run mode..."
echo "This will show what would be migrated without making changes"
echo ""
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan finance:migrate-payments --dry-run" || {
    echo "❌ Dry-run test failed"
    exit 1
}
echo ""
echo "✅ Dry-run test complete"
echo ""

# Step 6: Confirm before actual migration
echo "=========================================="
echo "⚠️  IMPORTANT: Review the dry-run results above"
echo "=========================================="
echo ""
read -p "Do you want to proceed with actual migration? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Deployment cancelled by user"
    exit 1
fi

echo ""
echo "Step 6: Running actual migration..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan finance:migrate-payments" || {
    echo "❌ Migration failed"
    echo "⚠️  Database backup is available for rollback"
    exit 1
}
echo "✅ Migration complete"
echo ""

# Step 7: Validate data integrity
echo "Step 7: Validating data integrity..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan wallet:compare --limit=20" || {
    echo "⚠️  Validation warnings detected - review output above"
}
echo "✅ Validation complete"
echo ""

# Step 8: Clear wallet caches
echo "Step 8: Clearing wallet caches..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan cache:forget 'wallet_*'" || {
    echo "⚠️  Cache clear warning - not critical"
}
echo "✅ Wallet caches cleared"
echo ""

# Step 9: Restart queue workers
echo "Step 9: Restarting queue workers..."
ssh ${SSH_TARGET} "cd ${REMOTE_PATH} && php artisan queue:restart" || {
    echo "⚠️  Queue restart warning - not critical"
}
echo "✅ Queue workers restarted"
echo ""

echo "=========================================="
echo "✅ Phase 3 Deployment Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Monitor application logs for errors"
echo "2. Check wallet balances for affected users"
echo "3. Run: php artisan wallet:compare --all (on production)"
echo "4. Monitor for 24-48 hours before Phase 4"
echo ""
echo "Rollback Instructions (if needed):"
echo "1. Restore database from backup"
echo "2. Git checkout previous commit"
echo "3. Clear caches"
echo "4. Restart services"
echo ""

