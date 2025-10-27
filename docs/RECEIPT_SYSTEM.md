# Receipt/Invoice System

## Overview
Automatic receipt generation system for all payments and purchases with PDF generation, email delivery, and complete audit trail.

## Features

### 1. **Automatic Receipt Generation**
- Generated automatically when payment is verified
- Generated automatically when starter kit is purchased
- PDF format with professional layout
- Unique receipt numbers for tracking

### 2. **Receipt Types**
- **Payment Receipts** (RCP-XXXXXXXX) - Registration payments
- **Starter Kit Receipts** (SKT-XXXXXXXX) - Starter kit purchases
- **Wallet Receipts** (WLT-XXXXXXXX) - Wallet transactions

### 3. **Audit Trail**
All receipts are stored in the `receipts` table with:
- Receipt number
- User ID
- Type
- Amount
- Payment method
- Transaction reference
- PDF file path
- Email status and timestamp
- Metadata (additional details)

### 4. **Email Delivery**
- Receipts automatically emailed to users
- PDF attached to email
- Professional email template
- Tracks email delivery status

### 5. **User Access**
Users can:
- View all their receipts at `/receipts`
- Download receipts as PDF
- View receipts in browser

## Technical Implementation

### Database
```sql
receipts table:
- id
- receipt_number (unique)
- user_id
- type (payment/starter_kit/wallet/purchase)
- amount
- payment_method
- transaction_reference
- description
- pdf_path
- emailed (boolean)
- emailed_at
- metadata (JSON)
- timestamps
```

### Services
- **ReceiptService** - Handles PDF generation and email delivery
- Integrates with DomPDF for PDF generation
- Uses Blade templates for receipt layout

### Routes
```php
GET  /receipts - List all receipts
GET  /receipts/{receipt}/download - Download PDF
GET  /receipts/{receipt}/view - View in browser
```

### Integration Points
1. **VerifyPaymentUseCase** - Generates receipt when payment verified
2. **StarterKitService** - Generates receipt when starter kit purchased
3. **Future**: Wallet transactions, subscriptions, etc.

## Usage

### For Admins
When verifying a payment, a receipt is automatically:
1. Generated as PDF
2. Saved to storage/app/receipts/
3. Recorded in database
4. Emailed to user

### For Users
Users receive:
1. Email with receipt attached
2. Access to view/download from dashboard
3. Complete history of all receipts

## File Storage
- PDFs stored in: `storage/app/receipts/`
- Organized by receipt number
- Persistent storage for audit compliance

## Future Enhancements
- [ ] Bulk receipt download
- [ ] Receipt regeneration
- [ ] Custom receipt templates
- [ ] Multi-currency support
- [ ] Receipt analytics
