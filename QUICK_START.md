# Quick Start Guide - Referral System Setup

## One-Command Setup

Run this single command to set up everything:

```bash
php artisan setup:referral-system
```

This will:
1. ✅ Run all migrations
2. ✅ Seed subscription packages
3. ✅ Clear all caches
4. ✅ Run system tests
5. ✅ Display status summary

---

## Manual Setup (Alternative)

If you prefer to run steps individually:

### Step 1: Run Migrations
```bash
php artisan migrate
```

### Step 2: Seed Packages
```bash
php artisan db:seed --class=PackageSeeder
```

### Step 3: Clear Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Step 4: Test System
```bash
php artisan test:referral-system
```

---

## What Was Fixed

### ✅ 7-Level Commission System
- **Before**: Only 2 levels (5%, 2%)
- **After**: All 7 levels (15%, 10%, 8%, 6%, 4%, 3%, 2%)

### ✅ Subscription System
- Created Package model (subscription packages)
- Created Subscription model (user subscriptions)
- 9 default packages seeded

### ✅ Qualification Checks
- Only active users receive commissions
- Must meet monthly MAP requirement
- Automatic qualification verification

### ✅ Database Fixes
- Added `status` column to users table
- Added `last_login_at` column to users table
- Fixed admin user management page

### ✅ UI Improvements
- Matrix labels now show depth (Direct Referrals, 2nd Generation, etc.)
- Clear distinction between professional levels and matrix depth

---

## Verify Everything Works

### 1. Check Admin Users Page
```
http://127.0.0.1:8001/admin/users
```
Should load without errors

### 2. Check Matrix Management
```
http://127.0.0.1:8001/admin/matrix
```
Labels should show "Direct Referrals", "2nd Generation", etc.

### 3. Check Packages
```bash
php artisan tinker
```
```php
App\Models\Package::count(); // Should return 9
App\Models\Package::all()->pluck('name', 'price');
```

### 4. Test Commission Rates
```bash
php artisan tinker
```
```php
App\Models\ReferralCommission::getAllCommissionRates();
// Should show all 7 levels with correct rates
```

---

## Create Test Subscription

```bash
php artisan tinker
```

```php
use App\Models\{User, Package, Subscription};

// Get a user with referrer
$user = User::whereNotNull('referrer_id')->first();

// Get a package
$package = Package::where('slug', 'professional')->first();

// Create subscription
$subscription = Subscription::create([
    'user_id' => $user->id,
    'package_id' => $package->id,
    'amount' => $package->price,
    'status' => 'active',
    'start_date' => now(),
    'end_date' => now()->addMonths($package->duration_months)
]);

// Check commissions created
$commissions = App\Models\ReferralCommission::where('subscription_id', $subscription->id)->get();

foreach ($commissions as $commission) {
    echo "Level {$commission->level}: K{$commission->amount} ({$commission->percentage}%)\n";
}
```

---

## Troubleshooting

### Issue: "Column 'status' not found"
**Solution**: Run migrations
```bash
php artisan migrate
```

### Issue: "No packages found"
**Solution**: Seed packages
```bash
php artisan db:seed --class=PackageSeeder
```

### Issue: "Commissions not created"
**Solution**: Check user qualification
```bash
php artisan tinker
```
```php
$user = User::find(1);
$user->meetsMonthlyQualification(); // Should return true
```

### Issue: "Wrong commission rates"
**Solution**: Verify rates in model
```bash
php artisan tinker
```
```php
App\Models\ReferralCommission::COMMISSION_RATES;
// Should show: [1 => 15.0, 2 => 10.0, 3 => 8.0, ...]
```

---

## Documentation

📚 **Complete Documentation Available**:

1. **SESSION_SUMMARY.md** - Complete session overview
2. **REFERRAL_SYSTEM_FIXES_COMPLETE.md** - Detailed implementation
3. **IMMEDIATE_ACTIONS_SUMMARY.md** - Quick reference
4. **REFERRAL_SYSTEM_ALIGNMENT_ANALYSIS.md** - Problem analysis
5. **USER_STATUS_COLUMN_FIX.md** - Database fix guide
6. **MATRIX_DOWNLINE_LABELS_FIX.md** - UI fix guide

---

## Commission Structure

| Level | Professional Level | Rate | Example (K250 package) |
|-------|-------------------|------|------------------------|
| 1 | Associate | 15% | K37.50 |
| 2 | Professional | 10% | K25.00 |
| 3 | Senior | 8% | K20.00 |
| 4 | Manager | 6% | K15.00 |
| 5 | Director | 4% | K10.00 |
| 6 | Executive | 3% | K7.50 |
| 7 | Ambassador | 2% | K5.00 |

**Total**: 48% distributed across 7 levels

---

## Success Checklist

- [ ] Run setup command: `php artisan setup:referral-system`
- [ ] Verify admin users page loads
- [ ] Check matrix labels are correct
- [ ] Verify 9 packages exist
- [ ] Test commission rates
- [ ] Create test subscription
- [ ] Verify commissions created
- [ ] Check logs for errors

---

## Support

If you encounter any issues:

1. Check the logs: `tail -f storage/logs/laravel.log`
2. Review documentation files
3. Run diagnostic: `php artisan test:referral-system`
4. Verify database: Check tables exist and have data

---

## Summary

✅ **Setup Command**: `php artisan setup:referral-system`  
✅ **Documentation**: 6 comprehensive guides  
✅ **Testing**: Built-in test commands  
✅ **Status**: Ready for production

---

**Last Updated**: October 18, 2025  
**Version**: 1.0  
**Status**: Production Ready
