# MyGrowNet Wallet System Documentation

## Overview

The MyGrowNet Wallet is a **prepaid digital account system** that allows members to store earnings, make platform purchases, and request withdrawals. The wallet balance is calculated dynamically from multiple data sources.

**Important**: The wallet is NOT a bank account or investment product. It's a convenience feature for platform transactions.

---

## Wallet Balance Calculation

### Formula

```
Wallet Balance = Total Earnings - Total Expenses
```

### Total Earnings (Credits)

1. **Referral Commissions** (`referral_commissions` table)
   - Status: `paid`
   - Sum of `amount` field

2. **Profit Shares** (`profit_shares` table)
   - All records
   - Sum of `amount` field

3. **Wallet Topups** (`member_payments` table)
   - Payment Type: `wallet_topup`
   - Status: `verified`
   - Sum of `amount` field

### Total Expenses (Debits)

1. **Approved Withdrawals** (`withdrawals` table)
   - Status: `approved`
   - Sum of `amount` field

2. **Workshop Expenses** (`workshop_registrations` + `workshops` tables)
   - Registration Status: `registered`, `attended`, or `completed`
   - Sum of workshop `price`

3. **Transaction Expenses** (`transactions` table)
   - Transaction Type: `withdrawal`
   - Status: `completed`
   - Sum of `amount` field

4. **Starter Kit Purchases** (`starter_kit_purchases` table)
   - Payment Method: `wallet`
   - Status: `completed`
   - Sum of `amount` field

---

## Database Tables

### Primary Tables

| Table | Purpose | Key Fields |
|-------|---------|------------|
| `users` | User wallet fields | `wallet_balance`, `bonus_balance`, `loyalty_points` |
| `referral_commissions` | Commission earnings | `user_id`, `amount`, `status` |
| `profit_shares` | Profit share earnings | `user_id`, `amount` |
| `member_payments` | Wallet topups | `user_id`, `amount`, `payment_type`, `status` |
| `withdrawals` | Withdrawal requests | `user_id`, `amount`, `status` |
| `workshop_registrations` | Workshop purchases | `user_id`, `workshop_id`, `status` |
| `transactions` | General transactions | `user_id`, `amount`, `transaction_type`, `status` |
| `starter_kit_purchases` | Starter kit purchases | `user_id`, `amount`, `payment_method`, `status` |

### User Table Fields

```php
// In users table
'wallet_balance' => 'decimal(10,2)', // Cached balance (optional)
'bonus_balance' => 'decimal(10,2)',  // Bonus/rewards balance (non-withdrawable)
'loyalty_points' => 'decimal(10,2)', // Loyalty Growth Reward points
'wallet_policy_accepted' => 'boolean',
'wallet_policy_accepted_at' => 'timestamp',
'daily_withdrawal_used' => 'decimal(10,2)',
'daily_withdrawal_reset_date' => 'date',
'verification_level' => 'enum', // basic, enhanced, premium
```

---

## Code Architecture

### Service Layer

**Primary Service**: `App\Services\WalletService`

```php
// Calculate wallet balance
$walletService = app(WalletService::class);
$balance = $walletService->calculateBalance($user);

// Get detailed breakdown
$breakdown = $walletService->getWalletBreakdown($user);
```

### Controllers

1. **`App\Http\Controllers\MyGrowNet\WalletController`**
   - Route: `/mygrownet/wallet`
   - Method: `index()` - Display wallet page
   - Method: `acceptPolicy()` - Accept wallet policy
   - Method: `checkWithdrawalLimit()` - Validate withdrawal limits

2. **`App\Http\Controllers\DashboardController`**
   - Uses `WalletService` to display balance on dashboard

### Models

- **`App\Models\User`** - User model with wallet relationships
- **`App\Models\ReferralCommission`** - Commission earnings
- **`App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel`** - Payment records

---

## Withdrawal Limits

Limits are based on verification level:

| Level | Daily Limit | Monthly Limit | Single Transaction |
|-------|-------------|---------------|-------------------|
| Basic | K1,000 | K10,000 | K500 |
| Enhanced | K5,000 | K50,000 | K2,000 |
| Premium | K20,000 | K200,000 | K10,000 |

### Daily Limit Reset

- Resets automatically at midnight
- Tracked in `users.daily_withdrawal_used`
- Reset date in `users.daily_withdrawal_reset_date`

---

## Wallet Policy

Members must accept the wallet policy before using wallet features.

**Policy Fields:**
- `wallet_policy_accepted` (boolean)
- `wallet_policy_accepted_at` (timestamp)

**Policy Document**: `/wallet/policy` route

---

## Usage Examples

### Get Wallet Balance

```php
use App\Services\WalletService;

$walletService = app(WalletService::class);
$user = auth()->user();

// Simple balance
$balance = $walletService->calculateBalance($user);

// Detailed breakdown
$breakdown = $walletService->getWalletBreakdown($user);
echo "Balance: K" . $breakdown['balance'];
echo "Total Earnings: K" . $breakdown['earnings']['total'];
echo "Total Expenses: K" . $breakdown['expenses']['total'];
```

### Check Withdrawal Eligibility

```php
$amount = 500;
$balance = $walletService->calculateBalance($user);

if ($balance >= $amount) {
    // Process withdrawal
}
```

### Add Wallet Topup

```php
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;

MemberPaymentModel::create([
    'user_id' => $user->id,
    'amount' => 1000,
    'payment_type' => 'wallet_topup',
    'status' => 'verified',
    'phone_number' => $user->phone,
    'account_name' => $user->name,
]);
```

---

## Important Notes

### Balance Calculation

1. **Dynamic Calculation**: Balance is calculated on-the-fly from multiple tables
2. **No Single Source of Truth**: The `users.wallet_balance` field is optional/cached
3. **Always Use WalletService**: Don't calculate balance manually in controllers

### Common Pitfalls

❌ **DON'T** use `$user->wallet_balance` directly
✅ **DO** use `WalletService::calculateBalance($user)`

❌ **DON'T** calculate balance in multiple places
✅ **DO** use centralized `WalletService`

❌ **DON'T** forget to include all expense sources
✅ **DO** include: withdrawals, workshops, transactions, starter kits

### Testing

When testing wallet functionality:
1. Create test topups in `member_payments`
2. Create test expenses in appropriate tables
3. Verify balance calculation includes all sources
4. Test withdrawal limits and verification levels

---

## Future Improvements

### Recommended Enhancements

1. **Wallet Transaction Log**: Create a unified `wallet_transactions` table to log all wallet activities
2. **Balance Caching**: Cache balance in `users.wallet_balance` and update on transactions
3. **Transaction Events**: Dispatch events for all wallet transactions
4. **Audit Trail**: Log all balance changes for compliance
5. **Real-time Updates**: WebSocket notifications for balance changes

### Migration Path

To implement a unified transaction log:

```php
// Create wallet_transactions table
Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->enum('type', ['credit', 'debit']);
    $table->decimal('amount', 10, 2);
    $table->string('source'); // commission, topup, withdrawal, etc.
    $table->text('description');
    $table->decimal('balance_before', 10, 2);
    $table->decimal('balance_after', 10, 2);
    $table->morphs('reference'); // Link to source record
    $table->timestamps();
});
```

---

## Support & Maintenance

**Last Updated**: November 2, 2025
**Maintained By**: Development Team
**Related Docs**: 
- `docs/WALLET_POLICY_TERMS.md`
- `docs/WALLET_MEMBER_GUIDE.md`
- `docs/MYGROWNET_PLATFORM_CONCEPT.md`
