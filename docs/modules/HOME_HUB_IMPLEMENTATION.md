# Home Hub Implementation

**Last Updated:** December 1, 2025
**Status:** Updated for Account Types

## Overview

The Home Hub is the central landing page for MyGrowNet. After login, users land on `/home-hub` - a clean page showing modules available based on their account type(s).

## Account Type Filtering

Home Hub displays only modules available to the user's account types.

### Controller Implementation

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\AccountType;

class HomeHubController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get all modules
        $allModules = config('modules');
        
        // Filter by account type
        $availableModules = collect($allModules)
            ->filter(function ($module, $slug) use ($user) {
                // Get allowed account types for this module
                $allowedTypes = $module['account_types'] ?? [];
                
                // Check if user has any of the allowed types
                foreach ($user->account_types as $userType) {
                    if (in_array($userType, $allowedTypes)) {
                        return true;
                    }
                }
                
                return false;
            })
            ->map(function ($module, $slug) use ($user) {
                // Add subscription status
                $module['subscribed'] = $user->hasModuleAccess($slug);
                $module['slug'] = $slug;
                return $module;
            })
            ->values();
        
        return Inertia::render('HomeHub/Index', [
            'modules' => $availableModules,
            'accountTypes' => $user->account_types,
        ]);
    }
}
```

### Vue Component

```vue
<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Account Type Badges -->
      <div class="mb-6 flex gap-2">
        <span
          v-for="type in accountTypes"
          :key="type"
          :class="`px-3 py-1 rounded-full text-sm ${getAccountTypeColor(type)}`"
        >
          {{ formatAccountType(type) }}
        </span>
      </div>

      <!-- Module Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <ModuleTile
          v-for="module in modules"
          :key="module.slug"
          :module="module"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modules: Array,
  accountTypes: Array,
});

const formatAccountType = (type) => {
  const labels = {
    member: 'Member',
    client: 'Client',
    business: 'Business',
    investor: 'Investor',
    employee: 'Employee',
  };
  return labels[type] || type;
};

const getAccountTypeColor = (type) => {
  const colors = {
    member: 'bg-blue-100 text-blue-700',
    client: 'bg-green-100 text-green-700',
    business: 'bg-purple-100 text-purple-700',
    investor: 'bg-indigo-100 text-indigo-700',
    employee: 'bg-gray-100 text-gray-700',
  };
  return colors[type] || 'bg-gray-100 text-gray-700';
};
</script>
```

## Design

- Centered "MyGrowNet" branding in header (teal "My" + gray "GrowNet")
- Simple "Home" title
- 3-column grid of square, colorful module tiles
- Each tile has a white icon and label
- Mobile-first responsive design

## Module Examples

| Module | Route | Account Types | Color |
|--------|-------|---------------|-------|
| Core (MLM) | `/dashboard` | `member` | Teal (bg-teal-400) |
| Wedding Planner | `/modules/wedding-planner` | `member`, `client` | Pink (bg-pink-400) |
| SME Accounting | `/modules/accounting` | `business` | Emerald (bg-emerald-400) |
| Investor Portal | `/investor/dashboard` | `investor`, `member` | Indigo (bg-indigo-400) |
| Employee Portal | `/employee/portal` | `employee` | Blue (bg-blue-500) |
| Marketplace | `/marketplace` | `member`, `client`, `business` | Orange (bg-orange-400) |

## Access Control

Access is based on **account types**, not roles:

```php
// config/modules.php
'core' => [
    'account_types' => ['member'], // MEMBER only
],

'wedding-planner' => [
    'account_types' => ['member', 'client'], // Both can access
],

'accounting' => [
    'account_types' => ['business'], // BUSINESS only
],
```

## Files

```
app/Http/Controllers/HomeHubController.php
app/Http/Middleware/CheckModuleAccess.php
resources/js/pages/HomeHub/Index.vue
resources/js/Components/HomeHub/ModuleTile.vue
```

## Routes

```php
// Home Hub (post-login landing)
Route::get('/home', [HomeHubController::class, 'index'])->name('home');

// Public landing page
Route::get('/', [HomeController::class, 'index'])->name('welcome');
```

## Post-Login Redirect

In `app/Providers/RouteServiceProvider.php`:
```php
public const HOME = '/home';
```

## Adding New Modules

1. Add permission in `HomeHubController::getUserModulePermissions()`
2. Add ModuleTile in `HomeHub/Index.vue`:
```vue
<ModuleTile
  v-if="hasPermission('access_new_module')"
  title="New Module"
  :icon="IconComponent"
  bgColor="bg-color-500"
  :href="route('new.module.route')"
/>
```

## Component Props

### ModuleTile
| Prop | Type | Description |
|------|------|-------------|
| title | string | Display name |
| icon | Component | Heroicon component |
| bgColor | string | Tailwind bg class |
| href | string | Route URL |
| comingSoon | boolean | Shows "Soon" badge |
