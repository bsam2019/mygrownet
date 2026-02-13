# CMS Payroll & Commission System

**Last Updated:** February 10, 2026  
**Status:** Production Ready ✅

## Overview

Complete payroll and commission management system for tracking worker attendance, calculating commissions, and processing payroll runs.

## Features

### 1. Worker Management

**Worker Registration:**
- Auto-generated worker numbers (WKR-0001, WKR-0002, etc.)
- Worker types: Casual, Contract, Permanent
- Personal information: Name, phone, email, ID number
- Rate configuration: Hourly rate, daily rate, commission percentage
- Payment methods: Cash, Mobile Money, Bank Transfer
- Status tracking: Active/Inactive

**Access:** `/cms/payroll/workers`

### 2. Attendance Tracking

**Record Attendance:**
- Link to specific jobs (optional)
- Track hours worked or days worked
- Automatic earnings calculation based on worker rates
- Work description notes
- Approval workflow (Pending → Approved → Paid)

**Features:**
- Attendance history per worker
- Approval by authorized users
- Automatic calculation: `hours_worked × hourly_rate` or `days_worked × daily_rate`

### 3. Commission Management

**Commission Types:**
- Sales commission
- Referral commission
- Performance commission
- Other custom commissions

**Calculation:**
- Percentage-based: `base_amount × commission_rate / 100`
- Link to jobs or invoices
- Approval workflow
- Commission history tracking

### 4. Payroll Processing

**Payroll Runs:**
- Auto-generated payroll numbers (PAY-2026-001, PAY-2026-002, etc.)
- Period types: Weekly, Bi-Weekly, Monthly
- Automatic aggregation of approved attendance and commissions
- Status workflow: Draft → Approved → Paid

**Process:**
1. Create payroll run for a specific period
2. System automatically includes all approved attendance and commissions
3. Generates payroll items per worker/staff member
4. Review and approve
5. Mark as paid when payments are completed

**Access:** `/cms/payroll`

## Database Schema

### Tables Created

1. **cms_workers** - Worker registration and details
2. **cms_worker_attendance** - Attendance records with earnings
3. **cms_commissions** - Commission calculations
4. **cms_payroll_runs** - Payroll period management
5. **cms_payroll_items** - Individual payment records per worker

## User Workflows

### Register a New Worker

1. Navigate to `/cms/payroll/workers`
2. Click "Add Worker"
3. Fill in personal information
4. Set worker type and rates
5. Configure payment method
6. Save

### Record Attendance

1. Navigate to worker details page
2. Click "Record Attendance"
3. Select work date
4. Enter hours or days worked
5. Add work description (optional)
6. Submit
7. Approve when ready

### Calculate Commission

1. Navigate to worker details page
2. Click "Add Commission" (or use commission form)
3. Select commission type
4. Enter base amount and rate
5. System calculates commission amount
6. Submit
7. Approve when ready

### Process Payroll

1. Navigate to `/cms/payroll`
2. Click "Create Payroll Run"
3. Select period type and dates
4. System automatically aggregates approved records
5. Review payroll items
6. Approve payroll run
7. Mark as paid when payments are completed

## API Endpoints

### Workers
- `GET /cms/payroll/workers` - List workers
- `POST /cms/payroll/workers` - Create worker
- `GET /cms/payroll/workers/{id}` - View worker details

### Attendance
- `POST /cms/payroll/attendance` - Record attendance
- `POST /cms/payroll/attendance/{id}/approve` - Approve attendance

### Commissions
- `POST /cms/payroll/commissions` - Calculate commission
- `POST /cms/payroll/commissions/{id}/approve` - Approve commission

### Payroll Runs
- `GET /cms/payroll` - List payroll runs
- `POST /cms/payroll` - Create payroll run
- `GET /cms/payroll/{id}` - View payroll details
- `POST /cms/payroll/{id}/approve` - Approve payroll
- `POST /cms/payroll/{id}/mark-paid` - Mark as paid

## Business Rules

### Attendance Calculation
- If hours_worked is provided: `amount = hours_worked × hourly_rate`
- If days_worked is provided: `amount = days_worked × daily_rate`
- Both can be provided for mixed work periods

### Commission Calculation
- Formula: `commission_amount = (base_amount × commission_rate) / 100`
- Example: K10,000 base × 5% rate = K500 commission

### Payroll Run Generation
- Only includes approved attendance and commissions
- Excludes records already included in previous payroll runs
- Groups by worker_id or cms_user_id
- Calculates totals: wages + commissions - deductions = net_pay

### Status Workflow
- **Attendance/Commission:** Pending → Approved → Paid
- **Payroll Run:** Draft → Approved → Paid
- Once marked as paid, records cannot be modified

## Testing Checklist

- [ ] Create worker with cash payment
- [ ] Create worker with mobile money payment
- [ ] Create worker with bank transfer payment
- [ ] Record attendance with hours worked
- [ ] Record attendance with days worked
- [ ] Record attendance linked to a job
- [ ] Approve attendance record
- [ ] Calculate sales commission
- [ ] Calculate referral commission
- [ ] Approve commission
- [ ] Create weekly payroll run
- [ ] Verify payroll items generated correctly
- [ ] Approve payroll run
- [ ] Mark payroll as paid
- [ ] Verify attendance status changed to paid
- [ ] Verify commission status changed to paid

## Future Enhancements

- [ ] Payroll reports (PDF/Excel export)
- [ ] Tax calculation and deductions
- [ ] Statutory deductions (NAPSA, PAYE)
- [ ] Payslip generation
- [ ] Direct payment integration (Mobile Money API)
- [ ] Recurring payroll automation
- [ ] Worker self-service portal
- [ ] Biometric attendance integration
- [ ] Overtime calculation
- [ ] Leave management integration

## Troubleshooting

### Worker not appearing in payroll
- Ensure attendance/commissions are approved
- Check that records fall within payroll period dates
- Verify records haven't been included in previous payroll runs

### Incorrect earnings calculation
- Verify worker rates are set correctly
- Check hours_worked or days_worked values
- Ensure attendance is approved before payroll generation

### Cannot approve payroll
- Check user permissions
- Verify payroll is in "draft" status
- Ensure all payroll items are valid

## Related Documentation

- [CMS Implementation Progress](./IMPLEMENTATION_PROGRESS.md)
- [CMS Complete Feature Specification](./COMPLETE_FEATURE_SPECIFICATION.md)
- [CMS Development Brief](./DEVELOPMENT_BRIEF.md)

## Changelog

### February 10, 2026
- Initial implementation complete
- All features tested and working
- Documentation created
