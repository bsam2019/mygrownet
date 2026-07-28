<?php

namespace App\Console\Commands;

use App\Domain\GrowFinance\Services\PastelMigrationService;
use Illuminate\Console\Command;

class PastelImportOpeningBalances extends Command
{
    protected $signature = 'growfinance:pastel-import-balances {business_id} {file} {as_of_date}';
    protected $description = 'Import Pastel opening balances from CSV file';

    public function handle(PastelMigrationService $pastelService): int
    {
        $businessId = (int) $this->argument('business_id');
        $filePath = $this->argument('file');
        $asOfDate = $this->argument('as_of_date');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $handle = fopen($filePath, 'r');
        $balances = [];
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $balances[] = array_combine($headers, $row);
        }
        fclose($handle);

        $this->info("Importing " . count($balances) . " opening balances...");
        $results = $pastelService->importOpeningBalances($businessId, $balances, $asOfDate);

        $this->info("Posted: {$results['posted']}");

        if (!empty($results['errors'])) {
            foreach ($results['errors'] as $error) {
                $this->error($error);
            }
        }

        $tb = $pastelService->verifyTrialBalance($businessId);
        $this->line("Trial Balance: Debit={$tb['total_debit']}, Credit={$tb['total_credit']}, Diff={$tb['difference']}");
        $this->info($tb['is_balanced'] ? 'Trial balance is balanced!' : 'Trial balance is NOT balanced!');

        return 0;
    }
}
