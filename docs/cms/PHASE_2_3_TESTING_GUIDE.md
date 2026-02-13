# CMS Phase 2 & 3 Testing Guide

**Last Updated:** February 7, 2026  
**Status:** Ready for Testing  
**Phases Covered:** Phase 2 (Invoices & Payments) + Phase 3 (PDF & Reports)

---

## Prerequisites

**Login Credentials:**
- URL: `http://localhost/cms` (or your domain)
- Email: `owner@geopamu.com`
- Password: `password`
- Company: Geopamu Investments Limited

**Required Setup:**
- ✅ Phase 1 complete (Jobs, Customers)
- ✅ Database migrations run
- ✅ Geopamu seeder run
- ✅ DomPDF installed (`composer require barryvdh/laravel-dompdf`)

---

## Test Workflow Overview

```
1. Create Customer
2. Create Job
3. Complete Job
4. Generate Invoice (auto or manual)
5. Send Invoice
6. Record Payment
7. Download PDF
8. View Reports
```

---

## Phase 2 Testing: Invoices & Payments

### Test 1: Auto-Generate Invoice from Completed Job

**Steps:**
1. Navigate to **Jobs** → Click existing job or create new one
2. If new job:
   - Select customer
   - Enter job details (title, estimated value, etc.)
   - Click "Create Job"
3. Click "Complete Job" button
4. Enter actual value and costs
5. Click "Complete"
6. **Expected:** Invoice automatically generated and linked to job

**Verify:**
- ✅ Invoice created with status "Draft"
- ✅ Invoice number format: `INV-2026-0001`
- ✅ Invoice linked to job
- ✅ Invoice amount matches job actual value
- ✅ Customer balance updated

---

### Test 2: Create Manual Invoice

**Steps:**
1. Navigate to **Invoices** → Click "Create Invoice"
2. Select customer from dropdown
3. Set due date (optional, defaults to 30 days)
4. Add invoice items:
   - Description: "Printing Services"
   - Quantity: 100
   - Unit Price: 50
5. Click "Add Item" to add more items
6. Add notes (optional)
7. Click "Create Invoice"

**Verify:**
- ✅ Invoice created successfully
- ✅ Redirected to invoice show page
- ✅ Subtotal calculated correctly (100 × 50 = K5,000)
- ✅ Status is "Draft"
- ✅ Customer balance updated

---

### Test 3: Invoice Status Workflow

**Steps:**
1. Open a draft invoice
2. Click "Send Invoice" button
3. **Expected:** Status changes to "Sent"
4. Try to edit the invoice
5. **Expected:** Edit button disabled (only drafts can be edited)

**Verify:**
- ✅ Status badge changes from gray (Draft) to blue (Sent)
- ✅ Edit button no longer visible
- ✅ "Record Payment" button visible

---

### Test 4: Record Payment (Full Payment)

**Steps:**
1. From invoice show page, click "Record Payment"
2. Or navigate to **Payments** → "Record Payment"
3. Select customer
4. Enter payment amount (equal to invoice total)
5. Select payment method (e.g., "Mobile Money")
6. Enter reference number (e.g., "MM123456")
7. In allocations section, allocate full amount to the invoice
8. Click "Record Payment"

**Verify:**
- ✅ Payment recorded successfully
- ✅ Invoice status changes to "Paid" (green badge)
- ✅ Invoice "Amount Paid" equals total
- ✅ Balance Due shows K0.00
- ✅ Customer outstanding balance updated

---

### Test 5: Record Payment (Partial Payment)

**Steps:**
1. Create a new invoice for K10,000
2. Send the invoice
3. Record payment for K6,000
4. Allocate K6,000 to the invoice

**Verify:**
- ✅ Invoice status changes to "Partial" (amber badge)
- ✅ Amount Paid shows K6,000
- ✅ Balance Due shows K4,000
- ✅ Customer balance reflects K4,000 outstanding

---

### Test 6: Multiple Payments to One Invoice

**Steps:**
1. From previous test (K4,000 remaining)
2. Record another payment for K4,000
3. Allocate to same invoice

**Verify:**
- ✅ Invoice status changes to "Paid"
- ✅ Total amount paid = K10,000
- ✅ Balance due = K0.00
- ✅ Customer balance updated

---

### Test 7: Unallocated Payment

**Steps:**
1. Record payment for K5,000
2. Only allocate K3,000 to an invoice
3. Leave K2,000 unallocated

**Verify:**
- ✅ Payment shows K2,000 unallocated
- ✅ Can allocate remaining amount later
- ✅ Payment list shows unallocated amount in amber

---

### Test 8: Cancel Invoice

**Steps:**
1. Open a sent invoice (not paid)
2. Click "Cancel Invoice" button
3. Enter reason: "Customer cancelled order"
4. Confirm cancellation

**Verify:**
- ✅ Invoice status changes to "Cancelled"
- ✅ Reason appended to notes
- ✅ Customer balance updated (invoice removed from outstanding)
- ✅ Cannot record payments to cancelled invoice

---

### Test 9: Void Paid Invoice

**Steps:**
1. Open a paid invoice
2. Click "Void Invoice" (if available in actions)
3. Enter reason: "Duplicate invoice"
4. Confirm void

**Verify:**
- ✅ Invoice status changes to "Void"
- ✅ Reason appended to notes
- ✅ Customer balance recalculated

---

### Test 10: Invoice Filtering & Search

**Steps:**
1. Navigate to **Invoices**
2. Test status filter:
   - Select "Sent" → Only sent invoices shown
   - Select "Paid" → Only paid invoices shown
3. Test search:
   - Enter invoice number → Invoice found
   - Enter customer name → Customer's invoices shown

**Verify:**
- ✅ Filters work correctly
- ✅ Search finds invoices by number and customer
- ✅ Pagination works
- ✅ Summary stats update based on filters

---

## Phase 3 Testing: PDF & Reports

### Test 11: Download Invoice PDF

**Steps:**
1. Open any invoice
2. Click "Download PDF" button
3. PDF should download automatically

**Verify:**
- ✅ PDF downloads with filename `Invoice-INV-2026-0001.pdf`
- ✅ PDF contains:
  - Company information (Geopamu)
  - Customer information
  - Invoice number and dates
  - Itemized line items
  - Totals (subtotal, total, paid, balance)
  - Status badge
  - Notes (if any)
- ✅ Professional layout with colors
- ✅ All amounts formatted correctly (K format)

---

### Test 12: Preview Invoice PDF

**Steps:**
1. Open any invoice
2. Right-click "Download PDF" → Open in new tab
3. Or modify URL from `/pdf` to `/preview`

**Verify:**
- ✅ PDF opens in browser
- ✅ Can print directly from browser
- ✅ Same content as download

---

### Test 13: Financial Reports - Sales Summary

**Steps:**
1. Navigate to **Reports** (add to menu or go to `/cms/reports`)
2. View default date range (current month)
3. Check sales summary cards

**Verify:**
- ✅ Total Invoices count correct
- ✅ Total Value sum correct
- ✅ Total Paid sum correct
- ✅ Outstanding amount correct
- ✅ Breakdown by status accurate

---

### Test 14: Financial Reports - Payment Summary

**Steps:**
1. On Reports page, scroll to Payment Summary
2. Check total payments and amount
3. View breakdown by payment method

**Verify:**
- ✅ Total payments count correct
- ✅ Total amount sum correct
- ✅ Breakdown shows each method (Cash, Mobile Money, etc.)
- ✅ Each method shows count and total

---

### Test 15: Financial Reports - Job Profitability

**Steps:**
1. On Reports page, view Job Profitability section
2. Check completed jobs metrics

**Verify:**
- ✅ Completed jobs count correct
- ✅ Revenue (actual value) sum correct
- ✅ Cost (total cost) sum correct
- ✅ Profit calculated correctly (Revenue - Cost)
- ✅ Profit margin percentage accurate

---

### Test 16: Financial Reports - Outstanding Invoices

**Steps:**
1. On Reports page, scroll to Outstanding Invoices
2. View list of unpaid/partially paid invoices
3. Check overdue tracking

**Verify:**
- ✅ Only sent/partial invoices shown
- ✅ Balance due calculated correctly
- ✅ Overdue invoices highlighted in red
- ✅ Days overdue calculated correctly
- ✅ Total outstanding sum correct
- ✅ Overdue count and amount accurate

---

### Test 17: Reports Date Range Filter

**Steps:**
1. On Reports page, change date range:
   - Start Date: First day of last month
   - End Date: Last day of last month
2. Click "Apply"

**Verify:**
- ✅ All metrics update based on date range
- ✅ Only invoices/payments in range shown
- ✅ Only jobs completed in range counted
- ✅ Outstanding invoices not affected by date (always current)

---

## Dashboard Testing

### Test 18: Dashboard Stats

**Steps:**
1. Navigate to **Dashboard** (`/cms`)
2. View stat cards

**Verify:**
- ✅ Active Jobs count correct
- ✅ Total Customers count correct
- ✅ Pending Invoices count correct (sent + partial)
- ✅ Monthly Revenue sum correct
- ✅ Total Outstanding amount shown

---

### Test 19: Dashboard Recent Items

**Steps:**
1. On Dashboard, scroll to recent sections

**Verify:**
- ✅ Recent Jobs list shows latest 10 jobs
- ✅ Recent Invoices list shows latest 5 invoices
- ✅ Links work correctly

---

## Edge Cases & Error Handling

### Test 20: Validation Errors

**Test scenarios:**
1. Try to create invoice without customer → Error shown
2. Try to create invoice without items → Error shown
3. Try to record payment without amount → Error shown
4. Try to allocate more than payment amount → Error shown
5. Try to allocate more than invoice balance → Error shown

**Verify:**
- ✅ Validation errors displayed clearly
- ✅ Form data preserved on error
- ✅ No database changes on validation failure

---

### Test 21: Permission Checks

**Test scenarios:**
1. Try to edit sent invoice → Blocked
2. Try to cancel paid invoice → Blocked
3. Try to void non-paid invoice → Blocked

**Verify:**
- ✅ Appropriate error messages
- ✅ Actions disabled in UI
- ✅ Backend validation prevents unauthorized actions

---

### Test 22: Customer Balance Accuracy

**Steps:**
1. Create customer
2. Create 3 invoices for K1,000 each (total K3,000)
3. Send all invoices
4. Pay K1,500 on first invoice
5. Pay K1,000 on second invoice
6. Cancel third invoice

**Verify:**
- ✅ Customer balance = K500 (K1,000 - K1,500 + K1,000 - K1,000 + K0)
- ✅ Balance updates after each action
- ✅ Cancelled invoice not in balance

---

## Performance Testing

### Test 23: Large Data Sets

**Steps:**
1. Create 50+ invoices
2. Create 100+ payments
3. Navigate through pages

**Verify:**
- ✅ Pagination works smoothly
- ✅ Filters respond quickly
- ✅ Reports load in reasonable time (<3 seconds)
- ✅ PDF generation completes (<5 seconds)

---

## Browser Testing

### Test 24: Cross-Browser Compatibility

**Test in:**
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari (if available)

**Verify:**
- ✅ Layout renders correctly
- ✅ Forms work properly
- ✅ PDF downloads work
- ✅ No console errors

---

## Mobile Responsiveness

### Test 25: Mobile View

**Steps:**
1. Open CMS on mobile device or use browser dev tools
2. Navigate through all pages

**Verify:**
- ✅ Dashboard cards stack vertically
- ✅ Tables scroll horizontally
- ✅ Forms are usable
- ✅ Buttons are tappable
- ✅ Modals display correctly

---

## Common Issues & Solutions

### Issue: PDF Not Downloading

**Solution:**
- Check DomPDF is installed: `composer require barryvdh/laravel-dompdf`
- Clear config cache: `php artisan config:clear`
- Check storage permissions

### Issue: Invoice Not Auto-Generating

**Solution:**
- Verify job status is "completed"
- Check job has actual_value set
- Check InvoiceService is injected in JobController

### Issue: Customer Balance Incorrect

**Solution:**
- Check all invoice statuses (exclude cancelled/void)
- Verify payment allocations
- Run balance recalculation if needed

### Issue: Reports Showing Wrong Data

**Solution:**
- Check date range filter
- Verify timezone settings
- Clear browser cache

---

## Success Criteria

**Phase 2 & 3 are successful if:**

✅ All 25 tests pass  
✅ No console errors  
✅ No database errors  
✅ Customer balances accurate  
✅ PDFs generate correctly  
✅ Reports show accurate data  
✅ Workflow is intuitive  
✅ Performance is acceptable  

---

## Next Steps After Testing

1. **Fix any bugs found** during testing
2. **Add email notifications** (Phase 4)
3. **Implement expense management** (Phase 4)
4. **Add recurring invoices** (Phase 4)
5. **Deploy to production**

---

## Testing Checklist

Print this checklist and mark off as you test:

- [ ] Test 1: Auto-generate invoice
- [ ] Test 2: Manual invoice
- [ ] Test 3: Invoice status workflow
- [ ] Test 4: Full payment
- [ ] Test 5: Partial payment
- [ ] Test 6: Multiple payments
- [ ] Test 7: Unallocated payment
- [ ] Test 8: Cancel invoice
- [ ] Test 9: Void invoice
- [ ] Test 10: Filtering & search
- [ ] Test 11: Download PDF
- [ ] Test 12: Preview PDF
- [ ] Test 13: Sales summary
- [ ] Test 14: Payment summary
- [ ] Test 15: Job profitability
- [ ] Test 16: Outstanding invoices
- [ ] Test 17: Date range filter
- [ ] Test 18: Dashboard stats
- [ ] Test 19: Recent items
- [ ] Test 20: Validation errors
- [ ] Test 21: Permission checks
- [ ] Test 22: Customer balance
- [ ] Test 23: Large data sets
- [ ] Test 24: Cross-browser
- [ ] Test 25: Mobile view

---

**Happy Testing! 🚀**
