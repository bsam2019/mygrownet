# Architecture & Integration Checks

Automated checks to enforce platform architecture rules. Integrate into CI pipeline.

## Check 1: No Cross-Module DB::table()

**Script:** `bash ci/check-db-cross-module.sh`
**Rule:** Files in module X must not query tables owned by module Y.
**Implementation:** Grep for `DB::table('` and verify the table matches the owning module.

```bash
# Example check
rg "DB::table\('" app/Domain/StockFlow/ --no-filename | rg -v "sa_" && echo "FAIL: StockFlow queries non-StockFlow table"
```

## Check 2: No Cross-Module Eloquent Imports

**Script:** `bash ci/check-eloquent-imports.sh`
**Rule:** Files in module X must not `use` an Eloquent model from a different module.
**Implementation:** Grep for `use App\Infrastructure\Persistence\Eloquent\*` and verify module boundary.

```bash
# Example check
rg "use App\\\Infrastructure\\\Persistence\\\Eloquent" app/Domain/StockFlow/ --no-filename && echo "FAIL: StockFlow domain imports Eloquent"
```

## Check 3: All Cross-Module Resolution via IntegrationRegistry

**Script:** `bash ci/check-integration-registry.sh`
**Rule:** No `app(Service::class)` calls should cross module boundaries.
**Implementation:** Grep for `app(...)` in domain/service layers across modules (excluding the owning module).

```bash
# Example check
rg "app\(" app/Domain/GrowNet/ --no-filename | rg -v "GrowNet" && echo "FAIL: May bypass IntegrationRegistry"
```

## Check 4: Migration Scope

**Script:** `bash ci/check-migration-scope.sh`
**Rule:** Migrations in `database/migrations/{module}/` must only `CREATE`/`ALTER`/`DROP` tables owned by that module.
**Implementation:** Parse migration files and verify table ownership via `DataOwnershipRegistry`.

## Check 5: Event Ownership

**Script:** `bash ci/check-event-ownership.sh`
**Rule:** Events must be dispatched only by their owning module.
**Implementation:** Search for `event(new ` or `Event::dispatch(` and verify the event class namespace matches the owning module.

## Check 6: Contract Interface Changes

**Script:** `bash ci/check-contract-changes.sh`
**Rule:** Changes to contract interfaces require Platform Core team review.
**Implementation:** Check diff for files matching `app/Domain/*/Contracts/*.php` and flag for review.

## Check 7: Circular Dependencies

**Script:** `bash ci/check-circular-deps.sh`
**Rule:** Module A → Module B → Module A is not allowed.
**Implementation:** Parse manifests for `listens` and `requiredCapabilities` to build dependency graph.

## Check 8: Tenant Scoping

**Script:** `bash ci/check-tenant-scoping.sh`
**Rule:** All queries on tenant-scoped tables must filter by `organization_id`.
**Implementation:** Run `php artisan platform:audit-tenant-scoping` and fail if unscoped rows exist.

## Check 9: Manifest Validation

**Script:** `bash ci/check-manifests.sh`
**Rule:** Every module must publish a valid manifest.
**Implementation:** Run manifest validation in application bootstrap. Fail if any manifest is invalid.

## Check 10: No Cross-Module Events in Controllers

**Script:** `bash ci/check-event-controllers.sh`
**Rule:** Controllers should not dispatch domain events directly. Use domain services or `EventDispatcher`.
**Implementation:** Grep for `event(new ` or `Event::dispatch(` in controller files.
