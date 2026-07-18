# Phase 1 Implementation Plan

## Objective

Create the platform foundation: organizations, applications, application registry, and workspace routing infrastructure — without modifying existing functionality.

## Key Design Decisions

- **No `core_` prefix** — platform tables use plain names (`organizations`, `applications`)
- **No migration of existing data** — `cms_companies`, `sa_companies`, `bizboost_businesses` have no users; they remain as dead schema
- **Existing middleware stays** — WorkspaceResolver runs alongside, not replacing DetectSubdomain
- **No auth changes** — User model, guards, Spatie roles untouched

---

## Part 1: Migration Files

All migrations go in `database/migrations/` with timestamps in correct order.

### Migration 1: Create `applications` table

```php
Schema::create('applications', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('type'); // BUSINESS, CONSUMER, NETWORK, TOOL
    $table->string('url')->nullable();
    $table->json('config')->nullable(); // routing, middleware, service provider settings
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

**config JSON structure:**
```json
{
    "domain_slug": "stockflow",
    "service_provider": "App\\Providers\\StockAuditServiceProvider",
    "middleware": ["web", "auth", "stockflow"],
    "uses_inertia": true,
    "blade_layout": null,
    "url_prefix": null,
    "session_prefix": "stockflow"
}
```

### Migration 2: Create `organizations` table

```php
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('type')->default('company'); // company, non_profit, individual, etc.
    $table->string('status')->default('active'); // active, suspended, archived
    $table->foreignId('owner_id')->constrained('users');
    $table->json('settings')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### Migration 3: Create `organization_branches` table

```php
Schema::create('organization_branches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->string('code')->nullable();
    $table->text('address')->nullable();
    $table->string('status')->default('active');
    $table->timestamps();
});
```

### Migration 4: Create `departments` table

```php
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('branch_id')->nullable()->constrained('organization_branches')->nullOnDelete();
    $table->string('name');
    $table->string('status')->default('active');
    $table->timestamps();
});
```

### Migration 5: Create `organization_members` table

```php
Schema::create('organization_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('active'); // active, invited, suspended, removed
    $table->timestamp('joined_at')->nullable();
    $table->timestamps();

    $table->unique(['organization_id', 'user_id']);
});
```

### Migration 6: Create `organization_applications` table

```php
Schema::create('organization_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
    $table->foreignId('application_id')->constrained()->cascadeOnDelete();
    $table->string('status')->default('active'); // active, suspended, expired, cancelled
    $table->timestamp('subscribed_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();

    $table->unique(['organization_id', 'application_id']);
});
```

### Migration 7: Create `user_applications` table

```php
Schema::create('user_applications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('application_id')->constrained()->cascadeOnDelete();
    $table->string('relationship_type'); // member, customer, admin, manager, etc.
    $table->string('status')->default('active');
    $table->timestamps();

    $table->unique(['user_id', 'application_id']);
});
```

### Migration 8: Create `custom_domains` table

```php
Schema::create('custom_domains', function (Blueprint $table) {
    $table->id();
    $table->string('domain')->unique();
    $table->morphs('owner'); // belongs to organization or application
    $table->string('status')->default('pending'); // pending, verified, active, expired
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
});
```

---

## Part 2: Models

Create under `app/Domain/Core/Models/`.

Follow StockFlow DDD conventions but keep models thin (Core is foundation, not a business module).

### Organization model

- Table: `organizations`
- Fillable: `uuid`, `name`, `slug`, `type`, `status`, `owner_id`, `settings`
- Casts: `settings` → `array`, `uuid` → `string`
- Relations:
  - `owner()` → belongsTo User
  - `branches()` → hasMany OrganizationBranch
  - `departments()` → hasMany Department
  - `members()` → hasMany OrganizationMember
  - `applications()` → belongsToMany Application via organization_applications

### OrganizationBranch model

- Table: `organization_branches`
- Fillable: `organization_id`, `name`, `code`, `address`, `status`
- Relations:
  - `organization()` → belongsTo Organization
  - `departments()` → hasMany Department

### Department model

- Table: `departments`
- Fillable: `organization_id`, `branch_id`, `name`, `status`
- Relations:
  - `organization()` → belongsTo Organization
  - `branch()` → belongsTo OrganizationBranch

### OrganizationMember model

- Table: `organization_members`
- Fillable: `organization_id`, `user_id`, `status`, `joined_at`
- Casts: `joined_at` → `datetime`
- Relations:
  - `organization()` → belongsTo Organization
  - `user()` → belongsTo User

### Application model

- Table: `applications`
- Fillable: `name`, `slug`, `type`, `url`, `config`, `is_active`
- Casts: `config` → `array`, `is_active` → `boolean`
- Relations:
  - `organizations()` → belongsToMany Organization via organization_applications
  - `users()` → belongsToMany User via user_applications

### CustomDomain model

- Table: `custom_domains`
- Fillable: `domain`, `owner_id`, `owner_type`, `status`, `verified_at`
- Casts: `verified_at` → `datetime`
- Morphs: `owner()` → morphTo

---

## Part 3: Services

Under `app/Domain/Core/Services/`.

### OrganizationService

Responsible for CRUD of organizations and their sub-resources.

Methods:
- `create(array $data): Organization` — creates org with UUID, slug generation
- `update(Organization $org, array $data): Organization`
- `archive(Organization $org): void`
- `addMember(Organization $org, User $user, ?string $status): OrganizationMember`
- `removeMember(Organization $org, User $user): void`
- `getMembers(Organization $org): Collection`
- `addBranch(Organization $org, array $data): OrganizationBranch`
- `addDepartment(Organization $org, array $data): Department`
- `getUserOrganizations(User $user): Collection`

### ApplicationRegistry

Responsible for listing and resolving applications.

Methods:
- `all(): Collection` — list all active applications
- `findBySlug(string $slug): ?Application`
- `getForOrganization(Organization $org): Collection`
- `getForUser(User $user): Collection`
- `isAvailableForOrganization(Application $app, Organization $org): bool`
- `getRoutingConfig(string $slug): ?array` — returns config JSON for routing

### WorkspaceResolver

Responsible for resolving incoming requests to an application or organization.

Methods:
- `resolveFromSubdomain(string $host): ?ResolvedWorkspace`
- `resolveFromCustomDomain(string $host): ?ResolvedWorkspace`
- `resolve(string $host): ?ResolvedWorkspace` — tries custom domain first, then subdomain

**ResolvedWorkspace value object:**
```php
class ResolvedWorkspace
{
    public function __construct(
        public readonly string $type,        // 'application', 'organization', 'custom'
        public readonly ?Application $application,
        public readonly ?Organization $organization,
        public readonly ?CustomDomain $domain,
    ) {}
}
```

---

## Part 4: Application Registry Seed

`database/seeders/ApplicationRegistrySeeder.php`

| Name | Slug | Type | Domain Slug |
|---|---|---|---|
| StockFlow | stockflow | BUSINESS | stockflow |
| BMS | bms | BUSINESS | cms |
| GrowMart | growmart | CONSUMER | growmart |
| ZamStay | zamstay | CONSUMER | zamstay |
| GrowNet | grownet | NETWORK | grownet |
| BizDocs | bizdocs | BUSINESS | bizdocs |
| BizBoost | bizboost | BUSINESS | bizboost |
| GrowBuilder | growbuilder | BUSINESS | growbuilder |
| GrowFinance | growfinance | BUSINESS | growfinance |

---

## Part 5: Implementation Order

### Step 1 — Database (safe, no existing code depends on these tables)

1. Create migration 1: `applications`
2. Create migration 2: `organizations`
3. Create migration 3: `organization_branches`
4. Create migration 4: `departments`
5. Create migration 5: `organization_members`
6. Create migration 6: `organization_applications`
7. Create migration 7: `user_applications`
8. Create migration 8: `custom_domains`
9. Run `php artisan migrate`
10. Run seeder

**Verify:** `php artisan db:show` shows all 8 new tables.

### Step 2 — Models (safe, new files only)

1. Create `app/Domain/Core/Models/Organization.php`
2. Create `app/Domain/Core/Models/OrganizationBranch.php`
3. Create `app/Domain/Core/Models/Department.php`
4. Create `app/Domain/Core/Models/OrganizationMember.php`
5. Create `app/Domain/Core/Models/Application.php`
6. Create `app/Domain/Core/Models/CustomDomain.php`

**Verify:** `php artisan tinker` can instantiate each model.

### Step 3 — Services (safe, new files only)

1. Create `app/Domain/Core/Services/OrganizationService.php`
2. Create `app/Domain/Core/Services/ApplicationRegistry.php`
3. Create `app/Domain/Core/Services/WorkspaceResolver.php`
4. Create `app/Domain/Core/Services/ResolvedWorkspace.php` (value object)

**Verify:** Unit test or tinker session that creates an org, adds members, queries applications.

### Step 4 — Wire into app (minimal integration)

1. Register `app/Domain/Core/` in composer autoload (PSR-4) if not auto-discovered
2. Optionally create a diagnostic route (e.g., `GET /_platform/workspace`) that returns resolved workspace info — useful for testing without affecting users

### Step 5 — Routing design (no code changes to existing middleware)

Document the WorkspaceResolver integration points in the existing DetectSubdomain middleware:
- After the subdomain switch, optionally call WorkspaceResolver to log the resolved workspace
- This validates the resolver logic without changing behavior

---

## Part 6: Verification

After each step:

| What | How |
|---|---|
| Tables exist | `php artisan db:show` or direct DB query |
| Models work | `php artisan tinker` → `Organization::create(...)` |
| Seed data present | Query `Application::all()` — 9 rows |
| Services resolve | `app(WorkspaceResolver::class)->resolve('stockflow.mygrownet.com')` |
| No regression | Login, navigate existing pages — everything unchanged |
| Existing users intact | `User::count()` matches before/after |
| DetectSubdomain still works | Visit any existing subdomain — no change |

---

## Part 7: Rollback Plan

If Phase 1 breaks something:

```bash
php artisan migrate:rollback --step=8   # drops all 8 new tables
```

This is safe because:
- No existing code references these tables
- No existing data is affected
- Existing middleware still works unchanged
- No migrations modify existing tables

To also remove code:
```bash
rm -rf app/Domain/Core/
```

---

## What Phase 1 Does NOT Cover

- ❌ Create `users` table (already exists)
- ❌ Modify User model or any existing model
- ❌ Modify authentication (web, stockflow, primeedge guards)
- ❌ Modify Spatie roles/permissions tables
- ❌ Remove or modify DetectSubdomain middleware
- ❌ Rewrite any module (GrowNet, StockFlow, GrowFinance, etc.)
- ❌ Migrate data from existing company tables (`cms_companies`, `sa_companies`, etc.)
- ❌ Create organization onboarding flow or UI
- ❌ Add authorization (policies, gates) — Phase 2+
