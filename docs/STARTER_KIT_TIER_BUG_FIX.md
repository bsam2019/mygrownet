# Starter Kit Tier Bug Fix

**Date:** 2025-11-05  
**Status:** ✅ Fixed  
**Severity:** High - Users were being overcharged

## Problem

Users selecting the **Premium tier** (K1,000) were being charged the correct amount but receiving **Basic tier** benefits:
- Charged: K1,000
- Received: Basic tier with K100 shop credit (should be K200)
- Missing: LGR qualification and premium benefits

## Root Cause

The `tier` field was **missing from the `$fillable` array** in `StarterKitPurchaseModel`.

When creating a purchase record with:
```php
StarterKitPurchaseModel::create([
    'user_id' => $user->id,
    'tier' => $tier,  // ❌ This was being silently ignored!
    'amount' => $price,
    // ...
]);
```

Laravel's mass assignment protection silently ignored the `tier` field, and the database default value of `'basic'` was used instead.

## The Fix

### File: `app/Infrastructure/Persistence/Eloquent/StarterKit/StarterKitPurchaseModel.php`

**Before:**
```php
protected $fillable = [
    'user_id',
    'amount',
    'payment_method',
    'payment_reference',
    'status',
    'invoice_number',
    'purchased_at',
];
```

**After:**
```php
protected $fillable = [
    'user_id',
    'tier',  // ✅ Added
    'amount',
    'payment_method',
    'payment_reference',
    'status',
    'invoice_number',
    'purchased_at',
];
```

## Additional Improvements

### 1. Enhanced Logging
Added tier information to purchase logs for better debugging:

```php
Log::info('Starter Kit purchase created', [
    'user_id' => $user->id,
    'invoice' => $purchase->invoice_number,
    'payment_method' => $paymentMethod,
    'tier' => $tier,  // ✅ Added
    'amount' => $price,  // ✅ Added
]);
```

### 2. Request Logging
Added logging in controller to track what tier users are selecting:

```php
\Log::info('Starter Kit purchase request', [
    'user_id' => $user->id,
    'tier' => $validated['tier'],
    'payment_method' => $validated['payment_method'],
]);
```

## Testing

### Manual Test Steps

1. **Reset test user** (if needed):
   ```bash
   php artisan tinker --execute="require 'scripts/reset-user-for-testing.php';"
   ```

2. **Issue loan** (if needed):
   - Go to Admin → Loans
   - Issue K1,000 loan to test user

3. **Purchase Premium tier**:
   - Login as test user
   - Go to My Starter Kit → Purchase
   - Select **Premium tier** (K1,000)
   - Complete purchase with wallet

4. **Verify results**:
   - User tier should be: `premium`
   - Shop credit should be: `K200`
   - LGR qualified: `Yes` (if other requirements met)
   - Wallet balance: `-K1,000` (loan spent)

### Verification Query

```php
$user = User::find(35);
$purchase = StarterKitPurchaseModel::where('user_id', 35)->latest()->first();

echo "Tier: " . $purchase->tier . "\n";
echo "Amount: K" . $purchase->amount . "\n";
echo "Shop Credit: K" . $user->starter_kit_shop_credit . "\n";
echo "LGR Qualified: " . ($user->lgr_qualified ? 'Yes' : 'No') . "\n";
```

## Impact

### Before Fix
- ❌ Users charged K1,000 but received K100 shop credit
- ❌ Users missing LGR qualification
- ❌ Users missing premium benefits
- ❌ K900 value loss per affected user

### After Fix
- ✅ Users charged K1,000 receive K200 shop credit
- ✅ Users get LGR qualification
- ✅ Users get all premium benefits
- ✅ Correct value delivered

## Related Files

- `app/Infrastructure/Persistence/Eloquent/StarterKit/StarterKitPurchaseModel.php` - Main fix
- `app/Services/StarterKitService.php` - Enhanced logging
- `app/Http/Controllers/MyGrowNet/StarterKitController.php` - Request logging
- `database/migrations/2025_11_01_000000_add_tier_to_starter_kit_purchases.php` - Migration with default value

## Prevention

To prevent similar issues in the future:

1. **Always check `$fillable` array** when adding new fields
2. **Add comprehensive logging** for critical operations
3. **Test both tiers** when making changes to starter kit system
4. **Review database defaults** - they can mask missing fillable fields

## Affected Users

Users who purchased between the tier feature launch and this fix may need their accounts corrected. Run:

```php
// Find affected users
$affected = StarterKitPurchaseModel::where('tier', 'basic')
    ->where('amount', 1000)
    ->get();

// Fix each user
foreach ($affected as $purchase) {
    DB::transaction(function () use ($purchase) {
        $purchase->update(['tier' => 'premium']);
        $purchase->user->update([
            'starter_kit_tier' => 'premium',
            'starter_kit_shop_credit' => 200,
        ]);
    });
}
```

---

**Fix Verified:** ✅  
**Ready for Production:** ✅
