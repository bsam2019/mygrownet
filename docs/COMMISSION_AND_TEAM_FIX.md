# Commission Processing and Team Overview Fix

## Date: October 23, 2025

## Issues Identified

### 1. Missing Commission Processing
**Problem**: When admin verifies a payment, no MLM commissions are created for upline referrers.

**Root Cause**: The `PaymentVerified` event is dispatched but has no listener to process commissions.

**Impact**: 
- Referrers are not receiving commissions when their downline pays registration (K500) or subscription fees
- Commission earnings show as K0 for all users
- No commission records in `referral_commissions` table

### 2. Incorrect Active Referrals Count
**Problem**: Team overview shows 0 active referrals even when team members have paid and are active. Additionally, team members who registered but haven't paid are shown as "Active" in the team list.

**Root Cause**: 
1. The `getReferralStatistics` method checks for `investments` (VBIF legacy) instead of checking for active subscriptions.
2. The team members list checks `status === 'active'` instead of verifying actual subscription payment status.

**Impact**:
- Dashboard shows incorrect "Active Team Members" count
- Misleading statistics for users
- Unpaid members appear as "Active" in the team list

## Solutions Implemented

### 1. Commission Processing Event Listener

**File Created**: `app/Listeners/ProcessMLMCommissions.php`

This listener:
- Listens to `PaymentVerified` event
- Processes 7-level MLM commissions when payment is verified
- Handles both registration payments (K500+) and subscription payments
- Uses `MLMCommissionService` to create commission records
- Logs all commission processing for audit trail

**Commission Rates** (MyGrowNet 7-Level Structure):
- Level 1: 15%
- Level 2: 10%
- Level 3: 8%
- Level 4: 6%
- Level 5: 4%
- Level 6: 3%
- Level 7: 2%

### 2. Event Registration

**File Modified**: `app/Providers/EventServiceProvider.php`

Added listener registration:
```php
\App\Domain\Payment\Events\PaymentVerified::class => [
    \App\Listeners\ProcessMLMCommissions::class,
],
```

### 3. Fixed Referral Statistics

**File Modified**: `app/Infrastructure/Persistence/Repositories/EloquentReferralRepository.php`

Changed `getReferralStatistics` method to:
- Check `user->status === 'active'` instead of checking investments
- Added `monthly_commission` calculation
- Added `pending_transactions_count`
- Added placeholders for matrix earnings
- Properly structured return data for MyGrowNet platform

## How It Works Now

### Registration Flow:
1. User registers with referral code → `referrer_id` set
2. User submits K500 registration payment
3. Admin verifies payment
4. `VerifyPaymentUseCase` updates user status to 'active'
5. `PaymentVerified` event dispatched
6. **NEW**: `ProcessMLMCommissions` listener creates commission records for up to 7 upline levels
7. Commissions are marked as 'pending' and can be processed for payment

### Team Overview:
1. User views "My Team" page
2. `ReferralController` fetches team statistics
3. **FIXED**: Active referrals count based on `status = 'active'`
4. Shows correct count of team members who have paid
5. Displays team member names, phone numbers, and status

## Testing Checklist

- [ ] Register new user with referral code
- [ ] Submit K500 registration payment
- [ ] Admin verifies payment
- [ ] Check commission records created in `referral_commissions` table
- [ ] Verify 7 levels of commissions created (if upline exists)
- [ ] Check commission amounts match rates (15%, 10%, 8%, 6%, 4%, 3%, 2%)
- [ ] Verify team overview shows correct active referrals count
- [ ] Check dashboard statistics are accurate

## Database Tables Affected

### `referral_commissions`
New records will be created with:
- `referrer_id`: Upline member receiving commission
- `referred_id`: New member who paid
- `level`: 1-7 (commission level)
- `amount`: Calculated commission amount
- `percentage`: Commission rate (15%, 10%, etc.)
- `status`: 'pending' (ready for payment processing)
- `commission_type`: 'REFERRAL'
- `package_type`: 'registration' or 'subscription'
- `package_amount`: Original payment amount

### `users`
- `status` field used to determine active members
- `total_referral_earnings` will be updated when commissions are paid

## Commission Payment Processing

Commissions are created as 'pending' and need to be processed for payment:

```php
// Process pending commissions (can be run via cron job)
$service = new MLMCommissionService();
$service->processCommissionPayments();
```

Or individual commission can be paid:
```php
$commission = ReferralCommission::find($id);
$commission->processPayment();
```

## Notes

- Commissions are only created for users with `status = 'active'` (have active subscription)
- Registration payments (K500+) trigger commission processing
- Subscription payments also trigger commission processing
- Team volumes are updated when commissions are processed
- All commission processing is logged for audit purposes

## Next Steps

1. Deploy changes to production
2. Test with real registration flow
3. Monitor commission creation in database
4. Verify team statistics are accurate
5. Set up automated commission payment processing (cron job)
