<?php

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Services\OpsHealthService;
use Illuminate\Console\Command;

class CheckGrowStreamOpsCommand extends Command
{
    protected $signature = 'growstream:check-ops {--cloudflare-ping : Also verify Cloudflare Stream API reachability}';

    protected $description = 'Check GrowStream operational health (Cloudflare quota, PawaPay webhook, failed jobs) and fire alerts';

    public function handle(OpsHealthService $ops): int
    {
        $this->info('Checking GrowStream operational health...');

        $alerts = $ops->checkAll();

        if ($this->option('cloudflare-ping')) {
            $reachability = $ops->verifyCloudflareReachability();
            if ($reachability) {
                $alerts[] = $reachability;
            }
        }

        if (empty($alerts)) {
            $this->info('All GrowStream ops checks passed.');
            return 0;
        }

        foreach ($alerts as $alert) {
            $this->warn("[{$alert['severity']}] {$alert['message']}");
        }

        return 1;
    }
}
