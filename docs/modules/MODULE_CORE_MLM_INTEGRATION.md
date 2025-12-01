# MyGrowNet Core Module Integration

**Last Updated:** December 1, 2025
**Status:** Documentation Update

## ⚠️ CRITICAL: Core is MEMBER-Only

The Core module (MLM dashboard) is **exclusively for MEMBER account type**.

### Why?
- Core contains MLM features (network, commissions, levels, profit-sharing)
- Only **MEMBER** account type participates in MLM
- **CLIENT, BUSINESS, INVESTOR, EMPLOYEE** do NOT have MLM access

### Access Control

**Route Protection:**
```php
// Core routes are protected by account type
Route::middleware(['auth', 'account.type:member'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index']);
});
```

**User Model Check:**
```php
// Check if user can access Core
if ($user->hasAccountType(AccountType::MEMBER)) {
    // Show Core dashboard
} else {
    // Redirect to Home Hub or marketplace
}
```

### What Happens to Non-Members?

- **CLIENT** → Sees purchased modules + marketplace (NO Core)
- **BUSINESS** → Sees business tools (NO Core)
- **INVESTOR** → Sees investor portal (NO Core)
- **EMPLOYEE** → Sees employee portal (NO Core)

They do NOT see Core/MLM dashboard.

---

## Overview

The existing MyGrowNet dashboard (MLM/community growth system) is now treated as **MyGrowNet Core** - the foundational module that all MEMBER account types have access to by default. This document explains how the Core module fits into the modular architecture.

## What is MyGrowNet Core?

MyGrowNet Core is the **original VBIF/MyGrowNet platform** - the MLM and community growth system that includes:

- 7-level professional progression (Associate → Ambassador)
- 3×3 forced matrix with spillover
- Referral commissions and level bonuses
- Quarterly profit-sharing from community projects
- Lifetime Points (LP) and Monthly Activity Points (MAP)
- Team network visualization
- Earnings calculator and tracking
- Withdrawal management
- Learning resources and business tools

## Core Module vs Other Modules

### Key Differences

| Aspect | MyGrowNet Core | Other Modules |
|--------|----------------|---------------|
| **Access** | MEMBER account type only | Based on account type + subscription |
| **Routes** | `/dashboard`, `/mobile-dashboard` | `/modules/{slug}`, `/apps/{slug}` |
| **Purpose** | MLM/community growth | Specialized tools |
| **Status** | Production (existing system) | In development |
| **Account Type** | `member` only | `member`, `client`, `business`, etc. |
| **MLM Features** | ✅ Yes | ❌ No |

### Relationship

```
┌─────────────────────────────────────────────────────────┐
│                  MyGrowNet Platform                      │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌────────────────────────────────────────────┐         │
│  │         MyGrowNet Core (MLM)               │         │
│  │  • All members have access                 │         │
│  │  • Foundation of the platform              │         │
│  │  • Routes: /dashboard, /mobile-dashboard   │         │
│  └────────────────────────────────────────────┘         │
│                        ↓                                 │
│              Members can ADD modules:                    │
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ SME Business │  │   Investor   │  │   Personal   │ │
│  │    Tools     │  │    Portal    │  │   Finance    │ │
│  │ (Subscription│  │ (Subscription│  │ (Subscription│ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

## Module Registry Entry

The Core module should be registered in `config/modules.php`:

```php
'mygrownet-core' => [
    'id' => 'mygrownet-core',
    'name' => 'MyGrowNet Core',
    'slug' => 'core',
    'category' => 'core',  // Special category
    
    // Display
    'icon' => 'SparklesIcon',  // or 'UsersIcon'
    'color' => 'blue',
    'description' => 'Community growth, referrals, and earnings',
    
    // Access Control
    'required_subscription' => [],  // No subscription required
    'required_role' => [],
    'min_user_level' => null,
    'default_access' => true,  // All users have access
    
    // Routing
    'routes' => [
        'integrated' => '/dashboard',
        'standalone' => '/apps/core',  // Optional PWA mode
        'mobile' => '/mobile-dashboard',
    ],
    
    // PWA Configuration (optional)
    'pwa' => [
        'enabled' => true,
        'installable' => true,
        'offline_capable' => true,
    ],
    
    // Features
    'features' => [
        'data_sync' => true,
        'notifications' => true,
        'offline' => true,
        'multi_user' => false,
    ],
    
    // Metadata
    'version' => '1.0.0',
    'status' => 'active',
],
```

## Home Hub Integration

### Module Tile Display

The Core module should appear in the Home Hub with special treatment:

```vue
<!-- Home Hub showing Core as primary module -->
<template>
  <div class="home-hub">
    <!-- Primary Module (Core) -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Your Dashboard</h2>
      <div class="grid grid-cols-1 gap-4">
        <ModuleTile
          :module="coreModule"
          :primary="true"
          :subscription="{ status: 'active' }"
          @launch="launchCore"
        />
      </div>
    </section>
    
    <!-- Additional Modules -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Your Apps</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <ModuleTile
          v-for="module in activeModules"
          :key="module.id"
          :module="module"
          @launch="launchModule"
        />
      </div>
    </section>
    
    <!-- Available Modules -->
    <section>
      <h2 class="text-2xl font-bold mb-4">Discover More</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <ModuleTile
          v-for="module in availableModules"
          :key="module.id"
          :module="module"
          :locked="true"
          @subscribe="showSubscriptionModal"
        />
      </div>
    </section>
  </div>
</template>
```

### Visual Layout

```
┌─────────────────────────────────────────────────────────┐
│  MyGrowNet Home Hub                                      │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Your Dashboard                                          │
│  ┌────────────────────────────────────────────────────┐ │
│  │ 🌱 MyGrowNet Core                                  │ │
│  │                                                    │ │
│  │ Community growth, referrals, and earnings          │ │
│  │                                                    │ │
│  │ Level: Professional | Team: 12 members             │ │
│  │ Earnings: K1,240 | Points: 450 LP                  │ │
│  │                                                    │ │
│  │ [Open Dashboard →]                                 │ │
│  └────────────────────────────────────────────────────┘ │
│                                                          │
│  Your Apps                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ 📊 SME       │  │ 💼 Investor  │  │ 💰 Finance   │ │
│  │ Accounting   │  │ Portal       │  │ Manager      │ │
│  │              │  │              │  │              │ │
│  │ [Open]       │  │ [Open]       │  │ [Open]       │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                          │
│  Discover More                                           │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │ 📦 Inventory │  │ 🎯 Goals     │  │ 🤝 CRM       │ │
│  │ Management   │  │ Tracker      │  │ System       │ │
│  │              │  │              │  │              │ │
│  │ [Subscribe]  │  │ [Subscribe]  │  │ [Subscribe]  │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

## Domain Structure

The Core module follows the same domain-driven structure as other modules:

```
app/
├── Domain/
│   ├── MyGrowNet/              # Core MLM system
│   │   ├── Entities/
│   │   │   ├── User.php
│   │   │   ├── Investment.php
│   │   │   ├── Commission.php
│   │   │   └── MatrixPosition.php
│   │   ├── ValueObjects/
│   │   │   ├── ProfessionalLevel.php
│   │   │   ├── LifetimePoints.php
│   │   │   └── MonthlyActivityPoints.php
│   │   ├── Services/
│   │   │   ├── CommissionService.php
│   │   │   ├── MatrixService.php
│   │   │   └── PointsService.php
│   │   ├── Repositories/
│   │   │   ├── UserRepository.php
│   │   │   └── CommissionRepository.php
│   │   └── Events/
│   │       ├── UserLeveledUp.php
│   │       └── CommissionEarned.php
│   │
│   ├── SME/                    # SME Business Tools
│   ├── Investor/               # Investor Portal
│   └── PersonalFinance/        # Personal Finance
│
├── Infrastructure/
│   └── Persistence/
│       └── Eloquent/
│           ├── MyGrowNet/      # Core models
│           │   ├── User.php
│           │   ├── Investment.php
│           │   └── Commission.php
│           ├── SME/
│           └── Investor/
│
├── Http/
│   └── Controllers/
│       ├── MyGrowNet/          # Core controllers
│       │   ├── DashboardController.php
│       │   ├── TeamController.php
│       │   └── EarningsController.php
│       ├── SME/
│       └── Investor/
│
└── Policies/
    ├── MyGrowNet/              # Core policies
    └── SME/
```

## Routing Structure

### Core Routes (Existing)

```php
// routes/web.php

// Core MLM routes (existing)
Route::middleware(['auth'])->group(function () {
    // Desktop dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    // Mobile dashboard
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index'])
        ->name('mobile.dashboard');
    
    // Team management
    Route::prefix('team')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('team.index');
        Route::get('/network', [TeamController::class, 'network'])->name('team.network');
    });
    
    // Earnings
    Route::prefix('earnings')->group(function () {
        Route::get('/', [EarningsController::class, 'index'])->name('earnings.index');
        Route::get('/calculator', [EarningsController::class, 'calculator'])->name('earnings.calculator');
    });
    
    // ... other core routes
});

// Optional: Core as standalone PWA
Route::middleware(['auth'])->group(function () {
    Route::prefix('apps/core')->group(function () {
        Route::get('/', [CorePWAController::class, 'index']);
        Route::get('/manifest.json', [CorePWAController::class, 'manifest']);
        Route::get('/sw.js', [CorePWAController::class, 'serviceWorker']);
    });
});
```

### Module Routes (New)

```php
// SME Module routes
Route::middleware(['auth', 'module:sme-accounting'])->group(function () {
    Route::prefix('modules/accounting')->group(function () {
        Route::get('/', [AccountingController::class, 'index']);
        // ... other routes
    });
});

// Investor Module routes
Route::middleware(['auth', 'module:investor-portal'])->group(function () {
    Route::prefix('modules/investor')->group(function () {
        Route::get('/', [InvestorController::class, 'index']);
        // ... other routes
    });
});
```

## Access Control

### Core Module Access

```php
// app/Services/ModuleService.php

public function hasAccess(User $user, string $moduleId): bool
{
    // Core module is always accessible
    if ($moduleId === 'mygrownet-core') {
        return true;
    }
    
    // Check subscription for other modules
    return $this->hasActiveSubscription($user, $moduleId);
}
```

### Account Types

```php
// app/Enums/AccountType.php

enum AccountType: string
{
    case MEMBER = 'member';      // Core MLM access (default)
    case SME = 'sme';            // Core + SME tools
    case INVESTOR = 'investor';   // Core + Investor portal
    case EMPLOYEE = 'employee';   // Core + Employee portal
    case ADMIN = 'admin';         // Core + All modules
}
```

**Important:** All account types have access to Core by default. The account type determines which ADDITIONAL modules they can access.

## User Journey

### New Member

```
1. Register → Account Type: MEMBER
2. Login → Redirected to /dashboard (Core)
3. See Home Hub → Core module + locked additional modules
4. Use Core features (referrals, earnings, team, etc.)
5. Optional: Subscribe to additional modules
```

### Member with Additional Modules

```
1. Login → Redirected to Home Hub
2. See Core module (always accessible)
3. See subscribed modules (SME, Investor, etc.)
4. Choose which module to use
5. Can switch between modules anytime
```

## Migration Strategy

### Phase 1: Documentation (Current)
- [x] Document Core module concept
- [x] Update module documentation
- [ ] Update architecture diagrams
- [ ] Update user guides

### Phase 2: Code Organization (Week 1)
- [ ] Create `app/Domain/MyGrowNet/` structure
- [ ] Move existing business logic to domain layer
- [ ] Keep existing routes and controllers working
- [ ] No breaking changes

### Phase 3: Module Registry (Week 2)
- [ ] Add Core module to `config/modules.php`
- [ ] Update ModuleService to recognize Core
- [ ] Add Core tile to Home Hub
- [ ] Test access control

### Phase 4: Optional PWA (Week 3-4)
- [ ] Create standalone PWA entry for Core
- [ ] Add install prompt
- [ ] Test offline functionality
- [ ] Deploy

## Benefits of This Approach

### For Users
✅ **Familiar Experience** - Existing dashboard remains unchanged
✅ **Clear Structure** - Core + optional modules
✅ **Flexibility** - Can add modules as needed
✅ **Consistency** - All modules follow same patterns

### For Development
✅ **Clean Architecture** - Domain-driven design
✅ **Maintainability** - Clear separation of concerns
✅ **Scalability** - Easy to add new modules
✅ **Testability** - Isolated domain logic

### For Business
✅ **Upsell Opportunities** - Core users can upgrade to modules
✅ **Retention** - Core keeps users engaged
✅ **Revenue Streams** - Core (free) + modules (paid)
✅ **Market Positioning** - Platform + specialized tools

## Updated Module List

### Complete Module Portfolio

| Module | Category | Access | Status | Routes |
|--------|----------|--------|--------|--------|
| **MyGrowNet Core** | Core | All members | Production | `/dashboard`, `/mobile-dashboard` |
| **SME Accounting** | SME | Subscription | Development | `/modules/accounting` |
| **Investor Portal** | Investor | Subscription | Development | `/modules/investor` |
| **Personal Finance** | Personal | Subscription | Planned | `/modules/finance` |
| **Inventory Management** | SME | Subscription | Planned | `/modules/inventory` |
| **CRM System** | SME | Subscription | Planned | `/modules/crm` |
| **Goal Tracker** | Personal | Subscription | Planned | `/modules/goals` |
| **Task Management** | SME | Subscription | Planned | `/modules/tasks` |

## Summary

The MyGrowNet Core module represents the existing MLM/community growth system and serves as the foundation of the platform. Key points:

1. **All members have access** to Core by default
2. **No subscription required** for Core features
3. **Routes remain unchanged** (`/dashboard`, `/mobile-dashboard`)
4. **Additional modules** require subscriptions
5. **Same architecture** as other modules (domain-driven)
6. **Home Hub** shows Core prominently as primary module
7. **Account types** determine additional module access

This approach maintains backward compatibility while providing a clear path for modular expansion.

## Next Steps

1. Update all module documentation to reference Core
2. Add Core module entry to `config/modules.php`
3. Update Home Hub to display Core prominently
4. Update ModuleService to handle Core access
5. Test integration with existing dashboard
6. Document for team members

## References

- [MODULE_SYSTEM_ARCHITECTURE.md](MODULE_SYSTEM_ARCHITECTURE.md)
- [MODULE_QUICK_START.md](MODULE_QUICK_START.md)
- [MODULAR_APPS_COMPLETE_GUIDE.md](../MODULAR_APPS_COMPLETE_GUIDE.md)
- [HOME_HUB_IMPLEMENTATION.md](../HOME_HUB_IMPLEMENTATION.md)
- [MYGROWNET_PLATFORM_CONCEPT.md](MYGROWNET_PLATFORM_CONCEPT.md)

