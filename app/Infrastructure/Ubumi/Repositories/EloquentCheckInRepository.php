<?php

namespace App\Infrastructure\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\Repositories\CheckInRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\CheckInId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use App\Infrastructure\Ubumi\Eloquent\CheckInModel;
use App\Infrastructure\Ubumi\Eloquent\PersonModel;
use DateTimeImmutable;

class EloquentCheckInRepository implements CheckInRepositoryInterface
{
    public function save(CheckIn $checkIn): void
    {
        CheckInModel::updateOrCreate(
            ['id' => $checkIn->id()->value()],
            [
                'person_id' => $checkIn->personId()->value(),
                'status' => $checkIn->status()->value,
                'note' => $checkIn->note(),
                'location' => $checkIn->location(),
                'photo_url' => $checkIn->photoUrl(),
                'checked_in_at' => $checkIn->checkedInAt()->format('Y-m-d H:i:s'),
                'updated_at' => $checkIn->updatedAt()->format('Y-m-d H:i:s'),
            ]
        );
    }

    public function findById(CheckInId $id): ?CheckIn
    {
        $model = CheckInModel::find($id->value());

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByPersonId(PersonId $personId, int $limit = 10): array
    {
        $models = CheckInModel::where('person_id', $personId->value())
            ->orderBy('checked_in_at', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findLatestByPersonId(PersonId $personId): ?CheckIn
    {
        $model = CheckInModel::where('person_id', $personId->value())
            ->orderBy('checked_in_at', 'desc')
            ->first();

        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByFamilyId(FamilyId $familyId, int $limit = 50): array
    {
        $models = CheckInModel::whereHas('person', function ($query) use ($familyId) {
                $query->where('family_id', $familyId->value());
            })
            ->orderBy('checked_in_at', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findRecentByFamilyId(FamilyId $familyId, int $hours = 24): array
    {
        $since = now()->subHours($hours);

        $models = CheckInModel::whereHas('person', function ($query) use ($familyId) {
                $query->where('family_id', $familyId->value());
            })
            ->where('checked_in_at', '>=', $since)
            ->orderBy('checked_in_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function countByPersonId(PersonId $personId): int
    {
        return CheckInModel::where('person_id', $personId->value())->count();
    }

    public function delete(CheckInId $id): void
    {
        CheckInModel::where('id', $id->value())->delete();
    }

    private function toDomainEntity(CheckInModel $model): CheckIn
    {
        return CheckIn::reconstitute(
            id: CheckInId::fromString($model->id),
            personId: PersonId::fromString($model->person_id),
            status: CheckInStatus::from($model->status),
            note: $model->note,
            location: $model->location,
            photoUrl: $model->photo_url,
            checkedInAt: new DateTimeImmutable($model->checked_in_at),
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
