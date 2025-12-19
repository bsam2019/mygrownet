# Subscription Checkout System

**Last Updated:** December 17, 2025
**Status:** Production

## Overview

Wallet-based subscription checkout system for all MyGrowNet apps (LifePlus, GrowBiz, GrowFinance, etc.). Users can purchase, upgrade, or start trials for app subscriptions using their wallet balance.

## Features

- **Wallet Payment**: Purchase subscriptions using wallet balance
- **Real-time Balance Check**: Shows current balance and balance after purchase
- **Insufficient Balance Warning**: Alerts users when balance is too low
- **Shared Top-Up Modal**: Reusable component across all apps
- **Transaction Recording**: All purchases recorded in transactions table
- **Notifications**: Users receive notifications for subscription changes
- **Cache Management**: Wallet cache cleared after transactions

## Shared Components

### WalletTopUpModal
**Location**: `resources/js/Components/Shared/WalletTopUpModal.vue`

Reusable modal for topping up wallet balance from any app.

**Props**:
- `show` - Boolean to control visibility
- `balance` - Current wallet balance
- `quickAmounts` - Array of quick amount options (default: [25, 50, 100, 200])
- `returnUrl` - URL to return to after payment

**Events**:
- `@close` - Emitted when modal is closed
- `@success` - Emitted with amount when top-up succeeds

**Usage**:
```vue
<WalletTopUpModal
    :show="showTopUpModal"
    :balance="walletBalance"
    @close="showTopUpModal = false"
    @success="onTopUpSuccess"
/>
```

## Implemented Apps

### LifePlus
- **Page**: `resources/js/pages/LifePlus/Profile/Subscription.vue`
- **Controller**: `app/Http/Controllers/LifePlus/ProfileController.php`
- **Routes**: `routes/lifeplus.php`
- **Plans**: Free, Premium (K25/month), Member Free, Elite

### GrowBiz
- **Page**: `resources/js/pages/GrowBiz/Settings/Subscription.vue`
- **Controller**: `app/Http/Controllers/GrowBiz/SubscriptionController.php`
- **Routes**: `routes/growbiz.php`
- **Plans**: Free, Starter (K50), Professional (K150), Enterprise (K300)

### GrowFinance
- **Page**: `resources/js/pages/GrowFinance/Settings/Subscription.vue`
- **Controller**: `app/Http/Controllers/GrowFinance/SubscriptionController.php`
- **Routes**: `routes/growfinance.php`
- **Plans**: Free, Basic (K49), Professional (K149), Business (K299)

### BizBoost
- **Page**: `resources/js/pages/BizBoost/Settings/Subscription.vue`
- **Controller**: `app/Http/Controllers/BizBoost/SubscriptionController.php`
- **Routes**: `routes/bizboost.php`
- **Plans**: Free, Starter (K79), Professional (K199), Business (K399)

## Backend Implementation

### Generic Controller
**File**: `app/Http/Controllers/ModuleSubscriptionCheckoutController.php`
- `purchase()` - Process subscription purchase
- `startTrial()` - Start free trial
- `upgrade()` - Upgrade existing subscription
- `cancel()` - Cancel subscription

### Module-Specific Controllers
Each module can have its own subscription controller that uses `UnifiedWalletService`:

```php
public function __construct(
    private SubscriptionService $subscriptionService,
    private UnifiedWalletService $walletService
) {}

public function settings(Request $request)
{
    return Inertia::render('Module/Settings/Subscription', [
        'walletBalance' => $this->walletService->calculateBalance($request->user()),
        'currentTier' => $this->subscriptionService->getUserTier($user, 'module-id'),
    ]);
}
```

## Transaction Flow

1. User clicks upgrade button
2. Checkout modal opens with plan details
3. System checks wallet balance
4. If insufficient, user can open top-up modal
5. User confirms purchase
6. Backend validates balance again
7. Subscription created/updated
8. Transaction recorded (negative amount = debit)
9. Wallet cache cleared
10. User redirected with success message

## Adding to New Apps

1. Create subscription page in `pages/[App]/Settings/Subscription.vue`
2. Import `WalletTopUpModal` component
3. Add wallet balance to controller props
4. Add routes for subscription purchase
5. Link from settings page

## Changelog

### December 17, 2025
- Initial implementation for LifePlus
- Created shared WalletTopUpModal component
- Added GrowBiz subscription page with wallet integration
- Added GrowFinance subscription page with wallet integration
- Added BizBoost subscription page with wallet integration
- Wallet-based checkout with real-time balance validation
- Transaction recording and notification system
