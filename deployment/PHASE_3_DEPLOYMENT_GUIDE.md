# Phase 3 Deployment Guide

**Date:** 2026-03-01  
**Phase:** Data Migration and Consolidation  
**Status:** Ready for Deployment

---

## What's Being Deployed

### Critical Fix
**Problem:** Verified payments in `member_payments` table don't create transaction records, causing negative wallet balances.

**Solution:** New event listener automatically creates transaction records when payments are verified.

### Components

1. **RecordPaymentTransaction Listener**
   - Listens to `PaymentVerified` event
   - Creates transaction record automatically
   - Maps payment types to transaction types
   - Prevents duplicates using TransactionIntegrityService

2. **MigratePaymentsToTransactions Command**
   - Migrates historical verified payments to transactions table
   - Supports dry-run mode for safe testing
   - Prevents duplicate transactions
   - Clears wallet caches after migration

3. **ValidateFinancialIntegrity Command**
   - Validates all financial data integrity
   - Checks for missing transactions
   - Detects duplicate transactions
   - Identifies negative balances

---

## Pre-Deployment Checklist

- [ ] Database backup created
- [ ] Code reviewed and tested in staging
- [ ] Dry-run migration tested
- [ ] Rollback plan prepared
- [ ] Team notified of deployment
- [ ] Monitoring tools ready

---

## Deployment Steps

### Step 1: Backup Database
```bash
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
php artisan backup:run --only-db
```

### Step 2: Deploy Code
```bash
# On local machine
bash deployment/deploy-phase3.sh
```

This script will:
1. Pull latest code
2. Install dependencies
3. Clear caches
4. Run dry-run migration (for review)
5. Ask for confirmation
6. Run actual migration
7. Validate data integrity
8. Restart services

### Step 3: Verify Deployment
```bash
# Check migration results
php artisan finance:validate-integrity

# Compare wallet services
php artisan wallet:compare --limit=20

# Check for negative balances
php artisan wallet:monitor
```

---

## Expected Results

### Migration Statistics
Based on current production data:
- **23 verified payments** in member_payments table
- **~17-19 will be migrated** (some already have transactions)
- **~4-6 will be skipped** (duplicates)

### Balance Changes
- Users with verified payments will see correct balances
- No more negative balances from missing deposits
- Historical transactions will be complete

---

## Post-Deployment Monitoring

### First Hour
- [ ] Check application logs for errors
- [ ] Verify no 500 errors on wallet pages
- [ ] Test payment verification flow
- [ ] Check wallet balances for test users

### First 24 Hours
- [ ] Run `php artisan finance:validate-integrity` every 6 hours
- [ ] Monitor for negative balances
- [ ] Check transaction creation on new payments
- [ ] Review user support tickets

### First Week
- [ ] Run full wallet comparison: `php artisan wallet:compare --all`
- [ ] Validate all new payments create transactions
- [ ] Check for any data inconsistencies
- [ ] Prepare for Phase 4 (if all good)

---

## Rollback Plan

If critical issues arise:

### Immediate Rollback
```bash
# 1. Restore database from backup
ssh sammy@138.197.187.134
cd /var/www/mygrownet.com
php artisan backup:restore --latest

# 2. Revert code
git checkout <previous-commit-hash>
composer install --no-dev --optimize-autoloader

# 3. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Restart services
php artisan queue:restart
```

### Partial Rollback (Keep Code, Remove Listener)
If only the listener is causing issues:

```php
// In app/Providers/EventServiceProvider.php
// Comment out the RecordPaymentTransaction listener:

\App\Domain\Payment\Events\PaymentVerified::class => [
    // \App\Listeners\RecordPaymentTransaction::class, // DISABLED
    \App\Listeners\ProcessMLMCommissions::class,
],
```

Then:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Testing Commands

### Dry-Run Migration
```bash
php artisan finance:migrate-payments --dry-run
```

### Migrate Specific User
```bash
php artisan finance:migrate-payments --user=6
```

### Validate Specific User
```bash
php artisan finance:validate-integrity --user=6
```

### Compare Wallet Services
```bash
# Single user
php artisan wallet:compare --user=6

# Random sample
php artisan wallet:compare --limit=10

# All users (takes time)
php artisan wallet:compare --all
```

---

## Success Criteria

Phase 3 deployment is successful when:

1. ✅ All verified payments have transaction records
2. ✅ No negative balances (unless legitimate debt)
3. ✅ New payment verifications create transactions automatically
4. ✅ Wallet balances are consistent
5. ✅ No duplicate transactions created
6. ✅ All validation checks pass
7. ✅ No user complaints about balances
8. ✅ System performance maintained

---

## Known Issues & Limitations

### Non-Issues (By Design)
- LGR credits are separate from wallet (intentional)
- Shop credit not in transactions (tracked separately)
- Some old transactions may not have source module (legacy data)

### To Be Addressed in Phase 4
- Withdrawals not linked to transactions (foreign key needed)
- UnifiedWalletService still queries member_payments (will be removed)
- Legacy WalletService still in use (2 files remaining)

---

## Support & Troubleshooting

### Common Issues

**Issue:** Migration shows "already exists" for all payments
- **Cause:** Transactions already created manually or by previous sync
- **Action:** This is normal, no action needed

**Issue:** Negative balance after migration
- **Cause:** Phantom withdrawal or missing deposit
- **Action:** Run `php artisan finance:validate-integrity --user=<id>` to diagnose

**Issue:** Duplicate transactions created
- **Cause:** TransactionIntegrityService not preventing duplicates
- **Action:** Run `php artisan finance:cleanup-duplicates` (to be created)

### Getting Help

1. Check application logs: `storage/logs/laravel.log`
2. Run validation: `php artisan finance:validate-integrity`
3. Check specific user: `php artisan wallet:compare --user=<id>`
4. Review deployment logs
5. Contact development team

---

## Files Modified/Created

### New Files
- `app/Listeners/RecordPaymentTransaction.php`
- `app/Console/Commands/MigratePaymentsToTransactions.php`
- `app/Console/Commands/ValidateFinancialIntegrity.php`
- `deployment/deploy-phase3.sh`
- `deployment/PHASE_3_DEPLOYMENT_GUIDE.md`
- `docs/Finance/PHASE_3_DATA_MIGRATION.md`

### Modified Files
- `app/Providers/EventServiceProvider.php`
- `docs/Finance/FINANCIAL_SYSTEM_ARCHITECTURE.md`
- `docs/Finance/FINANCE_TABLES_REFERENCE.md`

---

## Next Steps (Phase 4)

After successful Phase 3 deployment and 48-hour monitoring:

1. Add `transaction_id` foreign key to withdrawals table
2. Update withdrawal approval to link transactions
3. Remove member_payments query from UnifiedWalletService
4. Replace legacy WalletService in remaining 2 files
5. Full cutover to new domain-driven services

---

**Deployment Owner:** Development Team  
**Deployment Date:** 2026-03-01  
**Review Date:** 2026-03-03 (48 hours post-deployment)

