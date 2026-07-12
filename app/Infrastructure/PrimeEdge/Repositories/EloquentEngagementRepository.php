<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Engagement;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use App\Domain\PrimeEdge\ValueObjects\EngagementType;
use App\Domain\PrimeEdge\ValueObjects\EngagementStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Infrastructure\PrimeEdge\Persistence\EngagementModel;
use DateTimeImmutable;

class EloquentEngagementRepository implements EngagementRepositoryInterface
{
    public function save(Engagement $engagement): void
    {
        EngagementModel::updateOrCreate(
            ['id' => $engagement->id()->toString()],
            [
                'client_id' => $engagement->clientId()->toString(),
                'type' => $engagement->type()->value,
                'description' => $engagement->description(),
                'scope' => $engagement->scope(),
                'status' => $engagement->status()->value,
                'agreed_fee_amount' => $engagement->agreedFee()?->amount(),
                'agreed_fee_currency' => $engagement->agreedFee()?->currency(),
                'notes' => $engagement->notes(),
                'started_at' => $engagement->startedAt(),
                'completed_at' => $engagement->completedAt(),
            ]
        );
    }

    public function findById(EngagementId $id): ?Engagement
    {
        $model = EngagementModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return EngagementModel::where('client_id', $clientId->toString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return EngagementModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(string $status): array
    {
        return EngagementModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findActiveByClientId(ClientId $clientId): array
    {
        return EngagementModel::where('client_id', $clientId->toString())
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): EngagementId
    {
        return EngagementId::generate();
    }

    private function toDomainEntity(EngagementModel $model): Engagement
    {
        return Engagement::reconstitute(
            id: EngagementId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            type: EngagementType::from($model->type),
            description: $model->description,
            scope: $model->scope,
            status: EngagementStatus::from($model->status),
            agreedFee: $model->agreed_fee_amount ? Money::fromKwacha((float) $model->agreed_fee_amount) : null,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            startedAt: $model->started_at ? new DateTimeImmutable($model->started_at->toDateTimeString()) : null,
            completedAt: $model->completed_at ? new DateTimeImmutable($model->completed_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
