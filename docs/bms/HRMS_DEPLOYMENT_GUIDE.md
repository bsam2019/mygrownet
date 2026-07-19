# HRMS Deployment Guide

**Last Updated:** February 14, 2026  
**Version:** Phase 1 - Core HR Enhancement  
**Status:** Ready for Testing

---

## Prerequisites

- Laravel 12.0+ installed
- PHP 8.2+ configured
- Database connection configured
- Node.js and npm installed
- Composer dependencies installed

---

## Step 1: Run Database Migrations

Run the HRMS migrations in order:

```bash
# Run all pending migrations
php artisan migrate

# Or run specific migrations in order
php artisan migrate --path=database/migrations/2026_02_14_090000_create_cms_departments_and_branches.php
php artisan migrate --path=database/migrations/2026_02_14_100000_enhance_cms_workers_for_hrms.php
php artisan migrate --path=database/migrations/2026_02_14_110000_create_cms_leave_management_tables.php
```

**Expected Output:**
- 7 new tables created (cms_branches, cms_departments, cms_leave_types, cms_leave_balances, cms_leave_requests, cms_public_holidays)
- cms_workers table enhanced with 25+ new columns

---

## Step 2: Run Seeders

Seed default leave types and public holidays:

```bash
# Seed default leave types (7 Zambian leave types)
php artisan db:seed --class=DefaultLeaveTypesSeeder

# Seed Zambian public holidays for 2026
php artisan db:seed --class=ZambianPublicHolidaysSeeder
```

**Expected Output:**
- 7 leave types created per company (Annual, Sick, Maternity, Paternity, Compassionate, Unpaid, Study)
- 10 Zambian public holidays for 2026 created per company

---

## Step 3: Build Frontend Assets

Compile Vue components:

```bash
# Development build
npm run dev

# Production build
npm run build
```

---

## Step 4: Clear Caches

Clear all Laravel caches:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Step 5: Verify Installation

### Check Database Tables

```bash
php artisan tinker
```

```php
// Check if tables exist
Schema::hasTable('cms_departments'); // should return true
Schema::hasTable('cms_leave_types'); // should return true

// Check leave types
\App\Infrastructure\Persistence\Eloquent\CMS\LeaveTypeModel::count(); // should return 7 per company

// Check public holidays
DB::table('cms_public_holidays')->count(); // should return 10 per company
```

### Check Routes

```bash
php artisan route:list --name=cms.leave
php artisan route:list --name=cms.departments
```

**Expected Routes:**
- cms.leave.index
- cms.leave.create
- cms.leave.store
- cms.leave.show
- cms.leave.approve
- cms.leave.reject
- cms.leave.balance
- cms.departments.index
- cms.departments.create
- cms.departments.store
- cms.departments.edit
- cms.departments.update
- cms.departments.destroy

---

## Step 6: Test Basic Functionality

### Test 1: Access Leave Management

1. Log into CMS
2. Navigate to `/cms/leave`
3. Should see Leave Management page with empty list
4. Click "New Leave Request"
5. Should see leave request form

### Test 2: Create Department

1. Navigate to `/cms/departments`
2. Click "New Department"
3. Fill in department details
4. Submit form
5. Should see department in list

### Test 3: Create Leave Request

1. Ensure you have at least one active worker
2. Navigate to `/cms/leave/create`
3. Select employee and leave type
4. Select date range
5. Submit request
6. Should see leave request in pending status

### Test 4: Approve Leave Request

1. Navigate to leave request detail page
2. Click "Approve Leave"
3. Add optional notes
4. Submit approval
5. Leave status should change to "approved"
6. Leave balance should be updated

---

## Step 7: Initialize Leave Balances for Existing Workers

If you have existing workers, initialize their leave balances:

```bash
php artisan tinker
```

```php
$leaveService = app(\App\Domain\CMS\Core\Services\LeaveManagementService::class);
$year = date('Y');

// Initialize for all active workers
$workers = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::where('employment_status', 'active')->get();

foreach ($workers as $worker) {
    $leaveService->initializeLeaveBalances($worker->id, $year);
}

echo "Leave balances initialized for " . $workers->count() . " workers\n";
```

---

## Troubleshooting

### Issue: Migrations fail with foreign key constraint error

**Solution:** Ensure migrations run in correct order. The departments table must exist before enhancing workers table.

```bash
php artisan migrate:rollback --step=3
php artisan migrate
```

### Issue: Leave types not showing in dropdown

**Solution:** Run the seeder again:

```bash
php artisan db:seed --class=DefaultLeaveTypesSeeder --force
```

### Issue: Routes not found (404 errors)

**Solution:** Clear route cache:

```bash
php artisan route:clear
php artisan route:cache
```

### Issue: Vue pages not loading

**Solution:** Rebuild frontend assets:

```bash
npm run build
php artisan view:clear
```

### Issue: Leave balance not updating after approval

**Solution:** Check LeaveManagementService is properly injected in LeaveController. Verify database transactions are working.

---

## Rollback Instructions

If you need to rollback the HRMS implementation:

```bash
# Rollback last 3 migrations
php artisan migrate:rollback --step=3

# This will drop:
# - cms_public_holidays
# - cms_leave_requests
# - cms_leave_balances
# - cms_leave_types
# - cms_departments
# - cms_branches
# And remove new columns from cms_workers
```

**Warning:** This will delete all HRMS data including departments, leave requests, and leave balances.

---

## Post-Deployment Checklist

- [ ] All migrations ran successfully
- [ ] Seeders executed without errors
- [ ] Frontend assets compiled
- [ ] Routes accessible
- [ ] Can create department
- [ ] Can create leave request
- [ ] Can approve/reject leave request
- [ ] Leave balances calculate correctly
- [ ] Existing payroll functionality still works
- [ ] No console errors in browser
- [ ] No PHP errors in logs

---

## Performance Optimization

### Add Indexes (if needed)

```sql
-- Add indexes for frequently queried fields
CREATE INDEX idx_workers_employment_status ON cms_workers(employment_status);
CREATE INDEX idx_leave_requests_dates ON cms_leave_requests(start_date, end_date);
```

### Cache Leave Types

Consider caching leave types since they don't change frequently:

```php
// In LeaveController
$leaveTypes = Cache::remember("leave_types_{$companyId}", 3600, function() use ($companyId) {
    return LeaveTypeModel::where('company_id', $companyId)
        ->where('is_active', true)
        ->get();
});
```

---

## Next Steps

After successful deployment:

1. **User Training:** Train HR staff on new features
2. **Data Migration:** Import existing employee data if needed
3. **Customization:** Adjust leave types for company policies
4. **Phase 2:** Plan implementation of remaining HRMS features
5. **Monitoring:** Monitor system performance and user feedback

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review documentation: `docs/cms/HRMS_COMPLETE_IMPLEMENTATION.md`
- Check implementation status: `docs/cms/HRMS_IMPLEMENTATION_STATUS.md`

---

## Related Documentation

- [HRMS Complete Implementation](./HRMS_COMPLETE_IMPLEMENTATION.md)
- [HRMS Implementation Status](./HRMS_IMPLEMENTATION_STATUS.md)
- [Payroll System](./PAYROLL_SYSTEM.md)
- [Time Tracking](./TIME_TRACKING.md)
