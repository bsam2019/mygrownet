# StockFlow Extraction Dry Run

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 8.4 — Independent Deployment Readiness  
> **Objective:** Analyze whether StockFlow can be extracted from the monolith and deployed as an independent service

---

## Extraction Criteria

To extract StockFlow as an independent service, every integration point from StockFlow to the rest of the platform must use one of the three approved integration types (Platform Services, Platform Events, Integration Contracts). Any remaining direct coupling must be cataloged and resolved before extraction.

---

## 1. Integration Points That Already Use Contracts/Events

### Contracts Provided by StockFlow

| Contract | Status | Consumed By |
|---|---|---|
| `InventoryProvider` (v1) | ✅ Registered in manifest, bound in StockFlowServiceProvider | BMS IntegrationService |
| `InventoryProviderV2` (v2) | ✅ Registered alongside v1 | Future consumers |
| `SupplierProvider` | ✅ Declared in manifest | BMS (planned) |

### Contracts Consumed by StockFlow

| Contract | Provider | Status |
|---|---|---|
| `NotificationProvider` | Platform Core | ✅ Resolved via IntegrationRegistry |
| `MediaProvider` | Platform Core | ✅ Resolved via IntegrationRegistry |
| `AccountingProvider` | GrowFinance | ✅ Registered, stub impl |
| `IdentityProvider` | Platform Core | ✅ Resolved via IntegrationRegistry |

### Events Published by StockFlow

| Event | Status |
|---|---|
| `StockFlow\Events\GoodsReceived` | ✅ Domain event, implements DomainEvent |
| `StockFlow\Events\PurchaseOrderReceived` | ✅ Domain event |
| `StockFlow\Events\StockAdjusted` | ✅ Domain event |
| `StockFlow\Events\SaleCompleted` | ✅ Domain event |
| `StockFlow\Events\StockCountFinalized` | ✅ Domain event |
| `StockFlow\Events\CashDiscrepancyDetected` | ✅ Domain event |

### Events Consumed by StockFlow

| Event | Publisher | Status |
|---|---|---|
| `ApplicationEnabled` | Platform Core | ✅ Listed in manifest `listens` |
| `OrganizationCreated` | Platform Core | ✅ Listed in manifest `listens` |
| `InvoiceCreated` | BMS | ✅ Listed in manifest `listens` |

---

## 2. Remaining Coupling

### 2.1 Controllers — App-Layer Coupling (Acceptable)

These are HTTP controllers. In an extraction scenario, they'd move with the service. Listed for completeness.

| File | Coupling | Notes |
|---|---|---|
| `app/Http/Controllers/StockAudit/*.php` | Uses standard Laravel HTTP + Inertia | These move with StockFlow — no platform coupling concern |
| `StockFlowAdminMiddleware` | Checks `$user->is_stockflow_admin` (User model) | Uses Core's `web` guard; depends on shared User identity |

### 2.2 Middleware — Tightest Coupling

These middleware classes import StockFlow domain services directly — they could not be extracted without breaking the monolith's request pipeline.

| Middleware | Coupling | Impact |
|---|---|---|
| `DetectSubdomain` | Imports `CompanyRepositoryInterface` (StockFlow), injects directly | **Blocking.** Extracting StockFlow means this middleware loses its StockFlow dependency. The monolith must keep a stub or the subdomain resolution must be externalized. |
| `HandleInertiaRequests` | Checks `stockflow_company_id` attribute, shares `stockflowAccount` to Inertia | **Blocking.** The root template and shared data logic is monolith-specific. An independent StockFlow would handle this itself. |
| `ResolveStockFlowCompany` | Moves `stockflow_company_id` attribute to session | Moderate. This is infrastructure that exists because StockFlow shares the monolith's session. |
| `StockFlowPermission` | Checks permissions against StockFlow CompanyUserService | Moderate. Permissions would move with StockFlow. |
| `StockFlowCompany` | Validates stockflow_company_id in session | Moderate. |
| `StockFlowApiAuth` | Authenticates via `sa_api_keys` table + merges company_id | Low. Would become standard API auth in standalone deployment. |
| `StockFlowAdminMiddleware` | Checks `is_stockflow_admin` on User model | **Blocking.** Depends on Core's User model and a boolean flag. Would need an admin claim/token in standalone mode. |
| `CheckFeatureEnabled` | Reads `stockflow_company_id` from session, queries `SaCompanyModel` | Moderate. Feature checks would be internal in standalone mode. |

### 2.3 Config — Shared Configuration

| Config File | Coupling | Impact |
|---|---|---|
| `config/stockflow.php` | Standalone file, no cross-module references | Low. Moves with StockFlow. |
| `config/platform.php` | Contains `IDENTITY_REDIRECT_STOCKFLOW` flag, queue mappings | **Low.** The platform config would retain flags for the remote StockFlow instance. |

### 2.4 Service Providers

| Provider | Coupling | Impact |
|---|---|---|
| `StockFlowServiceProvider` | Registers manifest in `ModuleDiscovery`, binds contracts via IntegrationRegistry | **Blocking.** In standalone mode, StockFlow does not have ModuleDiscovery or IntegrationRegistry (those are Platform Core services). The provider would need a conditional registration path — register contracts normally when running standalone, register with Core when in monolith mode. |
| `StockFlowServiceProvider` | Iterates `config('stockflow.extensions')` for extension points | Low. Extensions can remain config-driven. |

### 2.5 Database Migrations

| Item | Status |
|---|---|
| `database/migrations/stockflow/` | ✅ Owned by StockFlow, loaded via `loadMigrationsFrom()` in `StockFlowServiceProvider` |
| Tables use `sa_` prefix | ✅ No naming conflicts with other modules |
| No cross-module FK references | ✅ All FKs are within `sa_*` tables |

**Verdict:** Migrations are extraction-ready. No changes needed.

---

## 3. What Changes If Deployed Independently

### Configuration

| Setting | Monolith | Standalone |
|---|---|---|
| `APP_URL` | `https://stockflow.mygrownet.com` (handled by middleware) | Same, configured in `.env` |
| `SESSION_DOMAIN` | `.mygrownet.com` (shared) | Not used — StockFlow validates tokens via Gateway API |
| `DB_DATABASE` | Shared `mygrownet` database | Dedicated database (or separate schema) |
| `QUEUE_CONNECTION` | Shared Redis | Dedicated queue |
| `IDENTITY_REDIRECT_STOCKFLOW` | `true` (redirect to auth.mygrownet.com) | `true` (same — redirect to Gateway) |
| Contract bindings | Registered in `StockFlowServiceProvider` via container | Contracts bound locally; remote resolution for non-StockFlow contracts |

### Routes

| Route File | Monolith | Standalone |
|---|---|---|
| `routes/stockflow.php` | Grouped under `/stock-audit` prefix in `web.php` | Served at `/` — no prefix needed |
| `routes/stockflow-subdomain.php` | Served by `DetectSubdomain` middleware at `{company}.stockflow.mygrownet.com` | Served at `{company}.{standalone-host}` — no middleware needed |
| `routes/stockflow-landing.php` | Landing/marketing pages | Same |
| `routes/stockflow-api.php` | API routes | Same |
| `routes/stockflow-admin.php` | Admin routes | Same |

### Service Providers

| Provider | Monolith | Standalone |
|---|---|---|
| `StockFlowServiceProvider` | Registers in ModuleDiscovery, binds contracts | `ModuleDiscovery` registration removed; contract bindings remain local for consumers that import StockFlow's interfaces |
| `StockAuditServiceProvider` (if exists) | — | May be renamed to primary provider |

---

## 4. Extraction Readiness Score

| Category | Score | Notes |
|---|---|---|
| **Domain layer** | ✅ Ready | Pure PHP, no Laravel deps, all domain events implement `DomainEvent` |
| **Repositories** | ✅ Ready | Implement domain interfaces, all `sa_` prefixed |
| **Contracts** | ✅ Ready | v1 and v2 contracts exist, versioned, registered |
| **Events** | ✅ Ready | 6 domain events, registered in ownership registry |
| **Migrations** | ✅ Ready | Own folder, own table prefix |
| **Controllers** | ✅ Ready | Thin — validation + service delegation |
| **Routes** | ✅ Ready | Self-contained files |
| **Config** | ✅ Mostly ready | `config/stockflow.php` is standalone |
| **Middleware** | ❌ Blocking | DetectSubdomain, HandleInertiaRequests, StockFlowAdminMiddleware have direct Core dependencies |
| **ServiceProvider** | ⚠️ Needs work | ModuleDiscovery/IntegrationRegistry registration is monolith-specific |
| **Auth** | ⚠️ Needs work | Currently uses shared `web` guard + SESSION_DOMAIN |

---

## 5. Extraction Action Items

### Before Extraction (Phase 8.4 Gaps)

1. **Middleware de-coupling** — Extract StockFlow-specific logic from `DetectSubdomain` into StockFlow's own middleware. The monolith's `DetectSubdomain` should detect StockFlow subdomains and forward to StockFlow's endpoint instead of importing StockFlow repositories.

2. **Admin middleware** — Replace `$user->is_stockflow_admin` with a token-based admin check or a call to Core's `IdentityProvider::hasRole()`.

3. **ServiceProvider conditional paths** — Add an `app()->runningInConsole()` / `config('stockflow.standalone')` check in `StockFlowServiceProvider` to skip `ModuleDiscovery` registration when running standalone.

4. **Inertia root view** — StockFlow's `HandleInertiaRequests` check for `stockflow_company_id` must become StockFlow's own root view logic, not live in the monolith's middleware.

### After Extraction (Standalone Mode)

5. **Standalone auth** — Replace `web` guard + session with token validation via Identity Gateway API. StockFlow validates each request with `auth.mygrownet.com/validate-session` (or JWT).

6. **Queue isolation** — Dedicated queue worker and queue name prefix. Update `config/queue.php` in the standalone deployment.

7. **Contract resolution for consumed contracts** — `NotificationProvider`, `MediaProvider`, `AccountingProvider` must resolve remotely via HTTP/API Gateway instead of local container resolution.

---

## 6. Summary

| Aspect | Verdict |
|---|---|
| Is StockFlow extractable today? | **Partially.** The domain layer, contracts, events, migrations, and controllers are ready. |
| What blocks extraction? | 3 middleware files with direct monolith coupling (`DetectSubdomain`, `HandleInertiaRequests`, `StockFlowAdminMiddleware`) plus `StockFlowServiceProvider`'s ModuleDiscovery registration. |
| How much would change? | Configuration only — routes, config files, service providers. Zero domain logic changes needed. |
| What about other modules? | BMS and StockFlow are the most tightly coupled pair (Inventory contract, InvoiceCreated event). BMS would gain a remote `InventoryProvider` HTTP implementation. |

**Honest assessment:** StockFlow can be extracted to a separate process with approximately 1-2 weeks of middleware de-coupling work. The domain is already clean. The middleware layer was never fully separated because it was built within the monolith first. The remaining coupling is all infrastructure — no business logic entanglement.
