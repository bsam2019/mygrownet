# MyGrowNet Quick Reference

## Balance Types

### 💰 Currency (Kwacha)

| Balance | Display | Withdrawable | Use |
|---------|---------|--------------|-----|
| **Main Wallet** | K5,000.00 | 100% | Commissions + Profit Shares |
| **LGR Balance** | K1,500.00 | 40% | Loyalty Growth Reward |
| **Bonus Balance** | K200.00 | 0% | Promotional credits |

### 📊 Points (Not Currency)

| Points | Display | Expires | Purpose |
|--------|---------|---------|---------|
| **LP** (Lifetime Points) | 1,500 LP | Never | Level advancement |
| **BP** (Bonus Points) | 800 BP | Monthly | Qualification |

## Key Differences

### LGR Balance vs LP

| Feature | LGR Balance | LP (Lifetime Points) |
|---------|-------------|---------------------|
| **Type** | Currency (K) | Points |
| **Display** | K1,500.00 | 1,500 LP |
| **Field** | `users.loyalty_points` | `user_points.lifetime_points` |
| **Withdraw** | Up to 40% | No (not currency) |
| **Purpose** | Reward credits | Level progression |

## Member Pages

### My Wallet (`/mygrownet/wallet`)
- Shows all currency balances
- Recent transactions
- Withdrawal interface
- Top-up options

### My Earnings (`/mygrownet/my-earnings`)
- Total earnings breakdown
- This month earnings
- Pending earnings
- LGR rewards
- Commission history
- Profit shares

### LGR Dashboard (`/mygrownet/loyalty-reward`)
- Active cycle status
- Daily activity tracking
- Qualification progress
- LGR balance

## Admin Features

### LGR Manual Awards
**Access:** Admin → LGR Management → Manual Awards

**Award Amounts:**
- Minimum: K10
- Maximum: K2,100
- Recommended: K500 for early adopters

**Award Types:**
- Early Adopter
- Performance
- Marketing
- Special

## Display Rules

### ✅ Correct

```vue
<!-- Currency -->
<p>K{{ amount.toFixed(2) }}</p>

<!-- Points -->
<p>{{ points }} LP</p>
<p>{{ points }} BP</p>

<!-- LGR (it's currency!) -->
<p>K{{ lgrBalance.toFixed(2) }}</p>
```

### ❌ Incorrect

```vue
<!-- Don't show LGR as points -->
<p>{{ lgrBalance }} pts</p>

<!-- Don't show points as currency -->
<p>K{{ lifetimePoints }}</p>

<!-- Don't mix terminology -->
<p>{{ loyaltyPoints }} LP</p> <!-- Ambiguous! -->
```

## Database Fields

```sql
-- Currency fields (decimal)
users.wallet_balance
users.bonus_balance
users.loyalty_points  -- ⚠️ Actually LGR currency!

-- Points fields (integer)
user_points.lifetime_points  -- LP
user_points.monthly_points   -- BP
```

## Common Mistakes

### ❌ Mistake 1: Treating LGR as Points
```vue
<p>Loyalty Points: {{ user.loyalty_points }} pts</p>
```

### ✅ Correct:
```vue
<p>LGR Balance: K{{ user.loyalty_points.toFixed(2) }}</p>
```

### ❌ Mistake 2: Confusing LP and LGR
- LP = Lifetime Points (for levels)
- LGR = Loyalty Growth Reward (currency)

### ✅ Correct:
- "You have 1,500 LP for level advancement"
- "Your LGR balance is K1,500.00"

## Quick Formulas

### Total Available Balance
```
Total = Main Wallet + LGR Balance + Bonus Balance
```

### Withdrawable Amount
```
Withdrawable = Main Wallet (100%) + LGR Balance (40%)
```

### Monthly Qualification
```
Qualified = BP >= Required BP for Level
```

## Support

**Documentation:**
- Technical: `docs/LGR_MANUAL_AWARDS.md`
- Admin Guide: `docs/LGR_MANUAL_AWARDS_QUICKSTART.md`
- Terminology: `docs/POINTS_TERMINOLOGY_CORRECTION.md`
- Complete Fixes: `docs/COMPLETE_FIXES_SUMMARY.md`

**Key Files:**
- Wallet: `resources/js/pages/MyGrowNet/Wallet.vue`
- Earnings: `resources/js/pages/MyGrowNet/MyEarnings.vue`
- LGR Dashboard: `resources/js/pages/MyGrowNet/LoyaltyReward/Dashboard.vue`
