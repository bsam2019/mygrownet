# MyGrowNet Financial Platform Architecture

**Version:** 5.0  
**Status:** Final Draft  
**Last Updated:** 2026-07-26  
**Supersedes:** v4.0  
**Related:** [PLATFORM_INTEGRATION_ARCHITECTURE.md](PLATFORM_INTEGRATION_ARCHITECTURE.md), [EVENT_INVENTORY.md](EVENT_INVENTORY.md)

> This document governs all financial capabilities in the MyGrowNet ecosystem. Every module with financial behavior **must** conform to the boundaries, contracts, and ownership rules defined here. Future modules — POS, Hospital, School, Manufacturing, or any other — must be validated against this document before implementation.

---

## 1. Purpose

This document defines the financial architecture of the MyGrowNet platform. It establishes ownership boundaries, integration contracts, and architectural principles for all financial capabilities across the platform. Its purpose is to ensure every financial responsibility has a single owner while allowing applications to evolve independently and be extracted into separate services in the future.

---

## 2. Vision

MyGrowNet is building a cloud-native financial platform where business applications generate financial events and specialized financial services transform those events into accounting records, reports, settlements, and business intelligence.

---

## 3. Architectural Principles

| # | Principle | Rationale |
|---|---|---|
| 1 | **Single Responsibility** | Every financial domain owns exactly one concern. Billing does not do payments. Payments does not do accounting. Accounting does not do billing. |
| 2 | **Single Source of Truth** | Every piece of financial data lives in exactly one place. No duplicate ledgers, no duplicate transaction records, no duplicate account balances. |
| 3 | **Event First** | Financial domains communicate by publishing and consuming events. An application does not call another domain's repository — it publishes an event and the owning domain reacts. |
| 4 | **Accounting is Centralized** | GrowFinance is the sole system of record for journals, ledgers, and financial statements. No business application maintains its own accounting books. |
| 5 | **Payments are Centralized** | Platform Payments is the sole owner of payment gateway integration, transaction processing, settlement, and reconciliation. No business application talks directly to a payment provider. |
| 6 | **No Duplicate Business Logic** | If two modules need the same financial rule, that rule belongs in a shared financial domain — not copied into both modules. |
| 7 | **Tenant Isolation** | Every financial record is scoped to an organization. No cross-tenant data leakage. Queries, caches, and jobs all respect the tenant boundary. |
| 8 | **Cloud-First** | All financial services are designed for cloud deployment. No assumptions about local filesystem, single-server processes, or shared memory. |
| 9 | **Service Extraction Ready** | Every financial domain is structured so it can be extracted into an independent deployment without business logic changes. In-process calls and remote calls share the same interface. |
| 10 | **API-First** | Financial domains expose their capabilities through well-defined contracts (interfaces). Internal implementation details are never exposed across domain boundaries. |
| 11 | **Backward Compatibility** | Contract versions are explicit. Consumers pin to a version. Deprecation follows a documented timeline with overlap window. |
| 12 | **Financial Dimensions Belong to Their Source** | Business modules own operational dimensions (branches, departments, projects). GrowFinance owns accounting dimensions (cost centres). GrowFinance references operational dimensions but does not create or manage them. |

---

## 4. Financial Domains

This is the core of the architecture. Every financial bounded context is defined here with its ownership boundaries.

---

### 4.1 Platform Billing

**Scope:** Platform-level subscription billing, licensing, and revenue.

**Owns:**

- Subscription plan definitions and pricing
- Application licensing (per-org, per-user)
- Recurring invoice generation
- Subscription lifecycle (trial, active, expired, cancelled, suspended)
- Metered billing (usage-based charges)
- Invoice delivery (email, portal)
- Grace period policy and suspension decisions
- Subscription reactivation

**Does NOT own:**

- Payment processing (delegates to Platform Payments)
- Payment retry scheduling (delegates to Platform Payments — see orchestration below)
- Journal entries or ledger records (delegates to GrowFinance)
- Business-specific invoicing (AR from customer sales)

**Dunning orchestration:**

Platform Billing does not manage payment retries directly. The ownership handoff is:

```
Platform Billing
    │
    ├── Invoice issued ──→ Platform Payments attempts collection
    │
    │                      Platform Payments
    │                      ├── Retry 1 (3 days)
    │                      ├── Retry 2 (7 days)
    │                      ├── Retry 3 (14 days)
    │                      └── All exhausted ──→ payment.collection_failed
    │                                                   │
    │                      Platform Billing              │
    │                      └── Receives event ──→ Decides suspension
    │
    ├── Subscription suspended (after payment failure)
    ├── Grace period applied (if configured)
    └── Reactivation (when payment resolves)
```

**Key events published:**

- `platform.billing.subscription.created.v1`
- `platform.billing.subscription.renewed.v1`
- `platform.billing.subscription.suspended.v1`
- `platform.billing.subscription.cancelled.v1`
- `platform.billing.invoice.issued.v1`
- `platform.billing.payment.due.v1`
- `platform.billing.grace_period.expiring.v1`

**Key contracts provided:**

- `BillingProvider` — query subscription state, plan details, invoice history

---

### 4.2 Platform Payments

**Scope:** All money movement into and out of the platform.

**Owns:**

- Payment gateway integrations (MTN MoMo, Airtel Money, MoneyUnify, card processors)
- Payment collection (one-time and recurring)
- Payment retry scheduling and execution
- Refund processing
- Settlement tracking and reconciliation
- Payment webhook handling
- Dispute and chargeback management
- Payouts to merchants, affiliates, and service providers
- Payment method management (saved payment instruments)
- Payment attempt history (success, failure, reason codes)

**Does NOT own:**

- Accounting records (GrowFinance journals the settled amounts)
- Subscription plan logic or invoice generation
- Grace period policies or suspension decisions (escalates to Billing)
- Business-specific transaction categorization

**Key events published:**

- `platform.payment.initiated.v1`
- `platform.payment.completed.v1`
- `platform.payment.failed.v1`
- `platform.payment.collection_failed.v1` (after all retries exhausted)
- `platform.payment.settled.v1`
- `platform.payment.refunded.v1`
- `platform.payment.reconciled.v1`

**Key contracts provided:**

- `PaymentGateway` — process, refund, verify, query
- `SettlementProvider` — reconciliation data, settlement reports

---

### 4.3 GrowFinance

**Scope:** Full double-entry accounting for organizations.

**Owns:**

- Chart of accounts (account codes, types, categories)
- General ledger (all journal entries, immutably recorded)
- Receivable control account (the accounting-side AR balance)
- Payable control account (the accounting-side AP balance)
- Tax accounting treatment (VAT liability accounts, withholding tax payable, income tax provision)
- Tax reporting (VAT returns, withholding tax schedules, income tax computations)
- Financial statements (trial balance, P&L, balance sheet, cash flow)
- Cost centre definitions (the chart of cost centres for cost accounting)
- Budgeting and forecasting
- Fiscal year definitions and management
- Accounting period definitions (monthly, quarterly, annual)
- Period locking and closing
- Fixed asset register and depreciation
- Bank reconciliation
- Audit trail (all changes logged, no deletion)

**Does NOT own:**

- **Operational receivables** (customer collections, credit control, aging workflow — owned by Business Applications)
- **Operational payables** (supplier payment scheduling, purchase order matching — owned by Business Applications)
- Payment gateways (delegates to Platform Payments)
- Subscription billing logic (delegates to Platform Billing)
- Business-specific transaction creation (consumes events from business apps)
- Inventory valuation methods (receives data from StockFlow)
- Currency exchange rates or conversion (delegates to Financial Services Core)

**The boundary between operational and accounting AR/AP:**

| Concern | Owner | Example |
|---|---|---|
| Which customers owe money? | Business Application (BMS, StockFlow) | `invoices` table, `customers` table |
| When are reminders sent? | Business Application | Credit control workflow |
| What is the customer's credit limit? | Business Application | Customer credit check |
| Is a sale blocked by credit limit? | Business Application | Operational decision |
| What is the total receivables balance? | GrowFinance | Receivable control account in ledger |
| What is the AR aging on the balance sheet? | GrowFinance | Financial statement line item |
| Was a journal entry created for the invoice? | GrowFinance | dr Receivables, cr Revenue |

**Key events published:**

- `growfinance.journal.posted.v1`
- `growfinance.account.balance.changed.v1`
- `growfinance.period.closed.v1`
- `growfinance.budget.updated.v1`
- `growfinance.report.generated.v1`

**Key contracts provided:**

- `AccountingProvider` — journal creation, balance queries, report access

---

### 4.4 Business Applications

**Scope:** Domain-specific business operations that generate financial activity.

**Examples:**

- StockFlow — inventory management, sales, purchasing, cash registers
- GrowMarket — ecommerce products, orders, cart, checkout
- BMS — customer management, project invoicing, HR
- Hospital (future) — patient billing, insurance claims
- School (future) — tuition, fees, payroll
- Manufacturing (future) — job costing, bill of materials

**Owns:**

- Business operations (products, orders, patients, students, jobs)
- Domain-specific pricing and discounting
- Operational transaction records (sales, purchases, adjustments)
- **Operational receivables** — customer collections, credit control, aging workflow, reminder schedules, collection notes
- **Operational payables** — supplier payment scheduling, purchase order matching
- Inventory costing logic (FIFO, weighted average, standard cost)
- Business reports specific to the domain
- Operational dimensions (branches, departments, projects)
- Tax applicability decisions (is this product VATable? Is this customer exempt? Which tax category applies?)
- Product-level tax codes and customer tax exemption status

**Does NOT own:**

- Accounting logic (no journal entries, no ledger updates)
- Payment gateways (all payments go through Platform Payments)
- Core tax computation or reporting (delegated to GrowFinance)
- Financial statements
- Receivable/Payable control accounts (GrowFinance maintains the ledger side)

**COGS boundary — the critical rule:**

StockFlow determines **economic value**. GrowFinance determines **accounting treatment**.

Business applications publish:

```json
{
  "event": "stockflow.sale.completed.v1",
  "payload": {
    "sale_id": 1001,
    "items": [
      {
        "item_id": 5,
        "quantity": 2,
        "selling_amount": 15000,
        "inventory_cost": 1250
      }
    ],
    "currency": "ZMW"
  }
}
```

The business application provides **facts**: what was sold, at what price, and what it cost. GrowFinance applies **accounting interpretation**: how to book revenue (net of discounts, deferred recognition rules) and cost of sales.

StockFlow calculates inventory cost using its own costing method (FIFO, weighted average). GrowFinance receives the cost and determines the accounting treatment:

```
GrowFinance creates:
  Dr Cost of Sales    2,500
  Cr Inventory        2,500
```

The business application **computes the economic impact**. GrowFinance **applies the accounting rules**. This line is drawn intentionally: inventory costing logic lives in StockFlow (where FIFO/weighted-average domain expertise sits), not duplicated in GrowFinance.

**How they integrate:**

Business applications **publish events** when financial activity occurs. Specialized financial domains **consume those events** and create the appropriate accounting records.

| Business App | Event Published | Consumer | Result |
|---|---|---|---|
| StockFlow | `stockflow.sale.completed.v1` | GrowFinance | Journal entry based on sale + cost data |
| StockFlow | `stockflow.stock.adjusted.v1` | GrowFinance | Journal entry: dr COGS, cr Inventory |
| StockFlow | `stockflow.purchase.received.v1` | GrowFinance | Journal entry: dr Inventory, cr AP |
| GrowMarket | `growmarket.order.placed.v1` | Platform Payments | Payment collection initiated |
| GrowMarket | `growmarket.order.fulfilled.v1` | GrowFinance | Journal entry: dr Cash/AR, cr Revenue |
| BMS | `bms.invoice.created.v1` | GrowFinance | Journal entry: dr AR (control), cr Revenue |
| BMS | `bms.invoice.paid.v1` | GrowFinance | Journal entry: dr Cash, cr AR (control) |
| BMS | `bms.expense.recorded.v1` | GrowFinance | Journal entry: dr Expense, cr Cash/AP |
| Hospital | `hospital.bill.posted.v1` | GrowFinance | Journal entry: dr AR/Patient, cr Revenue |
| Hospital | `hospital.payment.collected.v1` | Platform Payments | Payment processed through gateway |

---

### 4.5 Financial Services Core

**Scope:** Shared financial primitives that multiple domains consume.

**Owns:**

- Currency definitions (currency codes, decimal places, symbols)
- Exchange rate providers (Bank of Zambia, market rates)
- Historical exchange rate storage
- Rate conversion service

**Does NOT own:**

- Accounting records (owned by GrowFinance)
- Payment transactions (owned by Platform Payments)
- Business-specific financial data

**Why a separate domain?**

Multiple domains need currencies and exchange rates:

- GrowFinance — multi-currency journals and reporting
- Platform Billing — subscription pricing in different currencies
- Platform Payments — settlement currency conversion
- StockFlow — foreign supplier purchasing
- Future Treasury — cross-currency cash management

Without a shared service, each domain would duplicate exchange rate logic, leading to inconsistent rates across the platform. Financial Services Core owns the single source of truth.

**Key events published:**

- `platform.fx.rate_updated.v1` — published when rates change (idempotent, consumers re-cache)

**Key contracts provided:**

- `CurrencyService` — `convert(amount, from, to, date?)`, `getRate(from, to, date?)`, `supportedCurrencies()`
- `ExchangeRateProvider` — `fetchRates(baseCurrency)`, `historicalRates(from, to, dateRange)`

---

## 5. Responsibility Matrix

This is the reference table for every financial capability in the platform.

| Capability | Financial Services Core | Platform Billing | Platform Payments | GrowFinance | Business Apps |
|---|---|---|---|---|---|
| Currency definitions | ✓ | | | | |
| Exchange rates | ✓ | | | | |
| Rate conversion | ✓ | | | | |
| Subscription plans | | ✓ | | | |
| License management | | ✓ | | | |
| Recurring invoices | | ✓ | | | |
| Grace period policy | | ✓ | | | |
| Suspension decisions | | ✓ | | | |
| Payment gateway integration | | | ✓ | | |
| Payment collection | | | ✓ | | |
| Payment retry scheduling | | | ✓ | | |
| Refunds | | | ✓ | | |
| Settlement reconciliation | | | ✓ | | |
| Dispute management | | | ✓ | | |
| Payouts to merchants | | | ✓ | | |
| Chart of accounts | | | | ✓ | |
| General ledger | | | | ✓ | |
| Journals | | | | ✓ | |
| Receivable control account | | | | ✓ | |
| Payable control account | | | | ✓ | |
| Tax computation & reporting | | | | ✓ | |
| Financial statements | | | | ✓ | |
| Budgeting | | | | ✓ | |
| Period closing | | | | ✓ | |
| Fixed assets | | | | ✓ | |
| Bank reconciliation | | | | ✓ | |
| Audit trail | | | | ✓ | |
| Domain pricing | | | | | ✓ |
| Sales orders | | | | | ✓ |
| Purchase orders | | | | | ✓ |
| Inventory management | | | | | ✓ |
| Inventory costing (FIFO, WAC) | | | | | ✓ |
| Payroll computation | | | | | ✓ |
| Operational reports | | | | | ✓ |
| Operational receivables | | | | | ✓ |
| Operational payables | | | | | ✓ |
| Customer credit control | | | | | ✓ |
| Supplier payment scheduling | | | | | ✓ |
| Cost centre definitions | | | | ✓ | |
| Operational dimensions (branch, dept, project) | | | | | ✓ |

---

## 6. Financial Dimensions and Reference Data Ownership

Financial reporting often cuts across operational dimensions: branches, departments, cost centres, projects, business units. A journal entry might need to record which branch, which department, and which project generated the transaction.

### Who owns dimensions?

**Business applications own the dimensions they create.** GrowFinance does not create or manage branches, departments, or projects — it simply records financial activity against them.

| Dimension | Owner | Example |
|---|---|---|---|
| Branch | StockFlow / Business App | A physical location. StockFlow creates branches; sales are attributed to a branch ID. **Operational.** |
| Department | Employee / Payroll | An operational grouping. Payroll manages departments; salary costs attributed. **Operational.** |
| Cost Centre | GrowFinance | An accounting classification. Administration, Marketing, Operations — defined in chart of accounts for cost accounting. **Accounting dimension.** |
| Project | VentureBuilder / Project module | A funded initiative. Project costs tracked, reported in GrowFinance. **Operational.** |
| Business Unit | Organization structure | Org-level grouping for consolidated reporting. **Operational.** |

**Key distinction:** Departments and cost centres are often confused. A department is an operational structure (HR manages it). A cost centre is an accounting classification (GrowFinance manages it). They may overlap in name but are separate concepts with separate ownership.

### How GrowFinance references dimensions

A journal entry includes dimension IDs as reference fields, but GrowFinance does not validate them against the source system — it only records them.

```json
{
  "journal_entry": {
    "account_code": "4000",
    "debit": 5000,
    "credit": 0,
    "dimensions": {
      "branch_id": 12,
      "department_id": 5,
      "cost_centre_id": 8,
      "project_id": 23
    }
  }
}
```

If a dimension is renamed or restructured in its owning domain, that domain publishes a dimension update event. GrowFinance does not retroactively change journal entries — reports join dimension labels at query time.

### Contract for dimension resolution

Any domain that owns dimensions must expose a `DimensionProvider` contract that GrowFinance (and other consumers) can query to resolve dimension labels at reporting time:

- `DimensionProvider::resolveLabels(array $dimensionIds): array` — given a set of dimension IDs, return display names for reporting

Examples: StockFlow provides `DimensionProvider` for branches; HR provides `DimensionProvider` for departments; GrowFinance provides `DimensionProvider` for cost centres.

This avoids GrowFinance needing direct database access to other domain tables while still producing readable reports.

### Key principle

> GrowFinance owns the **accounting structure**. Business applications own the **operational structure**. The two meet at query time through contracts or event-carried dimension IDs.

---

## 7. Financial Event Model

Financial domains communicate through events. These events form the platform's financial language. Every event is immutable, versioned, and published to the outbox for guaranteed delivery.

### Core Financial Events

```
-- Financial Services Core
platform.fx.rate_updated.v1

-- Platform Billing
platform.billing.subscription.created.v1
platform.billing.subscription.renewed.v1
platform.billing.subscription.suspended.v1
platform.billing.subscription.cancelled.v1
platform.billing.invoice.issued.v1
platform.billing.payment.due.v1
platform.billing.grace_period.expiring.v1

-- Platform Payments
platform.payment.initiated.v1
platform.payment.completed.v1
platform.payment.failed.v1
platform.payment.collection_failed.v1
platform.payment.settled.v1
platform.payment.refunded.v1
platform.payment.reconciled.v1

-- GrowFinance
growfinance.journal.posted.v1
growfinance.account.balance.changed.v1
growfinance.period.closed.v1
growfinance.budget.updated.v1
growfinance.report.generated.v1

-- Business Applications
stockflow.sale.completed.v1
stockflow.stock.adjusted.v1
stockflow.purchase.received.v1
stockflow.cash.discrepancy.v1

growmarket.order.placed.v1
growmarket.order.fulfilled.v1
growmarket.order.refunded.v1

bms.invoice.created.v1
bms.invoice.paid.v1
bms.expense.recorded.v1
```

### Event Schema Standard

```json
{
  "event": "stockflow.sale.completed.v1",
  "id": "evt_01J7XYZ...",
  "timestamp": "2026-07-26T14:30:00Z",
  "publisher": "stockflow",
  "payload": {
    "organization_id": 42,
    "sale_id": 1001,
    "items": [
      { "item_id": 5, "quantity": 2, "unit_price": 15000, "total": 30000 }
    ],
    "total_amount": 30000,
    "currency": "ZMW",
    "payment_method": "mtn_momo",
    "sold_at": "2026-07-26T14:28:00Z",
    "dimensions": {
      "branch_id": 12,
      "department_id": 5
    }
  }
}
```

### Event Flow Diagrams

**Cash / credit sale (no payment gateway involved):**

```
StockFlow
    |
    |-- stockflow.sale.completed.v1
    |
GrowFinance
    |
    |-- growfinance.journal.posted.v1
    |
    (Business application records operational AR; GrowFinance records
     the receivable control account entry)
```

**Sale requiring payment collection (e.g., ecommerce):**

```
StockFlow
    |
    |-- stockflow.sale.completed.v1
    |
GrowFinance                          Platform Payments
    |                                      |
    |<-- awaiting payment settlement -->   |
    |                                      |
    |                         platform.payment.settled.v1 -->|
    |                                      |
    |-- growfinance.journal.posted.v1
    |
    (Payment completes the accounting picture:
     Dr Cash, Cr Receivable; Dr COGS, Cr Inventory)
```

**Key rule:** A sale **always** reaches GrowFinance. Payment is **optional** — cash and credit sales go directly; ecommerce and billed sales wait for payment settlement before finalizing the journal entry.

**Important caveat:** Payment receipt does not automatically equal revenue recognition. If payment is received before delivery (e.g., ecommerce prepayment, annual subscription), the accounting entry may defer revenue:

```
Payment received:
  Dr Cash
  Cr Deferred Revenue (liability)

On delivery / period end:
  Dr Deferred Revenue
  Cr Revenue
```

Revenue recognition rules are determined by GrowFinance's Accounting Rules Engine, not by the timing of payment settlement.

---

## 8. Integration Contracts

Financial domains expose their capabilities through well-defined contracts (PHP interfaces). These contracts are the **only** way one domain accesses another's functionality at runtime.

### Rules for Contract Usage

1. **Applications never write another domain's tables.** A business application does not INSERT into `journals`, `payment_transactions`, or `subscriptions`.
2. **Applications never call another domain's repositories.** The repository is an internal implementation detail.
3. **Applications never import another domain's Eloquent models.** Cross-module Eloquent imports were eliminated Phase 11 — this rule prevents regression.
4. **Instead: publish events, call public contracts, or both.**
   - If you need to **notify** another domain that something happened → publish an event.
   - If you need to **request** another domain to do something → use its contract interface via the `IntegrationRegistry`.
   - If you need to **query** data owned by another domain → use its contract interface.

### Contract Catalog

| Contract | Domain | Methods | Version |
|---|---|---|---|
| `CurrencyService` | Financial Services Core | `convert()`, `getRate()`, `supportedCurrencies()` | v1 |
| `ExchangeRateProvider` | Financial Services Core | `fetchRates()`, `historicalRates()` | v1 |
| `PaymentGateway` | Platform Payments | `process()`, `refund()`, `verify()`, `query()` | v1 |
| `BillingProvider` | Platform Billing | `getSubscription()`, `getPlan()`, `getInvoice()`, `isActive()` | v1 |
| `AccountingProvider` | GrowFinance | `createJournalEntry()`, `getAccountBalance()`, `getTrialBalance()` | v1 |
| `DimensionProvider` | Business Applications | `resolveLabels()` | v1 |
| `InventoryProviderV2` | StockFlow | `getItemDetail()`, `adjustStock()`, `reserveStock()` | v2 |
| `NotificationProvider` | Platform Core | `send()`, `getUnreadCount()`, `markAsRead()` | v1 |

### Contract Resolution Flow

```
Consumer code
    │
    ▼
IntegrationRegistry::resolve(PaymentGateway::class)
    │
    ├── ModuleDiscovery: which module provides PaymentGateway?
    │       → Platform Payments
    │
    ├── IntegrationGuard: authenticated? org member? feature enabled?
    │       → Pass/Fail
    │
    ├── ContractInvoker: invoke with circuit breaker + retry + metrics
    │       → Local: instantiate PaymentGatewayImpl
    │       → Remote (future): HTTP call to Payment Service
    │
    ▼
PaymentGatewayImpl::process(...)
```

---

## 9. Service Extraction Strategy

Every financial domain is designed for independent deployment. The architecture does not change — only the deployment topology.

### Today: Modular Monolith

```
┌─────────────────────────────────────────────────────────────────┐
│                      Laravel Application                         │
│                                                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──┐ │
│  │Financial │  │ Platform │  │ Platform │  │GrowFinance│  │...│ │
│  │Infrastr. │  │ Billing  │  │ Payments │  │          │  │   │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  └──┘ │
│                                                                 │
│              Communication: In-process via contracts             │
└─────────────────────────────────────────────────────────────────┘
```

### Tomorrow: Independent Services

```
┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌────────┐
│Financial │  │ Platform │  │ Platform │  │GrowFinance│  │StockFlow│
│Infrastr. │  │ Billing  │  │ Payments │  │  Service  │  │ Service │
│ Service  │  │ Service  │  │ Service  │  │          │  │        │
└────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬───┘
     │             │             │             │             │
     │       ┌─────┴─────┐       │             │             │
     │       │  Message   │       │             │             │
     ├───────►   Queue    ◄───────┤             │             │
     │       │ (RabbitMQ) │       │             │             │
     │       └───────────┘       │             │             │
     │                           │             │             │
     └─────────────┬─────────────┘             │             │
                   │                           │             │
             ┌─────┴──────┐                    │             │
             │   API      │                    │             │
             │  Gateway   │◄───────────────────┼─────────────┘
             └────────────┘                    │
                                               │
                  CurrencyService, PaymentGateway, AccountingProvider
                  resolved via ApiGateway → remote HTTP
```

### What changes when extracting

| Aspect | Monolith | Independent Service |
|---|---|---|
| Contract resolution | `IntegrationRegistry::resolve(...)` | Same call — `ApiGateway` routes to HTTP |
| Event publishing | `OutboxService::insert()` | Same — worker publishes to remote queue |
| Event consuming | In-process listener | Queue worker in target service |
| Database | Shared database | Separate database per service |
| Deployment | `git push` + `php artisan migrate` | CI/CD per service |

### Extraction readiness principles

Every financial domain service must be structured so it can operate independently if extracted. The following principles apply to all financial domains:

1. **All platform-specific coupling must pass through contracts or adapters**, never direct imports of platform models, facades, or middleware
2. **Authentication and tenant resolution must be injectable**, not inherited from the platform guard
3. **Each domain's public contract must be sufficient** for external systems to integrate without knowing about MyGrowNet internals

GrowFinance-specific extraction requirements (operating modes, deployment paths) are documented in the [GrowFinance Enterprise Architecture](GROWFINANCE_ENTERPRISE_ARCHITECTURE.md) §1.

### What does NOT change

- Business logic in entities and services
- Event schemas and contracts
- Ownership boundaries
- The responsibility matrix

---

## 10. Data Ownership

Every table in the database has exactly one owner. No table is shared across domains.

### Financial Services Core

| Table | Owner |
|---|---|
| `currencies` | Financial Services Core |
| `exchange_rates` | Financial Services Core |

### Platform Billing

| Table | Owner |
|---|---|
| `subscription_plans` | Platform Billing |
| `application_subscriptions` | Platform Billing |

### Platform Payments

| Table | Owner |
|---|---|
| `payment_transactions` | Platform Payments |
| `payment_attempts` | Platform Payments |
| `settlements` | Platform Payments |
| `payment_methods` | Platform Payments |
| `payment_webhooks` | Platform Payments |

### GrowFinance

| Table | Owner |
|---|---|
| `accounts` (chart of accounts) | GrowFinance |
| `journals` | GrowFinance |
| `journal_lines` | GrowFinance |
| `fiscal_years` | GrowFinance |
| `accounting_periods` | GrowFinance |
| `budgets` | GrowFinance |
| `budget_items` | GrowFinance |
| `fixed_assets` | GrowFinance |
| `tax_rates` | GrowFinance |

### Business Applications

| Table(s) | Owner |
|---|---|
| `sa_sales`, `sa_items`, `sa_purchase_orders`, etc. | StockFlow |
| `products`, `orders`, `cart_items`, etc. | GrowMarket |
| `invoices`, `customers`, `expenses`, etc. | BMS |
| `grow_net_users`, `commissions`, `rewards` | GrowNet |
| etc. | Respective module |

### What cross-module reads look like

**Allowed:** GrowFinance reads StockFlow sale totals via `IntegrationRegistry::resolve(InventoryProviderV2::class)->getItemDetail(...)`.

**Forbidden:** GrowFinance running `SELECT * FROM sa_sales`.

---

## 11. Event Ownership

Every event has exactly one publisher. The publisher is the domain that owns the source data.

| Event | Publisher | Consumers |
|---|---|---|
| `platform.fx.rate_updated.v1` | Financial Services Core | GrowFinance, Platform Billing, Platform Payments |
| `platform.billing.subscription.*` | Platform Billing | GrowFinance, Platform Payments, Notifications |
| `platform.billing.invoice.issued.v1` | Platform Billing | GrowFinance, Platform Payments |
| `platform.payment.completed.v1` | Platform Payments | GrowFinance, Platform Billing |
| `platform.payment.failed.v1` | Platform Payments | Platform Billing (tracks, does not suspend) |
| `platform.payment.collection_failed.v1` | Platform Payments | Platform Billing (decides suspension) |
| `platform.payment.settled.v1` | Platform Payments | GrowFinance, Business Apps |
| `stockflow.sale.completed.v1` | StockFlow | GrowFinance, Platform Payments |
| `stockflow.stock.adjusted.v1` | StockFlow | GrowFinance |
| `stockflow.purchase.received.v1` | StockFlow | GrowFinance |
| `stockflow.cash.discrepancy.v1` | StockFlow | GrowFinance, Notifications |
| `growmarket.order.placed.v1` | GrowMarket | Platform Payments, StockFlow |
| `growmarket.order.fulfilled.v1` | GrowMarket | GrowFinance |
| `bms.invoice.created.v1` | BMS | GrowFinance |
| `bms.invoice.paid.v1` | BMS | GrowFinance |
| `bms.expense.recorded.v1` | BMS | GrowFinance |
| `growfinance.journal.posted.v1` | GrowFinance | Audit, Reporting, Financial Analytics |

**Rule:** If GrowFinance needs data from StockFlow to create a journal entry, StockFlow publishes the event. GrowFinance never creates events on StockFlow's behalf.

---

## 12. Future Growth

The architecture supports adding new financial capabilities without structural changes.

### Adding a new financial domain (e.g., Treasury)

1. Define ownership boundaries in a new `Financial Domains` section
2. Create its bounded context under `app/Domain/Treasury/`
3. Define its contracts and events
4. Register in `ModuleDiscovery` manifest
5. Wire to IntegrationRegistry
6. Existing domains consume its events or call its contracts

### Adding a new business application (e.g., Hospital)

1. Create the application module following platform conventions
2. Define operational dimensions it owns (branches, departments, etc.)
3. Define financial events it will publish (e.g., `hospital.bill.posted.v1`)
4. Implement `DimensionProvider` contract for label resolution
5. Publish events when financial activity occurs
6. GrowFinance listens and creates appropriate journal entries
7. Platform Payments handles payment collection
8. No changes to existing financial domains

### Future financial domains (candidates)

| Domain | Ownership |
|---|---|
| **Treasury** | Cash management, bank account reconciliation, liquidity forecasting |
| **Lending** | Loan origination, underwriting, repayment scheduling |
| **Investments** | Portfolio management, dividend processing, cap table |
| **Revenue Sharing** | Platform commission splits, affiliate payouts, merchant settlement |
| **Financial Analytics** | BI dashboards, trend analysis, anomaly detection, ML forecasting |
| **Audit & Compliance** | Regulatory reporting, audit log retention, compliance checks |
| **Credit Scoring** | Risk assessment, credit limits, payment history |

Each follows the same pattern: owns its data, publishes events, exposes contracts, consumes what it needs.

---

## 13. Architectural Rules

These are permanent rules. Every PR, every new module, every refactor is validated against them.

| # | Rule | Violation Example | Enforcement |
|---|---|---|---|
| 1 | Every financial responsibility has exactly one owner. | StockFlow creating journal entries. | Architecture review |
| 2 | Business applications never implement accounting logic. | BMS calculating deferred revenue. | Architecture review |
| 3 | GrowFinance is the sole owner of accounting records. | Platform Payments writing to `journals`. | CI check |
| 4 | Platform Payments is the sole owner of payment gateway integrations. | StockFlow calling MTN MoMo API directly. | CI check |
| 5 | Business applications own operational AR/AP; GrowFinance owns control accounts. | BMS writing to `journals` to record a receivable. | Architecture review |
| 6 | Inventory costing logic lives in the business application. | GrowFinance recalculating FIFO or weighted average. | Architecture review |
| 7 | Financial Services Core is the single source of truth for exchange rates. | GrowFinance and Billing maintaining separate rate tables. | CI check |
| 8 | Financial dimensions belong to their source application. | GrowFinance creating or managing branches. | Architecture review |
| 9 | Applications communicate through contracts or events only. | BMS importing `StockFlow\EloqueNT\SaleModel`. | `ARCHITECTURE_CHECKS.md` check 3 |
| 10 | Every financial event is immutable. | Modifying an event payload after publication. | Code review |
| 11 | Modules must be independently deployable. | Tight coupling between financial domains. | Extraction dry run |
| 12 | No module may directly access another module's database tables. | `SELECT * FROM sa_sales` in GrowFinance. | `platform:audit-tenant-scoping` |
| 13 | Every financial transaction must be traceable. | Missing audit log for a payment reversal. | Code review |
| 14 | The architecture must support future extraction into independent services without requiring business logic changes. | Hardcoded `app(Service::class)` cross-module calls. | `ARCHITECTURE_CHECKS.md` check 5 |

---

## 14. What This Architecture Is Not

This section is as important as the rules above. It documents explicit non-goals to prevent architectural drift.

**Platform Payments is not an accounting system.**

- It does not know about debits, credits, or double-entry.
- It records payment transactions, not journal entries.
- It fires events when money moves; GrowFinance decides how to book it.

**GrowFinance is not a payment gateway.**

- It does not integrate with MTN MoMo, Airtel Money, or any payment provider.
- It does not process payments, handle webhooks, or reconcile settlements.
- It receives settled payment events and creates accounting records.

**GrowFinance is not an operational AR system.**

- It does not manage customer relationships, send reminders, or make collection calls.
- It records the **control account balance** for receivables on the balance sheet.
- Operational AR (who owes what, when to follow up) belongs to the business application.

**GrowFinance is not a currency exchange service.**

- It does not fetch exchange rates or decide conversion methodologies.
- It consumes rates from Financial Services Core and converts amounts as needed.
- Rate source selection and historical rate storage belong to Financial Services Core.

**Business applications do not create journal entries.**

- StockFlow does not write to `journals` or `ledgers`.
- GrowMarket does not compute tax journal entries.
- A future Hospital module does not record its own AR in GrowFinance's tables.
- Business applications publish events; GrowFinance handles the accounting treatment.

**Business applications do not recalculate inventory in accounting terms.**

- StockFlow determines **economic value** (inventory cost using FIFO/weighted average).
- GrowFinance determines **accounting treatment** (which accounts to debit and credit).
- GrowFinance does not re-run FIFO calculations — it trusts the cost data from StockFlow.

**Business applications own their operational dimensions; GrowFinance owns accounting dimensions.**

- StockFlow creates branches; GrowFinance records the branch ID in journal entries.
- Payroll manages departments; GrowFinance includes department IDs in cost records.
- GrowFinance creates and manages cost centres (accounting dimension).
- GrowFinance never creates, renames, or deletes operational dimensions (branch, department, project).
- If an operational dimension restructures, the business application publishes an update event.

**Platform Billing is not payment retry orchestration.**

- It does not schedule retry attempts or communicate with gateways.
- It receives `payment.collection_failed` and decides subscription suspension.
- Retry scheduling is owned by Platform Payments.

**Platform Billing is not accounts receivable.**

- It generates invoices and manages subscriptions.
- It does not track which customers have paid or pursue collections.
- Payment tracking and AR management belong to business applications and GrowFinance.

**Business modules are not financial reporting systems.**

- They provide operational reports (e.g., stock movement report, sales by cashier).
- Financial reporting (P&L, balance sheet, cash flow) is owned by GrowFinance.
- A consolidated dashboard across all modules is owned by Financial Analytics (future).

**Platform services do not implement business accounting.**

- Platform Payments does not know if a transaction is COGS or operating expense.
- Platform Billing does not categorize revenue by business segment.
- Business-specific accounting rules stay in the business domain and are communicated through event payloads.

---

## 15. Transitional Architecture / Technical Debt

The architecture described above is the target state. The current codebase reflects a partially evolved architecture with legacy structures that must be migrated.

### Known technical debt

| Legacy | Current Owner | Target Owner | Migration Plan |
|---|---|---|---|
| `payment_logs` table | Platform Core | Platform Payments | Phase 1: Mirror writes to `payment_transactions`. Phase 2: Read from Platform Payments. Phase 3: Drop `payment_logs`. |
| `app_settings` table (shared) | Platform Core | Platform Core (shared utility — will remain) | N/A — shared config is by design |
| Cross-module Eloquent imports | Various | Eliminated Phase 11 | Complete for service/domain layers; remaining in controllers (tracked in CONTRACT_MIGRATION_TRACKER.md) |
| Exchange rates embedded in domain logic | Various | Financial Services Core | Consolidate into a single `fx_rates` table and `CurrencyService` contract |

### Migration principles

1. **Don't block new features on cleanup.** Technical debt migrations follow the strangler fig pattern — new code follows the target architecture; legacy code is migrated incrementally.
2. **Mirror before cutover.** When moving table ownership, write to both locations first, validate parity, then cut reads.
3. **Document ownership changes.** Every migration is logged in this document's version history.
4. **No data migrations without rollback plan.** All table moves include a reverse migration script.

---

## 16. Financial Transaction Lifecycle

Every financial transaction follows a defined lifecycle across domain boundaries. This section defines the stages and where each stage is owned.

### Lifecycle stages

```
Business Event (app)
      │
      ▼
Operational Record Created (app)
      │
      ▼
Financial Event Published (app → outbox)
      │
      ▼
Financial Domain Processing (GrowFinance / Payments / Billing)
      │
      ├── Accounting Entry Created (GrowFinance)
      │
      ├── Payment Processed (Platform Payments) — if applicable
      │
      └── Settlement / Reconciliation (Platform Payments)
              │
              ▼
      Reporting (GrowFinance)
              │
              ▼
      Period Closing (GrowFinance)
```

### Stage ownership

| Stage | Owner | Description |
|---|---|---|
| Business event | Business Application | A sale, purchase, invoice — occurs in the application |
| Operational record | Business Application | The application records the transaction in its own tables |
| Financial event | Business Application | Application publishes event via OutboxService |
| Accounting entry | GrowFinance | Consumes event, creates journal entry per accounting rules |
| Payment processing | Platform Payments | Collects or disburses funds (optional — cash/credit sales skip this) |
| Settlement | Platform Payments | Reconciles provider settlement with expected amounts |
| Reporting | GrowFinance | Financial statements, trial balance |
| Period closing | GrowFinance | Locks period, carries forward balances |

### Example: StockFlow sale lifecycle

```
Customer buys item at clinic
      │
      ▼
StockFlow creates sale record in sa_sales
      │
      ▼
stockflow.sale.completed.v1 published via outbox
      │
      ├── GrowFinance receives event
      │     ├── AccountingRulesEngine determines accounts
      │     ├── Creates journal: dr Cash/AR, cr Revenue
      │     ├── Creates journal: dr COGS, cr Inventory
      │     └── growfinance.journal.posted.v1 published
      │
      └── If payment via MTN MoMo:
            Platform Payments receives event
              ├── Processes payment through MTN MoMo gateway
              ├── On settlement: platform.payment.settled.v1
              └── GrowFinance updates journal if needed
```

This lifecycle answers the question "at what point does accounting happen?" — it happens when GrowFinance consumes the financial event, not when the business application records the sale.

---

## 17. Accounting Rules Engine

GrowFinance owns an Accounting Rules Engine that maps financial events to journal entries using configurable rules. This allows adding new business applications, tax regimes, or revenue recognition policies without code changes.

**What this architecture requires:**
- Business applications include sufficient context in their events (product category, tax category, customer type) for the rules engine to determine the correct accounts
- Rules are configurable per organization — no hardcoded account mappings
- The rules engine supports event-to-template matching, account determination, and organization-specific overrides

Implementation details (mapping tables, account determination conditions, storage schema, rule configuration) are documented in the [GrowFinance Enterprise Architecture](GROWFINANCE_ENTERPRISE_ARCHITECTURE.md) §8.5 and §16.

---

## 18. Tax Architecture

Tax is one of the most complex areas in financial systems. This section formalizes the split between business applications and GrowFinance.

### Responsibility split

| Concern | Owner | Example |
|---|---|---|
| Is this product VATable? | Business Application | Medicine = exempt, Electronics = 16% |
| Is this customer exempt? | Business Application | NGO customer has exemption certificate |
| Which tax category applies? | Business Application | Standard VAT, reduced VAT, zero-rated, exempt |
| What is the tax accounting treatment? | GrowFinance | Dr Cash, Cr Revenue, Cr VAT Payable |
| Tax liability computation | GrowFinance | Total VAT collected minus input VAT |
| Tax return filing | GrowFinance | VAT return, withholding tax schedule |
| Tax authority reporting | GrowFinance | Monthly/quarterly returns per jurisdiction |

**Data ownership:** Business applications own product tax categories and customer exemption data in their own tables. GrowFinance owns tax rate definitions, tax liability calculations, and tax return records. GrowFinance-specific table schemas, tax processing flow, and compliance requirements are documented in the [GrowFinance Enterprise Architecture](GROWFINANCE_ENTERPRISE_ARCHITECTURE.md) §8.5 and §16.

---

## 19. Correction and Reversal Model

Financial records are immutable after posting. Corrections are handled through reversal transactions, never direct edits.

### Rules

1. **No direct modification.** Once a journal entry is posted, its lines are never edited.
2. **Corrections are new entries.** A correction creates a reversal entry that cancels the original, then a new correct entry.
3. **Every reversal references the original.** The reversal stores the original journal entry ID for audit trail.
4. **Reversals are timestamped and attributed.** Who reversed, when, and why are recorded.

### Correction flow

```
Original entry (posted in error):
  Dr Cash       10,000
  Cr Revenue    10,000
      │
      ▼
Reversal entry (references original):
  Dr Revenue    10,000
  Cr Cash       10,000
  reversal_of: journal_entry_id = 123
  reason: "Incorrect account — should be Deferred Revenue"
      │
      ▼
Corrected entry:
  Dr Cash       10,000
  Cr Deferred Revenue 10,000
```

### Period considerations

**Open period:** Reversal + correction allowed without special approval.

**Closed period:** Reversal allowed (audit trail required, reason mandatory). Correction posts to the current open period, not the closed one. A memo entry records the original period context.

Implementation details (table schemas, contract methods) are documented in the [GrowFinance Enterprise Architecture](GROWFINANCE_ENTERPRISE_ARCHITECTURE.md) §5.4, §6.5, and §15.

---

## 20. Idempotency & Reliability

Because events can be delivered more than once, every financial consumer must be idempotent.

### Idempotency rules

1. **Every financial event has a unique ID.** The `event_id` field in the event envelope is a UUID.
2. **Consumers store processed event IDs.** Each consumer maintains a deduplication table (`processed_events`).
3. **Duplicate events are rejected silently.** If an event ID already exists in the consumer's deduplication table, the event is acknowledged but not processed.
4. **Idempotency keys for non-event operations.** Operations initiated via contracts (e.g., `PaymentGateway::process()`) carry an idempotency key generated by the caller.
5. **Replay is supported.** The deduplication table allows replay of historical events — events with the same ID are skipped, not re-processed.

### Deduplication table schema

```
processed_events:
  event_id: string (UUID, primary key)
  event_name: string
  consumer: string (which service/listener processed it)
  status: string (processed, failed)
  processed_at: timestamp
  payload_hash: string (for integrity verification)
```

The `InboxService::processIfNew()` method already implements this pattern — every financial event consumer must use it.

### Retry policy

| Attempt | Delay | Action on failure |
|---|---|---|
| 1 | Immediate | Log warning, retry |
| 2 | 5 seconds | Log warning, retry |
| 3 | 30 seconds | Log error, send to DLQ |
| DLQ | — | Alert sent, manual replay required |

### Failure classification

| Error type | Retryable? | Example |
|---|---|---|
| Transient | Yes | Database deadlock, network timeout |
| Validation | No | Missing required field in event payload |
| Authorization | No | Consumer lacks permission to post journal |
| Configuration | No | Missing account mapping rule |
| Concurrency | Yes | Optimistic lock conflict on journal sequence |

### Dead letter queue

Failed events (after all retries) are stored in the `dead_letter_queue` table. Admin tooling provides:
- View failed events with error details
- Replay individual or batch of events
- Purge events after investigation
- Alert threshold monitoring (>5 failed events in 15 minutes)

These capabilities are already built — see `DeadLetterService` and `CheckPlatformAlerts` command.

---

## 21. Integration Failure Handling

Beyond idempotency, the platform must handle integration failures between financial domains.

### Failure scenarios

| Scenario | Impact | Recovery |
|---|---|---|
| GrowFinance is down when StockFlow publishes sale | Sale event in outbox, not consumed | Worker retries; outbox retry policy catches it |
| Platform Payments gateway times out | Payment not collected | Retry per payments retry schedule |
| Accounting Rules Engine missing a mapping | Event consumed, journal creation fails | Event goes to DLQ; admin creates mapping and replays |
| CurrencyService unavailable during journal creation | Journal cannot convert amount | Retry; if persistent, queue for manual intervention |
| Network partition between monolith and future Payment Service | Contract call fails | Circuit breaker opens; ApiGateway returns cached fallback |

### Circuit breaker rules

| State | Behaviour | Transition |
|---|---|---|
| Closed | Normal operation — all calls pass through | After 5 consecutive failures → Open |
| Open | Calls fail immediately without attempting | After 30 seconds → Half-Open |
| Half-Open | Single test call allowed | Success → Closed. Failure → Open |

The `ContractInvoker` class already implements this pattern for all contract-based integrations.

### Alerting thresholds

| Metric | Threshold | Action |
|---|---|---|
| Failed event rate | >5% over 15 minutes | Alert to #finance channel |
| Dead letter queue | Non-empty for >1 hour | Alert to on-call engineer |
| Payment gateway failure | 3 consecutive failures | Alert to payments team |
| Circuit breaker open | >5 minutes | Alert to platform team |

### Manual replay procedure

See `docs/platform-evolution/REPLAY_RUNBOOK.md` for the complete replay procedure. Key steps:

1. Identify the failed events in DLQ
2. Fix the root cause (add missing rule, restart service, etc.)
3. Select events to replay via admin UI or `platform:replay-events` command
4. Verify the journal entries were created correctly
5. Confirm no duplicates were created (idempotency keys prevent this)

---

## 22. Relationship to Existing Documents

| Document | Relationship |
|---|---|
| `PLATFORM_INTEGRATION_ARCHITECTURE.md` | Parent document. This financial architecture extends it with domain-specific ownership and event models. |
| `EVENT_INVENTORY.md` | Catalog of all platform events. This document defines which events are financial and who owns them. |
| `FUTURE_VISION.md` | Long-term extraction roadmap. This document provides the financial-specific extraction strategy. |
| `ARCHITECTURE_CHECKS.md` | CI enforcement. New checks will be added for financial boundary rules. |
| `IMPLEMENTATION_PLAN.md` | Execution tracking. Financial domain implementation phases will be tracked here. |
| `CONTRACT_MIGRATION_TRACKER.md` | Tracks migration of cross-module service calls to contract-based resolution. |
| `REPLAY_RUNBOOK.md` | Manual event replay procedure for financial event recovery. |

---

## Appendix A: Glossary

| Term | Definition |
|---|---|
| **Accounting AR/AP** | The control account balances in GrowFinance representing total receivables/payables on the balance sheet. |
| **Accounting Rules Engine** | The component within GrowFinance that maps financial events to journal entries using configurable rules. |
| **Bounded Context** | A domain boundary with its own data, logic, and language. |
| **Business Application** | A module that performs domain-specific operations (e.g., StockFlow for inventory). |
| **Contract** | A PHP interface that defines a capability. Resolved via `IntegrationRegistry`. |
| **Cost Centre** | An accounting classification within the chart of accounts used for cost accounting. Owned by GrowFinance. |
| **Dead Letter Queue** | Storage for events that failed processing after exhausting all retries. |
| **Economic Value** | The monetary impact of a transaction as determined by the business application (e.g., inventory cost per FIFO). |
| **Event** | An immutable message published when something happens. Delivered via outbox/inbox. |
| **Financial Domain** | A bounded context whose primary responsibility is financial (e.g., Platform Payments, GrowFinance). |
| **Financial Event** | An event with financial significance (a sale, a payment, a journal entry). |
| **Financial Services Core** | The domain responsible for shared financial primitives (currencies, exchange rates, conversion service). |
| **Financial Transaction Lifecycle** | The end-to-end stages a financial transaction passes through, from business event to period closing. |
| **IntegrationGuard** | Security layer that validates authentication, authorization, and feature flags before contract invocation. |
| **Operational AR/AP** | The day-to-day management of customer collections and supplier payments within a business application. |
| **Operational Dimension** | A business structure used for reporting (branch, department, project). Owned by the source application. |
| **Platform Billing** | The domain responsible for subscription management and invoice generation. |
| **Platform Payments** | The domain responsible for payment collection, retry scheduling, settlement, and reconciliation. |
| **Reversal** | A correcting journal entry that cancels a previously posted entry. Uses `reversal_of` to reference the original. |
| **GrowFinance** | The domain responsible for double-entry accounting, financial reporting, and tax. |

---

## Appendix B: Version History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | 2026-07-26 | System | Initial draft — financial domain boundaries, event model, responsibility matrix, architectural rules |
| 2.0 | 2026-07-26 | System | Split AR/AP into operational vs accounting; added Financial Services Core (multi-currency); clarified COGS boundary; defined Billing/Payments dunning orchestration; added Financial Dimensions section; added Transitional Architecture / technical debt section; updated responsibility matrix, events, and contract catalog; added principle 12 |
| 3.0 | 2026-07-26 | System | COGS event payload uses facts (selling_amount) not interpretation (revenue); financial periods moved from FSC to GrowFinance; explicit tax applicability vs treatment split; cost centres owned by GrowFinance; event flow diagram corrected (payment is optional); GrowFinance independence extraction statement added; renamed from Financial Infrastructure to Financial Services Core |
| 4.0 | 2026-07-26 | System | Added 6 essential sections: Financial Transaction Lifecycle (§16), Accounting Rules Engine (§17), Tax Architecture (§18), Correction & Reversal Model (§19), Idempotency & Reliability (§20), Integration Failure Handling (§21). Added GrowFinance operating modes (Platform/SaaS/Embedded). Added revenue recognition caveat. Widened DimensionProvider to any domain. Clarified cost centre vs department distinction. Deferred 12 future sections to FUTURE_FINANCIAL_FEATURES.md. |
| 5.0 | 2026-07-26 | System | Removed GrowFinance-specific implementation detail. Replaced operating modes and code paths with generalized extraction readiness principles (§9). Condensed Accounting Rules Engine (§17) to cross-domain requirements with reference to child doc. Stripped tax table schemas and flow diagram from Tax Architecture (§18) — kept only responsibility split. Removed implementation notes (column names, contract methods) from Correction model (§19). All GrowFinance implementation details now live exclusively in GROWFINANCE_ENTERPRISE_ARCHITECTURE.md. |
