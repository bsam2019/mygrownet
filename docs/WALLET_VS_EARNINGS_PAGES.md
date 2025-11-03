# Wallet vs My Earnings Pages - Current State & Issues

## Current Situation

### Two Separate Links in Member Sidebar

**Finance Section:**
1. **"My Wallet"** → `/mygrownet/wallet` (✅ Implemented)
2. **"My Earnings"** → `/mygrownet/my-earnings` (❌ Not Fully Implemented)

## Page Analysis

### 1. My Wallet Page (`/mygrownet/wallet`)

**Status:** ✅ Fully Implemented

**What It Shows:**
```
┌─────────────────────────────────────┐
│      Available Balance              │
│      K5,000.00                      │
│                                     │
│  Bonus Balance    │  Loyalty Points │
│  K200.00          │  1500 pts  ❌   │
└─────────────────────────────────────┘
```

**Data Displayed:**
- `balance` - Main wallet (from WalletService calculation)
- `bonusBalance` - Bonus credits (K)
- `loyaltyPoints` - **DISPLAYED AS "pts" BUT IT'S CURRENCY!** ❌
- Recent transactions
- Withdrawal limits
- Earnings breakdown

**CRITICAL ISSUE:**
```vue
<!-- Line 343 in Wallet.vue -->
<p class="text-xs text-blue-100">Loyalty Points</p>
<p class="text-sm font-semibold">{{ loyaltyPoints.toFixed(0) }} pts</p>
```

**Problem:** Shows "1500 pts" when it should show "K1,500.00" (it's LGR currency, not points!)

### 2. My Earnings Page (`/mygrownet/my-earnings`)

**Status:** ❌ Partially Implemented (Skeleton Only)

**What It Shows:**
```vue
// Hardcoded zeros - not connected to real data
const earningsData = {
    totalEarnings: 0,
    thisMonth: 0,
    pending: 0,
    lgrRewards: 0,
    commissions: 0,
    profitShares: 0
};
```

**Intended Purpose:**
- Detailed earnings breakdown
- LGR rewards tracking
- Commission history
- Profit share history
- Earnings analytics

**Current State:**
- Page exists but shows all zeros
- Has TODO comments
- Not connected to backend
- Beautiful UI design ready

## The Confusion

### Three Balance Types

1. **Main Wallet Balance** (`wallet_balance`)
   - Calculated: Commissions + Profit Shares + Topups - Withdrawals - Expenses
   - Displayed: ✅ Correctly as K5,000.00
   - 100% withdrawable

2. **Bonus Balance** (`bonus_balance`)
   - Promotional rewards
   - Displayed: ✅ Correctly as K200.00
   - NOT withdrawable (platform use only)

3. **LGR Balance** (`loyalty_points`)
   - Loyalty Growth Reward credits
   - Displayed: ❌ INCORRECTLY as "1500 pts"
   - Should be: K1,500.00
   - Up to 40% withdrawable

### Why the Confusion?

**Database Field Name:**
```sql
loyalty_points DECIMAL(10,2)  -- Misleading name!
```

**What It Actually Contains:**
- Currency amount in Kwacha (K)
- NOT points
- LGR credits = money

**What Members See:**
- Wallet page: "1500 pts" ❌ Wrong!
- LGR dashboard: "K1,500.00" ✅ Correct!
- Inconsistent display causes confusion

## Recommended Structure

### Option 1: Keep Both Pages (Recommended)

**My Wallet** - Transaction-focused
- Current balance (all types)
- Recent transactions
- Withdrawal interface
- Top-up interface
- Quick actions

**My Earnings** - Analytics-focused
- Earnings breakdown by source
- Historical trends
- Performance metrics
- Projections
- Detailed reports

### Option 2: Merge Into One

Combine both into single "My Wallet & Earnings" page with tabs:
- Tab 1: Balance & Transactions
- Tab 2: Earnings Analytics
- Tab 3: Withdrawal & Top-up

## Immediate Fixes Needed

### 1. Fix Wallet Page Display (CRITICAL)

**Current (Wrong):**
```vue
<p class="text-xs text-blue-100">Loyalty Points</p>
<p class="text-sm font-semibold">{{ loyaltyPoints.toFixed(0) }} pts</p>
```

**Should Be:**
```vue
<p class="text-xs text-blue-100">LGR Balance</p>
<p class="text-sm font-semibold">{{ formatCurrency(loyaltyPoints) }}</p>
```

### 2. Implement My Earnings Page

Connect to real data:
```php
// In EarningsController or add to WalletController
return Inertia::render('MyGrowNet/MyEarnings', [
    'totalEarnings' => $user->calculateTotalEarnings(),
    'thisMonth' => $this->getThisMonthEarnings($user),
    'pending' => $this->getPendingEarnings($user),
    'lgrRewards' => (float) $user->loyalty_points,
    'commissions' => $this->getTotalCommissions($user),
    'profitShares' => $this->getTotalProfitShares($user),
    'breakdown' => $this->getDetailedBreakdown($user),
]);
```

### 3. Add LGR Section to Wallet Page

Add a dedicated LGR card:
```vue
<!-- LGR Balance Card -->
<div class="bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl p-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-yellow-100 text-sm">LGR Balance</p>
            <h3 class="text-3xl font-bold">{{ formatCurrency(loyaltyPoints) }}</h3>
            <p class="text-xs text-yellow-100 mt-1">
                Up to {{ formatCurrency(loyaltyPoints * 0.4) }} withdrawable (40%)
            </p>
        </div>
        <StarIcon class="h-12 w-12 text-yellow-200" />
    </div>
    <Link :href="route('mygrownet.loyalty-reward.index')" 
          class="mt-4 text-sm text-yellow-100 hover:text-white">
        View LGR Dashboard →
    </Link>
</div>
```

### 4. Clarify Terminology Everywhere

**Consistent Labels:**
- ❌ "Loyalty Points" 
- ✅ "LGR Balance" or "Loyalty Credits"

**Always Show Currency:**
- ❌ "1500 pts"
- ✅ "K1,500.00"

**Add Tooltips:**
```vue
<Tooltip>
    <template #trigger>
        <InfoIcon class="h-4 w-4" />
    </template>
    <template #content>
        LGR (Loyalty Growth Reward) credits earned through 
        the 70-day activity program. Up to 40% withdrawable as cash.
    </template>
</Tooltip>
```

## User Journey

### Current (Confusing)

1. Member sees "My Wallet" - shows "1500 pts" ❌
2. Member sees "My Earnings" - shows all zeros ❌
3. Member goes to LGR dashboard - shows "K1,500.00" ✅
4. **Confusion:** Are points and LGR the same thing?

### Proposed (Clear)

1. Member sees "My Wallet" - shows all balances in K ✅
2. Member sees "My Earnings" - shows detailed breakdown ✅
3. Member goes to LGR dashboard - shows cycle details ✅
4. **Clear:** Everything is in Kwacha, LGR is a type of earning

## Summary

**Problems:**
1. ❌ Wallet page shows LGR as "pts" instead of currency
2. ❌ My Earnings page not implemented (shows zeros)
3. ❌ Inconsistent terminology (points vs credits vs balance)
4. ❌ Confusing database field name (`loyalty_points`)

**Solutions:**
1. ✅ Fix Wallet page to show LGR as K1,500.00
2. ✅ Implement My Earnings page with real data
3. ✅ Use consistent "LGR Balance" or "Loyalty Credits" everywhere
4. ✅ Add tooltips explaining each balance type
5. ✅ Consider renaming database field in future version

**Priority:**
1. **HIGH:** Fix Wallet page LGR display (quick fix)
2. **MEDIUM:** Implement My Earnings page (needs backend work)
3. **LOW:** Rename database field (breaking change, plan for v2)
