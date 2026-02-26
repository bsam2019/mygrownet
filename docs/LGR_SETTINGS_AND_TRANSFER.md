# LGR Settings & Transfer System

**Last Updated:** November 3, 2025  
**Status:** ✅ Production Ready

## Overview

The LGR system now includes comprehensive admin settings and member transfer functionality, giving full control over the Loyalty Growth Reward system.

## Features

### 1. Admin Settings Page
Centralized configuration for all LGR system parameters accessible at **Admin → LGR → Settings**

### 2. LGR to Wallet Transfer
Members can transfer their LGR balance to main wallet for purchases or withdrawals

### 3. Configurable Parameters
All limits, fees, and system behaviors are adjustable without code changes

---

## Admin Settings

### General Settings
- **Enable LGR System** - Master switch for entire LGR system
- **Enable LGR Expiry** - Whether credits expire after a period
- **LGR Expiry Days** - Number of days before expiration (if enabled)

### Withdrawal Settings
- **Enable LGR Withdrawals** - Allow direct withdrawals from LGR balance
- **Withdrawal Fee (%)** - Percentage fee charged on withdrawals
- **Minimum Withdrawal Amount** - Minimum amount per withdrawal (default: K50)
- **Maximum Withdrawal Amount** - Maximum amount per withdrawal (default: K5,000)

### Transfer Settings
- **Enable LGR to Wallet Transfer** - Allow members to transfer LGR to main wallet
- **Transfer Fee (%)** - Percentage fee on transfers (default: 0%)
- **Minimum Transfer Amount** - Minimum per transfer (default: K10)
- **Maximum Transfer Amount** - Maximum per transfer (default: K10,000)

### Award Settings
- **Minimum Manual Award** - Minimum amount for manual awards (default: K10)
- **Maximum Manual Award** - Maximum amount for manual awards (default: K2,100)

---

## Member Transfer Feature

### How It Works

1. **Member views LGR balance** on wallet page
2. **Clicks "Transfer to Main Wallet"** button
3. **Enters amount** to transfer (min K10)
4. **Confirms transfer**
5. **Funds moved instantly** from LGR to main wallet

### Transfer Process

```
LGR Balance (K500)
       ↓
  Transfer K200
       ↓
Fee Applied (0% default)
       ↓
Main Wallet (+K200)
LGR Balance (K300)
```

### Benefits

- **Flexibility** - Members can use LGR credits for any purpose
- **No Restrictions** - Transferred funds become regular wallet balance
- **Instant** - Transfer happens immediately
- **Transparent** - Clear fee display (if any)

---

## Technical Implementation

### Database

**Table:** `lgr_settings`
- Stores all configurable parameters
- Cached for performance
- Easy to update via admin interface

### Files Created

**Backend:**
- `database/migrations/2025_11_03_100000_create_lgr_settings_table.php`
- `app/Models/LgrSetting.php`
- `app/Http/Controllers/Admin/LgrSettingsController.php`
- `app/Http/Controllers/MyGrowNet/LgrTransferController.php`

**Frontend:**
- `resources/js/pages/Admin/LGR/Settings.vue`
- Updated: `resources/js/pages/MyGrowNet/Wallet.vue` (added transfer modal)
- Updated: `resources/js/components/AdminSidebar.vue` (added settings link)

### Routes

**Admin:**
- `GET /admin/lgr/settings` - View settings page
- `POST /admin/lgr/settings` - Update settings

**Member:**
- `POST /mygrownet/wallet/lgr-transfer` - Transfer LGR to wallet

---

## Usage Guide

### For Admins

#### Accessing Settings
1. Navigate to **Admin Panel**
2. Click **LGR Management** in sidebar
3. Click **Settings**

#### Updating Settings
1. Modify desired values
2. Click **Save Settings**
3. Changes take effect immediately

#### Recommended Settings

**Conservative (Strict):**
- Transfer Fee: 5%
- Min Transfer: K50
- Max Transfer: K1,000

**Moderate (Balanced):**
- Transfer Fee: 2%
- Min Transfer: K20
- Max Transfer: K5,000

**Liberal (Flexible):**
- Transfer Fee: 0%
- Min Transfer: K10
- Max Transfer: K10,000

### For Members

#### Transferring LGR to Wallet
1. Go to **My Wallet**
2. View LGR Balance card
3. Click **"Transfer to Main Wallet"**
4. Enter amount (min K10)
5. Click **Transfer**
6. Funds appear in main wallet instantly

#### What You Can Do With Transferred Funds
- Purchase products from shop
- Upgrade starter kit
- Withdraw to mobile money/bank
- Use for any platform transaction

---

## Notifications

### Transfer Notifications
Members receive notification when transfer completes:
- **Title:** "LGR Transfer Completed"
- **Message:** Amount transferred and fee (if any)
- **Action:** Link to view wallet
- **Priority:** Normal

---

## Security & Validation

### Transfer Validation
- ✅ Sufficient LGR balance check
- ✅ Minimum/maximum amount enforcement
- ✅ System enabled check
- ✅ Database transaction (rollback on error)
- ✅ Audit trail (transaction records)

### Settings Validation
- ✅ Admin-only access
- ✅ Type validation (boolean, decimal, integer)
- ✅ Cache clearing on update
- ✅ Error handling

---

## Future Enhancements

Potential additions:
- **Scheduled Transfers** - Set up automatic transfers
- **Transfer History** - Dedicated page for transfer records
- **Bulk Transfers** - Admin can transfer to multiple members
- **Transfer Limits** - Daily/monthly transfer caps per member
- **Tiered Fees** - Different fees based on amount or member tier

---

## Troubleshooting

**Transfer button not showing:**
- Check if member has LGR balance > 0
- Verify transfers are enabled in settings

**Transfer fails:**
- Check minimum/maximum limits
- Verify sufficient LGR balance
- Check system logs for errors

**Settings not saving:**
- Verify admin permissions
- Check for validation errors
- Clear cache: `php artisan cache:clear`

---

## Related Documentation

- `docs/LGR_MANUAL_AWARDS.md` - Manual awards system
- `docs/WALLET_SYSTEM.md` - Main wallet documentation
- `docs/LGR_BALANCE_EXPLANATION.md` - LGR balance details

---

## Summary

The LGR Settings & Transfer system provides:
- ✅ Full admin control over LGR parameters
- ✅ Member flexibility to use LGR credits
- ✅ Transparent fee structure
- ✅ Instant transfers
- ✅ Complete audit trail
- ✅ Production-ready implementation

Members can now easily convert their LGR rewards into usable wallet funds, while admins have complete control over system behavior through an intuitive settings interface.
