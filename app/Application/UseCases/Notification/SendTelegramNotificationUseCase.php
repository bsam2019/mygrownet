<?php

namespace App\Application\UseCases\Notification;

use App\Domain\Notification\Services\TelegramNotificationService;
use App\Domain\Notification\ValueObjects\TelegramChatId;
use App\Models\User;

class SendTelegramNotificationUseCase
{
    public function __construct(
        private TelegramNotificationService $telegramService
    ) {}

    public function sendOTP(User $user, string $code): bool
    {
        if (!$this->telegramService->isLinked($user)) {
            return false;
        }

        $message = "🔐 *MyGrowNet OTP Code*\n\n";
        $message .= "Your verification code is: `{$code}`\n\n";
        $message .= "This code expires in 10 minutes.\n";
        $message .= "Never share this code with anyone.";

        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        return $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }

    public function sendPaymentConfirmation(User $user, float $amount, string $type): bool
    {
        if (!$this->telegramService->isLinked($user)) {
            return false;
        }

        $message = "✅ *Payment Confirmed*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Type: {$type}\n";
        $message .= "Status: Verified\n\n";
        $message .= "Thank you for your payment!";

        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        return $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }

    public function sendLevelUpgrade(User $user, string $newLevel): bool
    {
        if (!$this->telegramService->isLinked($user)) {
            return false;
        }

        $message = "🎉 *Congratulations!*\n\n";
        $message .= "You've been promoted to *{$newLevel}*!\n\n";
        $message .= "New benefits unlocked:\n";
        $message .= "• Higher commission rates\n";
        $message .= "• Exclusive resources\n";
        $message .= "• Priority support\n\n";
        $message .= "Keep growing! 🚀";

        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        return $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }

    public function sendCommissionEarned(User $user, float $amount, string $source): bool
    {
        if (!$this->telegramService->isLinked($user)) {
            return false;
        }

        $message = "💰 *Commission Earned!*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Source: {$source}\n\n";
        $message .= "Check your dashboard for details.";

        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        return $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }

    public function sendWithdrawalProcessed(User $user, float $amount, string $status): bool
    {
        if (!$this->telegramService->isLinked($user)) {
            return false;
        }

        $emoji = $status === 'approved' ? '✅' : '❌';
        $message = "{$emoji} *Withdrawal " . ucfirst($status) . "*\n\n";
        $message .= "Amount: K" . number_format($amount, 2) . "\n";
        $message .= "Status: " . ucfirst($status) . "\n\n";
        
        if ($status === 'approved') {
            $message .= "Funds will be transferred within 24 hours.";
        } else {
            $message .= "Please contact support for more information.";
        }

        $chatId = TelegramChatId::fromString($user->telegram_chat_id);
        return $this->telegramService->sendMessage($chatId, $message, ['parse_mode' => 'Markdown']);
    }
}
