<?php

declare(strict_types=1);

namespace App\Infrastructure\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\Stage as StageEntity;
use App\Domain\GrowStart\Repositories\StageRepositoryInterface;
use App\Domain\GrowStart\ValueObjects\StageSlug;
use App\Models\GrowStart\Stage;
use Illuminate\Support\Collection;

class EloquentStageRepository implements StageRepositoryInterface
{
    public function findById(int $id): ?StageEntity
    {
        $model = Stage::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findBySlug(string $slug): ?StageEntity
    {
        $model = Stage::where('slug', $slug)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findAll(): Collection
    {
        return Stage::ordered()->get()->map(fn($model) => $this->toEntity($model));
    }

    public function findActive(): Collection
    {
        return Stage::active()->ordered()->get()->map(fn($model) => $this->toEntity($model));
    }

    public function findFirst(): ?StageEntity
    {
        $model = Stage::active()->ordered()->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findNext(int $currentStageId): ?StageEntity
    {
        $current = Stage::find($currentStageId);
        if (!$current) {
            return null;
        }

        $model = Stage::where('order', '>', $current->order)
            ->where('is_active', true)
            ->orderBy('order')
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findPrevious(int $currentStageId): ?StageEntity
    {
        $current = Stage::find($currentStageId);
        if (!$current) {
            return null;
        }

        $model = Stage::where('order', '<', $current->order)
            ->where('is_active', true)
            ->orderBy('order', 'desc')
            ->first();

        return $model ? $this->toEntity($model) : null;
    }

    private function toEntity(Stage $model): StageEntity
    {
        return StageEntity::reconstitute(
            id: $model->id,
            name: $model->name,
            slug: StageSlug::fromString($model->slug),
            description: $model->description,
            order: $model->order,
            icon: $model->icon,
            color: $model->color,
            estimatedDays: $model->estimated_days,
            isActive: $model->is_active
        );
    }
}
