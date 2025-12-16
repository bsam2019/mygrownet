<?php

namespace App\Domain\LifePlus\Services;

use App\Models\User;
use App\Domain\Notification\Services\FirebasePushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private string $fcmServerKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    private FirebasePushService $pushService;

    public function __construct(FirebasePushService $pushService)
    {
        $this->fcmServerKey = config('services.firebase.server_key', '');
        $this->pushService = $pushService;
    }

    /**
     * Send push notification to a user (supports multiple devices)
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        // Try new multi-device push service first
        if ($this->pushService->sendToUser($userId, $title, $body, $data)) {
            return true;
        }

        // Fallback to legacy single token on user model
        $user = User::find($userId);
        if (!$user || !$user->fcm_token) {
            return false;
        }

        return $this->send($user->fcm_token, $title, $body, $data);
    }

    /**
     * Send push notification to multiple users
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = User::whereIn('id', $userIds)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        return $this->sendToMultiple($tokens, $title, $body, $data);
    }

    /**
     * Send daily tip notification to all users with FCM tokens
     */
    public function sendDailyTip(string $tip): int
    {
        $tokens = User::whereNotNull('fcm_token')
            ->where('lifeplus_notifications_enabled', true)
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        return $this->sendToMultiple($tokens, '💡 Daily Tip', $tip, [
            'type' => 'daily_tip',
            'action' => 'open_knowledge',
        ]);
    }

    /**
     * Send habit reminder notification
     */
    public function sendHabitReminder(int $userId, string $habitName): bool
    {
        return $this->sendToUser($userId, '⏰ Habit Reminder', "Don't forget: {$habitName}", [
            'type' => 'habit_reminder',
            'action' => 'open_habits',
        ]);
    }

    /**
     * Send task due notification
     */
    public function sendTaskDue(int $userId, string $taskTitle): bool
    {
        return $this->sendToUser($userId, '📋 Task Due', $taskTitle, [
            'type' => 'task_due',
            'action' => 'open_tasks',
        ]);
    }

    /**
     * Send gig application notification
     */
    public function sendGigApplication(int $ownerId, string $gigTitle, string $applicantName): bool
    {
        return $this->sendToUser($ownerId, '🎯 New Gig Application', "{$applicantName} applied for: {$gigTitle}", [
            'type' => 'gig_application',
            'action' => 'open_my_gigs',
        ]);
    }

    /**
     * Send gig assigned notification
     */
    public function sendGigAssigned(int $workerId, string $gigTitle): bool
    {
        return $this->sendToUser($workerId, '✅ Gig Assigned', "You've been assigned: {$gigTitle}", [
            'type' => 'gig_assigned',
            'action' => 'open_my_gigs',
        ]);
    }

    /**
     * Send budget alert notification
     */
    public function sendBudgetAlert(int $userId, int $percentage): bool
    {
        $message = $percentage >= 100
            ? "You've exceeded your budget!"
            : "You've used {$percentage}% of your budget";

        return $this->sendToUser($userId, '💰 Budget Alert', $message, [
            'type' => 'budget_alert',
            'action' => 'open_money',
        ]);
    }

    /**
     * Register FCM token for user
     */
    public function registerToken(int $userId, string $token): bool
    {
        return User::where('id', $userId)->update(['fcm_token' => $token]) > 0;
    }

    /**
     * Unregister FCM token for user
     */
    public function unregisterToken(int $userId): bool
    {
        return User::where('id', $userId)->update(['fcm_token' => null]) > 0;
    }

    /**
     * Send notification to single device
     */
    private function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (empty($this->fcmServerKey)) {
            Log::warning('FCM server key not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('FCM notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     */
    private function sendToMultiple(array $tokens, string $title, string $body, array $data = []): int
    {
        if (empty($this->fcmServerKey)) {
            Log::warning('FCM server key not configured');
            return 0;
        }

        $sent = 0;
        $chunks = array_chunk($tokens, 1000); // FCM limit

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->fcmServerKey,
                    'Content-Type' => 'application/json',
                ])->post($this->fcmUrl, [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                    ],
                    'data' => $data,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $sent += $result['success'] ?? 0;
                }
            } catch (\Exception $e) {
                Log::error('FCM batch notification failed: ' . $e->getMessage());
            }
        }

        return $sent;
    }
}
