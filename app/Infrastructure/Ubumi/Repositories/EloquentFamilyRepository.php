<?php

namespace App\Infrastructure\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Family;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\FamilyName;
use App\Domain\Ubumi\ValueObjects\Slug;
use App\Infrastructure\Ubumi\Eloquent\FamilyModel;

class EloquentFamilyRepository implements FamilyRepositoryInterface
{
    public function save(Family $family): void
    {
        FamilyModel::updateOrCreate(
            ['id' => $family->getId()->toString()],
            [
                'name' => $family->getName()->toString(),
                'slug' => $family->getSlug()->value(),
                'admin_user_id' => $family->getAdminUserId(),
            ]
        );
    }

    public function findById(FamilyId $id): ?Family
    {
        $model = FamilyModel::find($id->toString());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Family
    {
        $model = FamilyModel::where('slug', $slug)->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function slugExists(string $slug, ?FamilyId $excludeId = null): bool
    {
        $query = FamilyModel::where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId->toString());
        }
        
        return $query->exists();
    }

    public function findByAdminUserId(int $userId): array
    {
        $models = FamilyModel::where('admin_user_id', $userId)->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(FamilyId $id): void
    {
        FamilyModel::where('id', $id->toString())->delete();
    }

    public function exists(FamilyId $id): bool
    {
        return FamilyModel::where('id', $id->toString())->exists();
    }

    private function toDomainEntity(FamilyModel $model): Family
    {
        return Family::reconstitute(
            FamilyId::fromString($model->id),
            FamilyName::fromString($model->name),
            Slug::fromString($model->slug),
            $model->admin_user_id,
            \DateTimeImmutable::createFromMutable($model->created_at),
            $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at) : null
        );
    }
}
