<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class StockFlowCheckGuardUsage extends Command
{
    protected $signature = 'stockflow:check-guard-usage
                            {--days=30 : Number of days to look back}
                            {--exit-criterion : Check if exit criterion is met (0 logins in period)}';

    protected $description = 'Check how many logins used the stockflow guard recently. Used for Phase 8d exit criterion.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $checkExit = $this->option('exit-criterion');

        $startDate = now()->subDays($days);

        $loginCount = AuditLog::where('event_type', AuditLog::EVENT_LOGIN_ATTEMPT)
            ->where('metadata->guard', 'stockflow')
            ->where('created_at', '>=', $startDate)
            ->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Lookback period', "{$days} days"],
                ['StockFlow guard logins', (string) $loginCount],
                ['Start date', $startDate->toDateTimeString()],
                ['End date', now()->toDateTimeString()],
            ]
        );

        if ($checkExit) {
            $criterionMet = $loginCount === 0;

            $this->newLine();
            $this->info('Exit criterion: 0 logins via stockflow guard for ' . $days . ' consecutive days.');

            if ($criterionMet) {
                $this->info('✅ Exit criterion MET. StockFlow guard can be removed.');
                $this->warn('Run MergeDuplicateUsers before removing the guard.');

                return Command::SUCCESS;
            }

            $this->error('❌ Exit criterion NOT MET. ' . $loginCount . ' login(s) found.');
            $this->line('The stockflow guard must remain until 0 logins for ' . $days . ' consecutive days.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
