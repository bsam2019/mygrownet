# Phase 4: Presentation Layer - Command Reference

**Last Updated:** December 1, 2025  
**Status:** Ready to Execute

---

## Overview

Phase 4 focuses on building the Presentation Layer - controllers, middleware, routes, form requests, and Vue components. This layer connects the Application layer to the user interface.

---

## Quick Start Commands

### 1. Create Controllers

```bash
# Create Home Hub controller
php artisan make:controller HomeHubController

# Create Module Subscription controller
php artisan make:controller ModuleSubscriptionController --resource

# Create Module controller
php artisan make:controller ModuleController
```

### 2. Create Middleware

```bash
# Create module access middleware
php artisan make:middleware CheckModuleAccess

# Create account type middleware
php artisan make:middleware CheckAccountType
```

### 3. Create Form Requests

```bash
# Create subscription request
php artisan make:request SubscribeToModuleRequest

# Create cancellation request
php artisan make:request CancelSubscriptionRequest

# Create upgrade request
php artisan make:request UpgradeSubscriptionRequest
```

### 4. Create Inertia Pages

```bash
# No artisan command - create manually
# Location: resources/js/Pages/HomeHub/
# Location: resources/js/Pages/Module/
```

---

## File Creation Checklist

### Controllers (app/Presentation/Http/Controllers/)
- [ ] `HomeHubController.php` - Module marketplace
- [ ] `ModuleSubscriptionController.php` - Subscription management
- [ ] `ModuleController.php` - Module-specific operations

### Middleware (app/Presentation/Http/Middleware/)
- [ ] `CheckModuleAccess.php` - Route protection
- [ ] `CheckAccountType.php` - Account type verification

### Form Requests (app/Presentation/Http/Requests/)
- [ ] `SubscribeToModuleRequest.php`
- [ ] `CancelSubscriptionRequest.php`
- [ ] `UpgradeSubscriptionRequest.php`

### Routes (routes/)
- [ ] Add Home Hub routes to `web.php`
- [ ] Add Module routes to `web.php`
- [ ] Add Subscription routes to `web.php`

### Vue Pages (resources/js/Pages/)
- [ ] `HomeHub/Index.vue` - Main hub page
- [ ] `Module/Show.vue` - Module detail page

### Vue Components (resources/js/Components/)
- [ ] `HomeHub/ModuleTile.vue` - Module card
- [ ] `HomeHub/SubscriptionModal.vue` - Subscription flow
- [ ] `Module/Header.vue` - Module header
- [ ] `Module/Navigation.vue` - Module navigation

---

## Controller Templates

### HomeHubController

```php
<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\GetUserModulesUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeHubController extends Controller
{
    public function __construct(
        private GetUserModulesUseCase $getUserModulesUseCase
    ) {}

    /**
     * Display the Home Hub (module marketplace)
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get all modules with access status
        $modules = $this->getUserModulesUseCase->execute($user);

        return Inertia::render('HomeHub/Index', [
            'modules' => array_map(fn($dto) => $dto->toArray(), $modules),
            'accountType' => $user->account_type,
        ]);
    }
}
```

### ModuleSubscriptionController

```php
<?php

namespace App\Presentation\Http\Controllers;

use App\Application\UseCases\Module\SubscribeToModuleUseCase;
use App\Application\UseCases\Module\CancelSubscriptionUseCase;
use App\Application\UseCases\Module\UpgradeSubscriptionUseCase;
use App\Presentation\Http\Requests\SubscribeToModuleRequest;
use App\Presentation\Http\Requests\CancelSubscriptionRequest;
use App\Presentation\Http\Requests\UpgradeSubscriptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleSubscriptionController extends Controller
{
    public function __construct(
        private SubscribeToModuleUseCase $subscribeUseCase,
        private CancelSubscriptionUseCase $cancelUseCase,
        private UpgradeSubscriptionUseCase $upgradeUseCase
    ) {}

    /**
     * Subscribe to a module
     */
    public function store(SubscribeToModuleRequest $request): RedirectResponse
    {
        try {
            $subscriptionDTO = $this->subscribeUseCase->execute(
                userId: $request->user()->id,
                moduleId: $request->validated('module_id'),
                tier: $request->validated('tier'),
                amount: $request->validated('amount'),
                currency: $request->validated('currency', 'ZMW'),
                billingCycle: $request->validated('billing_cycle', 'monthly')
            );

            return redirect()
                ->route('home-hub.index')
                ->with('success', 'Successfully subscribed to module!');
                
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a subscription
     */
    public function destroy(CancelSubscriptionRequest $request, int $subscriptionId): RedirectResponse
    {
        try {
            $this->cancelUseCase->execute(
                subscriptionId: $subscriptionId,
                userId: $request->user()->id,
                immediately: $request->validated('immediately', false)
            );

            return back()->with('success', 'Subscription cancelled successfully.');
            
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Upgrade a subscription
     */
    public function upgrade(UpgradeSubscriptionRequest $request, int $subscriptionId): RedirectResponse
    {
        try {
            $subscriptionDTO = $this->upgradeUseCase->execute(
                subscriptionId: $subscriptionId,
                userId: $request->user()->id,
                newTier: $request->validated('new_tier'),
                additionalAmount: $request->validated('additional_amount')
            );

            return back()->with('success', 'Subscription upgraded successfully!');
            
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
```

---

## Middleware Templates

### CheckModuleAccess

```php
<?php

namespace App\Presentation\Http\Middleware;

use App\Application\UseCases\Module\CheckModuleAccessUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleAccess
{
    public function __construct(
        private CheckModuleAccessUseCase $checkAccessUseCase
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleId): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $accessDTO = $this->checkAccessUseCase->execute($user, $moduleId);

        if (!$accessDTO->hasAccess) {
            return redirect()
                ->route('home-hub.index')
                ->with('error', $accessDTO->reason ?? 'You do not have access to this module.');
        }

        // Share access info with the view
        $request->attributes->set('moduleAccess', $accessDTO);

        return $next($request);
    }
}
```

### CheckAccountType

```php
<?php

namespace App\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountType
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$accountTypes): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has any of the required account types
        $userAccountTypes = is_array($user->account_types) 
            ? $user->account_types 
            : [$user->account_type];

        $hasAccess = !empty(array_intersect($accountTypes, $userAccountTypes));

        if (!$hasAccess) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Your account type does not have access to this feature.');
        }

        return $next($request);
    }
}
```

---

## Form Request Templates

### SubscribeToModuleRequest

```php
<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeToModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'module_id' => ['required', 'string', 'exists:modules,id'],
            'tier' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['sometimes', 'string', 'in:ZMW,USD'],
            'billing_cycle' => ['sometimes', 'string', 'in:monthly,quarterly,annual'],
        ];
    }

    public function messages(): array
    {
        return [
            'module_id.required' => 'Please select a module.',
            'module_id.exists' => 'The selected module does not exist.',
            'tier.required' => 'Please select a subscription tier.',
            'amount.required' => 'Subscription amount is required.',
            'amount.min' => 'Subscription amount must be positive.',
        ];
    }
}
```

### CancelSubscriptionRequest

```php
<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'immediately' => ['sometimes', 'boolean'],
        ];
    }
}
```

### UpgradeSubscriptionRequest

```php
<?php

namespace App\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpgradeSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_tier' => ['required', 'string'],
            'additional_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_tier.required' => 'Please select a new tier.',
            'additional_amount.required' => 'Additional payment amount is required.',
            'additional_amount.min' => 'Additional amount must be positive.',
        ];
    }
}
```

---

## Routes Template

Add to `routes/web.php`:

```php
use App\Presentation\Http\Controllers\HomeHubController;
use App\Presentation\Http\Controllers\ModuleSubscriptionController;
use App\Presentation\Http\Controllers\ModuleController;
use App\Presentation\Http\Middleware\CheckModuleAccess;

// Home Hub Routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Home Hub (Module Marketplace)
    Route::get('/home-hub', [HomeHubController::class, 'index'])
        ->name('home-hub.index');

    // Module Subscriptions
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::post('/', [ModuleSubscriptionController::class, 'store'])
            ->name('store');
        Route::delete('/{subscription}', [ModuleSubscriptionController::class, 'destroy'])
            ->name('destroy');
        Route::post('/{subscription}/upgrade', [ModuleSubscriptionController::class, 'upgrade'])
            ->name('upgrade');
    });

    // Module Routes (with access control)
    Route::prefix('modules/{moduleId}')->name('modules.')->group(function () {
        Route::middleware([CheckModuleAccess::class . ':' . '{moduleId}'])->group(function () {
            Route::get('/', [ModuleController::class, 'show'])
                ->name('show');
        });
    });
});
```

---

## Vue Component Templates

### HomeHub/Index.vue

```vue
<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ModuleTile from '@/Components/HomeHub/ModuleTile.vue';

interface Module {
  id: string;
  name: string;
  slug: string;
  category: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  thumbnail: string | null;
  has_access: boolean;
  is_subscribed: boolean;
  subscription_status: string | null;
  subscription_tier: string | null;
  requires_subscription: boolean;
  subscription_tiers: any[] | null;
  primary_route: string;
  is_pwa: boolean;
  status: string;
}

interface Props {
  modules: Module[];
  accountType: string;
}

const props = defineProps<Props>();

// Group modules by category
const modulesByCategory = props.modules.reduce((acc, module) => {
  if (!acc[module.category]) {
    acc[module.category] = [];
  }
  acc[module.category].push(module);
  return acc;
}, {} as Record<string, Module[]>);
</script>

<template>
  <Head title="Home Hub" />

  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Home Hub</h1>
          <p class="mt-2 text-gray-600">
            Discover and manage your MyGrowNet modules
          </p>
        </div>

        <!-- Modules by Category -->
        <div v-for="(modules, category) in modulesByCategory" :key="category" class="mb-12">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4 capitalize">
            {{ category.replace('_', ' ') }}
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <ModuleTile
              v-for="module in modules"
              :key="module.id"
              :module="module"
            />
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="props.modules.length === 0" class="text-center py-12">
          <p class="text-gray-500">No modules available for your account type.</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
```

### Components/HomeHub/ModuleTile.vue

```vue
<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Module {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  icon: string | null;
  color: string | null;
  thumbnail: string | null;
  has_access: boolean;
  is_subscribed: boolean;
  subscription_status: string | null;
  requires_subscription: boolean;
  primary_route: string;
  status: string;
}

interface Props {
  module: Module;
}

const props = defineProps<Props>();

const statusBadge = computed(() => {
  if (props.module.has_access) {
    return { text: 'Active', class: 'bg-green-100 text-green-800' };
  }
  if (props.module.status === 'beta') {
    return { text: 'Beta', class: 'bg-blue-100 text-blue-800' };
  }
  if (props.module.status === 'coming_soon') {
    return { text: 'Coming Soon', class: 'bg-gray-100 text-gray-800' };
  }
  return { text: 'Available', class: 'bg-indigo-100 text-indigo-800' };
});

const handleClick = () => {
  if (props.module.has_access) {
    router.visit(props.module.primary_route);
  } else {
    // Open subscription modal
    // TODO: Implement subscription modal
  }
};
</script>

<template>
  <div
    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer"
    @click="handleClick"
  >
    <!-- Module Icon/Thumbnail -->
    <div class="flex items-start justify-between mb-4">
      <div
        class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl font-bold"
        :style="{ backgroundColor: module.color || '#6366f1' }"
      >
        {{ module.icon || module.name.charAt(0) }}
      </div>
      
      <span
        class="px-2 py-1 text-xs font-medium rounded-full"
        :class="statusBadge.class"
      >
        {{ statusBadge.text }}
      </span>
    </div>

    <!-- Module Info -->
    <h3 class="text-lg font-semibold text-gray-900 mb-2">
      {{ module.name }}
    </h3>
    
    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
      {{ module.description || 'No description available' }}
    </p>

    <!-- Action Button -->
    <button
      class="w-full py-2 px-4 rounded-lg font-medium transition-colors"
      :class="module.has_access 
        ? 'bg-blue-600 text-white hover:bg-blue-700' 
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
    >
      {{ module.has_access ? 'Open' : 'Subscribe' }}
    </button>
  </div>
</template>
```

---

## Testing Commands

```bash
# Test routes
php artisan route:list | grep home-hub
php artisan route:list | grep modules

# Test middleware
php artisan tinker
>>> $user = \App\Models\User::find(1);
>>> $request = new \Illuminate\Http\Request();
>>> $request->setUserResolver(fn() => $user);

# Run feature tests
php artisan test --filter HomeHub
php artisan test --filter ModuleSubscription
```

---

## Directory Structure

```
app/Presentation/Http/
├── Controllers/
│   ├── HomeHubController.php
│   ├── ModuleSubscriptionController.php
│   └── ModuleController.php
├── Middleware/
│   ├── CheckModuleAccess.php
│   └── CheckAccountType.php
└── Requests/
    ├── SubscribeToModuleRequest.php
    ├── CancelSubscriptionRequest.php
    └── UpgradeSubscriptionRequest.php

resources/js/
├── Pages/
│   ├── HomeHub/
│   │   └── Index.vue
│   └── Module/
│       └── Show.vue
└── Components/
    ├── HomeHub/
    │   ├── ModuleTile.vue
    │   └── SubscriptionModal.vue
    └── Module/
        ├── Header.vue
        └── Navigation.vue

routes/
└── web.php (updated)
```

---

## Implementation Order

### Step 1: Controllers & Middleware
1. Create `HomeHubController`
2. Create `ModuleSubscriptionController`
3. Create `CheckModuleAccess` middleware
4. Register middleware in `Kernel.php`

### Step 2: Form Requests
1. Create `SubscribeToModuleRequest`
2. Create `CancelSubscriptionRequest`
3. Create `UpgradeSubscriptionRequest`

### Step 3: Routes
1. Add Home Hub routes
2. Add subscription routes
3. Add module routes with middleware
4. Test route registration

### Step 4: Vue Components
1. Create `HomeHub/Index.vue` page
2. Create `ModuleTile.vue` component
3. Test basic rendering
4. Add subscription modal (optional)

### Step 5: Integration Testing
1. Test full subscription flow
2. Test access control
3. Test UI interactions

---

## Key Principles

### Controllers
- Thin controllers - delegate to use cases
- Return Inertia responses for pages
- Handle errors gracefully
- Use form requests for validation

### Middleware
- Single responsibility
- Clear error messages
- Proper redirects
- Share data with views when needed

### Form Requests
- Centralize validation logic
- Custom error messages
- Authorization checks

### Vue Components
- TypeScript for type safety
- Composition API
- Reusable components
- Proper prop typing

---

**Ready to start Phase 4!** 🚀

**Estimated Time:** 2-3 days  
**Team:** Full-stack developers  
**Goal:** Build presentation layer and connect to application layer

