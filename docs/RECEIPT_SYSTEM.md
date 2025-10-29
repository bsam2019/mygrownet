# Receipt System Documentation

**Last Updated:** October 28, 2025  
**Version:** 2.3  
**Status:** ✅ Production Ready

---

## Overview

The MyGrowNet receipt system automatically generates professional, branded PDF receipts for all purchases and spending transactions. Receipts are stored in the database, saved as PDFs, and emailed to users.

**Key Principle:** Receipts are issued for SPENDING, not for DEPOSITS.

---

## Receipt Types

| Type | Code Format | When Generated | Can Be Deleted? |
|------|-------------|----------------|-----------------|
| **Registration Payment** | RCP-XXXXXXXX | Admin verifies K250 registration | ✅ Yes (if reset/rejected) |
| **Starter Kit** | SKT-XXXXXXXXXXXX | Starter kit purchase completed | ✅ Yes (if reset/rejected) |
| **Workshop** | WKS-XXXXXXXXXXXX | Workshop registration completed | ✅ Yes (if reset/rejected) |
| **Subscription** | SUB-XXXXXXXXXXXX | Subscription payment completed | ✅ Yes (if reset/rejected) |
| **Shop Purchase** | SHP-XXXXXXXXXXXX | Shop product purchase completed | ✅ Yes (if reset/rejected) |
| **Wallet Transaction** | WLT-XXXXXXXX | Wallet activity (optional) | ❌ No |

---

## What Gets Receipts?

### ✅ Generates Receipt (SPENDING)
- Starter kit purchases (all payment methods)
- Workshop registrations
- Subscription payments
- Shop product purchases
- Registration payments (K250)

### ❌ No Receipt (DEPOSITS)
- Wallet top-ups (just adding money)
- Commission earnings (receiving money)
- Profit share distributions (receiving money)

---

## Receipt Generation Flow

### Mobile Money/Bank Transfer Purchase

```
User pays for product/service
        ↓
Admin verifies payment
        ↓
✅ Receipt Generated
   - PDF created
   - Saved to database
   - Saved to file system
        ↓
📧 Email sent to user
        ↓
User can download from dashboard
```

### Wallet Purchase

```
User pays with wallet balance
        ↓
Wallet deducted instantly
        ↓
✅ Receipt Generated
   - PDF created
   - Saved to database
   - Saved to file system
        ↓
📧 Email sent to user
        ↓
User can download from dashboard
```

---

## Receipt Lifecycle

### Generation
- Automatic when payment verified (mobile/bank)
- Automatic when wallet payment processed
- PDF generated using DomPDF
- Saved to `storage/app/receipts/`
- Record saved to `receipts` table
- Email sent with PDF attachment

### Deletion
- Automatic when payment reset to pending
- Automatic when payment rejected
- PDF file deleted from storage
- Database record deleted
- User loses dashboard access
- **Note:** Email already sent cannot be recalled

### Exception
- Wallet payments cannot be reset (transaction is final)
- Receipts for wallet purchases are permanent

---

## Technical Implementation

### Files

**Service:**
- `app/Services/ReceiptService.php` - Receipt generation logic

**Use Cases:**
- `app/Application/Payment/UseCases/VerifyPaymentUseCase.php` - Handles payment verification
- `app/Application/Payment/UseCases/ResetPaymentUseCase.php` - Handles payment reset + receipt deletion
- `app/Application/Payment/UseCases/RejectPaymentUseCase.php` - Handles payment rejection + receipt deletion

**Templates:**
- `resources/views/receipts/payment.blade.php` - Payment & purchase receipts
- `resources/views/receipts/wallet.blade.php` - Wallet transaction receipts

**Controllers:**
- `app/Http/Controllers/ReceiptController.php` - User receipt access
- `app/Http/Controllers/Admin/ReceiptController.php` - Admin receipt management

---

## ReceiptService Methods

### Core Methods

```php
// Registration/general payments
generatePaymentReceipt(MemberPaymentModel $payment): Receipt

// Starter kit purchases
generateStarterKitReceipt(User $user, float $amount, string $paymentMethod, ?string $transactionRef): Receipt

// Workshop registrations
generateWorkshopReceipt(User $user, $workshop, string $paymentMethod, float $amount, ?string $transactionRef): Receipt

// Subscription payments
generateSubscriptionReceipt(User $user, $subscription, string $paymentMethod, float $amount, ?string $transactionRef): Receipt

// Shop purchases
generateShopReceipt(User $user, array $items, string $paymentMethod, float $totalAmount, ?string $transactionRef): Receipt

// Wallet transactions (optional)
generateWalletReceipt(Transaction $transaction): string

// Email delivery
emailReceipt(User $user, string $receiptPath, string $subject): void
```

---

## Receipt Design

### Features
- MyGrowNet logo (from `public/logo.png`)
- Modern gradient headers
- Color-coded sections
- Professional typography
- Itemized lists
- Contact information
- Status badges
- Print-ready format

### Colors
- Payment receipts: Blue gradient (#2563eb → #1d4ed8)
- Wallet receipts: Indigo gradient (#4f46e5 → #4338ca)
- Success: Green (#059669)
- Status badges: Green with checkmark (✓ 
PAID)

---

## Usage Examples

### Example 1: Wallet Top-up (No Receipt)
```php
// User deposits K1,000 to wallet
// Admin verifies payment
// Result: NO receipt generated (just a deposit)
// Wallet balance increases by K1,000
```

### Example 2: Starter Kit Purchase with Wallet
```php
// User buys starter kit with wallet (K500)
$receiptService = app(\App\Services\ReceiptService::class);
$receipt = $receiptService->generateStarterKitReceipt(
    $user,
    500,
    'wallet',
    'WALLET-20251028143022-123'
);
// Result: Receipt SKT-XXXXXXXXXXXX generated
// Email sent to user
```

### Example 3: Workshop Registration
```php
// User registers for workshop
$receipt = $receiptService->generateWorkshopReceipt(
    $user,
    $workshop,
    'mobile_money',
    200,
    'MTN-20251028-789456'
);
// Result: Receipt WKS-XXXXXXXXXXXX generated
```

---

## Database Schema

### receipts table
```sql
- id (primary key)
- receipt_number (unique, indexed)
- user_id (foreign key, indexed)
- type (enum: payment, starter_kit, workshop, subscription, shop_purchase, wallet)
- amount (decimal)
- payment_method (string)
- transaction_reference (string, nullable)
- description (text)
- pdf_path (string)
- emailed (boolean, default false)
- emailed_at (timestamp, nullable)
- metadata (json)
- created_at, updated_at (timestamps)
```

---

## Troubleshooting

### Issue: Logo not displaying in PDF
**Solution:** Ensure `public/logo.png` exists and is readable

### Issue: PDF generation fails
**Solution:** Check DomPDF installation: `composer show barryvdh/laravel-dompdf`

### Issue: Receipts not being deleted on payment reset
**Solution:** Check logs for errors, verify metadata structure

### Issue: Email not sending
**Solution:** Verify email configuration, check queue worker

---

## Changelog

### Version 2.3 - October 28, 2025
- ✅ **FIXED:** Duplicate receipt generation for wallet purchases
- ✅ Removed redundant `completePurchase()` call in `PurchaseStarterKitUseCase`
- ✅ Now generates only ONE receipt per purchase (all payment methods)

### Version 2.2 - October 28, 2025
- ✅ Reduced header padding for better space utilization
- ✅ Compressed section spacing throughout receipt
- ✅ Added comprehensive starter kit items list (8 items showing all benefits)
- ✅ Improved items display with checkmarks for included items

### Version 2.1 - October 28, 2025
- ✅ Fixed logo spacing and layout issues
- ✅ Corrected shop credit amount (K100 instead of K50)
- ✅ Updated contact information (info@mygrownet.com, +260 977 891 894)
- ✅ Improved receipt template spacing and readability

### Version 2.0 - October 28, 2025
- ✅ Removed receipt generation for wallet top-ups
- ✅ Added receipt methods for workshops, subscriptions, and shop purchases
- ✅ Enhanced receipt templates with professional design and logo
- ✅ Implemented automatic receipt deletion on payment reset/rejection
- ✅ Fixed duplicate receipt issue for starter kit purchases
- ✅ Consolidated documentation into single file

### Version 1.0 - October 27, 2025
- Initial receipt system implementation
- Basic receipt generation for payments
- Email delivery functionality
- User and admin dashboards

---

## Summary

**Key Points:**
- Receipts for SPENDING only (not deposits)
- Automatic generation and deletion
- Professional branded design
- Email delivery with PDF
- Accessible from user dashboard
- Admin can manage all receipts

**Remember:** One transaction = One receipt (no duplicates!)
