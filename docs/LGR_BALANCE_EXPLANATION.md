# LGR Balance System - How It Works

## Current Implementation

### Storage Location

LGR (Loyalty Growth Reward) credits are stored in the **`loyalty_points`** column in the `users` table.

```sql
users table:
- wallet_balance (decimal) - Main wallet balance (calculated)
- bonus_balance (decimal) - Bonus/rewards (non-withdrawable)
- loyalty_points (decimal) - LGR credits (SEPARATE from main wallet)
```

### Key Point: **LGR is SEPARATE from Main Wallet**

## How LGR Works

### 1. Earning LGR Credits

**Normal 70-Day Cycle:**
- Member completes daily activity
- Earns K30 per active day
- Credits added to `loyalty_points` field
- Maximum K2,100 over 70 days

**Manual Awards (NEW):**
- Admin awards bonus directly
- Credits added to `loyalty_points` field
- Bypasses qualification requirements
- Range: K10 - K2,100

### 2. Where LGR Shows Up

**Member Dashboard:**
- LGR balance displayed separately
- Shows in "Loyalty Credits Balance" section
- NOT included in main wallet balance

**Location:** `/mygrownet/loyalty-reward`

### 3. Using LGR Credits

According to the policy, members can:

**100% Platform Use:**
- Buy products and subscriptions
- Pay for training and workshops
- Invest in Venture Builder projects
- Apply for Business Growth Fund

**Up to 40% Cash Conversion:**
- Withdraw up to 40% as cash
- Minimum withdrawal: K100
- Processing: 3-5 business days
- To mobile money or bank

## Main Wallet vs LGR Balance

### Main Wallet Balance

**Calculated from:**
```
Wallet Balance = Earnings - Expenses

Earnings:
- Referral commissions (paid)
- Profit shares
- Wallet topups

Expenses:
- Approved withdrawals
- Workshop purchases
- Starter kit purchases (via wallet)
- Transaction expenses
```

**NOT included:** `loyalty_points` or `bonus_balance`

### LGR Balance (loyalty_points)

**Separate field:**
- Stored in `users.loyalty_points`
- Displayed separately on LGR dashboard
- Has its own usage rules (40% cash limit)
- Tracked independently

### Bonus Balance (bonus_balance)

**Another separate field:**
- Stored in `users.bonus_balance`
- Promotional rewards
- Cannot be withdrawn as cash
- Only for platform purchases

## Visual Representation

```
┌─────────────────────────────────────┐
│         MEMBER BALANCES             │
├─────────────────────────────────────┤
│                                     │
│  Main Wallet Balance: K5,000        │
│  ├─ Commissions                     │
│  ├─ Profit Shares                   │
│  └─ Topups                          │
│                                     │
│  ─────────────────────────────────  │
│                                     │
│  LGR Balance: K1,500 ⭐             │
│  (Loyalty Growth Reward)            │
│  └─ 40% withdrawable (K600)         │
│                                     │
│  ─────────────────────────────────  │
│                                     │
│  Bonus Balance: K200 🎁             │
│  (Platform use only)                │
│                                     │
└─────────────────────────────────────┘
```

## Code Implementation

### When LGR is Earned

```php
// In LgrCycleService.php
private function creditLgcToWallet(int $userId, float $amount): void
{
    DB::table('users')
        ->where('id', $userId)
        ->increment('loyalty_points', $amount);
}
```

### When Manual Award is Given

```php
// In LgrManualAwardController.php
$user->increment('loyalty_points', $validated['amount']);
```

### Displaying LGR Balance

```vue
<!-- In LoyaltyReward/Dashboard.vue -->
<p class="text-3xl font-bold text-blue-600">
  K{{ Number(user.loyalty_points || 0).toFixed(2) }}
</p>
```

## Why Separate?

### Benefits of Separate LGR Balance

1. **Clear Tracking** - Easy to see LGR earnings vs other income
2. **Usage Rules** - Can enforce 40% cash withdrawal limit
3. **Reporting** - Separate analytics for LGR program
4. **Compliance** - Clear distinction between reward types
5. **Member Understanding** - Members see exactly what they earned from LGR

### Alternative: Combined Balance

If you wanted to combine them, you would:
- Add `loyalty_points` to main wallet calculation
- Remove separate LGR dashboard display
- Lose ability to enforce 40% limit
- Harder to track LGR program success

## Recommendation

**Keep LGR separate** for these reasons:

✅ **Transparency** - Members see exactly what they earned from LGR  
✅ **Control** - You can enforce usage rules (40% limit)  
✅ **Analytics** - Track LGR program performance  
✅ **Flexibility** - Can change rules without affecting main wallet  
✅ **Marketing** - Highlight LGR as special benefit  

## Member Experience

### What Members See

**Dashboard:**
- Main wallet balance (commissions, profit shares)
- LGR balance (shown separately with star icon ⭐)
- Bonus balance (promotional rewards)

**LGR Dashboard:**
- Current LGR balance
- Active cycle progress
- Recent activities
- Earnings projection

**Withdrawal:**
- Can withdraw from main wallet (100%)
- Can withdraw from LGR (up to 40%)
- Bonus balance not withdrawable

## Future Considerations

### Potential Enhancements

1. **Unified View** - Show all balances in one place
2. **Conversion Tool** - Easy way to convert LGR to cash
3. **Usage History** - Track how LGR was spent
4. **Auto-Convert** - Option to auto-convert LGR to main wallet
5. **Tiered Limits** - Higher withdrawal % for higher tiers

### Current Status

- ✅ LGR stored in `loyalty_points`
- ✅ Displayed separately
- ✅ Manual awards working
- ✅ Normal cycle working
- ⚠️ Withdrawal integration needed (40% limit enforcement)
- ⚠️ Platform purchase integration needed

## Summary

**LGR Balance:**
- Stored in `users.loyalty_points`
- Separate from main wallet
- Displayed on LGR dashboard
- Subject to 40% cash withdrawal limit
- Can be used 100% on platform

**Main Wallet:**
- Calculated from commissions, profit shares, topups
- Does NOT include loyalty_points
- 100% withdrawable
- Used for general transactions

**This separation provides clarity, control, and better member experience.**
