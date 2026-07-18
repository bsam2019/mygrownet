# MyGrowNet Platform Evolution — Full Implementation Roadmap

## Objective

Evolve the MyGrowNet monolith into a modular platform architecture while preserving all existing functionality. Each phase is self-contained, testable, and independently deployable.

---

## Glossary

| Term | Definition |
|---|---|
| **Platform User** | A person with an identity on MyGrowNet. May or may not belong to an organization. |
| **Organization** | A business or legal entity that owns one or more workspaces and subscribes to applications. |
| **Workspace** | The environment where an organization accesses subscribed applications. Mapped to a subdomain or custom domain. |
| **Application** | An independently developed product running inside MyGrowNet. Applications are first-class platform extensions — new ones should register via the Application Registry without requiring Platform Core changes. Applications are first-class platform extensions — new ones should register via the Application Registry without requiring Platform Core changes. |
| **Module** | A bounded domain within the codebase. Maps to a directory under `app/Domain/` or `resources/js/pages/`. |
| **Platform Core** | Shared platform capabilities that every module depends on. The Core depends on no module. |
| **RoutingEngine** | The middleware that resolves incoming requests to the correct application or organization workspace. Runs in parallel with `DetectSubdomain` during migration. |

---

## Architecture Principles

### Platform Core

All phases build toward a **Platform Core** — a dependency-free center that every module depends on, and which depends on no module.

```
Platform Core
├── Identity             (user record, core auth data — not profile, not OAuth)
├── Profile              (avatar, preferences, timezone, language, linked apps)
├── Authentication       (passwords, sessions, OAuth — deferred to Phase 8)
├── Organizations        (org structure, members)
├── Applications         (registry, routing metadata)
├── Routing              (workspace resolver, subdomain → app/org)
├── Permissions          (application-aware roles)
├── Notifications        (channel-agnostic delivery: email, SMS, push, in-app)
├── Events               (platform-wide event bus)
├── Settings             (platform, org, application settings)
└── Audit                (centralized audit trail)
```

**Identity** is the bare user record: `id`, `email`, `phone`, `status`.
**Profile** is user preferences: `avatar`, `timezone`, `language`, `linked_applications`.
**Authentication** is passwords, sessions, OAuth — intentionally deferred to Phase 8.

### Dependency Rule

### Platform Core

All phases build toward a **Platform Core** — a dependency-free center that every module depends on, and which depends on no module.

```
Platform Core
├── User Identity        (profile, preferences, linked apps, org memberships)
│                        (NOT auth — no passwords, sessions, OAuth)
├── Organizations        (org structure, members)
├── Applications         (app registry, routing metadata)
├── Routing              (workspace resolver, subdomain → app/org)
├── Permissions          (application-aware roles)
├── Notifications        (channel-agnostic delivery: email, SMS, push, in-app)
├── Events               (platform-wide event bus)
├── Settings             (platform, org, application settings)
└── Audit                (centralized audit trail)
```

### Dependency Rule

> Dependencies always point toward the Platform Core.
>
> **Platform Core** depends on no module.
>
> **Modules** may depend on the Platform Core but must not depend on each other directly.
>
> Cross-module communication should occur through Platform Core services or events.

### Module Independence

> Each module owns:
>
> - Its database schema
> - Its business rules
> - Its services
> - Its events
> - Its UI
>
> Modules must not query another module's internal tables directly.
> If information from another module is required, use Platform Core services or platform events.

### Platform Users vs Organization Members

Every user of the platform is a **platform user** (recorded in `users` and `user_profiles`). A subset of platform users are also **organization members** (recorded in `organization_members`).

- A GrowMart customer is a **platform user** with no organization
- A Taradasi accountant is a **platform user** who is also an **organization member** of Taradasi Dental Clinic

This distinction prevents conflating platform identity with organizational affiliation.

### Module Versioning

Each module owns its own version independently of the platform version.

| Module | Current Version | Notes |
|---|---|---|
| Platform Core | 1.0 | Contract-defining version |
| StockFlow | 2.3 | Full DDD, own subdomain |
| GrowNet | 1.0 | Tightly coupled to User model |
| ... | ... | ... |

**Rules:**
- Platform Core defines compatibility contracts between modules
- Modules may version independently — Core does not dictate module release cadence
- Breaking changes to Platform Core require compatibility validation before deployment
- Module-to-module contracts are documented and versioned in Core

---

## Phase 1: Platform Core Foundation ✅ (Started)

### Goal
Create the Core foundation: platform tables, models, and services. No existing code is modified.

### Status
- ✅ Audit complete
- ✅ Architecture documented
- ✅ 8 migration files created
- ✅ 6 models created
- ✅ 3 services + ResolvedWorkspace created
- ✅ ApplicationRegistrySeeder created
- ⏳ Run `php artisan migrate`
- ⏳ Run `php artisan db:seed --class=ApplicationRegistrySeeder`
- ⏳ Verify tables, models, and services work

### Deliverables
| Artifact | Location |
|---|---|
| Migrations | `database/migrations/2026_07_18_0000*` |
| Models | `app/Domain/Core/Models/` |
| Services | `app/Domain/Core/Services/` |
| Seeder | `database/seeders/ApplicationRegistrySeeder.php` |

### Verification
```bash
php artisan migrate
php artisan db:seed --class=ApplicationRegistrySeeder
php artisan tinker
# >>> App\Domain\Core\Models\Application::count()
# >>> app(App\Domain\Core\Services\WorkspaceResolver::class)->resolve('stockflow.mygrownet.com')
```

---

## Phase 2: Workspace Routing

### Goal
Deploy the RoutingEngine alongside the existing `DetectSubdomain` middleware. Validate resolution matches before any replacement.

### Steps

#### 2.1 Create RoutingEngine middleware
A new middleware that queries `applications` by subdomain (via `config/platform.php` mapping, not DB config) and resolves the workspace. Runs **in parallel** with `DetectSubdomain` — logs its decision without acting on it.

```
Request → DetectSubdomain (active) → RoutingEngine (logging) → Controller
```

#### 2.2 Create `config/platform.php`
```php
<?php
return [
    'applications' => [
        'stockflow' => [
            'domain_slug' => 'stockflow',
            'service_provider' => App\Providers\StockAuditServiceProvider::class,
            'middleware' => ['web', 'auth', 'stockflow'],
            'uses_inertia' => true,
            'session_prefix' => 'stockflow',
        ],
        'cms' => [
            'domain_slug' => 'cms',
            // ...
        ],
        // All 9 applications
    ],
];
```

**Why config, not DB:** Laravel class names and middleware references in the database make deployments brittle. The DB identifies *what* the application is (slug, type, status). Config determines *how* it runs.

#### 2.3 Add diagnostic route
```php
Route::get('/_platform/workspace', function () {
    return app(\App\Domain\Core\Services\WorkspaceResolver::class)
        ->resolve(request()->getHost());
});
```

#### 2.4 Instrument DetectSubdomain
Add a logging call that calls `WorkspaceResolver::resolve()` and logs the result alongside the existing switch decision.

### Verification
- RoutingEngine logs match DetectSubdomain decisions
- Diagnostic route returns correct workspace per subdomain
- No change in user-facing behavior
- Adding a new app = DB record + config entry (no middleware edit)

### Risk
Low — parallel only, no behavior change.

---

## Phase 3: Organizations

### Goal
Identify the canonical business entity across existing company tables and establish organization workspace infrastructure.

### Steps

#### 3.1 Map existing company tables to business entities
Before adding any foreign keys, determine which existing business/company tables represent the same conceptual organization.

| Module | Table | Canonical Org? | Notes |
|---|---|---|---|
| StockFlow | `sa_companies` | ✅ Yes — each is a real business | Has active users and subscriptions |
| CMS (BMS) | `cms_companies` | ✅ Yes — each is a managed entity | ~100 companies |
| BizBoost | `bizboost_businesses` | ⚠️ Maybe — verify business model | Review with stakeholders |
| GrowBiz | `growbiz_business_profiles` | ⚠️ Maybe — verify business model | Review with stakeholders |
| GrowMart | (none) | ❌ No — sellers are individuals | No company table |
| ZamStay | (none) | ❌ No — properties, not companies | No company table |

**Action:** Only add `organization_id` to tables where the business entity is confirmed to represent the same concept as a Core Organization.

#### 3.2 Add `organization_id` to confirmed tables
Nullable FK, no cascade delete. One migration per confirmed table.

```php
Schema::table('sa_companies', function (Blueprint $table) {
    $table->foreignId('organization_id')
        ->nullable()
        ->constrained('organizations')
        ->nullOnDelete();
});
```

#### 3.3 Create import command
```bash
php artisan platform:import-companies
```
Reads confirmed company tables, creates `Organization` records, sets `organization_id` links.

#### 3.4 Organization dashboard / app launcher
Create a simple Vue page at the organization root listing available applications (from `organization_applications`).

### Verification
- `organization_id` column exists and is nullable on confirmed tables
- Import command creates correct Organization records
- Organization dashboard shows correct applications
- Existing company queries continue working unchanged

### Risk
Low — nullable FK, backward-compatible. No changes to existing queries.

---

## Phase 4: Application Registry & Settings

### Goal
Mature the Application Registry into a central service used by routing, permissions, and settings. Add platform-level settings infrastructure.

### Steps

#### 4.1 Define application types
The `applications.type` column uses these values:

| Type | Description | Examples |
|---|---|---|
| `business` | B2B SaaS tools | StockFlow, BizDocs, BizBoost, GrowBuilder, GrowFinance |
| `consumer` | B2C marketplaces | GrowMart, ZamStay |
| `platform` | Core network platform | GrowNet |
| `system` | Internal infrastructure | BMS (CMS) |

This enables filtering and navigation without hardcoding logic.

#### 4.2 Harden ApplicationRegistry service
Add caching to the discovery layer. ApplicationRegistry answers *what exists* — it does not authorize.

```php
class ApplicationRegistry
{
    public function all(): Collection                           // cached 1 hour
    public function findBySlug(string $slug): ?Application
    public function getByType(string $type): Collection
    public function getForOrganization(Organization $org): Collection
    public function getForUser(User $user): Collection
    public function isActive(string $slug): bool
    public function getRoutingConfig(string $slug): ?array      // reads config/platform.php
}
```

Authorization (can a user access this application?) belongs in `PermissionService` or `AuthorizationService`, not in the registry.

#### 4.3 Add platform settings tables
```php
Schema::create('platform_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // string, json, boolean, integer
    $table->timestamps();
});

Schema::create('organization_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->text('value')->nullable();
    $table->string('type')->default('string');
    $table->unique(['organization_id', 'key']);
    $table->timestamps();
});
```

#### 4.4 Add Platform Events concept (design only)
Define the event contracts that will enable module-to-module communication:

| Event | Payload | Example Listener |
|---|---|---|
| `OrganizationCreated` | `organization_id`, `owner_id` | StockFlow → create company |
| `ApplicationSubscribed` | `organization_id`, `application_id` | Provision resources |
| `MemberAdded` | `organization_id`, `user_id`, `role` | Sync module memberships |
| `MemberRemoved` | `organization_id`, `user_id` | Revoke access |

Implementation deferred to a later phase. Document only.

### Verification
- ApplicationRegistry returns cached results
- Platform settings CRUD works
- Organization settings CRUD works, scoped to org
- Event contracts documented

### Risk
Low — new tables and services, no existing code affected.

---

## Phase 5: Platform APIs

### Goal
Design and document the API surface. Even without full implementation, defining the contracts now keeps future development consistent.

### API Layers

```
┌─────────────────────────────────────────────────┐
│                  Platform API                    │
├────────────┬──────────────┬──────────┬──────────┤
│ Internal   │   Public     │ Partner  │ Admin    │
│ (modules)  │  (mobile)    │(3rd-party)│(ops)    │
└────────────┴──────────────┴──────────┴──────────┘
```

| Layer | Audience | Auth | Rate Limit |
|---|---|---|---|
| Platform API | Service-to-service within monolith | Internal token | None |
| Public API | Mobile apps, first-party clients | User token (Sanctum) | Standard |
| Partner API | Third-party integrations | API key + OAuth2 | Strict |
| Administration API | Internal tools, dashboards | Session + role check | None |

### Design Documents to Create

1. **API contracts** — request/response shapes for platform resources (organizations, applications, members)
2. **Authentication scheme** — token-based for public/partner, session-based for admin
3. **Error format** — consistent error envelope across all layers
4. **Versioning strategy** — URL prefix (`/api/v1/`) or header-based
5. **Rate limiting** — tiered limits per API layer

Implementation is incremental. Each phase exposes the endpoints it needs.

### Verification
- API contracts documented and reviewed
- Authentication scheme chosen
- Error format used consistently

### Risk
Low — design only, no production endpoints until consumers exist.

---

## Phase 6: Application-Aware Roles & Permissions

### Goal
Make Spatie roles application-aware so a user can have different roles in different applications.

### Steps

#### 6.1 Add `application_id` to roles table
Nullable migration. `NULL` = global role (backward-compatible). Non-null = scoped to that application.

```php
Schema::table('roles', function (Blueprint $table) {
    $table->foreignId('application_id')
        ->nullable()
        ->constrained()
        ->cascadeOnDelete();
});
```

#### 6.2 Seed application-specific roles
| Application | Roles |
|---|---|
| StockFlow | Manager, Viewer, Auditor |
| GrowFinance | Accountant, Viewer |
| BizDocs | Editor, Viewer |
| GrowNet | Member, Sponsor, Admin |

#### 6.3 Add scoped permission helper
```php
$user->hasApplicationRole('stockflow', 'manager');
$user->hasApplicationPermission('stockflow', 'view-reports');
```

Implemented via a new trait or service — does not modify Spatie core.

#### 6.4 Update HandleInertiaRequests
Share `auth.user.application_roles` for frontend use.

### Verification
- Existing global roles continue working
- A user can be Manager in StockFlow and Viewer in GrowFinance
- Frontend guards respect application roles

### Risk
Low — `application_id` is nullable, existing roles untouched.

---

## Phase 7: User Model Decomposition

### Goal
Extract ~85% of User model fields into their respective modules. Keep only core identity fields.

### Current User Model: 2331 lines, ~100 fields, 45+ relationships

### Steps

#### 7.1 Create UserProfile (Core identity)
```php
class UserProfile extends Model
{
    // Keeps: name, email, phone, password, status, avatar, preferences, fcm_token
    // Relations: user() → belongsTo User
}
```

#### 7.2 Extract GrowNet fields → GrowNetUser model
Move MLM, wallet, subscription, starter kit, loan, loyalty fields to:
`App\Domain\GrowNet\Models\GrowNetUser`

Relationship: `User::hasOne(GrowNetUser::class)`

#### 7.3 Add accessor shims on User model
```php
public function getBalanceAttribute($value)
{
    return $this->growNetUser?->balance ?? 0;
}
```

Existing code referencing `$user->balance` continues working. Shim queries the new table.

#### 7.4 Data migration
```bash
php artisan user:decompose
```

Copies existing values to new models. Dry-run mode for staging verification.

### Verification
- All field accessors work: `$user->balance`, `$user->referrer_id`, etc.
- New tables have correct data
- All Controllers, Vue pages, API routes unaffected
- `php artisan user:decompose --dry-run` matches production

### Risk
Medium — accessor coverage must be complete. Any missed field returns `null` silently.

---

## Phase 8: Authentication Unification

### Goal
Unify the three separate auth systems into a single identity gateway. This is intentionally late — auth touches everything and should only change once the Core, User model, and routing are stable.

### Current Auth Landscape
| Guard | User Table | Auth Style |
|---|---|---|
| `web` | `users` | Blade + session |
| `stockflow` | `sa_users` | Controller + session |
| `primeedge` | `prime_edge_clients` | Controller + session |

### Design Principles
- **Identity-provider agnostic** — do not lock into Passport today. Design the abstraction to support Passport, Keycloak, Authentik, FusionAuth, Auth0, Clerk, or a custom solution.
- **Evolutionary replacement** — existing auth continues working alongside the new unified gateway.
- **Feature-flagged** — new login behind a config flag until validated.

### Steps

#### 8.1 Populate `user_applications` for existing accounts
Artisan command linking existing users across all three auth systems to their applications.

#### 8.2 Design identity abstraction
```php
interface IdentityProvider
{
    public function authenticate(array $credentials): ?Identity;
    public function getUser(Identity $identity): User;
}

// Laravel-specific implementation (can be swapped later)
class LaravelIdentityProvider implements IdentityProvider { ... }
```

#### 8.3 Unified login gateway (behind feature flag)
A single `/login` endpoint that resolves the user regardless of auth system. Routes to the correct dashboard based on `user_applications`.

#### 8.4 Cross-subdomain session resolution
Enhance the existing `SESSION_DOMAIN` sharing with middleware that resolves the correct auth context per subdomain.

#### 8.5 Deprecate standalone auth controllers
Mark StockFlow and PrimeEdge auth controllers as deprecated. Add redirects to the unified gateway.

### Verification
- Existing auth for all three systems continues working
- New unified login works for all user types
- `user_applications` table populated
- No duplicate sessions or auth conflicts
- Feature flag disables the new flow entirely when off

### Risk
High — auth is the most sensitive subsystem. Must be feature-flagged and validated on staging against real account types before production rollout.

---

## Phase 9: Platform Events & Shared Services

### Goal
Implement event-driven communication between modules and establish shared platform services.

### 9.1 Platform Event Bus
Implement the contracts designed in Phase 4:

| Event | When | Listeners |
|---|---|---|
| `OrganizationCreated` | Org created | Sync to modules with company tables |
| `OrganizationArchived` | Org soft-deleted | Deactivate linked module records |
| `ApplicationSubscribed` | Org gains app access | Provision app resources |
| `ApplicationUnsubscribed` | Org loses app access | Deprovision |
| `MemberAdded` | User joins org | Sync module memberships |
| `MemberRoleChanged` | User role updated | Update application permissions |

- Laravel events + listeners
- Each listener lives in the target module, not in Core
- Can be promoted to a queue or message bus later without changing event contracts

### 9.2 Platform Shared Services (design)
Reserve service contracts for capabilities that multiple modules will need. Notifications is already in Platform Core — it does not belong here.

```
Platform Shared Services
├── StorageService          (file uploads, media management)
├── SearchService           (full-text, filtered search across modules)
├── PaymentGateway          (unified payment processing)
├── AuditService            (structured activity logging)
├── AIService               (AI/ML capabilities)
└── ReportingService        (cross-module report generation)
```

These are **not** implemented in Phase 9. They are documented as reserved contracts so new feature development does not accidentally create duplicates inside a module.

### Verification
- Event listeners fire correctly
- Event logs show correct propagation
- Cross-module synchronization works without direct table queries
- Shared service contracts documented and reviewed

### Risk
Low — events are additive, services are design-only.

---

## Phase 10: Future Scaling Options

### Goal
Document options for scaling modules independently — only if operational needs justify it.

### Candidates
| Module | Decoupling Level | Extraction Complexity |
|---|---|---|
| StockFlow | High (full DDD, own auth, own subdomain) | Low |
| PrimeEdge | High (own guard, own user table) | Low |
| GrowMart | Medium (partial DDD, minimal User coupling) | Medium |
| CMS (BMS) | Low (100 tables, complex routing) | High |

### Principles
1. **Modular monolith first** — the architecture from Phases 1-9 may be sufficient indefinitely
2. **Extract only when proven necessary** — demonstrated scaling bottleneck, team size, or deployment frequency
3. **Shared Core** — extracted modules still depend on the Platform Core (identity, organizations, auth)
4. **No premature microservices** — each extraction must be justified by operational data

### Verification
N/A — decision point, not implementation.

---

## Phase 11: Deprecation Cleanup

### Goal
Remove deprecated code, middleware, and migration workarounds.

### Items to Remove
- `DetectSubdomain` middleware (after RoutingEngine validated in production)
- Standalone auth controllers (after unified gateway validated)
- User model accessor shims (after all code references migrated)
- Redundant migration files
- Dead route files

### Verification
Full regression test suite must pass after each removal.

---

## Summary Timeline

| Phase | Description | Dependencies | Risk |
|---|---|---|---|
| **1** | Platform Core Foundation | None | Low |
| **2** | Workspace Routing (parallel with DetectSubdomain) | Phase 1 | Low |
| **3** | Organizations (canonical entities, org workspaces) | Phase 1, 2 | Low |
| **4** | Application Registry & Settings | Phase 1 | Low |
| **5** | Platform APIs (design) | Phase 4 | Low |
| **6** | Application-Aware Roles | Phase 1, 4 | Low |
| **7** | User Model Decomposition | Phase 6 | Medium |
| **8** | Authentication Unification | Phase 3, 7 | High |
| **9** | Platform Events & Shared Services | Phase 4, 6 | Low |
| **10** | Future Scaling Options (decision point) | All above | N/A |
| **11** | Deprecation Cleanup | All above | Medium |

---

## Recommended Next Step: Architecture Decision Records

Before beginning implementation, create one-page Architecture Decision Records (ADRs) for each major decision. These documents explain *why* each decision was made — far more valuable than reconstructing reasoning from code or git history months later.

| ADR | Title |
|---|---|
| ADR-001 | Modular Monolith over Microservices |
| ADR-002 | Platform Core as Dependency-Free Kernel |
| ADR-003 | Organizations Model (canonical business entity) |
| ADR-004 | Parallel Routing (RoutingEngine + DetectSubdomain) |
| ADR-005 | Application Registry (DB identity + config behavior) |
| ADR-006 | Authentication Unification (identity-provider agnostic, deferred) |
| ADR-007 | Event-Driven Cross-Module Communication |

Each ADR should contain: **Context → Decision → Consequences** — one page, no more.

---

## Key Principles

1. **Never break working code** — each phase runs alongside existing systems
2. **Feature flags** — new behavior behind config flags until validated
3. **Parallel run** — old and new systems run concurrently during transition
4. **Rollback always possible** — each phase can be rolled back independently
5. **Auth is last** — authentication should not change until the Core, User model, and routing are stable
6. **No microservices** — modular monolith unless extraction is proven necessary by operational data
7. **Config over DB for runtime behavior** — database identifies; config determines behavior
8. **Identity-provider agnostic** — abstract auth so Passport, Keycloak, Auth0, or custom can be swapped
9. **Platform Core** — the core depends on nothing; every module depends on the core
10. **Module versioning** — each module versions independently; Core defines compatibility contracts
11. **Platform Core owns shared concerns** — if two or more applications require the same capability, evaluate moving it into Platform Core instead of duplicating it in each module
