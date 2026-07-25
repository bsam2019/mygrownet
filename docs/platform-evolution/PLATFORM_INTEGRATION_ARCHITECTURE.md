# MyGrowNet Platform Integration Architecture

> **Status:** Architecture Reference  
> **Version:** 9.0  
> **Applies to:** All MyGrowNet platform applications and modules

---

## Table of Contents

1. [Architectural Principles](#1-architectural-principles)
 2. [Platform Core — Governance and Infrastructure Layer](#2-platform-core--governance-and-infrastructure-layer)
3. [Three Types of Integration](#3-three-types-of-integration)
    - [Type 1: Platform Services](#type-1-platform-services)
    - [Type 2: Platform Events](#type-2-platform-events)
    - [Type 3: Integration Contracts](#type-3-integration-contracts)
 4. [Platform Integration Layer](#4-platform-integration-layer)

   - [ApplicationProvisioningService](#41-applicationprovisioningservice)
   - [EventBus](#42-eventbus)
   - [IntegrationRegistry](#43-integrationregistry)
   - [ModuleDiscovery & Capability Registry](#44-modulediscovery--capability-registry)
   - [Application Manifest](#45-application-manifest)
   - [ServiceContracts](#46-servicecontracts)
   - [FeatureFlagService](#47-featureflagservice)
   - [HealthService](#48-healthservice)
5. [Application Runtime Layer](#5-application-runtime-layer)
6. [Module Lifecycle](#6-module-lifecycle)
7. [PlatformContext](#7-platformcontext)
8. [Dependency Rules](#8-dependency-rules)
9. [Standard Platform Events](#9-standard-platform-events)
10. [Integration Examples](#10-integration-examples)
    - [Application Provisioning](#101-application-provisioning)
    - [Invoice Processing](#102-invoice-processing)
    - [Inventory Accounting](#103-inventory-accounting)
    - [GrowFinance Full Invoice Lifecycle](#104-growfinance-full-invoice-lifecycle)
11. [Platform Contracts](#11-platform-contracts)
12. [Integration Security](#12-integration-security)
13. [Integration Policies](#13-integration-policies)
14. [Integration Rules](#14-integration-rules)
22. [Future Vision](#22-future-vision)
23. [Migration Strategy](#23-migration-strategy)
24. [Architectural Decision Records](#24-architectural-decision-records)

---

## 1. Architectural Principles

MyGrowNet is evolving into a **modular monolith** — every application is an independent bounded context designed so that it can be extracted and independently deployed in the future. The integration architecture enforces the following principles:

### 1.1 Module Independence

Every module owns:

- **its tables and persistence model** — schemas, indexes, migrations
- **its business rules** — domain entities, services, invariants
- **its services** — both internal and published contracts
- **its UI** — routes, controllers, views
- **its events** — what it announces and what it listens to

**Core rule:** No application writes directly into another application's tables.

```
Incorrect:

  BMS
   │
   ▼
  INSERT INTO sa_items          ← StockFlow's table

Correct:

  BMS
   │
   ▼
  ApplicationEnabled Event
   │
   ▼
  StockFlow Listener
   │
   ▼
  StockFlow creates its own records in sa_items
```

### 1.2 Application Data Ownership

A clear boundary must be maintained between platform data and application data.

**Platform Core owns only platform data:**

| Data | Purpose |
|------|---------|
| Users | Identity and authentication |
| Organizations | Tenant and organizational structure |
| Applications | Application registry and metadata |
| Application Installations | Per-org provisioning state |
| Authorization Framework | Permission engine, roles, assignment, authorization checks |
| Subscriptions | Licensing and billing |
| Settings | Platform-level configuration |
| Audit Logs | Cross-application audit trail |

**Applications own all business data:**

| Module | Owns |
|--------|------|
| BMS | Companies, jobs, invoices, HR records, payroll |
| StockFlow | Items, warehouses, suppliers, purchase orders, audits |
| GrowFinance | Chart of accounts, journals, budgets, financial reports |
| GrowNet | Members, commissions, tiers, referral network |
| GrowMart | Products, orders, cart, inventory |
| BizBoost | Campaigns, leads, marketing metrics |

**Permission ownership:**

The Platform Core owns the **authorization framework** — the permission engine, role definitions, assignment infrastructure, and authorization checks. Applications own their **permission vocabulary** — the specific permissions that make sense within their domain.

```
Platform Core:
  authorization framework
  role definitions (admin, manager, viewer)
  permission assignment

StockFlow:
  inventory.view
  inventory.adjust
  warehouse.manage
  purchase.approve

GrowFinance:
  accounts.create
  journal.post
  financial.report.view
```

This prevents Platform Core from needing to know every application's business permissions.

**Rule:** Platform Core migrations never create business tables. Application migrations never create platform tables.

### 1.3 Shared Data vs Owned Data

Some entities are referenced by multiple applications. These must have a single authoritative owner.

| Entity | Owner | Consumers |
|--------|-------|-----------|
| User | Platform Core (Identity) | All applications |
| Organization | Platform Core | All applications |
| Customer | CRM module (future) | BMS, GrowMart, BizBoost |
| Supplier | StockFlow (future: CRM) | Purchasing modules |
| Product | Product Catalog module (future) | GrowMart, POS, StockFlow, BMS |
| Invoice | Billing module (future) | GrowFinance, BizBoost, BMS |

**Ownership rules:**

- Every entity has exactly one authoritative owner. Ambiguous ownership ("Supplier: StockFlow / BMS") eventually leads to synchronization problems — two sources of truth.
- **Customers** should be owned by a dedicated CRM module. This avoids BMS becoming a bottleneck and allows GrowMart, BizBoost, and future applications to consume customer data through `CustomerProvider` without BMS coupling.
  - **Migration note:** Existing customer records remain owned by their current modules (BMS, BizBoost, etc.) until CRM migration is completed. During transition, synchronization adapters may exist temporarily. Do not attempt to move customer data before the CRM module exists.
- **Suppliers** should follow the same pattern. A CRM or Supplier Management module becomes the single source of truth, with StockFlow and BMS consuming via `SupplierProvider`.
  - **Note:** CRM, Product Catalog, and Billing are not optional modules — they become foundational platform-level business applications. Just as Platform Core provides identity and organization services, these business applications provide customer, product, and billing services that every other application depends on. They should be prioritized accordingly.
- **Products** require a dedicated Product Catalog module managing categories, variants, pricing, and inventory metadata. GrowMart, POS, StockFlow, and BMS all consume via `ProductProvider`.
- **Invoices** are multi-faceted. A future Billing module should own the sales invoice record; GrowFinance owns the accounting entry (debits/credits); and a Payment module owns the transaction (gateway, receipt, settlement). These are three different concerns:
  - **Sales Invoice** (future Billing module) — customer, items, due date, status
  - **Accounting Entry** (GrowFinance) — debit, credit, ledger impact
  - **Payment Transaction** (future Billing/Payment module) — gateway, receipt, settlement
  - For now, BMS holds the sales invoice record. GrowFinance owns the accounting treatment. Do not let GrowFinance own the invoice itself.

### 1.4 Ownership by Convention

| Owns | BMS | StockFlow | GrowFinance | GrowNet | GrowMart |
|------|-----|-----------|-------------|---------|----------|
| Tables | `cms_*`, `jobs`, `invoices` | `sa_*` | `gf_*` | `grow_net_*` | `gm_*` |
| Domain logic | HR, projects, jobs | Inventory, audits | Accounting, budgets | MLM, commissions | Products, orders |
| Events | `InvoiceCreated` | `GoodsReceived` | `PaymentReceived` | `CommissionAwarded` | `OrderPlaced` |

### 1.5 Communication via Platform-Managed Mechanisms

Applications never communicate through direct implementation dependencies. All cross-application communication uses platform-managed integration mechanisms such as contracts, events, and runtime services. The Platform Core manages the registry, security, identity, and integration infrastructure — it does not act as a mandatory relay for every message.

### 1.6 Application Boundary

Every application on the MyGrowNet platform follows a consistent boundary structure. This makes future extraction, testing, and independent deployment predictable.

```
StockFlow
 │
 ├── Domain
 │    ├── Inventory
 │    ├── Purchasing
 │    └── Warehousing
 │
 ├── Contracts             ← interfaces this application provides
 │    ├── InventoryProvider.php
 │    └── SupplierProvider.php
 │
 ├── Events                ← domain events this application publishes
 │    ├── GoodsReceived.php
 │    └── StockAdjusted.php
 │
 ├── API                   ← HTTP endpoints exposed by this application
 │    └── InventoryController.php
 │
 └── Infrastructure        ← implementations of contracts and persistence
      ├── InventoryProviderImpl.php
      └── EloquentItemModel.php
```

**Boundary rules:**

- An application's `Domain` layer never imports from another application. It only imports from `Platform Core` (base contracts, PlatformContext, value objects).
- An application's `Contracts` namespace defines the interfaces other applications can depend on.
- An application's `Events` namespace defines the domain events it publishes. Subscribers import the event class directly.
- An application's `Infrastructure` namespace is the only layer that touches framework concerns (Eloquent, HTTP clients). It can be swapped out entirely for independent deployment.

**API boundaries:**

Every application may expose three categories of API:

```
StockFlow
 │
 ├── api/internal/       ← Used only within MyGrowNet (other applications)
 │    └── inventory
 │
 ├── api/integration/    ← Used for cross-application contract resolution
 │    └── contracts/inventory
 │
 └── api/external/       ← Used by third-party consumers, mobile apps
      └── v1/products
```

- **Internal API** — Consumed by other MyGrowNet applications. Authenticated with platform context. No versioning required (applications deploy together).
- **Integration API** — Used by the IntegrationRegistry for remote contract resolution (future, distributed stage). Implements the same interface as the local contract.
- **External API** — Consumed by third parties, mobile apps, external integrations. Must be versioned (`v1`, `v2`) and documented. Rate-limited and API-key authenticated.

---

## 2. Platform Core — Governance and Infrastructure Layer

The Platform Core is the governance layer and infrastructure provider of MyGrowNet. It provides the services that all applications share — identity, organizations, permissions, application registry, event infrastructure, contract discovery, and runtime context. It does not act as a mandatory relay for every message; applications communicate directly through events and contracts, with the Platform Core governing the mechanisms.

```
              MyGrowNet Platform

┌──────────────────────────────────────────────────────────┐
│                    Platform Core                          │
│                                                           │
│  Domain Layer (permanent platform foundations):           │
│  ┌──────────────────────────────────────────────────┐   │
│  │  Identity         Organizations                  │   │
│  │  Authorization    Applications                   │   │
│  │  Workspace        Settings                       │   │
│  │  Audit            Notifications                  │   │
│  └──────────────────────────────────────────────────┘   │
│                                                           │
│  Infrastructure Services (may evolve independently):     │
│  ┌──────────────────────────────────────────────────┐   │
│  │  ApplicationProvisioningService    EventBus       │   │
│  │  IntegrationRegistry              ModuleDiscovery │   │
│  │  CapabilityRegistry               ServiceContracts│   │
│  │  FeatureFlagService               HealthService   │   │
│  └──────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
                          │
┌──────────────────────────────────────────────────────────┐
│               Application Runtime Layer                   │
│                                                           │
│  Auth Adapters        Tenancy Middleware                  │
│  Subdomain Routing    Cache Layer                         │
│  API Gateway          (future distributed stage)          │
│  Event Transport      (future distributed stage)          │
│                                                           │
│  Shared infrastructure serving Core and applications      │
└──────────────────────────────────────────────────────────┘
              │                    │                    │
              ▼                    ▼                    ▼
     ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
     │   StockFlow   │   │ GrowFinance  │   │    BMS       │
     │   ─────────   │   │   ─────────  │   │   ───────    │
     │   sa_* tables │   │   gf_* tables│   │   cms_*      │
     │   Inventory   │   │   Accounting │   │   HR/Jobs    │
     └──────────────┘   └──────────────┘   └──────────────┘

     ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
     │   GrowNet     │   │   GrowMart   │   │   BizBoost   │
     │   ────────    │   │   ────────   │   │   ────────   │
     │   MLM/Tiers   │   │   Commerce   │   │   Marketing  │
     └──────────────┘   └──────────────┘   └──────────────┘
```

**Key insight:** Applications never communicate through internal implementation dependencies (importing each other's classes, querying each other's databases). They communicate through registered contracts and events governed by Platform Core infrastructure. The Platform Core provides the registry, security, and runtime context — it does not sit in the middle of every message.

---

## 3. Three Types of Integration

Cross-application communication takes exactly three forms. It is critical to keep them clearly separated: **Platform Services** are owned by the Platform Core; **Integration Contracts** are owned by individual applications.

---

### Type 1: Platform Services

Synchronous calls to the Platform Core for platform-level information. These services are **owned and implemented by the Platform Core**.

An application calls a Platform Service when it needs to know something about the platform — the current user, their organizations, available applications, etc.

**Examples (owned by Platform Core):**

```
IdentityService
OrganizationService
WorkspaceService
ApplicationRegistry
PermissionService
FeatureFlagService
HealthService
NotificationService
```

**Characteristics:**

- Synchronous request-response
- Provided and implemented by Platform Core
- Called by any application
- Never reach into another application's domain

---

### Type 2: Platform Events

Asynchronous announcements. An application publishes an event to announce that something happened. It does not tell any other application what to do.

```
  Application A                     Application B
       │                                │
       │  ─── Event ─────────────────►  │
       │                                │
       │   "Something happened"         │   "I decide what to do"
       │                                │
```

**Characteristics:**

- Fire-and-forget
- Publisher has no knowledge of subscribers
- Subscribers decide how to react
- **Lifecycle events** are defined in the Platform Core; **domain events** are defined in the owning application

**Example flow:**

```
Enable StockFlow
       │
       ▼
ApplicationEnabled Event  (Platform Core lifecycle event)
       │
       ▼
Platform Event Bus
       │
       ▼
StockFlow Listener
       │
       ├── Create company record
       ├── Create default warehouse
       ├── Create default departments
       └── Done
```

BMS does not create StockFlow records. BMS only announces that StockFlow was enabled.

---

### Type 3: Integration Contracts

Synchronous contracts that let one application query **another application's data** without knowing where it is stored. These contracts are **owned and implemented by individual applications**, with base abstractions defined in the Platform Core.

**Examples (owned by applications):**

```
InventoryProvider      ← StockFlow
AccountingProvider     ← GrowFinance
CustomerProvider       ← CRM
SupplierProvider       ← StockFlow / BMS
PayrollProvider        ← Employee
```

**The wrong way:**

```php
// BMS reads directly from StockFlow's table
$products = DB::table('sa_items')->where('company_id', $companyId)->get();
```

**The right way:**

```php
// BMS asks through a contract
$inventory = app(InventoryProvider::class);
$products = $inventory->getItems($context);
```

**Characteristics:**

- Synchronous
- Interface lives in the owning application's `Contracts/` namespace
- Base abstraction (`ProviderContract`) defined in Platform Core
- Implementation lives in the owning application
- Caller never knows the storage layer
- Default implementations can use Eloquent; independent deployment requires only a new implementation class

---

## 4. Platform Integration Layer

The Platform Core provides the following Integration services. Each is a peer capability alongside Identity, Organizations, Workspace, Notifications, and Audit.

```
Platform Core — Domain Layer
│
├── Identity
├── Organizations
├── Authorization
├── Applications
├── Workspace
├── Settings
└── Audit

Platform Core — Infrastructure Services
│
├── ApplicationProvisioningService
├── EventBus
├── IntegrationRegistry
├── ModuleDiscovery
├── CapabilityRegistry
├── ServiceContracts
├── FeatureFlagService
├── HealthService
└── PlatformContext

Application Runtime Layer
│
├── Auth Adapters
├── Tenancy Middleware
├── Subdomain Routing
├── Cache Layer
├── API Gateway               (future, distributed stage)
└── Event Transport           (future, distributed stage)
```

### 4.1 ApplicationProvisioningService

Responsible for the lifecycle of application installations within an organization.

**Responsibilities:**

- Installing an application for an organization
- Running provisioning workflows
- Firing lifecycle events at each state transition

**Provisioning states:**

Some applications (especially GrowFinance) may spend significant time provisioning resources. The service tracks granular states:

```
  Not Installed
       │
       ▼
  Installing ──► ApplicationInstalling Event
       │
       ▼
  Provisioning ──► ApplicationProvisioning Event
       │
       ▼
  Configuring ──► ApplicationConfiguring Event
       │
       ▼
    Ready
       │
       ▼
  Activating ──► ApplicationEnabled Event
       │
       ▼
   Active
       │
       ├──► Maintenance ──► MaintenanceMode Event
       │        │
       │        ▼
       │     Active (restored)
       │
       ├──► Suspended ──► ApplicationDisabled Event
       │        │
       │        ▼
       │     Active (re-enabled)
       │
       └──► Archived (terminal)
```

**Example — StockFlow provisioned:**

```
Platform:
  organization_applications
    organization_id
    application_id
    status: active
    enabled_at

StockFlow (reacts to ApplicationEnabled):
  sa_warehouses
    creates default warehouse
  sa_settings
    creates inventory preferences
```

### 4.2 EventBus

The central communication backbone for asynchronous integration.

**Responsibilities:**

- Dispatching domain events to registered listeners
- Supporting both synchronous and queued listeners
- Maintaining event ordering guarantees within a request lifecycle
- Logging all events for audit trails

**Design notes:**

- Implemented on top of Laravel's event system
- Events are plain value objects (no database dependencies)
- Listeners are registered in each module's ServiceProvider
- Queued listeners prevent cross-module failures

### 4.3 IntegrationRegistry

A registry of all available integration points.

**Responsibilities:**

- Discovering which modules publish contracts
- Validating that contract implementations satisfy their interfaces
- Providing a runtime lookup for providers

**Example lookup:**

```php
$inventory = app(InventoryProvider::class);
$stock = $inventory->getStockLevel($context, $itemId, $warehouseId);
```

### 4.4 IntegrationGuard

Separate from the registry, the IntegrationGuard handles all authorization checks for cross-application communication. This prevents the IntegrationRegistry from becoming a "god service" that both discovers and authorizes.

**Responsibilities:**

- Verifying the caller is authenticated and belongs to the organization
- Checking that the target application is enabled and active
- Validating the caller has the required permissions for the specific contract
- Enforcing feature flags and tenant isolation

**Relationship:**

```
IntegrationRegistry
    │
    └── Contract Discovery / Provider Resolution

IntegrationGuard
    │
    ├── Authentication check
    ├── Organization membership
    ├── Application status
    ├── Permission validation
    ├── Feature flag check
    └── Tenant isolation
```

**Example flow:**

```php
// IntegrationRegistry handles discovery
$registry = app(IntegrationRegistry::class);
$providerClass = $registry->resolve(InventoryProvider::class);

// IntegrationGuard handles authorization
$guard = app(IntegrationGuard::class);
$guard->authorize($context, $providerClass);

// Safe to use the provider
$inventory = app($providerClass);
$stock = $inventory->getStockLevel($context, $itemId);
```

### 4.4 ModuleDiscovery & Capability Registry

Responsible for introspecting installed modules and exposing their capabilities.

**Responsibilities:**

- Detecting which modules are installed and booted
- Exposing module metadata (name, version, capabilities, contracts, events)
- Powering the Application Catalog
- Answering capability queries

**Capability-based lookup:**

Applications expose capabilities so that other modules can ask "who provides this?" instead of "is StockFlow installed?"

```
StockFlow

Capabilities:
  inventory
  warehouses
  suppliers
  purchasing
  audits
  stock_transfers

GrowFinance

Capabilities:
  general_ledger
  accounts_receivable
  accounts_payable
  budgeting
  payroll
  assets
```

**Usage:**

```php
$discovery = app(ModuleDiscovery::class);
$capabilities = $discovery->capabilities('stockflow');
// Returns: ['inventory', 'warehouses', 'suppliers', 'purchasing', 'auditing', 'stock_transfers']

$registry = app(CapabilityRegistry::class);
$contractClass = $registry->findProvider('inventory');
// Returns InventoryProvider::class (the interface, regardless of which module implements it)

// Business code depends on the interface, not the capability string:
$inventory = app(InventoryProvider::class);
$stock = $inventory->getStockLevel($context, $itemId);
```

BMS never needs to know "is StockFlow installed?" — it asks "can this organization provide inventory?" and gets an answer regardless of which application powers it. In ten years StockFlow could be replaced with a different inventory system and BMS would never change.

```
Today:

  Inventory Capability
       │
       │
   StockFlow

Future:

  Inventory Capability
       │
       │
   ─────────────────
   │               │
 StockFlow     External ERP
```

### 4.5 Application Manifest

Every application publishes a manifest declaring its identity, capabilities, contracts, and events. This manifest is consumed by ModuleDiscovery at boot time.

**Example manifest (returned from a ServiceProvider registration):**

```php
return [
    // Identity
    'id' => 'stockflow',
    'name' => 'StockFlow',
    'version' => '2.3.0',
    'category' => 'business',
    'type' => 'tenant',
    'description' => 'Inventory management, purchasing, and auditing',
    'platform_versions' => ['>=2.0', '<4.0'],   // Platform compatibility constraint

    // Entry
    'entrypoint' => '/stock-audit',
    'icon' => 'stockflow.svg',
    'supports_subdomain' => true,
    'supports_workspace_launch' => true,
    'requires_organization' => true,

    // Permissions this application defines
    'permissions' => [
        'stockflow.view_inventory',
        'stockflow.edit_inventory',
        'stockflow.approve_purchase_orders',
        'stockflow.manage_audits',
    ],

    // Settings schema (configuration options)
    'settings' => [
        'auto_adjust_on_count' => ['type' => 'boolean', 'default' => true],
        'low_stock_threshold'  => ['type' => 'integer', 'default' => 10],
    ],

    // Health checks this application exposes
    'health_checks' => [
        'database' => ['type' => 'connection', 'critical' => true],
        'queue'    => ['type' => 'queue_depth', 'critical' => false],
    ],

    // Capabilities this application provides
    'capabilities' => [
        'inventory',
        'purchasing',
        'warehouses',
        'auditing',
        'stock_transfers',
    ],

    // Contracts this application provides
    'contracts' => [
        InventoryProvider::class,
        SupplierProvider::class,
    ],

    // Events this application publishes (domain events)
    'events' => [
        GoodsReceived::class,
        StockAdjusted::class,
        PurchaseOrderApproved::class,
    ],

    // Events this application listens to
    'listens' => [
        ApplicationEnabled::class,   // Platform lifecycle event
        OrganizationCreated::class,  // Platform lifecycle event
        InvoiceCreated::class,       // BMS domain event
    ],

    // Capabilities this application depends on (for deployment validation)
    'dependencies' => [
        'notifications',
        'media',
    ],

    // Other applications this application optionally integrates with
    'optional_dependencies' => [
        'search',
        'ai_services',
    ],
];
```

The Platform Core aggregates all manifests and uses them to power the IntegrationRegistry, CapabilityRegistry, and Application Catalog. The Workspace builds itself entirely from manifests — no hardcoded application list.

### 4.6 ServiceContracts

The Platform Core provides base abstractions for integration contracts. Individual contracts are defined in the owning application's own namespace.

**Platform Core abstractions:**

```
app/Domain/Core/Contracts/
├── ProviderContract.php      ← base interface all contracts extend
└── CapabilityContract.php    ← base interface for capability registration
```

**Application contracts stay in the owning application:**

```
app/Domain/StockFlow/Contracts/
├── InventoryProvider.php     ← extends ProviderContract
└── SupplierProvider.php

app/Domain/GrowFinance/Contracts/
├── AccountingProvider.php    ← extends ProviderContract
└── PaymentProvider.php
```

**Why this structure:**

- Platform Core stays small — it defines only the integration mechanism, not every business domain
- Applications own their domain contracts — Core never needs to know about inventory, accounting, payroll, etc.
- The registry discovers contracts via the manifest — it does not need a hardcoded list in Core
- Future applications add new contracts without modifying Core

**Base interface example:**

```php
namespace App\Domain\Core\Contracts;

interface ProviderContract
{
    public function capability(): string;
}
```

### 4.7 FeatureFlagService

Feature flags let Platform Core release capabilities gradually — per organization, per user, or globally.

**API:**

```php
interface FeatureFlagService
{
    public function isEnabled(string $feature, ?string $organizationId = null, ?string $userId = null): bool;
    public function enable(string $feature, ?string $organizationId = null): void;
    public function disable(string $feature, ?string $organizationId = null): void;
    public function forOrganization(string $organizationId): array;
    public function forUser(string $userId): array;
}
```

**Use cases:**

- Gradual rollout of new applications to organizations
- Beta features gated to specific users
- Emergency kill switches for problematic capabilities
- Per-organization feature tiers based on subscription plan

### 4.8 HealthService

Exposes the operational status of every installed application.

**API:**

```php
interface HealthService
{
    public function check(string $application): HealthStatus;
    public function all(): array;  // ['stockflow' => 'healthy', 'growfinance' => 'maintenance', ...]
    public function isHealthy(string $application): bool;
}

enum HealthStatus
{
    case Healthy;
    case Degraded;
    case Maintenance;
    case Unavailable;
    case Offline;
}
```

**Health states:**

| State | Meaning |
|-------|---------|
| `Healthy` | Application is running normally |
| `Degraded` | Application is running but with performance or capacity issues |
| `Maintenance` | Application is deliberately disabled for updates |
| `Unavailable` | Application is installed but not responding (transient) |
| `Offline` | Application is unreachable — cannot be contacted at its endpoint (distributed mode only) |

**Health check modes:**

- **Local mode** — checks the module's internal status within the same process (service available, queue depth, last heartbeat)
- **Remote mode** — (future) checks a live HTTP endpoint: `https://stockflow.mygrownet.com/health`

```
Application Health
────────────────────────────────────
StockFlow       ● Healthy           (local)
GrowFinance     ● Healthy           (local)
BizBoost        ● Maintenance       (local)
GrowNet         ● Healthy           (local)
GrowMart        ● Degraded (queue backlog)
```

---

## 5. Application Runtime Infrastructure

Between the Platform Core and individual applications sits a shared runtime infrastructure layer. This layer is owned by the platform team but is **not part of domain core** — it is infrastructure that both Core and applications depend on.

```
MyGrowNet Platform
     │
     ├── Platform Core           (domain — identity, orgs, integration)
     │
     ├── Runtime Infrastructure  (infrastructure — middleware, routing, auth)
     │    │
     │    ├── Auth Adapters
     │    ├── Tenancy Middleware
     │    ├── Routing Gateway
     │    ├── API Gateway           (future, distributed stage)
     │    ├── Event Transport       (future)
     │    └── Cache Layer
     │
     └── Applications            (business domains)
```

**Auth Adapters** — The Identity Gateway (`auth.mygrownet.com`) and session sharing (`SESSION_DOMAIN`) currently live here. In the future, this layer handles token validation, API key authentication, and cross-service identity propagation.

**Tenancy Middleware** — `DetectSubdomain`, `ResolveDomainContext`, `SetPlatformContext` resolve the current organization and context from the request.

**Routing Gateway** — Maps hostnames (subdomains) and URL prefixes to application controllers. Today this is the `DetectSubdomain` middleware and route groups.

**API Gateway** — *(future, distributed stage)* A dedicated gateway that handles authentication, rate limiting, contract resolution, and routing for distributed services. Not needed in the current modular monolith.

**Event Transport** — *(future)* Replaces Laravel's local event dispatch with a message queue / event bus that works across process boundaries. Not needed in the current modular monolith.

---

## 6. Module Lifecycle

Every application on the MyGrowNet platform progresses through a defined lifecycle managed by Platform Core.

```
Installed
    │
    ▼
Registered
    │
    ▼
Installing ──► ApplicationInstalling Event
    │
    ▼
Provisioning ──► ApplicationProvisioning Event
    │
    ▼
Configuring ──► ApplicationConfiguring Event
    │
    ▼
   Ready
    │
    ▼
Activating ──► ApplicationEnabled Event
    │
    ▼
  Active
    │
    ├──► Maintenance ──► MaintenanceMode Event
    │        │
    │        ▼
    │     Active (restored)
    │
    ├──► Suspended ──► ApplicationDisabled Event
    │        │
    │        ▼
    │     Active (re-enabled)
    │
    └──► Archived (terminal)
```

### Lifecycle States

| State | Meaning | Managed By |
|-------|---------|------------|
| **Installed** | Module exists in the codebase, migrations have run | ModuleDiscovery |
| **Registered** | Module has booted and published its manifest | ModuleDiscovery |
| **Installing** | Platform Core is beginning the installation workflow | ApplicationProvisioningService |
| **Provisioning** | Platform Core is allocating resources (may take minutes) | ApplicationProvisioningService |
| **Configuring** | Application is applying organization-specific settings | ApplicationProvisioningService |
| **Ready** | Provisioning complete, awaiting activation | ApplicationProvisioningService |
| **Activating** | Final activation workflow running | ApplicationProvisioningService |
| **Active** | Fully operational for the organization | ApplicationProvisioningService |
| **Maintenance** | Temporarily unavailable (upgrades, data migration) | Platform Core |
| **Suspended** | Disabled (license expiry, admin action, payment failure) | ApplicationProvisioningService |
| **Archived** | Permanently disabled for the organization | ApplicationProvisioningService |

### What the Lifecycle Enables

- **Upgrades** — put an application in Maintenance, run migrations, restore to Active
- **Backups** — quiesce an application during snapshot
- **Licensing** — Suspend when subscription lapses, Reactivate on renewal
- **Provisioning** — fine-grained states let UI show progress during long provisioning

---

## 7. PlatformContext

Every application on the platform operates within a shared context. Instead of each application querying the same user, organization, and permission data independently, the Platform Core provides a lightweight `PlatformContext` that accompanies every request, event, and contract call.

### Context Shape

```php
class PlatformContext
{
    public readonly string $traceId;
    public readonly string $requestId;
    public readonly string $userId;
    public readonly string $organizationId;
    public readonly string $applicationId;
    public readonly ?string $installationId;
    public readonly string $workspaceId;
    public readonly string $locale;
    public readonly string $timezone;
}
```

The context describes the request environment — it always identifies **who** (userId), **which organization** (organizationId), **which application** (applicationId), and **which installation** (installationId). Full model objects (User, Organization) and authorization decisions (permissions, roles) are resolved lazily by dedicated services. This keeps event payloads small, serializable, and queue-friendly.

The context does not carry a single role because a user may have different roles per application (e.g., BMS: Manager, StockFlow: Warehouse Supervisor, GrowFinance: Accountant). Roles are resolved by the Authorization Framework per-application at the point of use.

**Fields explained:**

- `traceId` — Distributed trace identifier. Shared across all events, contract calls, and requests triggered by a single root operation. While `requestId` identifies a single interaction, `traceId` connects them into a trace tree for end-to-end observability.
- `requestId` — Unique identifier for every request, event dispatch, or contract call. Enables end-to-end tracing across applications: "InvoiceCreated with requestId abc-123" can be traced through BMS publishing → GrowFinance journal entry → Notification delivery.
- `applicationId` — Identifies which application the context is scoped to. Always present — even in platform-level requests, it identifies the platform itself.
- `installationId` — Identifies the specific application installation within an organization. Useful when the same organization has multiple installations of the same application (e.g., separate StockFlow instances for different divisions).

### How Context Flows

Every integration channel receives the PlatformContext automatically:

**Platform Service calls:**

```php
class NotificationService
{
    public function send(PlatformContext $context, string $message): void
    {
        $user = User::findOrFail($context->userId);
        // ...
    }
}
```

**Event dispatch:**

```php
class InvoiceCreated
{
    public function __construct(
        public readonly PlatformContext $context,
        public readonly string $invoiceId,
        public readonly float $amount,
    ) {}
}
```

**Contract calls:**

```php
class StockFlowInventoryProvider implements InventoryProvider
{
    public function getItems(PlatformContext $context): array
    {
        return ItemModel::where('sa_company_id', $context->organizationId)->get()->toArray();
    }
}
```

### Why PlatformContext Matters

- Eliminates thousands of repeated `auth()->user()`, `$request->organization()` calls
- Guarantees every integration interaction has the same security boundary
- Makes tenant isolation automatic — the organization ID is always known
- Lightweight and serializable — safe for queued events and future cross-service transport
- Full objects are resolved lazily, avoiding payload bloat

---

## 8. Platform SDK

The Platform SDK is a lightweight PHP library that every application uses to interact with the Platform Core. It provides a consistent, type-safe developer experience without requiring applications to know the internal structure of the Platform Core. The SDK is the **public programming surface** — applications depend only on the SDK, and the SDK hides whether the implementation is local (modular monolith) or remote (distributed services).

### 8.1 What the SDK Provides

```
mygrownet/platform-sdk/
│
├── Context/
│   └── PlatformContext.php
│
├── Events/
│   ├── PlatformEvent.php           ← Event envelope
│   └── EventDispatcher.php         ← Publish events with context injection
│
├── Contracts/
│   ├── NotificationProvider.php
│   ├── MediaProvider.php
│   └── SearchProvider.php
│
├── Auth/
│   ├── PlatformToken.php
│   └── TokenValidator.php
│
├── Identity/
│   ├── UserIdentity.php
│   └── OrganizationIdentity.php
│
├── Integration/
│   ├── IntegrationGuard.php
│   └── ContractResolver.php
│
└── Exceptions/
    ├── IntegrationException.php
    └── UnauthorizedIntegrationException.php
```

### 8.2 Usage

```php
// In any application service:
use MyGrowNet\Platform\Sdk\Context\PlatformContext;
use MyGrowNet\Platform\Sdk\Events\EventDispatcher;

class InventoryService
{
    public function __construct(
        private PlatformContext $context,
        private EventDispatcher $events,
    ) {}

    public function adjustStock(ItemId $id, int $qty): void
    {
        // Business logic...
        $this->events->dispatch('stock.adjusted', [
            'item_id' => $id,
            'quantity' => $qty,
        ]);
    }
}
```

The SDK ensures that `PlatformContext` is always available (injected automatically by the Application Runtime Infrastructure), and events are automatically wrapped in the standard envelope with context attached. Applications never import directly from `App\Domain\Core` — they consume the SDK.

In the current monolithic stage, the SDK's classes are actual implementations in `App\Domain\Core`. In the independent-deployment stage, the SDK becomes a Composer package with interface-only contracts backed by HTTP or message-based transport.

### 8.3 SDK Benefits

- **Stable API surface** — Applications depend on SDK interfaces, not Platform Core internals
- **Future-proof transport** — In the monolith, SDK calls are local method calls. When services split, the SDK switches transparently to HTTP/gRPC
- **Testability** — SDK interfaces can be mocked in isolation
- **Versioning** — SDK version can evolve independently from Platform Core

---

## 9. Dependency Rules

Clear dependency direction is essential to prevent circular coupling.

```
                      ┌──────────────────┐
                      │  Platform Core    │
                      │                   │
                      │  Identity         │
                      │  Organizations    │
                      │  Integration      │
                      │  Notifications    │
                      │  Audit            │
                      └────────┬─────────┘
                               │
                               ▼
                    ┌──────────────────┐
                    │  Runtime Layer    │
                    │                   │
                    │  Auth / Tenancy   │
                    │  Routing / Cache  │
                    └────────┬─────────┘
                               │
                   ┌────────────┼────────────┐
                   │            │            │
                   ▼            ▼            ▼
            ┌──────────┐ ┌──────────┐ ┌──────────┐
            │ StockFlow │ │GrowFinance│ │   BMS    │
            └──────────┘ └──────────┘ └──────────┘
                   │            │            │
                   └────────────┼────────────┘
                                │
                                ▼
                     No application depends
                     on another application
```

### 9.1 Platform Core Has No Business Dependencies

The Platform Core depends on Laravel infrastructure, database, cache, and queue — but it has **zero dependencies on any business application**.

```
Platform Core
    │
    ├── depends on: Laravel infrastructure, Database, Cache, Queue
    │
    └── business dependencies: none
      ✗ StockFlow
      ✗ GrowFinance
      ✗ BMS
```

### 9.2 Runtime Layer Depends on Platform Core

The Runtime Layer provides shared infrastructure (auth adapters, tenancy middleware, routing) but depends on the Platform Core for identity, organization context, and integration services.

```
Platform Core
    │
    ├── depends on: Laravel infrastructure, Database, Cache, Queue
    │
    └── business dependencies: none

Runtime Layer
    │
    └── depends on: Platform Core (IdentityService, OrganizationService, IntegrationRegistry)
    └── business dependencies: none
```

### 9.3 Applications Depend on Platform Core + Runtime Layer

Every application depends on the Platform Core for identity, organization, workspace, and integration services, and on the Runtime Layer for tenancy and routing.

```
StockFlow
    │
    └── depends on: Platform Core (IdentityService, OrganizationService, EventBus, ...)
    └── depends on: Runtime Layer (TenancyMiddleware, AuthAdapter, ...)
```

### 9.4 Applications Never Depend on Each Other

No application imports a class from another application's domain layer.

```
BMS
    │
    ├── depends on: Platform Core     (IdentityService, OrganizationService, EventBus, ...)
    ├── depends on: Runtime Layer     (TenancyMiddleware, AuthAdapter, ...)
    │
    └── NEVER depends on: another application
        ✗ StockFlow
        ✗ GrowFinance
        ✗ GrowMart
        ✗ Etc.
```

Cross-application communication happens exclusively through:

- **Platform Events** (fire-and-forget, publisher knows nothing about subscribers)
- **Integration Contracts** (interface in the Platform Core for stable contracts, or in the owning application for app-specific contracts; resolved through the IntegrationRegistry)

### 9.5 Practical Exception: Shared Domain Artifacts

In a modular monolith, applications occasionally need to share value objects, DTOs, or enums. The solution is **not** to import another application's classes — it is to use Platform Core shared contracts or a shared SDK namespace.

```
Allowed:

  app/Domain/Core/Contracts/         ← shared interfaces
  app/Domain/Core/ValueObjects/       ← shared value types (Money, Address, etc.)
  app/Domain/Core/DataTransfer/       ← shared DTOs

Forbidden:

  BMS imports StockFlow\Entities\Item
  GrowMart imports BMS\Models\Invoice
```

Shared domain artifacts in Core must remain business-neutral and stable. If a shared type is specific to a single domain, it belongs in that domain.

### 9.6 Consequences of Violating Dependency Rules

| Violation | Consequence |
|-----------|-------------|
| Application A imports Application B's Eloquent model | Deployment coupling — both must deploy together forever |
| Application A calls Application B's service directly | Testing coupling — tests for A must set up B |
| Application A writes to Application B's table | Data coupling — schema changes to B break A |
| Platform Core imports an application class | Platform cannot evolve independently |

---

## 10. Standard Platform Events

Events are categorized by ownership. **Platform lifecycle events** are defined in the Platform Core. **Domain events** are defined and owned by individual applications.

### 10.1 Event Envelope

Every event published on the MyGrowNet platform follows a standard envelope. This ensures a consistent format for transport, logging, tracing, and future cross-process dispatch.

```php
class PlatformEvent
{
    public readonly string $eventId;
    public readonly string $eventName;
    public readonly string $eventVersion;       // e.g. '1.0', '2.0'
    public readonly string $publisher;           // e.g. 'bms', 'stockflow'
    public readonly DateTimeImmutable $occurredAt;
    public readonly string $correlationId;       // traces entire event chain
    public readonly ?string $causationId;        // immediate parent event
    public readonly PlatformContext $context;
    public readonly array $payload;
}
```

**Fields explained:**

- `eventId` — Globally unique identifier for this specific event instance
- `eventName` — Dot-notation name with version: `invoice.created.v1`, `stock.goods_received.v2`
- `eventVersion` — Schema version: `1.0`, `2.0`. Incremented when the payload structure changes
- `publisher` — Application ID of the publisher (e.g., `bms`, `stockflow`, `platform-core`)
- `occurredAt` — Timestamp of when the event happened in the publisher's local time
- `correlationId` — Shared across all events triggered by a single root operation. If a user creates an invoice and that triggers a payment, a journal entry, and a notification, all four events share the same `correlationId`
- `causationId` — Identifies the immediate parent event. If `PaymentReceived` was caused by `InvoiceCreated`, the causation chain is traceable: `evt_001` → `evt_002` → `evt_003`
- `context` — PlatformContext identifying the user, organization, application, and trace
- `payload` — Business data (varies by event)

**Naming convention:**

Events follow a dot-notation convention that includes the version:

```
{publisher}.{entity}.{action}.v{version}

bms.invoice.created.v1
stockflow.goods_received.v2
growfinance.payment.received.v1
```

**Example serialized:**

```json
{
    "event_id": "evt_a1b2c3",
    "event_name": "bms.invoice.created.v1",
    "event_version": "1.0",
    "publisher": "bms",
    "occurred_at": "2026-07-24T10:00:00Z",
    "correlation_id": "corr_xyz789",
    "causation_id": null,
    "context": {
        "trace_id": "trace_abc123",
        "organization_id": "org_101",
        "application_id": "bms",
        "user_id": "usr_55"
    },
    "payload": {
        "invoice_id": "inv_999",
        "amount": 5000.00
    }
}
```

The envelope is transport-ready — the same format works for local Laravel dispatch, RabbitMQ, SQS, or Kafka without transformation.

### 10.2 Platform Lifecycle Events (owned by Platform Core)

These events concern the platform itself — application installations, organizations, and user membership.

| Event | Published By | Meaning |
|-------|-------------|---------|
| `ApplicationInstalling` | Platform Core | An application installation has begun |
| `ApplicationProvisioning` | Platform Core | Resources are being allocated |
| `ApplicationConfiguring` | Platform Core | Application settings are being applied |
| `ApplicationEnabled` | Platform Core | An application is now active for an organization |
| `ApplicationDisabled` | Platform Core | An application was disabled |
| `OrganizationCreated` | Platform Core | A new organization was registered |
| `OrganizationArchived` | Platform Core | An organization was archived |
| `OrganizationMemberAdded` | Platform Core | A user joined an organization |
| `OrganizationMemberRemoved` | Platform Core | A user was removed from an organization |
| `UserRegistered` | Platform Core | A new user registered on the platform |

### 10.3 Domain Events (owned by applications)

These events concern business domain concepts. They are defined in each application's own namespace, not in the Platform Core.

**BMS events:**

| Event | Meaning |
|-------|---------|
| `InvoiceCreated` | A new invoice was issued |
| `InvoicePaid` | An invoice was paid |
| `InvoiceCancelled` | An invoice was voided |
| `EmployeeCreated` | A new employee record was created |
| `PayrollProcessed` | Payroll run completed |

**StockFlow events:**

| Event | Meaning |
|-------|---------|
| `PurchaseOrderCreated` | A purchase order was raised |
| `PurchaseOrderApproved` | A purchase order was approved |
| `GoodsReceived` | Goods were received against a PO |
| `StockAdjusted` | Stock quantity was manually adjusted |

**GrowFinance events:**

| Event | Meaning |
|-------|---------|
| `PaymentReceived` | A payment was recorded |
| `PaymentFailed` | A payment attempt failed |
| `JournalEntryCreated` | A journal entry was posted |

**GrowMart events:**

| Event | Meaning |
|-------|---------|
| `OrderPlaced` | A customer placed an order |
| `OrderShipped` | An order was shipped |

**GrowNet events:**

| Event | Meaning |
|-------|---------|
| `CommissionAwarded` | A commission was credited |
| `TierUpgraded` | A member advanced to a higher tier |

**CRM events (future):**

| Event | Meaning |
|-------|---------|
| `CustomerCreated` | A new customer record was created |
| `CustomerUpdated` | Customer details changed |

### 10.4 Subscription Rules

- An application subscribes **only** to events it needs
- Subscriptions are declared in the module's ServiceProvider
- Listeners should be **idempotent** — processing an event twice produces the same result
- A listener failure never crashes the publisher (use queued listeners)

### 10.5 Event Ownership Registry

Every event on the platform has exactly one owner. Only the owner may publish that event. This prevents two applications from publishing events with the same meaning but different payloads.

| Event | Owner | Since |
|-------|-------|-------|
| `platform.organization.created.v1` | Platform Core | — |
| `platform.organization.member_added.v1` | Platform Core | — |
| `platform.application.enabled.v1` | Platform Core | — |
| `platform.application.disabled.v1` | Platform Core | — |
| `bms.invoice.created.v1` | BMS | Phase 3 |
| `bms.invoice.paid.v1` | BMS | Phase 3 |
| `bms.employee.created.v1` | BMS | Phase 4 |
| `stockflow.purchase_order.created.v1` | StockFlow | Phase 3 |
| `stockflow.goods_received.v1` | StockFlow | Phase 3 |
| `stockflow.stock.adjusted.v1` | StockFlow | Phase 4 |
| `growfinance.payment.received.v1` | GrowFinance | Phase 3 |
| `growfinance.journal.created.v1` | GrowFinance | Phase 4 |
| `growmart.order.placed.v1` | GrowMart | Phase 4 |
| `growmart.order.shipped.v1` | GrowMart | Phase 5 |
| `grownet.commission.awarded.v1` | GrowNet | Phase 5 |
| `crm.customer.created.v1` | CRM (future) | Phase 5 |

**Rule:** If an application needs to announce an event with the same business meaning as an existing event, it subscribes to the existing event rather than publishing its own. If the meaning differs, it must use a different event name (e.g., `stockflow.goods_received.v1` vs `bms.goods_received.v1` — but the latter is unlikely to be needed since goods receiving belongs to StockFlow).

---

## 11. Reliable Event Delivery *(v2 — design ready, not yet implemented)*

Financial systems such as GrowFinance require guaranteed event delivery — a published event must never be lost, even if the publishing process crashes after committing its database transaction. This section describes the reliability patterns that will be introduced as the platform matures.

### 11.1 Transactional Outbox

Instead of publishing an event directly inside a database transaction, the pattern is:

```
Application
       │
       ▼
   1. Begin transaction
        ├── Insert business record (e.g., invoice)
        ├── Insert event record into outbox table
   2. Commit transaction                    ← If crash occurs here, no event is lost
   3. Worker reads outbox table             ← Separate process, survives crashes
        └── Publish event to EventBus
   4. Mark outbox record as published
```

This guarantees that an event is never published before its business record is committed, and never lost after commit.

**Outbox table schema (in each application's database):**

```sql
CREATE TABLE {app}_event_outbox (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_name      VARCHAR(255) NOT NULL,
    event_payload   JSON NOT NULL,
    context         JSON NOT NULL,
    status          ENUM('pending', 'published', 'failed') DEFAULT 'pending',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    published_at    TIMESTAMP NULL,
    attempts        TINYINT UNSIGNED DEFAULT 0
);
```

In the current modular monolith, these outbox tables are in the same database as the application. In the distributed stage, each application owns its outbox independently.

### 11.2 Inbox Pattern (Idempotent Processing)

The receiving side uses an inbox table to guarantee at-most-once processing:

```
EventBus
   │
   ▼
Listener receives event
   │
   ├── Check inbox: has event_id been processed?  ← Idempotency guard
   │     └── If yes: discard
   │
   └── If no:
         ├── Insert event_id into inbox table
         ├── Process event
         └── Commit
```

This ensures that even if the EventBus delivers the same event twice, the listener processes it exactly once.

### 11.3 Event Replay

Applications expose an endpoint to replay historical events from the outbox:

- Platform Core provides an `EventReplayService` that accepts a date range and event name filter
- Applications can rebuild state by replaying events they missed during downtime
- Used for disaster recovery, data correction, and onboarding new subscribers

### 11.4 When Outbox Is Required

| Context | Required? | Rationale |
|---------|-----------|-----------|
| Financial events (invoice, payment, journal) | **Required** | Lost events cause accounting discrepancies |
| Inventory events (stock adjustment, goods received) | **Required** | Lost events cause inventory drift |
| Notification events | Optional | A lost notification is acceptable |
| Audit events | Required | Audit trail must be complete |
| Platform lifecycle events | Required | Org/application state must be consistent |

---

## 12. Integration Examples

### 12.1 Application Provisioning

**Scenario:** A company enables GrowFinance from BMS.

```
User clicks "Enable GrowFinance" in BMS
       │
       ▼
BMS calls ApplicationProvisioningService::enable()
       │
       ▼
Platform Core records the installation in organization_applications
       │
       ▼
Platform Core fires ApplicationEnabled Event
       │
       ▼
GrowFinance Listener receives the event
       │
       ├── Creates Chart of Accounts (default COA for the org)
       ├── Creates default Financial Year
       ├── Creates default Currencies
       ├── Creates default Tax Settings
       ├── Creates default Journals
       │
       └── Done
```

**Key point:** BMS never creates accounting records. BMS only triggers the provisioning — GrowFinance provisions itself.

### 12.2 Invoice Processing

**Scenario:** A sales invoice is raised in BMS and needs to be reflected in GrowFinance.

```
User creates an invoice in BMS
       │
       ▼
BMS persists the invoice in its own cms_invoices table
       │
       ▼
BMS fires InvoiceCreated Event  (BMS domain event)
       │
       ▼
GrowFinance Listener
       │
       ├── Debit Accounts Receivable
       ├── Credit Sales Revenue
       │
       └── Done
```

**Key point:** BMS never writes to GrowFinance tables. BMS announces the invoice — GrowFinance decides how to account for it.

### 12.3 Inventory Accounting

**Scenario:** StockFlow receives goods against a purchase order.

```
Warehouse staff receive goods in StockFlow
       │
       ▼
StockFlow persists the receipt in sa_stock_movements
       │
       ▼
StockFlow fires GoodsReceived Event  (StockFlow domain event)
       │
       ▼
GrowFinance Listener
       │
       ├── Debit Inventory Asset Account
       ├── Credit Accounts Payable
       │
       └── Done
```

**Key point:** StockFlow performs no accounting. It announces the goods receipt — GrowFinance handles all journal entries.

### 12.4 GrowFinance Full Invoice Lifecycle

**Scenario:** A complete invoice lifecycle demonstrating multiple applications reacting independently to the same process.

```
Step 1: Invoice Created in BMS
       │
       ▼
BMS fires InvoiceCreated Event  (BMS domain event)
       │
       ├──► GrowFinance Listener
       │         ├── Debit Accounts Receivable
       │         └── Credit Sales Revenue
       │
       ├──► BizBoost Listener (if enabled)
       │         └── Record sales metric
       │
       └──► (No other application reacts)

Step 2: Payment Received in GrowFinance
       │
       ▼
GrowFinance fires PaymentReceived Event  (GrowFinance domain event)
       │
       ├──► BMS Listener
       │         └── Update invoice status to "Paid"
       │
       ├──► StockFlow Listener
       │         └── Release delivery hold
       │
       ├──► BizBoost Listener
       │         └── Update payment metric
       │
       └──► GrowNet Listener (if enabled)
                 └── Credit commission for referred sale

Step 3: Order Fulfilled in StockFlow
       │
       ▼
StockFlow fires OrderShipped Event  (StockFlow domain event)
       │
       └──► GrowFinance Listener
                 ├── Debit Cost of Goods Sold
                 └── Credit Inventory Asset
```

**Key point:** Each application reacts only to the events it cares about. No application knows about any other application. BMS does not know GrowFinance creates journal entries. GrowFinance does not know BMS updates invoice status. Each event is owned by the application that publishes it — the Platform Core only knows about lifecycle events.

---

## 13. Platform Contracts

Contracts use a **domain-owned model**:

- **Business capability contracts** live in the owning application's `Contracts/` namespace. StockFlow owns `InventoryProvider`. GrowFinance owns `AccountingProvider`. The CRM owns `CustomerProvider`. This keeps the Platform Core from needing to know every business domain.
- **Platform-wide service contracts** (notification, media, search) live in `App\Domain\Core\Contracts` because these are infrastructure capabilities, not business domains.
- **Implementations** always live in the owning application's `Infrastructure/` layer.

Consumers import the contract interface from the owning application's namespace. This creates a clean dependency on the contract, not on the implementation. If StockFlow is later replaced, the new system implements `InventoryProvider` and consumers remain unchanged.

### 13.1 Base Abstractions (Platform Core)

```php
namespace App\Domain\Core\Contracts;

interface ProviderContract
{
    public function capability(): string;
}
```

### 13.2 Contract Locations

```
# Platform Core owns only platform-wide infrastructure contracts
app/Domain/Core/Contracts/
├── NotificationProvider.php
├── MediaProvider.php
└── SearchProvider.php

# Applications own their business domain contracts
app/Domain/StockFlow/Contracts/
├── InventoryProvider.php
├── SupplierProvider.php

app/Domain/GrowFinance/Contracts/
├── AccountingProvider.php

app/Domain/Billing/Contracts/  (future)
├── InvoiceProvider.php
├── PaymentProvider.php

app/Domain/Crm/Contracts/  (future)
├── CustomerProvider.php
└── LeadProvider.php

# Implementations always live in the owning application's infrastructure
app/Domain/StockFlow/Infrastructure/
├── InventoryProviderImpl.php
└── SupplierProviderImpl.php

app/Domain/GrowFinance/Infrastructure/
├── AccountingProviderImpl.php
```

### 13.3 Contract Catalog

| Contract | Responsibility | Interface Location | Implementation |
|----------|---------------|-------------------|----------------|
| `InventoryProvider` | Stock levels, items, movements, valuations | `StockFlow\Contracts` | StockFlow |
| `AccountingProvider` | Chart of accounts, journal entries, trial balance | `GrowFinance\Contracts` | GrowFinance |
| `CustomerProvider` | Customer records, contacts, addresses | `Crm\Contracts` (future) | CRM module |
| `SupplierProvider` | Supplier records, payment terms, contacts | `StockFlow\Contracts` | StockFlow |
| `OrderProvider` | Sales orders, order items, fulfillment status | `GrowMart\Contracts` | GrowMart |
| `InvoiceProvider` | Invoices, billing records | `Billing\Contracts` (future) | Billing module |
| `PaymentProvider` | Payment gateways, transactions, settlement | `Billing\Contracts` (future) | Billing module |
| `NotificationProvider` | In-app notifications, email, SMS | `Core\Contracts` | Platform Core |
| `MediaProvider` | File uploads, image processing, CDN URLs | `Core\Contracts` | Storage |
| `SearchProvider` | Full-text search across entities | `Core\Contracts` | Platform Core |

### 13.4 Contract Definition Pattern

```php
namespace App\Domain\StockFlow\Contracts;

use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\Context\PlatformContext;

interface InventoryProvider extends ProviderContract
{
    public function capability(): string;

    /** @return array<int, array{id: string, name: string, quantity: int}> */
    public function getItems(PlatformContext $context): array;

    public function getStockLevel(PlatformContext $context, string $itemId, ?string $warehouseId = null): int;

    public function adjustStock(PlatformContext $context, string $itemId, int $quantity, string $reason): void;

    /** @return array<int, array{id: string, item_id: string, quantity: int, type: string, created_at: string}> */
    public function getMovements(PlatformContext $context, \DateTimeImmutable $from, \DateTimeImmutable $to): array;
}
```

Every contract extends `ProviderContract` so the IntegrationRegistry can discover them uniformly. Return types use plain arrays or DTOs owned by the application — the Platform Core never imports business types from any application.

### 13.5 Implementation Location

Implementations live in the owning module's infrastructure layer:

```
app/Domain/StockFlow/
├── Entities/
├── Services/
├── Contracts/
│   └── InventoryProvider.php      ← interface
└── Infrastructure/
    └── InventoryProviderImpl.php  ← implements interface

app/Providers/StockAuditServiceProvider.php
    // Binding:
    $this->app->bind(InventoryProvider::class, InventoryProviderImpl::class);
```

### 13.6 Independent Deployment

If StockFlow is later deployed independently, only the implementation class changes:

```php
// Before (monolith):
class InventoryProviderImpl implements InventoryProvider
{
    public function getItems(PlatformContext $context): array
    {
        return ItemModel::where('sa_company_id', $context->organizationId)->get()->toArray();
    }
}

// After (independent deployment):
class InventoryProviderImpl implements InventoryProvider
{
    public function __construct(private HttpClient $http) {}

    public function getItems(PlatformContext $context): array
    {
        $response = $this->http->get("https://stockflow.internal/api/items?company={$context->organizationId}");
        return $response->json();
    }
}
```

All callers remain unchanged because they depend on the interface, not the implementation.

### 13.7 Contract Compatibility Rules

Changes to contracts fall into two categories: **compatible** (safe to deploy without updating consumers) and **breaking** (require coordinated updates or contract versioning).

| Change | Compatibility | Rule |
|--------|---------------|------|
| Adding an optional field to a return type | ✅ Compatible | Existing consumers ignore the new field |
| Adding a new method to the interface | ✅ Compatible | Existing consumers are not required to call it |
| Relaxing a parameter type (e.g., `int` → `int\|null`) | ✅ Compatible | Callers can still pass the narrower type |
| Widening a return type (e.g., `array` → `Collection`) | ✅ Compatible if `Collection` implements `array`-like access | Check consumer expectations |
| Removing a field from a return type | ❌ Breaking | Consumers accessing the removed field will fail |
| Changing a field's meaning or unit (e.g., cents → dollars) | ❌ Breaking | Data interpretation changes silently |
| Changing enum values | ❌ Breaking | `switch` statements and comparisons break |
| Changing return structure (e.g., flat → nested) | ❌ Breaking | Consumers expect the old shape |
| Narrowing a parameter type (e.g., `int\|null` → `int`) | ❌ Breaking | Callers passing `null` will fail |
| Removing a method from the interface | ❌ Breaking | Consumers using the method will fail at compile time |
| Adding a **required** parameter to a method | ❌ Breaking | All callers must be updated |

**Rule of thumb:** If a consumer can be deployed without modification after your change, it is compatible. If not, you must either create a new contract version or coordinate the update across all consumers.

**Contract versioning approach:**

For breaking changes, define a new interface with a version suffix:

```php
// Old (v1)
interface InventoryProvider
{
    public function getItems(PlatformContext $context): array;
}

// New (v2) — consumers migrate at their own pace
interface InventoryProviderV2
{
    public function getItems(PlatformContext $context, ?string $category = null): array;
}
```

Both interfaces live in the same namespace. The IntegrationRegistry resolves the appropriate version for each consumer based on the consumer's declared contract version in its manifest.

---

## 14. Integration Security

Every cross-application interaction must be authorized before it reaches the application's domain logic. Security is handled by the **IntegrationGuard** (a dedicated service separate from the IntegrationRegistry), enforced centrally by the Platform Core, not by individual applications.

### 14.1 Authorization Flow

```
Caller (Application A)
       │
       ▼
   IntegrationGuard::authorize($context, InventoryProvider::class)
       │
       ├── Verify user is authenticated
       ├── Verify user belongs to organization
       ├── Verify organization has access to the target application
       ├── Verify user has required permissions
       ├── Verify target application is Active (not Maintenance/Suspended)
       ├── Verify feature flag is enabled (if applicable)
       ├── Verify tenant isolation (org scope)
       │
       ▼
   IntegrationRegistry::resolve(InventoryProvider::class)
       │
       └── Returns provider implementation
       │
       ▼
   Contract implementation (Application B)
```

### 14.2 What Is Enforced

| Check | Enforced By | When |
|-------|-------------|------|
| User authenticated | IdentityService | Every request |
| Organization membership | OrganizationService | Every scoped request |
| Application enabled for org | ApplicationProvisioningService | Contract resolution |
| User permissions | Authorization Framework | Contract resolution |
| Feature flag enabled | FeatureFlagService | Contract resolution |
| Application health | HealthService | Contract resolution |
| Tenant isolation | PlatformContext | Automatically via organization ID |

### 14.3 Security by Convention

- Every contract method receives `PlatformContext` — implementations derive the organization from the context, never from a raw parameter
- The IntegrationGuard validates the context and permissions before passing control to any contract implementation
- Events carry the context so subscribers can enforce their own authorization without re-querying

### 14.4 Explicit Deny

If any security check fails, the IntegrationGuard returns an explicit denial before the contract implementation is ever reached:

```php
// IntegrationGuard pseudocode
public function authorize(PlatformContext $context, string $contractClass): void
{
    if (!$this->health->isHealthy($context->applicationId)) {
        throw new ApplicationUnavailableException($context->applicationId);
    }

    if (!$this->auth->canAccess($context->userId, $context->applicationId, $contractClass)) {
        throw new UnauthorizedContractAccessException($contractClass);
    }

    if (!$this->features->isEnabled($contractClass, $context->organizationId)) {
        throw new FeatureDisabledException($contractClass);
    }

    // Authorized — proceed to provider
}
```

---

## 15. Integration Policies

These policies govern how integrations behave at runtime. They apply to all three integration types.

### 15.1 Retry Policy

**Applies to:** Events (queued listeners), Contract calls

| Condition | Behavior |
|-----------|----------|
| Event listener throws exception | Retry up to 3 times with exponential backoff (1s, 4s, 16s) |
| Contract call throws transient exception (e.g., `NetworkTimeoutException`) | Retry up to 2 times with 500ms delay |
| Contract call throws domain/validation exception (e.g., `InventoryNotFoundException`, `InvalidItemIdException`) | **No retry** — propagate immediately. A retry will produce the same error. |
| All retries exhausted | Log failure, notify administrators, move to failed queue |

### 15.2 Queue Policy

**Applies to:** Events

| Aspect | Policy |
|--------|--------|
| Default queue for event listeners | `integrations` |
| Separate queue per application | `integrations-{app_id}` (optional) |
| Max attempts before dead letter | 3 |
| Dead letter queue retention | 7 days |
| Listener timeout | 60 seconds |

### 15.3 Timeout Policy

**Applies to:** Contract calls (synchronous), Platform Service calls

| Context | Timeout |
|---------|---------|
| Contract call (local, same process) | 30 seconds |
| Contract call (remote, independent deployment) | 10 seconds |
| Platform Service call | 15 seconds |
| Event listener (sync) | 30 seconds |
| Event listener (queued) | 60 seconds |

### 15.4 Failure Policy

**Applies to:** All integration types

| Failure | Policy |
|---------|--------|
| Event publisher transaction succeeds, listener fails | Listener retries (see retry policy). Publisher transaction is never rolled back. |
| Event publisher transaction fails | No event is dispatched |
| Contract call fails | Exception propagates to caller |
| Contract implementation has a bug | Bug is scoped to the owning application — other applications are unaffected |
| Integration service is unavailable | Caller receives `ServiceUnavailableException`. Caller should degrade gracefully (cache, fallback, or user-facing message). |

**Golden rule:** A failure in one application never corrupts another application's data or rolls back another application's transaction.

### 15.5 Versioning Policy

**Applies to:** Contracts

| Change | Version Bump | Backward Compatible? |
|--------|-------------|---------------------|
| Adding a new method | Minor | Yes |
| Adding an optional parameter | Minor | Yes |
| Changing a method signature | Major | No |
| Removing a method | Major | No |
| Changing return type | Major | No |
| Deprecating a method | None (annotation) | Yes |

### 15.6 Deprecation Policy

**Applies to:** Contracts, Events

1. Mark deprecated methods with `@deprecated` annotation and the version it was deprecated in
2. Deprecated methods remain functional for 2 major versions
3. Emit a deprecation warning log when a deprecated method is called
4. Announce deprecations in release notes
5. Remove only in a major version bump

---

## 16. Integration Observability

Operating the platform with multiple active applications requires visibility into every integration interaction. This section defines the monitoring surface.

### 16.1 Integration Dashboard

The Platform Core exposes an operational dashboard (available to platform administrators) showing:

| Metric | Source | Purpose |
|--------|--------|---------|
| Events published (24h) | EventBus | Volume trend |
| Events failed (24h) | EventBus | Failure rate |
| Retry queue depth | Queue system | Backlog monitoring |
| Dead letter queue count | Queue system | Unprocessable events |
| Average processing time per event | EventBus | Performance regression |
| Contract call success rate | IntegrationRegistry | Contract health |
| Slowest contract calls | IntegrationRegistry | Performance hotspots |
| Listeners per event | ModuleDiscovery | Subscription bloat |
| Queue length per application | Queue system | Per-app backlog |
| Listener health (last heartbeat) | HealthService | Dead listeners |

### 16.2 Failed Event Handling

When an event listener exhausts all retries:

1. The event moves to the **dead letter queue** for the application's queue
2. The platform notifies the application's maintainers (email, Slack, or webhook)
3. The event payload and full context are preserved for replay
4. Administrators can replay events from the dead letter queue after fixing the issue

```json
{
    "dead_letter": {
        "event_id": "evt_failed_123",
        "event_name": "bms.invoice.created.v1",
        "listener": "GrowFinance\\Listeners\\CreateJournalEntry",
        "failure_reason": "Database connection timeout",
        "attempts": 3,
        "last_attempt": "2026-07-24T10:15:00Z",
        "queued_at": "2026-07-24T10:14:55Z"
    }
}
```

### 16.3 Alerting Thresholds

| Condition | Alert | Severity |
|-----------|-------|----------|
| >5% event failure rate in 5 minutes | Integration degradation | Warning |
| Any dead letter queue non-empty | Events failing | Warning |
| Contract call >30s | Performance regression | Info |
| Listener offline >5 minutes | Listener down | Critical |
| Queue backlog >1000 events | Queue congestion | Warning |

These rules are non-negotiable. Every developer working on MyGrowNet must follow them.

### Rule 1: Never Query Another Application's Database

```
❌ BMS does:   DB::table('sa_items')->where(...)
❌ GrowMart does:   DB::connection('growfinance')->select(...)
✅ All cross-app data access: via Platform Contract or Event
```

### Rule 2: Database Isolation

Each application owns its migrations and schema. No application migration touches another application's tables.

```
Allowed:

  StockFlow migration → sa_items, sa_products, sa_stock_movements
  BMS migration       → cms_jobs, cms_invoices, cms_employees
  GrowFinance migration → gf_accounts, gf_journals, gf_budgets

Forbidden:

  BMS migration       → sa_items                          (StockFlow's table)
  StockFlow migration → cms_invoices                      (BMS's table)
  GrowFinance migration → grow_net_users                  (GrowNet's table)
```

This applies even for adding columns, indexes, or foreign keys to another application's tables. If you need data from another application, use a contract or an event.

**Exception — Reporting & Analytics:** A dedicated reporting module (or data warehouse schema) may read from any application's tables for read-only reporting purposes. This is the only exception. The reporting module never writes to application tables, and application migrations never need to account for it. The reporting schema is maintained separately and consumes data via DB snapshots, replicas, or CDC (change data capture) — never via direct cross-application migration dependencies.

### Rule 3: Applications Own Their Business Logic

```
❌ StockFlow performs accounting journal entries
❌ BMS creates GrowFinance chart of accounts
✅ Every application owns its domain logic entirely
```

### Rule 4: Cross-Application Communication Has Three Allowed Channels

| Channel | Owner | When to Use |
|---------|-------|-------------|
| **Platform Service** (synchronous) | Platform Core | Querying platform-level data (orgs, users, apps, permissions) |
| **Platform Event** (asynchronous) | Lifecycle: Platform Core. Domain: owning application. | Announcing something happened, expecting side effects |
| **Integration Contract** (synchronous) | Interface in owning application, base abstraction in Platform Core | Querying another application's data by interface |

### Rule 5: Platform Core Governs Communication

All cross-application communication is **governed by** Platform Core constructs, but the Platform Core does not sit in the middle of every message:

- Platform Services are defined and implemented in `app/Domain/Core/Services/`
- Platform Lifecycle Events are defined in `app/Domain/Core/Events/`
- Domain events are defined in each application's `Events/` namespace
- Platform Contracts base abstractions are defined in `app/Domain/Core/Contracts/`
- Event listeners are registered per-module in `ServiceProvider::boot()`

### Rule 6: Applications Never Depend on Each Other

```
❌ StockFlow imports BMS Eloquent model
❌ BMS calls GrowFinance service directly
✅ Cross-app communication only through events or contracts
```

### Rule 7: Platform Core Never Depends on Applications

The Platform Core must never import or reference any application class. It depends on nothing.

### Rule 8: Applications Must Be Designed for Independent Deployment

- No shared database migrations between applications
- No shared configuration files
- No hard-coded references to another app's internal classes
- Dependencies between applications are always on interfaces, never implementations

### Rule 9: The Integration Layer Must Remain Technology-Agnostic

- Contracts use plain PHP types (arrays, value objects, `DateTimeImmutable`)
- No framework-specific types in contract signatures
- No assumptions about transport (HTTP, CLI, queue) in contract definitions

### Rule 10: Independent Deployment Must Require Minimal Changes

If an application is deployed independently:

1. The application's tables move to a new database
2. Contract implementations switch from Eloquent to HTTP clients
3. Platform Events switch from local dispatch to remote event bus (e.g., RabbitMQ, SQS)
4. All code that consumes contracts or listens to events remains **unchanged**

---

## 17. Data Ownership

One of the strongest rules in enterprise architecture is: **who owns which data?** This section defines data ownership explicitly so that every developer and architect knows which application is authoritative for each dataset.

### 17.1 Ownership Table

| Data Entity | Owner | Storage | Can Modify | Can Read Via | Can React Via |
|-------------|-------|---------|------------|--------------|---------------|
| Users | Platform Core | `users` table | Platform Core only | Platform Service | Events |
| Organizations | Platform Core | `organizations` table | Platform Core only | Platform Service | Events |
| Applications | Platform Core | `applications` table | Platform Core only | Platform Service | Events |
| Organization Members | Platform Core | `organization_members` table | Platform Core only | Platform Service | Events |
| Permissions & Roles | Platform Core | `roles`, `permissions` tables | Platform Core only | Platform Service | Events |
| User Profiles | Platform Core | `user_profiles` table | Platform Core only | Platform Service | Events |
| Invoices | BMS | `cms_invoices` table | BMS only | Contract | Events |
| Jobs | BMS | `cms_jobs` table | BMS only | Contract | Events |
| Employees | BMS | `cms_employees` table | BMS only | Contract | Events |
| Items (inventory) | StockFlow | `sa_items` table | StockFlow only | Contract | Events |
| Purchase Orders | StockFlow | `sa_purchase_orders` table | StockFlow only | Contract | Events |
| Stock Movements | StockFlow | `sa_stock_movements` table | StockFlow only | Contract | Events |
| Audits | StockFlow | `sa_audits` table | StockFlow only | Contract | Events |
| Chart of Accounts | GrowFinance | `gf_accounts` table | GrowFinance only | Contract | Events |
| Journal Entries | GrowFinance | `gf_journals` table | GrowFinance only | Contract | Events |
| Budgets | GrowFinance | `gf_budgets` table | GrowFinance only | Contract | Events |
| Products | GrowMart | `gm_products` table | GrowMart only | Contract | Events |
| Orders | GrowMart | `gm_orders` table | GrowMart only | Contract | Events |
| Members | GrowNet | `grow_net_users` table | GrowNet only | Contract | Events |
| Commissions | GrowNet | `grow_net_commissions` table | GrowNet only | Contract | Events |

### 17.2 Ownership Rules

1. **Only the owner may modify its data.** No application writes to another application's tables — ever.
2. **Everyone else reads via contract.** If you need data from another application, use the contract the owning application exposes.
3. **Everyone else reacts via events.** If you need to act on a change in another application's data, subscribe to the event the owning application publishes.
4. **Owners may denormalize.** An application may cache or denormalize data from other applications in its own tables for performance, as long as the cache is derived from the contract or event and is never treated as authoritative.

### 17.3 Boundary Exceptions

| Exception | Justification | Controls |
|-----------|---------------|----------|
| Reporting & Analytics (read-only) | Data warehouse needs cross-domain visibility | Read-only replicas, CDC, never direct writes |
| Platform Core audit logs | Core may record who did what across all apps | Append-only, no business logic dependency |
| Search index | Indexing entities from multiple applications | Event-driven index rebuild, read-only |

---

## 18. Multi-tenant Data Isolation

The PlatformContext guarantees that every integration interaction carries an organization ID. This section makes the tenant isolation model explicit — it is the platform's primary security guarantee.

### 18.1 Tenant Isolation Rules

| Rule | Enforcement |
|------|-------------|
| Every database query must be scoped by `organization_id` | Repository layer enforces scope automatically |
| Cross-tenant queries are forbidden | IntegrationGuard rejects queries without org scope |
| Background jobs carry tenant context | Job middleware restores PlatformContext before execution |
| Caches are tenant-scoped | Cache keys prefixed with organization ID |
| Queue messages include tenant ID | Event envelop always carries `organization_id` |
| Contract implementations receive context | Implementation derives org from `PlatformContext`, never from raw input |

### 18.2 Tenant Isolation in the Database

```sql
-- Every application table includes organization_id
CREATE TABLE sa_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sa_company_id BIGINT UNSIGNED NOT NULL,   -- ← organization/tenant FK
    name VARCHAR(255) NOT NULL,
    ...
    INDEX idx_company (sa_company_id)
);

-- Every query is automatically scoped
-- (Repository base class applies the where clause)
class TenantAwareRepository
{
    protected function scope(Builder $query): Builder
    {
        return $query->where($this->tenantColumn, $this->context->organizationId);
    }
}
```

### 18.3 Cross-Tenant Operations

Some operations legitimately cross tenant boundaries (e.g., platform administrators, system migrations, global reports). These are:

- Limited to users with `super_admin` or `support` platform roles
- Logged explicitly in the audit trail
- Never performed by application business logic — only by Platform Core infrastructure

---

## 19. Anti-Corruption Layers

External systems (Stripe, QuickBooks, banks, government APIs) use their own data models, protocols, and semantics. These must never leak into the MyGrowNet domain. Each external integration gets an **Anti-Corruption Layer (ACL)** that translates between the external model and the platform's domain model.

### 19.1 ACL Pattern

```
External System (Stripe)
      │
      ▼
Stripe Webhook Handler     ← Receives Stripe's data model
      │
      ▼
StripeAdapter              ← Translates Stripe model → MyGrowNet model
      │
      ▼
PaymentProvider            ← Platform contract
      │
      ▼
GrowFinance                ← Pure domain logic
```

### 19.2 ACL Principles

1. **External types never enter the domain layer.** `Stripe\PaymentIntent`, `QuickBooks\Customer`, and `BankTransaction\Record` are translated at the boundary.
2. **The ACL owns the mapping.** The domain never imports an external SDK or library.
3. **External failures are wrapped.** `Stripe\Exception\ApiConnectionException` becomes `Platform\IntegrationException` with the original exception preserved for debugging.
4. **ACLs live in the application's infrastructure layer.** Each application that integrates with external systems owns its ACL.

```
app/Domain/GrowFinance/Infrastructure/
├── AntiCorruption/
│   ├── Stripe/
│   │   ├── StripeAdapter.php
│   │   ├── StripeWebhookHandler.php
│   │   └── Mappers/
│   │       ├── PaymentIntentMapper.php     ← Stripe → MyGrowNet
│   │       └── CustomerMapper.php
│   └── QuickBooks/
│       ├── QuickBooksAdapter.php
│       └── Mappers/
│           └── InvoiceMapper.php
```

### 19.3 When to Use an ACL

| Situation | ACL Required? |
|-----------|---------------|
| Payment gateway (Stripe, PayPal) | ✅ Required — different data model, critical financial data |
| Accounting export (QuickBooks, Xero) | ✅ Required — mapping between accounting standards |
| Government API (tax filing, e-invoicing) | ✅ Required — regulatory semantics, strict format |
| Bank feed (OFX, Plaid) | ✅ Required — different data model, financial criticality |
| Internal MyGrowNet application | ❌ Not needed — use contracts/events within the platform |

---

## 20. Platform Configuration Strategy

As the platform grows, configuration exists at multiple levels. A clear hierarchy prevents settings conflicts and inconsistent behavior.

### 20.1 Configuration Hierarchy

```
Platform-wide defaults
        ↓
Organization overrides
        ↓
Application overrides
        ↓
User preferences
```

Each level inherits from the level above and may override specific values.

### 20.2 Level Ownership

| Level | Owner | Examples | Stored In |
|-------|-------|----------|-----------|
| Platform | Platform Core | Default language, currency, timezone, feature flags | `config/platform.php`, database |
| Organization | Organization Admin | Company name, logo, default currency, enabled apps | `organization_settings` table |
| Application | Application Admin | StockFlow low stock threshold, GrowFinance fiscal year | `application_settings` table |
| User | User | Preferred language, notification preferences, dashboard layout | `user_settings` table |

### 20.3 Configuration Resolution

```php
// Resolution order: User → Application → Organization → Platform
$threshold = $settings->resolve('stockflow.low_stock_threshold', $context);
// Returns user-level value if set, otherwise app-level, otherwise org-level, otherwise platform default
```

### 20.4 Configuration as Code

Critical configuration (feature flags, integration endpoints, timeouts) is defined in code and deployed through the normal release process. Non-critical configuration (user preferences, display settings) may be changed at runtime through admin interfaces.

---

## 21. Error Taxonomy & Concurrency Policy

### 21.1 Standard Error Categories

Every application uses these exception types so that the IntegrationGuard, retry policies, and logging have consistent behavior:

| Exception | When to Throw | Retry Behavior | HTTP Status (if exposed) |
|-----------|---------------|----------------|--------------------------|
| `ValidationException` | Input data is invalid | Never retry | 422 |
| `AuthorizationException` | User lacks permission | Never retry | 403 |
| `NotFoundException` | Entity not found | Never retry | 404 |
| `ConcurrencyException` | Optimistic lock failure, version conflict | Retry (up to 3 times) | 409 |
| `IntegrationException` | External system failure (Stripe, bank API) | Retry (up to 3 times) | 502 |
| `ServiceUnavailableException` | Dependent service is down | Retry (up to 3 times) | 503 |
| `ConfigurationException` | Missing or invalid configuration | Never retry | 500 |
| `TransientException` | Temporary failure (network, timeout) | Retry (up to 3 times) | 500 |

```php
namespace App\Domain\Core\Exceptions;

class ValidationException extends \RuntimeException {}
class AuthorizationException extends \RuntimeException {}
class NotFoundException extends \RuntimeException {}
class ConcurrencyException extends \RuntimeException {}
class IntegrationException extends \RuntimeException {}
class ServiceUnavailableException extends \RuntimeException {}
class ConfigurationException extends \RuntimeException {}
class TransientException extends \RuntimeException {}
```

### 21.2 Concurrency Policy

Financial and inventory operations require strict concurrency controls to prevent duplicate or lost data.

| Scenario | Mechanism | Description |
|----------|-----------|-------------|
| Stock adjustment | Optimistic locking (version column) | `UPDATE sa_items SET quantity = ?, version = version + 1 WHERE id = ? AND version = ?` |
| Invoice creation | Database transaction | `INSERT` inside a `DB::transaction()` with foreign key validation |
| Journal posting | Optimistic locking + transaction | Both version check and atomic insert |
| Payment processing | Idempotency key | Each payment request carries a unique `idempotency_key`. The handler checks if the key was already processed before executing. |
| Purchase order approval | Optimistic locking | Version column on `sa_purchase_orders` prevents dual approval |
| Physical count completion | Database transaction | Lock the count row, verify all items counted, update atomically |
| Event processing | Inbox pattern (idempotency) | Event ID stored in inbox table, checked before processing |

**Optimistic locking example:**

```php
class Item
{
    public function adjustStock(int $quantity, int $expectedVersion): void
    {
        $affected = DB::update(
            'UPDATE sa_items SET quantity = ?, version = version + 1 WHERE id = ? AND version = ?',
            [$quantity, $this->id, $expectedVersion]
        );

        if ($affected === 0) {
            throw new ConcurrencyException("Item {$this->id} was modified by another process");
        }
    }
}
```

**Idempotency key pattern:**

```php
class PaymentHandler
{
    public function processPayment(string $idempotencyKey, PaymentRequest $request): PaymentResult
    {
        // Check if already processed
        $existing = PaymentLog::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing->result;  // Return cached result — idempotent
        }

        // Process payment (stripe charge, etc.)
        return DB::transaction(function () use ($idempotencyKey, $request) {
            $result = $this->gateway->charge($request);
            PaymentLog::create(['idempotency_key' => $idempotencyKey, 'result' => $result]);
            return $result;
        });
    }
}
```

---

## 22. Future Vision

This architecture positions MyGrowNet to support any number of applications while keeping each one independent.

### Supported Applications

| Application | Domain | Integration Pattern |
|-------------|--------|-------------------|
| BMS | Core business (companies, jobs, HR, payroll) | Events + Contracts |
| StockFlow | Inventory, audits, stock control | Events + Contracts |
| GrowFinance | Accounting, budgets, financial reports | Events + Contracts |
| GrowMart | E-commerce, products, orders | Events + Contracts |
| GrowNet | MLM, commissions, membership tiers | Events |
| BizBoost | Marketing, campaigns, leads | Events |
| CRM | Customer relationship management | Contracts |
| HR / Payroll | Human resources, payroll processing | Events + Contracts |
| POS | Point of sale | Contracts |
| Help Desk | Support ticketing | Events |
| Document Management | File storage, versioning | Contracts |
| AI Services | Recommendations, predictions, automation | Contracts |

> **Important:** Independent deployment, distributed services, and API Gateway are future capabilities described in this roadmap. The current implementation priority is strong module boundaries inside a single Laravel application. Prematurely building distributed infrastructure before module boundaries are mature will create unnecessary complexity. Follow the implementation order in §22.

### Evolution Roadmap

```
Stage 1 — Modular Monolith (Current)

  Existing applications have been separated into bounded contexts with DDD layers.
  Platform Core foundation exists (users, organizations, applications, workspace).
  Authentication consolidated under Identity Gateway (auth.mygrownet.com).
  Domain events adopted for lifecycle events; domain events partially deployed.
  Integration contracts exist in concept but live in application Contracts namespaces.
  Application Runtime Layer exists (DetectSubdomain, ResolveDomainContext, SetPlatformContext).
  Migration is ongoing — some cross-module references remain to be extracted.

Stage 2 — Platform Integration Layer (Target — Next 6 months)

  Application manifests published by every module.
  ModuleDiscovery and CapabilityRegistry operational.
  ApplicationProvisioningService manages the full lifecycle.
  FeatureFlagService and HealthService deployed.
  PlatformContext accompanies every request, event, and contract call.
  IntegrationRegistry resolves contracts by capability name.
  Application Runtime Layer is formalized.
  All contracts live in their owning application's namespace.

Stage 3 — Distributed Services (Target — Next 12 months)

  EventBus can dispatch across process boundaries via event transport layer.
  Contracts are resolved through an API Gateway or Service Registry.
  HealthService monitors live endpoints.
  IntegrationRegistry supports remote providers.
  Heavy applications (GrowFinance, StockFlow) can run in separate processes.

Stage 4 — Independent Deployments (Target — Next 18+ months)

  Any application can be deployed as an independent service.
  Communication switches from in-memory to network transport transparently.
  Zero domain logic changes in consuming applications (infrastructure configuration
  changes such as provider endpoint URLs are expected).
  New applications can be added without redeploying the Platform Core.
```

### Recommended Implementation Order

Not everything should be built immediately. The following order is conservative — each phase enables the next without over-engineering.

**Phase 1 — Platform Core Foundation (in progress)**

Already working:

- User identity and authentication
- Organizations and membership
- Application registry and installations
- Workspace routing
- Permissions

**Phase 2 — Runtime Layer (next)**

- PlatformContext middleware
- Application resolver (DetectSubdomain, ResolveDomainContext)
- Manifest registry (applications publish their manifests)
- Documentation of the Runtime Layer as a distinct concern

**Phase 3 — Events (incremental)**

Start with the events already in use:

- `OrganizationCreated`
- `ApplicationEnabled`
- `InvoiceCreated`
- `PaymentReceived`

Add domain events as new integration patterns emerge. Do not create 50 events upfront.

**Phase 4 — Contracts (incremental)**

Start with the contracts that have the most integration value:

- `InventoryProvider`
- `AccountingProvider`
- `NotificationProvider`
- `MediaProvider`

Add contracts as applications need to communicate. Each contract lives in the owning application's namespace.

**Phase 5 — Advanced Platform Services (later)**

Only after Phases 1-4 are stable:

- CapabilityRegistry
- FeatureFlagService
- Health monitoring
- API Gateway for remote contract resolution
- Event transport for cross-process dispatch

### Key Architectural Decisions

| Decision | Rationale |
|----------|-----------|
| Contracts live in the owning application | Platform Core stays small and does not need to know every business domain |
| PlatformContext uses scalar IDs | Keeps payloads small, serializable, and queue-friendly |
| Lifecycle events in Platform Core | Core is the authority on platform state |
| Domain events in applications | Core should not know business concepts |
| Capability-based lookup (not app-name lookup) | Allows replacing applications without changing consumers |
| Application Runtime Layer | Separates infrastructure concerns from domain logic |

## 23. Migration Strategy

This document describes the target architecture. MyGrowNet already exists as a Laravel application — the migration from current code to this architecture must be incremental.

### Phase 1: Extract Module Boundaries (in progress)

- Define bounded contexts for each application
- Move domain logic into `app/Domain/{Module}/` namespaces
- Separate Eloquent models into `app/Infrastructure/Persistence/Eloquent/{Module}/`
- Ensure each module has its own ServiceProvider

### Phase 2: Move Shared Models into Platform Core

- User, Organization, Application models belong in Platform Core
- Remove cross-module model references (BMS should not import StockFlow models)
- Create initial `app/Domain/Core/` namespace with identity and organization services

### Phase 3: Remove Direct Database Access

- Identify all `DB::table('sa_*')` or `DB::connection('growfinance')` patterns
- Replace with Integration Contracts or Platform Events
- Enforce the rule: no application reads another application's tables

### Phase 4: Introduce Domain Events

- Start with lifecycle events (OrganizationCreated, ApplicationEnabled)
- Add domain events as integration patterns emerge (InvoiceCreated, GoodsReceived)
- Move event listeners to the consuming application's ServiceProvider

### Phase 5: Introduce Stable Integration Contracts

- Define `InventoryProvider` in `StockFlow\Contracts`, `AccountingProvider` in `GrowFinance\Contracts`, `CustomerProvider` in `CRM\Contracts`
- Platform Core provides only the base `ProviderContract` and contract discovery infrastructure
- Implement in the owning application's infrastructure layer
- Replace direct `app(Service::class)` cross-module calls with contract-based resolution

### Phase 6: Extract Independent Applications (future)

- When module boundaries are mature and all communication uses contracts/events
- Extract applications into separate deployable units
- Replace in-process contract resolution with remote transport

### Migration Principles

- **No big bang rewrites.** Each phase produces a working application.
- **The monolith stays working.** Contracts and events are introduced alongside existing code, not replacing it all at once.
- **Strangler pattern.** Old direct access patterns are wrapped by contracts, then the direct path is removed.
- **Backward compatibility.** Existing API endpoints and data formats remain unchanged during migration.

### Guiding Principle

> An application should be able to be deployed independently and communicate with the rest of the platform — all without a single line of code changing in any other application.

This architecture document defines the path to that goal.

---

## 24. Architectural Decision Records

The following ADRs document key architectural decisions made during the design of this platform. Each ADR records the context, the decision, and the rationale. As the platform evolves, new ADRs should be added and existing ones may be superseded.

### ADR-001: Use Events Instead of Direct Module Calls

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Applications needed to react to changes in other applications. Direct service calls created deployment coupling. |
| **Decision** | Cross-application reactions use asynchronous events. The publishing application fires an event; subscribers react independently. |
| **Consequences** | Publishers and subscribers can evolve independently. Eventual consistency is accepted. |

### ADR-002: Platform Core Owns Identity

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Multiple applications needed user authentication and organization management. Each app had its own guard. |
| **Decision** | Platform Core owns the `users`, `organizations`, and `roles` tables. All applications delegate authentication and authorization to Core. |
| **Consequences** | Single source of truth for identity. Platform Core must never import application code. |

### ADR-003: Contracts Owned by Domains

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Earlier versions placed all integration contracts in Platform Core, creating a dumping ground. |
| **Decision** | Business domain contracts live in the owning application's `Contracts/` namespace. Only platform-wide infrastructure contracts (Notification, Media, Search) live in Core. |
| **Consequences** | Core stays small. Applications own their domain contracts. Contract discovery uses the IntegrationRegistry. |

### ADR-004: PlatformContext Uses Scalar IDs

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Events and contract calls needed security context. Passing full User/Organization objects created serialization problems and tight coupling. |
| **Decision** | PlatformContext carries only scalar identifiers (userId, organizationId, applicationId, traceId). Full objects are resolved lazily by dedicated services. |
| **Consequences** | Events are lightweight and serializable. Repository layer resolves IDs to models. |

### ADR-005: Lifecycle Events in Platform Core, Domain Events in Applications

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | There was ambiguity about which events belonged to Core vs applications. |
| **Decision** | Platform lifecycle events (organization created, application enabled) are defined in Platform Core. Business domain events (invoice created, goods received) are defined in the owning application. |
| **Consequences** | Core does not need to know business concepts. Applications define their own domain vocabulary. |

### ADR-006: Capability-Based Lookup Instead of App-Name Lookup

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Contracts were resolved by application name, creating tight coupling between consumers and specific application implementations. |
| **Decision** | Contracts are resolved by capability name (e.g., `inventory`) rather than by application name (e.g., `stockflow`). |
| **Consequences** | The providing application can be replaced without changing consumers. New implementations can be swapped in transparently. |

### ADR-007: Application Runtime Layer Separates Infrastructure from Domain

| Attribute | Value |
|-----------|-------|
| **Status** | Accepted |
| **Date** | 2026-07-24 |
| **Context** | Domain logic was mixed with HTTP concerns, subdomain resolution, and framework middleware. |
| **Decision** | An Application Runtime Layer sits between Platform Core and applications, handling subdomain routing, context resolution, and framework integration. |
| **Consequences** | Domain logic remains pure. Runtime concerns can evolve independently. Applications are framework-agnostic at the domain level. |