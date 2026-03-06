# Financial Reporting Revenue Tracking Fix

**Last Updated:** March 6, 2026
**Status:** Production

## Overview

Fixed critical issue where financial reports were not including all revenue sources. The system was only counting subscription revenue and missing wallet deposits, starter kit purchases, and other platform purchases.

## Problem

The legacy `FinancialReportingController` was using `FinancialReportingService` which only queried the `subscriptions` table for revenue calculation. Additionally, revenue calculations weren't using ABS() for wallet debits, causing negative revenue.

**Issues:**
- ❌ Wallet deposits/top-ups were not counted
- ❌ Starter kit purchases were not counted  
- ❌ Platform module purchases were not counted
- ❌ Service payments were not counted
- ❌ Wallet debits stored as negative amounts (-500.00) resulted in negative revenue
- ✅ Only subscription payments were counted

This resulted in severely underreported revenue figures (showing K0.00) in financial reports.

## Root Cause

The system had TWO financial reporting services:

1. **Legacy Service** (`FinancialReportingService`)
   - Only looked at `subscriptions` table
   - Incomplete revenue tracking
   - Used by `/admin/financial/*` routes

2. **New Service** (`TransactionBasedFinancialReportingService`)
   - Uses `transactions` table as single source of truth
   - Includes ALL revenue types
   - Used by `/admin/financial/v2/*` routes

The controller was using the legacy service, causing incomplete reports.

## Solution

Updated financial reporting services to properly calculate revenue:

### Changes Made

**Files Modified:**
1. `app/Http/Controllers/Admin/FinancialReportingController.php`
2. `app/Services/ProfitLossTrackingService.php`
3. `app/Services/TransactionBasedFinancialReportingService.php`

**Fix 1: Use Transaction-Based Service**
- Injected `TransactionBasedFinancialReportingService` into controller
- Updated `index()` method to use transaction-based service for overview
- Updated `getRevenueBreakdown()` to use transaction-based service

**Fix 2: Handle Negative Wallet Debits**
- Added `ABS()` to revenue calculations in both services
- Wallet debits (purchases) are stored as negative amounts
- Using ABS() converts -500.00 to 500.00 for revenue calculation
- Applied to both `ProfitLossTrackingService` and `TransactionBasedFinancialReportingService`

### Revenue Types Now Included

The transaction-based service properly tracks:

- `WALLET_TOPUP` - Wallet deposits/top-ups
- `SUBSCRIPTION_PAYMENT` - Platform subscriptions
- `PURCHASE` - General platform purchases
- `STARTER_KIT_PURCHASE` - Starter kit sales
- `SERVICE_PAYMENT` - Service payments

## Technical Details

### Before (Incorrect)

```php
private function getTotalRevenue(?Carbon $startDate = null): float
{
    $query = Subscription::where('status', 'active');
    if ($startDate) {
        $query->where('created_at', '>=', $startDate);
    }
    
    return $query->sum('amount');
}
```

### After (Correct)

```php
// Use ABS() because wallet debits are stored as negative amounts
// but represent revenue (money spent by users on products/services)
$breakdown = $query->select(
        'transaction_type',
        DB::raw('SUM(ABS(amount)) as total'),
        DB::raw('COUNT(*) as count')
    )
    ->groupBy('transaction_type')
    ->get();
```

**Example Transaction:**
```
amount: -500.00 (wallet debit)
ABS(amount): 500.00 (revenue)
```

## Impact

### Before Fix
- Revenue reports showed only subscription payments
- Wallet deposits not counted as revenue
- Starter kit sales not reflected in reports
- Financial health metrics were inaccurate

### After Fix
- ✅ All revenue sources properly tracked
- ✅ Accurate financial reports
- ✅ Correct revenue breakdown by source
- ✅ Proper financial health metrics

## Testing

To verify the fix works:

1. Navigate to `/admin/financial/dashboard`
2. Check revenue metrics - should now include all sources
3. Compare with `/admin/financial/v2/dashboard` - numbers should match
4. Verify wallet deposits appear in revenue breakdown
5. Verify starter kit purchases appear in revenue breakdown

## Migration Path

### Current State
- Legacy routes (`/admin/financial/*`) now use transaction-based service for revenue
- New routes (`/admin/financial/v2/*`) already use transaction-based service
- Both systems now report accurate revenue

### Future Recommendation
Consider fully migrating to v2 financial reporting system and deprecating legacy routes once all features are migrated.

## Related Files

- `app/Http/Controllers/Admin/FinancialReportingController.php` - Updated controller
- `app/Services/FinancialReportingService.php` - Legacy service (still used for some metrics)
- `app/Services/TransactionBasedFinancialReportingService.php` - New service (now used for revenue)
- `routes/admin.php` - Route definitions for both systems

## Changelog

### March 6, 2026 - Second Fix
- Fixed revenue showing K0.00 even with starter kit purchases
- Root cause: Wallet debits stored as negative amounts (-500.00)
- Revenue calculations were summing negative amounts
- Added ABS() to revenue queries in both services
- Now properly calculates revenue from wallet-paid purchases

### March 6, 2026 - Initial Fix
- Fixed revenue tracking to include all transaction types
- Injected TransactionBasedFinancialReportingService into controller
- Updated index() and getRevenueBreakdown() methods
- Deployed to production
- Revenue reports now accurate and complete
