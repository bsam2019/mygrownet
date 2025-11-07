# Deployment History

## November 6, 2025

### Organizational Structure Seeded
- Seeded 5 departments, 14 positions, 12 KPIs, 3 hiring plans
- Command: `php artisan db:seed --class=OrganizationalStructureSeeder --force`
- Server: 138.197.187.134
- Status: ✅ Complete

### Starter Kit Migration
- Migrated starter kit purchases from withdrawals to transactions table
- Script: `scripts/simple-migrate.php`
- Status: ✅ Complete

### Wallet Balance Fix
- Fixed double-counting of starter kit expenses
- File: `app/Services/WalletService.php`
- Impact: All wallet balances now accurate
- Status: ✅ Deployed

## November 5, 2025

### Starter Kit Tier Bug Fix
- Fixed Premium tier not being saved
- Added `tier` to `$fillable` array in StarterKitPurchaseModel
- Impact: Premium purchases now grant correct benefits
- Commit: 58a873e
- Status: ✅ Deployed

### Loan System Integration
- Updated wallet calculations to include loan transactions
- Files: WalletService, StarterKitService, PurchaseStarterKitUseCase
- Impact: Members can use loan funds for purchases
- Status: ✅ Deployed

### Organizational Structure System
- Complete implementation (backend + frontend)
- 5 pages, 3 components, 11 routes
- Sidebar integration
- Commit: c85b0bb
- Status: ✅ Deployed

### Sidebar Navigation Fixes
- Fixed route naming (added `admin.` prefix to 20 routes)
- Added auto-expand for Employees submenu
- Files: routes/admin.php, CustomAdminSidebar.vue
- Status: ✅ Deployed

### Idempotency Protection
- Implemented for LGR transfers
- Created IdempotencyService
- Frontend throttling and cooldown
- Status: ✅ Deployed

## Deployment Checklist

For each deployment:
- [ ] Commit changes to git
- [ ] Push to GitHub (main branch)
- [ ] Pull changes on droplet
- [ ] Run migrations (if needed)
- [ ] Clear caches (config, cache, view, route)
- [ ] Optimize application
- [ ] Test critical paths
- [ ] Monitor logs for errors

## Server Details

**Production Server:** 138.197.187.134  
**User:** sammy  
**Path:** /var/www/mygrownet.com  
**Domain:** https://mygrownet.com

## Cache Clear Commands

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
```
