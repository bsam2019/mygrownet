# LGR Naming Confusion & Recommendations

## Current Problem

### Confusing Database Field Name

**Field:** `users.loyalty_points`  
**Actual Content:** Currency amount in Kwacha (K)  
**Display:** K1,500 (not 1,500 points)  
**Problem:** The word "points" suggests a non-monetary reward system, but it's actually CURRENCY

### What LGR Actually Is

**LGR = Loyalty Growth Reward**
- Rewards are called **"Loyalty Growth Credits (LGC)"**
- Measured in **Kwacha (K)** - real currency
- Can be withdrawn as cash (up to 40%)
- Can be used on platform (100%)
- NOT points - it's MONEY

### Separate Points System

The platform DOES have actual points:
- **LP (Lifetime Points)** - For professional level progression
- **MAP (Monthly Activity Points)** - For monthly qualification
- These are TRUE points (not currency)

## Current Implementation

### Database Fields

```sql
users table:
├── wallet_balance (decimal) - Main wallet in Kwacha
├── bonus_balance (decimal) - Bonus credits in Kwacha  
├── loyalty_points (decimal) - LGR credits in Kwacha ❌ MISLEADING NAME
├── lifetime_points (int) - Actual LP points ✓
└── monthly_activity_points (int) - Actual MAP points ✓
```

### Display Issues

**Main Dashboard:**
- ❌ No wallet balance shown
- ❌ No LGR balance shown
- ❌ No bonus balance shown
- ✓ Only shows "Total Earnings" (commissions + profit shares)

**LGR Dashboard:**
- ✓ Shows LGR balance correctly as K1,500
- ✓ Calls it "Loyalty Credits Balance"
- ✓ Displays in Kwacha

**My Earnings Page:**
- ❌ Not implemented (shows TODO with 0 values)
- ❌ Should show all balance types

## Recommendations

### 1. Rename Database Field (Breaking Change)

```sql
ALTER TABLE users 
CHANGE COLUMN loyalty_points lgr_balance DECIMAL(10,2);
```

**Pros:**
- Clear naming
- No confusion with actual points
- Self-documenting code

**Cons:**
- Breaking change
- Need to update all references
- Migration required

### 2. Add Clarifying Comments (Non-Breaking)

```php
// In User model
/**
 * LGR Balance (Loyalty Growth Credits) in Kwacha
 * Note: Despite the field name "loyalty_points", this stores CURRENCY not points
 * @var float
 */
protected $casts = [
    'loyalty_points' => 'decimal:2', // LGR balance in Kwacha (K)
];
```

### 3. Consistent Terminology

**Use everywhere:**
- "LGR Balance" or "Loyalty Credits"
- Display as "K1,500" (with currency symbol)
- Never call it "points" in UI
- Distinguish from LP/MAP points

### 4. Add Unified Balance Display

Create a wallet overview component showing:

```
┌─────────────────────────────────────┐
│      MY MYGROWNET WALLET            │
├─────────────────────────────────────┤
│                                     │
│  💰 Main Balance                    │
│  K5,000.00                          │
│  From commissions & profit shares   │
│                                     │
│  ⭐ Loyalty Credits (LGR)           │
│  K1,500.00                          │
│  From loyalty program               │
│  └─ Up to K600 withdrawable (40%)   │
│                                     │
│  🎁 Bonus Credits                   │
│  K200.00                            │
│  Platform use only                  │
│                                     │
│  ─────────────────────────────────  │
│  Total Available: K6,700.00         │
└─────────────────────────────────────┘
```

## Immediate Actions

### Quick Wins (No Breaking Changes)

1. **Add Balance Cards to Dashboard**
   - Show all three balances clearly
   - Use icons to distinguish them
   - Add tooltips explaining each

2. **Implement My Earnings Page**
   - Complete the TODO implementation
   - Show detailed breakdown
   - Clarify LGR vs Points

3. **Update UI Labels**
   - Change "Loyalty Points" → "Loyalty Credits"
   - Always show currency symbol (K)
   - Add "(LGR)" label where needed

4. **Add Documentation**
   - Member guide explaining balances
   - FAQ about LGR vs Points
   - Withdrawal rules clearly stated

### Long-term (Consider for v2)

1. **Rename Database Field**
   - `loyalty_points` → `lgr_balance`
   - Update all code references
   - Migration script

2. **Unified Wallet Service**
   - Single service for all balance types
   - Consistent API
   - Better reporting

3. **Balance History**
   - Track all LGR transactions
   - Show earning/spending history
   - Export capability

## Code Examples

### Current (Confusing)

```php
// What it looks like
$user->loyalty_points; // Sounds like points

// What it actually is
$user->loyalty_points; // Currency in Kwacha!
```

### Recommended

```php
// Option 1: Rename field
$user->lgr_balance; // Clear it's a balance

// Option 2: Add accessor
public function getLgrBalanceAttribute()
{
    return $this->loyalty_points; // Bridge old to new
}

// Option 3: Use constant
const LGR_BALANCE_FIELD = 'loyalty_points';
$user->{User::LGR_BALANCE_FIELD};
```

## Summary

**Problem:** Field named `loyalty_points` contains currency, not points  
**Impact:** Confusing for developers and potentially members  
**Solution:** Rename field OR add clear documentation + UI improvements  
**Priority:** Medium (not breaking functionality, but causes confusion)  

**Immediate Fix:** Add balance display to dashboard + implement My Earnings page  
**Long-term Fix:** Consider renaming field in major version update
