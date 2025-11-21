<?php

namespace App\Traits;

use App\Application\UseCases\Notification\SendTelegramNotificationUseCase;
use App\Models\User;

trait SendsTelegramNotifications
{
    /**
     * Send Telegram notification to user
     */
    protected function notifyViaTelegram(User $user, string $type, ...$params): void
    {
        try {
            $telegram = app(SendTelegramNotificationUseCase::class);

            match($type) {
                'otp' => $telegram->sendOTP($user, $params[0]),
                'payment' => $telegram->sendPaymentConfirmation($user, $params[0], $params[1]),
                'level_upgrade' => $telegram->sendLevelUpgrade($user, $params[0]),
                'commission' => $telegram->sendCommissionEarned($user, $params[0], $params[1]),
                'withdrawal' => $telegram->sendWithdrawalProcessed($user, $params[0], $params[1]),
                default => false
            };
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error('Telegram notification failed', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }
}
