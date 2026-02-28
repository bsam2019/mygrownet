# Finance Tables Reference

**Last Updated:** 2026-02-28  
**Purpose:** Complete reference of all finance-related database tables

---

## Core Financial Tables

### 1. transactions
**Purpose:** Central transaction ledger for all financial operations  
**Created:** 2024-02-20  
**Status:** ACTIVE - Primary transaction table

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `investment_id` - Foreign key to investments (nullable)
- `referrer_id` - Foreign key to users (nullable)
- `amount` - Decimal(10,2)
- `transaction_type` - String (deposit, withdrawal, return, wallet_topup, starter_kit_purchase, etc.)
- `status` - String (pending, completed, failed)
- `payment_method` - String (nullable)
- `reference_number` - String (unique)
- `description` - Text (nullable)
- `notes` - Text (added later, nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `investment_id`, `referrer_id`
- Unique: `reference_number`
- Performance indexes added 2025-11-07

**Related Services:**
- `TransactionIntegrityService` - Prevents duplicate transactions
- `UnifiedWalletService` - PRIMARY service, reads for balance calculation
- `WalletService` - LEGACY service (deprecated, minimal use)

**Critical Notes:**
- This is the SINGLE SOURCE OF TRUTH for all transactions
- All debits/credits should be recorded here
- `UnifiedWalletService` is the primary service used across 95% of the application

---

### 2. member_payments
**Purpose:** Tracks external payment submissions (deposits, subscriptions, etc.)  
**Created:** 2025-10-20  
**Status:** ACTIVE - Payment verification table

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `amount` - Decimal(10,2)
- `payment_method` - Enum (mtn_momo, airtel_money, bank_transfer, cash, other)
- `payment_reference` - String (unique)
- `phone_number` - String
- `account_name` - String
- `payment_type` - Enum (wallet_topup, subscription, workshop, product, learning_pack, coaching, upgrade, other)
- `notes` - Text (nullable)
- `status` - Enum (pending, verified, rejected)
- `admin_notes` - Text (nullable)
- `verified_by` - Foreign key to users (nullable)
- `verified_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `verified_by`
- Unique: `payment_reference`

**Critical Issue:**
- ⚠️ **SYNC PROBLEM:** When status changes to 'verified', a corresponding record should be created in `transactions` table
- Currently NOT syncing automatically
- Causes negative wallet balances when deposit not synced

**Related Services:**
- Payment verification controllers
- `DepositSyncService` (to be created)
- Should sync to `transactions` table when status = 'verified'

---

### 3. withdrawals
**Purpose:** Tracks withdrawal requests  
**Created:** 2025-04-06  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `amount` - Decimal(10,2)
- `status` - Enum (pending, approved, rejected)
- `withdrawal_method` - String (nullable)
- `wallet_address` - String (nullable)
- `phone` - String (added later)
- `reference` - String (added later)
- `reason` - Text (nullable)
- `processed_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`

**Critical Issue:**
- ⚠️ **DOUBLE COUNTING:** Withdrawals counted from BOTH `withdrawals` table AND `transactions` table
- `UnifiedWalletService` sums both sources
- May cause incorrect balance if withdrawal recorded in both places

**Related Services:**
- `UnifiedWalletService` - PRIMARY service, reads for balance calculation
- `WalletService` - LEGACY service (deprecated)

---

### 4. referral_commissions
**Purpose:** Tracks MLM referral commissions (7 levels)  
**Created:** 2024-02-20, Enhanced 2025-01-15  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `referrer_id` - Foreign key to users
- `referred_id` - Foreign key to users
- `investment_id` - Foreign key to investments (nullable since 2025-10-25)
- `amount` - Decimal(15,2)
- `percentage` - Decimal(5,2)
- `level` - Integer (1-7)
- `status` - Enum (pending, paid, cancelled)
- `paid_at` - Timestamp (nullable)
- `payment_transaction_id` - Foreign key (added 2025-01-15)
- `admin_notes` - Text (added 2025-01-15)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `referrer_id`, `referred_id`, `investment_id`
- Composite: `(referrer_id, status)`, `(investment_id, level)`

**Related Services:**
- MLM commission calculation services
- `UnifiedWalletService` - PRIMARY service, reads for earnings calculation

---

### 5. profit_shares
**Purpose:** Tracks individual profit share distributions  
**Created:** 2024-02-20  
**Status:** ACTIVE (Legacy - see quarterly_profit_shares)

**Columns:**
- `id` - Primary key
- `investment_id` - Foreign key to investments
- `user_id` - Foreign key to users
- `amount` - Decimal(15,2)
- `percentage` - Decimal(5,2)
- `status` - Enum (pending, processed, failed)
- `description` - String (nullable)
- `payout_date` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `investment_id`, `user_id`
- Composite: `(investment_id, status)`, `(user_id, status)`
- Index: `payout_date`

**Related Services:**
- `UnifiedWalletService` - PRIMARY service, reads for earnings calculation

---

### 6. quarterly_profit_shares
**Purpose:** Tracks quarterly community rewards distribution  
**Created:** 2025-10-20  
**Status:** ACTIVE - New profit sharing system

**Tables:**
- `quarterly_profit_shares` - Distribution metadata
- `member_profit_shares` - Individual member allocations

**quarterly_profit_shares Columns:**
- `id` - Primary key
- `year` - Integer
- `quarter` - Integer (1-4)
- `total_project_profit` - Decimal(15,2)
- `member_share_amount` - Decimal(15,2) - 60% of total
- `company_retained` - Decimal(15,2) - 40% of total
- `total_active_members` - Integer
- `total_bp_pool` - Decimal(15,2) (nullable)
- `distribution_method` - Enum (bp_based, level_based, equal)
- `status` - Enum (draft, calculated, approved, distributed)
- `notes` - Text (nullable)
- `created_by` - Foreign key to users
- `approved_by` - Foreign key to users (nullable)
- `approved_at` - Timestamp (nullable)
- `distributed_at` - Timestamp (nullable)
- `timestamps`

**member_profit_shares Columns:**
- `id` - Primary key
- `quarterly_profit_share_id` - Foreign key
- `user_id` - Foreign key to users
- `professional_level` - String
- `level_multiplier` - Decimal(4,2)
- `member_bp` - Decimal(15,2) (nullable)
- `share_amount` - Decimal(10,2)
- `status` - Enum (pending, paid)
- `paid_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Unique: `(year, quarter)` on quarterly_profit_shares

**Related Services:**
- Community rewards distribution service
- `UnifiedWalletService` - PRIMARY service, reads for earnings calculation

---

## Starter Kit & Shop

### 7. starter_kit_purchases
**Purpose:** Tracks starter kit purchases  
**Created:** 2025-10-26  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `tier` - String (lite, basic, growth_plus, pro) - added 2025-11-01
- `amount` - Decimal(10,2)
- `payment_method` - String (nullable)
- `payment_reference` - String (nullable)
- `status` - Enum (pending, completed, failed)
- `invoice_number` - String (unique)
- `gifted_by` - Foreign key to users (nullable) - added 2025-11-11
- `purchased_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `gifted_by`
- Unique: `invoice_number`
- Index: `status`, `purchased_at`

**Critical Notes:**
- Payment via wallet creates transaction in `transactions` table using `TransactionIntegrityService`
- Shop credit NOT tracked in transactions (stored in users table)

**Related Services:**
- `StarterKitService`
- `TransactionIntegrityService`

---

### 8. orders
**Purpose:** Tracks shop/marketplace orders  
**Created:** 2025-10-24  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `order_number` - String (unique)
- `user_id` - Foreign key to users
- `subtotal` - Decimal(10,2)
- `total_amount` - Decimal(10,2)
- `total_bp_earned` - Integer
- `status` - Enum (pending, processing, completed, cancelled)
- `payment_method` - Enum (wallet, momo, airtel)
- `payment_status` - Enum (pending, paid, failed)
- `notes` - Text (nullable)
- `paid_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`
- Unique: `order_number`
- Index: `status`

**Critical Issue:**
- ⚠️ **SHOP CREDIT NOT IN TRANSACTIONS:** Shop credit expenses tracked in orders but NOT in transactions table
- `UnifiedWalletService` counts shop expenses from orders table separately
- Potential inconsistency if shop credit used

**Related Services:**
- `UnifiedWalletService` - PRIMARY service, reads for expense calculation

---

### 9. order_items
**Purpose:** Line items for orders  
**Created:** 2025-10-24  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `quantity` - Integer
- `price` - Decimal(10,2)
- `bp_earned` - Integer
- `timestamps`

---

## LGR (Loyalty Growth Rewards) System

### 10. lgr_cycles
**Purpose:** Tracks 70-day LGR reward cycles  
**Created:** 2025-10-31  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `start_date` - Date
- `end_date` - Date
- `status` - Enum (active, completed, suspended, terminated)
- `active_days` - Integer
- `total_earned_lgc` - Decimal(10,2)
- `daily_rate` - Decimal(10,2)
- `suspension_reason` - Text (nullable)
- `suspended_at` - Timestamp (nullable)
- `completed_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`
- Composite: `(user_id, status)`
- Index: `start_date`, `end_date`

---

### 11. lgr_activities
**Purpose:** Tracks daily activities for LGR eligibility  
**Created:** 2025-10-31  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `lgr_cycle_id` - Foreign key to lgr_cycles
- `activity_date` - Date
- `activity_type` - Enum (learning_module, marketplace_purchase, marketplace_sale, event_attendance, platform_task, community_engagement, business_plan, quiz_completion)
- `activity_description` - String
- `activity_metadata` - JSON (nullable)
- `lgc_earned` - Decimal(10,2)
- `verified` - Boolean
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `lgr_cycle_id`
- Composite: `(user_id, activity_date)`, `(lgr_cycle_id, activity_date)`
- Unique: `(user_id, activity_date, activity_type)`

---

### 12. lgr_pools
**Purpose:** Tracks LGR reward pool balance  
**Created:** 2025-10-31  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `pool_date` - Date (unique)
- `opening_balance` - Decimal(12,2)
- `contributions` - Decimal(12,2)
- `allocations` - Decimal(12,2)
- `closing_balance` - Decimal(12,2)
- `reserve_amount` - Decimal(12,2)
- `available_for_distribution` - Decimal(12,2)
- `contribution_sources` - JSON (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Unique: `pool_date`
- Index: `pool_date`

---

### 13. lgr_payouts
**Purpose:** Tracks LGC credit distributions  
**Created:** 2025-10-31  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `lgr_cycle_id` - Foreign key to lgr_cycles
- `lgr_pool_id` - Foreign key to lgr_pools (nullable)
- `payout_date` - Date
- `lgc_amount` - Decimal(10,2)
- `pool_balance_before` - Decimal(12,2)
- `pool_balance_after` - Decimal(12,2)
- `proportional_adjustment` - Boolean
- `adjustment_factor` - Decimal(5,4) (nullable)
- `status` - Enum (pending, credited, failed)
- `notes` - Text (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `lgr_cycle_id`, `lgr_pool_id`
- Composite: `(user_id, payout_date)`, `(lgr_cycle_id, status)`

**Critical Notes:**
- ✅ **LGR CREDITS ARE SEPARATE:** LGR payouts tracked separately from wallet balance (by design)
- LGR credits stored in `users.lgr_balance` and `users.lgr_withdrawable`
- Users must manually transfer LGR credits to wallet (creates transaction record)
- Transfer transaction type: `lgr_transfer` or `wallet_topup` with LGR source

---

### 14. lgr_qualifications
**Purpose:** Tracks member LGR qualification status  
**Created:** 2025-10-31  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users (unique)
- `starter_package_completed` - Boolean
- `starter_package_completed_at` - Timestamp (nullable)
- `training_completed` - Boolean
- `training_completed_at` - Timestamp (nullable)
- `first_level_members` - Integer
- `network_requirement_met` - Boolean
- `network_requirement_met_at` - Timestamp (nullable)
- `activities_completed` - Integer
- `activity_requirement_met` - Boolean
- `activity_requirement_met_at` - Timestamp (nullable)
- `fully_qualified` - Boolean
- `fully_qualified_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Unique: `user_id`
- Index: `fully_qualified`

---

## Loans

### 15. loan_applications
**Purpose:** Tracks loan applications  
**Created:** 2025-11-05  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `amount` - Decimal(10,2)
- `purpose` - Text
- `repayment_plan` - Enum (30_days, 60_days, 90_days)
- `status` - Enum (pending, approved, rejected)
- `reviewed_by` - Foreign key to users (nullable)
- `reviewed_at` - Timestamp (nullable)
- `rejection_reason` - Text (nullable)
- `admin_notes` - Text (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`, `reviewed_by`
- Composite: `(user_id, status)`
- Index: `created_at`

**Related User Columns:**
- `users.loan_balance` - Current loan balance
- `users.total_loan_issued` - Total loans issued
- `users.total_loan_repaid` - Total repaid
- `users.loan_issued_at` - When loan was issued
- `users.loan_issued_by` - Who issued the loan
- `users.loan_notes` - Loan notes

**Critical Notes:**
- ⚠️ **LOAN TRANSACTIONS:** Loan disbursements and repayments should be recorded in `transactions` table
- Transaction types: `loan_disbursement`, `loan_repayment`
- `WalletService` (LEGACY) includes these in balance calculation
- ⚠️ **ISSUE:** `UnifiedWalletService` (PRIMARY) does NOT currently include loan transactions
- **ACTION REQUIRED:** Add loan transaction support to `UnifiedWalletService`

---

## Performance & Bonuses

### 16. performance_bonuses
**Purpose:** Tracks performance-based bonuses  
**Created:** 2025-01-15  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `user_id` - Foreign key to users
- `period_start` - Date
- `period_end` - Date
- `team_volume` - Decimal(15,2)
- `bonus_rate` - Decimal(5,2)
- `bonus_amount` - Decimal(10,2)
- `status` - Enum (eligible, paid, cancelled)
- `paid_at` - Timestamp (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Foreign: `user_id`
- Composite: `(user_id, period_start, period_end)`, `(status, period_start)`
- Unique: `(user_id, period_start, period_end)`

**Critical Notes:**
- ⚠️ **NOT IN WALLET SERVICES:** Performance bonuses not included in either wallet service
- May need to be added to earnings calculation

---

## Receipts & Documentation

### 17. receipts
**Purpose:** Tracks generated receipts  
**Created:** 2025-10-27  
**Status:** ACTIVE

**Columns:**
- `id` - Primary key
- `receipt_number` - String (unique)
- `user_id` - Foreign key to users
- `type` - Enum (payment, starter_kit, wallet, purchase)
- `amount` - Decimal(10,2)
- `payment_method` - String (nullable)
- `transaction_reference` - String (nullable)
- `description` - Text (nullable)
- `pdf_path` - String (nullable)
- `emailed` - Boolean
- `emailed_at` - Timestamp (nullable)
- `metadata` - JSON (nullable)
- `timestamps`

**Indexes:**
- Primary: `id`
- Unique: `receipt_number`
- Composite: `(user_id, type)`

---

## User Balance Tracking

### Users Table Finance Columns

**Wallet & Balance:**
- `starter_kit_shop_credit` - Decimal(10,2) - Shop credit balance
- `starter_kit_credit_expiry` - Date - Shop credit expiry
- `life_points` - Integer - Lifetime points (LP)
- `bonus_points` - Integer - Monthly activity points (BP)
- `loan_balance` - Decimal(10,2) - Current loan balance
- `total_loan_issued` - Decimal(10,2) - Total loans issued
- `total_loan_repaid` - Decimal(10,2) - Total repaid
- `loan_issued_at` - Timestamp
- `loan_issued_by` - Foreign key to users
- `loan_notes` - Text

**LGR Tracking:**
- `lgr_balance` - Decimal(10,2) - LGR credit balance
- `lgr_withdrawable` - Decimal(10,2) - Withdrawable LGR amount
- `lgr_custom_percentage` - Decimal(5,2) - Custom withdrawal percentage

**Starter Kit:**
- `has_starter_kit` - Boolean
- `starter_kit_tier` - String (lite, basic, growth_plus, pro)
- `starter_kit_purchased_at` - Timestamp
- `library_access_until` - Timestamp

---

## Critical Issues Summary

### 1. Deposit Sync Issue (CRITICAL)
**Problem:** Deposits in `member_payments` not synced to `transactions`  
**Impact:** Negative wallet balances  
**Solution:** Create `DepositSyncService` to sync on verification

### 2. Withdrawal Double Counting (HIGH)
**Problem:** Withdrawals counted from both `withdrawals` and `transactions` tables  
**Impact:** Incorrect balance calculation  
**Solution:** Standardize on one source or ensure no duplication

### 3. Shop Credit Not in Transactions (MEDIUM)
**Problem:** Shop credit expenses not recorded in transactions  
**Impact:** Incomplete transaction history  
**Solution:** Record shop credit usage in transactions table

### 4. LGR Credits Separate from Wallet (NOT AN ISSUE)
**Status:** ✅ Working as designed  
**Design:** LGR credits are intentionally separate from wallet balance  
**Process:** Users manually transfer LGR credits to wallet when needed  
**Transaction:** Transfer creates record in `transactions` table with type `lgr_transfer` or `wallet_topup`

### 5. Performance Bonuses Not in Wallet (LOW)
**Problem:** Performance bonuses not included in wallet services  
**Impact:** Incomplete earnings calculation  
**Solution:** Add to earnings calculation

### 6. Cache Not Invalidated (CRITICAL)
**Problem:** Wallet balance cached for 120 seconds, not cleared after transactions  
**Impact:** Stale balance displayed  
**Solution:** Call `clearCache()` after every transaction

### 7. Dual Wallet Services (LOW - Mostly Resolved)
**Status:** 95% complete - `UnifiedWalletService` is primary  
**Remaining Work:** 
- Replace `WalletService` in 2 remaining files:
  - `app/Application/StarterKit/UseCases/GiftStarterKitUseCase.php`
  - `app/Services/StarterKitService.php`
- Remove unused injection from `GeneralWalletController`
- Mark `WalletService` as deprecated

**Current Usage:**
- ✅ `UnifiedWalletService`: Used in all wallet controllers, dashboards, module subscriptions (95%)
- ⚠️ `WalletService`: Only used in 2 files (5%)

**Missing Feature:**
- ⚠️ `UnifiedWalletService` doesn't include loan transactions (but `WalletService` does)
- Need to add loan support to `UnifiedWalletService` before full deprecation

---

## Recommended Actions

### Immediate (Today)
1. Add cache invalidation after all transactions
2. Create `DepositSyncService` to sync member_payments to transactions
3. Run reconciliation script to fix existing negative balances

### Short Term (This Week)
1. Add loan transaction support to `UnifiedWalletService`
2. Replace `WalletService` in remaining 2 files with `UnifiedWalletService`
3. Fix withdrawal double-counting issue
4. Verify LGR transfer transactions are properly recorded
5. Add performance bonuses to earnings calculation (if they should be in wallet)

### Long Term (This Month)
1. Implement domain-driven architecture per FINANCIAL_SYSTEM_ARCHITECTURE.md
2. Create unified transaction recording service
3. Implement proper event sourcing for financial operations
4. Add comprehensive audit trail

---

## Related Documentation

- `FINANCIAL_SYSTEM_ARCHITECTURE.md` - Complete system redesign plan
- `IMMEDIATE_FIXES_REQUIRED.md` - Emergency fixes for production
- `structure.md` - Overall project structure
- `domain-design.md` - Domain-driven design guidelines

---

**Maintained By:** Development Team  
**Review Frequency:** Monthly or after major changes  
**Last Review:** 2026-02-28
