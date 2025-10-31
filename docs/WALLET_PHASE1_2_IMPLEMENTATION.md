# Wallet Phase 1 & 2 Implementation Guide

**Date:** October 31, 2025  
**Status:** In Progress

---

## Completed

### ✅ Phase 1 Items:
1. Wallet Policy Page created (`/wallet/policy`)
2. Route added
3. Policies hub updated

### ✅ Phase 2 Started:
1. Migration created for:
   - Policy acceptance tracking
   - Bonus/rewards balance
   - Verification levels
   - Daily withdrawal limits

---

## Next Steps (To Complete)

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Update User Model
Add to `app/Models/User.php`:
```php
protected $fillable = [
    // ... existing fields
    'wallet_policy_accepted',
    'wallet_policy_accepted_at',
    'bonus_balance',
    'loyalty_points',
    'verification_level',
    'verification_completed_at',
    'daily_withdrawal_used',
    'daily_withdrawal_reset_date',
];

protected $casts = [
    // ... existing casts
    'wallet_policy_accepted' => 'boolean',
    'wallet_policy_accepted_at' => 'datetime',
    'bonus_balance' => 'decimal:2',
    'loyalty_points' => 'decimal:2',
    'verification_completed_at' => 'datetime',
    'daily_withdrawal_used' => 'decimal:2',
    'daily_withdrawal_reset_date' => 'date',
];

// Add helper methods
public function getWithdrawalLimit(): float
{
    return match($this->verification_level) {
        'premium' => 50000,
        'enhanced' => 20000,
        'basic' => 5000,
        default => 5000,
    };
}

public function getRemainingDailyLimit(): float
{
    // Reset if new day
    if ($this->daily_withdrawal_reset_date != today()) {
        $this->update([
            'daily_withdrawal_used' => 0,
            'daily_withdrawal_reset_date' => today(),
        ]);
    }
    
    return $this->getWithdrawalLimit() - $this->daily_withdrawal_used;
}
```

### 3. Update Wallet Controller
Enhance `app/Http/Controllers/MyGrowNet/WalletController.php` to pass:
- `bonusBalance`
- `loyaltyPoints`
- `verificationLevel`
- `withdrawalLimit`
- `remainingDailyLimit`
- `policyAccepted`

### 4. Update Wallet Vue Page
Add to `resources/js/pages/MyGrowNet/Wallet.vue`:
- Policy acceptance modal (first-time users)
- Transaction limits card
- Bonus balance display
- Verification status badge
- Help & support section
- Policy link

### 5. Create Policy Acceptance Endpoint
```php
// routes/web.php
Route::post('/wallet/accept-policy', [WalletController::class, 'acceptPolicy'])
    ->name('wallet.accept-policy');

// WalletController.php
public function acceptPolicy(Request $request)
{
    $request->user()->update([
        'wallet_policy_accepted' => true,
        'wallet_policy_accepted_at' => now(),
    ]);
    
    return back()->with('success', 'Wallet policy accepted');
}
```

---

## Implementation Priority

1. **Run migration** ✅
2. **Update User model** (5 min)
3. **Update WalletController** (10 min)
4. **Update Wallet.vue** (20 min)
5. **Add policy acceptance route** (5 min)
6. **Test everything** (10 min)

**Total Time:** ~50 minutes

---

## Files to Modify

1. `app/Models/User.php` - Add fields and methods
2. `app/Http/Controllers/MyGrowNet/WalletController.php` - Pass new data
3. `resources/js/pages/MyGrowNet/Wallet.vue` - UI enhancements
4. `routes/web.php` - Add policy acceptance route

---

**Status:** Migration created, ready for implementation

