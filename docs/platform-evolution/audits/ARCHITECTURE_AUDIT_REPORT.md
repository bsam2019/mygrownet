# MyGrowNet Architecture Audit Report

## 1. Executive Summary

MyGrowNet is a **Laravel 13.x monolith** (PHP 8.3+) with a Vue 3 + Vite frontend. It contains approximately **500+ database tables** across 25+ modules, 41 route files, 56 custom middleware classes, and 40 service providers.

**Key findings:**

- **Authentication**: 3 separate auth systems (web, stockflow, primeedge) with 3 different user tables — the primary target for consolidation
- **User model**: A 2331-line god object mixing identity, MLM, wallet, loans, subscriptions, and module data across ~100 fields
- **Roles & Permissions**: Spatie v6 installed with custom Role/Permission models, but not application-aware (no application_id on roles)
- **Subdomain routing**: Centralized `DetectSubdomain` middleware (671 lines) with hardcoded switch statements for 13+ subdomains
- **Module architecture**: Three patterns coexist — full DDD (StockFlow), partial DDD (GrowFinance, GrowMart, GrowBuilder), traditional MVC (GrowNet, ZamStay)
- **Organization structure**: CMS and StockFlow already have company/organization tables with `company_id` scoping — partial foundation exists
- **No authentication packages**: No Laravel Passport, Fortify, Breeze, or Jetstream installed — auth is entirely custom-built

**Phase 1 is safe to proceed.** The audit confirms that creating Core platform tables (organizations, applications, workspace routing) will not conflict with existing tables or break existing functionality, provided `users` or `credentials` tables are **not** created in Phase 1.

---

## 2. Current Architecture Overview

### Application Stack

| Component | Version |
|---|---|
| Laravel | 13.x |
| PHP | ^8.3 |
| Frontend | Vue 3.5 + Inertia.js 2 |
| Build | Vite 6 + laravel-vite-plugin |
| CSS | Tailwind 3 + HeadlessUI + Heroicons |
| State | Pinia 3 |
| Charts | Chart.js + vue-chartjs |
| Real-time | Laravel Reverb (WebSockets) |
| Queue | Redis (predis) |

### Directory Structure

```
app/
├── Domain/              ← DDD modules (StockFlow, GrowFinance, GrowMart, etc.)
├── Enums/               ← AccountType, other app-level enums
├── Http/
│   ├── Controllers/     ← 25+ controller directories
│   └── Middleware/       ← 56 custom middleware classes
├── Infrastructure/
│   └── Persistence/
│       └── Eloquent/    ← Eloquent models for all DDD modules
├── Models/              ← Laravel models (User, Role, Permission, etc.)
├── Providers/           ← 40 service providers
├── Services/            ← Various service classes (non-DDD)
└── Traits/              ← Reusable traits (HasActivityLogs, etc.)

database/
├── migrations/          ← ~500+ migration files
└── migrations/zamstay/  ← ZamStay-specific migrations

routes/                  ← 41 route files

resources/js/
├── components/
├── composables/
├── layouts/
├── pages/               ← Vue pages (Inertia)
├── stores/              ← Pinia stores
└── types/               ← TypeScript types
```

### Architectural Patterns (3 coexisting)

| Pattern | Modules | Characteristics |
|---|---|---|
| **Full DDD** | StockFlow | Pure domain layer with Entities, ValueObjects, Services, Repository interfaces. Infrastructure layer with Eloquent implementations. Zero Laravel dependencies in domain. |
| **Partial DDD** | GrowFinance, GrowMart, GrowBuilder, BizDocs, BizBoost, Ubumi, Wedding, Support, Storage, Tools, Wallet | Domain directories with services + value objects. Some have entities/repositories, some don't. Varies by module. |
| **Traditional MVC** | GrowNet, ZamStay, LifePlus, PrimeEdge, GrowStart, GrowStorage | Controllers call Eloquent models directly. No formal domain layer. |

---

## 3. Authentication Audit

### Three Separate Auth Systems

| System | Guard | Provider | Model | Table |
|---|---|---|---|---|
| Main site | `web` (default) | `users` | `App\Models\User` | `users` |
| PrimeEdge | `primeedge` | `primeedge_clients` | `App\Infrastructure\PrimeEdge\Persistence\ClientModel` | `prime_edge_clients` |
| StockFlow | `stockflow` | `stockflow_users` | `App\Infrastructure\Persistence\Eloquent\StockFlow\SaUserModel` | `sa_users` |

### Auth Flow Details

**Main site (web guard):**
- Blade-based auth (`BladeAuthController`) — NOT Inertia
- Google OAuth via Socialite (multi-domain callback support)
- Email + phone registration with referral code + 3x3 matrix placement
- Role-based post-login redirect (admin → admin.dashboard, employee → employee portal, member → dashboard)

**StockFlow (stockflow guard):**
- Separate login on `{account}.mygrownet.com` subdomains
- Own `AuthController` at `StockFlow\Auth`
- Own user table (`sa_users`) — completely separate from main `users`
- Session-based, same `SESSION_DOMAIN` sharing

**PrimeEdge (primeedge guard):**
- Separate login on `primeedge.mygrownet.com` subdomain
- Own `ClientModel` (table: `prime_edge_clients`)
- Own auth controllers under `PrimeEdge\Auth\`

### Key Findings

| Aspect | Finding |
|---|---|
| **OAuth packages** | Laravel Socialite installed (Google OAuth only). **No Passport, no Fortify, no Breeze, no Jetstream.** |
| **Sanctum** | Referenced in `api` middleware group but no published config. Used for SPA API auth. |
| **Session** | Shared across subdomains via `SESSION_DOMAIN=.mygrownet.com` (production). Cookie: `mygrownet_session`. Lifetime: 7 days (PWA optimization). |
| **Auth routes (Inertia)** | `routes/auth.php` is **commented out** — Blade auth is primary. |
| **CSRF** | Custom `RefreshCsrfToken` middleware refreshes on every request for PWA. |

### Phase 1 Impact

**Do not touch these auth systems.** They continue working as-is. The Core platform adds new tables but does not replace auth.

---

## 4. User Model Audit

**File:** `app/Models/User.php` — **2331 lines** (god object)

### Fields Grouped by Domain

#### Identity (14 fields — should stay in User)
`name`, `email`, `password`, `phone`, `phone_verified_at`, `email_verified_at`, `address`, `status`, `last_login_at`, `profile_photo_path`, `preferred_dashboard`, `user_currency`, `preferred_currency`, `fcm_token`, `default_company_id`, `pwa_default_app`

#### GrowNet MLM (14 fields — should move to GrowNet)
`referrer_id`, `referral_code`, `referral_count`, `last_referral_at`, `network_path`, `network_level`, `matrix_position`, `rank`, `direct_referrals`, `current_team_volume`, `current_personal_volume`, `current_team_depth`, `active_referrals_count`, `tier_upgraded_at`

#### GrowNet Wallets (5 fields — should move to GrowNet)
`balance`, `bonus_balance`, `total_earnings`, `total_referral_earnings`, `total_profit_earnings`, `total_investment_amount`, `daily_withdrawal_used`, `daily_withdrawal_reset_date`

#### GrowNet Loyalty (7 fields — should move to GrowNet)
`loyalty_points`, `loyalty_points_awarded_total`, `loyalty_points_withdrawn_total`, `lgr_custom_withdrawable_percentage`, `lgr_withdrawal_blocked`, `lgr_restriction_reason`

#### GrowNet Subscriptions (4 fields — should move to GrowNet)
`monthly_subscription_fee`, `subscription_start_date`, `subscription_end_date`, `subscription_status`

#### GrowNet Starter Kit (7 fields — should move to GrowNet)
`has_starter_kit`, `starter_kit_tier`, `starter_kit_purchased_at`, `starter_kit_terms_accepted`, `starter_kit_terms_accepted_at`, `starter_kit_shop_credit`, `starter_kit_credit_expiry`

#### GrowNet Loans (6 fields — should move to GrowNet)
`loan_balance`, `loan_limit`, `total_loan_issued`, `total_loan_repaid`, `loan_issued_at`, `loan_issued_by`, `loan_notes`

#### Security (10 fields — can remain in User or move)
`is_blocked`, `block_reason`, `blocked_at`, `blocked_by`, `failed_login_attempts`, `last_failed_login_at`, `security_flags`, `risk_score`, `risk_assessed_at`, `requires_id_verification`, `is_id_verified`, `id_verified_at`, `verification_level`, `verification_completed_at`

#### Other Module Data
- Telegram: `telegram_chat_id`, `telegram_notifications`, `telegram_linked_at`, `telegram_link_code`
- LifePlus: `lifeplus_onboarded`, `lifeplus_notifications_enabled`
- Premium templates: `premium_template_tier`, `premium_access_granted_at`, `premium_access_granted_by`, `premium_access_notes`
- Account types: `account_type`, `account_types` (JSON)
- Wallet policy: `wallet_policy_accepted`, `wallet_policy_accepted_at`

### Relationships (45+)

**HasOne:** profile, subscription, activeSubscription, teamVolume, points, currentMonthActivity, notificationPreferences  
**HasMany:** referrals, transactions, withdrawals, commissions, businessPlans, teamVolumes, networkMembers, pointTransactions, badges, profitShares, activities, courseEnrollments, otpTokens, starterKitPurchases, auditLogs, idVerifications, ventureInvestments, bgfApplications, paymentTransactions, commissionPayments, bonusPayments — **36+ relations**  
**BelongsTo:** referrer, membershipTier, blockedBy, loanIssuedBy, premiumAccessGrantedBy  
**BelongsToMany:** enrolledCourses, completedCourses, achievements, agencies, portalCustomers

### Key Finding

~85% of the User model fields belong to GrowNet-specific functionality (MLM, wallets, commissions, starter kits, loans, loyalty points, subscriptions). The remaining ~15% are core identity.

**Phase 1 does not change this.** The `User` model remains untouched. Data separation happens in a later phase.

---

## 5. Roles and Permissions Audit

### Current System

| Aspect | Details |
|---|---|
| **Package** | Spatie Laravel Permission v6.21 |
| **Custom models** | `App\Models\Role`, `App\Models\Permission` (extend Spatie with `slug`, `description`, `is_active`) |
| **Tables** | `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` |
| **Teams** | Disabled (no team_id support) |
| **Guard** | `web` (default) |
| **Cache** | 24-hour cache TTL |
| **Wildcard permissions** | Disabled |

### How Permissions Are Used

- **Spatie middleware aliases**: `admin`, `admin.or.role`, `employee`, `module.access`, `account.type`
- **StockFlow-specific**: `StockFlowPermission` middleware checks per-company permissions stored in `sa_company_roles` (JSON permissions column)
- **User trait**: `HasRoles` on User model — provides `hasRole()`, `hasPermission()`, `assignRole()`, etc.
- **Frontend**: `HandleInertiaRequests` shares `auth.user.roles` and `auth.user.permissions` as slug arrays (cached 5 min)

### Account Types vs Roles

The system has two parallel access control mechanisms:

| Mechanism | Purpose | Implementation |
|---|---|---|
| **Account types** | Functional classification (member, client, business, investor, employee) | Enum on User, JSON array in `account_types` column |
| **Spatie roles** | Permission groups (Administrator, Member, Client, Manager) | Standard Spatie `model_has_roles` |

These overlap but serve different purposes: account types control **module visibility**, roles control **actions within modules**.

### Limitation for Target Architecture

The current Spatie roles are **not application-aware** — there is no `application_id` column on roles. A user cannot have "Manager in StockFlow, Accountant in GrowFinance" without creating role-per-application workarounds.

**Phase 1 does not modify Spatie tables.** The existing system continues working. Application-aware roles are a future concern.

---

## 6. Database Audit

### Total Table Count by Module

| Module | Approx Tables | Pattern |
|---|---|---|
| **MyGrowNet Core (MLM)** | ~30 | Traditional migrations |
| **Investments / Profit-sharing** | ~15 | Traditional |
| **Points / LGR / Levels** | ~12 | Traditional |
| **Starter Kit** | ~7 | Traditional |
| **Learning / Courses** | ~5 | Traditional |
| **Community Projects** | ~7 | Traditional |
| **Gamification / Incentives** | ~12 | Traditional |
| **Venture Builder** | ~10 | DDD + Traditional |
| **BGF / Loans** | ~6 | Traditional |
| **Subscriptions / Modules** | ~15 | Traditional |
| **Communications (notifications, tickets, messages)** | ~10 | Traditional |
| **Security / Audit** | ~8 | Traditional |
| **CMS (BMS)** | ~100 | DDD + Traditional |
| **StockFlow** | ~60 | Full DDD |
| **GrowFinance** | ~25 | Partial DDD |
| **GrowMart** | ~12 | Partial DDD |
| **ZamStay** | ~4 | Traditional |
| **BizBoost** | ~36 | Partial DDD |
| **BizDocs** | ~9 | Partial DDD |
| **GrowBuilder** | ~22 | Partial DDD |
| **GrowBiz** | ~35 | Traditional |
| **GrowStart** | ~10 | Traditional |
| **GrowStream** | ~10 | Traditional |
| **Marketplace** | ~22 | Traditional |
| **LifePlus** | ~22 | Traditional |
| **Wedding** | ~6 | Partial DDD |
| **Ubumi** | ~8 | Partial DDD |
| **PrimeEdge** | ~11 | Traditional |
| **Site Builder** | ~16 | Traditional |
| **Investor Relations** | ~34 | Traditional |
| **Employee Portal** | ~20 | Traditional |
| **QuickInvoice** | ~10 | Traditional |
| **Agency** | ~15 | Traditional |
| **Storage** | ~7 | Partial DDD |
| **Other (orders, products, etc.)** | ~10 | Traditional |
| **Spatie (roles/permissions)** | 5 | Package |
| **Laravel core (sessions, cache, jobs, etc.)** | 10 | Framework |

### Existing Organization/Business Tables

The system already has several organization-related tables:

| Table | Module | Can Reuse? |
|---|---|---|
| `cms_companies` | CMS | No — it's CMS-specific with CMS fields |
| `cms_branches` | CMS | No — CMS-specific |
| `cms_departments` | CMS | No — CMS-specific |
| `cms_workers` | CMS | No — CMS employee records |
| `sa_companies` | StockFlow | No — StockFlow-specific with `sa_` prefix |
| `sa_departments` | StockFlow | No — StockFlow-specific |
| `sa_branches` | StockFlow | No — StockFlow-specific |
| `employees` | Main site | No — main site employee records (MLM) |
| `departments` | Main site | No — main site departments |
| `positions` | Main site | No — main site job positions |
| `bizboost_businesses` | BizBoost | No — BizBoost-specific |
| `growbiz_business_profiles` | GrowBiz | No — GrowBiz-specific |

**Key finding:** No generic, cross-module `organizations` or `organization_members` table exists. Each module has its own company/organization structure. An `organizations` table is safe to create as it fills a genuine gap.

---

## 7. Routing Audit

### Route File Loading Order

From `bootstrap/app.php`:

1. Subdomain route files (growmart, bizboost, zamstay, bizdocs, growbuilder, venture, grownet, growstorage, primeedge)
2. StockFlow subdomain files (stockflow-landing, stockflow-subdomain, stockflow-admin)
3. `web.php` (main domain)
4. CMS routes (cms-subdomain, cms)
5. Other modules (employee-portal, growbiz, growfinance, pos, inventory, marketplace, etc.)

Then from `RouteServiceProvider::boot()`:
- Additional routes loaded again (some loaded twice intentionally for subdomain matching order)

### Subdomain Resolution (DetectSubdomain Middleware)

```
Request → nginx → Laravel
              ↓
    DetectSubdomain Middleware (671 lines)
              ↓
    ├── Custom domain? → GrowBuilder site lookup
    ├── Reserved subdomain? → Skip (api, admin, mail, etc.)
    ├── Known product subdomain?
    │   ├── geopamu → GeopamuController
    │   ├── wowthem → WeddingController
    │   ├── cms → Inline CMS dispatch
    │   ├── growmart → Configure URL, block non-growmart routes
    │   ├── bizboost → configureSubdomainUrl()
    │   ├── bizdocs → configureSubdomainUrl()
    │   ├── growbuilder → configureSubdomainUrl()
    │   ├── venture → configureSubdomainUrl()
    │   ├── grownet → configureSubdomainUrl()
    │   ├── growstorage → configureSubdomainUrl()
    │   ├── zamstay → configureSubdomainUrl()
    │   ├── primeedge → configureSubdomainUrl()
    │   └── stockflow → configureSubdomainUrl()
    ├── Unknown subdomain?
    │   ├── StockFlow company? (CompanyRepository lookup)
    │   └── GrowBuilder site? (SiteRepository lookup)
    └── Not found? → 404
```

### Key Finding

The `DetectSubdomain` middleware has **hardcoded inline dispatch for CMS** (the most complex module). Adding a new product requires modifying this 671-line file. The proposed Routing Engine would replace this with database-driven resolution.

**Phase 1:** Create the Routing Engine design but **do not remove** the existing `DetectSubdomain` middleware. Run both in parallel until the new system is tested.

---

## 8. Module Audit

### Module Architecture Summary

| Module | Architecture | Auth Dependency | User Model Coupling | Has Domain Dir? |
|---|---|---|---|---|
| **StockFlow** | Full DDD | Separate (`stockflow` guard, `sa_users`) | Separate user table | Yes |
| **GrowFinance** | Partial DDD | Main (`web` guard) | User model (`default_company_id`) | Yes |
| **GrowMart** | Partial DDD | Main (`web` guard) | Minimal | Yes |
| **GrowBuilder** | Partial DDD | Main + Site auth | Minimal | Yes |
| **BizDocs** | Partial DDD | Main (`web` guard) | Minimal | Yes |
| **BizBoost** | Partial DDD | Main (`web` guard) | Minimal | Yes |
| **Ubumi** | Partial DDD | Main (`web` guard) | Minimal | Yes |
| **Wedding** | Partial DDD | Main (`web` guard) | Minimal | Yes |
| **Support** | Partial DDD | Main (`web` guard) | `User` (morph) | Yes |
| **Storage** | Partial DDD | Main (`web` guard) | `User` (FK) | Yes |
| **CMS (BMS)** | DDD + Traditional | Main (`web` guard) | `User` via `cms_users` | Partial |
| **GrowNet** | Traditional MVC | Main (`web` guard) | **Heavy** (~85% of User fields) | No |
| **ZamStay** | Traditional MVC | Main (`web` guard) | Minimal | No |
| **LifePlus** | Traditional MVC | Main (`web` guard) | `User` (FK) | No |
| **PrimeEdge** | Traditional MVC | Separate (`primeedge` guard) | Separate client table | No |
| **GrowStart** | Traditional MVC | Main (`web` guard) | Minimal | No |

### Key Finding

GrowNet is the most tightly coupled module — it's deeply embedded in the User model and main site routing. StockFlow is the most decoupled — it has full DDD, its own auth, its own user table, and its own subdomain.

**Phase 1 does not change any module's internal structure.** Each module continues working exactly as before.

---

## 9. Migration Risk Assessment

### High Risk

| Change | Risk | Mitigation |
|---|---|---|
| Creating `users` / `credentials` (duplicate) | Duplicate identity source, auth confusion | **Do not create in Phase 1** |
| Removing `DetectSubdomain` middleware | All subdomain routing breaks | **Do not remove in Phase 1** — design replacement only |
| Modifying Spatie roles/permissions tables | All existing authorization breaks | **Do not modify in Phase 1** |
| Migrating User model fields | Thousands of `$user->field` references break | **Do not migrate in Phase 1** |
| Changing Blade auth to Inertia auth | Login/register flow breaks for all users | **Do not change auth in Phase 1** |

### Medium Risk

| Change | Risk | Mitigation |
|---|---|---|
| Adding `organizations` table | No existing module references it — safe to create | Ensure no name collision with existing tables |
| Adding `applications` table | Safe — no existing module references it | Seed with existing product names |
| Adding Application Registry service | Safe — new service, no existing code depends on it | Keep separate from existing routing |
| Creating `app/Domain/Core/` | Safe — new directory, no existing code uses it | Follow StockFlow DDD pattern |

### Low Risk (Safe to Do in Phase 1)

- Creating `organizations` (no existing table with this name)
- Creating `organization_branches` (new table)
- Creating `departments` (no conflict — existing `departments` table is for main site)
- Creating `organization_members` (new table)
- Creating `applications` (new table — no existing `applications` table)
- Creating `organization_applications` (new table)
- Creating `user_applications` (new table)
- Creating `custom_domains` (new table)
- Creating `app/Domain/Core/` with models and services
- Creating `ApplicationRegistry` service
- Creating `WorkspaceResolver` service
- Designing Routing Engine (no migration yet)
- Seeding `applications` with existing products

---

## 10. Recommended Phase 1 Roadmap

### Step 1: Audit Review (current document)

Audit complete. Findings documented above.

### Step 2: Create Core Foundation

**New tables to create:**

```
organizations                ← NEW, no conflicts
organization_branches        ← NEW
departments                  ← NEW (no conflict with main site `departments`)
organization_members         ← NEW
applications                 ← NEW
organization_applications    ← NEW
user_applications            ← NEW
custom_domains               ← NEW
```

**Tables NOT to create (use existing):**

```
users           ← EXISTING, keep as-is
roles           ← EXISTING Spatie, keep as-is
permissions     ← EXISTING Spatie, keep as-is
```

**Models to create in `app/Domain/Core/Models/`:**
- `Organization` (maps `organizations`)
- `OrganizationBranch` (maps `organization_branches`)
- `Department` (maps `departments`)
- `Application` (maps `applications`)

### Step 3: Create Core Services

- `OrganizationService` — CRUD for organizations, branches, departments, members
- `ApplicationRegistry` — read applications, check access, list for user/org
- `WorkspaceResolver` — resolve subdomain → application or organization

### Step 4: Application Registry

- Seed `applications`:
  - StockFlow (type: BUSINESS)
  - BMS (type: BUSINESS)
  - GrowMart (type: CONSUMER)
  - ZamStay (type: CONSUMER)
  - GrowNet (type: NETWORK)
  - BizDocs (type: BUSINESS)
  - BizBoost (type: BUSINESS)
  - GrowBuilder (type: BUSINESS)
  - GrowFinance (type: BUSINESS)

### Step 5: Design Routing Engine

Design the replacement for `DetectSubdomain` middleare:
- Query `applications` for product slugs
- Query `organizations` for org slugs
- Query `custom_domains` for custom domains

**Do not remove** the existing middleware yet.

### What Phase 1 Does NOT Do

- ❌ Create `users` or `credentials` (already exist)
- ❌ Modify existing authentication
- ❌ Modify the User model
- ❌ Modify Spatie roles/permissions
- ❌ Rewrite any module (GrowNet, StockFlow, GrowFinance, etc.)
- ❌ Remove existing `DetectSubdomain` middleware
- ❌ Change any working business logic

### Verification

After Phase 1:

1. New `organizations` table exists with test data
2. New `applications` table is seeded with all products
3. `ApplicationRegistry` can list products for a user/org
4. `WorkspaceResolver` can resolve a subdomain to an application or org
5. All existing functionality continues working unchanged
6. `DetectSubdomain` middleware still handles routing (new engine is parallel, not replacing)

### Future Phases (Not in Scope)

- Phase 2: Connect existing modules to Core services
- Phase 3: Migrate `sa_users` and `prime_edge_clients` to Core
- Phase 4: Decompose User model (identity → Core, MLM → GrowNet)
- Phase 5: Replace `DetectSubdomain` with Routing Engine
- Phase 6: Add application-aware roles
- Phase 7: Extract high-traffic modules to separate services (if needed)
