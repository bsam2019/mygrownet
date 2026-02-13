# Vendor Management & Purchase Orders

**Last Updated:** February 12, 2026  
**Status:** Production Ready

## Overview

Complete vendor management and purchase order system with vendor registration, PO creation, approval workflows, goods receiving, vendor invoicing, payments, and performance tracking.

## Features

### Vendor Registration
- Vendor profile management
- Contact information and business details
- Tax and registration numbers
- Payment terms configuration
- Bank and mobile money details
- Vendor type classification (supplier, contractor, service provider)
- Active/inactive/blocked status

### Purchase Order Creation
- Create POs with multiple line items
- Link to inventory items
- Automatic PO numbering (PO-YYYY-####)
- Expected delivery dates
- Delivery address
- Terms and conditions
- Multi-currency support
- Automatic tax calculation (16% VAT)

### PO Approval Workflow
- Draft → Pending Approval → Approved → Sent
- Approval tracking with user and timestamp
- Rejection capability
- Audit trail for all status changes

### Receive Goods
- Goods Received Notes (GRN)
- Partial receiving support
- Quantity received vs ordered tracking
- Rejection tracking with reasons
- Automatic inventory updates
- PO status updates (partially received/received)
- Actual delivery date tracking

### Vendor Invoices
- Record vendor invoices
- Link to purchase orders
- Due date tracking
- Payment status (pending, approved, paid, overdue)
- Attachment support
- Balance tracking

### Vendor Payments
- Record payments to vendors
- Link to vendor invoices
- Multiple payment methods
- Reference number tracking
- Payment history

### Vendor Performance Tracking
- Quality rating (1-5)
- Delivery rating (1-5)
- Communication rating (1-5)
- Pricing rating (1-5)
- Overall rating calculation
- On-time delivery rate
- Total spending per vendor
- Total orders per vendor
- Performance reports

## Database Schema

### cms_vendors
- Vendor information and contacts
- Business and tax details
- Payment terms and methods
- Performance metrics

### cms_purchase_orders
- PO header information
- Vendor relationship
- Dates and delivery
- Amounts and currency
- Approval tracking

### cms_purchase_order_items
- Line items for each PO
- Quantity and pricing
- Receiving tracking
- Inventory linkage

### cms_goods_received_notes
- GRN header information
- Received date and person
- Discrepancies tracking

### cms_grn_items
- Items received per GRN
- Quantity received/rejected
- Rejection reasons

### cms_vendor_invoices
- Vendor invoice tracking
- Payment status
- Due dates

### cms_vendor_payments
- Payment records
- Payment methods
- Reference tracking

### cms_vendor_ratings
- Performance ratings
- Comments and feedback
- Overall rating calculation

## Implementation Files

### Backend
- `database/migrations/2026_02_12_180000_create_cms_vendor_management_tables.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/VendorModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/PurchaseOrderModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/PurchaseOrderItemModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/VendorRatingModel.php`
- `app/Domain/CMS/Core/Services/VendorManagementService.php`

## Usage

### Creating a Vendor
```php
POST /cms/vendors
{
  "name": "ABC Suppliers Ltd",
  "email": "info@abcsuppliers.com",
  "phone": "+260971234567",
  "vendor_type": "supplier",
  "payment_terms_days": 30,
  "tax_number": "1234567890"
}
```

### Creating a Purchase Order
```php
POST /cms/purchase-orders
{
  "vendor_id": 1,
  "po_date": "2026-02-12",
  "expected_delivery_date": "2026-02-20",
  "items": [
    {
      "description": "Cement 50kg",
      "quantity": 100,
      "unit": "bags",
      "unit_price": 150,
      "total_price": 15000
    }
  ]
}
```

### Approving a Purchase Order
```php
POST /cms/purchase-orders/{po}/approve
```

### Receiving Goods
```php
POST /cms/purchase-orders/{po}/receive
{
  "received_items": [
    {
      "po_item_id": 1,
      "quantity_received": 95,
      "quantity_rejected": 5,
      "rejection_reason": "Damaged bags"
    }
  ]
}
```

### Rating a Vendor
```php
POST /cms/vendors/{vendor}/rate
{
  "purchase_order_id": 1,
  "quality_rating": 5,
  "delivery_rating": 4,
  "communication_rating": 5,
  "pricing_rating": 4,
  "comments": "Excellent quality, slight delay in delivery"
}
```

## Business Rules

1. **PO Numbering**: Format PO-YYYY-#### (e.g., PO-2026-0001)
2. **Tax Calculation**: Automatic 16% VAT on subtotal
3. **Receiving**: Partial receiving allowed, PO status updates automatically
4. **Inventory Updates**: Automatic stock updates when goods received
5. **Vendor Rating**: Overall rating = average of 4 rating categories
6. **Performance Metrics**: Automatically updated after each transaction
7. **On-Time Delivery**: Calculated based on expected vs actual delivery dates
8. **Approval Required**: POs must be approved before sending to vendor

## Security

- Company-scoped queries (all queries filtered by company_id)
- Authorization policies for approval actions
- Audit trail for all vendor and PO activities
- Validation of vendor and inventory relationships

## Future Enhancements

- Email notifications to vendors
- PDF generation for POs
- Vendor portal for self-service
- Automated reordering based on inventory levels
- Vendor comparison reports
- Contract management
- RFQ (Request for Quotation) system
- Vendor onboarding workflow
- Integration with accounting systems
- Barcode scanning for receiving
- Mobile app for goods receiving

## Changelog

### February 12, 2026
- Initial implementation
- Vendor registration
- Purchase order creation
- Approval workflow
- Goods receiving
- Vendor invoicing
- Vendor payments
- Performance tracking
