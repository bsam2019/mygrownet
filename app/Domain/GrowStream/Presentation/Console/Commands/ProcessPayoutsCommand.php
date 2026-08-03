<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Services\PayoutService;
use Illuminate\Console\Command;

class ProcessPayoutsCommand extends Command
{
    protected $signature = 'growstream:process-payouts {--reference-prefix=PAY}';

    protected $description = 'Create payout records for creators with eligible pending earnings';

    public function handle(PayoutService $service): int
    {
        $payouts = $service->processEligible($this->option('reference-prefix'));

        if (empty($payouts)) {
            $this->info('No creators met the minimum payout threshold.');

            return self::SUCCESS;
        }

        $this->table(
            ['Payout ID', 'Creator ID', 'Amount (ZMW)', 'Status', 'Reference'],
            collect($payouts)->map(fn ($payout) => [
                $payout['id'],
                $payout['creator_id'],
                number_format((float) $payout['amount'], 2),
                $payout['status'],
                $payout['reference'],
            ])
        );

        $total = array_sum(array_column($payouts, 'amount'));
        $this->info(count($payouts).' payout(s) created totaling ZMW '.number_format($total, 2).'.');

        return self::SUCCESS;
    }
}
