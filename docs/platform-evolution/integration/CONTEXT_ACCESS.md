# PlatformContext Access Guide

> **Status:** Active  
> **Version:** 1.0  
> **Phase:** 1.2.6 — PlatformCore & Runtime Layer  
> **Applies to:** All MyGrowNet services, jobs, commands, and tests

---

## What is PlatformContext?

`PlatformContext` is an immutable value object that carries the execution context through every layer of the platform. It is available as a singleton in the Laravel container and is populated at the start of every HTTP request by the middleware stack.

### Fields

```php
class PlatformContext
{
    public readonly string $traceId;         // UUID — same for the entire request chain
    public readonly string $requestId;       // UUID — unique per HTTP request
    public readonly string $userId;          // ID of the authenticated user (empty string if guest)
    public readonly string $organizationId;  // ID of the current organization (empty string if none)
    public readonly string $applicationId;   // ID of the current application (e.g. 'stockflow')
    public readonly ?string $installationId; // Application installation record ID (nullable)
    public readonly string $workspaceId;     // Workspace context identifier
    public readonly string $locale;          // User locale, default 'en'
    public readonly string $timezone;        // User timezone, default 'UTC'
}
```

### traceId vs requestId

| Field | Purpose | Scope | Example |
|---|---|---|---|
| `traceId` | Correlates all events across service boundaries for a single user action | Propagated to events, logs, and downstream calls | `550e8400-e29b-41d4-a716-446655440000` |
| `requestId` | Identifies the specific HTTP request that originated the action | Unique per request, used for request-level debugging | `req-abc-123-def-456` |

In queue jobs, `requestId` is prefixed with `cli:` since there is no originating HTTP request.

---

## Accessing Context

### In Services (via Container)

The standard pattern for domain services. The `PlatformContext` is registered as a container singleton by the middleware stack.

```php
use App\Domain\Core\ValueObjects\PlatformContext;

class SomeDomainService
{
    public function doSomething(): void
    {
        $context = app(PlatformContext::class);

        if ($context->organizationId) {
            // Scoped to organization
        }

        logger('Operation completed', [
            'trace_id' => $context->traceId,
            'user_id' => $context->userId,
        ]);
    }
}
```

**Important:** Only use `app(PlatformContext::class)` in services that are invoked within an HTTP request context (after middleware has run). For constructor injection, use `PlatformContextResolver` instead (see below).

### In Controllers (via Request Attributes)

The middleware stack attaches the context to the request object:

```php
use App\Domain\Core\ValueObjects\PlatformContext;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var PlatformContext $context */
        $context = $request->attributes->get('platform_context');

        // Or through the workspace context:
        $workspaceContext = $request->attributes->get('workspace_context');

        return inertia('StockFlow/Inventory/Index', [
            'context' => $context->toArray(),
        ]);
    }
}
```

The `platform_context` attribute is set by the `SetPlatformContext` middleware after domain resolution is complete.

### In Queue Jobs (via PlatformContextResolver)

Queue jobs do not have an HTTP request context. Use `PlatformContextResolver` to establish context before job execution:

```php
use App\Domain\Core\Services\PlatformContextResolver;

class SyncInvoiceToAccounting implements ShouldQueue
{
    public function __construct(
        private int $organizationId,
        private int $invoiceId,
    ) {}

    public function handle(PlatformContextResolver $resolver): void
    {
        // Establish context for this job
        $context = $resolver->forJob(
            userId: '', // or specific user ID if available
            organizationId: (string) $this->organizationId,
            applicationId: 'bms',
        );

        // $context is now available via app(PlatformContext::class)
        $this->syncInvoice();
    }
}
```

**`forJob()` method** creates a fresh PlatformContext with:
- A new UUID for `traceId`
- A `cli:` prefixed `requestId`
- The provided `userId`, `organizationId`, `applicationId`
- Registers it in the container via `setContext()`

### In Artisan Commands

```php
use App\Domain\Core\Services\PlatformContextResolver;

class ProcessEventOutbox extends Command
{
    protected $signature = 'platform:process-outbox';

    public function handle(PlatformContextResolver $resolver): int
    {
        $context = $resolver->forJob(
            applicationId: 'platform-core',
        );

        // Now app(PlatformContext::class) returns $context
        $outbox = app(OutboxService::class);
        return $outbox->publishPending();
    }
}
```

### For Testing

Use `PlatformContext::make()` directly to create a context for tests:

```php
use App\Domain\Core\ValueObjects\PlatformContext;

class InventoryServiceTest extends TestCase
{
    private PlatformContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = PlatformContext::make(
            userId: '42',
            organizationId: '7',
            applicationId: 'stockflow',
        );

        app()->instance(PlatformContext::class, $this->context);
    }

    public function test_get_stock_level(): void
    {
        $service = app(InventoryService::class);
        $level = $service->getStockLevel($this->context, 1);

        $this->assertGreaterThanOrEqual(0, $level);
    }
}
```

---

## Context Flow Through Middleware Stack

```
Request arrives
    │
    ▼
DetectSubdomain                         ← Resolves subdomain/custom domain
    │                                        Sets stockflow_company_id, identity_gateway, etc.
    │
    ▼
ResolveDomainContext                    ← Looks up domains table
    │                                        Returns DomainResolution {type, domain, org, app}
    │                                        Attaches workspace_context to request attributes
    │
    ▼
SetPlatformContext                      ← Builds PlatformContext from resolution + user
    │                                        Creates PlatformContext in container
    │                                        Shares context to Inertia (workspace.platform_context)
    │                                        Attaches platform_context to request attributes
    │
    ▼
Controller / Service
    │
    ├── $request->attributes->get('platform_context')     ← In controllers
    ├── app(PlatformContext::class)                        ← In services (after setup)
    └── app(PlatformContextResolver::class)->current()     ← Safe anywhere (returns null if unset)
```

### PlatformContextResolver — Safe Access Anywhere

```php
class PlatformContextResolver
{
    // Resolves or creates context (memoized)
    public function resolve(): PlatformContext;          // Returns current or fallback (never null)

    // Safe to call anywhere — returns null if context not yet set up
    public function current(): ?PlatformContext;

    // Explicitly set context (used by middleware and forJob)
    public function setContext(PlatformContext $context): void;

    // Creates a fallback context for CLI/queue scenarios
    public function fallback(): PlatformContext;

    // Creates context for queue jobs and registers it
    public function forJob(string $userId, string $orgId, string $appId): PlatformContext;
}
```

**Rule of thumb:**
- In controllers: use `$request->attributes->get('platform_context')`
- In services invoked from controllers: inject `PlatformContext` in constructor or use `app(PlatformContext::class)`
- In queue jobs: use `PlatformContextResolver::forJob()` at the start
- In tests: use `PlatformContext::make()` and `app()->instance()`
- In code that might run before middleware (booting, providers): use `PlatformContextResolver::current()` (returns null if not set)
