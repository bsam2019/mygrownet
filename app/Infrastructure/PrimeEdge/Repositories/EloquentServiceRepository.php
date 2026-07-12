<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Service;
use App\Domain\PrimeEdge\Repositories\ServiceRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ServiceId;
use App\Domain\PrimeEdge\ValueObjects\ServiceCategory;
use App\Infrastructure\PrimeEdge\Persistence\ServiceModel;
use DateTimeImmutable;

class EloquentServiceRepository implements ServiceRepositoryInterface
{
    public function save(Service $service): void
    {
        ServiceModel::updateOrCreate(
            ['id' => $service->id()->toString()],
            [
                'name' => $service->name(),
                'description' => $service->description(),
                'category' => $service->category()->value,
                'active' => $service->isActive(),
                'sort_order' => $service->sortOrder(),
            ]
        );
    }

    public function findById(ServiceId $id): ?Service
    {
        $model = ServiceModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return ServiceModel::orderBy('sort_order')->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCategory(ServiceCategory $category): array
    {
        return ServiceModel::where('category', $category->value)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findActive(): array
    {
        return ServiceModel::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): ServiceId
    {
        return ServiceId::generate();
    }

    private function toDomainEntity(ServiceModel $model): Service
    {
        return Service::reconstitute(
            id: ServiceId::fromString($model->id),
            name: $model->name,
            description: $model->description,
            category: ServiceCategory::from($model->category),
            active: $model->active,
            sortOrder: $model->sort_order,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
