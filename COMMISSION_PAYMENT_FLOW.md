# Commission Payment & Withdrawal Flow

**Date:** November 9, 2025  
**Status:** ✅ Complete Guide

---

## Seven-Level Commission Structure

### Commission Rates (Percentages)

```
Level 1 (Associate)    → 15% of package amount
Level 2 (Professional) → 10% of package amount
Level 3 (Senior)       → 8% of package amount
Level 4 (Manager)      → 6% of package amount
Level 5 (Director)     → 4% of package amount
Level 6 (Executive)    → 3% of package amount
Level 7 (Ambassador)   → 2% of package amount
────────────────────────────────────────
TOTAL                  → 48% of package amount
```

### Example: K500 Subscription Purchase

```
Member A purchases K500 subscription
    ↓
Level 1 (Direct Referrer)  → K75.00  (15%)
Level 2 (Upline)           → K50.00  (10%)
Level 3 (Upline)           → K40.00  (8%)
Level 4 (Upline)           → K30.00  (6%)
Level 5 (Upline)           → K20.00  (4%)
Level 6 (Upline)           → K15.00  (3%)
Level 7 (Upline)           → K10.00  (2%)
────────────────────────────────────────
Total Commissions          → K240.00 (48%)
```

---

## Commission Payment Flow

### Step 1: Purchase Event

```
User purchases:
- Subscription (K500)
- Starter Kit (K500 or K1,000)
- Product
- Workshop
    ↓
MLMCommissionService::processMLMCommissions()
```

### Step 2: Calculate Commissions

```php
// In MLMCommissionService
public function processMLMCommissions(
    User $purchaser, 
    float $packageAmount, 
    string $packageType = 'subscription'
): array {
    // Get upline referrers (7 levels)
    $uplineReferrers = $this->getUplineReferrers($purchaser, 7);
    
    foreach ($uplineReferrers as $referrerData) {
        $referrer = User::find($referrerData['user_id']);
        $level = $referrerData['level'];
        
        // Check eligibility
        if (!$referrer->has_starter_kit) {
            continue; // Must have starter kit
        }
        
        if (!$referrer->hasActiveSubscription()) {
            continue; // Must have active subscription
        }
        
        // Calculate commission
        $commissionRate = ReferralCommission::getCommissionRate($level);
        $commissionAmount = $packageAmount * ($commissionRate / 100);
        
        // Create commission record
        $commission = ReferralCommission::create([
            'referrer_id' => $referrer->id,
            'referred_id' => $purchaser->id,
            'level' => $level,
            'amount' => $commissionAmount,
            'percentage' => $commissionRate,
            'status' => 'pending', // Initially pending
            'commission_type' => 'REFERRAL',
            'package_type' => $packageType,
            'package_amount' => $packageAmount,
        ]);
        
        // Process payment immediately (24-hour requirement)
        $commission->processPayment();
    }
}
```

### Step 3: Process Payment

```php
// In ReferralCommission model
public function processPayment(): void
{
    if ($this->status !== 'pending') {
        return;
    }
    
    DB::transaction(function () {
        // Update commission status
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        // Create transaction record (adds to wallet)
        DB::table('transactions')->insert([
            'user_id' => $this->referrer_id,
            'transaction_type' => 'commission',
            'amount' => $this->amount, // Positive (credit)
            'reference_number' => 'COMM-' . $this->id,
            'description' => "Level {$this->level} Commission from {$this->referee->name}",
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Update user's total earnings
        $this->referrer->increment('total_referral_earnings', $this->amount);
        
        // Send notification
        $this->sendCommissionNotification();
    });
}
```

### Step 4: Money Flow

```
Commission Paid
    ↓
Added to transactions table (credit)
    ↓
EarningsService calculates total earnings
    ↓
WalletService includes in wallet balance
    ↓
User sees balance in wallet
    ↓
User can withdraw
```

---

## Withdrawal Flow

### Step 1: User Requests Withdrawal

```
User clicks "Withdraw" in mobile dashboard
    ↓
WithdrawalModal opens
    ↓
User enters amount
    ↓
User selects method (Mobile Money / Bank)
    ↓
User submits request
```

### Step 2: Create Withdrawal Request

```php
// In WithdrawalController
public function store(Request $request)
{
    $validated = $request->validate([
        'amount' => 'required|numeric|min:50',
        'withdrawal_method' => 'required|in:mobile_money,bank_transfer',
        'phone_number' => 'required_if:withdrawal_method,mobile_money',
        'bank_details' => 'required_if:withdrawal_method,bank_transfer',
    ]);
    
    $user = $request->user();
    
    // Check wallet balance
    $walletService = app(WalletService::class);
    $balance = $walletService->calculateBalance($user);
    
    if ($validated['amount'] > $balance) {
        return back()->with('error', 'Insufficient balance');
    }
    
    // Create withdrawal request
    $withdrawal = Withdrawal::create([
        'user_id' => $user->id,
        'amount' => $validated['amount'],
        'withdrawal_method' => $validated['withdrawal_method'],
        'phone_number' => $validated['phone_number'] ?? null,
        'bank_details' => $validated['bank_details'] ?? null,
        'status' => 'pending', // Awaiting admin approval
        'requested_at' => now(),
    ]);
    
    // Send notification to admin
    $this->notifyAdminOfWithdrawal($withdrawal);
    
    return back()->with('success', 'Withdrawal request submitted');
}
```

### Step 3: Admin Approves Withdrawal

```
Admin reviews withdrawal request
    ↓
Admin approves
    ↓
System processes withdrawal
```

```php
// In Admin WithdrawalController
public function approve(Withdrawal $withdrawal)
{
    DB::transaction(function () use ($withdrawal) {
        // Update withdrawal status
        $withdrawal->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        
        // Create transaction record (debit from wallet)
        DB::table('transactions')->insert([
            'user_id' => $withdrawal->user_id,
            'transaction_type' => 'withdrawal',
            'amount' => -$withdrawal->amount, // Negative (debit)
            'reference_number' => 'WD-' . $withdrawal->id,
            'description' => "Withdrawal via {$withdrawal->withdrawal_method}",
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Send notification to user
        $this->notifyUserOfApproval($withdrawal);
    });
}
```

### Step 4: Money Deducted from Wallet

```
Withdrawal approved
    ↓
Transaction created (negative amount)
    ↓
WalletService recalculates balance
    ↓
Balance reduced
    ↓
User sees updated balance
```

---

## Complete Money Flow Diagram

```
┌─────────────────────────────────────────────────────┐
│              EARNING COMMISSIONS                    │
└─────────────────────────────────────────────────────┘
                        ↓
Member A purchases K500 subscription
                        ↓
        ┌───────────────┴───────────────┐
        │   MLMCommissionService        │
        │   processMLMCommissions()     │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │  Calculate 7-level commissions│
        │  Level 1: K75 (15%)           │
        │  Level 2: K50 (10%)           │
        │  Level 3: K40 (8%)            │
        │  Level 4: K30 (6%)            │
        │  Level 5: K20 (4%)            │
        │  Level 6: K15 (3%)            │
        │  Level 7: K10 (2%)            │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │  Create ReferralCommission    │
        │  status: 'pending'            │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │  processPayment()             │
        │  - Update status to 'paid'    │
        │  - Create transaction (credit)│
        │  - Update total_earnings      │
        │  - Send notification          │
        └───────────────┬───────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│              WALLET BALANCE                         │
└─────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   EarningsService             │
        │   calculateTotalEarnings()    │
        │   - Commissions: K75          │
        │   - Profit shares: K0         │
        │   - Subscriptions: K0         │
        │   - Products: K0              │
        │   - Bonuses: K0               │
        │   TOTAL: K75                  │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   WalletService               │
        │   calculateBalance()          │
        │   Credits:                    │
        │   - Earnings: K75             │
        │   - Deposits: K0              │
        │   - Loans: K0                 │
        │   Debits:                     │
        │   - Withdrawals: K0           │
        │   - Expenses: K0              │
        │   BALANCE: K75                │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   User sees K75 in wallet     │
        └───────────────┬───────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│              WITHDRAWING MONEY                      │
└─────────────────────────────────────────────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   User requests withdrawal    │
        │   Amount: K50                 │
        │   Method: Mobile Money        │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   Create Withdrawal           │
        │   status: 'pending'           │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   Admin approves              │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   Create transaction (debit)  │
        │   amount: -K50                │
        │   type: 'withdrawal'          │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   WalletService               │
        │   calculateBalance()          │
        │   Credits: K75                │
        │   Debits: K50                 │
        │   BALANCE: K25                │
        └───────────────┬───────────────┘
                        ↓
        ┌───────────────────────────────┐
        │   User sees K25 in wallet     │
        └───────────────────────────────┘
```

---

## Key Points

### 1. Commission Eligibility ✅
- Referrer MUST have starter kit
- Referrer MUST have active subscription
- Commissions go 7 levels deep
- Each level has different percentage

### 2. Automatic Payment ✅
- Commissions processed immediately
- Status changes from 'pending' to 'paid'
- Transaction created (credit to wallet)
- Notification sent to referrer

### 3. Wallet Balance ✅
- EarningsService calculates total earnings
- WalletService includes earnings in balance
- Balance = Earnings + Deposits - Withdrawals - Expenses

### 4. Withdrawal Process ✅
- User requests withdrawal
- Admin approves
- Transaction created (debit from wallet)
- Balance updated automatically

---

## Database Tables

### referral_commissions
```sql
- id
- referrer_id (who earns)
- referred_id (who purchased)
- level (1-7)
- amount (commission amount)
- percentage (commission rate)
- status ('pending', 'paid', 'cancelled')
- commission_type ('REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE')
- package_type ('subscription', 'starter_kit', 'product')
- package_amount (original purchase amount)
- paid_at
- created_at
```

### transactions
```sql
- id
- user_id
- transaction_type ('commission', 'withdrawal', 'deposit', etc.)
- amount (positive for credit, negative for debit)
- reference_number
- description
- status ('completed', 'pending', 'failed')
- created_at
```

### withdrawals
```sql
- id
- user_id
- amount
- withdrawal_method ('mobile_money', 'bank_transfer')
- phone_number
- bank_details
- status ('pending', 'approved', 'rejected')
- requested_at
- approved_at
- approved_by
```

---

## Services Involved

### 1. MLMCommissionService
- Processes 7-level commissions
- Checks eligibility
- Creates commission records
- Triggers payment

### 2. EarningsService
- Calculates total earnings
- Includes all commission types
- Provides breakdown by type
- Supports date ranges

### 3. WalletService
- Calculates wallet balance
- Uses EarningsService for earnings
- Tracks deposits and withdrawals
- Provides wallet breakdown

---

## Example Scenarios

### Scenario 1: New Subscription

```
Member A (Level 7) ← Member B (Level 6) ← Member C (Level 5) 
← Member D (Level 4) ← Member E (Level 3) ← Member F (Level 2) 
← Member G (Level 1) ← Member H (NEW)

Member H purchases K500 subscription
    ↓
Commissions distributed:
- Member G (Level 1): K75.00 (15%)
- Member F (Level 2): K50.00 (10%)
- Member E (Level 3): K40.00 (8%)
- Member D (Level 4): K30.00 (6%)
- Member C (Level 5): K20.00 (4%)
- Member B (Level 6): K15.00 (3%)
- Member A (Level 7): K10.00 (2%)

All commissions paid immediately to wallets
```

### Scenario 2: Withdrawal

```
Member G has:
- Commissions earned: K75.00
- Deposits: K0
- Withdrawals: K0
- Balance: K75.00

Member G requests withdrawal of K50
    ↓
Admin approves
    ↓
Transaction created: -K50
    ↓
New balance: K25.00
```

---

## Compliance

### Commission Cap: 48%
- Total commission percentage: 48%
- Within regulatory limits (< 50%)
- Sustainable business model

### Payment Timeline
- Commissions paid within 24 hours
- Withdrawals processed within 48 hours
- Transparent tracking

---

## Conclusion

**Commission Flow:** Purchase → Calculate → Pay → Wallet → Withdraw

**Seven Levels:** Each level earns different percentage (15% to 2%)

**Services:** MLMCommissionService → EarningsService → WalletService

**Everything is automated and tracked!** ✅
