# MyGrowNet Dashboard Links Audit & Fix Plan

**Date:** October 20, 2025  
**Purpose:** Ensure all dashboard links work before member registration

---

## Current Status

### ✅ Working Routes
- `mygrownet.dashboard` - Main dashboard (working)
- `mygrownet.api.*` - All API endpoints (working)

### ❌ Broken Links (Commented Out Routes)

The following routes are referenced in the dashboard but are commented out in `routes/web.php`:

1. **`mygrownet.membership.upgrade`** - Membership upgrade page
2. **`mygrownet.assets.index`** - Asset portfolio page
3. **`mygrownet.rewards.index`** - Available rewards page
4. **`mygrownet.projects.index`** - Community projects page
5. **`mygrownet.network.index`** - Network overview page
6. **`mygrownet.referrals.index`** - Referrals management page
7. **`mygrownet.commissions.index`** - Commission tracking page
8. **`mygrownet.learning.index`** - Learning hub page

---

## Fix Strategy

### Option 1: Quick Fix (Recommended for Launch)
Create simple placeholder pages for all missing routes with "Coming Soon" messages.

**Pros:**
- Fast implementation (30 minutes)
- No broken links
- Members can register immediately
- Can build full features later

**Cons:**
- Limited functionality initially

### Option 2: Full Implementation
Build complete controllers and pages for all features.

**Pros:**
- Full functionality from day 1

**Cons:**
- Takes 2-3 weeks
- Delays member registration

---

## Recommended: Quick Fix Implementation

### Step 1: Create Placeholder Controller

Create `app/Http/Controllers/MyGrowNet/PlaceholderController.php`:

```php
<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PlaceholderController extends Controller
{
    public function comingSoon(string $feature)
    {
        return Inertia::render('MyGrowNet/ComingSoon', [
            'feature' => ucwords(str_replace('-', ' ', $feature)),
            'message' => $this->getFeatureMessage($feature)
        ]);
    }
    
    private function getFeatureMessage(string $feature): string
    {
        return match($feature) {
            'membership-upgrade' => 'Upgrade your membership tier to unlock more benefits and higher commissions.',
            'assets' => 'Track your physical rewards and income-generating assets.',
            'rewards' => 'View available rewards and track your progress towards earning them.',
            'projects' => 'Invest in community projects and earn quarterly returns.',
            'network' => 'Visualize your network structure and team growth.',
            'referrals' => 'Manage your referrals and track their activity.',
            'commissions' => 'View detailed commission breakdowns and payment history.',
            'learning' => 'Access educational content, courses, and certifications.',
            default => 'This feature is coming soon!'
        };
    }
}
```

### Step 2: Create Coming Soon Page

Create `resources/js/Pages/MyGrowNet/ComingSoon.vue`:

```vue
<template>
  <AppLayout :title="`${feature} - Coming Soon`">
    <div class="py-12">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg p-8 text-center">
          <!-- Icon -->
          <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-6">
            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          
          <!-- Title -->
          <h1 class="text-3xl font-bold text-gray-900 mb-4">
            {{ feature }}
          </h1>
          
          <!-- Message -->
          <p class="text-lg text-gray-600 mb-8">
            {{ message }}
          </p>
          
          <!-- Coming Soon Badge -->
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-100 text-yellow-800 font-medium mb-8">
            <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            Coming Soon
          </div>
          
          <!-- Description -->
          <div class="bg-blue-50 rounded-lg p-6 mb-8">
            <h3 class="text-sm font-medium text-blue-900 mb-2">What to expect:</h3>
            <p class="text-sm text-blue-700">
              We're working hard to bring you this feature. In the meantime, you can continue building your network and earning commissions through the dashboard.
            </p>
          </div>
          
          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <Link
              :href="route('mygrownet.dashboard')"
              class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              Back to Dashboard
            </Link>
            
            <a
              href="mailto:support@mygrownet.com?subject=Feature Request: {{ feature }}"
              class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Contact Support
            </a>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps<{
  feature: string;
  message: string;
}>();
</script>
```

### Step 3: Update Routes

Uncomment and update routes in `routes/web.php`:

```php
Route::prefix('mygrownet')->name('mygrownet.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\MyGrowNet\DashboardController::class, 'index'])->name('dashboard');
    
    // Placeholder routes (coming soon pages)
    Route::get('/membership/upgrade', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('membership-upgrade'))->name('membership.upgrade');
    Route::get('/assets', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('assets'))->name('assets.index');
    Route::get('/rewards', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('rewards'))->name('rewards.index');
    Route::get('/projects', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('projects'))->name('projects.index');
    Route::get('/network', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('network'))->name('network.index');
    Route::get('/referrals', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('referrals'))->name('referrals.index');
    Route::get('/commissions', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('commissions'))->name('commissions.index');
    Route::get('/learning', fn() => app(App\Http\Controllers\MyGrowNet\PlaceholderController::class)->comingSoon('learning'))->name('learning.index');
    
    // API Routes (already working)
    Route::prefix('api')->name('api.')->group(function () {
        // ... existing API routes
    });
});
```

---

## Testing Checklist

After implementing the fix, test each link:

- [ ] Click "Upgrade Tier" button → Should show "Coming Soon" page
- [ ] Click "View all assets" link → Should show "Coming Soon" page
- [ ] Click "View Available Rewards" button → Should show "Coming Soon" page
- [ ] Click "View all projects" link → Should show "Coming Soon" page
- [ ] Click "View network" link → Should show "Coming Soon" page
- [ ] Click "Referrals" quick action → Should show "Coming Soon" page
- [ ] Click "Commissions" quick action → Should show "Coming Soon" page
- [ ] Click "Learning" quick action → Should show "Coming Soon" page
- [ ] All "Coming Soon" pages have "Back to Dashboard" button → Should work
- [ ] Dashboard loads without errors → Should work

---

## Future Implementation Priority

Once members are registered, implement features in this order:

### Phase 1 (Week 1-2): Critical Features
1. **Referrals Management** - Members need to track their referrals
2. **Network Visualization** - See their team structure
3. **Commissions Tracking** - View earnings breakdown

### Phase 2 (Week 3-4): Important Features
4. **Membership Upgrade** - Allow tier upgrades
5. **Learning Hub** - Access to courses and content
6. **Projects** - Community investment opportunities

### Phase 3 (Week 5-6): Nice-to-Have Features
7. **Assets Tracking** - Physical rewards management
8. **Rewards Catalog** - Browse available rewards

---

## Alternative: Disable Links Temporarily

If you prefer to hide features instead of showing "Coming Soon":

### Option A: Hide Buttons/Links

Update dashboard to conditionally show links:

```vue
<Link
  v-if="false"  <!-- Temporarily hide -->
  :href="route('mygrownet.membership.upgrade')"
  class="..."
>
  Upgrade Tier
</Link>
```

### Option B: Show Disabled State

```vue
<button
  disabled
  class="... opacity-50 cursor-not-allowed"
  title="Coming soon"
>
  Upgrade Tier (Coming Soon)
</button>
```

---

## Recommendation

**Use the Quick Fix (Coming Soon pages)** because:

1. ✅ No broken links - Better user experience
2. ✅ Shows transparency - Members know features are planned
3. ✅ Allows immediate registration - Don't delay launch
4. ✅ Easy to implement - 30 minutes of work
5. ✅ Professional appearance - Better than hidden/disabled buttons
6. ✅ Collects feedback - Members can email about features they want

---

## Implementation Time Estimate

- **Quick Fix**: 30 minutes
- **Testing**: 15 minutes
- **Total**: 45 minutes

**You can start registering members today!**

---

*Next: Implement the quick fix and test all links*
