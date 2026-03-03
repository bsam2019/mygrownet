# CMS Expense Integration - Quick Reference

**Last Updated:** 2026-03-02  
**Phase:** 1 - Backend Integration Complete

## What's Working ✅

### Backend Integration (100% Complete)
- ✅ Automatic expense sync when approved
- ✅ Transaction records created automatically
- ✅ P&L dashboard includes CMS expenses
- ✅ Manual sync command available
- ✅ Auto-login for admins
- ✅ Duplicate prevention
- ✅ Error handling and logging

### Database Schema
- ✅ `transactions` table has CMS reference fields
- ✅ `cms_sync_log` table tracks sync status
- ✅ 8 expense transaction types added

### Commands Available
```bash
# Show sync statistics
php artisan cms:sync-expenses --stats

# Sync all approved expenses
php artisan cms:sync-expenses --all

# Retry failed syncs
php artisan cms:sync-expenses --retry
```

## What Needs Work ⚠️

### UI Components (Not Implemented)
- ⚠️ CMS expense creation form (Vue component missing)
- ⚠️ "Record Expense" button on CMS page

### Workaround for Testing

**Create Test Expense:**
```bash
php artisan tinker --execute="
\$company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::where('name', 'MyGrowNet Platform')->first();
\$user = App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::where('company_id', \$company->id)->first();
\$category = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel::firstOrCreate([
    'company_id' => \$company->id,
    'name' => 'Marketing',
    'is_active' => true,
]);

\$expense = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::create([
    'company_id' => \$company->id,
    'expense_number' => 'EXP-TEST-' . time(),
    'category_id' => \$category->id,
    'description' => 'Test expense for integration',
    'amount' => 500,
    'payment_method' => 'cash',
    'expense_date' => now(),
    'requires_approval' => true,
    'approval_status' => 'pending',
    'recorded_by' => \$user->id,
]);

echo 'Created: ' . \$expense->expense_number . PHP_EOL;
"
```

**Approve Expense (triggers sync):**
```bash
php artisan tinker --execute="
\$expense = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::latest()->first();
\$user = App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel::first();

\$expense->update([
    'approval_status' => 'approved',
    'approved_by' => \$user->id,
    'approved_at' => now(),
]);

echo 'Approved: ' . \$expense->expense_number . ' - Sync triggered!' . PHP_EOL;
"
```

**Verify Sync:**
```bash
php artisan tinker --execute="
\$expense = App\Infrastructure\Persistence\Eloquent\CMS\ExpenseModel::latest()->first();
\$transaction = DB::table('transactions')->where('cms_expense_id', \$expense->id)->first();

if (\$transaction) {
    echo 'SUCCESS! Transaction synced:' . PHP_EOL;
    echo '- Amount: ' . \$transaction->amount . PHP_EOL;
    echo '- Type: ' . \$transaction->transaction_type . PHP_EOL;
    echo '- Status: ' . \$transaction->status . PHP_EOL;
} else {
    echo 'Not synced yet - check logs' . PHP_EOL;
}
"
```

## How It Works

### Automatic Sync Flow
1. Admin approves expense in CMS
2. `ExpenseApproved` event fires
3. `SyncApprovedExpenseToTransaction` listener runs
4. `CmsExpenseSyncService` creates transaction record
5. Sync log entry created
6. Transaction appears in P&L

### Category Mapping
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

## Accessing from Admin Dashboard

**Menu Location:** Finance → Manage Expenses

**What Happens:**
1. Auto-login middleware runs
2. Creates/finds MyGrowNet Platform company
3. Creates/finds your CMS user with Owner role
4. Redirects to CMS expenses page

## Troubleshooting

### Expense not syncing?
```bash
# Check sync log
php artisan tinker --execute="
\$logs = DB::table('cms_sync_log')->latest()->take(5)->get();
foreach (\$logs as \$log) {
    echo \$log->expense_id . ': ' . \$log->status . PHP_EOL;
}
"

# Retry failed syncs
php artisan cms:sync-expenses --retry
```

### Check P&L includes expenses
Navigate to: **Finance → Profit & Loss**
Look for: "Platform Expenses (CMS)"

## Files Modified

**Backend (11 files):**
- `app/Events/CMS/ExpenseApproved.php` (new)
- `app/Listeners/SyncApprovedExpenseToTransaction.php` (new)
- `app/Http/Middleware/AutoLoginToCMS.php` (new)
- `app/Console/Commands/SyncCmsExpenses.php` (new)
- `app/Services/CmsExpenseSyncService.php` (new)
- `app/Models/CmsSyncLog.php` (new)
- `app/Domain/CMS/Core/Services/ExpenseService.php` (updated)
- `app/Http/Controllers/CMS/ExpenseController.php` (updated)
- `app/Services/ProfitLossTrackingService.php` (updated)
- `app/Providers/EventServiceProvider.php` (updated)
- `routes/cms.php` (updated)

**Frontend (2 files):**
- `resources/js/components/AdminSidebar.vue` (updated)
- `resources/js/Pages/Admin/Financial/ProfitLoss.vue` (updated)

**Config (2 files):**
- `bootstrap/app.php` (updated)
- Database migrations (2 new)

## Next Steps (Future Phases)

**Phase 2: Budget Integration**
- Sync CMS budgets to financial system
- Budget vs actual tracking
- Budget alerts

**Phase 3: Full UI Implementation**
- Create Vue components for expense forms
- Inline expense creation in admin
- Approval workflow UI

**Phase 4: Advanced Features**
- Real-time notifications
- Expense analytics
- Automated categorization
- Receipt OCR

## Support

**Documentation:**
- `docs/Finance/CMS_EXPENSE_INTEGRATION_IMPLEMENTATION.md` - Full implementation details
- `docs/Finance/PHASE_1_TESTING_GUIDE.md` - Testing instructions
- `docs/Finance/ADMIN_EXPENSE_RECORDING_GUIDE.md` - User guide

**Commands:**
- `php artisan cms:sync-expenses --help` - Command help
- Check logs: `storage/logs/laravel.log`

---

**Phase 1 Status:** Backend integration complete and production-ready ✅
