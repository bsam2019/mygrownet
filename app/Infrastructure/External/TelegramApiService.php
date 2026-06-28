<?php

namespace App\Infrastructure\External;

use App\Domain\Notification\Services\TelegramNotificationService;
use App\Domain\Notification\ValueObjects\TelegramChatId;
use App\Domain\Notification\ValueObjects\TelegramLinkCode;
use App\Models\User;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class TelegramApiService implements TelegramNotificationService
{
    public function sendMessage(TelegramChatId $chatId, string $message, array $options = []): bool
    {
        try {
            Telegram::sendMessage([
                'chat_id' => $chatId->value(),
                'text' => $message,
                'parse_mode' => $options['parse_mode'] ?? 'Markdown',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram message failed', [
                'chat_id' => $chatId->value(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function linkAccount(TelegramChatId $chatId, TelegramLinkCode $linkCode): ?User
    {
        $user = User::where('telegram_link_code', $linkCode->value())->first();

        if (!$user) {
            return null;
        }

        $user->update([
            'telegram_chat_id' => $chatId->value(),
            'telegram_notifications' => true,
            'telegram_linked_at' => now(),
            'telegram_link_code' => null,
        ]);

        return $user;
    }

    public function generateLinkCode(User $user): TelegramLinkCode
    {
        $linkCode = TelegramLinkCode::generate();
        
        $user->update(['telegram_link_code' => $linkCode->value()]);

        return $linkCode;
    }

    public function isLinked(User $user): bool
    {
        return !empty($user->telegram_chat_id) && $user->telegram_notifications;
    }
}
