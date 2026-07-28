<?php

namespace App\Console\Commands;

use App\Domain\GrowFinance\Services\CsvImportService;
use Illuminate\Console\Command;

class ImportAccounts extends Command
{
    protected $signature = 'growfinance:import-accounts {business_id} {file}';
    protected $description = 'Import chart of accounts from CSV file';

    public function handle(CsvImportService $csvService): int
    {
        $businessId = (int) $this->argument('business_id');
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $content = file_get_contents($filePath);
        $results = $csvService->importAccounts($businessId, $content);

        $this->info("Imported: {$results['imported']} accounts");
        foreach ($results['errors'] as $error) {
            $this->error($error);
        }

        return 0;
    }
}
