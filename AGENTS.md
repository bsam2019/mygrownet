# MyGrowNet Project Memory

## Constraints
- NEVER run `npm run build` on the production server (droplet 138.197.187.134)
- `public/build/` is untracked from git — deployment of built assets must be handled separately
- No duplicate user accounts — single MyGrowNet user database

## Common Issues & Fixes
- **Login modal 404 or redirects to old page**: Cached routes in production don't include POST /login. **Fix**: SSH to server, run `php artisan route:clear && php artisan route:cache`. See `DEPLOYMENT_FIX.md` and `docs/LOGIN_MODAL_TROUBLESHOOTING.md`.
- **CMS company creation redirects to old login**: `EnsureCmsAccess` middleware was redirecting to main site login instead of CMS login. **Fix**: Changed to `route('cms.login')` and ensure session is saved after transaction.
- **Subdomains ask for login when already logged in**: Sessions not shared across subdomains. **Fix**: In production .env, set `SESSION_DOMAIN=.mygrownet.com` (note the leading dot). This shares sessions across all subdomains (bizboost, growmart, cms, etc.).
- **BIzBoost/GrowMart/ZamStay blank auth page**: `window.location.href` in Vue template compiles to `_ctx.window.location.href`. The render proxy doesn't always resolve `window` to global. **Fix**: Extract to script as `const currentUrl = encodeURIComponent(window.location.href)`, use `currentUrl` in template.
- **Google OAuth 500 error**: `laravel/socialite` package may be missing on production. Run `composer require laravel/socialite`.
- **HandleInertiaRequests**: Must skip Inertia for auth routes on main domain only. All subdomains (bizboost, zamstay, growmart) must keep Inertia processing.
- **ZamStay 500 error**: Caused by missing subdomain handler in `DetectSubdomain` middleware and unloaded zamstay migrations. **Fix**: Added `zamstay` handler to `DetectSubdomain.php`, created `ZamStayServiceProvider` to load `database/migrations/zamstay/`, removed double route loading from `RouteServiceProvider`. After pulling on production: run `php artisan migrate --path=database/migrations/zamstay` then `php artisan optimize`.
- **StockFlow subdomain "Page not found: ./pages/auth/Login.vue"**: Route cache or outdated frontend build. **Fix**: Run `bash fix-stockflow-production.sh` or manually: `php artisan route:clear && php artisan route:cache && php artisan optimize`. See `docs/STOCKFLOW_SUBDOMAIN_FIX.md` and `DEPLOY_STOCKFLOW_FIX.md` for complete guide.
- **Migration errors (table/column already exists)**: Migrations fail because schema elements exist from previous runs. **Fix**: Added existence checks to migrations. See `docs/MIGRATION_FIX_DEPLOYMENT.md`. After git pull, run `php artisan migrate --force`.

## Deployment
1. Commit and push to `origin/main` on local
2. SSH to droplet: `cd /var/www/mygrownet.com && git pull`
3. **CRITICAL**: Clear and rebuild caches: `php artisan route:clear && php artisan config:clear && php artisan cache:clear && php artisan route:cache && php artisan config:cache`
4. Run `php artisan optimize` (config, routes, events cached)
5. Built frontend assets must be deployed separately (build locally then upload or use CI)
6. **Production only**: Ensure `.env` has `SESSION_DOMAIN=.mygrownet.com` for cross-subdomain auth

**Quick script**: `bash fix-production.sh` (runs all cache clear/rebuild commands)

## Routes
- Auth: `GET|HEAD auth/google` and `GET|HEAD auth/google/callback` — no prefix, no subdomain
- Each subdomain needs its own callback URL registered in Google Cloud Console

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
