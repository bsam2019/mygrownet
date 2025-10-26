# Starter Kit Payment Flow

## Overview

The Starter Kit integrates seamlessly with MyGrowNet's existing payment submission and verification system.

## Payment Methods

### 1. Wallet Payment (Instant Access)
- **Flow**: Purchase page → Instant access
- **Status**: Auto-completed
- **Verification**: Not required
- **Access**: Immediate

### 2. Mobile Money / Bank Transfer
- **Flow**: Purchase page → Submit Payment page → Admin verification → Access granted
- **Status**: Pending → Completed
- **Verification**: Required by admin
- **Access**: After verification (usually 24 hours)

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    STARTER KIT PURCHASE                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │ Select Payment  │
                    │     Method      │
                    └─────────────────┘
                              │
                ┌─────────────┴─────────────┐
                │                           │
                ▼                           ▼
        ┌──────────────┐          ┌──────────────────┐
        │    WALLET    │          │  MOBILE MONEY /  │
        │   PAYMENT    │          │  BANK TRANSFER   │
        └──────────────┘          └──────────────────┘
                │                           │
                ▼                           ▼
        ┌──────────────┐          ┌──────────────────┐
        │   Validate   │          │   Redirect to    │
        │   Balance    │          │ Submit Payment   │
        └──────────────┘          └──────────────────┘
                │                           │
                ▼                           ▼
        ┌──────────────┐          ┌──────────────────┐
        │   Deduct     │          │  User Submits    │
        │   K500       │          │  Payment Proof   │
        └──────────────┘          └──────────────────┘
                │                           │
                ▼                           ▼
        ┌──────────────┐          ┌──────────────────┐
        │ Auto-Complete│          │  Status: Pending │
        │   Purchase   │          └──────────────────┘
        └──────────────┘                    │
                │                           ▼
                │                  ┌──────────────────┐
                │                  │ Admin Verifies   │
                │                  │    Payment       │
                │                  └──────────────────┘
                │                           │
                │                           ▼
                │                  ┌──────────────────┐
                │                  │ Auto-Complete    │
                │                  │ Starter Kit      │
                │                  └──────────────────┘
                │                           │
                └───────────┬───────────────┘
                            ▼
                ┌───────────────────────┐
                │   INSTANT ACCESS TO:  │
                ├───────────────────────┤
                │ ✓ Training Modules    │
                │ ✓ eBooks & Videos     │
                │ ✓ Marketing Tools     │
                │ ✓ K100 Shop Credit    │
                │ ✓ +37.5 LP Bonus      │
                │ ✓ Achievement Badges  │
                └───────────────────────┘
```

## Detailed Steps

### Wallet Payment Flow

1. **User on Purchase Page**
   - Sees wallet balance: K600
   - Green alert: "You have sufficient wallet balance!"
   - Selects "Wallet" payment method

2. **Purchase Confirmation**
   - Shows: "K500 will be deducted"
   - Shows: "New balance: K100"
   - Button: "Complete Purchase"

3. **Instant Processing**
   - Validates wallet balance
   - Creates purchase record (status: completed)
   - Deducts K500 from calculated balance
   - Grants starter kit access
   - Adds K100 shop credit (90 days)
   - Awards +37.5 LP
   - Creates unlock schedule

4. **Success**
   - Redirects to "My Starter Kit"
   - Shows success message
   - All content visible (progressive unlocking)

### Mobile Money / Bank Transfer Flow

1. **User on Purchase Page**
   - Selects "Mobile Money" or "Bank Transfer"
   - Sees instructions: "Next Step: Submit Payment"
   - Button: "Continue to Payment Submission"

2. **Redirect to Submit Payment**
   - Pre-filled amount: K500
   - Pre-filled type: "product"
   - Pre-filled notes: "MyGrowNet Starter Kit Purchase"
   - Shows payment numbers (MTN/Airtel)

3. **User Makes Payment**
   - Sends K500 to provided number
   - Gets transaction reference
   - Fills form with details
   - Submits payment proof

4. **Pending Status**
   - Payment status: "pending"
   - User sees: "Payment submitted for verification"
   - Can view status in Payment History

5. **Admin Verification**
   - Admin reviews payment in dashboard
   - Verifies transaction reference
   - Clicks "Verify Payment"

6. **Auto-Completion**
   - System detects: product payment + K500 + no starter kit
   - Automatically creates starter kit purchase
   - Grants access
   - Adds K100 shop credit
   - Awards +37.5 LP
   - Creates unlock schedule

7. **User Notification**
   - User receives notification (email/SMS)
   - Can access "My Starter Kit"
   - All benefits activated

## Payment Numbers

### MTN Mobile Money
- **Number**: 0963426511
- **Name**: Kafula Mbulo

### Airtel Money
- **Number**: 0979230669
- **Name**: Kafula Mbulo

## Technical Implementation

### Wallet Payment

```php
// In StarterKitController
if ($validated['payment_method'] === 'wallet') {
    $purchase = $this->starterKitService->purchaseStarterKit($user, 'wallet', null);
    $this->starterKitService->completePurchase($purchase);
    return redirect()->route('mygrownet.starter-kit.show')
        ->with('success', 'Welcome! Your Starter Kit is ready.');
}
```

### Other Payment Methods

```php
// Redirect to Submit Payment with context
return redirect()->route('mygrownet.payments.create')
    ->with('payment_context', [
        'type' => 'starter_kit',
        'amount' => 500,
        'description' => 'MyGrowNet Starter Kit Purchase',
    ]);
```

### Auto-Completion on Verification

```php
// In VerifyPaymentUseCase
if ($paymentType === 'product' && $payment->amount()->value() == 500 && !$user->has_starter_kit) {
    $starterKitService = app(\App\Services\StarterKitService::class);
    $purchase = $starterKitService->purchaseStarterKit($user, $payment->paymentMethod()->value, $payment->paymentReference());
    $starterKitService->completePurchase($purchase);
}
```

## Benefits Received

Upon successful payment (wallet or verified):

| Benefit | Value | Details |
|---------|-------|---------|
| **Training Modules** | K300 | 3 comprehensive business courses |
| **Premium eBooks** | K150 | 3 success guides |
| **Video Tutorials** | K200 | 3 instructional videos |
| **Marketing Tools** | K100 | Templates, pitch deck, content |
| **Digital Library** | K200 | 50+ books, 30-day access |
| **Shop Credit** | K100 | Valid 90 days |
| **Lifetime Points** | +37.5 LP | Instant bonus |
| **Achievements** | Badges | Progress tracking |
| **Total Value** | K1,050 | |
| **Your Price** | K500 | |
| **Savings** | K550 (52%) | |

## User Experience

### Purchase Page Features

1. **Wallet Balance Display**
   - Shows current balance
   - Color-coded alerts (green/blue)
   - Instant access badge

2. **Payment Method Cards**
   - Visual selection
   - Clear descriptions
   - Balance requirements

3. **Content Breakdown**
   - Detailed list of inclusions
   - Value per category
   - Total savings display

4. **Smart Instructions**
   - Different for wallet vs. other methods
   - Step-by-step guidance
   - Clear expectations

### Submit Payment Page

1. **Context Awareness**
   - Shows "Starter Kit Purchase" header
   - Pre-fills amount (K500)
   - Pre-fills payment type (product)
   - Adds description automatically

2. **Payment Numbers**
   - MTN and Airtel numbers displayed
   - Copy-friendly format
   - Account names shown

3. **Form Pre-filling**
   - Amount: K500
   - Type: product
   - Notes: "MyGrowNet Starter Kit Purchase"
   - User only enters reference

## Admin Dashboard

### Payment Verification

Admins can:
- View all pending payments
- Filter by type (product = starter kit)
- See amount (K500 = starter kit)
- Verify with one click
- System auto-completes starter kit

### Starter Kit Dashboard

Admins can:
- View all purchases (wallet + verified)
- See payment methods used
- Track completion rates
- Monitor member progress

## Error Handling

### Insufficient Wallet Balance
- Error: "Insufficient wallet balance"
- Suggestion: "Please deposit funds or use another payment method"
- Redirects back to purchase page

### Duplicate Purchase
- Check: User already has starter kit
- Message: "You already have the Starter Kit!"
- Redirects to "My Starter Kit"

### Payment Verification Failure
- Logs error
- Admin notified
- User can resubmit

## Testing

### Test Wallet Purchase

```bash
# Give user wallet balance
php artisan tinker
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

# Purchase via wallet
# Visit /mygrownet/my-starter-kit/purchase
# Select Wallet, Complete Purchase
```

### Test Payment Submission

```bash
# Purchase with mobile money
# Visit /mygrownet/my-starter-kit/purchase
# Select Mobile Money, Continue
# Submit payment details
# Admin verifies in /admin/payments
# System auto-completes starter kit
```

## Future Enhancements

- [ ] Email notifications on verification
- [ ] SMS alerts for access granted
- [ ] Partial wallet payment (wallet + other method)
- [ ] Payment reminders for pending
- [ ] Bulk verification for admins
- [ ] Payment analytics dashboard

---

**Status**: ✅ Complete & Integrated
**Last Updated**: October 26, 2025
**LP Bonus**: 37.5 points (updated from 50)
