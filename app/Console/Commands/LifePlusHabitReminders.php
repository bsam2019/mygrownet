<?php

namespace App\Console\Commands;

use App\Domain\LifePlus\Services\NotificationService;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LifePlusHabitReminders extends Command
{
    protected $signature = 'lifeplus:habit-reminders';
    protected $description = 'Send habit reminder notifications';

    public function handle(NotificationService $notificationService): int
    {
        $currentHour = Carbon::now()->format('H:i');
        $this->info("Checking habits with reminder time around {$currentHour}...");

        // Get habits with reminders within 5 minutes of current time
        $habits = LifePlusHabitModel::where('is_active', true)
            ->whereNotNull('reminder_time')
            ->whereRaw("TIME(reminder_time) BETWEEN TIME(?) AND TIME(?)", [
                Carbon::now()->subMinutes(2)->format('H:i:s'),
                Carbon::now()->addMinutes(2)->format('H:i:s'),
            ])
            ->with('user')
            ->get();

        $sent = 0;
        foreach ($habits as $habit) {
            if ($habit->user && $notificationService->sendHabitReminder($habit->user_id, $habit->name)) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} habit reminders");

        return self::SUCCESS;
    }
}
