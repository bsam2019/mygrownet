<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\MessageDTO;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;

class GetSentMessagesUseCase
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    public function execute(int $userId, int $limit = 100, int $offset = 0): array
    {
        $messages = $this->messagingService->getSent(
            UserId::fromInt($userId),
            $limit,
            $offset
        );

        // Load all user IDs for names
        $userIds = [];
        foreach ($messages as $message) {
            $userIds[] = $message->senderId()->value();
            $userIds[] = $message->recipientId()->value();
        }
        $userIds = array_unique($userIds);
        $users = User::whereIn('id', $userIds)->pluck('name', 'id')->toArray();

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
