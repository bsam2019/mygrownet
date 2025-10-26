# Starter Kit Wallet Integration

## Overview

The Starter Kit now integrates with MyGrowNet's existing wallet system, allowing members to purchase using their wallet balance.

## Wallet System Architecture

### Calculated Balance (Not Stored)

The wallet balance is **calculated dynamically** from:

**Income:**
- ✅ Commission earnings (paid)
- ✅ Profit shares
- ✅ Wallet topups (verified member_payments)

**Expenses:**
- ❌ Approved withdrawals
- ❌ Workshop registrations
- ❌ **Starter Kit purchases (wallet payment)**

**Formula:**
```
Wallet Balance = (Commissions + Profits + Topups) - (Withdrawals + Workshops + Starter Kits)
```

## Payment Options

### 1. Wallet Payment
- **Instant access** - No admin approval needed
- Deducted from calculated wallet balance
- Purchase record serves as expense
- Reference: `WALLET-{timestamp}-{user_id}`

### 2. Mobile Money / Bank Transfer
- Requires admin verification
- Status: `pending` → `completed`
- Member provides payment reference
- Admin updates status in dashboard

## Purchase Flow

### With Sufficient Wallet Balance

1. Member sees green alert: "You have sufficient wallet balance!"
2. Selects "Wallet" payment method
3. No payment reference needed
4. Clicks "Complete Purchase"
5. **Instant access** - Purchase auto-completed
6. K100 shop credit added immediately

### With Insufficient Balance

1. Member sees payment method options
2. Selects Mobile Money/Bank Transfer
3. Enters payment reference
4. Purchase status: `pending`
5. Admin verifies and updates status
6. Access granted when status → `completed`

## Shop Credit System

### Storage
Shop credit is stored in the `users` table:
- `starter_kit_shop_credit` - Amount (K100)
- `starter_kit_credit_expiry` - Expiry date (90 days)

### Usage
- Can be used in MyGrowNet shop
- Separate from wallet balance
- Expires after 90 days
- Cannot be withdrawn as cash

## What's Included in Purchase Page

### Detailed Breakdown Display

**Training Modules (K300 value)**
- Business Fundamentals
- Network Building Strategies
- Financial Success Planning

**Premium eBooks (K150 value)**
- Success Guide
- Network Building Mastery
- Financial Freedom Blueprint

**Video Tutorials (K200 value)**
- Platform Navigation
- Building Your Network
- Maximizing Earnings

**Marketing Tools (K100 value)**
- Marketing Templates
- Pitch Deck
- Social Media Content

**Digital Library (K200 value)**
- 50+ Business eBooks
- 30-Day Access
- Renewable Subscription

**Instant Bonuses (K200 value)**
- K100 Shop Credit (90 days)
- +50 Lifetime Points
- Achievement Badges

**Total Value: K1,050**
**Your Price: K500**
**Savings: K550 (52%)**

## Technical Implementation

### Wallet Balance Calculation

```php
// In Controller
$commissionEarnings = $user->referralCommissions()->where('status', 'paid')->sum('amount');
$profitEarnings = $user->profitShares()->sum('amount');
$walletTopups = MemberPaymentModel::where('user_id', $user->id)
    ->where('payment_type', 'wallet_topup')
    ->where('status', 'verified')
    ->sum('amount');
$totalEarnings = $commissionEarnings + $profitEarnings + $walletTopups;
$totalWithdrawals = $user->withdrawals()->where('status', 'approved')->sum('amount');
$workshopExpenses = WorkshopRegistrationModel::where('user_id', $user->id)
    ->whereIn('status', ['registered', 'attended', 'completed'])
    ->join('workshops', 'workshop_registrations.workshop_id', '=', 'workshops.id')
    ->sum('workshops.price');
$walletBalance = $totalEarnings - $totalWithdrawals - $workshopExpenses;
```

### Wallet Payment Validation

```php
// In StarterKitService
if ($paymentMethod === 'wallet') {
    // Calculate current balance
    $walletBalance = /* calculation above */;
    
    if ($walletBalance < self::PRICE) {
        throw new \Exception('Insufficient wallet balance');
    }
    
    // No deduction needed - purchase record is the expense
    $paymentReference = 'WALLET-' . now()->format('YmdHis') . '-' . $user->id;
}
```

### Purchase Status

```php
// Wallet purchases are auto-completed
$purchase = StarterKitPurchase::create([
    'user_id' => $user->id,
    'amount' => self::PRICE,
    'payment_method' => $paymentMethod,
    'payment_reference' => $paymentReference,
    'status' => $paymentMethod === 'wallet' ? 'completed' : 'pending',
    'invoice_number' => StarterKitPurchase::generateInvoiceNumber(),
]);
```

## User Experience

### Purchase Page Features

1. **Wallet Balance Alert**
   - Green: Sufficient balance
   - Blue: Partial balance available
   - Shows current balance

2. **Payment Method Cards**
   - Wallet option shows balance
   - "Instant Access" badge if sufficient
   - "Insufficient balance" warning if not enough

3. **Smart Form**
   - Payment reference hidden for wallet
   - Shows deduction preview
   - New balance after purchase

4. **Content Breakdown**
   - Visual cards for each category
   - Value displayed per item
   - Total value vs. price comparison

## Admin Dashboard

### Purchase Management

Admins can:
- View all purchases (wallet + other methods)
- Filter by payment method
- See wallet purchases marked as "completed"
- Update status for non-wallet purchases

### Analytics

Track:
- Wallet vs. other payment methods
- Instant access rate (wallet purchases)
- Pending verification count
- Revenue by payment method

## Benefits

### For Members
- ✅ Instant access with wallet payment
- ✅ No waiting for verification
- ✅ Clear balance visibility
- ✅ Multiple payment options
- ✅ Detailed value breakdown

### For Platform
- ✅ Reduced admin workload (auto-completed)
- ✅ Faster member onboarding
- ✅ Better conversion rates
- ✅ Integrated expense tracking
- ✅ Accurate wallet calculations

## Database Schema

### No New Tables Needed

Uses existing tables:
- `starter_kit_purchases` - Purchase records
- `users` - Shop credit storage
- `member_payments` - Wallet topups
- Calculated from existing earnings/expenses

### User Fields Added

```sql
ALTER TABLE users ADD COLUMN starter_kit_shop_credit DECIMAL(10,2) DEFAULT 0.00;
ALTER TABLE users ADD COLUMN starter_kit_credit_expiry DATE NULL;
```

## Testing

### Test Wallet Purchase

```php
// Give user wallet balance
$user = User::first();
$topup = MemberPaymentModel::create([
    'user_id' => $user->id,
    'amount' => 600,
    'payment_type' => 'wallet_topup',
    'payment_method' => 'mtn_momo',
    'payment_reference' => 'TEST123',
    'status' => 'verified',
    'phone_number' => '0977123456',
    'account_name' => 'Test User',
]);

// Purchase starter kit
$service = app(StarterKitService::class);
$purchase = $service->purchaseStarterKit($user, 'wallet');
$service->completePurchase($purchase);

// Verify
echo $user->fresh()->has_starter_kit ? 'SUCCESS' : 'FAILED';
```

## Future Enhancements

- [ ] Partial wallet payment (use wallet + other method)
- [ ] Wallet payment history in member dashboard
- [ ] Email notification for wallet purchases
- [ ] Wallet balance alerts/warnings
- [ ] Auto-topup suggestions

---

**Status**: ✅ Complete & Integrated
**Last Updated**: October 26, 2025
