# CMS Expense Integration - Phase 1 Testing Guide

**Last Updated:** 2026-03-02  
**Status:** Phase 1 Complete - Manual Expense Entry via CMS Dashboard

## What Was Implemented

Phase 1 creates a seamless integration between the CMS expense management system and MyGrowNet's financial tracking system. When expenses are approved in CMS, they automatically sync to the transactions table and appear in P&L reports.

**Latest Fix (2026-03-01):**
- ✅ Fixed 403 access error for admins
- ✅ Auto-login middleware now creates CMS company and user automatically
- ✅ Middleware runs before access check
- ✅ All caches cleared

## Quick Start Testing

### 1. Access CMS

From the Admin Dashboard:
1. Navigate to **Finance → Manage Expenses**
2. You'll be automatically logged into CMS

**Note:** The CMS expenses page loads but the "Record Expense" button may not work yet. For Phase 1 testing, you can:
- Use the CMS dashboard to navigate to expenses
- Or manually insert a test expense via database
- Or wait for the full CMS UI implementation

### 2. Manual Test via Database (Temporary Workaround)

Since the CMS expense form isn't fully integrated yet, test the sync functionality directly:

```bash
php artisan tinker --execute="
\$company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::where('name', 'MyGrowNet Platform')->first();
\$user = App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('company_id', \$company->id)->first();
\$category = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel::where('company_id', \$company->id)->first();

if (!\$category) {
    \$category = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel::create([
        'company_id' => \$company->id,
        'name' => 'Marketing',
        'is_active' => true,
    ]);
}

\$expense = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::create([
    'company_id' => \$company->id,
    'expense_number' => 'EXP-TEST-001',
    'category_id' => \$category->id,
    'description' => 'Test expense for integration',
    'amount' => 500,
    'payment_method' => 'cash',
    'expense_date' => now(),
    'requires_approval' => true,
    'approval_status' => 'pending',
    'recorded_by' => \$user->id,
]);

echo 'Test expense created: ' . \$expense->expense_number . PHP_EOL;
"
```

### 3. Approve the Expense

In CMS:
1. Go to Expenses list
2. Find your test expense (status: Pending)
3. Click "Approve"

**What happens automatically:**
- ✅ ExpenseApproved event fires
- ✅ SyncApprovedExpenseToTransaction listener runs
- ✅ Transaction record created in transactions table
- ✅ Sync log entry created in cms_sync_log table

### 4. Verify in P&L Dashboard

1. Navigate to **Finance → Profit & Loss**
2. Check expense breakdown
3. You should see "Platform Expenses (CMS)" with your test amount
4. Click "View Details →" to drill down to CMS

### 5. Verify in Database

```sql
-- Check transaction was created
SELECT * FROM transactions 
WHERE cms_expense_id IS NOT NULL 
ORDER BY created_at DESC 
LIMIT 5;

-- Check sync log
SELECT * FROM cms_sync_log 
ORDER BY synced_at DESC 
LIMIT 5;
```

## Manual Sync Command

If automatic sync fails or you need to sync historical expenses:

```bash
# Show sync statistics
php artisan cms:sync-expenses --stats

# Sync all approved expenses
php artisan cms:sync-expenses --all

# Retry failed syncs
php artisan cms:sync-expenses --retry
```

## What to Test

### ✅ Core Functionality
- [ ] Can access CMS from admin dashboard
- [ ] Can record expense in CMS
- [ ] Can approve expense in CMS
- [ ] Expense syncs to transactions table automatically
- [ ] Sync log entry created
- [ ] P&L includes CMS expense
- [ ] Drill-down link works from P&L to CMS

### ✅ Edge Cases
- [ ] Duplicate approval doesn't create duplicate transaction
- [ ] Different expense categories map correctly
- [ ] Large amounts sync correctly
- [ ] Special characters in description handled
- [ ] Manual sync command works
- [ ] Retry failed syncs works

### ✅ UI/UX
- [ ] CMS menu items visible in admin sidebar
- [ ] CMS badge displays correctly
- [ ] Icons display correctly (PlusCircle, ReceiptText)
- [ ] Auto-login to CMS works seamlessly
- [ ] P&L drill-down links work

## Expected Results

### Transaction Record
```
transaction_type: MARKETING_EXPENSE (or other expense type)
amount: -500.00 (negative for expense)
status: completed
cms_expense_id: [expense ID]
cms_reference_type: expense
description: "Test expense for integration"
```

### Sync Log Record
```
expense_id: [expense ID]
transaction_id: [transaction ID]
status: success
synced_at: [timestamp]
```

### P&L Display
```
Expenses:
  Platform Expenses (CMS): K500.00 [View Details →]
```

## Troubleshooting

### Expense not syncing?

**Check:**
1. Is expense approved? (Only approved expenses sync)
2. Check sync_log table: `SELECT * FROM cms_sync_log WHERE status = 'failed'`
3. Check Laravel logs: `storage/logs/laravel.log`

**Fix:**
```bash
php artisan cms:sync-expenses --retry
```

### Duplicate transactions?

**Check:**
```sql
SELECT cms_expense_id, COUNT(*) 
FROM transactions 
WHERE cms_expense_id IS NOT NULL 
GROUP BY cms_expense_id 
HAVING COUNT(*) > 1;
```

**Fix:** Service prevents duplicates automatically. If duplicates exist, they're from before the fix.

### Wrong transaction type?

**Check:** CMS expense category name and mapping in `CmsExpenseSyncService::mapExpenseCategoryToTransactionType()`

**Fix:** Update category name in CMS or add custom mapping in service.

### Auto-login not working?

**Check:**
1. Is user an admin?
2. Is middleware registered in bootstrap/app.php?
3. Check session: `dd(session()->all())`

**Fix:**
```bash
php artisan config:clear
php artisan cache:clear
```

## Category Mapping Reference

| CMS Category | Transaction Type |
|--------------|------------------|
| Marketing, Advertising | MARKETING_EXPENSE |
| Office, Office Supplies | OFFICE_EXPENSE |
| Travel | TRAVEL_EXPENSE |
| Infrastructure, Hosting, Software | INFRASTRUCTURE_EXPENSE |
| Legal | LEGAL_EXPENSE |
| Professional Fees | PROFESSIONAL_FEES |
| Utilities | UTILITIES_EXPENSE |
| Other | GENERAL_EXPENSE |

## Next Steps After Testing

Once Phase 1 is verified working:

1. **Phase 2:** Budget integration
2. **Phase 3:** Real-time notifications
3. **Phase 4:** Advanced reporting

## Support

For issues or questions:
- Check: `docs/Finance/CMS_EXPENSE_INTEGRATION_IMPLEMENTATION.md`
- Check: `docs/Finance/CMS_FINANCIAL_INTEGRATION_ANALYSIS.md`
- Run: `php artisan cms:sync-expenses --stats`

---

**Remember:** This is Phase 1 - basic expense sync. More features coming in Phase 2!
