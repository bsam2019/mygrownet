# Member Loan Application System

**Date:** 2025-11-05  
**Status:** ✅ Implemented  
**Purpose:** Allow members to apply for loans themselves instead of waiting for admin to issue

## Overview

Previously, only admins could issue loans to members. Now members can:
- View their loan eligibility
- Apply for loans online
- Track application status
- View loan history

Admins can:
- Review pending applications
- Approve or reject with reasons
- Issue loans directly (existing functionality)

## Features

### Member Features

1. **Loan Dashboard**
   - View loan limit and available credit
   - See current loan balance
   - Check eligibility status
   - View pending applications
   - See application history

2. **Loan Application**
   - Apply for K100 - K5,000
   - Choose repayment plan (30/60/90 days)
   - Explain loan purpose
   - Real-time validation

3. **Eligibility Checks**
   - Must have premium starter kit
   - Must have active account
   - Must have available credit
   - Good repayment history (if existing loan)

4. **Application Tracking**
   - See pending status
   - Get notified of approval/rejection
   - View rejection reasons

### Admin Features

1. **Application Review**
   - See all pending applications
   - View applicant details
   - Check loan history
   - One-click approve/reject

2. **Approval Process**
   - Approve → Automatically issues loan
   - Reject → Provide reason to member
   - Notifications sent automatically

## Database Schema

### loan_applications Table

```sql
CREATE TABLE loan_applications (
    id BIGINT PRIMARY KEY,
    user_id BIGINT (FK to users),
    amount DECIMAL(10,2),
    purpose TEXT,
    repayment_plan ENUM('30_days', '60_days', '90_days'),
    status ENUM('pending', 'approved', 'rejected'),
    reviewed_by BIGINT (FK to users),
    reviewed_at TIMESTAMP,
    rejection_reason TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Routes

### Member Routes
```php
GET  /mygrownet/loans          - View loan dashboard
POST /mygrownet/loans/apply    - Submit application
```

### Admin Routes
```php
GET  /admin/loans                                    - View all (includes pending applications)
POST /admin/loans/applications/{id}/approve          - Approve application
POST /admin/loans/applications/{id}/reject           - Reject application
POST /admin/loans/{user}/issue                       - Direct loan issue (existing)
```

## Files Created

1. **app/Http/Controllers/MyGrowNet/LoanApplicationController.php**
   - Member-facing controller
   - Application submission
   - Eligibility checks
   - Idempotency protection

2. **resources/js/pages/MyGrowNet/LoanApplication.vue**
   - Member loan dashboard
   - Application form
   - Application history
   - Eligibility display

3. **database/migrations/2025_11_05_000000_create_loan_applications_table.php**
   - Loan applications table
   - Proper indexes

4. **docs/MEMBER_LOAN_APPLICATION_SYSTEM.md** (this file)
   - Complete documentation

## Files Modified

5. **app/Http/Controllers/Admin/LoanManagementController.php**
   - Added `approveApplication()` method
   - Added `rejectApplication()` method
   - Updated `index()` to show pending applications

6. **routes/web.php**
   - Added member loan routes

7. **routes/admin.php**
   - Added application approval/rejection routes

## Eligibility Criteria

Members must meet ALL of the following:

1. **Premium Starter Kit**
   - Only premium members can apply
   - Basic members see upgrade message

2. **Active Account**
   - Account status must be 'active'
   - Suspended accounts cannot apply

3. **Available Credit**
   - Must have at least K100 available
   - Available = Loan Limit - Current Balance

4. **Good Repayment History** (if existing loan)
   - Must have repaid at least 50% of current loan
   - Prevents over-borrowing

5. **No Pending Applications**
   - Can only have one pending application at a time
   - Must wait for review before reapplying

## Repayment Plans

| Plan | Duration | Monthly Deduction | Best For |
|------|----------|-------------------|----------|
| 30 Days | 1 month | 10% of earnings | Quick needs |
| 60 Days | 2 months | 5% of earnings | Medium term |
| 90 Days | 3 months | 3.33% of earnings | Long term |

## Workflow

### Member Application Flow
```
1. Member visits /mygrownet/loans
2. Checks eligibility
3. Fills application form
   - Amount
   - Purpose
   - Repayment plan
4. Submits application
5. Receives confirmation
6. Waits for admin review
7. Gets notification of decision
```

### Admin Review Flow
```
1. Admin visits /admin/loans
2. Sees pending applications
3. Reviews applicant details
4. Decides:
   a) Approve → Loan issued automatically
   b) Reject → Provides reason
5. Member notified automatically
```

## Notifications

### Member Notifications

**Application Submitted:**
```
Title: Loan Application Submitted
Message: Your loan application for K{amount} has been submitted and is under review.
```

**Application Approved:**
```
Title: Loan Approved
Message: Your loan of K{amount} has been approved and credited to your wallet.
```

**Application Rejected:**
```
Title: Loan Application Rejected
Message: Your loan application for K{amount} has been rejected. Reason: {reason}
```

### Admin Notifications

**New Application:**
```
Title: New Loan Application
Message: {member_name} has applied for a loan of K{amount}
Action: Review Application
```

## Idempotency Protection

The system includes idempotency protection to prevent duplicate applications:

- **Frontend:** 3-second throttle between submissions
- **Backend:** Cache-based idempotency keys
- **Key TTL:** 10 minutes (prevents accidental duplicates)

## Security Features

1. **Authorization**
   - Only authenticated members can apply
   - Only admins can approve/reject

2. **Validation**
   - Amount limits enforced
   - Purpose required (min 20 chars)
   - Repayment plan validated

3. **Rate Limiting**
   - Idempotency protection
   - One pending application at a time

4. **Audit Trail**
   - All applications logged
   - Reviewer tracked
   - Timestamps recorded

## Testing

### Test Member Application

1. Login as premium member
2. Visit `/mygrownet/loans`
3. Check eligibility shows "Eligible"
4. Fill form:
   - Amount: K500
   - Purpose: "Purchase inventory for my shop"
   - Plan: 30 days
5. Submit
6. Verify pending application shows

### Test Admin Approval

1. Login as admin
2. Visit `/admin/loans`
3. See pending application
4. Click "Approve"
5. Verify:
   - Loan issued to member
   - Application status = approved
   - Member notified

### Test Admin Rejection

1. Login as admin
2. Visit `/admin/loans`
3. See pending application
4. Click "Reject"
5. Enter reason
6. Verify:
   - Application status = rejected
   - Member notified with reason

## Future Enhancements

Potential improvements:

1. **Credit Scoring**
   - Automatic approval for good history
   - Risk-based loan limits

2. **Loan Calculator**
   - Show repayment schedule
   - Calculate monthly deductions

3. **Bulk Approval**
   - Approve multiple applications at once

4. **Application Templates**
   - Pre-fill common purposes
   - Quick apply for repeat borrowers

5. **Analytics**
   - Approval rates
   - Average loan amounts
   - Repayment statistics

## Related Documentation

- `docs/MEMBER_LOAN_SYSTEM.md` - Original loan system
- `docs/IDEMPOTENCY_PROTECTION.md` - Duplicate prevention
- `docs/MYGROWNET_PLATFORM_CONCEPT.md` - Platform overview

---

**Implemented By:** Kiro AI Assistant  
**Date:** 2025-11-05  
**Status:** ✅ Ready for Testing
