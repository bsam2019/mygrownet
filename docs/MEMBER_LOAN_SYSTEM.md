# Member Loan System

**Last Updated:** November 4, 2025  
**Status:** Production Ready  
**Implementation:** Domain-Driven Design (DDD)

## Overview

The Member Loan System allows administrators to issue loans to members that are automatically repaid from future earnings. This provides members with immediate access to funds while ensuring systematic repayment.

## Key Features

- ✅ Admin can issue loans to specific members
- ✅ Loan shows as wallet credit (usable immediately)
- ✅ Separate tracking of loan balance vs earned balance
- ✅ Auto-deduction from future earnings
- ✅ Loan status dashboard for both admin and member
- ✅ Repayment tracking with history
- ✅ Withdrawal restrictions until loan is repaid

## Business Rules

### Loan Issuance
1. Only admins can issue loans
2. Loan amount credited to wallet immediately
3. Member can use loan funds for purchases (e.g., starter kit)
4. Loan details recorded with issuer and notes

### Automatic Repayment
1. **100% of future earnings** go to loan repayment until cleared
2. Earnings include:
   - Referral commissions
   - Level commissions
   - Profit shares
   - Any other earnings
3. Repayment happens automatically when earnings are credited
4. Member sees clear progress tracking

### Withdrawal Restrictions
1. **Cannot withdraw** while loan balance > 0
2. Withdrawal button disabled with clear message
3. Member sees loan balance prominently on dashboard
4. Once loan is fully repaid, normal withdrawal access restored

## Database Structure

### Users Table Columns

```php
loan_balance            // Current amount owed (decimal 10,2)
total_loan_issued       // Total amount ever issued (decimal 10,2)
total_loan_repaid       // Total amount repaid (decimal 10,2)
loan_issued_at          // When loan was given (timestamp)
loan_issued_by          // Admin who approved (foreign key)
loan_notes              // Why loan was issued (text)
```

### Migration

```bash
php artisan migrate
# Runs: 2025_11_04_130000_add_loan_tracking_to_users.php
```

## Domain-Driven Design Structure

### Value Objects

**`App\Domain\Financial\ValueObjects\LoanAmount`**
- Immutable loan amount representation
- Self-validating (no negative amounts)
- Rich behavior (add, subtract, min, etc.)

```php
$loanAmount = LoanAmount::fromFloat(500.00);
$remaining = $loanAmount->subtract($repayment);
```

### Domain Services

**`App\Domain\Financial\Services\LoanService`**

Core business logic for loan management:

```php
// Issue a loan
$loanService->issueLoan(
    member: $user,
    amount: LoanAmount::fromFloat(500),
    issuedBy: $admin,
    notes: 'Starter kit purchase'
);

// Repay from earnings
$remainingEarnings = $loanService->repayFromEarnings(
    member: $user,
    earningsAmount: LoanAmount::fromFloat(100)
);

// Check loan status
$hasLoan = $loanService->hasOutstandingLoan($user);
$canWithdraw = $loanService->canWithdraw($user);
$progress = $loanService->getRepaymentProgress($user);
```

## API Endpoints

### Admin Routes

```php
// Issue a loan
POST /admin/loans/{user}/issue
{
    "amount": 500.00,
    "notes": "Starter kit purchase"
}

// Get loan summary
GET /admin/loans/{user}/summary

// Get all members with loans
GET /admin/loans/members-with-loans
```

### Response Format

```json
{
    "has_loan": true,
    "loan_balance": 350.00,
    "total_issued": 500.00,
    "total_repaid": 150.00,
    "repayment_progress": 30.0,
    "issued_at": "2025-11-04T10:30:00Z",
    "issued_by": {
        "id": 1,
        "name": "Admin User"
    },
    "notes": "Starter kit purchase",
    "can_withdraw": false
}
```

## User Interface

### Admin Interface

**Location:** Admin > Users > [User] > Loan Button

Features:
- Issue loan modal with amount and notes
- View loan summary
- See all members with outstanding loans
- Track repayment progress

**Components:**
- `resources/js/components/Admin/LoanModal.vue`
- Integrated into `resources/js/pages/Admin/Users/Index.vue`

### Member Interface

**Location:** MyGrowNet > Wallet

Features:
- Prominent loan warning banner
- Repayment progress bar
- Loan details (issued, repaid, remaining)
- Clear explanation of repayment terms
- Withdrawal restrictions notice

**Display:**
```
┌─────────────────────────────────────────┐
│ ⚠️ Outstanding Loan                     │
│                                         │
│ You have an outstanding loan of K350.00 │
│ All future earnings will automatically  │
│ go towards loan repayment.              │
│                                         │
│ Repayment Progress: 30%                 │
│ ████████░░░░░░░░░░░░░░░░░░░░░░░░░░░░░  │
│                                         │
│ Total Issued: K500.00                   │
│ Repaid: K150.00                         │
│                                         │
│ Note: Starter kit purchase              │
└─────────────────────────────────────────┘
```

## Integration Points

### 1. Earnings Controller

When earnings are credited, automatically deduct loan repayment:

```php
use App\Domain\Financial\Services\LoanService;
use App\Domain\Financial\ValueObjects\LoanAmount;

$loanService = app(LoanService::class);

// Repay loan from earnings
$remainingEarnings = $loanService->repayFromEarnings(
    $user,
    LoanAmount::fromFloat($earningsAmount)
);

// Credit only remaining earnings to wallet
$user->wallet_balance += $remainingEarnings->value();
```

### 2. Withdrawal Controller

Check loan status before allowing withdrawal:

```php
$loanService = app(LoanService::class);

if (!$loanService->canWithdraw($user)) {
    return back()->with('error', 'Cannot withdraw while you have an outstanding loan.');
}
```

### 3. Wallet Controller

Display loan summary on wallet page:

```php
$loanService = app(LoanService::class);
$loanSummary = $loanService->getLoanSummary($user);

return Inertia::render('MyGrowNet/Wallet', [
    'loanSummary' => $loanSummary,
    // ... other data
]);
```

## Transaction Logging

All loan activities are logged in the transactions table:

### Loan Issued
```php
$user->transactions()->create([
    'type' => 'loan_issued',
    'amount' => 500.00,
    'balance_after' => $user->wallet_balance,
    'description' => 'Loan issued by Admin User: Starter kit purchase',
    'processed_by' => $admin->id,
]);
```

### Loan Repayment
```php
$user->transactions()->create([
    'type' => 'loan_repayment',
    'amount' => -100.00,
    'balance_after' => $user->loan_balance,
    'description' => 'Automatic loan repayment from earnings',
]);
```

## User Experience Flow

### For Members

1. **Loan Issued**
   - Receives notification
   - Sees funds in wallet immediately
   - Can use funds for purchases

2. **During Repayment**
   - Sees loan banner on wallet page
   - Tracks progress with visual bar
   - Understands earnings go to repayment
   - Cannot withdraw funds

3. **Loan Repaid**
   - Banner disappears
   - Full withdrawal access restored
   - Normal earnings flow resumes

### For Admins

1. **Issue Loan**
   - Select member
   - Enter amount and notes
   - Confirm issuance

2. **Monitor Loans**
   - View all members with loans
   - Track repayment progress
   - See loan history

## Testing Scenarios

### Test Case 1: Issue Loan
```php
// Given: Member with K0 balance
// When: Admin issues K500 loan
// Then: 
//   - Member wallet balance = K500
//   - Member loan_balance = K500
//   - Member total_loan_issued = K500
//   - Transaction logged
```

### Test Case 2: Automatic Repayment
```php
// Given: Member with K500 loan balance
// When: Member earns K100 commission
// Then:
//   - K100 goes to loan repayment
//   - Member loan_balance = K400
//   - Member total_loan_repaid = K100
//   - Member wallet balance unchanged
//   - Transaction logged
```

### Test Case 3: Withdrawal Restriction
```php
// Given: Member with K500 loan balance and K200 wallet balance
// When: Member attempts withdrawal
// Then:
//   - Withdrawal blocked
//   - Error message shown
//   - Loan balance displayed
```

### Test Case 4: Full Repayment
```php
// Given: Member with K50 loan balance
// When: Member earns K100 commission
// Then:
//   - K50 goes to loan repayment
//   - K50 credited to wallet
//   - Member loan_balance = K0
//   - Member can now withdraw
```

## Security Considerations

1. **Authorization**
   - Only admins can issue loans
   - Members cannot modify loan data
   - Loan repayment is automatic (no manual intervention)

2. **Validation**
   - Loan amount must be positive
   - Maximum loan amount enforced (K10,000)
   - Cannot issue loan to admin users

3. **Audit Trail**
   - All loan activities logged
   - Issuer tracked
   - Timestamps recorded

## Performance Considerations

1. **Database Indexes**
   - Index on `loan_balance` for quick filtering
   - Index on `loan_issued_by` for admin queries

2. **Caching**
   - Loan summary cached per user
   - Invalidated on repayment

3. **Query Optimization**
   - Eager load loan issuer when needed
   - Use select specific columns

## Future Enhancements

### Potential Features (Not Implemented)

1. **Multiple Loans**
   - Allow multiple concurrent loans
   - Separate `member_loans` table
   - More complex repayment logic

2. **Interest Rates**
   - Optional interest on loans
   - Configurable rates per loan

3. **Payment Plans**
   - Installment-based repayment
   - Flexible repayment schedules

4. **Loan Forgiveness**
   - Admin can forgive remaining balance
   - Partial forgiveness option

## Troubleshooting

### Issue: Loan not deducting from earnings

**Check:**
1. Is `LoanService` being called in earnings controller?
2. Are transactions being logged?
3. Is loan_balance updating?

**Solution:**
```php
// Ensure this is in your earnings credit logic
$loanService = app(\App\Domain\Financial\Services\LoanService::class);
$remainingEarnings = $loanService->repayFromEarnings($user, $earningsAmount);
```

### Issue: Member can still withdraw with loan

**Check:**
1. Is withdrawal controller checking loan status?
2. Is `canWithdraw()` method being called?

**Solution:**
```php
// Add to withdrawal controller
if (!$loanService->canWithdraw($user)) {
    return back()->with('error', 'Cannot withdraw with outstanding loan.');
}
```

### Issue: Loan balance not showing on wallet

**Check:**
1. Is `loanSummary` being passed to Inertia?
2. Is the loan banner component rendering?

**Solution:**
```php
// In WalletController
$loanSummary = $loanService->getLoanSummary($user);
return Inertia::render('MyGrowNet/Wallet', [
    'loanSummary' => $loanSummary,
]);
```

## Related Documentation

- `docs/DOMAIN-DESIGN.md` - DDD architecture overview
- `docs/STRUCTURE.md` - Project structure
- `docs/LGR_USER_RESTRICTIONS.md` - Similar restriction system

## Support

For questions or issues:
1. Check this documentation
2. Review domain service implementation
3. Check transaction logs
4. Contact development team

---

**Implementation Complete** ✅  
Simple, effective loan system using DDD principles.


## Changelog

### 2025-11-05
- **Fixed wallet balance calculation** - Updated `WalletService` to include loan transactions
- Loan disbursements now properly added to wallet earnings
- Loan repayments now properly deducted from wallet balance
- **Fixed starter kit purchase** - Updated `StarterKitService` and `PurchaseStarterKitUseCase` to use centralized `WalletService`
- All wallet balance calculations now go through single source of truth
- Starter kit purchases now work correctly with loan funds
- **Fixed starter kit tier bug** - Added `tier` to `StarterKitPurchaseModel` fillable array (users were being charged for Premium but receiving Basic)

### 2025-11-04
- Initial implementation of Member Loan System
- Created domain-driven design structure
- Implemented admin interface with loan management
- Added automatic repayment system
- Integrated notification system
