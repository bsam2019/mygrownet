<?php

namespace App\Application\Support\UseCases;

use App\Domain\Support\Services\TicketService;
use App\Domain\Support\ValueObjects\TicketId;
use App\Domain\Support\ValueObjects\UserId;

class AddCommentUseCase
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    public function execute(
        int $ticketId,
        int $userId,
        string $comment,
        bool $isInternal = false
    ): void {
        $this->ticketService->addComment(
            ticketId: TicketId::fromInt($ticketId),
            userId: UserId::fromInt($userId),
            comment: $comment,
            isInternal: $isInternal
        );
    }
}
