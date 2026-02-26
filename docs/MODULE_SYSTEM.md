# Module Activation System

**Last Updated:** February 20, 2026  
**Status:** Production Ready

---

## Overview

The Module Activation System allows administrators to control which features are visible and accessible in the MyGrowNet platform. This is essential for:

- Hiding features that are not ready for production (e.g., Venture Builder)
- Customizing the platform for specific deployments
- Gradually rolling out new features
- Managing feature access without code changes

---

## How It Works

### Configuration File

All modules are defined in `config/modules.php`:

```php
'modules' => [
    'venture_builder' => [
        'enabled' => false, // Module is hidden
        'name' => 'Venture Builder',
        'description' => 'Co-invest in vetted business projects',
        'icon' => 'BriefcaseIcon',
        'route' => 'ventures.index',
        'nav_group' => 'main',
    ],
    
    'grownet' => [
        'enabled' => true, // Module is visible
        'name' => 'GrowNet',
        'description' => '7-level professional network',
        'icon' => 'UsersIcon',
        'route' => 'grownet.dashboard',
        'nav_group' => 'main',
    ],
]
```

### Module Properties

- **enabled**: `true` or `false` - controls visibility
- **name**: Display name for the module
- **description**: Brief description of what the module does
- **icon**: Heroicon name for UI display
- **route**: Main route name for the module
- **nav_group**: Navigation group (main, business, learning, etc.)
- **always_enabled**: (optional) If `true`, module cannot be disabled

### Navigation Groups

Modules are organized into groups for better UI organization:

```php
'nav_groups' => [
    'main' => ['name' => 'Main Features', 'order' => 1],
    'business' => ['name' => 'Business Tools', 'order' => 2],
    'learning' => ['name' => 'Learning & Development', 'order' => 3],
    'financial' => ['name' => 'Financial', 'order' => 4],
    'communication' => ['name' => 'Communication', 'order' => 5],
    'community' => ['name' => 'Community', 'order' => 6],
    'lifestyle' => ['name' => 'Lifestyle', 'order' => 7],
]
```

---

## Implementation

### Backend Components

**1. ModuleService** (`app/Services/ModuleService.php`)
- `isEnabled(string $moduleKey)`: Check if module is enabled
- `getEnabledModules()`: Get all enabled modules
- `getModulesByGroup(string $group)`: Get modules by navigation group
- `enable(string $moduleKey)`: Enable a module
- `disable(string $moduleKey)`: Disable a module
- `clearCache()`: Clear module cache

**2. CheckModuleEnabled Middleware** (`app/Http/Middleware/CheckModuleEnabled.php`)
- Protects routes from disabled modules
- Returns 503 error or redirects to dashboard

**3. Module Controller** (`app/Http/Controllers/Admin/ModuleController.php`)
- Admin interface for managing modules
- Toggle module status
- Clear cache

### Frontend Components

**1. Admin Module Management Page** (`resources/js/pages/Admin/Modules/Index.vue`)
- Visual interface for enabling/disabling modules
- Grouped by navigation category
- Shows module status and statistics

**2. Inertia Shared Data**
- Module configuration shared with all pages via `HandleInertiaRequests`
- Available as `$page.props.modules` in Vue components

### Route Protection

Routes are protected using the `module` middleware:

```php
// Protect all Venture Builder routes
Route::middleware(['module:venture_builder'])->prefix('ventures')->group(function () {
    Route::get('/', [VentureController::class, 'index']);
    Route::get('/{venture}', [VentureController::class, 'show']);
});

// Protect authenticated routes
Route::middleware(['auth', 'module:venture_builder'])->group(function () {
    Route::post('/ventures/{venture}/invest', [VentureController::class, 'invest']);
});
```

---

## Usage

### For Administrators

**Access Module Management:**
1. Navigate to Admin Panel
2. Go to Settings → Module Management (or `/admin/modules`)
3. Toggle modules on/off using the switches
4. Click "Clear Cache" after making changes

**Enable a Module:**
1. Find the module in the list
2. Toggle the switch to "Enabled"
3. Module will immediately become visible to users

**Disable a Module:**
1. Find the module in the list
2. Toggle the switch to "Disabled"
3. Module will be hidden from navigation and routes will be blocked

### For Developers

**Check if Module is Enabled (Backend):**
```php
use App\Services\ModuleService;

if (ModuleService::isEnabled('venture_builder')) {
    // Module is enabled
}
```

**Check if Module is Enabled (Frontend):**
```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const modules = page.props.modules;

const isVentureBuilderEnabled = modules.venture_builder?.is_enabled ?? false;
</script>

<template>
    <Link v-if="isVentureBuilderEnabled" :href="route('ventures.index')">
        Venture Builder
    </Link>
</template>
```

**Protect Routes:**
```php
// Single module
Route::middleware(['module:venture_builder'])->group(function () {
    // Routes here
});

// Multiple middleware
Route::middleware(['auth', 'verified', 'module:growbuilder'])->group(function () {
    // Routes here
});
```

**Get All Enabled Modules:**
```php
$modules = ModuleService::getEnabledModules();
```

**Get Modules by Group:**
```php
$mainModules = ModuleService::getModulesByGroup('main');
$businessModules = ModuleService::getModulesByGroup('business');
```

---

## Adding New Modules

To add a new module to the system:

**1. Add to Configuration** (`config/modules.php`):
```php
'my_new_module' => [
    'enabled' => false, // Start disabled
    'name' => 'My New Module',
    'description' => 'Description of what it does',
    'icon' => 'SparklesIcon',
    'route' => 'my-module.index',
    'nav_group' => 'main',
],
```

**2. Protect Routes** (in your route file):
```php
Route::middleware(['module:my_new_module'])->prefix('my-module')->group(function () {
    Route::get('/', [MyModuleController::class, 'index'])->name('my-module.index');
});
```

**3. Update Navigation** (if applicable):
```vue
<Link 
    v-if="$page.props.modules.my_new_module?.is_enabled"
    :href="route('my-module.index')"
>
    My New Module
</Link>
```

**4. Clear Cache:**
```bash
php artisan cache:clear
```

---

## Caching

Module configuration is cached for performance:

- **Cache Duration**: 1 hour (3600 seconds)
- **Cache Keys**: 
  - `modules.enabled` - All enabled modules
  - `module.{key}.enabled` - Individual module status

**Clear Cache:**
- Via Admin UI: Module Management → Clear Cache button
- Via Code: `ModuleService::clearCache()`
- Via Artisan: `php artisan cache:clear`

---

## Current Module Status

### Enabled Modules ✅
- Dashboard (always enabled)
- GrowNet
- GrowBuilder
- CMS (Company Management)
- BizBoost (Business management & marketing)
- GrowFinance (Accounting & financial management)
- GrowBiz (Team & employee management)
- Inventory Management
- Point of Sale (POS)
- GrowMarket (Marketplace)
- BGF (Business Growth Fund)
- Library
- Workshops
- Messaging
- Announcements
- MyGrow Wallet
- Profit Sharing
- Community
- Support

### Disabled Modules ❌
- **Venture Builder** - Not ready for production (see `docs/VENTURE_BUILDER_CONCEPT.md`)
- **Life+** - Optional lifestyle module
- **Ubumi** - Optional health tracking module

---

## Security Considerations

**1. Admin-Only Access:**
- Only administrators can access module management
- Controlled via `admin` middleware

**2. Always-Enabled Modules:**
- Core modules (like Dashboard) cannot be disabled
- Prevents breaking the platform

**3. Route Protection:**
- Disabled modules return 503 errors
- Users are redirected to dashboard with error message

**4. Cache Invalidation:**
- Cache is automatically cleared when toggling modules
- Ensures immediate effect of changes

---

## Troubleshooting

**Module not showing after enabling:**
1. Clear cache via admin UI or `php artisan cache:clear`
2. Check browser cache (hard refresh: Ctrl+Shift+R)
3. Verify route middleware is correctly applied

**Cannot disable a module:**
- Check if module has `always_enabled => true`
- Core modules like Dashboard cannot be disabled

**Routes still accessible after disabling:**
- Ensure routes have `module` middleware applied
- Clear application cache
- Check for cached routes: `php artisan route:clear`

**Module not appearing in admin list:**
- Verify module is defined in `config/modules.php`
- Check configuration syntax
- Clear config cache: `php artisan config:clear`

---

## Files Modified

### Created:
- `config/modules.php` - Module configuration
- `app/Services/ModuleService.php` - Module management service
- `app/Http/Middleware/CheckModuleEnabled.php` - Route protection middleware
- `app/Http/Controllers/Admin/ModuleController.php` - Admin controller
- `resources/js/pages/Admin/Modules/Index.vue` - Admin UI

### Modified:
- `bootstrap/app.php` - Registered middleware alias
- `app/Http/Middleware/HandleInertiaRequests.php` - Share module data
- `routes/web.php` - Added admin routes
- `routes/venture.php` - Added module middleware
- `app/Presentation/Http/Controllers/HomeHubController.php` - Filter modules by enabled status
- `docs/VENTURE_BUILDER_CONCEPT.md` - Updated with module system info

---

## How Module Filtering Works

### Backend Filtering

Modules are filtered at the controller level before being sent to the frontend:

**HomeHubController** (`app/Presentation/Http/Controllers/HomeHubController.php`):
```php
private function filterEnabledModules(array $modules): array
{
    $enabledModules = \App\Services\ModuleService::getEnabledModules();
    $enabledSlugs = array_keys($enabledModules);
    
    // Map module slugs to config keys
    $slugToConfigKey = [
        'grownet' => 'grownet',
        'growbuilder' => 'growbuilder',
        'bizboost' => 'bizboost',
        'growfinance' => 'growfinance',
        'growbiz' => 'growbiz',
        'cms' => 'cms',
        'inventory' => 'inventory',
        'pos' => 'pos',
        'bgf' => 'bgf',
        'marketplace' => 'growmarket',
        'ventures' => 'venture_builder',
        // ... etc
    ];
    
    return array_filter($modules, function($module) use ($enabledSlugs, $slugToConfigKey) {
        $slug = $module['slug'] ?? '';
        $configKey = $slugToConfigKey[$slug] ?? $slug;
        
        // Always show dashboard
        if ($slug === 'dashboard' || $configKey === 'dashboard') {
            return true;
        }
        
        return in_array($configKey, $enabledSlugs);
    });
}
```

**DashboardController** (`app/Http/Controllers/DashboardController.php`):
- Uses the same `filterEnabledModules()` logic
- Filters modules before passing to Dashboard/Index.vue

**Note:** Each business tool module (bizboost, growfinance, growbiz, inventory, pos, bgf) now has its own config key for individual control.

### Route Protection

Routes are protected using the `module` middleware:

```php
// Venture Builder routes are protected
Route::middleware(['module:venture_builder'])->prefix('ventures')->group(function () {
    Route::get('/', [VentureController::class, 'index']);
});
```

If a user tries to access a disabled module's route:
- **Web requests**: Redirected to dashboard with error message
- **API requests**: Returns 503 JSON response

---

## Changelog

### February 20, 2026 - Dashboard Filtering Complete
- **Dashboard Controller**: Added module filtering to `DashboardController::index()`
- **Complete Coverage**: All entry points now filter modules (Dashboard, HomeHub, Navigation)
- **Consistent Filtering**: Same `filterEnabledModules()` logic across all controllers

### February 20, 2026 - Navigation Filtering Complete
- **Navigation Updates**: Updated all navigation components to filter based on module status
- **Footer**: Added module filtering for Venture Builder links
- **MyGrowNetSidebar**: Filtered Venture Builder in investor and member sections using computed properties
- **AdminSidebar**: Hidden Venture Builder admin section when module disabled
- **AdminSidebar**: Filtered Venture Builder nav group dynamically
- **Navigation.vue**: Fixed TypeScript syntax error (removed type annotations for Options API)
- **Complete Hiding**: Venture Builder now completely hidden when disabled (no links anywhere)
- **Frontend Build**: Successfully built all assets with module filtering

### February 20, 2026 - Complete Implementation
- **Initial Implementation**: Created module activation system
- **Configuration**: Added `config/modules.php` with all platform modules
- **Service Layer**: Implemented `ModuleService` with caching
- **Middleware**: Created `CheckModuleEnabled` for route protection
- **Admin UI**: Built module management interface at `/admin/modules`
- **Integration**: Shared module data with Inertia
- **Backend Filtering**: Implemented module filtering in `HomeHubController`
- **Frontend Integration**: Modules filtered before reaching Vue components
- **Route Protection**: All Venture Builder routes protected with middleware
- **Documentation**: Created comprehensive guide
- **Venture Builder**: Disabled by default (not production ready)
- **Testing**: Verified filtering works on /apps, dashboard, and navigation

**Result**: Disabling a module now:
- ✅ Hides it from /apps page
- ✅ Hides it from dashboard
- ✅ Hides it from AppLauncher dropdown
- ✅ Blocks all routes (returns 503 or redirects)
- ✅ Removes from navigation (all sidebars and menus)
- ✅ Removes from footer links
- ✅ Works for both authenticated and public users

---

## Future Enhancements

**Potential Improvements:**
- Role-based module access (different modules for different user roles)
- Module dependencies (Module A requires Module B)
- Module versioning and update tracking
- Module marketplace (install/uninstall modules)
- Per-user module preferences
- Module analytics (usage tracking)
- Scheduled module activation (enable at specific date/time)

---

*For specific module documentation, see individual module docs in `docs/` directory.*
