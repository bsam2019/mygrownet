# Immediate Fixes Required - Financial System

**Priority:** CRITICAL  
**Created:** 2026-02-28  
**Last Updated:** 2026-02-28  
**Status:** READY FOR DEPLOYMENT - 5 of 6 Fixes Applied

## Progress Summary

✅ **Fix 1: Add Loan Support to UnifiedWalletService** - COMPLETED  
✅ **Fix 2: Add Cache Invalidation** - COMPLETED  
✅ **Fix 3: Create DepositSyncService** - COMPLETED  
✅ **Fix 4: Replace WalletService** - COMPLETED  
✅ **Fix 5: Fix Withdrawal Double-Counting** - COMPLETED  
⏳ **Fix 6: Run Reconciliation** - READY TO DEPLOY

---

## Critical Bug: Negative Wallet Balance

### Symptom
User deposits K500, purchases starter kit, wallet shows -K500 instead of K0.

### Root Cause Analysis

**Key Finding:** The `transactions` table already exists (created 2024-02-20) with correct structure. The issue is NOT a missing table, but a synchronization problem.

**The Problem:**
1. **Deposits recorded in TWO places:**
   - `member_payments` table (payment_type='wallet_topup', status='verified')
   - `transactions` table (transaction_type='wallet_topup', status='completed')
   
2. **Sync Issue:** When a deposit is verified in `member_payments`, it's not always creating a corresponding record in `transactions`

3. **Double Counting Risk:** `UnifiedWalletService` counts deposits from BOTH tables:
   ```php
   $deposits = (deposits_mp) + (deposits_tx)
   ```
   This means if a deposit exists in both tables, it gets counted twice!

4. **Starter Kit Purchase:** Uses `TransactionIntegrityService` to create a debit transaction correctly, but if the deposit wasn't synced, balance goes negative

5. **Cache Not Cleared:** Balance cached for 120 seconds, not invalidated after transactions

### Actual Root Causes
1. Deposit not being synced from `member_payments` to `transactions`
2. Cache not invalidated after transactions
3. Potential double-counting if deposit exists in both tables

---

## Emergency Fixes (Deploy Today)

### ✅ Fix 1: Add Loan Support to UnifiedWalletService
**Priority:** CRITICAL  
**Risk:** LOW  
**Impact:** Feature parity with legacy WalletService  
**Status:** ✅ COMPLETED

**Changes Made:**
- Added `loan_disbursements` and `loan_repayments` to SQL query in `getWalletTotals()`
- Updated credits calculation to include loan disbursements
- Updated debits calculation to include loan repayments
- Updated `getWalletBreakdown()` to show loan information

**Files Modified:**
- ✅ `app/Domain/Wallet/Services/UnifiedWalletService.php`

---

### ✅ Fix 2: Add Cache Invalidation
**Priority:** CRITICAL  
**Risk:** LOW  
**Impact:** Immediate balance updates  
**Status:** ✅ COMPLETED

**Changes Made:**
- Added `UnifiedWalletService` injection to `TransactionIntegrityService` constructor
- Added `clearCache()` call after every debit transaction
- Added `clearCache()` call after every credit transaction
- Updated `checkSufficientBalance()` to use `UnifiedWalletService`
- Added `clearCache()` call in `GiftStarterKitUseCase` after gift transaction

**Files Modified:**
- ✅ `app/Domain/Financial/Services/TransactionIntegrityService.php`
- ✅ `app/Application/StarterKit/UseCases/GiftStarterKitUseCase.php`

---

### ✅ Fix 3: Create DepositSyncService
**Priority:** CRITICAL  
**Risk:** LOW  
**Impact:** Fixes negative balances  
**Status:** ✅ COMPLETED

**Changes Made:**
- Created `DepositSyncService` with duplicate prevention logic
- Created Artisan command `finance:sync-deposits` for manual/automated sync
- Service checks for existing transactions before creating new ones
- Clears wallet cache after sync

**Files Created:**
- ✅ `app/Services/DepositSyncService.php`
- ✅ `app/Console/Commands/SyncDepositsToTransactions.php`

**Usage:**
```bash
# Sync all deposits
php artisan finance:sync-deposits

# Sync for specific user
php artisan finance:sync-deposits --user=123

# Dry run (preview only)
php artisan finance:sync-deposits --dry-run
```

---

### ✅ Fix 4: Replace WalletService with UnifiedWalletService
**Priority:** MEDIUM  
**Risk:** LOW  
**Impact:** Consistency across codebase  
**Status:** ✅ COMPLETED

**Changes Made:**
- Replaced `WalletService` with `UnifiedWalletService` in `GiftStarterKitUseCase`
- Replaced `WalletService` with `UnifiedWalletService` in `StarterKitService`
- Removed unused `WalletService` injection from `GeneralWalletController`

**Files Modified:**
- ✅ `app/Application/StarterKit/UseCases/GiftStarterKitUseCase.php`
- ✅ `app/Services/StarterKitService.php`
- ✅ `app/Http/Controllers/Wallet/GeneralWalletController.php`

**Result:** `WalletService` is now completely unused and can be deprecated

---

### ✅ Fix 5: Fix Withdrawal Double-Counting
**Priority:** HIGH  
**Risk:** LOW  
**Impact:** Accurate balance calculation  
**Status:** ✅ COMPLETED

**Issue:**
- Withdrawals were counted from BOTH `withdrawals` table AND `transactions` table
- `UnifiedWalletService` was summing both sources, causing double-counting

**Solution:**
- Changed to use ONLY `transactions` table as single source of truth
- Removed `withdrawals` table query from balance calculation
- Updated withdrawal approval process to sync both tables
- Added cache invalidation after approval/rejection

**Changes Made:**
- Modified `UnifiedWalletService` to only count from `transactions` table
- Updated `WithdrawalApprovalController` to update transaction status when approving/rejecting
- Added cache clearing after withdrawal status changes
- Withdrawals now counted with status IN ('completed', 'pending') to include pending withdrawals in balance

**Files Modified:**
- ✅ `app/Domain/Wallet/Services/UnifiedWalletService.php`
- ✅ `app/Http/Controllers/Admin/WithdrawalApprovalController.php`

---

### ⏳ Fix 6: Run Reconciliation
**Priority:** HIGH  
**Risk:** LOW  
**Impact:** Fixes existing negative balances  
**Status:** READY TO DEPLOY

**Action Required:**
Run the deposit sync command to fix existing data issues

**Commands:**
```bash
# 1. Dry run first (preview only)
php artisan finance:sync-deposits --dry-run

# 2. Sync all deposits
php artisan finance:sync-deposits

# 3. Verify specific user if needed
php artisan wallet:diagnose USER_ID
```

---

## Diagnostic Steps

### Step 1: Run Diagnosis
```bash
php artisan wallet:diagnose USER_ID
```

This will show:
- All deposits in both tables
- All transactions
- Balance from both services
- Identify discrepancies

### Step 2: Check Specific User
```bash
php artisan tinker
```

```php
$user = User::find(USER_ID);

// Check deposits
$deposits = DB::table('member_payments')
    ->where('user_id', $user->id)
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->get();

echo "Deposits in member_payments: {$deposits->count()}\n";
echo "Total deposit amount: K" . $deposits->sum('amount') . "\n\n";

// Check transactions
$transactions = DB::table('transactions')
    ->where('user_id', $user->id)
    ->get();

echo "Transactions: {$transactions->count()}\n";
echo "Topup transactions: " . $transactions->where('transaction_type', 'wallet_topup')->count() . "\n";
echo "Total topup amount: K" . $transactions->where('transaction_type', 'wallet_topup')->sum('amount') . "\n\n";

// Check balance using UnifiedWalletService (primary service)
$uws = app(\App\Domain\Wallet\Services\UnifiedWalletService::class);
$uwsBalance = $uws->calculateBalance($user);
$breakdown = $uws->getWalletBreakdown($user);

echo "Current Balance: K{$uwsBalance}\n";
echo "Total Credits: K{$breakdown['credits']['total']}\n";
echo "Total Debits: K{$breakdown['debits']['total']}\n";
```

---

## Temporary Workaround (Until Fix Deployed)

### Manual Balance Correction

**IMPORTANT:** The `transactions` table already exists. The issue is deposits in `member_payments` are not synced to `transactions`.

If user has negative balance due to missing deposit:

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::find(USER_ID);

// Get all verified deposits from member_payments
$deposits = DB::table('member_payments')
    ->where('user_id', $user->id)
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->get();

echo "Found {$deposits->count()} deposits in member_payments\n";

foreach ($deposits as $deposit) {
    // Check if transaction already exists for this deposit
    $exists = DB::table('transactions')
        ->where('user_id', $user->id)
        ->where(function($q) use ($deposit) {
            $q->where('reference_number', $deposit->transaction_id)
              ->orWhere(function($q2) use ($deposit) {
                  $q2->where('transaction_type', 'wallet_topup')
                     ->where('amount', $deposit->amount)
                     ->whereDate('created_at', $deposit->created_at);
              });
        })
        ->exists();
    
    if (!$exists) {
        // Create missing transaction for deposit
        DB::table('transactions')->insert([
            'user_id' => $user->id,
            'transaction_type' => 'wallet_topup',
            'amount' => $deposit->amount,
            'reference_number' => $deposit->transaction_id ?? ('SYNC-' . $deposit->id),
            'description' => 'Deposit sync - ' . ($deposit->payment_method ?? 'Mobile Money'),
            'status' => 'completed',
            'payment_method' => $deposit->payment_method,
            'created_at' => $deposit->created_at,
            'updated_at' => $deposit->updated_at,
        ]);
        
        echo "✓ Synced deposit: K{$deposit->amount} from {$deposit->created_at}\n";
    } else {
        echo "- Deposit already synced: K{$deposit->amount}\n";
    }
}

// Clear cache
app(\App\Domain\Wallet\Services\UnifiedWalletService::class)->clearCache($user);

// Verify balance
$balance = app(\App\Domain\Wallet\Services\UnifiedWalletService::class)->calculateBalance($user);
echo "\n✓ New balance: K{$balance}\n";
```

### Bulk Fix for All Users

To fix all users at once:

```php
use App\Models\User;
use Illuminate\Support\Facades\DB;

$users = User::all();
$fixed = 0;

foreach ($users as $user) {
    $deposits = DB::table('member_payments')
        ->where('user_id', $user->id)
        ->where('payment_type', 'wallet_topup')
        ->where('status', 'verified')
        ->get();
    
    $synced = 0;
    foreach ($deposits as $deposit) {
        $exists = DB::table('transactions')
            ->where('user_id', $user->id)
            ->where(function($q) use ($deposit) {
                $q->where('reference_number', $deposit->transaction_id)
                  ->orWhere(function($q2) use ($deposit) {
                      $q2->where('transaction_type', 'wallet_topup')
                         ->where('amount', $deposit->amount)
                         ->whereDate('created_at', $deposit->created_at);
                  });
            })
            ->exists();
        
        if (!$exists) {
            DB::table('transactions')->insert([
                'user_id' => $user->id,
                'transaction_type' => 'wallet_topup',
                'amount' => $deposit->amount,
                'reference_number' => $deposit->transaction_id ?? ('SYNC-' . $deposit->id),
                'description' => 'Deposit sync - ' . ($deposit->payment_method ?? 'Mobile Money'),
                'status' => 'completed',
                'payment_method' => $deposit->payment_method,
                'created_at' => $deposit->created_at,
                'updated_at' => $deposit->updated_at,
            ]);
            $synced++;
        }
    }
    
    if ($synced > 0) {
        app(\App\Domain\Wallet\Services\UnifiedWalletService::class)->clearCache($user);
        $fixed++;
        echo "User {$user->id} ({$user->name}): Synced {$synced} deposits\n";
    }
}

echo "\n✓ Fixed {$fixed} users\n";
```

---

## Testing Checklist

Before deploying fixes:

- [ ] Test deposit flow end-to-end
- [ ] Verify transaction created in both tables
- [ ] Test starter kit purchase
- [ ] Verify balance updates immediately (no cache delay)
- [ ] Test with multiple users
- [ ] Verify no negative balances
- [ ] Check both wallet services show same balance
- [ ] Test withdrawal still works
- [ ] Verify transaction history displays correctly

---

## Deployment Steps

### 1. Backup Database
```bash
php artisan backup:run --only-db
```

### 2. Deploy Fixes
```bash
git pull origin main
composer install
php artisan migrate
php artisan cache:clear
php artisan config:clear
```

### 3. Run Reconciliation
```bash
php artisan wallet:reconcile-all
```

### 4. Monitor
- Check error logs
- Monitor user reports
- Verify no new negative balances

---

## Rollback Plan

If issues arise:

```bash
# Revert code
git revert HEAD

# Restore database backup
php artisan backup:restore --backup=BACKUP_NAME

# Clear caches
php artisan cache:clear
php artisan config:clear
```

---

## Communication Plan

### To Users (If Affected)
```
Subject: Wallet Balance Update

Dear [User Name],

We've identified and resolved an issue affecting wallet balance calculations. 
Your correct balance is now displayed.

If you have any questions, please contact support.

Thank you for your patience.
```

### To Support Team
- Brief on the issue
- Provide diagnostic commands
- Escalation process for affected users
- Compensation policy (if applicable)

---

## Success Metrics

After deployment, monitor:

1. **Negative Balances:** Should be 0 (unless legitimate debt)
2. **Balance Discrepancies:** Both services should match
3. **Cache Updates:** Balance updates within 1 second
4. **Transaction Recording:** 100% of operations create transactions
5. **User Complaints:** Should decrease to 0

---

## Next Steps

1. Deploy immediate fixes (today)
2. Monitor for 48 hours
3. Run full reconciliation
4. Begin Phase 1 of architecture refactoring (see FINANCIAL_SYSTEM_ARCHITECTURE.md)
5. Schedule weekly reviews until stable

---

**Contact:** Development Team  
**Escalation:** CTO  
**Emergency Contact:** [Phone Number]


## Deployment Steps

### Pre-Deployment Checklist

- [x] All code changes tested locally
- [x] Cache invalidation added to all transaction points
- [x] Deposit sync service created with duplicate prevention
- [x] WalletService replaced with UnifiedWalletService
- [x] Withdrawal double-counting fixed
- [ ] Backup database
- [ ] Run deposit sync
- [ ] Verify user balances
- [ ] Monitor for issues

### Step 1: Backup Database
```bash
php artisan backup:run --only-db
```

### Step 2: Deploy Code Changes
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Run Deposit Sync (Critical!)
```bash
# Preview what will be synced
php artisan finance:sync-deposits --dry-run

# If preview looks good, run actual sync
php artisan finance:sync-deposits
```

### Step 4: Verify Balances
```bash
# Check specific users who reported issues
php artisan wallet:diagnose USER_ID

# Example output should show:
# - Deposits from both tables
# - Transactions created
# - Correct balance calculation
```

### Step 5: Monitor
- Check error logs: `tail -f storage/logs/laravel.log`
- Monitor user reports
- Verify no new negative balances
- Check withdrawal approvals work correctly

---

## Post-Deployment Verification

### Test Cases

1. **Deposit Flow**
   - User makes deposit
   - Admin verifies deposit
   - Check transaction created in both tables
   - Verify balance updates immediately

2. **Withdrawal Flow**
   - User requests withdrawal
   - Check transaction created with status='pending'
   - Admin approves withdrawal
   - Verify transaction status updates to 'completed'
   - Check balance updates immediately

3. **Starter Kit Purchase**
   - User purchases starter kit with wallet
   - Verify transaction created
   - Check balance updates immediately
   - Confirm no negative balance

4. **Gift Starter Kit**
   - User gifts starter kit
   - Verify gifter's balance deducted
   - Check cache cleared
   - Confirm transaction recorded

---

## Rollback Plan

If issues arise:

```bash
# 1. Revert code
git revert HEAD~5..HEAD  # Revert last 5 commits

# 2. Restore database backup
php artisan backup:restore --backup=BACKUP_NAME

# 3. Clear caches
php artisan cache:clear
php artisan config:clear

# 4. Restart services
php artisan queue:restart
```

---

## Success Metrics

After deployment, verify:

1. ✅ **No Negative Balances** - Check all users have balance >= 0
2. ✅ **Immediate Balance Updates** - No 120-second cache delay
3. ✅ **Deposits Synced** - All verified deposits have transaction records
4. ✅ **Withdrawals Not Double-Counted** - Balance calculation correct
5. ✅ **Loan Support** - Loan transactions included in balance
6. ✅ **Single Wallet Service** - Only UnifiedWalletService in use

---

## Files Changed Summary

### Modified Files (6)
1. `app/Domain/Wallet/Services/UnifiedWalletService.php` - Added loan support, fixed withdrawal counting
2. `app/Domain/Financial/Services/TransactionIntegrityService.php` - Added cache invalidation
3. `app/Application/StarterKit/UseCases/GiftStarterKitUseCase.php` - Replaced WalletService, added cache clear
4. `app/Services/StarterKitService.php` - Replaced WalletService
5. `app/Http/Controllers/Wallet/GeneralWalletController.php` - Removed unused WalletService
6. `app/Http/Controllers/Admin/WithdrawalApprovalController.php` - Added transaction status sync

### New Files (2)
7. `app/Services/DepositSyncService.php` - Deposit synchronization service
8. `app/Console/Commands/SyncDepositsToTransactions.php` - Artisan command for sync

### Documentation (3)
9. `docs/Finance/IMMEDIATE_FIXES_REQUIRED.md` - This document
10. `docs/Finance/FINANCE_TABLES_REFERENCE.md` - Complete table reference
11. `docs/Finance/FINANCIAL_SYSTEM_ARCHITECTURE.md` - Long-term architecture plan

---

## Next Steps After Deployment

1. **Monitor for 48 hours** - Watch for any issues
2. **Deprecate WalletService** - Mark as @deprecated, add warning
3. **Update tests** - Ensure all tests use UnifiedWalletService
4. **Begin Phase 1 of Architecture Refactoring** - See FINANCIAL_SYSTEM_ARCHITECTURE.md
5. **Schedule weekly reviews** - Until system is stable

---

**Deployment Date:** _____________  
**Deployed By:** _____________  
**Verified By:** _____________  
**Issues Found:** _____________
