# Withdrawal System - Mobile Money Only

## Changes Made

### 1. Member Withdrawal Page (`resources/js/Pages/Withdrawals/MyGrowNetIndex.vue`)
- **Removed**: Bank transfer option from payment method dropdown
- **Updated Form**: Simplified to mobile money only with:
  - Amount input with currency formatting
  - Mobile money number field with validation pattern
  - Account name field (must match mobile money registration)
  - Info banners explaining mobile money process and processing time
- **Enhanced UX**:
  - Added K prefix to amount input
  - Phone number validation for Zambian numbers (MTN/Airtel)
  - Helpful placeholder text and hints
  - Processing time information (24-48 hours)
  - Better form validation and disabled states

### 2. Withdrawal Controller (`app/Http/Controllers/WithdrawalController.php`)
- **Updated Validation**: 
  - Removed `payment_method` field
  - Added regex validation for Zambian mobile numbers
  - Custom error message for invalid phone numbers
- **Phone Number Normalization**: Automatically converts to +260 format
- **Database Storage**: 
  - Stores `withdrawal_method` as 'mobile_money'
  - Stores phone number and account name in `wallet_address` field
- **Success Message**: Updated to mention 24-48 hour processing time

### 3. Withdrawal Model (`app/Models/Withdrawal.php`)
- **Added Fillable Fields**: 
  - `withdrawal_method`
  - `wallet_address`
  - `reason`
  - `processed_at`
- **Added Casts**: Proper decimal and datetime casting
- **Added Relationship**: User relationship method

### 4. Admin Withdrawal Management (`resources/js/Pages/Admin/Withdrawals/Index.vue`)
- **Updated Table**: Added "Mobile Money" column showing account details
- **Removed Column**: Removed "Investment Tier" column (not relevant for MyGrowNet)
- **Updated Details Modal**: Shows mobile money account information
- **Status Display**: Updated to handle both 'approved' and 'completed' statuses

## Mobile Money Details

### Supported Providers
- MTN Mobile Money
- Airtel Money

### Phone Number Format
- Accepts: `0971234567`, `+260971234567`
- Validates: Must be valid Zambian mobile number (starts with 7 or 9)
- Stores: Normalized to `+260` format

### Processing
- **Processing Time**: 24-48 hours during business days
- **Status Flow**: pending → approved → completed
- **Account Verification**: Name must match mobile money registration

## Database Schema
Uses existing `withdrawals` table with fields:
- `withdrawal_method`: 'mobile_money'
- `wallet_address`: Stores phone number and account name
- `status`: 'pending', 'approved', 'rejected'
- `reason`: Rejection reason (if applicable)
- `processed_at`: Timestamp when processed

## User Experience Improvements
1. Clear mobile money branding and instructions
2. Phone number validation with helpful error messages
3. Visual feedback on available balance and limits
4. Processing time expectations set upfront
5. Simplified form with only necessary fields
6. Better mobile responsiveness

## Route Changes
- **Removed**: `withdrawals.create` route and separate create page
- **Updated**: All references now point to `withdrawals.index` with inline modal form
- **Files Updated**:
  - `resources/js/pages/MyGrowNet/Wallet.vue`
  - `resources/js/pages/Dashboard/MyGrowNetDashboard.vue`
  - `resources/js/components/Dashboard/InvestmentQuickActions.vue`
  - `app/Http/Controllers/DashboardController.php`
  - `routes/web.php`
- **Deleted**: `resources/js/Pages/Withdrawals/Create.vue` (no longer needed)
