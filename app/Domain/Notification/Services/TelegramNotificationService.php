<?php

namespace App\Domain\Notification\Services;

use App\Domain\Notification\ValueObjects\TelegramChatId;
use App\Domain\Notification\ValueObjects\TelegramLinkCode;
use App\Models\User;

interface TelegramNotificationService
{
    /**
     * Send a notification message
     */
    public function sendMessage(TelegramChatId $chatId, string $message, array $options = []): bool;

    /**
     * Link user account to Telegram
     */
    public function linkAccount(TelegramChatId $chatId, TelegramLinkCode $linkCode): ?User;

    /**
     * Generate link code for user
     */
    public function generateLinkCode(User $user): TelegramLinkCode;

    /**
     * Check if user has Telegram linked
     */
    public function isLinked(User $user): bool;
}
