# MyGrowNet Project Memory

## Constraints
- NEVER run `npm run build` on the production server (droplet 138.197.187.134)
- NEVER run `npm run build` — building frontend assets must only be done by the user manually
- `public/build/` is untracked from git — deployment of built assets must be handled separately
- No duplicate user accounts — single MyGrowNet user database

## Conventions
- **Migrations**: Each module/domain owns its schema in `database/migrations/{module}/`. Load via `->loadMigrationsFrom()` in the module's ServiceProvider. Never put domain-scoped migrations in `database/migrations/` root (root is for global-only migrations). **Always check the table below before creating a migration folder.**

### Canonical Migration Folders
| Folder | Module | Loaded By | Status |
|---|---|---|---|
| `agency/` | Agency | `AgencyServiceProvider` | ✅ Active — migrated Jul 2026 |
| `bizboost/` | BizBoost | `BizBoostServiceProvider` | ✅ Active — migrated Jul 2026 |
| `bizdocs/` | BizDocs | `BizDocsServiceProvider` | ✅ Active — migrated Jul 2026 |
| `bms/` | BMS (formerly CMS — companies, jobs, invoices, HR, payroll, etc.) | `BmsServiceProvider` | ✅ Active — migrated Jul 2026 |
| `construction/` | Construction (production, installation, quality, safety, fleet) | `ConstructionServiceProvider` | ✅ Active — migrated Jul 2026 |
| `contract/` | Contracts | `ContractServiceProvider` | ✅ Active — migrated Jul 2026 |
| `core/` | Platform Core (orgs, apps, users, auth, system, core FK columns) | `CoreServiceProvider` | ✅ Active |
| `email_marketing/` | Email Marketing | `EmailMarketingServiceProvider` | ✅ Active — migrated Jul 2026 |
| `employee/` | Employee (HR, portal, payroll, recruitment) | `EmployeeDomainServiceProvider` | ✅ Active — migrated Jul 2026 |
| `geopamu/` | GeoPamu Blog | `GeoPamuServiceProvider` | ✅ Active — migrated Jul 2026 |
| `growbuilder/` | GrowBuilder (sites, commerce, media, AI usage, payments) | `GrowBuilderServiceProvider` | ✅ Active — migrated Jul 2026 |
| `growfinance/` | GrowFinance core (accounts, invoices, customers, budgets, etc.) | `GrowFinanceServiceProvider` | ✅ Active — migrated Jul 2026 |
| `growmarket/` | GrowMart (products, orders, cart, inventory, coupons) | `GrowMartServiceProvider` | ✅ Active — migrated Jul 2026 |
| `grownet/` | GrowNet (MLM, memberships, tiers, starter kits, commissions, rewards) | `GrowNetServiceProvider` | ✅ Active — migrated Jul 2026 |
| `growstart/` | GrowStart | `GrowStartServiceProvider` | ✅ Active — migrated Jul 2026 |
| `growstream/` | GrowStream | `GrowStreamServiceProvider` | ✅ Active — migrated Jul 2026 |
| `inventory/` | Inventory (standalone, cross-module) | `InventoryServiceProvider` | ✅ Active — migrated Jul 2026 |
| `investor/` | Investor (accounts, rounds, dividends, documents, legal) | `InvestorServiceProvider` / `InvestorDomainServiceProvider` | ✅ Active — migrated Jul 2026 |
| `learning/` | Learning system | `LearningServiceProvider` | ✅ Active — migrated Jul 2026 |
| `lifeplus/` | LifePlus | `LifeplusServiceProvider` | ✅ Active — migrated Jul 2026 |
| `marketplace/` | Marketplace | `MarketplaceServiceProvider` | ✅ Active — migrated Jul 2026 |
| `module/` | Module system (tiers, features, discounts, offers) | `ModuleServiceProvider` | ✅ Active — migrated Jul 2026 |
| `notification/` | Notifications, Messages | `NotificationServiceProvider` | ✅ Active — migrated Jul 2026 |
| `prime_edge/` | PrimeEdge | `PrimeEdgeServiceProvider` | ⚠️ Legacy — guard removed Phase 8a, table dropped Phase 8e |
| `quickinvoice/` | QuickInvoice | `QuickInvoiceServiceProvider` | ✅ Active — migrated Jul 2026 |
| `stockflow/` | StockFlow | `StockFlowServiceProvider` | ✅ Active — migrated Jul 2026 |
| `storage/` | Storage | `StorageServiceProvider` | ✅ Active — migrated Jul 2026 |
| `support/` | Support tickets | `SupportServiceProvider` | ✅ Active — migrated Jul 2026 |
| `transaction/` | Transactions, payment logs | `TransactionServiceProvider` | ✅ Active — migrated Jul 2026 |
| `ubumi/` | Ubumi | `UbumiServiceProvider` | ✅ Active — migrated Jul 2026 |
| `venturebuilder/` | Venture Builder (ventures, investments, BGF, shares) | `VentureBuilderServiceProvider` | ✅ Active — migrated Jul 2026 |
| `wedding/` | Wedding | `WeddingServiceProvider` | ✅ Active — migrated Jul 2026 |
| `withdrawal/` | Withdrawals | `WithdrawalServiceProvider` | ✅ Active — migrated Jul 2026 |
| `zamstay/` | ZamStay | `ZamStayServiceProvider` | ✅ Active — migrated Jul 2026 |

**Status key:** ✅ = fully wired, ⚠️ = exists but not yet registered with `loadMigrationsFrom()`

**Rule:** Before creating `database/migrations/{name}/`, check this table. If the module already has a folder, use it. If root, create a new folder with the exact module slug. Register via ServiceProvider. **Never** use a different name for the same module (e.g. `stock-audit/` instead of `stockflow/`, or `platform/` instead of `core/`).

## CMS/BMS Namespace Migration (Completed Jul 2026)
The CMS models were moved from `App\Infrastructure\Persistence\Eloquent\CMS\` to `App\Infrastructure\Persistence\Eloquent\BMS\`. A one-time script replaced all 127 imports across `app/` and `database/` seeders. The old `CMS\` namespace directory has been deleted.

**Note on naming:** Several model classes retain "Cms" in their class name (e.g. `CmsUserModel`, `CmsUser`) despite being in the `BMS\` namespace. These are pre-existing names and don't affect functionality — the class names match historical CMS terminology.

## Common Issues & Fixes
- **Login modal 404 or redirects to old page**: Cached routes in production don't include POST /login. **Fix**: SSH to server, run `php artisan route:clear && php artisan route:cache`. See `DEPLOYMENT_FIX.md` and `docs/LOGIN_MODAL_TROUBLESHOOTING.md`.
- **CMS company creation redirects to old login**: `EnsureCmsAccess` middleware was redirecting to main site login instead of CMS login. **Fix**: Changed to `route('cms.login')` and ensure session is saved after transaction.
- **Subdomains ask for login when already logged in**: Sessions not shared across subdomains. **Fix**: In production .env, set `SESSION_DOMAIN=.mygrownet.com` (note the leading dot). This shares sessions across all subdomains (bizboost, growmart, cms, etc.).
- **BIzBoost/GrowMart/ZamStay blank auth page**: `window.location.href` in Vue template compiles to `_ctx.window.location.href`. The render proxy doesn't always resolve `window` to global. **Fix**: Extract to script as `const currentUrl = encodeURIComponent(window.location.href)`, use `currentUrl` in template.
- **Google OAuth 500 error**: `laravel/socialite` package may be missing on production. Run `composer require laravel/socialite`.
- **HandleInertiaRequests**: Must skip Inertia for auth routes on main domain only. All subdomains (bizboost, zamstay, growmart) must keep Inertia processing.
- **ZamStay 500 error**: Caused by missing subdomain handler in `DetectSubdomain` middleware and unloaded zamstay migrations. **Fix**: Added `zamstay` handler to `DetectSubdomain.php`, created `ZamStayServiceProvider` to load `database/migrations/zamstay/`, removed double route loading from `RouteServiceProvider`. After pulling on production: run `php artisan migrate --path=database/migrations/zamstay` then `php artisan optimize`.
- **StockFlow subdomain "Page not found: ./pages/auth/Login.vue"**: Route cache or outdated frontend build. **Fix**: Run `bash fix-stockflow-production.sh` or manually: `php artisan route:clear && php artisan route:cache && php artisan optimize`. See `docs/STOCKFLOW_SUBDOMAIN_FIX.md` and `DEPLOY_STOCKFLOW_FIX.md` for complete guide.
- **Migration errors (table/column already exists)**: Migrations fail because schema elements exist from previous runs. **Fix**: Mark all pending migrations as complete using `php artisan migrate:mark-complete` command. See `docs/PROPER_MIGRATION_FIX.md`. This is safer than fixing hundreds of migration files individually.
- **StockFlow login redirects to auth.mygrownet.com and back in loop**: Sessions not shared across subdomains or redirect middleware configured before auth resolves. **Fix**: Ensure `SESSION_DOMAIN=.mygrownet.com` in production .env, and check that `IDENTITY_REDIRECT_STOCKFLOW` is not `true` until migration is validated.

## Deployment
1. Commit and push to `origin/main` on local
2. SSH to droplet: `cd /var/www/mygrownet.com && git pull`
3. **CRITICAL**: Clear and rebuild caches: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
4. Run `php artisan optimize` (config, routes, events cached)
5. Built frontend assets must be deployed separately (build locally then upload or use CI)
6. **Production only**: Ensure `.env` has `SESSION_DOMAIN=.mygrownet.com` for cross-subdomain auth
7. **After Phase 8 pull**: Run `php artisan migrate --path=database/migrations/2026_07_21_000001_drop_legacy_user_tables.php` then `php artisan route:clear && php artisan route:cache`

**Quick script**: `bash fix-production.sh` (runs all cache clear/rebuild commands)

## Routes
- Auth: `GET|HEAD auth/google` and `GET|HEAD auth/google/callback` — no prefix, no subdomain
- Identity Gateway: 16 routes in `routes/my-grow-identity.php` served exclusively by `auth.mygrownet.com` (login, register, password reset, email verify, 2FA, session validation)
- Each subdomain needs its own callback URL registered in Google Cloud Console

### Workspace Routes (authenticated)
| Method | URI | Name | Controller |
|---|---|---|---|
| GET | `/workspace` | `workspace` | `WorkspaceController@index` |
| POST | `/workspace/switch-context` | `workspace.switch-context` | `WorkspaceController@switchContext` |
| POST | `/workspace/launch/{application}` | `workspace.launch` | `WorkspaceController@launch` |
| GET | `/org/{slug}` | `workspace.organization` | `OrganizationWorkspaceController@show` |
| GET | `/organizations/create` | `workspace.organization.create` | `OrganizationWorkspaceController@create` |
| POST | `/organizations` | `workspace.organization.store` | `OrganizationWorkspaceController@store` |
| POST | `/org/{slug}/install/{application}` | `workspace.organization.install` | `OrganizationWorkspaceController@install` |
| GET | `/apps` | `apps.catalog` | `WorkspaceController@catalog` |
| GET | `/dashboard` | — | 301 → `/workspace` |
| GET | `/_platform/workspace` | — | `WorkspaceResolver` diagnostic |

**Launch flow:** AppTile calls `router.post(route('workspace.launch', { application: app.id }))` → `WorkspaceController@launch` checks `canAccess()` → `AppLaunchService::launch()` stores payload in session and redirects to `app.url`. Never use `window.location.href = app.url` directly.

### Middleware Stack (web group order)
```
... standard Laravel web middleware ...
\App\Http\Middleware\ResolveDomainContext::class   ← resolves host → DomainResolution + WorkspaceContext
\App\Http\Middleware\SetPlatformContext::class      ← shares workspace context/apps/orgs to Inertia
```

### Route Middleware Aliases
| Alias | Class |
|---|---|
| `ensure.organization.access` | `EnsureOrganizationAccess` |
| `ensure.application.access` | `EnsureApplicationAccess` |
| `identity.redirect` | `RedirectToMyGrowIdentity` |

## PrimeEdge Advisory Subdomain Setup
- **DNS**: Create `CNAME primeedge` pointing to `mygrownet.com` (or A record to droplet IP)
- **Middleware**: Handler added in `DetectSubdomain.php` at line 131 — calls `configureSubdomainUrl()` (same as bizboost/zamstay)
- **Routes**: `primeedge.mygrownet.com` group registered in `routes/primeedge.php` line 81 — serves all routes at root `/` with name prefix `primeedge.sub.`
- **Blade view**: `primeedge.mygrownet.com` → `primeedge` already mapped in `HandleInertiaRequests.php`
- **Note**: Subdomain routes use `primeedge.sub.*` name prefix to avoid collisions with main domain `primeedge.*` routes. Controllers that redirect (login, logout) currently use `primeedge.*` names. If activating subdomain, update redirect targets to detect current domain and use `primeedge.sub.*` names.

## StockFlow Module (DDD Architecture)

Domain-Driven Design module under `/stock-audit` prefix. Uses `sa_` prefixed tables with `sa_company_id` for tenant isolation.

### Architecture Layers

```
app/Domain/StockFlow/           ← Domain Layer (pure PHP, zero Laravel deps)
├── ValueObjects/               Typed IDs (CompanyId, ItemId, ...), enums (MovementType, PaymentMethod, ...), Money
├── Entities/                   Rich domain models (Item, Sale, PurchaseOrder, Audit, CashRegister, ...)
│   Each entity: private constructor, create()/reconstitute(), behavior methods, toArray()
├── Repositories/               Interface contracts (11 interfaces)
├── Services/                   Domain services orchestrating entities + repositories
│   ├── InventoryService        Create/update items, adjust stock
│   ├── SalesService            Create sale, auto-deduct stock, update register
│   ├── PurchasingService       Create PO, receive stock, supplier management
│   ├── PhysicalCountService    Create/complete count, generate audit
│   ├── AuditService            Finalize audit with recorded sales
│   ├── CashRegisterService     Open/close/verify register, add movements
│   ├── DashboardService        Stats and company listing
│   └── DepartmentBinService    Department and bin CRUD
└── Exceptions/                 StockFlowException, OperationFailedException, InsufficientStockException

app/Infrastructure/
├── Persistence/Eloquent/StockFlow/     ← Eloquent models (thin, table + fillable + casts + relations)
└── Persistence/Repositories/StockFlow/ ← Repository implementations (EloqueNT → Domain mapping)

app/Http/Controllers/StockAudit/         ← Controllers (thin — validation + service delegation)
routes/stock-audit.php                   ← 56 routes
app/Providers/StockAuditServiceProvider  ← DI bindings (interfaces → implementations)
```

### Repository Bindings

| Interface | Implementation |
|---|---|
| `CompanyRepositoryInterface` | `EloquentCompanyRepository` |
| `DepartmentRepositoryInterface` | `EloquentDepartmentRepository` |
| `BinRepositoryInterface` | `EloquentBinRepository` |
| `ItemRepositoryInterface` | `EloquentItemRepository` |
| `SupplierRepositoryInterface` | `EloquentSupplierRepository` |
| `PurchaseOrderRepositoryInterface` | `EloquentPurchaseOrderRepository` |
| `SaleRepositoryInterface` | `EloquentSaleRepository` |
| `StockMovementRepositoryInterface` | `EloquentStockMovementRepository` |
| `PhysicalCountRepositoryInterface` | `EloquentPhysicalCountRepository` |
| `AuditRepositoryInterface` | `EloquentAuditRepository` |
| `CashRegisterRepositoryInterface` | `EloquentCashRegisterRepository` |

### Layer Rules
- **Domain** — No Laravel dependencies. Pure PHP 8.1+ with typed properties, named constructors, `DateTimeImmutable`
- **Infrastructure** — Only implements Domain contracts. Eloquent models are thin data mappers
- **Controllers** — Only handle HTTP (validation, session, Inertia responses). All business logic in Domain services

### Tables (17 + extras)
`sa_companies`, `sa_departments`, `sa_bins`, `sa_items`, `sa_suppliers`, `sa_purchase_orders`, `sa_purchase_order_items`, `sa_sales`, `sa_sale_items`, `sa_stock_movements`, `sa_physical_counts`, `sa_count_items`, `sa_audits`, `sa_audit_items`, `sa_audit_reconciliations`, `sa_cash_registers`, `sa_cash_movements`, `sa_expiry_checks`, `sa_expiry_check_items`, `sa_subscription_plans`, `sa_company_subscriptions`

### Workflows

**Daily Operations**: Items → Purchases (PO → receive → stock+) → Sales (record → stock− → register+) → Cash (open → expenses/banking → close → verify) → Adjustments

**Audit**: Items → Physical Count (auto-populates all items) → Enter physical qty → Complete (updates system qty) → Generate Audit (reconciliations, variance) → Finalize (recorded sales → unaccounted) → Export CSV

**Key invariants**:
- `StockMovement` records `quantity_before` / `quantity_after` for every qty change
- `Item::adjustStock()` ensures `system_quantity ≥ 0`
- `CashRegister::recordSale()/addExpense()/addBanking()` auto-recalculates `expected_closing`
- `Audit::finalize()` computes `unaccountedValue = totalVariance − totalRecordedSales`
- All DB mutations inside `DB::transaction()` in domain services

### Artisan Commands
- `php artisan stock-audit:import-items {company} {file}` — CSV import
- `php artisan stock-audit:import-sample {company?}` — Sample Taradasi data

### Seeded Client
Taradasi Dental Clinic (run `StockAuditSeeder`)

## Removed Files
- `resources/js/Pages/GrowNet/Dashboard.vue` — classic desktop GrowNet dashboard, replaced by `GrowNet/GrowNet.vue` (modern mobile SPA)
- `routes/debug-analytics.php` — orphaned dev utility, deleted Phase 11
- `routes/debug.php` — orphaned dev utility, deleted Phase 11
- `routes/subdomain.php` — superseded by DetectSubdomain middleware, deleted Phase 11
- `app/Http/Middleware/DashboardRedirect.php` — no-op middleware, removed after workspace route switch

## Workspace Domain Layer (Domain-Driven Design)

The Workspace bounded context lives under `app/Domain/Workspace/`:

```
app/Domain/Workspace/
├── ValueObjects/           DomainResolution, WorkspaceContext (immutable data carriers)
├── Services/               DomainResolverService, ContextResolverService,
│                           ApplicationAccessService, OrganizationAccessService, AppLaunchService
└── Exceptions/             DomainNotFoundException, WorkspaceException
```

No repository interfaces yet — services query Eloquent models directly (Core models remain thin Eloquent, not pure DDD entities). If needed, repository pattern can be extracted later.

**New Core tables** (migrations in `database/migrations/core/`, prefixed `2400xx`):
- `application_installations` — per-org app settings and provisioning status
- `user_application_subscriptions` — user-level app subscriptions
- `domains` — routing authority (all subdomains, org domains, platform)
- `organization_invitations` — org membership invitations
- `application_roles` — app-specific role/permission definitions
- `feature_flags` — feature toggles per app
- `platform_roles` — platform-level admin roles (super_admin, support, etc.)

**Enhanced existing tables:**
- `applications` — added category, access_model, context_support, lifecycle, operational_status, etc.
- `organizations` — added country, currency, timezone, language
- `organization_members` — added role, permissions
- `user_profiles` — added first_name, last_name, avatar, timezone, language

**Workspace services** are singletons registered in `WorkspaceServiceProvider`:
- `DomainResolverService::resolve(string $host): DomainResolution` — looks up `domains` table
- `ContextResolverService::resolve(?User, ?string $domainType, ?Organization $orgHint): WorkspaceContext`
- `ApplicationAccessService::getAvailableApps(User, WorkspaceContext): Collection` — filtered by context
- `OrganizationAccessService::getAccessibleOrganizations(User): Collection`
- `AppLaunchService::buildPayload(Application, WorkspaceContext, User): array`

### Vue Component Tree
```
resources/js/
  Layouts/WorkspaceLayout.vue        ← header + ContextSwitcher + slot
  Pages/Workspace/
    Index.vue                        ← Platform Workspace launcher
    Organization.vue                  ← Org Workspace (apps + members)
  Components/Workspace/
    ContextSwitcher.vue              ← "Personal Workspace ▼" dropdown with orgs
    AppGrid.vue                      ← categorized app tiles
    AppTile.vue                      ← single app icon + name + launch
    OrganizationList.vue             ← list of user's orgs
    OrganizationCard.vue             ← single org card → /org/{slug}
    GlobalAppSwitcher.vue            ← cross-subdomain flyout menu
    LegacyAppBadge.vue               ← migration status badge
    IntendedAppHighlight.vue         ← auto-highlight for app-first entry
```

### Inertia Shared Data (from SetPlatformContext)
```typescript
interface WorkspaceShared {
    context: {
        type: 'personal' | 'organization' | 'guest';
        organization_id: number | null;
        organization_slug: string | null;
        organization_name: string | null;
        application_id: number | null;
    };
    apps: Record<string, App[]>;   // keyed by category (business, consumer, shared)
    organizations: (Organization & {
        apps: { id: number; name: string; slug: string }[];
    })[];                         // each org now includes its subscribed apps
}
```
Available in Vue as `usePage().props.workspace`.

## Platform Evolution
- 11-phase roadmap at `docs/platform-evolution/FULL_IMPLEMENTATION_ROADMAP.md` (phases 1-9 implemented, 10-11 design/cleanup)
- Implementation plan at `docs/platform-evolution/IMPLEMENTATION_PLAN.md` (12 migrations, 5 services, 4 middleware, 2 controllers, Vue tree)
- Architecture Decision Records at `docs/adr/ADR-001` through `ADR-007`
- Platform event bus: OrganizationCreated, OrganizationArchived, MemberAdded, ApplicationSubscribed events dispatch automatically; listeners live in target modules (StockFlow, CMS)
- Shared services contracts reserved at `docs/platform-evolution/SHARED_SERVICES.md` (Storage, Search, Payment, Audit, AI, Reporting)

### Application Authentication Rule
Applications do not authenticate users. They only verify that the Platform Core has already authenticated the user and that the user has permission to access the application.

```
Identity Gateway (Platform Core)
    │
Authenticates User
    │
Creates Session / Issues Token
    │
Redirects to Application
    │
▼
Application
    │
Checks:
• Is user authenticated? (validate session/token with Gateway)
• Does user have access?  (check Platform Core permissions)
    │
▼
Open application
```

### Identity Design Principle
Design around a **Platform Identity** that every application trusts. Today that identity may use the `web` guard and a shared session (`SESSION_DOMAIN=.mygrownet.com`). In the future it could be backed by an Identity Gateway inside Platform Core — a shared authentication service that every application delegates to, without each app owning its own login.

### Auth Landscape (Phase 8a–8e Completed Jul 2026)
The `primeedge` and `stockflow` guards have been removed (no real users ever existed for `primeedge`; StockFlow had one test user). `ResolveSubdomainAuth` middleware deleted. `SaUserModel` deleted. `sa_users` and `prime_edge_clients` tables dropped via migration.

| Guard | Table | Status |
|---|---|---|
| `web` | `users` | **Only** authentication system — all apps target this |

**Phase 8 (MyGrow Identity & Centralized Authentication)** built `auth.mygrownet.com` as the shared identity gateway. All applications redirect to `auth.mygrownet.com/login` via `RedirectToMyGrowIdentity` middleware (per-app kill switch in `config/platform.php`). StockFlow login routes now act as signed HMAC redirect proxies. Sanctum needs installation for token minting to work.

## GrowNet DDD Refactoring (Completed Jul 2026)
GrowNet was refactored from scattered code (25 controllers in `MyGrowNet/`, services in `app/Services/`, model in `Domain/GrowNet/Models/`) into a proper DDD bounded context:

### Architecture
```
app/Domain/GrowNet/
├── ValueObjects/       MemberId, Money, Percentage, ReferralCode, CommissionLevel (enum),
│                       MembershipTier (enum), SubscriptionStatus (enum), VerificationLevel (enum),
│                       NetworkLevel
├── Entities/           Member (rich domain entity), Commission, Referral, TeamVolume, TierUpgrade,
│                       StarterKit, LoyaltyPoints
├── Repositories/       MemberRepositoryInterface, CommissionRepositoryInterface,
│                       ReferralRepositoryInterface, TeamVolumeRepositoryInterface,
│                       TierUpgradeRepositoryInterface, StarterKitRepositoryInterface,
│                       LoyaltyPointsRepositoryInterface
├── Services/           MemberService, DashboardService, TierAdvancementService
└── Exceptions/         GrowNetException, MemberNotFoundException, InsufficientFundsException,
                        TierUpgradeException, ReferralException

app/Infrastructure/
├── Persistence/Eloquent/GrowNet/     MemberModel (moved from Domain, now table `grow_net_users`)
└── Persistence/Repositories/GrowNet/ 7 Eloquent repository implementations

app/Http/Controllers/GrowNet/          Refactored thin controllers (DashboardController migrated)
app/Providers/GrowNetServiceProvider   DI bindings for repository interfaces + singleton services
```

### Key Changes
- **`GrowNetUser` model** → `MemberModel` in `Infrastructure/Persistence/Eloquent/GrowNet/` (same table `grow_net_users`)
- **`User::growNetData()` relationship** updated to point to new `MemberModel`
- **`MyGrowNet\DashboardController`** → `GrowNet\DashboardController` — business logic extracted to domain services
- **`MyGrowNetTierAdvancementService`** → `Domain/GrowNet/Services/TierAdvancementService`
- **GrowNetServiceProvider** registered in `bootstrap/providers.php`
- **Routes** updated to use new controller namespace

### Repository Bindings
| Interface | Implementation |
|---|---|
| `MemberRepositoryInterface` | `EloquentMemberRepository` |
| `CommissionRepositoryInterface` | `EloquentCommissionRepository` |
| `ReferralRepositoryInterface` | `EloquentReferralRepository` |
| `TeamVolumeRepositoryInterface` | `EloquentTeamVolumeRepository` |
| `TierUpgradeRepositoryInterface` | `EloquentTierUpgradeRepository` |
| `StarterKitRepositoryInterface` | `EloquentStarterKitRepository` |
| `LoyaltyPointsRepositoryInterface` | `EloquentLoyaltyPointsRepository` |

### Remaining Controllers (not yet refactored — still in `MyGrowNet/`)
The remaining 24 controllers in `app/Http/Controllers/MyGrowNet/` still directly query Eloquent models. Future work: migrate them to use domain services/repositories.
