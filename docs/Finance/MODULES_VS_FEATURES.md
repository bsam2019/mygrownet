# Modules vs Features - Financial Tracking

**Last Updated:** December 2024  
**Status:** Reference Document

## Core Concept

In MyGrowNet's financial system, it's critical to distinguish between **Modules** (business units) and **Features** (platform capabilities).

## What is a Module?

A **module** is an **independent business unit** that:
1. Generates its own revenue stream
2. Has its own expense allocation
3. Can be analyzed for profitability independently
4. Operates as a distinct product/service offering

### Current Business Unit Modules

| Code | Name | Description | Revenue Source |
|------|------|-------------|----------------|
| `grownet` | GrowNet | MLM/Network marketing platform | Subscriptions, membership fees |
| `growbuilder` | GrowBuilder | Business building tools | Service fees, subscriptions |
| `growfinance` | GrowFinance | Financial services & lending | Interest, service fees |
| `bizboost` | BizBoost | Business acceleration services | Consulting fees, packages |
| `growmarket` | GrowMarket | E-commerce marketplace | Transaction fees, commissions |
| `invoice_generator` | Invoice Generator | Professional invoicing service | Subscription fees |
| `learning_center` | Learning Center | Online courses & training | Course sales, subscriptions |
| `platform` | Platform Operations | Core infrastructure | N/A (operational expenses only) |

## What is NOT a Module?

**Features** are platform capabilities that support the business but are not independent business units:

### Platform Features (NOT Modules)

| Feature | Why It's NOT a Module |
|---------|----------------------|
| **Wallet** | Payment mechanism used across all modules |
| **Commissions** | Payout mechanism, not a revenue source |
| **LGR (Loyalty Rewards)** | Incentive program, not a business unit |
| **Loans** | Financial feature within GrowFinance module |
| **Profit Sharing** | Distribution mechanism, not revenue source |
| **Starter Kits** | Onboarding tool, not independent business |
| **Workshops** | Part of Learning Center module |
| **Coaching** | Part of Learning Center or BizBoost |

## Why This Matters

### For Financial Reporting
When filtering P&L or Budget reports by module, you want to see:
- ✅ "How profitable is GrowNet?"
- ✅ "What are GrowMarket's expenses?"
- ✅ "Is Learning Center meeting revenue targets?"

You DON'T want to see:
- ❌ "Wallet profitability" (wallet is used by all modules)
- ❌ "Commission expenses" (commissions are paid across all modules)
- ❌ "LGR revenue" (LGR is a loyalty feature, not a product)

### For Budget Planning
Budgets should be allocated to business units:
- ✅ "GrowNet Marketing Budget: K50,000"
- ✅ "Learning Center Content Creation: K30,000"
- ✅ "Platform Infrastructure: K100,000"

NOT to features:
- ❌ "Wallet Budget" (wallet costs are infrastructure)
- ❌ "Commission Budget" (commissions are module expenses)

## Transaction Tagging

When recording transactions, `transaction_source` should reflect the **business unit** that generated the transaction:

### How Module Tracking Works

The system uses the `transaction_source` column in the `transactions` table to track which module generated each transaction. This enables:
1. Module-specific P&L analysis
2. Revenue attribution by business unit
3. Expense allocation to modules
4. Module profitability tracking

### Database Schema

```sql
transactions table:
- transaction_source VARCHAR(50)  -- Module code (e.g., 'grownet', 'bizboost')
- module_reference VARCHAR(100)   -- Module's internal reference ID
- transaction_type VARCHAR(50)    -- Type of transaction
- amount DECIMAL(15,2)            -- Transaction amount
```

### Automatic Module Tagging

When a module creates a transaction, it should automatically set the `transaction_source`:

```php
// Example: GrowNet subscription payment
Transaction::create([
    'user_id' => $user->id,
    'transaction_type' => 'subscription_payment',
    'transaction_source' => 'grownet',  // ✅ Module code
    'amount' => 500,
    'status' => 'completed',
    'description' => 'GrowNet monthly subscription',
]);

// Example: GrowBuilder website payment
Transaction::create([
    'user_id' => $user->id,
    'transaction_type' => 'website_payment',
    'transaction_source' => 'growbuilder',  // ✅ Module code
    'module_reference' => $site->id,        // Site ID for reference
    'amount' => 49,
    'status' => 'completed',
]);

// Example: CMS expense (via sync)
Transaction::create([
    'transaction_type' => 'marketing_expense',
    'transaction_source' => 'cms',  // ✅ CMS module
    'cms_expense_id' => $expense->id,
    'amount' => 5000,
    'status' => 'completed',
]);
```

### Module-Specific Transaction Types

Each module should use consistent transaction types:

**GrowNet:**
- `subscription_payment` (revenue)
- `commission_earned` (expense - paid to members)
- `profit_share` (expense - community rewards)

**GrowBuilder:**
- `website_payment` (revenue)
- `website_upgrade` (revenue)
- `domain_fee` (revenue)

**GrowFinance:**
- `service_fee` (revenue)
- `loan_disbursement` (expense)
- `loan_repayment` (revenue)

**BizBoost:**
- `marketing_service` (revenue)
- `ai_credits` (revenue)

**GrowMarket:**
- `product_sale` (revenue)
- `transaction_fee` (revenue)
- `seller_payout` (expense)

**CMS:**
- `subscription_payment` (revenue)
- `marketing_expense` (expense)
- `office_expense` (expense)
- `infrastructure_expense` (expense)

**Platform:**
- `infrastructure_expense` (expense)
- `legal_expense` (expense)
- `general_expense` (expense)

### Implementation in Controllers

When creating transactions in your controllers, always set `transaction_source`:

```php
// In GrowNetController
public function processSubscription(Request $request)
{
    $transaction = Transaction::create([
        'user_id' => $request->user()->id,
        'transaction_type' => 'subscription_payment',
        'transaction_source' => 'grownet',  // ✅ Always set module
        'amount' => $package->price,
        'status' => 'completed',
    ]);
}

// In GrowBuilderController
public function processSitePayment(Request $request, Site $site)
{
    $transaction = Transaction::create([
        'user_id' => $request->user()->id,
        'transaction_type' => 'website_payment',
        'transaction_source' => 'growbuilder',  // ✅ Always set module
        'module_reference' => $site->id,        // ✅ Link to site
        'amount' => $tier->price,
        'status' => 'completed',
    ]);
}
```

### Querying by Module

The financial services use `transaction_source` to filter by module:

```php
// Get GrowNet revenue
$revenue = Transaction::where('transaction_source', 'grownet')
    ->whereIn('transaction_type', ['subscription_payment', 'upgrade_fee'])
    ->where('status', 'completed')
    ->sum('amount');

// Get BizBoost expenses
$expenses = Transaction::where('transaction_source', 'bizboost')
    ->whereIn('transaction_type', ['commission_earned', 'refund'])
    ->where('status', 'completed')
    ->sum('amount');
```

### Cross-Module Transactions

Some transactions involve multiple modules. Use the originating module:

```php
// Commission paid from GrowNet subscription
Transaction::create([
    'transaction_type' => 'commission_earned',
    'transaction_source' => 'grownet',  // ✅ Originated from GrowNet
    'amount' => 50,
    'description' => 'Referral commission from GrowNet subscription',
]);

// Wallet top-up (use platform or specific module where it will be used)
Transaction::create([
    'transaction_type' => 'wallet_topup',
    'transaction_source' => 'platform',  // ✅ Platform-wide feature
    'amount' => 1000,
]);
```

### Correct Tagging Examples

```php
// ✅ GrowNet subscription payment
Transaction::create([
    'transaction_type' => 'subscription_payment',
    'transaction_source' => 'grownet',
    'amount' => 500,
]);

// ✅ Learning Hub course purchase
Transaction::create([
    'transaction_type' => 'course_purchase',
    'transaction_source' => 'learning-hub',
    'amount' => 1000,
]);

// ✅ Platform infrastructure expense
Transaction::create([
    'transaction_type' => 'infrastructure_expense',
    'transaction_source' => 'platform',
    'amount' => 5000,
]);

// ✅ CMS subscription
Transaction::create([
    'transaction_type' => 'subscription_payment',
    'transaction_source' => 'cms',
    'amount' => 299,
]);
```

### Incorrect Tagging Examples

```php
// ❌ Wrong - using feature name instead of module
Transaction::create([
    'transaction_type' => 'commission_earned',
    'transaction_source' => 'commissions',  // ❌ Not a module!
    'amount' => 200,
]);
// Should be: transaction_source => 'grownet' (the module that generated the commission)

// ❌ Wrong - using wallet as module
Transaction::create([
    'transaction_type' => 'wallet_topup',
    'transaction_source' => 'wallet',  // ❌ Wallet is not a module!
    'amount' => 1000,
]);
// Should be: transaction_source => 'platform' or the specific module

// ❌ Wrong - no transaction_source
Transaction::create([
    'transaction_type' => 'subscription_payment',
    // Missing transaction_source!
    'amount' => 500,
]);
// Should include: transaction_source => 'grownet'
```

## Implementation Guidelines

### For Developers

When adding new revenue sources, ask:
1. **Is this an independent business unit?**
   - Yes → Create a new module
   - No → Tag to existing module or platform

2. **Does it have its own P&L?**
   - Yes → It's a module
   - No → It's a feature

3. **Can it be sold/marketed independently?**
   - Yes → Likely a module
   - No → Likely a feature

### For Financial Reporting

When analyzing finances:
1. **Module-level analysis** - Use module filter to see business unit performance
2. **Platform-level analysis** - View all modules combined (no filter)
3. **Feature analysis** - Query transactions by type, not by "module"

## Database Schema

### financial_modules Table
```sql
CREATE TABLE financial_modules (
    id BIGINT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,           -- e.g., 'grownet', 'learning_center'
    name VARCHAR(100),                 -- e.g., 'GrowNet', 'Learning Center'
    description TEXT,
    is_revenue_module BOOLEAN,         -- TRUE for business units, FALSE for platform
    is_active BOOLEAN,
    display_order INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Seeding Modules
```bash
# Seed correct business unit modules
php artisan db:seed --class=FinancialModulesSeeder
```

## Common Mistakes to Avoid

### ❌ Mistake 1: Creating Module for Every Feature
```php
// Wrong
DB::table('financial_modules')->insert([
    ['code' => 'wallet', 'name' => 'Wallet'],
    ['code' => 'commissions', 'name' => 'Commissions'],
    ['code' => 'lgr', 'name' => 'LGR'],
]);
```

### ✅ Correct: Only Business Units
```php
// Correct
DB::table('financial_modules')->insert([
    ['code' => 'grownet', 'name' => 'GrowNet'],
    ['code' => 'learning_center', 'name' => 'Learning Center'],
    ['code' => 'growmarket', 'name' => 'GrowMarket'],
]);
```

### ❌ Mistake 2: Tagging Feature Transactions to Non-Existent Modules
```php
// Wrong
$transaction->module_id = Module::findByCode('wallet')->id; // No such module!
```

### ✅ Correct: Tag to Originating Business Unit
```php
// Correct
$transaction->module_id = $user->activeModule->id; // The module user is using
// Or
$transaction->module_id = Module::findByCode('platform')->id; // For platform-wide
```

## Testing Module Configuration

Run this check to ensure modules are correctly configured:

```bash
php artisan tinker

# Check active modules
>>> App\Models\Module::where('is_active', true)->pluck('name', 'code');

# Should return only business units:
=> [
     "grownet" => "GrowNet",
     "growbuilder" => "GrowBuilder",
     "growfinance" => "GrowFinance",
     "bizboost" => "BizBoost",
     "growmarket" => "GrowMarket",
     "invoice_generator" => "Invoice Generator",
     "learning_center" => "Learning Center",
     "platform" => "Platform Operations",
   ]

# Should NOT include: wallet, commissions, lgr, loans, etc.
```

## Summary

**Remember:**
- **Modules** = Independent business units with P&L
- **Features** = Platform capabilities supporting modules
- **Module filtering** = Analyze business unit performance
- **Feature analysis** = Query by transaction type, not module

When in doubt, ask: "Can this be sold as a standalone product/service?" If yes, it's a module. If no, it's a feature.

## Related Documentation

- [CMS Financial Integration](./CMS_FINANCIAL_INTEGRATION_ANALYSIS.md)
- [Transaction-Based Reporting](./TRANSACTION_BASED_REPORTING.md)
- [Financial System Architecture](./FINANCIAL_SYSTEM_ARCHITECTURE.md)


## Revenue Recognition Principles

### What IS Revenue

Revenue is recognized when users spend money on products or services:

- ✅ **Subscription payments** - Users paying for module access
- ✅ **Product purchases** - Starter kits, learning packs, shop items, marketplace products
- ✅ **Service payments** - Workshops, coaching, GrowBuilder services
- ✅ **Loan repayments** - Platform loans being repaid (money coming back to platform)

### What is NOT Revenue

- ❌ **Wallet deposits/top-ups** - These are LIABILITIES (money owed to users)
- ❌ **Deposits** - Money held in trust until spent
- ❌ **Pending transactions** - Not yet completed
- ❌ **Transfers between accounts** - No value created

### Accounting Principle: Deposits are Liabilities

**Key Concept:** When users deposit money into their wallet, the platform has NOT earned revenue yet. The platform owes that money back to the user until they spend it on products/services.

#### Example Flow

**Step 1: User Deposits K1,000**
```
Assets:     Cash +K1,000
Liabilities: Wallet Balance +K1,000
Revenue:    K0 (no revenue yet!)
```

**Step 2: User Spends K500 on GrowNet Subscription**
```
Assets:     Cash K1,000 (no change)
Liabilities: Wallet Balance -K500 (now K500)
Revenue:    +K500 (NOW we recognize revenue!)
```

**Step 3: User Still Has K500 in Wallet**
```
Assets:     Cash K1,000
Liabilities: Wallet Balance K500 (still owe user)
Revenue:    K500 (only what was spent)
```

### Loan Accounting

**Important:** Loans are financial instruments, NOT operational expenses or cost of sales.

#### Proper Accounting Treatment

**Loan Disbursements** (giving loans to members):
- **Balance Sheet:** Asset (Loans Receivable)
- **Cash Flow:** Cash outflow (Investing/Financing activity)
- **P&L Impact:** No immediate P&L impact (it's an asset exchange: Cash → Loan Receivable)
- Tracked as `loan_disbursement` transaction type

**Loan Repayments** (receiving repayments from members):
- **Balance Sheet:** Reduces Loans Receivable, increases Cash
- **Cash Flow:** Cash inflow (Investing/Financing activity)
- **P&L Impact:** Principal repayment = No P&L impact; Interest = Revenue
- Tracked as `loan_repayment` transaction type

**Interest Income** (if applicable):
- **Revenue** when earned
- Separate from principal repayment
- Tracked as `interest_income` transaction type

#### Simplified P&L Approach (Current Implementation)

For simplified P&L reporting (not full balance sheet accounting), the system currently treats:
- Loan disbursements = **Operating Expense** (cash out)
- Loan repayments = **Other Income** (cash in)

**Note:** This is a simplified cash-basis approach. For proper accrual accounting, loans should be tracked on the balance sheet as assets, not as expenses.

#### Why Loans Are NOT Cost of Sales

**Cost of Sales (COGS)** includes:
- Direct costs to produce/deliver products sold
- Inventory costs, manufacturing costs, direct labor
- Examples: Starter kit production costs, course content creation

**Loans are NOT COGS because:**
- They are financial instruments, not product costs
- Money is expected to be repaid (it's an asset, not consumed)
- They don't directly relate to product/service delivery
- Should be classified as "Financial Activities" or "Other Expenses"

#### Expense Classification

If treating loans as expenses (simplified approach):
- ✅ **Operating Expenses** or **Financial Expenses**
- ❌ NOT Cost of Sales
- ❌ NOT included in Gross Profit calculation

**Proper P&L Structure:**
```
Revenue                          K100,000
- Cost of Sales                  (K30,000)  ← Product/service direct costs
= Gross Profit                    K70,000

- Operating Expenses             (K40,000)  ← Salaries, marketing, etc.
- Loan Disbursements             (K10,000)  ← Financial activity (if expensed)
= Operating Profit                K20,000

+ Loan Repayments                 K5,000   ← Financial income
+ Interest Income                 K1,000   ← Revenue
= Net Profit                      K26,000
```

### Implementation in Code

The system correctly excludes deposits from revenue calculations:

```php
// ProfitLossTrackingService.php - getRevenue() method
private function getRevenue(Carbon $startDate, Carbon $endDate, ?int $moduleId = null): array
{
    // Revenue transaction types (EXCLUDING deposits - they are liabilities)
    $revenueTypes = [
        // Product sales - actual revenue
        TransactionType::SUBSCRIPTION_PAYMENT->value,
        TransactionType::STARTER_KIT_PURCHASE->value,
        TransactionType::STARTER_KIT_UPGRADE->value,
        TransactionType::SHOP_PURCHASE->value,
        TransactionType::MARKETPLACE_PURCHASE->value,
        TransactionType::LEARNING_PACK_PURCHASE->value,
        
        // Service payments - actual revenue
        TransactionType::SERVICE_PAYMENT->value,
        TransactionType::WORKSHOP_PAYMENT->value,
        TransactionType::COACHING_PAYMENT->value,
        TransactionType::GROWBUILDER_PAYMENT->value,
        
        // Loan repayments - revenue (platform loans being repaid)
        TransactionType::LOAN_REPAYMENT->value,
        
        // Interest income from loans (if tracked separately)
        // TransactionType::INTEREST_INCOME->value,
        
        // NOTE: WALLET_TOPUP and DEPOSIT are NOT revenue!
        // They are liabilities (money owed to users) until spent on products/services
        
        // NOTE: Loan disbursements are NOT included here!
        // They are financial activities (assets), not revenue or expenses in proper accounting
        // For simplified P&L, they're tracked separately as "Financial Expenses"
    ];
    
    // Query only completed transactions of revenue types
    $query = DB::table('transactions')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('status', TransactionStatus::COMPLETED->value)
        ->whereIn('transaction_type', $revenueTypes);
    
    // Apply module filter if provided
    if ($moduleId) {
        $query->where('transaction_source', $moduleId);
    }
    
    // ... rest of calculation
}
```

### Why This Matters

**Incorrect Revenue Recognition:**
```php
// ❌ WRONG - Counting deposits as revenue
$revenue = Transaction::where('transaction_type', 'wallet_topup')->sum('amount');
// This inflates revenue! Deposits are liabilities, not revenue.
```

**Correct Revenue Recognition:**
```php
// ✅ CORRECT - Only count actual product/service sales
$revenue = Transaction::whereIn('transaction_type', [
    'subscription_payment',
    'starter_kit_purchase',
    'shop_purchase',
    'loan_repayment',
])->sum('amount');
// This accurately reflects earned revenue.
```

### Financial Impact

If deposits were counted as revenue:
- **Overstated revenue** - Platform appears more profitable than it is
- **Hidden liabilities** - Wallet balances are obligations to users
- **Cash flow confusion** - Deposits are cash in, but not earnings
- **Tax implications** - Could pay tax on money that's not actually earned

### Correct Approach

1. **Deposits** → Record as liability (wallet balance)
2. **Purchases** → Reduce liability, recognize revenue
3. **Withdrawals** → Reduce liability, reduce cash (no revenue impact)
4. **Loan disbursements** → Expense (cash out)
5. **Loan repayments** → Revenue (cash in)

### Module Subscription Integration

Module subscriptions are correctly integrated with transaction tracking:

**When user subscribes to a module:**
```php
// ModuleSubscriptionCheckoutController.php
DB::table('transactions')->insert([
    'user_id' => $user->id,
    'transaction_type' => 'subscription_payment',
    'transaction_source' => $validated['module_id'],  // ✅ Tracks which module
    'amount' => -$amount,  // Negative for debit from wallet
    'status' => 'completed',
    'description' => "Subscription: {$module} - {$tier} tier",
    'created_at' => now(),
]);
```

**When user upgrades subscription:**
```php
DB::table('transactions')->insert([
    'user_id' => $user->id,
    'transaction_type' => 'subscription_upgrade',
    'transaction_source' => $validated['module_id'],  // ✅ Tracks which module
    'amount' => -$amount,
    'status' => 'completed',
    'description' => "Upgrade: {$module} to {$tier} tier",
    'created_at' => now(),
]);
```

This ensures:
- ✅ Revenue is attributed to the correct module
- ✅ Module P&L reports show subscription revenue
- ✅ Budget vs actual comparisons work correctly
- ✅ Module profitability can be accurately calculated

### Verification Checklist

To ensure correct revenue recognition:

1. ✅ Deposits NOT in revenue types list
2. ✅ Only completed transactions counted
3. ✅ Subscription payments tracked with `transaction_source`
4. ✅ Loan disbursements counted as expenses
5. ✅ Loan repayments counted as revenue
6. ✅ Module filter uses `transaction_source` field
7. ✅ All module controllers set `transaction_source` when creating transactions

### Testing Revenue Recognition

```bash
php artisan tinker

# Check that deposits are NOT counted as revenue
>>> use App\Services\ProfitLossTrackingService;
>>> $service = app(ProfitLossTrackingService::class);
>>> $pl = $service->getProfitLossStatement('month');
>>> $pl['revenue']['breakdown'];

# Should NOT see 'wallet_topup' or 'deposit' in revenue breakdown
# Should see: subscription_payment, starter_kit_purchase, loan_repayment, etc.
```

## Summary: Revenue Recognition

**Remember:**
- **Deposits** = Liabilities (money owed to users)
- **Purchases** = Revenue (money earned from products/services)
- **Loan disbursements** = Expenses (money given out)
- **Loan repayments** = Revenue (money coming back)
- **Module subscriptions** = Revenue attributed to specific module via `transaction_source`

**Golden Rule:** Revenue is only recognized when value is delivered to the user (product sold, service rendered, loan repaid).


## Financial Statement Structure

### Proper P&L Categories

The platform's financial reporting should follow this structure:

#### 1. Revenue (Top Line)
- Subscription payments
- Product sales (starter kits, learning packs, shop items)
- Service fees (workshops, coaching, GrowBuilder)
- Marketplace transaction fees
- **NOT deposits** (those are liabilities)

#### 2. Cost of Sales (COGS)
Direct costs to deliver products/services:
- Product manufacturing/procurement costs
- Course content creation costs
- Direct service delivery costs
- Payment processing fees (for product sales)
- **NOT loan disbursements** (those are financial activities)
- **NOT commissions** (those are operating expenses)

#### 3. Gross Profit
```
Gross Profit = Revenue - Cost of Sales
Gross Margin % = (Gross Profit / Revenue) × 100
```

#### 4. Operating Expenses
Costs to run the business:
- Salaries and wages
- Marketing and advertising
- Office expenses
- Infrastructure costs
- Legal and professional fees
- Commissions paid to members
- LGR rewards
- Community profit sharing
- **NOT loan disbursements** (separate category)

#### 5. Operating Profit (EBITDA)
```
Operating Profit = Gross Profit - Operating Expenses
```

#### 6. Financial Activities (Separate Section)
- Loan disbursements (cash out)
- Loan repayments (cash in)
- Interest income
- Interest expense

#### 7. Net Profit
```
Net Profit = Operating Profit + Financial Income - Financial Expenses
```

### Example P&L Statement

```
MyGrowNet Platform - Profit & Loss Statement
Period: January 2025

REVENUE
  Subscriptions                    K 50,000
  Starter Kit Sales                K 30,000
  Learning Pack Sales              K 20,000
  Workshop Fees                    K 15,000
  GrowBuilder Services             K 25,000
  ----------------------------------------
  Total Revenue                    K 140,000

COST OF SALES
  Starter Kit Costs                K  9,000
  Course Content Creation          K  5,000
  Payment Processing Fees          K  2,800
  ----------------------------------------
  Total Cost of Sales              K 16,800
  
GROSS PROFIT                       K 123,200
Gross Margin                       88.0%

OPERATING EXPENSES
  Salaries & Wages                 K 40,000
  Marketing & Advertising          K 15,000
  Office Expenses                  K  5,000
  Infrastructure                   K 10,000
  Legal & Professional             K  3,000
  Member Commissions               K 12,000
  LGR Rewards                      K  8,000
  Community Profit Sharing         K  5,000
  ----------------------------------------
  Total Operating Expenses         K 98,000

OPERATING PROFIT                   K 25,200
Operating Margin                   18.0%

FINANCIAL ACTIVITIES
  Loan Disbursements              (K 10,000)
  Loan Repayments                  K  3,000
  Interest Income                  K    500
  ----------------------------------------
  Net Financial Activities        (K  6,500)

NET PROFIT                         K 18,700
Net Margin                         13.4%
```

### Key Metrics

**Gross Margin:** Should be high (70-90%) for digital products/services
**Operating Margin:** Target 15-25% for sustainable growth
**Net Margin:** Target 10-20% after all expenses

### What Goes Where

| Item | Category | Reason |
|------|----------|--------|
| Subscription payments | Revenue | Money earned from services |
| Product sales | Revenue | Money earned from products |
| Deposits | NOT Revenue | Liability until spent |
| Starter kit costs | COGS | Direct cost to deliver product |
| Commissions | Operating Expense | Cost to acquire/retain customers |
| Salaries | Operating Expense | Cost to run business |
| Loan disbursements | Financial Activity | Asset exchange, not expense |
| Loan repayments | Financial Activity | Asset recovery, not revenue |
| Withdrawals | Operating Expense | Member payout obligation |

### Implementation Note

The current system uses a **simplified cash-basis approach** where:
- Loan disbursements are treated as expenses (cash out)
- Loan repayments are treated as income (cash in)

For **proper accrual accounting**, loans should be:
- Tracked on balance sheet as "Loans Receivable" (asset)
- Not included in P&L until written off or interest earned
- Reported in cash flow statement under "Investing Activities"

### Future Enhancement

To implement proper loan accounting:
1. Create `loans_receivable` table to track outstanding loans
2. Separate principal from interest in repayments
3. Track loan aging and default risk
4. Generate balance sheet showing loans as assets
5. Report loan activity in cash flow statement, not P&L

This would provide more accurate financial reporting and better visibility into the platform's true profitability.
