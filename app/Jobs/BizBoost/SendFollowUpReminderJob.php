<?php

namespace App\Jobs\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Models\User;
use App\Notifications\BizBoost\FollowUpReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendFollowUpReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct()
    {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        Log::info('SendFollowUpReminderJob: Starting reminder check');

        // Get all due reminders - check both remind_at and due_date/due_time
        $dueReminders = DB::table('bizboost_follow_up_reminders')
            ->where('status', 'pending')
            ->where('notification_sent', false)
            ->where(function ($query) {
                $query->where('remind_at', '<=', now())
                    ->orWhere(function ($q) {
                        // Fallback: use due_date + due_time if remind_at is null
                        $q->whereNull('remind_at')
                          ->whereRaw("CONCAT(due_date, ' ', due_time) <= ?", [now()]);
                    });
            })
            ->get();

        Log::info("SendFollowUpReminderJob: Found {$dueReminders->count()} due reminders");

        foreach ($dueReminders as $reminder) {
            try {
                $this->processReminder($reminder);
            } catch (\Exception $e) {
                Log::error("SendFollowUpReminderJob: Failed to process reminder {$reminder->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('SendFollowUpReminderJob: Completed');
    }

    protected function processReminder(object $reminder): void
    {
        // Get the business and user
        $business = BizBoostBusinessModel::find($reminder->business_id);
        if (!$business) {
            $this->markReminderFailed($reminder->id, 'Business not found');
            return;
        }

        $user = User::find($business->user_id);
        if (!$user) {
            $this->markReminderFailed($reminder->id, 'User not found');
            return;
        }

        // Get customer info if applicable
        $customer = null;
        if ($reminder->customer_id) {
            $customer = DB::table('bizboost_customers')
                ->where('id', $reminder->customer_id)
                ->first();
        }

        // Send notification
        $user->notify(new FollowUpReminderNotification(
            title: $reminder->title,
            description: $reminder->description,
            customerName: $customer?->name,
            reminderType: $reminder->reminder_type,
            businessName: $business->name,
        ));

        // Mark notification as sent (keep status as pending until user completes it)
        DB::table('bizboost_follow_up_reminders')
            ->where('id', $reminder->id)
            ->update([
                'notification_sent' => true,
                'sent_at' => now(),
                'updated_at' => now(),
            ]);

        Log::info("SendFollowUpReminderJob: Sent reminder {$reminder->id} to user {$user->id}");
    }

    protected function markReminderFailed(int $reminderId, string $reason): void
    {
        // Mark as notification sent but log the failure reason
        // We don't change status since 'failed' isn't in the enum
        DB::table('bizboost_follow_up_reminders')
            ->where('id', $reminderId)
            ->update([
                'notification_sent' => true, // Prevent retry loops
                'notes' => "Notification failed: {$reason}",
                'updated_at' => now(),
            ]);

        Log::warning("SendFollowUpReminderJob: Marked reminder {$reminderId} as failed - {$reason}");
    }
}
