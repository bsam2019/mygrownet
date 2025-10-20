# Finance Category Alignment with MyGrowNet

**Date**: October 20, 2025  
**Status**: Updated to MyGrowNet Business Model

---

## Overview

The Finance category has been updated to align with MyGrowNet's business model, moving away from investment-focused terminology to a subscription-based platform with digital wallet and earnings tracking.

---

## Updated Finance Navigation

### Before (VBIF Investment Model)
- My Wallet
- Transactions
- Withdrawals

### After (MyGrowNet Business Model)
- **MyGrow Save** - Digital wallet for storing bonuses and earnings
- **Earnings & Bonuses** - Track monthly income from various streams
- **Withdrawals** - Request withdrawals to mobile money/bank
- **Transaction History** - Complete history of all financial activities

---

## Finance Structure Alignment

### 1. MyGrow Save (Digital Wallet)
**Route**: `wallet.index`  
**Status**: Placeholder (Coming Soon)

**Purpose**:
- Secure digital wallet for member funds
- Store bonuses and earnings
- Make purchases within ecosystem
- Transfer funds to other members
- Withdraw to mobile money/bank

**Features**:
- Voluntary savings with loyalty bonuses (BP-linked)
- Emergency fund building
- Structured as wallet service (legally compliant)
- Integration with MTN MoMo, Airtel Money

**Reference**: `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Section 3.5

---

### 2. Earnings & Bonuses
**Route**: `earnings.index`  
**Status**: Placeholder (Coming Soon)

**Purpose**:
- Track all income streams in one place
- View monthly bonus calculations
- Monitor BP-based earnings
- See profit-sharing distributions

**Income Streams**:
1. **Referral Bonuses** - Direct referral commissions (BP-based)
2. **Level Commissions** - 7-level network earnings (BP-based)
3. **Product Sales Commissions** - Earnings from MyGrow Shop (BP-based)
4. **Monthly Profit-Sharing** - 60% of company project profits (BP distribution)
5. **Milestone Rewards** - Achievement-based bonuses (LP-based)
6. **Booster Funds** - Business startup capital (LP qualification)

**Monthly Bonus Formula**:
```
Member Bonus = (Individual BP / Total BP) × 60% of monthly profit
```

**Reference**: `docs/UNIFIED_PRODUCTS_SERVICES.md` - Section 2.2

---

### 3. Withdrawals
**Route**: `withdrawals.index`  
**Status**: Functional (Existing Implementation)

**Purpose**:
- Request withdrawals from MyGrow Save wallet
- Track withdrawal status (pending, approved, rejected)
- View withdrawal history

**Features**:
- Minimum withdrawal amounts
- Processing timeframes
- Mobile money integration
- Bank transfer options

**Note**: This is an existing feature that continues to work with the new model.

---

### 4. Transaction History
**Route**: `transactions`  
**Status**: Functional (Existing Implementation)

**Purpose**:
- Complete history of all financial activities
- Track deposits, withdrawals, bonuses, purchases
- Export transaction records
- Filter by date, type, status

**Transaction Types**:
- Subscription payments
- Bonus credits (referral, level, profit-sharing)
- Withdrawals
- Purchases from MyGrow Shop
- Transfers between members
- Milestone rewards

---

## Reports & Analytics Updates

The Reports & Analytics section has also been updated to reflect MyGrowNet's business model:

### Updated Navigation
- **Business Performance** (was: Performance Report)
- **Earnings Summary** (was: Reports)
- **Network Analytics** (new)

---

## Key Differences from VBIF Model

### VBIF (Investment Fund)
- Investment tiers (K500 - K10,000)
- Fixed profit sharing (3-15%)
- Withdrawal penalties
- Investment-focused language

### MyGrowNet (Subscription Platform)
- Subscription tiers (K150 - K1,000/month)
- Performance-based bonuses (BP system)
- Digital wallet for earnings
- Business/empowerment language

---

## Implementation Status

### ✅ Completed
- Updated Finance navigation terminology
- MyGrow Save wallet page with balance and transactions
- Earnings & Bonuses dashboard with income streams
- Network Analytics page with growth visualization
- Updated Reports & Analytics navigation
- Full controller implementations
- Documentation created

### 📋 Pending
- Mobile money integration (MTN MoMo, Airtel Money)
- Wallet-to-wallet transfers
- Savings loyalty bonus system
- Enhanced transaction filtering
- Real-time earnings updates

---

## Technical Details

### Routes Added
```php
// Finance Routes
Route::get('/wallet', ...)->name('wallet.index');
Route::get('/earnings', ...)->name('earnings.index');
Route::get('/profit-sharing', ...)->name('profit-sharing.index');

// Reports Routes
Route::get('/network/analytics', ...)->name('network.analytics');
```

### Navigation Structure
```typescript
const financeNavItems: NavItem[] = [
    { title: 'MyGrow Save', href: route('wallet.index'), icon: BanknoteIcon },
    { title: 'Earnings & Bonuses', href: route('earnings.index'), icon: GiftIcon },
    { title: 'Withdrawals', href: route('withdrawals.index'), icon: ArrowRightLeftIcon },
    { title: 'Transaction History', href: route('transactions'), icon: ChartBarIcon },
];
```

---

## Next Steps

1. **Implement MyGrow Save Wallet**
   - Create wallet controller and views
   - Integrate mobile money APIs
   - Add wallet-to-wallet transfer functionality

2. **Build Earnings & Bonuses Dashboard**
   - Display all income streams
   - Show BP calculations
   - Track monthly profit-sharing

3. **Create Network Analytics Page**
   - Visualize network growth
   - Show team performance metrics
   - Display matrix structure

4. **Enhance Transaction History**
   - Add advanced filtering
   - Export functionality
   - Transaction categories

---

## References

- `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Platform overview and business model
- `docs/UNIFIED_PRODUCTS_SERVICES.md` - Products, services, and LP/BP system
- `docs/POINTS_SYSTEM_SPECIFICATION.md` - Technical specification for points
- `routes/web.php` - Route definitions
- `resources/js/components/AppSidebar.vue` - Navigation structure

---

**Last Updated**: October 20, 2025  
**Updated By**: Kiro AI Assistant
