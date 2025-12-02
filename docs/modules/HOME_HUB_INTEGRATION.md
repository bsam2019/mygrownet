# Home Hub Integration Guide

**Last Updated:** December 1, 2025  
**Purpose:** Integrate Home Hub into existing MyGrowNet dashboard

---

## Overview

This guide explains how to integrate the new Home Hub (module marketplace) into the existing MyGrowNet dashboard navigation and user experience.

---

## Integration Points

### 1. Main Navigation

Add Home Hub link to the main navigation menu.

**Location:** `resources/js/components/NavMain.vue` or similar

```vue
<template>
  <nav>
    <!-- Existing navigation items -->
    <NavLink href="/dashboard" :active="route().current('dashboard')">
      Dashboard
    </NavLink>
    
    <!-- Add Home Hub link -->
    <NavLink href="/home-hub" :active="route().current('home-hub.index')">
      <template #icon>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
      </template>
      Home Hub
    </NavLink>
    
    <!-- Other navigation items -->
  </nav>
</template>
```

### 2. Dashboard Widget

Add a Home Hub widget to the main dashboard.

**Location:** `resources/js/Pages/Dashboard/Index.vue` or similar

```vue
<template>
  <div class="dashboard">
    <!-- Existing dashboard content -->
    
    <!-- Home Hub Widget -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">My Modules</h3>
        <Link href="/home-hub" class="text-sm text-blue-600 hover:text-blue-700">
          View All →
        </Link>
      </div>
      
      <div class="grid grid-cols-2 gap-4">
        <div v-for="module in activeModules" :key="module.id" 
             class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 cursor-pointer"
             @click="openModule(module)">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white"
                 :style="{ backgroundColor: module.color }">
              {{ module.icon || module.name.charAt(0) }}
            </div>
            <div>
              <div class="font-medium text-gray-900">{{ module.name }}</div>
              <div class="text-xs text-gray-500">{{ module.subscription_tier }}</div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="activeModules.length === 0" class="text-center py-8 text-gray-500">
        <p>No active modules</p>
        <Link href="/home-hub" class="text-blue-600 hover:text-blue-700 text-sm mt-2 inline-block">
          Browse Modules
        </Link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';

interface Props {
  activeModules: Array<{
    id: string;
    name: string;
    icon: string | null;
    color: string | null;
    subscription_tier: string;
    primary_route: string;
  }>;
}

const props = defineProps<Props>();

const openModule = (module: any) => {
  router.visit(module.primary_route);
};
</script>
```

### 3. Dashboard Controller Update

Update the dashboard controller to pass active modules.

**Location:** `app/Http/Controllers/DashboardController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Application\UseCases\Module\GetUserModulesUseCase;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private GetUserModulesUseCase $getUserModulesUseCase
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        
        // Get user's active modules
        $allModules = $this->getUserModulesUseCase->execute($user);
        $activeModules = array_filter($allModules, fn($m) => $m->hasAccess);
        
        return Inertia::render('Dashboard/Index', [
            // ... existing dashboard data ...
            'activeModules' => array_map(fn($dto) => [
                'id' => $dto->id,
                'name' => $dto->name,
                'icon' => $dto->icon,
                'color' => $dto->color,
                'subscription_tier' => $dto->subscriptionTier,
                'primary_route' => $dto->primaryRoute,
            ], array_values($activeModules)),
        ]);
    }
}
```

### 4. User Menu Integration

Add Home Hub to user dropdown menu.

**Location:** `resources/js/components/NavUser.vue` or similar

```vue
<template>
  <DropdownMenu>
    <DropdownMenuTrigger>
      <!-- User avatar/name -->
    </DropdownMenuTrigger>
    
    <DropdownMenuContent>
      <!-- Existing menu items -->
      <DropdownMenuItem as-child>
        <Link href="/dashboard">Dashboard</Link>
      </DropdownMenuItem>
      
      <!-- Add Home Hub -->
      <DropdownMenuItem as-child>
        <Link href="/home-hub">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z..." />
          </svg>
          Home Hub
        </Link>
      </DropdownMenuItem>
      
      <!-- Other menu items -->
    </DropdownMenuContent>
  </DropdownMenu>
</template>
```

---

## Quick Access Buttons

### Add to Main Dashboard

```vue
<!-- Quick Access Section -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
  <!-- Existing quick access buttons -->
  
  <!-- Home Hub Quick Access -->
  <Link href="/home-hub" 
        class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 hover:shadow-lg transition-shadow">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold mb-1">Home Hub</h3>
        <p class="text-blue-100 text-sm">Explore modules</p>
      </div>
      <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z..." />
      </svg>
    </div>
  </Link>
</div>
```

---

## Breadcrumb Integration

Add Home Hub to breadcrumb navigation.

```vue
<template>
  <nav class="flex" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2">
      <li>
        <Link href="/dashboard" class="text-gray-500 hover:text-gray-700">
          Dashboard
        </Link>
      </li>
      <li>
        <span class="text-gray-400">/</span>
      </li>
      <li>
        <Link href="/home-hub" class="text-gray-500 hover:text-gray-700">
          Home Hub
        </Link>
      </li>
      <li v-if="currentModule">
        <span class="text-gray-400">/</span>
      </li>
      <li v-if="currentModule">
        <span class="text-gray-900">{{ currentModule.name }}</span>
      </li>
    </ol>
  </nav>
</template>
```

---

## Mobile Navigation

Add Home Hub to mobile navigation menu.

```vue
<template>
  <div class="mobile-nav">
    <!-- Existing mobile nav items -->
    
    <!-- Home Hub -->
    <Link href="/home-hub" 
          class="flex items-center gap-3 px-4 py-3 hover:bg-gray-100"
          :class="{ 'bg-blue-50 text-blue-600': route().current('home-hub.index') }">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z..." />
      </svg>
      <span>Home Hub</span>
    </Link>
  </div>
</template>
```

---

## Notification Integration

Add notifications for module-related events.

```php
// In ModuleSubscriptionController or event listener

use Illuminate\Support\Facades\Notification;
use App\Notifications\ModuleSubscriptionCreated;

// After successful subscription
Notification::send($user, new ModuleSubscriptionCreated($subscription));
```

**Notification Class:**

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ModuleSubscriptionCreated extends Notification
{
    public function __construct(
        private $subscription
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Module Subscription Activated')
            ->line('Your subscription to ' . $this->subscription->module_name . ' has been activated!')
            ->action('View Module', url('/modules/' . $this->subscription->module_id))
            ->line('Thank you for using MyGrowNet!');
    }

    public function toArray($notifiable): array
    {
        return [
            'module_id' => $this->subscription->module_id,
            'module_name' => $this->subscription->module_name,
            'tier' => $this->subscription->tier,
            'message' => 'Your subscription to ' . $this->subscription->module_name . ' is now active!',
        ];
    }
}
```

---

## Search Integration

Add modules to global search.

```vue
<template>
  <div class="search-results">
    <!-- Existing search results -->
    
    <!-- Module Results -->
    <div v-if="moduleResults.length > 0" class="search-section">
      <h3 class="text-sm font-semibold text-gray-500 mb-2">Modules</h3>
      <div v-for="module in moduleResults" :key="module.id">
        <Link :href="`/modules/${module.id}`" 
              class="flex items-center gap-3 p-2 hover:bg-gray-100 rounded">
          <div class="w-8 h-8 rounded flex items-center justify-center text-white text-sm"
               :style="{ backgroundColor: module.color }">
            {{ module.icon || module.name.charAt(0) }}
          </div>
          <div>
            <div class="font-medium">{{ module.name }}</div>
            <div class="text-xs text-gray-500">{{ module.description }}</div>
          </div>
        </Link>
      </div>
    </div>
  </div>
</template>
```

---

## Analytics Integration

Track module usage in analytics.

```typescript
// In module access or subscription events

import { trackEvent } from '@/utils/analytics';

// Track module view
trackEvent('module_viewed', {
  module_id: module.id,
  module_name: module.name,
  user_id: user.id,
});

// Track subscription
trackEvent('module_subscribed', {
  module_id: module.id,
  tier: subscription.tier,
  amount: subscription.amount,
});
```

---

## Testing Integration

### Test Dashboard Integration

```php
php artisan tinker

// Test dashboard with modules
$controller = app(\App\Http\Controllers\DashboardController::class);
$user = \App\Models\User::find(1);
$request = new \Illuminate\Http\Request();
$request->setUserResolver(fn() => $user);
$response = $controller->index($request);
```

### Test Navigation

1. Login to application
2. Check main navigation for "Home Hub" link
3. Click Home Hub link
4. Verify page loads correctly
5. Check breadcrumbs
6. Test mobile navigation

---

## Deployment Checklist

- [ ] Add Home Hub to main navigation
- [ ] Update dashboard controller
- [ ] Add dashboard widget
- [ ] Update user menu
- [ ] Add mobile navigation
- [ ] Configure notifications
- [ ] Test all integration points
- [ ] Update user documentation

---

## User Documentation

### For End Users

**Accessing Home Hub:**
1. Login to MyGrowNet
2. Click "Home Hub" in the main navigation
3. Browse available modules
4. Click "Subscribe" to activate a module
5. Access your modules from the dashboard

**Managing Subscriptions:**
1. Go to Home Hub
2. Click on an active module
3. Find subscription management options
4. Cancel or upgrade as needed

---

## Support

### Common Issues

**Home Hub link not showing:**
- Check navigation component is updated
- Clear cache: `php artisan view:clear`
- Rebuild frontend: `npm run build`

**Modules not loading:**
- Check database is seeded
- Verify routes are registered
- Check user permissions

---

**Integration Status:** Ready for implementation ✅

