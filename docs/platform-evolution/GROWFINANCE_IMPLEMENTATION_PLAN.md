# GrowFinance Enterprise Accounting — Implementation Plan

> **Status:** Draft  
> **Version:** 1.0  
> **Aligns with:** `GROWFINANCE_ENTERPRISE_ARCHITECTURE.md` v6.0  
> **Objective:** Build GrowFinance from partial codebase to production-ready accounting platform following the roadmap in §21 and build/do-not-build list in §23

---

## Overview

GrowFinance currently has 17 services, partial tables (`growfinance_accounts`, `growfinance_journal_entries`, `growfinance_journal_lines`, `growfinance_budgets`), and a stub `AccountingProvider`. This plan builds the full product.

### Engine Tiers (from §3.1)

| Tier | Engines | Phase in This Plan |
|---|---|---|
| **Core** | General Ledger Engine, Posting Engine, Transaction Processing Engine, Reporting Engine | Phase 1 |
| **Expansion** | Tax Engine, Banking Engine, Budget Engine, Workflow Engine, Fixed Asset Engine | Phase 2 |
| **Advanced** | Revenue Recognition Engine, AI Financial Services, Forecasting, Anomaly Detection | Phase 4–5 |

### Phases at a Glance

| Phase | Name | Duration | Key Deliverable |
|---|---|---|---|
| G1 | Accounting Foundation | 8 weeks | Working double-entry accounting system |
| G2 | SME Accounting | 8 weeks | Day-to-day financial management |
| G3 | Integrations & Automation | 6 weeks | Connected financial operations |
| G4 | Enterprise | 6 weeks | Multi-org and consolidation |
| G5 | Financial Intelligence | 8 weeks | AI-assisted finance |
| G6 | Regional Capability | 6 weeks | Offline resilience and regulatory connectivity |

**Total estimated duration:** ~42 weeks (phases build sequentially, some overlap possible)

---

## Phase G1: Accounting Foundation

**Duration:** 8 weeks  
**Target:** Core accounting operational — a user can create journals, post them, and see financial statements  
**Engine tier:** Core — General Ledger, Posting, Transaction Processing, Reporting  
**Reference:** §21 Phase 1, §23 Phase 1 Build These

### What This Phase Enables

A user can:
- Configure a chart of accounts (hierarchy, templates, org seeding)
- Create and post manual journal entries (balanced, immutable)
- Reverse a posted journal (full reversal, not edit or delete)
- View the general ledger with date-range filtering
- Run trial balance, P&L, and balance sheet reports
- Manage accounting periods (open, close, lock)
- Access GrowFinance through the MyGrowNet platform (auth, workspace, org context)

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G1.1 | Rebuild `growfinance_accounts` table with hierarchy support (parent_id, level, path) and statement mapping columns | Migration | P0 |
| G1.2 | Create `Account` domain entity with `isActive()`, `isContraAccount()`, `children()`, `parent()`, `statementCategory()` | Entity | P0 |
| G1.3 | Create `AccountRepositoryInterface` + Eloquent impl with `getChart(orgId)`, `getAccount(code)`, `getChildren(parentId)`, `getAccountsByType(type)`, `getActiveAccounts()` | Repository | P0 |
| G1.4 | Create chart of accounts seeding service with templates (retail, service, manufacturing, NGO) | Seeding | P0 |
| G1.5 | Create chart of accounts management UI (list, create, edit, activate/deactivate accounts) | Vue pages | P0 |
| G1.6 | Replace `growfinance_journal_entries` and `growfinance_journal_lines` with proper schema: `journal_number`, `date`, `description`, `period_id`, `status` (draft/posted/reversed), `reversal_of_id`, `reversal_reason`, `source_event_id`, `created_by`, `posted_at`, `dimensions_json` — with `reversal_of_id` FK as self-referencing | Migration | P0 |
| G1.7 | Create `JournalEntry` domain entity with `isBalanced()`, `post()`, `reverse(reason)`, `lines()`, `totalDebit()`, `totalCredit()` | Entity | P0 |
| G1.8 | Create `JournalLine` domain entity with `account()`, `debitAmount()`, `creditAmount()`, `dimensions()` | Entity | P0 |
| G1.9 | Create `JournalRepositoryInterface` with `create()`, `findById()`, `findByPeriod()`, `findByDateRange()`, `findByAccount()`, `findByStatus()`, `findByEventId()` | Repository | P0 |
| G1.10 | Create `PostingEngine` — validates balance, resolves accounts, assigns journal number (per-org sequence), sets status=posted, records posted_at, fires `growfinance.journal.posted.v1` | Domain service | P0 |
| G1.11 | Create `GeneralLedgerEngine` — `getAccountActivity(orgId, accountId, from, to)`, `getAccountBalance(orgId, accountId, asOf)`, `getPeriodBalances(orgId, periodId)`, `getTrialBalance(orgId, asOf)` | Domain service | P0 |
| G1.12 | Create `Period` entity + `PeriodRepositoryInterface` — open, close, re-open, status validation | Entity + repo | P0 |
| G1.13 | Create `AccountingPeriodService` — `validatePeriodIsOpen()`, `closePeriod()`, `reopenPeriod()`, `getCurrentPeriod()`, `getPeriodsForFiscalYear()` | Service | P0 |
| G1.14 | Create reversal journal flow — `JournalEntry::reverse(reason)` creates a mirror entry with reversed debits/credits, sets `reversal_of_id` on both entries, posts both | Domain logic | P0 |
| G1.15 | Implement `ReportingEngine` — `getTrialBalance()`, `getProfitAndLoss(from, to)`, `getBalanceSheet(asOf)`, `getCashFlow(from, to)` | Domain service | P0 |
| G1.16 | Create `ReportRepositoryInterface` for cached report snapshots | Repository | P0 |
| G1.17 | Create manual journal entry UI (create draft, add lines, validate balance, post) | Vue pages | P0 |
| G1.18 | Create journal list UI (filter by period, status, date range) | Vue pages | P0 |
| G1.19 | Create reversal journal UI (select original entry, enter reason, confirm) | Vue pages | P0 |
| G1.20 | Create trial balance report UI with date filter and drill-down | Vue page | P0 |
| G1.21 | Create P&L report UI with period comparison | Vue page | P0 |
| G1.22 | Create balance sheet report UI as-of date | Vue page | P0 |
| G1.23 | Create period management UI (open, close, lock periods) | Vue pages | P0 |
| G1.24 | Implement real `AccountingProviderImpl` (replaces stub) — delegates to PostingEngine, GeneralLedgerEngine, ReportingEngine | Implementation | P0 |
| G1.25 | Wire `AccountingProvider` in IntegrationRegistry with real implementation | Registry | P0 |
| G1.26 | Create `GrowFinanceServiceProvider` — bind all repository interfaces, register manifest | ServiceProvider | P0 |
| G1.27 | Create `growfinance.fiscal_years` table migration | Migration | P1 |
| G1.28 | Create `growfinance.accounting_periods` table migration | Migration | P1 |
| G1.29 | Create fiscal year entity + `FiscalYearService` (create year, generate periods, close year) | Domain | P1 |
| G1.30 | Implement `DimensionProvider` for GrowFinance cost centres | Contract | P1 |
| G1.31 | Add `growfinance.account.balance.changed.v1` event published after every posted journal | Event | P1 |
| G1.32 | Add `growfinance.period.closed.v1` event | Event | P1 |
| G1.33 | Wire outbox for all GrowFinance events | Outbox adoption | P1 |
| G1.34 | Create growfinance routes: journal CRUD, period management, report viewing | Routes | P1 |
| G1.35 | Create GrowFinance workspace layout (wraps PlatformShell with growfinance nav items) | Layout | P1 |
| G1.36 | Write feature tests: journal creation/posting, reversal, trial balance, P&L, balance sheet | Tests | P1 |

### Success Criteria

- [ ] User creates chart of accounts → adds accounts with hierarchy
- [ ] User creates manual journal entry → adds lines → validates balance → posts
- [ ] Posted journal appears in general ledger query
- [ ] User reverses posted journal → reversal entry appears with `reversal_of` reference
- [ ] Trial balance matches sum of all posted journals
- [ ] P&L and balance sheet reflect posted entries correctly
- [ ] Period can be closed → new entries blocked → can be re-opened
- [ ] `AccountingProvider` returns real data (not stub)
- [ ] All P0 tasks complete and tested

---

## Phase G2: SME Accounting

**Duration:** 8 weeks  
**Target:** Day-to-day financial management — AR, AP, fixed assets, tax, multi-currency, budgets, bank reconciliation, Pastel migration  
**Engine tier:** Expansion — Tax Engine, Banking Engine, Budget Engine, Fixed Asset Engine  
**Reference:** §21 Phase 2, §8.5 Tax Engine (Phase 1 scope)

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G2.1 | Create AR aging report — customer balances, overdue buckets (30/60/90+), contact info | Report + UI | P0 |
| G2.2 | Create AP aging report — supplier balances, overdue buckets | Report + UI | P0 |
| G2.3 | Create customer balance inquiry UI (per-customer drill-down from AR) | Vue page | P0 |
| G2.4 | Create supplier balance inquiry UI (per-supplier drill-down from AP) | Vue page | P0 |
| G2.5 | Create `growfinance_fixed_assets` table migration (asset register: name, category, purchase_date, cost, residual_value, useful_life, depreciation_method, accumulated_depreciation, net_book_value, status) | Migration | P0 |
| G2.6 | Create `FixedAsset` domain entity + `FixedAssetRepositoryInterface` | Entity + repo | P0 |
| G2.7 | Create `FixedAssetService` with `acquire()`, `depreciate()`, `dispose()`, `getNetBookValue()` | Service | P0 |
| G2.8 | Create depreciation engine — straight-line, reducing balance methods, auto-generates period-end depreciation journals | Domain service | P0 |
| G2.9 | Create fixed asset register UI (list, acquire, dispose, view depreciation schedule) | Vue pages | P0 |
| G2.10 | Create `growfinance_tax_rates` table migration (tax_type, rate, effective_from, effective_to, jurisdiction, account_code) | Migration | P0 |
| G2.11 | Create `TaxEngine` — `determineTaxTreatment(event)`, `computeVAT(amount, taxCode)`, `computeWithholding(amount, supplierType)`, `getVATReturn(orgId, from, to)`, `getWithholdingSchedule(orgId, from, to)` | Domain service | P0 |
| G2.12 | Create Zambia VAT return report (output VAT, input VAT, net VAT payable) | Report + UI | P0 |
| G2.13 | Create Zambia withholding tax schedule report | Report + UI | P0 |
| G2.14 | Add multi-currency columns to journal entries (currency_code, exchange_rate, functional_amount) | Migration | P0 |
| G2.15 | Create `CurrencyConversionService` within GrowFinance (delegates to Financial Services Core CurrencyService) | Service | P0 |
| G2.16 | Add realized/unrealized FX gain/loss posting on multi-currency journals | Domain logic | P0 |
| G2.17 | Create `growfinance_budgets` — enhance with versioning, categories | Migration | P0 |
| G2.18 | Create `growfinance_budget_items` table | Migration | P0 |
| G2.19 | Create `BudgetService` — create budget, add items, track actuals, variance report | Service | P0 |
| G2.20 | Create budget creation and variance reporting UI | Vue pages | P0 |
| G2.21 | Create `RecurringJournalService` — template-based recurring entries (frequency, next_run, auto-post) | Service | P0 |
| G2.22 | Create recurring journal UI (create template, schedule, activate) | Vue pages | P1 |
| G2.23 | Create `BankReconciliationService` — CSV/MT940 import, auto-match by amount/date/reference, status tracking | Service | P0 |
| G2.24 | Create bank reconciliation UI (import statement, match transactions, flag discrepancies) | Vue pages | P0 |
| G2.25 | Create Pastel migration tools — chart of accounts mapping import, opening balances import with trial balance verification | Console commands | P1 |
| G2.26 | Create CSV/Excel import framework for journals, accounts, budgets | Console commands | P1 |
| G2.27 | Create `growfinance_report_snapshots` table for frozen period-end reports | Migration | P1 |
| G2.28 | Create report snapshot service (save trial balance/P&L/balance sheet at period close) | Service | P1 |

### Success Criteria

- [ ] AR aging report shows customer balances bucketed by overdue period
- [ ] AP aging report shows supplier balances bucketed by overdue period
- [ ] Fixed asset depreciation journals post automatically each period
- [ ] VAT return shows output VAT, input VAT, and net payable for Zambia
- [ ] Multi-currency journals with FX gain/loss post correctly
- [ ] Budget variance report compares budget vs actuals
- [ ] Bank reconciliation statement import matches against journal entries
- [ ] Pastel chart of accounts and opening balances import verified

---

## Phase G3: Integrations & Automation

**Duration:** 6 weeks  
**Target:** Connected financial operations — auto-journaling from business apps, approval workflows, automated period-end  
**Engine tier:** Expansion — Workflow Engine  
**Reference:** §21 Phase 3

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G3.1 | Create StockFlow sales listener — consumes `stockflow.sale.completed.v1`, creates revenue + COGS journal entries | Event listener | P0 |
| G3.2 | Create StockFlow purchase listener — consumes `stockflow.purchase.received.v1`, creates inventory + AP entries | Event listener | P0 |
| G3.3 | Create StockFlow stock adjustment listener — consumes `stockflow.stock.adjusted.v1`, creates inventory adjustment entry | Event listener | P0 |
| G3.4 | Create Platform Payments settlement listener — consumes `platform.payment.settled.v1`, creates cash/AR entry | Event listener | P0 |
| G3.5 | Create BMS invoice listener — consumes `bms.invoice.created.v1`, creates AR + revenue entry | Event listener | P0 |
| G3.6 | Create BMS payment listener — consumes `bms.invoice.paid.v1`, creates cash + AR contra entry | Event listener | P0 |
| G3.7 | Create BMS expense listener — consumes `bms.expense.recorded.v1`, creates expense + cash/AP entry | Event listener | P1 |
| G3.8 | Create auto-journaling mapping UI — map event types to account templates per org | Vue page | P1 |
| G3.9 | Create `WorkflowEngine` — configurable approval chains (create → submit → approve → post), status tracking | Domain service | P1 |
| G3.10 | Create journal approval workflow UI (submit for approval, approve/reject, view pending) | Vue pages | P1 |
| G3.11 | Create automated period-end procedures — recurring closing entries, accruals, prepayments, depreciation | Service | P1 |
| G3.12 | Create period-end checklist UI with status tracking | Vue page | P1 |
| G3.13 | Create report scheduling service — schedule PDF reports, email delivery to configured recipients | Service | P1 |
| G3.14 | Create scheduled report configuration UI | Vue page | P1 |
| G3.15 | Write integration tests: StockFlow sale → GrowFinance journal posted end-to-end | Tests | P0 |
| G3.16 | Write integration tests: Platform Payments settlement → GrowFinance journal posted | Tests | P0 |
| G3.17 | Write integration tests: BMS invoice → GrowFinance journal posted | Tests | P0 |

### Success Criteria

- [ ] StockFlow sale auto-creates revenue + COGS journal entries without manual intervention
- [ ] StockFlow purchase auto-creates inventory + AP journal entries
- [ ] Platform Payments settlement auto-creates cash journal entries
- [ ] BMS invoice auto-creates AR journal entries
- [ ] Journal approval workflow routes entries through configurable chains
- [ ] Period-end procedures run with single click (or automatically)
- [ ] Report scheduling delivers PDFs to configured users

---

## Phase G4: Enterprise

**Duration:** 6 weeks  
**Target:** Multi-organization and consolidation  
**Engine tier:** Advanced (first half)  
**Reference:** §21 Phase 4, §10 Multi-Organization

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G4.1 | Create multi-company data model — org group, parent-child org relationships | Migration + entities | P0 |
| G4.2 | Create `growfinance_group_consolidation` table for group-level reporting | Migration | P0 |
| G4.3 | Create `ConsolidationService` — aggregate org-level data to group, currency translation, elimination entries | Service | P0 |
| G4.4 | Create intercompany transaction tracking — `growfinance_intercompany_transactions` table + matching | Migration + service | P0 |
| G4.5 | Create intercompany elimination engine — auto-generates elimination journals | Domain service | P0 |
| G4.6 | Create consolidated financial statements UI (trial balance, P&L, balance sheet at group level) | Vue pages | P0 |
| G4.7 | Create currency translation for consolidation (functional currency per org, reporting currency for group) | Service | P1 |
| G4.8 | Create branch/department profitability reporting — filter P&L by operational dimension | Report + UI | P1 |
| G4.9 | Create advanced reporting — financial ratios (current ratio, quick ratio, ROI, ROE), trend analysis (period-over-period) | Reports + UI | P1 |
| G4.10 | Create dashboard widgets — cash position, revenue trend, expense breakdown, AR/AP summary | Vue components | P1 |
| G4.11 | Create report export — PDF, CSV, XLSX for all financial statements | Export service | P1 |

### Success Criteria

- [ ] Group-level consolidated trial balance aggregates child orgs
- [ ] Intercompany transactions can be matched and eliminated
- [ ] Currency translation applies correct exchange rates per org functional currency
- [ ] Branch/department P&L filters by operational dimension
- [ ] Financial ratios computed and displayed in dashboard

---

## Phase G5: Financial Intelligence

**Duration:** 8 weeks  
**Target:** AI-assisted finance  
**Engine tier:** Advanced (second half)  
**Reference:** §21 Phase 5, §9 Financial Intelligence Layer

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G5.1 | Create anomaly detection engine — duplicate invoice detection, unusual journal patterns, out-of-range amounts | ML service | P0 |
| G5.2 | Create cash flow forecasting — historical trend analysis, projected inflows/outflows, confidence intervals | ML service | P0 |
| G5.3 | Create revenue and expense prediction — regression models on historical P&L data | ML service | P1 |
| G5.4 | Create scenario modelling — what-if analysis (e.g., "what if we increase price by 10%?") | Service + UI | P1 |
| G5.5 | Create natural language query interface — "what was our revenue last month?" → trial balance query | NLP service | P1 |
| G5.6 | Create automated recommendations — suggested journal corrections, budget alerts, cash flow warnings | Service + UI | P1 |
| G5.7 | Create financial analytics dashboard — trends, forecasts, anomalies, key metrics | Vue page | P1 |

### Success Criteria

- [ ] Anomaly detection identifies potential duplicate journal entries
- [ ] Cash flow forecast has measurable accuracy against actuals
- [ ] Natural language queries return correct financial data
- [ ] Recommendations are surfaced in the dashboard

---

## Phase G6: Regional Capability

**Duration:** 6 weeks  
**Target:** Offline resilience and regulatory connectivity  
**Reference:** §21 Phase 6, §20 Offline-First, §8.5 ZRA integration

### Tasks

| # | Task | Deliverable | Priority |
|---|---|---|---|
| G6.1 | Create offline-first mode — IndexedDB cache, offline queue, sync engine | Frontend + service | P0 |
| G6.2 | Create ZRA e-invoicing integration — SmartInvoice API, validation gateway | Integration | P0 |
| G6.3 | Create audit snapshot management — frozen reports at period close with integrity hash | Service | P0 |
| G6.4 | Create three-way matching engine — PO → Goods Receipt Note → Invoice auto-matching | Service | P1 |
| G6.5 | Create full workflow engine — configurable approval chains with conditions, escalations, SLA tracking | Service | P1 |
| G6.6 | Create electronic tax return submission — ZRA API for VAT returns | Integration | P0 |
| G6.7 | Create bank API integration — real-time feeds from Zambian banks | Integration | P1 |

### Success Criteria

- [ ] Offline mode allows journal creation without connectivity; synced on reconnect
- [ ] ZRA e-invoicing generates compliant invoices
- [ ] Audit snapshots provide verifiable period-end reports
- [ ] Three-way matching flags discrepancies between PO, GRN, and invoice
- [ ] Tax returns submitted electronically to ZRA

---

## Dependency Graph

```
G1 (Accounting Foundation)
    │
    ▼
G2 (SME Accounting)
    │
    ▼
G3 (Integrations & Automation)
    │
    ├──────────────────┐
    ▼                  ▼
G4 (Enterprise)   G5 (Financial Intelligence)
    │                  │
    └──────────────────┘
           │
           ▼
    G6 (Regional Capability)
```

Phases are sequential — each depends on the previous. G4 and G5 can overlap partially after G3 is stable.

---

## Pastel Replacement Checklist

Tracked against §22 Pastel Replacement Checklist. Phase G1–G2 targets the **Accounting (core)** and **Usability** checklists. G3 adds **Inventory integration**. G2 includes **Migration** tools.

| Checklist | Target Phase | Status |
|---|---|---|
| General ledger with period-level drill-down | G1 | Not started |
| Cashbook with bank reconciliation | G2 | Not started |
| Accounts receivable with aging | G2 | Not started |
| Accounts payable with aging | G2 | Not started |
| VAT computation and return filing (Zambia) | G2 | Not started |
| Withholding tax computation and certificates | G2 | Not started |
| Trial balance, P&L, balance sheet, cash flow | G1 | Not started |
| Fixed asset register with depreciation | G2 | Not started |
| Budget entry and variance reporting | G2 | Not started |
| Multi-company / multi-branch support | G4 | Not started |
| Sales auto-journaled (StockFlow) | G3 | Not started |
| Purchases auto-journaled (StockFlow) | G3 | Not started |
| Stock adjustments auto-journaled | G3 | Not started |
| Pastel migration tools | G2 | Not started |
| Accountant workflow (approval, reversal) | G1/G3 | Not started |
| Business owner dashboard | G4 | Not started |

---

## What NOT to Build (per §23 Phase 1)

| Feature | Deferred To | Rationale |
|---|---|---|
| AI Financial Assistant | G5 | Requires transaction volume |
| Offline-first mode | G6 | Requires real user demand |
| Multi-organization consolidation | G4 | Single-org in initial phases |
| Revenue recognition engine | G4–G5 | ASC 606/IFRS 15 not needed for SMEs |
| Banking API / real-time bank feeds | G6 | Zambia bank APIs not widely available |
| Full workflow engine (BPMN) | G6 | Simple approval flags suffice initially |
| Standalone SaaS mode | FUTURE_VISION.md | Platform Core handles auth/orgs |
| Anomaly detection | G5 | Requires historical data |
| Forecasting | G5 | Requires historical data |
| Data warehouse integration | G5 | CDC export to BigQuery/Redshift |
| Electronic invoicing / ZRA API | G6 | Phase 1 uses manual return generation |
| Three-way matching (PO → GRN → Invoice) | G6 | Not needed at launch |

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Phase G1 scope creep (building too much too soon) | High | High | Strict adherence to §23 Build/Do-Not-Build list |
| Pastel migration data quality issues | Medium | High | Validate opening balances with trial balance comparison before cutover |
| Multi-currency complexity delays G2 | Medium | Medium | Single-currency journals in G1; multi-currency isolated to G2 |
| Event listeners fail silently (G3) | Medium | High | DLQ + alerts required before G3 starts |
| AI features (G5) lack sufficient training data | Low | Medium | Start with rule-based heuristics; add ML when volume grows |
| Offshore-first (G6) duplicates platform work | Low | Medium | Evaluate actual demand before committing |

---

## Measuring Progress

| Metric | Target | Phase |
|---|---|---|
| Journals posted end-to-end (create → validate → post → query) | Working | G1 |
| Financial statements produce correct output | Verified | G1 |
| Reversal journals linked with `reversal_of` | Working | G1 |
| AccountingProvider returns real data (not stub) | Working | G1 |
| Tax return matches manual calculation | Verified | G2 |
| Multi-currency FX gain/loss posts correctly | Verified | G2 |
| Bank reconciliation matches against journals | Working | G2 |
| StockFlow sale → GrowFinance journal (no manual entry) | Automated | G3 |
| Consolidated financial statements across org groups | Working | G4 |
| Anomaly detection identifies duplicates | Verified | G5 |
| Offline journals sync on reconnect | Working | G6 |
