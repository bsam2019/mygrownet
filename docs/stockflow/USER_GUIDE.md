# StockFlow User Guide

## Table of Contents
1. Getting Started
2. Items (Inventory)
3. Suppliers
4. Purchases
5. Sales
6. Quotations
7. Invoices
8. Receipts
9. Cash Register
10. Physical Counts
11. Audits
12. Departments and Bins
13. Stock Movements
14. Reports
15. Settings

## Getting Started

### Accessing StockFlow
Each business has its own subdomain. If you don't have login credentials, contact the company administrator.

### Logging In
1. Navigate to your company's subdomain
2. Click Login on the landing page
3. Enter your email and password

### Dashboard
Shows Total Items, Low Stock Items, Today's Sales, Open Cash Register status, Recent Sales, and Quick Actions.

## Items (Inventory)
Products you buy, sell, and track.

### Creating an Item
1. Go to Items in the sidebar
2. Click Create Item
3. Fill in: Name (required), SKU (auto-generated), Category, Unit, Cost Price, Selling Price, Opening Stock, Reorder Level, Description
4. Click Save

### Adjusting Stock
1. Open an item's detail page
2. Click Adjust Stock
3. Enter new quantity and a reason
4. Click Save — a StockMovement record is created

### Importing from CSV
1. Go to Items list, click Import
2. Upload CSV with columns: name, sku, category, unit, cost_price, selling_price, opening_stock, reorder_level

## Suppliers
1. Go to Suppliers, click Add Supplier
2. Enter name, contact person, phone, email, address
3. Click Save

## Purchases (Purchase Orders)

### Creating a PO
1. Go to Purchases, click Create Purchase Order
2. Select a supplier, add items with quantities and prices
3. Click Save — PO is created as Pending

### Receiving Stock
1. Open a Pending PO
2. Click Receive Stock
3. Enter actual quantities received
4. Click Confirm — stock is updated

## Sales (Point of Sale)

### Creating a Sale
1. Go to Sales, click New Sale
2. Select items and quantities
3. Choose payment method (Cash, Mobile Money, Card, Bank Transfer)
4. Click Complete Sale
   - Stock is deducted automatically
   - Receipt number is generated
   - If cash register is open, the sale is recorded

### Sales Report
View and export PDF sales reports filtered by date range.

## Quotations
Create quotes for customers.

### Creating
1. Go to Quotations, click Create Quotation
2. Add customer name and items
3. Click Save — number QTN-XXXXXX assigned

### Status Workflow
Draft -> Sent -> Accepted/Rejected -> Convert to Invoice

### Converting to Invoice
On the quotation detail page, click Convert to Invoice.

## Invoices
Bill customers and track payments.

### Creating
1. Go to Invoices, click Create Invoice
2. Add customer and items (optionally link to a quotation)
3. Click Save — number INV-XXXXXX assigned

### Recording a Payment
1. Open an invoice
2. Click Record Payment
3. Enter amount and method
4. Click Save — a receipt is auto-generated

### Status
Draft -> Sent -> Paid/Cancelled

## Receipts
Auto-generated when a sale is completed or an invoice payment is recorded. View in Receipts section. Number format: RCT-XXXXXX.

## Cash Register

### Opening
1. Go to Cash Register
2. Click Open Register, enter opening balance, click Open

### Movements
Add expenses or banking while register is open. Expected closing auto-calculates.

### Closing
1. Click Close Register
2. Enter actual counted closing amount
3. System shows discrepancy

### Verifying
A manager can verify a closed register to confirm the numbers.

## Physical Counts

### Starting a Count
1. Go to Physical Counts, click Start New Count
2. System auto-populates all items with current system quantities
3. Enter actual physical quantities
4. Click Complete Count — system quantities update to match physical counts

### Generating an Audit
After completing a count, click Generate Audit.

## Audits
Full reconciliation of inventory.

An audit shows: expected vs physical quantity for each item, variance, recorded sales, unaccounted value.

### Finalizing
1. Open the audit, review variances
2. Click Finalize — audit is locked permanently
3. Export as CSV or PDF

## Departments and Bins
Organise inventory by location. Departments are groups (e.g., Pharmacy). Bins are specific locations (e.g., Shelf A1).

## Stock Movements
Complete history of every stock change: date, item, type, quantity before/after, user.

## Reports
- Sales Report (PDF)
- Purchases Report (PDF)
- Inventory Report (PDF)
- Cash Summary (PDF)
- Reports Hub with graphs

## Settings
Configure company details (name, address, logo) and profile (name, password). Feature flags enable/disable modules.
