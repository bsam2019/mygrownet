# LGR vs Profit Sharing - Clarification & Fix

**Date:** November 9, 2025  
**Status:** ✅ Fixed

---

## The Confusion

**LGR (Loyalty Growth Reward)** and **Profit Sharing** are TWO DIFFERENT things!

### LGR (Loyalty Growth Reward)
- **What:** Loyalty points system (separate balance)
- **Stored in:** `users.loyalty_points` column
- **Display:** K1,500.00 (currency format)
- **Withdrawal:** Only 40% can be withdrawn as cash
- **Use:** 100% can be used on platform
- **NOT earnings** - It's a separate loyalty reward balance

### Quarterly Profit Sharing
- **What:** Actual profit distributions from community projects
- **Stored in:** `profit_shares` table
- **Display:** Part of earnings breakdown
- **Withdrawal:** 100% withdrawable (part of wallet)
- **Source:** Company's empowerment project profits

---

## What Was Wrong

### Before ❌
```
Earnings Breakdown:
- Referral Commissions
- LGR Profit Sharing ← WRONG! LGR is not profit sharing
- Team Performance
```

### After ✅
```
Earnings Breakdown:
- Referral Commissions
- Quarterly Profit Sharing ← CORRECT! This is actual profit distributions
- Team Performance
```

---

## The Fix

### Changed in EarningsBreakdown.vue

**Before:**
```vue
<p class="text-sm font-semibold text-gray-900">LGR Profit Sharing</p>
<p class="text-xs text-gray-500">Quarterly distributions</p>
```

**After:**
```vue
<p class="text-sm font-semibold text-gray-900">Quarterly Profit Sharing</p>
<p class="text-xs text-gray-500">Project profit distributions</p>
```

---

## Correct Understanding

### Three Separate Balances

```
┌─────────────────────────────────────┐
│  1. MAIN WALLET                     │
│     - Commissions                   │
│     - Profit Shares                 │
│     - Deposits                      │
│     - 100% withdrawable             │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  2. LGR BALANCE (Loyalty Points)    │
│     - Loyalty rewards               │
│     - Stored separately             │
│     - 40% withdrawable as cash      │
│     - 100% usable on platform       │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│  3. BONUS BALANCE                   │
│     - Promotional credits           │
│     - Platform use only             │
│     - NOT withdrawable              │
└─────────────────────────────────────┘
```

### Earnings Breakdown (Wallet)

```
Referral Commissions
  ↓ From 7-level network
  
Quarterly Profit Sharing
  ↓ From community project profits
  ↓ (NOT LGR!)
  
Team Performance
  ↓ From team purchases & subscriptions
```

---

## Data Flow

### Profit Sharing (Part of Wallet)

```
Company invests in projects
    ↓
Projects generate profit
    ↓
60% distributed to members
    ↓
Creates profit_shares records
    ↓
EarningsService includes in earnings
    ↓
WalletService includes in wallet balance
    ↓
100% withdrawable
```

### LGR (Separate Balance)

```
Member qualifies for LGR
    ↓
Admin awards LGR points
    ↓
Stored in users.loyalty_points
    ↓
Displayed separately
    ↓
40% withdrawable, 100% usable
```

---

## Services Verification

### EarningsService ✅
```php
private function getProfitShareEarnings(User $user): float
{
    $query = $user->profitShares()->where('status', 'paid');
    return (float) $query->sum('amount');
}
```
**Correct:** Gets actual profit share distributions

### DashboardController ✅
```php
private function getEarningsBreakdown(User $user): array
{
    $breakdown = $this->earningsService->getEarningsBreakdown($user);
    
    return [
        'referral_commissions' => $breakdown['commissions'],
        'profit_shares' => $breakdown['profit_shares'], // Actual profit shares
        'team_performance' => $teamPerformance,
        'pending_earnings' => $pendingEarnings,
        'total_earnings' => $total,
    ];
}
```
**Correct:** Uses EarningsService, returns actual profit shares

---

## Mobile Dashboard Display

### Earnings Breakdown Card

```
┌─────────────────────────────────────┐
│ Earnings Breakdown                  │
├─────────────────────────────────────┤
│ 👥 Referral Commissions             │
│    7-level network earnings         │
│                          K1,250.00  │
├─────────────────────────────────────┤
│ 📊 Quarterly Profit Sharing         │
│    Project profit distributions     │
│                            K500.00  │
├─────────────────────────────────────┤
│ 🏆 Team Performance                 │
│    Purchases & subscriptions        │
│                            K350.00  │
├─────────────────────────────────────┤
│ Total Earnings          K2,100.00   │
└─────────────────────────────────────┘
```

**Note:** LGR balance is NOT shown here because it's a separate balance, not earnings!

---

## Where LGR Should Be Shown

LGR should be displayed in:
1. **Wallet page** - As a separate balance card
2. **Profile/Account** - As loyalty points balance
3. **LGR section** - Dedicated LGR management page

LGR should NOT be in:
- ❌ Earnings breakdown
- ❌ Commission reports
- ❌ Wallet balance calculation

---

## Summary

**Fixed:**
- ✅ Changed "LGR Profit Sharing" to "Quarterly Profit Sharing"
- ✅ Updated description to "Project profit distributions"
- ✅ Verified earnings breakdown uses EarningsService
- ✅ Confirmed profit_shares are actual distributions, not LGR

**Clarified:**
- LGR = Separate loyalty points balance
- Profit Sharing = Actual earnings from projects
- They are completely different things!

---

## Files Modified

1. `resources/js/Components/Mobile/EarningsBreakdown.vue`
   - Changed label from "LGR Profit Sharing" to "Quarterly Profit Sharing"
   - Updated description

---

**The mobile dashboard now correctly shows "Quarterly Profit Sharing" instead of confusing it with LGR!** ✅
