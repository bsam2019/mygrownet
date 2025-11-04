# LGR Withdrawable Percentage Implementation

## Overview

This implementation fixes the LGR withdrawable calculation to be based on **total awarded amount** rather than current balance, and makes the percentage configurable via settings.

## Problem

Previously:
- Withdrawable amount was calculated as `current_balance * 40%`
- This was incorrect because it didn't track what was originally awarded
- The 40% was hardcoded in the frontend

## Solution

### 1. Database Changes

**New columns in `users` table:**
- `loyalty_points_awarded_total` - Tracks total LGR ever awarded to user
- `loyalty_points_withdrawn_total` - Tracks total LGR withdrawn/transferred

**New setting in `lgr_settings` table:**
- `lgr_withdrawable_percentage` - Configurable percentage (default: 100%)

### 2. Calculation Logic

```
Max Withdrawable = (Total Awarded × Percentage) - Total Withdrawn
Actual Withdrawable = MIN(Current Balance, Max Withdrawable)
```

**Example:**
- User awarded: K1000
- Percentage: 40%
- Already withdrawn: K200
- Current balance: K800

Calculation:
- Max withdrawable = (1000 × 40%) - 200 = K200
- Actual withdrawable = MIN(800, 200) = K200

### 3. Tracking Points

**When LGR is awarded:**
- Increment `loyalty_points`
- Increment `loyalty_points_awarded_total`

**When LGR is transferred/withdrawn:**
- Decrement `loyalty_points`
- Increment `loyalty_points_withdrawn_total`

## Files Modified

### Migrations
1. `2025_11_03_170000_add_lgr_tracking_to_users.php` - Adds tracking columns
2. `2025_11_03_170100_add_lgr_withdrawable_percentage_setting.php` - Adds setting

### Controllers
1. `app/Http/Controllers/MyGrowNet/LgrTransferController.php` - Tracks withdrawals
2. `app/Http/Controllers/Admin/LgrManualAwardController.php` - Tracks awards
3. `app/Http/Controllers/MyGrowNet/WalletController.php` - Calculates withdrawable
4. `app/Http/Controllers/MyGrowNet/EarningsController.php` - Shows withdrawable info

### Frontend
1. `resources/js/pages/MyGrowNet/Wallet.vue` - Displays correct withdrawable amount

## Deployment Steps

### 1. Run Migrations

```bash
php artisan migrate --force
```

This will:
- Add `loyalty_points_awarded_total` and `loyalty_points_withdrawn_total` columns
- Backfill `awarded_total` with current balance for existing users
- Add `lgr_withdrawable_percentage` setting (default: 100%)

### 2. Verify Settings

Check that the setting exists:
```bash
php artisan tinker
DB::table('lgr_settings')->where('key', 'lgr_withdrawable_percentage')->first();
```

### 3. Update Existing Awards

For any existing LGR awards that need tracking, you may need to manually update:
```sql
-- This is already done by the migration backfill
UPDATE users 
SET loyalty_points_awarded_total = loyalty_points 
WHERE loyalty_points > 0;
```

### 4. Configure Percentage

To change from 100% to 40%:
1. Go to Admin → LGR Management → Settings
2. Find "LGR Withdrawable Percentage"
3. Change from 100 to 40
4. Save

Or via database:
```sql
UPDATE lgr_settings 
SET value = '40' 
WHERE key = 'lgr_withdrawable_percentage';
```

## Admin Interface

The setting appears in the LGR Settings page under "General Settings":
- **Label:** LGR Withdrawable Percentage
- **Description:** Percentage of total awarded LGR that can be withdrawn/transferred
- **Type:** Decimal
- **Default:** 100

## Testing

### Test Scenario 1: New Award
1. Award K1000 to a user
2. Check: `loyalty_points` = 1000, `loyalty_points_awarded_total` = 1000
3. With 40% setting: withdrawable = K400

### Test Scenario 2: After Transfer
1. User transfers K200 to wallet
2. Check: `loyalty_points` = 800, `loyalty_points_withdrawn_total` = 200
3. With 40% setting: withdrawable = K200 (400 - 200)

### Test Scenario 3: Multiple Awards
1. Award K500 (total awarded = 1500)
2. With 40% setting: withdrawable = K400 (600 - 200)

## Backward Compatibility

- Existing users: `awarded_total` is backfilled with current balance
- Existing transfers: `withdrawn_total` starts at 0
- This means existing users can withdraw based on their current balance
- Future awards and withdrawals will be tracked correctly

## Future Enhancements

1. **Withdrawal History Report** - Show detailed history of awards vs withdrawals
2. **Admin Dashboard** - Show system-wide LGR statistics
3. **User Dashboard** - Show personal LGR award/withdrawal history
4. **Automated Tracking** - Track all LGR sources (cycles, bonuses, etc.)

## Notes

- The percentage can be changed at any time via settings
- Changes apply immediately to all calculations
- The system prevents withdrawing more than the allowed percentage
- Frontend shows clear messaging about the limit
