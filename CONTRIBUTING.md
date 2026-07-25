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
