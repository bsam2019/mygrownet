<?php

namespace App\Infrastructure\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\Relationship;
use App\Domain\Ubumi\Repositories\RelationshipRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\RelationshipType;
use App\Infrastructure\Ubumi\Eloquent\RelationshipModel;

class EloquentRelationshipRepository implements RelationshipRepositoryInterface
{
    public function save(Relationship $relationship): void
    {
        $model = RelationshipModel::updateOrCreate(
            ['id' => $relationship->getId() ?: null],
            [
                'person_id' => $relationship->getPersonId()->toString(),
                'related_person_id' => $relationship->getRelatedPersonId()->toString(),
                'relationship_type' => $relationship->getRelationshipType()->toString(),
            ]
        );

        // Update the entity with the generated ID if it's new
        if ($relationship->getId() === 0) {
            $reflection = new \ReflectionClass($relationship);
            $property = $reflection->getProperty('id');
            $property->setAccessible(true);
            $property->setValue($relationship, $model->id);
        }
    }

    public function findById(int $id): ?Relationship
    {
        $model = RelationshipModel::find($id);

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByPersonId(PersonId $personId): array
    {
        $models = RelationshipModel::where('person_id', $personId->toString())
            ->orWhere('related_person_id', $personId->toString())
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findRelationship(PersonId $personId, PersonId $relatedPersonId): ?Relationship
    {
        $model = RelationshipModel::where(function($query) use ($personId, $relatedPersonId) {
            $query->where('person_id', $personId->toString())
                  ->where('related_person_id', $relatedPersonId->toString());
        })->orWhere(function($query) use ($personId, $relatedPersonId) {
            $query->where('person_id', $relatedPersonId->toString())
                  ->where('related_person_id', $personId->toString());
        })->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function exists(PersonId $personId, PersonId $relatedPersonId, RelationshipType $type): bool
    {
        return RelationshipModel::where(function($query) use ($personId, $relatedPersonId, $type) {
            $query->where('person_id', $personId->toString())
                  ->where('related_person_id', $relatedPersonId->toString())
                  ->where('relationship_type', $type->toString());
        })->orWhere(function($query) use ($personId, $relatedPersonId, $type) {
            // Check inverse for reciprocal relationships
            if ($type->isReciprocal()) {
                $query->where('person_id', $relatedPersonId->toString())
                      ->where('related_person_id', $personId->toString())
                      ->where('relationship_type', $type->toString());
            }
        })->exists();
    }

    public function delete(int $id): void
    {
        RelationshipModel::where('id', $id)->delete();
    }

    public function deleteAllForPerson(PersonId $personId): void
    {
        RelationshipModel::where('person_id', $personId->toString())
            ->orWhere('related_person_id', $personId->toString())
            ->delete();
    }

    private function toDomainEntity(RelationshipModel $model): Relationship
    {
        return Relationship::reconstitute(
            $model->id,
            PersonId::fromString($model->person_id),
            PersonId::fromString($model->related_person_id),
            RelationshipType::fromString($model->relationship_type),
            \DateTimeImmutable::createFromMutable($model->created_at),
            $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at) : null
        );
    }
}
