<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\MessageDTO;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;

class GetInboxUseCase
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    /**
     * Get inbox messages for a user, optionally filtered by module.
     *
     * @param int $userId
     * @param int $limit
     * @param int $offset
     * @param string|null $module Filter by module (mygrownet, growfinance, growbiz, etc.)
     * @return array
     */
    public function execute(int $userId, int $limit = 100, int $offset = 0, ?string $module = null): array
    {
        $messages = $this->messagingService->getInbox(
            UserId::fromInt($userId),
            $limit,
            $offset,
            $module
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
