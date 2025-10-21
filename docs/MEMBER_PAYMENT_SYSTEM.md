# Member Payment System

## Overview

The Member Payment System allows members to submit proof of payment for various MyGrowNet services including subscriptions, workshops, products, and coaching. All payments require admin verification before being processed.

## Features

### Payment Types Supported
1. **Monthly Subscriptions** - Basic, Standard, Premium, Elite tiers
2. **Workshops & Training** - Financial literacy, business skills, leadership development
3. **Learning Packs** - Starter, Professional, Elite educational materials
4. **Coaching & Mentorship** - One-on-one, group coaching, premium mentorship

### Payment Methods
- MTN MoMo
- Airtel Money
- Bank Transfer
- Cash
- Other

### Payment Status Flow
1. **Pending** - Initial submission, awaiting admin verification
2. **Verified** - Admin has confirmed payment
3. **Rejected** - Payment could not be verified

## Database Structure

### Table: `member_payments`
- `id` - Primary key
- `user_id` - Foreign key to users table
- `amount` - Payment amount (decimal)
- `payment_method` - Enum: mtn_momo, airtel_money, bank_transfer, cash, other
- `payment_reference` - Unique transaction reference
- `phone_number` - Phone number used for payment
- `account_name` - Name on payment account
- `payment_type` - Enum: subscription, workshop, product, learning_pack, coaching, upgrade, other
- `notes` - Optional member notes
- `status` - Enum: pending, verified, rejected
- `admin_notes` - Admin verification notes
- `verified_by` - Foreign key to users (admin who verified)
- `verified_at` - Timestamp of verification
- `created_at` / `updated_at` - Standard timestamps

## Routes

### Member Routes
- `GET /mygrownet/payments` - View payment history
- `GET /mygrownet/payments/create` - Submit new payment
- `POST /mygrownet/payments` - Store payment submission

## Models

### MemberPayment
- Belongs to User (member)
- Belongs to User (verifier)
- Fillable fields for payment details
- Casts amount to decimal, verified_at to datetime

### User
- Has many MemberPayments

## Controllers

### MemberPaymentController
- `index()` - Display payment history
- `create()` - Show payment submission form with predefined options
- `store()` - Validate and store payment submission

## Vue Components

### SubmitPayment.vue
- Tabbed interface for different payment categories
- Predefined payment options for quick selection
- Custom amount entry
- Payment method selection
- Transaction reference capture
- Payment instructions display

### PaymentHistory.vue
- List of all submitted payments
- Status indicators (pending, verified, rejected)
- Payment details display
- Admin notes visibility
- Empty state with call-to-action

## Navigation

Payment features are accessible via the Finance section:
- **Submit Payment** - Quick access to payment submission
- **Payment History** - View all payment submissions

## Admin Features (To Be Implemented)

Future admin panel features:
- View all pending payments
- Verify/reject payments
- Add admin notes
- Search and filter payments
- Export payment reports

## Usage Flow

1. Member selects payment category (subscription, workshop, product, coaching)
2. Member chooses predefined option or enters custom amount
3. Member makes payment via preferred method (MoMo, Airtel, Bank)
4. Member submits payment proof with transaction reference
5. Payment status shows as "Pending"
6. Admin verifies payment and updates status
7. Member receives confirmation and services are activated

## Validation Rules

- Amount: Required, numeric, minimum K50
- Payment Method: Required, must be valid enum value
- Payment Reference: Required, unique, max 255 characters
- Phone Number: Required, max 20 characters
- Account Name: Required, max 255 characters
- Payment Type: Required, must be valid enum value
- Notes: Optional, max 1000 characters

## Future Enhancements

1. **Automated Verification** - Integration with payment gateway APIs
2. **Receipt Generation** - PDF receipts for verified payments
3. **Email Notifications** - Alerts for submission and verification
4. **SMS Confirmations** - Payment status updates via SMS
5. **Payment Reminders** - Automated subscription renewal reminders
6. **Bulk Payments** - Support for multiple service payments in one transaction
7. **Payment Plans** - Installment options for expensive services
8. **Refund Management** - Handle payment refunds and disputes
