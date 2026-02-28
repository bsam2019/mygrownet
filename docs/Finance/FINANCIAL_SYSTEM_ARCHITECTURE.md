# MyGrowNet Financial System Architecture

**Last Updated:** 2026-02-28  
**Status:** Planning / Refactoring Required  
**Version:** 2.0 (Proposed)

## Executive Summary

This document outlines the complete financial system architecture for MyGrowNet, including wallet management, transaction processing, deposits, withdrawals, and earnings distribution. It addresses critical issues found in the current implementation and proposes a unified, reliable architecture.

---

## Current State Analysis

### Critical Issues Identified

1. **Dual Wallet Service Implementation**
   - `WalletService` (app/Services/WalletService.php)
   - `UnifiedWalletService` (app/Domain/Wallet/Services/UnifiedWalletService.php)
   - Different controllers use different services
   - Produces inconsistent balance calculations

2. **Deposit Recording Fragmentation**
   - Deposits in `member_payments` table
   - Deposits in `transactions` table
   - No synchronization mechanism
   - Missing deposits cause negative balances

3. **Transaction Recording Inconsistency**
   - Some use `TransactionIntegrityService`
   - Some use raw DB inserts
   - Bypasses duplicate detection
   - No unified transaction recording

4. **Cache Invalidation Missing**
   - Balance cached for 120 seconds
   - Not cleared after transactions
   - Users see stale balances

5. **Shop Credit Not Tracked**
   - Added to user but not recorded as transaction
   - No audit trail
   - Balance doesn't reflect shop credit

---

## Proposed Architecture

### Core Principles

1. **Single Source of Truth** - One wallet service, one balance calculation method
2. **Transaction Integrity** - All financial operations create transaction records
3. **Audit Trail** - Every financial change is logged and traceable
4. **Cache Consistency** - Cache invalidated immediately after changes
5. **Idempotency** - Operations can be safely retried without duplication

---

## System Components

### 1. Domain Layer (Business Logic)

#### 1.1 Wallet Domain
**Location:** `app/Domain/Wallet/`

**Services:**
- `WalletService.php` - Single unified wallet service
  - `calculateBalance(User $user): float`
  - `getWalletBreakdown(User $user): array`
  - `canWithdraw(User $user, float $amount): bool`
  - `clearCache(User $user): void`

**Value Objects:**
- `Money.php` - Represents monetary amounts with currency
- `WalletBalance.php` - Immutable balance snapshot
- `TransactionReference.php` - Unique transaction identifier

**Entities:**
- `Wallet.php` - User wallet aggregate root
- `WalletTransaction.php` - Individual wallet transaction

**Repositories (Interfaces):**
- `WalletRepositoryInterface.php`
- `TransactionRepositoryInterface.php`

#### 1.2 Transaction Domain
**Location:** `app/Domain/Transaction/`

**Services:**
- `TransactionService.php` - Unified transaction recording
  - `recordCredit(User, Money, TransactionType, string $description): Transaction`
  - `recordDebit(User, Money, TransactionType, string $description): Transaction`
  - `findByReference(string $reference): ?Transaction`
  - `preventDuplicate(string $reference): bool`

**Enums:**
- `TransactionType.php` - All transaction types
  ```php
  enum TransactionType: string {
      case DEPOSIT = 'deposit';
      case WITHDRAWAL = 'withdrawal';
      case STARTER_KIT_PURCHASE = 'starter_kit_purchase';
      case STARTER_KIT_GIFT = 'starter_kit_gift';
      case SHOP_PURCHASE = 'shop_purchase';
      case SHOP_CREDIT_ALLOCATION = 'shop_credit_allocation';
      case SHOP_CREDIT_USAGE = 'shop_credit_usage';
      case COMMISSION_EARNED = 'commission_earned';
      case PROFIT_SHARE = 'profit_share';
      case LGR_EARNED = 'lgr_earned';
      case LGR_TRANSFER = 'lgr_transfer';
      case LOAN_DISBURSEMENT = 'loan_disbursement';
      case LOAN_REPAYMENT = 'loan_repayment';
      case SUBSCRIPTION_PAYMENT = 'subscription_payment';
      case WORKSHOP_PAYMENT = 'workshop_payment';
  }
  ```

- `TransactionStatus.php`
  ```php
  enum TransactionStatus: string {
      case PENDING = 'pending';
      case PROCESSING = 'processing';
      case COMPLETED = 'completed';
      case FAILED = 'failed';
      case CANCELLED = 'cancelled';
      case REVERSED = 'reversed';
  }
  ```

**Events:**
- `TransactionCreated.php`
- `TransactionCompleted.php`
- `TransactionFailed.php`

#### 1.3 Payment Domain
**Location:** `app/Domain/Payment/`

**Services:**
- `DepositService.php` - Handle all deposits
  - `initiateDeposit(User, Money, PaymentMethod): DepositRequest`
  - `verifyDeposit(string $reference): void`
  - `recordDeposit(User, Money, string $reference): Transaction`

- `WithdrawalService.php` - Handle all withdrawals
  - `requestWithdrawal(User, Money, WithdrawalMethod): WithdrawalRequest`
  - `approveWithdrawal(WithdrawalRequest): void`
  - `processWithdrawal(WithdrawalRequest): Transaction`

**Value Objects:**
- `PaymentMethod.php` - Mobile money, bank, etc.
- `WithdrawalMethod.php` - Mobile money, bank transfer

#### 1.4 Earnings Domain
**Location:** `app/Domain/Earnings/`

**Services:**
- `EarningsService.php` - Calculate all earnings
  - `calculateTotalEarnings(User): Money`
  - `getCommissions(User): Money`
  - `getProfitShares(User): Money`
  - `getLgrEarnings(User): Money`

- `CommissionService.php` - MLM commissions
- `ProfitShareService.php` - Quarterly profit distribution
- `LgrService.php` - Loyalty Gift Rewards

---

### 2. Infrastructure Layer (Data Access)

#### 2.1 Database Tables

**Primary Tables:**

1. **`transactions`** - Single source of truth for all financial movements
   ```sql
   - id (bigint, primary key)
   - user_id (bigint, foreign key)
   - transaction_type (enum/string)
   - amount (decimal 15,2) - positive for credits, negative for debits
   - balance_after (decimal 15,2) - snapshot of balance after transaction
   - reference_number (string, unique, indexed)
   - description (text)
   - status (enum)
   - metadata (json) - additional data
   - related_id (bigint, nullable) - link to related record
   - related_type (string, nullable) - polymorphic relation
   - created_at
   - updated_at
   - processed_at
   ```

2. **`wallet_balances`** - Cached balance snapshots (for performance)
   ```sql
   - id (bigint, primary key)
   - user_id (bigint, foreign key, unique)
   - balance (decimal 15,2)
   - total_credits (decimal 15,2)
   - total_debits (decimal 15,2)
   - last_transaction_id (bigint, foreign key)
   - updated_at
   ```

3. **`deposits`** - Deposit requests and verification
   ```sql
   - id (bigint, primary key)
   - user_id (bigint, foreign key)
   - amount (decimal 15,2)
   - payment_method (string)
   - payment_reference (string, unique)
   - status (enum: pending, verified, failed)
   - transaction_id (bigint, foreign key, nullable)
   - verified_at
   - verified_by
   - created_at
   - updated_at
   ```

4. **`withdrawals`** - Withdrawal requests and processing
   ```sql
   - id (bigint, primary key)
   - user_id (bigint, foreign key)
   - amount (decimal 15,2)
   - withdrawal_method (string)
   - account_details (json)
   - status (enum: pending, approved, processing, completed, rejected)
   - transaction_id (bigint, foreign key, nullable)
   - requested_at
   - approved_at
   - approved_by
   - processed_at
   - processed_by
   - rejection_reason (text, nullable)
   - created_at
   - updated_at
   ```

**Legacy Tables (To Be Migrated):**
- `member_payments` - Migrate wallet_topup records to `deposits`
- Keep for historical reference, mark as deprecated

#### 2.2 Repositories (Implementations)

**Location:** `app/Infrastructure/Persistence/Eloquent/`

- `EloquentWalletRepository.php`
- `EloquentTransactionRepository.php`
- `EloquentDepositRepository.php`
- `EloquentWithdrawalRepository.php`

---

### 3. Application Layer (Use Cases)

#### 3.1 Wallet Use Cases
**Location:** `app/Application/Wallet/UseCases/`

- `GetWalletBalanceUseCase.php`
- `GetWalletBreakdownUseCase.php`
- `GetTransactionHistoryUseCase.php`

#### 3.2 Deposit Use Cases
**Location:** `app/Application/Deposit/UseCases/`

- `InitiateDepositUseCase.php`
- `VerifyDepositUseCase.php`
- `ProcessDepositCallbackUseCase.php`

#### 3.3 Withdrawal Use Cases
**Location:** `app/Application/Withdrawal/UseCases/`

- `RequestWithdrawalUseCase.php`
- `ApproveWithdrawalUseCase.php`
- `ProcessWithdrawalUseCase.php`
- `RejectWithdrawalUseCase.php`

#### 3.4 Purchase Use Cases
**Location:** `app/Application/Purchase/UseCases/`

- `PurchaseStarterKitUseCase.php`
- `PurchaseFromShopUseCase.php`
- `PayForSubscriptionUseCase.php`
- `PayForWorkshopUseCase.php`

---

### 4. Presentation Layer (Controllers & APIs)

#### 4.1 Wallet Controllers
**Location:** `app/Http/Controllers/Wallet/`

- `WalletDashboardController.php`
  - `index()` - Show wallet dashboard
  - `breakdown()` - Get wallet breakdown API
  - `transactions()` - Get transaction history

- `DepositController.php`
  - `create()` - Show deposit form
  - `store()` - Initiate deposit
  - `callback()` - Handle payment gateway callback

- `WithdrawalController.php`
  - `create()` - Show withdrawal form
  - `store()` - Request withdrawal
  - `show($id)` - View withdrawal status

#### 4.2 Admin Controllers
**Location:** `app/Http/Controllers/Admin/Finance/`

- `DepositManagementController.php`
  - `index()` - List all deposits
  - `verify($id)` - Verify deposit manually

- `WithdrawalManagementController.php`
  - `index()` - List all withdrawals
  - `approve($id)` - Approve withdrawal
  - `reject($id)` - Reject withdrawal
  - `process($id)` - Mark as processed

- `TransactionAuditController.php`
  - `index()` - View all transactions
  - `show($id)` - View transaction details
  - `reconcile()` - Run reconciliation

---

## Transaction Flow Diagrams

### Deposit Flow

```
User Initiates Deposit
    ↓
DepositController::store()
    ↓
InitiateDepositUseCase
    ↓
DepositService::initiateDeposit()
    ↓
Create Deposit Record (status: pending)
    ↓
PaymentGateway::initiatePayment()
    ↓
Return Payment URL to User
    ↓
User Completes Payment
    ↓
Payment Gateway Callback
    ↓
DepositController::callback()
    ↓
ProcessDepositCallbackUseCase
    ↓
DepositService::verifyDeposit()
    ↓
TransactionService::recordCredit()
    ↓
Create Transaction (type: deposit, status: completed)
    ↓
Update Deposit (status: verified, transaction_id)
    ↓
WalletService::clearCache()
    ↓
Dispatch TransactionCreated Event
    ↓
Send Notification to User
```

### Starter Kit Purchase Flow

```
User Selects Starter Kit
    ↓
StarterKitController::purchase()
    ↓
PurchaseStarterKitUseCase
    ↓
WalletService::calculateBalance() - Check balance
    ↓
TransactionService::recordDebit()
    ↓
Create Transaction (type: starter_kit_purchase, amount: -500)
    ↓
StarterKitService::createPurchase()
    ↓
Create StarterKitPurchase Record
    ↓
StarterKitBenefitService::allocateBenefits()
    ↓
If Shop Credit: TransactionService::recordCredit()
    ↓
Create Transaction (type: shop_credit_allocation, amount: +credit)
    ↓
WalletService::clearCache()
    ↓
Dispatch Events (PurchaseCompleted, BenefitsAllocated)
    ↓
Send Notifications
```

### Withdrawal Flow

```
User Requests Withdrawal
    ↓
WithdrawalController::store()
    ↓
RequestWithdrawalUseCase
    ↓
WalletService::canWithdraw() - Validate
    ↓
WithdrawalService::requestWithdrawal()
    ↓
Create Withdrawal Record (status: pending)
    ↓
Notify Admin
    ↓
Admin Reviews Request
    ↓
Admin Approves
    ↓
WithdrawalManagementController::approve()
    ↓
ApproveWithdrawalUseCase
    ↓
WithdrawalService::approveWithdrawal()
    ↓
TransactionService::recordDebit()
    ↓
Create Transaction (type: withdrawal, amount: -amount)
    ↓
Update Withdrawal (status: approved, transaction_id)
    ↓
WalletService::clearCache()
    ↓
ProcessWithdrawal (send money to user)
    ↓
Update Withdrawal (status: completed, processed_at)
    ↓
Send Notification to User
```

---

## Balance Calculation Logic

### Single Source of Truth

```php
class WalletService
{
    public function calculateBalance(User $user): float
    {
        // Get from cached snapshot first
        $cached = WalletBalance::where('user_id', $user->id)->first();
        
        if ($cached && $this->isCacheValid($cached)) {
            return $cached->balance;
        }
        
        // Recalculate from transactions
        $balance = Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::COMPLETED)
            ->sum('amount');
        
        // Update cache
        WalletBalance::updateOrCreate(
            ['user_id' => $user->id],
            [
                'balance' => $balance,
                'total_credits' => $this->calculateCredits($user),
                'total_debits' => $this->calculateDebits($user),
                'last_transaction_id' => $this->getLastTransactionId($user),
                'updated_at' => now(),
            ]
        );
        
        return max(0, $balance);
    }
    
    private function calculateCredits(User $user): float
    {
        return Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::COMPLETED)
            ->where('amount', '>', 0)
            ->sum('amount');
    }
    
    private function calculateDebits(User $user): float
    {
        return abs(Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::COMPLETED)
            ->where('amount', '<', 0)
            ->sum('amount'));
    }
}
```

---

## Migration Strategy

### Phase 1: Preparation (Week 1)
1. Create new database tables
2. Implement new domain services
3. Add diagnostic tools
4. Run reconciliation reports

### Phase 2: Parallel Running (Week 2-3)
1. Deploy new services alongside old
2. Use feature flag to control which service is used
3. Compare results between old and new
4. Fix discrepancies

### Phase 3: Data Migration (Week 4)
1. Migrate `member_payments` wallet_topup to `deposits`
2. Ensure all transactions in `transactions` table
3. Populate `wallet_balances` cache
4. Verify data integrity

### Phase 4: Cutover (Week 5)
1. Switch all controllers to new services
2. Deprecate old services
3. Monitor for issues
4. Rollback plan ready

### Phase 5: Cleanup (Week 6)
1. Remove old service code
2. Archive legacy tables
3. Update documentation
4. Performance optimization

---

## Testing Strategy

### Unit Tests
- Test each service method independently
- Mock dependencies
- Test edge cases (negative amounts, duplicates, etc.)

### Integration Tests
- Test complete flows (deposit → balance update)
- Test transaction integrity
- Test cache invalidation

### Load Tests
- Simulate concurrent transactions
- Test race conditions
- Verify balance consistency

### User Acceptance Tests
- Test with real user scenarios
- Verify UI displays correct balances
- Test all payment methods

---

## Monitoring & Alerts

### Key Metrics
1. **Balance Discrepancies** - Alert if calculated != cached
2. **Negative Balances** - Alert immediately
3. **Failed Transactions** - Monitor failure rate
4. **Duplicate Transactions** - Alert on detection
5. **Cache Hit Rate** - Monitor performance

### Logging
- Log all financial operations
- Include user_id, amount, type, reference
- Log before and after balance
- Structured logging for easy querying

### Audit Trail
- Every transaction immutable
- Track who approved/processed
- Timestamp all operations
- Maintain full history

---

## Security Considerations

1. **Authorization** - Verify user owns wallet before operations
2. **Validation** - Validate all amounts (positive, within limits)
3. **Idempotency** - Use unique references to prevent duplicates
4. **Encryption** - Encrypt sensitive payment details
5. **Rate Limiting** - Limit withdrawal requests per user
6. **Two-Factor Auth** - Require for large withdrawals
7. **IP Logging** - Log IP for all financial operations

---

## Performance Optimization

1. **Caching Strategy**
   - Cache balance in `wallet_balances` table
   - Invalidate immediately after transaction
   - Use Redis for frequently accessed data

2. **Database Indexing**
   - Index `user_id` on all financial tables
   - Index `reference_number` for quick lookups
   - Index `status` for filtering
   - Composite index on `(user_id, status, created_at)`

3. **Query Optimization**
   - Use eager loading for relationships
   - Batch operations where possible
   - Use database transactions for consistency

---

## API Endpoints

### Public API (User)
```
GET    /api/wallet/balance
GET    /api/wallet/breakdown
GET    /api/wallet/transactions
POST   /api/wallet/deposit
POST   /api/wallet/withdraw
GET    /api/wallet/withdrawal/{id}
```

### Admin API
```
GET    /api/admin/deposits
POST   /api/admin/deposits/{id}/verify
GET    /api/admin/withdrawals
POST   /api/admin/withdrawals/{id}/approve
POST   /api/admin/withdrawals/{id}/reject
POST   /api/admin/withdrawals/{id}/process
GET    /api/admin/transactions
GET    /api/admin/transactions/{id}
POST   /api/admin/reconcile
```

---

## Error Handling

### Common Errors
1. **InsufficientFundsException** - Balance too low
2. **DuplicateTransactionException** - Transaction already exists
3. **InvalidAmountException** - Amount <= 0 or too large
4. **WithdrawalLimitExceededException** - Exceeds daily/monthly limit
5. **PaymentGatewayException** - External service error

### Error Response Format
```json
{
    "success": false,
    "error": {
        "code": "INSUFFICIENT_FUNDS",
        "message": "Your wallet balance is insufficient for this transaction",
        "details": {
            "required": 500.00,
            "available": 250.00,
            "shortfall": 250.00
        }
    }
}
```

---

## Rollback Plan

If critical issues arise:

1. **Immediate Actions**
   - Switch feature flag to use old service
   - Stop all financial operations temporarily
   - Notify users of maintenance

2. **Investigation**
   - Run diagnostic commands
   - Compare balances between services
   - Identify root cause

3. **Fix & Redeploy**
   - Fix identified issues
   - Test thoroughly in staging
   - Gradual rollout (1% → 5% → 10% → 100%)

4. **Data Correction**
   - Identify affected users
   - Correct balances manually if needed
   - Notify affected users
   - Provide compensation if necessary

---

## Success Criteria

The financial system refactoring is successful when:

1. ✅ Zero negative balances (unless legitimate debt)
2. ✅ 100% transaction recording (no missing transactions)
3. ✅ Balance consistency (calculated = cached)
4. ✅ No duplicate transactions
5. ✅ Cache invalidation working (balance updates immediately)
6. ✅ All deposits synced and recorded
7. ✅ All withdrawals processed correctly
8. ✅ Shop credit tracked properly
9. ✅ Performance maintained (< 100ms for balance query)
10. ✅ Zero data loss during migration

---

## Maintenance & Support

### Daily Tasks
- Monitor for negative balances
- Review failed transactions
- Process pending withdrawals

### Weekly Tasks
- Run reconciliation report
- Review transaction logs
- Check for anomalies

### Monthly Tasks
- Performance review
- Security audit
- Update documentation

---

## Appendix

### A. Current File Locations

**Services:**
- `app/Services/WalletService.php` (OLD - to be deprecated)
- `app/Domain/Wallet/Services/UnifiedWalletService.php` (OLD - to be deprecated)
- `app/Services/StarterKitService.php`
- `app/Domain/Financial/Services/TransactionIntegrityService.php`

**Controllers:**
- `app/Http/Controllers/Wallet/GeneralWalletController.php`
- `app/Http/Controllers/MyGrowNet/WalletController.php`
- `app/Presentation/Http/Controllers/HomeHubController.php`

**Models:**
- `app/Models/Transaction.php`
- `app/Infrastructure/Persistence/Eloquent/Payment/MemberPaymentModel.php`

### B. Related Documentation
- See: `docs/Finance/MIGRATION_PLAN.md` (to be created)
- See: `docs/Finance/API_DOCUMENTATION.md` (to be created)
- See: `docs/Finance/TROUBLESHOOTING_GUIDE.md` (to be created)

---

**Document Owner:** Development Team  
**Review Schedule:** Monthly  
**Next Review:** 2026-03-28
