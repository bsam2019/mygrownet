# HRMS Testing Guide

**Last Updated:** February 14, 2026  
**Version:** 1.0  
**Phase:** 1 - Core HR Enhancement

---

## Pre-Testing Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Run Seeders
```bash
php artisan db:seed --class=DefaultLeaveTypesSeeder
php artisan db:seed --class=ZambianPublicHolidaysSeeder
```

### 3. Initialize Leave Balances (for existing workers)
```bash
php artisan cms:initialize-leave-balances
```

### 4. Build Frontend
```bash
npm run build
```

---

## Test Scenarios

### Department Management

#### Test 1: Create Department
1. Navigate to HR Management > Departments
2. Click "Create Department"
3. Fill in:
   - Name: "Human Resources"
   - Code: "HR"
   - Branch: (select if available)
   - Manager: (select a worker)
   - Description: "Handles recruitment and employee relations"
4. Click "Create Department"
5. **Expected:** Success message, redirected to departments list
6. **Verify:** New department appears in list

#### Test 2: Edit Department
1. From departments list, click "Edit" on a department
2. Change the name
3. Click "Update Department"
4. **Expected:** Success message, changes reflected in list

#### Test 3: Delete Department
1. Try to delete a department with assigned workers
2. **Expected:** Error message "Cannot delete department with assigned workers"
3. Delete a department with no workers
4. **Expected:** Success message, department removed from list

---

### Worker Management

#### Test 4: Create Worker with Full HR Fields
1. Navigate to Payroll > Workers
2. Click "Add Worker"
3. Fill in all sections:
   - **Personal Information:**
     - First Name: "John"
     - Last Name: "Doe"
     - Date of Birth: "1990-01-15"
     - Gender: "Male"
     - Phone: "+260 XXX XXX XXX"
     - Email: "john.doe@example.com"
     - Address, City, Province
   - **Emergency Contact:**
     - Name, Phone, Relationship
   - **Employment Details:**
     - Job Title: "Construction Worker"
     - Department: (select)
     - Hire Date: (today's date)
     - Employment Type: "Full Time"
   - **Compensation:**
     - Monthly Salary: 5000
   - **Tax & Benefits:**
     - TPIN, NAPSA, NHIMA numbers
   - **Payment Method:**
     - Select method and fill details
4. Click "Create Worker"
5. **Expected:** Success message, redirected to worker details
6. **Verify:** All information displays correctly

#### Test 5: View Worker Details
1. From workers list, click "View" on a worker
2. **Verify:**
   - Personal information section shows all fields
   - Employment details section shows job title and department
   - Emergency contact section (if filled)
   - Compensation section shows salary/rates
   - Tax & Benefits section shows TPIN, NAPSA, NHIMA

#### Test 6: Workers List Display
1. Navigate to workers list
2. **Verify:**
   - Job Title column shows worker's job title
   - Department column shows department name
   - Search works for first name, last name
   - Filter by worker type works
   - Filter by employment status works

---

### Leave Management

#### Test 7: View Leave Types
1. Check database for leave types:
```sql
SELECT * FROM cms_leave_types;
```
2. **Expected:** 7 Zambian leave types:
   - Annual Leave (21 days)
   - Sick Leave (12 days)
   - Maternity Leave (90 days)
   - Paternity Leave (3 days)
   - Compassionate Leave (3 days)
   - Unpaid Leave (0 days)
   - Study Leave (0 days)

#### Test 8: View Leave Balance
1. Navigate to HR Management > Leave Management
2. Click on a worker's leave balance
3. **Verify:**
   - Summary cards show total available, used, pending days
   - Each leave type shows:
     - Total days allocated
     - Used days
     - Pending days
     - Available days
     - Progress bar

#### Test 9: Create Leave Request
1. Click "Request Leave"
2. Fill in:
   - Leave Type: "Annual Leave"
   - Start Date: (future date)
   - End Date: (future date, 5 days later)
   - Reason: "Family vacation"
3. Click "Submit Request"
4. **Expected:**
   - Success message
   - Leave request appears in list with "Pending" status
   - Working days calculated correctly (excludes weekends)
   - Leave balance shows pending days

#### Test 10: Approve Leave Request
1. From leave requests list, click on a pending request
2. Click "Approve"
3. Confirm approval
4. **Expected:**
   - Status changes to "Approved"
   - Leave balance updated (used days increased, available decreased)
   - Pending days cleared

#### Test 11: Reject Leave Request
1. Create another leave request
2. Click "Reject"
3. Enter rejection reason
4. Confirm rejection
5. **Expected:**
   - Status changes to "Rejected"
   - Leave balance unchanged (pending days cleared)

#### Test 12: Working Days Calculation
1. Create leave request spanning a weekend
2. **Verify:** Working days excludes Saturday and Sunday
3. Create leave request including a public holiday
4. **Verify:** Working days excludes the public holiday

---

### Navigation

#### Test 13: HR Management Menu
1. Check sidebar navigation
2. **Verify:**
   - "HR Management" section visible
   - "Departments" menu item present
   - "Leave Management" menu item present
   - Active state highlights correctly when on each page

---

## Edge Cases

### Test 14: Leave Balance Insufficient
1. Try to request more leave days than available
2. **Expected:** Validation error or warning

### Test 15: Overlapping Leave Requests
1. Create a leave request for dates Jan 10-15
2. Try to create another request for Jan 12-17
3. **Expected:** Validation error about overlapping dates

### Test 16: Backward Compatibility
1. View existing workers created before HRMS
2. **Verify:**
   - Workers display correctly in list
   - Can view worker details (new fields show as empty)
   - Can edit and update workers

---

## Performance Testing

### Test 17: Large Dataset
1. Create 100+ workers
2. Navigate to workers list
3. **Verify:**
   - Page loads within 2 seconds
   - Pagination works correctly
   - Search is responsive

### Test 18: Leave Balance Initialization
1. Run initialization command for all workers:
```bash
php artisan cms:initialize-leave-balances --force
```
2. **Verify:**
   - Command completes successfully
   - All workers have leave balances
   - No duplicate balances created

---

## Regression Testing

### Test 19: Existing Payroll Functionality
1. Create attendance record for a worker
2. **Verify:** Attendance recording still works
3. Create commission record
4. **Verify:** Commission recording still works
5. Generate payroll
6. **Verify:** Payroll generation includes all workers

### Test 20: Existing Worker Operations
1. Record attendance for worker with new HR fields
2. **Verify:** No errors, attendance recorded correctly
3. View payroll for worker with new HR fields
4. **Verify:** Payroll displays correctly

---

## Security Testing

### Test 21: Permission Checks
1. Test as different user roles
2. **Verify:**
   - Only authorized users can approve/reject leave
   - Only authorized users can create/edit departments
   - Workers can only view their own leave balance

### Test 22: Data Validation
1. Try to create worker with invalid email
2. **Expected:** Validation error
3. Try to create leave request with end date before start date
4. **Expected:** Validation error
5. Try to create department with duplicate code
6. **Expected:** Validation error

---

## Bug Reporting Template

When reporting bugs, include:

```
**Bug Title:** [Brief description]

**Steps to Reproduce:**
1. 
2. 
3. 

**Expected Behavior:**
[What should happen]

**Actual Behavior:**
[What actually happened]

**Environment:**
- Browser: 
- User Role: 
- Company ID: 

**Screenshots:**
[If applicable]

**Error Messages:**
[Copy any error messages]
```

---

## Test Sign-Off

| Test Category | Status | Tester | Date | Notes |
|--------------|--------|--------|------|-------|
| Department Management | ⏳ | | | |
| Worker Management | ⏳ | | | |
| Leave Management | ⏳ | | | |
| Navigation | ⏳ | | | |
| Edge Cases | ⏳ | | | |
| Performance | ⏳ | | | |
| Regression | ⏳ | | | |
| Security | ⏳ | | | |

**Legend:**
- ⏳ Pending
- ✅ Passed
- ❌ Failed
- ⚠️ Passed with issues

---

## Post-Testing

### Checklist
- [ ] All critical tests passed
- [ ] All bugs documented
- [ ] Performance acceptable
- [ ] Security verified
- [ ] Documentation updated
- [ ] Training materials prepared
- [ ] Deployment plan reviewed

---

## Support

For issues or questions:
- Check HRMS_DEPLOYMENT_GUIDE.md
- Check HRMS_COMPLETE_IMPLEMENTATION.md
- Review error logs: `storage/logs/laravel.log`
