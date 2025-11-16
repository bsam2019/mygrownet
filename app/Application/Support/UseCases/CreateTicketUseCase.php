<?php

namespace App\Application\Support\UseCases;

use App\Application\Support\DTOs\CreateTicketDTO;
use App\Application\Support\DTOs\TicketDTO;
use App\Domain\Support\Entities\Ticket;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketCategory;
use App\Domain\Support\ValueObjects\TicketPriority;
use App\Domain\Support\ValueObjects\TicketContent;

class CreateTicketUseCase
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function execute(CreateTicketDTO $dto): TicketDTO
    {
        $ticket = Ticket::create(
            id: $this->ticketRepository->nextId(),
            userId: UserId::fromInt($dto->userId),
            category: TicketCategory::fromString($dto->category),
            priority: TicketPriority::fromString($dto->priority),
            subject: $dto->subject,
            description: TicketContent::fromString($dto->description)
        );

        $this->ticketRepository->save($ticket);

        return TicketDTO::fromEntity($ticket);
    }
}
