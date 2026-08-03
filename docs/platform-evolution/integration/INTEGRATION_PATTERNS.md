# Integration Patterns

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 0.7 — Foundation Audit  
> **Applies to:** All cross-module integration in MyGrowNet

---

## Overview

The platform defines exactly **three types** of cross-module integration. Every communication between modules must use one of these patterns. Direct class imports, `app(Service::class)` calls, and raw `DB::table()` queries across module boundaries are violations.

```
Integration Types
     │
     ├── Platform Services    (synchronous, caller → Core service)
     ├── Platform Events      (async, publisher → EventBus → listeners)
     └── Integration Contracts (synchronous, caller → Registry → Provider)
```

---

## Type 1: Platform Services

**Purpose:** Synchronous calls to Platform Core services for platform-level concerns — identity, organizations, applications, settings, feature flags, health checks.

**Direction:** Applications call Core. Core never calls applications.

**Flow diagram:**

```
Application
    │
    ▼
app(IdentityProvider::class)
    │
    ▼
IntegrationRegistry::resolve()
    │
    ▼
LaravelIdentityProvider (in Core)
    │
    ▼
User/Eloquent model
```

**Examples from the codebase:**

```php
// Resolving the current user's identity
$identity = app(IdentityProvider::class);
$user = $identity->findById($userId);

// Checking feature flags
$features = app(FeatureFlagService::class);
if ($features->isEnabled('new_dashboard', $context)) { ... }

// Checking application health
$health = app(HealthService::class);
if ($health->isHealthy('stockflow')) { ... }

// Reading platform settings
$settings = app(SettingsService::class);
$timeout = $settings->get('contract_timeout_ms', module: 'platform-core');
```

**When to use:**
- You need platform data (users, orgs, apps, settings)
- You need authorization checks
- You need feature flag or health checks

**When NOT to use:**
- Communicating between two application modules (use Type 2 or Type 3)
- Business logic that belongs in a domain service

---

## Type 2: Platform Events

**Purpose:** Asynchronous announcements. A module publishes an event; zero or more subscribers react. The publisher never knows who is listening.

**Flow diagram:**

```
Publisher (StockFlow)
    │
    ├── EventDispatcher::dispatch('stockflow.goods_received', $payload)
    │       │
    │       ▼
    │   EventOwnershipRegistry → validates publisher owns this event
    │       │
    │       ▼
    │   PlatformEvent (envelope)
    │       │
    │       ├── OutboxService (Phase 7) — transactional outbox
    │       │
    │       ▼
    │   Laravel Event::dispatch()
    │       │
    │       ▼
    └── Subscribers
            │
            ├── BMS listener (sync invoice status)
            ├── GrowFinance listener (create journal entry)
            └── Audit logger (record event)
```

**Examples from the codebase:**

```php
// Publishing (in StockFlow PurchasingService)
$dispatcher = app(EventDispatcher::class);
$dispatcher->dispatch('stockflow.goods_received', [
    'company_id' => $companyId,
    'purchase_order_id' => $po->id,
    'order_number' => $po->order_number,
    'received_by' => auth()->id(),
    'items' => $items,
]);

// Publishing lifecycle events (in ApplicationProvisioningService)
ApplicationEnabled::dispatch($org, (string) $app->id, $app->name);

// Subscribing (in EventServiceProvider)
protected $listen = [
    'stockflow.goods_received' => [
        UpdateInventoryLedger::class,
        NotifyPurchasingManager::class,
    ],
    'bms.invoice.created' => [
        InvoiceCreatedListener::class,  // ShouldQueue
    ],
];
```

**When to use:**
- One module needs to react to something that happened in another module
- The reaction can be asynchronous (queueable)
- Multiple modules need to react to the same event
- You want loose coupling — the publisher doesn't need to know who cares

**When NOT to use:**
- You need a return value from the reaction
- Performance requires synchronous processing (use Type 3 instead)
- The reaction must happen in the same database transaction

---

## Type 3: Integration Contracts

**Purpose:** Synchronous queries or commands between application modules, resolved by capability rather than by module name. The caller depends on an interface, not on the implementing module.

**Flow diagram:**

```
Caller (BMS needs inventory data)
    │
    ▼
IntegrationRegistry::resolve(InventoryProvider::class)
    │
    ├── ModuleDiscovery → finds which module provides "inventory"
    │
    ▼
IntegrationGuard
    ├── requireAuthenticated($context)
    ├── requireOrganizationMember($context)
    ├── requireHealthy($context)
    └── requireFeatureEnabled($context, contract)
    │
    ▼
ContractInvoker
    ├── Circuit breaker check (open? → fallback or throw)
    ├── attemptWithRetry() (3 retries with exponential backoff)
    │       │
    │       └── on failure:
    │             ├── Record metric
    │             ├── Capture to DeadLetterService
    │             └── Call fallback if provided
    │
    ▼
Provider Implementation (StockFlow's InventoryProviderImpl)
    │
    ▼
    Returns stock level / item detail
```

**Examples from the codebase:**

```php
// Interface (defined in StockFlow, used by BMS)
interface InventoryProvider extends ProviderContract
{
    public function getStockLevel(PlatformContext $context, int $itemId): int;
    public function getMovements(PlatformContext $context, int $itemId, string $from, string $to): array;
    public function getItemDetail(PlatformContext $context, int $itemId): array;
    public function reserveStock(PlatformContext $context, int $itemId, int $quantity): bool;
}

// Calling code (BMS IntegrationService)
$inventory = app(IntegrationRegistry::class)->resolve(InventoryProvider::class);
$stockLevel = $inventory->getStockLevel($context, $itemId);

// With circuit breaker and fallback
$invoker = app(ContractInvoker::class);
$result = $invoker->call(
    InventoryProvider::class,
    'getStockLevel',
    [$context, $itemId],
    fallback: fn() => 0,  // assume zero if unavailable
);
```

**When to use:**
- You need data or an action from another module's domain
- You need a synchronous response
- The capability should be swappable (different provider in the future)
- You want formal versioning and backward compatibility

**When NOT to use:**
- You need platform data (use Type 1 — Platform Services)
- You just need to announce something (use Type 2 — Platform Events)
- The operation is long-running or doesn't need an immediate response

---

## IntegrationGuard Flow (Full Pipeline)

```
Request arrives
    │
    ▼
IntegrationGuard::authorize($context, $contractClass)
    │
    ├── 1. requireAuthenticated()
    │       └── Is userId non-empty?
    │             ├── Yes → continue
    │             └── No  → throw AuthorizationException
    │
    ├── 2. requireOrganizationMember()
    │       └── If orgId present: does user belong to org?
    │             ├── Yes → continue
    │             └── No  → throw AuthorizationException
    │
    ├── 3. requireHealthy()
    │       └── Is the target application healthy?
    │             ├── Yes → continue
    │             └── No  → throw AuthorizationException
    │
    └── 4. requireFeatureEnabled()
            └── Is the contract's feature flag enabled?
                  ├── Yes → continue
                  └── No  → throw AuthorizationException
    │
    ▼
IntegrationRegistry::resolve($contractClass)
    │
    ├── Cache hit? → return resolved provider
    ├── Contract extends ProviderContract? → no: throw ConfigurationException
    ├── Container has binding? → no: check ModuleDiscovery
    │     └── Manifest declares contract? → no: throw NotFoundException
    └── Resolve from container → cache → return provider
    │
    ▼
ContractInvoker::call($contractClass, $method, $args, $fallback?)
    │
    ├── Circuit breaker open? → fallback or throw ServiceUnavailableException
    │
    ├── Try $provider->$method(...$args)
    │     ├── Success → record metric, close circuit
    │     └── RetryableException → retry (3x, exponential backoff)
    │           └── All retries exhausted → record failure
    │                 ├── Circuit breaker: 5 failures → open (30s cooldown)
    │                 ├── Capture to DeadLetterService
    │                 ├── Log warning
    │                 └── Fallback or rethrow
    │
    └── NonRetryableException → rethrow immediately
```

---

## Decision Matrix

| If you need to... | Use Pattern | Example |
|---|---|---|
| Get the current user's organizations | Type 1 — Platform Service | `app(OrganizationService::class)` |
| Announce an invoice was paid | Type 2 — Platform Event | `InvoicePaid::dispatch(...)` |
| Check stock level of an item | Type 3 — Integration Contract | `InventoryProvider::getStockLevel()` |
| Create a new organization | Type 1 — Platform Service | `OrganizationService::create()` |
| Sync invoice to accounting | Type 2 — Platform Event | Listener on `bms.invoice.created` |
| Reserve inventory for an order | Type 3 — Integration Contract | `InventoryProvider::reserveStock()` |
| Check if a feature is enabled | Type 1 — Platform Service | `FeatureFlagService::isEnabled()` |
| React to application being enabled | Type 2 — Platform Event | Listener on `platform.application.enabled` |
| Get account balance for a customer | Type 3 — Integration Contract | `AccountingProvider::getBalance()` |

---

## Rules

1. No module imports another module's Eloquent model directly
2. No module calls `app(Service::class)` on a service owned by another module
3. No module writes to another module's database tables
4. All cross-module communication uses one of the three integration types above
5. Integration Contracts always go through IntegrationGuard → Registry → Invoker
6. Platform Events always go through EventOwnershipRegistry validation
7. Platform Services are the only way to access Core data from application modules
