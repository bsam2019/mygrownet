<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\MessageDTO;
use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;

class SendMessageUseCase
{
    public function __construct(
        private MessagingService $messagingService
    ) {}

    public function execute(SendMessageDTO $dto): MessageDTO
    {
        $senderId = UserId::fromInt($dto->senderId);
        $recipientId = UserId::fromInt($dto->recipientId);
        $content = MessageContent::create($dto->subject, $dto->body);
        $parentId = $dto->parentId ? MessageId::fromInt($dto->parentId) : null;

        // Validate users can message each other
        if (!$this->messagingService->canUserMessageRecipient($senderId, $recipientId)) {
            throw new \DomainException('You cannot send messages to this user');
        }

        $message = $this->messagingService->sendMessage(
            $senderId,
            $recipientId,
            $content,
            $parentId
        );

        // Load user names for DTO
        $sender = User::find($dto->senderId);
        $recipient = User::find($dto->recipientId);

        return MessageDTO::fromDomain(
            $message,
            $sender->name ?? 'Unknown',
            $recipient->name ?? 'Unknown'
        );
    }
}
