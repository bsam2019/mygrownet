<?php

namespace App\Console\Commands\BizBoost;

use App\Jobs\BizBoost\SendFollowUpReminderJob;
use Illuminate\Console\Command;

class SendFollowUpRemindersCommand extends Command
{
    protected $signature = 'bizboost:send-reminders';

    protected $description = 'Process and send due follow-up reminders to users';

    public function handle(): int
    {
        $this->info('Dispatching follow-up reminder job...');

        SendFollowUpReminderJob::dispatch();

        $this->info('Follow-up reminder job dispatched successfully.');

        return Command::SUCCESS;
    }
}
