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
            $parentId,
            $dto->module,
            $dto->metadata
        );

        // Load user names for DTO
        $sender = User::find($dto->senderId);
        $recipient = User::find($dto->recipientId);

        // Determine the correct route based on module and recipient's role
        $actionUrl = $this->determineActionUrl($dto->module, $recipient, $message->id()->value());

        // Create a preview of the message body (first 100 characters)
        $preview = strlen($dto->body) > 100 ? substr($dto->body, 0, 100) . '...' : $dto->body;

        // Send notification to recipient
        $this->sendNotificationUseCase->execute(
            userId: $dto->recipientId,
            type: 'messages.received',
            data: [
                'title' => $dto->subject,
                'message' => "From {$sender->name}: {$preview}",
                'action_url' => $actionUrl,
                'action_text' => 'Read Message',
                'message_id' => $message->id()->value(),
                'sender_name' => $sender->name ?? 'Unknown',
                'subject' => $dto->subject,
                'preview' => $preview,
                'is_broadcast' => false,
                'module' => $dto->module,
            ]
        );

        return MessageDTO::fromDomain(
            $message,
            $sender->name ?? 'Unknown',
            $recipient->name ?? 'Unknown'
        );
    }

    /**
     * Determine the action URL based on module and recipient role.
     */
    private function determineActionUrl(string $module, User $recipient, int $messageId): string
    {
        // Admin always goes to admin route
        if ($recipient->hasRole('admin') || $recipient->hasRole('Administrator')) {
            return route('admin.messages.show', $messageId);
        }

        // Route based on module
        return match ($module) {
            'growfinance' => route('growfinance.messages.show', $messageId),
            'growbiz' => route('growbiz.messages.show', $messageId),
            default => route('mygrownet.messages.show', $messageId),
        };
    }
}
