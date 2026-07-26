# GrowFinance Enterprise Accounting Platform Architecture

**Version:** 6.0  
**Status:** Engineering-Ready Architecture  
**Last Updated:** 2026-07-26  
**Supersedes:** v5.2  
**Related:** [MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md](MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md) v4.0

> This document defines the architecture, product capabilities, and technical direction of GrowFinance as an enterprise-grade accounting platform designed to compete with established accounting systems such as Pastel, Sage, QuickBooks, and ERP financial modules while operating as a first-class financial service within the MyGrowNet ecosystem.

**Relationship to the Financial Platform Architecture:** The [MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md](MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md) defines who owns what financial responsibility across the platform. This document does not redefine those boundaries — it consumes them. GrowFinance is a financial domain within that architecture. This document describes what GrowFinance itself must become as a product.

---

## 1. Product Vision

GrowFinance is a cloud-native accounting and financial intelligence platform that provides organizations with complete financial control through double-entry accounting, automation, compliance, analytics, and integration capabilities.

The goal is not only bookkeeping. GrowFinance should become the financial operating system for SMEs, corporates, NGOs, government entities, and platform businesses — capable of serving as a standalone accounting service while deeply integrated into the MyGrowNet ecosystem.

**Operating modes** (defined in the Financial Platform Architecture §9):

| Mode | Description |
|---|---|
| **Platform** (primary) | Serves MyGrowNet tenants through the normal event/contract pipeline. Uses Platform Core for authentication, organization management, and permissions — no duplicate identity system. |
| **Standalone SaaS** (future) | Independent deployment where GrowFinance manages its own auth and orgs. Not built in initial phases — Platform Core already solves identity and multi-tenancy. |
| **Embedded** (future) | Third-party software integrates via the `AccountingProvider` API contract |

**Important:** For Phases 1-3, GrowFinance operates exclusively as a MyGrowNet Platform application. It uses the platform's existing users, organizations, workspaces, permissions, and billing infrastructure. Building separate authentication or organization management in the initial phases would be duplicating Platform Core capabilities. Standalone SaaS capability is a future product decision, not an immediate engineering requirement.

**Architectural readiness note:** Although GrowFinance is Platform-only in Phases 1-3, all new code should follow patterns that enable future extraction:
- Authentication and tenant resolution should pass through contracts or adapters, not direct imports
- No hard coupling to Platform Core middleware or session
- Integration points use the event/contract pipeline (not direct database access)
- These patterns are documented in the Financial Platform Architecture §9 and enforced via code review. The goal is to avoid a costly extraction refactor later, not to build the full multi-mode infrastructure now.

---

## 2. Competitive Positioning

| Capability | Traditional Accounting Software | GrowFinance Target |
|---|---|---|
| Deployment | Desktop / cloud hybrid | Cloud native |
| Multi-company | Limited | Native multi-organization |
| Multi-currency | Add-on | Core capability (via Financial Services Core) |
| Integrations | API extensions | Event-driven platform |
| Real-time reporting | Limited | Real-time financial intelligence |
| Automation | Rules-based | AI-assisted workflows |
| Industry modules | Separate products | Connected platform applications |
| Payments | External | Native platform payments integration |
| Data ownership | Application database | Service-oriented architecture |
| Tenant isolation | Single-tenant or bolt-on | Multi-tenant by design |
| Correction model | Direct edit | Immutable journal with reversal entries |
| Schema flexibility | Rigid | Configurable chart of accounts per org |

**Market entry scope:** GrowFinance targets Zambia as its first market (Phase 1-2). This means the Tax Engine, compliance rules, and reporting formats are Zambia-specific initially. The competitive comparison table (§2) lists global ambitions (Pastel, Sage, QuickBooks) to define the long-term product vision, but Phase 1-2 engineering should focus exclusively on Zambian tax and compliance requirements. Expansion to other jurisdictions is Phase 4+.

### 2.5 Next-Generation Accounting Principles

These principles distinguish GrowFinance from traditional desktop accounting software and guide all architectural decisions.

**Real-time collaboration:**
- Multiple users work simultaneously on the same organization's books
- Accountant prepares a journal; manager approves remotely; finance officer receives notification
- Reports update immediately after posting — no batch processing
- Live activity tracking shows who is working on what
- Collaborative workflows with in-app approvals and comments

**Immutable financial records:**
- Financial records are corrected through new transactions, not by editing history
- Every correction follows the chain: Original → Error Discovered → Reversal Journal → Correction Journal
- No delete or update operations on journal tables — only INSERT
- Full audit trail preserved through reversal references

**Connected finance:**
- Accounting does not operate in isolation — it consumes events from every business operation
- Sales, purchases, inventory, payments, payroll — all flow into GrowFinance automatically
- Finance teams focus on analysis and compliance, not data entry

**Real-time reporting:**
- Trial balance reflects every posted journal within seconds
- Period-end closing is accelerated through automation, not a multi-day manual process
- Financial intelligence (forecasts, anomalies) continuously analyses data without user intervention

---

## 3. Accounting Engine Architecture

GrowFinance's internal domain structure:

```
GrowFinance
│
├── General Ledger       Core double-entry, journals, chart of accounts
├── Accounts Receivable  Customer balances, aging, collections analytics
├── Accounts Payable     Supplier balances, payment obligations
├── Fixed Assets         Asset register, depreciation, disposal
├── Tax Engine           VAT, withholding tax, tax reporting
├── Budgeting            Budget creation, monitoring, variance analysis
├── Cost Accounting      Cost centres, departments, project profitability
├── Financial Reporting  Statements, ratios, consolidated reporting
├── Consolidation        Multi-company, intercompany eliminations, group reporting
├── Audit                Immutable trail, reversal tracking, compliance logging
└── Compliance           Regulatory reporting, period locks, retention
```

Each subdomain follows the same DDD pattern used across the platform: Entities, ValueObjects, Repositories (interfaces), Services, with Infrastructure implementations in `Infrastructure/Persistence/`.

### 3.1 Financial Core Platform Layers

The subdomains above are powered by a shared engine stack within GrowFinance. This stack is the runtime that processes every transaction, enforces every rule, and produces every report.

```
                    GrowFinance

                         |

             Financial Core Platform

                         |

 ┌─────────────────────────────────────────────────────┐
 │                                                     │
 │  General Ledger Engine                               │
 │  (chart of accounts, balance computation,            │
 │   period management, financial statements)           │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Accounting Rules Engine                             │
 │  (account mapping, posting templates,                │
 │   tax treatment rules, revenue recognition rules,    │
 │   industry-specific accounting rules)                │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Transaction Processing Engine                       │
 │  (journal creation, validation, posting,             │
 │   duplicate detection, source event handling)        │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Posting Engine                                      │
 │  (debit/credit validation, account resolution,       │
 │   dimension tagging, balance update, audit logging)  │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Tax Engine                                          │
 │  (rate resolution, liability computation,            │
 │   input/output VAT tracking, return generation)      │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Revenue Recognition Engine                          │
 │  (deferred revenue schedules, accrued revenue,       │
 │   subscription accounting, contract-based            │
 │   revenue allocation, ASC 606 / IFRS 15 patterns)    │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Banking Engine                                      │
 │  (bank feeds, statement import, auto-matching,       │
 │   reconciliation status, cash position dashboard,    │
 │   duplicate detection, bank rules)                   │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Reporting Engine                                    │
 │  (financial statements, ratio computation,           │
 │   snapshot management, report scheduling)            │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Budget Engine                                       │
 │  (budget creation, versioning, variance analysis,    │
 │   forecasting data preparation)                      │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Workflow Engine                                     │
 │  (approval chains, period closure workflows,         │
 │   notification routing, escalation)                  │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Integration API Layer                               │
 │  (AccountingProvider contract, REST API,             │
 │   webhook dispatch, event publishing)                │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  Automation Engine                                   │
 │  (recurring journals, bank matching rules,           │
 │   period-end automation, template execution)         │
 │                                                     │
 ├─────────────────────────────────────────────────────┤
 │                                                     │
 │  AI Financial Services (future)                      │
 │  (anomaly detection, forecasting,                    │
 │   natural language query, recommendations)           │
 │                                                     │
 └─────────────────────────────────────────────────────┘

                         |

              MyGrowNet Business Modules
              (StockFlow, GrowMarket, BMS, etc.)
```

Each engine is a domain service within `app/Domain/GrowFinance/Services/` that consumes repositories, enforces rules from the Accounting Rules Engine, and publishes events for downstream consumers. Engines are independently testable and swappable — the Workflow Engine, for example, could start as a simple approval flag and grow into a full BPMN-compliant workflow without affecting the Posting Engine.

**Key engine distinctions:**

| Engine | Responds to | Produces |
|---|---|---|
| Accounting Rules Engine | Configuration changes, new business apps | Account mapping, posting templates |
| Transaction Processing Engine | Financial events (sale, purchase, etc.) | Validated, balanced journals |
| Posting Engine | Validated journals | Posted entries, updated balances |
| Revenue Recognition Engine | Time triggers, contract milestones | Deferred revenue schedules, recognition journals |
| Banking Engine | Bank statements, API feeds | Matched transactions, reconciliation status |
| Workflow Engine | Approval requests, period events | Approval decisions, notifications |

**Engine implementation tiers** (not all engines are built in Phase 1):

| Tier | Engines | Phase |
|---|---|---|
| **Core** | General Ledger Engine, Posting Engine, Transaction Processing Engine, Reporting Engine | Phase 1 |
| **Expansion** | Tax Engine, Banking Engine, Budget Engine, Workflow Engine, Fixed Asset Engine | Phase 2-3 |
| **Advanced** | Revenue Recognition Engine, AI Financial Services, Forecasting, Anomaly Detection | Phase 4-5 |

This classification prevents the engineering team from attempting to build everything at once. The document describes the complete vision — the roadmap defines what to build first.

---

## 4. Core Accounting Model

### 4.1 Chart of Accounts

The chart of accounts is the structural foundation of every GrowFinance organization.

**Capabilities:**
- Configurable account hierarchy (parent-child, multi-level)
- Flexible account numbering (customizable prefixes and lengths)
- Account classes: Asset, Liability, Equity, Revenue, Expense
- Financial statement mapping (each account maps to a statement line)
- Organization templates (seed charts for different business types: retail, service, manufacturing, NGO)
- Account visibility control (active, inactive, hidden)

**Example hierarchy:**

```
1000 Assets
  1100 Current Assets
    1110 Cash
    1120 Bank Accounts
    1130 Accounts Receivable
    1140 Inventory
  1200 Non-Current Assets
    1210 Fixed Assets
    1220 Accumulated Depreciation

2000 Liabilities
  2100 Current Liabilities
    2110 Accounts Payable
    2120 VAT Payable
    2130 Withholding Tax Payable

3000 Equity
  3100 Share Capital
  3200 Retained Earnings

4000 Revenue
  4100 Sales Revenue
  4200 Service Revenue

5000 Expenses
  5100 Cost of Sales
  5200 Operating Expenses
```

### 4.2 Double-Entry Engine

The core of GrowFinance. Every financial transaction is recorded as a balanced journal entry.

**Requirements:**
- Immutable journals — once posted, entries are never edited or deleted
- Balanced entries — every journal must have equal debits and credits
- Period-aware — entries are scoped to an open accounting period
- Reversal entries — corrections create reversal entries referencing the original
- Adjustment entries — period-end adjustments (accruals, prepayments, depreciation)
- Audit history — every entry records user, timestamp, reason, and source event

**Schema principle:**

```
Journal
  id, organization_id, journal_number, date, description,
  period_id, source_event_id, status (draft/posted/reversed),
  reversal_of_id, created_by, created_at, approved_by, approved_at

JournalLine
  id, journal_id, account_id, description, debit_amount,
  credit_amount, cost_centre_id, branch_id, department_id,
  project_id
```

**Example: Sale on credit**

```
Journal #J-2026-0001
  Date: 2026-07-26
  Description: Sale of 5 units Widget A to Customer X
  Source: stockflow.sale.completed.v1 (evt_01J7XYZ)
  Status: posted

Lines:
  Debit:  Accounts Receivable (1130)        30,000
  Credit: Sales Revenue (4100)              30,000

  Debit:  Cost of Sales (5100)              12,500
  Credit: Inventory (1140)                  12,500
```

### 4.3 Accounting Periods

Fiscal periods control when transactions can be posted and how financial reporting is scoped.

**Hierarchy:**
```
Organization → Fiscal Year → Accounting Period
```

**Period states:**
- **Open** — new transactions and adjustments allowed
- **Closing** — system performing period-end procedures
- **Closed** — only reversal entries allowed (posts to current open period)
- **Locked** — no entries of any kind; audit-only access

**Rules:**
- Periods cannot be closed if there are unposted journals
- Reversals of closed-period entries post to the current open period
- Reversal must reference the original journal for audit trail
- Period-end adjustments post to the closing period before closure

---

## 5. Accounting Domain Model

Before defining enterprise modules, the core accounting domain objects and their relationships must be established. This model is the foundation that every feature in subsequent sections builds upon.

### 5.1 Entity Hierarchy

```
Organization
    │
    ├── owns one or more Charts of Accounts
    ├── uses one or more Currencies
    │
    ├── Fiscal Year
    │   │
    │   └── Accounting Period (12 monthly periods per year)
    │       │
    │       ├── Journal (header: date, description, source, status)
    │       │   │
    │       │   └── Journal Line (detail: account, debit, credit, dimensions)
    │       │
    │       └── Account Balance (running balance per account for this period)
    │
    ├── Chart of Accounts
    │   │
    │   ├── Account Group (parent: Assets, Liabilities, Equity, Revenue, Expenses)
    │   │   │
    │   │   └── Account (leaf: 1110 Cash, 1130 AR, 4100 Sales Revenue)
    │   │
    │   └── Account Type (control: Asset, Liability, Equity, Revenue, Expense)
    │
    ├── Customer Financial Profile / Supplier Financial Profile (credit limit, payment terms, aging — not operational customer management)
    │
    ├── Tax Rate (VAT rate, WHT rate)
    │
    └── Cost Centre (accounting dimension — owned by GrowFinance). Operational dimensions (branch, department, project) are referenced by ID only, stored in source business applications.
```

### 5.2 Entity Definitions

| Entity | Description | Key Attributes |
|---|---|---|
| **Organization** | The legal entity or business unit that owns financial data | id, name, currency, fiscal_year_start, timezone |
| **Fiscal Year** | A 12-month reporting period | id, organization_id, name, start_date, end_date, status |
| **Accounting Period** | A subdivision of a fiscal year (typically monthly) | id, fiscal_year_id, period_number, start_date, end_date, status (open/closed/locked) |
| **Chart of Accounts** | The complete list of accounts for an organization | id, organization_id, name, is_template |
| **Account** | A single line item in the chart of accounts | id, chart_id, parent_id, code, name, type, normal_balance, is_active |
| **Journal** | A financial transaction header | id, organization_id, period_id, journal_number, date, description, source_event_id, status (draft/posted/reversed) |
| **Journal Line** | A single debit or credit within a journal | id, journal_id, account_id, description, debit_amount, credit_amount, dimension_ids |
| **Account Balance** | Cached running balance for an account in a period | id, account_id, period_id, opening_balance, debit_total, credit_total, closing_balance |
| **Currency** | Monetary unit reference (owned by Financial Services Core — GrowFinance reads only) | code, name, symbol, decimal_places |
| **Tax Rate** | A tax percentage with accounting treatment | id, organization_id, name, rate, account_code, region |
| **Dimension (Cost Centre)** | An accounting dimension for reporting cost allocation | id, organization_id, code, name, parent_id |

### 5.3 Entity Relationship Diagram (Relational)

```
organizations ──────< fiscal_years ──────< accounting_periods
     │                                        │
     │                                   journals ──────< journal_lines
     │                                        │               │
     │                                   account_balances    accounts ──────< account_hierarchy
     │                                                            │
     └──────────────────────────────────────────────────────< charts_of_accounts
                                                                       │
                                                                  account_types

organizations ──────< tax_rates

organizations ──────< dimensions

organizations ──────< customers

organizations ──────< suppliers
```

### 5.4 Key Domain Invariants

These rules must hold at the domain layer — enforced by entities and services, not only the database.

1. **Every journal must balance.** `SUM(debits) = SUM(credits)` per journal. Enforced in `Journal::addLine()` and `Journal::post()`.
2. **Every journal belongs to an open period.** A journal cannot be posted to a closed or locked period. Checked before posting.
3. **Every journal line references an active account.** Inactive accounts cannot receive postings.
4. **Account balances are maintained transactionally, derived from journal_lines.** The `account_balances` table is updated atomically within every journal posting transaction. The general ledger (`journal_lines`) is the source of truth; `account_balances` is a materialized projection kept in sync by the Posting Engine. Balances can always be rebuilt from `journal_lines` if the cache degrades.
5. **Reversal entries reference their original.** A reversal journal has `reversal_of_id` pointing to the original journal. The original's `status` changes to `reversed` but its lines are never modified.
6. **No cascade deletes on financial data.** Journal lines are never deleted. If a journal was created in error, it is reversed, not removed.
7. **Every journal has a source.** Either a source event ID (for event-driven journals) or a user ID + manual entry reference.

**Design decision — full-journal reversal only:** GrowFinance does not support partial-line correction. If a single line in a 5-line journal has an incorrect amount, the entire journal must be reversed and a new journal posted with the correct amounts. This design choice avoids the complexity and audit ambiguity of partial corrections (which create journals that are neither original nor new). The workflow is:
- Reverse original journal (creates mirror-image reversal, both sides cancel)
- Post new journal with correct amounts on all lines

This is a deliberate simplification. It is not an oversight. Teams building integrations should plan for full reversal + re-entry on any correction.

### 5.5 Dimension Mapping

A journal line can carry optional dimension references for multi-dimensional reporting:

```
journal_line
  ├── account_id (required — which account is affected)
  ├── cost_centre_id (optional — who is responsible)
  ├── branch_id (optional — where the transaction occurred)
  ├── department_id (optional — which department)
  └── project_id (optional — which project or grant)
```

Dimensions are validated by reference only — GrowFinance does not create operational dimensions (per the Financial Platform Architecture §6). Dimension IDs are provided in the event payload or entered manually.

### 5.6 Multi-Currency Accounting

GrowFinance supports multi-currency at the journal level: every journal is denominated in the organization's base currency, but journal lines can carry a foreign currency amount and exchange rate for traceability.

**Rules:**

1. **Journals are always denominated in the organization's base currency.** The `debit_amount` and `credit_amount` columns in `journal_lines` always store base-currency values. Foreign currency amounts are stored in optional `fx_amount` and `fx_rate` columns for audit reference.

2. **Exchange rates come from Financial Services Core.** GrowFinance never fetches or manages exchange rates directly. Every journal that involves a foreign currency records the exchange rate used (sourced from `CurrencyService::getRate()`) to allow audit traceability.

3. **Realized FX gain/loss is automatic on settlement.** When a foreign-currency receivable is paid at a different exchange rate than when it was recorded, the difference is posted to a realized FX gain/loss account.

4. **Unrealized FX gain/loss is computed at period end.** For open foreign-currency balances (unpaid invoices in a foreign currency), GrowFinance computes the revaluation at the period-end rate and posts an unrealized FX gain/loss journal.

5. **A rounding difference account is required.** Every organization must configure a rounding difference account (typically within equity or an expense category). When exchange rate computations produce fractional cent differences, the residual posts here.

**Journal example — foreign currency sale and settlement:**

```
Day 1: Sale of $1,000 USD to customer (organization base = ZMW, rate = 25.0)

  Dr Accounts Receivable (ZMW 25,000)  [fx_amount: 1000, fx_rate: 25.0]
  Cr Revenue (ZMW 25,000)              [fx_amount: 1000, fx_rate: 25.0]

Day 90: Customer pays $1,000 USD (rate = 26.0)

  Dr Cash (ZMW 26,000)                 [fx_amount: 1000, fx_rate: 26.0]
  Cr Accounts Receivable (ZMW 25,000)  [fx_amount: 1000, fx_rate: 25.0]
  Cr Realized FX Gain (ZMW 1,000)

The realized FX gain of ZMW 1,000 is the difference between the settlement rate
and the original booking rate.
```

**Journal example — period-end unrealized FX revaluation:**

```
Period end: Open USD receivable of $1,000 booked at 25.0, period-end rate = 26.5

  Dr Unrealized FX Loss (ZMW 1,500)
  Cr Unrealized FX Reserve (ZMW 1,500)

  ($1,000 × 1.5 rate movement = ZMW 1,500 unrealized loss)

Next period: If USD receivable settles at 26.0:

  Dr Cash (ZMW 26,000)
  Dr Realized FX Loss (ZMW 500)
  Cr Accounts Receivable (ZMW 25,000)
  Cr Unrealized FX Reserve (ZMW 1,500)

  The unrealized reserve is reversed, and the net realized loss of ZMW 500 is booked.
```

**Key decision:** Multi-currency is a core capability (not deferred like consolidation), but the initial Phase 1 implementation supports single-currency journals only. Multi-currency journal creation and FX gain/loss posting are added in Phase 2 once the single-currency accounting engine is stable and organizations with foreign currency needs exist.

---

## 6. Enterprise Accounting Rules

This section consolidates the accounting rules that govern GrowFinance's behaviour. Every rule here must be enforced either in the domain layer (entities/services) or the accounting rules engine.

### 6.1 Journal Posting Rules

A journal entry must satisfy all of the following before it can be posted:

| # | Rule | Enforcement |
|---|---|---|
| 1 | Total debits must equal total credits (within rounding tolerance) | `Journal::post()` validates balance |
| 2 | All referenced accounts must exist and be active | `JournalLine::setAccount()` validates |
| 3 | The journal must belong to an open accounting period | `Journal::post()` checks period status |
| 4 | All line amounts must be positive (debit or credit, not both) | `JournalLine` constructor enforces |
| 5 | The journal must have at least two lines | `Journal::post()` checks count >= 2 |
| 6 | No line may reference the same account for both debit and credit within the same journal | Business rule validation |
| 7 | The journal date must fall within the accounting period's date range | `Journal::setDate()` validates |
| 8 | The journal currency must match the organization's base currency or have a valid exchange rate | `Journal::post()` validates via CurrencyService |

### 6.2 Period Closing Rules

Period closing is a controlled process that transitions an accounting period from open to closed.

**Pre-close checklist (enforced by `AccountingPeriod::close()`):**

- [ ] All journals in the period are posted (none in draft)
- [ ] All bank reconciliations for the period are completed
- [ ] All fixed asset depreciation for the period is posted
- [ ] All recurring journals for the period have been generated and posted
- [ ] All period-end adjustments (accruals, prepayments) are posted
- [ ] Tax liabilities for the period have been computed and booked
- [ ] Account balances have been snapshotted for the period

**Post-close behaviour:**

| Action | Open Period | Closed Period | Locked Period |
|---|---|---|---|
| Post new journal | ✓ | Only reversal entries | ✗ |
| Post reversal entry | ✓ | ✓ (posts to current open, references closed period original) | ✗ |
| Modify account balance snapshot | ✗ | ✗ | ✗ |
| Reopen period | N/A | Requires Finance Manager permission, records audit log | Must be unlocked first |
| View reports | ✓ | ✓ | ✓ |
| Modify chart of accounts | ✓ | ✓ (but balance accounts must be migrated) | ✗ |

**Reopening rules:**
- Reopening a closed period creates an audit event
- All journals posted after reopening are tagged as "late entries"
- The period must be re-closed after corrections are complete
- A reopened period cannot be closed again within 24 hours (cool-down to prevent accidental flip-flopping)

### 6.3 Multi-Dimensional Accounting Rules

Dimensions enable reporting across organizational structures beyond the chart of accounts.

**Rules:**
- **Dimensions are additive, not restrictive.** A journal line can carry zero, one, or multiple dimensions. No dimension is required unless the organization's configuration mandates it.
- **Dimensions do not affect the double-entry balance.** They are annotations for reporting. The debit/credit amounts are unaffected by which dimensions are tagged.
- **Dimension validation by reference only.** GrowFinance stores dimension IDs as opaque references. It does not validate that the ID exists in the source application. If a dimension is deleted in the source, reports show "Unknown" for that dimension ID.
- **All standard reports can be filtered by any dimension.** The trial balance, P&L, and balance sheet must support dimension-driven drill-down.
- **Cost centres are accounting dimensions.** Unlike branches (operational), cost centres are defined and managed within GrowFinance. See Financial Platform Architecture §6 for the ownership split.

**Example multi-dimensional query:**

```
SELECT
  cost_centre_id,
  department_id,
  SUM(debit_amount) as total_expenses
FROM journal_lines
WHERE account_id IN (SELECT id FROM accounts WHERE type = 'expense')
  AND period_id = 42
GROUP BY cost_centre_id, department_id
```

### 6.4 Account Balance Rules

| # | Rule | Rationale |
|---|---|---|
| 1 | The general ledger is the source of truth for all balances | Account balance tables are materialized projections, rebuildable from journal_lines |
| 2 | Account balances are updated transactionally on every journal post | The Posting Engine updates `account_balances` within the same DB transaction as the journal INSERT. This ensures real-time balance queries never require a full scan of `journal_lines`. |
| 3 | Opening balance for period N = closing balance for period N-1 | Ensures continuity across periods |
| 4 | Closing balance = opening balance + debit total - credit total (for asset/expense accounts) | Standard accounting formula for debit-normal accounts |
| 5 | Closing balance = opening balance - debit total + credit total (for liability/equity/revenue accounts) | Standard accounting formula for credit-normal accounts |
| 6 | Balance snapshots are taken at period closure | The current `account_balances` for the period is copied to the snapshot. This gives auditors a fixed reference point. |

**Reporting query path (for clarity):**

```
Standard report (trial balance, P&L, balance sheet)
    → Reads from account_balances (sub-second, transactionally current)

Drill-down report (click P&L line to see detail)
    → Reads from journal_lines filtered by account + period

Period comparison (current vs prior period)
    → Reads from balance snapshots for closed periods
    → Reads from account_balances for the current (open) period

Audit rebuild (verify balances)
    → Scans journal_lines, recomputes, compares to account_balances
```

This gives engineers a clear answer: real-time dashboards read from the transactionally-maintained balance cache. Detailed analysis reads from `journal_lines`. Both are correct because they are kept in sync by the Posting Engine.

### 6.5 Audit Trail Rules

| # | Rule | Enforced By |
|---|---|---|
| 1 | Every journal creation records user_id, timestamp, and source | `Journal::create()` |
| 2 | Every journal posting records the posting timestamp and user | `Journal::post()` |
| 3 | Every reversal records the original journal ID, reason, and approving user | `Journal::reverse()` |
| 4 | Period status changes record who changed it and why | `AccountingPeriod::close()`, `::reopen()` |
| 5 | Chart of accounts changes record the before and after state | `Account::update()` via audit observer |
| 6 | No data is ever physically deleted from financial tables | Application-level enforcement in the Journal Aggregate + append-only database permissions for application user |

### 6.6 Concurrency & Locking Model

Real-time collaboration (multiple users posting simultaneously) and period closing can race if not explicitly guarded.

**Sequence of operations within a single journal posting transaction:**

```
BEGIN TRANSACTION
  1. SELECT account_balances FOR UPDATE (row-level lock on affected accounts, ordered by account_id ASC)
  2. Validate period is open (read with NOWAIT to detect concurrent close)
  3. Validate all accounts are active
  4. INSERT into journal_lines
  5. UPDATE account_balances (increment/debit totals)
COMMIT
```

**Critical: locks must always be acquired in a canonical order** (`ORDER BY account_id ASC`). Without this, two concurrent journals touching the same set of accounts in opposite order (e.g., Journal A locks [AR, Revenue] while Journal B locks [Revenue, AR]) create a classic deadlock — each holds a lock the other needs. `ORDER BY account_id ASC` ensures every transaction requests locks in the same sequence, eliminating the deadlock class entirely.

**Race conditions and guards:**

| Scenario | Guard | Mechanism |
|---|---|---|
| Two journals post to the same account simultaneously | Row-level locking on `account_balances` | `SELECT ... FOR UPDATE` on the affected account balance rows within the transaction. Second poster waits for first to commit. |
| Period is being closed while a journal is mid-post | Period status validation with `NOWAIT` | The period status row is locked at the start of the transaction. If period closure acquires the lock first, the journal post fails and retries. |
| Account balance cache diverges from journal_lines (rare) | Periodic reconciliation check | A nightly `platform:reconcile-balances` command scans all open periods (where data is actively changing) and the most recently closed period, recomputes balances from `journal_lines`, and alerts on any mismatch. Open periods are reconciled nightly because a corruption in the current period goes undetected until close if only closed periods are checked. |
| Long-running report reads stale balances | Read from balance snapshots or read replica | Reports that can tolerate staleness read from the last snapshot or a read replica, avoiding read locks on the primary `account_balances` table. |
| Customer/supplier balance races | Same lock-and-update-in-transaction pattern as account_balances | `gf_customer_balances` and `gf_supplier_balances` are materialized projections in the same class as `account_balances`. When an AR or AP journal posts, the affected customer/supplier balance row is locked (`SELECT ... FOR UPDATE ORDER BY customer_id ASC`) and updated within the same transaction. |

**Design decision:** GrowFinance uses pessimistic locking (row-level) rather than optimistic concurrency for journal posting because:
1. The contention window is short (single INSERT + single UPDATE per account)
2. The cost of a balance-corruption incident is higher than the cost of a brief lock wait
3. Accounting periods naturally batch work (period-end close), making optimistic retry more disruptive than predictable queueing

**Period close concurrency:**

```
Period close initiates:
  1. Lock the period status row (prevents new journal posts)
  2. Wait for all in-flight transactions to complete (short timeout)
  3. Snapshot account_balances
  4. Update period status to closed
  5. Release lock
```

If any in-flight journal post is still running past the timeout, the close fails and must be retried. This prevents partial closes where some journals are included and others are not.

**Known scaling limit — control account hot rows:**

Under the pessimistic-locking design, every sale journal debits AR (or Cash). At high transaction volumes (e.g., StockFlow pushing a burst of sale events), every concurrent sale serializes on the AR control account's balance row. This means the "real-time collaboration, multiple users posting simultaneously" claim from §2.5 has a practical ceiling.

This is not a Phase 1 issue — for the initial transaction volumes of a single organization, row-level contention on control accounts is negligible. But it is a known scaling limit that the team should be aware of before designing highly concurrent posting workflows.

**Eventual solution (Phase 3+):** Periodic micro-batched balance updates — rather than locking and updating the control account on every individual journal post, batch increments within a short time window and apply them atomically. This replaces per-transaction row locks with a single batch update per control account per window. The journal_lines INSERT remains per-event (no batching); only the balance projection is deferred.

## 7. Migration Architecture

GrowFinance will need to accept data from external systems — especially Pastel, which is the primary competitive target. This section defines the architecture for importing financial data without compromising data integrity.

### 7.1 Import Sources

| Source | Format | Priority | Complexity |
|---|---|---|---|
| Pastel Partner | CSV (standard export), direct DB | High | High |
| Pastel Evolution | CSV, API | High | Medium |
| Excel / Spreadsheets | XLSX, CSV | Medium | Low |
| QuickBooks | QBXML, CSV export | Medium | Medium |
| Sage | CSV, PDF (extraction) | Medium | High |
| Bank statements | CSV, MT940, OFX | High | Medium |
| Opening balances | CSV, manual entry | High | Low |

### 7.2 Import Architecture

```
Source Data (CSV, Excel, MT940)
    │
    ▼
Import Adapter (parser + validator)
    │
    ├── Column mapping (user-configurable or template-based)
    ├── Data validation (dates, amounts, account codes)
    └── Error reporting (which rows failed and why)
    │
    ▼
Import Preview (user reviews before committing)
    │
    ├── Shows: rows to import, accounts to create, balances to set
    ├── Detects: duplicates, mismatches, missing accounts
    └── User can: fix mappings, skip rows, cancel
    │
    ▼
Import Execution (within a database transaction)
    │
    ├── Creates chart of accounts (if mapping creates new accounts)
    ├── Posts journals (dated to maintain historical integrity)
    ├── Tags all entries with import source and batch ID
    └── On failure: entire batch rolls back, no partial imports
```

### 7.3 Pastel Migration Flow

The most critical migration path. Pastel holds years of historical data that must transfer accurately.

**Step 1: Chart of Accounts Mapping**

```
Pastel Account Code    →    GrowFinance Account
──────────────────────       ────────────────────
1000/001 (Assets)            Assets group
1100/001 (Bank)              1120 Bank Account
1200/001 (Debtors)            1130 Accounts Receivable
```

The user maps Pastel account codes to GrowFinance accounts using a configuration interface. Template mappings are provided for common Pastel chart layouts.

**Step 2: Opening Balances Import**

```
Date: Start of current fiscal year (e.g., 1 January 2026)

Account         | Debit    | Credit
─────────────────┼──────────┼─────────
Bank            | 150,000  |
AR              | 80,000   |
AP              |          | 45,000
Equipment       | 200,000  |
Accum Depr      |          | 60,000
Share Capital   |          | 300,000
Retained Earnings|         | 25,000
─────────────────┼──────────┼─────────
Total           | 430,000  | 430,000
```

Opening balances are imported as a single balancing journal that establishes the starting position. Every line references the import batch ID.

**Step 3: Historical Transactions (Optional)**

For organizations that need full history, Pastel transaction data is imported as posted journals with original dates. These are tagged as "historical import" and their event_source field records "pastel_migration:{batch_id}".

**Large dataset strategy:** Historical imports may include tens of thousands of rows. To avoid monolithic transactions (which risk full rollback on a single row failure late in the batch), large imports are processed in batches of 500 rows per transaction:
- Each batch is a separate database transaction
- Batches are sequential (batch N+1 depends on batch N completing for correct period balances)
- Failed batches are retried up to 3 times
- Unrecoverable rows are logged to an import exception table for manual review (not rolled back)
- A summary report shows: total rows, imported, skipped, failed
- The entire import can be rolled back via a single "undo" action that posts reversal journals for all imported batches, tagged with the same batch ID

**Step 4: Verification Report**

After import, the system generates a comparison report:

```
Verification Report — Pastel → GrowFinance Migration
                Pastel Balance    GrowFinance Balance    Difference
─────────────────┼─────────────────┼──────────────────────┼────────────
Bank            | 150,000         | 150,000              | 0
AR              | 80,000          | 80,000               | 0
AP              | 45,000          | 45,000               | 0
Equipment       | 200,000         | 200,000              | 0
Accum Depr      | 60,000          | 60,000               | 0
Share Capital   | 300,000         | 300,000              | 0
Retained Earnings| 25,000         | 25,000               | 0
─────────────────┼─────────────────┼──────────────────────┼────────────
                |                 |                       | ZERO
```

If differences exist, the import is rejected and must be corrected before retrying.

### 7.4 Import Rules (Enforced)

| # | Rule | Rationale |
|---|---|---|
| 1 | Every import creates a balancing journal | Prevents orphan entries |
| 2 | Every import has a unique batch ID (UUID) | Enables rollback and audit |
| 3 | No import can modify or delete existing journals | Source events are immutable |
| 4 | Duplicate detection before import | Same Pastel export imported twice should produce zero new entries |
| 5 | Account codes not in the chart are either mapped or rejected | No phantom accounts |
| 6 | Historical imports preserve original transaction dates | Maintains audit chronology |
| 7 | All imports are reversible via a single reversal batch entry | Facilitates correction |

### 7.5 Idempotency for Import

Import batches use the same idempotency mechanism as financial events. The import batch ID is stored in `processed_events` before execution. If the same batch is submitted twice, the second attempt is silently rejected.

---

## 8. Enterprise Modules

**Target:** Day-to-day financial management capabilities built on the core accounting model.

### 8.1 General Ledger

The central hub. All transactions flow through the general ledger before appearing in financial statements.

**Capabilities:**
- Journal entry creation (manual, template-based, event-driven)
- Journal approval workflow (optional, configurable per org)
- Account balance inquiry (real-time, as-of-date)
- General ledger report (transaction detail per account)
- Trial balance (with period comparison)
- Account activity drill-down

### 8.2 Accounts Receivable

Operational AR (customer collections, reminders, credit control) belongs to business applications per the Financial Platform Architecture. GrowFinance owns the **financial truth** — the receivable balance and aging on the balance sheet.

**GrowFinance AR capabilities:**
- Customer balance summary (total outstanding per customer, for financial statements)
- Aging analysis (current, 30, 60, 90+ days, for balance sheet presentation)
- Receivable turnover metrics (for financial reporting)
- Payment history against invoices (for audit trail)

**Operational AR** (collections, credit control, dunning, reminder scheduling, risk analysis) is owned by business applications per the Financial Platform Architecture §4.4. GrowFinance provides the financial aging data; business apps drive the operational workflow.

**The boundary in practice:**

| Who | What |
|---|---|
| Business application | Creates invoice, sends reminder, records payment |
| GrowFinance | Records the accounting entry: dr Receivable, cr Revenue |
| Business application | Updates customer credit limit |
| GrowFinance | Reflects the balance sheet impact |
| Business application | Sends dunning letters |
| GrowFinance | Reports aging on financial statements |

### 8.3 Accounts Payable

Same operational vs financial split as AR.

**GrowFinance AP capabilities:**
- Supplier balance summary
- Aging analysis (payables due)
- Payment obligation tracking
- Supplier statement reconciliation
- Payable turnover metrics

### 8.4 Fixed Assets

**Features:**
- Asset register (acquisition date, cost, useful life, residual value)
- Depreciation methods (straight-line, declining balance, sum-of-years-digits, units of production)
- Asset categorization (buildings, equipment, vehicles, furniture, IT)
- Asset disposals (gain/loss calculation)
- Impairment tracking
- Depreciation schedule (monthly/quarterly/annual)
- Asset history (acquisition, transfers, disposals, revaluations)

**Depreciation journal flow:**

```
Period-end:
  Dr Depreciation Expense (5200)
  Cr Accumulated Depreciation (1220)

On disposal:
  Dr Accumulated Depreciation
  Dr Cash / Receivable (if sold)
  Cr Fixed Asset (cost)
  Cr Gain on Disposal (if proceeds exceed NBV)
  -- or --
  Dr Loss on Disposal (if NBV exceeds proceeds)
```

### 8.5 Tax Engine

Tax compliance is one of the highest-value features for competing with Pastel in African markets.

**Supported taxes (Phase 1 — Zambia):**
- VAT (Standard 16%, reduced rates, zero-rated, exempt)
- Withholding tax (on services, rent, dividends, interest, management fees)
- PAYE integration point (payroll tax data consumption)

**VAT-specific capabilities (Zambia focus):**
- VAT categories: standard (16%), reduced (if applicable), zero-rated (exports), exempt (medicines, education, financial services)
- Tax invoice requirements per ZRA rules (TPIN, invoice number, date, description, VAT exclusive amount, VAT amount, VAT inclusive total)
- Input VAT recovery tracking (recoverable vs non-recoverable per ZRA categories)
- Output VAT computation per tax period (monthly / quarterly)
- VAT return generation matching ZRA format (return fields, schedules, attachments)
- Electronic invoicing readiness — architecture for ZRA e-invoicing integration (API submission, QR code generation, real-time validation) when mandated
- VAT audit trail: every transaction records its VAT category and rate for auditor review

**Withholding tax:**
- WHT on services (15% for non-residents, varies for residents)
- WHT on rent (15% commercial, 10% residential)
- WHT on dividends (15% for residents)
- WHT on interest (15% for residents)
- WHT certificate generation (per supplier, per period)
- WHT return schedule for ZRA filing

**ZRA integration readiness:**
- The architecture supports future integration with ZRA systems (SmartInvoice, ASYCUDA for customs data)
- No real-time integration in Phase 1 — return generation and manual submission
- Phase 3+: API submission of VAT returns, e-invoicing validation gateway

**Tax features:**
- Tax rate configuration per jurisdiction
- Product-to-tax-category mapping consumed from business applications (business apps determine tax category per product; GrowFinance references it)
- Customer exemption status consumed from business applications (business apps own exemption data — GrowFinance reads it for tax computation)
- Input VAT tracking (recoverable vs non-recoverable)
- Output VAT computation per period
- Tax return generation (Zambia VAT and WHT formats)
- Withholding tax certificate generation
- Tax audit trail (every tax-impacting transaction is tagged)

**Tax data model:**

```
tax_rates
  id, organization_id, name, rate, account_code,
  region, is_active, effective_from, effective_to

TaxImpact (on journal lines)
  journal_line_id, tax_rate_id, tax_amount, is_input_vat,
  vat_category, exemption_ref

-- Tax categories and customer exemptions are owned by business applications.
-- GrowFinance references tax_category_id and exemption_status from source events.
-- No separate gf_tax_categories or gf_tax_exemptions table in GrowFinance.
```

### 8.6 Revenue Recognition

Revenue recognition handles the timing difference between when cash is received and when revenue is earned — critical for subscriptions, retainers, and long-term contracts.

**Capabilities:**
- Deferred revenue scheduling (recognize over time on a straight-line or usage basis)
- Accrued revenue (recognize revenue before invoicing for milestone-based contracts)
- Subscription accounting (monthly recognition from prepaid annual subscriptions)
- Contract-based revenue allocation (ASC 606 / IFRS 15 performance obligations)
- Revenue schedule modification (contract amendments, early cancellations)
- Automated recognition journals (generated by the Revenue Recognition Engine on schedule)

**Example: Annual subscription of K12,000**

```
Payment received (January):
  Dr Cash                    12,000
  Cr Deferred Revenue        12,000

Each month (Jan–Dec):
  Dr Deferred Revenue         1,000
  Cr Revenue                  1,000
```

**Integration:**
- Platform Billing publishes `platform.billing.subscription.renewed.v1`
- Revenue Recognition Engine creates the deferred revenue schedule
- The schedule drives monthly automatic journals

### 8.7 Banking & Reconciliation

Banking automation is a key competitive advantage against desktop accounting software.

**Capabilities:**
- Bank feed connections (API-based, auto-refresh)
- Statement import (CSV, MT940, OFX, PDF extraction)
- Auto-matching rules (amount, reference, date, counterparty)
- Manual matching interface (drag-and-drop)
- Reconciliation status dashboard (reconciled, unreconciled, in-progress)
- Duplicate transaction detection
- Cash position dashboard (real-time balance across all accounts)
- Bank rules engine (user-defined rules: "if reference contains 'INV' → match to invoice")

**Reconciliation flow:**

```
Import bank statement
      │
      ├── Auto-match: amount + reference match → reconciled
      ├── Suggested match: close matches → user confirms
      └── Unmatched: user creates journal or flags for investigation
      │
      ▼
Reconciliation report: opening balance + cleared transactions + differences = closing balance
```

### 8.8 Cost Accounting

This is a key differentiator against Pastel, which has limited cost accounting capabilities.

**Features:**
- Cost centre definitions (owned by GrowFinance per the Financial Platform Architecture)
- Department-level profitability
- Project cost tracking
- Activity-based costing framework
- Budget vs actual comparison by cost centre
- Contribution margin analysis

### 8.9 Reporting & Business Intelligence

Reporting is what transforms accounting data into business decisions. GrowFinance must match Pastel's standard reports and go beyond with self-service BI.

**Standard financial reports (Pastel baseline):**
- Trial Balance (summary and detailed)
- Profit & Loss (current period, YTD, budget comparison)
- Balance Sheet (current period, comparative)
- Cash Flow Statement (direct and indirect method)
- General Ledger Detail (account activity drill-down)
- Aged Receivables / Payables (summary and detailed)
- VAT Return (Zambia format)
- Withholding Tax Certificate

**Differentiator reports:**
- Multi-dimensional P&L (filter by cost centre, department, project, product line)
- Cash Position Dashboard (real-time bank balances + pending transactions)
- Revenue Recognition Schedule (deferred vs earned revenue by period)
- Reconciliation Status Dashboard (reconciled vs unreconciled transactions across bank accounts)
- Custom Report Builder (drag-and-drop dimensions, measures, filters)
- Report Scheduling & Auto-Delivery (email, Slack, webhook)
- Export to Excel/PDF/CSV with company branding

**BI capabilities (competitive moat against desktop accounting):**
- Self-service drill-down: click any P&L line to see underlying journals
- Period-over-period comparison with variance percentage
- Budget vs actual with variance flags (green/amber/red thresholds)
- Trend charts embedded in reports (12-month revenue trend on the P&L)
- Saved report configurations with parameterised filters
- Report versioning (compare two P&L snapshots from different dates)

**Technical architecture:**
- Standard reports (trial balance, P&L, balance sheet) read from `gf_account_balances` — the transactionally-maintained balance cache updated by the Posting Engine on every journal post. This gives sub-second response regardless of transaction volume.
- Drill-down reports (e.g., click a P&L line to see underlying journals) read from `gf_journal_lines` filtered by account and period.
- Balance snapshots are verification-only — used for audit comparisons and period-closure reference. Reports do not read from snapshots by default.
- Dimension filtering happens at query time via join on dimension columns against `journal_lines` (for drill-down) or against the dimension-tagged balance table (for multi-dimensional P&L).
- Report results are cached for 5 minutes (configurable) to avoid recomputation on repeated clicks.
- Large reports (>10,000 rows) stream paginated results rather than loading into memory.
- Report scheduling uses Laravel job queue with per-organization concurrency limit.

---

## 9. Financial Intelligence Layer

This is where GrowFinance differentiates from traditional accounting software. The intelligence layer sits above the accounting engine and consumes ledger data.

**Current (Phase 1-3):** Dashboards, analytics, and standard reports that are built alongside the core accounting engine. These are not AI — they are computed queries against journal data.

**Future (Phase 4-5):** AI-assisted capabilities that require transaction volume, historical data, and clean classifications to be effective. Building these before users exist would produce features without training data or validated use cases.

### 9.1 Financial Analytics

The analytics layer is a key competitive advantage against Pastel, which has minimal analytical capabilities beyond standard reports.

**Standard analytics:**
- Revenue trends (daily/weekly/monthly/period-over-period)
- Expense breakdown by category
- Profitability by branch, department, project, product line
- Cash flow patterns
- Working capital metrics

**Multi-dimensional drill-down:**
- Click a P&L line → see journals → see source events (traceability)
- Filter reports by any dimension (cost centre, department, branch, project)
- Compare periods with variance highlighting
- Exclude dimension values (e.g., P&L without Head Office overhead)

**Embedded BI widgets (in-app dashboard):**
- Cash position gauge (current balance vs 7-day forward projection)
- Revenue trend sparkline (12-month)
- Top 5 expense categories (pie chart)
- Aged receivables waterfall
- Budget burn rate (for project-based organisations)
- Quick ratio / current ratio tracker

**Export & share:**
- Scheduled PDF delivery to stakeholders
- One-click Excel export with formulas preserved
- Dashboard URL sharing (read-only, time-limited)
- White-label reports for accountants to send to their clients

**Data warehouse integration (Phase 5):**
- Connect GrowFinance to an external data warehouse (BigQuery, Redshift, ClickHouse)
- Export journal data via CDC (change data capture)
- Power BI / Tableau / Metabase connector (read-only database user)
- Custom SQL access for advanced analysts

### 9.2 AI Financial Assistant (future)

Natural language query interface for financial data:

- "Why did expenses increase this month?"
- "Which branch is reducing profitability?"
- "Predict cash shortage next quarter."
- "Show me all suppliers with increasing prices."

### 9.3 Anomaly Detection (future)

Automated detection of:

- Unusual expense patterns (duplicate invoices, above-threshold amounts)
- Abnormal journal entries (out-of-balance risk, unusual accounts used)
- Suspicious adjustments (period-end entries without adequate description)
- Payment anomalies (unexpected payment timing, amount deviations)

### 9.4 Financial Forecasting (future)

- Cash flow prediction (based on historical patterns and open receivables/payables)
- Revenue forecasts (trend-based, seasonal adjustment)
- Expense projections
- Scenario modelling (what-if analysis)
- Budget recommendation engine

---

## 10. Multi-Organization Architecture

GrowFinance must support complex organizational structures natively.

**Organization hierarchy:**

```
Group (holding company)
│
├── Company A (Zambia)
│   ├── Branch: Lusaka
│   ├── Branch: Ndola
│   └── Branch: Kitwe
│
├── Company B (Malawi)
│   ├── Branch: Lilongwe
│   └── Branch: Blantyre
│
└── Company C (Tanzania)
```

**Capabilities:**
- Each company has its own chart of accounts, fiscal year, and currency
- Each branch is a dimension for reporting (operational, owned by business apps)
- Intercompany transactions (Company A sells to Company B — automatically creates mirror entries)
- Consolidated reporting across all companies with currency translation
- Partial ownership consolidation (proportionate consolidation for joint ventures)
- Group-level eliminations (remove intercompany balances for group financial statements)

**Consolidation flow:**

```
Company A (ZMW)    Company B (MWK)    Company C (TZS)
      │                   │                  │
      │                   │                  │
      └───────────────────┼──────────────────┘
                          │
                    Currency translation
                    (via Financial Services Core)
                          │
                    Consolidated entries
                          │
                    Eliminations
                    (intercompany revenue, AR/AP, loans)
                          │
                    Group Trial Balance
                          │
                    Group Financial Statements
```

---

## 11. Industry Adaptation Layer

Rather than building industry-specific modules inside GrowFinance, GrowFinance acts as the financial engine for industry-specific applications. This leverages the MyGrowNet platform architecture where business applications publish events and GrowFinance creates the accounting records.

```
Hospital App          School App          Manufacturing App
    │                      │                     │
    │ financial events     │ financial events    │ financial events
    │                      │                     │
    └──────────────────────┼─────────────────────┘
                           │
                    GrowFinance
                    (accounting engine)
                           │
                    Financial statements
                    (per organization or consolidated)
```

**What GrowFinance provides per industry:**

| Industry | Accounting needs | GrowFinance handles |
|---|---|---|
| Healthcare | Patient billing, insurance claims, revenue cycle | Journal entries for billings, payments, adjustments. Receivable tracking. |
| Education | Tuition fees, payroll, grants | Revenue recognition over term. Grant accounting. Expense allocation. |
| Retail / Ecommerce | Sales, inventory, VAT | POS integration via events. Automated VAT booking. Cost of sales. |
| Manufacturing | Job costing, BOM, inventory valuation | WIP accounting. Variance analysis. Cost allocation. |
| NGO / Donor | Grant management, restricted funds | Fund accounting. Donor reporting. Budget vs actual. |

---

## 12. Reporting Architecture

### 12.1 Standard Financial Reports

| Report | Description |
|---|---|
| Trial Balance | All accounts with debit/credit balances as of a date |
| Income Statement (P&L) | Revenue and expenses over a period |
| Balance Sheet | Assets, liabilities, and equity as of a date |
| Cash Flow Statement | Operating, investing, financing cash flows |
| General Ledger | Transaction detail per account over a period |
| Accounts Receivable Aging | Outstanding customer balances by age bracket |
| Accounts Payable Aging | Outstanding supplier balances by age bracket |
| Fixed Asset Schedule | Asset register with cost, depreciation, NBV |
| Tax Report | VAT return summary, withholding tax schedule |

### 12.2 Advanced Reports

| Report | Description |
|---|---|
| Profitability Analysis | Revenue and cost per branch, department, project |
| Budget Variance | Actual vs budget with variance percentage |
| Financial Ratios | Liquidity, profitability, efficiency, solvency ratios |
| Trend Analysis | Period-over-period comparison of key metrics |
| Contribution Margin | Revenue minus variable costs per product line |

### 12.3 Report Delivery

- Export to PDF, CSV, XLSX
- Email scheduling (periodic reports delivered automatically)
- Dashboard widgets (real-time KPIs)
- Report templates (customizable per organization)
- Drill-down (click from statement to journal to source event)

---

## 13. Automation Engine

Automation is a key competitive advantage over traditional desktop accounting software.

**Capabilities:**

| Feature | Description |
|---|---|
| Recurring journals | Automatic creation of journals on a schedule (e.g., monthly depreciation) |
| Recurring invoices | Automatic invoice generation for subscriptions, retainers |
| Approval workflows | Configurable approval chains for journals, payments, adjustments |
| Payment reminders | Automated customer reminders based on aging rules |
| Bank reconciliation rules | Auto-match bank transactions to journal entries |
| Accounting templates | Predefined journal templates for common transactions |
| Period-end automation | Automated depreciation, accruals, prepayment amortization |

**Recurring journal example:**

```
Template: Monthly Depreciation
Frequency: Monthly on last day
Lines:
  Dr: Depreciation Expense (5200)
  Cr: Accumulated Depreciation (1220)
Amount: Calculated from fixed asset register
```

---

## 14. Compliance Architecture

Enterprise accounting requires robust compliance controls.

**Audit trail:**
- Every journal entry records: who created, who approved, when, from what source event
- Every reversal records the original journal reference
- No data deletion — entries are reversed, not removed
- Read-only audit user role for external auditors

**Segregation of duties:**
- Journal creator cannot approve their own entries
- Period closure requires separate authorization
- Payment approval separate from journal creation
- Configurable approval thresholds (amount-based)

**Period controls:**
- Only users with "close period" permission can close
- Closed periods prevent new entries
- Reversal of closed-period entries requires manager approval
- Period closure logs: who closed, when, checklist completed

**Three-way matching (purchase-to-pay):**
An enterprise control that ensures payment is only made when three documents agree:

```
Purchase Order
    │  (what was ordered)
    │
Goods Received Note
    │  (what was received)
    │
Supplier Invoice
    │  (what was billed)
    │
▼
Payment (only if all three match)
```

**Matching rules:**
- Quantity ordered ≤ quantity received ≤ quantity invoiced
- Unit price on PO matches unit price on invoice (within tolerance)
- Total amount matches within configured threshold
- Mismatches create exception flags for manual review
- Partial matches allowed (partial receipt, partial invoice)

**Regulatory reporting:**
- Tax return generation per jurisdiction format
- Auditor-ready export (standard format)
- Retention policy enforcement (minimum retention periods)
- Compliance report scheduling

### 14.1 Audit Locking & Snapshots

Enterprise accounting systems need to freeze financial data at the start of an external audit so that reports don't change while the auditor is reviewing them.

**Capability:**
When an external auditor starts a review (e.g., Q1 2026 audit), the system creates an audit snapshot:

```
Audit Snapshot #2026-Q1
  - Chart of accounts version
  - Journal state (all entries up to snapshot date)
  - Account balances
  - Report outputs (P&L, balance sheet, trial balance)
  - User permissions
  - Period statuses
```

**What the snapshot captures:**
- All journal lines posted before the snapshot timestamp
- The chart of accounts hierarchy as it existed at snapshot time
- Account balances computed from those journals
- A cryptographic hash of the snapshot for tamper evidence

**What continues after snapshot:**
- Normal operations continue in the current (future) period
- New journals post to the current period, not the frozen one
- The auditor reviews against the snapshot, which is immutable

**Use case:**
```
March 31: Period closed
April 15: Auditor starts Q1 review → snapshot created
April 15–30: Auditor reviews snapshot; company posts April transactions normally
May 1: Audit complete, adjustments posted to April, Q1 confirmed
```

The snapshot is a read-only view. It does not block operations.

---

## 15. Integration & API Platform

GrowFinance exposes its capabilities through multiple integration channels — events for internal platform modules, APIs for external systems, and webhooks for event-driven integrations.

### 15.1 Hybrid Event + Relational Architecture

GrowFinance uses a hybrid approach: events for processing, relational tables for storage. This avoids the complexity of full event sourcing while retaining the benefits of event-driven integration.

**Architecture:**

```
Business Transaction (StockFlow sale)
      │
      ▼
Financial Event (stockflow.sale.completed.v1)
      │
      ├── Consumed by Transaction Processing Engine
      │       │
      │       ├── Validates event payload
      │       ├── Resolves accounts via Accounting Rules Engine
      │       ├── Creates Journal entry (INSERT into gf_journals, gf_journal_lines)
      │       └── Updates account balances
      │
      ├── Journal stored relationally (gf_journals, gf_journal_lines)
      │       └── Source of truth for all reporting and queries
      │
      └── Event stored in outbox for downstream consumers (growfinance.journal.posted.v1)
```

**Rules:**
- Events drive **processing** — every journal originates from a financial event (or manual creation which generates an internal event)
- Relational tables are the **system of record** — all queries, reports, and balances read from `gf_` tables
- No event replay rebuilds the ledger — if repair is needed, reversal entries are used (see §19 Correction & Reversal Model)
- The event outbox guarantees delivery to downstream consumers; the relational store guarantees data integrity for GrowFinance itself
- This hybrid approach gives the best of both: event-driven integration without event sourcing complexity

### 15.2 Internal Integrations (via Platform Architecture)

| Module | Integration Pattern | Events Consumed |
|---|---|---|
| StockFlow | Event → Journal | `stockflow.sale.completed.v1`, `stockflow.purchase.received.v1`, `stockflow.stock.adjusted.v1` |
| GrowMarket | Event → Journal | `growmarket.order.fulfilled.v1`, `growmarket.order.refunded.v1` |
| BMS | Event → Journal | `bms.invoice.created.v1`, `bms.invoice.paid.v1`, `bms.expense.recorded.v1` |
| Platform Payments | Event → Settlement Journal | `platform.payment.settled.v1` |
| Platform Billing | Event → Revenue Journal | `platform.billing.invoice.issued.v1` |
| Financial Services Core | Contract → Currency Conversion | `CurrencyService::convert()` |

### 15.3 External Integrations

| System | Integration Method | Purpose |
|---|---|---|
| Banks | API / File import (CSV, MT940, OFX) | Bank reconciliation |
| Payroll systems | API / Events | Salary journal import |
| Tax authorities | Report export | VAT return submission |
| ERP systems | `AccountingProvider` contract | Integration via API Gateway |
| Third-party apps | Public REST API | Custom integrations |

### 15.4 API Capabilities

GrowFinance exposes capabilities through two channels:
- **`AccountingProvider` contract** — the platform integration contract for core accounting operations (`createJournalEntry()`, `createReversalEntry()`, `getAccountBalance()`, `getTrialBalance()`). Used by MyGrowNet modules and embedded third-party integrations.
- **Public REST API** — a richer HTTP interface covering the full capability set (journals, accounts, customers/suppliers financial profiles, reports, tax, periods, assets, budgets). Used for custom integrations and external systems that need broader access.

The `AccountingProvider` contract is the minimal stable interface. The REST API is the full surface area. External systems can use either, depending on their needs.

**API categories:**

| Category | Examples |
|---|---|
| Journals | Create, list, reverse, get by ID |
| Accounts | List chart of accounts, get balance, get activity |
| Customers | Get balance, get aging, list transactions |
| Suppliers | Get balance, get aging, list transactions |
| Reports | Trial balance, P&L, balance sheet, cash flow |
| Tax | Get rates, file return, get filing history |
| Periods | Get status, close, reopen |
| Fixed Assets | Register, depreciate, dispose |
| Budgets | Create, update, variance report |

**API design principles:**
- RESTful resource-oriented URLs (`/api/v1/organizations/{orgId}/journals`)
- Standard HTTP verbs (GET, POST, PATCH)
- **No DELETE on financial records** — journals are reversed via `POST /journals/{id}/reverse`, accounts are deactivated not deleted
- JSON request/response bodies
- Pagination, filtering, sorting on list endpoints
- API key authentication for external systems
- Rate limiting per organization (configurable thresholds)
- Webhook support for event notifications (journal.posted, payment.received, period.closed)
- Idempotency keys on mutation endpoints to prevent duplicate processing

**Webhook events** (naming consistent with the Financial Platform Architecture §7 — uses the `growfinance.` prefix and `.v1` version suffix):

```
growfinance.journal.posted.v1
growfinance.journal.reversed.v1
growfinance.account.balance.changed.v1
growfinance.period.closed.v1
growfinance.tax.return.filed.v1
growfinance.budget.updated.v1
growfinance.report.generated.v1
```

### 15.5 Integration Principles

1. All internal integrations use events (outbox → queue → inbox) for guaranteed delivery
2. External integrations use the REST API or `AccountingProvider` contract, never direct database access
3. Imported data is journaled with source reference for full traceability
4. Failed integrations go to dead letter queue with alerting
5. API responses include idempotency keys for all mutation endpoints

**Backpressure and event pipeline failure handling:**

| Scenario | Behaviour | Impact on Reporting |
|---|---|---|
| Transaction Processing Engine down (< 1 hour) | Events queue in the inbox. When the engine resumes, it processes from oldest to newest. | Reports may lag by up to 1 hour. Balances reflect the last processed event. Users see "last updated: HH:MM" on dashboards. |
| Transaction Processing Engine down (> 1 hour) | Alert triggers. Operations team notified. | Same as above — no data loss. Events are durable in the outbox/inbox. |
| Event processing fails permanently (bad data) | The event is moved to the dead letter queue after max retries. A placeholder journal with status "failed" is created in `gf_financial_events`. | Reports are incomplete until the failed event is resolved. The failed event is visible on the integration dashboard with error details. |
| Event consumer backlog (StockFlow producing faster than GrowFinance can consume) | The inbox acts as a buffer. If the backlog exceeds 10,000 events, backpressure signals the upstream to pause non-critical events via the platform's event throttling mechanism. | Reports show data up to the last processed event. The dashboard displays a "processing backlog" indicator. |
| CurrencyService unavailable during journal processing | The journal is queued for retry. If the exchange rate is cached, it uses the cached rate with a warning. If no rate is available, the event stays in the inbox until the rate is available. | FX-denominated journals may be delayed. Base-currency journals process normally. |

**Key principle:** The event pipeline is at-least-once delivery. Duplicates are handled by idempotency checks in the Transaction Processing Engine (checking `gf_financial_events.event_id` for uniqueness). The inbox ensures that processing is complete before acknowledging the message.
6. Webhook delivery is retried with exponential backoff (3 attempts, 30s DLQ escalation)

---

## 16. Database Architecture

All GrowFinance tables use the `gf_` prefix for clear ownership within the shared database (monolith) and easy identification for extraction (independent service).

### Core tables

```
gf_accounts              -- Chart of accounts
gf_account_hierarchy     -- Parent-child account relationships
gf_journals              -- Journal entries (header)
gf_journal_lines         -- Journal entry lines (detail)
gf_financial_events      -- Inbound event log (audit bridge: source event → journal)
gf_fiscal_years          -- Fiscal year definitions
gf_accounting_periods    -- Period definitions and status
-- No gf_currencies table — currency definitions owned by Financial Services Core.
-- GrowFinance references currency codes from Financial Services Core via CurrencyService contract.
```

### AR/AP tables (financial profiles only)

GrowFinance stores financial relationship data, not operational customer/supplier management. Customer identity is owned by Platform Core (Party/Contact identity) and business applications (CRM, BMS, StockFlow). GrowFinance stores the accounting-specific attributes:

```
gf_customer_financial_profiles  -- Credit limit, payment terms, receivable account, aging info (not customer identity)
gf_supplier_financial_profiles  -- Payment terms, payable account, tax info
gf_customer_balances            -- Running receivable balances
gf_supplier_balances            -- Running payable balances
gf_aging_snapshots              -- Period-end aging for reporting
```

**Ownership boundary:**
- **Platform Core** — party identity (name, contact, address)
- **Business application (StockFlow, BMS, GrowMarket, etc.)** — customer lifecycle, orders, invoices
- **GrowFinance** — financial profile only (credit limit, payment terms, account mapping, aging data)

When a business application creates a customer, it publishes an event. GrowFinance listens and creates the financial profile. It does not manage the customer identity itself.

### Fixed assets

```
gf_assets                -- Asset register
gf_asset_depreciation    -- Depreciation schedules and history
gf_asset_disposals       -- Disposal records
```

### Tax

```
gf_tax_rates             -- VAT, withholding tax rates per jurisdiction
gf_tax_returns           -- Submitted tax returns
gf_tax_return_items      -- Line items within a return
gf_tax_certificates      -- Withholding tax certificates issued/received
gf_tax_liability         -- Running tax liability per period
```

### Reconciliation

```
gf_bank_accounts         -- Bank accounts linked for reconciliation
gf_bank_statements       -- Imported bank statements
gf_statement_lines       -- Individual transactions within a statement
gf_reconciliation_runs   -- Reconciliation sessions per period
gf_reconciliation_matches-- Matched statement lines to ledger entries
gf_bank_rules            -- User-defined auto-matching rules
```

### Approval workflow

```
gf_approval_policies     -- Rules defining which journals need approval
gf_approval_requests     -- Pending approval requests
gf_approval_actions      -- Approve/reject history with user attribution
```

### Revenue recognition

```
gf_revenue_schedules     -- Deferred revenue schedules
gf_revenue_schedule_lines-- Individual recognition events within a schedule
gf_contract_obligations  -- Performance obligations (ASC 606 / IFRS 15)
```

### Three-way matching

```
gf_matching_exceptions   -- Mismatch flags requiring manual review
```

No `gf_purchase_orders` or `gf_grn_records` table — PO/GRN data is owned by StockFlow (Business Application). GrowFinance queries PO/GRN data via the `InventoryProvider` contract when performing three-way matching. Only mismatch exceptions are persisted in GrowFinance.

### Audit & control

```
gf_audit_trail           -- All financial record changes
gf_period_audit_log      -- Period closure and modification history
gf_user_permissions      -- Financial role assignments
```

### Budgeting

```
gf_budgets               -- Budget headers (period, type)
gf_budget_lines          -- Budget amounts per account
gf_budget_versions       -- Multiple budget versions (draft, approved, revised)
```

### Cost accounting

```
gf_cost_centres          -- Cost centre definitions
gf_cost_allocations      -- Allocation rules and history
```

### Migration & import

```
gf_import_batches        -- Import batch tracking (UUID, source, status)
gf_import_mappings       -- Account mapping configurations (Pastel → GrowFinance)
gf_import_errors         -- Per-row import error details
```

### Reporting

```
gf_report_templates      -- Saved report configurations
gf_report_schedules      -- Automated report delivery
gf_financial_ratios      -- Ratio definitions and computations
```

### Financial events audit bridge

The `gf_financial_events` table connects every journal back to its originating business event. It answers the question: *"Why does this journal exist?"*

Schema:

```sql
gf_financial_events
  id                  -- Primary key
  organization_id     -- Tenant scope
  event_id            -- Source event UUID (from outbox)
  event_type          -- e.g., stockflow.sale.completed.v1
  source_application  -- e.g., StockFlow
  source_id           -- e.g., sale_id = 1001
  payload             -- Full event payload (JSON)
  journal_id          -- Generated journal reference (nullable until posted)
  status              -- received, processed, failed
  error_message       -- Failure details if status = failed
  created_at          -- When the event was received
  processed_at        -- When the journal was created
```

**Key design note:** The `gf_` prefix ensures clear ownership for extraction. All tables use `organization_id` for multi-tenant scoping. Journal tables are append-only — the application layer enforces immutability (the Journal Aggregate never exposes update/delete methods), and the database user has append-only permissions for journal tables as a second line of defence. If GrowFinance is later extracted into its own service, the `gf_` prefix can be dropped in the new database.

---

## 17. Security Model

### 17.1 Role-Based Access

| Role | Permissions |
|---|---|
| **Accountant** | Create journals, view reports, manage AR/AP |
| **Finance Manager** | Approve journals, approve payments, manage budgets, close periods |
| **Auditor** | Read-only access to all data and audit logs |
| **Admin** | Configure chart of accounts, manage users, configure tax rates |
| **Viewer** | Read-only access to specific reports |

### 17.2 Segregation of Duties

- Journal creator cannot approve their own entries
- Payment initiator cannot be payment approver
- Period closure requires two-party authorization
- User management is separate from financial operations

### 17.3 Data Isolation

- All queries scoped to organization_id (multi-tenant by design)
- No cross-organization data access
- Audit logs scoped to organization; platform admins have cross-org audit access only
- Cache keys isolated per organization (via `CacheKeyHelper`)

### 17.4 Multi-Factor Authentication (Future)

Phase 1 uses the Platform Core's existing authentication (password-based). MFA is deferred to Phase 3, at which point:
- Period closure, large journal approvals (> configurable threshold), and chart of accounts modifications require a second factor
- Platform Core's MFA infrastructure is used (GrowFinance does not build its own MFA)
- A forward-reference for this integration exists in the Platform Core roadmap

### 17.5 Encryption

- Data encrypted at rest within the database
- Sensitive fields (tax IDs, bank account numbers) encrypted column-level
- All external API communication over TLS
- API keys for external integrations stored encrypted in `app_settings`

---

## 18. Scalability Considerations

| Concern | Approach |
|---|---|
| Data volume | Partition journal tables by `organization_id` (leading) then fiscal year (secondary). All queries scope to a single organization, so `organization_id` must be the leading partition key — year-only partitioning scatters one organization's data across all year partitions. Archive closed fiscal years to read-only storage per organization. |
| Concurrent users | Stateless API layer. Database connection pooling. Read replicas for reporting queries. |
| Tenant growth | Multi-tenant with shared schema. Organization-savvy indexes. No per-tenant databases. |
| Report performance | Pre-aggregated account balances. Periodic balance snapshots. Materialized views for complex reports. |
| Extraction readiness | All services behind contracts. Event-driven integration. No shared mutable state with other domains. |

---

## 19. Operational Monitoring & Alerting

Financial systems have a failure mode worse than an outage: silently wrong financial statements. Monitoring is not optional.

### 19.1 Health Checks

| Check | Frequency | Action on Failure |
|---|---|---|
| Journal balance invariant (all posted journals are balanced) | Every journal post (inline) | Reject the post, alert engineering |
| Account balance vs journal_lines reconciliation | Nightly (`platform:reconcile-balances`), sweeps all open periods + most recently closed | Alert if any account balance diverges by more than 1 currency unit |
| Event pipeline latency (time from event received to journal posted) | Per-event | Alert if > 5 minutes for any single event |
| Dead letter queue depth | Every 5 minutes | Alert if non-empty |
| Period status consistency (no orphaned open periods) | Hourly | Alert and auto-close (if beyond end date + grace period) |
| CurrencyService availability | Every minute | Cache last known rates; alert if unavailable > 5 minutes |

### 19.2 Alert Thresholds

| Alert | Threshold | Severity |
|---|---|---|
| Balance reconciliation mismatch | Any difference > 0 | Critical |
| Journal posting failure rate | > 1% in 5 minutes | Critical |
| Event backlog | > 1,000 unprocessed events | Warning |
| Inbox processing lag | > 10 minutes behind current time | Warning |
| Dead letter queue | Any event in DLQ | Warning |
| CurrencyService down | > 5 minutes | Warning |
| Period not closed | 5 days after period end | Info |

### 19.3 Dashboard Metrics

The operations dashboard displays:
- **Journal volume** (last hour, today, this period)
- **Average posting latency** (ms per journal)
- **Event pipeline depth** (inbox queue size)
- **Balance reconciliation status** (all OK / mismatches detected)
- **Open vs closed periods** (per organization)
- **Failed events** (last 24 hours, grouped by error type)

### 19.4 Runbook (Critical Incidents)

| Incident | Immediate Action | Resolution |
|---|---|---|
| Balance mismatch detected | Lock affected accounts (prevent new posts), notify finance team | Recompute balances from journal_lines; identify root cause; post correction journal if needed |
| Event processing stalled | Restart Transaction Processing Engine worker | Check queue connection, dead letter queue, error logs |
| Journal posting partial failure (some accounts updated, some not) | Emergency stop all posting; lock all account balances | The journal posting is within a single DB transaction — either all accounts updated or none. If the application crashed mid-transaction, the DB rollback ensures consistency. Verify by checking the journal's posted_at timestamp. |
| Data corruption suspected (auditor reports wrong balance) | Create audit snapshot of current state; engage engineering + external auditor | Compare account_balances against journal_lines replay; trace the discrepancy to a specific journal; reverse and re-post if needed |

---

## 20. Offline-First Capability (Phase 6+)

Offline-first is architecturally designed but deferred to Phase 6 (Regional Capability). Accounting is one of the hardest domains to make offline because periods close, approvals change, tax rules change, and balances must remain consistent. Building offline before the core accounting system has real users risks over-engineering an unproven feature.

**Deferred scope:**
- The architecture is documented below at a high level for design continuity
- Engineering should not implement offline capability until GrowFinance has active users demonstrating real need
- The initial focus is operational capture from business applications (StockFlow, GrowMarket) which already handle their own offline needs
- Accounting journals are always created server-side where period validation, account existence, approval, and tax computation are guaranteed

### 20.1 Design (Reference)

When implemented, offline capable clients should:

1. Cache the chart of accounts (read-only, versioned)
2. Queue operational transactions locally (sales, expense drafts)
3. Sync on reconnect — server validates and generates journals
4. Never allow journal creation offline (period validation and approval require server)
5. Use signed tokens with configurable offline validity (default 7 days)

---

## 21. Roadmap

### Phase 1: Accounting Foundation

**Target: Core accounting operational**

- Chart of accounts with hierarchy and templates
- Double-entry journal engine (immutable, balanced)
- General ledger and trial balance
- Accounting period management (open, close, lock)
- Manual journal entry creation
- Basic financial statements (trial balance, P&L, balance sheet)

### Phase 2: SME Accounting

**Target: Day-to-day financial management**

- AR aging and customer balance reports
- AP aging and supplier balance reports
- Fixed asset register and depreciation
- Tax engine (VAT, withholding tax)
- Multi-currency journal creation and FX gain/loss posting
- Budget creation and variance reporting
- Recurring journals and automation
- Bank reconciliation (CSV, MT940 import)
- Pastel migration (chart mapping, opening balances, verification reports)
- General CSV/Excel import framework

### Phase 3: Integrations & Automation

**Target: Connected financial operations**

- StockFlow integration (sales, purchases, stock adjustments auto-journaled)
- Platform Payments integration (settlement auto-reconciliation)
- BMS integration (invoice and expense auto-journaling)
- Approval workflows (configurable chains)
- Automated period-end procedures
- Report scheduling and email delivery

### Phase 4: Enterprise

**Target: Multi-organization and consolidation**

- Multi-company support
- Intercompany transactions
- Consolidated financial statements
- Currency translation
- Branch/department profitability reporting
- Advanced reporting (ratios, trends, dashboards)

### Phase 5: Financial Intelligence

**Target: AI-assisted finance**

- Anomaly detection (duplicate invoices, unusual patterns)
- Cash flow forecasting
- Revenue and expense predictions
- Scenario modelling
- Natural language query interface
- Automated recommendations

### Phase 6: Regional Capability

**Target: Offline resilience and regulatory connectivity (deferred until GrowFinance has real users)**

- Offline-first mode for low-connectivity environments
- ZRA e-invoicing integration (SmartInvoice API)
- Audit snapshot management
- Three-way matching (PO → GRN → Invoice)
- Full workflow engine (configurable approval chains)
- Electronic tax return submission
- Bank API integration (real-time feeds)

---

## 22. Success Criteria

GrowFinance should be considered competitive with Pastel, Sage, and QuickBooks when it achieves:

| # | Criterion | Measurement |
|---|---|---|
| 1 | Full double-entry accounting | All transaction types supported through journal engine |
| 2 | Multi-company support | Consolidated reporting across 10+ companies |
| 3 | Cloud-native operation | 99.9% uptime, automatic backups, zero-downtime deployments |
| 4 | Real-time reporting | Trial balance within 1 second of posting a journal (measured at 100,000 transactions across 1,000 accounts — the threshold scales with infrastructure) |
| 5 | API-first integrations | Core accounting capabilities accessible through `AccountingProvider` contract; full surface area available via REST API |
| 6 | Industry connectivity | Auto-journaling from StockFlow, GrowMarket, BMS |
| 7 | Automated compliance | VAT return generation, period lock enforcement |
| 8 | AI-assisted finance | Anomaly detection and cash flow forecasting |
| 9 | Pastel feature parity | All capabilities in the Pastel Replacement Checklist matched |
| 10 | Scalable multi-tenancy | 1,000+ organizations on a single instance |

### Pastel Replacement Checklist

Before claiming parity with Pastel, GrowFinance must verify each capability:

**Accounting (core):**
- [ ] General ledger with period-level drill-down
- [ ] Cashbook with bank reconciliation
- [ ] Accounts receivable with aging
- [ ] Accounts payable with aging
- [ ] VAT computation and return filing (Zambia)
- [ ] Withholding tax computation and certificates
- [ ] Trial balance, P&L, balance sheet, cash flow
- [ ] Fixed asset register with depreciation
- [ ] Budget entry and variance reporting
- [ ] Multi-company / multi-branch support

**Inventory integration (StockFlow):**
- [ ] Sales auto-journaled to revenue and COGS accounts
- [ ] Purchases auto-journaled to inventory and AP
- [ ] Stock adjustments auto-journaled
- [ ] Inventory valuation reflected in balance sheet

**Migration:**
- [ ] Chart of accounts import with mapping
- [ ] Customer and supplier import
- [ ] Opening balances import (trial balance verification)
- [ ] Historical transaction import (optional, with date preservation)

**Usability:**
- [ ] Accountant workflow (journal creation, approval, reversal)
- [ ] Business owner dashboard (cash position, profitability)
- [ ] Mobile / PWA access for approvals and inquiries
- [ ] Role-based access for teams (accountant, manager, auditor, admin)
- [ ] Audit trail with user attribution and reversal tracking

---

## 23. Implementation Boundaries

This section is the most important section for the engineering team. The document above describes the complete vision for GrowFinance. This section defines what the team should build first, and what should be deferred.

### Phase 1: Build These (Accounting Foundation)

These are non-negotiable for a working accounting system:

- [ ] Chart of accounts (hierarchy, templates, organization seeding)
- [ ] Journal engine (create, validate balance, post)
- [ ] Reversal journals (not delete, not edit)
- [ ] General ledger queries (account activity, date range filter)
- [ ] Trial balance report
- [ ] Profit & Loss report
- [ ] Balance Sheet report
- [ ] Accounting period management (open, close, reopen)
- [ ] Manual journal entry UI
- [ ] Integration with MyGrowNet Platform Core (auth, orgs, workspace)

### Phase 1: Do NOT Build These

These are documented in the architecture but explicitly out of scope for the initial release:

- [ ] AI Financial Assistant
- [ ] Offline-first mode
- [ ] Multi-organization consolidation
- [ ] Revenue recognition engine
- [ ] Banking API / real-time bank feeds
- [ ] Full workflow engine (simple approval flags only)
- [ ] Standalone SaaS mode (separate auth, separate org management)
- [ ] Anomaly detection
- [ ] Forecasting
- [ ] Data warehouse integration
- [ ] Electronic invoicing / ZRA API integration
- [ ] Three-way matching (PO → GRN → Invoice)

### How To Read This Document

| Section | Read As |
|---|---|
| Core Accounting Model (§4-5) | Build this exactly |
| Engine Layers (§3.1) | Architecture vision — implement Core tier (GL, Posting, Transaction Processing, Reporting) in Phase 1 |
| Enterprise Modules (§8.1-8.4) | Build GL, basic AR/AP, Fixed Assets; defer Revenue Recognition, Banking, full workflow |
| Financial Intelligence Layer (§9) | Build Financial Analytics (dashboards, reports); defer AI, forecasting, anomaly detection |
| Multi-Organization (§10) | Single-org in Phase 1; multi-org architecture ready but inactive |
| Compliance (§14) | Build audit trail, period controls, concurrency locking; defer audit snapshots and three-way matching |
| Integration & API (§15) | Build events and `AccountingProvider` contract; defer public REST API |
| Operational Monitoring (§19) | Build health checks and alerts alongside Phase 1 (silently wrong statements > outage) |
| Offline-First (§20) | Reference design only — do not implement until Phase 6 |
| Roadmap (§21) | Follow this exactly |
| Pastel Checklist | Track progress but do not use as Phase 1 requirements |

### Key Architectural Decisions for Engineering

1. **GrowFinance is a MyGrowNet application.** It uses Platform Core for auth, users, organizations, and workspace. Do not build a separate login, user management, or organization system.

2. **Journals are immutable.** The Journal aggregate never exposes update or delete methods. The database user has append-only permissions on journal tables.

3. **Customers and suppliers are profiles, not identities.** GrowFinance stores financial attributes (credit limit, payment terms, account mapping). Customer identity belongs to Platform Core and business applications.

4. **Events in, events out.** GrowFinance consumes financial events from business applications and publishes accounting events. It does not directly query other application databases.

5. **Build for extraction.** Every engine is behind a contract. Every table is prefixed for clear ownership. GrowFinance can be extracted into its own service when the business justifies it.

---

## Appendix A: Relationship to MyGrowNet Financial Platform Architecture

| Aspect | Financial Platform Architecture | GrowFinance Enterprise Architecture |
|---|---|---|
| Scope | All financial domains across MyGrowNet | GrowFinance only |
| Audience | Architects, module developers | Product team, accountants, investors |
| Concerns | Ownership boundaries, contracts, events | Accounting capabilities, features, roadmap |
| Defines | Who owns what | What GrowFinance does internally |
| Mobility | Neutral — protects the ecosystem | Competitive — defines product direction |

GrowFinance Enterprise Architecture **consumes** the Financial Platform Architecture. It does not redefine boundaries. Every capability described here fits within GrowFinance's ownership as defined in the platform document.

---

## Appendix B: Glossary

| Term | Definition |
|---|---|
| **Accounting Period** | A defined time interval (month, quarter, year) within a fiscal year for which financial reporting is performed. |
| **Chart of Accounts** | A structured list of all accounts used by an organization, organized by account class. |
| **Consolidation** | The process of combining financial statements of multiple entities into a single group statement. |
| **Control Account** | A general ledger account that summarizes sub-ledger balances (e.g., AR Control summarizes all customer balances). |
| **Dimension** | A reporting classification (cost centre, branch, department, project) used to tag journal lines for multi-dimensional analysis. |
| **Domain Invariant** | A business rule that must always hold true, enforced at the domain entity or service layer. |
| **Engine Layer** | A domain service within the Financial Core Platform that processes a specific category of accounting operations (e.g., Posting Engine, Tax Engine). |
| **Hybrid Event-Relational Architecture** | An architectural pattern where events drive processing and relational tables serve as the system of record, avoiding full event sourcing complexity. |
| **Offline-First** | A capability allowing clients to queue transactions locally when disconnected and synchronize when connectivity is restored, using the outbox pattern for consistency. |
| **Cost Centre** | An accounting classification used to track costs by department or function. |
| **Double-Entry Accounting** | The method where every transaction affects at least two accounts, with equal debits and credits. |
| **Fiscal Year** | A 12-month period used for financial reporting, may not align with calendar year. |
| **General Ledger** | The complete record of all financial transactions over the life of an organization. |
| **Intercompany Transaction** | A financial transaction between two entities within the same group. |
| **Journal Entry** | A record of a financial transaction with at least one debit and one credit line. |
| **Reversal Entry** | A correcting journal entry that cancels a previously posted entry. |
| **Segregation of Duties** | An internal control requiring that no single individual has control over all phases of a financial process. |
| **Trial Balance** | A report listing all accounts with their debit or credit balances as of a specific date. |

---

## Appendix C: Version History

| Version | Date | Author | Changes |
|---|---|---|---|
| 1.0 | 2026-07-26 | System | Initial draft — product vision, competitive positioning, accounting engine architecture, enterprise modules, financial intelligence, multi-org, industry adaptation, reporting, automation, compliance, integrations, database schema, security, scalability, roadmap, success criteria |
| 2.0 | 2026-07-26 | System | Added Accounting Domain Model with entity hierarchy, ERD, domain invariants (§5). Added Enterprise Accounting Rules covering journal posting, period closing, multi-dimensional accounting, account balances, audit trail (§6). Added Migration Architecture for Pastel/CSV/Excel import with verification flow and idempotency (§7). Added migration tables to database schema. Added Pastel migration to roadmap Phase 2. Renumbered sections 8–20. |
| 3.0 | 2026-07-26 | System | Added Next-Generation Accounting Principles (§2.5): real-time collaboration, immutable records, connected finance, real-time reporting. Added Financial Core Platform Layers diagram (§3.1): 10 engine layers from GL to AI Services. Added Hybrid Event + Relational Architecture (§15.1): events for processing, relational tables for storage. Expanded Integration & API Platform (§15.2–15.5): REST API catalog, webhook events, API design principles. Added Offline-First Capability (§19): PWA sync architecture, offline capabilities table, conflict resolution, security. Renamed §15 to Integration & API Platform. Renumbered §20→§21. |
| 4.0 | 2026-07-26 | System | Architecture review response: engine tier classification (Core/Expansion/Advanced), redefined standalone SaaS as Platform-primary (future standalone), removed DELETE from API catalog (replaced with reverse/void), renamed customer/supplier tables to financial profiles with ownership boundary, moved offline-first to Phase 6 (implementation stub), split Financial Intelligence into current analytics vs future AI, added audit locking/snapshots to Compliance, expanded Zambia compliance (ZRA, VAT categories, e-invoicing readiness, withholding tax), replaced database trigger enforcement with application-level + DB permissions, added Implementation Boundaries section (§22) with Phase 1 build/do-not-build checklists and engineering ADRs. Version 4.0. |
| 5.0 | 2026-07-26 | System | Second architecture review response: reconciled real-time reporting vs recompute-from-journal_lines contradiction (transactionally-maintained `account_balances` with explicit query path for reports vs drill-down vs audit); added concurrency/locking model (§6.6) with row-level locking, period-close race guard, and reconciliation job; stated full-journal reversal only (no partial-line correction, deliberate design decision); added multi-currency depth (§5.6) with realized/unrealized FX gain-loss, rounding accounts, and journal examples; fixed partitioning to use `organization_id` as leading key; added batch import strategy for large historical datasets; stated Zambia-first market entry explicitly; added backpressure/failure story for event pipeline (§15.5); qualified success criterion #4 with org size assumption; added MFA forward-reference to Security; added Operational Monitoring & Alerting section (§19) with health checks, alert thresholds, dashboard metrics, and runbook. Version 5.0. |
| 5.1 | 2026-07-26 | System | Third architecture review response: added lock-ordering requirement (`ORDER BY account_id ASC` before `FOR UPDATE`) to prevent classic deadlock on multi-account journals; flagged control-account hot-row contention as a known scaling limit with eventual micro-batched solution (Phase 3+); clarified reconciliation job scope to sweep all open periods nightly (not only closed); extended concurrency model to cover `gf_customer_balances` and `gf_supplier_balances` with same lock-and-update-in-transaction pattern. Version 5.1. |
| 5.2 | 2026-07-26 | System | Cleaned duplicate table definitions in §16 (Database Architecture): removed stray `tables` code-fence fragment, consolidated duplicate `gf_tax_*` block (already defined under Tax), merged duplicate "Audit & compliance" heading into "Audit & control" with `gf_period_audit_log`, eliminated triple-definition of `gf_audit_trail` and `gf_user_permissions`. Version 5.2. |
| 6.0 | 2026-07-26 | System | Cross-document conflict resolution against MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md v4.0. Aligned data ownership boundaries: removed `tax_exemptions` and `tax_categories` tables from GrowFinance schema (owned by business applications); restricted Dimension entity to cost_centre only (operational dimensions referenced by ID only); removed `gf_currencies` table (currency definitions owned by Financial Services Core); removed `gf_purchase_orders` and `gf_grn_records` tables (PO/GRN data owned by StockFlow, queried via `InventoryProvider` contract). Aligned event naming to parent convention (`growfinance.*.v1`). Added missing events (`budget.updated`, `report.generated`). Clarified `AccountingProvider` contract scope (core operations) vs REST API (full surface). Added `createReversalEntry()` to contract method list. Added architectural readiness note for future extraction. Fixed AR aging boundary (financial reporting only, operational collections owned by business apps). All 14 identified conflicts resolved. Version 6.0. |

---

*This document is governed by the [MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md](MYGROWNET_FINANCIAL_PLATFORM_ARCHITECTURE.md). Any conflict between the two documents is resolved by the platform architecture.*
