# VBIF System Cleanup Plan

**Date:** October 30, 2025  
**Purpose:** Remove old VBIF (Village Banking Investment Fund) resources now replaced by Venture Builder

---

## Overview

The old VBIF system has been replaced by the new Venture Builder feature. This document outlines all VBIF-related resources that should be removed or archived.

---

## Files to Remove

### Controllers

**Admin Controllers:**
- `app/Http/Controllers/Admin/InvestmentTierController.php` ❌
- `app/Http/Controllers/Admin/AdminInvestmentController.php` ❌ (if exists)

**Member Controllers:**
- `app/Http/Controllers/Investment/InvestmentTierController.php` ❌
- `app/Http/Controllers/InvestmentController.php` ❌ (check if still needed for other features)

### Models

- `app/Models/InvestmentTier.php` ❌
- `app/Models/Investment.php` ❌ (check dependencies first)
- `app/Models/UserInvestment.php` ❌ (if exists)

### Vue Pages

**Admin Pages:**
- `resources/js/pages/Admin/InvestmentTiers/` (entire directory) ❌
- `resources/js/pages/Admin/Investments/` (entire directory) ❌

**Member Pages:**
- `resources/js/pages/Investment/` (entire directory) ❌
- `resources/js/pages/Investments/` (entire directory) ❌
- `resources/js/pages/Investors/` (entire directory) ❌
- `resources/js/pages/Portfolio/Index.vue` ❌ (if VBIF-specific)
- `resources/js/pages/Tiers/` (entire directory) ❌
- `resources/js/pages/Investment.vue` ❌

**Components:**
- `resources/js/components/custom/InvestmentTiers.vue` ❌

### Database

**Migrations:**
- Search for migrations containing "investment_tier"
- Search for migrations containing "user_investment"
- Keep these for historical data, but mark as deprecated

**Seeders:**
- `database/seeders/InvestmentTierSeeder.php` ❌

**Factories:**
- `database/factories/InvestmentTierFactory.php` ❌

### Tests

**Unit Tests:**
- `tests/Unit/Models/InvestmentTierTest.php` ❌
- `tests/Unit/Models/InvestmentTierOptimizedTest.php` ❌
- `tests/Unit/Models/InvestmentTierMinimalTest.php` ❌
- `tests/Unit/InvestmentTierTest.php` ❌
- `tests/Unit/Domain/Investment/Services/InvestmentTierServiceTest.php` ❌
- `tests/Unit/Models/InvestmentTest.php` ❌
- `tests/Unit/Infrastructure/Persistence/Repositories/EloquentInvestmentRepositoryTest.php` ❌
- `tests/Unit/Services/InvestmentMetricsServiceTest.php` ❌

**Vue Tests:**
- `tests/Vue/Investment/InvestmentPerformanceChart.test.ts` ❌
- `tests/Vue/Investment/InvestmentOverview.test.ts` ❌

### Routes

**In `routes/admin.php`:**
```php
// Remove these lines:
use App\Http\Controllers\Admin\InvestmentTierController;

// Investment tiers management
Route::get('/investment-tiers', [InvestmentTierController::class, 'index'])->name('investment-tiers.index');
Route::get('/investment-tiers/create', [InvestmentTierController::class, 'create'])->name('investment-tiers.create');
Route::post('/investment-tiers', [InvestmentTierController::class, 'store'])->name('investment-tiers.store');
Route::get('/investment-tiers/{tier}/edit', [InvestmentTierController::class, 'edit'])->name('investment-tiers.edit');
Route::put('/investment-tiers/{tier}', [InvestmentTierController::class, 'update'])->name('investment-tiers.update');
Route::delete('/investment-tiers/{tier}', [InvestmentTierController::class, 'destroy'])->name('investment-tiers.destroy');
Route::patch('/investment-tiers/{tier}/toggle-status', [InvestmentTierController::class, 'toggleStatus'])->name('investment-tiers.toggle-status');
Route::patch('/investment-tiers/{tier}/toggle-archive', [InvestmentTierController::class, 'toggleArchive'])->name('investment-tiers.toggle-archive');
```

**In `routes/web.php`:**
```php
// Remove these lines:
use App\Http\Controllers\Investment\InvestmentTierController;
use App\Http\Controllers\Admin\AdminInvestmentController;

// VBIF Dashboard API Endpoints
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/real-time-earnings', [DashboardController::class, 'realTimeEarnings'])->name('real-time-earnings');
    // ... other VBIF-specific routes
});

// VBIF Investment enhancements
Route::get('investments/history/all', [InvestmentController::class, 'history'])->name('investments.history');
Route::get('investments/performance/metrics', [InvestmentController::class, 'performance'])->name('investments.performance');
Route::post('investments/tier-upgrade', [InvestmentController::class, 'requestTierUpgrade'])->name('investments.tier-upgrade');
Route::post('investments/{investment}/withdrawal', [InvestmentController::class, 'requestWithdrawal'])->name('investments.withdrawal');

// VBIF-specific routes
// Matrix Management (if VBIF-specific)
Route::get('/matrix', [App\Http\Controllers\MatrixController::class, 'index'])->name('matrix.index');
```

---

## Files to Keep (Check Dependencies)

These files may have dependencies or be used by other features:

### Controllers
- `app/Http/Controllers/InvestmentController.php` - Check if used by other features
- `app/Http/Controllers/DashboardController.php` - Contains VBIF code but also MyGrowNet code

### Models
- `app/Models/Investment.php` - May be referenced by other features
- Check User model for VBIF-specific methods

### Database Tables
- Keep existing tables for historical data
- Don't drop tables, just stop using them
- Consider adding a migration to mark them as deprecated

---

## Code References to Update

### In Controllers

**DashboardController.php:**
- Remove VBIF-specific methods
- Update to use MyGrowNet/Venture Builder data
- Lines to check: 50-80, 569-590

**ReferralController.php:**
- Update VBIF-specific comments (lines 86, 104, 148)
- Keep functionality if it works for MyGrowNet

### In User Model

Check for VBIF-specific methods:
- `getReferralStats()`
- `calculateTotalEarningsDetailed()`
- `buildMatrixStructure()`
- `checkTierUpgradeEligibility()`
- `getTierProgressPercentage()`
- `getMatrixDownlineCount()`
- `getInvestmentPerformanceMetrics()`
- `canWithdraw()`

**Action:** Either remove or adapt for MyGrowNet

---

## Navigation/Sidebar Updates

### Admin Sidebar
- Remove "Investment Tiers" menu item
- Remove "Investments" menu item (if VBIF-specific)
- Keep "Ventures" menu item

### Member Sidebar
- Remove "My Investments" (VBIF)
- Keep "My Investments" (Venture Builder) - different route
- Remove "Investment Tiers"
- Remove "Portfolio" (if VBIF-specific)

---

## Database Considerations

### Option 1: Keep Tables (Recommended)
- Keep all VBIF tables for historical data
- Add `deprecated_at` column to mark as legacy
- Don't delete any data
- Stop creating new records

### Option 2: Archive Tables
- Create archive database
- Move VBIF tables to archive
- Keep for reference only

### Option 3: Drop Tables (Not Recommended)
- Only if absolutely no historical data needed
- Backup first!

---

## ⚠️ IMPORTANT: Database Considerations

**CRITICAL:** The `investment_tiers` table is heavily integrated into the system:
- Used by user profiles (`current_investment_tier`)
- Referenced by subscriptions
- Referenced by tier upgrades
- Referenced by tier qualifications
- Referenced by physical reward allocations
- Has been transformed for MyGrowNet use

**RECOMMENDATION:** 
- **DO NOT** drop the `investment_tiers` table
- **DO NOT** remove VBIF migrations (needed for database history)
- **KEEP** the table structure as it's now used by MyGrowNet
- **ONLY** remove UI/routes/controllers that reference old VBIF investment system

The `investment_tiers` table has been repurposed for MyGrowNet membership tiers, so it should stay!

---

## Safe Migration Strategy

### Phase 1: Remove UI Only (SAFE) ✅ COMPLETE
**Goal:** Remove user-facing VBIF interfaces without touching database

- ✅ Remove VBIF routes from `routes/admin.php` and `routes/web.php`
- ✅ Remove VBIF controllers (InvestmentTierController, etc.)
- ✅ Remove VBIF Vue pages (Investment/, Investments/, Investors/, etc.)
- ✅ Remove VBIF components
- [ ] Update navigation/sidebars to remove VBIF links (if any remain)

**Risk:** Low - Only removes UI, doesn't touch data

**Completed Actions:**
1. Removed `InvestmentTierController` import from `routes/admin.php`
2. Removed all investment-tiers routes from `routes/admin.php`
3. Removed VBIF Dashboard API endpoints from `routes/web.php`
4. Removed VBIF Investment enhancement routes from `routes/web.php`
5. Removed Investment Opportunities and Portfolio routes
6. Deleted `app/Http/Controllers/Admin/InvestmentTierController.php`
7. Deleted `app/Http/Controllers/Investment/InvestmentTierController.php`
8. Deleted all VBIF Vue pages:
   - `resources/js/pages/Investment/` (all files)
   - `resources/js/pages/Investments/` (all files)
   - `resources/js/pages/Admin/InvestmentTiers/` (all files)
   - `resources/js/pages/Tiers/` (all files)
   - `resources/js/pages/Portfolio/Index.vue`
   - `resources/js/pages/Investment.vue`
9. Deleted `resources/js/components/custom/InvestmentTiers.vue`
10. Updated `resources/js/components/AdminSidebar.vue`:
    - Removed old VBIF investment menu items
    - Replaced "Investments" section with "Venture Builder" section
    - Added Venture Builder menu items (Ventures, Investments, Categories, Analytics)

### Phase 2: Update Code References (MEDIUM RISK)
**Goal:** Clean up VBIF-specific code in shared files

- [ ] Update DashboardController to remove VBIF methods
- [ ] Update User model to remove VBIF-specific methods (or mark deprecated)
- [ ] Update ReferralController comments
- [ ] Search for "VBIF" comments and update

**Risk:** Medium - May break features if not careful

### Phase 3: Archive Tests (SAFE)
**Goal:** Move VBIF tests to archive folder

- [ ] Create `tests/Archive/VBIF/` directory
- [ ] Move all VBIF tests there
- [ ] Update test suites to exclude archived tests

**Risk:** Low - Tests are separate from production code

### Phase 4: Documentation (SAFE)
**Goal:** Update documentation

- [ ] Update README to remove VBIF references
- [ ] Create VBIF_LEGACY.md with historical info
- [ ] Update API documentation
- [ ] Update user guides

**Risk:** None

### Phase 5: Database Cleanup (DO NOT DO)
**Goal:** N/A - Keep all database structures

- ❌ DO NOT drop `investment_tiers` table (used by MyGrowNet)
- ❌ DO NOT remove migrations (needed for history)
- ❌ DO NOT delete user investment data
- ✅ Keep everything as-is for data integrity

**Risk:** N/A - Not doing this phase

---

## Testing Checklist

After cleanup:
- [ ] All pages load without errors
- [ ] No 404 errors in browser console
- [ ] No missing route errors
- [ ] Admin dashboard works
- [ ] Member dashboard works
- [ ] Venture Builder works
- [ ] Navigation menus work
- [ ] No broken links

---

## Rollback Plan

If issues arise:
1. Restore deleted files from git history
2. Re-add removed routes
3. Update navigation back
4. Test thoroughly

---

## Notes

- **Priority:** Medium (not urgent, but should be done for code cleanliness)
- **Risk:** Low (VBIF is not in production use)
- **Estimated Time:** 2-3 hours
- **Dependencies:** Check User model, Dashboard, and shared services

---

## Next Steps

1. Review this plan
2. Confirm which files to remove
3. Check for any production data in VBIF tables
4. Execute cleanup in development environment
5. Test thoroughly
6. Deploy to production

