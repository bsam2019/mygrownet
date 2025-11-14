<?php

namespace App\Application\Messaging\UseCases;

use App\Application\Messaging\DTOs\SendMessageDTO;
use App\Application\Notification\UseCases\SendNotificationUseCase;
use App\Domain\Messaging\Services\MessagingService;
use App\Domain\Messaging\ValueObjects\MessageContent;
use App\Domain\Messaging\ValueObjects\UserId;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BroadcastMessageUseCase
{
    public function __construct(
        private MessagingService $messagingService,
        private SendNotificationUseCase $sendNotificationUseCase
    ) {}

    /**
     * Send a message to all members
     * 
     * @param int $senderId Admin user ID
     * @param string $subject Message subject
     * @param string $body Message body
     * @param array $filters Optional filters (e.g., ['role' => 'Member', 'has_starter_kit' => true])
     * @return array Statistics about the broadcast
     */
    public function execute(int $senderId, string $subject, string $body, array $filters = []): array
    {
        $sender = User::findOrFail($senderId);
        
        // Verify sender is admin
        if (!$sender->hasRole('admin') && !$sender->hasRole('Administrator')) {
            throw new \DomainException('Only administrators can broadcast messages');
        }

        // Get recipients based on filters
        $recipients = $this->getRecipients($senderId, $filters);
        
        if ($recipients->isEmpty()) {
            throw new \DomainException('No recipients found matching the criteria');
        }

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $content = MessageContent::create($subject, $body);
            $senderUserId = UserId::fromInt($senderId);

            foreach ($recipients as $recipient) {
                try {
                    // Send message
                    $message = $this->messagingService->sendMessage(
                        $senderUserId,
                        UserId::fromInt($recipient->id),
                        $content,
                        null // No parent for broadcast
                    );

                    // Send notification
                    $actionUrl = $recipient->hasRole('admin') || $recipient->hasRole('Administrator')
                        ? route('admin.messages.show', $message->id()->value())
                        : route('mygrownet.messages.show', $message->id()->value());

                    $this->sendNotificationUseCase->execute(
                        userId: $recipient->id,
                        type: 'messages.received',
                        data: [
                            'title' => 'Broadcast Message',
                            'message' => "Broadcast from {$sender->name}: {$subject}",
                            'action_url' => $actionUrl,
                            'action_text' => 'View Message',
                            'message_id' => $message->id()->value(),
                            'sender_name' => $sender->name,
                            'subject' => $subject,
                            'preview' => substr($body, 0, 100),
                        ]
                    );

                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    $errors[] = "Failed to send to {$recipient->name} (ID: {$recipient->id}): {$e->getMessage()}";
                    Log::error('Broadcast message failed for recipient', [
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            Log::info('Broadcast message completed', [
                'sender_id' => $senderId,
                'total_recipients' => $recipients->count(),
                'success_count' => $successCount,
                'failure_count' => $failureCount
            ]);

            return [
                'success' => true,
                'total_recipients' => $recipients->count(),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Broadcast message transaction failed', [
                'sender_id' => $senderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get recipients based on filters
     */
    private function getRecipients(int $senderId, array $filters)
    {
        $query = User::where('id', '!=', $senderId);

        // Apply filters
        if (isset($filters['role'])) {
            $query->role($filters['role']);
        }

        if (isset($filters['has_starter_kit'])) {
            $query->where('has_starter_kit', $filters['has_starter_kit']);
        }

        if (isset($filters['professional_level'])) {
            $query->where('professional_level', $filters['professional_level']);
        }

        if (isset($filters['active_subscription'])) {
            $query->whereHas('subscription', function ($q) {
                $q->where('status', 'active');
            });
        }

        return $query->select('id', 'name', 'email')->get();
    }
}
