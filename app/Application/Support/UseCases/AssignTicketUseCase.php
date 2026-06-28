<?php

namespace App\Application\Support\UseCases;

use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;

class AssignTicketUseCase
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function execute(int $ticketId, int $adminId): void
    {
        $ticket = $this->ticketRepository->findById(
            TicketId::fromInt($ticketId)
        );

        if (!$ticket) {
            throw new \DomainException('Ticket not found');
        }

        $ticket->assignTo(UserId::fromInt($adminId));
        $this->ticketRepository->save($ticket);
    }
}
