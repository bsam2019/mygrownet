# MyGrowNet Module System Architecture

**Last Updated:** December 1, 2025
**Status:** Design Phase

## Overview

MyGrowNet's modular architecture allows apps to function both as integrated modules within the platform and as standalone Progressive Web Apps (PWAs). This dual-mode approach provides flexibility for users while maintaining a unified backend.

**Critical:** The system integrates with MyGrowNet's account type system. See `../account-types/USER_TYPES_AND_ACCESS_MODEL.md` for complete details.

## Account Type Integration

### Overview
The module system uses account types to control access. Each module specifies which account types can access it.

### Account Types & Module Access

| Account Type | Can Access |
|--------------|------------|
| **MEMBER** | Core (MLM) + Purchased modules |
| **CLIENT** | Purchased modules only (NO Core/MLM) |
| **BUSINESS** | Business modules |
| **INVESTOR** | Investor portal |
| **EMPLOYEE** | Employee portal + Admin tools |

### Module Configuration with Account Types

Each module must specify which account types can access it:

```php
// config/modules.php
return [
    'core' => [
        'name' => 'MyGrowNet Core',
        'slug' => 'core',
        'account_types' => ['member'], // ← MEMBER only!
        'price' => 0, // Free for members
        'routes' => [
            'desktop' => '/dashboard',
            'mobile' => '/mobile-dashboard',
        ],
    ],
    
    'wedding-planner' => [
        'name' => 'Wedding Planner',
        'slug' => 'wedding-planner',
        'account_types' => ['member', 'client'], // ← Both can access
        'price' => 50,
        'billing_cycle' => 'monthly',
    ],
    
    'sme-accounting' => [
        'name' => 'SME Accounting',
        'slug' => 'accounting',
        'account_types' => ['business'], // ← BUSINESS only
        'price' => 200,
        'billing_cycle' => 'monthly',
    ],
];
```

### Access Control Implementation

**Route Protection:**
```php
// Protect Core (MLM) routes - MEMBER only
Route::middleware(['auth', 'account.type:member'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index']);
});

// Protect module routes - Check account type + subscription
Route::middleware(['auth', 'module.access:wedding-planner'])->group(function () {
    Route::get('/modules/wedding-planner', [WeddingPlannerController::class, 'index']);
});

// Protect business routes - BUSINESS only
Route::middleware(['auth', 'account.type:business'])->group(function () {
    Route::get('/modules/accounting', [AccountingController::class, 'index']);
});
```

**Controller Check:**
```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Check if user has required account type
    if (!$user->hasAccountType(AccountType::MEMBER)) {
        abort(403, 'This module requires MEMBER account type.');
    }
    
    // Continue with module logic...
}
```

**Home Hub Filtering:**
```php
public function index(Request $request)
{
    $user = $request->user();
    
    // Filter modules by user's account types
    $modules = collect(config('modules'))
        ->filter(function ($module) use ($user) {
            $allowedTypes = $module['account_types'] ?? [];
            
            // Check if user has any of the allowed account types
            foreach ($user->account_types as $userType) {
                if (in_array($userType, $allowedTypes)) {
                    return true;
                }
            }
            
            return false;
        });
    
    return Inertia::render('HomeHub/Index', [
        'modules' => $modules,
    ]);
}
```

## Business Model

### Subscription Structure

#### Personal Apps (Individual Users)
- **Free Tier**: Access to Home Hub + 1 basic app
- **Growth Tier (K50/month)**: 3-5 personal apps
- **Premium Tier (K100/month)**: All personal apps + priority support

#### SME Apps (Business Users)
- **Starter (K200/month)**: 2 business tools + 5 users
- **Professional (K500/month)**: 5 business tools + 20 users  
- **Enterprise (K1000/month)**: All tools + unlimited users + API access

### Revenue Streams
1. Monthly/annual subscriptions
2. Per-user pricing for SME apps
3. Premium features and add-ons
4. API access for enterprise
5. White-label licensing

## Technical Architecture

### Dual-Mode Operation

```
┌─────────────────────────────────────────────────────────┐
│                    MyGrowNet Platform                    │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  ┌──────────────┐         ┌──────────────┐              │
│  │   Home Hub   │────────▶│   Modules    │              │
│  │  (Dashboard) │         │  (Integrated)│              │
│  └──────────────┘         └──────────────┘              │
│         │                         │                      │
│         │                         │                      │
│         ▼                         ▼                      │
│  ┌─────────────────────────────────────┐                │
│  │      Shared Backend Services        │                │
│  │  • Auth • Subscriptions • Data      │                │
│  └─────────────────────────────────────┘                │
│                    │                                     │
└────────────────────┼─────────────────────────────────────┘
                     │
                     ▼
          ┌──────────────────────┐
          │  Standalone PWAs     │
          │  • Same backend      │
          │  • Isolated UI       │
          │  • Offline capable   │
          └──────────────────────┘
```

### Module Definition

```typescript
interface AppModule {
  // Identity
  id: string;                    // 'sme-accounting'
  name: string;                  // 'SME Accounting'
  slug: string;                  // 'accounting'
  category: 'personal' | 'sme';
  
  // Display
  icon: string;                  // Heroicon name
  color: string;                 // Tailwind color
  description: string;
  thumbnail?: string;
  
  // Access Control
  requiredSubscription: string[];  // ['sme-starter', 'sme-pro']
  requiredRole?: string[];         // ['business-owner', 'accountant']
  minUserLevel?: number;           // Minimum professional level
  
  // Routing
  routes: {
    integrated: string;          // '/modules/accounting'
    standalone: string;          // '/apps/accounting'
  };
  
  // PWA Configuration
  pwa: {
    enabled: boolean;
    manifestUrl: string;         // '/modules/accounting/manifest.json'
    serviceWorkerUrl: string;    // '/modules/accounting/sw.js'
    installable: boolean;
    offlineCapable: boolean;
  };
  
  // Features
  features: {
    dataSync: boolean;           // Sync with main platform
    notifications: boolean;      // Push notifications
    offline: boolean;            // Offline functionality
    multiUser: boolean;          // Multiple users per subscription
  };
  
  // Metadata
  version: string;
  lastUpdated: string;
  status: 'active' | 'beta' | 'coming-soon';
}
```

## Database Schema

### Module Management

```sql
-- Modules registry
CREATE TABLE modules (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    category ENUM('personal', 'sme') NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    color VARCHAR(50),
    thumbnail VARCHAR(255),
    
    -- Access control
    required_subscription JSON,  -- ['sme-starter', 'sme-pro']
    required_role JSON,
    min_user_level INT,
    
    -- Configuration
    routes JSON,
    pwa_config JSON,
    features JSON,
    
    -- Metadata
    version VARCHAR(20),
    status ENUM('active', 'beta', 'coming-soon') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User subscriptions to modules
CREATE TABLE module_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    subscription_tier VARCHAR(50) NOT NULL,
    
    -- Status
    status ENUM('active', 'trial', 'suspended', 'cancelled') DEFAULT 'active',
    
    -- Dates
    started_at TIMESTAMP NOT NULL,
    trial_ends_at TIMESTAMP,
    expires_at TIMESTAMP,
    cancelled_at TIMESTAMP,
    
    -- Billing
    auto_renew BOOLEAN DEFAULT true,
    billing_cycle ENUM('monthly', 'annual') DEFAULT 'monthly',
    amount DECIMAL(10, 2),
    currency VARCHAR(3) DEFAULT 'ZMW',
    
    -- Limits (for SME apps)
    user_limit INT,
    storage_limit_mb INT,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id),
    UNIQUE KEY unique_user_module (user_id, module_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
);

-- Module access tracking
CREATE TABLE module_access_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    
    -- Access details
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    access_type ENUM('web', 'pwa', 'api') NOT NULL,
    session_duration INT,  -- seconds
    
    -- Context
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id),
    INDEX idx_user_module (user_id, module_id),
    INDEX idx_accessed_at (accessed_at)
);

-- User-specific module settings
CREATE TABLE user_module_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    
    -- Settings
    settings JSON,  -- Module-specific configuration
    
    -- PWA tracking
    pwa_installed BOOLEAN DEFAULT false,
    pwa_installed_at TIMESTAMP,
    last_pwa_sync TIMESTAMP,
    
    -- Preferences
    notifications_enabled BOOLEAN DEFAULT true,
    offline_mode_enabled BOOLEAN DEFAULT false,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id),
    UNIQUE KEY unique_user_module_settings (user_id, module_id)
);

-- SME team access (for multi-user SME apps)
CREATE TABLE module_team_access (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    module_id VARCHAR(50) NOT NULL,
    
    -- Role within module
    role VARCHAR(50) NOT NULL,  -- 'owner', 'admin', 'user', 'viewer'
    permissions JSON,
    
    -- Status
    status ENUM('active', 'suspended') DEFAULT 'active',
    invited_by BIGINT,
    invited_at TIMESTAMP,
    accepted_at TIMESTAMP,
    
    FOREIGN KEY (subscription_id) REFERENCES module_subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES modules(id),
    FOREIGN KEY (invited_by) REFERENCES users(id),
    UNIQUE KEY unique_subscription_user (subscription_id, user_id)
);
```

## Implementation Phases

### Phase 1: Core Module System (Week 1-2)
- [ ] Create module registry and database schema
- [ ] Implement ModuleService for access control
- [ ] Build Home Hub with module tiles
- [ ] Create CheckModuleAccess middleware
- [ ] Implement subscription management

### Phase 2: Integrated Modules (Week 3-4)
- [ ] Refactor existing SME Accounting as module
- [ ] Create module layout wrapper
- [ ] Implement module navigation
- [ ] Add module-specific settings
- [ ] Build subscription upgrade flows

### Phase 3: Standalone PWA Support (Week 5-6)
- [ ] Create PWA manifest generator
- [ ] Build standalone entry points
- [ ] Implement service workers per module
- [ ] Add install prompts
- [ ] Create offline data sync

### Phase 4: Additional Modules (Week 7-8)
- [ ] Personal Finance Manager
- [ ] Inventory Management
- [ ] CRM System
- [ ] Task Management
- [ ] Goal Tracking

## Module Access Control

### Middleware Implementation

```php
// app/Http/Middleware/CheckModuleAccess.php
class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $moduleId)
    {
        $user = $request->user();
        
        // Check if user has active subscription
        if (!$this->moduleService->hasAccess($user, $moduleId)) {
            return $this->handleNoAccess($request, $moduleId);
        }
        
        // Log access
        $this->moduleService->logAccess($user, $moduleId, $request);
        
        return $next($request);
    }
    
    private function handleNoAccess(Request $request, string $moduleId)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Module access denied',
                'module' => $moduleId,
                'upgrade_url' => route('subscriptions.upgrade', $moduleId)
            ], 403);
        }
        
        return redirect()->route('home-hub')
            ->with('error', 'Please upgrade your subscription to access this module');
    }
}
```

### Route Protection

```php
// routes/web.php

// Integrated module routes
Route::middleware(['auth', 'module:sme-accounting'])->group(function () {
    Route::prefix('modules/accounting')->group(function () {
        Route::get('/', [AccountingController::class, 'index']);
        Route::get('/dashboard', [AccountingController::class, 'dashboard']);
        // ... other routes
    });
});

// Standalone PWA routes
Route::middleware(['auth', 'module:sme-accounting'])->group(function () {
    Route::prefix('apps/accounting')->group(function () {
        Route::get('/', [AccountingController::class, 'standalone']);
        Route::get('/manifest.json', [AccountingController::class, 'manifest']);
        Route::get('/sw.js', [AccountingController::class, 'serviceWorker']);
    });
});
```

## PWA Configuration

### Manifest Generation

```php
// app/Services/PWAManifestService.php
class PWAManifestService
{
    public function generateManifest(AppModule $module): array
    {
        return [
            'name' => $module->name,
            'short_name' => $module->slug,
            'description' => $module->description,
            'start_url' => "/apps/{$module->slug}",
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $this->getThemeColor($module->color),
            'icons' => $this->generateIcons($module),
            'categories' => [$module->category],
            'scope' => "/apps/{$module->slug}/",
        ];
    }
}
```

### Service Worker Template

```javascript
// resources/js/modules/service-worker-template.js
const CACHE_NAME = 'module-{{MODULE_ID}}-v{{VERSION}}';
const API_CACHE = 'module-{{MODULE_ID}}-api';

// Cache strategies per route type
const CACHE_STRATEGIES = {
  static: 'cache-first',
  api: 'network-first',
  dynamic: 'stale-while-revalidate'
};

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll([
        '/apps/{{MODULE_SLUG}}/',
        '/apps/{{MODULE_SLUG}}/offline',
        // Module-specific assets
      ]);
    })
  );
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // API requests - network first
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(networkFirst(request));
    return;
  }
  
  // Static assets - cache first
  if (isStaticAsset(url)) {
    event.respondWith(cacheFirst(request));
    return;
  }
  
  // Dynamic content - stale while revalidate
  event.respondWith(staleWhileRevalidate(request));
});
```

## Data Synchronization

### Sync Strategy

```typescript
// resources/js/modules/sync-manager.ts
class ModuleSyncManager {
  private db: IDBDatabase;
  private syncQueue: SyncOperation[] = [];
  
  async syncToServer(): Promise<void> {
    if (!navigator.onLine) {
      console.log('Offline - queuing sync');
      return;
    }
    
    const pendingOps = await this.getPendingOperations();
    
    for (const op of pendingOps) {
      try {
        await this.executeSyncOperation(op);
        await this.markSynced(op.id);
      } catch (error) {
        console.error('Sync failed:', error);
        await this.markFailed(op.id, error);
      }
    }
  }
  
  async syncFromServer(): Promise<void> {
    const lastSync = await this.getLastSyncTime();
    const updates = await api.getUpdates(lastSync);
    
    await this.applyUpdates(updates);
    await this.setLastSyncTime(Date.now());
  }
}
```

## User Experience

### Module Discovery (Home Hub)

```vue
<!-- resources/js/pages/HomeHub/Index.vue -->
<template>
  <div class="home-hub">
    <!-- Active Modules -->
    <section class="mb-8">
      <h2 class="text-2xl font-bold mb-4">Your Apps</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <ModuleTile
          v-for="module in activeModules"
          :key="module.id"
          :module="module"
          :subscription="getSubscription(module.id)"
          @launch="launchModule"
          @install-pwa="installPWA"
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

### Module Tile Component

```vue
<!-- resources/js/Components/HomeHub/ModuleTile.vue -->
<template>
  <div class="module-tile bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
    <!-- Icon and Name -->
    <div class="flex items-center gap-3 mb-3">
      <component :is="iconComponent" :class="`h-8 w-8 text-${module.color}-600`" />
      <h3 class="text-lg font-semibold">{{ module.name }}</h3>
    </div>
    
    <!-- Description -->
    <p class="text-gray-600 text-sm mb-4">{{ module.description }}</p>
    
    <!-- Actions -->
    <div class="flex gap-2">
      <button
        v-if="!locked"
        @click="$emit('launch', module)"
        class="btn-primary flex-1"
      >
        Open
      </button>
      
      <button
        v-if="!locked && module.pwa.installable && !pwaInstalled"
        @click="$emit('install-pwa', module)"
        class="btn-secondary"
        title="Install as app"
      >
        <ArrowDownTrayIcon class="h-5 w-5" />
      </button>
      
      <button
        v-if="locked"
        @click="$emit('subscribe', module)"
        class="btn-primary flex-1"
      >
        Subscribe
      </button>
    </div>
    
    <!-- Status badges -->
    <div class="mt-3 flex gap-2">
      <span v-if="module.status === 'beta'" class="badge-warning">Beta</span>
      <span v-if="pwaInstalled" class="badge-success">Installed</span>
    </div>
  </div>
</template>
```

## Subscription Management

### Upgrade Flow

```typescript
// Subscription upgrade process
1. User clicks "Subscribe" on locked module
2. Show subscription tier selection modal
3. Display pricing and features comparison
4. Process payment (MTN MoMo, Airtel Money)
5. Activate subscription immediately
6. Redirect to module or show success message
7. Send confirmation email/SMS
```

### Trial Period

```php
// app/Services/ModuleSubscriptionService.php
public function startTrial(User $user, string $moduleId): ModuleSubscription
{
    $module = $this->moduleRepository->find($moduleId);
    
    return ModuleSubscription::create([
        'user_id' => $user->id,
        'module_id' => $moduleId,
        'subscription_tier' => $module->getBasicTier(),
        'status' => 'trial',
        'started_at' => now(),
        'trial_ends_at' => now()->addDays(14),
        'expires_at' => now()->addDays(14),
        'amount' => 0,
    ]);
}
```

## Analytics & Monitoring

### Key Metrics

```sql
-- Module usage analytics
SELECT 
    m.name,
    COUNT(DISTINCT mal.user_id) as active_users,
    COUNT(mal.id) as total_sessions,
    AVG(mal.session_duration) as avg_session_duration,
    SUM(CASE WHEN mal.access_type = 'pwa' THEN 1 ELSE 0 END) as pwa_sessions
FROM modules m
LEFT JOIN module_access_logs mal ON m.id = mal.module_id
WHERE mal.accessed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY m.id;

-- Subscription revenue
SELECT 
    module_id,
    subscription_tier,
    COUNT(*) as active_subscriptions,
    SUM(amount) as monthly_revenue
FROM module_subscriptions
WHERE status = 'active'
GROUP BY module_id, subscription_tier;
```

## Security Considerations

### Access Control
- JWT tokens for API authentication
- Module-specific permissions
- Rate limiting per module
- Audit logging for sensitive operations

### Data Isolation
- Tenant-based data separation for SME apps
- Encrypted sensitive data
- Secure offline storage
- Regular security audits

## Next Steps

1. Review and approve architecture
2. Create database migrations
3. Implement core ModuleService
4. Build Home Hub UI
5. Refactor SME Accounting as first module
6. Test integrated and standalone modes
7. Deploy beta version
8. Gather user feedback
9. Iterate and add more modules

## References

- [HOME_HUB_IMPLEMENTATION.md](HOME_HUB_IMPLEMENTATION.md)
- [SME_BUSINESS_TOOLS_MVP_BRIEF.md](SME_BUSINESS_TOOLS_MVP_BRIEF.md)
- [MYGROWNET_PLATFORM_CONCEPT.md](docs/MYGROWNET_PLATFORM_CONCEPT.md)
