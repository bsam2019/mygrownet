# Wallet System

**Last Updated:** November 6, 2025  
**Status:** ✅ Complete

## Overview

Digital wallet system for managing member funds, including earnings, expenses, and loan integration.

## Balance Calculation

**Formula:**
```
Balance = Earnings - Expenses
```

### Earnings Include:
- Topups (mobile money, bank transfers)
- Commissions (referral, level, matrix)
- Bonuses (registration, performance, milestone)
- Profit sharing (LGR quarterly distributions)
- Loan disbursements

### Expenses Include:
- Withdrawals (to bank/mobile money)
- Workshop registrations
- Internal transactions (starter kit, upgrades)
- Loan repayments

## Key Service

**File:** `app/Services/WalletService.php`

**Main Methods:**
- `calculateBalance(User $user)` - Calculate current balance
- `calculateTotalEarnings(User $user)` - Sum all earnings
- `calculateTotalExpenses(User $user)` - Sum all expenses

## Recent Fixes

### Double-Counting Fix (November 6, 2025)

**Problem:** Wallet showing negative balance after starter kit purchase

**Root Cause:** Starter kit purchases counted twice:
1. In `withdrawals` table with `withdrawal_method='wallet_payment'`
2. In `starter_kit_purchases` table with `payment_method='wallet'`

**Solution:** Removed duplicate `starterKitExpenses` calculation since starter kit wallet payments are already in withdrawals table

**Impact:** All wallet balances now accurate

### Loan Integration (November 5, 2025)

**Enhancement:** Wallet now includes loan transactions

**Changes:**
- Loan disbursements added to earnings
- Loan repayments added to expenses
- Members can use loan funds for purchases

**Impact:** Complete financial picture in wallet balance

## Transaction Types

### Earnings (Positive)
- `topup` - Mobile money/bank deposit
- `commission` - Referral/level commission
- `bonus` - Registration/performance bonus
- `profit_share` - LGR quarterly distribution
- `loan_disbursement` - Loan received

### Expenses (Negative)
- `withdrawal` - Cash out to bank/mobile money
- `workshop_registration` - Workshop payment
- `starter_kit_purchase` - Starter kit payment
- `starter_kit_upgrade` - Tier upgrade payment
- `loan_repayment` - Loan payment

## Usage

### Check Balance
```php
$walletService = app(WalletService::class);
$balance = $walletService->calculateBalance($user);
```

### Validate Sufficient Funds
```php
if ($balance < $requiredAmount) {
    throw new \Exception('Insufficient wallet balance');
}
```

### Record Transaction
```php
DB::table('transactions')->insert([
    'user_id' => $user->id,
    'transaction_type' => 'starter_kit_purchase',
    'amount' => -500, // Negative for expense
    'reference_number' => 'SK-' . uniqid(),
    'description' => 'Starter Kit Purchase',
    'status' => 'completed',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

## Frontend

**Component:** `resources/js/pages/MyGrowNet/Wallet.vue`

**Features:**
- Real-time balance display
- Transaction history
- LGR transfer functionality
- Withdrawal requests
- Topup instructions

## Related Documentation

- `docs/MEMBER_LOAN_SYSTEM.md` - Loan integration details
- `docs/STARTER_KIT_SYSTEM.md` - Starter kit wallet payments
