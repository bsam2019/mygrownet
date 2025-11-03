# Points System - Correct Terminology

## Official Points System

MyGrowNet uses a **dual-points system**:

### 1. LP (Lifetime Points)
- **Purpose:** Professional level advancement (Associate → Ambassador)
- **Persistence:** Never expire, accumulate forever
- **Storage:** `user_points.lifetime_points`
- **Display:** "1,500 LP"
- **Earning:** Network building, product sales, education, achievements

### 2. BP (Bonus Points)
- **Purpose:** Monthly qualification for commissions and bonuses
- **Persistence:** Reset to 0 on the 1st of each month
- **Storage:** `user_points.monthly_points` or `bonus_points`
- **Display:** "800 BP"
- **Earning:** Platform activity, engagement, purchases, referrals

## Important: LGR is NOT Points!

### LGR Balance (Confusing Field Name)

**Database field:** `users.loyalty_points`  
**Actual content:** Currency in Kwacha (NOT points!)  
**Display:** K1,500.00 (with currency symbol)  
**Purpose:** Loyalty Growth Reward credits from 70-day program

**The Problem:**
- Field is named `loyalty_points` but contains CURRENCY
- Should be displayed as K1,500.00, not "1500 pts"
- Completely separate from LP/BP points system

## Complete System Overview

```
┌─────────────────────────────────────────────────┐
│           MYGROWNET REWARD SYSTEMS              │
├─────────────────────────────────────────────────┤
│                                                 │
│  💰 CURRENCY (Kwacha)                          │
│  ├─ Main Wallet Balance                        │
│  ├─ Bonus Balance (promotional)                │
│  └─ LGR Balance (loyalty_points field) ⚠️      │
│                                                 │
│  📊 POINTS (Not Currency)                      │
│  ├─ LP (Lifetime Points) - Level advancement  │
│  └─ BP (Bonus Points) - Monthly qualification │
│                                                 │
└─────────────────────────────────────────────────┘
```

## Correct Terminology Usage

### ✅ Correct

- "You have 1,500 LP" (Lifetime Points)
- "You earned 200 BP this month" (Bonus Points)
- "Your LGR balance is K1,500.00" (currency)
- "Wallet balance: K5,000.00" (currency)

### ❌ Incorrect

- "You have 1500 loyalty points" (ambiguous - LP or LGR?)
- "LGR: 1500 pts" (it's currency, not points!)
- "Monthly Activity Points" (old term, use BP)
- "Loyalty points: K1,500" (mixing points and currency)

## Database Fields Summary

| Field | Table | Type | Display | Purpose |
|-------|-------|------|---------|---------|
| `lifetime_points` | user_points | Integer | 1,500 LP | Level advancement |
| `monthly_points` | user_points | Integer | 800 BP | Monthly qualification |
| `loyalty_points` | users | Decimal | K1,500.00 | LGR currency ⚠️ |
| `bonus_balance` | users | Decimal | K200.00 | Bonus currency |
| `wallet_balance` | users | Decimal | K5,000.00 | Main currency |

## Monthly Qualification System

**How BP Works:**
1. Member earns BP through platform activities
2. Each professional level has minimum BP requirement
3. Must meet BP threshold to receive monthly commissions
4. BP resets to 0 on 1st of each month
5. Start earning again for next month

**BP Requirements by Level:**
- Associate: 100 BP
- Professional: 200 BP
- Senior: 300 BP
- Manager: 400 BP
- Director: 500 BP
- Executive: 600 BP
- Ambassador: 800 BP

## Implementation Notes

### Display Guidelines

**For LP:**
```vue
<p>{{ user.lifetime_points }} LP</p>
```

**For BP:**
```vue
<p>{{ user.bonus_points }} BP</p>
```

**For LGR (currency!):**
```vue
<p>K{{ user.loyalty_points.toFixed(2) }}</p>
<!-- NOT: {{ user.loyalty_points }} pts -->
```

### API Responses

```json
{
  "points": {
    "lifetime_points": 1500,
    "bonus_points": 800
  },
  "balances": {
    "wallet": 5000.00,
    "lgr": 1500.00,
    "bonus": 200.00
  }
}
```

## Summary

**Two Points Systems:**
1. **LP** - Lifetime Points (never expire)
2. **BP** - Bonus Points (reset monthly)

**LGR is Currency:**
- Despite field name `loyalty_points`
- Display as K1,500.00
- Not part of points system

**Key Rule:**
- Points = LP or BP (integers, no currency symbol)
- Currency = K amount (decimals, with K symbol)
- Never mix the two!
