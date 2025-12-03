# Starter Kit Commission Fix

**Date:** December 3, 2025  
**Issue:** Duplicate commissions and double charges for starter kit purchases  
**Affected User:** Ruth Luneta (and potentially others)

## Problems Identified

### 1. Duplicate Commission Issue ❌ CRITICAL

**Problem:** Ruth received K150 in commissions but should only get K75

**Details:**
- Two commissions paid for the same person (Ndimanye Kajoba):
  - K75 for "registration" commission
  - K75 for "starter_kit" commission
- This is a duplicate - she should only get ONE commission of K75

**Root Cause:**
```php
// StarterKitService.php line 289
protected function processStarterKitCommissions(User $user, float $amount): void
{
    $mlmService = app(MLMCommissionService::class);
    $commissions = $mlmService->processMLMCommissions($user, $commissionableAmount, 'starter_kit');
}
```

AND

```php
// ProcessMLMCommissions.php line 55
if ($event->paymentType === 'registration' || ($event->paymentType === 'wallet_topup' && $event->amount >= 500)) {
    $packageType = 'registration';
    // Processes commissions AGAIN
}
```

**The Problem:**
1. When a user purchases a starter kit, `StarterKitService::completePurchase()` calls `processStarterKitCommissions()` with type `'starter_kit'`
2. The `ProcessMLMCommissions` listener ALSO triggers on payment verification and processes commissions with type `'registration'`
3. Result: **TWO sets of commissions for the SAME purchase**

### 2. Double Charge Issue ❌ CRITICAL

**Problem:** Ruth was charged K1000 total for her own starter kit

**Details:**
- K500 for "Basic Starter Kit Purchase"
- K500 for "Starter Kit Upgrade: Basic to Premium"
- She only deposited K500, creating a negative balance

**Root Cause:** Unknown - need to investigate if there's an upgrade flow that's incorrectly charging users

### 3. Dashboard Display Issue ✅ MINOR

**Problem:** Dashboard only shows downlines with starter kits, not all downlines

**Impact:** Low - this is actually intentional behavior (only show qualified downlines)

## Solution

### Fix 1: Remove Duplicate Commission Logic

**Option A: Remove from StarterKitService (RECOMMENDED)**

The `ProcessMLMCommissions` listener already handles all commission processing when payments are verified. We should NOT process commissions again in `StarterKitService`.

```php
// StarterKitService.php - REMOVE THIS CALL
public function completePurchase(StarterKitPurchaseModel $purchase): void
{
    // ... other code ...
    
    // ❌ REMOVE THIS - commissions already processed by ProcessMLMCommissions listener
    // $this->processStarterKitCommissions($user, $purchase->amount);
    
    // ... rest of code ...
}
```

**Why this is correct:**
- Starter kit purchases create a payment record
- Payment verification triggers `PaymentVerified` event
- `ProcessMLMCommissions` listener handles ALL commission processing
- No need to duplicate this logic in `StarterKitService`

**Option B: Prevent Duplicate in Listener**

Add a check to prevent processing commissions if they were already processed by `StarterKitService`:

```php
// ProcessMLMCommissions.php
public function handle(PaymentVerified $event): void
{
    // Check if starter_kit commissions already exist
    if ($event->paymentType === 'registration' || $event->paymentType === 'wallet_topup') {
        $hasStarterKitCommission = \App\Models\ReferralCommission::where('referred_id', $user->id)
            ->where('package_type', 'starter_kit')
            ->exists();
            
        if ($hasStarterKitCommission) {
            Log::info("Starter kit commission already processed, skipping registration commission");
            return;
        }
    }
}
```

### Fix 2: Investigate Double Charge

Need to:
1. Check if there's an upgrade flow that's incorrectly implemented
2. Ensure starter kit purchases only charge once
3. Add transaction integrity checks to prevent double charging

### Fix 3: Add Safeguards

```php
// Add to StarterKitService.php
public function purchaseStarterKit(...): StarterKitPurchaseModel
{
    // Check if user already has starter kit
    if ($user->has_starter_kit) {
        throw new \Exception('User already has a starter kit. Use upgrade method instead.');
    }
    
    // ... rest of code ...
}
```

## Implementation Plan

### Step 1: Fix Ruth's Account (Immediate)

Run the fix script:
```bash
php scripts/fix-ruth-account.php
```

This will:
- Remove duplicate registration commission
- Remove incorrect upgrade charge
- Correct starter kit tier to 'basic'
- Recalculate wallet balance

### Step 2: Fix Code (Deploy to Production)

1. **Remove duplicate commission call from StarterKitService**
   - Comment out or remove `processStarterKitCommissions()` call
   - Let `ProcessMLMCommissions` listener handle all commissions

2. **Add safeguards**
   - Prevent purchasing starter kit twice
   - Add transaction integrity checks
   - Add logging for debugging

3. **Test thoroughly**
   - Test new starter kit purchase
   - Verify only ONE set of commissions created
   - Verify correct amount charged
   - Test with both basic and premium tiers

### Step 3: Audit Other Accounts

Check if other users have the same issue:
```sql
-- Find users with duplicate commissions
SELECT 
    rc.referrer_id,
    rc.referred_id,
    COUNT(*) as commission_count,
    SUM(rc.amount) as total_amount
FROM referral_commissions rc
WHERE rc.package_type IN ('registration', 'starter_kit')
GROUP BY rc.referrer_id, rc.referred_id
HAVING COUNT(*) > 1;
```

## Testing Checklist

- [ ] New user purchases basic starter kit (K500)
  - [ ] Only ONE commission set created
  - [ ] Correct amount charged (K500)
  - [ ] Wallet balance correct
  - [ ] Tier set to 'basic'

- [ ] New user purchases premium starter kit (K1000)
  - [ ] Only ONE commission set created
  - [ ] Commissions based on K500 (not K1000)
  - [ ] Correct amount charged (K1000)
  - [ ] Wallet balance correct
  - [ ] Tier set to 'premium'

- [ ] User tries to purchase starter kit twice
  - [ ] Error thrown
  - [ ] No duplicate charge

- [ ] Upline receives commission
  - [ ] Only ONE commission per downline purchase
  - [ ] Correct amount (15% of K500 = K75 for level 1)

## Files to Modify

1. `app/Services/StarterKitService.php`
   - Remove `processStarterKitCommissions()` call from `completePurchase()`
   - Add duplicate purchase check in `purchaseStarterKit()`

2. `app/Listeners/ProcessMLMCommissions.php`
   - Add check to prevent duplicate processing (optional, if keeping both)

3. `scripts/fix-ruth-account.php`
   - Run to fix Ruth's account

## Deployment Steps

### Pre-Deployment (Immediate Fix)

1. **Backup database** (critical!)
   ```bash
   # Backup production database
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Audit all accounts**
   ```bash
   php scripts/audit-duplicate-commissions.php
   ```

3. **Fix Ruth's account specifically**
   ```bash
   php scripts/fix-ruth-account.php
   ```

4. **Fix all affected accounts**
   ```bash
   php scripts/fix-duplicate-commissions-all.php
   ```

### Code Deployment

1. **Deploy code changes**
   - Modified: `app/Services/StarterKitService.php`
   - Modified: `app/Listeners/ProcessMLMCommissions.php`

2. **Test in staging first**
   - Test new starter kit purchase
   - Verify only ONE commission set created
   - Verify correct amounts

3. **Deploy to production**
   ```bash
   git add .
   git commit -m "Fix: Remove duplicate commission processing for starter kits"
   git push origin main
   # Deploy via your deployment process
   ```

4. **Monitor logs**
   ```bash
   tail -f storage/logs/laravel.log | grep -i commission
   ```

5. **Verify fixes**
   - Check Ruth's account is correct
   - Monitor new purchases
   - Verify no duplicate commissions

### Post-Deployment

1. **Run audit again**
   ```bash
   php scripts/audit-duplicate-commissions.php
   ```

2. **Test with real purchase**
   - Have a test user purchase starter kit
   - Verify commission processing
   - Check wallet balances

3. **Monitor for 24 hours**
   - Watch for any errors
   - Check commission processing
   - Verify wallet calculations

## Prevention

To prevent this in the future:

1. **Single Source of Truth:** Commission processing should happen in ONE place only
2. **Idempotency:** Add checks to prevent duplicate commissions
3. **Transaction Integrity:** Use database constraints and unique indexes
4. **Logging:** Add comprehensive logging for debugging
5. **Testing:** Add automated tests for commission processing

## Notes

- The `ProcessMLMCommissions` listener is the correct place for commission processing
- It's triggered by the `PaymentVerified` event, which is the single source of truth
- `StarterKitService` should focus on starter kit logic, not commission processing
- This follows separation of concerns and domain-driven design principles
