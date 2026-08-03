# MyGrowNet Platform Integration Architecture — Implementation Plan

> **Status:** Active  
> **Version:** 1.1  
> **Aligns with:** `PLATFORM_INTEGRATION_ARCHITECTURE.md` v10.1  
> **Objective:** Incrementally build the platform integration layer defined in the architecture document

---

## Overview

This plan breaks down the architecture into **9 phases** ordered by dependency and risk. Each phase produces a working, deployable system. Nothing is built before it is needed.

### Phases at a Glance

| Phase | Name | Duration | Dependency |
|-------|------|----------|------------|
| 0 | Foundation Audit & Remediation | 2 weeks | None |
| 1 | Platform Core & Runtime Layer | 4 weeks | Phase 0 |
| 2 | Event Infrastructure | 3 weeks | Phase 1 |
| 3 | Integration Contracts | 4 weeks | Phase 1 |
| 4 | Platform Services | 4 weeks | Phase 1 |
| 5 | Operational Readiness | 3 weeks | Phases 2–4 |
| 6 | Data Governance & Tenant Isolation | 3 weeks | Phase 1 |
| 7 | Reliable Event Delivery (v2) | 3 weeks | Phase 2 |
| 8 | Independent Deployment Readiness | 4 weeks | Phases 2–7 |

**Total estimated duration:** ~30 weeks (incremental, many phases can overlap)

---

## Phase 0: Foundation Audit & Remediation

**Duration:** 2 weeks  
**Dependency:** None  
**Goal:** Understand current state, fix blocking issues, establish patterns

### Tasks

| # | Task | Deliverable | Owner |
|---|------|-------------|-------|
| 0.1 | Audit all cross-module `DB::table()` and `DB::connection()` calls | Report of violations | Platform team |
| 0.2 | Audit all cross-module Eloquent model imports | Report of violations | Platform team |
| 0.3 | Audit all `app(Service::class)` calls across module boundaries | Report of violations | Platform team |
| 0.4 | Verify every module has its own ServiceProvider with `loadMigrationsFrom()` | Module registry checklist | Platform team |
| 0.5 | Verify no module migration touches another module's tables | Migration scope audit | Platform team |
| 0.6 | Document current event usage (Laravel events, listeners, dispatches) | Event inventory | Platform team |
| 0.7 | Document current integration patterns (direct calls, observers, webhooks) | Integration pattern map | Platform team |
| 0.8 | Create `CONTRIBUTING.md` with integration rules (Rule 1–10 from §16) | Developer guidelines | Platform team |

### Success Criteria

- All cross-module violations are documented in a tracking issue
- Every module's migration folder is verified against the Canonical Migration Folders table
- Integration rules are published and shared with the team

---

## Phase 1: Platform Core & Runtime Layer

**Duration:** 4 weeks  
**Dependency:** Phase 0  
**Goal:** Establish Platform Core as the identity/org/auth authority and deploy the Runtime Layer

### 1.1 Platform Core Consolidation

| # | Task | Deliverable |
|---|------|-------------|
| 1.1.1 | Move `User` model into `App\Domain\Core` (keep Eloquent in Infrastructure) | Core user entity |
| 1.1.2 | Move `Organization`, `OrganizationMember` models into Core | Core org entities |
| 1.1.3 | Move `Application` model into Core | Core app entity |
| 1.1.4 | Create `App\Domain\Core\Services\IdentityService` | Identity service |
| 1.1.5 | Create `App\Domain\Core\Services\OrganizationService` | Org service |
| 1.1.6 | Create `App\Domain\Core\Services\ApplicationService` | App service |
| 1.1.7 | Remove cross-module model references (BMS → Core User, etc.) | Clean imports |
| 1.1.8 | Create `App\Domain\Core\Exceptions\` base exceptions | Exception base |

### 1.2 PlatformContext

| # | Task | Deliverable |
|---|------|-------------|
| 1.2.1 | Create `PlatformContext` class with all fields (traceId, requestId, userId, organizationId, applicationId, installationId, workspaceId, locale, timezone) | PlatformContext value object |
| 1.2.2 | Create middleware `ResolvePlatformContext` that builds context from request | Middleware |
| 1.2.3 | Inject PlatformContext into all controllers via `SetPlatformContext` middleware | Context injection |
| 1.2.4 | Add PlatformContext to Inertia shared data | Frontend availability |
| 1.2.5 | Create `PlatformContextFacade` or helper for queue/CLI access | Context resolver |
| 1.2.6 | Document how to access context in services, jobs, commands | Developer docs |

### 1.3 Application Runtime Infrastructure

| # | Task | Deliverable |
|---|------|-------------|
| 1.3.1 | Formalize `ResolveDomainContext` middleware (`§5`) | Runtime middleware |
| 1.3.2 | Create `SetPlatformContext` middleware (`§5`) | Runtime middleware |
| 1.3.3 | Ensure `DetectSubdomain` feeds into DomainResolution | Subdomain → context |
| 1.3.4 | Create `TenantAwareRepository` base class with automatic `organization_id` scoping (`§18`) | Repository base |
| 1.3.5 | Document Application Runtime Layer as a distinct concern | Runtime docs |

### 1.4 Application Manifest Prototype

| # | Task | Deliverable |
|---|------|-------------|
| 1.4.1 | Define manifest array schema (all fields from §4.6) | Schema definition |
| 1.4.2 | Create `ModuleDiscovery` service that collects manifests from ServiceProviders | Discovery service |
| 1.4.3 | Have 2–3 modules (StockFlow, BMS, GrowFinance) publish initial manifests | Manifest examples |

### Success Criteria

- PlatformContext flows through every request, event, and queue job
- No application imports Core models from outside Core
- ModuleDiscovery can enumerate installed modules and their manifests
- TenantAwareRepository is ready for use

---

## Phase 2: Event Infrastructure

**Duration:** 3 weeks  
**Dependency:** Phase 1 (needs PlatformContext)  
**Goal:** Deploy the event envelope, event bus, and first domain events

### 2.1 Event Envelope & Bus

| # | Task | Deliverable |
|---|------|-------------|
| 2.1.1 | Create `PlatformEvent` class with all envelope fields (§10.1) | Envelope class |
| 2.1.2 | Create `EventDispatcher` service that auto-injects PlatformContext | Dispatch service |
| 2.1.3 | Add `event_version`, `correlation_id`, `causation_id` to envelope | Full metadata |
| 2.1.4 | Adopt dot-notation event naming with version (`bms.invoice.created.v1`) | Naming convention |
| 2.1.5 | Update Laravel `EventServiceProvider` or create custom bus | Bus registration |

### 2.2 Event Ownership Registry

| # | Task | Deliverable |
|---|------|-------------|
| 2.2.1 | Create `EventOwnershipRegistry` with mapping from §10.7 | Registry |
| 2.2.2 | Add validation: only the owning module may dispatch an event | Guard |
| 2.2.3 | Log ownership violations with stack traces | Monitoring |

### 2.3 First Domain Events

| # | Task | Deliverable |
|---|------|-------------|
| 2.3.1 | `OrganizationCreated` — Platform Core lifecycle event | Event + listener |
| 2.3.2 | `ApplicationEnabled` — Platform Core lifecycle event | Event + listener |
| 2.3.3 | `InvoiceCreated` — BMS domain event | Event + listener |
| 2.3.4 | `GoodsReceived` — StockFlow domain event | Event + listener |
| 2.3.5 | `PaymentReceived` — GrowFinance domain event | Event + listener |
| 2.3.6 | Move all existing event listeners to consuming module's ServiceProvider | Listener ownership |

### 2.4 Event Naming Migration

| # | Task | Deliverable |
|---|------|-------------|
| 2.4.1 | Rename existing events to dot-notation with v1 | Migration script |
| 2.4.2 | Add backward-compatible aliases for old event names | BC layer |
| 2.4.3 | Update all `->dispatch()` calls to use new names | Code update |

### Success Criteria

- Events carry full envelope with version, correlationId, causationId
- Event ownership registry prevents unauthorized publishing
- At least 5 events are flowing through the new envelope
- Listeners are owned by consuming modules

---

## Phase 3: Integration Contracts

**Duration:** 4 weeks  
**Dependency:** Phase 1 (needs PlatformContext + Core)  
**Goal:** Deploy the IntegrationRegistry, IntegrationGuard, and first contracts

### 3.1 IntegrationRegistry

| # | Task | Deliverable |
|---|------|-------------|
| 3.1.1 | Create `IntegrationRegistry` service that resolves contracts by capability | Registry |
| 3.1.2 | Create base `ProviderContract` interface in Core (§13.1) | Base contract |
| 3.1.3 | Create `ContractResolver` that uses manifests to find implementations | Resolver |
| 3.1.4 | Register contract implementations in each module's ServiceProvider | DI wiring |

### 3.2 IntegrationGuard & ContractInvoker

Per ADR-009, authorization and resilience are separate concerns:

| # | Task | Deliverable |
|---|------|-------------|
| 3.2.1 | Create `IntegrationGuard` service (§14.1) — authorization only | Guard |
| 3.2.2 | Implement authorization checks: authenticated, org member, app active, permission, feature flag | Auth chain |
| 3.2.3 | Return explicit denial before contract implementation is reached | Fail-fast |
| 3.2.4 | Log all authorization failures | Audit trail |
| 3.2.5 | Create `ContractInvoker` service — circuit breaker, retry, fallback (§20) | Invoker |
| 3.2.6 | Wire ContractInvoker into IntegrationGuard's authorized path (guard → invoker → impl) | Pipeline |
| 3.2.7 | Allow trusted internal callers to bypass IntegrationGuard but retain ContractInvoker | Bypass support |

### 3.3 First Contracts

| # | Task | Deliverable |
|---|------|-------------|
| 3.3.1 | `NotificationProvider` in Core\Contracts (platform-wide) | Interface + BMS impl |
| 3.3.2 | `MediaProvider` in Core\Contracts (platform-wide) | Interface + Storage impl |
| 3.3.3 | `InventoryProvider` in StockFlow\Contracts | Interface + impl |
| 3.3.4 | `AccountingProvider` in GrowFinance\Contracts | Interface + impl |
| 3.3.5 | Replace existing direct service calls with contract-based resolution | Migration |

### 3.4 Contract Compatibility Rules

| # | Task | Deliverable |
|---|------|-------------|
| 3.4.1 | Document compatible vs breaking changes in CONTRIBUTING.md (§13.7) | Developer docs |
| 3.4.2 | Add CI lint check: contract interface changes must be reviewed | CI check |
| 3.4.3 | Create contract versioning convention (InventoryProviderV2 pattern) | Versioning guide |

### Success Criteria

- IntegrationRegistry resolves 4+ contracts by capability name
- IntegrationGuard enforces all security checks before contract execution
- No direct `app(Service::class)` calls remain between modules
- Compatibility rules are documented and enforced in CI

---

## Phase 4: Platform Integration Services

**Duration:** 4 weeks  
**Dependency:** Phase 1 (needs Core + Runtime)  
**Goal:** Deploy ApplicationProvisioningService, CapabilityRegistry, FeatureFlagService, HealthService (architecture doc §23 Phase 2 items)

### 4.1 ApplicationProvisioningService

| # | Task | Deliverable |
|---|------|-------------|
| 4.1.1 | Create `ApplicationProvisioningService` with full lifecycle (§4.1) | Service |
| 4.1.2 | Implement `enable()` method with provisioning pipeline | Enable flow |
| 4.1.3 | Implement `disable()` method with teardown | Disable flow |
| 4.1.4 | Fire `ApplicationEnabled` / `ApplicationDisabled` lifecycle events | Events |
| 4.1.5 | Create provisioning state machine (installing → configuring → active → disabled) | State machine |

### 4.2 CapabilityRegistry

| # | Task | Deliverable |
|---|------|-------------|
| 4.2.1 | Create `CapabilityRegistry` that indexes manifests by capability (§4.5) | Registry |
| 4.2.2 | Implement `findProviders(capability): ContractInterface[]` | Capability lookup |
| 4.2.3 | Implement `hasCapability(application, capability): bool` | Capability check |

### 4.3 FeatureFlagService

| # | Task | Deliverable |
|---|------|-------------|
| 4.3.1 | Create `FeatureFlagService` (§4.8) | Service |
| 4.3.2 | Create `feature_flags` database table | Migration |
| 4.3.3 | Implement `isEnabled(flag, context): bool` with org-level overrides | Flag resolution |
| 4.3.4 | Integrate with IntegrationGuard (reject if flag disabled) | Guard integration |

### 4.4 HealthService

| # | Task | Deliverable |
|---|------|-------------|
| 4.4.1 | Create `HealthService` interface with 5 states (§4.9) | Interface |
| 4.4.2 | Create `HealthStatus` enum (Healthy, Degraded, Maintenance, Unavailable, Offline) | Enum |
| 4.4.3 | Implement local health checks (database, queue, service availability) | Local checks |
| 4.4.4 | Expose `/health` endpoint per application | Health endpoint |
| 4.4.5 | Create health dashboard view for platform administrators | Dashboard |

### 4.5 Application Manifest Adoption

| # | Task | Deliverable |
|---|------|-------------|
| 4.5.1 | All modules publish complete manifests (§4.6) | Full manifest coverage |
| 4.5.2 | Add `min_platform_version` and `max_platform_version` constraints to each manifest | Version constraint |
| 4.5.3 | Add `permissions`, `settings`, `health_checks` to manifests | Enhanced manifest |
| 4.5.4 | Validate manifests at boot time (ModuleDiscovery validation) | Boot validation |

### Success Criteria

- Full application lifecycle (enable → configure → active → disable) works
- Feature flags can toggle behavior per organization
- Health dashboard shows all applications with their status
- Every module publishes a validated manifest

---

## Phase 5: Operational Readiness

**Duration:** 3 weeks  
**Dependency:** Phases 2–4 (needs events, contracts, services running)  
**Goal:** Deploy observability, alerting, error taxonomy, retry/queue policies

### 5.1 Integration Observability

| # | Task | Deliverable |
|---|------|-------------|
| 5.1.1 | Create integration monitoring dashboard (§16.1) | Dashboard |
| 5.1.2 | Track events published/failed per hour | Metrics |
| 5.1.3 | Track retry queue depth and dead letter queue count | Metrics |
| 5.1.4 | Track average event processing time | Metrics |
| 5.1.5 | Track contract call success rate and slowest calls | Metrics |

### 5.2 Dead Letter Handling

| # | Task | Deliverable |
|---|------|-------------|
| 5.2.1 | Create failed event storage (dead letter queue) | DLQ |
| 5.2.2 | Implement event replay from dead letter queue | Replay |
| 5.2.3 | Add admin UI for viewing and replaying failed events | Admin UI |
| 5.2.4 | Configure alerting thresholds (§16.3) | Alerts |

### 5.3 Retry & Queue Policy Enforcement

| # | Task | Deliverable |
|---|------|-------------|
| 5.3.1 | Implement retry with exponential backoff (§15.1) | Retry logic |
| 5.3.2 | Configure dead letter queue with 7-day retention (§15.2) | DLQ config |
| 5.3.3 | Set listener timeout to 60 seconds (§15.3) | Timeout config |
| 5.3.4 | Configure separate queue per application (§15.2) | Queue isolation |

### 5.4 Error Taxonomy Adoption

| # | Task | Deliverable |
|---|------|-------------|
| 5.4.1 | Create base exception classes in `App\Domain\Core\Exceptions\` (§22.1) | Exception classes |
| 5.4.2 | Audit existing exceptions and migrate to standard types | Migration |
| 5.4.3 | Add retry behavior mapping (validation → no retry, transient → retry) | Retry mapping |
| 5.4.4 | Document error taxonomy in CONTRIBUTING.md | Developer docs |

### 5.5 Alerting

| # | Task | Deliverable |
|---|------|-------------|
| 5.5.1 | Configure >5% failure rate alert | Alert |
| 5.5.2 | Configure dead letter queue non-empty alert | Alert |
| 5.5.3 | Configure listener offline >5min alert | Alert |
| 5.5.4 | Configure queue backlog >1000 alert | Alert |

### Success Criteria

- Dashboard shows live integration metrics for all modules
- Dead letter queue captures and replays failed events
- All modules use the standard error taxonomy
- Alerts fire for all defined threshold violations

---

## Phase 6: Data Governance & Tenant Isolation

**Duration:** 3 weeks  
**Dependency:** Phase 1 (needs PlatformContext + TenantAwareRepository)  
**Goal:** Harden tenant isolation, enforce data ownership, deploy config hierarchy

### 6.1 Tenant Isolation Hardening

| # | Task | Deliverable |
|---|------|-------------|
| 6.1.1 | Audit every query for `organization_id` scope | Query audit |
| 6.1.2 | Fix unscoped queries (add missing `where organization_id = ?`) | Fixes |
| 6.1.3 | Add `organization_id` to queue job serialization | Queue scope |
| 6.1.4 | Add organization prefix to cache keys | Cache isolation |
| 6.1.5 | Ensure background jobs restore PlatformContext before execution | Job middleware |
| 6.1.6 | Document tenant isolation rules in CONTRIBUTING.md (§18) | Developer docs |

### 6.2 Data Ownership Enforcement

| # | Task | Deliverable |
|---|------|-------------|
| 6.2.1 | Audit all `INSERT`, `UPDATE`, `DELETE` statements against ownership table (§17.1) | Write audit |
| 6.2.2 | Fix violations (move writes to owning module) | Fixes |
| 6.2.3 | Add CI check: new migration must not touch another module's table | CI check |
| 6.2.4 | Create reporting database user with read-only access to all tables | Reporting access |

### 6.3 Configuration Strategy

| # | Task | Deliverable |
|---|------|-------------|
| 6.3.1 | Create `platform_settings` table for platform-level config | Migration |
| 6.3.2 | Create `organization_settings` table for org-level config | Migration |
| 6.3.3 | Create `application_settings` table for app-level config | Migration |
| 6.3.4 | Create `SettingsResolver` service with hierarchical resolution (§21.3) | Service |
| 6.3.5 | Migrate existing config values to the new hierarchy | Migration |

### 6.4 Anti-Corruption Layer Pattern

| # | Task | Deliverable |
|---|------|-------------|
| 6.4.1 | Document ACL pattern in CONTRIBUTING.md (§19) | Developer docs |
| 6.4.2 | Create ACL directory structure template | Template |
| 6.4.3 | Refactor existing Stripe/PayPal integrations into ACL pattern | Refactor |
| 6.4.4 | Wrap external exceptions in `IntegrationException` | Exception wrapping |

### Success Criteria

- Every query is scoped by organization_id
- No module writes to another module's tables
- Configuration is resolved hierarchically (Platform → Org → App → User)
- External integrations use the ACL pattern

---

## Phase 7: Reliable Event Delivery (v2)

**Duration:** 3 weeks  
**Dependency:** Phase 2 (needs events running)  
**Goal:** Deploy Transactional Outbox, Inbox Pattern, Event Replay

### 7.1 Transactional Outbox

| # | Task | Deliverable |
|---|------|-------------|
| 7.1.1 | Create outbox table migration per module (`{module}_event_outbox`) | Migration |
| 7.1.2 | Create `OutboxService` that inserts events atomically with business transaction | Service |
| 7.1.3 | Create outbox worker that publishes pending events | Worker/command |
| 7.1.4 | Implement outbox cleanup (archive published events older than 7 days) | Cleanup |
| 7.1.5 | Add monitoring: outbox queue depth per module | Metrics |

### 7.2 Inbox Pattern (Idempotent Processing)

| # | Task | Deliverable |
|---|------|-------------|
| 7.2.1 | Create inbox table migration per module (`{module}_event_inbox`) | Migration |
| 7.2.2 | Create `InboxService` that checks/saves event_id before processing | Service |
| 7.2.3 | Implement idempotency guard in base event listener | Base listener |
| 7.2.4 | Add monitoring: duplicate event rate | Metrics |

### 7.3 Event Replay

| # | Task | Deliverable |
|---|------|-------------|
| 7.3.1 | Create `EventReplayService` with date range + event name filter | Service |
| 7.3.2 | Create artisan command: `platform:replay-events {--from=} {--event=}` | Command |
| 7.3.3 | Add admin UI for event replay | Admin UI |
| 7.3.4 | Document replay procedure in runbook | Runbook |

### 7.4 Outbox Adoption (Financial Events First)

| # | Task | Deliverable |
|---|------|-------------|
| 7.4.1 | Wire outbox for `bms.invoice.*` events (required per FUTURE_VISION.md §1.4) | Adoption |
| 7.4.2 | Wire outbox for `stockflow.goods_received.*` (required) | Adoption |
| 7.4.3 | Wire outbox for `growfinance.payment.*` (required) | Adoption |
| 7.4.4 | Wire inbox for consuming modules (GrowFinance listens to invoices, etc.) | Adoption |

### Success Criteria

- Financial events use outbox (no lost events on crash)
- Idempotent processing prevents duplicate event handling
- Admin can replay events by date range and event name
- All "Required" events from FUTURE_VISION.md §1.4 use the outbox

---

## Phase 8: Independent Deployment Readiness

**Duration:** 4 weeks  
**Dependency:** Phases 2–7  
**Goal:** Validate that every module can be extracted as an independent service

### 8.1 Contract Versioning Exercise

| # | Task | Deliverable |
|---|------|-------------|
| 8.1.1 | Introduce breaking change to one contract (e.g., InventoryProviderV2) | Versioned contract |
| 8.1.2 | Add v2 alongside v1 with both implementations | Coexistence |
| 8.1.3 | Verify IntegrationRegistry resolves correct version per consumer | Registry test |
| 8.1.4 | Document contract versioning playbook | Playbook |

### 8.2 Remote Contract Resolution (Design)

| # | Task | Deliverable |
|---|------|-------------|
| 8.2.1 | Design remote contract resolution flow (HTTP client → Gateway → Implementation) | Design doc |
| 8.2.2 | Create HTTP client implementation of one contract (e.g., `InventoryProviderHttpImpl`) | HTTP impl |
| 8.2.3 | Create API Gateway design for contract routing | Gateway design |
| 8.2.4 | Benchmark local vs remote contract resolution | Benchmark |

### 8.3 Event Transport (Design)

| # | Task | Deliverable |
|---|------|-------------|
| 8.3.1 | Design cross-process event transport (RabbitMQ/SQS adapter) | Design doc |
| 8.3.2 | Create event serializer for remote transport | Serializer |
| 8.3.3 | Design event subscription discovery for remote listeners | Subscription design |

### 8.4 Extraction Dry Run

| # | Task | Deliverable |
|---|------|-------------|
| 8.4.1 | Extract one module (e.g., StockFlow) to separate directory | Extraction |
| 8.4.2 | Verify all integration points use contracts/events only | Verification |
| 8.4.3 | Document what changes if deployed independently (configuration only) | Extraction docs |
| 8.4.4 | Identify remaining coupling issues and create remediation plan | Gap analysis |

### 8.5 Final Governance

| # | Task | Deliverable |
|---|------|-------------|
| 8.5.1 | Create ADR template and repository | ADR process |
| 8.5.2 | Hold architecture review of all modules | Review |
| 8.5.3 | Update `CONTRIBUTING.md` with all rules from §§14–21 | Final guidelines |
| 8.5.4 | Create `ARCHITECTURE_CHECKS.md` for automated CI enforcement | CI checks |

### Success Criteria

- One module can be extracted and verified with no domain logic changes
- Contract versioning works end-to-end with consumer coexistence
- Remote transport is designed and ready for implementation
- All integration rules are documented and enforced in CI

---

## Dependency Graph

```
Phase 0 (Audit)
    │
    ▼
Phase 1 (Core + Runtime)
    │
    ├──────────────────┐
    ▼                  ▼
Phase 2 (Events)   Phase 3 (Contracts)   Phase 4 (Platform Integration Svc)
    │                  │                       │
    └────────┬─────────┘                       │
              ▼                                 │
        Phase 5 (Operations) ◄──────────────────┘
             │
             ▼
       Phase 6 (Data Governance)
             │
             ▼
       Phase 7 (Reliable Events v2)
             │
             ▼
       Phase 8 (Independent Deployment)
```

Phases 2, 3, and 4 can run **concurrently** after Phase 1 is complete. Phase 5 requires all three. Phases 6 and 7 build on earlier work and can partially overlap.

**Note on Phase 2 vs architecture doc's "Phase 2":** The architecture document (§23) numbers its build-out phases 1–4 (Foundation, Runtime Layer & Platform Integration Services, Events, Contracts). This implementation plan splits the architecture doc's Phase 2 across its own Phase 1 (Runtime Layer) and Phase 4 (Platform Integration Services) for practical work breakdown. The two numbering schemes describe the same ordering — just different grouping granularity.

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Existing direct DB calls missed in audit | Medium | High | Strangler pattern — wrap after discovery |
| Team resists event ownership rules | Low | Medium | Document in CONTRIBUTING.md, enforce in CI |
| Phase 7 (outbox) adds latency to event dispatch | Low | Medium | Benchmark before/after; async worker mitigates |
| Contract versioning becomes complex | Medium | Low | Start with simple v1/v2 coexistence; iterate |
| Modules cannot be extracted without changes | Medium | High | Identify in Phase 8 dry run; fix coupling iteratively |
| Developer friction from new patterns | Medium | Medium | Pair programming sessions, ADR reviews, gradual adoption |

---

## Quick Wins (Weeks 1–2)

These can be started immediately without blocking on other phases:

1. **PlatformContext middleware** — Define the class, add to middleware stack. No other changes needed.
2. **Event naming convention** — Start using `module.entity.action.v1` in new events.
3. **TenantAwareRepository** — Create the base class; modules adopt it incrementally.
4. **Error taxonomy classes** — Create the 8 exception types; modules start using them.
5. **Data ownership table** — Publish the §17.1 table as a reference; no code changes needed.
6. **ADRs** — Create ADR files for the 9 decisions in §25 (ADR-001 through ADR-009).

---

## Measuring Progress

| Metric | Target | When |
|--------|--------|------|
| Cross-module direct DB calls | 0 | Phase 3 |
| Cross-module Eloquent imports | 0 | Phase 3 |
| Events using new envelope | 100% | Phase 2 complete |
| Contracts resolved via IntegrationRegistry | 100% of defined contracts | Phase 3 complete |
| Modules with published manifests | 100% | Phase 4 complete |
| Queries scoped by organization_id | 100% | Phase 6 complete |
| Financial events using outbox | 100% | Phase 7 complete |
| Integration dashboard showing all apps | Operational | Phase 5 complete |
| Modules verified extractable | ≥1 | Phase 8 complete |
