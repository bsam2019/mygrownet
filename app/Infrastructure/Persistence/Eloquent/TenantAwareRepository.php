<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class TenantAwareRepository
{
    protected string $tenantColumn = 'organization_id';

    public function __construct(
        protected PlatformContext $context,
    ) {}

    abstract protected function model(): Model;

    protected function newQuery(): Builder
    {
        return $this->model()->newQuery();
    }

    protected function scope(Builder $query): Builder
    {
        return $query->where($this->tenantColumn, $this->context->organizationId);
    }

    public function findAll(): array
    {
        return $this->scope($this->newQuery())->get()->all();
    }

    public function findById(string $id): ?Model
    {
        return $this->scope($this->newQuery())->where('id', $id)->first();
    }

    public function exists(string $id): bool
    {
        return $this->scope($this->newQuery())->where('id', $id)->exists();
    }

    public function count(): int
    {
        return $this->scope($this->newQuery())->count();
    }

    protected function query(): Builder
    {
        return $this->scope($this->newQuery());
    }

    public function create(array $data): Model
    {
        $data[$this->tenantColumn] = $this->context->organizationId;
        return $this->model()->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        $this->assertOwns($model);
        return $model->update($data);
    }

    public function delete(Model $model): bool
    {
        $this->assertOwns($model);
        return $model->delete();
    }

    protected function assertOwns(Model $model): void
    {
        $tenantId = $model->getAttribute($this->tenantColumn);
        if ((string) $tenantId !== $this->context->organizationId) {
            throw new \RuntimeException('Tenant isolation violation: cannot access record from another organization');
        }
    }
}
