#!/bin/bash

# Esther Ziwa Account Fix - Production Deployment Script
# Usage: ./deployment/fix-esther-production.sh

set -e  # Exit on any error

echo "=== ESTHER ZIWA ACCOUNT FIX - PRODUCTION DEPLOYMENT ==="
echo "Started at: $(date)"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ ERROR: Not in Laravel root directory"
    echo "Please run this script from the Laravel project root"
    exit 1
fi

# Create logs directory if it doesn't exist
mkdir -p storage/logs/account-fixes

# Set log file with timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_DIR="storage/logs/account-fixes"
DIAGNOSIS_LOG="$LOG_DIR/esther_diagnosis_$TIMESTAMP.log"
FIX_LOG="$LOG_DIR/esther_fix_$TIMESTAMP.log"
VERIFICATION_LOG="$LOG_DIR/esther_verification_$TIMESTAMP.log"

echo "📁 Log files will be saved to:"
echo "   Diagnosis: $DIAGNOSIS_LOG"
echo "   Fix: $FIX_LOG"
echo "   Verification: $VERIFICATION_LOG"
echo ""

# Step 1: Create database backup
echo "1. CREATING DATABASE BACKUP"
echo "================================"

BACKUP_FILE="$LOG_DIR/esther_fix_backup_$TIMESTAMP.sql"

# Check if mysqldump is available
if command -v mysqldump >/dev/null 2>&1; then
    echo "Creating database backup..."
    
    # Get database config from Laravel
    DB_NAME=$(php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo config('database.connections.mysql.database');
    ")
    
    DB_USER=$(php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo config('database.connections.mysql.username');
    ")
    
    DB_PASS=$(php -r "
        require 'vendor/autoload.php';
        \$app = require_once 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        echo config('database.connections.mysql.password');
    ")
    
    if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ]; then
        mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"
        echo "✅ Database backup created: $BACKUP_FILE"
    else
        echo "⚠️  Could not create automatic backup - database config not found"
        echo "Please create manual backup before proceeding"
        read -p "Press Enter to continue or Ctrl+C to abort..."
    fi
else
    echo "⚠️  mysqldump not available - please create manual backup"
    read -p "Press Enter to continue or Ctrl+C to abort..."
fi

echo ""

# Step 2: Run diagnosis
echo "2. RUNNING DIAGNOSIS"
echo "===================="

if [ -f "diagnose-esther-account.php" ]; then
    echo "Running diagnostic script..."
    php diagnose-esther-account.php > "$DIAGNOSIS_LOG" 2>&1
    echo "✅ Diagnosis complete - check $DIAGNOSIS_LOG"
    
    # Show summary
    echo ""
    echo "Diagnosis Summary:"
    echo "------------------"
    tail -10 "$DIAGNOSIS_LOG"
else
    echo "⚠️  diagnose-esther-account.php not found - skipping diagnosis"
fi

echo ""

# Step 3: Confirm before proceeding
echo "3. CONFIRMATION"
echo "==============="

echo "⚠️  IMPORTANT: About to apply fixes to Esther Ziwa's account"
echo ""
echo "This will:"
echo "- Investigate her transaction history"
echo "- Fix negative balance issues"
echo "- Add corrective transactions if needed"
echo "- Clear application caches"
echo ""

read -p "Do you want to proceed with the fix? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "❌ Fix cancelled by user"
    exit 1
fi

echo ""

# Step 4: Apply fix
echo "4. APPLYING FIX"
echo "==============="

if [ -f "fix-esther-ziwa-negative-balance.php" ]; then
    echo "Running fix script..."
    php fix-esther-ziwa-negative-balance.php > "$FIX_LOG" 2>&1
    
    # Check if fix was successful
    if [ $? -eq 0 ]; then
        echo "✅ Fix script completed - check $FIX_LOG"
        
        # Show summary
        echo ""
        echo "Fix Summary:"
        echo "------------"
        tail -15 "$FIX_LOG"
    else
        echo "❌ Fix script failed - check $FIX_LOG for details"
        exit 1
    fi
else
    echo "❌ fix-esther-ziwa-negative-balance.php not found"
    exit 1
fi

echo ""

# Step 5: Run verification
echo "5. VERIFICATION"
echo "==============="

if [ -f "diagnose-esther-account.php" ]; then
    echo "Running verification..."
    php diagnose-esther-account.php > "$VERIFICATION_LOG" 2>&1
    echo "✅ Verification complete - check $VERIFICATION_LOG"
    
    # Show summary
    echo ""
    echo "Verification Summary:"
    echo "--------------------"
    tail -10 "$VERIFICATION_LOG"
else
    echo "⚠️  Skipping verification - diagnostic script not found"
fi

echo ""

# Step 6: Clear caches
echo "6. CLEARING CACHES"
echo "=================="

echo "Clearing application caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
echo "✅ Caches cleared"

echo ""

# Step 7: Summary
echo "7. DEPLOYMENT SUMMARY"
echo "===================="

echo "✅ Esther Ziwa account fix deployment completed"
echo ""
echo "📊 Files created:"
echo "   - Backup: $BACKUP_FILE"
echo "   - Diagnosis: $DIAGNOSIS_LOG"
echo "   - Fix Log: $FIX_LOG"
echo "   - Verification: $VERIFICATION_LOG"
echo ""
echo "📝 Next Steps:"
echo "   1. Review the fix log to confirm success"
echo "   2. Test Esther's account login and wallet access"
echo "   3. Inform Esther that her account has been corrected"
echo "   4. Monitor for any related issues"
echo ""
echo "🚨 If issues occur:"
echo "   - Check the log files for details"
echo "   - Restore from backup if needed: $BACKUP_FILE"
echo "   - Contact technical support"
echo ""

echo "Completed at: $(date)"
echo "=== DEPLOYMENT COMPLETE ==="