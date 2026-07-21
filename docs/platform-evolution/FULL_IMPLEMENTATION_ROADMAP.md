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

Implemented in early phases (1-7):
├── Identity
│   ├── User              (id, email, phone, status — bare record)
│   ├── Profile           (avatar, preferences, timezone, language)
│   ├── Organizations     (org structure, memberships)
│   ├── User Applications (which apps a user can access)
│   └── Identity Links    (OAuth provider links, legacy user references)
│
├── Access
│   ├── Roles             (application-aware roles via Spatie)
│   ├── Permissions       (scoped per application)
│   └── Workspace Resolver (which app/org the user sees)
│
├── Platform
│   ├── Applications      (registry, routing metadata)
│   ├── Routing           (workspace resolver, subdomain → app/org)
│   ├── Notifications     (channel-agnostic delivery)
│   ├── Events            (platform-wide event bus)
│   ├── Settings          (platform, org, application settings)
│   └── Audit             (centralized audit trail)

Implemented later (Phase 8+):
└── MyGrow Identity (auth.mygrownet.com)
    ├── Login             (shared login form)
    ├── Register          (shared registration form)
    ├── Password Reset    (shared reset flow)
    ├── Email Verification (shared verification)
    ├── Two-Factor Auth   (shared 2FA)
    ├── Session Validation (token/session API)
    └── Application Redirect (return to calling app)
```

Authentication belongs exclusively to the Platform. Phase 1 deliberately defers it — identity and access are built first. But it remains a Platform Core capability from an architectural perspective.

**Identity** answers "who is this user?" — built in early phases.
**Access** answers "what can this user do?" — built in early phases.
**Platform** provides shared infrastructure every module needs — built in early phases.
**MyGrow Identity** is the shared authentication service hosted at `auth.mygrownet.com` — every application delegates to it, implemented in Phase 8.

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

### Authentication Principle (Permanent Architectural Rule)

Authentication belongs **exclusively** to the Platform.

Applications may authorize authenticated users but must **never** authenticate users themselves.

**Rules:**
- ✓ Only the Platform owns login.
- ✓ Only the Platform owns registration.
- ✓ Only the Platform owns password reset.
- ✓ Only the Platform owns email verification.
- ✓ Only the Platform owns two-factor authentication.
- ✓ Modules never authenticate users.
- ✓ Modules never display login pages.
- ✓ Modules trust the authenticated platform session.
- ✓ Authentication is performed once per session.
- ✓ Authorization is determined by the current application and permissions.

**Request flow:**
```
User
  │
  ▼
login.mygrownet.com          ← single entry point
  │
  ▼
Platform Authentication      ← Identity Gateway
  │
  ▼
Platform Session             ← shared across all apps
  │
  ▼
Workspace                    ← which app/org does the user need?
  │
  ▼
Application                  ← StockFlow, GrowMart, BizBoost, etc.
  │
  ▼
Application Authorization    ← does this user have permission here?
  │
  ▼
Dashboard
```

The application never authenticates the user. It only asks:
1. **Who is this user?** — validated session from Gateway
2. **Is the session valid?** — check with Gateway
3. **What role does this user have here?** — Platform Core permissions

Any new application added to MyGrowNet must integrate with the Platform Authentication Gateway and must not implement its own login, registration, password reset, or session management.

**What every module must expose:**
```
Instead of:
  /login              ← ❌ no
  /register           ← ❌ no
  /forgot-password    ← ❌ no

Modules expose only:
  /                   ← landing or dashboard
  /dashboard          ← authenticated home
  /settings           ← user preferences
  /profile            ← user profile
  ... application-specific routes
```

There should never be `stockflow.mygrownet.com/login` or `growfinance.mygrownet.com/login`. Those URLs should either redirect to the platform login or — if already authenticated — continue into the application.

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

## Phase 8: MyGrow Identity & Centralized Authentication

### Goal

Eliminate **all** module-specific authentication systems. After Phase 8 there will be exactly one authentication system for the entire MyGrowNet platform — **MyGrow Identity**, hosted at `auth.mygrownet.com`.

Authentication belongs exclusively to the Platform. Applications may authorize authenticated users but must **never** authenticate users themselves. This is a permanent architectural rule, not a temporary migration step.

| Before Phase 8 | After Phase 8 |
|---|---|
| 3 auth guards (web, stockflow, primeedge) | 1 auth system (MyGrow Identity) |
| 3 login pages across subdomains | 1 login page (`auth.mygrownet.com/login`) |
| Applications own user tables | Platform owns all user records |
| New apps must build auth from scratch | New apps integrate with MyGrow Identity — no auth code needed |
| Password reset scattered per app | Password reset centralized |
| Email verification duplicated per app | Email verification centralized |

### Authentication vs Authorization

A critical distinction that every developer must understand:

| Authentication | Authorization |
|---|---|
| Login | Application Access |
| Logout | Roles |
| Registration | Permissions |
| Password Reset | Organization Membership |
| Email Verification | Feature Access |
| Two-Factor Authentication | Workspace Resolution |
| Session Management | Data-Level Permissions |
| **Belongs to the Platform** | **Belongs to the Application** |

Authentication answers *"who is this user?"* Authorization answers *"what can this user do?"* Applications may determine the latter but must **never** perform the former.

### Application Authentication Rule

Applications never authenticate users. Applications only determine:
- Is the user authenticated? (check with MyGrow Identity)
- Does the user have access? (check Platform Core permissions)
- Which role does the user have? (check Platform Core roles)

### Authentication Rules

| Rule | Applies To |
|---|---|
| Only the Platform owns login | Every module, every subdomain |
| Only the Platform owns registration | Every module, every subdomain |
| Only the Platform owns password reset | Every module, every subdomain |
| Only the Platform owns email verification | Every module, every subdomain |
| Only the Platform owns two-factor authentication | Every module, every subdomain |
| Modules never authenticate users | StockFlow, PrimeEdge, GrowNet, all current and future |
| Modules never display login pages | StockFlow, PrimeEdge, GrowBuilder, all |
| Modules trust the authenticated platform session | Every module |
| Authentication is performed once per session | Every module |
| Authorization is determined by the current app and permissions | Platform Core Access layer |

### Authentication URLs

All authentication is performed exclusively through `auth.mygrownet.com`:

| URL | Purpose |
|---|---|
| `https://auth.mygrownet.com/login` | Login form |
| `https://auth.mygrownet.com/logout` | Logout |
| `https://auth.mygrownet.com/register` | Registration |
| `https://auth.mygrownet.com/password/reset` | Password reset |
| `https://auth.mygrownet.com/email/verify` | Email verification |
| `https://auth.mygrownet.com/session/validate` | Session validation API |

Applications should never implement these routes. MyGrow Identity is the single entry point for all authentication on the platform.

### Identity Gateway Principles

The Identity Gateway is the single authentication authority for the entire MyGrowNet platform.

**Rules:**

1. **Only `auth.mygrownet.com`** serves login, registration, password reset, email verification, and two-factor authentication.
2. **Applications never authenticate users directly.** They may only verify that the platform session is valid.
3. **Applications never maintain independent login pages.** If a user needs to authenticate, the application redirects to `auth.mygrownet.com/login` with a signed `return_url`.
4. **Authentication cookies are issued for `.mygrownet.com`** to enable seamless navigation between applications without repeated logins.
5. **Future OAuth2 or OpenID Connect support** will be implemented inside the Identity Gateway without changing application code.
6. **Default destination:** Platform-originated logins (no `return_url`) go to `mygrownet.com/workspace`. Application-originated logins return to the calling application.

### Problem

Today authentication is scattered:

| Application | Has /login | Has /register | Has /password/reset |
|---|---|---|---|
| Main site | ✅ | ✅ | ✅ |
| StockFlow subdomain | ✅ | ❌ | ❌ |
| PrimeEdge | ✅ | ✅ | ❌ |
| GrowMart | ❌ | ❌ | ❌ |
| BizBoost | ❌ | ❌ | ❌ |

Each app that implements auth owns its own login page, session, and user table. This is duplication at best, a security risk at worst.

There should never be `stockflow.mygrownet.com/login` or `growfinance.mygrownet.com/login`. Those URLs must redirect to `auth.mygrownet.com/login` or — if already authenticated — continue into the application.

### Target Architecture

**MyGrow Identity** is the single authentication authority for the entire platform. It is not just another application — it is the official identity service that every application trusts.

```
                    auth.mygrownet.com
                           │
                    MyGrow Identity
                    (Platform Core)
                           │
          ┌────────────────┼────────────────┐
          │                │                │
   mygrownet.com     stockflow.      growbuilder.
    /workspace       mygrownet.com   mygrownet.com
    /apps
    /marketing
          │                │                │
          └────────────────┼────────────────┘
                     Shared Session
                 (.mygrownet.com cookie)
```

**Every application delegates** authentication to `auth.mygrownet.com`. The shared session cookie at `.mygrownet.com` means a user logs in once and is authenticated everywhere.

### Login Flow

```
User visits any application

If not authenticated:

stockflow.mygrownet.com
        │
        ▼
Redirect to auth.mygrownet.com/login?return_url=...
        │
        ▼
User logs in once
        │
        ▼
Identity Gateway creates the session
        │
        ▼
Browser receives cookie for SESSION_DOMAIN=.mygrownet.com
        │
        ▼
Redirect back to return_url
        │
        ▼
stockflow.mygrownet.com

StockFlow sees the same session and does not ask for another login.
```

This is how Google, Microsoft, Atlassian, GitHub Enterprise, and most SaaS platforms operate.

### Default Destination

- **Platform-originated login** (user visits `auth.mygrownet.com/login` directly with no `return_url`): redirect to `mygrownet.com/workspace`
- **Application-originated login** (user was redirected from `stockflow.mygrownet.com` with `return_url`): redirect back to the original application

This keeps the experience consistent:
- Users who start from the platform land in `/workspace`
- Users who start from an application return directly to that application
- Users authenticate only once across the entire MyGrowNet ecosystem

### Identity vs Access

Two distinct concerns that Platform Core must own separately:

| Concept | Responsibility | Examples |
|---|---|---|
| **Identity** | Who the user is | User record, profile, organizations, auth providers |
| **Access** | What the user can do | Roles, permissions, workspace resolver |

MyGrow Identity belongs to the **Identity** concern. Roles and permissions belong to **Access**.

### Current Auth Landscape
| Guard | User Table | Auth Style | Has Own Login? |
|---|---|---|---|
| `web` | `users` | Blade + session | ✅ Main site → redirects to auth.mygrownet.com |
| `stockflow` | `sa_users` | — | ❌ Dropped Phase 8d/8e — redirects to auth.mygrownet.com |
| `primeedge` | `prime_edge_clients` | — | ❌ Removed Phase 8a, table dropped Phase 8e |

### Design Principles
- **Applications never authenticate** — identity is a platform service, not an application concern.
- **Single login surface** — `https://auth.mygrownet.com/login` for all applications.
- **MyGrow Identity as the source of truth** — every application validates sessions/tokens against MyGrow Identity, not against its own user table.
- **Identity-provider agnostic** — design the abstraction to support Passport, Keycloak, Authentik, FusionAuth, Auth0, or a custom solution without changing applications.
- **Evolutionary replacement** — existing auth continues working alongside MyGrow Identity during transition.
- **Applications own access, not identity** — StockFlow decides who can view reports, but MyGrow Identity decides who the user is.

### What Each Application Loses

| Feature | Before | After |
|---|---|---|
| Login page | Each app has one | MyGrow Identity only |
| Register page | Each app has one | MyGrow Identity only |
| Password reset | Each app has one | MyGrow Identity only |
| Email verification | Each app does it | MyGrow Identity only |
| 2FA | Scattered | MyGrow Identity only |
| User table | Separate per app | Platform `users` table |
| Auth middleware | Custom per guard | Gateway validation |

### MyGrow Identity Components

```
MyGrow Identity (auth.mygrownet.com)
├── Login                  (POST /login → session + redirect)
├── Logout                 (POST /logout → invalidate session)
├── Register               (POST /register → create user + redirect)
├── Password Reset         (forgot / reset flows)
├── Email Verification     (verify link → mark verified)
├── Two-Factor Auth        (setup / verify / recovery codes)
├── Session Validation     (validate cookie sessions for browser apps)
├── Token Validation       (validate Sanctum tokens for mobile/API)
├── Sanctum Token Minting  (issue API tokens — single authority)
├── Application Redirect   (redirect back with signed+expiring return_url)
└── Rate Limiting          (per-IP + per-user throttling, lockout, anomaly detection)
```

### Steps

Phase 8 is split into six sequenced sub-phases. Each has a clear deliverable, and later sub-phases are not started until the previous one is validated in production.

---

#### Phase 8a: Foundation (completed)

Clean up the dead `primeedge` guard and middleware that Phase 8b+ will not need. No new functionality — just removing what the gateway replaces.

- Remove `primeedge` guard and provider from `config/auth.php` (no users ever existed)
- Remove `ResolveSubdomainAuth` middleware (file deleted, entry removed from `bootstrap/app.php`)
- Update PrimeEdge routes from `auth:primeedge` → `auth:web` and `guest:primeedge` → `guest`
- Update architecture docs and ADR-006 to reflect MyGrow Identity naming
- Mark `prime_edge/` migration folder as Legacy in AGENTS.md canonical table

---

#### Phase 8b: Build MyGrow Identity Gateway ✅ (Jul 2026)

Gateway built at `auth.mygrownet.com` with 16 routes (login, register, password reset, email verify, 2FA, session validation), 7 Blade controllers, 7 Tailwind views. `DetectSubdomain` handler added. `HandleInertiaRequests` skips Inertia for gateway. 3 rate limiters registered. Sanctum token minting deferred — `mintToken()` throws until `composer require laravel/sanctum`.

**8b.1 Design contract**

Define the interface between applications and MyGrow Identity before writing code. Applications should not need to change when the underlying identity provider changes.

```php
// config/platform.php
'identity' => [
    'login_url' => env('IDENTITY_LOGIN_URL', 'https://auth.mygrownet.com/login'),
    'register_url' => env('IDENTITY_REGISTER_URL', 'https://auth.mygrownet.com/register'),
],
```

Applications reference this config:
```php
redirect()->away(config('platform.identity.login_url') . '?return_url=' . urlencode($currentUrl));
```

```
interface MyGrowIdentity
{
    public function authenticate(Request $request): AuthResult;
    public function validateSession(string $token): ?User;  // supports cookie + token modes
    public function redirectToLogin(string $returnUrl): RedirectResponse;
    public function getLoginUrl(): string;
}
```

**8b.2 Build the gateway routes**

Create the shared auth routes under `routes/my-grow-identity.php`. These routes are served exclusively by `auth.mygrownet.com` — the dedicated identity subdomain within the same Laravel application:

| Method | URI | Name | Purpose |
|---|---|---|---|
| GET | `/login` | `identity.login` | Login form |
| POST | `/login` | `identity.login.store` | Authenticate |
| POST | `/logout` | `identity.logout` | Logout |
| GET | `/register` | `identity.register` | Registration form |
| POST | `/register` | `identity.register.store` | Create account |
| GET | `/password/reset` | `identity.password.request` | Forgot password |
| POST | `/password/email` | `identity.password.email` | Send reset link |
| GET | `/password/reset/{token}` | `identity.password.reset` | Reset form |
| POST | `/password/reset` | `identity.password.update` | Save new password |
| GET | `/email/verify/{id}/{hash}` | `identity.verification.verify` | Verify email |
| POST | `/email/verification-notification` | `identity.verification.send` | Resend verification |
| GET | `/session/validate` | `identity.session.validate` | Session + token validation API |
| GET | `/2fa/setup` | `identity.2fa.setup` | Configure 2FA |
| POST | `/2fa/verify` | `identity.2fa.verify` | Verify 2FA code |

These routes are the only authentication routes on the entire platform. No application may expose its own versions.

**8b.3 Session/token validation**

`MyGrowIdentity::validateSession(string $token)` is designed from day one for two modes:

- **Cookie-based** — browser requests on `.mygrownet.com` subdomains read the shared session
- **Token-based** — mobile apps, API clients, and custom domains pass a Sanctum token or signed JWT

The validator detects the request type and dispatches to the correct handler. MyGrow Identity is the issuing authority for both — tokens and sessions are minted by the gateway, not by individual applications.

**8b.4 Sanctum integration**

Phase 5's Public API uses Sanctum tokens. Phase 8 does not replace Sanctum — it becomes the single issuer:

```
                MyGrow Identity
                       │
          ┌────────────┴────────────┐
          │                         │
     Browser                   Mobile / API
          │                         │
     Session cookie           Sanctum token
   (.mygrownet.com)     (minted by MyGrow Identity)
          │                         │
          └────────────┬────────────┘
                       │
              validateSession()
              dispatches by request type
```

Sanctum tokens are minted by MyGrow Identity, not by StockFlow or any other application. Existing Sanctum tokens remain valid; new tokens go through the gateway.

**8b.5 Rate limiting and abuse prevention**

Consolidating all login attempts onto one endpoint raises the attack value proportionally. These protections are required, not optional:

- `auth.mygrownet.com/login` uses Laravel's `RateLimiter` — per-IP limits (generous) and per-user throttling (aggressive, < 5 attempts/min)
- Account lockout after N consecutive failures with exponential backoff
- Anomaly detection for credential-stuffing patterns (e.g., same IP attempting logins for 50+ accounts)
- All abuse metrics are logged and alertable

---

#### Phase 8c: Application Redirect Rollout ✅ (Jul 2026)

`RedirectToMyGrowIdentity` middleware built with HMAC+expiry signing. Applied to StockFlow and PrimeEdge route groups. Per-app kill switches in `config/platform.php` (all default `false`). `identity.redirect` alias registered in Kernel. StockFlow AuthController and Admin AuthController rewritten as redirect proxies.

**8c.1 Build the redirect middleware**

Every application that previously owned its own auth must now redirect unauthenticated users to the gateway. **Open redirect protection is mandatory** — a raw `return_url` without validation creates a high-value phishing vector on the single login surface of the entire platform.

The HMAC payload includes an expiry timestamp. A leaked `return_url` in browser history, server logs, or a screenshot is valid for only 5 minutes.

```
// Application middleware — generates a signed+timestamped return_url
class RedirectToMyGrowIdentity
{
    public function handle($request, $next)
    {
        if (!Auth::check()) {
            $returnUrl = $request->fullUrl();
            $expires = time() + config('platform.identity.return_url_ttl', 300);
            $payload = $returnUrl . '|' . $expires;
            $signature = hash_hmac('sha256', $payload, config('platform.identity.signing_key'));
            $loginUrl = config('platform.identity.login_url');
            return redirect()->away($loginUrl
                . '?return_url=' . urlencode($returnUrl)
                . '&expires=' . $expires
                . '&signature=' . $signature);
        }
        return $next($request);
    }
}
```

On the Identity Gateway side, before redirecting back:

```
// Gateway verifier — validate return_url before redirecting
function validateReturnUrl(string $returnUrl, string $signature, int $expires): bool
{
    // 1. Expiry check — reject stale links
    if (time() > $expires) {
        return false;  // redirect to default workspace instead
    }

    // 2. HMAC verification
    $payload = $returnUrl . '|' . $expires;
    $expected = hash_hmac('sha256', $payload, config('platform.identity.signing_key'));
    if (!hash_equals($expected, $signature)) {
        return false;
    }

    // 3. Allow-list enforcement
    $host = parse_url($returnUrl, PHP_URL_HOST);
    $allowed = config('platform.identity.allowed_return_hosts', ['*.mygrownet.com']);
    foreach ($allowed as $pattern) {
        if (fnmatch($pattern, $host)) {
            return true;
        }
    }

    return false; // host not in allow-list — redirect to workspace instead
}
```

Full config:

```php
// config/platform.php
'identity' => [
    'login_url'            => env('IDENTITY_LOGIN_URL', 'https://auth.mygrownet.com/login'),
    'register_url'         => env('IDENTITY_REGISTER_URL', 'https://auth.mygrownet.com/register'),
    'signing_key'          => env('IDENTITY_SIGNING_KEY'),          // HMAC key shared between apps and gateway
    'return_url_ttl'       => env('IDENTITY_RETURN_URL_TTL', 300),  // seconds
    'allowed_return_hosts' => ['*.mygrownet.com'],
    'app_redirect_enabled' => [     // per-app kill switch — all default false
        'stockflow'   => env('IDENTITY_REDIRECT_STOCKFLOW', false),
        'growbuilder' => env('IDENTITY_REDIRECT_GROWBUILDER', false),
    ],
],
```

**Never delete authentication code immediately.** Old auth pages remain deployed as redirect proxies until validated:

```php
// StockFlow login route becomes a signed, expiring redirect proxy:
Route::get('/login', function () {
    $returnUrl = request()->fullUrl();
    $expires = time() + config('platform.identity.return_url_ttl', 300);
    $payload = $returnUrl . '|' . $expires;
    $signature = hash_hmac('sha256', $payload, config('platform.identity.signing_key'));
    return redirect()->away(config('platform.identity.login_url')
        . '?return_url=' . urlencode($returnUrl)
        . '&expires=' . $expires
        . '&signature=' . $signature
        . '&app=stockflow');
})->name('stockflow.sub.login');
```

**8c.2 Per-app rollout**

1. Set `IDENTITY_REDIRECT_STOCKFLOW=true` in production `.env` for the first application
2. Monitor authentication success rates, error logs, and support tickets
3. If something breaks, flip back to `false` — old auth is still deployed and functional
4. Only after validation, move to the next application
5. Repeat until every application's flag is `true`

**8c.3 Remove old auth pages per-app**

Once an application has been running on gateway redirects in production with zero incidents:
- Delete its standalone `LoginController`, `RegisterController`, etc.
- Remove its auth route definitions (not the redirect proxy — that stays)
- Remove its application-level `Auth` middleware references

---

#### Phase 8d: StockFlow Migration ✅ (Jul 2026)

StockFlow guard and `stockflow_users` provider removed from `config/auth.php`. `auth:stockflow` → `auth:web` in all route files. `StockFlowPermission`, `StockFlowAdminMiddleware`, `LandingController` updated to use `Auth::guard('web')`. `HandleInertiaRequests`, `ShareModulesData`, `LaravelIdentityProvider` cleaned of SaUserModel references. `stockflow:check-guard-usage` command created. **No exit criterion wait** — StockFlow had 0 real users, making guard removal safe immediately.

**Completed actions:**
1. ✅ Audit determined StockFlow had 0 real users and PrimeEdge had 0 users — exit criterion was effectively met
2. ✅ Guard and provider removed from `config/auth.php`
3. ✅ All routes changed from `auth:stockflow` → `auth:web`
4. ✅ `SaUserModel.php` deleted
5. ✅ StockFlow auth controllers rewritten as redirect proxies
6. ✅ `MergeDuplicateUsers` command created (runs before table drop as safety net)
7. ✅ Redirect middleware enabled for StockFlow routes

---

#### Phase 8e: Legacy Table Decommissioning ✅ (Jul 2026)

`SaUserModel.php` deleted. `MergeDuplicateUsers` command created. Migration `2026_07_21_000001_drop_legacy_user_tables.php` drops `sa_users` and `prime_edge_clients` with FK cleanup. No real users existed in either table — all code actions completed, migration runs on deploy.

---

#### Phase 8f: Custom Domain SSO (designed, implementation deferred)

The gateway supports custom domains (e.g., `taradasidental.com`) that cannot share `.mygrownet.com` cookies. The interface handles this from day one (`validateSession(string $token)`), but the implementation is deferred until an organization actually requests a custom domain.

**8f.1 JWT exchange flow**

```
Custom domain → redirect to auth.mygrownet.com/login
    → authenticate
    → gateway issues short-lived signed JWT
    → redirect back to custom domain with JWT in URL
    → application validates JWT against validateSession() API
```

**8f.2 JWT replay protection**

The JWT travels as a URL query parameter, which means it can appear in server access logs and Referer headers. Two mitigation options — decide during staging security review before enabling custom domains:

- **Single-use nonce (jti):** Include a unique `jti` claim in the JWT. Gateway tracks used `jti` values (Redis, TTL = JWT lifetime). A replayed URL with the same `jti` is rejected.
- **POST redirect:** Gateway auto-submits a hidden form that delivers the JWT in the request body instead of the query string, keeping it out of access logs and Referer headers.

### Verification

**Authentication flow:**
- An unauthenticated visitor to `stockflow.mygrownet.com` is redirected to `auth.mygrownet.com/login`
- After login, they are redirected back to their original application
- Password reset, email verification, and 2FA all work through MyGrow Identity
- No application has its own `/login` or `/register` route that authenticates — StockFlow routes are signed HMAC redirect proxies
- `config('platform.identity.login_url')` is the single source of truth for the login URL
- The `stockflow` guard has been removed (Phase 8d) — all apps use the `web` guard
- New applications integrate with MyGrow Identity without writing auth code

**Security (open redirect protection):**
- `return_url` is HMAC-signed by the originating application, verified by the gateway
- HMAC payload includes an expiry timestamp — links are rejected after `return_url_ttl` (default 300s)
- Gateway enforces allow-list (`*.mygrownet.com` or explicitly configured hosts)
- Rejected signatures, expired links, or hosts not in allow-list redirect to `mygrownet.com/workspace` instead

**Rate limiting & abuse prevention (single high-value endpoint):**
- `auth.mygrownet.com/login` uses Laravel's `RateLimiter` — per-IP limits (generous) and per-user throttling (aggressive, < 5 attempts/min)
- Account lockout after N consecutive failures, with exponential backoff
- Anomaly detection (e.g., same IP attempting logins for 50 different accounts) flagged via notification
- These protections are required, not optional — consolidating all login attempts onto one endpoint raises the attack value proportionally

**Kill switch (per-app feature flag):**
- Each application has `config('platform.identity.app_redirect_enabled.{app_slug}')` — defaults to `false`
- Flipped to `true` only after that specific app is validated in production
- If a rollout breaks for one app, flip that app's flag back to `false` without affecting any other app
- The old auth code remains deployed and functional until every app is validated and the flag is removed

**Token-based validation (custom domains, mobile, API):**
- `MyGrowIdentity::validateSession(string $token)` supports both cookie-based (browser → `.mygrownet.com` cookie) and token-based (custom domains, mobile apps, partner integrations) validation
- Browser requests: session cookie → auto-detected
- Mobile/API requests: Sanctum token → validated by MyGrow Identity
- MyGrow Identity is the issuing authority for both — Sanctum tokens are minted by MyGrow Identity, not by individual applications

**Custom domain SSO:**
- For custom domains (e.g., `taradasidental.com`) that cannot share `.mygrownet.com` cookies, authentication uses a signed JWT exchange instead of a shared session
- Flow: custom domain → redirect to `auth.mygrownet.com/login` → authenticate → gateway issues short-lived signed JWT → return to custom domain → application validates JWT against `validateSession()` API
- The interface supports both modes from day one — see `validateSession(string $token)` in the contract

**⚠️ JWT replay protection:** The JWT travels as a URL query parameter on the redirect, which means it can appear in server access logs and Referer headers. Two mitigation options (choose one during implementation):
  - **Single-use nonce (jti):** Include a unique `jti` claim in the JWT. The gateway tracks used `jti` values (Redis, TTL = JWT lifetime). A replayed URL with the same `jti` is rejected.
  - **POST redirect:** Instead of a GET redirect with the JWT in the query string, the gateway auto-submits a hidden form (POST) that delivers the JWT in the request body, keeping it out of access logs and Referer headers.

Neither blocks implementation — decide during staging security review.

**Local development:**
- Use `*.mygrow.test` via `/etc/hosts` (or Laravel Herd/Valet) so the `.mygrow.test` session cookie works across subdomains
- `SESSION_DOMAIN=.mygrow.test` in `.env` for local development
- The `allowed_return_hosts` allow-list must include `*.mygrow.test` in non-production environments

**Legacy table decommissioning:**
- `MergeDuplicateUsers` command runs before any table is dropped
- Every merge action is logged for audit
- Ambiguous cases require manual review — no silent data loss

### Risk
High — auth is the most sensitive subsystem. Must be validated on staging against real account types before production rollout. The sub-phase structure (8a → 8b → 8c → 8d → 8e → 8f) with per-app kill switches ensures a failure in one application never affects others. Phases 8b–8e are code-complete; 8f deferred. Sanctum must be installed (`composer require laravel/sanctum && php artisan install:api`) for token minting to work.

### What Phase 8 is NOT
- It is NOT a microservice extraction — MyGrow Identity lives inside the monolith at `auth.mygrownet.com`
- It is NOT a replacement for Sanctum — Phase 5's Public API continues using Sanctum tokens for mobile/API auth. MyGrow Identity mints both: Sanctum tokens for mobile/API, sessions for browser. `validateSession()` dispatches to the correct validator based on request type. (Note: Sanctum not yet installed — `mintToken()` throws at runtime.)
- It is NOT a rewrite of working auth — existing auth is redirected through MyGrow Identity by signed HMAC proxies, not replaced overnight
- It is NOT optional for new applications — any application added to MyGrowNet after Phase 8 must integrate with MyGrow Identity and must not implement its own auth

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
| StockFlow | High (full DDD, own subdomain) | Low |
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
| **8** | MyGrow Identity & Centralized Authentication (6 sub-phases: 8a ✅, 8b ✅, 8c ✅, 8d ✅, 8e ✅, 8f ⏳ Custom Domains deferred) | Phase 3, 7 | High |
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
| ADR-006 | MyGrow Identity & Centralized Authentication |
| ADR-007 | Event-Driven Cross-Module Communication |

Each ADR should contain: **Context → Decision → Consequences** — one page, no more.

---

## Key Principles

1. **Never break working code** — each phase runs alongside existing systems
2. **Feature flags** — new behavior behind config flags until validated
3. **Parallel run** — old and new systems run concurrently during transition
4. **Rollback always possible** — each phase can be rolled back independently
5. **Authentication belongs exclusively to the Platform** — applications may authorize, but never authenticate. This is permanent, not temporary.
6. **Only one authentication system** — after Phase 8, there will be exactly one auth system for the entire platform. This is not negotiable for future applications.
7. **No microservices** — modular monolith unless extraction is proven necessary by operational data
8. **Config over DB for runtime behavior** — database identifies; config determines behavior
9. **Identity-provider agnostic** — abstract auth so Passport, Keycloak, Auth0, or custom can be swapped without changing applications
10. **Platform Core** — the core depends on nothing; every module depends on the core
11. **Module versioning** — each module versions independently; Core defines compatibility contracts
12. **Platform Core owns shared concerns** — if two or more applications require the same capability, evaluate moving it into Platform Core instead of duplicating it in each module
