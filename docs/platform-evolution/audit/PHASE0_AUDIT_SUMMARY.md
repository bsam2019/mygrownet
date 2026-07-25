# Phase 0: Foundation Audit & Remediation — Summary

> Generated: 2026-07-25
> Covers tasks 0.1–0.7

---

## 0.1 Cross-Module `DB::table()`/`DB::connection()` Calls

Scanned all PHP files under `app/` (excluding routes/, config/, database/migrations/).

### By Table

| Table Name | Owned By | Direct Query Files (non-owner module) |
|---|---|---|
| `transactions` | Transaction | 8 files: TransactionBasedFinancialReportingService, ProfitLossTrackingService, CashFlowStatementService, AnalyticsService, PredictiveAnalyticsService, PaymentService, WalletHealthController, InvestmentReportController, ROICalculationService, Admin LgrManualAwardController, GrowNet WalletController, LgrTransferController, MyGrowNetStarterKitController, LgrPackageController |
| `referral_commissions` | GrowNet | 9 files: TransactionBasedFinancialReportingService, ProfitLossTrackingService, BalanceSheetService, CashFlowStatementService, EarningsService, QueryCacheService, WalletHealthController, Admin MatrixController, GrowNet GrowNetDashboardController |
| `users` | Core | 5 files: QueryCacheService, PredictiveAnalyticsService, LgrCycleService, PlatformMetricsService, VentureInvestmentService, VentureDividendService, GrowStream AdminController, Admin RewardAnalyticsController, LgrActivityReportController, StarterKitAdminController |
| `module_subscriptions` | Module | 5 files: BMS SettingsController, BizBoost SubscriptionController, GrowBuilder SubscriptionController, Admin GrowBuilderController, Admin ModuleSubscriptionAdminController |
| `point_transactions` | Transaction/GrowNet | 4 files: StarterKitService, GrowNet DashboardController, GrowNetDashboardController, MembershipController |
| `user_networks` | GrowNet | 3 files: AnalyticsService, NetworkManagementController, NetworkReorganizationService |
| `profit_shares` | GrowNet | 3 files: ProfitLossTrackingService, BalanceSheetService, CashFlowStatementService |
| `analytics_events` | Core | 3 files: AnalyticsService, RecommendationEngine, PredictiveAnalyticsService |
| `investor_messages` | Investor | 2 files: InvestorMessagingService, InvestorAnalyticsService |
| `investments` | Investor | QueryCacheService, Admin DashboardController |

### Key Findings

- **`app/Services/` flat directory** is the biggest offender — 15+ legacy services use `DB::table()` across 10+ different modules' tables
- **GrowNet domain services** query `transactions` (owned by Transaction), `point_transactions` (split ownership), and `users` (owned by Core) — top priority cleanup
- **Admin controllers** (`app/Http/Controllers/Admin/`) query tables from 8 different modules — expected for admin panels but should go through module contracts
- **No module** queries only its own tables; all 34 modules have at least some cross-module `DB::table()` calls from external callers

---

## 0.2 Cross-Module Eloquent Model Imports

Scanned all PHP files under `app/` for `use App\Domain\*` and `use App\Infrastructure\Persistence\Eloquent\*` imports.

### Domain-to-Domain Violations (Strongest)

| Source | Target | File(s) |
|---|---|---|
| BMS | Transaction | `Domain\BMS\Core\Services\LoanAccountingService.php` |
| BizBoost | Module | `Domain\BizBoost\Services\BizBoostUsageProvider.php` |
| Employee | Investment, GrowNet | `Domain\Employee\Services\CommissionCalculationService.php` |
| GrowNet | Financial | `Domain\GrowNet\Services\StarterKitService.php` |
| LifePlus | Notification, Module | `LifePlusAccessService.php`, `NotificationService.php` |
| Module | POS, Inventory | `Domain\Module\Services\ModuleIntegrationService.php` |
| POS | Inventory | `Domain\POS\Services\POSService.php` |
| Transaction | GrowNet | `Domain\Transaction\Repositories\TransactionRepositoryInterface.php` |

### Infrastructure Model Bypasses (Layer Violations)

~50+ instances where files import` Infrastructure\Persistence\Eloquent\*` models directly instead of going through Domain repository layer. Worst offenders:
- `app/Services/VentureBuilder/*` — 8 files importing VentureBuilder models
- `app/Services/Integration/*` — BMSIntegrationService, GrowMarketIntegrationService
- `app/Http/Controllers/Admin/*` — numerous admin controllers
- `app/Console/Commands/*` — 15+ commands

---

## 0.3 Cross-Module `app(Service::class)` Calls

| Calling Module | Violations | Targets |
|---|---|---|
| Core (DetectSubdomain) | 17 | BMS (controller routing) |
| Core (DashboardController) | 3 | GrowNet WalletService, Module UseCase |
| Core (Admin) | 5 | SendNotificationUseCase, Employee |
| Core (Auth/Socialite) | 1 | GrowNet LgrActivityTrackingService |
| GrowNet Controllers | 4 | Financial LoanService |
| GrowNet Services | 3 | Wallet, Financial, Announcement |
| Financial Services | 1 | SendNotificationUseCase |
| Module Integration | 2 | POS, Inventory |
| GrowBuilder | 4 | Module SubscriptionService/TierConfig |
| BizBoost | 2 | Module ModuleSubscriptionService |
| Application/Payment | 3 | GrowNet StarterKitService |
| **Total** | **~59 violations** | |

---

## 0.4 ServiceProvider & Migration Registry

### All ServiceProviders Verified

**34 migration folders** — all have matching ServiceProviders with `loadMigrationsFrom()` calls. No orphaned folders or providers.

### Registration Issues

| Issue | Details |
|---|---|
| **In `config/app.php` only** (not in `bootstrap/providers.php`) | `CoreServiceProvider`, `StockFlowServiceProvider`, `WeddingServiceProvider`, `ZamStayServiceProvider` |
| **Registered in both** (duplicate) | `EmailMarketingServiceProvider`, `EmployeeDomainServiceProvider`, `GrowFinanceServiceProvider`, `LifeplusServiceProvider`, `SupportServiceProvider` |

### Discrepancies with AGENTS.md

- `database/migrations/stock-audit/` does NOT exist — the AGENTS.md table lists `stock-audit/` but the actual folder is `stockflow/`. `StockFlowServiceProvider` loads from `database/migrations/stockflow/`. The table entry should be corrected to `stockflow/`.
- `database/migrations/prime_edge/` exists and is loaded by `PrimeEdgeServiceProvider` — matches AGENTS.md status "Legacy"

---

## 0.5 Migration Scope Audit

### Summary

| Category | Count |
|---|---|
| Total migration directories | 34 |
| Cross-module violations | ~75 |
| Violating source modules | 15 |

### Top Violators

| Source Module | Violations | Tables Accessed |
|---|---|---|
| `grownet/` | 15 | `users` (Core), `investment_tiers` (Investor), `point_transactions` (Transaction), `employee_kpi_tracking` (Employee), `investor_notification_preferences` (Investor) |
| `core/` | 12 | `growbuilder_ai_feedback`, `growbuilder_chatbot_leads`, `grow_net_users`, `marketplace_products`, `sa_companies`, `cms_companies`, `bizboost_businesses`, `bizdocs_business_profiles`, `quick_invoice_profiles`, `transactions`, `prime_edge_*` |
| `contract/` | 4 | 6 CMS tables (BMS) |
| `module/` | 4 | 13 CMS tables (BMS), `messages` (Notification), `notifications` (Notification), `support_tickets` (Support) |
| `bizboost/` | 4 | `bizdocs_business_profiles` (BizDocs), `marketplace_sellers` (Marketplace) |

### Notable Issues

1. **`grownet/` modifies `users` table** — 10 migration files alter `users` (owned by Core). Historic MLM profile fields placed directly on users table.
2. **`core/` workspace refactoring adds `organization_id`** to 7 domain tables (intentional but violates boundaries).
3. **`contract/` and `construction/`** both create `cms_*` tables — these modules extend BMS rather than being independent.
4. **`module/` creates `cms_workflows`** — Operations module placed in `module/` but creates BMS-scoped tables.
5. **Table ownership conflicts**: `point_transactions` (created in `grownet/`, should be `transaction/`), `grow_net_users` (created in `core/`, should be `grownet/`).

---

## 0.6 Event Inventory

### Event Classes: 55 total across 16 namespaces

| Namespace | Events | Has Listeners? |
|---|---|---|
| Domain/Core/Events | 4 | Yes (3 listeners each) |
| Domain/BMS/Core/Events | 3 | No |
| Domain/Payment/Events | 2 | Yes (2 listeners each) |
| Domain/Employee/Events | 7 | Yes (1 subscriber) |
| Domain/StockFlow/Events | 6 | Yes (1 listener) |
| Domain/GrowStream | 3 | Yes (3 listeners) |
| Domain/Messaging/Events | 2 | No |
| Domain/Storage/Events | 3 | No |
| Domain/Support/Events | 4 | No |
| Domain/PrimeEdge/Events | 10 | No |
| app/Events (legacy) | 6 | Yes (5-10 listeners) |
| app/Events/BMS | 6 | Yes (3-7 listeners each) |
| app/Events/BizBoost | 5 | No (broadcast only) |
| app/Events/VentureBuilder | 4 | Yes (4 listeners) |
| app/Events/Employee | 5 | No (broadcast only) |
| app/Events/Support | 2 | No (broadcast only) |

### Cross-Module Event Flows

| Publisher | Event → | Consumer |
|---|---|---|
| Core | OrganizationCreated → | StockFlow, BMS, Core |
| BMS | InvoiceCreated → | GrowFinance, GrowBuilder, GrowMarket |
| BMS | ExpenseApproved → | Transaction |
| Payment | PaymentVerified → | Transaction, GrowNet (MLM) |
| Learning | CourseCompleted → | GrowNet (Points) |

### Issues

- **Three separate event namespaces** (legacy `app/Events/`, DDD `Domain/*/Events/`, infrastructure `Domain/*/Infrastructure/Events/`)
- **39 events with zero listeners** (broadcast-only or unused)
- **Duplicate VentureBuilder dispatching** — both legacy and DDD services dispatch same 4 events

---

## 0.7 Integration Pattern Map

### Pattern Breakdown

| Pattern | Count | Examples |
|---|---|---|
| Direct `DB::table()` cross-module | 60+ files | Legacy services querying other modules' tables |
| Eloquent model import cross-module | 50+ files | Infrastructure bypass in Services/, Controllers/ |
| `app(Service::class)` cross-module | 59 calls | Service locator across module boundaries |
| Event-driven (Laravel events) | 15 cross-module | BMS→GrowFinance, Payment→GrowNet |
| Webhook (incoming) | 2 | NOWPayments, Moneyunify, Pawapay |
| Broadcast (Pusher/WebSockets) | 12 events | BizBoost, Employee, Support realtime |
| Direct class instantiation | ~20 | `DetectSubdomain` middleware instantiating controllers |
| Shared database | Shared MySQL | All modules share one database, no tenant isolation |

### Primary Integration Issues

1. **Direct DB queries** bypassing all domain/repository layers
2. **Service locator pattern** (`app()`) instead of constructor injection
3. **Legacy `app/Events/` namespace** mixed with DDD events
4. **No event envelope** (versioning, correlation IDs, causation IDs)
5. **No contract interfaces** between modules — direct class coupling
6. **BMS→GrowFinance sync** (just deleted) was the worst example: 7 files of direct SQL sync
7. **No tenant isolation** — all data in same database with `business_id` scoping

---

## Recommendations (Immediate)

1. **Delete remaining orphaned BMS-GrowFinance sync routes/controllers** (if any exist)
2. **Move `grow_net_users` migration** from `core/` to `grownet/`
3. **Move `point_transactions` creation** from `grownet/` to `transaction/`
4. **Fix double-registered providers** — remove 5 entries from `config/app.php`
5. **Add 4 missing providers** to `bootstrap/providers.php`
6. **Correct AGENTS.md** — `stock-audit/` → `stockflow/`
