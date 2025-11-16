<?php

namespace App\Application\Support\UseCases;

use App\Application\Support\DTOs\TicketDTO;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\ValueObjects\UserId;

class GetUserTicketsUseCase
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function execute(int $userId): array
    {
        $tickets = $this->ticketRepository->findByUserId(
            UserId::fromInt($userId)
        );

        return array_map(
            fn($ticket) => TicketDTO::fromEntity($ticket),
            $tickets
        );
    }
}
