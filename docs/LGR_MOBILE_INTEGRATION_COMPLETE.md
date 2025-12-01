# LGR Mobile Integration - Complete

**Status:** ✅ Complete  
**Date:** November 9, 2025

## Overview

LGR (Loyalty Growth Rewards) has been fully integrated into the mobile dashboard, allowing users to view their LGR balance and transfer eligible amounts to their wallet.

**Important:** The LGR card only displays for users with `loyalty_points > 0`. If you don't see it, the user has no LGR balance.

## What Was Implemented

### 1. Backend - EarningsService Enhancement

**File:** `app/Services/EarningsService.php`

Added LGR-specific methods:
- `getLgrBalance()` - Get user's LGR balance
- `getLgrWithdrawableInfo()` - Calculate withdrawable LGR with all rules
- Updated `getEarningsBreakdown()` to include `lgr_daily_bonus`

**Features:**
- Respects 40% withdrawable percentage (customizable per user)
- Calculates based on awarded total vs withdrawn total
- Handles blocked status
- Returns comprehensive LGR info array

### 2. Backend - DashboardController Enhancement

**File:** `app/Http/Controllers/MyGrowNet/DashboardController.php`

Updated `getEarningsBreakdown()` method to include:
- `lgr_daily_bonus` - Current LGR balance
- `lgr_withdrawable` - Amount that can be transferred
- `lgr_percentage` - Withdrawable percentage
- `lgr_blocked` - Whether transfers are blocked

Updated `mobileIndex()` method to pass:
- `loyaltyPoints` - LGR balance
- `lgrWithdrawable` - Transferable amount
- `lgrWithdrawablePercentage` - Percentage (40%)
- `lgrWithdrawalBlocked` - Block status
- `earningsBreakdown` - Includes all LGR data

### 3. Frontend - Mobile Dashboard

**File:** `resources/js/pages/MyGrowNet/MobileDashboard.vue`

**Added Props:**
```typescript
loyaltyPoints?: number;
lgrWithdrawable?: number;
lgrWithdrawablePercentage?: number;
lgrWithdrawalBlocked?: boolean;
```

**Added State:**
```typescript
const showLgrTransferModal = ref(false);
```

**Added Import:**
```typescript
import LgrTransferModal from '@/components/Mobile/LgrTransferModal.vue';
```

**Updated EarningsBreakdown Usage:**
```vue
<EarningsBreakdown
  v-if="earningsBreakdown"
  :earnings="earningsBreakdown"
  :lgrBalance="loyaltyPoints"
  :lgrWithdrawable="lgrWithdrawable"
  :lgrPercentage="lgrWithdrawablePercentage"
  :lgrBlocked="lgrWithdrawalBlocked"
  @transfer-lgr="showLgrTransferModal = true"
/>
```

**Added Modal:**
```vue
<LgrTransferModal
  :show="showLgrTransferModal"
  :lgrBalance="loyaltyPoints || 0"
  :lgrWithdrawable="lgrWithdrawable || 0"
  :lgrPercentage="lgrWithdrawablePercentage || 40"
  @close="showLgrTransferModal = false"
  @success="handleToastSuccess"
  @error="handleToastError"
/>
```

### 4. Frontend - EarningsBreakdown Component

**File:** `resources/js/Components/Mobile/EarningsBreakdown.vue`

**Implemented:**
- LGR Daily Bonus card with yellow/amber gradient
- **Always visible** (even with 0 balance)
- Shows balance and transferable percentage
- Transfer button (only shown if withdrawable > 0 and not blocked)
- Emits `transfer-lgr` event when clicked
- Smart messaging based on state:
  - 0 balance: "Earn LGR through daily activities and purchases"
  - No withdrawable: "No transferable LGR available yet"
  - Blocked: "LGR transfers are currently blocked"
  - Has withdrawable: Shows transfer button

### 5. Frontend - LgrTransferModal Component

**File:** `resources/js/Components/Mobile/LgrTransferModal.vue`

**Already Implemented:**
- Mobile-optimized modal with yellow/amber theme
- Shows LGR balance and transferable amount
- Amount input with validation
- "Transfer Maximum" quick action
- Transfer rules information
- Connects to `mygrownet.wallet.lgr-transfer` route
- Success/error handling with toast notifications

## Data Flow

```
User Model (loyalty_points, lgr_custom_withdrawable_percentage, etc.)
    ↓
EarningsService.getLgrWithdrawableInfo()
    ↓
DashboardController.getEarningsBreakdown()
    ↓
DashboardController.mobileIndex() → Inertia props
    ↓
MobileDashboard.vue (receives props)
    ↓
EarningsBreakdown.vue (displays LGR card)
    ↓
User clicks "Transfer to Wallet"
    ↓
LgrTransferModal.vue (opens)
    ↓
User enters amount and confirms
    ↓
POST to mygrownet.wallet.lgr-transfer
    ↓
LgrTransferController.store()
    ↓
Success → Toast notification + modal closes + data refreshes
```

## LGR Calculation Logic

This implementation follows the **existing LGR system** exactly as implemented in `LgrTransferController`:

```php
$lgrBalance = $user->loyalty_points;
$lgrAwardedTotal = $user->loyalty_points_awarded_total;
$lgrWithdrawnTotal = $user->loyalty_points_withdrawn_total;

// Use custom percentage if set, otherwise get from settings (default 40%)
$lgrPercentage = $user->lgr_custom_withdrawable_percentage 
    ?? LgrSetting::get('lgr_max_cash_conversion', 40);

// Calculate maximum withdrawable based on lifetime awards
$lgrMaxWithdrawable = ($lgrAwardedTotal * $lgrPercentage / 100) - $lgrWithdrawnTotal;

// Actual withdrawable is minimum of balance and max withdrawable
$lgrWithdrawable = min($lgrBalance, max(0, $lgrMaxWithdrawable));

// If blocked, set to 0
if ($user->lgr_withdrawal_blocked) {
    $lgrWithdrawable = 0;
}
```

**Key Features:**
- Respects `lgr_custom_withdrawable_percentage` per user (admin override)
- Falls back to `LgrSetting::get('lgr_max_cash_conversion', 40)` if no custom percentage
- Tracks lifetime awarded vs withdrawn to prevent over-withdrawal
- Honors `lgr_withdrawal_blocked` flag

## Example Scenario

**User Data:**
- LGR Balance: K1,000
- Awarded Total: K2,500
- Withdrawn Total: K500
- Percentage: 40%
- Blocked: No

**Calculation:**
- Max Withdrawable: (2,500 × 40%) - 500 = K500
- Actual Withdrawable: min(1,000, 500) = K500

**Result:**
- User sees K1,000 balance
- Can transfer K500 to wallet
- Remaining K500 stays as LGR for platform use

## Testing

All integration tests pass:

```bash
php scripts/test-lgr-complete-flow.php
```

**Test Results:**
- ✅ EarningsService: LGR methods working
- ✅ DashboardController: LGR data included
- ✅ Mobile Dashboard: Props defined
- ✅ Components: LGR integration complete
- ✅ Routes: LGR transfer endpoint exists

## User Experience

1. User opens mobile dashboard
2. Scrolls to "Earnings Breakdown" section
3. Sees "LGR Daily Bonus" card with yellow/amber styling
4. Card shows:
   - Current balance (e.g., K1,000)
   - Transferable percentage (e.g., 40%)
   - "Transfer K500 to Wallet" button
5. Clicks transfer button
6. Modal opens with:
   - Balance display
   - Transferable amount
   - Input field for amount
   - "Transfer Maximum" quick action
   - Transfer rules explanation
7. Enters amount or clicks "Transfer Maximum"
8. Clicks "Transfer to Wallet"
9. Success toast appears
10. Modal closes
11. Dashboard refreshes with updated balances

## Files Modified

### Backend
- `app/Services/EarningsService.php` - Added LGR methods
- `app/Http/Controllers/MyGrowNet/DashboardController.php` - Updated earnings breakdown

### Frontend
- `resources/js/pages/MyGrowNet/MobileDashboard.vue` - Added LGR props and modal
- `resources/js/Components/Mobile/EarningsBreakdown.vue` - Already had LGR display
- `resources/js/Components/Mobile/LgrTransferModal.vue` - Already existed

### Testing
- `scripts/test-lgr-mobile-integration.php` - Basic integration test
- `scripts/test-lgr-complete-flow.php` - Comprehensive flow test

## Routes

**LGR Transfer:**
- Route: `mygrownet.wallet.lgr-transfer`
- Method: POST
- Controller: `App\Http\Controllers\MyGrowNet\LgrTransferController@store`
- Parameters: `{ amount: number }`

## Compliance

LGR terminology is compliant with platform guidelines:
- "LGR Daily Bonus" (not "loyalty points")
- "Transfer to Wallet" (not "withdraw" or "cash out")
- Clear indication that it's a loyalty reward system
- Transparent about transferable percentage

## Next Steps

The LGR mobile integration is complete and ready for use. No further action required.

## Display Conditions

**LGR Card Visibility:**
- Shows only if `loyalty_points > 0`
- Hidden if user has no LGR balance

**Transfer Button Visibility:**
- Shows if `lgrWithdrawable > 0 AND !lgrBlocked`
- Hidden if no transferable amount or transfers are blocked
- Shows appropriate message when hidden

## Testing in Browser

1. Login as user with LGR balance (e.g., `pending-user3@mygrownet.com`)
2. Navigate to `/mygrownet/mobile`
3. Scroll to "Earnings Breakdown" section
4. Look for yellow/amber "LGR Daily Bonus" card
5. Click "Transfer K[amount] to Wallet" button
6. Modal opens for transfer confirmation

**Test Users with LGR:**
```bash
php scripts/verify-lgr-display.php
```

## Notes

- LGR balance is separate from wallet balance
- Only a percentage (default 40%) can be transferred to wallet
- Remaining LGR can be used for platform purchases
- Admin can block LGR transfers per user if needed
- Custom percentages can be set per user (overrides global setting)
- All calculations respect lifetime awarded vs withdrawn totals
- Implementation follows existing `LgrTransferController` logic exactly
