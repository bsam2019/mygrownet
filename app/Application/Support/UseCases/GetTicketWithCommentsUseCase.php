<?php

namespace App\Application\Support\UseCases;

use App\Domain\Support\Services\TicketService;
use App\Domain\Support\ValueObjects\TicketId;
use App\Application\Support\DTOs\TicketDTO;

class GetTicketWithCommentsUseCase
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    public function execute(int $ticketId, bool $includeInternal = false): array
    {
        $result = $this->ticketService->getTicketWithComments(
            ticketId: TicketId::fromInt($ticketId),
            includeInternal: $includeInternal
        );

        return [
            'ticket' => TicketDTO::fromEntity($result['ticket']),
            'comments' => $result['comments']
        ];
    }
}
