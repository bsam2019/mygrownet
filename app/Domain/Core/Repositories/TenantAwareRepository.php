<?php

namespace App\Domain\Core\Repositories;

use App\Domain\Core\Services\PlatformContextResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class TenantAwareRepository
{
    protected Model $model;

    public function __construct(
        protected PlatformContextResolver $contextResolver,
    ) {}

    protected function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    protected function scopeForCurrentTenant(Builder $query): Builder
    {
        $context = $this->contextResolver->current();

        if ($context && $context->organizationId) {
            $query->where($this->getTable() . '.organization_id', $context->organizationId);
        }

        return $query;
    }

    protected function tenantQuery(): Builder
    {
        return $this->scopeForCurrentTenant($this->newQuery());
    }

    protected function findForTenant(int $id): ?Model
    {
        return $this->tenantQuery()->find($id);
    }

    protected function findForTenantOrFail(int $id): Model
    {
        $result = $this->findForTenant($id);

        if (!$result) {
            throw (new ModelNotFoundException)->setModel(get_class($this->model), $id);
        }

        return $result;
    }

    protected function createForTenant(array $data): Model
    {
        $context = $this->contextResolver->current();

        if ($context && $context->organizationId && !isset($data['organization_id'])) {
            $data['organization_id'] = (int) $context->organizationId;
        }

        return $this->model->create($data);
    }

    protected function updateForTenant(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    protected function deleteForTenant(Model $model): bool
    {
        return $model->delete();
    }

    protected function paginateForTenant(int $perPage = 15): mixed
    {
        return $this->tenantQuery()->paginate($perPage);
    }

    protected function existsForTenant(array $conditions): bool
    {
        return $this->tenantQuery()->where($conditions)->exists();
    }

    protected function countForTenant(): int
    {
        return $this->tenantQuery()->count();
    }

    abstract protected function getTable(): string;
}
