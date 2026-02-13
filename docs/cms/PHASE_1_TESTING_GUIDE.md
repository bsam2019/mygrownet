# CMS Phase 1 Testing Guide

**Last Updated:** February 7, 2026  
**Status:** Ready for Testing

---

## Quick Start

### 1. Access the CMS

**URL:** `http://your-domain.com/cms`

**Credentials:**
- Email: `owner@geopamu.com`
- Password: `password`

**Note:** You should now be logged in and able to access the CMS dashboard.

### 2. What to Test

---

## Test Scenarios

### Scenario 1: Dashboard Overview

**Steps:**
1. Login with test credentials
2. Navigate to `/cms`
3. Verify dashboard displays:
   - Company name: "Geopamu Investments Limited"
   - User name and role badge
   - 4 stat cards (Active Jobs, Total Customers, Pending Invoices, Monthly Revenue)
   - Quick action buttons
   - Recent jobs table (empty initially)

**Expected Result:** Dashboard loads without errors, all UI elements visible

---

### Scenario 2: Create Customer

**Steps:**
1. Click "Add Customer" from dashboard or navigate to Customers
2. Fill in form:
   - Name: "Test Customer Ltd"
   - Phone: "+260977123456"
   - Email: "test@customer.com"
   - Address: "123 Test Street, Lusaka"
   - Credit Limit: "5000"
3. Click "Add Customer"

**Expected Result:**
- Redirected to customer detail page
- Customer number auto-generated (CUST-0001)
- All details displayed correctly
- Success message shown

---

### Scenario 3: Create Job

**Steps:**
1. Navigate to Jobs → Create Job
2. Fill in form:
   - Customer: Select "Test Customer Ltd"
   - Job Type: "Business Cards - 1000 pcs"
   - Description: "Full color, double-sided, 350gsm"
   - Quoted Value: "500"
   - Priority: "Normal"
   - Deadline: (select tomorrow's date)
   - Notes: "Customer wants matte finish"
3. Click "Create Job"

**Expected Result:**
- Redirected to job detail page
- Job number auto-generated (JOB-2026-0001)
- Status shows "Pending"
- All details displayed correctly
- "Assign Job" button visible

---

### Scenario 4: View Job Details

**Steps:**
1. Navigate to Jobs list
2. Click "View" on the created job
3. Verify all sections display:
   - Job header with number and status
   - Job details section
   - Assignment section (shows "Not assigned")
   - Metadata section

**Expected Result:** All job information displayed correctly

---

### Scenario 5: View Customer Details

**Steps:**
1. Navigate to Customers list
2. Click "View" on the created customer
3. Verify sections:
   - Customer information
   - Financial summary (Outstanding Balance: K0.00, Credit Limit: K5,000.00)
   - Recent jobs (shows the created job)
   - Quick actions ("Create Job" button)

**Expected Result:** All customer information and related jobs displayed

---

### Scenario 6: Search and Filter

**Customers:**
1. Navigate to Customers
2. Use search box to search by name, number, or phone
3. Filter by status (Active/Inactive)

**Jobs:**
1. Navigate to Jobs
2. Filter by status (Pending/In Progress/Completed/Cancelled)

**Expected Result:** Filtering and search work correctly

---

## Database Verification

### Check Auto-Generated Numbers

```sql
-- Check customer numbers
SELECT id, customer_number, name FROM cms_customers;

-- Check job numbers
SELECT id, job_number, job_type, status FROM cms_jobs;
```

**Expected:**
- Customer numbers: CUST-0001, CUST-0002, etc.
- Job numbers: JOB-2026-0001, JOB-2026-0002, etc.

### Check Audit Trail

```sql
-- Check audit logs
SELECT * FROM cms_audit_trail ORDER BY created_at DESC LIMIT 10;
```

**Expected:** Entries for customer creation, job creation with old/new values

### Check Multi-Tenancy

```sql
-- All records should have company_id = 1 (Geopamu)
SELECT 'customers' as table_name, COUNT(*) as count FROM cms_customers WHERE company_id = 1
UNION ALL
SELECT 'jobs', COUNT(*) FROM cms_jobs WHERE company_id = 1
UNION ALL
SELECT 'roles', COUNT(*) FROM cms_roles WHERE company_id = 1;
```

**Expected:** All records scoped to company_id = 1

---

## Known Limitations (Phase 1)

The following features are **not yet implemented** (coming in Phase 2):

- ❌ Job assignment functionality (button visible but not functional)
- ❌ Job completion workflow
- ❌ Invoice generation
- ❌ Payment recording
- ❌ Expense management
- ❌ Financial reports
- ❌ Customer edit functionality
- ❌ Job edit functionality

---

## Troubleshooting

### Issue: "No CMS company access" error

**Solution:**
1. Check if user has CMS user record:
   ```sql
   SELECT * FROM cms_users WHERE user_id = (SELECT id FROM users WHERE email = 'owner@geopamu.com');
   ```
2. If missing, run seeder again:
   ```bash
   php artisan db:seed --class=GeopamuCmsSeeder
   ```

### Issue: Dashboard shows 0 for all stats

**Solution:** This is normal if no data exists yet. Create customers and jobs to see stats update.

### Issue: Auto-generated numbers not working

**Solution:**
1. Check if migrations ran successfully
2. Verify database has proper indexes
3. Check JobService and CustomerService for number generation logic

---

## Success Criteria

Phase 1 is considered successful if:

- ✅ User can login and access CMS dashboard
- ✅ User can create customers with auto-generated numbers
- ✅ User can create jobs linked to customers
- ✅ User can view customer and job details
- ✅ Search and filtering work correctly
- ✅ All data is properly scoped to company (multi-tenant)
- ✅ Audit trail records all changes
- ✅ No console errors or PHP exceptions
- ✅ UI is responsive and user-friendly

---

## Next Steps

After successful Phase 1 testing:

1. Proceed to Phase 2 implementation (Quotations, Invoices, Payments)
2. Implement job assignment and completion workflows
3. Add invoice generation from completed jobs
4. Implement payment recording and allocation
5. Build financial reports

---

## Feedback

Document any issues or suggestions:
- UI/UX improvements needed
- Missing validations
- Performance concerns
- Feature requests

Update this document with findings for future reference.
