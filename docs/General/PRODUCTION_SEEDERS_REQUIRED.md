# Production Seeders Required

**Last Updated:** March 3, 2026
**Status:** Production Deployment

## Overview

This document outlines ALL seeders required in production for the MyGrowNet platform, including core system seeders and recent financial system updates (Transaction-based reporting, P&L tracking, CMS integration, Budget management).

## Core Production Seeders (Already Configured)

The following seeders are already configured in `ProductionSeeder.php` and run automatically:

### 1. RolesAndPermissionsSeeder ✅
**Purpose:** Creates user roles and permissions
**Roles Created:**
- Administrator (full access)
- Manager (management access)
- Support (support access)
- Member (standard member access)

### 2. UserSeeder ✅
**Purpose:** Creates admin and staff users
**Users Created:**
- admin@mygrownet.com (Administrator)
- manager@mygrownet.com (Manager)

### 3. PackageSeeder ✅
**Purpose:** Creates 7-level subscription packages
**Packages Created:**
- Associate (Level 1)
- Professional (Level 2)
- Senior (Level 3)
- Manager (Level 4)
- Director (Level 5)
- Executive (Level 6)
- Ambassador (Level 7)

### 4. CategorySeeder ✅
**Purpose:** Creates project and investment categories

### 5. MyGrowNetAchievementsSeeder ✅
**Purpose:** Creates milestone achievements and badges

## Additional Required Seeders (Financial System)

These seeders are required for the financial system features but are NOT yet in ProductionSeeder.php:

### 1. MyGrowNetPlatformCompanySeeder ✅ (REQUIRED - Not in ProductionSeeder)
**File:** `database/seeders/MyGrowNetPlatformCompanySeeder.php`
**Purpose:** Creates the MyGrowNet Platform company in CMS for internal expense tracking
**Status:** Newly created, needs to be added to ProductionSeeder
**Run Command:**
```bash
php artisan db:seed --class=MyGrowNetPlatformCompanySeeder
```

**Creates:**
- Company: MyGrowNet Platform
- Industry: Technology
- Email: admin@mygrownet.com
- Status: Active

### 2. ModuleSeeder ✅ (REQUIRED - Not in ProductionSeeder)
**File:** `database/seeders/ModuleSeeder.php`
**Purpose:** Seeds the 14 financial modules used in transaction categorization
**Status:** Already exists and complete
**Run Command:**
```bash
php artisan db:seed --class=ModuleSeeder
```

### 2. ModuleSeeder ✅ (REQUIRED - Not in ProductionSeeder)
**File:** `database/seeders/ModuleSeeder.php`
**Purpose:** Seeds the 14 financial modules used in transaction categorization
**Status:** Already exists and complete
**Run Command:**
```bash
php artisan db:seed --class=ModuleSeeder
```

**Modules Created:**
- grownet (GrowNet)
- growbiz (GrowBiz)
- growmarket (GrowMarket)
- growbuilder (GrowBuilder)
- mygrow-save (MyGrow Save)
- inventory (Inventory Management)
- pos (Point of Sale)
- growfinance (GrowFinance)
- bizboost (BizBoost)
- lifeplus (LifePlus)
- venture-builder (Venture Builder)
- hr-management (HR Management)
- ecommerce (E-Commerce)
- learning-hub (Learning Hub)

### 3. MyGrowNetExpenseCategoriesSeeder ✅ (REQUIRED - Not in ProductionSeeder)
**File:** `database/seeders/MyGrowNetExpenseCategoriesSeeder.php`
**Purpose:** Seeds expense categories for MyGrowNet Platform internal tracking
**Status:** Newly created, needs to be added to ProductionSeeder
**Run Command:**
```bash
php artisan db:seed --class=MyGrowNetExpenseCategoriesSeeder
```

### 3. MyGrowNetExpenseCategoriesSeeder ✅ (REQUIRED - Not in ProductionSeeder)
**File:** `database/seeders/MyGrowNetExpenseCategoriesSeeder.php`
**Purpose:** Seeds expense categories for MyGrowNet Platform internal tracking
**Status:** Newly created, needs to be added to ProductionSeeder
**Dependencies:** Requires MyGrowNetPlatformCompanySeeder to run first
**Run Command:**
```bash
php artisan db:seed --class=MyGrowNetExpenseCategoriesSeeder
```

**Categories Created:**
1. Marketing & Advertising
2. Office Expenses
3. Travel & Transport
4. Infrastructure & Technology
5. Legal & Compliance
6. Professional Services
7. Utilities
8. General Expenses
9. Staff Welfare
10. Equipment & Maintenance

### 4. SampleBudgetSeeder ⚠️ (OPTIONAL - For Testing Only)
**File:** `database/seeders/SampleBudgetSeeder.php`
**Purpose:** Seeds sample budget for current month for MyGrowNet Platform
**Status:** Already exists and complete
**Run Command:**
```bash
php artisan db:seed --class=SampleBudgetSeeder
```

**Creates:**
- Monthly budget for current month
- 8 expense categories with budgeted amounts (K40,000 total)
- 4 revenue categories with budgeted amounts (K75,000 total)
- Total budget: K50,000

## Recommended Production Seeder Update

Update `database/seeders/ProductionSeeder.php` to include financial system seeders:

```php
public function run(): void
{
    $this->command->info('🌱 Starting MyGrowNet production seeding...');
    $this->command->newLine();

    $this->call([
        // Core System Data (Required)
        RolesAndPermissionsSeeder::class,   // User roles and permissions (must be first)
        UserSeeder::class,                  // Admin and staff users
        
        // Membership Packages (7 Levels: Associate to Ambassador)
        PackageSeeder::class,               // Subscription packages with learning materials
        
        // Community Projects & Categories
        CategorySeeder::class,              // Project and investment categories
        
        // Achievement & Recognition System
        MyGrowNetAchievementsSeeder::class, // Milestone achievements and badges
        
        // Financial System (NEW)
        MyGrowNetPlatformCompanySeeder::class, // MyGrowNet Platform company for expense tracking
        ModuleSeeder::class,                // Financial modules for transaction categorization
        MyGrowNetExpenseCategoriesSeeder::class, // Expense categories for internal tracking
        
        // Optional: Educational Content (uncomment if ready)
        // EducationalContentSeeder::class,  // Learning packs and courses
        // SampleBudgetSeeder::class,        // Sample budget for testing (TESTING ONLY)
    ]);

    $this->command->newLine();
    $this->command->info('✅ Production seeding completed successfully!');
}
```

## Production Deployment Steps

### Step 1: Run Production Seeder (Recommended)
The easiest way is to update ProductionSeeder.php and run it:

```bash
php artisan db:seed --class=ProductionSeeder --force
```

### Step 2: Or Run Individual Seeders
If you prefer to run seeders individually:

```bash
# Step 1: Create MyGrowNet Platform company
php artisan db:seed --class=MyGrowNetPlatformCompanySeeder --force

# Step 2: Seed financial modules
php artisan db:seed --class=ModuleSeeder --force

# Step 3: Seed expense categories
php artisan db:seed --class=MyGrowNetExpenseCategoriesSeeder --force

# Optional: Seed sample budget (testing only)
php artisan db:seed --class=SampleBudgetSeeder --force
```

### Step 3: Verify Data

After running seeders, verify the data:

```bash
# Check modules
php artisan tinker
>>> App\Models\Module::count();
# Should return 14

# Check MyGrowNet Platform company
>>> $company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::where('name', 'MyGrowNet Platform')->first();
>>> $company->id;
# Should return company ID

# Check expense categories
>>> App\Infrastructure\Persistence\Eloquent\CMS\ExpenseCategoryModel::where('company_id', $company->id)->count();
# Should return 10

# Check budget (if SampleBudgetSeeder was run)
>>> App\Infrastructure\Persistence\Eloquent\CMS\BudgetModel::where('company_id', $company->id)->count();
# Should return 1
```

## Production Checklist

- [ ] Update ProductionSeeder.php to include financial system seeders
- [ ] Run: `php artisan db:seed --class=ProductionSeeder --force`
- [ ] Verify all data using verification commands above
- [ ] Test expense creation in CMS
- [ ] Test budget management dashboard
- [ ] Test P&L report generation
- [ ] Test PDF exports (Presentation, P&L, Budget)
- [ ] Test admin sidebar active link highlighting

## Complete Seeder List for Production

### Core System (Already in ProductionSeeder)
1. ✅ RolesAndPermissionsSeeder - User roles and permissions
2. ✅ UserSeeder - Admin and staff users
3. ✅ PackageSeeder - 7-level subscription packages
4. ✅ CategorySeeder - Project categories
5. ✅ MyGrowNetAchievementsSeeder - Achievement system

### Financial System (Need to Add to ProductionSeeder)
6. ⚠️ MyGrowNetPlatformCompanySeeder - MyGrowNet Platform company
7. ⚠️ ModuleSeeder - 14 financial modules
8. ⚠️ MyGrowNetExpenseCategoriesSeeder - 10 expense categories

### Optional (Testing/Development Only)
9. ❌ SampleBudgetSeeder - Sample budget data (DO NOT RUN IN PRODUCTION)
10. ❌ ExpenseCategoriesSeeder - For other CMS companies (not MyGrowNet Platform)

## Related Documentation

- `docs/Finance/CMS_FINANCIAL_INTEGRATION_ANALYSIS.md` - Complete CMS integration details
- `database/migrations/*_create_cms_*.php` - CMS table migrations
- `database/migrations/*_add_cms_fields_to_transactions.php` - Transaction table updates
- `database/seeders/ProductionSeeder.php` - Main production seeder configuration

