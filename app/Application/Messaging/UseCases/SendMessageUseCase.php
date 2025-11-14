<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\MessageDTO;
use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\MessageId;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;

class SendMessageUseCase
{
    public function __construct(
        private MessagingService $messagingService,
        private SendNotificationUseCase $sendNotificationUseCase
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

        // Determine the correct route based on recipient's role
        $actionUrl = ($recipient->hasRole('admin') || $recipient->hasRole('Administrator'))
            ? route('admin.messages.show', $message->id()->value())
            : route('mygrownet.messages.show', $message->id()->value());

        // Send notification to recipient
        $this->sendNotificationUseCase->execute(
            userId: $dto->recipientId,
            type: 'messages.received',
            data: [
                'title' => 'New Message',
                'message' => "New message from {$sender->name}: {$dto->subject}",
                'action_url' => $actionUrl,
                'action_text' => 'View Message',
                'message_id' => $message->id()->value(),
                'sender_name' => $sender->name ?? 'Unknown',
                'subject' => $dto->subject,
                'preview' => substr($dto->body, 0, 100),
            ]
        );

        return MessageDTO::fromDomain(
            $message,
            $sender->name ?? 'Unknown',
            $recipient->name ?? 'Unknown'
        );
    }
}
