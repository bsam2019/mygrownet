# Event Inventory

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 0.6 — Foundation Audit  
> **Applies to:** All MyGrowNet platform events

---

## 1. Platform Lifecycle Events

Owned by **platform-core**. All active as of Phase 8.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.organization.created` | v1 | platform-core | A new organization was created on the platform | `{organization_id, organization_name, created_by}` | **active** |
| `platform.organization.member_added` | v1 | platform-core | A user was added to an organization | `{organization_id, user_id, role}` | **active** |
| `platform.organization.member_removed` | v1 | platform-core | A user was removed from an organization | `{organization_id, user_id}` | **active** |
| `platform.application.enabled` | v1 | platform-core | An application was enabled for an organization | `{organization_id, application_id, application_name}` | **active** |
| `platform.application.disabled` | v1 | platform-core | An application was disabled for an organization | `{organization_id, application_id, application_name}` | **active** |
| `platform.application.maintenance` | v1 | platform-core | An application entered or exited maintenance mode | `{organization_id, application_id, application_name, entering_maintenance: bool}` | **active** |
| `platform.application.archived` | v1 | platform-core | An application installation was permanently archived | `{organization_id, application_id, application_name}` | **active** |

### Event Classes

All live in `app/Domain/Core/Events/`:

| Class | Dispatchable | Fields |
|---|---|---|
| `OrganizationCreated` | Yes | `Organization $organization` |
| `ApplicationEnabled` | Yes | `Organization $organization, string $applicationId, string $applicationName` |
| `ApplicationDisabled` | Yes | `Organization $organization, string $applicationId, string $applicationName` |
| `ApplicationMaintenance` | Yes | `Organization $organization, string $applicationId, string $applicationName, bool $enteringMaintenance` |
| `ApplicationArchived` | Yes | `Organization $organization, string $applicationId, string $applicationName` |

---

## 2. Integration Events

Owned by **platform-core**. These are infrastructure-level events, not domain events.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.integration.contract_timeout` | v1 | platform-core | A contract call exceeded its timeout threshold | `{contract, method, duration_ms, trace_id}` | **active** |
| `platform.integration.event_delivery_failed` | v1 | platform-core | An event could not be delivered to its subscribers | `{event_name, event_id, error_message, attempts}` | **active** |
| `platform.integration.provider_unhealthy` | v1 | platform-core | A contract provider has been marked unhealthy by the HealthService | `{application_id, status, previous_status}` | **active** |

### Additional Platform Failure Events

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.contract.resolved` | v1 | platform-core | A contract was successfully resolved by the registry | `{contract_class, provider_id}` | **active** |
| `platform.contract.failed` | v1 | platform-core | A contract resolution failed | `{contract_class, error_message}` | **active** |
| `platform.failure.circuit_broken` | v1 | platform-core | The circuit breaker opened for a contract method | `{contract, method, failure_count}` | **active** |

---

## 3. BMS Domain Events

Owned by **bms**. Located in `app/Events/BMS/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `bms.invoice.created` | v1 | bms | A new invoice was created in BMS | `{invoice_id, invoice, source}` | **active** |
| `bms.invoice.paid` | v1 | bms | An invoice payment was recorded in BMS | `{invoice_id, invoice, amount_paid, payment_method}` | **active** |
| `bms.employee.created` | v1 | bms | A new employee record was created in BMS | `{employee_id, company_id}` | **active** |

### Event Classes

```php
// app/Events/BMS/InvoiceCreated.php
class InvoiceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public string $source // 'growbuilder', 'growmarket', 'manual'
    ) {}
}

// app/Events/BMS/InvoicePaid.php
class InvoicePaid
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public InvoiceModel $invoice,
        public float $amountPaid,
        public string $paymentMethod,
    ) {}
}
```

---

## 4. StockFlow Domain Events

Owned by **stockflow**. All implement `DomainEvent` interface. Located in `app/Domain/StockFlow/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `stockflow.purchase_order.created` | v1 | stockflow | A purchase order was created or received | `{company_id, purchase_order_id, order_number, received_by, items}` | **active** |
| `stockflow.goods_received` | v1 | stockflow | Goods were received against a purchase order | `{company_id, purchase_order_id, order_number, received_by, items}` | **active** |
| `stockflow.stock.adjusted` | v1 | stockflow | Stock quantity was manually adjusted | `{company_id, item_id, reason, quantity_before, quantity_after, adjustment_type, adjusted_by}` | **active** |
| `stockflow.sale.completed` | v1 | stockflow | A sale was completed at a POS register | `{company_id, sale_id, receipt_number, total, payment_method, sold_by, items}` | **active** |
| `stockflow.count.finalized` | v1 | stockflow | A physical count was finalized | `{company_id, physical_count_id, finalized_by, totals}` | **active** |
| `stockflow.cash.discrepancy` | v1 | stockflow | A cash register closing had a discrepancy | `{company_id, cash_register_id, register_date, expected_amount, counted_amount, variance, closed_by}` | **active** |

### Known Listeners

- `bms.invoice.created` → `InvoiceCreatedListener` (ShouldQueue) — syncs invoice data to GrowFinance
- `bms.invoice.paid` → triggers `PaymentReceivedNotification` in GrowFinance

### Outbox Wiring Status

| Event | Outbox Wired? | Notes |
|---|---|---|
| `stockflow.goods_received` | ✅ Wired in PurchasingService |
| `stockflow.stock.adjusted` | ✅ Wired in InventoryService (was raw Event::dispatch) |
| `stockflow.sale.completed` | ✅ Wired in SalesService (was raw Event::dispatch) |
| `stockflow.count.finalized` | ✅ Wired in PhysicalCountService (was raw Event::dispatch) |
| `stockflow.cash.discrepancy` | ✅ Wired in CashRegisterService (+ wrapped in DB transaction) |
| `bms.invoice.created` | ✅ Wired in BMSIntegrationService |
| `bms.invoice.paid` | ✅ Wired in PaymentService |
| `growfinance.journal.created` | ✅ Wired in BankingService + AccountingService |
| `growfinance.payment.received` | ✅ Wired in BankingService |
| `platform.application.*` | ✅ Wired via EventDispatcher in ApplicationProvisioningService + ApplicationLifecycleService |
| `platform.organization.member_added/removed` | ✅ Wired in OrganizationService |
| `platform.contract.*` + `platform.failure.circuit_broken` | ✅ Wired in ContractInvoker |
| `platform.outbox.event_published/failed` | ✅ Wired in OutboxService |
| `platform.inbox.event_processed/duplicate` | ✅ Wired in InboxService |

---

## 5. GrowFinance Domain Events

Owned by **growfinance**. Located in `app/Domain/GrowFinance/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `growfinance.payment.received` | v1 | growfinance | A payment was recorded in GrowFinance | `{company_id, payment_id, invoice_id, invoice_number, amount, payment_method, customer_id}` | **active** |
| `growfinance.journal.created` | v1 | growfinance | A new journal entry was created | `{company_id, journal_id, journal_type, total_debit, total_credit, created_by}` | **active** |

### Event Classes

```php
// app/Domain/GrowFinance/Events/PaymentReceived.php
class PaymentReceived
{
    public function __construct(
        private int $companyId,
        private int $paymentId,
        private int $invoiceId,
        private string $invoiceNumber,
        private float $amount,
        private string $paymentMethod,
        private int $customerId,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {}
}
```

---

## 6. Platform Event Envelope

All events dispatched through `EventDispatcher` are wrapped in the `PlatformEvent` envelope:

```php
// app/Domain/Core/Events/PlatformEvent.php
class PlatformEvent
{
    public readonly string $eventId;        // UUID
    public readonly string $eventName;       // dot-notation, e.g. 'stockflow.goods_received'
    public readonly string $eventVersion;    // semver, e.g. '1.0'
    public readonly string $publisher;       // module id, e.g. 'stockflow'
    public readonly DateTimeImmutable $occurredAt;
    public readonly string $correlationId;   // propagated across related events
    public readonly ?string $causationId;    // parent event ID for causality chains
    public readonly PlatformContext $context; // traceId, userId, orgId, appId
    public readonly array $payload;          // event-specific data
}
```

### Serialized format (JSON wire)

```json
{
    "event_id": "550e8400-e29b-41d4-a716-446655440000",
    "event_name": "stockflow.goods_received",
    "event_version": "1.0",
    "publisher": "stockflow",
    "occurred_at": "2026-07-26T12:00:00+00:00",
    "correlation_id": "550e8400-e29b-41d4-a716-446655440001",
    "causation_id": null,
    "context": {
        "trace_id": "abc-123",
        "request_id": "req-456",
        "user_id": "42",
        "organization_id": "7",
        "application_id": "stockflow"
    },
    "payload": {
        "company_id": 7,
        "purchase_order_id": 101,
        "order_number": "PO-2026-001",
        "received_by": 42,
        "items": [{"item_id": 1, "quantity": 10, "unit_cost": 5.00}]
    }
}
```

---

## 7. Event Ownership Registry

Registered in `CoreServiceProvider::boot()` via `EventOwnershipRegistry::register()`:

```php
$registry->register('platform.organization.created.v1', 'platform-core');
$registry->register('bms.invoice.created.v1', 'bms');
$registry->register('stockflow.goods_received.v1', 'stockflow');
$registry->register('growfinance.payment.received.v1', 'growfinance');
// ... all 20+ events registered
// See app/Providers/CoreServiceProvider.php lines 104-141
```

The `EventOwnershipRegistry` enforces that only the owning module can publish each event. Attempting to publish an event from the wrong module throws a runtime exception.

---

## 8. Event Ownership Table

| Event Name | Owner | Registered In | Used By |
|---|---|---|---|
| `platform.organization.created.v1` | platform-core | CoreServiceProvider | StockFlow, BMS listeners |
| `platform.organization.member_added.v1` | platform-core | CoreServiceProvider | Workspace |
| `platform.application.enabled.v1` | platform-core | CoreServiceProvider | ApplicationProvisionsioningService |
| `platform.application.disabled.v1` | platform-core | CoreServiceProvider | ApplicationLifecycleService |
| `platform.application.maintenance.v1` | platform-core | CoreServiceProvider | ApplicationLifecycleService |
| `platform.application.archived.v1` | platform-core | CoreServiceProvider | ApplicationLifecycleService |
| `platform.integration.contract_timeout.v1` | platform-core | CoreServiceProvider | ContractInvoker |
| `platform.integration.event_delivery_failed.v1` | platform-core | CoreServiceProvider | OutboxService |
| `platform.integration.provider_unhealthy.v1` | platform-core | CoreServiceProvider | HealthService |
| `bms.invoice.created.v1` | bms | CoreServiceProvider | GrowFinance sync |
| `bms.invoice.paid.v1` | bms | CoreServiceProvider | Notifications |
| `bms.employee.created.v1` | bms | CoreServiceProvider | Employee portal |
| `stockflow.purchase_order.created.v1` | stockflow | CoreServiceProvider | Inventory tracking |
| `stockflow.goods_received.v1` | stockflow | CoreServiceProvider | Inventory tracking |
| `stockflow.stock.adjusted.v1` | stockflow | CoreServiceProvider | Audit trail |
| `growfinance.payment.received.v1` | growfinance | CoreServiceProvider | Reconciliation |
| `growfinance.journal.created.v1` | growfinance | CoreServiceProvider | Accounting |
| `platform.outbox.event_published.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.outbox.event_failed.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.inbox.event_processed.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.inbox.event_duplicate.v1` | platform-core | CoreServiceProvider | Phase 7 |
