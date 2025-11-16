<?php

namespace App\Application\Support\DTOs;

use App\Domain\Support\Entities\Ticket;

class TicketDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $category,
        public readonly string $priority,
        public readonly string $status,
        public readonly string $subject,
        public readonly string $description,
        public readonly ?int $assignedTo,
        public readonly string $createdAt,
        public readonly ?string $resolvedAt,
        public readonly ?string $closedAt,
        public readonly ?int $satisfactionRating
    ) {}

    public static function fromEntity(Ticket $ticket): self
    {
        return new self(
            id: $ticket->id()->value(),
            userId: $ticket->userId()->value(),
            category: $ticket->category()->value,
            priority: $ticket->priority()->value,
            status: $ticket->status()->value,
            subject: $ticket->subject(),
            description: $ticket->description()->value(),
            assignedTo: $ticket->assignedTo()?->value(),
            createdAt: $ticket->createdAt()->format('Y-m-d H:i:s'),
            resolvedAt: $ticket->resolvedAt()?->format('Y-m-d H:i:s'),
            closedAt: $ticket->closedAt()?->format('Y-m-d H:i:s'),
            satisfactionRating: $ticket->satisfactionRating()
        );
    }
}
