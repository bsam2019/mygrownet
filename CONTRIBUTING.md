# MyGrowNet Platform — Developer Guidelines

## Integration Rules

All cross-module integration must follow these 10 rules. Violations are caught in code review and CI.

### Rule 1: No Direct DB Queries Across Modules
Never use `DB::table()` or `DB::connection()` to query another module's tables. Use the owning module's contracts instead.

```php
// ❌ BAD
DB::table('growfinance_invoices')->where('business_id', $id)->sum('amount');

// ✅ GOOD
$invoiceProvider = IntegrationRegistry::resolve(AccountingProvider::class);
$total = $invoiceProvider->getTotalInvoiced($organizationId);
```

### Rule 2: No Eloquent Model Imports Across Modules
Never `use` an Eloquent model from a different module. Go through the module's repository interface.

```php
// ❌ BAD
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceInvoiceModel;

// ✅ GOOD
use App\Domain\GrowFinance\Contracts\AccountingProvider;
```

### Rule 3: No Service Locator Across Modules
Never call `app(Service::class)` or `resolve()`, or inject another module's concrete service. Use the IntegrationRegistry.

```php
// ❌ BAD
$service = app(\App\Domain\GrowNet\Wallet\Services\WalletService::class);

// ✅ GOOD
$walletProvider = IntegrationRegistry::resolve(WalletProvider::class);
```

### Rule 4: Event Ownership
Only the owning module may dispatch a domain event. Publishing an event that belongs to another module is a violation.

- `bms.invoice.created.v1` — only BMS dispatches this
- `growfinance.payment.received.v1` — only GrowFinance dispatches this
- `stockflow.goods.received.v1` — only StockFlow dispatches this

### Rule 5: Listeners Are Consumer-Owned
If module A listens to module B's event, the listener class lives in module A, not module B.

```php
// ✅ GOOD — GrowFinance owns this listener in its own codebase
app/Domain/GrowFinance/Listeners/HandleBmsInvoiceCreated.php
```

### Rule 6: Table Ownership
The module that created a table owns it. Only that module's migrations may `CREATE`, `ALTER`, or `DROP` that table. A cross-module `ALTER` in a migration is a violation.

Exception: Platform Core may add foreign key columns (like `organization_id`) during platform-level refactoring, with explicit ADR approval.

### Rule 7: Data Reads via Contracts
To read data owned by another module, use a contract interface registered in the IntegrationRegistry. Direct SELECT queries on another module's tables are not allowed.

### Rule 8: Data Writes via Events
To trigger a write in another module's data, dispatch an owned domain event and let the consuming module handle it in its listener.

Exception: For synchronous write requirements, use a contract that returns a result (e.g., `InventoryProvider::reserveStock()`).

### Rule 9: Feature Gating in the Consumer
If a feature depends on another module, the gating logic lives in the consuming module, not the provider.

### Rule 10: No Circular Dependencies
If module A depends on module B, module B must not depend on module A. Detect cycles in CI.

---

## Migration Conventions

- Each module owns its schema in `database/migrations/{module}/`
- Load via `$this->loadMigrationsFrom(database_path('migrations/{module}'))` in the module's ServiceProvider
- Never put domain-scoped migrations in `database/migrations/` root

### Canonical Migration Folders

See `AGENTS.md` for the authoritative table of all 34 migration folders.

---

## Architecture Decisions

Platform evolution ADRs are in `docs/adr/`. Key decisions:

- ADR-001: Domain-Driven Design with bounded contexts
- ADR-002: Monorepo with module isolation (not microservices yet)
- ADR-003: Event-driven cross-module communication
- ADR-004: Contract interfaces via IntegrationRegistry
- ADR-005: Single shared database with organization_id scoping
- ADR-006: Phase 8 explores independent deployment feasibility
- ADR-007: Identity Gateway as shared authentication authority

---

## CI Enforcement

The following checks should be automated:

1. **Cross-module DB::table() grep** — fail if a file in module X queries table owned by module Y
2. **Cross-module import lint** — fail if file imports Eloquent model from different module
3. **Migration scope check** — fail if migration alters table owned by different module
4. **Event ownership check** — fail if event dispatched from non-owning module
5. **Contract check** — all cross-module service resolution goes through IntegrationRegistry

### Contract Versioning Convention

Integration contracts follow `MAJOR.MINOR` versioning (no PATCH).

**Rules:**
1. Interfaces are versioned via class name: `InventoryProvider` (v1), `InventoryProviderV2` (v2).
2. **MAJOR** change: method signature change, removal, or semantic behavior change.
3. **MINOR** change: adding a new method or optional parameter (backward-compatible).
4. Deprecate old interfaces with `@deprecated` PHPDoc.
5. Consumers declare contract dependency in their manifest under `contracts`.
6. Providers implement all active contract versions until consumers migrate.
7. Backward compatibility window: at least one release cycle after deprecation.

**Contract change checklist:**
- [ ] Breaking change? → New interface name
- [ ] Backward-compatible? → Add method to existing interface
- [ ] Consumers notified? → File tracking issue
- [ ] Old interface deprecated? → Add `@deprecated` PHPDoc
- [ ] Manifest updated? → Consumer and provider manifests reflect correct class

**CI enforcement (expanded):**
6. **Contract interface change detection** — changes trigger Platform Core team review
7. **Circular dependency detection** — verify no module dependency cycles

**Contract catalog:**

| Contract | Version | Provider Module | Deprecated |
|---|---|---|---|
| `IdentityProvider` | v1 | Platform Core | No |
| `NotificationProvider` | v1 | Platform Core | No |
| `MediaProvider` | v1 | Platform Core | No |
| `InventoryProvider` | v1 | StockFlow | No |
| `AccountingProvider` | v1 | GrowFinance | No |

---

## Error Taxonomy

All platform exceptions live in `App\Domain\Core\Exceptions\`.

### Interface Hierarchy

| Interface | Meaning | Behavior |
|---|---|---|
| `RetryableExceptionInterface` | Temporary failure, safe to retry | ContractInvoker auto-retries with exponential backoff |
| `NonRetryableExceptionInterface` | Permanent failure, retrying won't help | ContractInvoker throws immediately without retry |

### Exception Classes & Retryability

| Exception | Default HTTP | Retryable? | Retry Delay (ms) |
|---|---|---|---|
| `TransientException` | 500 | ✅ Retryable | 100 × 2^(attempt-1) |
| `ServiceUnavailableException` | 503 | ✅ Retryable | 5000 × 2^(attempt-1) |
| `IntegrationException` | 502 | ✅ Retryable | 1000 × 2^(attempt-1) |
| `ConcurrencyException` | 409 | ✅ Retryable | 100 × 2^(attempt-1) |
| `AuthorizationException` | 403 | ❌ Non-retryable | — |
| `ValidationException` | 422 | ❌ Non-retryable | — |
| `ConfigurationException` | 500 | ❌ Non-retryable | — |
| `NotFoundException` | 404 | ❌ Non-retryable | — |
| `ProvisioningException` | — | ❌ Non-retryable | — |

### Creating a New Exception

```php
// If retryable:
class MyRetryableException extends \RuntimeException implements RetryableExceptionInterface
{
    public function retryDelayMs(int $attempt): int
    {
        return (int) (1000 * pow(2, $attempt - 1));
    }
}

// If non-retryable:
class MyNonRetryableException extends \RuntimeException implements NonRetryableExceptionInterface {}
```

---

## Tenant Isolation & Data Ownership

### Organization ID Scoping Rule
Every row in a tenant-scoped table **must** have an `organization_id` (or module-equivalent tenant column) populated. Queries **must** filter by the current organization context.

```php
// ❌ BAD — retrieves data from all tenants
$invoices = InvoiceModel::where('status', 'paid')->get();

// ✅ GOOD — scoped to current tenant
$invoices = InvoiceModel::where('organization_id', $orgId)
    ->where('status', 'paid')
    ->get();
```

### Using TenantAwareRepository
For repositories, extend `App\Domain\Core\Repositories\TenantAwareRepository`:

```php
class EloquentInvoiceRepository extends TenantAwareRepository implements InvoiceRepositoryInterface
{
    protected Model $model;

    public function __construct(PlatformContextResolver $resolver)
    {
        parent::__construct($resolver);
        $this->model = new InvoiceModel();
    }

    protected function getTable(): string { return 'invoices'; }

    public function findForOrganization(int $id): ?Invoice
    {
        return $this->findForTenant($id);
    }
}
```

### Queue Job Isolation
Queue jobs must carry `platformContext` in their serialized data. Attach the `RestoreTenantContext` middleware to restore context on job execution:

```php
// In a job class:
public $platformContext;

public function __construct(array $data)
{
    $this->platformContext = app(PlatformContextResolver::class)->current()->toArray();
    $this->data = $data;
}

// Configure middleware:
public function middleware(): array
{
    return [new RestoreTenantContext];
}
```

### Cache Key Isolation
Always prefix cache keys with the organization ID using `CacheKeyHelper`:

```php
// ❌ BAD — cache collision across tenants
Cache::put('invoice_summary', $data, 3600);

// ✅ GOOD — isolated by tenant
$key = app(CacheKeyHelper::class)->prefixed('invoice_summary');
Cache::put($key, $data, 3600);
```

### Data Ownership
The `DataOwnershipRegistry` maps tables to owning modules. Cross-module queries without using the owning module's contract is a violation (Rule 1-3). To add a new table to the registry, update `DataOwnershipRegistry::registerDefaults()`. Use `platform:audit-tenant-scoping` to detect unscoped rows.

---

## CI Enforcement

The `ARCHITECTURE_CHECKS.md` file in the repository root defines 10 automated checks to enforce architecture rules. All checks should run in CI on every PR.

1. **No cross-module DB::table()** — grep violation
2. **No cross-module Eloquent imports** — grep violation
3. **All cross-module resolution via IntegrationRegistry** — grep for `app(Service::class)` across modules
4. **Migration scope** — tables modified only by owning module
5. **Event ownership** — events dispatched only by owning module
6. **Contract interface changes** — flag for Platform Core review
7. **Circular dependencies** — module dependency graph validation
8. **Tenant scoping** — all rows have organization_id
9. **Manifest validation** — all modules publish valid manifests
10. **No cross-module events in controllers** — controllers must use services

## ADR Process

Architecture Decision Records (ADRs) document significant architectural decisions. Use the template at `docs/adr/TEMPLATE.md`.

### When to Write an ADR
- Adding a new integration contract
- Changing a contract interface (breaking change)
- Adding a new cross-module communication pattern
- Changing tenant isolation strategy
- Adding a new infrastructure dependency (cache, queue, database)
- Any decision that affects multiple modules

### ADR States
- **Proposed** — under review
- **Accepted** — approved and implemented
- **Deprecated** — still in use but should not be used for new work
- **Superseded** — replaced by a newer ADR

### ADR Workflow
1. Copy `docs/adr/TEMPLATE.md` to `docs/adr/ADR-NNN.md`
2. Fill in Context, Decision, Alternatives, Consequences
3. Submit for review in PR
4. Once accepted, update implementation to match

---

## Quick Reference

| If you need to... | Use... | Example |
|---|---|---|
| Read data from another module | IntegrationRegistry::resolve(ContractInterface) | `$inv = IntegrationRegistry::resolve(InventoryProvider::class)` |
| Trigger action in another module | Dispatch an owned domain event | `event(new InvoiceCreated($data))` |
| Add a column to own table | Migration in `database/migrations/your_module/` | `Schema::table('your_table', ...)` |
| Add a column to another module's table | File ADR, get approval, migrate in Core | Core platform refactoring workflow |
| Resolve a service within your module | Constructor injection | `class MyService { public function __construct(private OwnRepo $repo) }` |
| Register a ServiceProvider | `bootstrap/providers.php` | Laravel 11+ convention |
| Document an architecture decision | ADR template at `docs/adr/TEMPLATE.md` | Copy to `docs/adr/ADR-NNN.md` |
| Run architecture CI checks locally | See `ARCHITECTURE_CHECKS.md` | 10 checks documented |
