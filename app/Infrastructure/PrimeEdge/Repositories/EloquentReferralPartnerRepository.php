<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\ReferralPartner;
use App\Domain\PrimeEdge\Repositories\ReferralPartnerRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\PartnerId;
use App\Domain\PrimeEdge\ValueObjects\PartnerType;
use App\Infrastructure\PrimeEdge\Persistence\ReferralPartnerModel;
use DateTimeImmutable;

class EloquentReferralPartnerRepository implements ReferralPartnerRepositoryInterface
{
    public function save(ReferralPartner $partner): void
    {
        ReferralPartnerModel::updateOrCreate(
            ['id' => $partner->id()->toString()],
            [
                'name' => $partner->name(),
                'contact_person' => $partner->contactPerson(),
                'email' => $partner->email(),
                'phone' => $partner->phone(),
                'type' => $partner->type()->value,
                'specialization' => $partner->specialization(),
                'active' => $partner->isActive(),
                'notes' => $partner->notes(),
            ]
        );
    }

    public function findById(PartnerId $id): ?ReferralPartner
    {
        $model = ReferralPartnerModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findAll(): array
    {
        return ReferralPartnerModel::orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByType(string $type): array
    {
        return ReferralPartnerModel::where('type', $type)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findActive(): array
    {
        return ReferralPartnerModel::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): PartnerId
    {
        return PartnerId::generate();
    }

    private function toDomainEntity(ReferralPartnerModel $model): ReferralPartner
    {
        return ReferralPartner::reconstitute(
            id: PartnerId::fromString($model->id),
            name: $model->name,
            contactPerson: $model->contact_person,
            email: $model->email,
            phone: $model->phone,
            type: PartnerType::from($model->type),
            specialization: $model->specialization,
            active: $model->active,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
