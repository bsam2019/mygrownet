<?php

namespace App\Application\PrimeEdge\DTOs;

use App\Domain\PrimeEdge\Entities\Engagement;

class EngagementDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientId,
        public readonly string $type,
        public readonly string $typeLabel,
        public readonly string $description,
        public readonly ?string $scope,
        public readonly string $status,
        public readonly ?array $agreedFee,
        public readonly ?string $notes,
        public readonly string $createdAt,
        public readonly ?string $startedAt,
        public readonly ?string $completedAt,
    ) {}

    public static function fromEntity(Engagement $engagement): self
    {
        return new self(
            id: $engagement->id()->toString(),
            clientId: $engagement->clientId()->toString(),
            type: $engagement->type()->value,
            typeLabel: $engagement->type()->label(),
            description: $engagement->description(),
            scope: $engagement->scope(),
            status: $engagement->status()->value,
            agreedFee: $engagement->agreedFee()?->toArray(),
            notes: $engagement->notes(),
            createdAt: $engagement->createdAt()->format('Y-m-d H:i:s'),
            startedAt: $engagement->startedAt()?->format('Y-m-d H:i:s'),
            completedAt: $engagement->completedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
