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
| `stockflow.purchase.received` | v1 | stockflow | Goods received against a purchase order | `{company_id, purchase_order_id, order_number, received_by, items}` | **active** |

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

## 5. GrowMart Domain Events

Owned by **growmart**. Located in `app/Domain/GrowMart/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `growmart.order.placed` | v1 | growmart | A new order was placed in GrowMart | `{order_id, company_id, customer_id, total, currency, item_count}` | **active** |
| `growmart.order.fulfilled` | v1 | growmart | An order was fulfilled/delivered | `{order_id, company_id, fulfillment_status, fulfilled_at}` | **active** |
| `growmart.order.refunded` | v1 | growmart | An order was refunded | `{order_id, company_id, refund_amount, currency, reason, refunded_at}` | **active** |

### Event Classes

```php
// app/Domain/GrowMart/Events/OrderPlaced.php
class OrderPlaced extends PlatformEvent
{
    public const NAME = 'growmart.order.placed.v1';
    // orderId, companyId, customerId, total, currency, itemCount
}
```

---

## 6. GrowFinance Domain Events

Owned by **growfinance**. Located in `app/Domain/GrowFinance/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `growfinance.payment.received` | v1 | growfinance | A payment was recorded in GrowFinance | `{company_id, payment_id, invoice_id, invoice_number, amount, payment_method, customer_id}` | **active** |
| `growfinance.journal.posted` | v1 | growfinance | A journal entry was posted (renamed from `.created`) | `{journal_id, company_id, total_debit, total_credit, currency, description, posted_at}` | **active** |
| `growfinance.account.balance.changed` | v1 | growfinance | An account balance changed | `{account_id, company_id, previous_balance, new_balance, change_amount, currency}` | **active** |
| `growfinance.period.closed` | v1 | growfinance | An accounting period was closed | `{company_id, period_type, period_start, period_end, closed_at}` | **active** |
| `growfinance.budget.updated` | v1 | growfinance | A budget was updated | `{budget_id, company_id, budgeted_amount, spent_amount, remaining_amount, category, period}` | **active** |
| `growfinance.report.generated` | v1 | growfinance | A financial report was generated | `{company_id, report_type, report_format, report_url, generated_at}` | **active** |

### Event Classes

```php
// app/Domain/GrowFinance/Events/PaymentReceived.php
class PaymentReceived
{
    // Existing class — no NAME constant yet; registered as growfinance.payment.received.v1
}

// app/Domain/GrowFinance/Events/JournalPosted.php
class JournalPosted extends PlatformEvent
{
    public const NAME = 'growfinance.journal.posted.v1';
    // journalId, companyId, totalDebit, totalCredit, currency, description, postedAt
}
```

### Event Rename (Phase F5)

`growfinance.journal.created.v1` was renamed to `growfinance.journal.posted.v1` in Phase F5. Both old and new names remain registered in the Event Ownership Registry during the transition period.

---

## 7. Platform Billing Events

Owned by **platform-billing**. Located in `app/Domain/PlatformBilling/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.billing.subscription.created` | v1 | platform-billing | A new subscription was created | `{subscription_id, organization_id, plan_id, status}` | **active** |
| `platform.billing.subscription.renewed` | v1 | platform-billing | A subscription was renewed | `{subscription_id, organization_id, plan_id, renew_at}` | **active** |
| `platform.billing.subscription.suspended` | v1 | platform-billing | A subscription was suspended | `{subscription_id, organization_id, reason}` | **active** |
| `platform.billing.subscription.cancelled` | v1 | platform-billing | A subscription was cancelled | `{subscription_id, organization_id, cancelled_at}` | **active** |
| `platform.billing.invoice.issued` | v1 | platform-billing | A billing invoice was issued | `{invoice_id, subscription_id, organization_id, amount, due_date}` | **active** |
| `platform.billing.payment.due` | v1 | platform-billing | A payment is due on an invoice | `{invoice_id, subscription_id, organization_id, amount, due_date}` | **active** |
| `platform.billing.grace_period.expiring` | v1 | platform-billing | Grace period is about to expire | `{subscription_id, organization_id, days_remaining}` | **active** |

---

## 8. Platform Payments Events

Owned by **platform-payments**. Located in `app/Domain/PlatformPayments/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.payment.initiated` | v1 | platform-payments | A payment transaction was initiated | `{transaction_id, organization_id, amount, currency, payment_method}` | **active** |
| `platform.payment.failed` | v1 | platform-payments | An individual payment attempt failed | `{transaction_id, organization_id, amount, currency, failure_reason, attempt_number}` | **active** |
| `platform.payment.completed` | v1 | platform-payments | A payment was successfully completed | `{transaction_id, organization_id, amount, currency, provider_transaction_id}` | **active** |
| `platform.payment.collection_failed` | v1 | platform-payments | All retry attempts exhausted, payment collection failed | `{transaction_id, organization_id, amount, currency, failure_reason, attempt_count}` | **active** |
| `platform.payment.refunded` | v1 | platform-payments | A payment was refunded | `{transaction_id, organization_id, amount, currency, refund_reference}` | **active** |
| `platform.payment.settled` | v1 | platform-payments | A payment was settled by the provider | `{transaction_id, organization_id, settled_amount, fee, currency}` | **active** |
| `platform.payment.retry_scheduled` | v1 | platform-payments | A retry was scheduled for a failed payment | `{transaction_id, organization_id, attempt_number, scheduled_at}` | **active** |
| `platform.payment.settlement_reconciled` | v1 | platform-payments | A settlement was reconciled | `{settlement_id, organization_id, expected_amount, actual_amount, currency, status}` | **active** |

---

## 9. Financial Services Core Events

Owned by **financial-services-core**. Located in `app/Domain/FinancialServicesCore/Events/`.

| Event Name (dot-notation) | Version | Owner | Description | Payload | Status |
|---|---|---|---|---|---|
| `platform.fx.rate_updated` | v1 | financial-services-core | An exchange rate was updated | `{from_currency, to_currency, rate, source, date}` | **active** |

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
| `stockflow.stock.adjusted.v1` | stockflow | StockFlowServiceProvider | Audit trail |
| `stockflow.sale.completed.v1` | stockflow | StockFlowServiceProvider | GrowFinance journal entry |
| `stockflow.count.finalized.v1` | stockflow | StockFlowServiceProvider | Audit trail |
| `stockflow.cash.discrepancy.v1` | stockflow | StockFlowServiceProvider | Notifications |
| `stockflow.purchase.received.v1` | stockflow | StockFlowServiceProvider | GrowFinance AP journal |
| `growfinance.payment.received.v1` | growfinance | CoreServiceProvider | Reconciliation |
| `growfinance.journal.created.v1` | growfinance | CoreServiceProvider | Accounting (legacy, transitioning to `.posted`) |
| `growfinance.journal.posted.v1` | growfinance | GrowFinanceServiceProvider | Accounting (replaces `.created`) |
| `growfinance.account.balance.changed.v1` | growfinance | GrowFinanceServiceProvider | Notifications |
| `growfinance.period.closed.v1` | growfinance | GrowFinanceServiceProvider | Reporting |
| `growfinance.budget.updated.v1` | growfinance | GrowFinanceServiceProvider | Budget tracking |
| `growfinance.report.generated.v1` | growfinance | GrowFinanceServiceProvider | Reporting |
| `growmart.order.placed.v1` | growmart | GrowMartServiceProvider | Order processing |
| `growmart.order.fulfilled.v1` | growmart | GrowMartServiceProvider | Fulfillment |
| `growmart.order.refunded.v1` | growmart | GrowMartServiceProvider | Refunds |
| `bms.expense.recorded.v1` | bms | BmsServiceProvider | Transaction sync |
| `platform.billing.subscription.created.v1` | platform-billing | PlatformBillingServiceProvider | Subscription lifecycle |
| `platform.billing.subscription.renewed.v1` | platform-billing | PlatformBillingServiceProvider | Subscription lifecycle |
| `platform.billing.subscription.suspended.v1` | platform-billing | PlatformBillingServiceProvider | Subscription lifecycle |
| `platform.billing.subscription.cancelled.v1` | platform-billing | PlatformBillingServiceProvider | Subscription lifecycle |
| `platform.billing.invoice.issued.v1` | platform-billing | PlatformBillingServiceProvider | Billing |
| `platform.billing.payment.due.v1` | platform-billing | PlatformBillingServiceProvider | Billing |
| `platform.billing.grace_period.expiring.v1` | platform-billing | PlatformBillingServiceProvider | Dunning |
| `platform.payment.initiated.v1` | platform-payments | PlatformPaymentsServiceProvider | Payment processing |
| `platform.payment.failed.v1` | platform-payments | PlatformPaymentsServiceProvider | Attempt tracking |
| `platform.payment.completed.v1` | platform-payments | PlatformPaymentsServiceProvider | Payment processing |
| `platform.payment.collection_failed.v1` | platform-payments | PlatformPaymentsServiceProvider | Retry orchestration |
| `platform.payment.refunded.v1` | platform-payments | PlatformPaymentsServiceProvider | Refunds |
| `platform.payment.settled.v1` | platform-payments | PlatformPaymentsServiceProvider | Settlement |
| `platform.payment.retry_scheduled.v1` | platform-payments | PlatformPaymentsServiceProvider | Retry orchestration |
| `platform.payment.settlement_reconciled.v1` | platform-payments | PlatformPaymentsServiceProvider | Reconciliation |
| `platform.fx.rate_updated.v1` | financial-services-core | FinancialServicesCoreServiceProvider | Exchange rates |
| `platform.outbox.event_published.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.outbox.event_failed.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.inbox.event_processed.v1` | platform-core | CoreServiceProvider | Phase 7 |
| `platform.inbox.event_duplicate.v1` | platform-core | CoreServiceProvider | Phase 7 |
