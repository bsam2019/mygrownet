# Starter Kit System

**Last Updated:** November 6, 2025  
**Status:** ✅ Complete

## Overview

Two-tier starter kit system with Basic (K500) and Premium (K1000) options, including progressive content unlocking, shop credits, and MLM commissions.

## Tiers

### Basic Tier (K500)
- **Price:** K500
- **Shop Credit:** K100 (90-day expiry)
- **Lifetime Points:** 25 LP
- **Library Access:** 30 days free
- **Content:** Progressive unlock over 30 days
- **MLM Commissions:** Uplines receive commissions on K500

### Premium Tier (K1000)
- **Price:** K1000
- **Shop Credit:** K200 (90-day expiry)
- **Lifetime Points:** 50 LP
- **Library Access:** 30 days free
- **Content:** All Basic content + premium materials
- **LGR Qualification:** Eligible for quarterly profit sharing
- **MLM Commissions:** Uplines receive commissions on K500 only (extra K500 is for premium content)

### Upgrade (Basic → Premium)
- **Upgrade Fee:** K500
- **Additional LP:** 25 LP (total 50 LP)
- **Shop Credit:** +K100 (total K200)
- **LGR Access:** Granted
- **MLM Commissions:** NO commissions to uplines (upgrade is for content only)

## Points and Commission Logic

### Registration Points
- **Basic (K500):** Member receives 25 LP
- **Premium (K1000):** Member receives 50 LP (full points for their investment)

### Upgrade Points
- **Basic → Premium:** Member receives 25 LP (additional)
- **Total after upgrade:** 50 LP (same as direct Premium registration)

### MLM Commissions
- **All tiers:** Commissions always based on K500 only
- **Premium rationale:** Extra K500 is for premium content/benefits, not commissionable
- **Upgrade:** NO commissions (upgrade is for content, not a new referral)

## Implementation

### Key Files
- `app/Services/StarterKitService.php` - Core purchase and completion logic
- `app/Http/Controllers/MyGrowNet/StarterKitController.php` - Purchase and upgrade endpoints
- `app/Infrastructure/Persistence/Eloquent/StarterKit/StarterKitPurchaseModel.php` - Purchase records

### Payment Methods
- **Mobile Money:** MoMo/Airtel Money (pending verification)
- **Wallet:** Instant completion using wallet balance (includes loan funds)

### Progressive Unlocking
Content unlocks over 30 days:
- **Day 1:** Module 1, eBook 1, Video 1
- **Day 8:** Module 2, eBook 2, Video 2
- **Day 15:** Module 3, eBook 3, Video 3
- **Day 22:** Marketing tools, pitch deck, social media pack
- **Day 30:** Digital library access (50+ eBooks)

## Recent Fixes

### Tier Bug Fix (November 5, 2025)
**Problem:** Premium tier selection not being saved  
**Cause:** `tier` field missing from `$fillable` array  
**Fix:** Added `tier` to fillable array  
**Impact:** Premium purchases now correctly grant K200 credit and LGR access

### Wallet Balance Fix (November 6, 2025)
**Problem:** Wallet showing negative balance after purchase  
**Cause:** Double-counting starter kit expenses  
**Fix:** Removed duplicate expense calculation  
**Impact:** Wallet balances now accurate

### Points/Commission Fix (November 6, 2025)
**Problem:** Unclear commission structure for Premium tier  
**Fix:** Clarified that Premium members get 50 LP but uplines only get K500 commissions  
**Impact:** Fair and sustainable commission structure

## Migration

Starter kit purchases migrated from `withdrawals` table to `transactions` table for better organization:

**Script:** `scripts/simple-migrate.php`

**Migration:**
- Finds all starter kit withdrawals
- Creates corresponding transaction records
- Reference format: `SK-MIG-{withdrawal_id}`
- Transaction types: `starter_kit_purchase` or `starter_kit_upgrade`

## Testing

### Test Scenarios
1. New Basic registration (K500) - 25 LP, K100 credit, K500 commissions
2. New Premium registration (K1000) - 50 LP, K200 credit, K500 commissions, LGR access
3. Upgrade Basic → Premium (K500) - +25 LP, +K100 credit, NO commissions, LGR access
4. Wallet payment - Instant completion, balance deducted correctly
5. Loan funds - Can use loan to purchase starter kit

## Related Documentation

- `docs/STARTER_KIT_TIER_BUG_FIX.md` - Tier bug fix details
- `docs/MEMBER_LOAN_SYSTEM.md` - Loan integration
