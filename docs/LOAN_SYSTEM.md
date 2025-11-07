# Member Loan System

**Last Updated:** November 6, 2025  
**Status:** ✅ Production Ready  
**Implementation:** Domain-Driven Design (DDD)

---

## Overview

The Member Loan System allows members to access short-term, interest-free loans that are automatically repaid from future earnings. Admins have full control over loan limits and approvals.

### Key Features

- ✅ Interest-free loans for all members (with approval)
- ✅ Manual admin approval required (all limits default to K0)
- ✅ Automatic repayment from future earnings
- ✅ Separate tracking of loan vs earned balance
- ✅ Withdrawal restrictions until loan repaid
- ✅ Easy access from wallet page
- ✅ Admin interface for limit management

---

## Important: Manual Approval Required

**All loan limits default to K0.** Admins must manually review and approve loan limits for each member.

### Why Manual Approval?

- ✅ Proper risk assessment
- ✅ Manual review of each member
- ✅ Better control over lending
- ✅ Prevents automatic access
- ✅ Assess creditworthiness
- ✅ Check member history

---

## Eligibility & Access

### Member Eligibility

**Requirements:**
- ✅ Must have active account
- ✅ Must have a starter kit (basic or premium)
- ✅ Must have available credit (loan limit > current balance)
- ✅ Good repayment history (if existing loan)
- ✅ Admin-approved loan limit > K0

**Default Loan Limits:**
- **All members:** K0 (must be set by admin)
- Admins manually approve and set limits
- Ensures proper risk assessment

### How Members Access

**Method 1: From Wallet (Primary)**
1. Go to "My Wallet"
2. See purple "Need Financial Support?" card
3. Click "Apply for Loan" button
4. Land on loan application page

**Method 2: Direct URL**
- Visit: `/mygrownet/loans`

**Method 3: From Loan Banner**
- If active loan, see banner in wallet
- Click through to manage loans

### Loan Application Card Design

**Location:** Wallet page, above "Balance Breakdown"

**Features:**
- Purple-to-indigo gradient (stands out)
- Money/dollar icon
- 3 key benefits with checkmarks
- Clear "Apply for Loan" CTA
- Mobile responsive

---

## Admin Loan Management

### Setting Loan Limits

**Access:**
1. Go to Admin → Users
2. Find the member
3. Click **"Limit"** button (indigo)
4. Set custom loan limit (K0 - K50,000)
5. Save

**Features:**
- Quick presets (K0, K2,000, K5,000, K10,000)
- Shows current status (tier, balance, available credit)
- Real-time validation
- Audit logging

**Use Cases:**
- Increase limit for trusted members
- Decrease limit for risky borrowers
- Disable loans (set to K0)
- Special limits for VIP members

### Recommended Loan Limits

| Member Type | Recommended Limit | Reasoning |
|-------------|-------------------|-----------|
| **Default (All)** | **K0** | **Must be manually approved** |
| New Basic | K1,000 - K2,000 | Lower risk, building trust |
| New Premium | K3,000 - K5,000 | Higher commitment |
| Trusted Basic | K2,000 - K5,000 | Good repayment history |
| Trusted Premium | K5,000 - K10,000 | Excellent history |
| VIP/Special | K10,000 - K50,000 | Case-by-case basis |
| Risky/Defaulted | K0 | Revoke loan access |

### Admin Workflow

```
Member Request
    ↓
Admin Reviews
    ↓
Check:
- Member tier (basic/premium)
- Account status (active)
- Time as member
- Earnings history
- Previous loans (if any)
- Repayment history
    ↓
Decide Limit:
- New member: K1,000 - K2,000
- Trusted member: K3,000 - K5,000
- VIP member: K5,000+
- Deny: Keep at K0
    ↓
Set Limit in System
    ↓
Member Notified
```

### Issuing Loans

**Admin can issue loans directly:**

```php
POST /admin/loans/{user}/issue
{
    "amount": 500.00,
    "notes": "Starter kit purchase"
}
```

**Features:**
- Issue loan modal with amount and notes
- View loan summary
- See all members with outstanding loans
- Track repayment progress

---

## Business Rules

### Loan Issuance
1. Only admins can issue loans
2. Loan amount credited to wallet immediately
3. Member can use loan funds for purchases
4. Loan details recorded with issuer and notes

### Automatic Repayment
1. **100% of future earnings** go to loan repayment until cleared
2. Earnings include:
   - Referral commissions
   - Level commissions
   - Profit shares
   - Any other earnings
3. Repayment happens automatically when earnings credited
4. Member sees clear progress tracking

### Withdrawal Restrictions
1. **Cannot withdraw** while loan balance > 0
2. Withdrawal button disabled with clear message
3. Member sees loan balance prominently
4. Once fully repaid, normal withdrawal access restored

---

## Database Structure

### Users Table Columns

```sql
loan_limit              DECIMAL(10,2) DEFAULT 0      -- Maximum borrowable
loan_balance            DECIMAL(10,2) DEFAULT 0      -- Current amount owed
total_loan_issued       DECIMAL(10,2) DEFAULT 0      -- Total ever issued
total_loan_repaid       DECIMAL(10,2) DEFAULT 0      -- Total repaid
loan_issued_at          TIMESTAMP NULL               -- When loan given
loan_issued_by          BIGINT NULL                  -- Admin who approved
loan_notes              TEXT NULL                    -- Why loan issued
```

### Calculations

```
Available Credit = loan_limit - loan_balance
Can Borrow = loan_limit > 0 AND loan_balance < loan_limit
Can Withdraw = loan_balance == 0
```

### Migrations

```bash
# Add loan limit column
php artisan migrate
# Runs: 2025_11_05_120000_add_loan_limit_to_users.php

# Add loan tracking columns
php artisan migrate
# Runs: 2025_11_04_130000_add_loan_tracking_to_users.php
```

---

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

---

## API Endpoints

### Admin Routes

```php
// Issue a loan
POST /admin/loans/{user}/issue
{
    "amount": 500.00,
    "notes": "Starter kit purchase"
}

// Update loan limit
PATCH /admin/users/{user}/loan-limit
{
    "loan_limit": 5000.00
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
    "loan_limit": 5000.00,
    "available_credit": 4650.00,
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

---

## User Interface

### Member Interface

**Location:** MyGrowNet > Wallet

**Loan Application Card:**
```
┌─────────────────────────────────────┐
│ Need Financial Support?             │
│ [Purple/Indigo Gradient]            │
│                                     │
│ Apply for a short-term loan         │
│ ✓ Interest-free                     │
│ ✓ Quick approval                    │
│ ✓ Flexible repayment                │
│                                     │
│ [Apply for Loan Button]             │
└─────────────────────────────────────┘
```

**Outstanding Loan Banner:**
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

### Admin Interface

**Components:**
- `resources/js/components/Admin/LoanModal.vue` - Issue loans
- `resources/js/components/Admin/LoanLimitModal.vue` - Set limits
- Integrated into `resources/js/pages/Admin/Users/Index.vue`

---

## Integration Points

### 1. Earnings Controller

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

```php
$loanService = app(LoanService::class);

if (!$loanService->canWithdraw($user)) {
    return back()->with('error', 'Cannot withdraw while you have an outstanding loan.');
}
```

### 3. Wallet Controller

```php
$loanService = app(LoanService::class);
$loanSummary = $loanService->getLoanSummary($user);

return Inertia::render('MyGrowNet/Wallet', [
    'loanSummary' => $loanSummary,
]);
```

---

## Transaction Logging

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

---

## Testing

### Test Loan Limit Setting

1. Login as admin
2. Go to Users page
3. Click "Limit" on any user
4. Set to K2,000
5. Save
6. Verify user can now apply

### Test Member View

1. Login as member with K0 limit
2. Go to /mygrownet/loans
3. Verify shows "Not Eligible"
4. Admin sets limit to K2,000
5. Refresh page
6. Verify shows "✅ Eligible"

### Test Automatic Repayment

```php
// Given: Member with K500 loan balance
// When: Member earns K100 commission
// Then:
//   - K100 goes to loan repayment
//   - Member loan_balance = K400
//   - Member total_loan_repaid = K100
//   - Transaction logged
```

---

## Security & Validation

### Backend Validation
- Loan limit: 0 - 50,000
- Only admins can set limits
- Changes are logged
- Audit trail maintained

### Authorization
- Only admins can issue loans
- Members cannot modify loan data
- Loan repayment is automatic

---

## Troubleshooting

### Issue: Member can't find loan application

**Solution:** Loan card is in wallet page above "Balance Breakdown"

### Issue: Member shows K0 limit

**Solution:** Admin must manually set loan limit in Admin → Users → Limit

### Issue: Loan not deducting from earnings

**Check:**
1. Is `LoanService` being called in earnings controller?
2. Are transactions being logged?
3. Is loan_balance updating?

---

## Files Modified

### Backend
- `app/Domain/Financial/Services/LoanService.php`
- `app/Domain/Financial/ValueObjects/LoanAmount.php`
- `app/Http/Controllers/MyGrowNet/LoanApplicationController.php`
- `app/Http/Controllers/Admin/UserManagementController.php`
- `database/migrations/2025_11_05_120000_add_loan_limit_to_users.php`
- `database/migrations/2025_11_04_130000_add_loan_tracking_to_users.php`
- `routes/admin.php`

### Frontend
- `resources/js/pages/MyGrowNet/Wallet.vue` - Added loan card
- `resources/js/pages/MyGrowNet/Loans.vue` - Loan application page
- `resources/js/pages/Admin/Users/Index.vue` - Added limit button
- `resources/js/components/Admin/LoanModal.vue` - Issue loans
- `resources/js/components/Admin/LoanLimitModal.vue` - Set limits

---

## Changelog

### 2025-11-06
- Consolidated all loan documentation into single file
- Merged 5 separate loan docs

### 2025-11-05
- **Added loan application card in wallet** - Easy access for members
- **Changed default loan limits to K0** - Manual approval required
- **Added admin loan limit interface** - Set custom limits per member
- **Made loans available to all members** - Not just premium
- **Fixed wallet balance calculation** - Includes loan transactions
- **Fixed starter kit purchase with loans** - Uses centralized WalletService

### 2025-11-04
- Initial implementation of Member Loan System
- Created domain-driven design structure
- Implemented admin interface
- Added automatic repayment system

---

**Status:** ✅ Production Ready  
**Next Steps:** Monitor loan performance, adjust limits as needed

