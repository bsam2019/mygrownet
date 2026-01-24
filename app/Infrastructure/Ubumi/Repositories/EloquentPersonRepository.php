<?php

namespace App\Infrastructure\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use App\Domain\Ubumi\ValueObjects\Slug;
use App\Infrastructure\Ubumi\Eloquent\PersonModel;

class EloquentPersonRepository implements PersonRepositoryInterface
{
    public function save(Person $person): void
    {
        PersonModel::withTrashed()->updateOrCreate(
            ['id' => $person->getId()->toString()],
            [
                'family_id' => $person->getFamilyId(),
                'name' => $person->getName()->toString(),
                'slug' => $person->getSlug()->value(),
                'photo_url' => $person->getPhotoUrl(),
                'date_of_birth' => $person->getDateOfBirth(),
                'approximate_age' => $person->getApproximateAge()?->getValue(),
                'gender' => $person->getGender(),
                'is_deceased' => $person->getIsDeceased(),
                'is_merged' => $person->getIsMerged(),
                'merged_from' => $person->getMergedFrom(),
                'created_by' => $person->getCreatedBy(),
                'deleted_at' => $person->getDeletedAt(),
            ]
        );
    }

    public function findById(PersonId $id): ?Person
    {
        $model = PersonModel::withTrashed()->find($id->toString());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findBySlug(string $slug): ?Person
    {
        $model = PersonModel::where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByFamilyAndSlug(string $familyId, string $slug): ?Person
    {
        $model = PersonModel::where('family_id', $familyId)
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function slugExistsInFamily(string $familyId, string $slug, ?PersonId $excludeId = null): bool
    {
        $query = PersonModel::where('family_id', $familyId)
            ->where('slug', $slug)
            ->whereNull('deleted_at');
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId->toString());
        }
        
        return $query->exists();
    }

    public function findByFamilyId(string $familyId): array
    {
        $models = PersonModel::where('family_id', $familyId)
            ->whereNull('deleted_at')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findSimilar(string $familyId, PersonName $name, ?int $age = null): array
    {
        $query = PersonModel::where('family_id', $familyId)
            ->whereNull('deleted_at');

        // Simple name similarity using LIKE
        $searchName = $name->toString();
        $query->where(function($q) use ($searchName) {
            $q->where('name', 'LIKE', "%{$searchName}%")
              ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$searchName]);
        });

        // Age similarity if provided
        if ($age !== null) {
            $query->where(function($q) use ($age) {
                $q->whereRaw('ABS(YEAR(CURDATE()) - YEAR(date_of_birth) - ?) <= 5', [$age])
                  ->orWhereRaw('ABS(approximate_age - ?) <= 5', [$age]);
            });
        }

        $models = $query->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(PersonId $id): void
    {
        PersonModel::where('id', $id->toString())->forceDelete();
    }

    public function softDelete(PersonId $id): void
    {
        PersonModel::where('id', $id->toString())->delete();
    }

    public function restore(PersonId $id): void
    {
        PersonModel::withTrashed()
            ->where('id', $id->toString())
            ->restore();
    }

    public function exists(PersonId $id): bool
    {
        return PersonModel::where('id', $id->toString())->exists();
    }

    private function toDomainEntity(PersonModel $model): Person
    {
        return Person::reconstitute(
            PersonId::fromString($model->id),
            $model->family_id,
            PersonName::fromString($model->name),
            Slug::fromString($model->slug),
            $model->photo_url,
            $model->date_of_birth,
            $model->approximate_age ? ApproximateAge::fromInt($model->approximate_age) : null,
            $model->gender,
            $model->is_deceased,
            $model->is_merged,
            $model->merged_from,
            $model->created_by,
            \DateTimeImmutable::createFromMutable($model->created_at),
            $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at) : null,
            $model->deleted_at ? \DateTimeImmutable::createFromMutable($model->deleted_at) : null
        );
    }
}
