<?php

namespace App\Infrastructure\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Inquiry;
use App\Domain\PrimeEdge\Repositories\InquiryRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\InquiryStatus;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Infrastructure\PrimeEdge\Persistence\InquiryModel;
use DateTimeImmutable;

class EloquentInquiryRepository implements InquiryRepositoryInterface
{
    public function save(Inquiry $inquiry): void
    {
        InquiryModel::updateOrCreate(
            ['id' => $inquiry->id()->toString()],
            [
                'client_id' => $inquiry->clientId()->toString(),
                'service_description' => $inquiry->serviceDescription(),
                'preferred_service_type' => $inquiry->preferredServiceType(),
                'status' => $inquiry->status()->value,
                'quoted_amount' => $inquiry->quotedAmount()?->amount(),
                'quoted_currency' => $inquiry->quotedAmount()?->currency(),
                'quote_notes' => $inquiry->quoteNotes(),
                'notes' => $inquiry->notes(),
                'quoted_at' => $inquiry->quotedAt(),
                'responded_at' => $inquiry->respondedAt(),
            ]
        );
    }

    public function findById(InquiryId $id): ?Inquiry
    {
        $model = InquiryModel::find($id->toString());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByClientId(ClientId $clientId): array
    {
        return InquiryModel::where('client_id', $clientId->toString())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findAll(): array
    {
        return InquiryModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByStatus(string $status): array
    {
        return InquiryModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function nextId(): InquiryId
    {
        return InquiryId::generate();
    }

    private function toDomainEntity(InquiryModel $model): Inquiry
    {
        return Inquiry::reconstitute(
            id: InquiryId::fromString($model->id),
            clientId: ClientId::fromString($model->client_id),
            serviceDescription: $model->service_description,
            preferredServiceType: $model->preferred_service_type,
            status: InquiryStatus::from($model->status),
            quotedAmount: $model->quoted_amount ? Money::fromKwacha((float) $model->quoted_amount) : null,
            quoteNotes: $model->quote_notes,
            notes: $model->notes,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            quotedAt: $model->quoted_at ? new DateTimeImmutable($model->quoted_at->toDateTimeString()) : null,
            respondedAt: $model->responded_at ? new DateTimeImmutable($model->responded_at->toDateTimeString()) : null,
            updatedAt: $model->updated_at ? new DateTimeImmutable($model->updated_at->toDateTimeString()) : null,
        );
    }
}
