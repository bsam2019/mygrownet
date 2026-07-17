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
16. Industry Extensions
17. CSV Import & Templates
18. Pricing & Trial Periods

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

## Industry Extensions

StockFlow supports industry-specific extensions that add features beyond core inventory management. Extensions are assigned to companies by an administrator.

### Pharmacy Extension — Controlled Medicines
Track controlled medicines by name, dosage, schedule (e.g. 2/2 for Second Schedule), quantity, and expiry date. Helps pharmacies comply with regulatory requirements.

- Navigate to Controlled Medicines in the sidebar
- Click Add Controlled Medicine
- Enter medicine name, dosage, schedule, quantity, and expiry
- Click Save

### Manufacturing Extension — BOMs & Work Orders
Bill of Materials (BOM) management and work order production scheduling.

**BOMs:**
1. Go to BOMs in the sidebar
2. Click Create BOM
3. Enter product name, output quantity, and raw materials with costs
4. Click Save — total cost is auto-calculated

**Work Orders:**
1. Go to Work Orders
2. Click Create Work Order
3. Select a BOM, enter quantity, set due date
4. Click Save — material requirements auto-calculated
5. When production starts, issue materials from inventory

### Restaurant Extension — Recipes & Wastage

**Recipes:**
1. Go to Recipes
2. Click Create Recipe
3. Add ingredients with quantities
4. Set selling price — cost and profit margin auto-calculated
5. Save for menu costing

**Wastage:**
1. Go to Wastage
2. Click Record Wastage
3. Enter item, quantity, reason (spoilage, return, expired), and cost
4. Click Save to track losses

## CSV Import & Templates

### Downloading Templates
On any list page (Items, Suppliers, Bins), click the **Template** button to download a CSV with the correct column headers.

### Bulk Import
1. Fill in the template with your data using Excel or Google Sheets
2. On the list page, click **Import CSV**
3. Select your prepared CSV file
4. The system imports records in bulk
   - Items: bin names are auto-resolved to bin IDs
   - Bins: department names are auto-resolved
   - Errors are reported per row

## Pricing & Trial Periods

### Dual-Currency Pricing
Each extension has pricing in both USD and Zambian Kwacha (ZMW):
- **USD** — applies internationally
- **ZMW** — applies for Zambian businesses

### Free Trials
When an extension is assigned to your company, if a trial period is configured (default: 14 days), your company receives full access to all extension features during the trial. The sidebar shows a "Trial" badge with the expiry date. After the trial, an active subscription is required to continue using the extension.
