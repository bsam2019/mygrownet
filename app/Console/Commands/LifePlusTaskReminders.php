<?php

namespace App\Console\Commands;

use App\Domain\LifePlus\Services\NotificationService;
use App\Infrastructure\Persistence\Eloquent\LifePlusTaskModel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LifePlusTaskReminders extends Command
{
    protected $signature = 'lifeplus:task-reminders';
    protected $description = 'Send task due reminders';

    public function handle(NotificationService $notificationService): int
    {
        $this->info('Checking tasks due soon...');

        // Get tasks due within the next hour that haven't been completed
        $tasks = LifePlusTaskModel::where('is_completed', false)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [
                Carbon::now(),
                Carbon::now()->addHour(),
            ])
            ->get();

        $sent = 0;
        foreach ($tasks as $task) {
            if ($notificationService->sendTaskDue($task->user_id, $task->title)) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} task reminders");

        return self::SUCCESS;
    }
}
