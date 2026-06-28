<?php

namespace App\Application\UseCases\Notification;

use App\Domain\Notification\Services\TelegramNotificationService;
use App\Domain\Notification\ValueObjects\TelegramChatId;
use App\Domain\Notification\ValueObjects\TelegramLinkCode;
use App\Models\User;

class LinkTelegramAccountUseCase
{
    public function __construct(
        private TelegramNotificationService $telegramService
    ) {}

    /**
     * Generate link code for user
     */
    public function generateLinkCode(User $user): string
    {
        $linkCode = $this->telegramService->generateLinkCode($user);
        return $linkCode->value();
    }

    /**
     * Link user account using code
     */
    public function linkAccount(string $chatId, string $code): ?User
    {
        try {
            $telegramChatId = TelegramChatId::fromString($chatId);
            $linkCode = TelegramLinkCode::fromString($code);

            return $this->telegramService->linkAccount($telegramChatId, $linkCode);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}
