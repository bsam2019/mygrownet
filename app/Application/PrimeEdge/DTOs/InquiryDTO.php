<?php

namespace App\Application\PrimeEdge\DTOs;

use App\Domain\PrimeEdge\Entities\Inquiry;

class InquiryDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $clientId,
        public readonly string $serviceDescription,
        public readonly ?string $preferredServiceType,
        public readonly string $status,
        public readonly ?array $quotedAmount,
        public readonly ?string $quoteNotes,
        public readonly ?string $notes,
        public readonly string $createdAt,
        public readonly ?string $quotedAt,
        public readonly ?string $respondedAt,
    ) {}

    public static function fromEntity(Inquiry $inquiry): self
    {
        return new self(
            id: $inquiry->id()->toString(),
            clientId: $inquiry->clientId()->toString(),
            serviceDescription: $inquiry->serviceDescription(),
            preferredServiceType: $inquiry->preferredServiceType(),
            status: $inquiry->status()->value,
            quotedAmount: $inquiry->quotedAmount()?->toArray(),
            quoteNotes: $inquiry->quoteNotes(),
            notes: $inquiry->notes(),
            createdAt: $inquiry->createdAt()->format('Y-m-d H:i:s'),
            quotedAt: $inquiry->quotedAt()?->format('Y-m-d H:i:s'),
            respondedAt: $inquiry->respondedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
