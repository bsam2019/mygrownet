# Workspace Implementation Plan

## Overview

This document translates the WORKSPACE_ARCHITECTURE.md into a concrete Laravel implementation plan. Follow the phase order strictly — each phase depends on the previous.

---

## Phase 1: Database Foundation

### Migration 1: Enhance `applications` table

```php
Schema::table('applications', function (Blueprint $table) {
    $table->string('category')->after('slug'); // business | consumer | shared
    $table->string('access_model')->after('category'); // customer | organization_members | both
    $table->string('context_support')->after('access_model'); // personal | organization | both
    $table->boolean('requires_organization_context')->default(false)->after('context_support');
    $table->boolean('subscription_required')->default(true)->after('requires_organization_context');
    $table->string('lifecycle')->default('active')->after('subscription_required'); // active | legacy | retired
    $table->string('operational_status')->default('online')->after('lifecycle'); // online | maintenance | disabled
    $table->foreignId('replacement_app_id')->nullable()->constrained('applications')->after('operational_status');
    $table->date('migration_deadline')->nullable()->after('replacement_app_id');
    $table->boolean('is_visible')->default(true)->after('migration_deadline');
});
```

### Migration 2: Create `organization_members` table

```php
Schema::create('organization_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('role'); // owner | admin | accountant | manager | employee | viewer
    $table->string('status')->default('active'); // active | invited | suspended
    $table->json('permissions')->nullable();
    $table->timestamps();
    $table->unique(['organization_id', 'user_id']);
});
```

### Migration 3: Create `organization_applications` table

```php
Schema::create('organization_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
    $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
    $table->foreignId('plan_id')->nullable()->constrained('plans');
    $table->string('status')->default('active'); // active | trial | suspended | cancelled
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
    $table->unique(['organization_id', 'application_id']);
});
```

### Migration 4: Create `application_installations` table

```php
Schema::create('application_installations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
    $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
    $table->string('status')->default('provisioning'); // provisioning | active | suspended
    $table->json('settings')->nullable();
    $table->timestamps();
    $table->unique(['organization_id', 'application_id']);
});
```

### Migration 5: Create `user_application_subscriptions` table

```php
Schema::create('user_application_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
    $table->foreignId('plan_id')->nullable()->constrained('plans');
    $table->string('status')->default('active'); // active | trial | cancelled
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
    $table->unique(['user_id', 'application_id']);
});
```

### Migration 6: Create `domains` table

```php
Schema::create('domains', function (Blueprint $table) {
    $table->id();
    $table->string('domain')->unique();
    $table->string('type'); // application | organization | platform
    $table->foreignId('application_id')->nullable()->constrained('applications');
    $table->foreignId('organization_id')->nullable()->constrained('organizations');
    $table->string('route_path')->default('/');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

### Migration 7: Create `organization_invitations` table

```php
Schema::create('organization_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
    $table->foreignId('invited_user_id')->nullable()->constrained('users');
    $table->string('email')->nullable();
    $table->string('role');
    $table->string('token')->unique();
    $table->timestamp('expires_at');
    $table->string('status')->default('pending'); // pending | accepted | expired | revoked
    $table->timestamps();
});
```

### Migration 8: Create `application_roles` table

```php
Schema::create('application_roles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
    $table->string('role_name');
    $table->json('permissions');
    $table->timestamps();
    $table->unique(['application_id', 'role_name']);
});
```

### Migration 9: Create `feature_flags` table

```php
Schema::create('feature_flags', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('application_id')->nullable()->constrained('applications');
    $table->boolean('enabled')->default(false);
    $table->json('rules')->nullable();
    $table->timestamps();
});
```

### Migration 10: Create `platform_roles` table

```php
Schema::create('platform_roles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('role'); // super_admin | support | finance | developer
    $table->json('permissions')->nullable();
    $table->timestamps();
    $table->unique(['user_id', 'role']);
});
```

### Migration 11: Create `activity_logs` table

```php
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('organization_id')->nullable()->constrained('organizations');
    $table->foreignId('application_id')->nullable()->constrained('applications');
    $table->string('action');
    $table->string('model_type')->nullable();
    $table->unsignedBigInteger('model_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address')->nullable();
    $table->timestamps();
    $table->index(['organization_id', 'application_id', 'created_at']);
});
```

### Migration 12: Create `user_profiles` table

```php
Schema::create('user_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->string('first_name')->nullable();
    $table->string('last_name')->nullable();
    $table->string('phone')->nullable();
    $table->string('avatar')->nullable();
    $table->string('country')->nullable();
    $table->string('timezone')->nullable();
    $table->string('language')->default('en');
    $table->timestamps();
});
```

---

## Phase 1: Services

### DomainResolverService

```php
class DomainResolverService
{
    public function resolve(string $host): DomainResolution
    {
        // 1. Exact match in domains table
        // 2. Registered application domain
        // 3. Registered organization domain
        // 4. Platform domain (mygrownet.com)
        // 5. 404

        $domain = Domain::where('domain', $host)->where('is_active', true)->first();

        if (!$domain) {
            throw new DomainNotFoundException("No registered domain: {$host}");
        }

        return new DomainResolution(
            type: $domain->type,
            application: $domain->application,
            organization: $domain->organization,
            route: $domain->route_path
        );
    }
}
```

### ContextResolverService

```php
class ContextResolverService
{
    public function resolve(?User $user, ?string $domainType, ?Organization $orgHint = null): WorkspaceContext
    {
        // Returns current context from session, domain, or user preferences
        // Priority: session > domain hint > user preference > default

        $context = new WorkspaceContext();

        if (!$user) {
            return $context->setType('guest');
        }

        // Check session first
        if ($cached = session('workspace_context')) {
            return $context->fromArray($cached);
        }

        // Domain-based resolution
        if ($domainType === 'organization' && $orgHint) {
            return $context->setOrganization($orgHint);
        }

        // User preference or default
        return $this->resolveDefault($user);
    }

    public function switchContext(User $user, string $type, ?int $organizationId = null): WorkspaceContext
    {
        // Validate switch
        if ($type === 'organization') {
            $membership = OrganizationMember::where('user_id', $user->id)
                ->where('organization_id', $organizationId)
                ->where('status', 'active')
                ->firstOrFail();
        }

        $context = new WorkspaceContext();
        $context->setType($type);
        if ($organizationId) {
            $context->setOrganization(Organization::findOrFail($organizationId));
        }

        session(['workspace_context' => $context->toArray()]);

        return $context;
    }
}
```

### ApplicationAccessService

```php
class ApplicationAccessService
{
    public function getAvailableApps(User $user, WorkspaceContext $context): Collection
    {
        if ($context->isPersonal()) {
            return $this->getPersonalApps($user);
        }

        return $this->getOrganizationApps($user, $context->organization);
    }

    protected function getPersonalApps(User $user): Collection
    {
        return Application::where('is_active', true)
            ->whereIn('context_support', ['personal', 'both'])
            ->where(function ($q) use ($user) {
                // Default consumer apps (no subscription needed)
                $q->where('access_model', 'customer')
                  // Or user has a subscription
                  ->orWhereHas('userSubscriptions', fn($q) => $q->where('user_id', $user->id));
            })
            ->where('lifecycle', '!=', 'retired')
            ->where('operational_status', 'online')
            ->get()
            ->groupBy('category');
    }

    protected function getOrganizationApps(User $user, Organization $org): Collection
    {
        return Application::where('is_active', true)
            ->whereIn('context_support', ['organization', 'both'])
            ->whereHas('installations', fn($q) => $q->where('organization_id', $org->id)->where('status', 'active'))
            ->where('lifecycle', '!=', 'retired')
            ->where('operational_status', 'online')
            ->get()
            ->groupBy('category');
    }

    public function canAccess(User $user, Application $app, WorkspaceContext $context): bool
    {
        // Implementation of the security chain
        // User → Membership → Subscription → Role → Data
    }
}
```

### OrganizationAccessService

```php
class OrganizationAccessService
{
    public function getAccessibleOrganizations(User $user): Collection
    {
        return Organization::whereHas('members', fn($q) => $q->where('user_id', $user->id)->where('status', 'active'))
            ->get();
    }

    public function getUserRole(User $user, Organization $org): ?string
    {
        return OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $org->id)
            ->value('role');
    }

    public function validateMembership(User $user, Organization $org): bool
    {
        return OrganizationMember::where('user_id', $user->id)
            ->where('organization_id', $org->id)
            ->where('status', 'active')
            ->exists();
    }
}
```

### App Launch Contract

```php
class AppLaunchService
{
    public function buildPayload(Application $app, WorkspaceContext $context, User $user): array
    {
        return [
            'application' => $app->slug,
            'context_type' => $context->type,
            'organization_id' => $context->organization?->id,
            'organization_slug' => $context->organization?->slug,
            'user_id' => $user->id,
            'permissions' => $this->getPermissions($user, $app, $context),
            'installation_id' => $context->organization?->installations
                ->where('application_id', $app->id)->first()?->id,
            'installation_settings' => $context->organization?->installations
                ->where('application_id', $app->id)->first()?->settings,
        ];
    }

    public function launch(Application $app, WorkspaceContext $context, User $user): RedirectResponse
    {
        $payload = $this->buildPayload($app, $context, $user);

        // Encode payload in session or signed URL for the destination app
        session(['app_launch_payload' => $payload]);

        return redirect()->away($app->url);
    }
}
```

---

## Phase 2: Middleware

### ResolveDomainContext (global middleware)

```php
class ResolveDomainContext
{
    public function handle(Request $request, Closure $next)
    {
        $resolver = app(DomainResolverService::class);
        $contextResolver = app(ContextResolverService::class);

        try {
            $resolution = $resolver->resolve($request->getHost());
            $request->attributes->set('domain_resolution', $resolution);

            // Resolve context from domain + session
            $context = $contextResolver->resolve(
                user: $request->user(),
                domainType: $resolution->type,
                orgHint: $resolution->organization
            );
            $request->attributes->set('workspace_context', $context);
        } catch (DomainNotFoundException $e) {
            abort(404);
        }

        return $next($request);
    }
}
```

### EnsureOrganizationAccess (route middleware)

```php
class EnsureOrganizationAccess
{
    public function handle(Request $request, Closure $next)
    {
        $context = $request->attributes->get('workspace_context');

        if ($context->isOrganization()) {
            $user = $request->user();
            $org = $context->organization;

            $isMember = app(OrganizationAccessService::class)
                ->validateMembership($user, $org);

            abort_unless($isMember, 403, 'Not a member of this organization');
        }

        return $next($request);
    }
}
```

### EnsureApplicationAccess (route middleware)

```php
class EnsureApplicationAccess
{
    public function handle(Request $request, Closure $next)
    {
        $context = $request->attributes->get('workspace_context');
        $user = $request->user();

        // Resolve application from route or domain
        $app = $request->attributes->get('domain_resolution')?->application;

        if ($app && $app->requires_organization_context && $context->isPersonal()) {
            abort(403, 'This application requires an organization context');
        }

        if ($app && !app(ApplicationAccessService::class)->canAccess($user, $app, $context)) {
            abort(403, 'No access to this application');
        }

        return $next($request);
    }
}
```

### SetPlatformContext (global middleware, runs after ResolveDomainContext)

```php
class SetPlatformContext
{
    public function handle(Request $request, Closure $next)
    {
        // Share context with Inertia for Vue
        if (function_exists('inertia')) {
            $context = $request->attributes->get('workspace_context');
            $user = $request->user();

            if ($user && $context) {
                inertia()->share('workspace', [
                    'context' => $context->toArray(),
                    'apps' => fn() => app(ApplicationAccessService::class)
                        ->getAvailableApps($user, $context),
                    'organizations' => fn() => app(OrganizationAccessService::class)
                        ->getAccessibleOrganizations($user),
                ]);
            }
        }

        return $next($request);
    }
}
```

---

## Phase 2: Controllers

### WorkspaceController

```php
class WorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
        private ContextResolverService $contextResolver,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $context = $request->attributes->get('workspace_context');

        return Inertia::render('Workspace/Index', [
            'context' => $context->toArray(),
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'organizations' => $this->orgAccess->getAccessibleOrganizations($user),
            'user' => $user->only('id', 'name', 'email'),
        ]);
    }

    public function switchContext(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:personal,organization',
            'organization_id' => 'required_if:type,organization|integer|exists:organizations,id',
        ]);

        $context = $this->contextResolver->switchContext(
            $request->user(),
            $validated['type'],
            $validated['organization_id'] ?? null
        );

        return redirect()->route('workspace');
    }
}
```

### OrganizationWorkspaceController

```php
class OrganizationWorkspaceController extends Controller
{
    public function __construct(
        private ApplicationAccessService $appAccess,
        private OrganizationAccessService $orgAccess,
    ) {}

    public function show(Request $request, string $slug)
    {
        $org = Organization::where('slug', $slug)->firstOrFail();
        $user = $request->user();

        // Validate membership
        $this->orgAccess->validateMembership($user, $org);

        // Set organization context
        $context = app(ContextResolverService::class)->switchContext(
            $user, 'organization', $org->id
        );

        return Inertia::render('Workspace/Organization', [
            'organization' => $org,
            'apps' => $this->appAccess->getAvailableApps($user, $context),
            'members' => $org->members()->with('user.profile')->get(),
            'context' => $context->toArray(),
        ]);
    }
}
```

---

## Phase 2: Vue Components

### Component Tree

```
resources/js/
  Layouts/
    WorkspaceLayout.vue         ← shared layout for both workspaces
  Pages/
    Workspace/
      Index.vue                 ← Platform Workspace (/workspace)
      Organization.vue          ← Organization Workspace (/org/{slug})
  Components/
    Workspace/
      ContextSwitcher.vue       ← "Working as: [Personal ▼]"
      AppGrid.vue               ← categorized app tiles
      AppTile.vue               ← single app icon + name
      OrganizationList.vue      ← organizations section
      OrganizationCard.vue      ← single org card
      GlobalAppSwitcher.vue     ← cross-subdomain flyout menu
      LegacyAppBadge.vue        ← migration status badge for legacy apps
      IntendedAppHighlight.vue  ← auto-highlight for application-first entry
```

### Data Flow

```
Inertia page load:
  workspace.context       → WorkspaceLayout → ContextSwitcher
  workspace.apps          → AppGrid → AppTile
  workspace.organizations → OrganizationList → OrganizationCard
```

### Key Inertia Shared Data (from SetPlatformContext middleware)

```typescript
interface WorkspaceShared {
    context: {
        type: 'personal' | 'organization';
        organization_id: number | null;
        organization_slug: string | null;
        organization_name: string | null;
        application_id: number | null;
    };
    apps: {
        business: App[];
        consumer: App[];
        shared: App[];
    };
    organizations: Organization[];
}
```

---

## Phase 2: Routes

### web.php

```php
// Context switching
Route::post('/workspace/switch-context', [WorkspaceController::class, 'switchContext'])
    ->name('workspace.switch-context')
    ->middleware('auth');

// Platform Workspace
Route::get('/workspace', [WorkspaceController::class, 'index'])
    ->name('workspace')
    ->middleware('auth');

// Legacy redirect
Route::redirect('/dashboard', '/workspace', 301);

// Organization Workspace
Route::get('/org/{slug}', [OrganizationWorkspaceController::class, 'show'])
    ->name('workspace.organization')
    ->middleware(['auth', 'ensure.organization.access']);

// Diagnostic
Route::get('/_platform/workspace', function (DomainResolverService $resolver) {
    return response()->json($resolver->resolve(request()->getHost()));
});
```

### Kernel.php Middleware Registration

```php
// Global
'web' => [
    // ... existing middleware ...
    \App\Http\Middleware\ResolveDomainContext::class,
    \App\Http\Middleware\SetPlatformContext::class,
],

// Route
'ensure.organization.access' => \App\Http\Middleware\EnsureOrganizationAccess::class,
'ensure.application.access' => \App\Http\Middleware\EnsureApplicationAccess::class,
```

---

## Phase 3: Application Migration

### Step 1: Add `organization_id` to existing business app tables

- StockFlow: alias `sa_company_id` → standardize to `organization_id`
- BMS: use existing `cms_companies.id` mapping

### Step 2: Seed `applications` registry

```php
DB::table('applications')->insert([
    [
        'name' => 'GrowNet',
        'slug' => 'grownet',
        'category' => 'consumer',
        'access_model' => 'customer',
        'context_support' => 'personal',
        'requires_organization_context' => false,
        'subscription_required' => false,
        'lifecycle' => 'active',
        'operational_status' => 'online',
        'is_visible' => true,
    ],
    // ... all 13 apps from the inventory
]);
```

### Step 3: Seed `domains` table

```php
DB::table('domains')->insert([
    ['domain' => 'mygrownet.com', 'type' => 'platform'],
    ['domain' => 'grownet.mygrownet.com', 'type' => 'application', 'application_id' => 1],
    ['domain' => 'finance.mygrownet.com', 'type' => 'application', 'application_id' => 5],
    // ... all domains from the subdomain registry
]);
```

### Step 4: Migrate GrowNet.vue

- Extract workspace logic from `GrowNet.vue` into `Workspace/Index.vue`
- GrowNet.vue becomes GrowNet's own dashboard (accessible via `grownet.mygrownet.com`)
- Remove all wallet/earnings/investment data from workspace page

---

## Phase 3: Cleanup

### Deprecations

- Remove `DashboardController@index` after workspace is stable
- Remove all `route('dashboard')` references (already done, verify)
- Replace hardcoded module/company logic with Application Registry queries

### Legacy Apps

- Keep Quick Invoice in registry with `lifecycle: legacy`, `is_visible: false` for new users
- Existing users see it via `user_application_subscriptions` check
- After BizDocs stabilizes: migration assistant → retire

---

## Implementation Order Summary

```
Week 1-2: Phase 1 (Database + Services)
  - All 12 migrations
  - DomainResolverService, ContextResolverService
  - ApplicationAccessService, OrganizationAccessService
  - AppLaunchService

Week 3: Phase 2 (Middleware + Controllers)
  - ResolveDomainContext, EnsureOrganizationAccess
  - EnsureApplicationAccess, SetPlatformContext
  - WorkspaceController, OrganizationWorkspaceController

Week 4: Phase 2 (Vue)
  - WorkspaceLayout, ContextSwitcher, AppGrid
  - Workspace/Index.vue, Workspace/Organization.vue
  - GlobalAppSwitcher component

Week 5: Phase 3 (Migration)
  - Seed applications + domains + org_members data
  - Migrate GrowNet.vue workspace logic out
  - Standardize organization_id across business apps
  - Run tests
```
