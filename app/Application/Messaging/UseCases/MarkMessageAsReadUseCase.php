<?php

namespace App\Application\Messaging\UseCases;

use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;

class MarkMessageAsReadUseCase
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    public function execute(int $messageId, int $userId): void
    {
        $this->messagingService->markAsRead(
            MessageId::fromInt($messageId),
            UserId::fromInt($userId)
        );
    }
}
