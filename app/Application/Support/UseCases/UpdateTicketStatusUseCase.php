<?php

namespace App\Application\Support\UseCases;

use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\TicketStatus;

class UpdateTicketStatusUseCase
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function execute(int $ticketId, string $status): void
    {
        $ticket = $this->ticketRepository->findById(
            TicketId::fromInt($ticketId)
        );

        if (!$ticket) {
            throw new \DomainException('Ticket not found');
        }

        $ticket->updateStatus(TicketStatus::fromString($status));
        $this->ticketRepository->save($ticket);
    }
}
