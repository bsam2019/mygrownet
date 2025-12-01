# Mobile Earnings Breakdown ✅

**Date:** November 9, 2025  
**Status:** ✅ Complete - Earnings Breakdown Added to Wallet Tab

---

## Overview

Added a comprehensive earnings breakdown section to the wallet tab showing detailed income sources including referral commissions, LGR profit sharing, and team performance earnings.

---

## Features Implemented

### ✅ Earnings Categories

#### 1. Referral Commissions
- **Source:** 7-level network earnings
- **Icon:** Users icon (blue)
- **Description:** Commissions from direct and indirect referrals
- **Calculation:** All paid referral commissions across all 7 levels

#### 2. LGR Profit Sharing
- **Source:** Quarterly distributions
- **Icon:** Chart bar icon (green)
- **Description:** Loyalty Growth Reward profit sharing
- **Calculation:** All paid profit shares from LGR program

#### 3. Team Performance
- **Source:** Purchases & subscriptions
- **Icon:** Trophy icon (purple)
- **Description:** Earnings from team member activities
- **Calculation:** Commissions from team purchases, subscriptions, and starter kits

#### 4. Pending Earnings
- **Source:** Awaiting approval
- **Icon:** Clock icon (amber)
- **Description:** Earnings not yet approved
- **Calculation:** Sum of pending commissions and profit shares
- **Display:** Only shown if > 0

---

## Visual Design

```
┌─────────────────────────────────────┐
│ Earnings Breakdown                  │
├─────────────────────────────────────┤
│ 👥 Referral Commissions             │
│    7-level network earnings         │
│                          K1,250.00  │
├─────────────────────────────────────┤
│ 📊 LGR Profit Sharing               │
│    Quarterly distributions          │
│                            K500.00  │
├─────────────────────────────────────┤
│ 🏆 Team Performance                 │
│    Purchases & subscriptions        │
│                            K350.00  │
├─────────────────────────────────────┤
│ ⏰ Pending Earnings                 │
│    Awaiting approval                │
│                            K150.00  │
├─────────────────────────────────────┤
│ Total Earnings          K2,100.00   │
├─────────────────────────────────────┤
│ ℹ️ Earnings are automatically added │
│    to your wallet balance when      │
│    approved.                        │
└─────────────────────────────────────┘
```

---

## Component Structure

```vue
EarningsBreakdown.vue
├── Header ("Earnings Breakdown")
├── Earnings List
│   ├── Referral Commissions (blue gradient)
│   ├── LGR Profit Sharing (green gradient)
│   ├── Team Performance (purple gradient)
│   └── Pending Earnings (amber gradient, conditional)
├── Total Section (border-top)
└── Info Note (blue background)
```

---

## Data Structure

### Backend (DashboardController)

```php
private function getEarningsBreakdown(User $user): array
{
    return [
        'referral_commissions' => (float) $referralCommissions,
        'profit_shares' => (float) $profitShares,
        'team_performance' => (float) $teamPerformance,
        'pending_earnings' => (float) $pendingEarnings,
        'total_earnings' => (float) $totalEarnings,
    ];
}
```

### Frontend (Props)

```typescript
interface Earnings {
  referral_commissions: number;
  profit_shares: number;
  team_performance: number;
  pending_earnings: number;
  total_earnings: number;
}
```

---

## Calculation Logic

### Referral Commissions
```php
$referralCommissions = $user->referralCommissions()
    ->where('status', 'paid')
    ->sum('amount');
```
- All levels (1-7)
- Only paid commissions
- Includes direct and indirect referrals

### LGR Profit Sharing
```php
$profitShares = $user->profitShares()
    ->where('status', 'paid')
    ->sum('amount');
```
- Quarterly LGR distributions
- Only paid profit shares
- Premium tier members only

### Team Performance
```php
$teamPerformance = DB::table('referral_commissions')
    ->where('referrer_id', $user->id)
    ->where('status', 'paid')
    ->whereIn('source', ['subscription', 'product_purchase', 'starter_kit'])
    ->sum('amount');
```
- Team member subscriptions
- Team member product purchases
- Team member starter kit purchases

### Pending Earnings
```php
$pendingEarnings = $user->referralCommissions()
    ->where('status', 'pending')
    ->sum('amount') +
    $user->profitShares()
    ->where('status', 'pending')
    ->sum('amount');
```
- Pending commissions
- Pending profit shares
- Awaiting admin approval

---

## Integration

### Mobile Dashboard - Wallet Tab

```vue
<!-- Earnings Breakdown -->
<EarningsBreakdown
  v-if="earningsBreakdown"
  :earnings="earningsBreakdown"
/>
```

### Position
- Displayed at the top of wallet tab
- Above "Quick Stats" (deposits/withdrawals)
- Below balance overview card

---

## Color Scheme

### Referral Commissions
- **Background:** `from-blue-50 to-indigo-50`
- **Icon BG:** `bg-blue-100`
- **Icon Color:** `text-blue-600`
- **Amount Color:** `text-blue-600`

### LGR Profit Sharing
- **Background:** `from-green-50 to-emerald-50`
- **Icon BG:** `bg-green-100`
- **Icon Color:** `text-green-600`
- **Amount Color:** `text-green-600`

### Team Performance
- **Background:** `from-purple-50 to-indigo-50`
- **Icon BG:** `bg-purple-100`
- **Icon Color:** `text-purple-600`
- **Amount Color:** `text-purple-600`

### Pending Earnings
- **Background:** `from-amber-50 to-yellow-50`
- **Border:** `border-amber-200`
- **Icon BG:** `bg-amber-100`
- **Icon Color:** `text-amber-600`
- **Amount Color:** `text-amber-600`

---

## User Experience

### Benefits
- ✅ Clear visibility of income sources
- ✅ Easy to understand breakdown
- ✅ Visual distinction between categories
- ✅ Pending earnings highlighted
- ✅ Total earnings prominently displayed

### Information Hierarchy
1. **Most Important:** Total earnings (bottom, bold)
2. **Primary:** Individual category amounts (right-aligned, bold)
3. **Secondary:** Category names and descriptions
4. **Tertiary:** Info note about automatic wallet addition

---

## Files Created/Modified

### Created
1. `resources/js/Components/Mobile/EarningsBreakdown.vue`
   - Complete earnings breakdown component
   - ~150 lines of code
   - Fully styled with gradients

### Modified
1. `app/Http/Controllers/MyGrowNet/DashboardController.php`
   - Added `getEarningsBreakdown()` method
   - Added earnings data to mobile dashboard
   - Calculates all earning categories

2. `resources/js/pages/MyGrowNet/MobileDashboard.vue`
   - Added EarningsBreakdown import
   - Added earningsBreakdown prop
   - Integrated component in wallet tab

---

## Testing Checklist

- [x] Component displays correctly
- [x] All earnings categories show
- [x] Amounts format correctly (2 decimals)
- [x] Pending earnings only show when > 0
- [x] Total calculates correctly
- [x] Colors match design system
- [x] Icons display properly
- [x] Responsive on all screen sizes
- [x] Info note is readable
- [x] Backend calculations are accurate

---

## Future Enhancements

### Phase 1 (Current)
- ✅ Basic earnings breakdown
- ✅ Four main categories
- ✅ Pending earnings display
- ✅ Total calculation

### Phase 2 (Planned)
- [ ] Click to view detailed breakdown
- [ ] Monthly earnings trend
- [ ] Earnings history chart
- [ ] Export earnings report

### Phase 3 (Future)
- [ ] Real-time earnings updates
- [ ] Earnings projections
- [ ] Comparison with previous months
- [ ] Achievement-based bonuses display

---

## Notes

- Team performance currently includes subscriptions, product purchases, and starter kits
- LGR profit sharing only available to Premium tier members
- Pending earnings require admin approval before being added to wallet
- All amounts are displayed in Zambian Kwacha (K)

---

## Success Metrics

### User Engagement
- Time spent on wallet tab
- Earnings breakdown views
- User understanding of income sources

### Business
- Increased transparency
- Better user retention
- Higher upgrade conversion (to Premium for LGR)

---

**Result:** Mobile users can now see a clear, detailed breakdown of their earnings from all sources! 🎉💰📱
