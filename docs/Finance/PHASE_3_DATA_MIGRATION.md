# Phase 3: Data Migration and Consolidation

**Last Updated:** 2026-03-01  
**Status:** In Progress  
**Goal:** Consolidate all financial data into transactions table as single source of truth

---

## Problem Statement

Currently, financial data is fragmented across multiple tables causing:
- **Double/triple counting** - Same deposit recorded in member_payments AND transactions
- **Missing transactions** - Verified payments don't create transaction records
- **Inconsistent balances** - Different services calculate different balances
- **Data integrity issues** - No single source of truth

### Current Issues

1. **Deposits in 3 locations:**
   - `member_payments` (23 records) - Legacy payment verification table
   - `transactions` with type `wallet_topup` (6 records) - Some duplicates
   - `transactions` with type `deposit` (11 records) - Created 1-2 min after topup

2. **No transaction creation on payment verification:**
   - `VerifyPaymentUseCase` verifies payment but doesn't create transaction
   - Only `ProcessMLMCommissions` listener runs (creates commissions, not transactions)
   - Wallet balance doesn't reflect verified payments

3. **Withdrawals mismatch:**
   - `withdrawals` table: 5 requests
   - `transactions` table: 56 withdrawal transactions
   - Phantom withdrawals exist (e.g., user 6 had K995.96 withdrawal before any deposits)

---

## Phase 3 Objectives

### 1. Immediate Fixes (Critical)
- ✅ Create transaction record when payment is verified
- ✅ Stop double-counting deposits from member_payments
- ✅ Ensure all financial operations create transaction records

### 2. Data Consolidation (High Priority)
- Migrate all verified member_payments to transactions table
- Link withdrawals table to transactions table
- Ensure starter_kit_purchases always have corresponding transactions
- Clean up duplicate and phantom transactions

### 3. Service Updates (High Priority)
- Update all deposit controllers to write only to transactions
- Ensure withdrawal approval creates/updates transaction
- Add validation to prevent financial operations without transactions

### 4. Monitoring & Validation (Medium Priority)
- Create reconciliation reports
- Add alerts for missing transactions
- Monitor for data inconsistencies

---

## Implementation Plan

### Step 1: Create Transaction on Payment Verification ✅ IN PROGRESS

**Problem:** When admin verifies a payment in `member_payments`, no transaction record is created.

**Solution:** Create event listener to record transaction when payment is verified.

**Files to Create/Modify:**
- `app/Listeners/RecordPaymentTransaction.php` - NEW listener
- `app/Providers/EventServiceProvider.php` - Register listener
- `app/Domain/Transaction/Services/TransactionRecordingService.php` - NEW service

**Implementation:**
```php
// New listener: RecordPaymentTransaction
class RecordPaymentTransaction
{
    public function handle(PaymentVerified $event): void
    {
        // Create transaction record for verified payment
        // Use TransactionIntegrityService to prevent duplicates
        // Map payment_type to transaction_type
        // Set transaction_source based on payment_type
    }
}
```

**Transaction Type Mapping:**
- `wallet_topup` → `TransactionType::WALLET_TOPUP`
- `subscription` → `TransactionType::SUBSCRIPTION_PAYMENT`
- `workshop` → `TransactionType::WORKSHOP_PAYMENT`
- `product` → `TransactionType::SHOP_PURCHASE` or `STARTER_KIT_PURCHASE`
- `learning_pack` → `TransactionType::LEARNING_PACK_PURCHASE`
- `coaching` → `TransactionType::COACHING_PAYMENT`

### Step 2: Stop Using member_payments for Balance Calculation

**Problem:** `UnifiedWalletService` queries member_payments table causing double-counting.

**Solution:** Remove member_payments query from wallet service.

**Files to Modify:**
- `app/Domain/Wallet/Services/UnifiedWalletService.php`

**Changes:**
```php
// REMOVE this query:
SELECT 'deposits_mp' as type, COALESCE(SUM(amount), 0) as total 
FROM member_payments 
WHERE user_id = ? AND payment_type = 'wallet_topup' AND status = 'verified'

// Keep only transactions table query
```

### Step 3: Migrate Historical Data

**Problem:** Existing verified payments in member_payments don't have transaction records.

**Solution:** Create migration command to sync historical data.

**Files to Create:**
- `app/Console/Commands/MigratePaymentsToTransactions.php`

**Implementation:**
```php
// For each verified payment in member_payments:
// 1. Check if transaction already exists (by reference)
// 2. If not, create transaction record
// 3. Log migration for audit trail
```

### Step 4: Link Withdrawals to Transactions

**Problem:** Withdrawals table and transactions table not linked.

**Solution:** Add foreign key and ensure withdrawal approval creates/updates transaction.

**Files to Modify:**
- `database/migrations/XXXX_add_transaction_id_to_withdrawals.php` - NEW migration
- `app/Http/Controllers/Admin/WithdrawalApprovalController.php`

**Changes:**
```php
// Add column to withdrawals table:
$table->foreignId('transaction_id')->nullable()->constrained('transactions');

// When withdrawal is approved:
// 1. Create transaction record (type: withdrawal, status: completed)
// 2. Update withdrawal record with transaction_id
// 3. Clear wallet cache
```

### Step 5: Validate Starter Kit Transactions

**Problem:** 18 starter_kit_purchases but only 17 transactions.

**Solution:** Ensure every starter kit purchase has a transaction.

**Files to Modify:**
- `app/Services/StarterKitService.php`

**Validation:**
```php
// After creating starter_kit_purchase:
// 1. Verify transaction exists
// 2. If not, create transaction record
// 3. Log discrepancy
```

### Step 6: Add Transaction Validation

**Problem:** No enforcement that financial operations create transactions.

**Solution:** Add validation layer to ensure all operations create transactions.

**Files to Create:**
- `app/Domain/Transaction/Services/TransactionValidator.php`

**Implementation:**
```php
// Before completing any financial operation:
// 1. Verify transaction will be created
// 2. Validate transaction data
// 3. Prevent operation if validation fails
```

---

## Migration Commands

### Migrate Historical Payments
```bash
php artisan finance:migrate-payments
```

### Validate Data Integrity
```bash
php artisan finance:validate-integrity
```

### Reconcile Balances
```bash
php artisan finance:reconcile-balances
```

---

## Testing Strategy

### 1. Unit Tests
- Test transaction creation on payment verification
- Test duplicate prevention
- Test transaction type mapping

### 2. Integration Tests
- Test complete payment flow (submit → verify → transaction created)
- Test withdrawal flow (request → approve → transaction created)
- Test starter kit flow (purchase → transaction created)

### 3. Data Validation Tests
- Compare old vs new balance calculations
- Verify no missing transactions
- Verify no duplicate transactions

### 4. Production Validation
- Run on staging first
- Compare balances before/after migration
- Monitor for 48 hours before full rollout

---

## Rollback Plan

If issues arise:

1. **Disable new listener** - Remove RecordPaymentTransaction from EventServiceProvider
2. **Revert service changes** - Restore member_payments query in UnifiedWalletService
3. **Clear caches** - Clear all wallet caches
4. **Notify users** - Inform users of temporary maintenance
5. **Investigate** - Identify root cause
6. **Fix and redeploy** - Fix issues and redeploy with additional validation

---

## Success Criteria

Phase 3 is complete when:

1. ✅ All verified payments create transaction records
2. ✅ No double-counting of deposits
3. ✅ Withdrawals linked to transactions table
4. ✅ All starter kit purchases have transactions
5. ✅ Balance calculations consistent across services
6. ✅ No missing transactions
7. ✅ No phantom transactions
8. ✅ Historical data migrated successfully
9. ✅ All tests passing
10. ✅ Production validation successful

---

## Timeline

- **Day 1:** Implement transaction creation on payment verification
- **Day 2:** Update wallet service, test thoroughly
- **Day 3:** Migrate historical data, validate
- **Day 4:** Link withdrawals, validate starter kits
- **Day 5:** Deploy to staging, monitor
- **Day 6-7:** Deploy to production, monitor closely
- **Day 8-14:** Monitor for issues, optimize

---

## Files to Create/Modify

### New Files
- `app/Listeners/RecordPaymentTransaction.php`
- `app/Domain/Transaction/Services/TransactionRecordingService.php`
- `app/Domain/Transaction/Services/TransactionValidator.php`
- `app/Console/Commands/MigratePaymentsToTransactions.php`
- `app/Console/Commands/ValidateFinancialIntegrity.php`
- `app/Console/Commands/ReconcileBalances.php`
- `database/migrations/XXXX_add_transaction_id_to_withdrawals.php`
- `tests/Feature/Finance/PaymentTransactionTest.php`
- `tests/Feature/Finance/WithdrawalTransactionTest.php`

### Modified Files
- `app/Providers/EventServiceProvider.php`
- `app/Domain/Wallet/Services/UnifiedWalletService.php`
- `app/Http/Controllers/Admin/WithdrawalApprovalController.php`
- `app/Services/StarterKitService.php`

---

## Changelog

### 2026-03-01 - Phase 3 Started
- Created implementation plan
- Identified critical issues
- Defined success criteria
- Started implementation of transaction creation on payment verification

