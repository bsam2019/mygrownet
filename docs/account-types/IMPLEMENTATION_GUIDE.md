# Account Types Implementation Guide

**Last Updated:** December 1, 2025
**Status:** In Progress

## Overview

This guide provides step-by-step instructions for implementing the account type system in MyGrowNet.

---

## Current Status

### ✅ Completed
- [x] AccountType enum created with all 5 types
- [x] Helper methods added (hasMLMAccess, availableModules, etc.)
- [x] UI colors and icons defined
- [x] Documentation structure created

### 🚧 In Progress
- [ ] User model multi-account type support
- [ ] Database migration for account_types JSON column
- [ ] Middleware for account type checking
- [ ] Registration flows for each type

### 📋 Pending
- [ ] Portal routing implementation
- [ ] Billing integration
- [ ] Account type upgrade paths
- [ ] Admin management interface

---

## Phase 1: Database & User Model

### Step 1.1: Update Users Table Migration

**File:** `database/migrations/YYYY_MM_DD_add_account_types_to_users.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change account_type from single value to JSON array
            // This allows users to have multiple account types
            $table->json('account_types')->nullable()->after('account_type');
        });

        // Migrate existing account_type values to account_types array
        DB::table('users')->whereNotNull('account_type')->update([
            'account_types' => DB::raw("JSON_ARRAY(account_type)")
        ]);

        // After migration, we can optionally drop the old column
        // Schema::table('users', function (Blueprint $table) {
        //     $table->dropColumn('account_type');
        // });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_types');
        });
    }
};
```

### Step 1.2: Update User Model

**File:** `app/Models/User.php`

Add these methods to the User model:

```php
/**
 * Get account types as array
 */
public function getAccountTypesAttribute($value): array
{
    if (is_null($value)) {
        // Default to single account_type if account_types is null
        return $this->account_type ? [$this->account_type] : [];
    }
    
    return json_decode($value, true) ?? [];
}

/**
 * Set account types
 */
public function setAccountTypesAttribute($value): void
{
    $this->attributes['account_types'] = json_encode(array_unique($value));
}

/**
 * Check if user has specific account type
 */
public function hasAccountType(AccountType $type): bool
{
    return in_array($type->value, $this->account_types);
}

/**
 * Add account type to user
 */
public function addAccountType(AccountType $type): void
{
    $types = $this->account_types;
    if (!in_array($type->value, $types)) {
        $types[] = $type->value;
        $this->account_types = $types;
        $this->save();
    }
}

/**
 * Remove account type from user
 */
public function removeAccountType(AccountType $type): void
{
    $types = $this->account_types;
    $types = array_filter($types, fn($t) => $t !== $type->value);
    $this->account_types = array_values($types);
    $this->save();
}

/**
 * Check if user is MLM participant
 */
public function isMLMParticipant(): bool
{
    return $this->hasAccountType(AccountType::MEMBER);
}

/**
 * Check if user is internal employee
 */
public function isEmployee(): bool
{
    return $this->hasAccountType(AccountType::EMPLOYEE);
}

/**
 * Get all available modules for user based on all their account types
 */
public function getAvailableModules(): array
{
    $modules = [];
    
    foreach ($this->account_types as $typeValue) {
        $type = AccountType::from($typeValue);
        $modules = array_merge($modules, $type->availableModules());
    }
    
    return array_unique($modules);
}

/**
 * Get primary account type (first one in array)
 */
public function getPrimaryAccountType(): ?AccountType
{
    $types = $this->account_types;
    return empty($types) ? null : AccountType::from($types[0]);
}
```

### Step 1.3: Update Fillable Fields

Add to User model's `$fillable` array:

```php
protected $fillable = [
    // ... existing fields
    'account_types',
];
```

### Step 1.4: Create Seeder for Account Types

**File:** `database/seeders/AccountTypeSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\AccountType;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Update existing users based on their current account_type
        User::whereNotNull('account_type')->chunk(100, function ($users) {
            foreach ($users as $user) {
                // Convert single account_type to array
                $user->account_types = [$user->account_type];
                $user->save();
            }
        });

        // Set default account type for users without one
        User::whereNull('account_type')->whereNull('account_types')->chunk(100, function ($users) {
            foreach ($users as $user) {
                // Users with referrer = MEMBER, without = CLIENT
                $type = $user->referrer_id ? AccountType::MEMBER : AccountType::CLIENT;
                $user->account_types = [$type->value];
                $user->save();
            }
        });
    }
}
```

---

## Phase 2: Middleware & Access Control

### Step 2.1: Create CheckAccountType Middleware

**File:** `app/Http/Middleware/CheckAccountType.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\AccountType;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$types  Account types allowed (e.g., 'member', 'client')
     */
    public function handle(Request $request, Closure $next, string ...$types): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Convert string types to AccountType enums
        $allowedTypes = array_map(fn($type) => AccountType::from($type), $types);

        // Check if user has any of the allowed account types
        foreach ($allowedTypes as $type) {
            if ($request->user()->hasAccountType($type)) {
                return $next($request);
            }
        }

        // User doesn't have required account type
        abort(403, 'You do not have access to this area. Required account type: ' . implode(' or ', $types));
    }
}
```

### Step 2.2: Register Middleware

**File:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        // ... existing middleware
        'account.type' => \App\Http\Middleware\CheckAccountType::class,
    ]);
})
```

### Step 2.3: Update CheckModuleAccess Middleware

**File:** `app/Http/Middleware/CheckModuleAccess.php`

Update to check account types:

```php
public function handle(Request $request, Closure $next, string $moduleSlug): Response
{
    if (!$request->user()) {
        return redirect()->route('login');
    }

    // Get user's available modules based on account types
    $availableModules = $request->user()->getAvailableModules();

    // Check if user has access to this module
    if (!in_array($moduleSlug, $availableModules)) {
        abort(403, 'You do not have access to this module.');
    }

    return $next($request);
}
```

---

## Phase 3: Route Protection

### Step 3.1: Protect MLM Routes

**File:** `routes/web.php`

```php
// MLM Member Routes - Only for MEMBER account type
Route::middleware(['auth', 'account.type:member'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index'])->name('mobile.dashboard');
    Route::get('/network', [NetworkController::class, 'index'])->name('network.index');
    Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
});
```

### Step 3.2: Protect Investor Routes

```php
// Investor Routes - For INVESTOR or MEMBER account types
Route::middleware(['auth', 'account.type:investor,member'])->group(function () {
    Route::prefix('investor')->name('investor.')->group(function () {
        Route::get('/dashboard', [InvestorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/portfolio', [InvestorPortfolioController::class, 'index'])->name('portfolio');
        Route::get('/projects', [InvestorProjectsController::class, 'index'])->name('projects');
    });
});
```

### Step 3.3: Protect Business Routes

```php
// Business Routes - Only for BUSINESS account type
Route::middleware(['auth', 'account.type:business'])->group(function () {
    Route::prefix('business')->name('business.')->group(function () {
        Route::get('/accounting', [AccountingController::class, 'index'])->name('accounting');
        Route::get('/tasks', [TasksController::class, 'index'])->name('tasks');
        Route::get('/staff', [StaffController::class, 'index'])->name('staff');
    });
});
```

### Step 3.4: Protect Employee Routes

```php
// Employee Routes - Only for EMPLOYEE account type
Route::middleware(['auth', 'account.type:employee'])->group(function () {
    Route::prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeController::class, 'dashboard'])->name('dashboard');
        Route::get('/live-chat', [LiveChatController::class, 'index'])->name('live-chat');
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
    });
});
```

### Step 3.5: Public/Shared Routes

```php
// Routes accessible by multiple account types
Route::middleware(['auth', 'account.type:member,client'])->group(function () {
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/modules/{slug}', [ModuleController::class, 'show'])->name('modules.show');
});

Route::middleware(['auth', 'account.type:member,client,investor'])->group(function () {
    Route::prefix('venture-builder')->name('venture.')->group(function () {
        Route::get('/', [VentureBuilderController::class, 'index'])->name('index');
        Route::get('/projects', [VentureBuilderController::class, 'projects'])->name('projects');
    });
});
```

---

## Phase 4: Registration Flows

### Step 4.1: Update Registration Controller

**File:** `app/Http/Controllers/Auth/RegisterController.php`

```php
protected function create(array $data)
{
    // Determine account type based on registration context
    $accountType = $this->determineAccountType($data);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'phone' => $data['phone'] ?? null,
        'referrer_id' => $data['referrer_id'] ?? null,
        'account_types' => [$accountType->value],
    ]);

    return $user;
}

protected function determineAccountType(array $data): AccountType
{
    // If has referrer, they're joining as MEMBER
    if (!empty($data['referrer_id'])) {
        return AccountType::MEMBER;
    }

    // If registering for business tools
    if (!empty($data['business_registration'])) {
        return AccountType::BUSINESS;
    }

    // If registering as investor
    if (!empty($data['investor_registration'])) {
        return AccountType::INVESTOR;
    }

    // Default to CLIENT (app/shop user)
    return AccountType::CLIENT;
}
```

### Step 4.2: Create Account Type Selection UI

**File:** `resources/js/Pages/Auth/Register.vue`

```vue
<template>
  <div>
    <!-- Account Type Selection -->
    <div class="mb-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        I want to:
      </label>
      <div class="space-y-3">
        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
          <input
            type="radio"
            v-model="form.registration_type"
            value="member"
            class="mr-3"
          />
          <div>
            <div class="font-medium">Join as Member</div>
            <div class="text-sm text-gray-500">
              Build network, earn commissions, access training
            </div>
          </div>
        </label>

        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
          <input
            type="radio"
            v-model="form.registration_type"
            value="client"
            class="mr-3"
          />
          <div>
            <div class="font-medium">Use Apps & Shop</div>
            <div class="text-sm text-gray-500">
              Purchase apps and shop without MLM participation
            </div>
          </div>
        </label>

        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
          <input
            type="radio"
            v-model="form.registration_type"
            value="business"
            class="mr-3"
          />
          <div>
            <div class="font-medium">Business Tools</div>
            <div class="text-sm text-gray-500">
              Accounting, tasks, staff management for SMEs
            </div>
          </div>
        </label>

        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
          <input
            type="radio"
            v-model="form.registration_type"
            value="investor"
            class="mr-3"
          />
          <div>
            <div class="font-medium">Invest in Projects</div>
            <div class="text-sm text-gray-500">
              Co-invest in Venture Builder projects
            </div>
          </div>
        </label>
      </div>
    </div>

    <!-- Conditional fields based on account type -->
    <div v-if="form.registration_type === 'member'" class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Referral Code (Optional)
      </label>
      <input
        type="text"
        v-model="form.referral_code"
        class="w-full px-4 py-2 border rounded-lg"
        placeholder="Enter referral code"
      />
    </div>

    <!-- Rest of registration form -->
  </div>
</template>
```

---

## Phase 5: Home Hub Integration

### Step 5.1: Update Home Hub Controller

**File:** `app/Http/Controllers/HomeHubController.php`

```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Get available modules based on user's account types
    $availableModules = $user->getAvailableModules();
    
    // Get account types for display
    $accountTypes = array_map(
        fn($type) => AccountType::from($type),
        $user->account_types
    );
    
    return Inertia::render('HomeHub/Index', [
        'availableModules' => $availableModules,
        'accountTypes' => $accountTypes,
        'primaryAccountType' => $user->getPrimaryAccountType(),
    ]);
}
```

### Step 5.2: Update Home Hub Vue Component

**File:** `resources/js/Pages/HomeHub/Index.vue`

```vue
<template>
  <div>
    <!-- Account Type Badges -->
    <div class="mb-6 flex gap-2">
      <span
        v-for="type in accountTypes"
        :key="type.value"
        :class="`px-3 py-1 rounded-full text-sm bg-${type.color}-100 text-${type.color}-700`"
      >
        {{ type.label }}
      </span>
    </div>

    <!-- Module Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <ModuleTile
        v-for="module in availableModules"
        :key="module"
        :module="module"
      />
    </div>
  </div>
</template>
```

---

## Phase 6: Testing

### Test Cases

1. **Single Account Type**
   - Create MEMBER user → Should see MLM dashboard
   - Create CLIENT user → Should NOT see MLM dashboard
   - Create BUSINESS user → Should see business tools
   - Create INVESTOR user → Should see investor portal
   - Create EMPLOYEE user → Should see employee portal

2. **Multiple Account Types**
   - Create MEMBER + INVESTOR → Should see both MLM and investor portals
   - Create CLIENT + INVESTOR → Should see marketplace and investor portal
   - Create MEMBER + BUSINESS → Should see MLM and business tools

3. **Access Control**
   - CLIENT tries to access MLM routes → Should get 403
   - MEMBER tries to access employee portal → Should get 403
   - INVESTOR tries to access business tools → Should get 403

4. **Registration**
   - Register with referral code → Should create MEMBER
   - Register without referral code → Should create CLIENT
   - Register for business tools → Should create BUSINESS

5. **Upgrades**
   - CLIENT upgrades to MEMBER → Should add MEMBER to account_types
   - MEMBER invests in project → Should add INVESTOR to account_types

---

## Phase 7: Admin Interface

### Step 7.1: Create Account Type Management

**File:** `app/Http/Controllers/Admin/AccountTypeController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\AccountType;
use Illuminate\Http\Request;

class AccountTypeController extends Controller
{
    public function update(Request $request, User $user)
    {
        $request->validate([
            'account_types' => 'required|array',
            'account_types.*' => 'in:member,client,business,investor,employee',
        ]);

        $user->account_types = $request->account_types;
        $user->save();

        return back()->with('success', 'Account types updated successfully.');
    }

    public function add(Request $request, User $user)
    {
        $request->validate([
            'account_type' => 'required|in:member,client,business,investor,employee',
        ]);

        $type = AccountType::from($request->account_type);
        $user->addAccountType($type);

        return back()->with('success', 'Account type added successfully.');
    }

    public function remove(Request $request, User $user)
    {
        $request->validate([
            'account_type' => 'required|in:member,client,business,investor,employee',
        ]);

        $type = AccountType::from($request->account_type);
        $user->removeAccountType($type);

        return back()->with('success', 'Account type removed successfully.');
    }
}
```

---

## Rollout Plan

### Week 1: Foundation
- [ ] Run database migration
- [ ] Update User model
- [ ] Run seeder to migrate existing users
- [ ] Test basic functionality

### Week 2: Access Control
- [ ] Implement middleware
- [ ] Update route protection
- [ ] Test access control
- [ ] Fix any issues

### Week 3: Registration
- [ ] Update registration flows
- [ ] Create account type selection UI
- [ ] Test all registration paths
- [ ] Update onboarding

### Week 4: Portals
- [ ] Update Home Hub
- [ ] Implement portal routing
- [ ] Test multi-account types
- [ ] Polish UI/UX

### Week 5: Admin & Polish
- [ ] Create admin interface
- [ ] Add account type management
- [ ] Final testing
- [ ] Documentation updates

---

## Troubleshooting

### Issue: Users can't access their portal
**Solution:** Check if they have the correct account type assigned. Use admin interface to add missing account type.

### Issue: Migration fails
**Solution:** Ensure account_type column exists before adding account_types. Run migrations in order.

### Issue: Middleware blocks legitimate access
**Solution:** Check route middleware configuration. Ensure account types are passed correctly.

### Issue: Multi-account type not working
**Solution:** Verify account_types is JSON array in database. Check User model accessors/mutators.

---

## Next Steps

After implementation:

1. **Monitor Usage**
   - Track which account types are most common
   - Monitor upgrade paths
   - Identify issues

2. **Optimize**
   - Cache available modules
   - Optimize database queries
   - Improve UI/UX

3. **Expand**
   - Add more account type-specific features
   - Create account type analytics
   - Build upgrade incentives

---

**Remember:** Account types are fundamental to MyGrowNet's architecture. Take time to implement correctly and test thoroughly.
