<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\Entities\Ticket;
use App\Domain\Support\Entities\TicketComment;
use App\Domain\Support\Repositories\TicketRepository;
use App\Domain\Support\Repositories\TicketCommentRepository;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;
use App\Domain\Support\ValueObjects\TicketStatus;

class TicketService
{
    public function __construct(
        private TicketRepository $ticketRepository,
        private TicketCommentRepository $commentRepository
    ) {}

    public function addComment(
        TicketId $ticketId,
        UserId $userId,
        string $comment,
        bool $isInternal = false
    ): TicketComment {
        $ticket = $this->ticketRepository->findById($ticketId);
        
        if (!$ticket) {
            throw new \DomainException('Ticket not found');
        }

        if ($ticket->status()->isClosed()) {
            throw new \DomainException('Cannot add comments to a closed ticket');
        }

        $ticketComment = TicketComment::create(
            id: $this->commentRepository->nextId(),
            ticketId: $ticketId,
            userId: $userId,
            comment: $comment,
            isInternal: $isInternal
        );

        $this->commentRepository->save($ticketComment);

        // If member adds comment and ticket is waiting, move to in progress
        if ($ticket->status()->isWaiting() && !$isInternal) {
            $ticket->updateStatus(TicketStatus::inProgress());
            $this->ticketRepository->save($ticket);
        }

        return $ticketComment;
    }

    public function getTicketWithComments(TicketId $ticketId, bool $includeInternal = false): array
    {
        $ticket = $this->ticketRepository->findById($ticketId);
        
        if (!$ticket) {
            throw new \DomainException('Ticket not found');
        }

        $comments = $this->commentRepository->findByTicketId($ticketId, $includeInternal);

        return [
            'ticket' => $ticket,
            'comments' => $comments
        ];
    }
}
