<?php

namespace App\Infrastructure\Persistence\Eloquent\Library;

use App\Domain\Library\Entities\LibraryResource as DomainLibraryResource;
use App\Domain\Library\Repositories\LibraryResourceRepositoryInterface;
use App\Domain\Library\ValueObjects\ResourceType;
use App\Domain\Library\ValueObjects\ResourceCategory;
use App\Infrastructure\Persistence\Eloquent\Library\LibraryResourceModel;

class EloquentLibraryResourceRepository implements LibraryResourceRepositoryInterface
{
    public function findById(int $id): ?DomainLibraryResource
    {
        $model = LibraryResourceModel::find($id);
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return LibraryResourceModel::ordered()
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findActive(): array
    {
        return LibraryResourceModel::active()
            ->ordered()
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findFeatured(int $limit = 6): array
    {
        return LibraryResourceModel::active()
            ->featured()
            ->ordered()
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByCategory(ResourceCategory $category): array
    {
        return LibraryResourceModel::active()
            ->byCategory($category->value())
            ->ordered()
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function findByType(ResourceType $type): array
    {
        return LibraryResourceModel::active()
            ->byType($type->value())
            ->ordered()
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->all();
    }

    public function save(DomainLibraryResource $resource): void
    {
        $model = LibraryResourceModel::findOrNew($resource->id());
        
        $model->fill([
            'title' => $resource->title(),
            'description' => $resource->description(),
            'type' => $resource->type()->value(),
            'category' => $resource->category()->value(),
            'resource_url' => $resource->resourceUrl(),
            'author' => $resource->author(),
            'duration_minutes' => $resource->durationMinutes(),
            'difficulty' => $resource->difficulty(),
            'is_external' => $resource->isExternal(),
            'is_featured' => $resource->isFeatured(),
            'is_active' => $resource->isActive(),
            'view_count' => $resource->viewCount(),
        ]);
        
        $model->save();
    }

    public function delete(int $id): void
    {
        LibraryResourceModel::destroy($id);
    }

    public function getTotalCount(): int
    {
        return LibraryResourceModel::count();
    }

    public function getActiveCount(): int
    {
        return LibraryResourceModel::active()->count();
    }

    private function toDomainEntity(LibraryResourceModel $model): DomainLibraryResource
    {
        return DomainLibraryResource::create(
            $model->id,
            $model->title,
            $model->description,
            ResourceType::fromString($model->type),
            ResourceCategory::fromString($model->category),
            $model->resource_url,
            $model->author,
            $model->duration_minutes,
            $model->difficulty,
            $model->is_external,
            $model->is_featured,
            $model->is_active
        );
    }
}
