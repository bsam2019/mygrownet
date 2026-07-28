<?php

namespace App\Console\Commands;

use App\Domain\GrowFinance\Services\PastelMigrationService;
use Illuminate\Console\Command;

class PastelImportChartOfAccounts extends Command
{
    protected $signature = 'growfinance:pastel-import-coa {business_id} {file}';
    protected $description = 'Import Pastel chart of accounts from CSV file';

    public function handle(PastelMigrationService $pastelService): int
    {
        $businessId = (int) $this->argument('business_id');
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $handle = fopen($filePath, 'r');
        $accounts = [];
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $accounts[] = array_combine($headers, $row);
        }
        fclose($handle);

        $this->info("Importing " . count($accounts) . " accounts from Pastel...");
        $results = $pastelService->importChartOfAccounts($businessId, $accounts);

        $this->info("Imported: {$results['imported']}");
        $this->info("Skipped: {$results['skipped']}");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                $this->error($error);
            }
        }

        return 0;
    }
}
