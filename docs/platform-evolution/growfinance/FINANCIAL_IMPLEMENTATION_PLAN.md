# MyGrowNet Financial Platform Architecture — Implementation Plan

> **Status:** Draft  
> **Version:** 1.0  
> **Aligns with:** `MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md` v5.0  
> **Objective:** Build the cross-domain financial infrastructure defined in the architecture — Platform Billing, Platform Payments, Financial Services Core, data ownership enforcement, and event wiring

---

## Overview

This plan covers the **shared financial infrastructure** that multiple domains depend on. It does not cover GrowFinance's internal accounting engine (see [GROWFINANCE_IMPLEMENTATION_PLAN.md](GROWFINANCE_IMPLEMENTATION_PLAN.md) for that).

### Scope

| Domain | Status | This Plan Builds |
|---|---|---|
| Platform Billing | Not built | Contract, events, subscription lifecycle, invoice-rendering |
| Platform Payments | Scattered | Unified contract, events, retry orchestration, settlement tracking |
| Financial Services Core | Barely exists | CurrencyService, ExchangeRateProvider, shared FX data |
| Cross-domain governance | Partial | Data ownership enforcement, financial CI checks, event wiring |

### Phases at a Glance

| Phase | Name | Duration | Dependency |
|---|---|---|---|
| F1 | Platform Billing Domain | 4 weeks | None (standalone) |
| F2 | Platform Payments Domain | 4 weeks | None (standalone) |
| F3 | Financial Services Core | 3 weeks | None (standalone) |
| F4 | Data Ownership & Table Migration | 3 weeks | Phases F1–F3 (tables owned) |
| F5 | Financial Event Wiring & Governance | 3 weeks | Phases F1–F4 |

**Total estimated duration:** ~17 weeks (Phases F1–F3 can run concurrently)

---

## Phase F1: Platform Billing Domain

**Duration:** 4 weeks  
**Dependency:** None  
**Goal:** Establish Platform Billing as a first-class financial domain with its own contract, events, and subscription lifecycle

### Rationale

Currently `subscription_plans` lives in Platform Core with no dedicated Billing domain. The architecture requires Platform Billing to own subscription plans, licensing, recurring invoices, and suspension decisions — with Platform Payments owning the payment collection side.

### Tasks

| # | Task | Deliverable | Reference |
|---|---|---|---|
| F1.1 | Create `app/Domain/PlatformBilling/` bounded context directory | Domain skeleton | §4.1 |
| F1.2 | Create `BillingProvider` contract with `getSubscription()`, `getPlan()`, `getInvoice()`, `isActive()` | Interface | Contract Catalog §8 |
| F1.3 | Implement BillingProvider (DELEGATE to Core models for now — refactor later) | Implementation | §4.1 |
| F1.4 | Create `app/Domain/PlatformBilling/Entities/Subscription.php` | Domain entity | §4.1 |
| F1.5 | Create `app/Domain/PlatformBilling/Entities/SubscriptionPlan.php` | Domain entity | §4.1 |
| F1.6 | Create `app/Domain/PlatformBilling/Entities/Invoice.php` | Domain entity | §4.1 |
| F1.7 | Create `SubscriptionRepositoryInterface` + Eloquent impl | Repository | §4.1 |
| F1.8 | Create `PlanRepositoryInterface` + Eloquent impl | Repository | §4.1 |
| F1.9 | Create `BillingService` with subscription lifecycle methods (create, renew, suspend, cancel, reactivate) | Service | §4.1 |
| F1.10 | Publish all 7 billing events: `platform.billing.subscription.created.v1`, `.renewed.v1`, `.suspended.v1`, `.cancelled.v1`, `platform.billing.invoice.issued.v1`, `.payment.due.v1`, `.grace_period.expiring.v1` | Events | §7 |
| F1.11 | Wire outbox for all billing events | Outbox adoption | §20 |
| F1.12 | Create BillingServiceProvider, register DI bindings + manifest | ServiceProvider | Platform standard |
| F1.13 | Create `grace_period_expiring` listener that decides suspension on payment failure | Dunning orchestration | §4.1 §4.2 |
| F1.14 | Migrate `subscription_plans` table ownership from Core to PlatformBilling | Migration | §10 |
| F1.15 | Move `GenerateRecurringInvoices` command into Billing domain | Command ownership | §4.1 |
| F1.16 | Register `BillingProvider` in IntegrationRegistry | Registry | §8 |

### Success Criteria

- `BillingProvider` resolves via IntegrationRegistry with all 4 methods working
- Subscription lifecycle (create → renew → suspend → cancel → reactivate) is fully event-driven
- Dunning handoff: billing `invoice.issued` → payments `collection_failed` → billing `suspended` is wired end-to-end
- `subscription_plans` table is owned by PlatformBilling migration folder

---

## Phase F2: Platform Payments Domain

**Duration:** 4 weeks  
**Dependency:** None  
**Goal:** Consolidate scattered payment logic into a single Platform Payments domain with unified contract, events, and retry orchestration

### Rationale

Payment adapters (MTN MoMo, Airtel Money, MoneyUnify) exist in `app/AntiCorruption/Payments/` but there is no unifying Payments domain. `payment_transactions` tables exist per-module (growbuilder, stockflow, grownet) with no central platform table. Payment events (`platform.payment.*`) do not exist. The document requires Platform Payments to own all payment gateway integration, collection, retry scheduling, and settlement.

### Tasks

| # | Task | Deliverable | Reference |
|---|---|---|---|
| F2.1 | Create `app/Domain/PlatformPayments/` bounded context directory | Domain skeleton | §4.2 |
| F2.2 | Refine `PaymentGateway` contract — align methods with doc (`process()`, `refund()`, `verify()`, `query()`) | Interface alignment | §8 |
| F2.3 | Create `SettlementProvider` contract with `reconciliationData()`, `settlementReports()` | Interface | §8 |
| F2.4 | Create `app/Domain/PlatformPayments/Entities/PaymentTransaction.php` | Domain entity | §4.2 |
| F2.5 | Create `app/Domain/PlatformPayments/Entities/PaymentAttempt.php` | Domain entity | §4.2 |
| F2.6 | Create `app/Domain/PlatformPayments/Entities/Settlement.php` | Domain entity | §4.2 |
| F2.7 | Create `PaymentTransactionRepositoryInterface` + Eloquent impl | Repository | §4.2 |
| F2.8 | Create `PaymentAttemptRepositoryInterface` + Eloquent impl | Repository | §4.2 |
| F2.9 | Create `PaymentService` — collect(), retry(), refund(), settle() | Service | §4.2 |
| F2.10 | Create `SettlementService` — reconcile(), track disputes | Service | §4.2 |
| F2.11 | Create `RetryOrchestrator` — retry scheduling with configurable schedule (3d, 7d, 14d) | Retry engine | §4.1 dunning |
| F2.12 | Publish all 7 payment events: `platform.payment.initiated.v1`, `.completed.v1`, `.failed.v1`, `.collection_failed.v1`, `.settled.v1`, `.refunded.v1`, `.settlement_reconciled.v1` | Events | §7 |
| F2.13 | Wire outbox for all payment events | Outbox adoption | §20 |
| F2.14 | Create `payment_transactions` unified migration in `database/migrations/platform_payments/` | Migration | §10 |
| F2.15 | Create `payment_attempts`, `settlements`, `payment_methods`, `payment_webhooks` tables | Migrations | §10 |
| F2.16 | Move MTN MoMo, Airtel Money, MoneyUnify adapters into PlatformPayments/Infrastructure/ | Adapter ownership | §4.2 |
| F2.17 | Create `PaymentGatewayImpl` that delegates to correct adapter via config | Gateway implementation | §4.2 |
| F2.18 | Wire `payment.collection_failed` → Platform Billing's `grace_period_expiring` | Dunning integration | §4.1 |
| F2.19 | Create PlatformPaymentsServiceProvider with DI + manifest | ServiceProvider | Platform standard |
| F2.20 | Register `PaymentGateway` + `SettlementProvider` in IntegrationRegistry | Registry | §8 |
| F2.21 | Begin `payment_logs` → `payment_transactions` mirror write (strangler fig) | Data migration | §15 |

### Success Criteria

- `PaymentGateway` resolves via IntegrationRegistry with all 4 methods working
- All 7 payment events published through outbox
- Retry orchestration follows 3d/7d/14d schedule and fires `collection_failed` on exhaustion
- Dunning integration: `collection_failed` → Platform Billing suspension decision
- `payment_transactions` mirrors `payment_logs` (strangler phase 1 complete)

---

## Phase F3: Financial Services Core

**Duration:** 3 weeks  
**Dependency:** None  
**Goal:** Establish Financial Services Core as the single source of truth for currencies and exchange rates

### Rationale

`CurrencyService` exists in two places (StockFlow, BMS) with no shared contract. `ExchangeRateProvider` does not exist. `currencies` and `exchange_rates` tables do not exist. The architecture requires a single Financial Services Core domain that all other domains query.

### Tasks

| # | Task | Deliverable | Reference |
|---|---|---|---|
| F3.1 | Create `app/Domain/FinancialServicesCore/` bounded context directory | Domain skeleton | §4.5 |
| F3.2 | Create `CurrencyService` contract with `convert(amount, from, to, date?)`, `getRate(from, to, date?)`, `supportedCurrencies()` | Interface | §8 |
| F3.3 | Create `ExchangeRateProvider` contract with `fetchRates(baseCurrency)`, `historicalRates(from, to, dateRange)` | Interface | §8 |
| F3.4 | Create `app/Domain/FinancialServicesCore/Entities/Currency.php` | Domain entity | §4.5 |
| F3.5 | Create `app/Domain/FinancialServicesCore/Entities/ExchangeRate.php` | Domain entity | §4.5 |
| F3.6 | Create `CurrencyRepositoryInterface` + Eloquent impl | Repository | §4.5 |
| F3.7 | Create `ExchangeRateRepositoryInterface` + Eloquent impl | Repository | §4.5 |
| F3.8 | Create `CurrencyServiceImpl` — implements CurrencyService | Implementation | §4.5 |
| F3.9 | Create `ExchangeRateProviderImpl` — Bank of Zambia rate source, configurable fallback | Implementation | §4.5 |
| F3.10 | Create `currencies` table migration in `database/migrations/financial_services_core/` | Migration | §10 |
| F3.11 | Create `exchange_rates` table migration | Migration | §10 |
| F3.12 | Seed common currencies (ZMW, USD, ZAR, GBP, EUR) | Seed data | §4.5 |
| F3.13 | Create `FinancialServicesCoreServiceProvider` with DI + manifest | ServiceProvider | Platform standard |
| F3.14 | Register `CurrencyService` + `ExchangeRateProvider` in IntegrationRegistry | Registry | §8 |
| F3.15 | Publish `platform.fx.rate_updated.v1` event when rates change | Event | §7 |
| F3.16 | Wire outbox for FX rate event | Outbox adoption | §20 |
| F3.17 | Refactor StockFlow `CurrencyService` → delegate to Financial Services Core | Migration | §4.5 |
| F3.18 | Refactor BMS `CurrencyService` → delegate to Financial Services Core | Migration | §4.5 |

### Success Criteria

- `CurrencyService` resolves via IntegrationRegistry with all 3 methods working
- `ExchangeRateProvider` resolves via IntegrationRegistry with both methods working
- `currencies` and `exchange_rates` tables exist and are owned by FinancialServicesCore
- Bank of Zambia rate fetching is automated (or mockable)
- StockFlow and BMS `CurrencyService` classes are removed (delegate to Financial Services Core)

---

## Phase F4: Data Ownership & Table Migration

**Duration:** 3 weeks  
**Dependency:** Phases F1–F3 domains must exist to own their tables  
**Goal:** Every financial table is owned by exactly one domain. Migration plan from §15 completed.

### Tasks

| # | Task | Deliverable | Reference |
|---|---|---|---|
| F4.1 | Create `platform:audit-financial-ownership` command that checks all financial tables against §10 ownership table | Audit command | §10 |
| F4.2 | Migrate `payment_logs` → `payment_transactions` Phase 2: cut reads to use PlatformPayments tables | Cutover | §15 |
| F4.3 | Migrate `payment_logs` → `payment_transactions` Phase 3: drop `payment_logs` table (after validation period) | Cleanup | §15 |
| F4.4 | Rename non-standard GrowFinance tables if needed (track in GROWFINANCE_IMPLEMENTATION_PLAN instead) | N/A — owned by GrowFinance plan | §15 |
| F4.5 | Add CI check: no module migration may write to another module's tables (extend ARCHITECTURE_CHECKS.md) | CI check 11 | §13 rule 12 |
| F4.6 | Add CI check: no business app may SELECT from financial domain tables | CI check 12 | §13 rule 2 |
| F4.7 | Ensure all financial domains export a `DimensionProvider` contract for operational dimensions | Contract enforcement | §6 |
| F4.8 | Create dimension resolution CI check: every application with operational dimensions must publish `DimensionProvider` | CI check 13 | §6 |

### Success Criteria

- `payment_logs` table removed (strangler fig complete)
- CI blocks any migration that touches another module's financial table
- CI blocks any business app querying financial domain tables directly
- All modules with operational dimensions expose `DimensionProvider`

---

## Phase F5: Financial Event Wiring & Governance

**Duration:** 3 weeks  
**Dependency:** Phases F1–F4  
**Goal:** All financial events from the architecture document are published, consumed, and governed. Missing events wired.

### Tasks

| # | Task | Deliverable | Reference |
|---|---|---|---|
| F5.1 | Create `bms.expense.recorded.v1` event in BMS (published from BmsExpenseSyncService) | New event | §7 |
| F5.2 | Wire `growmarket.order.placed.v1` and `growmarket.order.fulfilled.v1` events through platform event system (currently notification-only) | New events | §7 |
| F5.3 | Rename `growfinance.journal.created.v1` → `growfinance.journal.posted.v1` for consistency with doc | Rename event | §7 |
| F5.4 | Create `growfinance.account.balance.changed.v1` event | New event | §7 |
| F5.5 | Create `growfinance.period.closed.v1` event | New event | §7 |
| F5.6 | Create `growfinance.budget.updated.v1` event | New event | §7 |
| F5.7 | Create `growfinance.report.generated.v1` event | New event | §7 |
| F5.8 | Wire outbox for all missing financial events | Outbox adoption | §20 |
| F5.9 | Add Event Ownership Registry validation for all financial events — only owning module may publish | Governance | §11 |
| F5.10 | Add CI check: every financial event must be registered in EventOwnershipRegistry | CI check 14 | §11 |
| F5.11 | Add CI check: every financial event consumer must use `InboxService::processIfNew()` | CI check 15 | §20 |
| F5.12 | Update EVENT_INVENTORY.md with all financial events | Documentation | §22 |

### Success Criteria

- All 20+ financial events from §7 are published through outbox with correct ownership
- `growfinance.journal.posted.v1` is the canonical event name (`.created` removed)
- Event Ownership Registry rejects unauthorized publishing attempts
- All financial event consumers use the inbox idempotency pattern
- CI validates event ownership and inbox usage

---

## Dependency Graph

```
F1 (Platform Billing)    F2 (Platform Payments)    F3 (Financial Services Core)
        │                         │                           │
        └─────────────────────────┼───────────────────────────┘
                                  │
                                  ▼
                     F4 (Data Ownership & Migration)
                                  │
                                  ▼
                     F5 (Financial Event Wiring & Governance)
```

Phases F1, F2, and F3 can run **concurrently** (no shared dependencies). F4 requires all three to define their table ownership. F5 requires F1–F4 for full event coverage.

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Platform Payments domain conflicts with existing per-module payment tables | High | Medium | Strangler fig — mirror, validate, cutover, drop |
| Financial Services Core blocks StockFlow/BMS if unavailable | Low | High | Local cache with stale rate tolerance; degrade gracefully |
| Billing domain overlaps with existing Core subscription logic | Medium | Medium | Extract gradually — Billing contract delegates to Core initially |
| Event rename (.created → .posted) breaks existing listeners | Low | High | Publish both events during transition period; remove old name after validation |
| CI checks block legitimate migrations | Low | Low | Allowlist mechanism in ARCHITECTURE_CHECKS.md |

---

## Measuring Progress

| Metric | Target | Phase |
|---|---|---|
| `BillingProvider` resolves via IntegrationRegistry | Working | F1 |
| `PaymentGateway` resolves via IntegrationRegistry | Working | F2 |
| `CurrencyService` resolves via IntegrationRegistry | Working | F3 |
| `ExchangeRateProvider` resolves via IntegrationRegistry | Working | F3 |
| `payment_logs` table removed | Gone | F4 |
| Financial events using dot-notation + outbox | 100% | F5 |
| Event ownership violations blocked by CI | 100% | F5 |
| Financial event consumers using inbox | 100% | F5 |
