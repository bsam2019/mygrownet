# Starter Kit Points and Commission Logic

**Last Updated:** November 6, 2025  
**Status:** Implemented

## Quick Reference

### K500 Basic Tier Registration
- **Member receives:** 25 LP
- **Uplines receive:** Commissions based on K500
- **Shop credit:** K100

### K1000 Premium Tier Registration
- **Member receives:** 50 LP (full points for their K1000 investment)
- **Uplines receive:** Commissions based on K500 only (extra K500 is for premium content)
- **Shop credit:** K200
- **Extra benefits:** LGR access, priority support

### K500 → K1000 Upgrade (Basic to Premium)
- **Member receives:** 25 LP (additional points)
- **Uplines receive:** NO commissions (upgrade is for content only)
- **Shop credit:** +K100 (total K200)
- **Extra benefits:** LGR access, priority support

## Rationale

### Why Premium members get 50 LP but uplines only get K500 commissions?

**Member Perspective:**
- They invested K1000, so they deserve full recognition (50 LP)
- Their points reflect their commitment level
- Fair reward for choosing Premium tier

**Upline Perspective:**
- The extra K500 is for premium content/benefits, not a referral bonus
- Prevents gaming the system (encouraging Premium just for commissions)
- Keeps commission structure sustainable
- Uplines still benefit from member's success through LGR profit sharing

### Why upgrades give 25 LP but no commissions?

**Member Perspective:**
- They're investing an additional K500, so they get 25 LP
- Encourages members to upgrade for better benefits
- Rewards their continued commitment

**Upline Perspective:**
- The upgrade is a personal choice for content, not a new referral
- Commissions were already paid on the initial K500 registration
- Prevents double-dipping on commissions
- Uplines benefit indirectly through member's increased engagement

## Implementation Details

### Files Modified

1. **app/Services/StarterKitService.php**
   - `awardRegistrationBonus()`: Awards 25 LP for Basic, 50 LP for Premium
   - `processStarterKitCommissions()`: Always uses K500 as commission base

2. **app/Http/Controllers/MyGrowNet/StarterKitController.php**
   - `processUpgrade()`: Awards 25 LP to member, no commissions to uplines

### Commission Calculation

```php
// For ALL tiers (Basic K500 or Premium K1000)
$commissionableAmount = 500.00;

// Level 1: 10% of K500 = K50
// Level 2: 5% of K500 = K25
// Level 3: 3% of K500 = K15
// ... and so on
```

### Point Calculation

```php
// Registration
$lpAmount = $tier === 'premium' ? 50 : 25;

// Upgrade (only if upgrading from Basic to Premium)
$lpAmount = 25; // Additional points
```

## Testing Scenarios

### Scenario 1: New Basic Registration (K500)
- ✅ Member gets 25 LP
- ✅ Uplines get commissions on K500
- ✅ Member gets K100 shop credit

### Scenario 2: New Premium Registration (K1000)
- ✅ Member gets 50 LP
- ✅ Uplines get commissions on K500 only
- ✅ Member gets K200 shop credit
- ✅ Member gets LGR access

### Scenario 3: Upgrade from Basic to Premium (K500 upgrade fee)
- ✅ Member gets 25 LP (additional)
- ✅ Uplines get NO commissions
- ✅ Member gets +K100 shop credit (total K200)
- ✅ Member gets LGR access

### Scenario 4: Total LP for Premium via Upgrade Path
- Initial Basic: 25 LP
- Upgrade to Premium: +25 LP
- **Total: 50 LP** (same as direct Premium registration)

## Data Correction

A script is available to fix any existing incorrect data:

```bash
php scripts/fix-premium-tier-points-commissions.php
```

The script will:
1. Ensure Premium members have 50 LP from purchase
2. Ensure upgrade members have 25 LP from upgrade
3. Correct upline commissions to K500 base
4. Create correction withdrawals for excess commissions already paid

## Communication Points

When explaining to members:

**For Premium Registration:**
- "You'll receive 50 LP for your K1000 investment"
- "Your uplines will receive standard referral bonuses"
- "You get double shop credit and LGR access"

**For Upgrades:**
- "You'll receive 25 LP for upgrading"
- "Upgrade to unlock LGR profit sharing"
- "Get double shop credit and priority support"

**For Uplines:**
- "Referral bonuses are based on the K500 base registration"
- "Premium members contribute more to LGR profit pool"
- "Your network's success benefits you through profit sharing"

## Future Considerations

- Monitor Premium adoption rate
- Track upgrade conversion rate
- Analyze LGR value vs commission structure
- Consider seasonal promotions for Premium tier
- Evaluate if upgrade LP bonus drives conversions
