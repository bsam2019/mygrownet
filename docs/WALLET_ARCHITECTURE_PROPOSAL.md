# Wallet & Earnings Architecture - Best Practices

**Date:** November 9, 2025  
**Status:** 📋 Proposal for Long-Term Architecture

---

## Problem Analysis

### Current Issues:
1. **Duplication:** Both WalletService and EarningsService calculate earnings
2. **Unclear Boundaries:** What belongs where?
3. **Future Scalability:** How to handle products, subscriptions, etc.?

### Current Duplication:
```php
// WalletService
public function calculateTotalEarnings(User $user): float
{
    $commissionEarnings = $user->referralCommissions()->where('status', 'paid')->sum('amount');
    $profitEarnings = $user->profitShares()->sum('amount');
    // ...
}

// EarningsService
public function calculateTotalEarnings(User $user): float
{
    return $this->getCommissionEarnings($user) 
         + $this->getProfitShareEarnings($user)
         + $this->getBonusEarnings($user);
}
```

---

## Proposed Architecture

### Single Responsibility Principle

```
┌─────────────────────────────────────────────────────┐
│                  WalletService                      │
│  Responsibility: Wallet Balance & Transactions     │
│                                                     │
│  - calculateBalance()                               │
│  - getTransactionHistory()                          │
│  - recordTransaction()                              │
│  - getWalletBreakdown()                             │
│                                                     │
│  USES: EarningsService (for earnings data)         │
└─────────────────────────────────────────────────────┘
                        ↓ depends on
┌─────────────────────────────────────────────────────┐
│                 EarningsService                     │
│  Responsibility: Calculate ALL Earnings             │
│                                                     │
│  - calculateTotalEarnings()                         │
│  - getEarningsBreakdown()                           │
│  - getCommissionEarnings()                          │
│  - getProfitShareEarnings()                         │
│  - getProductSalesEarnings()                        │
│  - getSubscriptionEarnings()                        │
│  - getBonusEarnings()                               │
│                                                     │
│  PURE: No wallet logic, only earnings calculation   │
└─────────────────────────────────────────────────────┘
```

---

## Clear Separation of Concerns

### EarningsService (What you EARNED)
**Purpose:** Calculate income from all sources

**Responsibilities:**
- ✅ Referral commissions (7 levels)
- ✅ LGR profit sharing
- ✅ Product sales commissions
- ✅ Subscription commissions
- ✅ Starter kit commissions
- ✅ Achievement bonuses
- ✅ Team performance bonuses
- ✅ Workshop commissions
- ✅ Venture Builder dividends

**Does NOT:**
- ❌ Calculate wallet balance
- ❌ Handle deposits/topups
- ❌ Handle withdrawals
- ❌ Handle expenses

### WalletService (What's in your WALLET)
**Purpose:** Manage wallet balance and transactions

**Responsibilities:**
- ✅ Calculate current wallet balance
- ✅ Record deposits/topups
- ✅ Record withdrawals
- ✅ Record expenses (workshops, products)
- ✅ Transaction history
- ✅ Wallet breakdown (credits vs debits)

**Uses EarningsService for:**
- ✅ Getting total earnings to add to wallet
- ✅ Getting earnings breakdown for display

---

## Refactored Architecture

### 1. EarningsService (Pure Earnings Calculator)

```php
class EarningsService
{
    /**
     * Calculate total earnings from ALL sources
     */
    public function calculateTotalEarnings(User $user): float
    {
        return $this->getCommissionEarnings($user)
             + $this->getProfitShareEarnings($user)
             + $this->getProductSalesEarnings($user)
             + $this->getSubscriptionEarnings($user)
             + $this->getBonusEarnings($user);
    }
    
    /**
     * Get detailed earnings breakdown
     */
    public function getEarningsBreakdown(User $user): array
    {
        return [
            'commissions' => $this->getCommissionEarnings($user),
            'profit_shares' => $this->getProfitShareEarnings($user),
            'product_sales' => $this->getProductSalesEarnings($user),
            'subscriptions' => $this->getSubscriptionEarnings($user),
            'bonuses' => $this->getBonusEarnings($user),
            'total' => $this->calculateTotalEarnings($user),
        ];
    }
    
    // Individual earning type methods
    private function getCommissionEarnings(User $user, ?Carbon $start = null, ?Carbon $end = null): float
    {
        $query = $user->referralCommissions()->where('status', 'paid');
        if ($start && $end) $query->whereBetween('created_at', [$start, $end]);
        return (float) $query->sum('amount');
    }
    
    private function getProfitShareEarnings(User $user, ?Carbon $start = null, ?Carbon $end = null): float
    {
        $query = $user->profitShares()->where('status', 'paid');
        if ($start && $end) $query->whereBetween('created_at', [$start, $end]);
        return (float) $query->sum('amount');
    }
    
    private function getProductSalesEarnings(User $user, ?Carbon $start = null, ?Carbon $end = null): float
    {
        // Future: Product sales commissions
        return 0.0;
    }
    
    private function getSubscriptionEarnings(User $user, ?Carbon $start = null, ?Carbon $end = null): float
    {
        // Commissions from team subscriptions
        $query = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->where('source', 'subscription');
        if ($start && $end) $query->whereBetween('created_at', [$start, $end]);
        return (float) $query->sum('amount');
    }
}
```

### 2. WalletService (Uses EarningsService)

```php
class WalletService
{
    protected EarningsService $earningsService;
    
    public function __construct(EarningsService $earningsService)
    {
        $this->earningsService = $earningsService;
    }
    
    /**
     * Calculate wallet balance
     * Balance = Earnings + Deposits - Withdrawals - Expenses
     */
    public function calculateBalance(User $user): float
    {
        $credits = $this->calculateTotalCredits($user);
        $debits = $this->calculateTotalDebits($user);
        
        return $credits - $debits;
    }
    
    /**
     * Calculate total credits (money IN)
     */
    private function calculateTotalCredits(User $user): float
    {
        // Get earnings from EarningsService
        $earnings = $this->earningsService->calculateTotalEarnings($user);
        
        // Add deposits/topups
        $deposits = $this->getDeposits($user);
        
        // Add loan disbursements
        $loans = $this->getLoanDisbursements($user);
        
        return $earnings + $deposits + $loans;
    }
    
    /**
     * Calculate total debits (money OUT)
     */
    private function calculateTotalDebits(User $user): float
    {
        return $this->getWithdrawals($user)
             + $this->getExpenses($user)
             + $this->getLoanRepayments($user);
    }
    
    /**
     * Get wallet breakdown for display
     */
    public function getWalletBreakdown(User $user): array
    {
        // Get earnings breakdown from EarningsService
        $earningsBreakdown = $this->earningsService->getEarningsBreakdown($user);
        
        return [
            'credits' => [
                'earnings' => $earningsBreakdown,
                'deposits' => $this->getDeposits($user),
                'loans' => $this->getLoanDisbursements($user),
                'total' => $this->calculateTotalCredits($user),
            ],
            'debits' => [
                'withdrawals' => $this->getWithdrawals($user),
                'expenses' => $this->getExpenses($user),
                'loan_repayments' => $this->getLoanRepayments($user),
                'total' => $this->calculateTotalDebits($user),
            ],
            'balance' => $this->calculateBalance($user),
        ];
    }
    
    // Helper methods for specific transaction types
    private function getDeposits(User $user): float { /* ... */ }
    private function getWithdrawals(User $user): float { /* ... */ }
    private function getExpenses(User $user): float { /* ... */ }
    private function getLoanDisbursements(User $user): float { /* ... */ }
    private function getLoanRepayments(User $user): float { /* ... */ }
}
```

---

## Benefits of This Architecture

### 1. Single Responsibility ✅
- **EarningsService:** Only calculates earnings
- **WalletService:** Only manages wallet balance

### 2. No Duplication ✅
- Earnings calculated once in EarningsService
- WalletService uses EarningsService

### 3. Easy to Extend ✅
```php
// Adding new earning type:
// 1. Add method to EarningsService
private function getVentureBuilderEarnings(User $user): float { /* ... */ }

// 2. Include in calculateTotalEarnings()
return $this->getCommissionEarnings($user)
     + $this->getProfitShareEarnings($user)
     + $this->getVentureBuilderEarnings($user); // NEW
```

### 4. Clear Dependencies ✅
```
Controller → WalletService → EarningsService → Database
```

### 5. Testable ✅
```php
// Test EarningsService independently
$earnings = $earningsService->calculateTotalEarnings($user);

// Test WalletService with mocked EarningsService
$walletService = new WalletService($mockEarningsService);
```

---

## Future-Proof for New Features

### Product Sales
```php
// EarningsService
private function getProductSalesEarnings(User $user): float
{
    return DB::table('product_sales')
        ->where('seller_id', $user->id)
        ->where('status', 'completed')
        ->sum('commission_amount');
}
```

### Subscriptions
```php
// EarningsService
private function getSubscriptionEarnings(User $user): float
{
    return DB::table('referral_commissions')
        ->where('referrer_id', $user->id)
        ->where('source', 'subscription')
        ->where('status', 'paid')
        ->sum('amount');
}
```

### Venture Builder
```php
// EarningsService
private function getVentureBuilderEarnings(User $user): float
{
    return DB::table('venture_dividends')
        ->where('user_id', $user->id)
        ->where('status', 'paid')
        ->sum('amount');
}
```

---

## Migration Plan

### Phase 1: Refactor EarningsService ✅
- Make it pure (only earnings calculation)
- Add all earning types
- Remove wallet logic

### Phase 2: Refactor WalletService ✅
- Inject EarningsService
- Use EarningsService for all earnings
- Remove duplicate earnings calculations

### Phase 3: Update Controllers ✅
- Use WalletService for balance
- Use EarningsService for earnings breakdown
- Remove manual calculations

### Phase 4: Testing ✅
- Unit test EarningsService
- Unit test WalletService
- Integration test controllers

---

## Recommended Implementation

```php
// app/Services/EarningsService.php
class EarningsService
{
    // Pure earnings calculation
    // No wallet logic
    // All earning types
}

// app/Services/WalletService.php
class WalletService
{
    public function __construct(
        protected EarningsService $earningsService
    ) {}
    
    // Uses EarningsService for earnings
    // Handles deposits, withdrawals, expenses
    // Calculates balance
}

// app/Http/Controllers/MyGrowNet/DashboardController.php
class DashboardController
{
    public function __construct(
        protected WalletService $walletService,
        protected EarningsService $earningsService
    ) {}
    
    // Use WalletService for balance
    // Use EarningsService for earnings breakdown
}
```

---

## Decision Matrix

| Feature | EarningsService | WalletService |
|---------|----------------|---------------|
| Referral Commissions | ✅ Calculate | ❌ |
| LGR Profit Sharing | ✅ Calculate | ❌ |
| Product Sales | ✅ Calculate | ❌ |
| Subscriptions | ✅ Calculate | ❌ |
| Bonuses | ✅ Calculate | ❌ |
| Deposits/Topups | ❌ | ✅ Track |
| Withdrawals | ❌ | ✅ Track |
| Expenses | ❌ | ✅ Track |
| Wallet Balance | ❌ | ✅ Calculate |
| Transaction History | ❌ | ✅ Provide |

---

## Conclusion

**Best Approach:**
1. **EarningsService** = Pure earnings calculator (all income sources)
2. **WalletService** = Wallet manager (uses EarningsService + handles transactions)
3. **Controllers** = Use both services as needed

**This architecture:**
- ✅ Eliminates duplication
- ✅ Clear responsibilities
- ✅ Easy to extend
- ✅ Testable
- ✅ Future-proof

**Next Step:** Implement this refactoring?
