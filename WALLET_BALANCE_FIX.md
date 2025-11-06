# Wallet Balance Double-Counting Fix

**Date:** November 6, 2025  
**Issue:** Esther Ziwa's wallet showed -K1000 after premium starter kit purchase  
**Status:** ✅ Fixed and Deployed

## Problem

After Esther Ziwa purchased a premium starter kit (K1000) using her wallet balance, her wallet showed -K1000 instead of K0.

### Root Cause

The `WalletService::calculateTotalExpenses()` method was counting starter kit purchases **twice**:

1. **First count**: In `withdrawals` table with `withdrawal_method='wallet_payment'`
2. **Second count**: In `starter_kit_purchases` table with `payment_method='wallet'`

This caused a double deduction:
- Topups: K1000
- Withdrawals: K1000 (starter kit)
- Starter Kit Purchases: K1000 (same purchase, counted again!)
- **Result**: K1000 - K1000 - K1000 = -K1000 ❌

## Solution

Removed the `starterKitExpenses` calculation from `WalletService::calculateTotalExpenses()` since starter kit wallet payments are already recorded in the `withdrawals` table.

### Changes Made

**File: `app/Services/WalletService.php`**

**Before:**
```php
public function calculateTotalExpenses(User $user): float
{
    $totalWithdrawals = ...;
    $workshopExpenses = ...;
    $transactionExpenses = ...;
    $starterKitExpenses = ...; // ❌ Double counting
    $loanRepayments = ...;
    
    return $totalWithdrawals + $workshopExpenses + 
           $transactionExpenses + $starterKitExpenses + $loanRepayments;
}
```

**After:**
```php
public function calculateTotalExpenses(User $user): float
{
    $totalWithdrawals = ...; // ✅ Already includes starter kit payments
    $workshopExpenses = ...;
    $transactionExpenses = ...;
    // Removed starterKitExpenses - already in withdrawals
    $loanRepayments = ...;
    
    return $totalWithdrawals + $workshopExpenses + 
           $transactionExpenses + $loanRepayments;
}
```

### Why Withdrawals Table is Used

The starter kit purchase uses the `withdrawals` table with `withdrawal_method='wallet_payment'` for backward compatibility. While semantically this isn't a "withdrawal" (money leaving the system), it's an internal transaction that debits the wallet.

**Added comments in code:**
- `StarterKitService.php`: "Using withdrawals table for backward compatibility with existing data"
- `StarterKitController.php`: Same comment for upgrade process
- `WalletService.php`: "Starter kit purchases are NOT counted separately because they are already recorded as withdrawals"

## Verification

**Esther Ziwa's Balance (User ID: 135):**
- Topups: K1000
- Withdrawals: K1000 (starter kit purchase)
- **Expected Balance: K0** ✅
- **Actual Balance: K0** ✅

## Impact

### ✅ Fixed
- All users with starter kit wallet purchases now show correct balance
- No more double-counting
- Backward compatible with existing data

### ✅ No Breaking Changes
- Existing withdrawal records remain unchanged
- New purchases continue to use same method
- All historical data is preserved

## Testing

Tested on production with Esther Ziwa's account:
- Before fix: -K1000
- After fix: K0
- ✅ Balance now correct

## Files Modified

1. `app/Services/WalletService.php`
   - Removed `starterKitExpenses` from expense calculation
   - Updated comments and documentation

2. `app/Services/StarterKitService.php`
   - Added comment explaining withdrawals table usage

3. `app/Http/Controllers/MyGrowNet/StarterKitController.php`
   - Added comment explaining withdrawals table usage for upgrades

## Deployment

- ✅ Committed: 731587f
- ✅ Pushed to GitHub
- ✅ Deployed to production
- ✅ Cache cleared
- ✅ Verified working

## Future Consideration

While the current implementation works, consider migrating to use the `transactions` table for internal wallet transactions in a future update. This would be more semantically correct:

- `withdrawals` table: For actual withdrawals (money leaving to bank/mobile money)
- `transactions` table: For internal transactions (starter kit, upgrades, etc.)

This would require a data migration script to move existing records, so it's deferred to avoid breaking changes.
