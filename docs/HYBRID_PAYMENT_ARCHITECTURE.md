# Hybrid Payment Architecture

## Overview

MyGrowNet uses a **hybrid payment architecture** that separates payment processing from financial ledger management. This provides the best of both worlds: clean payment reconciliation and a single source of truth for balances.

## Architecture Components

### 1. Payment Logs (`payment_logs` table)
**Purpose**: Track external payment processing and reconciliation

**Responsibilities**:
- Record payment initiation
- Track payment provider interactions
- Store provider callbacks/webhooks
- Handle payment reconciliation
- Link to transactions once completed

**Lifecycle**:
```
initiated → pending → processing → completed → reconciled
                                 ↓
                              failed/cancelled
```

**Key Fields**:
- `provider_reference`: External reference from MTN, Airtel, etc.
- `internal_reference`: Our unique reference
- `status`: Payment processing status
- `reconciled`: Whether payment has been matched with provider reports
- `transaction_id`: Link to transaction once payment completes

### 2. Transactions (`transactions` table)
**Purpose**: Complete financial ledger (single source of truth)

**Responsibilities**:
- Record ALL financial movements
- Calculate user balances
- Provide audit trail
- Generate financial reports

**Transaction Types**:
- `deposit`: Money coming in
- `withdrawal`: Money going out
- `starter_kit_purchase`: Product purchases
- `lgr_manual_award`: Loyalty rewards
- `lgr_transfer_out`: LGR to wallet transfers
- `wallet_topup`: Internal transfers
- `commission`: Referral earnings
- `profit_share`: Profit distributions

**Key Principle**: 
> **ALL balance calculations use ONLY the transactions table**

### 3. Payment Service (`PaymentService`)
**Purpose**: Orchestrate payment flow between payment_logs and transactions

**Key Methods**:
- `initiateDeposit()`: Start a deposit payment
- `completePayment()`: Mark payment complete and create transaction
- `failPayment()`: Handle payment failures
- `reconcilePayment()`: Match with provider reports
- `initiateWithdrawal()`: Start withdrawal process

## Data Flow

### Deposit Flow
```
1. User initiates deposit
   ↓
2. Create payment_log (status: initiated)
   ↓
3. Send to payment provider (MTN, Airtel, etc.)
   ↓
4. Provider callback received
   ↓
5. Update payment_log (status: completed)
   ↓
6. Create transaction (type: deposit, amount: +K500)
   ↓
7. Link transaction_id to payment_log
   ↓
8. User balance updates automatically
```

### Withdrawal Flow
```
1. User requests withdrawal
   ↓
2. Check balance from transactions table
   ↓
3. Create payment_log (status: pending)
   ↓
4. Create transaction (type: withdrawal, amount: -K500, status: pending)
   ↓
5. Admin approves
   ↓
6. Update payment_log (status: processing)
   ↓
7. Send to payment provider
   ↓
8. Provider confirms
   ↓
9. Update payment_log (status: completed)
   ↓
10. Update transaction (status: completed)
```

### Purchase Flow (Internal)
```
1. User purchases starter kit
   ↓
2. Create transaction (type: starter_kit_purchase, amount: -K500)
   ↓
3. Balance updates automatically
   
(No payment_log needed - internal transaction only)
```

## Balance Calculation

**Simple Rule**: Sum all completed transactions

```php
$balance = DB::table('transactions')
    ->where('user_id', $userId)
    ->where('status', 'completed')
    ->sum('amount');
```

**Why this works**:
- Deposits: +K500
- Purchases: -K500
- LGR awards: +K30
- Withdrawals: -K100
- **Sum = Current balance**

## Reconciliation Process

### Daily Reconciliation
1. Get provider report (MTN, Airtel transactions for the day)
2. Match provider_reference with payment_logs
3. Mark matched payments as reconciled
4. Flag unmatched payments for investigation

### Query for Unreconciled Payments
```php
$unreconciled = PaymentLog::unreconciled()
    ->where('completed_at', '>=', now()->subDays(7))
    ->get();
```

## Migration from Old System

### Old System
- `member_payments`: Deposits and top-ups
- `referral_commissions`: Commission earnings
- `profit_shares`: Profit distributions
- `withdrawals`: Withdrawal requests
- Multiple tables, complex balance calculations

### New System
- `payment_logs`: External payment tracking only
- `transactions`: Everything in one place
- Simple balance calculation
- Clear data flow

### Migration Steps
1. ✅ Create `payment_logs` table
2. ✅ Migrate `member_payments` → `payment_logs`
3. ✅ Ensure all payments have transactions
4. ✅ Remove duplicate deposits
5. ✅ Update controllers to use transactions only
6. Archive old tables

## Code Examples

### Initiating a Deposit
```php
use App\Services\PaymentService;

$paymentService = app(PaymentService::class);

$paymentLog = $paymentService->initiateDeposit(
    user: $user,
    amount: 500.00,
    paymentMethod: 'mobile_money',
    provider: 'MTN',
    metadata: [
        'phone' => '0977123456',
        'account_name' => 'John Doe'
    ]
);

// Send to payment provider with $paymentLog->internal_reference
```

### Handling Provider Callback
```php
// MTN callback received
$providerReference = $request->input('transaction_id');
$status = $request->input('status');

$paymentLog = $paymentService->findByInternalReference($request->input('our_reference'));

if ($status === 'SUCCESS') {
    $paymentService->completePayment($paymentLog, $providerReference);
    // Transaction automatically created, balance updated
} else {
    $paymentService->failPayment($paymentLog, $request->input('error_message'));
}
```

### Getting User Balance
```php
// Simple and reliable
$balance = DB::table('transactions')
    ->where('user_id', $user->id)
    ->where('status', 'completed')
    ->sum('amount');
```

### Creating Internal Transaction (No Payment Log)
```php
// For internal operations like LGR awards, purchases, etc.
DB::table('transactions')->insert([
    'user_id' => $user->id,
    'amount' => 30.00,
    'transaction_type' => 'lgr_manual_award',
    'status' => 'completed',
    'description' => 'Loyalty Growth Reward',
    'created_at' => now(),
    'updated_at' => now(),
    'processed_at' => now(),
]);
```

## Benefits of This Architecture

### 1. Scalability
- Payment logs can be archived/purged without losing transaction history
- Transactions table optimized for balance queries
- Payment reconciliation doesn't slow down balance calculations

### 2. Clarity
- Clear separation: payment_logs = external, transactions = internal
- Single source of truth for balances
- Easy to understand data flow

### 3. Reconciliation
- Easy to match with provider reports
- Track payment status independently
- Identify discrepancies quickly

### 4. Audit Trail
- Complete history in transactions table
- Payment provider interactions in payment_logs
- Both tables provide different audit perspectives

### 5. Flexibility
- Add new payment providers easily
- Change payment processing without affecting ledger
- Support multiple currencies in future

## Performance Considerations

### Indexes
```sql
-- payment_logs
INDEX (user_id, status)
INDEX (provider_reference)
INDEX (internal_reference)
INDEX (reconciled, completed_at)

-- transactions
INDEX (user_id, status)
INDEX (transaction_type, status)
INDEX (created_at)
INDEX (reference_number)
```

### Archiving Strategy
- **payment_logs**: Archive after 2 years (once reconciled)
- **transactions**: Keep forever (or archive after 5+ years)
- Archived data moved to separate tables for reporting

### Query Optimization
```php
// Good: Single query for balance
$balance = DB::table('transactions')
    ->where('user_id', $userId)
    ->where('status', 'completed')
    ->sum('amount');

// Bad: Multiple queries
$deposits = DB::table('member_payments')->sum('amount');
$withdrawals = DB::table('withdrawals')->sum('amount');
$balance = $deposits - $withdrawals; // Incomplete!
```

## Testing

### Unit Tests
- Test PaymentService methods
- Test balance calculations
- Test payment state transitions

### Integration Tests
- Test complete deposit flow
- Test complete withdrawal flow
- Test reconciliation process

### Load Tests
- 10,000 users, 50 transactions each = 500,000 rows
- Balance query should be < 100ms
- Payment log queries should be < 50ms

## Monitoring

### Key Metrics
- Unreconciled payments count
- Failed payments rate
- Average payment completion time
- Balance calculation performance

### Alerts
- Unreconciled payments > 24 hours old
- Failed payment rate > 5%
- Balance calculation > 200ms

## Future Enhancements

1. **Multi-currency support**: Add currency conversion
2. **Automated reconciliation**: Match payments automatically
3. **Payment retry logic**: Auto-retry failed payments
4. **Fraud detection**: Flag suspicious patterns
5. **Real-time balance updates**: WebSocket notifications

## Related Documentation

- `docs/WALLET_SYSTEM.md` - Wallet features and policies
- `docs/CONSOLIDATION_PLAN.md` - System consolidation strategy
- `docs/WALLET_BALANCE_ISSUE.md` - Historical balance issues

## Support

For questions or issues:
- Check logs: `storage/logs/laravel.log`
- Review payment_logs table for payment issues
- Review transactions table for balance issues
- Contact: dev@mygrownet.com
