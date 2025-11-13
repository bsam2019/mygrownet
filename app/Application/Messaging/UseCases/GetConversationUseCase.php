<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\MessageDTO;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;

class GetConversationUseCase
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    public function execute(int $userId, int $otherUserId, int $limit = 50): array
    {
        $messages = $this->messagingService->getConversation(
            UserId::fromInt($userId),
            UserId::fromInt($otherUserId),
            $limit
        );

        // Load user names
        $users = User::whereIn('id', [$userId, $otherUserId])->pluck('name', 'id')->toArray();

        return array_map(
            fn($message) => MessageDTO::fromDomain(
                $message,
                $users[$message->senderId()->value()] ?? 'Unknown',
                $users[$message->recipientId()->value()] ?? 'Unknown'
            ),
            $messages
        );
    }
}
