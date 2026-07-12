<?php

namespace App\Console\Commands;

use App\Domain\Payment\Services\CurrencyConversionService;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'rates:refresh {base=USD}';
    protected $description = 'Refresh daily exchange rates from API';

    public function handle(CurrencyConversionService $service): int
    {
        $base = $this->argument('base');

        $this->info("Refreshing daily exchange rates for {$base}...");

        if (!$service->isConfigured()) {
            $this->warn('ExchangeRate API key is not configured.');
            $this->warn('Set EXCHANGERATE_API_KEY in your .env file.');

            if ($this->confirm('Set a placeholder rate for ZMW/USD?', false)) {
                $rate = (float) $this->ask('Enter 1 USD to ZMW rate', '27');
                $this->info("Rate set to {$rate} (placeholder only)");
            }

            return Command::FAILURE;
        }

        $success = $service->refreshAllDailyRates($base);

        if ($success) {
            $this->info("Daily exchange rates refreshed successfully for {$base}.");
            $this->table(
                ['Pair', 'Rate'],
                $this->getSampleRates($service)
            );
            return Command::SUCCESS;
        }

        $this->error('Failed to refresh exchange rates. Check the logs.');
        return Command::FAILURE;
    }

    private function getSampleRates(CurrencyConversionService $service): array
    {
        $pairs = [['USD', 'ZMW'], ['ZMW', 'USD'], ['USD', 'EUR'], ['EUR', 'USD']];
        $rows = [];

        foreach ($pairs as [$from, $to]) {
            $rate = $service->getDailyRate($from, $to);
            $rows[] = ["{$from}/{$to}", $rate ?? 'N/A'];
        }

        return $rows;
    }
}
