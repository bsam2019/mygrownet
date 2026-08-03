<?php

declare(strict_types=1);

namespace App\Domain\GrowStream\Presentation\Console\Commands;

use App\Domain\GrowStream\Services\RevenuePoolService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CalculateRevenueCommand extends Command
{
    protected $signature = 'growstream:calculate-revenue
        {period? : Period label or YYYY-MM for a specific month (defaults to last month)}
        {--revenue=0 : Total subscription revenue collected for the period (ZMW)}';

    protected $description = 'Calculate creator earnings from the premium watch-time revenue pool';

    public function handle(RevenuePoolService $service): int
    {
        $period = $this->argument('period') ?? now()->subMonth()->format('Y-m');
        $revenue = (float) $this->option('revenue');

        [$start, $end] = $this->resolvePeriod($period);

        if ($revenue <= 0) {
            $this->warn('No revenue provided (--revenue=). Earnings will still be computed with a zero pool.');

            if (! $this->confirm('Continue anyway?', true)) {
                return self::FAILURE;
            }
        }

        $this->info("Calculating revenue for {$start->toDateString()} → {$end->toDateString()} (pool revenue: ZMW {$revenue})");

        $earnings = $service->calculateForPeriod($start, $end, $revenue);

        if (empty($earnings)) {
            $this->info('No premium watch activity found for the period.');

            return self::SUCCESS;
        }

        $this->table(
            ['Creator ID', 'Premium seconds', 'Pool (ZMW)', 'Share %', 'Earned (ZMW)'],
            collect($earnings)->map(fn ($row) => [
                $row['creator_id'],
                $row['premium_watch_seconds'],
                number_format((float) $row['pool_amount'], 2),
                number_format((float) $row['share_percentage'], 2),
                number_format((float) $row['earned_amount'], 2),
            ])
        );

        $this->info('Earnings calculated for '.count($earnings).' creator(s).');

        return self::SUCCESS;
    }

    /**
     * @return array{\DateTimeInterface, \DateTimeInterface}
     */
    protected function resolvePeriod(string $period): array
    {
        if (str_contains($period, '→') || str_contains($period, ' to ')) {
            $parts = preg_split('/\s*[→>]\s*|\s+to\s+/i', $period);

            return [new \DateTimeImmutable(trim($parts[0])), new \DateTimeImmutable(trim($parts[1]))];
        }

        $date = CarbonImmutable::createFromFormat('Y-m', $period);
        if (! $date) {
            $this->error("Invalid period: {$period}. Use YYYY-MM or a date range.");

            throw new \InvalidArgumentException("Invalid period: {$period}");
        }

        return [$date->startOfMonth(), $date->endOfMonth()];
    }
}
